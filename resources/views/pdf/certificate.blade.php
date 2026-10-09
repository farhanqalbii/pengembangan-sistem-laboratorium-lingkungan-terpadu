<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sertifikat Hasil Uji - {{ $certificate->certificate_number }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #1e293b; margin: 0; padding: 20px; }
        .header { text-align: center; border-bottom: 3px double #168975; padding-bottom: 12px; margin-bottom: 20px; }
        .header h1 { font-size: 18px; margin: 0; color: #0e263e; text-transform: uppercase; letter-spacing: 1px; }
        .header h2 { font-size: 13px; margin: 4px 0 0 0; color: #168975; font-weight: normal; }
        .header p { font-size: 10px; margin: 4px 0 0 0; color: #64748b; }
        .title-box { text-align: center; margin-bottom: 20px; }
        .title-box h3 { font-size: 15px; text-transform: uppercase; margin: 0; text-decoration: underline; color: #0e263e; }
        .title-box p { font-size: 11px; margin: 3px 0 0 0; font-weight: bold; color: #168975; }
        
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 5px 8px; vertical-align: top; }
        .info-table td.label { font-weight: bold; width: 160px; color: #334155; }

        .results-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .results-table th { background-color: #0e263e; color: #ffffff; font-size: 11px; text-transform: uppercase; padding: 8px; border: 1px solid #0e263e; }
        .results-table td { padding: 8px; border: 1px solid #cbd5e1; text-align: center; }
        .results-table tr:nth-child(even) { background-color: #f8fafc; }
        
        .badge-success { background-color: #d3f5eb; color: #10564b; font-weight: bold; padding: 3px 8px; border-radius: 4px; font-size: 10px; }
        .badge-danger { background-color: #ffe4e6; color: #9f1239; font-weight: bold; padding: 3px 8px; border-radius: 4px; font-size: 10px; }
        
        .footer-sig { width: 100%; margin-top: 30px; }
        .footer-sig td { vertical-align: top; text-align: center; }
        .sig-box { width: 200px; display: inline-block; }
        .hash-code { font-family: monospace; font-size: 9px; color: #64748b; margin-top: 15px; word-break: break-all; }
    </style>
</head>
<body>

    <div class="header">
        <table style="width: 100%; border: none; margin-bottom: 5px;">
            <tr>
                <td style="width: 80px; text-align: left; vertical-align: middle; border: none; padding: 0;">
                    @if(file_exists(public_path('images/logo_lab_lgn_rgst.png')))
                        <img src="{{ public_path('images/logo_lab_lgn_rgst.png') }}" style="height: 52px; width: auto;">
                    @elseif(file_exists(public_path('images/logo_lab.png')))
                        <img src="{{ public_path('images/logo_lab.png') }}" style="height: 52px; width: auto;">
                    @endif
                </td>
                <td style="text-align: center; vertical-align: middle; border: none; padding: 0;">
                    <h1 style="margin: 0; font-size: 16px; color: #0e263e; text-transform: uppercase;">UPTD LABORATORIUM LINGKUNGAN TERPADU 2026</h1>
                    <h2 style="margin: 2px 0 0 0; font-size: 12px; color: #168975; font-weight: bold;">SERTIFIKAT HASIL UJI (CERTIFICATE OF ANALYSIS)</h2>
                    <p style="margin: 3px 0 0 0; font-size: 9px; color: #64748b;">Jl. Sains & Teknologi No. 45, Jakarta | Telp: (021) 555-0192 | Email: info@lab-lingkungan.go.id</p>
                </td>
                <td style="width: 80px; border: none; padding: 0;"></td>
            </tr>
        </table>
    </div>

    <div class="title-box">
        <h3>SERTIFIKAT HASIL UJI</h3>
        <p>Nomor: {{ $certificate->certificate_number }}</p>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">Nama Pemohon / Perusahaan</td>
            <td>: {{ $customer->company_name ?? $customer->name }}</td>
            <td class="label">Kode Sampel</td>
            <td>: <strong>{{ $sample->sample_code }}</strong></td>
        </tr>
        <tr>
            <td class="label">Alamat Pemohon</td>
            <td>: {{ $customer->address ?? '-' }}</td>
            <td class="label">Jenis Sampel</td>
            <td>: {{ $sample->sample_type }}</td>
        </tr>
        <tr>
            <td class="label">Lokasi Pengambilan</td>
            <td>: {{ $sample->sampling_location }}</td>
            <td class="label">Tanggal Diterima</td>
            <td>: {{ $sample->received_date ? $sample->received_date->format('d F Y') : '-' }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal Pengambilan</td>
            <td>: {{ $sample->sampling_date ? $sample->sampling_date->format('d F Y') : '-' }}</td>
            <td class="label">Tanggal Penerbitan COA</td>
            <td>: {{ $certificate->issued_at->format('d F Y') }}</td>
        </tr>
    </table>

    <table class="results-table">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th style="text-align: left;">Parameter Uji</th>
                <th style="text-align: left;">Metode Uji</th>
                <th>Satuan</th>
                <th>Baku Mutu</th>
                <th>Hasil Uji</th>
                <th>Status Kesesuaian</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sampleParameters as $index => $sp)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td style="text-align: left; font-weight: bold;">{{ $sp->parameter->name ?? '-' }}</td>
                <td style="text-align: left; font-size: 10px;">{{ $sp->test_method }}</td>
                <td>{{ $sp->unit ?? '-' }}</td>
                <td style="font-size: 10px;">{{ $sp->quality_standard_summary }}</td>
                <td style="font-weight: bold; color: #047857;">{{ $sp->test_value ?? 'N/A' }}</td>
                <td>
                    @if($sp->compliance_status === 'Memenuhi')
                        <span class="badge-success">Memenuhi</span>
                    @elseif($sp->compliance_status === 'Tidak Memenuhi')
                        <span class="badge-danger">Tidak Memenuhi</span>
                    @else
                        <span>{{ $sp->compliance_status }}</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p style="font-size: 10px; color: #475569; margin-bottom: 20px;">
        * Catatan: Hasil pengujian ini hanya berlaku untuk sampel yang diuji di UPTD Laboratorium Lingkungan Terpadu 2026.
    </p>

    <table class="footer-sig">
        <tr>
            <td style="width: 50%; text-align: left; padding-left: 20px;">
                <p style="font-size: 10px; color: #64748b;">Kode Keabsahan Digital (Digital Verification Hash):</p>
                <div class="hash-code">
                    SHA256: {{ $certificate->digital_signature_hash }}
                </div>
            </td>
            <td style="width: 50%; text-align: center;">
                <p>Jakarta, {{ $certificate->issued_at->format('d F Y') }}</p>
                <p><strong>Manajer Teknis Laboratorium</strong></p>
                <br><br><br>
                <p style="font-weight: bold; text-decoration: underline; color: #047857;">
                    {{ $certificate->verifier->name ?? 'Dr. Hendra Gunawan, M.Si' }}
                </p>
                <p style="font-size: 10px; color: #64748b;">NIP. 19780412 200312 1 002</p>
            </td>
        </tr>
    </table>

</body>
</html>
