<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice - {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #1e293b; margin: 0; padding: 20px; }
        .header { border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 20px; }
        .header table { width: 100%; }
        .logo-title { font-size: 18px; font-weight: bold; color: #047857; text-transform: uppercase; }
        .inv-title { font-size: 20px; font-weight: bold; text-align: right; color: #0f172a; }
        
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 4px 0; vertical-align: top; }

        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th { background-color: #0f172a; color: #ffffff; padding: 8px; font-size: 11px; text-transform: uppercase; text-align: left; }
        .items-table td { padding: 8px; border-bottom: 1px solid #e2e8f0; }

        .total-box { width: 40%; float: right; margin-bottom: 30px; }
        .total-box table { width: 100%; border-collapse: collapse; }
        .total-box td { padding: 6px 8px; text-align: right; }
        .total-box tr.grand-total { background-color: #ecfdf5; font-weight: bold; font-size: 14px; color: #047857; }

        .badge-paid { background-color: #d1fae5; color: #065f46; font-weight: bold; padding: 4px 10px; border-radius: 4px; }
        .badge-unpaid { background-color: #ffe4e6; color: #9f1239; font-weight: bold; padding: 4px 10px; border-radius: 4px; }
    </style>
</head>
<body>

    <div class="header">
        <table>
            <tr>
                <td>
                    <div class="logo-title">UPTD LAB LINGKUNGAN 2026</div>
                    <div style="font-size: 10px; color: #64748b; margin-top: 3px;">Kementerian Lingkungan Hidup & Kehutanan RI</div>
                </td>
                <td>
                    <div class="inv-title">INVOICE</div>
                    <div style="text-align: right; font-size: 11px; color: #475569; margin-top: 3px;">No: {{ $invoice->invoice_number }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="info-table">
        <tr>
            <td style="width: 50%;">
                <strong style="color: #64748b; font-size: 10px; text-transform: uppercase;">Diterbitkan Untuk:</strong><br>
                <strong style="font-size: 13px;">{{ $customer->company_name ?? $customer->name }}</strong><br>
                {{ $customer->address ?? '-' }}<br>
                Telp: {{ $customer->phone }} | Email: {{ $customer->email }}
            </td>
            <td style="width: 50%; text-align: right;">
                <strong style="color: #64748b; font-size: 10px; text-transform: uppercase;">Detail Tagihan:</strong><br>
                Tanggal: {{ $invoice->created_at->format('d F Y') }}<br>
                Status: 
                @if($invoice->status === 'Lunas')
                    <span class="badge-paid">LUNAS</span>
                @else
                    <span class="badge-unpaid">BELUM LUNAS</span>
                @endif
                <br>
                Metode Pembayaran: <strong>{{ ($invoice->status === 'Lunas' && $payment && $payment->paid_at) ? $payment->payment_method : 'QRIS / Virtual Account / Transfer Bank' }}</strong>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>Deskripsi Layanan</th>
                <th style="text-align: center;">Jumlah / Referensi</th>
                <th style="text-align: right;">Harga Satuan</th>
                <th style="text-align: right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @if($payment && $payment->sample)
                @foreach($payment->sample->sampleParameters as $sp)
                <tr>
                    <td>Pengujian Parameter: <strong>{{ $sp->parameter->name ?? 'Parameter Uji' }}</strong> ({{ $sp->test_method }})</td>
                    <td style="text-align: center;">1 sampel</td>
                    <td style="text-align: right;">Rp {{ number_format($sp->parameter->price ?? 0, 0, ',', '.') }}</td>
                    <td style="text-align: right;">Rp {{ number_format($sp->parameter->price ?? 0, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            @elseif($payment && $payment->borrowing)
                @foreach($payment->borrowing->borrowingDetails as $detail)
                <tr>
                    <td>Sewa Alat: <strong>{{ $detail->equipment->name }}</strong> ({{ $payment->borrowing->start_date->format('d/m/Y') }} - {{ $payment->borrowing->end_date->format('d/m/Y') }})</td>
                    <td style="text-align: center;">{{ $detail->quantity }} unit</td>
                    <td style="text-align: right;">Rp {{ number_format($detail->equipment->rental_price_per_day, 0, ',', '.') }}/hari</td>
                    <td style="text-align: right;">Rp {{ number_format($detail->equipment->rental_price_per_day * $detail->quantity, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td>Tagihan Layanan Laboratorium Lingkungan ({{ $payment->type ?? 'Pengujian/Peminjaman' }})</td>
                    <td style="text-align: center;">1 paket</td>
                    <td style="text-align: right;">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
                    <td style="text-align: right;">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="total-box">
        <table>
            <tr>
                <td>Subtotal:</td>
                <td>Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
            </tr>
            @if($invoice->deposit > 0)
            <tr>
                <td>Deposit Peminjaman:</td>
                <td>Rp {{ number_format($invoice->deposit, 0, ',', '.') }}</td>
            </tr>
            @endif
            @if($invoice->fine > 0)
            <tr>
                <td>Denda / Penalty:</td>
                <td>Rp {{ number_format($invoice->fine, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr class="grand-total">
                <td>Total Tagihan:</td>
                <td>Rp {{ number_format($invoice->total, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div style="clear: both;"></div>

    <div style="margin-top: 40px; font-size: 11px; color: #64748b; border-top: 1px solid #e2e8f0; padding-top: 10px;">
        <p><strong>Informasi Pembayaran Bank:</strong><br>
        Bank BCA Cabang Utama - No. Rekening: <strong>883-0912-341</strong> a.n. UPTD Laboratorium Lingkungan Terpadu.<br>
        Harap sertakan nomor invoice ({{ $invoice->invoice_number }}) pada berita transfer.
        </p>
    </div>

</body>
</html>
