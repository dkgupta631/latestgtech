<div class="mm-auth-wrap">
    <div class="mm-auth-card mm-fade-in">

        <aside class="mm-auth-side">
            <div class="mm-auth-brand">
                <img src="{{ URL::to('newassets/images/khbiz-logo.png') }}" alt="KHBiz">
            </div>

            <div>
                <h2>{{ __('messages.Global payments,') }}<br>{{ __('messages.one secure platform.') }}</h2>
                <p>{{ __('messages.Accept deposits, manage payouts and track every transaction across all your gateways in real time.') }}</p>
            </div>

            <ul class="mm-auth-features">
                <li><i class="mdi mdi-shield-check-outline"></i> {{ __('messages.Bank-grade security & encryption') }}</li>
                <li><i class="mdi mdi-earth"></i> {{ __('messages.Multi-currency, multi-gateway') }}</li>
                <li><i class="mdi mdi-chart-line"></i> {{ __('messages.Live dashboards & reports') }}</li>
            </ul>

            <div class="mm-auth-methods">
                <span>VISA</span><span>MASTERCARD</span><span>KHQR</span><span>UPI</span><span>CRYPTO</span>
            </div>
        </aside>

        <section class="mm-auth-form">
            <h1>{{ __('messages.Welcome back') }}</h1>
            <p class="lead">{{ __('messages.Sign in to your account to continue.') }}</p>

            <form wire:submit.prevent="loginCheck" method="POST" novalidate>
                @csrf
                <div class="mm-field">
                    <label for="username">{{ __('messages.Username') }}</label>
                    <div class="mm-input-icon">
                        <i class="mdi mdi-account-outline"></i>
                        <input type="text" id="username" autocomplete="username" autofocus
                               class="form-control @error('username') is-invalid @enderror"
                               wire:model.live="username" placeholder="{{ __('messages.Enter your username') }}">
                    </div>
                    @error('username')
                        <label class="error" for="username">{{ $message }}</label>
                    @enderror
                </div>

                <div class="mm-field">
                    <label for="password">{{ __('messages.Password') }}</label>
                    <div class="mm-input-icon">
                        <i class="mdi mdi-lock-outline"></i>
                        <input type="{{ $showPassword ? 'text' : 'password' }}" id="password" autocomplete="current-password"
                               class="form-control with-eye @error('password') is-invalid @enderror"
                               wire:model.live="password" placeholder="{{ __('messages.Enter your password') }}">
                        <button type="button" class="mm-eye" wire:click="$toggle('showPassword')"
                                aria-label="{{ $showPassword ? __('messages.Hide password') : __('messages.Show password') }}">
                            <i class="mdi {{ $showPassword ? 'mdi-eye-off-outline' : 'mdi-eye-outline' }}"></i>
                        </button>
                    </div>
                    @error('password')
                        <label class="error" for="password">{{ $message }}</label>
                    @enderror
                </div>

                <button class="btn btn-success mm-btn-block" type="submit" wire:loading.attr="disabled" wire:target="loginCheck">
                    <span wire:loading.remove wire:target="loginCheck" class="mm-signin-label">
                        {{ __('messages.Sign In') }}
                        <svg class="mm-long-arrow" viewBox="0 0 40 16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <line x1="1" y1="8" x2="37" y2="8"/><polyline points="30 2 37 8 30 14"/>
                        </svg>
                    </span>
                    <span wire:loading wire:target="loginCheck"><span class="mm-spin"></span> {{ __('messages.Signing in...') }}</span>
                </button>
            </form>

            <div class="mm-auth-secure">
                <i class="mdi mdi-lock-check"></i> {{ __('messages.Secure sign-in · Authorized users only') }}
            </div>
        </section>
    </div>
</div>
