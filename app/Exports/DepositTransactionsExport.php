<?php

namespace App\Exports;

use App\Models\DepositTransaction;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Session;
use App\Support\DateRangeFilter;

class DepositTransactionsExport implements FromView
{
    public $statusFilter, $dateFilter, $search;

    public function __construct($statusFilter, $dateFilter, $search)
    {
        $this->statusFilter = $statusFilter;
        $this->dateFilter   = $dateFilter;
        $this->search       = $search;
    }

    public function view(): View
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
            $query->where(function ($q) {
                $q->where('merchant_code', 'like', "%{$this->search}%")
                  ->orWhere('reference_id', 'like', "%{$this->search}%")
                  ->orWhere('systemgenerated_TransId', 'like', "%{$this->search}%")
                  ->orWhere('customer_name', 'like', "%{$this->search}%");
            });
        }

        $transactions = $query->orderByDesc('id')->get();

        return view('exports.deposit-transactions', [
            'transactionlist' => $transactions
        ]);
    }
}
