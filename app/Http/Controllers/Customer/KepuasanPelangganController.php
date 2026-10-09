<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\KepuasanPelanggan;
use App\Models\LogAktivitas;
use App\Models\Peminjaman;
use App\Models\Sample;
use Illuminate\Http\Request;

class KepuasanPelangganController extends Controller
{
    public function create(Request $request)
    {
        $serviceType = $request->get('type', 'Pengujian'); // Pengujian or Peminjaman Alat
        $serviceId = $request->get('id');

        $completedSamples = Sample::where('customer_id', auth()->id())
            ->where('status', 'Selesai')
            ->latest()
            ->get();

        $completedBorrowings = Peminjaman::where('customer_id', auth()->id())
            ->whereIn('status', ['Dikembalikan', 'Selesai'])
            ->latest()
            ->get();

        $isEligible = false;
        $activeService = null;

        if ($serviceId) {
            if ($serviceType === 'Pengujian') {
                $activeService = $completedSamples->firstWhere('id', (int) $serviceId);
                $isEligible = ($activeService !== null);
            } elseif ($serviceType === 'Peminjaman Alat') {
                $activeService = $completedBorrowings->firstWhere('id', (int) $serviceId);
                $isEligible = ($activeService !== null);
            }
        } else {
            // Auto-select first completed service if available
            if ($completedSamples->isNotEmpty()) {
                $serviceType = 'Pengujian';
                $serviceId = $completedSamples->first()->id;
                $activeService = $completedSamples->first();
                $isEligible = true;
            } elseif ($completedBorrowings->isNotEmpty()) {
                $serviceType = 'Peminjaman Alat';
                $serviceId = $completedBorrowings->first()->id;
                $activeService = $completedBorrowings->first();
                $isEligible = true;
            }
        }

        $existing = null;
        if ($isEligible && $serviceId) {
            $existing = KepuasanPelanggan::where('customer_id', auth()->id())
                ->where('tipe_layanan', $serviceType)
                ->where('id_layanan', $serviceId)
                ->first();
        }

        return view('customer.kepuasan.create', compact(
            'serviceType',
            'serviceId',
            'existing',
            'isEligible',
            'activeService',
            'completedSamples',
            'completedBorrowings'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_type' => ['required', 'string', 'in:Pengujian,Peminjaman Alat'],
            'service_id' => ['required', 'integer'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'feedback' => ['nullable', 'string', 'max:1000'],
        ]);

        // Verify service completion per SRS rules
        if ($validated['service_type'] === 'Pengujian') {
            $sample = Sample::where('id', $validated['service_id'])
                ->where('customer_id', auth()->id())
                ->first();

            if (! $sample || $sample->status !== 'Selesai') {
                return redirect()->route('customer.dashboard')
                    ->with('error', 'Form kepuasan hanya dapat diisi untuk pengujian sampel yang telah selesai.');
            }
        } elseif ($validated['service_type'] === 'Peminjaman Alat') {
            $peminjaman = Peminjaman::where('id', $validated['service_id'])
                ->where('customer_id', auth()->id())
                ->first();

            if (! $peminjaman || ! in_array($peminjaman->status, ['Dikembalikan', 'Selesai'])) {
                return redirect()->route('customer.dashboard')
                    ->with('error', 'Form kepuasan hanya dapat diisi untuk peminjaman alat yang telah selesai atau dikembalikan.');
            }
        }

        KepuasanPelanggan::updateOrCreate(
            [
                'customer_id' => auth()->id(),
                'tipe_layanan' => $validated['service_type'],
                'id_layanan' => $validated['service_id'],
            ],
            [
                'rating' => $validated['rating'],
                'saran_masukan' => $validated['feedback'] ?? null,
            ]
        );

        LogAktivitas::record('Input Kepuasan Pelanggan', 'Kepuasan', "Customer memberikan rating {$validated['rating']}/5 bintang untuk layanan {$validated['service_type']}.", auth()->id());

        return redirect()->route('customer.dashboard')
            ->with('success', 'Terima kasih atas ulasan dan umpan balik yang Anda berikan!');
    }
}
