<div class="navbar-custom" wire:poll.10s.keep-alive="checkNew">
    <ul class="list-unstyled topnav-menu float-right mb-0">

        @php
            $locales = ['en' => ['name' => 'English', 'flag' => 'us.jpg'], 'th' => ['name' => 'ไทย (Thai)', 'flag' => 'th.png']];
            $current = session()->get('locale') == 'th' ? 'th' : 'en';
        @endphp
        <li class="dropdown notification-list" id="Langchange">
            <a class="nav-link dropdown-toggle mm-lang" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false" aria-label="Language: {{ $locales[$current]['name'] }}">
                <img src="{{ URL::to('newassets/images/flags/' . $locales[$current]['flag']) }}" alt="" class="mm-flag">
                <span class="mm-lang-code">{{ strtoupper($current) }}</span>
                <i class="mdi mdi-chevron-down mm-lang-caret"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right mm-lang-menu">
                <div class="mm-lang-title">{{ __('messages.Language') }}</div>
                @foreach ($locales as $code => $loc)
                    <a href="{{ route('change.lang', ['lang' => $code]) }}" class="dropdown-item mm-lang-item {{ $code === $current ? 'is-active' : '' }}" id="{{ $code }}">
                        <img src="{{ URL::to('newassets/images/flags/' . $loc['flag']) }}" alt="" class="mm-flag">
                        <span>{{ $loc['name'] }}</span>
                        @if ($code === $current)<i class="mdi mdi-check"></i>@endif
                    </a>
                @endforeach
            </div>
        </li>

        {{-- sound on/off (also unlocks browser audio on first click) --}}
        <li class="notification-list" wire:ignore>
            <button type="button" class="nav-link mm-sound-toggle" id="mmSoundToggle" title="{{ __('messages.Transaction sound alerts') }}" aria-pressed="true">
                <i class="mdi mdi-volume-high"></i>
            </button>
        </li>

        <li class="dropdown notification-list">
            <a class="nav-link dropdown-toggle mm-bell {{ $NotificationCount > 0 ? 'has-unread' : '' }}" data-toggle="dropdown" href="#" role="button"
               aria-haspopup="true" aria-expanded="false" aria-label="{{ trans('messages.Notification') }}: {{ $NotificationCount }}">
                <i class="mdi mdi-bell-outline mm-bell-icon"></i>
                @if ($NotificationCount > 0)
                    <span class="mm-bell-badge">{{ $NotificationCount > 99 ? '99+' : $NotificationCount }}</span>
                @endif
            </a>

            <div class="dropdown-menu dropdown-menu-right mm-noti-menu">
                <div class="mm-noti-head">
                    <div>
                        <b>{{ trans('messages.Notification') }}</b>
                        <small>{{ $NotificationCount }} {{ __('messages.unread') }}</small>
                    </div>
                    @if ($NotificationCount > 0)
                        <button type="button" wire:click="markReadTransaction" class="mm-noti-clear">{{ trans('messages.mark all as read') }}</button>
                    @endif
                </div>

                <div class="mm-noti-list">
                    @forelse ($NotificationData as $unread)
                        @php
                            $record = json_decode($unread->data, true) ?: [];
                            $st = strtolower($record['status'] ?? '');
                            $isWithdraw = str_contains(strtolower(($record['type'] ?? '') . ' ' . $unread->msg), 'withdraw');
                            $icon = str_starts_with((string) $unread->notifiable_type, 'Notification send') ? 'mdi-send-outline'
                                  : ($isWithdraw ? 'mdi-arrow-top-right' : 'mdi-arrow-bottom-left');
                        @endphp
                        <div class="mm-noti-item">
                            <span class="mm-noti-icon mm-st-{{ in_array($st, ['success', 'pending', 'processing', 'failed']) ? $st : 'success' }}"><i class="mdi {{ $icon }}"></i></span>
                            <div class="mm-noti-body">
                                <div class="mm-noti-row">
                                    <b>{{ ucfirst($unread->msg) }}</b>
                                    <time>{{ $unread->created_at->diffForHumans(null, true) }}</time>
                                </div>
                                <div class="mm-noti-row">
                                    <span class="mm-noti-txn">{{ $record['transaction_id'] ?? '' }}</span>
                                    @if (!empty($record['amount']))
                                        <span class="mm-noti-amt">{{ is_numeric($record['amount']) ? number_format((float) $record['amount'], 2) : $record['amount'] }} {{ $record['Currency'] ?? '' }}</span>
                                    @endif
                                </div>
                                @if ($st)
                                    <span class="mm-noti-status mm-st-{{ $st }}">{{ ucfirst($st) }}</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="mm-noti-empty">
                            <i class="mdi mdi-bell-check-outline"></i>
                            {{ trans('messages.There is no have any Notification!') }}
                        </div>
                    @endforelse
                </div>
                @if ($NotificationCount > 20)
                    <div class="mm-noti-more">{{ __('messages.Showing latest 20 of :count', ['count' => $NotificationCount]) }}</div>
                @endif
            </div>
        </li>

        

        @php
            $authUser = Session::get('auth');
            $roleIcons = ['Admin' => 'mdi-shield-account', 'Agent' => 'mdi-account-tie', 'Merchant' => 'mdi-store'];
            $roleIcon = $roleIcons[$authUser->role_name ?? ''] ?? 'mdi-account';
            $roleClass = 'mm-avatar-' . strtolower($authUser->role_name ?? 'user');
        @endphp
        <li class="dropdown notification-list">
            <a class="nav-link dropdown-toggle mm-user-btn" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                <span class="mm-head-avatar {{ $roleClass }}"><i class="mdi {{ $roleIcon }}"></i></span>
                <span class="mm-user-meta d-none d-sm-flex">
                    <b>{{ $authUser->user_name ?? '' }}</b>
                    <small>{{ __('messages.' . ($authUser->role_name ?? 'Admin')) }}</small>
                </span>
                <i class="mdi mdi-chevron-down mm-lang-caret d-none d-sm-inline-block"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right mm-user-menu">
                <div class="mm-user-menu-head">
                    <span class="mm-head-avatar {{ $roleClass }} mm-head-avatar-lg"><i class="mdi {{ $roleIcon }}"></i></span>
                    <div>
                        <b>{{ $authUser->name ?: $authUser->user_name }}</b>
                        <small>{{ $authUser->email ?? '' }}</small>
                        <span class="mm-role-chip">{{ __('messages.' . ($authUser->role_name ?? 'Admin')) }}</span>
                    </div>
                </div>
                <a href="{{ route('user.profile') }}" class="dropdown-item mm-user-item">
                    <i class="mdi mdi-account-circle-outline"></i> {{ __('messages.Profile') }}
                </a>
                <a href="{{ route('user.profile') }}#password" class="dropdown-item mm-user-item">
                    <i class="mdi mdi-lock-reset"></i> {{ __('messages.Change Password') }}
                </a>
                <a href="{{ route('api.documentation') }}" class="dropdown-item mm-user-item">
                    <i class="mdi mdi-code-braces"></i> {{ __('messages.Api Documentation') }}
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" wire:click.prevent="logout" class="dropdown-item mm-user-item mm-user-logout">
                    <i class="mdi mdi-logout-variant"></i> {{ __('messages.Logout') }}
                </a>
            </div>
        </li>
    </ul>

    <!-- LOGO -->
    <div class="logo-box">
        <a href="{{ route('dashboard') }}" class="logo logo-dark">
            <span class="logo-lg">
                <img src="{{ URL::to('newassets/images/money-magnet-sm-logo.png') }}" class="mm-brand-logo" alt="Money Magnet">
                <span class="mm-brand-name">Money Magnet<small>{{ __('messages.Payment Gateway') }}</small></span>
            </span>
            <span class="logo-sm">
                <img src="{{ URL::to('newassets/images/money-magnet-sm-logo.png') }}" class="mm-brand-logo" alt="Money Magnet">
            </span>
        </a>
    </div>

    <ul class="list-unstyled topnav-menu topnav-menu-left m-0">
        <li>
            <button class="button-menu-mobile waves-effect waves-light">
                <i class="mdi mdi-menu"></i>
            </button>
        </li>
    </ul>
</div>