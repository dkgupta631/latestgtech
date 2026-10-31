@php
    $status = strtolower($postData['payment_status'] ?? '');
    $state = match ($status) {
        'success', 'approved' => ['key' => 'success', 'label' => 'Success'],
        'pending' => ['key' => 'pending', 'label' => 'Pending'],
        'processing' => ['key' => 'processing', 'label' => 'Processing'],
        default => ['key' => 'failed', 'label' => 'Failed'],
    };
    $symbols = ['USD' => '$', 'EUR' => '€', 'KHR' => '៛', 'THB' => '฿', 'INR' => '₹'];
    $currency = $postData['Currency'] ?? '';
    $amount = is_numeric($postData['amount'] ?? null) ? number_format((float) $postData['amount'], 2) : ($postData['amount'] ?? '');
    $callbackHost = parse_url($callbackUrl ?? '', PHP_URL_HOST) ?: ($callbackUrl ?? '');
    $redirectSeconds = 5;
    $rows = [
        'Transaction ID' => $postData['transaction_id'] ?? '',
        'Reference ID' => $postData['referenceId'] ?? '',
        'Merchant' => $postData['merchant_code'] ?? '',
        'Callback URL' => $callbackHost,
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Resend Callback</title>
    <style>
        :root {
            --brand: #00245a;
            --brand-dark: #00245a;
            --text: #1f2d3d;
            --muted: #6b7a8c;
            --line: #e3e6eb;
            --success: #1e9e5a; --success-bg: #e8f7ef;
            --pending: #b77400; --pending-bg: #fff5e0;
            --processing: #00245a; --processing-bg: #e8eef8;
            --failed: #e53935; --failed-bg: #fdecec;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; background: #f1f2f4;
            font-family: "Nunito Sans", "Helvetica Neue", Helvetica, Arial, sans-serif; color: var(--text);
            display: flex; align-items: center; justify-content: center; padding: 32px 16px;
        }
        .card { width: 100%; max-width: 460px; background: #fff; border-radius: 12px; box-shadow: 0 10px 30px rgba(0, 36, 90, .12); overflow: hidden; }
        .card-top { height: 6px; background: linear-gradient(90deg, #00245a 0%, #00245a 70%, #e31e24 70%, #e31e24 100%); }
        .body { padding: 32px 28px 24px; text-align: center; }
        .icon {
            position: relative; width: 76px; height: 76px; margin: 0 auto 16px; border-radius: 50%;
            background: #e8eef8; display: flex; align-items: center; justify-content: center;
        }
        .icon svg { width: 34px; height: 34px; stroke: var(--brand-dark); }
        .icon::after {
            content: ""; position: absolute; inset: -6px; border-radius: 50%;
            border: 3px solid transparent; border-top-color: var(--brand-dark); animation: spin 1s linear infinite;
        }
        .done .icon { background: var(--success-bg); }
        .done .icon svg { stroke: var(--success); }
        .done .icon::after { display: none; }
        @keyframes spin { to { transform: rotate(360deg); } }
        h1 { margin: 0 0 6px; font-size: 21px; font-weight: 800; }
        .msg { margin: 0 auto; max-width: 330px; font-size: 14px; line-height: 1.5; color: var(--muted); }
        .amount { margin: 20px 0 6px; font-size: 30px; font-weight: 800; letter-spacing: -.5px; }
        .amount small { font-size: 14px; font-weight: 700; color: var(--muted); margin-left: 4px; }
        .pill { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 800; letter-spacing: .4px; text-transform: uppercase; }
        .pill::before { content: ""; width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
        .s-success { color: var(--success); background: var(--success-bg); }
        .s-pending { color: var(--pending); background: var(--pending-bg); }
        .s-processing { color: var(--processing); background: var(--processing-bg); }
        .s-failed { color: var(--failed); background: var(--failed-bg); }
        .details { margin: 22px 0 0; padding: 4px 16px; background: #f7f8fa; border: 1px solid var(--line); border-radius: 10px; text-align: left; }
        .row { display: flex; justify-content: space-between; gap: 16px; padding: 10px 0; border-bottom: 1px solid var(--line); font-size: 13.5px; }
        .row:last-child { border-bottom: 0; }
        .row dt { color: var(--muted); flex-shrink: 0; }
        .row dd { margin: 0; font-weight: 700; text-align: right; word-break: break-all; }
        .countdown { margin: 18px 0 0; font-size: 13.5px; color: var(--muted); }
        .countdown b { color: var(--brand-dark); }
        .bar { height: 4px; margin-top: 8px; border-radius: 4px; background: var(--line); overflow: hidden; }
        .bar span { display: block; height: 100%; background: var(--brand-dark); transform-origin: left; animation: shrink {{ $redirectSeconds }}s linear forwards; }
        @keyframes shrink { from { transform: scaleX(1); } to { transform: scaleX(0); } }
        .btn {
            width: 100%; margin-top: 16px; height: 46px; border: 0; border-radius: 8px; cursor: pointer;
            background: linear-gradient(135deg, #0a3a82, #00245a); color: #fff; font-size: 15px; font-weight: 800;
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn:hover { filter: brightness(.96); }
        .btn:disabled { opacity: .7; cursor: wait; }
        .btn svg { width: 18px; height: 18px; stroke: #fff; }
        .foot { display: flex; align-items: center; justify-content: center; gap: 6px; padding: 14px; background: #f7f8fa; color: var(--muted); font-size: 12px; border-top: 1px solid var(--line); }
        .foot svg { width: 14px; height: 14px; stroke: var(--muted); }
        @media (prefers-reduced-motion: reduce) { .icon::after, .bar span { animation: none; } }
    </style>
</head>
<body>
    <main class="card" id="card">
        <div class="card-top"></div>
        <div style="text-align:center;padding:18px 20px 0"><img src="{{ URL::to('newassets/images/khbiz-logo.png') }}" alt="KHBiz" style="height:52px;width:auto"></div>
        <div class="body">
            <div class="icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4z"/></svg>
            </div>
            <h1 id="title">Resending Callback</h1>
            <p class="msg" id="msg">The latest transaction status will be posted to the merchant's callback URL.</p>

            @if ($amount !== '')
                <p class="amount">{{ $symbols[$currency] ?? '' }}{{ $amount }}<small>{{ $currency }}</small></p>
            @endif
            <span class="pill s-{{ $state['key'] }}">{{ $state['label'] }}</span>

            <dl class="details">
                @foreach ($rows as $label => $value)
                    @if ($value !== '' && $value !== null)
                        <div class="row"><dt>{{ $label }}</dt><dd>{{ $value }}</dd></div>
                    @endif
                @endforeach
            </dl>

            <div class="countdown" id="countdownBox">
                Sending to merchant in <b id="countdown">{{ $redirectSeconds }}</b> seconds...
                <div class="bar"><span></span></div>
            </div>

            {{-- Callback payload: same fields as before, posted to the merchant --}}
            <form name="member_signup" action="{{ $callbackUrl }}" method="post">
                @foreach ($postData as $key => $item)
                    <input type="hidden" name="{{ $key }}" value="{{ $item }}">
                @endforeach
                <button type="submit" class="btn" id="sendBtn">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4z"/></svg>
                    Send Now
                </button>
            </form>
        </div>
        <footer class="foot">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
            Merchant notification &middot; Transaction callback
        </footer>
    </main>

    <script>
        (function () {
            var form = document.forms['member_signup'];
            var btn = document.getElementById('sendBtn');
            var counter = document.getElementById('countdown');
            var left = {{ $redirectSeconds }};
            var sent = false;

            function sendOnce() {
                if (sent) return;
                sent = true;
                btn.disabled = true;
                btn.lastChild.textContent = ' Sending...';
                document.getElementById('countdownBox').style.display = 'none';
                document.getElementById('card').classList.add('done');
                document.getElementById('title').textContent = 'Sending Callback...';
                document.getElementById('msg').textContent = 'Posting the transaction status to the merchant. You will be taken to the merchant page.';
                form.submit();
            }

            form.addEventListener('submit', function (e) { e.preventDefault(); sendOnce(); });
            var timer = setInterval(function () {
                left -= 1;
                counter.textContent = Math.max(left, 0);
                if (left <= 0) { clearInterval(timer); sendOnce(); }
            }, 1000);
        })();
    </script>
</body>
</html>
