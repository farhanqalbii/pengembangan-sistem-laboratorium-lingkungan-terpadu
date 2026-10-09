<?php

namespace App\Http\Controllers;

use App\Models\Sertifikat;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CertificateVerificationController extends Controller
{
    /**
     * Memverifikasi keabsahan Certificate of Analysis (COA) berdasarkan digital signature hash atau nomor sertifikat.
     */
    public function verify(Request $request, ?string $hash = null): View
    {
        $searchHash = $hash ?? $request->get('hash') ?? $request->get('code');

        $certificate = null;
        $isValid = false;

        if ($searchHash) {
            $certificate = Sertifikat::with(['sample.sampleParameters.parameter', 'customer', 'verifier'])
                ->where('hash_tanda_tangan', $searchHash)
                ->orWhere('nomor_sertifikat', $searchHash)
                ->first();

            if ($certificate && $certificate->isFinal()) {
                $isValid = true;
            }
        }

        return view('certificate.verify', compact('certificate', 'searchHash', 'isValid'));
    }
}
