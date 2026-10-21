@extends('layouts.app')

@section('title', 'Detail Sampel ' . $sample->sample_code . ' - LIMS 2026')
@section('page-title', 'Detail & Status Pengujian Sampel')

@section('content')
<div class="space-y-6">
    <!-- Header Summary Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3 mb-1">
                <span class="font-mono font-bold text-base sm:text-lg text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200">{{ $sample->sample_code }}</span>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $sample->status_badge_class }}">{{ $sample->status }}</span>
            </div>
            <h1 class="text-lg sm:text-xl font-bold text-slate-800">{{ $sample->sample_type }}</h1>
            <p class="text-xs text-slate-500 mt-1"><i class="fa-solid fa-location-dot mr-1"></i> {{ $sample->sampling_location }} | Diajukan pada: {{ $sample->created_at->format('d F Y H:i') }}</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('customer.samples.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 font-semibold text-xs rounded-xl shadow-2xs transition-colors cursor-pointer">
                <i class="fa-solid fa-arrow-left text-[11px] text-slate-400"></i> Kembali
            </a>
            @if($sample->certificate)
                <a href="{{ route('customer.certificates.download', $sample->certificate->id) }}" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-600/30 flex items-center transition-colors">
                    <i class="fa-solid fa-file-pdf mr-2"></i> Unduh COA Resmi (PDF)
                </a>
            @endif
            @if($sample->status === 'Selesai')
                <a href="{{ route('customer.satisfaction.create', ['type' => 'Pengujian', 'id' => $sample->id]) }}" class="px-3.5 py-2 bg-amber-500 hover:bg-amber-400 text-white font-bold text-xs rounded-xl shadow-md flex items-center transition-colors">
                    <i class="fa-solid fa-star mr-2"></i> Beri Penilaian
                </a>
            @endif
            @if($sample->payments->first() && $sample->payments->first()->invoice)
                <a href="{{ route('customer.invoices.download', $sample->payments->first()->invoice->id) }}" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs rounded-xl shadow-md flex items-center transition-colors">
                    <i class="fa-solid fa-file-invoice mr-2"></i> Unduh Invoice
                </a>
            @endif
        </div>
    </div>

    <!-- Progress Workflow Stepper -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-sm">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-5">Alur Progress Pengujian Sampel</h3>
        @php
            $steps = ['Diajukan', 'Diterima', 'Diuji', 'Menunggu Verifikasi', 'Selesai'];
            $currentIndex = array_search($sample->status, $steps);
            if($currentIndex === false) $currentIndex = 2;
        @endphp
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2.5">
            @foreach($steps as $idx => $step)
                <div class="flex flex-col items-center text-center">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-xs mb-1.5 transition-all
                        {{ $idx <= $currentIndex ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30' : 'bg-slate-100 text-slate-400' }}
                    ">
                        @if($idx < $currentIndex)
                            <i class="fa-solid fa-check"></i>
                        @else
                            {{ $idx + 1 }}
                        @endif
                    </div>
                    <span class="text-xs font-bold {{ $idx <= $currentIndex ? 'text-slate-800' : 'text-slate-400' }}">{{ $step }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Payment & Invoice Status Card -->
    @php
        $latestPayment = $sample->payments->first();
        $invoice = $latestPayment?->invoice;
    @endphp

    @if($latestPayment)
        <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-sm relative overflow-hidden" x-data="{ showPayModal: false }" x-effect="document.body.classList.toggle('overflow-hidden', showPayModal); document.body.classList.toggle('modal-open', showPayModal)">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Tagihan & Pembayaran Layanan</span>
                        @if($latestPayment->status === 'Berhasil' || $invoice?->status === 'Lunas')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                <i class="fa-solid fa-circle-check mr-1.5 text-emerald-600"></i> LUNAS
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                <i class="fa-solid fa-clock mr-1.5 text-amber-600"></i> MENUNGGU PEMBAYARAN
                            </span>
                        @endif
                    </div>

                    <div class="flex flex-wrap items-baseline gap-3">
                        <span class="text-2xl sm:text-3xl font-extrabold text-slate-900 break-words">
                            Rp {{ number_format($latestPayment->amount, 0, ',', '.') }}
                        </span>
                        @if($invoice)
                            <span class="text-xs font-mono text-slate-500 bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200">
                                No. Invoice: {{ $invoice->invoice_number }}
                            </span>
                        @endif
                    </div>

                    <p class="text-xs text-slate-500 flex flex-wrap items-center gap-x-4 gap-y-1">
                        <span><i class="fa-solid fa-receipt text-slate-400 mr-1"></i> Kode Pembayaran: <strong>{{ $latestPayment->payment_code }}</strong></span>
                        @if($latestPayment->status === 'Berhasil' && $latestPayment->paid_at)
                            <span class="text-emerald-700 font-semibold"><i class="fa-solid fa-calendar-check mr-1"></i> Dibayar pada: {{ $latestPayment->paid_at->format('d/m/Y H:i') }} via <strong>{{ $latestPayment->payment_method }}</strong></span>
                        @else
                            <span class="text-amber-700 font-medium"><i class="fa-solid fa-wallet mr-1"></i> Pilihan Metode: <strong>QRIS, Virtual Account, atau Transfer Bank</strong></span>
                        @endif
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    @if($invoice)
                        <a href="{{ route('customer.invoices.download', $invoice->id) }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl border border-slate-200 transition-all flex items-center gap-2">
                            <i class="fa-solid fa-file-pdf text-rose-500"></i>
                            <span>Unduh Invoice (PDF)</span>
                        </a>
                    @endif

                    @if($latestPayment->status !== 'Berhasil' && $invoice?->status !== 'Lunas')
                        <button type="button" @click="showPayModal = true" class="px-6 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-500 hover:to-teal-600 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-700/25 transition-all flex items-center gap-2 active:scale-95 cursor-pointer">
                            <i class="fa-solid fa-qrcode text-sm"></i>
                            <span>Lihat QRIS & Cara Bayar</span>
                        </button>
                    @endif
                </div>
            </div>

            <!-- Modal Pembayaran Interaktif -->
            <template x-teleport="body">
                <div x-show="showPayModal" 
                     x-cloak
                     @keydown.escape.window="showPayModal = false"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-[99999] flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
                     style="z-index: 99999;">
                    <div @click.outside="showPayModal = false" 
                         x-show="showPayModal"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                         x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                         class="bg-white rounded-3xl max-w-lg w-full p-4 sm:p-7 shadow-2xl space-y-5 border border-slate-200 my-auto relative max-h-[90vh] overflow-y-auto">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <div>
                                <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                                    <i class="fa-solid fa-wallet text-emerald-600"></i>
                                    <span>Petunjuk & Informasi Pembayaran</span>
                                </h3>
                                <p class="text-xs text-slate-500 mt-0.5">Pilih metode pembayaran yang Anda inginkan.</p>
                            </div>
                            <button type="button" @click="showPayModal = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition-colors cursor-pointer">
                                <i class="fa-solid fa-xmark text-sm"></i>
                            </button>
                        </div>

                        <!-- Payment Summary Box -->
                        <div class="bg-gradient-to-br from-slate-900 to-slate-800 text-white p-4 sm:p-5 rounded-2xl space-y-1.5 shadow-inner">
                            <span class="text-slate-400 text-xs uppercase font-medium">Total Yang Harus Dibayar:</span>
                            <div class="text-2xl sm:text-3xl font-extrabold text-emerald-400 break-words">
                                Rp {{ number_format($latestPayment->amount, 0, ',', '.') }}
                            </div>
                            <div class="text-xs text-slate-300 flex items-center justify-between pt-1 border-t border-slate-700/60 mt-2">
                                <span>Sampel: {{ $sample->sample_code }}</span>
                                <span class="font-mono text-cyan-300">{{ $latestPayment->payment_code }}</span>
                            </div>
                        </div>

                        <div class="space-y-4" x-data="{ 
                            processing: false,
                            syncing: false,
                            errorMessage: '',
                            infoMessage: '',
                            payNow() {
                                if (this.processing) return;
                                this.processing = true;
                                this.errorMessage = '';
                                this.infoMessage = '';
                                fetch('{{ route('customer.payments.process', $latestPayment->id) }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json'
                                    }
                                })
                                .then(res => res.json().then(data => ({ status: res.status, ok: res.ok, data })))
                                .then(({ ok, data }) => {
                                    this.processing = false;
                                    if (!ok || !data.success) {
                                        this.errorMessage = data.message || 'Gagal memproses transaksi ke Payment Gateway.';
                                        return;
                                    }

                                    if (window.snap && data.token) {
                                        this.infoMessage = 'Membuka popup pembayaran Snap...';
                                        window.snap.pay(data.token, {
                                            onSuccess: (result) => {
                                                this.checkStatus();
                                            },
                                            onPending: (result) => {
                                                this.checkStatus();
                                            },
                                            onError: (result) => {
                                                this.errorMessage = 'Pembayaran dibatalkan atau gagal.';
                                            },
                                            onClose: () => {
                                                this.checkStatus();
                                            }
                                        });
                                    } else if (data.redirect_url) {
                                        this.infoMessage = 'Mengarahkan ke halaman resmi Payment Gateway...';
                                        window.location.href = data.redirect_url;
                                    } else {
                                        this.infoMessage = 'Transaksi berhasil didaftarkan. Silakan selesaikan pembayaran.';
                                    }
                                })
                                .catch(err => {
                                    this.processing = false;
                                    this.errorMessage = 'Terjadi kesalahan jaringan saat menghubungi server.';
                                });
                            },
                            checkStatus() {
                                if (this.syncing) return;
                                this.syncing = true;
                                this.errorMessage = '';
                                this.infoMessage = 'Memeriksa status pembayaran dari gateway...';
                                fetch('{{ route('customer.payments.check-status', $latestPayment->id) }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json'
                                    }
                                })
                                .then(res => res.json())
                                .then(res => {
                                    this.syncing = false;
                                    if (res.status === 'Berhasil') {
                                        this.infoMessage = 'Pembayaran LUNAS terkonfirmasi! Memperbarui halaman...';
                                        setTimeout(() => window.location.reload(), 1000);
                                    } else {
                                        this.infoMessage = res.message || 'Status pembayaran belum terkonfirmasi lunas.';
                                    }
                                })
                                .catch(() => {
                                    this.syncing = false;
                                    this.errorMessage = 'Gagal memeriksa status pembayaran dari server.';
                                });
                            }
                        }">
                            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-2 text-xs">
                                <div class="flex items-center gap-2 text-slate-700 font-bold">
                                    <i class="fa-solid fa-shield-halved text-emerald-600"></i>
                                    <span>Koneksi Midtrans Sandbox (QRIS, VA Bank, Kartu)</span>
                                </div>
                                <p class="text-[11px] text-slate-500 leading-relaxed">
                                    Tagihan pengujian sampel ini akan diproses melalui Payment Gateway terverifikasi. Setelah Anda menyelesaikan pembayaran di gateway, status sampel akan otomatis diperbarui melalui webhook resmi atau klik sinkronisasi status.
                                </p>
                            </div>

                            <!-- Error Message Box -->
                            <div x-show="errorMessage" x-cloak class="p-3.5 bg-rose-50 border border-rose-200 rounded-2xl text-xs text-rose-700 space-y-1">
                                <div class="font-bold flex items-center gap-1.5">
                                    <i class="fa-solid fa-triangle-exclamation text-rose-500"></i>
                                    <span>Pemberitahuan Gateway</span>
                                </div>
                                <p class="text-[11px] leading-relaxed" x-text="errorMessage"></p>
                            </div>

                            <!-- Info Message Box -->
                            <div x-show="infoMessage" x-cloak class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-2xl text-xs text-emerald-800 space-y-1">
                                <div class="font-bold flex items-center gap-1.5">
                                    <i class="fa-solid fa-circle-notch fa-spin text-emerald-600" x-show="processing || syncing"></i>
                                    <span x-text="infoMessage"></span>
                                </div>
                            </div>

                            <div class="pt-2 flex flex-col sm:flex-row items-center justify-between gap-2.5">
                                <button type="button" @click="showPayModal = false" class="w-full sm:w-auto px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition-all cursor-pointer">
                                    Tutup
                                </button>
                                <button type="button" @click="checkStatus()" :disabled="syncing || processing" class="w-full sm:w-auto px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs rounded-xl shadow-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                    <i class="fa-solid fa-arrows-rotate" :class="{ 'fa-spin': syncing }"></i>
                                    <span>Cek Status Gateway</span>
                                </button>
                                <button type="button" @click="payNow()" :disabled="processing || syncing" class="w-full sm:flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-500 disabled:opacity-60 text-white font-bold text-xs rounded-xl shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer">
                                    <i class="fa-solid fa-lock" :class="{ 'fa-spin': processing }"></i>
                                    <span x-text="processing ? 'Menghubungkan...' : 'Bayar via Gateway'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    @endif

    <!-- Test Parameters & Compliance Results Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800">Hasil Analisis & Evaluasi Baku Mutu</h3>
            <span class="text-xs text-slate-500 font-semibold">Total {{ $sample->sampleParameters->count() }} Parameter</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[650px]">
                <thead class="bg-slate-100 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-4 sm:px-5 py-3">Parameter Uji</th>
                        <th class="px-4 sm:px-5 py-3">Metode Uji</th>
                        <th class="px-4 sm:px-5 py-3 text-center">Satuan</th>
                        <th class="px-4 sm:px-5 py-3 text-center">Ambang Baku Mutu</th>
                        <th class="px-4 sm:px-5 py-3 text-center">Hasil Pengujian</th>
                        <th class="px-4 sm:px-5 py-3 text-center">Kesesuaian</th>
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
                            <td class="px-4 sm:px-5 py-3 text-center text-slate-600">
                                {{ $sp->unit ?? '-' }}
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-center font-mono text-slate-600">
                                {{ $sp->quality_standard_summary }}
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-center font-bold text-sm font-mono text-emerald-700">
                                {{ $sp->test_value ?? 'Proses Analisis' }}
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
</div>
@push('scripts')
@if(config('services.payment_gateway.client_key'))
    <script src="{{ config('services.payment_gateway.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}" data-client-key="{{ config('services.payment_gateway.client_key') }}"></script>
@endif
@endpush
@endsection
