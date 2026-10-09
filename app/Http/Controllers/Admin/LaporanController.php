<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KepuasanPelanggan;
use App\Models\Pembayaran;
use App\Models\Peminjaman;
use App\Models\Peralatan;
use App\Models\Sample;
use App\Services\SimpleXlsxService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function samples(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->endOfMonth()->toDateString());

        $samples = Sample::whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->with(['customer', 'sampleParameters.parameter', 'certificate'])
            ->latest()
            ->get();

        if ($request->get('export') === 'pdf') {
            $pdf = Pdf::loadView('laporan.pdf.samples', compact('samples', 'startDate', 'endDate'));

            return $pdf->download("Laporan_Sampel_{$startDate}_sd_{$endDate}.pdf");
        }

        if ($request->get('export') === 'excel') {
            return SimpleXlsxService::download("Laporan_Sampel_{$startDate}_sd_{$endDate}", [
                'Kode Sampel', 'Customer', 'Jenis Sampel', 'Lokasi', 'Tanggal Pengajuan', 'Status',
            ], $samples->map(fn ($s) => [
                $s->sample_code,
                $s->customer->name ?? '-',
                $s->sample_type,
                $s->sampling_location,
                $s->created_at->format('d/m/Y H:i'),
                $s->status,
            ]));
        }

        return view('admin.laporan.samples', compact('samples', 'startDate', 'endDate'));
    }

    public function inventory(Request $request)
    {
        $equipmentList = Peralatan::with(['calibrations', 'serviceHistories', 'stockHistories'])->get();

        if ($request->get('export') === 'pdf') {
            $pdf = Pdf::loadView('laporan.pdf.inventory', compact('equipmentList'));

            return $pdf->download('Laporan_Inventori_Alat.pdf');
        }

        if ($request->get('export') === 'excel') {
            return SimpleXlsxService::download('Laporan_Inventori_Alat', [
                'Kode Alat', 'Nama Alat', 'Kategori', 'Merk', 'Stok', 'Stok Min', 'Lokasi', 'Status', 'Sewa/Hari',
            ], $equipmentList->map(fn ($e) => [
                $e->code,
                $e->name,
                $e->category,
                $e->brand ?? '-',
                $e->quantity,
                $e->min_stock,
                $e->location,
                $e->status,
                number_format($e->rental_price_per_day, 0, ',', '.'),
            ]));
        }

        return view('admin.laporan.inventory', compact('equipmentList'));
    }

    public function borrowings(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->endOfMonth()->toDateString());

        $borrowings = Peminjaman::whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->with(['customer', 'borrowingDetails.equipment'])
            ->latest()
            ->get();

        if ($request->get('export') === 'pdf') {
            $pdf = Pdf::loadView('laporan.pdf.borrowings', compact('borrowings', 'startDate', 'endDate'));

            return $pdf->download("Laporan_Peminjaman_{$startDate}_sd_{$endDate}.pdf");
        }

        if ($request->get('export') === 'excel') {
            return SimpleXlsxService::download("Laporan_Peminjaman_{$startDate}_sd_{$endDate}", [
                'Kode Pinjam', 'Customer', 'Tgl Mulai', 'Tgl Pengembalian', 'Status', 'Deposit', 'Denda', 'Refund',
            ], $borrowings->map(fn ($b) => [
                $b->borrow_code,
                $b->customer->name ?? '-',
                $b->start_date->format('d/m/Y'),
                $b->end_date->format('d/m/Y'),
                $b->status,
                number_format($b->deposit_amount, 0, ',', '.'),
                number_format($b->fine_amount, 0, ',', '.'),
                number_format($b->refund_amount, 0, ',', '.'),
            ]));
        }

        return view('admin.laporan.borrowings', compact('borrowings', 'startDate', 'endDate'));
    }

    public function financial(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->endOfMonth()->toDateString());

        $payments = Pembayaran::whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->with(['customer', 'invoice'])
            ->latest()
            ->get();

        $totalIncome = $payments->where('status', 'Berhasil')->sum('jumlah_bayar');

        if ($request->get('export') === 'pdf') {
            $pdf = Pdf::loadView('laporan.pdf.financial', compact('payments', 'totalIncome', 'startDate', 'endDate'));

            return $pdf->download("Laporan_Keuangan_{$startDate}_sd_{$endDate}.pdf");
        }

        if ($request->get('export') === 'excel') {
            return SimpleXlsxService::download("Laporan_Keuangan_{$startDate}_sd_{$endDate}", [
                'Kode Bayar', 'Customer', 'Jenis Transaksi', 'Jumlah (Rp)', 'Metode', 'Status', 'Tgl Transaksi',
            ], $payments->map(fn ($p) => [
                $p->payment_code,
                $p->customer->name ?? '-',
                $p->type,
                number_format($p->amount, 0, ',', '.'),
                $p->payment_method,
                $p->status,
                $p->created_at->format('d/m/Y H:i'),
            ]));
        }

        return view('admin.laporan.financial', compact('payments', 'totalIncome', 'startDate', 'endDate'));
    }

    public function satisfaction(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->endOfMonth()->toDateString());

        $satisfactions = KepuasanPelanggan::with('customer')
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->latest()
            ->get();

        $avgRating = round($satisfactions->avg('rating') ?? 0, 2);

        if ($request->get('export') === 'pdf') {
            $pdf = Pdf::loadView('laporan.pdf.satisfaction', compact('satisfactions', 'avgRating', 'startDate', 'endDate'));

            return $pdf->download("Laporan_Kepuasan_Pelanggan_{$startDate}_sd_{$endDate}.pdf");
        }

        if ($request->get('export') === 'excel') {
            return SimpleXlsxService::download("Laporan_Kepuasan_Pelanggan_{$startDate}_sd_{$endDate}", [
                'Customer', 'Jenis Layanan', 'Rating (1-5)', 'Feedback', 'Tanggal',
            ], $satisfactions->map(fn ($s) => [
                $s->customer->name ?? '-',
                $s->service_type,
                $s->rating,
                $s->feedback ?? '-',
                $s->created_at->format('d/m/Y H:i'),
            ]));
        }

        return view('admin.laporan.satisfaction', compact('satisfactions', 'avgRating', 'startDate', 'endDate'));
    }
}
