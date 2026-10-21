@extends('layouts.app')

@section('title', 'Laporan Pengujian Sampel - Admin')
@section('page-title', 'Laporan Operasional Pengujian Sampel')

@section('content')
<div class="space-y-6">
    <!-- Filter & Export Card -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <form action="{{ route('admin.reports.samples') }}" method="GET" class="flex flex-col sm:flex-row sm:items-center gap-3 w-full sm:w-auto">
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
        </form>

        <a href="{{ route('admin.reports.samples', array_merge(request()->all(), ['export' => 'pdf'])) }}" class="w-full sm:w-auto px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-emerald-600/30 flex items-center justify-center">
            <i class="fa-solid fa-file-pdf mr-2"></i> Export Laporan PDF
        </a>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-200 bg-slate-50 flex flex-col sm:flex-row sm:items-center justify-between gap-1 sm:gap-2">
            <h3 class="text-sm font-bold text-slate-800">Rekapitulasi Sampel Masuk (Total {{ $samples->count() }} Sampel)</h3>
            <span class="text-xs text-slate-500">Periode: {{ date('d/m/Y', strtotime($startDate)) }} - {{ date('d/m/Y', strtotime($endDate)) }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[700px]">
                <thead class="bg-slate-100 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-4 sm:px-5 py-3">Kode Sampel</th>
                        <th class="px-4 sm:px-5 py-3">Customer</th>
                        <th class="px-4 sm:px-5 py-3">Jenis Sampel</th>
                        <th class="px-4 sm:px-5 py-3">Lokasi Sampling</th>
                        <th class="px-4 sm:px-5 py-3">Tgl Pengajuan</th>
                        <th class="px-4 sm:px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($samples as $s)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 sm:px-5 py-3 font-mono font-bold text-emerald-600">
                                {{ $s->sample_code }}
                            </td>
                            <td class="px-4 sm:px-5 py-3 font-bold text-slate-800">
                                {{ $s->customer->company_name ?? $s->customer->name }}
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-slate-700">
                                {{ $s->sample_type }}
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-slate-500">
                                {{ $s->sampling_location }}
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-slate-600">
                                {{ $s->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 sm:px-5 py-3">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $s->status_badge_class }}">
                                    {{ $s->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-slate-400">Tidak ada data sampel pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
