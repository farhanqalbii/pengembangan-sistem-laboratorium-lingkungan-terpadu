<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\DetailPeminjaman;
use App\Models\LogAktivitas;
use App\Models\Notification;
use App\Models\Peminjaman;
use App\Models\Peralatan;
use App\Models\User;
use Illuminate\Http\Request;

class PeminjamanController extends Controller
{
    public function index(Request $request)
    {
        $query = Peminjaman::where('customer_id', auth()->id())
            ->with(['borrowingDetails.equipment', 'payments']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $borrowings = $query->latest()->paginate(10);

        return view('customer.peminjaman.index', compact('borrowings'));
    }

    public function create(Request $request)
    {
        $query = Peralatan::where('status', 'Tersedia')
            ->where('jumlah', '>', 0);

        if ($request->filled('category')) {
            $query->where('kategori', $request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('merk', 'like', "%{$search}%")
                    ->orWhere('kode', 'like', "%{$search}%");
            });
        }

        $equipmentList = $query->paginate(12)->withQueryString();
        $categories = Peralatan::distinct('kategori')->pluck('kategori');

        return view('customer.peminjaman.create', compact('equipmentList', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'equipment_id' => ['required', 'exists:peralatan,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'purpose' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $eq = Peralatan::findOrFail($validated['equipment_id']);

        if ($validated['quantity'] > $eq->jumlah) {
            return back()->withErrors(['quantity' => "Jumlah melebihi stok yang tersedia ({$eq->jumlah} unit)."])->withInput();
        }

        $borrowing = null;
        $code = '';

        \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $eq, &$borrowing, &$code) {
            $code = 'BRW-'.date('Ymd').'-'.sprintf('%03d', Peminjaman::whereDate('created_at', today())->count() + 1);

            $borrowing = Peminjaman::create([
                'borrow_code' => $code,
                'customer_id' => auth()->id(),
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'purpose' => $validated['purpose'],
                'notes' => $validated['notes'] ?? null,
                'status' => 'Diajukan',
                'deposit_amount' => $eq->deposit_amount * $validated['quantity'],
            ]);

            DetailPeminjaman::create([
                'borrowing_id' => $borrowing->id,
                'equipment_id' => $eq->id,
                'quantity' => $validated['quantity'],
            ]);

            LogAktivitas::record('Pengajuan Peminjaman', 'Peminjaman', "Pengajuan peminjaman alat {$eq->nama} ({$code})", auth()->id());

            // Notify admins/staff
            $staffUsers = User::whereIn('role', ['admin', 'staff'])->get();
            foreach ($staffUsers as $staff) {
                Notification::create([
                    'user_id' => $staff->id,
                    'title' => 'Pengajuan Peminjaman Alat',
                    'message' => "Pengajuan peminjaman {$code} dari ".auth()->user()->name,
                    'link' => '/admin/borrowings/'.$borrowing->id,
                ]);
            }
        });

        return redirect()->route('customer.borrowings.show', $borrowing)
            ->with('success', "Pengajuan peminjaman alat {$code} berhasil dikirim! Menunggu persetujuan admin.");
    }

    public function show(Peminjaman $borrowing)
    {
        if ($borrowing->customer_id !== auth()->id()) {
            abort(403, 'Akses ditolak.');
        }

        $borrowing->load(['borrowingDetails.equipment', 'payments.invoice']);

        return view('customer.peminjaman.show', compact('borrowing'));
    }

    public function requestExtension(Request $request, Peminjaman $borrowing)
    {
        if ($borrowing->customer_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'extended_end_date' => ['required', 'date', 'after:'.$borrowing->end_date->toDateString()],
        ]);

        $borrowing->update([
            'extension_requested' => true,
            'extended_end_date' => $validated['extended_end_date'],
            'extension_status' => 'Pending',
        ]);

        LogAktivitas::record('Request Perpanjangan Peminjaman', 'Peminjaman', "Request perpanjangan peminjaman {$borrowing->borrow_code} ke tanggal {$validated['extended_end_date']}", auth()->id());

        return back()->with('success', 'Permintaan perpanjangan pengembalian telah diajukan kepada admin.');
    }
}
