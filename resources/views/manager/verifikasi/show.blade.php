@extends('layouts.app')

@section('title', 'Review Verifikasi Sampel ' . $sample->sample_code . ' - Manajer')
@section('page-title', 'Review & Approval Verifikasi Teknis')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{ showRejectModal: false }" x-effect="document.body.classList.toggle('overflow-hidden', showRejectModal); document.body.classList.toggle('modal-open', showRejectModal)">
    <!-- Header Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3 mb-1">
                <span class="font-mono font-bold text-base sm:text-lg text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200">{{ $sample->sample_code }}</span>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $sample->status_badge_class }}">{{ $sample->status }}</span>
            </div>
            <h1 class="text-lg sm:text-xl font-bold text-slate-800">{{ $sample->sample_type }}</h1>
            <p class="text-xs text-slate-500 mt-1">Customer: <strong>{{ $sample->customer->company_name ?? $sample->customer->name }}</strong> | Sampling: {{ $sample->sampling_location }}</p>
        </div>

        <a href="{{ route('manager.verifications.index') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl inline-flex items-center justify-center transition-colors">Kembali</a>
    </div>

    <!-- Parameter Results Verification Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-200 bg-slate-50">
            <h3 class="text-sm font-bold text-slate-800">Evaluasi Data Pengujian Analis</h3>
            <p class="text-xs text-slate-500">Periksa keabsahan nilai hasil uji dan kesesuaian baku mutu lingkungan.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[650px]">
                <thead class="bg-slate-100 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-4 sm:px-5 py-3">Parameter Uji</th>
                        <th class="px-4 sm:px-5 py-3">Metode Uji</th>
                        <th class="px-4 sm:px-5 py-3">Analis</th>
                        <th class="px-4 sm:px-5 py-3 text-center">Baku Mutu</th>
                        <th class="px-4 sm:px-5 py-3 text-center">Hasil Uji</th>
                        <th class="px-4 sm:px-5 py-3 text-center">Compliance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @foreach($sample->sampleParameters as $sp)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 sm:px-5 py-3 font-bold text-slate-800">
                                {{ $sp->parameter->name ?? 'Parameter' }}
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-slate-500">
                                {{ $sp->test_method }}
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-slate-700">
                                {{ $sp->analyst->name ?? '-' }}
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-center font-mono text-slate-600">
                                {{ $sp->quality_standard_summary }}
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-center font-mono font-bold text-emerald-700 text-sm">
                                {{ $sp->test_value }} {{ $sp->unit }}
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-center">
                                @if($sp->compliance_status === 'Memenuhi')
                                    <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 rounded-full font-bold text-[11px]">Memenuhi</span>
                                @elseif($sp->compliance_status === 'Tidak Memenuhi')
                                    <span class="px-2.5 py-0.5 bg-rose-100 text-rose-800 rounded-full font-bold text-[11px]">Tidak Memenuhi</span>
                                @else
                                    <span class="px-2.5 py-0.5 bg-slate-100 text-slate-600 rounded-full font-semibold text-[11px]">Pending</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if($sample->status === 'Menunggu Verifikasi')
        @if($sample->rejection_reason)
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-amber-900 text-xs flex items-start gap-3">
                <i class="fa-solid fa-clock-rotate-left text-amber-600 text-base mt-0.5 shrink-0"></i>
                <div>
                    <h4 class="font-bold text-sm text-amber-800">Catatan Permintaan Revisi Sebelumnya:</h4>
                    <p class="mt-0.5 italic text-amber-900 font-medium">"{{ $sample->rejection_reason }}"</p>
                    <p class="mt-1 text-[11px] text-amber-700">Staf analis telah memperbarui nilai pengujian dan mengirimkannya kembali untuk ditinjau ulang.</p>
                </div>
            </div>
        @endif

        <!-- Manager Decision Action Box (Hanya Tampil Saat Menunggu Verifikasi) -->
        <div class="bg-slate-900 text-white rounded-2xl p-4 sm:p-5 shadow-xl flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
                <h3 class="text-sm sm:text-base font-bold text-emerald-400">Keputusan Verifikasi Manajer Lab</h3>
                <p class="text-xs text-slate-300 mt-0.5">Approval akan secara otomatis menanda-tangani digital Sertifikat (COA) dan menerbitkannya untuk customer.</p>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 w-full sm:w-auto">
                <button type="button" @click="showRejectModal = true" class="w-full sm:w-auto px-4 py-2 bg-rose-600/30 hover:bg-rose-600/50 text-rose-300 border border-rose-500/50 font-bold text-xs rounded-xl transition-all text-center">
                    <i class="fa-solid fa-rotate-left mr-1.5"></i> Tolak / Minta Revisi
                </button>

                <form action="{{ route('manager.verifications.approve', $sample) }}" method="POST" x-data="{ submitting: false }" @submit="submitting = true" x-init="window.addEventListener('pageshow', () => submitting = false)" class="w-full sm:w-auto">
                    @csrf
                    <button type="submit" 
                        :disabled="submitting"
                        :class="{ 'opacity-75 pointer-events-none': submitting }"
                        class="w-full sm:w-auto px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-600/30 transition-all flex items-center justify-center gap-2 cursor-pointer disabled:cursor-not-allowed">
                        <i class="fa-solid fa-spinner fa-spin" x-show="submitting" x-cloak></i>
                        <i class="fa-solid fa-signature" x-show="!submitting"></i>
                        <span x-text="submitting ? 'Memproses & Menandatangani...' : 'Approve & Terbitkan COA'">Approve & Terbitkan COA</span>
                    </button>
                </form>
            </div>
        </div>
    @elseif($sample->status === 'Selesai' || $sample->certificate)
        <!-- Kartu Notifikasi Verifikasi Selesai & Sertifikat COA Terbit -->
        <div class="bg-gradient-to-r from-emerald-950/80 to-slate-900 border border-emerald-500/50 rounded-2xl p-4 sm:p-5 shadow-xl text-white">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 flex items-center justify-center shrink-0 text-2xl shadow-inner">
                        <i class="fa-solid fa-stamp"></i>
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">
                                <i class="fa-solid fa-circle-check mr-1"></i> Verifikasi Teknis Selesai
                            </span>
                            @if($sample->certificate)
                                <span class="font-mono text-xs font-semibold text-emerald-300 bg-slate-800/80 px-2.5 py-0.5 rounded-full border border-slate-700">
                                    {{ $sample->certificate->certificate_number }}
                                </span>
                            @endif
                        </div>
                        <h3 class="text-base font-bold text-white">Sertifikat Hasil Uji (COA) Resmi Telah Diterbitkan</h3>
                        <p class="text-xs text-slate-300 mt-1">
                            Diverifikasi oleh: <strong class="text-emerald-300">{{ $sample->certificate?->verifier?->name ?? auth()->user()->name }}</strong> 
                            pada {{ $sample->certificate?->verified_at ? $sample->certificate->verified_at->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }} WIB.
                        </p>
                        @if($sample->certificate?->digital_signature_hash)
                            <p class="text-[11px] font-mono text-slate-400 mt-2 bg-slate-950/60 p-2 rounded-lg border border-slate-800 break-all">
                                <span class="text-emerald-400 font-semibold"><i class="fa-solid fa-fingerprint mr-1"></i> Tanda Tangan Digital (SHA-256):</span><br>
                                {{ $sample->certificate->digital_signature_hash }}
                            </p>
                        @endif
                    </div>
                </div>

                @if($sample->certificate)
                    <div class="shrink-0 w-full sm:w-auto">
                        <a href="{{ route('customer.certificates.download', $sample->certificate) }}" class="w-full sm:w-auto px-5 py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-600/40 transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-file-pdf text-sm"></i> Unduh Dokumen COA (PDF)
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @elseif($sample->status === 'Revisi')
        <!-- Banner Peringatan Status Revisi -->
        <div class="bg-amber-950/50 border border-amber-500/40 rounded-2xl p-6 shadow-xl text-white">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-amber-500/20 border border-amber-500/40 text-amber-400 flex items-center justify-center shrink-0 text-xl">
                    <i class="fa-solid fa-rotate-left"></i>
                </div>
                <div>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                        Status Revisi
                    </span>
                    <h3 class="text-base font-bold text-white mt-1">Permintaan Revisi Telah Diteruskan ke Staf Analis</h3>
                    <p class="text-xs text-slate-300 mt-1">
                        Catatan revisi: <strong class="text-amber-200">"{{ $sample->rejection_reason ?? '-' }}"</strong>
                    </p>
                    <p class="text-[11px] text-slate-400 mt-1">Sampel akan kembali muncul di antrean verifikasi setelah staf analis menginput ulang hasil pengujian.</p>
                </div>
            </div>
        </div>
    @else
        <!-- Info Status Lainnya -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 text-slate-300 text-xs flex items-center gap-3">
            <i class="fa-solid fa-circle-info text-blue-400 text-base"></i>
            <div>
                Status saat ini: <strong class="text-white">{{ $sample->status }}</strong>. Pengujian belum siap untuk diverifikasi oleh Manajer Lab.
            </div>
        </div>
    @endif

    <!-- Modal Rejection Reason -->
    <template x-teleport="body">
        <div x-show="showRejectModal" 
             x-cloak
             @keydown.escape.window="showRejectModal = false"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-[99999] flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
             style="z-index: 99999;">
            <div @click.outside="showRejectModal = false" 
                 x-show="showRejectModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                 class="bg-white rounded-3xl max-w-md w-full p-4 sm:p-6 shadow-2xl space-y-4 my-auto relative border border-slate-200">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-bold text-slate-800 text-base">Alasan Penolakan / Permintaan Revisi</h3>
                    <button @click="showRejectModal = false" class="text-slate-400 hover:text-slate-700 cursor-pointer"><i class="fa-solid fa-xmark text-lg"></i></button>
                </div>
                <p class="text-xs text-slate-500">Berikan catatan perbaikan secara spesifik untuk staf teknis / analis laboratorium.</p>

                <form action="{{ route('manager.verifications.reject', $sample) }}" method="POST" x-data="{ submitting: false }" @submit="if ($el.checkValidity()) submitting = true" x-init="window.addEventListener('pageshow', () => submitting = false)" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Revisi <span class="text-rose-500">*</span></label>
                        <textarea name="rejection_reason" rows="3" required placeholder="Contoh: Parameter pH perlu diuji ulang karena berada di luar ambang batas..." class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-xs text-slate-800"></textarea>
                    </div>

                    <div class="flex justify-end space-x-3 pt-2">
                        <button type="button" @click="showRejectModal = false; submitting = false" :disabled="submitting" class="px-4 py-2 bg-slate-100 text-slate-700 text-xs font-semibold rounded-xl cursor-pointer disabled:opacity-50">Batal</button>
                        <button type="submit" 
                            :disabled="submitting"
                            :class="{ 'opacity-75 pointer-events-none': submitting }"
                            class="px-5 py-2 bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold rounded-xl shadow-md flex items-center gap-1.5 cursor-pointer disabled:cursor-not-allowed">
                            <i class="fa-solid fa-spinner fa-spin" x-show="submitting" x-cloak></i>
                            <span x-text="submitting ? 'Mengirim Revisi...' : 'Kirim Catatan Revisi'">Kirim Catatan Revisi</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
@endsection
