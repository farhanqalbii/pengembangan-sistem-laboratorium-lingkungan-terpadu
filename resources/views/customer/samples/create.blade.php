@extends('layouts.app')

@section('title', 'Pengajuan Uji Sampel Baru - LIMS 2026')
@section('page-title', 'Form Pengajuan Pengujian Sampel')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-sm">
        <h2 class="text-base sm:text-lg font-bold text-slate-800 mb-1">Formulir Pendaftaran Sampel</h2>
        <p class="text-xs text-slate-500 mb-5">Pilih jenis sampel dan parameter pengujian yang Anda butuhkan.</p>

        <form action="{{ route('customer.samples.store') }}" method="POST" x-data="sampleForm()" @submit="if ($el.checkValidity()) submitting = true" x-init="window.addEventListener('pageshow', () => submitting = false)" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Jenis Sampel <span class="text-rose-500">*</span></label>
                    <input type="text" name="sample_type" value="{{ old('sample_type') }}" placeholder="Contoh: Air Limbah Outlet, Air Sumur, Air Sungai..." required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Lokasi Sampling / Pengambilan <span class="text-rose-500">*</span></label>
                    <input type="text" name="sampling_location" value="{{ old('sampling_location') }}" placeholder="Contoh: WWTP Unit 2, Outlet Pabrik Bekasi..." required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Tanggal Pengambilan Sampel <span class="text-rose-500">*</span></label>
                    <input type="date" name="sampling_date" value="{{ old('sampling_date', date('Y-m-d')) }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Catatan Khusus / Deskripsi Tambahan</label>
                    <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Opsional: Keterangan tambahan untuk teknisi lab" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                </div>
            </div>

            <!-- Selection of Parameters -->
            <div class="border-t border-slate-200 pt-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 mb-3">
                    <h3 class="text-sm font-bold text-slate-800">Pilih Parameter Pengujian & Baku Mutu <span class="text-rose-500">*</span></h3>
                    <span class="text-xs text-slate-500 font-medium">Centang parameter yang akan diuji</span>
                </div>

                <div class="space-y-4">
                    @foreach($parameters as $category => $paramList)
                        <div class="bg-slate-50 rounded-xl p-3.5 border border-slate-200">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-700 mb-2.5 border-b border-slate-200 pb-1.5">Kategori: {{ $category }}</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                @foreach($paramList as $p)
                                    <label class="flex items-start space-x-3 p-2.5 rounded-lg bg-white border border-slate-200 hover:border-emerald-400 cursor-pointer transition-all">
                                        <input type="checkbox" name="parameters[]" value="{{ $p->id }}" data-price="{{ $p->price }}" @change="updateTotal()" class="mt-0.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                        <div class="text-xs">
                                            <span class="font-bold text-slate-800 block">{{ $p->name }}</span>
                                            <span class="text-slate-500 text-[11px] block">Metode: {{ $p->test_method }}</span>
                                            <span class="text-emerald-600 font-bold block mt-0.5">Rp {{ number_format($p->price, 0, ',', '.') }}</span>
                                            @if($p->qualityStandard)
                                                <span class="text-[10px] text-slate-400 block mt-0.5">Baku Mutu: {{ $p->qualityStandard->summary }}</span>
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Total Estimated Price Sticky Box -->
            <div class="p-4 sm:p-5 rounded-xl bg-slate-900 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xl">
                <div>
                    <p class="text-[11px] text-slate-400 uppercase font-bold tracking-wider">Estimasi Biaya Pengujian</p>
                    <h3 class="text-xl sm:text-2xl font-extrabold text-emerald-400" x-text="formatRupiah(totalPrice)">Rp 0</h3>
                </div>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 w-full sm:w-auto">
                    <a href="{{ route('customer.samples.index') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-xl text-xs font-semibold transition-all text-center">
                        Batal
                    </a>
                    <button type="submit" 
                        :disabled="submitting"
                        :class="{ 'opacity-75 pointer-events-none': submitting }"
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/30 text-xs transition-all flex items-center justify-center gap-2 cursor-pointer disabled:cursor-not-allowed">
                        <i class="fa-solid fa-spinner fa-spin" x-show="submitting" x-cloak></i>
                        <i class="fa-solid fa-paper-plane" x-show="!submitting"></i>
                        <span x-text="submitting ? 'Mengirim Pengajuan...' : 'Kirim Pengajuan Sampel'">Kirim Pengajuan Sampel</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function sampleForm() {
        return {
            totalPrice: 0,
            submitting: false,
            updateTotal() {
                let total = 0;
                document.querySelectorAll('input[name="parameters[]"]:checked').forEach(cb => {
                    total += parseFloat(cb.getAttribute('data-price') || 0);
                });
                this.totalPrice = total;
            },
            formatRupiah(num) {
                return 'Rp ' + num.toLocaleString('id-ID');
            }
        }
    }
</script>
@endsection
