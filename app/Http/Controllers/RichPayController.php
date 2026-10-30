<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\GatewayAccount;
use App\Models\GatewayChannel;
use App\Models\GatewayChannelParameter;
use App\Models\GatewayConfigurationMerchant;
use App\Models\DepositTransaction;
use App\Models\WithdrawTransaction;
use App\Models\Merchant;
use App\Events\DepositCreated;
use App\Models\TransactionNotification;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Session;

class RichPayController extends Controller
{
    public function payin(Request $request)
    {
        $validatedData = $request->validate([
            'bank_code' => 'required',
            // 'bank_code_character' => 'required',
            'bank_account_name' => 'required',
            'customer_account_number' => 'required',
        ]);
        // echo "<pre>";  print_r($request->all()); die;
        // fetching gateway details START
        $res = $this->getGatewayParameters($request->merchant_code, $request->channel_id);
        // fetching gateway details END
        if($res == 'Invalid Merchant!' || $res == 'Merchant is Disabled!' || $res == 'Invalid Channel!' || $res == 'Channel is Disabled!' || $res == 'Gateway is Disabled!' || $res == 'Gateway not configured for this Merchant!' || $res == 'Gateway configuration is Disabled for this Merchant!' || $res == 'Parameter not set!'){
             echo "<pre>";  print_r($res); die;
        }
        $frtransaction = $this->generateUniqueCode();
        $client_ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
        $secretKey =  $res['parameters']['secretKey'] ?? 'Z0FBQUFBQm55WHJRYllhRGdjNXl5NjFvTDRLRHNhcElGamN3'; 
        $accessToken = $res['parameters']['accessToken'];
        $orderId = $frtransaction; // Replace with actual Order ID
        $cleanAmount = str_replace(",", "", $request->amount);
        // Step 1: Concatenate in required format
        $signatureString = "{$secretKey}:{$orderId}:{$cleanAmount}";
        // Step 2: Encode using Base64
        $encodedSignature = base64_encode($signatureString);

        // Call Curl API code START
        
        $postData = [
            // 'UrlFront' => url('s2p/payinResponse'), 
            'order_id' => $frtransaction,
            'amount' => $cleanAmount,
            'ref_account' => $request->customer_account_number,
            'ref_bank_code' => $request->bank_code,
            'ref_name_th' => $request->customer_name,
            'ref_name_en' => $request->customer_name,
            'ref_user_id' => '',
            'ref1' => '',
            'ref2' => '',
            'callback_url' => url('api/r2pDepositNotifiication'),
        ];
        //  echo "<pre>";  print_r($postData); die;
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken, // Correct concatenation
            'x-api-key' => $res['parameters']['apiKey'] ?? 'c48beec83f740331c0ff58', // Correct usage of array key
            'x-signature' => $encodedSignature, // No change needed
        ])->post($res['parameters']['apiUrl'] ?? 'https://service.richpay.io/api/v1/client/create_deposit', $postData);
        $jsonData = $response->json();
        // echo "<pre>";  print_r($jsonData); die;
        // Redirect to the payment link
        if (isset($jsonData['qr_image_link'])) {
            //Insert data into DB
             // for speedpay deposit charge START
            if(!empty($cleanAmount)){
                $percentage = $res['parameters']['percentage_charge'];     // Deposit Charge for RichPay
                $totalWidth = $cleanAmount;
                $mdr_fee_amount = ($percentage / 100) * $totalWidth;
                $net_amount= $totalWidth-$mdr_fee_amount;
            }
            // for speedpay deposit charge END
            $addRecord = [
                'agent_id' => $res['merchantdata']['agent_id'],
                'merchant_id' => $res['merchantdata']['id'],
                'merchant_code' => $request->merchant_code,
                'reference_id' => $request->referenceId,
                'systemgenerated_TransId' => $frtransaction,
                'gateway_TransId' => $jsonData['ref_id'],
                'callback_url' => $request->callback_url,
                'amount' => $cleanAmount,
                'Currency' => $request->Currency,
                'bank_account_name' => $request->bank_account_name ?? $request->customer_name,
                'bank_code' => $request->bank_code_character ?? $request->bank_code,
                'bank_account_number' => $request->customer_account_number,
                'payment_channel' => $res['channel']['id'] ?? '',
                'payment_method' => $res['gateway_account']['payment_method'] ?? 'QR Payment',
                'request_data' => json_encode($postData),
                'gateway_name' => $res['gateway_account']['gateway_name'],
                'customer_name' => $request->customer_name ?? $request->bank_account_name,
                'customer_email' => $request->customer_email,
                'payin_arr' => json_encode($jsonData),
                'receipt_url' => $jsonData['qr_image_link'],
                'ip_address' => $client_ip,
                'net_amount' => $net_amount ?? '',
                'mdr_fee_amount' => $mdr_fee_amount ?? '',
            ];
            // echo "<pre>";  print_r($addRecord); die;
            DepositTransaction::create($addRecord);
            // sleep(20);
             // Broadcast the event Notification code START
                $data = [
                    'type' => 'Deposit',
                    'transaction_id' => $frtransaction,
                    'amount' => $request->amount,
                    'Currency' => $request->Currency,
                    'status' => 'pending',
                    'msg' => 'New Deposit Transaction Created!',
                ];
                event(new DepositCreated($data));   
                // Broadcast the event Notification code END
                // Insert data in Notification table Code START
                $merchant=Merchant::where('merchant_code', $request->merchant_code)->first();
                $addNotificationRecord = [
                    'notifiable_type' => 'Deposit',
                    'agent_id' => $merchant->agent_id,
                    'merchant_id' => $merchant->id,
                    'data' => json_encode($data,true),
                    'msg' => 'New Deposit Transaction Created!',
                ];
                TransactionNotification::create($addNotificationRecord);
                // Insert data in Notification table Code END

            return redirect(url('r2pPaymentPage/'.base64_encode($frtransaction)));
        }else{
            return back()->with('error', 'Payment link not found.');
        }

    }

    public function paymentPage(Request $request, $frtransaction)
    {
        $RefID = base64_decode($frtransaction);
        $paymentDetail = DepositTransaction::where('systemgenerated_TransId', $RefID)->first();
          
            $data = [
                'merchant_code' => $paymentDetail->merchant_code,
                'referenceId' => $paymentDetail->reference_id,
                'transaction_id' => $paymentDetail->systemgenerated_TransId,
                'amount' => $paymentDetail->amount,
                'Currency' => $paymentDetail->Currency,
                'customer_name' => $paymentDetail->customer_name,
                'bank_account_name' => $paymentDetail->bank_account_name,
                'bank_code' => $paymentDetail->bank_code,
                'bank_account_number' => $paymentDetail->bank_account_number,
                'receipt_url' => $paymentDetail->receipt_url,
                'payment_status' => $paymentDetail->payment_status,
                'created_at' => $paymentDetail->created_at,
            ];
                // echo "<pre>";  print_r($data); die;
        return view('payment-form.r2p.paymentPage', compact('data'));
    }

    public function paymentProcessingPage(Request $request, $frtransaction)
    {
        $RefID = base64_decode($frtransaction);
        $paymentDetail = DepositTransaction::where('systemgenerated_TransId', $RefID)->first();
            $data = [
                'merchant_code' => $paymentDetail->merchant_code,
                'referenceId' => $paymentDetail->reference_id,
                'transaction_id' => $paymentDetail->systemgenerated_TransId,
                'amount' => $paymentDetail->amount,
                'Currency' => $paymentDetail->Currency,
                'customer_name' => $paymentDetail->customer_name,
                'bank_account_name' => $paymentDetail->bank_account_name,
                'bank_code' => $paymentDetail->bank_code,
                'bank_account_number' => $paymentDetail->bank_account_number,
                'receipt_url' => $paymentDetail->receipt_url,
                'payment_status' => $paymentDetail->payment_status,
                'created_at' => $paymentDetail->created_at,
            ];
            // echo "<pre>";  print_r($data); die;
        return view('payment-form.r2p.paymentProcessingPage', compact('data'));
    }

    public function payinResponse(Request $request, $frtransaction)
    {
        $transId = base64_decode($frtransaction);
        $paymentDetail = DepositTransaction::where('systemgenerated_TransId', $transId)->first();

        $status = $paymentDetail->payment_status === 'pending' ? 'processing' : $paymentDetail->payment_status;

        DepositTransaction::where('systemgenerated_TransId', $transId)->update([
            'payment_status' => $status,
        ]);

        $callbackUrl = $paymentDetail->callback_url;
        $postData = [
            'merchant_code' => $paymentDetail->merchant_code,
            'referenceId' => $paymentDetail->reference_id,
            'transaction_id' => $paymentDetail->systemgenerated_TransId,
            'amount' => $paymentDetail->amount,
            'Currency' => $paymentDetail->Currency,
            'customer_name' => $paymentDetail->customer_name,
            'payment_status' =>$status,
            'created_at' => $paymentDetail->created_at,
        ];

            // Broadcast the event Notification code START
                $data = [
                    'type' => 'Transaction Updated',
                    'transaction_id' => $paymentDetail->systemgenerated_TransId,
                    'amount' => $paymentDetail->amount,
                    'Currency' => $paymentDetail->Currency,
                    'status' =>$status,
                    'msg' => 'Transaction Status Updated!',
                ];
                event(new DepositCreated($data));   
                // Broadcast the event Notification code END
                // Insert data in Notification table Code START
                $addNotificationRecord = [
                    'notifiable_type' => 'Transaction Updated',
                    'agent_id' => $paymentDetail->agent_id,
                    'merchant_id' => $paymentDetail->merchant_id,
                    'data' => json_encode($data,true),
                    'msg' => 'Transaction Status Updated!',
                ];
                TransactionNotification::create($addNotificationRecord);
            // Insert data in Notification table Code END

        return view('payment.payment_status', compact('request', 'postData', 'callbackUrl'));
    }

    public function r2pPayinCallbackURL(Request $request)
    {
        $data = $request->all();
        // echo "Transaction Information as follows" . '<br/>' .
        //     "Merchant : " . $data['merchant_code'] . '<br/>' .
        //     "ReferenceId : " . $data['referenceId'] . '<br/>' .
        //     "TransactionId : " . $data['transaction_id'] . '<br/>' .
        //     "Type : Deposit" .'<br/>' .
        //     "Currency : " . $data['Currency'] . '<br/>' .
        //     "Amount : " . $data['amount'] . '<br/>' .
        //     "customer_name : " . $data['customer_name'] . '<br/>' .
        //     "Datetime : " . $data['created_at'] . '<br/>' .
        //     "Status : " . $data['payment_status'];
        return view('payment-form.r2p.deposit-response-page', compact('data'));
    }

    public function r2pDepositNotifiication(Request $request)
    {
        // echo "<pre>";  print_r($request->all());
        // $results = '{
        //   "type": "DEPOSIT",
        //   "status": "SUCCESS",
        //   "status_des": "",
        //   "amount": 0,
        //   "txn_ref_id": "",
        //   "txn_ref_order_id": "",
        //   "txn_ref_bank_code": "",
        //   "txn_ref_bank_acc_no": "",
        //   "txn_ref_bank_acc_name": "",
        //   "txn_ref_user_id": "",
        //   "txn_ref1": "",
        //   "txn_ref2": "",
        //   "txn_timestamp": "1970-01-01T00:00:00",
        //   "stm_timestamp": "1970-01-01T00:00:00",
        //   "stm_bank_code": "",
        //   "stm_bank_acc_no": "",
        //   "stm_bank_acc_name": "",
        //   "stm_desc": "",
        //   "stm_ref_id": ""
        // }';
        $data = $request->json()->all(); // Get JSON data from request
        if (!empty($data)) {
            //  $orderStatus = match ($data['status'] ?? '') {
            //     'SUCCESS' => 'success',
            //     'AUTO_SUCCESS' => 'success',
            //     'PROCESSING' => 'processing',
            //     'Failed' => 'failed',
            //     default => 'not confirm',
            // };
            $RefID = $data['txn_ref_order_id'];
            sleep(30);
            $updateData = [
                // 'payment_status' => $orderStatus ?? 'success',
                'payment_status' => 'success',
                'response_data' => json_encode($data),
            ];
            // echo "<pre>";  print_r($updateData); die;
            DepositTransaction::where('systemgenerated_TransId', $RefID)->update($updateData);
            echo "Transaction updated successfully!";
            //Call webhook API START
            $paymentDetail = DepositTransaction::where('systemgenerated_TransId', $RefID)->first();
            
            // Broadcast the event Notification code START
                $data = [
                    'type' => 'Callback Transaction Status',
                    'transaction_id' => $paymentDetail->systemgenerated_TransId,
                    'amount' => $paymentDetail->amount,
                    'Currency' => $paymentDetail->Currency,
                    'status' => $paymentDetail->payment_status,
                    'msg' => 'Callback Transaction Status Updated!',
                ];
                event(new DepositCreated($data));   
                // Broadcast the event Notification code END
                // Insert data in Notification table Code START
                $addNotificationRecord = [
                    'notifiable_type' => 'Callback Transaction Status',
                    'agent_id' => $paymentDetail->agent_id,
                    'merchant_id' => $paymentDetail->merchant_id,
                    'data' => json_encode($data,true),
                    'msg' => 'Callback Transaction Status Updated!',
                ];
                TransactionNotification::create($addNotificationRecord);
            // Insert data in Notification table Code END

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
            try {
                if ($paymentDetail->callback_url != null) {
                    $response = Http::post($paymentDetail->callback_url, $postData);
                    echo $response->body(); die;
                }
            } catch (\Exception $e) {
                return response()->json(['error' => 'Failed to call webhook','message' => $e->getMessage()], 500);
            }
             //Call webhook API START

        }else{ 
            return response()->json(['error' => 'Data Not Found or Invalid Request!'], 400);
        }

    }

    public function payintest(Request $request)
    {
        return view('payment-form.r2p.payintest');
    }

    public static function generateUniqueCode()
    {
        $mytime = Carbon::now();
        $currentDateTime = str_replace(' ', '', $mytime->parse($mytime->toDateTimeString())->format('Ymd His'));
        $systemgenerated_TransId = $currentDateTime.random_int(1000, 9999);
        return 'TR'.$systemgenerated_TransId;
    }

    public function sendDepositNotification($id)
    {
        $paymentDetail = DepositTransaction::where('id', base64_decode($id))->first();
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
   
            // Broadcast the event Notification code START
                $data = [
                    'type' => 'Notification send Successfully!',
                    'transaction_id' => $paymentDetail->systemgenerated_TransId,
                    'amount' => $paymentDetail->amount,
                    'Currency' => $paymentDetail->Currency,
                    'status' => $paymentDetail->payment_status,
                    'msg' => 'One Transaction notified!',
                ];
                event(new DepositCreated($data));   
                // Broadcast the event Notification code END
                // Insert data in Notification table Code START
                $addNotificationRecord = [
                    'notifiable_type' => 'Notification send Successfully!',
                    'agent_id' => $paymentDetail->agent_id,
                    'merchant_id' => $paymentDetail->merchant_id,
                    'data' => json_encode($data,true),
                    'msg' => 'One Transaction notified!',
                ];
                TransactionNotification::create($addNotificationRecord);
            // Insert data in Notification table Code END

        return view('payment.depositNotification', compact('postData', 'callbackUrl'));
    }

    // Resend withdrawal callback to the merchant (same payload keys as the deposit callback)
    public function sendWithdrawNotification($id)
    {
        $paymentDetail = WithdrawTransaction::where('id', base64_decode($id))->first();
        abort_if(!$paymentDetail, 404, 'Transaction not found');
        abort_if(empty($paymentDetail->callback_url), 422, 'No callback URL saved for this transaction');

        $callbackUrl = $paymentDetail->callback_url;
        $postData = [
            'merchant_code' => $paymentDetail->merchant_code,
            'referenceId' => $paymentDetail->reference_id,
            'transaction_id' => $paymentDetail->systemgenerated_TransId,
            'amount' => $paymentDetail->total,
            'Currency' => $paymentDetail->Currency,
            'customer_name' => $paymentDetail->customer_name,
            'payment_status' => $paymentDetail->status,
            'created_at' => $paymentDetail->created_at,
        ];

            // Broadcast the event Notification code START
                $data = [
                    'type' => 'Notification send Successfully!',
                    'transaction_id' => $paymentDetail->systemgenerated_TransId,
                    'amount' => $paymentDetail->total,
                    'Currency' => $paymentDetail->Currency,
                    'status' => $paymentDetail->status,
                    'msg' => 'One Withdraw Transaction notified!',
                ];
                event(new DepositCreated($data));
                // Broadcast the event Notification code END
                // Insert data in Notification table Code START
                TransactionNotification::create([
                    'notifiable_type' => 'Notification send Successfully!',
                    'agent_id' => $paymentDetail->agent_id,
                    'merchant_id' => $paymentDetail->merchant_id,
                    'data' => json_encode($data, true),
                    'msg' => 'One Withdraw Transaction notified!',
                ]);
            // Insert data in Notification table Code END

        return view('payment.depositNotification', compact('postData', 'callbackUrl'));
    }

    public static function getGatewayParameters($merchant_code, $channel_id)
    {
        // 1. Validate Merchant
        $merchantData=Merchant::where('merchant_code', $merchant_code)->first();
        if (empty($merchantData)) {
            return 'Invalid Merchant!'; die;
        }
        // 2. Check Channel Status
        if ($merchantData->status == 0) {
            return 'Merchant is Disabled!'; die;
        }

        // 3. Validate Channel
        $channel = GatewayChannel::with('gatewayAccount', 'parameters')
            ->where('id', $channel_id)
            ->first();

        if (!$channel) {
            return 'Invalid Channel!'; die;
        }

        // 4. Check Channel Status
        if ($channel->status == 0) {
            return 'Channel is Disabled!'; die;
        }

        // 5. Check Gateway Account Status
        $gatewayAccount = $channel->gatewayAccount;
        if (!$gatewayAccount || $gatewayAccount->status == 0) {
            return 'Gateway is Disabled!'; die; 
            die;
        }

        // 6. Check Gateway Account is linked with Merchant
        $config = GatewayConfigurationMerchant::where('gateway_account_id', $gatewayAccount->id)
            ->where('merchant_id', $merchantData->id)
            ->first();

        if (!$config) {
            return 'Gateway not configured for this Merchant!'; die;
        }

        if ($config->status == 0) {
            return 'Gateway configuration is Disabled for this Merchant!'; die;
        }

        // 7. Build Parameters Array
        $parameters = $channel->parameters->pluck('parameter_value', 'parameter_name')->toArray();
        if (!$parameters) {
            return 'Parameter not set!'; die;
        }
        // 8. Final Data
        $data = [
            'merchantdata' => $merchantData->only(['id', 'merchant_code', 'merchant_name', 'agent_id']),
            'gateway_account' => $gatewayAccount->only(['id', 'gateway_name', 'payment_method', 'website_url']),
            'channel' => $channel->only(['id', 'channel_name', 'channel_desc']),
            'parameters' => $parameters,
        ];
        return $data;
    }

}
