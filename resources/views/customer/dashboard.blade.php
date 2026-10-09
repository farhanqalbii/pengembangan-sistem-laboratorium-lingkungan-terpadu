@extends('layouts.app')

@section('title', 'Dashboard Customer - LIMS 2026')
@section('page-title', 'Dashboard Customer')

@section('content')
<div class="space-y-6">
    <!-- Welcome Hero Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-[#103a5e] to-emerald-700 rounded-2xl p-5 sm:p-6 text-white shadow-lg relative overflow-hidden border border-slate-700/40 anim-fade-up card-hover">
        <div class="relative z-10 max-w-2xl">
            <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight">Portal Layanan Pelanggan</h1>
            <p class="text-slate-200 text-xs sm:text-sm mt-1.5 leading-relaxed">
                Portal Layanan Mandiri Laboratorium Lingkungan Terpadu 2026. Pantau status pengujian sampel air, udara, tanah, serta kelola peminjaman instrumen laboratorium Anda secara realtime.
            </p>
            <div class="mt-4 sm:mt-5 flex flex-wrap gap-2.5">
                <a href="{{ route('customer.samples.create') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-md shadow-emerald-950/30 transition-all flex items-center">
                    <i class="fa-solid fa-plus-circle mr-2 text-cyan-200"></i> Ajukan Uji Sampel Baru
                </a>
                <a href="{{ route('customer.borrowings.create') }}" class="px-4 py-2 bg-slate-900/80 hover:bg-slate-800 text-white text-xs font-bold rounded-xl border border-slate-700/80 backdrop-blur-md transition-all flex items-center">
                    <i class="fa-solid fa-toolbox mr-2 text-cyan-200"></i> Pinjam Instrumen Lab
                </a>
            </div>
        </div>
        <div class="absolute right-0 top-0 bottom-0 opacity-15 pointer-events-none hidden md:flex items-center pr-10 text-cyan-200">
            <i class="fa-solid fa-flask-vial text-[140px]"></i>
        </div>
    </div>

    <!-- Quick Metrics Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5 stagger-grid anim-fade-up anim-delay-1">
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/90 shadow-sm flex items-center space-x-3.5 card-hover">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg font-bold flex-shrink-0">
                <i class="fa-solid fa-vial-circle-check"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-0.5">Sampel Aktif</p>
                <h3 class="text-xl sm:text-2xl font-black text-slate-800">{{ $activeSamples->count() }}</h3>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/90 shadow-sm flex items-center space-x-3.5 card-hover">
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg font-bold flex-shrink-0">
                <i class="fa-solid fa-toolbox"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-0.5">Peminjaman Aktif</p>
                <h3 class="text-xl sm:text-2xl font-black text-slate-800">{{ $activeBorrowings->count() }}</h3>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/90 shadow-sm flex items-center space-x-3.5 card-hover">
            <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg font-bold flex-shrink-0">
                <i class="fa-solid fa-file-contract"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-0.5">Total Sertifikat (COA)</p>
                <h3 class="text-xl sm:text-2xl font-black text-slate-800">{{ $latestCertificates->count() }}</h3>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/90 shadow-sm flex items-center space-x-3.5 card-hover">
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg font-bold flex-shrink-0">
                <i class="fa-solid fa-file-invoice-dollar"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-0.5">Tagihan Menunggu</p>
                <h3 class="text-xl sm:text-2xl font-black text-slate-800">{{ $unpaidInvoices->count() }}</h3>
            </div>
        </div>
    </div>

    <!-- Active Samples & Borrowings Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 anim-fade-up anim-delay-2">
        <!-- Left: Status Sampel Terbaru -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm card-hover">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-bold text-slate-800">Status Pengujian Sampel</h2>
                <a href="{{ route('customer.samples.index') }}" class="text-xs font-semibold text-emerald-600 hover:underline">Lihat Semua &rarr;</a>
            </div>

            <div class="space-y-4">
                @forelse($activeSamples as $sample)
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 hover:border-emerald-300 transition-all">
                        <div class="flex items-start justify-between">
                            <div>
                                <span class="text-xs font-bold text-emerald-600 font-mono">{{ $sample->sample_code }}</span>
                                <h4 class="font-bold text-slate-800 text-sm mt-0.5">{{ $sample->sample_type }}</h4>
                                <p class="text-xs text-slate-500 mt-1"><i class="fa-solid fa-location-dot mr-1"></i> {{ $sample->sampling_location }}</p>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $sample->status_badge_class }}">
                                {{ $sample->status }}
                            </span>
                        </div>
                        <div class="mt-3 pt-3 border-t border-slate-200/60 flex items-center justify-between text-xs">
                            <span class="text-slate-400">Tgl Pengajuan: {{ $sample->request_date ? $sample->request_date->format('d/m/Y') : '-' }}</span>
                            <a href="{{ route('customer.samples.show', $sample->id) }}" class="font-semibold text-emerald-600 hover:underline">Lacak Status &rarr;</a>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-slate-400 text-xs">Belum ada sampel yang sedang aktif.</div>
                @endforelse
            </div>
        </div>

        <!-- Right: Status Peminjaman Alat Terbaru -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm card-hover">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-bold text-slate-800">Status Peminjaman Alat</h2>
                <a href="{{ route('customer.borrowings.index') }}" class="text-xs font-semibold text-emerald-600 hover:underline">Lihat Semua &rarr;</a>
            </div>

            <div class="space-y-4">
                @forelse($activeBorrowings as $borrowing)
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 hover:border-emerald-300 transition-all">
                        <div class="flex items-start justify-between">
                            <div>
                                <span class="text-xs font-bold text-blue-600 font-mono">{{ $borrowing->borrow_code }}</span>
                                <h4 class="font-bold text-slate-800 text-sm mt-0.5">
                                    {{ $borrowing->borrowingDetails->first()->equipment->name ?? 'Alat Laboratorium' }}
                                </h4>
                                <p class="text-xs text-slate-500 mt-1">
                                    <i class="fa-solid fa-calendar-days mr-1"></i> {{ $borrowing->start_date->format('d/m/Y') }} - {{ $borrowing->end_date->format('d/m/Y') }}
                                </p>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $borrowing->status_badge_class }}">
                                {{ $borrowing->status }}
                            </span>
                        </div>
                        <div class="mt-3 pt-3 border-t border-slate-200/60 flex items-center justify-between text-xs">
                            <span class="text-slate-400">Deposit: Rp {{ number_format($borrowing->deposit_amount, 0, ',', '.') }}</span>
                            <a href="{{ route('customer.borrowings.show', $borrowing->id) }}" class="font-semibold text-emerald-600 hover:underline">Detail Peminjaman &rarr;</a>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-slate-400 text-xs">Belum ada peminjaman alat yang aktif.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
