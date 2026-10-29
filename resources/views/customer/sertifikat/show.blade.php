@extends('layouts.app')

@section('title', 'Detail Sertifikat COA ' . $certificate->certificate_number . ' - LIMS 2026')
@section('page-title', 'Detail Sertifikat Hasil Uji (COA)')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4 anim-fade-up anim-delay-1 card-hover">
        <div>
            <span class="text-xs font-bold text-emerald-600 uppercase tracking-widest block mb-1">CERTIFICATE OF ANALYSIS (COA)</span>
            <h1 class="text-xl sm:text-2xl font-mono font-bold text-slate-800">{{ $certificate->certificate_number }}</h1>
            <p class="text-xs text-slate-500 mt-1">Diterbitkan pada: {{ $certificate->issued_at->format('d F Y') }} oleh {{ $certificate->verifier->name ?? 'Manajer Lab' }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('customer.certificates.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 font-semibold text-xs rounded-xl shadow-2xs transition-colors cursor-pointer">
                <i class="fa-solid fa-arrow-left text-[11px] text-slate-400"></i> Kembali
            </a>
            <a href="{{ route('customer.certificates.download', $certificate) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-600/30 flex items-center transition-colors">
                <i class="fa-solid fa-file-pdf mr-2"></i> Unduh File PDF Resmi
            </a>
        </div>
    </div>

    <!-- Certificate Preview Frame Card -->
    <div class="bg-slate-900 text-white rounded-2xl p-4 sm:p-5 shadow-xl space-y-5 transition-all duration-300 hover:shadow-2xl border border-slate-800 anim-fade-up anim-delay-2 card-hover">
        <div class="text-center border-b border-slate-800 pb-4">
            <h2 class="text-lg font-bold uppercase text-emerald-400">UPTD LABORATORIUM LINGKUNGAN TERPADU 2026</h2>
            <p class="text-xs text-slate-400">Sertifikat Hasil Uji Laboratorium Pengujian Lingkungan</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div>
                <span class="text-slate-400 block">Kode Sampel:</span>
                <span class="font-bold text-white text-sm">{{ $certificate->sample->sample_code }}</span>
            </div>
            <div>
                <span class="text-slate-400 block">Jenis Sampel:</span>
                <span class="font-bold text-white text-sm">{{ $certificate->sample->sample_type }}</span>
            </div>
            <div>
                <span class="text-slate-400 block">Lokasi Sampling:</span>
                <span class="font-bold text-white">{{ $certificate->sample->sampling_location }}</span>
            </div>
            <div>
                <span class="text-slate-400 block">Digital Hash Verification:</span>
                <span class="font-mono text-[10px] text-emerald-400 break-all">{{ $certificate->digital_signature_hash }}</span>
            </div>
        </div>

        <div class="border-t border-slate-800 pt-4">
            <h3 class="text-xs font-bold uppercase text-slate-300 mb-3">Ringkasan Hasil Analisis:</h3>
            <div class="space-y-2">
                @foreach($certificate->sample->sampleParameters as $sp)
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 p-3 rounded-xl bg-slate-800/80 text-xs border border-slate-700">
                        <div>
                            <span class="font-bold text-white block">{{ $sp->parameter->name ?? 'Parameter' }}</span>
                            <span class="text-[11px] text-slate-400">Metode: {{ $sp->test_method }} | Baku Mutu: {{ $sp->quality_standard_summary }}</span>
                        </div>
                        <div class="self-start sm:self-auto text-left sm:text-right">
                            <span class="font-mono font-bold text-sm text-emerald-400 block">{{ $sp->test_value }} {{ $sp->unit }}</span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full inline-block mt-0.5 {{ $sp->compliance_status === 'Memenuhi' ? 'bg-emerald-950 text-emerald-300 border border-emerald-800' : 'bg-rose-950 text-rose-300 border border-rose-800' }}">
                                {{ $sp->compliance_status }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
