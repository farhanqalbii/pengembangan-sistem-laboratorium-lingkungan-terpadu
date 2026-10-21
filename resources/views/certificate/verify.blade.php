@extends('layouts.app')

@section('title', 'Verifikasi Keabsahan Sertifikat COA - Laboratorium Lingkungan')
@section('page-title', 'Verifikasi Keabsahan Digital COA')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Verification Status Banner -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-6 shadow-sm text-center space-y-4">
        @if($isValid && $certificate)
            <div class="w-16 h-16 mx-auto rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-3xl shadow-sm">
                <i class="fa-solid fa-certificate"></i>
            </div>
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                    <i class="fa-solid fa-circle-check"></i> Dokumen Terverifikasi & Sah
                </span>
                <h1 class="text-xl font-bold text-slate-800 mt-2">{{ $certificate->nomor_sertifikat }}</h1>
                <p class="text-xs text-slate-500 mt-1">Certificate of Analysis diterbitkan resmi oleh UPTD Laboratorium Lingkungan Hidup</p>
            </div>

            <!-- Details Card -->
            <div class="text-left bg-slate-50 rounded-xl p-4 border border-slate-200 text-xs space-y-2">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center py-1.5 border-b border-slate-200 gap-0.5">
                    <span class="text-slate-500">Kode Sampel:</span>
                    <span class="font-mono font-bold text-slate-800">{{ $certificate->sample->sample_code }}</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center py-1.5 border-b border-slate-200 gap-0.5">
                    <span class="text-slate-500">Nama Pelanggan / Perusahaan:</span>
                    <span class="font-bold text-slate-800 break-words">{{ $certificate->customer->company_name ?? $certificate->customer->name }}</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center py-1.5 border-b border-slate-200 gap-0.5">
                    <span class="text-slate-500">Tanggal Pengesahan:</span>
                    <span class="font-semibold text-slate-800">{{ $certificate->tanggal_verifikasi?->format('d F Y, H:i') }} WIB</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center py-1.5 border-b border-slate-200 gap-0.5">
                    <span class="text-slate-500">Disahkan Oleh (Manajer Lab):</span>
                    <span class="font-bold text-emerald-700">{{ $certificate->verifier->name ?? 'Dr. Hendra Gunawan, M.Si' }}</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center py-1.5 border-b border-slate-200 gap-0.5">
                    <span class="text-slate-500">Jumlah Parameter Diuji:</span>
                    <span class="font-bold text-slate-800">{{ $certificate->sample->sampleParameters->count() }} Parameter SNI</span>
                </div>
                <div class="pt-2">
                    <span class="text-slate-500 block mb-1">Kode Tanda Tangan Digital (HMAC-SHA256):</span>
                    <span class="font-mono text-[11px] text-slate-700 break-all bg-white px-2.5 py-1.5 rounded-lg border border-slate-200 block">
                        {{ $certificate->hash_tanda_tangan }}
                    </span>
                </div>
            </div>
        @elseif($searchHash)
            <div class="w-16 h-16 mx-auto rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-3xl">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300">
                    <i class="fa-solid fa-circle-xmark"></i> Dokumen Tidak Valid / Tidak Ditemukan
                </span>
                <h2 class="text-lg font-bold text-slate-800 mt-2">Sertifikat Tidak Ditemukan</h2>
                <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                    Kode verifikasi atau hash digital yang Anda masukkan tidak cocok dengan dokumen resmi yang terdaftar di basis data kami.
                </p>
            </div>
        @else
            <div class="w-16 h-16 mx-auto rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-3xl">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <h2 class="text-lg font-bold text-slate-800">Verifikasi Integritas Dokumen COA</h2>
            <p class="text-xs text-slate-500 max-w-md mx-auto">
                Masukkan kode nomor sertifikat atau hash digital untuk memeriksa keaslian dan status final Certificate of Analysis.
            </p>
        @endif

        <!-- Search Form -->
        <form action="{{ route('certificate.verify') }}" method="GET" class="pt-4 flex flex-col sm:flex-row gap-2 max-w-lg mx-auto">
            <input type="text" name="hash" value="{{ $searchHash }}" placeholder="Masukkan Hash / Nomor COA..." class="flex-1 text-xs border border-slate-300 rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-emerald-500 font-mono">
            <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-sm transition-all text-center">
                Verifikasi
            </button>
        </form>
    </div>
</div>
@endsection
