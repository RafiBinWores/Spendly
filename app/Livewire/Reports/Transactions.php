<?php

namespace App\Livewire\Reports;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Transactions extends Component
{
        use WithPagination;

    // Filters
    #[Url(except: 'all')] public string $type = 'all';  // all|income|expense
    #[Url] public ?string $from = null; // 'YYYY-MM-DD'
    #[Url] public ?string $to   = null; // 'YYYY-MM-DD'
    #[Url(except: 10)] public int $perPage = 10;

    public function mount(): void
    {
        // Set sensible defaults (this month) if no dates provided
        if (!$this->from || !$this->to) {
            $this->from = now()->startOfMonth()->toDateString();
            $this->to   = now()->endOfMonth()->toDateString();
        }
    }

    public function updated($prop): void
    {
        if (in_array($prop, ['type','from','to','perPage'], true)) {
            $this->resetPage();
        }
    }

    protected function baseUnion()
    {
        $uid = Auth::id();

        // Income side (no category)
        $incomes = DB::table('incomes as i')
            ->when($this->from && $this->to, fn($q) => $q->whereBetween('i.income_date', [$this->from.' 00:00:00', $this->to.' 23:59:59']))
            ->where('i.user_id', $uid)
            ->select([
                'i.id',
                DB::raw("'income' as type"),
                'i.amount',
                'i.note',
                DB::raw('NULL as category_id'),
                DB::raw('NULL as category_name'),
                'i.income_date as transacted_at',
            ]);

        // Expense side (has category)
        $expenses = DB::table('expenses as e')
            ->leftJoin('categories as c', 'c.id', '=', 'e.category_id')
            ->when($this->from && $this->to, fn($q) => $q->whereBetween('e.expense_date', [$this->from.' 00:00:00', $this->to.' 23:59:59']))
            ->where('e.user_id', $uid)
            ->select([
                'e.id',
                DB::raw("'expense' as type"),
                'e.amount',
                'e.note',
                'e.category_id',
                'c.name as category_name',
                'e.expense_date as transacted_at',
            ]);

        // Filter by type before union to keep pagination fast
        if ($this->type === 'income') {
            return $incomes;
        } elseif ($this->type === 'expense') {
            return $expenses;
        }

        // Merge both
        return $incomes->unionAll($expenses);
    }

    protected function rows()
    {
        $q = DB::query()->fromSub($this->baseUnion(), 't')
            ->orderByDesc('transacted_at')
            ->orderByDesc('id');

        return $q->paginate($this->perPage);
    }

    protected function totals(): array
    {
        $uid = Auth::id();

        $incomeQ = DB::table('incomes')->where('user_id', $uid);
        $expenseQ = DB::table('expenses')->where('user_id', $uid);

        if ($this->from && $this->to) {
            $incomeQ->whereBetween('income_date', [$this->from.' 00:00:00', $this->to.' 23:59:59']);
            $expenseQ->whereBetween('expense_date', [$this->from.' 00:00:00', $this->to.' 23:59:59']);
        }

        if ($this->type === 'income') {
            $totalIncome = (float) $incomeQ->sum('amount');
            return ['income' => $totalIncome, 'expense' => 0.0, 'balance' => $totalIncome];
        }
        if ($this->type === 'expense') {
            $totalExpense = (float) $expenseQ->sum('amount');
            return ['income' => 0.0, 'expense' => $totalExpense, 'balance' => -$totalExpense];
        }

        $totalIncome  = (float) $incomeQ->sum('amount');
        $totalExpense = (float) $expenseQ->sum('amount');

        return [
            'income'  => $totalIncome,
            'expense' => $totalExpense,
            'balance' => $totalIncome - $totalExpense,
        ];
    }

    public function getExportUrlsProperty(): array
    {
        $params = http_build_query([
            'type' => $this->type,
            'from' => $this->from,
            'to'   => $this->to,
        ]);

        return [
            'excel' => route('transactions.export_excel').'?'.$params,
            'pdf'   => route('transactions.export_pdf').'?'.$params,
        ];
    }

    
    public function render()
    {
                $rows   = $this->rows();
        $totals = $this->totals();

        return view('livewire.reports.transactions', compact('rows', 'totals'));
    }
}
