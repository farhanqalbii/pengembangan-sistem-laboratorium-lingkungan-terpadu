<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfGeneratorService
{
    public static function generateCoaPdf(Certificate $certificate)
    {
        $certificate->load(['sample.customer', 'sample.sampleParameters.parameter.qualityStandard', 'sample.sampleParameters.analyst', 'verifier']);

        $pdf = Pdf::loadView('pdf.certificate', [
            'certificate' => $certificate,
            'sample' => $certificate->sample,
            'customer' => $certificate->customer,
            'sampleParameters' => $certificate->sample->sampleParameters,
        ])->setPaper('a4', 'portrait');

        return $pdf;
    }

    public static function generateInvoicePdf(Invoice $invoice)
    {
        $invoice->load([
            'customer',
            'payment.sample.sampleParameters.parameter',
            'payment.borrowing.borrowingDetails.equipment',
        ]);

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'payment' => $invoice->payment,
            'customer' => $invoice->customer,
            'terbilang' => self::terbilang($invoice->total),
        ])->setPaper('a4', 'portrait');

        return $pdf;
    }

    public static function terbilang(float|int|string $nilai): string
    {
        $nilai = (int) round((float) $nilai);
        if ($nilai <= 0) {
            return 'Nol Rupiah';
        }

        return trim(self::penyebut($nilai)).' Rupiah';
    }

    private static function penyebut(int $nilai): string
    {
        if ($nilai <= 0) {
            return '';
        }

        $huruf = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];

        if ($nilai < 12) {
            return ' '.$huruf[$nilai];
        }

        if ($nilai < 20) {
            return self::penyebut($nilai - 10).' Belas';
        }

        if ($nilai < 100) {
            return self::penyebut((int) ($nilai / 10)).' Puluh'.self::penyebut($nilai % 10);
        }

        if ($nilai < 200) {
            return ' Seratus'.self::penyebut($nilai - 100);
        }

        if ($nilai < 1000) {
            return self::penyebut((int) ($nilai / 100)).' Ratus'.self::penyebut($nilai % 100);
        }

        if ($nilai < 2000) {
            return ' Seribu'.self::penyebut($nilai - 1000);
        }

        if ($nilai < 1000000) {
            return self::penyebut((int) ($nilai / 1000)).' Ribu'.self::penyebut($nilai % 1000);
        }

        if ($nilai < 1000000000) {
            return self::penyebut((int) ($nilai / 1000000)).' Juta'.self::penyebut($nilai % 1000000);
        }

        if ($nilai < 1000000000000) {
            return self::penyebut((int) ($nilai / 1000000000)).' Miliar'.self::penyebut((int) fmod($nilai, 1000000000));
        }

        return '';
    }

    public static function formatPaymentMethod(?string $method): string
    {
        if (empty($method)) {
            return 'Menunggu Pembayaran';
        }

        $normalized = strtolower(trim($method));

        return match ($normalized) {
            'qris' => 'QRIS (GoPay / OVO / Dana / LinkAja)',
            'gopay' => 'GoPay / GoPay Later',
            'shopeepay' => 'ShopeePay',
            'bank_transfer' => 'Transfer Bank / Virtual Account (Midtrans)',
            'bca_va' => 'BCA Virtual Account',
            'bni_va' => 'BNI Virtual Account',
            'bri_va' => 'BRI Virtual Account',
            'mandiri_bill', 'echannel' => 'Mandiri Bill Payment',
            'permata_va' => 'Permata Virtual Account',
            'cimb_va' => 'CIMB Niaga Virtual Account',
            'credit_card' => 'Kartu Kredit / Debit Online',
            'cstore' => 'Convenience Store (Indomaret / Alfamart)',
            default => ucwords(str_replace(['_', '-'], ' ', $method)),
        };
    }
}
