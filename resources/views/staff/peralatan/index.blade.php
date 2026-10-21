@extends('layouts.app')

@section('title', 'Inventori Alat & Labware - Staff')
@section('page-title', 'Inventori Alat & Instrumen Laboratorium')

@section('content')
<div class="space-y-6">
    <!-- Header Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm anim-fade-up anim-delay-1 card-hover">
        <form action="{{ route('staff.equipment.index') }}" method="GET" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama/kode/merk/lokasi..." class="bg-slate-50 border border-slate-300 text-xs rounded-xl px-4 py-2 text-slate-800 w-full sm:w-64 focus:outline-none focus:border-emerald-500">
            <select name="category" onchange="this.form.submit()" class="bg-slate-50 border border-slate-300 text-xs rounded-xl px-3 py-2 text-slate-800 w-full sm:w-auto focus:outline-none focus:border-emerald-500">
                <option value="">-- Semua Kategori --</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
            <select name="status" onchange="this.form.submit()" class="bg-slate-50 border border-slate-300 text-xs rounded-xl px-3 py-2 text-slate-800 w-full sm:w-auto focus:outline-none focus:border-emerald-500">
                <option value="">-- Semua Status --</option>
                <option value="Tersedia" {{ request('status') === 'Tersedia' ? 'selected' : '' }}>Tersedia</option>
                <option value="Dipinjam" {{ request('status') === 'Dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                <option value="Dalam Servis" {{ request('status') === 'Dalam Servis' ? 'selected' : '' }}>Dalam Servis</option>
                <option value="Dalam Kalibrasi" {{ request('status') === 'Dalam Kalibrasi' ? 'selected' : '' }}>Dalam Kalibrasi</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-xl text-xs font-semibold hover:bg-slate-700 w-full sm:w-auto">Filter</button>
        </form>

        <a href="{{ route('staff.equipment.create') }}" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-emerald-600/30 flex items-center justify-center w-full sm:w-auto">
            <i class="fa-solid fa-plus-circle mr-2"></i> Tambah Alat Baru
        </a>
    </div>

    <!-- Equipment Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5 stagger-grid anim-fade-up anim-delay-2">
        @forelse($equipmentList as $eq)
            <div class="bg-white rounded-2xl border border-slate-200/90 p-4 sm:p-5 shadow-sm card-hover flex flex-col justify-between">
                <div>
                    <!-- Equipment Photo Area -->
                    <div class="w-full h-28 sm:h-32 bg-slate-50/80 rounded-xl mb-3 flex items-center justify-center overflow-hidden border border-slate-100 p-2">
                        @if($eq->photo_url)
                            <img src="{{ $eq->photo_url }}" alt="{{ $eq->name }}" class="max-h-full max-w-full object-contain mx-auto transition-transform duration-300 hover:scale-105" loading="lazy">
                        @else
                            <div class="flex flex-col items-center justify-center text-slate-300">
                                <i class="fa-solid fa-microscope text-3xl mb-1 text-slate-300"></i>
                                <span class="text-[10px] font-semibold text-slate-400">Instrumen Lab</span>
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center justify-between mb-2">
                        <span class="font-mono font-bold text-xs text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-md">{{ $eq->code }}</span>
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $eq->status_badge_class }}">
                            {{ $eq->status }}
                        </span>
                    </div>

                    <h3 class="font-bold text-slate-800 text-sm mb-1 line-clamp-1" title="{{ $eq->name }}">{{ $eq->name }}</h3>
                    <p class="text-xs text-slate-500 mb-3 truncate">Kategori: {{ $eq->category }} | Merk: {{ $eq->brand ?? 'N/A' }}</p>

                    <div class="bg-slate-50 p-3 rounded-xl space-y-1 text-xs mb-4 border border-slate-100">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-400">Stok Tersedia:</span>
                            <span class="font-bold flex items-center gap-1.5 {{ $eq->quantity <= $eq->min_stock ? 'text-rose-600 font-extrabold' : 'text-slate-800' }}">
                                @if($eq->quantity <= $eq->min_stock)
                                    <span class="stock-pulse-dot w-2 h-2 rounded-full bg-rose-500"></span>
                                @endif
                                <span>{{ $eq->quantity }} unit (Min: {{ $eq->min_stock }})</span>
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Lokasi Lab:</span>
                            <span class="font-semibold text-slate-700">{{ $eq->location }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Harga Sewa:</span>
                            <span class="font-bold text-emerald-700">Rp {{ number_format($eq->rental_price_per_day, 0, ',', '.') }}/hr</span>
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <a href="{{ route('equipment.detail', $eq->code) }}" target="_blank" class="text-slate-500 hover:text-emerald-600">
                        <i class="fa-solid fa-qrcode mr-1"></i> QR / Barcode
                    </a>
                    <div class="space-x-2">
                        <a href="{{ route('staff.equipment.edit', $eq->id) }}" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg">Edit</a>
                        <a href="{{ route('staff.equipment.show', $eq->id) }}" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-lg shadow-sm">Detail</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 text-slate-400 bg-white rounded-2xl border border-slate-200">
                Belum ada data instrumen / alat laboratorium.
            </div>
        @endforelse
    </div>

    @if($equipmentList->hasPages())
        <div class="p-4 bg-white rounded-2xl border border-slate-200">
            {{ $equipmentList->links() }}
        </div>
    @endif
</div>
@endsection
