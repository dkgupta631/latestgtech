<?php
$referenceNo = "GZTRN" . time() . (function ($length = 3) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
})();
// $apiUrl = "http://127.0.0.1:8000/api/qp/deposit/";
$apiUrl = "https://sprint.zaffranpay.com/api/qp/deposit/";
$data = [
    'merchant_code' => $_GET['merchant_code'],
    'channel_id' => '8',        // for local 7 , for live 8 
    'referenceId' => $referenceNo, 
    // 'callback_url' => 'http://127.0.0.1:8000/api/qp/depositResponse',
    'callback_url' => 'https://sprint.zaffranpay.com/api/qp/depositResponse',
    'Currency' =>  $_GET['Currency'], 
    'amount' => $_GET['amount'],    
    'card_holder_name' => $_GET['card_holder_name'],
    'card_number' => $_GET['card_number'],
    'expiryMonth' => $_GET['expiryMonth'],
    'expiryYear' => $_GET['expiryYear'],
    'cvv' => $_GET['cvv']
];
$fullUrl = $apiUrl . '?' . http_build_query($data);
?>
<script>
    window.location.href = '<?php echo $fullUrl; ?>';     
</script>

