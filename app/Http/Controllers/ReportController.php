<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    private function baseQuery(string $type, ?string $from, ?string $to)
    {
        $uid = Auth::id();

        $incomes = DB::table('incomes as i')
            ->when($from && $to, fn($q) => $q->whereBetween('i.income_date', [$from.' 00:00:00', $to.' 23:59:59']))
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
            ->when($from && $to, fn($q) => $q->whereBetween('e.expense_date', [$from.' 00:00:00', $to.' 23:59:59']))
            ->where('e.user_id', $uid)
            ->select([
                'e.id',
                DB::raw("'expense' as type"),
                'e.amount',
                'e.note',
                'c.name as category_name',
                'e.expense_date as transacted_at',
            ]);

        if ($type === 'income') {
            return $incomes;
        } elseif ($type === 'expense') {
            return $expenses;
        }

        return $incomes->unionAll($expenses);
    }

    public function exportExcel(Request $request)
    {
        // handled in section 3.2 (kept here for completeness)
        return app()->call([self::class, 'exportExcelImpl'], ['request' => $request]);
    }

    public function exportExcelImpl(Request $request)
    {
        $type = $request->string('type', 'all')->toString();
        $from = $request->input('from');
        $to   = $request->input('to');

        $file = 'transactions_'.now()->format('Ymd_His').'.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\TransactionsExport($type, $from, $to),
            $file
        );
    }

    public function exportPdf(Request $request)
    {
        $type = $request->string('type', 'all')->toString();
        $from = $request->input('from');
        $to   = $request->input('to');

        $rows = DB::query()->fromSub($this->baseQuery($type, $from, $to), 't')
            ->orderBy('transacted_at')
            ->orderBy('id')
            ->get();

        $totals = $this->totals($type, $from, $to);

        $pdf = Pdf::loadView('transactions.export.pdf', [
            'rows'  => $rows,
            'totals'=> $totals,
            'type'  => $type,
            'from'  => $from,
            'to'    => $to,
        ])->setPaper('a4', 'portrait');

        $file = 'transactions_'.now()->format('Ymd_His').'.pdf';
        return $pdf->download($file);
    }

    private function totals(string $type, ?string $from, ?string $to): array
    {
        $uid = Auth::id();

        $incomeQ = DB::table('incomes')->where('user_id', $uid);
        $expenseQ = DB::table('expenses')->where('user_id', $uid);

        if ($from && $to) {
            $incomeQ->whereBetween('income_date', [$from.' 00:00:00', $to.' 23:59:59']);
            $expenseQ->whereBetween('expense_date', [$from.' 00:00:00', $to.' 23:59:59']);
        }

        if ($type === 'income') {
            $ti = (float) $incomeQ->sum('amount');
            return ['income' => $ti, 'expense' => 0.0, 'balance' => $ti];
        }
        if ($type === 'expense') {
            $te = (float) $expenseQ->sum('amount');
            return ['income' => 0.0, 'expense' => $te, 'balance' => -$te];
        }

        $ti = (float) $incomeQ->sum('amount');
        $te = (float) $expenseQ->sum('amount');

        return ['income' => $ti, 'expense' => $te, 'balance' => $ti - $te];
    }
}
