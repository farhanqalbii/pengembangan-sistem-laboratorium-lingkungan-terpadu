<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\KepuasanPelanggan;
use App\Models\Sample;
use App\Models\SampleParameter;
use App\Models\Sertifikat;

class DashboardController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user() && auth()->user()->role === 'manager', 403, 'Akses ditolak. Dashboard ini hanya untuk Manajer Laboratorium.');

        $pendingVerifications = Sample::where('status', 'Menunggu Verifikasi')
            ->with(['customer', 'sampleParameters.parameter', 'sampleParameters.analyst'])
            ->latest()
            ->get();

        $revisionSamples = Sample::where('status', 'Revisi')
            ->with(['customer', 'sampleParameters.parameter'])
            ->latest()
            ->get();

        $activeSamples = Sample::whereNotIn('status', ['Selesai'])
            ->with(['customer', 'sampleParameters.parameter'])
            ->latest()
            ->get();

        $recentCertificates = Sertifikat::with(['sample', 'customer', 'verifier'])
            ->latest()
            ->take(5)
            ->get();

        // Satisfaction metrics
        $averageRating = KepuasanPelanggan::avg('rating') ?? 0;
        $avgRating = $averageRating;
        $totalFeedback = KepuasanPelanggan::count();
        $recentFeedback = KepuasanPelanggan::with('customer')->latest()->take(5)->get();

        $totalCertificates = Sertifikat::count();
        $testedParametersCount = SampleParameter::count();

        // Turnaround metrics
        $completedSamples = Sample::where('status', 'Selesai')->get();
        $totalDays = $completedSamples->sum(function ($s) {
            return $s->received_date && $s->updated_at ? $s->received_date->diffInDays($s->updated_at) : 1;
        });
        $avgTurnaround = $completedSamples->count() > 0 ? round($totalDays / $completedSamples->count(), 1) : 0;

        return view('manager.dashboard', compact(
            'pendingVerifications',
            'revisionSamples',
            'activeSamples',
            'recentCertificates',
            'averageRating',
            'avgRating',
            'totalCertificates',
            'testedParametersCount',
            'totalFeedback',
            'recentFeedback',
            'avgTurnaround'
        ));
    }
}
