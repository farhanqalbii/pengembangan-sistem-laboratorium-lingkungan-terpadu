@extends('layouts.app')

@section('title', 'Detail Peminjaman ' . $borrowing->borrow_code . ' - LIMS 2026')
@section('page-title', 'Detail Peminjaman Alat')

@section('content')
<div class="space-y-6">
    <!-- Header Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3 mb-1">
                <span class="font-mono font-bold text-base sm:text-lg text-blue-600 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-200">{{ $borrowing->borrow_code }}</span>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $borrowing->status_badge_class }}">{{ $borrowing->status }}</span>
            </div>
            <h1 class="text-base sm:text-lg font-bold text-slate-800">{{ $borrowing->purpose }}</h1>
            <p class="text-xs text-slate-500 mt-1"><i class="fa-solid fa-calendar-days mr-1"></i> Periode Pinjam: {{ $borrowing->start_date->format('d/m/Y') }} s/d {{ $borrowing->end_date->format('d/m/Y') }}</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('customer.borrowings.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 font-semibold text-xs rounded-xl shadow-2xs transition-colors cursor-pointer">
                <i class="fa-solid fa-arrow-left text-[11px] text-slate-400"></i> Kembali
            </a>

            @if(in_array($borrowing->status, ['Dikembalikan', 'Selesai']))
                <a href="{{ route('customer.satisfaction.create', ['type' => 'Peminjaman Alat', 'id' => $borrowing->id]) }}" class="px-3.5 py-2 bg-amber-500 hover:bg-amber-400 text-white font-bold text-xs rounded-xl shadow-md flex items-center transition-colors">
                    <i class="fa-solid fa-star mr-2"></i> Beri Penilaian
                </a>
            @endif

            @if($borrowing->status === 'Dipinjam' && !$borrowing->extension_requested)
                <div x-data="{ openModal: false }" x-effect="document.body.classList.toggle('overflow-hidden', openModal); document.body.classList.toggle('modal-open', openModal)">
                    <button @click="openModal = true" class="px-3.5 py-2 bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs rounded-xl shadow-md flex items-center transition-colors cursor-pointer">
                        <i class="fa-solid fa-clock-rotate-left mr-2"></i> Ajukan Perpanjangan Pinjam
                    </button>

                    <!-- Modal Extension -->
                    <template x-teleport="body">
                        <div x-show="openModal" 
                             x-cloak
                             @keydown.escape.window="openModal = false"
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-[99999] flex items-center justify-center p-4 overflow-y-auto"
                             style="z-index: 99999;">
                            <div @click.outside="openModal = false" 
                                 x-show="openModal"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-200"
                                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                 x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                                 class="bg-white rounded-3xl max-w-md w-full p-5 sm:p-6 shadow-2xl space-y-4 text-left my-auto relative border border-slate-200">
                                <h3 class="font-bold text-slate-800 text-base">Permohonan Perpanjangan Peminjaman</h3>
                                <p class="text-xs text-slate-500">Masa pinjam saat ini berakhir pada <strong>{{ $borrowing->end_date->format('d F Y') }}</strong>.</p>
                                
                                <form action="{{ route('customer.borrowings.extend', $borrowing->id) }}" method="POST" class="space-y-4">
                                    @csrf
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Pengembalian Baru yang Diinginkan</label>
                                        <input type="date" name="extended_end_date" min="{{ date('Y-m-d', strtotime($borrowing->end_date->toDateString() . ' +1 day')) }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-800">
                                    </div>
                                    <div class="flex justify-end space-x-3 pt-2">
                                        <button type="button" @click="openModal = false" class="px-4 py-2 bg-slate-100 text-slate-700 text-xs font-semibold rounded-xl cursor-pointer">Batal</button>
                                        <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold rounded-xl cursor-pointer">Kirim Request</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </template>
                </div>
            @endif
        </div>
    </div>

    <!-- Card Status Tagihan & Pembayaran Deposit -->
    @php
        $depositPayment = $borrowing->payments->where('tipe', 'Deposit Peminjaman')->first() 
            ?? $borrowing->payments->first();
        $invoice = $depositPayment?->invoice;
    @endphp

    <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-sm relative overflow-hidden" 
         x-data="{ showPayModal: false }" 
         x-effect="document.body.classList.toggle('overflow-hidden', showPayModal); document.body.classList.toggle('modal-open', showPayModal)">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2 flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Tagihan & Deposit Jaminan Alat</span>
                    @if($depositPayment && ($depositPayment->status === 'Berhasil' || $invoice?->status === 'Lunas'))
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                            <i class="fa-solid fa-circle-check mr-1.5 text-emerald-600"></i> LUNAS (DIPINJAM)
                        </span>
                    @elseif($borrowing->status === 'Diajukan')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300">
                            <i class="fa-solid fa-hourglass-half mr-1.5 text-amber-600"></i> MENUNGGU PERSETUJUAN ADMIN
                        </span>
                    @elseif($borrowing->status === 'Disetujui' || $depositPayment)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-300">
                            <i class="fa-solid fa-clock mr-1.5 text-blue-600"></i> MENUNGGU PEMBAYARAN DEPOSIT
                        </span>
                    @elseif($borrowing->status === 'Ditolak')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300">
                            <i class="fa-solid fa-ban mr-1.5 text-rose-600"></i> DITOLAK
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-800 border border-slate-300">
                            {{ $borrowing->status }}
                        </span>
                    @endif
                </div>

                <div class="flex flex-wrap items-baseline gap-3">
                    <span class="text-2xl sm:text-3xl font-extrabold text-slate-900">
                        Rp {{ number_format($borrowing->deposit_amount, 0, ',', '.') }}
                    </span>
                    @if($invoice)
                        <span class="text-xs font-mono text-slate-500 bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200">
                            No. Invoice: {{ $invoice->invoice_number }}
                        </span>
                    @endif
                </div>

                <p class="text-xs text-slate-500 flex flex-wrap items-center gap-x-4 gap-y-1">
                    @if($depositPayment)
                        <span><i class="fa-solid fa-receipt text-slate-400 mr-1"></i> Kode Pembayaran: <strong>{{ $depositPayment->payment_code }}</strong></span>
                        @if($depositPayment->status === 'Berhasil' && $depositPayment->paid_at)
                            <span class="text-emerald-700 font-semibold"><i class="fa-solid fa-calendar-check mr-1"></i> Dibayar pada: {{ $depositPayment->paid_at->format('d/m/Y H:i') }} via <strong>{{ $depositPayment->payment_method }}</strong></span>
                        @else
                            <span class="text-amber-700 font-medium"><i class="fa-solid fa-wallet mr-1"></i> Pilihan Metode: <strong>QRIS, Virtual Account, atau Transfer Bank</strong></span>
                        @endif
                    @elseif($borrowing->status === 'Diajukan')
                        <span class="text-amber-700"><i class="fa-solid fa-circle-info mr-1"></i> Tagihan deposit & barcode QRIS/VA otomatis terbit setelah admin menyetujui pengajuan peminjaman alat.</span>
                    @endif
                </p>
            </div>

            <div class="flex flex-wrap sm:flex-nowrap items-center justify-end gap-3 sm:ml-auto shrink-0 self-start md:self-center">
                @if($invoice)
                    <a href="{{ route('customer.invoices.download', $invoice->id) }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl border border-slate-200 transition-all flex items-center gap-2 whitespace-nowrap cursor-pointer">
                        <i class="fa-solid fa-file-pdf text-rose-500"></i>
                        <span>Unduh Invoice (PDF)</span>
                    </a>
                @endif

                @if($depositPayment && $depositPayment->status !== 'Berhasil' && $invoice?->status !== 'Lunas')
                    <button type="button" @click="showPayModal = true" class="px-6 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-500 hover:to-teal-600 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-700/25 transition-all flex items-center gap-2 active:scale-95 cursor-pointer whitespace-nowrap">
                        <i class="fa-solid fa-qrcode text-sm"></i>
                        <span>Payment</span>
                    </button>
                @endif
            </div>
        </div>

        @if($depositPayment && $depositPayment->status !== 'Berhasil')
            <!-- Modal Pembayaran Deposit Interaktif -->
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
                                    <span>Pembayaran Deposit Jaminan Alat</span>
                                </h3>
                                <p class="text-xs text-slate-500 mt-0.5">Pilih metode pembayaran deposit yang Anda inginkan.</p>
                            </div>
                            <button type="button" @click="showPayModal = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition-colors cursor-pointer">
                                <i class="fa-solid fa-xmark text-sm"></i>
                            </button>
                        </div>

                        <!-- Summary Box -->
                        <div class="bg-gradient-to-br from-slate-900 to-slate-800 text-white p-4 sm:p-5 rounded-2xl space-y-1.5 shadow-inner">
                            <span class="text-slate-400 text-xs uppercase font-medium">Total Deposit Jaminan:</span>
                            <div class="text-2xl sm:text-3xl font-extrabold text-emerald-400 break-words">
                                Rp {{ number_format($depositPayment->amount, 0, ',', '.') }}
                            </div>
                            <div class="text-xs text-slate-300 flex items-center justify-between pt-1 border-t border-slate-700/60 mt-2">
                                <span>Peminjaman: {{ $borrowing->borrow_code }}</span>
                                <span class="font-mono text-cyan-300">{{ $depositPayment->payment_code }}</span>
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
                                fetch('{{ route('customer.payments.process', $depositPayment->id) }}', {
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
                                this.infoMessage = 'Memeriksa status pembayaran deposit dari gateway...';
                                fetch('{{ route('customer.payments.check-status', $depositPayment->id) }}', {
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
                                        this.infoMessage = 'Deposit LUNAS terkonfirmasi! Alat siap dipinjam. Memperbarui halaman...';
                                        setTimeout(() => window.location.reload(), 1000);
                                    } else {
                                        this.infoMessage = res.message || 'Status deposit belum terkonfirmasi lunas.';
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
                                    Tagihan deposit peminjaman ini akan diproses melalui Payment Gateway terverifikasi. Setelah Anda menyelesaikan pembayaran di gateway, status alat akan otomatis berubah menjadi <strong>Dipinjam</strong> melalui webhook resmi atau sinkronisasi status.
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
        @endif
    </div>

    <!-- Borrowed Items List -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-200 bg-slate-50">
            <h3 class="text-sm font-bold text-slate-800">Daftar Instrumen & Spesifikasi</h3>
        </div>
        <div class="p-4 space-y-3">
            @foreach($borrowing->borrowingDetails as $detail)
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h4 class="font-bold text-slate-800 text-sm">{{ $detail->equipment->name }}</h4>
                        <p class="text-xs text-slate-500">Kode: {{ $detail->equipment->code }} | Merk: {{ $detail->equipment->brand ?? 'N/A' }} | Lokasi: {{ $detail->equipment->location }}</p>
                        @if($detail->condition_return)
                            <span class="inline-block mt-2 text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-indigo-100 text-indigo-800">
                                Kondisi Saat Dikembalikan: {{ $detail->condition_return }}
                            </span>
                        @endif
                    </div>
                    <div class="self-start sm:self-auto text-left sm:text-right">
                        <span class="text-xs font-bold text-slate-800 bg-white border border-slate-200 px-3 py-1 rounded-lg">
                            {{ $detail->quantity }} Unit
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Financial & Deposit Breakdown -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-sm space-y-4">
        <h3 class="text-sm font-bold text-slate-800">Rincian Keuangan, Deposit & Denda</h3>
        
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200">
                <span class="text-emerald-700 font-bold block uppercase tracking-wider text-[10px]">Deposit Jaminan (Awal)</span>
                <span class="text-lg font-extrabold text-emerald-800 mt-1 block">Rp {{ number_format($borrowing->deposit_amount, 0, ',', '.') }}</span>
            </div>
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200">
                <span class="text-rose-700 font-bold block uppercase tracking-wider text-[10px]">Denda Pengembalian / Kerusakan</span>
                <span class="text-lg font-extrabold text-rose-800 mt-1 block">Rp {{ number_format($borrowing->fine_amount, 0, ',', '.') }}</span>
            </div>
            <div class="p-4 rounded-xl bg-indigo-50 border border-indigo-200">
                <span class="text-indigo-700 font-bold block uppercase tracking-wider text-[10px]">Deposit Refundable</span>
                <span class="text-lg font-extrabold text-indigo-800 mt-1 block">Rp {{ number_format($borrowing->refund_amount, 0, ',', '.') }}</span>
                <span class="text-[10px] text-indigo-600 font-semibold block mt-1">Status: {{ $borrowing->refund_status }}</span>
            </div>
        </div>
    </div>
</div>
@push('scripts')
@if(config('services.payment_gateway.client_key'))
    <script src="{{ config('services.payment_gateway.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}" data-client-key="{{ config('services.payment_gateway.client_key') }}"></script>
@endif
@endpush
@endsection
