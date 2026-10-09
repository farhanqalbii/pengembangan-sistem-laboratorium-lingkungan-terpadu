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
        $invoice->load(['customer', 'payment.sample.sampleParameters.parameter', 'payment.borrowing.borrowingDetails.equipment']);

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'payment' => $invoice->payment,
            'customer' => $invoice->customer,
        ])->setPaper('a4', 'portrait');

        return $pdf;
    }
}
