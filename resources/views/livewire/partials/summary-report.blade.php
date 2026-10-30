{{--
    Shared summary report (deposit + withdrawal).
    Expects: $type ('deposit'|'withdraw'), $title, and the component's
    $transactionlist (paginator), $totalAmount, $totalmdrfee, $totalNetAmount,
    $totalCount, $statusFilter, $startDate, $endDate.
--}}
@php
    $isMerchant = Session::get('auth')->role_name == 'Merchant';
    $isFc = Session::get('auth')->merchant_id == '1';
    $amountField = $type === 'withdraw' ? 'total' : 'amount';
    $statusField = $type === 'withdraw' ? 'status' : 'payment_status';
    $money = fn ($v) => number_format((float) ($v ?? 0), 2);
    $statusMeta = [
        'success'    => ['label' => __('messages.Success'), 'class' => 'mm-status-success', 'icon' => 'mdi-check-circle'],
        'pending'    => ['label' => __('messages.pending'), 'class' => 'mm-status-pending', 'icon' => 'mdi-clock-outline'],
        'processing' => ['label' => __('messages.processing'), 'class' => 'mm-status-processing', 'icon' => 'mdi-progress-clock'],
        'failed'     => ['label' => __('messages.Failed'), 'class' => 'mm-status-failed', 'icon' => 'mdi-close-circle'],
    ];
    $presets = [
        'today' => __('messages.Today'), 'yesterday' => __('messages.Yesterday'), '7days' => __('messages.7 days ago'),
        'this_month' => __('messages.This month'), 'last_month' => __('messages.Last month'), 'this_year' => __('messages.This year'),
    ];
    // highlight the chip that matches the picked dates
    $activePreset = collect(array_keys($presets))->first(function ($p) use ($startDate, $endDate) {
        $r = \App\Support\DateRangeFilter::range($p);
        return $r && $r[0]->format('Y-m-d') === $startDate && $r[1]->format('Y-m-d') === $endDate;
    });
    $periodLabel = $startDate === $endDate
        ? \Carbon\Carbon::parse($startDate)->translatedFormat('d M Y')
        : \Carbon\Carbon::parse($startDate)->translatedFormat('d M Y') . ' – ' . \Carbon\Carbon::parse($endDate)->translatedFormat('d M Y');
    $totalsScope = ($statusMeta[$statusFilter === 'all' ? 'success' : $statusFilter]['label'] ?? $statusFilter);
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

    <div class="mm-report-period">
        <i class="mdi mdi-calendar-range"></i>
        <span>{{ __('messages.Report period:') }} <b>{{ $periodLabel }}</b></span>
        <span class="mm-report-scope">{{ __('messages.Totals include :status transactions only', ['status' => $totalsScope]) }}</span>
    </div>

    {{-- KPI tiles --}}
    <div class="mm-kpis">
        <div class="mm-kpi">
            <span class="mm-kpi-icon mm-kpi-blue"><i class="mdi mdi-cash-multiple"></i></span>
            <div><small>{{ __('messages.Total Amount:') }}</small><b>{{ $money($totalAmount) }}</b></div>
        </div>
        <div class="mm-kpi">
            <span class="mm-kpi-icon mm-kpi-red"><i class="mdi mdi-percent"></i></span>
            <div><small>{{ __('messages.Total MDR Fee:') }}</small><b>{{ $money($totalmdrfee) }}</b></div>
        </div>
        <div class="mm-kpi">
            <span class="mm-kpi-icon mm-kpi-teal"><i class="mdi mdi-wallet-outline"></i></span>
            <div><small>{{ __('messages.Total Net Amount:') }}</small><b>{{ $money($totalNetAmount) }}</b></div>
        </div>
        <div class="mm-kpi">
            <span class="mm-kpi-icon mm-kpi-navy"><i class="mdi mdi-swap-vertical"></i></span>
            <div>
                <small>{{ __('messages.Transactions') }}</small>
                <b>{{ number_format($transactionlist->total()) }}</b>
                <span class="mm-kpi-sub"><span class="mm-dot"></span>{{ number_format($totalCount) }} {{ $totalsScope }}</span>
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
                <div class="mm-date-range">
                    <label class="mm-date-field">
                        <span>{{ __('messages.From') }}</span>
                        <input type="date" wire:model.live="startDate" max="{{ $endDate }}" class="form-control">
                    </label>
                    <i class="mdi mdi-arrow-right mm-date-sep"></i>
                    <label class="mm-date-field">
                        <span>{{ __('messages.To') }}</span>
                        <input type="date" wire:model.live="endDate" min="{{ $startDate }}" class="form-control">
                    </label>
                </div>
                <button wire:click="exportExcel" type="button" class="btn btn-success mm-filter-export">
                    <i class="mdi mdi-download"></i> {{ __('messages.Export') }}
                </button>
            </div>

            <div class="mm-date-chips" role="group" aria-label="{{ __('messages.Quick date range') }}">
                @foreach ($presets as $key => $label)
                    <button type="button" wire:click="setRange('{{ $key }}')"
                            class="mm-chip {{ $activePreset === $key ? 'is-active' : '' }}"
                            aria-pressed="{{ $activePreset === $key ? 'true' : 'false' }}">{{ $label }}</button>
                @endforeach
            </div>
        </div>

        <div class="mm-loading" wire:loading.flex wire:target="search, statusFilter, startDate, endDate, setRange, gotoPage, nextPage, previousPage">
            <span class="mm-spin mm-spin-blue"></span> {{ __('messages.Updating...') }}
        </div>

        {{-- Table --}}
        <div class="table-responsive mm-table-scroll">
            <table class="table mm-txn-table" style="width: 100%;">
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
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactionlist as $row)
                        @php $status = $statusMeta[$row->{$statusField}] ?? $statusMeta['success']; @endphp
                        <tr wire:key="rpt-{{ $row->id }}">
                            <td class="text-muted">{{ $transactionlist->firstItem() + $loop->index }}</td>
                            <td>
                                <div class="mm-txn-id">{{ $row->systemgenerated_TransId ?? '' }}</div>
                                <div class="mm-txn-sub">{{ $isFc ? __('messages.Invoice Number') : __('messages.Reference ID') }}: {{ $row->reference_id ?? '—' }}</div>
                            </td>
                            @unless ($isMerchant)
                                <td><span class="mm-code">{{ $row->merchant_code ?? '' }}</span></td>
                            @endunless
                            <td>{{ $row->customer_name ?: '—' }}</td>
                            <td class="text-right mm-amount">{{ $money($row->{$amountField}) }} <small>{{ $row->Currency }}</small></td>
                            <td class="text-right mm-fee">{{ $money($row->mdr_fee_amount) }}</td>
                            <td class="text-right mm-net">{{ $money($row->net_amount) }}</td>
                            <td><span class="mm-status {{ $status['class'] }}"><i class="mdi {{ $status['icon'] }}"></i> {{ $status['label'] }}</span></td>
                            <td>
                                @if ($row->created_at)
                                    <div class="mm-date">{{ $row->created_at->translatedFormat('d M Y') }}</div>
                                    <div class="mm-txn-sub">{{ $row->created_at->format('h:i:s A') }}</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr class="mm-empty-row">
                            <td colspan="{{ $isMerchant ? 8 : 9 }}">
                                <div class="mm-empty">
                                    <i class="mdi mdi-file-chart-outline"></i>
                                    <b>{{ __('messages.Record not found') }}</b>
                                    <span>{{ __('messages.No transactions in this period. Try another date range or status.') }}</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($transactionlist->hasPages() || $transactionlist->total())
            <div class="mm-pager">
                <span>{!! __('messages.Showing :from–:to of :total', ['from' => '<b>' . ($transactionlist->firstItem() ?? 0), 'to' => ($transactionlist->lastItem() ?? 0) . '</b>', 'total' => '<b>' . number_format($transactionlist->total()) . '</b>']) !!}</span>
                {{ $transactionlist->onEachSide(1)->links() }}
            </div>
        @endif
    </div>
</div>
