<?php

namespace App\Exports;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TransactionsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        public string $type = 'all',
        public ?string $from = null,
        public ?string $to = null,
    ) {}

    public function collection(): Collection
    {
        $uid = Auth::id();

        $incomes = DB::table('incomes as i')
            ->when($this->from && $this->to, fn($q) => $q->whereBetween('i.income_date', [$this->from.' 00:00:00', $this->to.' 23:59:59']))
            ->where('i.user_id', $uid)
            ->select([
                'i.id',
                DB::raw("'income' as type"),
                'i.amount',
                'i.note',
                DB::raw('NULL as category_name'),
                'i.income_date as transacted_at',
            ]);

        $expenses = DB::table('expenses as e')
            ->leftJoin('categories as c', 'c.id', '=', 'e.category_id')
            ->when($this->from && $this->to, fn($q) => $q->whereBetween('e.expense_date', [$this->from.' 00:00:00', $this->to.' 23:59:59']))
            ->where('e.user_id', $uid)
            ->select([
                'e.id',
                DB::raw("'expense' as type"),
                'e.amount',
                'e.note',
                'c.name as category_name',
                'e.expense_date as transacted_at',
            ]);

        if ($this->type === 'income') {
            $rows = $incomes;
        } elseif ($this->type === 'expense') {
            $rows = $expenses;
        } else {
            $rows = $incomes->unionAll($expenses);
        }

        return DB::query()->fromSub($rows, 't')
            ->orderBy('transacted_at')
            ->orderBy('id')
            ->get();
    }

    public function headings(): array
    {
        return ['Date', 'Type', 'Category', 'Note', 'Amount'];
    }

    public function map($row): array
    {
        return [
            \Carbon\Carbon::parse($row->transacted_at)->format('Y-m-d H:i'),
            ucfirst($row->type),
            $row->category_name ?? '—',
            $row->note,
            (float) $row->amount,
        ];
    }
}
