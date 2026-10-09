<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use App\Models\Notification;
use App\Models\Pembayaran;
use App\Models\Peminjaman;
use Illuminate\Http\Request;

class KeuanganController extends Controller
{
    public function index(Request $request)
    {
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
        if ($borrowing->refund_amount <= 0) {
            return back()->withErrors(['error' => 'Peminjaman ini tidak memiliki deposit refundable.']);
        }

        $borrowing->update([
            'status_pengembalian' => 'Refunded',
        ]);

        // Record refund payment entry
        $payCode = 'PAY-RFD-'.date('Ymd').'-'.sprintf('%04d', Pembayaran::whereDate('created_at', today())->count() + 1);
        Pembayaran::create([
            'kode_pembayaran' => $payCode,
            'customer_id' => $borrowing->customer_id,
            'id_peminjaman' => $borrowing->id,
            'tipe' => 'Refund Deposit Peminjaman',
            'jumlah_bayar' => $borrowing->refund_amount,
            'metode_pembayaran' => 'Transfer Bank Refund',
            'status' => 'Refunded',
            'tanggal_bayar' => now(),
        ]);

        LogAktivitas::record('Proses Refund Deposit', 'Keuangan', 'Admin memproses refund deposit Rp '.number_format($borrowing->refund_amount, 0, ',', '.')." untuk peminjaman {$borrowing->borrow_code}", auth()->id());

        Notification::create([
            'user_id' => $borrowing->customer_id,
            'title' => 'Refund Deposit Berhasil!',
            'message' => 'Pengembalian deposit (refund) sebesar Rp '.number_format($borrowing->refund_amount, 0, ',', '.')." untuk peminjaman {$borrowing->borrow_code} telah dikirim.",
            'link' => '/customer/borrowings/'.$borrowing->id,
        ]);

        return back()->with('success', 'Refund deposit sebesar Rp '.number_format($borrowing->refund_amount, 0, ',', '.').' berhasil diproses!');
    }
}
