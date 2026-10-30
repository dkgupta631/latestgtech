<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;

use App\Models\DepositTransaction;
use App\Models\GatewayChannel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Merchant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use App\Events\DepositCreated;
use App\Models\TransactionNotification;


class AcledaPaymentController extends Controller
{
    const SERVICE_PATH = '/XPAYConnectorServiceInterfaceImplV2/XPAYConnectorServiceInterfaceImplV2RS/';

    /**
     * Deposit form (reference id, amount, currency, KHQR/card).
     */
    public function AcDepositform()
    {
        return view('payment-form.acleda.deposit-form');
    }

    public function acledaDepositApifun(Request $request)
    {
        // Direct URL support: /api/acleda/deposit?amount=100&Currency=USD&merchant_code=khbiz001&channel=khqr
        // referenceId, channel_id and callback_url are optional and get defaults below.
        $validator = Validator::make($request->all(), [
            'merchant_code' => 'required',
            'referenceId'   => 'nullable|max:64',
            'Currency'      => 'required|in:USD,KHR',
            'amount'        => 'required|numeric|gt:0',
            'channel'       => 'nullable|in:khqr,card',
            'callback_url'  => 'nullable|url',
        ]);
        if ($validator->fails()) {
            echo "<pre>"; print_r($validator->errors()->all()); die;
        }

        $request->mergeIfMissing([
            'referenceId'  => 'GZTRN' . time() . Str::random(3),
            'callback_url' => route('apiroute.AcledaDepositResponse'),
        ]);
        if (!$request->filled('channel_id')) {
            // active ACLEDA channel (local 7, live 8)
            $request->merge(['channel_id' => GatewayChannel::where('status', 1)
                ->whereHas('gatewayAccount', fn ($q) => $q->where('gateway_name', 'Acleda'))
                ->value('id')]);
        }

        // fetching gateway details
        $res = RichPayController::getGatewayParameters($request->merchant_code, $request->channel_id);
        if (in_array($res, [
            'Invalid Merchant!', 'Merchant is Disabled!', 'Invalid Channel!', 'Channel is Disabled!',
            'Gateway is Disabled!', 'Gateway not configured for this Merchant!',
            'Gateway configuration is Disabled for this Merchant!', 'Parameter not set!',
        ])) {
            echo "<pre>"; print_r($res); die;
        }
        $params = $res['parameters'];

        $client_ip   = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
        $cleanAmount = str_replace(",", "", $request->amount);
        $frtransaction = RichPayController::generateUniqueCode();
        // ACLEDA txid max length is 16, so drop the "TR20" prefix (TR20251030122952 1234 -> 2510301229521234)
        $txid = substr($frtransaction, 4);
        $paymentCard = $request->channel === 'card' ? '1' : '0';    // 1 = Visa/Master, 0 = KHQR
        $expiryTime  = (string) ($params['expiry_minutes'] ?? 5);

        // for ACLEDA deposit charge START
        $percentage = $params['percentage_charge'] ?? 0;
        $mdr_fee_amount = ($percentage / 100) * $cleanAmount;
        $net_amount = $cleanAmount - $mdr_fee_amount;
        // for ACLEDA deposit charge END

        $sessionRequest = [
            'loginId'    => $params['login_id'],
            'password'   => $params['password'],
            'merchantID' => $params['merchant_id'],
            'hash'       => $this->makeHash($params, $txid),
            'xpayTransaction' => [
                'txid'             => $txid,
                'purchaseAmount'   => $this->formatAmount($cleanAmount),
                'purchaseCurrency' => $request->Currency,
                'purchaseDate'     => gmdate('d-m-Y'),
                'purchaseDesc'     => 'Deposit',
                'invoiceid'        => $frtransaction,
                'item'             => '1',
                'quantity'         => '1',
                'expiryTime'       => $expiryTime,
                'paymentCard'      => $paymentCard,
            ],
        ];

        $result = $this->callAcleda($params, 'openSessionV2', $sessionRequest);

        $addRecord = [
            'agent_id' => $res['merchantdata']['agent_id'],
            'merchant_id' => $res['merchantdata']['id'],
            'merchant_code' => $request->merchant_code,
            'reference_id' => $request->referenceId,
            'systemgenerated_TransId' => $frtransaction,
            'gateway_TransId' => $result['result']['xTran']['paymentTokenid'] ?? '',
            'callback_url' => $request->callback_url,
            'amount' => $cleanAmount,
            'Currency' => $request->Currency,
            'payment_channel' => $res['channel']['id'] ?? '',
            'payment_method' => $paymentCard === '1' ? 'card' : 'KHQR',
            // never store login/password/hash of the gateway
            'request_data' => json_encode(['xpayTransaction' => $sessionRequest['xpayTransaction']]),
            'gateway_name' => $res['gateway_account']['gateway_name'],
            'customer_name' => $request->customer_name,
            'payin_arr' => json_encode($result),
            'ip_address' => $client_ip,
            'net_amount' => $net_amount,
            'mdr_fee_amount' => $mdr_fee_amount,
        ];

        if (isset($result['result']['code']) && $result['result']['code'] == 0 && !empty($result['result']['sessionid'])) {
                $addRecord['payment_status'] = 'pending';
                DepositTransaction::create($addRecord);

                $this->sendNotification('Deposit', [
                    'type' => 'Deposit',
                    'transaction_id' => $frtransaction,
                    'amount' => $cleanAmount,
                    'Currency' => $request->Currency,
                    'status' => 'pending',
                    'msg' => 'New Deposit Transaction Created!',
                ], $res['merchantdata']['agent_id'], $res['merchantdata']['id']);

                // Redirect (GET) to checkout page, so refresh/back does not open a new session
                return redirect()->to(url('acleda/deposit/checkout/' . $frtransaction));

        } else {
                $error = $result['result']['errorDetails'] ?? $this->lastError ?? 'Payment gateway error';
                $addRecord['payment_status'] = 'failed';
                $addRecord['receipt_url'] = substr($error, 0, 255);
                DepositTransaction::create($addRecord);

                return view('payment-form.acleda.deposit-form')
                    ->withErrors(['acleda' => 'ACLEDA payment could not be started: ' . $error . '. Please try again.']);
        }
    }

    /**
     * Page with a hidden form that auto-submits to ACLEDA paymentPage.jsp.
     */
    public function AcDepositCheckout($frtransaction)
    {
        $paymentDetail = DepositTransaction::where('systemgenerated_TransId', $frtransaction)
            ->where('gateway_name', 'Acleda')
            ->first();
        $session = json_decode($paymentDetail->payin_arr ?? '', true)['result'] ?? [];
        if (!$paymentDetail || empty($session['sessionid'])) {
            abort(404, 'Transaction not found');
        }
        // already paid / failed / expired: show the result page instead of opening the payment page again
        if ($paymentDetail->payment_status !== 'pending') {
            return redirect()->to(url('acleda/deposit/gatewayResponse/' . $frtransaction));
        }

        $res = RichPayController::getGatewayParameters($paymentDetail->merchant_code, $paymentDetail->payment_channel);
        if (!is_array($res)) {
            echo "<pre>"; print_r($res); die;
        }
        $params = $res['parameters'];
        $xpay = json_decode($paymentDetail->request_data, true)['xpayTransaction'] ?? [];

        $returnUrl = url('acleda/deposit/gatewayResponse/' . $frtransaction);
        $paymentPage = $res['gateway_account']['website_url'] ?: rtrim($params['base_url'], '/') . '/paymentPage.jsp';
        $formData = [
            'merchantID'         => $params['merchant_id'],
            'sessionid'          => $session['sessionid'],
            'paymenttokenid'     => $paymentDetail->gateway_TransId,
            'description'        => $xpay['purchaseDesc'] ?? 'Deposit',
            'expirytime'         => $xpay['expiryTime'] ?? '5',
            'amount'             => $this->formatAmount($paymentDetail->amount),
            'quantity'           => '1',
            'item'               => '1',
            'invoiceid'          => $frtransaction,
            'currencytype'       => $paymentDetail->Currency,
            'transactionID'      => $xpay['txid'] ?? substr($frtransaction, 4),
            'successUrlToReturn' => $returnUrl,
            'errorUrl'           => $returnUrl,
        ];
        return view('payment-form.acleda.redirect-payment-page', compact('paymentPage', 'formData'));
    }

    /**
     * Customer comes back from ACLEDA payment page (success or error URL).
     * ACLEDA query params (_paymentresult etc.) are not trusted, status is taken from getTxnStatus.
     */
    public function AcDepositGatewayResponse(Request $request, $frtransaction)
    {
        $paymentDetail = DepositTransaction::where('systemgenerated_TransId', $frtransaction)->first();
        if (!$paymentDetail) {
            abort(404, 'Transaction not found');
        }

        $this->syncStatus($paymentDetail, $request->query());

        $callbackUrl = $paymentDetail->callback_url;
        $postData = [
            'merchant_code' => $paymentDetail->merchant_code,
            'referenceId' => $paymentDetail->reference_id,
            'transaction_id' => $paymentDetail->systemgenerated_TransId,
            'amount' => $paymentDetail->amount,
            'Currency' => $paymentDetail->Currency,
            'customer_name' => $paymentDetail->customer_name,
            'payment_status' => $paymentDetail->payment_status,
            'created_at' => $paymentDetail->created_at,
        ];
        return view('payment.payment_status', compact('request', 'postData', 'callbackUrl'));
    }

    /**
     * Push notification from ACLEDA server (_paymenttokenid, _paymentresult, _resultCode ...).
     * Payload is only used to find the transaction, status is confirmed with getTxnStatus.
     */
    public function acledaWebhookNotifiication(Request $request)
    {
        Log::info('ACLEDA webhook', $request->all());

        $paymentDetail = DepositTransaction::where('gateway_TransId', $request->input('_paymenttokenid'))
            ->where('gateway_name', 'Acleda')
            ->first();
        if (!$paymentDetail) {
            return response()->json(['status' => 'failed', 'message' => 'Transaction not found'], 404);
        }

        $status = $this->syncStatus($paymentDetail, $request->all());
        return response()->json(['status' => 'success', 'payment_status' => $status]);
    }

    // Merchant callback page (sample merchant side)
    public function acledaDepositResponse(Request $request)
    {
        $data = $request->all();
        return view('payment-form.r2p.deposit-response-page', compact('data'));
    }

    /**
     * Check status on ACLEDA, update deposit_transactions and send notification when status changed.
     */
    protected function syncStatus(DepositTransaction $paymentDetail, array $gatewayReturn = []): string
    {
        // final success never goes back, failed before session has no token to check
        if ($paymentDetail->payment_status === 'success' || empty($paymentDetail->gateway_TransId)) {
            return $paymentDetail->payment_status;
        }

        $res = RichPayController::getGatewayParameters($paymentDetail->merchant_code, $paymentDetail->payment_channel);
        if (!is_array($res)) {
            Log::error('ACLEDA status check: ' . $res, ['transaction' => $paymentDetail->systemgenerated_TransId]);
            return $paymentDetail->payment_status;
        }
        $params = $res['parameters'];

        $result = $this->callAcleda($params, 'getTxnStatus', [
            'loginId'        => $params['login_id'],
            'password'       => $params['password'],
            'merchantId'     => $params['merchant_id'],
            'merchantName'   => $params['merchant_name'],
            'hash'           => $this->makeHash($params, $paymentDetail->gateway_TransId),
            'paymentTokenid' => $paymentDetail->gateway_TransId,
        ]);

        // Map gateway status to our internal payment status
        // code may come as 0 or "0"
        $code = isset($result['result']['code']) && is_numeric($result['result']['code']) ? (int) $result['result']['code'] : null;
        $details = strtoupper((string) ($result['result']['errorDetails'] ?? ''));
        switch (true) {
            case $code === 0 && $details === 'SUCCESS':
                $paymentStatus = 'success';
                break;
            case $code === -23:     // Transaction Awaiting For Payment!
            case $code === null:    // no answer from gateway, check again later
                $paymentStatus = 'pending';
                break;
            default:
                $paymentStatus = 'failed';
                break;
        }

        $oldStatus = $paymentDetail->payment_status;
        $paymentDetail->update([
            'payment_status' => $paymentStatus,
            // coreRefNum (T24 reference) is returned for unpaid transactions too, keep it only when paid
            'receipt_url' => $paymentStatus === 'success' ? ($result['result']['coreRefNum'] ?? null) : ($result['result']['errorDetails'] ?? $paymentDetail->receipt_url),
            'customer_name' => $paymentDetail->customer_name ?: ($result['result']['customerName'] ?? null),
            'response_data' => json_encode(['gateway_return' => $gatewayReturn, 'txn_status' => $result]),
        ]);

        if ($oldStatus !== $paymentStatus) {
            $this->sendNotification('Transaction Updated', [
                'type' => 'Transaction Updated',
                'transaction_id' => $paymentDetail->systemgenerated_TransId,
                'amount' => $paymentDetail->amount,
                'Currency' => $paymentDetail->Currency,
                'status' => $paymentStatus,
                'msg' => 'Transaction Status Updated!',
            ], $paymentDetail->agent_id, $paymentDetail->merchant_id);
        }

        return $paymentStatus;
    }

    // Broadcast the event and insert in Notification table
    protected function sendNotification(string $type, array $data, $agentId, $merchantId): void
    {
        event(new DepositCreated($data));
        TransactionNotification::create([
            'notifiable_type' => $type,
            'agent_id' => $agentId,
            'merchant_id' => $merchantId,
            'data' => json_encode($data, true),
            'msg' => $data['msg'],
        ]);
    }

    // Uppercase HEX HMAC-SHA512 of merchantID + loginId + password + (txid | paymentTokenid)
    protected function makeHash(array $params, string $value): string
    {
        $message = $params['merchant_id'] . $params['login_id'] . $params['password'] . $value;
        return strtoupper(hash_hmac('sha512', $message, $params['secret']));
    }

    // Last connection/HTTP error of callAcleda(), shown to the customer
    protected ?string $lastError = null;

    protected function callAcleda(array $params, string $api, array $body): ?array
    {
        $this->lastError = null;
        try {
            $response = Http::timeout((int) ($params['timeout'] ?? 60))
                ->acceptJson()
                ->post(rtrim($params['base_url'], '/') . self::SERVICE_PATH . $api, $body);
        } catch (\Throwable $e) {
            Log::error("ACLEDA {$api} failed: " . $e->getMessage());
            $this->lastError = 'cannot connect to ACLEDA';
            return null;
        }

        Log::info("ACLEDA {$api}", ['http_status' => $response->status(), 'response' => $response->json() ?? $response->body()]);
        if (!$response->successful() || !is_array($response->json())) {
            $this->lastError = "ACLEDA {$api} HTTP {$response->status()}";
            return null;
        }
        return $response->json();
    }

    // Amount without trailing zeros ("5", "1.5") like the ACLEDA samples
    protected function formatAmount($amount): string
    {
        return rtrim(rtrim(number_format((float) $amount, 2, '.', ''), '0'), '.');
    }
}
