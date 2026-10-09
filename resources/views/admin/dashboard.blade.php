@extends('layouts.app')

@section('title', 'Dashboard Administrator - LIMS 2026')
@section('page-title', 'Dashboard Control Center Administrator')

@section('content')
<div class="space-y-6">
    <!-- Admin KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5 stagger-grid">
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/90 shadow-sm flex items-center space-x-3.5 card-hover">
            <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg font-bold flex-shrink-0">
                <i class="fa-solid fa-users-gear"></i>
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-0.5 truncate">Total Pengguna</p>
                <h3 class="text-xl sm:text-2xl font-black text-slate-800">{{ $totalUsers }}</h3>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/90 shadow-sm flex items-center space-x-3.5 card-hover">
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg font-bold flex-shrink-0">
                <i class="fa-solid fa-hand-holding-hand"></i>
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-0.5 truncate">Approval Peminjaman</p>
                <h3 class="text-xl sm:text-2xl font-black text-slate-800">{{ $pendingBorrowingsCount }}</h3>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/90 shadow-sm flex items-center space-x-3.5 card-hover">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg font-bold flex-shrink-0">
                <i class="fa-solid fa-wallet"></i>
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-0.5 truncate">Total Revenue Pembayaran</p>
                <h3 class="text-base sm:text-lg lg:text-xl font-black text-emerald-700 truncate">Rp {{ number_format($totalIncome, 0, ',', '.') }}</h3>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/90 shadow-sm flex items-center space-x-3.5 card-hover">
            <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg font-bold flex-shrink-0">
                <i class="fa-solid fa-money-bill-transfer"></i>
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-0.5 truncate">Pending Refund Deposit</p>
                <h3 class="text-xl sm:text-2xl font-black text-slate-800">{{ $pendingRefundsCount }}</h3>
            </div>
        </div>
    </div>

    <!-- Quick Shortcuts & System Overview -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 anim-fade-up anim-delay-2">
        <!-- Quick Action Panels -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 sm:p-6 shadow-sm space-y-4 card-hover">
            <h2 class="text-sm sm:text-base font-bold text-slate-800">Akses Cepat System Control</h2>
            <div class="grid grid-cols-1 gap-2.5">
                <a href="{{ route('admin.users.index') }}" class="p-3 rounded-xl bg-slate-50 border border-slate-200/80 hover:border-emerald-400 hover:bg-emerald-50/50 flex items-center justify-between text-xs font-semibold text-slate-700 hover:text-emerald-900 transition-all">
                    <span><i class="fa-solid fa-user-plus text-purple-600 mr-2.5"></i> Tambah Pengguna Baru</span>
                    <i class="fa-solid fa-chevron-right text-slate-400 text-[10px]"></i>
                </a>
                <a href="{{ route('admin.borrowings.index') }}" class="p-3 rounded-xl bg-slate-50 border border-slate-200/80 hover:border-emerald-400 hover:bg-emerald-50/50 flex items-center justify-between text-xs font-semibold text-slate-700 hover:text-emerald-900 transition-all">
                    <span><i class="fa-solid fa-boxes-packing text-blue-600 mr-2.5"></i> Approval Peminjaman Alat</span>
                    <i class="fa-solid fa-chevron-right text-slate-400 text-[10px]"></i>
                </a>
                <a href="{{ route('admin.finance.index') }}" class="p-3 rounded-xl bg-slate-50 border border-slate-200/80 hover:border-emerald-400 hover:bg-emerald-50/50 flex items-center justify-between text-xs font-semibold text-slate-700 hover:text-emerald-900 transition-all">
                    <span><i class="fa-solid fa-receipt text-emerald-600 mr-2.5"></i> Verifikasi Pembayaran & Refund</span>
                    <i class="fa-solid fa-chevron-right text-slate-400 text-[10px]"></i>
                </a>
                <a href="{{ route('admin.parameters.index') }}" class="p-3 rounded-xl bg-slate-50 border border-slate-200/80 hover:border-emerald-400 hover:bg-emerald-50/50 flex items-center justify-between text-xs font-semibold text-slate-700 hover:text-emerald-900 transition-all">
                    <span><i class="fa-solid fa-sliders text-amber-600 mr-2.5"></i> Pengaturan Baku Mutu</span>
                    <i class="fa-solid fa-chevron-right text-slate-400 text-[10px]"></i>
                </a>
            </div>
        </div>

        <!-- Recent Audit Activity Log -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/90 p-5 sm:p-6 shadow-sm card-hover">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm sm:text-base font-bold text-slate-800">Audit Trail Activity Logs (Terbaru)</h2>
                <a href="{{ route('admin.activity-logs.index') }}" class="text-xs font-semibold text-emerald-600 hover:underline">Semua Log &rarr;</a>
            </div>

            <div class="space-y-2.5">
                @forelse($recentActivityLogs as $log)
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/70 flex items-center justify-between text-xs">
                        <div class="flex items-center space-x-3 min-w-0 pr-3">
                            <div class="w-8 h-8 rounded-lg bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                {{ strtoupper(substr($log->user->name ?? 'SYS', 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <span class="font-bold text-slate-800 block truncate">{{ $log->user->name ?? 'Sistem' }} <span class="font-normal text-slate-500">({{ $log->user->role ?? 'system' }})</span></span>
                                <span class="text-slate-500 text-[11px] block truncate">{{ $log->description }}</span>
                            </div>
                        </div>
                        <span class="text-[10px] text-slate-400 font-mono whitespace-nowrap flex-shrink-0">{{ $log->created_at->diffForHumans() }}</span>
                    </div>
                @empty
                    <div class="text-center py-8 text-slate-400 text-xs">Belum ada catatan aktivitas log.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
