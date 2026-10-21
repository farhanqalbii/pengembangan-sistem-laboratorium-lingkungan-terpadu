<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice - {{ $invoice->invoice_number }}</title>
    <style>
        @page {
            margin: 24px 28px 24px 28px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        /* Kop / Header */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #0e263e;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .header-table td {
            vertical-align: middle;
            padding: 0;
        }
        .logo-box {
            width: 70px;
            text-align: left;
            vertical-align: middle;
        }
        .logo-img {
            height: 48px;
            width: auto;
        }
        .institution-box {
            padding-left: 10px;
            vertical-align: middle;
        }
        .institution-title {
            font-size: 14px;
            font-weight: bold;
            color: #0e263e;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .institution-sub {
            font-size: 10px;
            color: #047857;
            font-weight: 600;
            margin-top: 2px;
        }
        .institution-meta {
            font-size: 9px;
            color: #64748b;
            margin-top: 3px;
        }

        .doc-title-box {
            text-align: right;
            vertical-align: middle;
        }
        .doc-title {
            font-size: 18px;
            font-weight: bold;
            color: #0e263e;
            letter-spacing: 1px;
            margin: 0;
        }
        .doc-number {
            font-size: 11px;
            font-weight: bold;
            color: #047857;
            margin-top: 3px;
        }
        .doc-code {
            font-size: 9px;
            color: #64748b;
            margin-top: 2px;
        }

        /* Status Stamp Box */
        .stamp-box-paid {
            display: inline-block;
            border: 2px solid #059669;
            background-color: #ecfdf5;
            color: #065f46;
            padding: 4px 10px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 11px;
            text-align: center;
            letter-spacing: 0.5px;
        }
        .stamp-box-unpaid {
            display: inline-block;
            border: 2px dashed #dc2626;
            background-color: #fef2f2;
            color: #991b1b;
            padding: 4px 10px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 11px;
            text-align: center;
            letter-spacing: 0.5px;
        }

        /* Grid Informasi Tagihan & Pelanggan */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .info-table td {
            vertical-align: top;
            padding: 0;
        }
        .customer-card {
            width: 52%;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 12px;
        }
        .meta-card {
            width: 45%;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 12px;
        }
        .card-header-title {
            font-size: 9px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
            margin-bottom: 6px;
        }
        .card-main-title {
            font-size: 12px;
            font-weight: bold;
            color: #0e263e;
        }
        .card-item {
            font-size: 10px;
            color: #334155;
            margin-top: 3px;
        }

        /* Tabel Rincian Item Layanan */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 14px;
        }
        .items-table th {
            background-color: #0e263e;
            color: #ffffff;
            padding: 7px 8px;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid #0e263e;
        }
        .items-table td {
            padding: 7px 8px;
            border: 1px solid #cbd5e1;
            font-size: 10.5px;
            vertical-align: top;
        }
        .items-table tr.context-row td {
            background-color: #f1f5f9;
            color: #0e263e;
            font-weight: bold;
            font-size: 10px;
            border-left: 3px solid #047857;
            padding: 5px 8px;
        }
        .items-table tr.even-row td {
            background-color: #f8fafc;
        }

        /* Ringkasan & Terbilang */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            page-break-inside: avoid;
        }
        .summary-table td {
            vertical-align: top;
            padding: 0;
        }
        .terbilang-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 10px;
            margin-right: 14px;
        }
        .terbilang-label {
            font-size: 9px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        .terbilang-text {
            font-size: 10.5px;
            font-weight: 600;
            color: #047857;
            font-style: italic;
        }
        .payment-notes {
            margin-top: 8px;
            font-size: 9px;
            color: #475569;
            line-height: 1.35;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            overflow: hidden;
        }
        .totals-table td {
            padding: 5px 8px;
            font-size: 10.5px;
            border-bottom: 1px solid #e2e8f0;
        }
        .totals-table td.label-col {
            text-align: left;
            color: #475569;
            width: 50%;
        }
        .totals-table td.value-col {
            text-align: right;
            font-weight: 600;
            color: #1e293b;
            width: 50%;
        }
        .totals-table tr.grand-total-row td {
            background-color: #ecfdf5;
            color: #047857;
            font-weight: bold;
            font-size: 12px;
            border-top: 2px solid #047857;
            border-bottom: none;
        }

        /* Footer */
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 24px;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            page-break-inside: avoid;
        }
        .footer-table td {
            font-size: 8.5px;
            color: #64748b;
            vertical-align: top;
            padding: 0;
        }
    </style>
</head>
<body>

    <!-- KOP / HEADER RESMI LABORATORIUM -->
    <table class="header-table">
        <tr>
            <td class="logo-box">
                @if(file_exists(public_path('images/logo_lab_lgn_rgst.png')))
                    <img src="{{ public_path('images/logo_lab_lgn_rgst.png') }}" class="logo-img" alt="Logo Lab">
                @elseif(file_exists(public_path('images/logo_lab_navbar.png')))
                    <img src="{{ public_path('images/logo_lab_navbar.png') }}" class="logo-img" alt="Logo Lab">
                @elseif(file_exists(public_path('images/logo_icon.png')))
                    <img src="{{ public_path('images/logo_icon.png') }}" class="logo-img" alt="Logo Lab">
                @endif
            </td>
            <td class="institution-box">
                <div class="institution-title">UPTD LABORATORIUM LINGKUNGAN TERPADU 2026</div>
                <div class="institution-sub">Sistem Informasi Manajemen Laboratorium & Pengujian Lingkungan (LIMS)</div>
                <div class="institution-meta">Jl. Sains & Teknologi No. 45, Jakarta | Telp: (021) 555-0192 | Email: info@lab-lingkungan.go.id</div>
            </td>
            <td class="doc-title-box">
                <div class="doc-title">INVOICE</div>
                <div class="doc-number">No: {{ $invoice->invoice_number }}</div>
                @if($payment && $payment->kode_pembayaran)
                    <div class="doc-code">Order ID: {{ $payment->kode_pembayaran }}</div>
                @endif
            </td>
        </tr>
    </table>

    <!-- GRID INFORMASI PENAGIHAN & CUSTOMER -->
    <table class="info-table">
        <tr>
            <td class="customer-card">
                <div class="card-header-title">Diterbitkan Kepada (Customer / Mitra):</div>
                @if(!empty($customer->company_name))
                    <div class="card-main-title">{{ $customer->company_name }}</div>
                    <div class="card-item"><strong>PIC / Nama:</strong> {{ $customer->name }}</div>
                @else
                    <div class="card-main-title">{{ $customer->name }}</div>
                @endif
                <div class="card-item"><strong>Alamat:</strong> {{ $customer->address ?? '-' }}</div>
                <div class="card-item"><strong>Kontak:</strong> Telp/WA: {{ $customer->phone ?? '-' }} | Email: {{ $customer->email }}</div>
            </td>
            <td style="width: 3%;"></td>
            <td class="meta-card">
                <div class="card-header-title">Detail Tagihan & Pembayaran:</div>
                <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
                    <tr>
                        <td style="width: 45%; color: #64748b; padding: 2px 0;">Tanggal Terbit:</td>
                        <td style="font-weight: 600; padding: 2px 0;">{{ $invoice->created_at->locale('id')->translatedFormat('d F Y') }}</td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; padding: 2px 0;">Status Tagihan:</td>
                        <td style="padding: 2px 0;">
                            @if($invoice->status === 'Lunas')
                                <span class="stamp-box-paid">LUNAS / PAID</span>
                            @else
                                <span class="stamp-box-unpaid">BELUM LUNAS</span>
                            @endif
                        </td>
                    </tr>
                    @if($invoice->status === 'Lunas')
                        <tr>
                            <td style="color: #64748b; padding: 2px 0;">Waktu Lunas:</td>
                            <td style="font-weight: 600; color: #047857; padding: 2px 0;">
                                {{ ($payment && $payment->paid_at) ? $payment->paid_at->locale('id')->translatedFormat('d F Y, H:i') . ' WIB' : $invoice->updated_at->locale('id')->translatedFormat('d F Y') }}
                            </td>
                        </tr>
                        <tr>
                            <td style="color: #64748b; padding: 2px 0;">Kanal Bayar:</td>
                            <td style="font-weight: 600; padding: 2px 0;">
                                {{ \App\Services\PdfGeneratorService::formatPaymentMethod($payment->metode_pembayaran ?? $payment->payment_method ?? null) }}
                            </td>
                        </tr>
                        @if($payment && ($payment->referensi_pembayaran ?? $payment->payment_reference))
                        <tr>
                            <td style="color: #64748b; padding: 2px 0;">No. Referensi:</td>
                            <td style="font-family: monospace; font-size: 9.5px; color: #0e263e; padding: 2px 0;">
                                {{ $payment->referensi_pembayaran ?? $payment->payment_reference }}
                            </td>
                        </tr>
                        @endif
                    @else
                        <tr>
                            <td style="color: #64748b; padding: 2px 0;">Kanal Bayar:</td>
                            <td style="font-weight: 600; padding: 2px 0;">Midtrans Snap / Virtual Account</td>
                        </tr>
                        <tr>
                            <td style="color: #64748b; padding: 2px 0;">Jatuh Tempo:</td>
                            <td style="color: #991b1b; font-weight: 600; padding: 2px 0;">Sebelum Layanan Pengujian</td>
                        </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <!-- TABEL RINCIAN LAYANAN & TARIF -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 6%; text-align: center;">No</th>
                <th style="width: 54%; text-align: left;">Deskripsi Layanan & Rincian Pengujian</th>
                <th style="width: 12%; text-align: center;">Jumlah</th>
                <th style="width: 14%; text-align: right;">Tarif Satuan</th>
                <th style="width: 14%; text-align: right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @if($payment && $payment->sample)
                {{-- KASUS 1: PENGUJIAN SAMPEL --}}
                <tr class="context-row">
                    <td colspan="5">
                        Sampel: <strong>{{ $payment->sample->sample_code }}</strong> — {{ $payment->sample->sample_type }}
                        @if(!empty($payment->sample->sampling_location))
                            | Lokasi: {{ $payment->sample->sampling_location }}
                        @endif
                    </td>
                </tr>
                @forelse($payment->sample->sampleParameters as $idx => $sp)
                    <tr class="{{ $idx % 2 === 1 ? 'even-row' : '' }}">
                        <td style="text-align: center;">{{ $idx + 1 }}</td>
                        <td>
                            <strong>{{ $sp->parameter->name ?? 'Parameter Uji' }}</strong>
                            <div style="font-size: 9px; color: #64748b; margin-top: 2px;">
                                Metode Uji: {{ $sp->test_method ?? ($sp->parameter->test_method ?? 'Standar Nasional Indonesia (SNI)') }}
                            </div>
                        </td>
                        <td style="text-align: center;">1 sampel</td>
                        <td style="text-align: right;">Rp {{ number_format($sp->parameter->price ?? 0, 0, ',', '.') }}</td>
                        <td style="text-align: right;">Rp {{ number_format($sp->parameter->price ?? 0, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td style="text-align: center;">1</td>
                        <td>Paket Pengujian Sampel {{ $payment->sample->sample_code }} ({{ $payment->sample->sample_type }})</td>
                        <td style="text-align: center;">1 paket</td>
                        <td style="text-align: right;">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
                        <td style="text-align: right;">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforelse

            @elseif($payment && ($payment->tipe === 'Denda Peminjaman' || $payment->type === 'Denda Peminjaman' || ($invoice->fine > 0 && $invoice->subtotal == 0)))
                {{-- KASUS 2: DENDA PEMINJAMAN ALAT --}}
                <tr class="context-row">
                    <td colspan="5">
                        Denda / Penalti Peminjaman Alat: <strong>{{ $payment->borrowing->borrow_code ?? '-' }}</strong>
                    </td>
                </tr>
                <tr>
                    <td style="text-align: center;">1</td>
                    <td>
                        <strong>Denda Keterlambatan / Kondisi Pengembalian Alat Laboratorium</strong>
                        <div style="font-size: 9px; color: #64748b; margin-top: 2px;">
                            {{ $payment->borrowing->catatan ?? 'Sanksi keterlambatan pengembalian atau perbaikan alat sesuai berita acara pengembalian.' }}
                        </div>
                    </td>
                    <td style="text-align: center;">1 kejadian</td>
                    <td style="text-align: right;">Rp {{ number_format($invoice->fine, 0, ',', '.') }}</td>
                    <td style="text-align: right;">Rp {{ number_format($invoice->fine, 0, ',', '.') }}</td>
                </tr>

            @elseif($payment && $payment->borrowing)
                {{-- KASUS 3: DEPOSIT / SEWA PEMINJAMAN ALAT --}}
                @php
                    $startDate = $payment->borrowing->start_date;
                    $endDate = $payment->borrowing->end_date;
                    $durationDays = ($startDate && $endDate) ? max(1, $startDate->diffInDays($endDate)) : 1;
                @endphp
                <tr class="context-row">
                    <td colspan="5">
                        Peminjaman Alat: <strong>{{ $payment->borrowing->borrow_code }}</strong> |
                        Periode: {{ $startDate ? $startDate->format('d/m/Y') : '-' }} s/d {{ $endDate ? $endDate->format('d/m/Y') : '-' }} ({{ $durationDays }} hari)
                    </td>
                </tr>
                @forelse($payment->borrowing->borrowingDetails as $idx => $detail)
                    @php
                        $eq = $detail->equipment;
                        $depositUnit = $eq->biaya_deposit ?? $eq->deposit_amount ?? 0;
                        $itemSubtotal = $depositUnit * $detail->quantity;
                    @endphp
                    <tr class="{{ $idx % 2 === 1 ? 'even-row' : '' }}">
                        <td style="text-align: center;">{{ $idx + 1 }}</td>
                        <td>
                            <strong>{{ $eq->nama ?? $eq->name ?? 'Instrumen Laboratorium' }}</strong>
                            <div style="font-size: 9px; color: #64748b; margin-top: 2px;">
                                Kode: {{ $eq->kode ?? '-' }} | Kategori: {{ $eq->kategori ?? '-' }} | Deposit jaminan sewa
                            </div>
                        </td>
                        <td style="text-align: center;">{{ $detail->quantity }} unit</td>
                        <td style="text-align: right;">Rp {{ number_format($depositUnit, 0, ',', '.') }}</td>
                        <td style="text-align: right;">Rp {{ number_format($itemSubtotal, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td style="text-align: center;">1</td>
                        <td>Deposit Peminjaman Alat ({{ $payment->borrowing->borrow_code }})</td>
                        <td style="text-align: center;">1 paket</td>
                        <td style="text-align: right;">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
                        <td style="text-align: right;">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforelse

            @else
                {{-- KASUS 4: DEFAULT FALLBACK --}}
                <tr>
                    <td style="text-align: center;">1</td>
                    <td>Tagihan Layanan Laboratorium Lingkungan ({{ $payment->type ?? $payment->tipe ?? 'Pengujian/Peminjaman' }})</td>
                    <td style="text-align: center;">1 paket</td>
                    <td style="text-align: right;">Rp {{ number_format($invoice->subtotal > 0 ? $invoice->subtotal : $invoice->total, 0, ',', '.') }}</td>
                    <td style="text-align: right;">Rp {{ number_format($invoice->subtotal > 0 ? $invoice->subtotal : $invoice->total, 0, ',', '.') }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- RINGKASAN TAGIHAN & TERBILANG (2 KOLOM BEBAS FLOAT) -->
    <table class="summary-table">
        <tr>
            <!-- Kolom Kiri: Terbilang & Catatan Pembayaran -->
            <td style="width: 56%;">
                <div class="terbilang-box">
                    <div class="terbilang-label">Jumlah Terbilang:</div>
                    <div class="terbilang-text"># {{ $terbilang }} #</div>
                </div>

                <div class="payment-notes">
                    @if($invoice->status === 'Lunas')
                        <strong style="color: #047857;">Bukti Pembayaran Sah:</strong><br>
                        Pembayaran telah terverifikasi secara elektronik melalui Payment Gateway Midtrans. Invoice ini sah sebagai bukti pembayaran resmi layanan laboratorium.
                    @else
                        <strong>Petunjuk Pembayaran:</strong><br>
                        Pembayaran dapat dilakukan melalui Portal Pelanggan dengan integrasi <strong>Midtrans Snap</strong> (QRIS, Virtual Account BCA/BNI/BRI/Mandiri, E-Wallet) atau transfer manual ke rekening resmi: <strong>Bank BCA Cabang Utama - No. Rekening: 883-0912-341</strong> a.n. UPTD Laboratorium Lingkungan Terpadu.
                    @endif
                </div>
            </td>

            <td style="width: 4%;"></td>

            <!-- Kolom Kanan: Rincian Subtotal & Grand Total -->
            <td style="width: 40%;">
                <table class="totals-table">
                    <tr>
                        <td class="label-col">Subtotal Layanan:</td>
                        <td class="value-col">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @if($invoice->deposit > 0)
                    <tr>
                        <td class="label-col">Deposit Jaminan:</td>
                        <td class="value-col">Rp {{ number_format($invoice->deposit, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @if($invoice->fine > 0)
                    <tr>
                        <td class="label-col">Denda / Penalti:</td>
                        <td class="value-col">Rp {{ number_format($invoice->fine, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    <tr class="grand-total-row">
                        <td class="label-col" style="font-weight: bold; color: #047857;">Total Tagihan:</td>
                        <td class="value-col" style="font-weight: bold; color: #047857;">Rp {{ number_format($invoice->total, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- FOOTER RESMI & DISCLAIMER ELEKTRONIK -->
    <table class="footer-table">
        <tr>
            <td style="width: 70%;">
                <strong>Catatan Keabsahan:</strong> Dokumen tagihan ini diterbitkan secara otomatis oleh Sistem Informasi Manajemen Laboratorium Lingkungan Terpadu (LIMS). Keabsahan dokumen diakui secara elektronik berdasarkan UU ITE No. 11 Tahun 2008 dan sah tanpa tanda tangan basah.
            </td>
            <td style="width: 30%; text-align: right;">
                Waktu Cetak: {{ now()->locale('id')->translatedFormat('d F Y, H:i') }} WIB
            </td>
        </tr>
    </table>

</body>
</html>
