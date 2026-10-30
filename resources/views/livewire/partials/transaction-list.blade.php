{{--
    Shared transaction list (deposit + withdraw).
    Expects: $type ('deposit'|'withdraw'), $title, and the component's
    $transactionlist, $totalAmount, $totalmdrfee, $totalNetAmount, $dateFilter.
--}}
@php
    $isMerchant = Session::get('auth')->role_name == 'Merchant';
    $isFc = Session::get('auth')->merchant_id == '1';
    $money = fn ($v) => number_format((float) ($v ?? 0), 2);
    // deposits and withdrawals store amount/status in different columns
    $amountField = $type === 'withdraw' ? 'total' : 'amount';
    $statusField = $type === 'withdraw' ? 'status' : 'payment_status';
    $count = $transactionlist->count();
    $successCount = $transactionlist->where($statusField, 'success')->count();
    $successRate = $count ? round($successCount / $count * 100) : 0;
    $statusMeta = [
        'success'    => ['label' => __('messages.Success'), 'class' => 'mm-status-success', 'icon' => 'mdi-check-circle'],
        'pending'    => ['label' => __('messages.pending'), 'class' => 'mm-status-pending', 'icon' => 'mdi-clock-outline'],
        'processing' => ['label' => __('messages.processing'), 'class' => 'mm-status-processing', 'icon' => 'mdi-progress-clock'],
        'failed'     => ['label' => __('messages.Failed'), 'class' => 'mm-status-failed', 'icon' => 'mdi-close-circle'],
    ];
    $dateButtons = [
        'today'      => __('messages.Today'),
        'yesterday'  => __('messages.Yesterday'),
        '7days'      => __('messages.7 days ago'),
        'this_week'  => __('messages.This week'),
        'last_week'  => __('messages.Last week'),
        'this_month' => __('messages.This month'),
        'last_month' => __('messages.Last month'),
        'this_year'  => __('messages.This year'),
        'last_year'  => __('messages.Last year'),
    ];
@endphp

<div>
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="/">GTech</a></li>
                        <li class="breadcrumb-item active">{{ $title }}</li>
                    </ol>
                </div>
                <h4 class="page-title">{{ $title }}</h4>
            </div>
        </div>
    </div>
    <!-- end page title -->

    {{-- KPI tiles --}}
    <div class="mm-kpis">
        <div class="mm-kpi">
            <span class="mm-kpi-icon mm-kpi-blue"><i class="mdi mdi-cash-multiple"></i></span>
            <div>
                <small>{{ __('messages.Total Amount:') }}</small>
                <b>{{ $money($totalAmount) }}</b>
            </div>
        </div>
        <div class="mm-kpi">
            <span class="mm-kpi-icon mm-kpi-red"><i class="mdi mdi-percent"></i></span>
            <div>
                <small>{{ __('messages.Total MDR Fee:') }}</small>
                <b>{{ $money($totalmdrfee) }}</b>
            </div>
        </div>
        <div class="mm-kpi">
            <span class="mm-kpi-icon mm-kpi-teal"><i class="mdi mdi-wallet-outline"></i></span>
            <div>
                <small>{{ __('messages.Total Net Amount:') }}</small>
                <b>{{ $money($totalNetAmount) }}</b>
            </div>
        </div>
        <div class="mm-kpi">
            <span class="mm-kpi-icon mm-kpi-navy"><i class="mdi mdi-swap-vertical"></i></span>
            <div>
                <small>{{ __('messages.Transactions') }}</small>
                <b>{{ number_format($count) }}</b>
                <span class="mm-kpi-sub"><span class="mm-dot"></span>{{ __('messages.:rate% success', ['rate' => $successRate]) }}</span>
            </div>
        </div>
    </div>

    <div class="card-box">
        {{-- Filters --}}
        <div class="mm-filters">
            <div class="mm-filter-fields">
                <div class="mm-input-icon mm-filter-search">
                    <i class="mdi mdi-magnify"></i>
                    <input type="text" wire:model.live.debounce.400ms="search" class="form-control" placeholder="{{ __('messages.Search here') }}...">
                </div>
                <select wire:model.live="statusFilter" class="form-control custom-select mm-filter-status" aria-label="{{ __('messages.Status') }}">
                    <option value="all">{{ __('messages.All Status') }}</option>
                    <option value="pending">{{ __('messages.pending') }}</option>
                    <option value="success">{{ __('messages.Success') }}</option>
                    <option value="processing">{{ __('messages.processing') }}</option>
                    <option value="failed">{{ __('messages.Failed') }}</option>
                </select>
                <button wire:click="exportExcel" type="button" class="btn btn-success mm-filter-export">
                    <i class="mdi mdi-download"></i> {{ __('messages.Export') }}
                </button>
            </div>

            <div class="mm-date-chips" role="group" aria-label="{{ __('messages.Quick date range') }}">
                @foreach ($dateButtons as $key => $label)
                    <button type="button" wire:click="$set('dateFilter', '{{ $key }}')"
                            class="mm-chip {{ $dateFilter === $key ? 'is-active' : '' }}"
                            aria-pressed="{{ $dateFilter === $key ? 'true' : 'false' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="mm-loading" wire:loading.flex wire:target="search, statusFilter, dateFilter">
            <span class="mm-spin mm-spin-blue"></span> {{ __('messages.Updating...') }}
        </div>

        {{-- Table --}}
        <div class="table-responsive mm-table-scroll">
            <table id="txn-table" class="table mm-txn-table" style="width: 100%;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('messages.Transaction ID') }}</th>
                        @unless ($isMerchant)
                            <th>{{ __('messages.Merchant Code') }}</th>
                        @endunless
                        <th>{{ __('messages.Customer Name') }}</th>
                        <th class="text-right">{{ __('messages.Amount') }}</th>
                        <th class="text-right">{{ __('messages.MDR') }}</th>
                        <th class="text-right">{{ __('messages.Net') }}</th>
                        <th>{{ __('messages.Status') }}</th>
                        <th>{{ __('messages.Created Time') }}</th>
                        <th class="text-center">{{ __('messages.Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactionlist as $row)
                        @php $status = $statusMeta[$row->{$statusField}] ?? $statusMeta['success']; @endphp
                        <tr wire:key="txn-{{ $row->id }}">
                            <td class="text-muted">{{ $loop->iteration }}</td>
                            <td>
                                <div class="mm-txn-id">{{ $row->systemgenerated_TransId ?? '' }}</div>
                                <div class="mm-txn-sub">
                                    {{ $isFc ? __('messages.Invoice Number') : __('messages.Reference ID') }}: {{ $row->reference_id ?? '—' }}
                                </div>
                            </td>
                            @unless ($isMerchant)
                                <td><span class="mm-code">{{ $row->merchant_code ?? '' }}</span></td>
                            @endunless
                            <td>{{ $row->customer_name ?: '—' }}</td>
                            <td class="text-right mm-amount">{{ $money($row->{$amountField}) }} <small>{{ $row->Currency }}</small></td>
                            <td class="text-right mm-fee">{{ $money($row->mdr_fee_amount) }}</td>
                            <td class="text-right mm-net">{{ $money($row->net_amount) }}</td>
                            <td>
                                <span class="mm-status {{ $status['class'] }}"><i class="mdi {{ $status['icon'] }}"></i> {{ $status['label'] }}</span>
                            </td>
                            <td>
                                @if ($row->created_at)
                                    <div class="mm-date">{{ $row->created_at->translatedFormat('d M Y') }}</div>
                                    <div class="mm-txn-sub">{{ $row->created_at->format('h:i:s A') }}</div>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="mm-kebab" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="{{ __('messages.Action') }}">
                                        <i class="mdi mdi-dots-vertical"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        @if ($row->callback_url)
                                            <a class="dropdown-item" href="{{ url(($type === 'withdraw' ? 'sendWithdrawNotification/' : 'sendDepositNotification/') . base64_encode($row->id)) }}" target="_blank" rel="noopener">
                                                <i class="mdi mdi-send-outline mr-1"></i> {{ __('messages.Resend Callback') }}
                                            </a>
                                        @else
                                            <span class="dropdown-item text-muted" aria-disabled="true"><i class="mdi mdi-send-outline mr-1"></i> {{ __('messages.No callback URL') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="mm-empty-row">
                            <td colspan="{{ $isMerchant ? 9 : 10 }}">
                                <div class="mm-empty">
                                    <i class="mdi mdi-file-search-outline"></i>
                                    <b>{{ __('messages.Record not found') }}</b>
                                    <span>{{ __('messages.Try another date range or status.') }}</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
