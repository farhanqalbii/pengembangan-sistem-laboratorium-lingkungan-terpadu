@extends('layouts.app')

@section('title', 'Approval Peminjaman Alat - Admin')
@section('page-title', 'Otorisasi & Approval Peminjaman Alat')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-200 bg-slate-50 flex flex-col sm:flex-row sm:items-center justify-between gap-1 sm:gap-2">
            <h3 class="text-sm font-bold text-slate-800">Permohonan Peminjaman Alat Lapangan</h3>
            <span class="text-xs text-slate-500">Pemeriksaan Ketersediaan Stok & Syarat Admin</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[720px]">
                <thead class="bg-slate-100 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-4 sm:px-5 py-3">Kode Pinjam</th>
                        <th class="px-4 sm:px-5 py-3">Customer</th>
                        <th class="px-4 sm:px-5 py-3">Instrumen Alat</th>
                        <th class="px-4 sm:px-5 py-3">Periode Pinjam</th>
                        <th class="px-4 sm:px-5 py-3">Deposit</th>
                        <th class="px-4 sm:px-5 py-3">Status</th>
                        <th class="px-4 sm:px-5 py-3 text-center">Aksi Approval</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($borrowings as $b)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 sm:px-5 py-3">
                                <a href="{{ route('admin.borrowings.show', $b->id) }}" class="font-mono font-bold text-blue-600 hover:text-blue-800 hover:underline">
                                    {{ $b->borrow_code }}
                                </a>
                                @if($b->perpanjangan_diajukan && $b->status_perpanjangan === 'Pending')
                                    <span class="block text-[10px] font-bold text-amber-700 bg-amber-50 border border-amber-200 rounded px-1.5 py-0.5 mt-1 text-center">
                                        Perpanjangan Diajukan
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 sm:px-5 py-3">
                                <div class="font-bold text-slate-800">{{ $b->customer->company_name ?? $b->customer->name }}</div>
                                <div class="text-[11px] text-slate-500">{{ $b->customer->phone }}</div>
                            </td>
                            <td class="px-4 sm:px-5 py-3">
                                <div class="font-semibold text-slate-700">{{ $b->borrowingDetails->first()->equipment->name ?? 'Alat Lab' }}</div>
                                <div class="text-[11px] text-slate-400">Tujuan: {{ $b->purpose }}</div>
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-slate-600">
                                {{ $b->start_date->format('d/m/Y') }} s/d {{ $b->end_date->format('d/m/Y') }}
                            </td>
                            <td class="px-4 sm:px-5 py-3 font-bold text-emerald-700">
                                Rp {{ number_format($b->deposit_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 sm:px-5 py-3">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $b->status_badge_class }}">
                                    {{ $b->status }}
                                </span>
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-center space-x-1.5 whitespace-nowrap">
                                @if($b->status === 'Diajukan')
                                    <form action="{{ route('admin.borrowings.approve', $b->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-lg shadow-sm cursor-pointer">
                                            Approve
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.borrowings.reject', $b->id) }}" method="POST" class="inline" onsubmit="return confirm('Tolak permohonan pinjam ini?')">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1.5 bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold rounded-lg shadow-sm cursor-pointer">
                                            Tolak
                                        </button>
                                    </form>
                                @elseif($b->perpanjangan_diajukan && $b->status_perpanjangan === 'Pending')
                                    <a href="{{ route('admin.borrowings.show', $b->id) }}" class="px-2.5 py-1.5 bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold rounded-lg shadow-sm inline-block">
                                        Perpanjangan
                                    </a>
                                @else
                                    <a href="{{ route('admin.borrowings.show', $b->id) }}" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-xs inline-block">
                                        Detail
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-slate-400">Belum ada permohonan peminjaman alat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($borrowings->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50">
                {{ $borrowings->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
