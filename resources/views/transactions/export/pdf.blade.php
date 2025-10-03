<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Transactions Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 18px; margin: 0 0 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; }
        th { background: #f2f2f2; text-align: left; }
        .right { text-align: right; }
        .muted { color: #666; }
        .summary { margin-top: 12px; }
    </style>
</head>
<body>
    <h1>Transactions Report</h1>
    <div class="muted">
        Type: {{ ucfirst($type) }} |
        Date: {{ $from ?? '—' }} to {{ $to ?? '—' }}
    </div>

    <table>
        <thead>
            <tr>
                <th>SL</th>
                <th>Date</th>
                <th>Type</th>
                <th>Category</th>
                <th>Note</th>
                <th class="right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $r)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ \Carbon\Carbon::parse($r->transacted_at)->format('Y-m-d H:i') }}</td>
                    <td>{{ ucfirst($r->type) }}</td>
                    <td>{{ $r->category_name ?? '—' }}</td>
                    <td>{{ $r->note }}</td>
                    <td class="right">Tk. {{ number_format($r->amount, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="muted">No data found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        <strong>Total Income:</strong> Tk. {{ number_format($totals['income'], 2) }} |
        <strong>Total Expense:</strong> Tk. {{ number_format($totals['expense'], 2) }} |
        <strong>Balance:</strong> Tk. {{ number_format($totals['balance'], 2) }}
    </div>
</body>
</html>
