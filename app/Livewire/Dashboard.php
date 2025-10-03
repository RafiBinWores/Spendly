<?php

namespace App\Livewire;

use App\Models\Expense;
use App\Models\Income;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithPagination;

    public ?string $range = 'this_month'; // today|this_week|this_month|this_year|all
    public int $perPage = 10;

    public float $totalIncome = 0;
    public float $totalExpense = 0;
    public float $balance = 0;

    protected $queryString = [
        'range'   => ['except' => 'this_month'],
        'page'    => ['except' => 1],
        'perPage' => ['except' => 10],
    ];

    public function updatingRange()
    {
        $this->resetPage();
    }

    protected function dateBounds(): array
    {
        $now = now();
        return match ($this->range) {
            'today'      => [$now->copy()->startOfDay(),  $now->copy()->endOfDay()],
            'this_week'  => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'this_month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'this_year'  => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            default      => [null, null],
        };
    }

    protected function computeTotals(): void
    {
        [$from, $to] = $this->dateBounds();
        $uid = Auth::id();

        $incomeQ = DB::table('incomes')->where('user_id', $uid);
        if ($from && $to) $incomeQ->whereBetween('income_date', [$from, $to]);
        $this->totalIncome = (float) $incomeQ->sum('amount');

        $expenseQ = DB::table('expenses')->where('user_id', $uid);
        if ($from && $to) $expenseQ->whereBetween('expense_date', [$from, $to]);
        $this->totalExpense = (float) $expenseQ->sum('amount');

        $this->balance = $this->totalIncome - $this->totalExpense;
    }

    protected function recentTransactions()
    {
        [$from, $to] = $this->dateBounds();
        $uid = Auth::id();

        // Incomes (no category)
        $incomes = DB::table('incomes as i')
            ->where('i.user_id', $uid)
            ->when($from && $to, fn($q) => $q->whereBetween('i.income_date', [$from, $to]))
            ->select([
                'i.id',
                DB::raw("'income' as type"),
                'i.amount',
                'i.note',
                DB::raw('NULL as category_id'),
                DB::raw('NULL as category_name'),
                'i.income_date as transacted_at',
            ]);

        // Expenses (has category)
        $expenses = DB::table('expenses as e')
            ->leftJoin('categories as c', 'c.id', '=', 'e.category_id')
            ->where('e.user_id', $uid)
            ->when($from && $to, fn($q) => $q->whereBetween('e.expense_date', [$from, $to]))
            ->select([
                'e.id',
                DB::raw("'expense' as type"),
                'e.amount',
                'e.note',
                'e.category_id',
                'c.name as category_name',
                'e.expense_date as transacted_at',
            ]);

        $union = $incomes->unionAll($expenses);
        $wrapped = DB::query()
            ->fromSub($union, 't')
            ->orderByDesc('transacted_at')
            ->orderByDesc('id');

        return $wrapped->paginate($this->perPage);
    }

    protected function formatForPeriod(string $period): string
    {
        return match ($period) {
            'hour'  => '%Y-%m-%d %H:00',
            'day'   => '%Y-%m-%d',
            'month' => '%Y-%m',
            default => '%Y-%m-%d',
        };
    }

    protected function chartLineData(): array
    {
        [$from, $to] = $this->dateBounds();
        $uid = Auth::id();

        $period = match ($this->range) {
            'today'      => 'hour',
            'this_week'  => 'day',
            'this_month' => 'day',
            'this_year'  => 'month',
            default      => 'month',
        };
        $fmt = $this->formatForPeriod($period);

        $incomes = DB::table('incomes')
            ->selectRaw("DATE_FORMAT(income_date, ?) as grp, SUM(amount) as total", [$fmt])
            ->where('user_id', $uid)
            ->when($from && $to, fn($q) => $q->whereBetween('income_date', [$from, $to]))
            ->groupBy('grp')
            ->pluck('total', 'grp');

        $expenses = DB::table('expenses')
            ->selectRaw("DATE_FORMAT(expense_date, ?) as grp, SUM(amount) as total", [$fmt])
            ->where('user_id', $uid)
            ->when($from && $to, fn($q) => $q->whereBetween('expense_date', [$from, $to]))
            ->groupBy('grp')
            ->pluck('total', 'grp');

        $labels = collect($incomes->keys())->merge($expenses->keys())->unique()->sort()->values();
        $incomeData  = $labels->map(fn($l) => (float) ($incomes[$l]  ?? 0));
        $expenseData = $labels->map(fn($l) => (float) ($expenses[$l] ?? 0));

        return [
            'labels'  => $labels->all(),
            'income'  => $incomeData->all(),
            'expense' => $expenseData->all(),
        ];
    }



    public function render()
    {
        $this->computeTotals();
        $transactions = $this->recentTransactions();

        $chartDataPie = [
            'labels' => ['Income', 'Expense', 'Balance'],
            'values' => [
                round($this->totalIncome, 2),
                round($this->totalExpense, 2),
                round(max(0, $this->balance), 2),
            ],
        ];

        $chartDataLine = $this->chartLineData();

        // send fresh datasets to the browser every render
        $this->dispatch('chart-pie',  data: $chartDataPie);
        $this->dispatch('chart-line', data: $chartDataLine);

        return view('livewire.dashboard', [
            'transactions'  => $transactions,
            'chartDataPie'  => $chartDataPie,
            'chartDataLine' => $chartDataLine,
        ]);
    }
}
