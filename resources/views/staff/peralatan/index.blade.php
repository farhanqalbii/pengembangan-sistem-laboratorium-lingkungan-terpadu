@extends('layouts.app')

@section('title', 'Inventori Alat & Labware - Staff')
@section('page-title', 'Inventori Alat & Instrumen Laboratorium')

@section('content')
<div class="space-y-6">
    <!-- Header Bar & Filter -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-sm anim-fade-up anim-delay-1 card-hover">
        <form action="{{ request()->routeIs('admin.*') ? route('admin.equipment.index') : route('staff.equipment.index') }}" method="GET" class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-3 w-full lg:w-auto flex-1">
            <div class="relative flex-1 min-w-[200px]">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Cari nama, kode, merk, lokasi..." 
                       class="w-full bg-slate-50 border border-slate-300 text-xs rounded-xl pl-9 pr-4 py-2 text-slate-800 placeholder-slate-400 focus:outline-none focus:border-emerald-500 focus:bg-white transition-all">
            </div>

            <div class="w-full sm:w-auto">
                <select name="category" onchange="this.form.submit()" class="w-full sm:w-auto bg-slate-50 border border-slate-300 text-xs rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:border-emerald-500">
                    <option value="">-- Semua Kategori --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full sm:w-auto">
                <select name="status" onchange="this.form.submit()" class="w-full sm:w-auto bg-slate-50 border border-slate-300 text-xs rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:border-emerald-500">
                    <option value="">-- Semua Status --</option>
                    <option value="Tersedia" {{ request('status') === 'Tersedia' ? 'selected' : '' }}>Tersedia</option>
                    <option value="Dipinjam" {{ request('status') === 'Dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                    <option value="Dalam Servis" {{ request('status') === 'Dalam Servis' ? 'selected' : '' }}>Dalam Servis</option>
                    <option value="Dalam Kalibrasi" {{ request('status') === 'Dalam Kalibrasi' ? 'selected' : '' }}>Dalam Kalibrasi</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 sm:flex-initial px-4 py-2 bg-slate-800 text-white rounded-xl text-xs font-semibold hover:bg-slate-700 transition-colors">
                    Filter
                </button>
                @if(request()->filled('search') || request()->filled('category') || request()->filled('status'))
                    <a href="{{ request()->routeIs('admin.*') ? route('admin.equipment.index') : route('staff.equipment.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition-colors" title="Reset Filter">
                        <i class="fa-solid fa-arrow-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>

        @if(auth()->user()->isStaff())
            <a href="{{ route('staff.equipment.create') }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-md shadow-emerald-600/30 flex items-center justify-center gap-2 w-full lg:w-auto shrink-0 transition-colors">
                <i class="fa-solid fa-plus-circle"></i>
                <span>Tambah Alat Baru</span>
            </a>
        @endif
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

                <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2 text-xs">
                    <a href="{{ route('equipment.detail', $eq->code) }}" target="_blank" class="inline-flex items-center text-slate-500 hover:text-emerald-600 font-medium py-1">
                        <i class="fa-solid fa-qrcode mr-1.5"></i> QR / Barcode
                    </a>
                    <div class="flex items-center gap-1.5">
                        @if(auth()->user()->isStaff())
                            <a href="{{ route('staff.equipment.edit', $eq) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition-colors">Edit</a>
                        @endif
                        <a href="{{ request()->routeIs('admin.*') ? route('admin.equipment.show', $eq) : route('staff.equipment.show', $eq) }}" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl shadow-xs transition-colors">Detail</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 text-slate-400 bg-white rounded-2xl border border-slate-200">
                <div class="flex flex-col items-center justify-center space-y-2">
                    <i class="fa-solid fa-microscope text-3xl text-slate-300"></i>
                    <p class="font-semibold text-slate-500">Belum ada data instrumen / alat laboratorium.</p>
                </div>
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
