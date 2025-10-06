<?php

namespace App\Livewire\Reports;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Transactions extends Component
{
    use WithPagination;

    // Existing filters
    #[Url(except: 'all')] public string $type = 'all';   // all|income|expense
    #[Url] public ?string $from = null;                  // 'YYYY-MM-DD'
    #[Url] public ?string $to   = null;                  // 'YYYY-MM-DD'
    #[Url(except: 10)] public int $perPage = 10;

    // NEW: Category filters
    #[Url(except: '')] public string $categoryId = '';     // keep as string to avoid 0/false gotchas
    #[Url(except: '')] public string $subcategoryId = '';  // keep as string

    // Options for selects
    public array $categoryOptions = [];
    public array $subcategories = [];

    public function mount(): void
    {
        // Set sensible defaults (this month) if no dates provided
        if (!$this->from || !$this->to) {
            $this->from = now()->startOfMonth()->toDateString();
            $this->to   = now()->endOfMonth()->toDateString();
        }

        // Preload categories
        $this->categoryOptions = Category::query()
            ->where('status', 'active')
            ->orderBy('name', 'asc')
            ->get(['id','name'])
            ->map(fn($c) => ['id' => (string)$c->id, 'name' => $c->name])
            ->toArray();

        // Preload dependent subcategories if category already set via URL
        $this->loadSubcategories();
    }

    public function updated($prop): void
    {
        // Reset page when any of these change
        if (in_array($prop, ['type', 'from', 'to', 'perPage', 'categoryId', 'subcategoryId'], true)) {
            $this->resetPage();
        }
    }

    public function updatedCategoryId(): void
    {
        // Reset subcategory when category changes
        $this->subcategoryId = '';
        $this->loadSubcategories();
        $this->resetPage();
    }

    protected function loadSubcategories(): void
    {
        if ($this->categoryId === '') {
            $this->subcategories = [];
            return;
        }

        $this->subcategories = SubCategory::query()
            ->where('status', 'active')
            ->where('category_id', $this->categoryId)
            ->orderBy('name', 'asc')
            ->get(['id','name'])
            ->map(fn($sc) => ['id' => (string)$sc->id, 'name' => $sc->name])
            ->toArray();
    }

    protected function baseUnion()
    {
        $uid = Auth::id();

        // Income side (no category/subcategory)
        $incomes = DB::table('incomes as i')
            ->when($this->from && $this->to, fn($q) => $q->whereBetween('i.income_date', [$this->from . ' 00:00:00', $this->to . ' 23:59:59']))
            ->where('i.user_id', $uid)
            ->select([
                'i.id',
                DB::raw("'income' as type"),
                'i.amount',
                'i.note',
                DB::raw('NULL as category_id'),
                DB::raw('NULL as category_name'),
                DB::raw('NULL as subcategory_id'),
                DB::raw('NULL as subcategory_name'),
                'i.income_date as transacted_at',
            ]);

        // Expense side (with category/subcategory)
        $expenses = DB::table('expenses as e')
            ->leftJoin('categories as c', 'c.id', '=', 'e.category_id')
            ->leftJoin('sub_categories as sc', 'sc.id', '=', 'e.subcategory_id')
            ->when($this->from && $this->to, fn($q) => $q->whereBetween('e.expense_date', [$this->from . ' 00:00:00', $this->to . ' 23:59:59']))
            ->when($this->categoryId !== '', fn($q) => $q->where('e.category_id', $this->categoryId))
            ->when($this->subcategoryId !== '', fn($q) => $q->where('e.subcategory_id', $this->subcategoryId))
            ->where('e.user_id', $uid)
            ->select([
                'e.id',
                DB::raw("'expense' as type"),
                'e.amount',
                'e.note',
                'e.category_id',
                'c.name as category_name',
                'e.subcategory_id',
                'sc.name as subcategory_name',
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

        $incomeQ  = DB::table('incomes')->where('user_id', $uid);
        $expenseQ = DB::table('expenses')->where('user_id', $uid);

        if ($this->from && $this->to) {
            $incomeQ->whereBetween('income_date',  [$this->from . ' 00:00:00', $this->to . ' 23:59:59']);
            $expenseQ->whereBetween('expense_date', [$this->from . ' 00:00:00', $this->to . ' 23:59:59']);
        }

        // Apply the same category filters to totals (only affects expenses)
        if ($this->categoryId !== '') {
            $expenseQ->where('category_id', $this->categoryId);
        }
        if ($this->subcategoryId !== '') {
            $expenseQ->where('subcategory_id', $this->subcategoryId);
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
            'type'          => $this->type,
            'from'          => $this->from,
            'to'            => $this->to,
            'category_id'   => $this->categoryId,
            'subcategory_id'=> $this->subcategoryId,
            'per_page'      => $this->perPage,
        ]);

        return [
            'excel' => route('transactions.export_excel') . '?' . $params,
            'pdf'   => route('transactions.export_pdf') . '?' . $params,
        ];
    }

    public function render()
    {
        $rows   = $this->rows();
        $totals = $this->totals();

        return view('livewire.reports.transactions', [
            'rows'            => $rows,
            'totals'          => $totals,
            'categoryOptions' => $this->categoryOptions,
            'subcategories'   => $this->subcategories,
        ]);
    }
}
