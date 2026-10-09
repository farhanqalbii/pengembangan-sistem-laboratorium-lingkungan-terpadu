@extends('layouts.app')

@section('title', 'Daftar Pengujian Sampel - LIMS 2026')
@section('page-title', 'Pengujian Sampel Saya')

@section('content')
<div class="space-y-6">
    <!-- Action & Filter Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm anim-fade-up anim-delay-1 card-hover">
        <form action="{{ route('customer.samples.index') }}" method="GET" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode/jenis sampel/lokasi..." class="bg-slate-50 border border-slate-300 text-xs rounded-xl px-4 py-2 text-slate-800 w-full sm:w-64 focus:outline-none focus:border-emerald-500">
            <select name="status" onchange="this.form.submit()" class="bg-slate-50 border border-slate-300 text-xs rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:border-emerald-500">
                <option value="">-- Semua Status --</option>
                <option value="Diajukan" {{ request('status') === 'Diajukan' ? 'selected' : '' }}>Diajukan</option>
                <option value="Diterima" {{ request('status') === 'Diterima' ? 'selected' : '' }}>Diterima</option>
                <option value="Diproses" {{ request('status') === 'Diproses' ? 'selected' : '' }}>Diproses</option>
                <option value="Diuji" {{ request('status') === 'Diuji' ? 'selected' : '' }}>Diuji</option>
                <option value="Menunggu Verifikasi" {{ request('status') === 'Menunggu Verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                <option value="Selesai" {{ request('status') === 'Selesai' ? 'selected' : '' }}>Selesai</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-xl text-xs font-semibold hover:bg-slate-700">Filter</button>
        </form>

        <a href="{{ route('customer.samples.create') }}" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-emerald-600/30 flex items-center justify-center">
            <i class="fa-solid fa-plus-circle mr-2"></i> Ajukan Uji Sampel Baru
        </a>
    </div>

    <!-- Samples Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden anim-fade-up anim-delay-2 card-hover">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-4 sm:px-5 py-3">Kode Sampel</th>
                        <th class="px-4 sm:px-5 py-3">Jenis & Lokasi Sampel</th>
                        <th class="px-4 sm:px-5 py-3">Tgl Sampling</th>
                        <th class="px-4 sm:px-5 py-3">Parameter Uji</th>
                        <th class="px-4 sm:px-5 py-3">Status</th>
                        <th class="px-4 sm:px-5 py-3 text-center">Aksi / Sertifikat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($samples as $sample)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-4 sm:px-5 py-3">
                                <span class="font-mono font-bold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-md">{{ $sample->sample_code }}</span>
                            </td>
                            <td class="px-4 sm:px-5 py-3">
                                <div class="font-bold text-slate-800 text-xs sm:text-sm">{{ $sample->sample_type }}</div>
                                <div class="text-slate-500 text-[11px]"><i class="fa-solid fa-location-dot mr-1"></i> {{ $sample->sampling_location }}</div>
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-slate-600">
                                {{ $sample->sampling_date ? $sample->sampling_date->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-4 sm:px-5 py-3">
                                <span class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-full font-semibold text-[11px]">
                                    {{ $sample->sampleParameters->count() }} Parameter
                                </span>
                            </td>
                            <td class="px-4 sm:px-5 py-3">
                                <span class="px-2.5 py-1 rounded-full font-bold text-[11px] {{ $sample->status_badge_class }}">
                                    {{ $sample->status }}
                                </span>
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-center space-x-1.5 whitespace-nowrap">
                                <a href="{{ route('customer.samples.show', $sample->id) }}" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg text-xs transition-all inline-flex items-center">
                                    <i class="fa-solid fa-eye mr-1 text-[11px]"></i> Detail
                                </a>
                                @if($sample->certificate)
                                    <a href="{{ route('customer.certificates.download', $sample->certificate->id) }}" class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold rounded-lg text-xs shadow-sm transition-all inline-flex items-center">
                                        <i class="fa-solid fa-file-pdf mr-1 text-[11px]"></i> COA PDF
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-slate-400">Belum ada data sampel diajukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($samples->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50">
                {{ $samples->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
