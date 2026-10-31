@php
    $symbols = ['USD' => '$', 'KHR' => '៛'];
    $currency = $formData['currencytype'] ?? '';
    $amount = is_numeric($formData['amount'] ?? null)
        ? number_format((float) $formData['amount'], $currency === 'KHR' ? 0 : 2)
        : ($formData['amount'] ?? '');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Redirecting to ACLEDA Bank</title>
    <style>
        :root {
            --brand: #00245a;
            --brand-dark: #00245a;
            --text: #1f2d3d;
            --muted: #6b7a8c;
            --line: #e3e6eb;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            background: #f1f2f4;
            font-family: "Nunito Sans", "Helvetica Neue", Helvetica, Arial, sans-serif;
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
        }
        .card {
            width: 100%;
            max-width: 440px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 36, 90, .12);
            overflow: hidden;
            text-align: center;
        }
        .card-top { height: 6px; background: linear-gradient(90deg, #00245a 0%, #00245a 70%, #e31e24 70%, #e31e24 100%); }
        .body { padding: 36px 28px 24px; }
        .spinner {
            width: 64px; height: 64px; margin: 0 auto 20px;
            border-radius: 50%;
            border: 5px solid #e8eef8;
            border-top-color: var(--brand-dark);
            animation: spin .9s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        h1 { margin: 0 0 6px; font-size: 21px; font-weight: 800; }
        .msg { margin: 0 auto; max-width: 320px; font-size: 14px; line-height: 1.5; color: var(--muted); }
        .summary { margin: 24px 0 0; padding: 4px 18px; background: #f7f8fa; border: 1px solid var(--line); border-radius: 10px; text-align: left; }
        .row { display: flex; justify-content: space-between; gap: 16px; padding: 10px 0; border-bottom: 1px solid var(--line); font-size: 14px; }
        .row:last-child { border-bottom: 0; }
        .row span:first-child { color: var(--muted); }
        .row span:last-child { font-weight: 700; text-align: right; word-break: break-all; }
        .row.total span:last-child { font-size: 18px; }
        .steps { display: flex; align-items: center; justify-content: center; gap: 8px; margin: 22px 0 0; font-size: 12px; color: var(--muted); }
        .steps b { color: var(--brand-dark); }
        .steps i { width: 24px; height: 2px; background: var(--line); display: inline-block; }
        .btn {
            display: none; width: 100%; margin-top: 20px; padding: 12px; border: 0; border-radius: 8px;
            background: linear-gradient(135deg, #0a3a82, #00245a); color: #fff; font-size: 15px; font-weight: 700; cursor: pointer;
        }
        .btn:hover { filter: brightness(.96); }
        .note { margin: 14px 0 0; font-size: 12px; color: var(--muted); }
        .foot {
            display: flex; align-items: center; justify-content: center; gap: 6px;
            padding: 14px; background: #f7f8fa; color: var(--muted); font-size: 12px; border-top: 1px solid var(--line);
        }
        .foot svg { width: 14px; height: 14px; stroke: var(--muted); }
    </style>
</head>
<body>
    <main class="card">
        <div class="card-top"></div>
        <div style="text-align:center;padding:18px 20px 0"><img src="{{ URL::to('newassets/images/khbiz-logo.png') }}" alt="KHBiz" style="height:52px;width:auto"></div>
        <div class="body">
            <div class="spinner" aria-hidden="true"></div>
            <h1>Redirecting to ACLEDA Bank</h1>
            <p class="msg">Please wait while we securely connect you to the ACLEDA payment page. Do not refresh or close this window.</p>

            <div class="summary">
                <div class="row total"><span>Amount</span><span>{{ $symbols[$currency] ?? '' }}{{ $amount }} {{ $currency }}</span></div>
                <div class="row"><span>Transaction ID</span><span>{{ $formData['invoiceid'] ?? '' }}</span></div>
                <div class="row"><span>Description</span><span>{{ $formData['description'] ?? '' }}</span></div>
                @if (!empty($formData['expirytime']))
                    <div class="row"><span>Session valid for</span><span>{{ $formData['expirytime'] }} min</span></div>
                @endif
            </div>

            <div class="steps"><span>Merchant</span><i></i><b>ACLEDA Bank</b><i></i><span>Result</span></div>

            <form id="_xpayForm" name="_xpayForm" action="{{ $paymentPage }}" method="post">
                @foreach ($formData as $key => $value)
                    <input type="hidden" id="{{ $key }}" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                {{-- shown if the automatic redirect did not happen (JS disabled or blocked) --}}
                <button type="submit" class="btn" id="continueBtn">Continue to payment</button>
                <noscript><style>#continueBtn { display: block; }</style></noscript>
            </form>
            <p class="note" id="slowNote" hidden>Taking longer than expected? Click the button above.</p>
        </div>
        <footer class="foot">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
            Secured connection &middot; Payment processed by ACLEDA Bank
        </footer>
    </main>

    <script>
        document.getElementById('_xpayForm').submit();
        setTimeout(function () {
            document.getElementById('continueBtn').style.display = 'block';
            document.getElementById('slowNote').hidden = false;
        }, 8000);
    </script>
</body>
</html>
