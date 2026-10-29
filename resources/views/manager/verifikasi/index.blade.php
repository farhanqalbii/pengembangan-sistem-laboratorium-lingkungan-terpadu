@extends('layouts.app')

@section('title', 'Verifikasi Technical Sampel - Manajer')
@section('page-title', 'Daftar Verifikasi Technical Hasil Analisis')

@section('content')
<div class="space-y-6">
    <!-- Tab Filter Status -->
    <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 pb-3">
        <a href="{{ route('manager.verifications.index', ['status' => 'Menunggu Verifikasi']) }}" 
           class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ $currentStatus === 'Menunggu Verifikasi' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
            <i class="fa-solid fa-clock-rotate-left"></i>
            <span>Menunggu Verifikasi</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ $currentStatus === 'Menunggu Verifikasi' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800' }}">
                {{ $countPending }}
            </span>
        </a>

        <a href="{{ route('manager.verifications.index', ['status' => 'Selesai']) }}" 
           class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ $currentStatus === 'Selesai' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
            <i class="fa-solid fa-circle-check"></i>
            <span>Riwayat Selesai & COA</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ $currentStatus === 'Selesai' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-700' }}">
                {{ $countDone }}
            </span>
        </a>

        <a href="{{ route('manager.verifications.index', ['status' => 'Revisi']) }}" 
           class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ $currentStatus === 'Revisi' ? 'bg-amber-600 text-white shadow-md shadow-amber-600/30' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
            <i class="fa-solid fa-rotate-left"></i>
            <span>Dalam Revisi</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ $currentStatus === 'Revisi' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800' }}">
                {{ $countRevision }}
            </span>
        </a>

        <a href="{{ route('manager.verifications.index', ['status' => 'all']) }}" 
           class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ $currentStatus === 'all' ? 'bg-slate-800 text-white shadow-md' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
            <i class="fa-solid fa-list-check"></i>
            <span>Semua Sampel</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ $currentStatus === 'all' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-700' }}">
                {{ $countAll }}
            </span>
        </a>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-200 bg-slate-50 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h3 class="text-sm font-bold text-slate-800">
                    @if($currentStatus === 'Menunggu Verifikasi')
                        Antrean Sampel Menunggu Verifikasi Teknis Manajer
                    @elseif($currentStatus === 'Selesai')
                        Daftar Sampel yang Telah Selesai Diverifikasi & Terbit COA
                    @elseif($currentStatus === 'Revisi')
                        Daftar Sampel yang Dikembalikan untuk Revisi Analis
                    @else
                        Seluruh Sampel Tahap Verifikasi & Hasil Analisis
                    @endif
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Otorisasi penerbitan Certificate of Analysis (COA) dengan tanda tangan digital resmi</p>
            </div>
            <span class="text-xs font-semibold text-slate-500 bg-white px-3 py-1 rounded-lg border border-slate-200 self-start sm:self-auto">
                Total: {{ $samples->total() }} Data
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[720px]">
                <thead class="bg-slate-100 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-4 sm:px-5 py-3">Kode Sampel</th>
                        <th class="px-4 sm:px-5 py-3">Customer</th>
                        <th class="px-4 sm:px-5 py-3">Jenis Sampel</th>
                        <th class="px-4 sm:px-5 py-3">Tgl Sampling</th>
                        <th class="px-4 sm:px-5 py-3">Parameter</th>
                        <th class="px-4 sm:px-5 py-3 text-center">Status</th>
                        <th class="px-4 sm:px-5 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($samples as $sample)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 sm:px-5 py-3 whitespace-nowrap">
                                <a href="{{ route('manager.verifications.show', $sample) }}" 
                                   class="font-mono font-bold text-xs text-emerald-600 hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-300 hover:underline transition-colors inline-flex items-center gap-1.5"
                                   title="Lihat Detail Verifikasi {{ $sample->sample_code }}">
                                    <i class="fa-solid fa-flask text-emerald-500/70 text-[11px]"></i>
                                    <span>{{ $sample->sample_code }}</span>
                                </a>
                            </td>
                            <td class="px-4 sm:px-5 py-3 font-bold text-slate-800">
                                {{ $sample->customer->company_name ?? $sample->customer->name }}
                            </td>
                            <td class="px-4 sm:px-5 py-3 font-semibold text-slate-700">
                                {{ $sample->sample_type }}
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-slate-600">
                                {{ $sample->sampling_date ? $sample->sampling_date->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-4 sm:px-5 py-3">
                                <span class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-full font-semibold text-[11px]">
                                    {{ $sample->sampleParameters->count() }} Parameter
                                </span>
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-center">
                                @if($sample->status === 'Menunggu Verifikasi')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        Menunggu Verifikasi
                                    </span>
                                @elseif($sample->status === 'Selesai')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        <i class="fa-solid fa-check mr-1"></i> Selesai (COA)
                                    </span>
                                @elseif($sample->status === 'Revisi')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                        Revisi
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700">
                                        {{ $sample->status }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-center whitespace-nowrap">
                                @if($sample->status === 'Menunggu Verifikasi')
                                    <a href="{{ route('manager.verifications.show', $sample) }}" 
                                       class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-lg text-xs shadow-sm inline-flex items-center gap-1.5 transition-all">
                                        <i class="fa-solid fa-user-check"></i> Periksa & Approve
                                    </a>
                                @elseif($sample->status === 'Selesai')
                                    <a href="{{ route('manager.verifications.show', $sample) }}" 
                                       class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-lg text-xs shadow-sm inline-flex items-center gap-1.5 transition-all">
                                        <i class="fa-solid fa-file-lines text-emerald-400"></i> Detail / COA
                                    </a>
                                @elseif($sample->status === 'Revisi')
                                    <a href="{{ route('manager.verifications.show', $sample) }}" 
                                       class="px-3 py-1.5 bg-amber-600 hover:bg-amber-500 text-white font-bold rounded-lg text-xs shadow-sm inline-flex items-center gap-1.5 transition-all">
                                        <i class="fa-solid fa-rotate-left"></i> Catatan Revisi
                                    </a>
                                @else
                                    <a href="{{ route('manager.verifications.show', $sample) }}" 
                                       class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-xs shadow-sm inline-flex items-center gap-1.5 transition-all">
                                        <i class="fa-solid fa-eye"></i> Detail
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-slate-400">
                                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-xl">
                                    <i class="fa-solid fa-inbox"></i>
                                </div>
                                @if($currentStatus === 'Menunggu Verifikasi')
                                    <p class="font-semibold text-slate-600">Tidak ada sampel dalam antrean verifikasi saat ini.</p>
                                    <p class="text-xs text-slate-400 mt-1">Seluruh sampel telah diverifikasi atau masih dalam proses pengujian oleh analis.</p>
                                @elseif($currentStatus === 'Selesai')
                                    <p class="font-semibold text-slate-600">Belum ada sampel yang berstatus selesai diverifikasi.</p>
                                @elseif($currentStatus === 'Revisi')
                                    <p class="font-semibold text-slate-600">Tidak ada sampel yang sedang dalam status revisi.</p>
                                @else
                                    <p class="font-semibold text-slate-600">Tidak ada data sampel ditemukan.</p>
                                @endif
                            </td>
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
