<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Sertifikat;
use App\Services\PdfGeneratorService;

class SertifikatController extends Controller
{
    public function index()
    {
        $certificates = Sertifikat::where('customer_id', auth()->id())
            ->with(['sample', 'verifier'])
            ->latest()
            ->paginate(10);

        return view('customer.sertifikat.index', compact('certificates'));
    }

    public function show(Sertifikat $certificate)
    {
        if ($certificate->customer_id !== auth()->id()) {
            abort(403, 'Akses ditolak.');
        }

        $certificate->load(['sample.sampleParameters.parameter.qualityStandard', 'sample.customer', 'verifier']);

        return view('customer.sertifikat.show', compact('certificate'));
    }

    public function download(Sertifikat $certificate)
    {
        if ($certificate->customer_id !== auth()->id() && ! in_array(auth()->user()->role, ['admin', 'manager', 'staff'])) {
            abort(403, 'Akses ditolak.');
        }

        $pdf = PdfGeneratorService::generateCoaPdf($certificate);

        $filename = str_replace('/', '_', $certificate->certificate_number).'.pdf';

        return $pdf->download($filename);
    }
}
