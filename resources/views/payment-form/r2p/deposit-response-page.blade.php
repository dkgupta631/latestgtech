@php
    $status = strtolower($data['payment_status'] ?? '');
    $state = match ($status) {
        'success', 'approved' => [
            'key' => 'success', 'label' => 'Payment Successful',
            'message' => 'Thank you! Your deposit has been received successfully.',
        ],
        'pending' => [
            'key' => 'pending', 'label' => 'Payment Pending',
            'message' => 'Your payment is waiting for confirmation from the bank. We will update you once it is confirmed.',
        ],
        'processing' => [
            'key' => 'processing', 'label' => 'Payment Processing',
            'message' => 'Your payment has been sent to the bank and is being processed.',
        ],
        default => [
            'key' => 'failed', 'label' => 'Payment Failed',
            'message' => 'Your payment could not be completed. Please try again or contact support with the Transaction ID below.',
        ],
    };
    $symbols = ['USD' => '$', 'EUR' => '€', 'KHR' => '៛', 'THB' => '฿', 'INR' => '₹'];
    $currency = $data['Currency'] ?? '';
    $amount = is_numeric($data['amount'] ?? null) ? number_format((float) $data['amount'], 2) : ($data['amount'] ?? '');
    try {
        $datetime = !empty($data['created_at']) ? \Carbon\Carbon::parse($data['created_at'])->format('d M Y, h:i A') : '';
    } catch (\Throwable $e) {
        $datetime = $data['created_at'];
    }
    $rows = [
        'Transaction ID' => $data['transaction_id'] ?? '',
        'Reference ID' => $data['referenceId'] ?? '',
        'Merchant' => $data['merchant_code'] ?? '',
        'Customer Name' => $data['customer_name'] ?? '',
        'Payment Type' => 'Deposit',
        'Currency' => $currency,
        'Date & Time' => $datetime,
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $state['label'] }}</title>
    <style>
        :root {
            --brand: #00245a;
            --brand-dark: #00245a;
            --text: #1f2d3d;
            --muted: #6b7a8c;
            --line: #e3e6eb;
            --success: #1e9e5a;
            --success-bg: #e8f7ef;
            --pending: #e69500;
            --pending-bg: #fff5e0;
            --processing: #00245a;
            --processing-bg: #e8eef8;
            --failed: #e53935;
            --failed-bg: #fdecec;
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
        .receipt {
            width: 100%;
            max-width: 480px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 36, 90, .12);
            overflow: hidden;
        }
        .receipt-top { height: 6px; background: linear-gradient(90deg, #00245a 0%, #00245a 70%, #e31e24 70%, #e31e24 100%); }
        .head { text-align: center; padding: 32px 28px 24px; }
        .icon {
            width: 76px; height: 76px; margin: 0 auto 16px;
            border-radius: 50%;
            background: var(--accent-bg);
            display: flex; align-items: center; justify-content: center;
        }
        .icon svg { width: 38px; height: 38px; stroke: var(--accent); }
        .status-label { margin: 0 0 6px; font-size: 22px; font-weight: 800; color: var(--accent); }
        .status-msg { margin: 0 auto; max-width: 340px; font-size: 14px; line-height: 1.5; color: var(--muted); }
        .amount { margin: 22px 0 0; font-size: 36px; font-weight: 800; letter-spacing: -.5px; }
        .amount small { font-size: 16px; font-weight: 700; color: var(--muted); margin-left: 4px; }
        .badge {
            display: inline-block; margin-top: 10px; padding: 4px 12px;
            border-radius: 999px; font-size: 12px; font-weight: 700; letter-spacing: .5px; text-transform: uppercase;
            color: var(--accent); background: var(--accent-bg);
        }
        .divider { position: relative; border-top: 2px dashed var(--line); margin: 0 20px; }
        .divider::before, .divider::after {
            content: ""; position: absolute; top: -12px; width: 22px; height: 22px; border-radius: 50%; background: var(--brand);
        }
        .divider::before { left: -32px; }
        .divider::after { right: -32px; }
        .details { padding: 18px 28px 8px; margin: 0; }
        .row { display: flex; justify-content: space-between; gap: 16px; padding: 10px 0; border-bottom: 1px solid var(--line); font-size: 14px; }
        .row:last-child { border-bottom: 0; }
        .row dt { color: var(--muted); flex-shrink: 0; }
        .row dd { margin: 0; font-weight: 700; text-align: right; word-break: break-all; }
        .copy { border: 0; background: none; cursor: pointer; padding: 0 0 0 6px; color: var(--brand-dark); font-size: 12px; font-weight: 700; }
        .actions { padding: 16px 28px 24px; display: flex; gap: 12px; }
        .btn {
            flex: 1; padding: 12px; border-radius: 8px; font-size: 15px; font-weight: 700; cursor: pointer; text-align: center;
            border: 2px solid var(--brand); background: #fff; color: var(--brand-dark);
        }
        .btn-primary { background: linear-gradient(135deg, #0a3a82, #00245a); color: #fff; }
        .btn:hover { filter: brightness(.96); }
        .foot {
            display: flex; align-items: center; justify-content: center; gap: 6px;
            padding: 14px; background: #f7f8fa; color: var(--muted); font-size: 12px; border-top: 1px solid var(--line);
        }
        .foot svg { width: 14px; height: 14px; stroke: var(--muted); }
        .state-success { --accent: var(--success); --accent-bg: var(--success-bg); }
        .state-pending { --accent: var(--pending); --accent-bg: var(--pending-bg); }
        .state-processing { --accent: var(--processing); --accent-bg: var(--processing-bg); }
        .state-failed { --accent: var(--failed); --accent-bg: var(--failed-bg); }
        @media print {
            body { background: #fff; padding: 0; }
            .receipt { box-shadow: none; }
            .actions, .copy, .divider::before, .divider::after { display: none; }
        }
    </style>
</head>
<body>
    <main class="receipt state-{{ $state['key'] }}">
        <div class="receipt-top"></div>
        <div style="text-align:center;padding:18px 20px 0"><img src="{{ URL::to('newassets/images/khbiz-logo.png') }}" alt="KHBiz" style="height:52px;width:auto"></div>

        <section class="head">
            <div class="icon" aria-hidden="true">
                @if ($state['key'] === 'success')
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                @elseif ($state['key'] === 'failed')
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="3" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                @else
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 14"/></svg>
                @endif
            </div>
            <h1 class="status-label">{{ $state['label'] }}</h1>
            <p class="status-msg">{{ $state['message'] }}</p>
            <p class="amount">{{ $symbols[$currency] ?? '' }}{{ $amount }}<small>{{ $currency }}</small></p>
            <span class="badge">{{ $state['key'] }}</span>
        </section>

        <div class="divider"></div>

        <dl class="details">
            @foreach ($rows as $label => $value)
                @if ($value !== '' && $value !== null)
                    <div class="row">
                        <dt>{{ $label }}</dt>
                        <dd>
                            <span>{{ $value }}</span>
                            @if ($label === 'Transaction ID')
                                <button type="button" class="copy" data-copy="{{ $value }}">Copy</button>
                            @endif
                        </dd>
                    </div>
                @endif
            @endforeach
        </dl>

        <div class="actions">
            <button type="button" class="btn" onclick="window.print()">Print Receipt</button>
        </div>

        <footer class="foot">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
            Secured payment &middot; Keep the Transaction ID for your reference
        </footer>
    </main>

    <script>
        document.querySelectorAll('.copy').forEach(function (btn) {
            btn.addEventListener('click', function () {
                navigator.clipboard && navigator.clipboard.writeText(btn.dataset.copy).then(function () {
                    btn.textContent = 'Copied';
                    setTimeout(function () { btn.textContent = 'Copy'; }, 1500);
                });
            });
        });
    </script>
</body>
</html>
