@php
    $status = strtolower($postData['payment_status'] ?? '');
    $state = match ($status) {
        'success', 'approved' => [
            'key' => 'success', 'label' => 'Payment Successful',
            'message' => 'Your payment has been received successfully.',
        ],
        'pending' => [
            'key' => 'pending', 'label' => 'Payment Pending',
            'message' => 'Your transaction has been sent to the bank and is waiting for confirmation.',
        ],
        'processing' => [
            'key' => 'processing', 'label' => 'Payment Processing',
            'message' => 'Your transaction has been sent to the bank and is being processed.',
        ],
        default => [
            'key' => 'failed', 'label' => 'Payment Failed',
            'message' => 'Something went wrong and your payment could not be completed.',
        ],
    };
    $symbols = ['USD' => '$', 'EUR' => '€', 'KHR' => '៛', 'THB' => '฿', 'INR' => '₹'];
    $currency = $postData['Currency'] ?? '';
    $amount = is_numeric($postData['amount'] ?? null) ? number_format((float) $postData['amount'], 2) : ($postData['amount'] ?? '');
    $redirectSeconds = 5;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $state['label'] }}</title>
    <style>
        :root {
            --brand: #64b0f2;
            --brand-dark: #2f86d6;
            --text: #1f2d3d;
            --muted: #6b7a8c;
            --line: #e6edf5;
            --success: #1e9e5a;
            --success-bg: #e8f7ef;
            --pending: #e69500;
            --pending-bg: #fff5e0;
            --processing: #2f86d6;
            --processing-bg: #e8f2fc;
            --failed: #e53935;
            --failed-bg: #fdecec;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            background: var(--brand);
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
            box-shadow: 0 12px 40px rgba(16, 56, 99, .18);
            overflow: hidden;
            text-align: center;
        }
        .card-top { height: 6px; background: var(--accent); }
        .body { padding: 34px 28px 26px; }
        .icon {
            width: 76px; height: 76px; margin: 0 auto 16px;
            border-radius: 50%;
            background: var(--accent-bg);
            display: flex; align-items: center; justify-content: center;
        }
        .icon svg { width: 38px; height: 38px; stroke: var(--accent); }
        h1 { margin: 0 0 6px; font-size: 22px; font-weight: 800; color: var(--accent); }
        .msg { margin: 0 auto; max-width: 320px; font-size: 14px; line-height: 1.5; color: var(--muted); }
        .amount { margin: 20px 0 0; font-size: 32px; font-weight: 800; letter-spacing: -.5px; }
        .amount small { font-size: 15px; font-weight: 700; color: var(--muted); margin-left: 4px; }
        .txn { margin: 6px 0 0; font-size: 13px; color: var(--muted); word-break: break-all; }
        .txn b { color: var(--text); }
        .redirect { margin: 24px 0 0; padding: 14px; background: #f6f9fc; border: 1px solid var(--line); border-radius: 10px; font-size: 14px; color: var(--muted); }
        .redirect b { color: var(--brand-dark); }
        .bar { height: 4px; margin-top: 10px; border-radius: 4px; background: var(--line); overflow: hidden; }
        .bar span { display: block; height: 100%; width: 100%; background: var(--brand-dark); transform-origin: left; animation: shrink {{ $redirectSeconds }}s linear forwards; }
        @keyframes shrink { to { transform: scaleX(0); } }
        .btn {
            width: 100%; margin-top: 18px; padding: 12px; border: 0; border-radius: 8px;
            background: var(--brand); color: #fff; font-size: 15px; font-weight: 700; cursor: pointer;
        }
        .btn:hover { filter: brightness(.96); }
        .btn:disabled { opacity: .7; cursor: wait; }
        .foot {
            display: flex; align-items: center; justify-content: center; gap: 6px;
            padding: 14px; background: #f6f9fc; color: var(--muted); font-size: 12px; border-top: 1px solid var(--line);
        }
        .foot svg { width: 14px; height: 14px; stroke: var(--muted); }
        .state-success { --accent: var(--success); --accent-bg: var(--success-bg); }
        .state-pending { --accent: var(--pending); --accent-bg: var(--pending-bg); }
        .state-processing { --accent: var(--processing); --accent-bg: var(--processing-bg); }
        .state-failed { --accent: var(--failed); --accent-bg: var(--failed-bg); }
    </style>
</head>
<body>
    <main class="card state-{{ $state['key'] }}">
        <div class="card-top"></div>
        <div class="body">
            <div class="icon" aria-hidden="true">
                @if ($state['key'] === 'success')
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                @elseif ($state['key'] === 'failed')
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="3" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                @else
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 14"/></svg>
                @endif
            </div>
            <h1>{{ $state['label'] }}</h1>
            <p class="msg">{{ $state['message'] }}</p>

            @if ($amount !== '')
                <p class="amount">{{ $symbols[$currency] ?? '' }}{{ $amount }}<small>{{ $currency }}</small></p>
            @endif
            @if (!empty($postData['transaction_id']))
                <p class="txn">Transaction ID: <b>{{ $postData['transaction_id'] }}</b></p>
            @endif

            <div class="redirect">
                Returning to merchant in <b id="countdown">{{ $redirectSeconds }}</b> seconds...
                <div class="bar"><span></span></div>
            </div>

            {{-- Callback to merchant: same fields as before, posted automatically --}}
            <form name="member_signup" action="{{ $callbackUrl }}" method="post">
                @foreach ($postData as $key => $item)
                    <input type="hidden" name="{{ $key }}" value="{{ $item }}">
                @endforeach
                <button type="submit" class="btn" id="returnBtn">Return to Merchant</button>
            </form>
        </div>
        <footer class="foot">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
            Secured payment &middot; Please do not close this window
        </footer>
    </main>

    <script>
        (function () {
            var form = document.forms['member_signup'];
            var btn = document.getElementById('returnBtn');
            var counter = document.getElementById('countdown');
            var left = {{ $redirectSeconds }};
            var sent = false;

            function submitOnce() {
                if (sent) return;
                sent = true;
                btn.disabled = true;
                btn.textContent = 'Redirecting...';
                form.submit();
            }

            form.addEventListener('submit', function (e) { e.preventDefault(); submitOnce(); });

            var timer = setInterval(function () {
                left -= 1;
                counter.textContent = Math.max(left, 0);
                if (left <= 0) { clearInterval(timer); submitOnce(); }
            }, 1000);
        })();
    </script>
</body>
</html>
