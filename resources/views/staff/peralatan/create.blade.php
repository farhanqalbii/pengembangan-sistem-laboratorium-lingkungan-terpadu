@extends('layouts.app')

@section('title', 'Tambah Alat Lab Baru - Staff')
@section('page-title', 'Pendaftaran Instrumen Laboratorium Baru')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-sm">
        <h2 class="text-base sm:text-lg font-bold text-slate-800 mb-1">Formulir Tambah Alat Lab</h2>
        <p class="text-xs text-slate-500 mb-5">Lengkapi data teknis alat untuk inventori & peminjaman.</p>

        <form action="{{ route('staff.equipment.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Kode Alat <span class="text-rose-500">*</span></label>
                    <input type="text" name="code" value="{{ old('code', 'EQP-' . strtoupper(Str::random(3)) . '-' . rand(100, 999)) }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs font-mono font-bold text-slate-800 focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Nama Instrumen / Alat <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: Spectrophotometer UV-Vis..." required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Kategori <span class="text-rose-500">*</span></label>
                    <input type="text" name="category" value="{{ old('category') }}" placeholder="Contoh: Spektrofotometri, Fisika..." required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Merk / Brand</label>
                    <input type="text" name="brand" value="{{ old('brand') }}" placeholder="Contoh: Thermo Scientific, Hanna..." class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Tahun Pengadaan</label>
                    <input type="number" name="purchase_year" value="{{ old('purchase_year', date('Y')) }}" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Jumlah Stok (Unit) <span class="text-rose-500">*</span></label>
                    <input type="number" name="quantity" min="0" value="{{ old('quantity', 1) }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Stok Minimum Alert <span class="text-rose-500">*</span></label>
                    <input type="number" name="min_stock" min="1" value="{{ old('min_stock', 1) }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Lokasi Lab / Ruangan <span class="text-rose-500">*</span></label>
                    <input type="text" name="location" value="{{ old('location') }}" placeholder="Contoh: Lab Kimia 1, Lab Lapangan..." required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Status Alat <span class="text-rose-500">*</span></label>
                    <x-custom-select 
                        name="status" 
                        :options="[
                            'Tersedia' => 'Tersedia',
                            'Dipinjam' => 'Dipinjam',
                            'Dalam Servis' => 'Dalam Servis',
                            'Dalam Kalibrasi' => 'Dalam Kalibrasi',
                            'Tidak Tersedia' => 'Tidak Tersedia'
                        ]"
                        :selected="old('status', 'Tersedia')"
                        required
                        aria-label="Pilih Status Alat"
                    />
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Harga Sewa / Hari (Rp) <span class="text-rose-500">*</span></label>
                    <input type="number" name="rental_price_per_day" min="0" step="5000" value="{{ old('rental_price_per_day', 150000) }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Deposit Jaminan (Rp) <span class="text-rose-500">*</span></label>
                    <input type="number" name="deposit_amount" min="0" step="50000" value="{{ old('deposit_amount', 500000) }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Spesifikasi Teknis / Keterangan</label>
                <textarea name="specification" rows="3" placeholder="Sebutkan range pengukuran, konsumsi daya, akurasi..." class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">{{ old('specification') }}</textarea>
            </div>

            <!-- Foto Alat (Opsional) with Live Preview -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Foto Alat <span class="text-slate-400 font-normal lowercase">(opsional)</span>
                </label>
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 p-4 bg-slate-50 border border-dashed border-slate-300 rounded-2xl" x-data="{ photoPreview: null }">
                    <!-- Preview Box -->
                    <div class="w-24 h-24 bg-white border border-slate-200 rounded-xl flex items-center justify-center overflow-hidden flex-shrink-0 shadow-2xs">
                        <template x-if="photoPreview">
                            <img :src="photoPreview" alt="Preview Foto Alat" class="w-full h-full object-contain p-1">
                        </template>
                        <template x-if="!photoPreview">
                            <div class="flex flex-col items-center justify-center text-slate-300 p-2 text-center">
                                <i class="fa-solid fa-camera text-2xl text-slate-300 mb-1"></i>
                                <span class="text-[9px] text-slate-400 font-medium">Pratinjau</span>
                            </div>
                        </template>
                    </div>

                    <!-- Input File & Instruction -->
                    <div class="flex-1 space-y-1.5">
                        <input type="file" 
                               name="photo" 
                               id="photo"
                               accept="image/jpeg,image/png,image/webp,image/jpg"
                               @change="const file = $event.target.files[0]; if (file) { const reader = new FileReader(); reader.onload = (e) => { photoPreview = e.target.result; }; reader.readAsDataURL(file); } else { photoPreview = null; }"
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

            <div class="flex flex-col sm:flex-row justify-end gap-2.5 sm:space-x-3 pt-4 border-t border-slate-200">
                <a href="{{ route('staff.equipment.index') }}" class="w-full sm:w-auto px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-colors text-center">Batal</a>
                <button type="submit" class="w-full sm:w-auto px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-600/30 text-center">
                    Simpan Instrumen Lab <i class="fa-solid fa-save ml-1"></i>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
