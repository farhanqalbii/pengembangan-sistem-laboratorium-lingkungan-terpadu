@extends('layouts.app')

@section('title', 'Tambah Parameter Uji & Baku Mutu - Admin')
@section('page-title', 'Tambah Parameter Uji & Baku Mutu Baru')

@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-2xl border border-slate-200 p-4 sm:p-6 shadow-sm">
    <form action="{{ route('admin.parameters.store') }}" method="POST" class="space-y-4">
        @csrf

        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Parameter Uji</label>
            <input type="text" name="name" value="{{ old('name') }}" placeholder="misal: BOD, COD, pH, Timbal (Pb)" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-800">
            @error('name') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Kategori Pengujian</label>
                <input type="text" name="category" value="{{ old('category') }}" placeholder="misal: Fisika & Kimia Dasar, Logam Berat" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-800">
                @error('category') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Satuan</label>
                <input type="text" name="unit" value="{{ old('unit') }}" placeholder="misal: mg/L, ppm, °C, -" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-800">
                @error('unit') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Metode Pengujian (SNI / Standard Method)</label>
            <input type="text" name="test_method" value="{{ old('test_method') }}" placeholder="misal: SNI 06-6989.11-2004" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-800">
            @error('test_method') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Tarif Uji (Rp)</label>
            <input type="number" name="price" value="{{ old('price') }}" placeholder="misal: 150000" min="0" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-800 font-mono">
            @error('price') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
        </div>

        <div class="border-t border-slate-200 pt-4 space-y-3">
            <h4 class="font-bold text-xs text-slate-800 uppercase tracking-wider">Baku Mutu Acuan Lingkungan</h4>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nilai Min (Opsional)</label>
                    <input type="number" step="any" name="min_value" value="{{ old('min_value') }}" placeholder="misal: 6.0" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-800 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nilai Max (Opsional)</label>
                    <input type="number" step="any" name="max_value" value="{{ old('max_value') }}" placeholder="misal: 30.0" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-800 font-mono">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Regulasi Acuan Baku Mutu</label>
                <input type="text" name="regulation_source" value="{{ old('regulation_source') }}" placeholder="misal: Permen LHK No. 68 Tahun 2016" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-800">
                @error('regulation_source') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:space-x-3 pt-4 border-t border-slate-100">
            <a href="{{ route('admin.parameters.index') }}" class="w-full sm:w-auto px-4 py-2 bg-slate-100 text-slate-700 text-xs font-semibold rounded-xl text-center">Batal</a>
            <button type="submit" class="w-full sm:w-auto px-5 py-2 bg-emerald-600 text-white text-xs font-bold rounded-xl shadow-lg shadow-emerald-600/30 text-center">Simpan Parameter</button>
        </div>
    </form>
</div>
@endsection
