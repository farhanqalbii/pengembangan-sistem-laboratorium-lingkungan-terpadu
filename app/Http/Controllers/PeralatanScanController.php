<?php

namespace App\Http\Controllers;

use App\Models\Peralatan;
use Illuminate\Http\Request;

class PeralatanScanController extends Controller
{
    public function scan(Request $request)
    {
        return view('peralatan.scan');
    }

    public function detail($code)
    {
        $equipment = Peralatan::where('kode', $code)
            ->orWhere('qr_code', $code)
            ->orWhere('barcode', $code)
            ->with(['calibrations' => fn ($q) => $q->latest(), 'serviceHistories' => fn ($q) => $q->latest()])
            ->firstOrFail();

        return view('peralatan.detail', compact('equipment'));
    }
}
