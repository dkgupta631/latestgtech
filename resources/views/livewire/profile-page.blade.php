@php
    $user = Session::get('auth');
    $details = [
        ['icon' => 'mdi-account-outline', 'label' => trans('messages.User Name'), 'value' => $user->user_name ?? ''],
        ['icon' => 'mdi-email-outline', 'label' => __('messages.Email'), 'value' => $user->email ?? ''],
        ['icon' => 'mdi-phone-outline', 'label' => __('messages.Mobile Number'), 'value' => $user->mobile_number ?? ''],
        ['icon' => 'mdi-calendar-clock', 'label' => trans('messages.Created Date/Time'), 'value' => $user->created_at ? strtoupper($user->created_at->format('dMY')) : ''],
    ];
    if ($user->role_name == 'Merchant') {
        $details[] = ['icon' => 'mdi-store-outline', 'label' => __('messages.Merchant Code'), 'value' => $merchantData->merchant_code ?? ''];
        $details[] = ['icon' => 'mdi-store-outline', 'label' => __('messages.Merchant Name'), 'value' => $merchantData->merchant_name ?? ''];
    }
    if (in_array($user->role_name, ['Merchant', 'Agent'])) {
        $details[] = ['icon' => 'mdi-account-tie-outline', 'label' => __('messages.Agent Code'), 'value' => $agentData->agent_code ?? ''];
        $details[] = ['icon' => 'mdi-account-tie-outline', 'label' => __('messages.Agent Name'), 'value' => $agentData->agent_name ?? ''];
    }
@endphp
<div>
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="/">GTech</a></li>
                        <li class="breadcrumb-item active">{{ __('messages.Profile') }}</li>
                    </ol>
                </div>
                <h4 class="page-title">{{ __('messages.Profile') }}</h4>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <div class="row">
        <div class="col-lg-7">
            <div class="card-box mm-profile">
                <div class="mm-profile-head">
                    <span class="mm-head-avatar mm-head-avatar-xl mm-avatar-{{ strtolower($user->role_name ?? 'user') }}"><i class="mdi {{ ['Admin' => 'mdi-shield-account', 'Agent' => 'mdi-account-tie', 'Merchant' => 'mdi-store'][$user->role_name] ?? 'mdi-account' }}"></i></span>
                    <div>
                        <h4 class="mb-1">{{ $user->user_name ?? '' }}</h4>
                        <span class="badge badge-info">{{ $user->role_name ?? '' }}</span>
                    </div>
                </div>
                <div class="mm-profile-grid">
                    @foreach ($details as $item)
                        <div class="mm-profile-item">
                            <i class="mdi {{ $item['icon'] }}"></i>
                            <div>
                                <small>{{ $item['label'] }}</small>
                                <b>{{ $item['value'] !== '' ? $item['value'] : '—' }}</b>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card-box">
                <h4 class="header-title mb-1" id="password">{{ __('messages.Change Password') }}</h4>
                <p class="sub-header mb-3">{{ __('messages.Use at least 8 characters with a mix of letters and numbers.') }}</p>
                <form wire:submit.prevent="changePass" method="POST" role="form" novalidate>
                    @csrf
                    <div class="form-group">
                        <label for="NewPassword">{{ __('messages.New Password') }}<span class="text-danger">*</span></label>
                        <input type="password" id="NewPassword" autocomplete="new-password" class="form-control @error('NewPassword') is-invalid @enderror" wire:model.live="NewPassword" placeholder="{{ __('messages.Enter here') }}">
                        @error('NewPassword')
                            <label class="error" for="NewPassword">{{ $message }}</label>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword">{{ __('messages.Confirm Password') }}<span class="text-danger">*</span></label>
                        <input type="password" id="confirmPassword" autocomplete="new-password" class="form-control @error('confirmPassword') is-invalid @enderror" wire:model.live="confirmPassword" placeholder="{{ __('messages.Enter here') }}">
                        @error('confirmPassword')
                            <label class="error" for="confirmPassword">{{ $message }}</label>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="oldPassword">{{ __('messages.Old Password') }}<span class="text-danger">*</span></label>
                        <input type="password" id="oldPassword" autocomplete="current-password" class="form-control @error('oldPassword') is-invalid @enderror" wire:model.live="oldPassword" placeholder="{{ __('messages.Enter here') }}">
                        @error('oldPassword')
                            <label class="error" for="oldPassword">{{ $message }}</label>
                        @enderror
                    </div>
                    <div class="d-flex mt-4">
                        <button type="submit" class="btn btn-primary mr-2" wire:loading.attr="disabled" wire:target="changePass">{{ __('messages.Save') }}</button>
                        <button type="reset" class="btn btn-secondary">{{ __('messages.Reset') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
