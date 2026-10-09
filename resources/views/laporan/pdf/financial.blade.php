<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #1e293b; padding: 15px; }
        .header { text-align: center; border-bottom: 2px solid #047857; margin-bottom: 15px; padding-bottom: 8px; }
        .header h2 { margin: 0; color: #047857; text-transform: uppercase; font-size: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #0f172a; color: white; padding: 6px; border: 1px solid #0f172a; font-size: 10px; }
        td { border: 1px solid #cbd5e1; padding: 6px; text-align: center; }
        tr:nth-child(even) { background-color: #f8fafc; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN KEUANGAN & PEMBAYARAN LABORATORIUM 2026</h2>
        <p style="font-size: 10px; margin: 3px 0 0 0;">Periode: {{ $startDate }} s/d {{ $endDate }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Bayar</th>
                <th>Customer</th>
                <th>Jenis Transaksi</th>
                <th>Metode</th>
                <th>Status</th>
                <th>Jumlah Pembayaran</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $i => $p)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td><strong>{{ $p->payment_code }}</strong></td>
                <td style="text-align: left;">{{ $p->customer->company_name ?? $p->customer->name }}</td>
                <td>{{ $p->type }}</td>
                <td>{{ $p->payment_method }}</td>
                <td>{{ $p->status }}</td>
                <td style="text-align: right; font-weight: bold;">Rp {{ number_format($p->amount, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 20px; text-align: right; font-size: 13px; font-weight: bold; color: #047857;">
        Total Pendapatan Diterima: Rp {{ number_format($totalIncome, 0, ',', '.') }}
    </div>
</body>
</html>
