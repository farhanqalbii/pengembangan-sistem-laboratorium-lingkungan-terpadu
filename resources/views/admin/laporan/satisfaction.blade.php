@extends('layouts.app')

@section('title', 'Laporan Kepuasan Pelanggan - LIMS 2026')
@section('page-title', 'Laporan Indeks Kepuasan Pelanggan (IKP)')

@section('content')
<div class="space-y-6">
    <!-- Filter & Export Card -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4">
        <form action="{{ url()->current() }}" method="GET" class="flex flex-col sm:flex-row sm:items-center gap-3 w-full lg:w-auto">
            <div class="w-full sm:w-auto">
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full sm:w-auto bg-slate-50 border border-slate-300 text-xs rounded-xl px-3 py-1.5 text-slate-800">
            </div>
            <div class="w-full sm:w-auto">
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full sm:w-auto bg-slate-50 border border-slate-300 text-xs rounded-xl px-3 py-1.5 text-slate-800">
            </div>
            <div class="w-full sm:w-auto sm:pt-4">
                <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-slate-800 text-white rounded-xl text-xs font-semibold hover:bg-slate-700 text-center">Filter Periode</button>
            </div>
            <div class="pt-2 sm:pt-4 pl-0 sm:pl-2 sm:border-l sm:border-slate-200">
                <span class="text-xs text-slate-500">Rata-rata IKP: <strong class="text-amber-600 text-sm font-extrabold">{{ number_format($avgRating, 2) }} / 5.00 ★</strong></span>
            </div>
        </form>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 w-full lg:w-auto">
            <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="w-full sm:w-auto px-4 py-2.5 bg-emerald-700 hover:bg-emerald-600 text-white text-xs font-bold rounded-xl shadow-md transition-all flex items-center justify-center">
                <i class="fa-solid fa-file-excel mr-2"></i> Export Excel (.xlsx)
            </a>
            <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="w-full sm:w-auto px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-md transition-all flex items-center justify-center">
                <i class="fa-solid fa-file-pdf mr-2"></i> Export PDF
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[650px]">
                <thead class="bg-slate-100 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-4 sm:px-5 py-3">Customer</th>
                        <th class="px-4 sm:px-5 py-3">Jenis Layanan</th>
                        <th class="px-4 sm:px-5 py-3">Penilaian Rating</th>
                        <th class="px-4 sm:px-5 py-3">Ulasan / Feedback</th>
                        <th class="px-4 sm:px-5 py-3">Tanggal Ulasan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($satisfactions as $s)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 sm:px-5 py-3 font-bold text-slate-800">
                                {{ $s->customer->company_name ?? $s->customer->name }}
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-slate-700">
                                {{ $s->service_type }}
                            </td>
                            <td class="px-4 sm:px-5 py-3">
                                <span class="text-amber-500 font-extrabold text-sm">
                                    {{ $s->rating }} / 5 ★
                                </span>
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-slate-600">
                                {{ $s->feedback ?? '-' }}
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-slate-500">
                                {{ $s->created_at->format('d/m/Y H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-12 text-slate-400">Belum ada survei kepuasan yang diisi customer.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
