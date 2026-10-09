@extends('layouts.app')

@section('title', 'Input Hasil Uji ' . $sample->sample_code . ' - Staff')
@section('page-title', 'Form Input Hasil Pengujian Laboratorium')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-sm flex items-center justify-between">
        <div>
            <span class="font-mono font-bold text-xs sm:text-sm text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded">{{ $sample->sample_code }}</span>
            <h1 class="text-base sm:text-lg font-bold text-slate-800 mt-1">{{ $sample->sample_type }}</h1>
            <p class="text-xs text-slate-500">Customer: {{ $sample->customer->company_name ?? $sample->customer->name }} | Lokasi: {{ $sample->sampling_location }}</p>
        </div>
        <a href="{{ route('staff.samples.show', $sample->id) }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-colors">Kembali</a>
    </div>

    <form action="{{ route('staff.samples.store-results', $sample->id) }}" method="POST" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden space-y-5">
        @csrf

        <div class="p-4 sm:p-5 border-b border-slate-200 bg-slate-50">
            <h3 class="text-sm font-bold text-slate-800">Isi Nilai Hasil Analisis Laboratorium</h3>
            <p class="text-xs text-slate-500 mt-0.5">Sistem akan secara otomatis membandingkan nilai hasil uji dengan Ambang Baku Mutu yang berlaku.</p>
        </div>

        <div class="p-4 sm:p-5 space-y-4">
            @foreach($sample->sampleParameters as $idx => $sp)
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 space-y-3">
                    <input type="hidden" name="results[{{ $idx }}][sample_parameter_id]" value="{{ $sp->id }}">

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <span class="font-bold text-sm text-slate-800 block">{{ $sp->parameter->name ?? 'Parameter' }}</span>
                            <span class="text-xs text-slate-500 block">Metode: {{ $sp->test_method }}</span>
                        </div>
                        <div class="bg-white px-2.5 py-1 rounded-lg border border-slate-200 text-xs">
                            <span class="text-slate-400">Baku Mutu:</span>
                            <span class="font-mono font-bold text-slate-700 ml-1">{{ $sp->quality_standard_summary }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nilai Hasil Pengujian <span class="text-rose-500">*</span></label>
                            <div class="flex items-center space-x-2">
                                <input type="text" name="results[{{ $idx }}][test_value]" value="{{ old("results.{$idx}.test_value", $sp->test_value) }}" placeholder="Contoh: 7.4 atau 18.5..." required class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-xs font-mono font-bold text-slate-800 focus:outline-none focus:border-emerald-500">
                                <span class="text-xs font-bold text-slate-500 min-w-[40px]">{{ $sp->parameter->unit ?? '-' }}</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Analisis (Opsional)</label>
                            <input type="text" name="results[{{ $idx }}][notes]" value="{{ old("results.{$idx}.notes", $sp->notes) }}" placeholder="Keterangan pengujian..." class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
            <span class="text-xs text-slate-500 font-semibold"><i class="fa-solid fa-shield-halved text-emerald-600 mr-1"></i> Otomatisasi Check Baku Mutu Aktif</span>
            <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-600/30">
                Simpan & Teruskan ke Manajer Lab <i class="fa-solid fa-paper-plane ml-1"></i>
            </button>
        </div>
    </form>
</div>
@endsection
