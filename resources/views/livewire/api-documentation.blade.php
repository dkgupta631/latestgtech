@php
    $base = url('/api');
    $common = [
        ['merchant_code', 'string', true, 'Your merchant code, e.g. testmerchant005'],
        ['channel_id', 'integer', true, 'Payment channel assigned to you by the admin'],
        ['referenceId', 'string', true, 'Your unique order / reference ID (max 64)'],
        ['callback_url', 'url', true, 'Where we POST the final transaction status'],
        ['Currency', 'string', true, 'ISO currency code, e.g. USD, THB, KHR'],
        ['amount', 'decimal', true, 'Amount to charge, e.g. 100.00'],
    ];
    $endpoints = [
        ['id' => 'bank', 'icon' => 'mdi-bank-transfer-in', 'title' => 'Bank Transfer Deposit', 'path' => '/r2p/payin',
         'desc' => 'Customer pays from a local bank account.',
         'extra' => [['bank_code', 'string', true, 'Customer bank code (see bank list in PDF)'], ['bank_account_name', 'string', true, 'Account holder name'], ['customer_account_number', 'string', true, 'Customer bank account number']]],
        ['id' => 'card', 'icon' => 'mdi-credit-card-outline', 'title' => 'Card Deposit', 'path' => '/qp/deposit',
         'desc' => 'Visa / Mastercard payment with 3-D Secure.',
         'extra' => [['card_holder_name', 'string', true, 'Name printed on the card'], ['card_number', 'string', true, 'Card number'], ['expiration', 'string', true, 'MM/YY'], ['cvv', 'string', true, '3 digit security code']]],
        ['id' => 'khqr', 'icon' => 'mdi-qrcode-scan', 'title' => 'KHQR / Card Checkout', 'path' => '/acleda/deposit',
         'desc' => 'Redirects the customer to a hosted checkout page (KHQR or card).',
         'extra' => [['channel', 'string', false, 'khqr (default) or card']]],
        ['id' => 'upi', 'icon' => 'mdi-cellphone-nfc', 'title' => 'UPI Deposit', 'path' => '/xpz/UPIdeposit',
         'desc' => 'Customer pays with a UPI app.',
         'extra' => [['customer_name', 'string', true, 'Customer full name']]],
        ['id' => 'crypto', 'icon' => 'mdi-bitcoin', 'title' => 'Crypto Checkout', 'path' => '/ip/checkout',
         'desc' => 'Customer pays with a crypto wallet.',
         'extra' => [['customer_name', 'string', true, 'Customer full name'], ['customer_email', 'email', true, 'Customer email']]],
    ];
    $callback = [
        ['merchant_code', 'Your merchant code'], ['referenceId', 'Your reference ID'], ['transaction_id', 'Our transaction ID (e.g. TR2026...)'],
        ['amount', 'Transaction amount'], ['Currency', 'Currency code'], ['customer_name', 'Customer name, if provided'],
        ['payment_status', 'success | pending | processing | failed'], ['created_at', 'Transaction time (Y-m-d H:i:s)'],
    ];
    $statuses = [
        ['success', 'mm-status-success', 'Payment completed. Credit the customer.'],
        ['pending', 'mm-status-pending', 'Waiting for the customer or the bank. Do not credit yet.'],
        ['processing', 'mm-status-processing', 'Sent to the bank and being processed.'],
        ['failed', 'mm-status-failed', 'Payment failed, expired or was declined.'],
    ];
    $phpSample = <<<'PHP'
<?php
$params = [
    'merchant_code' => 'YOUR_MERCHANT_CODE',
    'channel_id'    => 'YOUR_CHANNEL_ID',
    'referenceId'   => 'ORDER-' . time(),
    'callback_url'  => 'https://your-site.com/payment/callback',
    'Currency'      => 'USD',
    'amount'        => '100.00',
];

// Send the customer's browser to the payment page
header('Location: __BASE__/acleda/deposit?' . http_build_query($params));
exit;
PHP;
    $curlSample = "curl -G \"{$base}/acleda/deposit\" \\\n  --data-urlencode \"merchant_code=YOUR_MERCHANT_CODE\" \\\n  --data-urlencode \"channel_id=YOUR_CHANNEL_ID\" \\\n  --data-urlencode \"referenceId=ORDER-1001\" \\\n  --data-urlencode \"callback_url=https://your-site.com/payment/callback\" \\\n  --data-urlencode \"Currency=USD\" \\\n  --data-urlencode \"amount=100.00\"";
    $callbackSample = <<<'PHP'
<?php
// https://your-site.com/payment/callback  (receives a POST form)
$referenceId = $_POST['referenceId'] ?? '';
$status      = $_POST['payment_status'] ?? '';

if ($status === 'success') {
    // mark order $referenceId as paid (only once!)
} elseif ($status === 'failed') {
    // mark order as failed
}
// pending / processing: wait for the next callback
PHP;
    $phpSample = str_replace('__BASE__', $base, $phpSample);
@endphp
<div>
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="/">GTech</a></li>
                        <li class="breadcrumb-item active">{{ __('messages.Api Documentation') }}</li>
                    </ol>
                </div>
                <h4 class="page-title">{{ __('messages.Api Documentation') }}</h4>
            </div>
        </div>
    </div>

    {{-- Hero --}}
    <div class="mm-api-hero">
        <div>
            <span class="mm-api-version">REST API · v1</span>
            <h2>Payment Gateway API</h2>
            <p>Accept deposits by card, bank transfer, KHQR, UPI and crypto with one integration. Redirect your customer, then receive the final status on your callback URL.</p>
            <div class="mm-api-base">
                <span>Base URL</span>
                <code id="apiBase">{{ $base }}</code>
                <button type="button" class="mm-copy" data-copy="#apiBase" title="Copy"><i class="mdi mdi-content-copy"></i></button>
            </div>
        </div>
        <div class="mm-api-hero-actions">
            <a href="{{ URL::to('pdf/RichPayDepositAPI.pdf') }}" download class="btn btn-light"><i class="mdi mdi-file-pdf-box"></i> Download PDF</a>
            <a href="#endpoints" class="btn btn-success"><i class="mdi mdi-rocket"></i> View Endpoints</a>
        </div>
    </div>

    <div class="mm-api-layout">
        {{-- Table of contents --}}
        <nav class="mm-api-toc" aria-label="Documentation sections">
            <small>On this page</small>
            <a href="#quick-start"><i class="mdi mdi-flag-checkered"></i> Quick start</a>
            <a href="#authentication"><i class="mdi mdi-key-outline"></i> Authentication</a>
            <a href="#endpoints"><i class="mdi mdi-api"></i> Endpoints</a>
            @foreach ($endpoints as $ep)
                <a href="#ep-{{ $ep['id'] }}" class="mm-api-toc-sub">{{ $ep['title'] }}</a>
            @endforeach
            <a href="#callback"><i class="mdi mdi-webhook"></i> Callback</a>
            <a href="#statuses"><i class="mdi mdi-format-list-checks"></i> Status values</a>
            <a href="#samples"><i class="mdi mdi-code-tags"></i> Code samples</a>
            <a href="#resources"><i class="mdi mdi-file-document-outline"></i> Resources</a>
        </nav>

        <div class="mm-api-content">
            <section id="quick-start" class="card-box mm-api-section">
                <h3><i class="mdi mdi-flag-checkered"></i> Quick start</h3>
                <ol class="mm-api-steps">
                    <li><b>Get your credentials.</b> The admin creates your merchant account and assigns a payment channel (<code>merchant_code</code> + <code>channel_id</code>).</li>
                    <li><b>Redirect the customer.</b> Send the customer's browser to the deposit endpoint with the order details as query parameters.</li>
                    <li><b>Customer pays.</b> We show the secure payment page (card, bank, KHQR, UPI or crypto).</li>
                    <li><b>Receive the result.</b> We POST the final status to your <code>callback_url</code>. Update your order using <code>referenceId</code>.</li>
                </ol>
            </section>

            <section id="authentication" class="card-box mm-api-section">
                <h3><i class="mdi mdi-key-outline"></i> Authentication</h3>
                <p>Every request is identified by your <code>merchant_code</code> and <code>channel_id</code>. Requests with an unknown, disabled or unassigned merchant / channel are rejected:</p>
                <div class="mm-api-errors">
                    @foreach (['Invalid Merchant!', 'Merchant is Disabled!', 'Invalid Channel!', 'Channel is Disabled!', 'Gateway not configured for this Merchant!'] as $err)
                        <code>{{ $err }}</code>
                    @endforeach
                </div>
                <div class="mm-api-note"><i class="mdi mdi-shield-alert-outline"></i> Always confirm the final status from the callback before delivering goods. Never trust the status shown in the customer's browser alone.</div>
            </section>

            <section id="endpoints" class="mm-api-section-group">
                <h3 class="mm-api-group-title"><i class="mdi mdi-api"></i> Endpoints</h3>
                <p class="mm-api-group-sub">All deposit endpoints accept <b>GET</b> query parameters and redirect the customer to the payment page. These common parameters are required on every endpoint:</p>
                <div class="card-box mm-api-section">
                    <div class="table-responsive mm-table-scroll">
                        <table class="table mm-api-params">
                            <thead><tr><th>Parameter</th><th>Type</th><th>Required</th><th>Description</th></tr></thead>
                            <tbody>
                                @foreach ($common as [$name, $type, $req, $desc])
                                    <tr><td><code>{{ $name }}</code></td><td><span class="mm-type">{{ $type }}</span></td><td>{!! $req ? '<span class="mm-req">required</span>' : '<span class="mm-opt">optional</span>' !!}</td><td>{{ $desc }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                @foreach ($endpoints as $ep)
                    <div class="card-box mm-api-section mm-api-endpoint" id="ep-{{ $ep['id'] }}">
                        <div class="mm-ep-head">
                            <span class="mm-ep-icon"><i class="mdi {{ $ep['icon'] }}"></i></span>
                            <div>
                                <h4>{{ $ep['title'] }}</h4>
                                <p>{{ $ep['desc'] }}</p>
                            </div>
                        </div>
                        <div class="mm-ep-url">
                            <span class="mm-method">GET</span>
                            <code id="url-{{ $ep['id'] }}">{{ $base . $ep['path'] }}</code>
                            <button type="button" class="mm-copy" data-copy="#url-{{ $ep['id'] }}" title="Copy"><i class="mdi mdi-content-copy"></i></button>
                        </div>
                        <small class="mm-ep-label">Additional parameters</small>
                        <div class="table-responsive mm-table-scroll">
                            <table class="table mm-api-params">
                                <tbody>
                                    @foreach ($ep['extra'] as [$name, $type, $req, $desc])
                                        <tr><td><code>{{ $name }}</code></td><td><span class="mm-type">{{ $type }}</span></td><td>{!! $req ? '<span class="mm-req">required</span>' : '<span class="mm-opt">optional</span>' !!}</td><td>{{ $desc }}</td></tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </section>

            <section id="callback" class="card-box mm-api-section">
                <h3><i class="mdi mdi-webhook"></i> Callback</h3>
                <p>When the payment finishes we send an HTML form <b>POST</b> (<code>application/x-www-form-urlencoded</code>) to your <code>callback_url</code>. The same payload is used for deposits and withdrawals, and an admin can resend it at any time.</p>
                <div class="table-responsive mm-table-scroll">
                    <table class="table mm-api-params">
                        <thead><tr><th>Field</th><th>Description</th></tr></thead>
                        <tbody>
                            @foreach ($callback as [$name, $desc])
                                <tr><td><code>{{ $name }}</code></td><td>{{ $desc }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mm-api-note"><i class="mdi mdi-repeat"></i> Callbacks can arrive more than once. Make your handler idempotent: update an order only if its status actually changes.</div>
            </section>

            <section id="statuses" class="card-box mm-api-section">
                <h3><i class="mdi mdi-format-list-checks"></i> Status values</h3>
                <div class="mm-api-statuses">
                    @foreach ($statuses as [$code, $class, $desc])
                        <div class="mm-api-status"><span class="mm-status {{ $class }}">{{ $code }}</span><span>{{ $desc }}</span></div>
                    @endforeach
                </div>
            </section>

            <section id="samples" class="card-box mm-api-section">
                <h3><i class="mdi mdi-code-tags"></i> Code samples</h3>
                <div class="mm-tabs" role="tablist">
                    <button type="button" class="mm-tab is-active" data-tab="php" role="tab">PHP · Redirect</button>
                    <button type="button" class="mm-tab" data-tab="curl" role="tab">cURL</button>
                    <button type="button" class="mm-tab" data-tab="cb" role="tab">PHP · Callback handler</button>
                </div>
                @foreach (['php' => $phpSample, 'curl' => $curlSample, 'cb' => $callbackSample] as $key => $code)
                    <div class="mm-codeblock {{ $key === 'php' ? 'is-active' : '' }}" data-panel="{{ $key }}">
                        <button type="button" class="mm-copy mm-code-copy" data-copy="#code-{{ $key }}" title="Copy"><i class="mdi mdi-content-copy"></i> Copy</button>
                        <pre id="code-{{ $key }}"><code>{{ $code }}</code></pre>
                    </div>
                @endforeach
            </section>

            <section id="resources" class="card-box mm-api-section">
                <h3><i class="mdi mdi-file-document-outline"></i> Resources</h3>
                <div class="mm-api-resource">
                    <span class="mm-ep-icon mm-ep-icon-red"><i class="mdi mdi-file-pdf-box"></i></span>
                    <div>
                        <b>Integration guide (PDF)</b>
                        <small>Full request examples and the bank code list for bank transfer deposits.</small>
                    </div>
                    <div class="mm-api-resource-actions">
                        <button type="button" class="btn btn-secondary" id="pdfToggle"><i class="mdi mdi-eye-outline"></i> Preview</button>
                        <a href="{{ URL::to('pdf/RichPayDepositAPI.pdf') }}" download class="btn btn-success"><i class="mdi mdi-download"></i> Download</a>
                    </div>
                </div>
                <div class="mm-api-pdf" id="pdfFrame" hidden>
                    <iframe data-src="{{ URL::to('pdf/RichPayDepositAPI.pdf') }}" title="Integration guide"></iframe>
                </div>
            </section>
        </div>
    </div>

    <script>
        (function () {
            // copy buttons
            document.querySelectorAll('.mm-api-content .mm-copy, .mm-api-hero .mm-copy').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var el = document.querySelector(btn.dataset.copy); if (!el) return;
                    navigator.clipboard && navigator.clipboard.writeText(el.innerText.trim()).then(function () {
                        var old = btn.innerHTML; btn.innerHTML = '<i class="mdi mdi-check"></i>' + (btn.classList.contains('mm-code-copy') ? ' Copied' : '');
                        setTimeout(function () { btn.innerHTML = old; }, 1500);
                    });
                });
            });
            // code tabs
            document.querySelectorAll('.mm-tab').forEach(function (tab) {
                tab.addEventListener('click', function () {
                    document.querySelectorAll('.mm-tab').forEach(function (t) { t.classList.toggle('is-active', t === tab); });
                    document.querySelectorAll('.mm-codeblock').forEach(function (p) { p.classList.toggle('is-active', p.dataset.panel === tab.dataset.tab); });
                });
            });
            // PDF preview loads only when opened
            var toggle = document.getElementById('pdfToggle'), box = document.getElementById('pdfFrame');
            toggle && toggle.addEventListener('click', function () {
                var frame = box.querySelector('iframe'); if (!frame.src) frame.src = frame.dataset.src;
                box.hidden = !box.hidden;
                toggle.innerHTML = box.hidden ? '<i class="mdi mdi-eye-outline"></i> Preview' : '<i class="mdi mdi-eye-off-outline"></i> Hide';
            });
            // highlight the current section in the table of contents
            var links = [...document.querySelectorAll('.mm-api-toc a')];
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (e) {
                    if (!e.isIntersecting) return;
                    links.forEach(function (l) { l.classList.toggle('is-active', l.getAttribute('href') === '#' + e.target.id); });
                });
            }, { rootMargin: '-80px 0px -70% 0px' });
            links.forEach(function (l) { var t = document.querySelector(l.getAttribute('href')); t && io.observe(t); });
        })();
    </script>
</div>
