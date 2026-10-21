<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\DetailPeminjaman;
use App\Models\Kalibrasi;
use App\Models\LogAktivitas;
use App\Models\Peralatan;
use App\Models\RiwayatServis;
use App\Models\RiwayatStok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PeralatanController extends Controller
{
    public function index(Request $request)
    {
        $query = Peralatan::query();

        if ($request->filled('category')) {
            $query->where('kategori', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('kode', 'like', "%{$search}%")
                    ->orWhere('merk', 'like', "%{$search}%")
                    ->orWhere('lokasi', 'like', "%{$search}%");
            });
        }

        $equipmentList = $query->latest()->paginate(12)->withQueryString();
        $categories = Peralatan::distinct('kategori')->pluck('kategori');

        return view('staff.peralatan.index', compact('equipmentList', 'categories'));
    }

    public function create()
    {
        return view('staff.peralatan.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'unique:peralatan,kode'],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string'],
            'brand' => ['nullable', 'string'],
            'specification' => ['nullable', 'string'],
            'purchase_year' => ['nullable', 'integer'],
            'quantity' => ['required', 'integer', 'min:0'],
            'location' => ['required', 'string'],
            'min_stock' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:Tersedia,Dipinjam,Dalam Servis,Dalam Kalibrasi,Tidak Tersedia'],
            'rental_price_per_day' => ['required', 'numeric', 'min:0'],
            'deposit_amount' => ['required', 'numeric', 'min:0'],
            'photo' => ['nullable', 'file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ]);

        $newPhotoPath = null;
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $newPhotoPath = $request->file('photo')->store('equipment', 'public');
            $validated['foto'] = $newPhotoPath;
        }
        unset($validated['photo']);

        $validated['qr_code'] = 'QR-'.$validated['code'];
        do {
            $barcode = '899'.str_pad((string) rand(1, 999999999), 10, '0', STR_PAD_LEFT);
        } while (Peralatan::where('barcode', $barcode)->exists());
        $validated['barcode'] = $barcode;

        try {
            $equipment = DB::transaction(function () use ($validated) {
                $equipment = Peralatan::create($validated);

                RiwayatStok::create([
                    'id_peralatan' => $equipment->id,
                    'tipe' => 'Stok Awal',
                    'jumlah_sebelum' => 0,
                    'jumlah_perubahan' => $equipment->jumlah,
                    'jumlah_setelah' => $equipment->jumlah,
                    'user_id' => auth()->id(),
                    'catatan' => 'Pendaftaran data alat baru di inventori',
                ]);

                LogAktivitas::record('Tambah Alat', 'Inventori', "Penambahan alat baru {$equipment->nama} ({$equipment->kode})", auth()->id());

                return $equipment;
            });
        } catch (\Throwable $e) {
            if ($newPhotoPath && Storage::disk('public')->exists($newPhotoPath)) {
                Storage::disk('public')->delete($newPhotoPath);
            }
            throw $e;
        }

        return redirect()->route('staff.equipment.show', $equipment)
            ->with('success', "Alat {$equipment->nama} berhasil ditambahkan ke sistem!");
    }

    public function show(Peralatan $equipment)
    {
        $equipment->load(['calibrations' => function ($q) {
            $q->latest();
        }, 'serviceHistories' => function ($q) {
            $q->latest();
        }, 'stockHistories' => function ($q) {
            $q->latest()->with('user');
        }]);

        return view('staff.peralatan.show', compact('equipment'));
    }

    public function edit(Peralatan $equipment)
    {
        return view('staff.peralatan.edit', compact('equipment'));
    }

    public function update(Request $request, Peralatan $equipment)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string'],
            'brand' => ['nullable', 'string'],
            'specification' => ['nullable', 'string'],
            'purchase_year' => ['nullable', 'integer'],
            'quantity' => ['required', 'integer', 'min:0'],
            'location' => ['required', 'string'],
            'min_stock' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:Tersedia,Dipinjam,Dalam Servis,Dalam Kalibrasi,Tidak Tersedia'],
            'rental_price_per_day' => ['required', 'numeric', 'min:0'],
            'deposit_amount' => ['required', 'numeric', 'min:0'],
            'stock_change_notes' => ['nullable', 'string'],
            'photo' => ['nullable', 'file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ]);

        $newPhotoPath = null;
        $oldPhotoToDelete = null;

        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $newPhotoPath = $request->file('photo')->store('equipment', 'public');
            $validated['foto'] = $newPhotoPath;

            $oldFoto = $equipment->foto;
            if ($oldFoto && ! str_starts_with($oldFoto, 'http') && ! str_starts_with($oldFoto, 'images/')) {
                $isUsedElsewhere = Peralatan::where('id', '!=', $equipment->id)->where('foto', $oldFoto)->exists();
                if (! $isUsedElsewhere) {
                    $oldPhotoToDelete = $oldFoto;
                }
            }
        }
        unset($validated['photo']);

        try {
            $equipment = DB::transaction(function () use ($equipment, $validated) {
                $lockedEq = Peralatan::where('id', $equipment->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $oldQty = $lockedEq->jumlah;
                $newQty = (int) $validated['quantity'];

                $lockedEq->update($validated);

                if ($oldQty !== $newQty) {
                    $diff = $newQty - $oldQty;
                    RiwayatStok::create([
                        'id_peralatan' => $lockedEq->id,
                        'tipe' => $diff > 0 ? 'Penambahan' : 'Pengurangan',
                        'jumlah_sebelum' => $oldQty,
                        'jumlah_perubahan' => $diff,
                        'jumlah_setelah' => $newQty,
                        'user_id' => auth()->id(),
                        'catatan' => $validated['stock_change_notes'] ?? 'Perubahan jumlah stok oleh staf/admin',
                    ]);
                }

                LogAktivitas::record('Update Alat', 'Inventori', "Pembaruan data alat {$lockedEq->nama} ({$lockedEq->kode})", auth()->id());

                return $lockedEq;
            });

            if ($oldPhotoToDelete && Storage::disk('public')->exists($oldPhotoToDelete)) {
                Storage::disk('public')->delete($oldPhotoToDelete);
            }
        } catch (\Throwable $e) {
            if ($newPhotoPath && Storage::disk('public')->exists($newPhotoPath)) {
                Storage::disk('public')->delete($newPhotoPath);
            }
            throw $e;
        }

        return redirect()->route('staff.equipment.show', $equipment)
            ->with('success', "Data alat {$equipment->nama} berhasil diperbarui.");
    }

    public function destroy(Peralatan $equipment)
    {
        $activeBorrowingExists = DetailPeminjaman::where('id_peralatan', $equipment->id)
            ->whereHas('borrowing', function ($q) {
                $q->whereIn('status', ['Diajukan', 'Disetujui', 'Dipinjam', 'Terlambat']);
            })
            ->exists();

        if ($activeBorrowingExists) {
            return back()->with('error', "Alat {$equipment->nama} tidak dapat dihapus karena sedang terkait dengan permohonan atau transaksi peminjaman aktif.");
        }

        $name = $equipment->nama;
        $code = $equipment->kode;
        $equipment->delete();

        LogAktivitas::record('Hapus Alat', 'Inventori', "Menghapus alat {$name} ({$code}) dari inventori", auth()->id());

        return redirect()->route('staff.equipment.index')
            ->with('success', "Alat {$name} berhasil dihapus.");
    }

    public function recordCalibration(Request $request, Peralatan $equipment)
    {
        $result = $request->input('result') ?? $request->input('status');
        $notes = $request->input('notes');
        $noteParts = [];

        if ($request->filled('calibrated_by')) {
            $noteParts[] = 'Lembaga: '.$request->input('calibrated_by');
        }
        if ($request->filled('certificate_number')) {
            $noteParts[] = 'No. Sertifikat: '.$request->input('certificate_number');
        }
        if ($request->filled('notes')) {
            $noteParts[] = $request->input('notes');
        }
        if (! empty($noteParts)) {
            $notes = implode(' | ', $noteParts);
        }

        $request->merge([
            'result' => $result,
            'notes' => $notes,
        ]);

        $validated = $request->validate([
            'calibration_date' => ['required', 'date'],
            'next_calibration_date' => ['required', 'date', 'after:calibration_date'],
            'result' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        Kalibrasi::create([
            'id_peralatan' => $equipment->id,
            'tanggal_kalibrasi' => $validated['calibration_date'],
            'tanggal_kalibrasi_berikutnya' => $validated['next_calibration_date'],
            'hasil' => $validated['result'],
            'catatan' => $validated['notes'] ?? null,
        ]);

        LogAktivitas::record('Catat Kalibrasi', 'Inventori', "Mencatat kalibrasi untuk alat {$equipment->nama}", auth()->id());

        return back()->with('success', 'Riwayat kalibrasi alat berhasil ditambahkan.');
    }

    public function recordService(Request $request, Peralatan $equipment)
    {
        $technician = $request->input('technician') ?? $request->input('technician_name');
        $notes = $request->input('notes') ?? $request->input('description');
        if ($request->filled('cost') && (float) $request->input('cost') > 0) {
            $costFormatted = 'Biaya: Rp '.number_format((float) $request->input('cost'), 0, ',', '.');
            $notes = $notes ? $notes.' | '.$costFormatted : $costFormatted;
        }
        $status = $request->input('status', 'Selesai');

        $request->merge([
            'technician' => $technician,
            'notes' => $notes,
            'status' => $status,
        ]);

        $validated = $request->validate([
            'service_date' => ['required', 'date'],
            'status' => ['required', 'string'],
            'technician' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        RiwayatServis::create([
            'id_peralatan' => $equipment->id,
            'tanggal_servis' => $validated['service_date'],
            'status' => $validated['status'],
            'teknisi' => $validated['technician'] ?? null,
            'catatan' => $validated['notes'] ?? null,
        ]);

        LogAktivitas::record('Catat Servis', 'Inventori', "Mencatat servis untuk alat {$equipment->nama}", auth()->id());

        return back()->with('success', 'Riwayat servis alat berhasil ditambahkan.');
    }
}
