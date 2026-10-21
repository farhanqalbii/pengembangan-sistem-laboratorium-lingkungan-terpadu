@extends('layouts.app')

@section('title', 'Dashboard Manajer Lab - LIMS 2026')
@section('page-title', 'Dashboard Executive & Verifikasi Manajer Lab')

@section('content')
<div class="space-y-6">
    <!-- Executive KPI Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5 stagger-grid">
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/90 shadow-sm flex items-center space-x-3.5 card-hover">
            <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg font-bold flex-shrink-0">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-0.5">Antrean Verifikasi</p>
                <h3 class="text-xl sm:text-2xl font-black text-slate-800">{{ $pendingVerifications->count() }}</h3>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/90 shadow-sm flex items-center space-x-3.5 card-hover">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg font-bold flex-shrink-0">
                <i class="fa-solid fa-file-contract"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-0.5">Total COA Terbit</p>
                <h3 class="text-xl sm:text-2xl font-black text-slate-800">{{ $totalCertificates }}</h3>
            </div>
        </div>

        <a href="{{ route('manager.reports.satisfaction') }}" class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/90 shadow-sm flex items-center justify-between card-hover group cursor-pointer transition-all">
            <div class="flex items-center space-x-3.5">
                <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg font-bold flex-shrink-0 group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-star"></i>
                </div>
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-0.5">Indeks Kepuasan (IKP)</p>
                    <h3 class="text-xl sm:text-2xl font-black text-slate-800">{{ number_format($avgRating, 2) }} / 5.00</h3>
                </div>
            </div>
            <i class="fa-solid fa-chevron-right text-xs text-slate-400 group-hover:text-amber-600 transition-colors"></i>
        </a>

        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/90 shadow-sm flex items-center space-x-3.5 card-hover">
            <div class="w-11 h-11 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-lg font-bold flex-shrink-0">
                <i class="fa-solid fa-vials"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-0.5">Parameter Diuji</p>
                <h3 class="text-xl sm:text-2xl font-black text-slate-800">{{ $testedParametersCount }}</h3>
            </div>
        </div>
    </div>

    <!-- Pending Technical Verifications Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden anim-fade-up anim-delay-2 card-hover">
        <div class="p-5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800">Antrean Verifikasi Teknis & Penerbitan COA</h3>
            <a href="{{ route('manager.verifications.index') }}" class="text-xs font-semibold text-emerald-600 hover:underline">Lihat Semua &rarr;</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[720px]">
                <thead class="bg-slate-100 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-4 sm:px-5 py-3">Kode Sampel</th>
                        <th class="px-4 sm:px-5 py-3">Customer</th>
                        <th class="px-4 sm:px-5 py-3">Jenis Sampel</th>
                        <th class="px-4 sm:px-5 py-3">Parameter Diuji</th>
                        <th class="px-4 sm:px-5 py-3">Status Compliance</th>
                        <th class="px-4 sm:px-5 py-3 text-center">Aksi Verifikasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($pendingVerifications as $s)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 sm:px-5 py-3 font-mono font-bold text-emerald-600 bg-emerald-50/50">
                                {{ $s->sample_code }}
                            </td>
                            <td class="px-4 sm:px-5 py-3">
                                <div class="font-bold text-slate-800">{{ $s->customer->company_name ?? $s->customer->name }}</div>
                            </td>
                            <td class="px-4 sm:px-5 py-3 font-semibold text-slate-700">
                                {{ $s->sample_type }}
                            </td>
                            <td class="px-4 sm:px-5 py-3">
                                <span class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-full font-semibold">
                                    {{ $s->sampleParameters->count() }} Parameter
                                </span>
                            </td>
                            <td class="px-4 sm:px-5 py-3">
                                @php
                                    $nonCompliant = $s->sampleParameters->where('compliance_status', 'Tidak Memenuhi')->count();
                                @endphp
                                @if($nonCompliant > 0)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                        {{ $nonCompliant }} Tidak Memenuhi BM
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        Semua Memenuhi BM
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-center space-x-2">
                                <a href="{{ route('manager.verifications.show', $s->id) }}" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-lg shadow-sm text-xs">
                                    Review & Approval <i class="fa-solid fa-user-check ml-1"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-slate-400">Semua sampel telah diverifikasi. Antrean bersih!</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Published COA Certificates Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden anim-fade-up anim-delay-3 card-hover">
        <div class="p-4 sm:p-5 border-b border-slate-200 bg-slate-50 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-bold">
                    <i class="fa-solid fa-file-contract"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Berkas Sertifikat Hasil Uji (COA) yang Telah Terbit</h3>
                    <p class="text-[11px] text-slate-400">Daftar dokumen resmi COA yang telah disahkan dan siap diunduh</p>
                </div>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full self-start sm:self-auto">
                {{ $recentCertificates->count() }} Berkas Terakhir
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[700px]">
                <thead class="bg-slate-100 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-4 sm:px-5 py-3">Dokumen</th>
                        <th class="px-4 sm:px-5 py-3">Nomor Sertifikat</th>
                        <th class="px-4 sm:px-5 py-3">Kode & Jenis Sampel</th>
                        <th class="px-4 sm:px-5 py-3">Customer</th>
                        <th class="px-4 sm:px-5 py-3">Tanggal Terbit</th>
                        <th class="px-4 sm:px-5 py-3 text-center">Aksi Dokumen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($recentCertificates as $cert)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 sm:px-5 py-3">
                                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-base font-bold shrink-0">
                                    <i class="fa-solid fa-file-contract"></i>
                                </div>
                            </td>
                            <td class="px-4 sm:px-5 py-3 font-mono font-bold text-emerald-700">
                                {{ $cert->certificate_number }}
                            </td>
                            <td class="px-4 sm:px-5 py-3">
                                <div class="font-bold text-slate-800">{{ $cert->sample?->sample_code ?? '-' }}</div>
                                <div class="text-[11px] text-slate-500">{{ $cert->sample?->sample_type ?? '-' }}</div>
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-slate-700 font-medium">
                                {{ $cert->customer?->company_name ?? $cert->customer?->name ?? '-' }}
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-slate-600">
                                {{ $cert->issued_at ? $cert->issued_at->format('d M Y H:i') : $cert->created_at->format('d M Y H:i') }} WIB
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-center">
                                <a href="{{ route('customer.certificates.download', $cert->id) }}" class="inline-flex items-center px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-lg shadow-sm transition-all gap-1.5">
                                    <i class="fa-solid fa-file-pdf"></i> Unduh PDF
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-10 text-slate-400">Belum ada sertifikat COA yang diterbitkan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
