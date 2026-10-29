<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\DetailPeminjaman;
use App\Models\Invoice;
use App\Models\LogAktivitas;
use App\Models\Notification;
use App\Models\Pembayaran;
use App\Models\Peminjaman;
use App\Models\Peralatan;
use App\Models\RiwayatServis;
use App\Models\RiwayatStok;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeminjamanController extends Controller
{
    protected function authorizeStaff(): void
    {
        abort_unless(auth()->user() && auth()->user()->isStaff(), 403, 'Akses ditolak: Hanya Staf yang berwenang memproses operasional peminjaman alat.');
    }

    public function index(Request $request)
    {
        $this->authorizeStaff();

        $query = Peminjaman::with(['customer', 'borrowingDetails.equipment', 'payments']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode_peminjaman', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%")
                            ->orWhere('company_name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    })
                    ->orWhereHas('borrowingDetails.equipment', function ($eq) use ($search) {
                        $eq->where('nama', 'like', "%{$search}%")
                            ->orWhere('kode', 'like', "%{$search}%");
                    });
            });
        }

        $borrowings = $query->latest()->paginate(10)->withQueryString();

        return view('staff.peminjaman.index', compact('borrowings'));
    }

    public function show(Peminjaman $borrowing)
    {
        $this->authorizeStaff();

        $borrowing->load(['customer', 'borrowingDetails.equipment', 'payments.invoice']);

        return view('staff.peminjaman.show', compact('borrowing'));
    }

    public function approve(Peminjaman $borrowing)
    {
        $this->authorizeStaff();

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

                // Periksa ketersediaan stok fisik alat sebelum menyetujui
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

                // Generate deposit payment request jika belum ada
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

                LogAktivitas::record('Persetujuan Peminjaman', 'Peminjaman', "Staf menyetujui pengajuan peminjaman {$lockedBorrowing->borrow_code} setelah memeriksa kesiapan fisik alat.", auth()->id());

                Notification::create([
                    'user_id' => $lockedBorrowing->customer_id,
                    'title' => 'Pengajuan Peminjaman Disetujui!',
                    'message' => "Pengajuan peminjaman {$lockedBorrowing->borrow_code} telah diverifikasi dan disetujui staf. Silakan lakukan pembayaran deposit.",
                    'link' => '/customer/borrowings/'.($lockedBorrowing->kode_peminjaman ?? $lockedBorrowing->borrow_code),
                ]);
            });
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage())->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('success', "Pengajuan peminjaman {$borrowing->borrow_code} berhasil disetujui.");
    }

    public function reject(Request $request, Peminjaman $borrowing)
    {
        $this->authorizeStaff();

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

                LogAktivitas::record('Penolakan Peminjaman', 'Peminjaman', "Staf menolak peminjaman {$lockedBorrowing->borrow_code}: {$validated['notes']}", auth()->id());

                Notification::create([
                    'user_id' => $lockedBorrowing->customer_id,
                    'title' => 'Pengajuan Peminjaman Ditolak',
                    'message' => "Pengajuan peminjaman {$lockedBorrowing->borrow_code} ditolak oleh staf. Alasan: {$validated['notes']}",
                    'link' => '/customer/borrowings/'.($lockedBorrowing->kode_peminjaman ?? $lockedBorrowing->borrow_code),
                ]);
            });
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage())->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('success', "Pengajuan peminjaman {$borrowing->borrow_code} ditolak.");
    }

    public function processReturn(Request $request, Peminjaman $borrowing)
    {
        $this->authorizeStaff();

        if (! in_array($borrowing->status, ['Dipinjam', 'Terlambat'])) {
            return back()->with('error', "Pengembalian tidak dapat diproses karena status peminjaman saat ini adalah '{$borrowing->status}'.")
                ->withErrors(['status' => 'Status peminjaman tidak valid untuk pengembalian.']);
        }

        if (! $request->has('conditions') && $request->has('returns')) {
            $request->merge(['conditions' => $request->input('returns')]);
        }

        if (! $request->filled('actual_return_date')) {
            $request->merge(['actual_return_date' => date('Y-m-d')]);
        }

        $validated = $request->validate([
            'actual_return_date' => ['required', 'date'],
            'conditions' => ['required', 'array'],
            'conditions.*.detail_id' => ['required', 'exists:detail_peminjaman,id'],
            'conditions.*.condition_return' => ['required', 'in:Baik,Rusak Ringan,Rusak Berat,Hilang'],
            'conditions.*.notes' => ['nullable', 'string'],
            'fine_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $totalFine = (float) ($validated['fine_amount'] ?? 0);

        // Check for late days fine calculation
        $expectedEnd = Carbon::parse($borrowing->extended_end_date ?? $borrowing->end_date);
        $actualReturn = Carbon::parse($validated['actual_return_date']);
        if ($actualReturn->greaterThan($expectedEnd)) {
            $lateDays = $actualReturn->diffInDays($expectedEnd);
            $lateFine = $lateDays * 50000;
            $totalFine += $lateFine;
        }

        $refund = 0;

        try {
            DB::transaction(function () use ($borrowing, $validated, &$totalFine, &$refund) {
                $lockedBorrowing = Peminjaman::where('id', $borrowing->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! in_array($lockedBorrowing->status, ['Dipinjam', 'Terlambat'])) {
                    throw new \DomainException("Pengembalian tidak dapat diproses karena status peminjaman saat ini adalah '{$lockedBorrowing->status}'.");
                }

                foreach ($validated['conditions'] as $cond) {
                    $detail = DetailPeminjaman::where('id', $cond['detail_id'])
                        ->where('id_peminjaman', $lockedBorrowing->id)
                        ->first();

                    if ($detail) {
                        $detail->update([
                            'condition_return' => $cond['condition_return'],
                            'condition_notes' => $cond['notes'] ?? null,
                        ]);

                        // Restore equipment stock if condition is Baik or Rusak Ringan
                        if (in_array($cond['condition_return'], ['Baik', 'Rusak Ringan'])) {
                            $eq = Peralatan::where('id', $detail->id_peralatan)
                                ->lockForUpdate()
                                ->first();

                            if ($eq) {
                                $oldQty = $eq->quantity;
                                $eq->increment('jumlah', $detail->quantity);
                                $newQty = $eq->fresh()->quantity;

                                RiwayatStok::create([
                                    'id_peralatan' => $eq->id,
                                    'tipe' => 'Pengembalian',
                                    'jumlah_sebelum' => $oldQty,
                                    'jumlah_perubahan' => $detail->quantity,
                                    'jumlah_setelah' => $newQty,
                                    'user_id' => auth()->id(),
                                    'catatan' => "Pengembalian peminjaman {$lockedBorrowing->borrow_code} (Kondisi: {$cond['condition_return']})",
                                ]);
                            }
                        } else {
                            // Damaged/Lost fine penalty & audit record
                            $eq = Peralatan::where('id', $detail->id_peralatan)
                                ->lockForUpdate()
                                ->first();

                            if ($eq) {
                                if ($cond['condition_return'] === 'Rusak Berat') {
                                    $totalFine += $eq->deposit_amount * 0.5 * $detail->quantity;

                                    RiwayatServis::create([
                                        'id_peralatan' => $eq->id,
                                        'tanggal_servis' => $validated['actual_return_date'],
                                        'status' => 'Perlu Perbaikan',
                                        'teknisi' => 'Staff Pengembalian',
                                        'catatan' => 'Kerusakan pasca peminjaman '.$lockedBorrowing->borrow_code.': '.($cond['notes'] ?? 'Rusak Berat saat serah-terima pengembalian'),
                                    ]);

                                    RiwayatStok::create([
                                        'id_peralatan' => $eq->id,
                                        'tipe' => 'Rusak/Afkir',
                                        'jumlah_sebelum' => $eq->quantity,
                                        'jumlah_perubahan' => 0,
                                        'jumlah_setelah' => $eq->quantity,
                                        'user_id' => auth()->id(),
                                        'catatan' => "Unit tidak dipulihkan ke stok siap pakai karena Rusak Berat pada peminjaman {$lockedBorrowing->borrow_code}",
                                    ]);
                                } elseif ($cond['condition_return'] === 'Hilang') {
                                    $totalFine += $eq->deposit_amount * 1.0 * $detail->quantity;

                                    RiwayatStok::create([
                                        'id_peralatan' => $eq->id,
                                        'tipe' => 'Hilang',
                                        'jumlah_sebelum' => $eq->quantity,
                                        'jumlah_perubahan' => 0,
                                        'jumlah_setelah' => $eq->quantity,
                                        'user_id' => auth()->id(),
                                        'catatan' => "Unit hilang pada transaksi peminjaman {$lockedBorrowing->borrow_code}",
                                    ]);
                                }
                            }
                        }
                    }
                }

                $deposit = (float) $lockedBorrowing->deposit_amount;
                $refund = max(0, $deposit - $totalFine);
                $refundStatus = $refund > 0 ? 'Diprofilkan' : 'Belum';
                $excessFine = max(0, $totalFine - $deposit);

                $lockedBorrowing->update([
                    'actual_return_date' => $validated['actual_return_date'],
                    'status' => 'Dikembalikan',
                    'fine_amount' => $totalFine,
                    'refund_amount' => $refund,
                    'refund_status' => $refundStatus,
                ]);

                // Hanya tagihkan tagihan denda tambahan jika denda melebihi deposit jaminan (SRS BYR-F-002)
                if ($excessFine > 0) {
                    $existingFinePayment = Pembayaran::where('id_peminjaman', $lockedBorrowing->id)
                        ->whereIn('tipe', ['Denda Peminjaman', 'denda'])
                        ->first();

                    if (! $existingFinePayment) {
                        $payCode = 'PAY-'.date('Ymd').'-'.sprintf('%04d', Pembayaran::whereDate('created_at', today())->count() + 1);
                        $payment = Pembayaran::create([
                            'payment_code' => $payCode,
                            'customer_id' => $lockedBorrowing->customer_id,
                            'borrowing_id' => $lockedBorrowing->id,
                            'type' => 'Denda Peminjaman',
                            'amount' => $excessFine,
                            'payment_method' => 'Transfer Bank',
                            'status' => 'Menunggu Pembayaran',
                        ]);

                        $invCode = 'INV/'.date('Y/m').'/'.sprintf('%04d', Invoice::whereYear('created_at', date('Y'))->count() + 1);
                        Invoice::create([
                            'invoice_number' => $invCode,
                            'customer_id' => $lockedBorrowing->customer_id,
                            'payment_id' => $payment->id,
                            'subtotal' => 0,
                            'deposit' => 0,
                            'fine' => $excessFine,
                            'total' => $excessFine,
                            'status' => 'Belum Lunas',
                        ]);
                    }
                }

                LogAktivitas::record('Pencatatan Pengembalian', 'Peminjaman', "Staf mencatat pengembalian alat {$lockedBorrowing->borrow_code}. Denda: Rp ".number_format($totalFine, 0, ',', '.'), auth()->id());

                Notification::create([
                    'user_id' => $lockedBorrowing->customer_id,
                    'title' => 'Pengembalian Alat Dicatat',
                    'message' => "Pengembalian alat peminjaman {$lockedBorrowing->borrow_code} telah dicatat oleh staf laboratorium.",
                    'link' => '/customer/borrowings/'.($lockedBorrowing->kode_peminjaman ?? $lockedBorrowing->borrow_code),
                ]);
            });
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage())->withErrors(['status' => $e->getMessage()]);
        }

        return redirect()->route('staff.borrowings.show', $borrowing)
            ->with('success', "Pengembalian alat {$borrowing->borrow_code} berhasil diproses. Denda: Rp ".number_format($totalFine, 0, ',', '.').', Deposit Refundable: Rp '.number_format($refund, 0, ',', '.'));
    }
}
