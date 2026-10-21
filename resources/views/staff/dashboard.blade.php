@extends('layouts.app')

@section('title', 'Dashboard Staf Analis - LIMS 2026')
@section('page-title', 'Dashboard Staf & Analis Laboratorium')

@section('content')
<div class="space-y-6">
    <!-- Top Alert Metrics (Stagger Animation) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5 stagger-grid">
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/90 shadow-sm flex items-center space-x-3.5 card-hover cursor-default">
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg font-bold transition-transform group-hover:scale-110 flex-shrink-0">
                <i class="fa-solid fa-vial-circle-check"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-0.5">Sampel Perlu Diterima</p>
                <h3 class="text-xl sm:text-2xl font-black text-slate-800">{{ $incomingSamples->count() }}</h3>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/90 shadow-sm flex items-center space-x-3.5 card-hover cursor-default">
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg font-bold flex-shrink-0">
                <i class="fa-solid fa-flask"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-0.5">Sampel Dalam Pengujian</p>
                <h3 class="text-xl sm:text-2xl font-black text-slate-800">{{ $processingSamples->count() }}</h3>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/90 shadow-sm flex items-center space-x-3.5 card-hover cursor-default">
            <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg font-bold flex-shrink-0 {{ $lowStockEquipment->count() > 0 ? 'animate-pulse-subtle' : '' }}">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-0.5">Stok Alat Kritis</p>
                <h3 class="text-xl sm:text-2xl font-black text-slate-800">{{ $lowStockEquipment->count() }}</h3>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/90 shadow-sm flex items-center space-x-3.5 card-hover cursor-default">
            <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg font-bold flex-shrink-0">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-0.5">Jadwal Kalibrasi (30hr)</p>
                <h3 class="text-xl sm:text-2xl font-black text-slate-800">{{ $upcomingCalibrations->count() }}</h3>
            </div>
        </div>
    </div>

    <!-- Main Workspace Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 anim-fade-up anim-delay-2">
        <!-- Sampel Masuk & Dalam Proses -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm card-hover">
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-bold">
                        <i class="fa-solid fa-flask-vial"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-800">Sampel Perlu Diproses & Input Hasil</h2>
                        <p class="text-xs text-slate-400">Daftar sampel yang siap diuji atau menunggu input data</p>
                    </div>
                </div>
                <a href="{{ route('staff.samples.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 hover:underline">Kelola Semua &rarr;</a>
            </div>

            <div class="space-y-3">
                @forelse($processingSamples as $s)
                    <div class="p-4 rounded-xl bg-slate-50/70 hover:bg-slate-50 border border-slate-200/80 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="space-y-1.5 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-xs text-emerald-700 bg-emerald-100/70 px-2 py-0.5 rounded border border-emerald-200/70">{{ $s->sample_code }}</span>
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $s->status_badge_class }}">{{ $s->status }}</span>
                            </div>
                            <h4 class="font-bold text-slate-800 text-sm truncate">{{ $s->sample_type }}</h4>
                            <p class="text-xs text-slate-500 flex items-center gap-2">
                                <span class="truncate"><i class="fa-regular fa-building text-[10px] mr-1 text-slate-400"></i>{{ $s->customer->company_name ?? $s->customer->name }}</span>
                                @if($s->sampleParameters->count() > 0)
                                    <span class="text-slate-300">•</span>
                                    <span class="text-slate-400 shrink-0"><i class="fa-solid fa-vials text-[10px] mr-1 text-slate-400"></i>{{ $s->sampleParameters->count() }} Parameter</span>
                                @endif
                            </p>
                        </div>
                        <div class="shrink-0 flex items-center gap-2 self-end sm:self-center">
                            <a href="{{ route('staff.samples.show', $s->id) }}" class="px-3 py-1.5 bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 font-semibold text-xs rounded-lg transition-colors shadow-2xs">
                                Detail
                            </a>
                            <a href="{{ route('staff.samples.input-results', $s->id) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-lg shadow-sm transition-all hover:shadow-emerald-600/20">
                                <i class="fa-solid fa-pen-to-square text-[11px]"></i> Input Hasil
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-200 py-10 px-4 text-center bg-slate-50/50">
                        <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 mx-auto flex items-center justify-center text-sm mb-2 border border-emerald-100">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <p class="text-xs font-bold text-slate-700">Antrean Bersih</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Tidak ada sampel aktif yang perlu diproses saat ini.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Peringatan Stok & Kalibrasi Alat -->
        <div class="space-y-6">
            <!-- Kalibrasi -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm card-hover">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-bold">
                            <i class="fa-solid fa-calendar-check"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-800">Pengingat Kalibrasi & Servis Instrumen</h2>
                            <p class="text-xs text-slate-400">Jadwal kalibrasi terdekat dalam 30 hari ke depan</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-2.5">
                    @forelse($upcomingCalibrations as $cal)
                        <div class="p-3.5 rounded-xl bg-indigo-50/60 border border-indigo-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                            <div>
                                <span class="font-bold text-indigo-950 block text-xs">{{ $cal->equipment->name }}</span>
                                <span class="text-[11px] text-indigo-700">Jadwal: {{ $cal->next_calibration_date ? $cal->next_calibration_date->format('d F Y') : '-' }}</span>
                            </div>
                            <a href="{{ route('staff.equipment.show', $cal->equipment_id) }}" class="self-start sm:self-auto px-2.5 py-1 bg-white border border-indigo-200 hover:bg-indigo-100/50 text-indigo-700 font-bold text-xs rounded-lg transition-colors">
                                Detail Alat
                            </a>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-slate-200 py-6 px-4 text-center bg-slate-50/50">
                            <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 mx-auto flex items-center justify-center text-sm mb-2 border border-emerald-100">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                            <p class="text-xs font-bold text-slate-700">Semua Instrumen Terkalibrasi</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">Tidak ada jadwal kalibrasi mendesak dalam 30 hari ke depan.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Stok Kritis -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm card-hover">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-sm font-bold">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-800">Stok Minimum Inventori Kritis</h2>
                            <p class="text-xs text-slate-400">Peralatan yang perlu pengadaan / penambahan unit</p>
                        </div>
                    </div>
                    <a href="{{ route('staff.equipment.index') }}" class="text-xs font-semibold text-rose-600 hover:underline">Inventori &rarr;</a>
                </div>

                <div class="space-y-2.5">
                    @forelse($lowStockEquipment as $eq)
                        <div class="p-3.5 rounded-xl bg-rose-50/60 border border-rose-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                            <div>
                                <span class="font-bold text-rose-950 block text-xs">{{ $eq->name }} ({{ $eq->code }})</span>
                                <span class="text-[11px] text-rose-700">Tersisa: <strong>{{ $eq->quantity }} unit</strong> (Min: {{ $eq->min_stock }})</span>
                            </div>
                            <a href="{{ route('staff.equipment.edit', $eq->id) }}" class="self-start sm:self-auto px-3 py-1.5 bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs rounded-lg transition-colors shadow-sm">
                                Restok
                            </a>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-slate-200 py-6 px-4 text-center bg-slate-50/50">
                            <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 mx-auto flex items-center justify-center text-sm mb-2 border border-emerald-100">
                                <i class="fa-solid fa-boxes-stacked"></i>
                            </div>
                            <p class="text-xs font-bold text-slate-700">Kapasitas Stok Aman</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">Semua stok peralatan laboratorium berada di atas batas minimum.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
