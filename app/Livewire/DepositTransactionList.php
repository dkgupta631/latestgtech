<?php
namespace App\Livewire;
use Livewire\Component;
use Livewire\Attributes\Session as KeepInSession;

use App\Models\DepositTransaction;
use App\Exports\DepositTransactionsExport;
use Maatwebsite\Excel\Facades\Excel;
use Session;
use App\Support\DateRangeFilter;

class DepositTransactionList extends Component
{
    public $transactionlist, $totalAmount, $totalmdrfee, $totalNetAmount;
    // remembered per page in the session, so a refresh keeps the selected filters
    #[KeepInSession]
    public $statusFilter = 'all';
    #[KeepInSession]
    public $dateFilter = 'today';
    public $search = '';

    public function mount()
    {
        $this->getTransactions();
    }

    public function exportExcel()
    {
        return Excel::download(
            new DepositTransactionsExport($this->statusFilter, $this->dateFilter, $this->search),
            'deposit_transactions.xlsx'
        );
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['statusFilter', 'dateFilter', 'search'])) {
            $this->getTransactions();
        }
    }

    public function getTransactions()
    {
        $query = DepositTransaction::query();

        // Role-based filtering
        if (Session::get('auth')->role_name === 'Merchant') {
            $query->where('merchant_id', Session::get('auth')->merchant_id);
        } elseif (Session::get('auth')->role_name === 'Agent') {
            $query->where('agent_id', Session::get('auth')->agent_id);
        }

        // Status filter
        if ($this->statusFilter !== 'all') {
            $query->where('payment_status', $this->statusFilter);
        }

        // Date filter
        DateRangeFilter::apply($query, $this->dateFilter);

        // Search filter
        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('merchant_code', 'like', "%{$this->search}%")
                  ->orWhere('reference_id', 'like', "%{$this->search}%")
                  ->orWhere('systemgenerated_TransId', 'like', "%{$this->search}%")
                  ->orWhere('customer_name', 'like', "%{$this->search}%");
            });
        }

        // Totals = successful transactions within the current filters
        $success = (clone $query)->where('payment_status', 'success');
        $this->totalAmount = (clone $success)->sum('amount');
        $this->totalmdrfee = (clone $success)->sum('mdr_fee_amount');
        $this->totalNetAmount = (clone $success)->sum('net_amount');

        $this->transactionlist = $query->orderByDesc('id')->get();
    }


    public function render()
    {
        $title =   __('messages.Deposit Transactions');
        return view('livewire.deposit-transaction-list')->title($title);
    }
}
