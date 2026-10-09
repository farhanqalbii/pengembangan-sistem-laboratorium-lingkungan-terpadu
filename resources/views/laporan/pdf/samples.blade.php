<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pengujian Sampel</title>
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
        <h2>LAPORAN PENGUJIEN SAMPEL LABORATORIUM LINGKUNGAN 2026</h2>
        <p style="font-size: 10px; margin: 3px 0 0 0;">Periode: {{ $startDate }} s/d {{ $endDate }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Sampel</th>
                <th>Nama Customer</th>
                <th>Jenis Sampel</th>
                <th>Lokasi Sampling</th>
                <th>Tgl Pengajuan</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($samples as $i => $s)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td><strong>{{ $s->sample_code }}</strong></td>
                <td style="text-align: left;">{{ $s->customer->company_name ?? $s->customer->name }}</td>
                <td>{{ $s->sample_type }}</td>
                <td style="text-align: left;">{{ $s->sampling_location }}</td>
                <td>{{ $s->created_at->format('d/m/Y') }}</td>
                <td>{{ $s->status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
