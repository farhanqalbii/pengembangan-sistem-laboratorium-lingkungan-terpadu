@extends('layouts.app')

@section('title', 'Laporan Inventori Alat - Admin')
@section('page-title', 'Laporan Asset & Kalibrasi Inventori')

@section('content')
<div class="space-y-6">
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-sm font-bold text-slate-800">Rekap Aset & Status Kelayakan Instrumen</h2>
            <p class="text-xs text-slate-500 mt-0.5">Kelola data alat, cetak label stiker QR code, atau unduh laporan aset.</p>
        </div>
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 w-full sm:w-auto">
            <a href="{{ route('staff.equipment.index') }}" class="w-full sm:w-auto px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-cyan-300 text-xs font-bold rounded-xl shadow-md transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-microscope text-xs"></i>
                <span>Master Inventori Alat & QR</span>
            </a>
            <a href="{{ route('admin.reports.inventory', ['export' => 'pdf']) }}" class="w-full sm:w-auto px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-emerald-600/30 flex items-center justify-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-file-pdf"></i>
                <span>Export Laporan PDF</span>
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[760px]">
                <thead class="bg-slate-100 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-4 sm:px-5 py-3">Kode Alat</th>
                        <th class="px-4 sm:px-5 py-3">Nama Instrumen</th>
                        <th class="px-4 sm:px-5 py-3">Kategori & Merk</th>
                        <th class="px-4 sm:px-5 py-3">Stok / Min</th>
                        <th class="px-4 sm:px-5 py-3">Lokasi</th>
                        <th class="px-4 sm:px-5 py-3">Status Alat</th>
                        <th class="px-4 sm:px-5 py-3">Tarif Sewa/Hari</th>
                        <th class="px-4 sm:px-5 py-3 text-center">Aksi / Label QR</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($equipmentList as $eq)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 sm:px-5 py-3 font-mono font-bold text-slate-800">
                                {{ $eq->code }}
                            </td>
                            <td class="px-4 sm:px-5 py-3 font-bold text-slate-800">
                                {{ $eq->name }}
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-slate-600">
                                {{ $eq->category }} ({{ $eq->brand ?? 'N/A' }})
                            </td>
                            <td class="px-4 sm:px-5 py-3 font-bold text-slate-800">
                                {{ $eq->quantity }} unit (Min: {{ $eq->min_stock }})
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-slate-500">
                                {{ $eq->location }}
                            </td>
                            <td class="px-4 sm:px-5 py-3">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $eq->status_badge_class }}">
                                    {{ $eq->status }}
                                </span>
                            </td>
                            <td class="px-4 sm:px-5 py-3 font-bold text-emerald-700">
                                Rp {{ number_format($eq->rental_price_per_day, 0, ',', '.') }}
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-center">
                                <a href="{{ route('staff.equipment.show', $eq->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200/90 rounded-lg text-xs font-semibold transition-all cursor-pointer shadow-2xs">
                                    <i class="fa-solid fa-qrcode text-[11px]"></i>
                                    <span>Detail & QR</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-12 text-slate-400">Belum ada inventori alat terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
