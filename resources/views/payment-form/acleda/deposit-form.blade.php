@php
    $referenceNo = 'GZTRN' . time() . \Illuminate\Support\Str::random(3);
    $amount = old('amount', '2');
    $currency = old('Currency', 'USD');
    $channel = old('channel', 'khqr');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Secure Checkout</title>
    <style>
        :root {
            --brand: #1e3d6e;
            --brand-dark: #1e3d6e;
            --text: #1f2d3d;
            --muted: #6b7a8c;
            --line: #ebe8e1;
            --field: #faf9f6;
            --danger: #e53935;
            --danger-bg: #fdecec;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            background: #eef1f6;
            font-family: "Nunito Sans", "Helvetica Neue", Helvetica, Arial, sans-serif;
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
        }
        .card {
            width: 100%;
            max-width: 460px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(30, 61, 110, .12);
            overflow: hidden;
        }
        .card-top { height: 6px; background: linear-gradient(90deg, #1e3d6e 0%, #1e3d6e 70%, #d6a630 70%, #f1cf6b 100%); }
        .head { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 22px 28px 0; }
        .head h1 { margin: 0; font-size: 20px; font-weight: 800; }
        .head p { margin: 2px 0 0; font-size: 13px; color: var(--muted); }
        .secure-badge {
            display: inline-flex; align-items: center; gap: 5px; flex-shrink: 0;
            padding: 5px 10px; border-radius: 999px; background: #e8f7ef; color: #1e9e5a; font-size: 12px; font-weight: 700;
        }
        .secure-badge svg { width: 13px; height: 13px; stroke: #1e9e5a; }
        .total {
            margin: 20px 28px 0; padding: 16px 18px; border-radius: 10px;
            background: linear-gradient(135deg, var(--brand-dark), var(--brand)); color: #fff;
            display: flex; align-items: center; justify-content: space-between;
        }
        .total > span { font-size: 13px; opacity: .9; }
        .total b { font-size: 26px; font-weight: 800; letter-spacing: -.5px; white-space: nowrap; }
        form { padding: 8px 28px 26px; }
        .field { margin-top: 18px; }
        .lbl { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .4px; }
        .lbl .req { color: var(--danger); }
        .input, select.input {
            width: 100%; height: 48px; padding: 0 14px;
            border: 1.5px solid var(--line); border-radius: 8px; background: #fff;
            font: inherit; font-size: 15px; color: var(--text); outline: none; transition: border-color .15s, box-shadow .15s;
        }
        .input:focus { border-color: var(--brand-dark); box-shadow: 0 0 0 3px rgba(30, 61, 110, .15); }
        .input[readonly] { background: var(--field); color: var(--muted); }
        .row2 { display: grid; grid-template-columns: 1fr 1.4fr; gap: 12px; }
        .methods { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .method { position: relative; cursor: pointer; }
        .method input { position: absolute; opacity: 0; pointer-events: none; }
        .method .box {
            display: flex; align-items: center; gap: 10px; height: 56px; padding: 0 14px;
            border: 1.5px solid var(--line); border-radius: 8px; transition: border-color .15s, background .15s;
        }
        .method .box svg { width: 22px; height: 22px; stroke: var(--muted); flex-shrink: 0; }
        .method .box b { display: block; font-size: 14px; }
        .method .box small { display: block; font-size: 11px; color: var(--muted); }
        .method input:checked + .box { border-color: var(--brand-dark); background: #e8eef8; }
        .method input:checked + .box svg { stroke: var(--brand-dark); }
        .method input:focus-visible + .box { box-shadow: 0 0 0 3px rgba(30, 61, 110, .25); }
        .error { margin-top: 6px; font-size: 13px; color: var(--danger); }
        .alert { margin: 18px 28px 0; padding: 12px 14px; border-radius: 8px; background: var(--danger-bg); color: var(--danger); font-size: 14px; }
        .btn {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            width: 100%; margin-top: 24px; height: 50px; border: 0; border-radius: 8px;
            background: linear-gradient(135deg, #2a5296, #1e3d6e); color: #fff; font-size: 16px; font-weight: 800; cursor: pointer;
        }
        .btn:hover { filter: brightness(.96); }
        .btn:disabled { cursor: wait; opacity: .85; }
        .btn svg { width: 18px; height: 18px; stroke: #fff; }
        .spin { width: 18px; height: 18px; border: 2.5px solid rgba(255,255,255,.4); border-top-color: #fff; border-radius: 50%; animation: spin .8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .hint { margin: 10px 0 0; text-align: center; font-size: 12px; color: var(--muted); }
        .foot {
            display: flex; align-items: center; justify-content: center; gap: 6px; flex-wrap: wrap;
            padding: 14px; background: #faf9f6; color: var(--muted); font-size: 12px; border-top: 1px solid var(--line); text-align: center;
        }
        .foot svg { width: 14px; height: 14px; stroke: var(--muted); }
        @media (max-width: 420px) {
            .head, form { padding-left: 18px; padding-right: 18px; }
            .total, .alert { margin-left: 18px; margin-right: 18px; }
            .row2, .methods { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <main class="card">
        <div class="card-top"></div>

        <header class="head">
            <div>
                <h1>Secure Checkout</h1>
                <p>Deposit in USD or KHR</p>
            </div>
            <span class="secure-badge">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                Secure
            </span>
        </header>

        <div class="total">
            <span>Amount to pay</span>
            <b><span id="symbolLabel">$</span><span id="amountLabel">{{ $amount }}</span> <span id="currencyLabel" style="font-size:14px;opacity:.9">{{ $currency }}</span></b>
        </div>

        @error('acleda')
            <div class="alert" role="alert">{{ $message }}</div>
        @enderror

        <form action="{{ route('apiroute.AcledaDepositApi') }}" method="GET" id="paymentForm" novalidate>
            <input type="hidden" name="merchant_code" value="khbiz001">
            {{-- channel_id is optional: the API picks the active ACLEDA channel (local 7, live 8) --}}
            <input type="hidden" name="callback_url" value="{{ route('apiroute.AcledaDepositResponse') }}">

            <div class="field">
                <label class="lbl" for="referenceId">Reference ID <span class="req">*</span></label>
                <input class="input" id="referenceId" name="referenceId" value="{{ old('referenceId', $referenceNo) }}" maxlength="64" readonly>
                @error('referenceId')<div class="error">{{ $message }}</div>@enderror
            </div>

            <div class="field row2">
                <div>
                    <label class="lbl" for="currencySelect">Currency <span class="req">*</span></label>
                    <select class="input" name="Currency" id="currencySelect" required>
                        @foreach (['USD', 'KHR'] as $c)
                            <option value="{{ $c }}" @selected($currency === $c)>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="lbl" for="amountInput">Amount <span class="req">*</span></label>
                    <input class="input" name="amount" id="amountInput" inputmode="decimal" autocomplete="off" placeholder="0.00" value="{{ $amount }}" required>
                    @error('amount')<div class="error">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="error" id="amountError" hidden>Please enter a valid amount greater than 0.</div>

            <div class="field">
                <span class="lbl">Payment Method <span class="req">*</span></span>
                <div class="methods">
                    <label class="method">
                        <input type="radio" name="channel" value="khqr" @checked($channel === 'khqr')>
                        <span class="box">
                            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h3v3h-3zM20 14v.01M14 20h.01M17 20h4v-3"/></svg>
                            <span><b>KHQR</b><small>Scan with bank app</small></span>
                        </span>
                    </label>
                    <label class="method">
                        <input type="radio" name="channel" value="card" @checked($channel === 'card')>
                        <span class="box">
                            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/><line x1="6" y1="15" x2="10" y2="15"/></svg>
                            <span><b>Card</b><small>Visa / Mastercard</small></span>
                        </span>
                    </label>
                </div>
                @error('channel')<div class="error">{{ $message }}</div>@enderror
            </div>

            <button type="submit" class="btn" id="submitBtn">
                <span>Pay Now</span>
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </button>
            <p class="hint" id="hint">You will be redirected to the bank's secure payment page.</p>
        </form>

        <footer class="foot">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l8 4v6c0 5-3.5 9-8 10-4.5-1-8-5-8-10V6z"/></svg>
            Encrypted connection &middot; Your payment details are handled by the bank
        </footer>
    </main>

    <script>
        (function () {
            var symbols = { USD: '$', KHR: '៛' };
            var form = document.getElementById('paymentForm');
            var amount = document.getElementById('amountInput');
            var currency = document.getElementById('currencySelect');
            var btn = document.getElementById('submitBtn');
            var amountError = document.getElementById('amountError');

            function refresh() {
                document.getElementById('amountLabel').textContent = amount.value || '0';
                document.getElementById('symbolLabel').textContent = symbols[currency.value] || '';
                document.getElementById('currencyLabel').textContent = currency.value;
            }

            amount.addEventListener('input', function () {
                // allow only numbers with one decimal point
                var clean = amount.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');
                if (clean !== amount.value) amount.value = clean;
                amountError.hidden = true;
                refresh();
            });
            currency.addEventListener('change', refresh);

            form.addEventListener('submit', function (e) {
                if (!(parseFloat(amount.value) > 0)) {
                    e.preventDefault();
                    amountError.hidden = false;
                    amount.focus();
                    return;
                }
                btn.disabled = true;
                btn.innerHTML = '<span class="spin"></span><span>Opening secure session...</span>';
                document.getElementById('hint').textContent = 'This can take up to 30 seconds. Please do not close this page.';
            });

            // re-enable the button if the user comes back with the browser back button
            window.addEventListener('pageshow', function (e) {
                if (e.persisted) location.reload();
            });

            refresh();
        })();
    </script>
</body>
</html>
