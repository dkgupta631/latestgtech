@php
    $symbols = ['THB' => '฿', 'USD' => '$', 'USDT' => '₮'];
    $previewAmount = is_numeric($amount) ? number_format((float) $amount, 2) : '0.00';
@endphp
<div class="mm-auth-wrap">
    <div class="mm-auth-card mm-qr-card mm-fade-in">

        <aside class="mm-auth-side">
            <div class="mm-auth-brand">
                <img src="{{ URL::to('newassets/images/money-magnet-sm-logo.png') }}" alt="Money Magnet">
                <div>
                    <b>Money Magnet</b>
                    <small>{{ __('messages.Payment Gateway') }}</small>
                </div>
            </div>

            <div>
                <h2>{{ __('messages.Create a payment QR') }}</h2>
                <p>{{ __('messages.Generate a secure QR code your customer can scan to pay the invoice instantly.') }}</p>
            </div>

            {{-- live preview of what the customer will pay --}}
            <div class="mm-qr-preview">
                <div class="mm-qr-preview-icon"><i class="mdi mdi-qrcode"></i></div>
                <div class="mm-qr-preview-body">
                    <small>{{ __('messages.Amount to collect') }}</small>
                    <b>{{ $symbols[$Currency] ?? '' }}{{ $previewAmount }} <span>{{ $Currency }}</span></b>
                    <div class="mm-qr-preview-meta">
                        <span><i class="mdi mdi-account-outline"></i> {{ $customer_name ?: __('messages.Customer Name') }}</span>
                        <span><i class="mdi mdi-file-document-outline"></i> {{ $invoice_number ?: __('messages.Invoice no.') }}</span>
                    </div>
                </div>
            </div>
        </aside>

        <section class="mm-auth-form">
            <h1>{{ __('messages.Generate QR Code') }}</h1>
            <p class="lead">{{ __('messages.Fill in the payment details below.') }}</p>

            <form wire:submit.prevent="saveQRdata" method="POST" novalidate>
                @csrf
                <div class="mm-field">
                    <label for="customer_name">{{ __('messages.Customer Name') }} <span class="text-danger">*</span></label>
                    <div class="mm-input-icon">
                        <i class="mdi mdi-account-outline"></i>
                        <input type="text" id="customer_name" autocomplete="off" autofocus
                               class="form-control @error('customer_name') is-invalid @enderror"
                               wire:model.live.debounce.300ms="customer_name" placeholder="{{ __('messages.e.g. John Smith') }}">
                    </div>
                    @error('customer_name') <label class="error" for="customer_name">{{ $message }}</label> @enderror
                </div>

                <div class="mm-field">
                    <span class="mm-label">{{ __('messages.Currency') }} <span class="text-danger">*</span></span>
                    <div class="mm-segment" role="radiogroup" aria-label="{{ __('messages.Currency') }}">
                        @foreach (['THB' => __('messages.Thai Baht'), 'USD' => __('messages.US Dollar'), 'USDT' => __('messages.Tether')] as $code => $name)
                            <label class="mm-segment-item">
                                <input type="radio" value="{{ $code }}" wire:model.live="Currency">
                                <span><b>{{ $symbols[$code] }} {{ $code }}</b><small>{{ $name }}</small></span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="mm-field">
                    <label for="amount">{{ __('messages.Amount') }} <span class="text-danger">*</span></label>
                    <div class="mm-input-icon mm-input-amount">
                        <i class="mm-currency-sign">{{ $symbols[$Currency] ?? '' }}</i>
                        <input type="text" id="amount" inputmode="decimal" autocomplete="off"
                               class="form-control @error('amount') is-invalid @enderror"
                               wire:model.live.debounce.300ms="amount" placeholder="100.00"
                               oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1')">
                        <span class="mm-input-suffix">{{ $Currency }}</span>
                    </div>
                    @error('amount') <label class="error" for="amount">{{ $message }}</label> @enderror
                </div>

                <div class="mm-field">
                    <label for="invoice_number">{{ __('messages.Invoice Number') }} <span class="text-danger">*</span></label>
                    <div class="mm-input-icon">
                        <i class="mdi mdi-file-document-outline"></i>
                        <input type="text" id="invoice_number" autocomplete="off"
                               class="form-control @error('invoice_number') is-invalid @enderror"
                               wire:model.live.debounce.300ms="invoice_number" placeholder="{{ __('messages.e.g. INV-2026-0001') }}">
                    </div>
                    @error('invoice_number') <label class="error" for="invoice_number">{{ $message }}</label> @enderror
                </div>

                <button class="btn btn-success mm-btn-block" type="submit" wire:loading.attr="disabled" wire:target="saveQRdata">
                    <span wire:loading.remove wire:target="saveQRdata"><i class="mdi mdi-qrcode-plus"></i> {{ __('messages.Generate QR Code') }}</span>
                    <span wire:loading wire:target="saveQRdata"><span class="mm-spin"></span> {{ __('messages.Generating...') }}</span>
                </button>
            </form>

            <div class="mm-auth-secure">
                <i class="mdi mdi-lock-check"></i> {{ __('messages.Secure payment link · Encrypted QR') }}
            </div>
        </section>
    </div>
</div>
