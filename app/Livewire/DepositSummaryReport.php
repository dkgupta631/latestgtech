<?php
namespace App\Livewire;
use Livewire\Component;

use Livewire\WithPagination;
use App\Models\DepositTransaction;
use App\Exports\DepositReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Session;
use App\Support\DateRangeFilter;

class DepositSummaryReport extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $statusFilter = 'all';
    public $search = '';
    public $startDate;
    public $endDate;
    public $totalAmount = 0;
    public $totalmdrfee = 0;
    public $totalNetAmount = 0;
    public $totalCount = 0;   // transactions counted in the totals

    protected $queryString = ['statusFilter', 'search', 'startDate', 'endDate'];

    public function mount()
    {
        // 🗓️ Default start and end date = today
        $today = now()->format('Y-m-d');
        $this->startDate = $today;
        $this->endDate = $today;

        $this->calculateTotals();
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['statusFilter', 'search', 'startDate', 'endDate'])) {
            // keep the range valid when a date is picked out of order
            if ($this->startDate && $this->endDate && $this->startDate > $this->endDate) {
                [$this->startDate, $this->endDate] = [$this->endDate, $this->startDate];
            }
            $this->resetPage();
            $this->calculateTotals();
        }
    }

    // quick range chips: today, last 7 days, this month ...
    public function setRange($preset)
    {
        if ($range = DateRangeFilter::range($preset)) {
            $this->startDate = $range[0]->format('Y-m-d');
            $this->endDate = $range[1]->format('Y-m-d');
            $this->resetPage();
            $this->calculateTotals();
        }
    }

    public function exportExcel()
    {
        return Excel::download(
            new DepositReportExport(
                $this->statusFilter,
                $this->startDate,
                $this->endDate,
                $this->search
            ),
            'deposit-transactions-report.xlsx'
        );
    }

    public function calculateTotals()
    {
        $query = DepositTransaction::query();

        // Role-based filter
        if (Session::get('auth')->role_name === 'Merchant') {
            $query->where('merchant_id', Session::get('auth')->merchant_id);
        } elseif (Session::get('auth')->role_name === 'Agent') {
            $query->where('agent_id', Session::get('auth')->agent_id);
        }

        // Status
        if ($this->statusFilter !== 'all') {
            $query->where('payment_status', $this->statusFilter);
        } else {
            $query->where('payment_status', 'success'); // Default totals for success
        }

        // Date range (always applied)
        $query->whereBetween('created_at', [
            $this->startDate . ' 00:00:00',
            $this->endDate . ' 23:59:59',
        ]);

        // Search
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('merchant_code', 'like', "%{$this->search}%")
                    ->orWhere('reference_id', 'like', "%{$this->search}%")
                    ->orWhere('systemgenerated_TransId', 'like', "%{$this->search}%")
                    ->orWhere('customer_name', 'like', "%{$this->search}%");
            });
        }

        $this->totalCount = (clone $query)->count();
        $this->totalAmount = $query->sum('amount');
        $this->totalmdrfee = $query->sum('mdr_fee_amount');
        $this->totalNetAmount = $query->sum('net_amount');
    }

    public function getTransactions()
    {
        $query = DepositTransaction::query();

        // Role-based filter
        if (Session::get('auth')->role_name === 'Merchant') {
            $query->where('merchant_id', Session::get('auth')->merchant_id);
        } elseif (Session::get('auth')->role_name === 'Agent') {
            $query->where('agent_id', Session::get('auth')->agent_id);
        }

        // Status filter
        if ($this->statusFilter !== 'all') {
            $query->where('payment_status', $this->statusFilter);
        }

        // Date range
        $query->whereBetween('created_at', [
            $this->startDate . ' 00:00:00',
            $this->endDate . ' 23:59:59',
        ]);

        // Search filter
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('merchant_code', 'like', "%{$this->search}%")
                    ->orWhere('reference_id', 'like', "%{$this->search}%")
                    ->orWhere('systemgenerated_TransId', 'like', "%{$this->search}%")
                    ->orWhere('customer_name', 'like', "%{$this->search}%");
            });
        }

        return $query->orderByDesc('id')->paginate(10);
    }

    public function render()
    {
        $title = __('messages.Deposit Summary Report');

        // Always return a collection for the view
        $transactions = $this->getTransactions();

        return view('livewire.deposit-summary-report', [
            'transactionlist' => $transactions,
        ])->title($title);
    }
}

