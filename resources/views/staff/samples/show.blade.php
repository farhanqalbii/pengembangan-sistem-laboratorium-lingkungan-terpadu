@extends('layouts.app')

@section('title', 'Detail Sampel ' . $sample->sample_code . ' - Staff')
@section('page-title', 'Penerimaan & Penugasan Analis Sampel')

@section('content')
<div class="space-y-6">
    <!-- Header Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-2 mb-1">
                <span class="font-mono font-bold text-base sm:text-lg text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200">{{ $sample->sample_code }}</span>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $sample->status_badge_class }}">{{ $sample->status }}</span>
                @php
                    $samplePay = $sample->payments->first();
                @endphp
                @if($samplePay && $samplePay->status === 'Berhasil')
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                        <i class="fa-solid fa-circle-check mr-1 text-emerald-600"></i> Pembayaran: LUNAS ({{ $samplePay->payment_method }})
                    </span>
                @elseif($samplePay)
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300">
                        <i class="fa-solid fa-clock mr-1 text-amber-600"></i> Pembayaran: Belum Lunas
                    </span>
                @endif
            </div>
            <h1 class="text-lg sm:text-xl font-bold text-slate-800">{{ $sample->sample_type }}</h1>
            <p class="text-xs text-slate-500 mt-1">Customer: <strong>{{ $sample->customer->company_name ?? $sample->customer->name }}</strong> | Telp: {{ $sample->customer->phone }}</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('staff.samples.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 font-semibold text-xs rounded-xl shadow-2xs transition-colors cursor-pointer">
                <i class="fa-solid fa-arrow-left text-[11px] text-slate-400"></i> Kembali
            </a>

            @if($sample->status === 'Diajukan')
                <form action="{{ route('staff.samples.receive', $sample->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="received_date" value="{{ date('Y-m-d H:i:s') }}">
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-600/30 flex items-center transition-colors cursor-pointer">
                        <i class="fa-solid fa-box-archive mr-2"></i> Konfirmasi Penerimaan Sampel
                    </button>
                </form>
            @endif

            @if(in_array($sample->status, ['Diterima', 'Diproses', 'Diuji', 'Revisi']))
                <a href="{{ route('staff.samples.input-results', $sample->id) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-600/30 flex items-center transition-colors cursor-pointer">
                    <i class="fa-solid fa-pen-to-square mr-2"></i> Input Hasil Pengujian
                </a>
            @endif
        </div>
    </div>

    @if($sample->status === 'Revisi' && $sample->rejection_reason)
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs">
            <h4 class="font-bold text-sm mb-1 text-rose-800"><i class="fa-solid fa-circle-exclamation mr-1"></i> Catatan Revisi dari Manajer Lab:</h4>
            <p class="pl-5 italic">{{ $sample->rejection_reason }}</p>
        </div>
    @endif

    <!-- Assign Analyst & Equipment Form (SPL-F-003) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Penugasan Analis & Alokasi Instrumen Uji (SPL-F-003)</h3>
                <p class="text-xs text-slate-500 mt-0.5">Setiap parameter uji wajib memiliki metode uji, analis penanggung jawab, dan instrumen laboratorium yang valid.</p>
            </div>
            <span class="text-xs text-slate-500 font-semibold bg-white px-2.5 py-1 rounded-lg border border-slate-200">
                Total {{ $sample->sampleParameters->count() }} Parameter
            </span>
        </div>

        @if (isset($errors) && $errors->any())
            <div class="m-4 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                <div class="font-bold mb-1 flex items-center gap-1.5 text-rose-900">
                    <i class="fa-solid fa-triangle-exclamation text-rose-600"></i> Alokasi Gagal: Data belum lengkap atau tidak valid
                </div>
                <ul class="list-disc list-inside space-y-1 pl-1 text-[11px] text-rose-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(!in_array($sample->status, ['Diterima', 'Diproses', 'Diuji', 'Revisi']))
            <div class="p-4 text-xs text-amber-800 bg-amber-50 border-b border-amber-200 flex items-center gap-2">
                <i class="fa-solid fa-circle-info text-amber-600"></i>
                <span>Status sampel saat ini <strong>{{ $sample->status }}</strong>. Form penugasan hanya dapat diperbarui ketika sampel berstatus <strong>Diterima</strong>, <strong>Diproses</strong>, <strong>Diuji</strong>, atau <strong>Revisi</strong>.</span>
            </div>
        @endif

        <form action="{{ route('staff.samples.assign', $sample->id) }}" method="POST">
            @csrf
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-4 sm:px-5 py-3">Parameter Uji</th>
                            <th class="px-4 sm:px-5 py-3">Metode Uji & Baku Mutu</th>
                            <th class="px-4 sm:px-5 py-3">Analis Penanggung Jawab <span class="text-rose-500">*</span></th>
                            <th class="px-4 sm:px-5 py-3">Instrumen/Alat Pengujian <span class="text-rose-500">*</span></th>
                            <th class="px-4 sm:px-5 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($sample->sampleParameters as $idx => $sp)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 sm:px-5 py-3 font-bold text-slate-800">
                                    {{ $sp->parameter->name ?? 'Parameter' }}
                                    <input type="hidden" name="assignments[{{ $idx }}][sample_parameter_id]" value="{{ $sp->id }}">
                                </td>
                                <td class="px-4 sm:px-5 py-3 text-slate-500">
                                    <div class="font-semibold text-slate-700">{{ $sp->test_method ?: ($sp->parameter->test_method ?? '-') }}</div>
                                    <div class="text-[11px] text-slate-400">BM: {{ $sp->quality_standard_summary ?: ($sp->parameter->qualityStandard->summary ?? 'N/A') }}</div>
                                </td>
                                <td class="px-4 sm:px-5 py-3 min-w-[200px]">
                                    <select name="assignments[{{ $idx }}][analyst_id]" required class="bg-white border @error("assignments.{$idx}.analyst_id") border-rose-500 @else border-slate-300 @enderror rounded-lg px-2.5 py-1.5 text-xs text-slate-800 w-full focus:ring-2 focus:ring-emerald-500">
                                        <option value="" disabled {{ old("assignments.{$idx}.analyst_id", $sp->analyst_id) ? '' : 'selected' }}>-- Pilih Analis (Wajib) --</option>
                                        @foreach($analysts as $an)
                                            <option value="{{ $an->id }}" {{ old("assignments.{$idx}.analyst_id", $sp->analyst_id) == $an->id ? 'selected' : '' }}>
                                                {{ $an->name }} ({{ ucfirst($an->role) }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error("assignments.{$idx}.analyst_id")
                                        <p class="text-rose-500 text-[11px] mt-1 font-semibold">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="px-4 sm:px-5 py-3 min-w-[220px]">
                                    <select name="assignments[{{ $idx }}][equipment_id]" required class="bg-white border @error("assignments.{$idx}.equipment_id") border-rose-500 @else border-slate-300 @enderror rounded-lg px-2.5 py-1.5 text-xs text-slate-800 w-full focus:ring-2 focus:ring-emerald-500">
                                        <option value="" disabled {{ old("assignments.{$idx}.equipment_id", $sp->equipment_id ?? $sp->id_peralatan) ? '' : 'selected' }}>-- Pilih Instrumen/Alat (Wajib) --</option>
                                        @foreach($equipmentList as $eq)
                                            <option value="{{ $eq->id }}" {{ old("assignments.{$idx}.equipment_id", $sp->equipment_id ?? $sp->id_peralatan) == $eq->id ? 'selected' : '' }}>
                                                {{ $eq->name ?? $eq->nama }} ({{ $eq->code ?? $eq->kode }}) - {{ $eq->status }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error("assignments.{$idx}.equipment_id")
                                        <p class="text-rose-500 text-[11px] mt-1 font-semibold">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="px-4 sm:px-5 py-3 text-center font-semibold text-slate-600">
                                    <span class="px-2 py-0.5 rounded-full text-[11px] bg-slate-100 text-slate-700">
                                        {{ $sp->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-400">
                                    Belum ada parameter uji yang dipilih untuk sampel ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 bg-slate-50 border-t border-slate-200 text-right">
                @if(in_array($sample->status, ['Diterima', 'Diproses', 'Diuji', 'Revisi']))
                    <button type="submit" class="px-5 py-2 bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs rounded-xl shadow transition-colors cursor-pointer">
                        <i class="fa-solid fa-floppy-disk mr-1.5"></i> Simpan Penugasan Analis & Alat
                    </button>
                @else
                    <button type="button" disabled class="px-5 py-2 bg-slate-300 text-slate-500 font-bold text-xs rounded-xl cursor-not-allowed">
                        Penugasan Dikunci (Status: {{ $sample->status }})
                    </button>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection
