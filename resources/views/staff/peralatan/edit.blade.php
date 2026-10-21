@extends('layouts.app')

@section('title', 'Edit Alat Lab ' . $equipment->code . ' - Staff')
@section('page-title', 'Pembaruan Data & Stok Instrumen')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-sm">
        <h2 class="text-base sm:text-lg font-bold text-slate-800 mb-1">Edit Data Alat: {{ $equipment->name }}</h2>
        <p class="text-xs text-slate-500 mb-5">Perbarui detail spesifikasi atau lakukan perubahan jumlah stok.</p>

        <form id="update-equipment-form" action="{{ route('staff.equipment.update', $equipment->id) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Kode Alat</label>
                    <input type="text" value="{{ $equipment->code }}" disabled class="w-full bg-slate-100 border border-slate-300 rounded-xl px-3.5 py-2 text-xs font-mono font-bold text-slate-500">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Nama Instrumen / Alat <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $equipment->name) }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Kategori <span class="text-rose-500">*</span></label>
                    <input type="text" name="category" value="{{ old('category', $equipment->category) }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Merk / Brand</label>
                    <input type="text" name="brand" value="{{ old('brand', $equipment->brand) }}" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Tahun Pengadaan</label>
                    <input type="number" name="purchase_year" value="{{ old('purchase_year', $equipment->purchase_year) }}" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Jumlah Stok (Unit) <span class="text-rose-500">*</span></label>
                    <input type="number" name="quantity" min="0" value="{{ old('quantity', $equipment->quantity) }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs font-bold text-slate-800 focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Stok Minimum Alert <span class="text-rose-500">*</span></label>
                    <input type="number" name="min_stock" min="1" value="{{ old('min_stock', $equipment->min_stock) }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Lokasi Lab / Ruangan <span class="text-rose-500">*</span></label>
                    <input type="text" name="location" value="{{ old('location', $equipment->location) }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Status Alat <span class="text-rose-500">*</span></label>
                    <select name="status" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                        <option value="Tersedia" {{ $equipment->status === 'Tersedia' ? 'selected' : '' }}>Tersedia</option>
                        <option value="Dipinjam" {{ $equipment->status === 'Dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                        <option value="Dalam Servis" {{ $equipment->status === 'Dalam Servis' ? 'selected' : '' }}>Dalam Servis</option>
                        <option value="Dalam Kalibrasi" {{ $equipment->status === 'Dalam Kalibrasi' ? 'selected' : '' }}>Dalam Kalibrasi</option>
                        <option value="Tidak Tersedia" {{ $equipment->status === 'Tidak Tersedia' ? 'selected' : '' }}>Tidak Tersedia</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Harga Sewa / Hari (Rp) <span class="text-rose-500">*</span></label>
                    <input type="number" name="rental_price_per_day" min="0" step="5000" value="{{ old('rental_price_per_day', $equipment->rental_price_per_day) }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Deposit Jaminan (Rp) <span class="text-rose-500">*</span></label>
                    <input type="number" name="deposit_amount" min="0" step="50000" value="{{ old('deposit_amount', $equipment->deposit_amount) }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Spesifikasi Teknis / Keterangan</label>
                <textarea name="specification" rows="3" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">{{ old('specification', $equipment->specification) }}</textarea>
            </div>

            <!-- Foto Alat: Tampilkan Foto Saat Ini & Upload Pengganti -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Foto Alat Laboratorium
                </label>
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5 p-4 bg-slate-50 border border-slate-200 rounded-2xl" x-data="{ newPhotoPreview: null }">
                    <!-- Current / New Photo Box -->
                    <div class="w-28 h-28 bg-white border border-slate-200 rounded-xl flex items-center justify-center overflow-hidden shadow-2xs relative flex-shrink-0">
                        <!-- Show new preview if selected -->
                        <template x-if="newPhotoPreview">
                            <div class="w-full h-full flex flex-col items-center justify-center p-1">
                                <img :src="newPhotoPreview" alt="Pratinjau Foto Baru" class="max-h-full max-w-full object-contain">
                                <span class="absolute bottom-1 right-1 bg-emerald-600 text-white text-[9px] font-bold px-1.5 py-0.5 rounded shadow">Baru</span>
                            </div>
                        </template>

                        <!-- If no new file selected, show current photo or placeholder -->
                        <template x-if="!newPhotoPreview">
                            @if($equipment->photo_url)
                                <img src="{{ $equipment->photo_url }}" alt="{{ $equipment->name }}" class="max-h-full max-w-full object-contain p-1">
                            @else
                                <div class="flex flex-col items-center justify-center text-slate-300 p-2 text-center">
                                    <i class="fa-solid fa-microscope text-3xl mb-1 text-slate-300"></i>
                                    <span class="text-[9px] text-slate-400 font-medium">Tanpa Foto</span>
                                </div>
                            @endif
                        </template>
                    </div>

                    <!-- Input Replacement & Description -->
                    <div class="flex-1 space-y-2">
                        <div class="text-xs">
                            <span class="font-semibold text-slate-700">
                                @if($equipment->photo_url)
                                    Ganti Foto Alat (Opsional):
                                @else
                                    Unggah Foto Alat (Opsional):
                                @endif
                            </span>
                            <p class="text-slate-500 text-[11px] mt-0.5">
                                @if($equipment->photo_url)
                                    Pilih file baru jika ingin mengganti foto saat ini. Jika dikosongkan, foto saat ini tetap dipertahankan.
                                @else
                                    Alat ini belum memiliki foto khusus. Pilih file untuk menambahkan foto instrumen laboratorium.
                                @endif
                            </p>
                        </div>

                        <input type="file" 
                               name="photo" 
                               id="edit_photo"
                               accept="image/jpeg,image/png,image/webp,image/jpg"
                               @change="const file = $event.target.files[0]; if (file) { const reader = new FileReader(); reader.onload = (e) => { newPhotoPreview = e.target.result; }; reader.readAsDataURL(file); } else { newPhotoPreview = null; }"
                               class="block w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 file:cursor-pointer border border-slate-200 rounded-xl bg-white p-1 focus:outline-none">

                        <p class="text-[11px] text-slate-500">
                            Format file yang didukung: <span class="font-semibold text-slate-700">JPG, JPEG, PNG, WebP</span>. Ukuran maksimum: <span class="font-semibold text-slate-700">2 MB</span>.
                        </p>
                        @error('photo')
                            <p class="text-xs text-rose-600 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Catatan Perubahan Stok (Opsional)</label>
                <input type="text" name="stock_change_notes" placeholder="Alasan penambahan/pengurangan stok..." class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
            </div>

            <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-3 pt-4 border-t border-slate-200">
                <button type="submit" form="delete-equipment-form" class="w-full sm:w-auto px-3.5 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs rounded-xl border border-rose-200 transition-colors cursor-pointer text-center">
                    <i class="fa-solid fa-trash mr-1 text-rose-500"></i> Hapus Alat
                </button>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 w-full sm:w-auto">
                    <a href="{{ route('staff.equipment.show', $equipment->id) }}" class="w-full sm:w-auto px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-colors text-center">Batal</a>
                    <button type="submit" form="update-equipment-form" class="w-full sm:w-auto px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-600/30 text-center">
                        Simpan Perubahan Data <i class="fa-solid fa-save ml-1"></i>
                    </button>
                </div>
            </div>
        </form>

        <form id="delete-equipment-form" action="{{ route('staff.equipment.destroy', $equipment->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus alat ini dari inventori?')" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>
</div>
@endsection
