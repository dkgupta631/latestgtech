<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use App\Models\GatewayAccount;
use App\Models\GatewayChannel;
use App\Models\GatewayChannelParameter;
use App\Models\GatewayConfigurationMerchant;
use App\Models\DepositTransaction;
use App\Models\Merchant;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Session;

use App\Events\DepositCreated;
use App\Models\TransactionNotification;


use App\Services\QoreCardEncryptor;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class QorePaymentController extends Controller
{
    public function qoreDepositform(Request $request)
    {
        return view('payment-form.qore.deposit-form');
    }

   public function qoreDepositApifun(Request $request)
    {
        $validatedData = $request->validate([
            'referenceId'   => 'required',
            'Currency'      => 'required',
            'amount'        => 'required',
            'card_holder_name' => 'required',
            'card_number'   => 'required',
            'cvv'           => 'required',
        ]);

        // fetching gateway details
        $res = RichPayController::getGatewayParameters($request->merchant_code, $request->channel_id);
        if (in_array($res, [
            'Invalid Merchant!', 'Merchant is Disabled!', 'Invalid Channel!', 'Channel is Disabled!',
            'Gateway is Disabled!', 'Gateway not configured for this Merchant!',
            'Gateway configuration is Disabled for this Merchant!', 'Parameter not set!',
        ])) {
            echo "<pre>"; print_r($res); die;
        }

        
        $client_ip   = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
        $cleanAmount = str_replace(",", "", $request->amount);
        $frtransaction = RichPayController::generateUniqueCode();

        // Expiry parsing
        $expiration = $request->expiration;
        if (empty($expiration)) {
            $expiryMonth = $request->expiryMonth;
            $expiryYear  = $request->expiryYear;
        } else {
            [$expiryMonth, $expiryYear] = explode('/', $expiration);
        }
        $expiryMonth = str_pad(trim($expiryMonth), 2, '0', STR_PAD_LEFT);
        $expiryYear  = strlen(trim($expiryYear)) === 2 ? '20' . trim($expiryYear) : trim($expiryYear);

        // Encrypt card fields with the merchant's public key
        $encryptedCard = QoreCardEncryptor::encryptCardData(
            $request->card_number,
            $request->cvv,
            $expiryMonth,
            $expiryYear,
            $res['parameters']['merchant_public_key']
        );

        // Sanity check — API rejects the whole payment_method block if any of these are empty
        foreach ($encryptedCard as $key => $value) {
            if (empty($value)) {
                echo "<pre>"; print_r("Encryption failed for {$key}, aborting before API call."); die;
            }
        }

      
    
      

    

         $accessToken = $this->getAccessToken(
            $res['parameters']['token_url'],
            $res['parameters']['client_id'],
            $res['parameters']['client_secret']
        );

         $response = Http::withHeaders([
            'Content-Type' => 'application/json; charset=utf-8',
            'Accept' => 'application/json',
            'User-Agent' => 'PostmanTestClient/1.0',
            'Authorization' => 'Bearer '.$accessToken, // truncated for safety
        ])->post(rtrim($res['parameters']['api_url'], '/') . '/api/transactions/authorize', [
            'terminal_id'      => $res['parameters']['terminal_id'],
            'reference'        => $frtransaction,
            'description'      => 'Card purchase in ' . $request->Currency,
            'currency'         => $request->Currency,
            "amount" => $cleanAmount,
            "transaction_type" => "PURCHASE",
            "payment_method" => [
                "type" => "card",
                // "data" => [
                //     "encrypted_card_number" => "TZSFOYulf+itpLHp9RCVtIrJ/BBQr7sMvzEN9+NjoklArkLCy4O4pUzfWP0hD6QpuRDDjPmqQGnrlFo2H895kaDAeI4l6h36vxJ5+6pooMFC9TBtcI+20FORm0KlNaYT14wnxVwNKY6pXvh2b6h/awbX7QHvTOXUvJPYvkGlh6U7g/XzN9lrdvEmjozisNWORFOQzZVnJv+YTg8U+m2Z25lFJrut7ubADDA2TQTKVeRu5msl839gXFfQXgWiFK6n7Kv0f63fcJCXQsInPvpEZwM0c+8vYjeunOIkeTm4psjeLUrCl2IUySbUV/rKibpWgp+jPAKgPWFdWZV6MKrfuw==",
                //     "encrypted_cvv" => "fvAbNuKYBhICTxcZu9MQ6E6pWxcWicfKbTnQSvqcTSsO28ywLf6MwMyLNXKn3h+1+6dr0yBPT/gfKBafKvCkkSKAQvzfiPPOg5cgEqaHhnCH2QQQ1eIYSrj42aJnOj03JSYYlYifAnXBX4bFVnBj1XNfj65Ay5V+WdlfhJmYWKHs7F/vzTvKpU0dVDMKytEZBzDM3Sy7dTz9DW8lZVu5KPUSIkSehwuqGqe7mEEXDzaf1xZfHG8lH2lAB2zdQO+NmuZsG2T6pIGcWAYItWGuotSrSI0gsiD9ncSgoYUyYDUDX07jPYcBWsiGa+10CifzJzq6aNGJa6ksIdVjN0KtZw==",
                //     "encrypted_expiration_month" => "oZPkf4ueX8lxzkF/k9+2BkOgKhoMsSq8SV+VYwM5wBokOKAv5or0d7EW//8SbRDPgFbjBY7YUderc/tmYbiio1LYMxkaJq2ZXDdbqmEtcZoI/bbBIPJJAF/K4SH241Q+LCMpejugKjNvfgJ8OI8+Il85WEwXcZ/EdocGEqMgWbisV0L4+o/35xv+gf+tVfDYvKhJhhDFIy/wW7UrnTuH4NE0+DXHdoYuxEYqBiC7NcBpv5Zd8fc2q0ub0Og0N4NliLln0/ETxvmY9C/K3h2BwdTW9SYd3nwMAi63fb4RNI62KBeubR6unILXnRHCC+N1BkSEcFWXystIXZLtFSatgw==",
                //     "encrypted_expiration_year" => "aXWZItwHBIsJA16Qgyu7bT0CDyO3lTD/Wd0ZSJA9d2unNBc28NGQjn8SBy3vVR1TB7mZOvjuqvfmq21fO2NcpE/thMubZH+suu/wpXkarmTsIzWODs66nrIgUsejBafFHnyIY9a9/hyVGlT9B+9WvQUcizIoRMafxLV1CCzrG3tlEudI6aqqsfKPdv0N9neYNtFWImlfWduxzGb0wGXs6BaJmY7Yhd3X+5cYwqGyZwWLSGiPpeMgaZXNS1m8fnTtW2Dd0D4PtHOFpfmLYyuVwS2N8OahCGDmm7Z3GmyH1Wm5XurYLxc4Qd6QcZq7ORt5MzBoV8LhIYrPSa6hFvwy+A==",
                // ],
                "data" => $encryptedCard,
            ],
            "customer" => [
                "first_name" => $request->card_holder_name,
                "last_name" => $request->card_holder_name,
                'email'  => 'qurePayment@gmail.com',
                "phone" => "+85596861409",
                "address" => "poipet",
                "city" => "poipet",
                "country" => "KH",
                "postal_code" => "273154",
            ],
            "browser_info" => [
                "user_agent" => "Mozilla/5.0",
                "accept_header" => "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
                "java_enabled" => false,
                "color_depth" => 24,
                "screen_height" => 1080,
                "screen_width" => 1920,
                "time_zone_offset" => -120,
                "language" => "en-US",
            ],
            "metadata" => (object) [],
            "return_url" => url('qp/deposit/gatewayResponse'),
            // "return_url" => 'https://sprint.zaffranpay.com/qp/deposit/gatewayResponse',
        ]);
        $result = $response->json();

        // echo "<pre>"; print_r($result);

         // for Xprixo deposit charge START
        if(!empty($cleanAmount)){
            $percentage = $res['parameters']['percentage_charge'];     // Deposit Charge for RichPay
            $totalWidth = $cleanAmount;
            $mdr_fee_amount = ($percentage / 100) * $totalWidth;
            $net_amount= $totalWidth-$mdr_fee_amount;
        }
        // for Xprixo deposit charge END

        if ( isset($result)  &&  $result['result']['status'] == 'APPROVED' ) {
                //Insert data into DB
                $addRecord = [
                    'agent_id' => $res['merchantdata']['agent_id'],
                    'merchant_id' => $res['merchantdata']['id'],
                    'merchant_code' => $request->merchant_code,
                    'reference_id' => $request->referenceId,
                    'systemgenerated_TransId' => $frtransaction,
                    'gateway_TransId' => $result['result']['id'] ?? '',
                    'callback_url' => $request->callback_url,
                    'amount' => $cleanAmount,
                    'Currency' => $request->Currency,
                    'payment_channel' => $res['channel']['id'] ?? '',
                    'payment_method' => $res['gateway_account']['payment_method'] ?? 'QR Payment',
                    'request_data' => json_encode($res),
                    'gateway_name' => $res['gateway_account']['gateway_name'],
                    'customer_name' => $request->card_holder_name,
                    'payin_arr' => json_encode($result),
                    'receipt_url' => $result['result']['redirect_url'] ?? '',
                    'ip_address' => $client_ip,
                    'net_amount' => $net_amount ?? '',
                    'mdr_fee_amount' => $mdr_fee_amount ?? '',
                    'payment_status' => $result['result']['status'],
                ];
                DepositTransaction::create($addRecord);

                
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

                return redirect()->to(
                                        $result['result']['redirect_url'] . '?' . http_build_query([
                                            'frtransaction' => $frtransaction,                 // systemgenerated_TransId
                                            'status'    => $result['result']['status'],    // APPROVED/INVALID/DECLINED etc.
                                        ])
                                    );

        } else {
                $addRecord = [
                    'agent_id' => $res['merchantdata']['agent_id'],
                    'merchant_id' => $res['merchantdata']['id'],
                    'merchant_code' => $request->merchant_code,
                    'reference_id' => $request->referenceId,
                    'systemgenerated_TransId' => $frtransaction,
                    'callback_url' => $request->callback_url,
                    'amount' => $cleanAmount,
                    'Currency' => $request->Currency,
                    'payment_channel' => $res['channel']['id'] ?? '',
                    'payment_method' => $res['gateway_account']['payment_method'] ?? 'QR Payment',
                    'request_data' => json_encode($res),
                    'gateway_name' => $res['gateway_account']['gateway_name'],
                    'customer_name' => $request->card_holder_name,
                    'payin_arr' => json_encode($result),
                    'receipt_url' => $result['result']['errors'][0]['message'] ?? '',
                    'ip_address' => $client_ip,
                    'net_amount' => $net_amount ?? '',
                    'mdr_fee_amount' => $mdr_fee_amount ?? '',
                    'payment_status' => 'failed',
                ];
                DepositTransaction::create($addRecord);
                // echo "Unexpected Response"; echo "<pre>"; print_r($result['result']['errors']); die;
                return redirect()->to(
                                        $result['result']['redirect_url'] . '?' . http_build_query([
                                            'frtransaction' => $frtransaction,                 // systemgenerated_TransId
                                            'status'    => $result['result']['status'],    // APPROVED/INVALID/DECLINED etc.
                                        ])
                                    );

        }


      
    }

    protected function getAccessToken($token_url, $client_id, $client_secret): string
    {
        return Cache::remember('qore_access_token', 45, function () use ($token_url, $client_id, $client_secret) {
            $response = Http::asForm()->post($token_url, [
                'grant_type'    => 'client_credentials',
                'client_id'     => $client_id,
                'client_secret' => $client_secret,
            ]);

            return $response->json('access_token');
        });
    }

    public function qpDepositGatewayResponse(Request $request)
    {
            // $response = $request->all();
            $systemgenerated_TransId = $request->query('frtransaction');
            $orderstatus = strtoupper($request->query('status'));

            $json = [
                    'systemgenerated_TransId' => $systemgenerated_TransId,
                    'orderstatus' => $orderstatus,
                ];
          
        
            // $orderstatus = match ($response['status'] ?? null) {
            //     'Active' => 'success',
            //     'Pending' => 'pending',
            //     default => 'failed',
            // };

             // Map gateway status to your internal payment status
                switch ($orderstatus) {
                    case 'APPROVED':
                    case 'SUCCESS':
                    case 'ACTIVE':
                        $paymentStatus = 'success';
                        break;

                    case 'PENDING':
                        $paymentStatus = 'pending';
                        break;

                    case 'INVALID':
                    case 'DECLINED':
                    case 'FAILED':
                    case 'FAIL':
                    default:
                        $paymentStatus = 'failed';
                        break;
                }
     
                $updateData = [
                    'payment_status' => $paymentStatus,
                    'response_data' => json_encode($json)
                ];
                DepositTransaction::where('systemgenerated_TransId', $systemgenerated_TransId)->update($updateData);
                $paymentDetail = DepositTransaction::where('systemgenerated_TransId', $systemgenerated_TransId)->first();
                        // Broadcast the event Notification code START
                        $data = [
                            'type' => 'Transaction Updated',
                            'transaction_id' => $paymentDetail->systemgenerated_TransId,
                            'amount' => $paymentDetail->amount,
                            'Currency' => $paymentDetail->Currency,
                            'status' => $paymentDetail->payment_status,
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

     // COMMON PART START
    public function qoreDepositResponse(Request $request)
    {
        $data = $request->all();
        // echo "Transaction Information as follows" . '<br/>' .
        //     "Merchant_code : " . $data['merchant_code'] . '<br/>' .
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
    
}
