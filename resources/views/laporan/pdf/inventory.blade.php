<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Inventori Alat</title>
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
        <h2>LAPORAN INVENTORI ALAT & KALIBRASI LABORATORIUM 2026</h2>
        <p style="font-size: 10px; margin: 3px 0 0 0;">Tanggal Cetak: {{ date('d F Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode</th>
                <th>Nama Alat</th>
                <th>Kategori</th>
                <th>Merk</th>
                <th>Stok</th>
                <th>Lokasi</th>
                <th>Status</th>
                <th>Harga Sewa/Hari</th>
            </tr>
        </thead>
        <tbody>
            @foreach($equipmentList as $i => $e)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td><strong>{{ $e->code }}</strong></td>
                <td style="text-align: left;">{{ $e->name }}</td>
                <td>{{ $e->category }}</td>
                <td>{{ $e->brand ?? '-' }}</td>
                <td>{{ $e->quantity }} unit</td>
                <td>{{ $e->location }}</td>
                <td>{{ $e->status }}</td>
                <td style="text-align: right;">Rp {{ number_format($e->rental_price_per_day, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
