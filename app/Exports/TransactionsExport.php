<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class TransactionsExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithColumnFormatting,
    WithColumnWidths,
    WithStyles,
    ShouldAutoSize
{
    private int $rowNum = 0; // for SL no.

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
        // SL added, Amount column stays numeric so we can format with currency
        return ['SL', 'Date', 'Type', 'Category', 'Note', 'Amount'];
    }

    public function map($row): array
    {
        $this->rowNum++;

        return [
            $this->rowNum, // SL
            \Carbon\Carbon::parse($row->transacted_at)->format('Y-m-d H:i'),
            ucfirst($row->type),
            $row->category_name ?? '—',
            (string) $row->note,
            (float) $row->amount, // keep numeric for currency formatting
        ];
    }

    /** Add BDT symbol in Excel cell formatting and a decent date format */
    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_DATE_YYYYMMDD . ' ' . NumberFormat::FORMAT_DATE_TIME3, // Date
            'F' => '"৳" #,##0.00', // Amount with BDT symbol (keeps numeric)
        ];
    }

    /** Fix Note column width + tidy others */
    public function columnWidths(): array
    {
        return [
            'A' => 6,   // SL
            'B' => 18,  // Date
            'C' => 10,  // Type
            'D' => 20,  // Category
            'E' => 60,  // Note
            'F' => 18,  // Amount
        ];
    }

    /** Optional: right-align Amount, wrap Note */
    public function styles(Worksheet $sheet)
    {
        // Right align entire Amount column
        $sheet->getStyle('F:F')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        // Wrap Note column
        $sheet->getStyle('E:E')->getAlignment()->setWrapText(true);

        // Bold headings
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);

        return [];
    }
}
