@extends('layouts.app')

@section('title', 'Survei Kepuasan Pelanggan - LIMS 2026')
@section('page-title', 'Survei Kepuasan Pelanggan (IKP)')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-sm">
        <div class="text-center mb-5">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center text-2xl mx-auto mb-2">
                <i class="fa-solid fa-star"></i>
            </div>
            <h2 class="text-lg font-bold text-slate-800">Evaluasi Kepuasan Layanan</h2>
            <p class="text-xs text-slate-500 mt-1">Ulasan Anda sangat berharga untuk meningkatkan kualitas pelayanan UPTD Laboratorium Lingkungan Terpadu 2026.</p>
        </div>

        @if(!$isEligible)
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 text-center space-y-3">
                <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center mx-auto text-lg">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-amber-900">Layanan Belum Selesai / Belum Tersedia</h3>
                    <p class="text-xs text-amber-700 mt-1 max-w-md mx-auto">
                        Sesuai standar operasional laboratorium, formulir survei kepuasan pelanggan (IKP) hanya dapat diisi setelah pengujian sampel berstatus <strong>Selesai</strong> atau peminjaman alat telah <strong>Dikembalikan / Selesai</strong>.
                    </p>
                </div>
                <div class="pt-2">
                    <a href="{{ route('customer.dashboard') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs rounded-xl shadow-sm transition-colors">
                        <i class="fa-solid fa-arrow-left text-[10px]"></i> Kembali ke Dashboard
                    </a>
                </div>
            </div>
        @else
            @if($activeService)
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 mb-4 text-xs flex items-center justify-between">
                    <div>
                        <span class="text-slate-400 font-semibold uppercase text-[10px]">Layanan yang Dinilai:</span>
                        <div class="font-bold text-slate-800">
                            {{ $serviceType }}: {{ $serviceType === 'Pengujian' ? ($activeService->sample_code . ' (' . $activeService->sample_type . ')') : ($activeService->kode_peminjaman . ' (' . $activeService->purpose . ')') }}
                        </div>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                        {{ $activeService->status }}
                    </span>
                </div>
            @endif

            <form action="{{ route('customer.satisfaction.store') }}" method="POST" x-data="{ rating: {{ $existing->rating ?? 5 }} }" class="space-y-5">
                @csrf
                <input type="hidden" name="service_type" value="{{ $serviceType }}">
                <input type="hidden" name="service_id" value="{{ $serviceId }}">

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2.5 text-center">Beri Rating Penilaian Layanan</label>
                    <div class="flex items-center justify-center space-x-3 text-2xl sm:text-3xl py-1.5">
                        <template x-for="star in [1, 2, 3, 4, 5]">
                            <button type="button" 
                                    @click="rating = star" 
                                    class="focus:outline-none transition-all duration-200 transform hover:scale-125 hover:-translate-y-1 active:scale-90 p-1 cursor-pointer">
                                <i class="fa-solid fa-star transition-all duration-200" 
                                   :class="star <= rating ? 'text-amber-400 drop-shadow-[0_4px_10px_rgba(251,191,36,0.45)] scale-105' : 'text-slate-200 hover:text-amber-200'"></i>
                            </button>
                        </template>
                    </div>
                    <input type="hidden" name="rating" :value="rating">
                    <p class="text-center text-xs font-bold text-slate-600 mt-1.5 transition-all" x-text="rating + ' / 5 Bintang'"></p>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Ulasan / Kritik & Saran (Umpan Balik)</label>
                    <textarea name="feedback" rows="3" placeholder="Tuliskan pendapat Anda tentang kecepatan, keakuratan hasil, atau sikap pelayanan staf kami..." class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">{{ old('feedback', $existing->saran_masukan ?? ($existing->feedback ?? '')) }}</textarea>
                </div>

                <div class="space-y-2.5">
                    <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-600/30 transition-all cursor-pointer">
                        Kirim Penilaian Kepuasan Pelanggan <i class="fa-solid fa-paper-plane ml-1"></i>
                    </button>
                    <div class="text-center">
                        <a href="{{ route('customer.dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 transition-colors py-1">
                            <i class="fa-solid fa-arrow-left text-[10px]"></i> Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection
