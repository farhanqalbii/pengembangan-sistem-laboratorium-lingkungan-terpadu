@extends('layouts.app')

@section('title', 'Invoice & Pembayaran - LIMS 2026')
@section('page-title', 'Invoice & Riwayat Pembayaran')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden anim-fade-up anim-delay-1 card-hover" x-data="{ selectedPayment: null, showPayModal: false }" x-effect="document.body.classList.toggle('overflow-hidden', showPayModal); document.body.classList.toggle('modal-open', showPayModal)">
        <div class="p-5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800">Daftar Tagihan & Invoice Saya</h3>
            <span class="text-xs text-slate-500">Integrasi Payment Gateway & QRIS Terverifikasi</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[650px]">
                <thead class="bg-slate-100 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-4 sm:px-5 py-3">Nomor Invoice</th>
                        <th class="px-4 sm:px-5 py-3">Jenis Transaksi</th>
                        <th class="px-4 sm:px-5 py-3">Tgl Tagihan</th>
                        <th class="px-4 sm:px-5 py-3">Total Tagihan</th>
                        <th class="px-4 sm:px-5 py-3">Status</th>
                        <th class="px-4 sm:px-5 py-3 text-center">Aksi & Pembayaran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($invoices as $inv)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 sm:px-5 py-3 font-mono font-bold text-slate-800">
                                {{ $inv->invoice_number }}
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-slate-700">
                                {{ $inv->payment->type ?? 'Layanan Lab' }}
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-slate-500">
                                {{ $inv->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 sm:px-5 py-3 font-bold text-slate-800 text-xs sm:text-sm">
                                Rp {{ number_format($inv->total, 0, ',', '.') }}
                            </td>
                            <td class="px-4 sm:px-5 py-3">
                                @if($inv->status === 'Lunas')
                                    <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 rounded-full font-bold text-[11px]">LUNAS</span>
                                @else
                                    <span class="px-2.5 py-1 bg-rose-100 text-rose-800 rounded-full font-bold text-[11px]">BELUM LUNAS</span>
                                @endif
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-center space-x-1.5 whitespace-nowrap">
                                <a href="{{ route('customer.invoices.download', $inv->id) }}" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg text-xs">
                                    <i class="fa-solid fa-file-pdf mr-1 text-[11px]"></i> Invoice PDF
                                </a>
                                @if($inv->status === 'Belum Lunas' && $inv->payment)
                                    <button @click="selectedPayment = {{ json_encode($inv->payment) }}; showPayModal = true" class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-lg text-xs shadow-sm cursor-pointer">
                                        <i class="fa-solid fa-credit-card mr-1 text-[11px]"></i> Bayar via Gateway
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-slate-400">Belum ada invoice tagihan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Payment Modal -->
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
                     class="bg-white rounded-3xl max-w-md w-full p-4 sm:p-7 shadow-2xl space-y-4 my-auto relative border border-slate-200 max-h-[90vh] overflow-y-auto">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h3 class="font-bold text-slate-800 text-base">Pembayaran Payment Gateway</h3>
                        <button @click="showPayModal = false" class="text-slate-400 hover:text-slate-700 cursor-pointer"><i class="fa-solid fa-xmark text-lg"></i></button>
                    </div>

                    <div class="bg-slate-900 text-white p-4 rounded-2xl space-y-1">
                        <span class="text-slate-400 text-xs uppercase block">Total Tagihan:</span>
                        <h4 class="text-xl sm:text-2xl font-extrabold text-emerald-400 break-words" x-text="'Rp ' + (selectedPayment?.amount || 0).toLocaleString('id-ID')"></h4>
                        <span class="text-xs text-slate-300 block break-words" x-text="'Kode Transaksi: ' + selectedPayment?.payment_code"></span>
                    </div>

                    <div class="space-y-4" x-data="{ 
                        processing: false,
                        syncing: false,
                        errorMessage: '',
                        infoMessage: '',
                        payNow() {
                            if (!selectedPayment?.id || this.processing) return;
                            this.processing = true;
                            this.errorMessage = '';
                            this.infoMessage = '';
                            fetch('/customer/payments/' + selectedPayment.id + '/process', {
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
                            if (!selectedPayment?.id || this.syncing) return;
                            this.syncing = true;
                            this.errorMessage = '';
                            this.infoMessage = 'Memeriksa status pembayaran dari gateway...';
                            fetch('/customer/payments/' + selectedPayment.id + '/check-status', {
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
                                Transaksi diproses melalui Payment Gateway resmi. Setelah Anda menyelesaikan pembayaran di gateway, status akan otomatis terkonfirmasi melalui webhook atau klik tombol sinkronisasi.
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
</div>

@push('scripts')
@if(config('services.payment_gateway.client_key'))
    <script src="{{ config('services.payment_gateway.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}" data-client-key="{{ config('services.payment_gateway.client_key') }}"></script>
@endif
@endpush
@endsection
