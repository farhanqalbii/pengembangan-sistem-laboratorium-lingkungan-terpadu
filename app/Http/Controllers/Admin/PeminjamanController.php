<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\LogAktivitas;
use App\Models\Notification;
use App\Models\Pembayaran;
use App\Models\Peminjaman;
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
        DB::transaction(function () use ($borrowing) {
            $borrowing->update([
                'status' => 'Disetujui',
            ]);

            // Generate deposit payment request
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

            LogAktivitas::record('Persetujuan Peminjaman', 'Peminjaman', "Admin menyetujui pengajuan peminjaman {$borrowing->borrow_code}", auth()->id());

            Notification::create([
                'user_id' => $borrowing->customer_id,
                'title' => 'Pengajuan Peminjaman Disetujui!',
                'message' => "Pengajuan peminjaman {$borrowing->borrow_code} telah disetujui. Silakan lakukan pembayaran deposit.",
                'link' => '/customer/borrowings/'.$borrowing->id,
            ]);
        });

        return back()->with('success', "Pengajuan peminjaman {$borrowing->borrow_code} disetujui.");
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

            LogAktivitas::record('Penolakan Peminjaman', 'Peminjaman', "Admin menolak peminjaman {$borrowing->borrow_code}: {$validated['notes']}", auth()->id());

            Notification::create([
                'user_id' => $borrowing->customer_id,
                'title' => 'Pengajuan Peminjaman Ditolak',
                'message' => "Pengajuan peminjaman {$borrowing->borrow_code} ditolak. Alasan: {$validated['notes']}",
                'link' => '/customer/borrowings/'.$borrowing->id,
            ]);
        });

        return back()->with('success', "Pengajuan peminjaman {$borrowing->borrow_code} ditolak.");
    }

    public function approveExtension(Peminjaman $borrowing)
    {
        if ($borrowing->extended_end_date) {
            $borrowing->update([
                'tanggal_selesai' => $borrowing->extended_end_date,
                'status_perpanjangan' => 'Disetujui',
            ]);

            LogAktivitas::record('Persetujuan Perpanjangan', 'Peminjaman', "Admin menyetujui perpanjangan peminjaman {$borrowing->borrow_code} s.d {$borrowing->end_date->format('d/m/Y')}", auth()->id());

            Notification::create([
                'user_id' => $borrowing->customer_id,
                'title' => 'Perpanjangan Peminjaman Disetujui',
                'message' => "Perpanjangan masa pinjam {$borrowing->borrow_code} disetujui hingga {$borrowing->end_date->format('d/m/Y')}.",
                'link' => '/customer/borrowings/'.$borrowing->id,
            ]);

            return back()->with('success', 'Perpanjangan masa peminjaman telah disetujui.');
        }

        return back()->withErrors(['error' => 'Tidak ada tanggal perpanjangan diajukan.']);
    }
}
