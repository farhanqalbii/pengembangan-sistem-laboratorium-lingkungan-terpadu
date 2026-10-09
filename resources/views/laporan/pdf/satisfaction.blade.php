<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kepuasan Pelanggan</title>
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
        <h2>LAPORAN INDEKS KEPUASAN PELANGGAN (IKP) LABORATORIUM 2026</h2>
        <p style="font-size: 10px; margin: 3px 0 0 0;">Rata-Rata Rating: <strong>{{ $avgRating }} / 5.00 Bintang</strong></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Customer</th>
                <th>Jenis Layanan</th>
                <th>Rating</th>
                <th>Ulasan / Feedback</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($satisfactions as $i => $s)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td style="text-align: left;">{{ $s->customer->company_name ?? $s->customer->name }}</td>
                <td>{{ $s->service_type }}</td>
                <td style="font-weight: bold; color: #d97706;">{{ $s->rating }} / 5 ★</td>
                <td style="text-align: left;">{{ $s->feedback ?? '-' }}</td>
                <td>{{ $s->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
