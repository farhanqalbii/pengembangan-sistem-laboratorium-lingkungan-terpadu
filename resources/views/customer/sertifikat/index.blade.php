@extends('layouts.app')

@section('title', 'Sertifikat Hasil Uji (COA) - LIMS 2026')
@section('page-title', 'Sertifikat Hasil Uji (COA)')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden anim-fade-up anim-delay-1 card-hover">
        <div class="p-5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800">Daftar COA Terbit</h3>
            <span class="text-xs text-slate-500">Resmi & Terverifikasi Digital</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[620px]">
                <thead class="bg-slate-100 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-4 sm:px-5 py-3">Nomor Sertifikat</th>
                        <th class="px-4 sm:px-5 py-3">Kode & Jenis Sampel</th>
                        <th class="px-4 sm:px-5 py-3">Diverifikasi Oleh</th>
                        <th class="px-4 sm:px-5 py-3">Tgl Terbit</th>
                        <th class="px-4 sm:px-5 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($certificates as $cert)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 sm:px-5 py-3 font-mono font-bold text-emerald-700">
                                {{ $cert->certificate_number }}
                            </td>
                            <td class="px-4 sm:px-5 py-3">
                                <div class="font-bold text-slate-800">{{ $cert->sample->sample_code ?? '-' }}</div>
                                <div class="text-slate-500 text-[11px]">{{ $cert->sample->sample_type ?? '-' }}</div>
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-slate-700">
                                <i class="fa-solid fa-user-check text-emerald-600 mr-1"></i> {{ $cert->verifier->name ?? 'Manajer Lab' }}
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-slate-600">
                                {{ $cert->issued_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-center space-x-1.5 whitespace-nowrap">
                                <a href="{{ route('customer.certificates.show', $cert->id) }}" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg text-xs">
                                    Lihat Detail
                                </a>
                                <a href="{{ route('customer.certificates.download', $cert->id) }}" class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-lg text-xs shadow-sm">
                                    <i class="fa-solid fa-download mr-1 text-[11px]"></i> PDF
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-12 text-slate-400">Belum ada Sertifikat Hasil Uji (COA) yang diterbitkan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($certificates->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50">
                {{ $certificates->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
