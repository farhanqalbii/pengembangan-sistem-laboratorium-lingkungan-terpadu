<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\DetailPeminjaman;
use App\Models\Invoice;
use App\Models\LogAktivitas;
use App\Models\Notification;
use App\Models\Pembayaran;
use App\Models\Peminjaman;
use App\Models\RiwayatStok;
use Carbon\Carbon;
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

        return view('staff.peminjaman.index', compact('borrowings'));
    }

    public function show(Peminjaman $borrowing)
    {
        $borrowing->load(['customer', 'borrowingDetails.equipment', 'payments.invoice']);

        return view('staff.peminjaman.show', compact('borrowing'));
    }

    public function approve(Peminjaman $borrowing)
    {
        DB::transaction(function () use ($borrowing) {
            $borrowing->update([
                'status' => 'Disetujui',
            ]);

            // Generate deposit payment request jika belum ada
            if ($borrowing->deposit_amount > 0 && ! $borrowing->payments()->where('tipe', 'Deposit Peminjaman')->exists()) {
                $payCode = 'PAY-'.date('Ymd').'-'.sprintf('%04d', Pembayaran::whereDate('created_at', today())->count() + 1);
                $payment = Pembayaran::create([
                    'kode_pembayaran' => $payCode,
                    'customer_id' => $borrowing->customer_id,
                    'id_peminjaman' => $borrowing->id,
                    'tipe' => 'Deposit Peminjaman',
                    'jumlah_bayar' => $borrowing->deposit_amount,
                    'metode_pembayaran' => 'Transfer Bank',
                    'status' => 'Menunggu Pembayaran',
                ]);

                $invCode = 'INV/'.date('Y/m').'/'.sprintf('%04d', Invoice::whereYear('created_at', date('Y'))->count() + 1);
                Invoice::create([
                    'invoice_number' => $invCode,
                    'customer_id' => $borrowing->customer_id,
                    'id_pembayaran' => $payment->id,
                    'subtotal' => $borrowing->deposit_amount,
                    'deposit' => $borrowing->deposit_amount,
                    'fine' => 0,
                    'total' => $borrowing->deposit_amount,
                    'status' => 'Belum Lunas',
                ]);
            }

            LogAktivitas::record('Persetujuan Peminjaman', 'Peminjaman', "Staf menyetujui pengajuan peminjaman {$borrowing->borrow_code} setelah memeriksa kesiapan fisik alat.", auth()->id());

            Notification::create([
                'user_id' => $borrowing->customer_id,
                'title' => 'Pengajuan Peminjaman Disetujui!',
                'message' => "Pengajuan peminjaman {$borrowing->borrow_code} telah diverifikasi dan disetujui staf. Silakan lakukan pembayaran deposit.",
                'link' => '/customer/borrowings/'.$borrowing->id,
            ]);
        });

        return back()->with('success', "Pengajuan peminjaman {$borrowing->borrow_code} berhasil disetujui.");
    }

    public function reject(Request $request, Peminjaman $borrowing)
    {
        $validated = $request->validate([
            'notes' => ['required', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($borrowing, $validated) {
            $borrowing->update([
                'status' => 'Ditolak',
                'catatan' => $validated['notes'],
            ]);

            LogAktivitas::record('Penolakan Peminjaman', 'Peminjaman', "Staf menolak peminjaman {$borrowing->borrow_code}: {$validated['notes']}", auth()->id());

            Notification::create([
                'user_id' => $borrowing->customer_id,
                'title' => 'Pengajuan Peminjaman Ditolak',
                'message' => "Pengajuan peminjaman {$borrowing->borrow_code} ditolak oleh staf. Alasan: {$validated['notes']}",
                'link' => '/customer/borrowings/'.$borrowing->id,
            ]);
        });

        return back()->with('success', "Pengajuan peminjaman {$borrowing->borrow_code} ditolak.");
    }

    public function processReturn(Request $request, Peminjaman $borrowing)
    {
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

        DB::transaction(function () use ($borrowing, $validated, &$totalFine, &$refund) {
            foreach ($validated['conditions'] as $cond) {
                $detail = DetailPeminjaman::with('equipment')->find($cond['detail_id']);
                if ($detail && (int) $detail->borrowing_id === (int) $borrowing->id) {
                    $detail->update([
                        'condition_return' => $cond['condition_return'],
                        'condition_notes' => $cond['notes'] ?? null,
                    ]);

                    // Restore equipment stock if condition is Baik or Rusak Ringan
                    if (in_array($cond['condition_return'], ['Baik', 'Rusak Ringan'])) {
                        $eq = $detail->equipment;
                        $oldQty = $eq->quantity;
                        $eq->increment('jumlah', $detail->quantity);

                        RiwayatStok::create([
                            'id_peralatan' => $eq->id,
                            'tipe' => 'Pengembalian',
                            'jumlah_sebelum' => $oldQty,
                            'jumlah_perubahan' => $detail->quantity,
                            'jumlah_setelah' => $eq->quantity,
                            'user_id' => auth()->id(),
                            'catatan' => "Pengembalian peminjaman {$borrowing->borrow_code} (Kondisi: {$cond['condition_return']})",
                        ]);
                    } else {
                        // Damaged/Lost fine penalty
                        if ($cond['condition_return'] === 'Rusak Berat') {
                            $totalFine += $detail->equipment->deposit_amount * 0.5 * $detail->quantity;
                        } elseif ($cond['condition_return'] === 'Hilang') {
                            $totalFine += $detail->equipment->deposit_amount * 1.0 * $detail->quantity;
                        }
                    }
                }
            }

            $deposit = (float) $borrowing->deposit_amount;
            $refund = max(0, $deposit - $totalFine);
            $refundStatus = $refund > 0 ? 'Diprofilkan' : 'Belum';

            $borrowing->update([
                'actual_return_date' => $validated['actual_return_date'],
                'status' => 'Dikembalikan',
                'fine_amount' => $totalFine,
                'refund_amount' => $refund,
                'refund_status' => $refundStatus,
            ]);

            if ($totalFine > 0) {
                // Generate fine payment record
                $payCode = 'PAY-'.date('Ymd').'-'.sprintf('%04d', Pembayaran::whereDate('created_at', today())->count() + 1);
                $payment = Pembayaran::create([
                    'payment_code' => $payCode,
                    'customer_id' => $borrowing->customer_id,
                    'borrowing_id' => $borrowing->id,
                    'type' => 'Denda Peminjaman',
                    'amount' => $totalFine,
                    'payment_method' => 'Transfer Bank',
                    'status' => 'Menunggu Pembayaran',
                ]);

                $invCode = 'INV/'.date('Y/m').'/'.sprintf('%04d', Invoice::whereYear('created_at', date('Y'))->count() + 1);
                Invoice::create([
                    'invoice_number' => $invCode,
                    'customer_id' => $borrowing->customer_id,
                    'payment_id' => $payment->id,
                    'subtotal' => 0,
                    'deposit' => $deposit,
                    'fine' => $totalFine,
                    'total' => $totalFine,
                    'status' => 'Belum Lunas',
                ]);
            }

            LogAktivitas::record('Pencatatan Pengembalian', 'Peminjaman', "Staf mencatat pengembalian alat {$borrowing->borrow_code}. Denda: Rp ".number_format($totalFine, 0, ',', '.'), auth()->id());

            Notification::create([
                'user_id' => $borrowing->customer_id,
                'title' => 'Pengembalian Alat Dicatat',
                'message' => "Pengembalian alat peminjaman {$borrowing->borrow_code} telah dicatat oleh staf laboratorium.",
                'link' => '/customer/borrowings/'.$borrowing->id,
            ]);
        });

        return redirect()->route('staff.borrowings.show', $borrowing)
            ->with('success', "Pengembalian alat {$borrowing->borrow_code} berhasil diproses. Denda: Rp ".number_format($totalFine, 0, ',', '.').', Deposit Refundable: Rp '.number_format($refund, 0, ',', '.'));
    }
}
