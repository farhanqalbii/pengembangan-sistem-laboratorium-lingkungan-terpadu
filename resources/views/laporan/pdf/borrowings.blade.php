<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Peminjaman Alat</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #1e293b; padding: 15px; }
        .header { text-align: center; border-bottom: 2px solid #047857; margin-bottom: 15px; padding-bottom: 8px; }
        .header h2 { margin: 0; color: #047857; text-transform: uppercase; font-size: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #047857; color: white; padding: 6px; border: 1px solid #047857; font-size: 10px; }
        td { border: 1px solid #cbd5e1; padding: 6px; text-align: center; }
        tr:nth-child(even) { background-color: #f8fafc; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN PEMINJAMAN ALAT LABORATORIUM 2026</h2>
        <p style="font-size: 10px; margin: 3px 0 0 0;">Periode: {{ $startDate }} s/d {{ $endDate }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Pinjam</th>
                <th>Nama Customer</th>
                <th>Tgl Mulai</th>
                <th>Tgl Selesai</th>
                <th>Status</th>
                <th>Deposit (Rp)</th>
                <th>Denda (Rp)</th>
                <th>Refund (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($borrowings as $i => $b)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td><strong>{{ $b->borrow_code }}</strong></td>
                <td style="text-align: left;">{{ $b->customer->company_name ?? $b->customer->name }}</td>
                <td>{{ $b->start_date->format('d/m/Y') }}</td>
                <td>{{ $b->end_date->format('d/m/Y') }}</td>
                <td>{{ $b->status }}</td>
                <td style="text-align: right;">{{ number_format($b->deposit_amount, 0, ',', '.') }}</td>
                <td style="text-align: right;">{{ number_format($b->fine_amount, 0, ',', '.') }}</td>
                <td style="text-align: right;">{{ number_format($b->refund_amount, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
