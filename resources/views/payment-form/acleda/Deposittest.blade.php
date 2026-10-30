{{--
    ACLEDA deposit test entry point.

    Usage: /acleda/DepositTest?merchant_code=khbiz001&Currency=USD&amount=100&channel=khqr or card

    Builds the deposit API request with a fresh reference ID and the sample
    merchant callback, then forwards the browser to the deposit API.
    channel_id is not sent: the API picks the active ACLEDA channel itself.
--}}
@php
    use Illuminate\Support\Str;

    $query = [
        'merchant_code' => request()->query('merchant_code'),
        'referenceId'   => 'GZTRN' . now()->timestamp . Str::random(3),
        'callback_url'  => route('apiroute.AcledaDepositResponse'),
        'Currency'      => strtoupper(request()->query('Currency', 'USD')),
        'amount'        => request()->query('amount'),
        'channel'       => request()->query('channel', 'khqr'),
    ];

    $redirectUrl = route('apiroute.AcledaDepositApi', array_filter($query, 'filled'));
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="0;url={{ $redirectUrl }}">
    <title>Redirecting...</title>
</head>
<body>
    <noscript>
        <a href="{{ $redirectUrl }}">Continue to payment</a>
    </noscript>
    <script>
        window.location.replace(@json($redirectUrl));
    </script>
</body>
</html>
