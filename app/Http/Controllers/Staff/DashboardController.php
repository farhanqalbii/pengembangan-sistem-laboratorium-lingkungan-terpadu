<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Kalibrasi;
use App\Models\Peminjaman;
use App\Models\Peralatan;
use App\Models\Sample;
use App\Models\SampleParameter;

class DashboardController extends Controller
{
    public function index()
    {
        $assignedSamplesCount = SampleParameter::where('analyst_id', auth()->id())
            ->where('status', 'Draft')
            ->count();

        $incomingSamples = Sample::where('status', 'Diajukan')
            ->latest()
            ->get();

        $processingSamples = Sample::whereIn('status', ['Diterima', 'Diproses', 'Diuji'])
            ->with(['customer', 'sampleParameters.parameter'])
            ->latest()
            ->get();

        $upcomingCalibrations = Kalibrasi::whereDate('tanggal_kalibrasi_berikutnya', '<=', now()->addDays(30))
            ->with('equipment')
            ->orderBy('tanggal_kalibrasi_berikutnya')
            ->get();

        $lowStockEquipment = Peralatan::whereRaw('jumlah <= stok_minimum')->get();

        $pendingReturns = Peminjaman::where('status', 'Dipinjam')
            ->with(['customer', 'borrowingDetails.equipment'])
            ->orderBy('tanggal_selesai')
            ->get();

        return view('staff.dashboard', compact(
            'assignedSamplesCount',
            'incomingSamples',
            'processingSamples',
            'upcomingCalibrations',
            'lowStockEquipment',
            'pendingReturns'
        ));
    }
}
