@extends('layouts.app')

@section('title', 'Peminjaman Alat Saya - LIMS 2026')
@section('page-title', 'Peminjaman Alat Laboratorium')

@section('content')
<div class="space-y-6">
    <!-- Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm anim-fade-up anim-delay-1 card-hover">
        <h2 class="text-sm font-bold text-slate-800">Daftar Peminjaman Instrumen & Alat Field</h2>
        <a href="{{ route('customer.borrowings.create') }}" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-emerald-600/30 flex items-center justify-center w-full sm:w-auto">
            <i class="fa-solid fa-plus-circle mr-2"></i> Pengajuan Pinjam Alat Baru
        </a>
    </div>

    <!-- Borrowing Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 stagger-grid anim-fade-up anim-delay-2">
        @forelse($borrowings as $borrowing)
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm card-hover flex flex-col justify-between">
                <div>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
                        <span class="font-mono font-bold text-xs text-blue-600 bg-blue-50 px-2.5 py-1 rounded-md self-start sm:self-auto">{{ $borrowing->borrow_code }}</span>
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold self-start sm:self-auto {{ $borrowing->status_badge_class }}">
                            {{ $borrowing->status }}
                        </span>
                    </div>

                    <h3 class="font-bold text-slate-800 text-sm mb-1">
                        {{ $borrowing->borrowingDetails->first()->equipment->name ?? 'Alat Lab' }}
                        @if($borrowing->borrowingDetails->count() > 1)
                            <span class="text-slate-400 text-xs font-normal">+{{ $borrowing->borrowingDetails->count() - 1 }} alat lainnya</span>
                        @endif
                    </h3>

                    <p class="text-xs text-slate-500 mb-3 line-clamp-2">{{ $borrowing->purpose }}</p>

                    <div class="space-y-1 text-xs text-slate-600 bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Periode Pinjam:</span>
                            <span class="font-semibold">{{ $borrowing->start_date->format('d/m/Y') }} - {{ $borrowing->end_date->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Deposit Jaminan:</span>
                            <span class="font-bold text-emerald-600">Rp {{ number_format($borrowing->deposit_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-[11px] text-slate-400">Peminjaman {{ $borrowing->created_at->diffForHumans() }}</span>
                    <a href="{{ route('customer.borrowings.show', $borrowing) }}" class="text-xs font-bold text-emerald-600 hover:underline">
                        Lihat Detail &rarr;
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 text-slate-400 bg-white rounded-2xl border border-slate-200">
                Belum ada pengajuan peminjaman alat.
            </div>
        @endforelse
    </div>
</div>
@endsection
