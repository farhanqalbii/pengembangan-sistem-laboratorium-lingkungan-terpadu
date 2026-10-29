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
use Illuminate\Support\Facades\DB;

class PeminjamanController extends Controller
{
    protected function authorizeCustomer(): void
    {
        abort_unless(auth()->user() && auth()->user()->isCustomer(), 403, 'Akses ditolak: Hanya Customer yang berwenang mengakses fitur peminjaman ini.');
    }

    public function index(Request $request)
    {
        $this->authorizeCustomer();

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
        $this->authorizeCustomer();

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
        $this->authorizeCustomer();

        $validated = $request->validate([
            'equipment_id' => ['required', 'exists:peralatan,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'purpose' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $eq = Peralatan::findOrFail($validated['equipment_id']);

        if ($eq->status !== 'Tersedia') {
            return back()->withErrors(['equipment_id' => "Alat tidak dapat dipinjam karena status saat ini adalah '{$eq->status}'."])->withInput();
        }

        $availableStock = $eq->available_stock;
        if ($validated['quantity'] > $availableStock) {
            return back()->withErrors(['quantity' => "Jumlah melebihi stok yang tersedia ({$availableStock} unit tersedia)."])->withInput();
        }

        $borrowing = null;
        $code = '';

        try {
            DB::transaction(function () use ($validated, &$borrowing, &$code) {
                $lockedEq = Peralatan::where('id', $validated['equipment_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedEq->status !== 'Tersedia') {
                    throw new \DomainException("Alat tidak dapat dipinjam karena status saat ini adalah '{$lockedEq->status}'.");
                }

                $availableStockLocked = $lockedEq->available_stock;
                if ($validated['quantity'] > $availableStockLocked) {
                    throw new \DomainException("Jumlah melebihi stok yang tersedia ({$availableStockLocked} unit tersedia).");
                }

                $code = 'BRW-'.date('Ymd').'-'.sprintf('%03d', Peminjaman::whereDate('created_at', today())->count() + 1);

                $borrowing = Peminjaman::create([
                    'borrow_code' => $code,
                    'customer_id' => auth()->id(),
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date'],
                    'purpose' => $validated['purpose'],
                    'notes' => $validated['notes'] ?? null,
                    'status' => 'Diajukan',
                    'deposit_amount' => $lockedEq->deposit_amount * $validated['quantity'],
                ]);

                DetailPeminjaman::create([
                    'borrowing_id' => $borrowing->id,
                    'equipment_id' => $lockedEq->id,
                    'quantity' => $validated['quantity'],
                ]);

                LogAktivitas::record('Pengajuan Peminjaman', 'Peminjaman', "Pengajuan peminjaman alat {$lockedEq->nama} ({$code})", auth()->id());

                // Notify admins/staff
                $staffUsers = User::whereIn('role', ['admin', 'staff'])->get();
                foreach ($staffUsers as $staff) {
                    Notification::create([
                        'user_id' => $staff->id,
                        'title' => 'Pengajuan Peminjaman Alat',
                        'message' => "Pengajuan peminjaman {$code} dari ".auth()->user()->name,
                        'link' => '/admin/borrowings/'.($borrowing->kode_peminjaman ?? $borrowing->borrow_code ?? $code),
                    ]);
                }
            });
        } catch (\DomainException $e) {
            return back()->withErrors(['quantity' => $e->getMessage()])->withInput();
        }

        return redirect()->route('customer.borrowings.show', $borrowing)
            ->with('success', "Pengajuan peminjaman alat {$code} berhasil dikirim! Menunggu persetujuan admin.");
    }

    public function show(Peminjaman $borrowing)
    {
        $this->authorizeCustomer();

        if ($borrowing->customer_id !== auth()->id()) {
            abort(403, 'Akses ditolak.');
        }

        $borrowing->load(['borrowingDetails.equipment', 'payments.invoice']);

        return view('customer.peminjaman.show', compact('borrowing'));
    }

    public function requestExtension(Request $request, Peminjaman $borrowing)
    {
        $this->authorizeCustomer();

        if ($borrowing->customer_id !== auth()->id()) {
            abort(403);
        }

        if ($borrowing->status !== 'Dipinjam') {
            return back()->with('error', "Perpanjangan hanya dapat diajukan untuk peminjaman aktif yang berstatus 'Dipinjam'. Status saat ini: '{$borrowing->status}'.")
                ->withErrors(['status' => 'Status peminjaman tidak valid untuk perpanjangan.']);
        }

        if ($borrowing->perpanjangan_diajukan || $borrowing->status_perpanjangan === 'Pending') {
            return back()->with('error', 'Permohonan perpanjangan untuk peminjaman ini sudah diajukan dan sedang menunggu persetujuan admin.')
                ->withErrors(['status_perpanjangan' => 'Permohonan perpanjangan sedang diproses.']);
        }

        $validated = $request->validate([
            'extended_end_date' => ['required', 'date', 'after:'.$borrowing->end_date->toDateString()],
        ]);

        try {
            DB::transaction(function () use ($borrowing, $validated) {
                $lockedBorrowing = Peminjaman::where('id', $borrowing->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedBorrowing->status !== 'Dipinjam') {
                    throw new \DomainException('Perpanjangan hanya dapat diajukan untuk peminjaman aktif (Dipinjam).');
                }

                if ($lockedBorrowing->perpanjangan_diajukan || $lockedBorrowing->status_perpanjangan === 'Pending') {
                    throw new \DomainException('Permohonan perpanjangan peminjaman ini sudah diajukan sebelumnya.');
                }

                $lockedBorrowing->update([
                    'extension_requested' => true,
                    'extended_end_date' => $validated['extended_end_date'],
                    'extension_status' => 'Pending',
                ]);

                LogAktivitas::record('Request Perpanjangan Peminjaman', 'Peminjaman', "Request perpanjangan peminjaman {$lockedBorrowing->borrow_code} ke tanggal {$validated['extended_end_date']}", auth()->id());
            });
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage())->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Permintaan perpanjangan pengembalian telah diajukan kepada admin.');
    }
}
