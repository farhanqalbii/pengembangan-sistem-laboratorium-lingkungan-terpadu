<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use App\Models\Notification;
use App\Models\Pembayaran;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KeuanganController extends Controller
{
    protected function authorizeAdmin(): void
    {
        abort_unless(auth()->user() && auth()->user()->isAdmin(), 403, 'Akses ditolak: Fitur keuangan hanya dapat diakses oleh Administrator.');
    }

    public function index(Request $request)
    {
        $this->authorizeAdmin();

        $query = Pembayaran::with(['customer', 'invoice', 'sample', 'borrowing']);

        if ($request->filled('type')) {
            $query->where('tipe', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payments = $query->latest()->paginate(10)->withQueryString();

        $totalIncome = Pembayaran::where('status', 'Berhasil')->sum('jumlah_bayar');
        $pendingPayments = Pembayaran::where('status', 'Menunggu Pembayaran')->get();
        $pendingRefunds = Peminjaman::where('biaya_pengembalian', '>', 0)
            ->where(function ($q) {
                $q->whereIn('status_pengembalian', ['Diprofilkan', 'Pending', 'Belum']);
            })
            ->get();

        return view('admin.keuangan.index', compact('payments', 'totalIncome', 'pendingPayments', 'pendingRefunds'));
    }

    public function processRefund(Request $request, Peminjaman $borrowing)
    {
        $this->authorizeAdmin();

        try {
            DB::transaction(function () use ($borrowing) {
                $lockedBorrowing = Peminjaman::where('id', $borrowing->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedBorrowing->status_pengembalian === 'Refunded' || $lockedBorrowing->refund_status === 'Refunded') {
                    throw new \DomainException('Deposit untuk peminjaman ini sudah pernah dicairkan (Refunded).');
                }

                if ($lockedBorrowing->refund_amount <= 0) {
                    throw new \DomainException('Peminjaman ini tidak memiliki deposit refundable.');
                }

                $lockedBorrowing->update([
                    'status_pengembalian' => 'Refunded',
                ]);

                // Record refund payment entry
                $payCode = 'PAY-RFD-'.date('Ymd').'-'.sprintf('%04d', Pembayaran::whereDate('created_at', today())->count() + 1);
                Pembayaran::create([
                    'kode_pembayaran' => $payCode,
                    'customer_id' => $lockedBorrowing->customer_id,
                    'id_peminjaman' => $lockedBorrowing->id,
                    'tipe' => 'Refund Deposit Peminjaman',
                    'jumlah_bayar' => $lockedBorrowing->refund_amount,
                    'metode_pembayaran' => 'Transfer Bank Refund',
                    'status' => 'Refunded',
                    'tanggal_bayar' => now(),
                ]);

                LogAktivitas::record('Proses Refund Deposit', 'Keuangan', 'Admin memproses refund deposit Rp '.number_format($lockedBorrowing->refund_amount, 0, ',', '.')." untuk peminjaman {$lockedBorrowing->borrow_code}", auth()->id());

                Notification::create([
                    'user_id' => $lockedBorrowing->customer_id,
                    'title' => 'Refund Deposit Berhasil!',
                    'message' => 'Pengembalian deposit (refund) sebesar Rp '.number_format($lockedBorrowing->refund_amount, 0, ',', '.')." untuk peminjaman {$lockedBorrowing->borrow_code} telah dikirim.",
                    'link' => '/customer/borrowings/'.($lockedBorrowing->kode_peminjaman ?? $lockedBorrowing->borrow_code),
                ]);
            });
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Refund deposit sebesar Rp '.number_format($borrowing->refund_amount, 0, ',', '.').' berhasil diproses!');
    }
}
