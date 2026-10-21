<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\LogAktivitas;
use App\Models\Notification;
use App\Models\Pembayaran;
use App\Models\Peminjaman;
use App\Models\Peralatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeminjamanController extends Controller
{
    public function index(Request $request)
    {
        $query = Peminjaman::with(['customer', 'borrowingDetails.equipment', 'payments']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $borrowings = $query->latest()->paginate(10);

        return view('admin.peminjaman.index', compact('borrowings'));
    }

    public function show(Peminjaman $borrowing)
    {
        $borrowing->load(['customer', 'borrowingDetails.equipment', 'payments.invoice']);

        return view('admin.peminjaman.show', compact('borrowing'));
    }

    public function approve(Peminjaman $borrowing)
    {
        if ($borrowing->status !== 'Diajukan') {
            return back()->with('error', "Pengajuan peminjaman tidak dapat disetujui karena status saat ini adalah '{$borrowing->status}'.")
                ->withErrors(['status' => "Status peminjaman bukan 'Diajukan'."]);
        }

        try {
            DB::transaction(function () use ($borrowing) {
                $lockedBorrowing = Peminjaman::where('id', $borrowing->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedBorrowing->status !== 'Diajukan') {
                    throw new \DomainException("Pengajuan peminjaman tidak dapat disetujui karena status saat ini adalah '{$lockedBorrowing->status}'.");
                }

                // Periksa status dan ketersediaan stok alat (memperhitungkan reservasi aktif) sebelum menyetujui
                foreach ($lockedBorrowing->borrowingDetails as $detail) {
                    $eq = Peralatan::where('id', $detail->id_peralatan)
                        ->lockForUpdate()
                        ->first();

                    if (! $eq) {
                        throw new \DomainException('Data alat tidak ditemukan.');
                    }

                    if ($eq->status !== 'Tersedia') {
                        throw new \DomainException("Alat '{$eq->name}' tidak tersedia untuk dipinjam (Status: {$eq->status}).");
                    }

                    $available = $eq->getAvailableStockExcludingBorrowing($lockedBorrowing->id);
                    if ($available < $detail->quantity) {
                        throw new \DomainException("Stok alat '{$eq->name}' tidak mencukupi untuk disetujui (Tersedia: {$available}, Dibutuhkan: {$detail->quantity}).");
                    }
                }

                $lockedBorrowing->update([
                    'status' => 'Disetujui',
                ]);

                // Generate deposit payment request
                if ($lockedBorrowing->deposit_amount > 0 && ! $lockedBorrowing->payments()->where('tipe', 'Deposit Peminjaman')->exists()) {
                    $payCode = 'PAY-'.date('Ymd').'-'.sprintf('%04d', Pembayaran::whereDate('created_at', today())->count() + 1);
                    $payment = Pembayaran::create([
                        'kode_pembayaran' => $payCode,
                        'customer_id' => $lockedBorrowing->customer_id,
                        'id_peminjaman' => $lockedBorrowing->id,
                        'tipe' => 'Deposit Peminjaman',
                        'jumlah_bayar' => $lockedBorrowing->deposit_amount,
                        'metode_pembayaran' => 'Transfer Bank',
                        'status' => 'Menunggu Pembayaran',
                    ]);

                    $invCode = 'INV/'.date('Y/m').'/'.sprintf('%04d', Invoice::whereYear('created_at', date('Y'))->count() + 1);
                    Invoice::create([
                        'invoice_number' => $invCode,
                        'customer_id' => $lockedBorrowing->customer_id,
                        'id_pembayaran' => $payment->id,
                        'subtotal' => $lockedBorrowing->deposit_amount,
                        'deposit' => $lockedBorrowing->deposit_amount,
                        'fine' => 0,
                        'total' => $lockedBorrowing->deposit_amount,
                        'status' => 'Belum Lunas',
                    ]);
                }

                LogAktivitas::record('Persetujuan Peminjaman', 'Peminjaman', "Admin menyetujui pengajuan peminjaman {$lockedBorrowing->borrow_code}", auth()->id());

                Notification::create([
                    'user_id' => $lockedBorrowing->customer_id,
                    'title' => 'Pengajuan Peminjaman Disetujui!',
                    'message' => "Pengajuan peminjaman {$lockedBorrowing->borrow_code} telah disetujui. Silakan lakukan pembayaran deposit.",
                    'link' => '/customer/borrowings/'.$lockedBorrowing->id,
                ]);
            });
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage())->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('success', "Pengajuan peminjaman {$borrowing->borrow_code} disetujui.");
    }

    public function reject(Request $request, Peminjaman $borrowing)
    {
        if ($borrowing->status !== 'Diajukan') {
            return back()->with('error', "Pengajuan peminjaman tidak dapat ditolak karena status saat ini adalah '{$borrowing->status}'.")
                ->withErrors(['status' => "Status peminjaman bukan 'Diajukan'."]);
        }

        $validated = $request->validate([
            'notes' => ['required', 'string', 'max:500'],
        ]);

        try {
            DB::transaction(function () use ($borrowing, $validated) {
                $lockedBorrowing = Peminjaman::where('id', $borrowing->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedBorrowing->status !== 'Diajukan') {
                    throw new \DomainException("Pengajuan peminjaman tidak dapat ditolak karena status saat ini adalah '{$lockedBorrowing->status}'.");
                }

                $lockedBorrowing->update([
                    'status' => 'Ditolak',
                    'catatan' => $validated['notes'],
                ]);

                LogAktivitas::record('Penolakan Peminjaman', 'Peminjaman', "Admin menolak peminjaman {$lockedBorrowing->borrow_code}: {$validated['notes']}", auth()->id());

                Notification::create([
                    'user_id' => $lockedBorrowing->customer_id,
                    'title' => 'Pengajuan Peminjaman Ditolak',
                    'message' => "Pengajuan peminjaman {$lockedBorrowing->borrow_code} ditolak. Alasan: {$validated['notes']}",
                    'link' => '/customer/borrowings/'.$lockedBorrowing->id,
                ]);
            });
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage())->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('success', "Pengajuan peminjaman {$borrowing->borrow_code} ditolak.");
    }

    public function approveExtension(Peminjaman $borrowing)
    {
        if ($borrowing->status !== 'Dipinjam') {
            return back()->with('error', "Perpanjangan tidak dapat disetujui karena status peminjaman saat ini adalah '{$borrowing->status}'.")
                ->withErrors(['status' => "Status peminjaman bukan 'Dipinjam'."]);
        }

        if (! $borrowing->extended_end_date || ! $borrowing->extension_requested || $borrowing->extension_status !== 'Pending') {
            return back()->with('error', 'Tidak ada permohonan perpanjangan yang valid atau sedang menunggu persetujuan.')
                ->withErrors(['status_perpanjangan' => 'Status perpanjangan bukan Pending.']);
        }

        try {
            DB::transaction(function () use ($borrowing) {
                $lockedBorrowing = Peminjaman::where('id', $borrowing->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedBorrowing->status !== 'Dipinjam') {
                    throw new \DomainException("Perpanjangan tidak dapat disetujui karena status peminjaman saat ini adalah '{$lockedBorrowing->status}'.");
                }

                if (! $lockedBorrowing->extended_end_date || ! $lockedBorrowing->extension_requested || $lockedBorrowing->extension_status !== 'Pending') {
                    throw new \DomainException('Tidak ada permohonan perpanjangan aktif yang menunggu persetujuan.');
                }

                $lockedBorrowing->update([
                    'tanggal_selesai' => $lockedBorrowing->extended_end_date,
                    'status_perpanjangan' => 'Disetujui',
                    'perpanjangan_diajukan' => false,
                ]);

                LogAktivitas::record('Persetujuan Perpanjangan', 'Peminjaman', "Admin menyetujui perpanjangan peminjaman {$lockedBorrowing->borrow_code} s.d {$lockedBorrowing->end_date->format('d/m/Y')}", auth()->id());

                Notification::create([
                    'user_id' => $lockedBorrowing->customer_id,
                    'title' => 'Perpanjangan Peminjaman Disetujui',
                    'message' => "Perpanjangan masa pinjam {$lockedBorrowing->borrow_code} disetujui hingga {$lockedBorrowing->end_date->format('d/m/Y')}.",
                    'link' => '/customer/borrowings/'.$lockedBorrowing->id,
                ]);
            });
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage())->withErrors(['status_perpanjangan' => $e->getMessage()]);
        }

        return back()->with('success', 'Perpanjangan masa peminjaman telah disetujui.');
    }
}
