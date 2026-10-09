<?php

namespace App\Http\Controllers;

use App\Models\KepuasanPelanggan;
use App\Models\Parameter;
use App\Models\Peralatan;
use App\Models\Sample;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Tampilkan landing page utama Sistem Laboratorium Lingkungan Terpadu.
     */
    public function index(): View
    {
        $parameters = Parameter::with('qualityStandard')
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $equipments = Peralatan::where('status', 'Tersedia')
            ->take(6)
            ->get();

        $stats = [
            'total_parameters' => Parameter::count(),
            'total_equipments' => Peralatan::count(),
            'total_samples' => Sample::count(),
            'avg_rating' => round((float) (KepuasanPelanggan::avg('rating') ?: 4.9), 1),
            'total_feedback' => KepuasanPelanggan::count(),
        ];

        $testimonials = KepuasanPelanggan::with('customer')
            ->where('rating', '>=', 4)
            ->latest()
            ->take(3)
            ->get();

        return view('landing', compact('parameters', 'equipments', 'stats', 'testimonials'));
    }

    /**
     * Pelacakan status pengujian sampel publik berdasarkan kode sampel.
     */
    public function trackSample(Request $request): JsonResponse
    {
        $code = strtoupper(trim((string) $request->get('code', '')));

        if (empty($code)) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan masukkan kode sampel pengujian.',
            ], 422);
        }

        // Cek format kode dasar: harus memiliki pemisah strip dan panjang yang wajar
        $isStandardFormat = (bool) preg_match('/^[A-Z]{2,4}-\d{8}-\d{3,4}$/', $code);
        $hasCodePattern = (bool) preg_match('/^[A-Z0-9]{2,5}-[A-Z0-9\-]{5,20}$/', $code);

        if (! $isStandardFormat && ! $hasCodePattern) {
            return response()->json([
                'success' => false,
                'message' => "Format kode '{$code}' tidak sesuai. Format resmi kode sampel adalah SMP-YYYYMMDD-XXXX (contoh: SMP-20260901-0001).",
            ], 422);
        }

        // Jika user memasukkan kode peminjaman alat (BRW-...)
        if (str_starts_with($code, 'BRW-')) {
            return response()->json([
                'success' => false,
                'message' => "Kode '{$code}' adalah kode peminjaman alat lab, bukan pengujian sampel. Silakan cek menu peminjaman di portal customer.",
            ], 404);
        }

        $sample = Sample::with(['customer:id,name,company_name', 'sampleParameters.parameter', 'certificate'])
            ->where('sample_code', $code)
            ->first();

        if (! $sample) {
            return response()->json([
                'success' => false,
                'message' => "Data sampel dengan kode '{$code}' tidak ditemukan di sistem. Silakan periksa kembali kode pada bukti serah terima Anda.",
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'sample_code' => $sample->sample_code,
                'sample_type' => $sample->sample_type,
                'sampling_location' => $sample->sampling_location,
                'sampling_date' => $sample->sampling_date ? $sample->sampling_date->format('d M Y') : '-',
                'created_at' => $sample->created_at ? $sample->created_at->format('d M Y, H:i') : '-',
                'status' => $sample->status,
                'customer_company' => $sample->customer->company_name ?? ($sample->customer->name ?? 'Customer Umum'),
                'parameters_count' => $sample->sampleParameters->count(),
                'has_certificate' => $sample->certificate !== null,
                'certificate_number' => $sample->certificate?->nomor_sertifikat ?? null,
            ],
        ]);
    }
}
