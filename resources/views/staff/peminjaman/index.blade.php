@extends('layouts.app')

@section('title', 'Manajemen Pengembalian Alat - Staff')
@section('page-title', 'Pengembalian & Inspeksi Alat Laboratorium')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800">Daftar Peminjaman Aktif & Menunggu Pengembalian</h3>
            <span class="text-xs text-slate-500">Pemeriksaan Kondisi & Hitung Denda</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[650px]">
                <thead class="bg-slate-100 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-4 sm:px-5 py-3">Kode Pinjam</th>
                        <th class="px-4 sm:px-5 py-3">Customer</th>
                        <th class="px-4 sm:px-5 py-3">Instrumen Alat</th>
                        <th class="px-4 sm:px-5 py-3">Periode Pinjam</th>
                        <th class="px-4 sm:px-5 py-3">Status</th>
                        <th class="px-4 sm:px-5 py-3 text-center">Aksi Staff</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($borrowings as $b)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 sm:px-5 py-3 font-mono font-bold text-blue-600">
                                {{ $b->borrow_code }}
                            </td>
                            <td class="px-4 sm:px-5 py-3">
                                <div class="font-bold text-slate-800">{{ $b->customer->company_name ?? $b->customer->name }}</div>
                                <div class="text-[11px] text-slate-500">{{ $b->customer->phone }}</div>
                            </td>
                            <td class="px-4 sm:px-5 py-3 font-semibold text-slate-700">
                                {{ $b->borrowingDetails->first()->equipment->name ?? 'Alat Lab' }}
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-slate-600">
                                {{ $b->start_date->format('d/m/Y') }} s/d {{ $b->end_date->format('d/m/Y') }}
                            </td>
                            <td class="px-4 sm:px-5 py-3">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $b->status_badge_class }}">
                                    {{ $b->status }}
                                </span>
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-center space-x-2">
                                <a href="{{ route('staff.borrowings.show', $b->id) }}" class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-lg shadow-sm text-xs">
                                    {{ in_array($b->status, ['Diajukan', 'Menunggu']) ? 'Otorisasi & Kesiapan' : (in_array($b->status, ['Dipinjam', 'Terlambat']) ? 'Inspeksi & Pengembalian' : 'Lihat Detail') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-slate-400">Belum ada transaksi peminjaman alat.</td>
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
