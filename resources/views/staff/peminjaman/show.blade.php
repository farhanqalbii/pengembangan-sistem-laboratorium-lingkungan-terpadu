@extends('layouts.app')

@section('title', 'Detail Peminjaman ' . $borrowing->borrow_code . ' - Staff')
@section('page-title', 'Pemeriksaan & Otorisasi Peminjaman Alat')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Header Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3 mb-1">
                <span class="font-mono font-bold text-base sm:text-lg text-blue-600 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-200">{{ $borrowing->borrow_code }}</span>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $borrowing->status_badge_class }}">{{ $borrowing->status }}</span>
            </div>
            <h1 class="text-lg sm:text-xl font-bold text-slate-800">{{ $borrowing->customer->company_name ?? $borrowing->customer->name }}</h1>
            <p class="text-xs text-slate-500 mt-1">
                Target Kembali: <strong>{{ ($borrowing->extended_end_date ?? $borrowing->end_date)->format('d F Y') }}</strong>
                &bull; Deposit: <strong>Rp {{ number_format($borrowing->deposit_amount, 0, ',', '.') }}</strong>
            </p>
        </div>

        <a href="{{ route('staff.borrowings.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-colors self-start md:self-center">
            <i class="fa-solid fa-arrow-left text-[11px]"></i> Kembali ke Daftar
        </a>
    </div>

    <!-- Informasi Peminjam & Jadwal (Always Visible) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <!-- Kartu Identitas Peminjam -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-sm space-y-3">
            <div class="flex items-center gap-2 border-b border-slate-100 pb-2.5">
                <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-user"></i>
                </div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Informasi Peminjam</h3>
            </div>
            <div class="space-y-2 text-xs">
                <div class="flex justify-between">
                    <span class="text-slate-400">Nama Kontak:</span>
                    <span class="font-bold text-slate-800">{{ $borrowing->customer->name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Instansi / Perusahaan:</span>
                    <span class="font-semibold text-slate-700">{{ $borrowing->customer->company_name ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Nomor Telepon:</span>
                    <span class="font-mono text-slate-700">{{ $borrowing->customer->phone ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Alamat Email:</span>
                    <span class="text-slate-700">{{ $borrowing->customer->email }}</span>
                </div>
            </div>
        </div>

        <!-- Kartu Jadwal & Tujuan -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-sm space-y-3">
            <div class="flex items-center gap-2 border-b border-slate-100 pb-2.5">
                <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Jadwal & Tujuan Peminjaman</h3>
            </div>
            <div class="space-y-2 text-xs">
                <div class="flex justify-between">
                    <span class="text-slate-400">Tanggal Mulai:</span>
                    <span class="font-bold text-slate-800">{{ $borrowing->start_date->format('d F Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Batas Pengembalian:</span>
                    <span class="font-bold text-slate-800">{{ $borrowing->end_date->format('d F Y') }}</span>
                </div>
                @if($borrowing->extended_end_date)
                    <div class="flex justify-between text-amber-700 font-semibold">
                        <span>Perpanjangan Disetujui:</span>
                        <span>{{ $borrowing->extended_end_date->format('d F Y') }}</span>
                    </div>
                @endif
                @if($borrowing->actual_return_date)
                    <div class="flex justify-between text-emerald-700 font-semibold">
                        <span>Tanggal Kembali Aktual:</span>
                        <span>{{ $borrowing->actual_return_date->format('d F Y') }}</span>
                    </div>
                @endif
                <div class="pt-1 border-t border-slate-100">
                    <span class="text-slate-400 block mb-0.5">Tujuan Penggunaan:</span>
                    <p class="font-medium text-slate-700 italic">{{ $borrowing->purpose ?: 'Tidak ada keterangan khusus.' }}</p>
                </div>
                @if($borrowing->notes)
                    <div class="pt-1">
                        <span class="text-slate-400 block mb-0.5">Catatan Tambahan:</span>
                        <p class="text-slate-600">{{ $borrowing->notes }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Daftar Alat Laboratorium Dipinjam (Always Visible) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-microscope text-slate-500"></i>
                <h3 class="text-sm font-bold text-slate-800">Daftar Instrumen & Alat yang Diajukan</h3>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 bg-white border border-slate-200 rounded-lg text-slate-600">
                {{ $borrowing->borrowingDetails->count() }} Macam Alat
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[550px]">
                <thead class="bg-slate-100 text-slate-600 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-4 sm:px-5 py-3">Instrumen Laboratorium</th>
                        <th class="px-4 sm:px-5 py-3">Jumlah Diminta</th>
                        <th class="px-4 sm:px-5 py-3">Stok Lab Saat Ini</th>
                        <th class="px-4 sm:px-5 py-3">Status Alat</th>
                        @if($borrowing->status === 'Dikembalikan')
                            <th class="px-4 sm:px-5 py-3">Kondisi Kembali</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @foreach($borrowing->borrowingDetails as $detail)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 sm:px-5 py-3">
                                <div class="font-bold text-slate-800">{{ $detail->equipment->name }}</div>
                                <div class="text-[11px] text-slate-400">Kode: {{ $detail->equipment->code }} &bull; Merk: {{ $detail->equipment->brand ?? 'N/A' }}</div>
                            </td>
                            <td class="px-4 sm:px-5 py-3 font-bold text-blue-600">
                                {{ $detail->quantity }} Unit
                            </td>
                            <td class="px-4 sm:px-5 py-3 font-bold {{ $detail->equipment->quantity < $detail->quantity ? 'text-rose-600' : 'text-emerald-600' }}">
                                {{ $detail->equipment->quantity }} Unit
                            </td>
                            <td class="px-4 sm:px-5 py-3">
                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold {{ $detail->equipment->status === 'Tersedia' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $detail->equipment->status }}
                                </span>
                            </td>
                            @if($borrowing->status === 'Dikembalikan')
                                <td class="px-4 sm:px-5 py-3">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold {{ $detail->condition_return === 'Baik' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                        {{ $detail->condition_return ?? 'Baik' }}
                                    </span>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Ringkasan Keuangan & Deposit (Always Visible) -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-sm space-y-3">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
            <i class="fa-solid fa-receipt text-slate-400"></i> Ringkasan Deposit & Tagihan Peminjaman
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                <span class="text-slate-500 block">Deposit Jaminan (Awal)</span>
                <span class="text-base font-extrabold text-slate-800 mt-0.5 block">Rp {{ number_format($borrowing->deposit_amount, 0, ',', '.') }}</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">Wajib dibayar customer sebelum alat diambil</span>
            </div>
            <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200">
                <span class="text-rose-700 font-bold block">Total Denda</span>
                <span class="text-base font-extrabold text-rose-800 mt-0.5 block">Rp {{ number_format($borrowing->fine_amount, 0, ',', '.') }}</span>
                <span class="text-[10px] text-rose-600 block mt-0.5">{{ $borrowing->fine_reason ?: 'Tidak ada denda tercatat' }}</span>
            </div>
            <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200">
                <span class="text-emerald-700 font-bold block">Deposit Refundable</span>
                <span class="text-base font-extrabold text-emerald-800 mt-0.5 block">Rp {{ number_format($borrowing->refund_amount, 0, ',', '.') }}</span>
                <span class="text-[10px] text-emerald-600 block mt-0.5 font-semibold">Status: {{ $borrowing->refund_status ?? 'Belum Diproses' }}</span>
            </div>
        </div>
    </div>

    <!-- Status Specific Actions / Alerts -->
    @if(in_array($borrowing->status, ['Diajukan', 'Menunggu']))
    <!-- Staff Approval & Physical Readiness Check Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden space-y-4">
        <div class="p-4 sm:p-5 border-b border-slate-200 bg-emerald-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Verifikasi Kesiapan Fisik Alat & Otorisasi Peminjaman</h3>
                <p class="text-xs text-slate-500 mt-0.5">Periksa ketersediaan fisik instrumen alat sebelum menyetujui permohonan.</p>
            </div>
            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200 self-start sm:self-auto">
                Menunggu Otorisasi
            </span>
        </div>

        <div class="p-4 sm:p-5 space-y-4">
            <p class="text-xs text-slate-600 leading-relaxed">
                Pastikan seluruh instrumen yang diminta dalam kondisi prima, bersih, dan tidak sedang dalam jadwal kalibrasi atau perbaikan sebelum memberikan persetujuan.
            </p>

            <div class="flex flex-wrap items-center justify-end gap-3 pt-2">
                <!-- Reject Button & Form -->
                <form action="{{ route('staff.borrowings.reject', $borrowing) }}" method="POST" x-data="{ submitting: false }" @submit="if (!confirm('Yakin ingin menolak pengajuan peminjaman ini?')) { $event.preventDefault(); return false; } submitting = true" x-init="window.addEventListener('pageshow', () => submitting = false)" class="inline-flex gap-2">
                    @csrf
                    <input type="hidden" name="notes" value="Alat tidak memenuhi syarat kesiapan fisik atau sedang dijadwalkan maintenance.">
                    <button type="submit" 
                        :disabled="submitting"
                        :class="{ 'opacity-75 pointer-events-none': submitting }"
                        class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs shadow transition-colors flex items-center gap-1.5 cursor-pointer disabled:cursor-not-allowed">
                        <i class="fa-solid fa-spinner fa-spin" x-show="submitting" x-cloak></i>
                        <i class="fa-solid fa-xmark" x-show="!submitting"></i>
                        <span x-text="submitting ? 'Menolak...' : 'Tolak Peminjaman'">Tolak Peminjaman</span>
                    </button>
                </form>

                <!-- Approve Button -->
                <form action="{{ route('staff.borrowings.approve', $borrowing) }}" method="POST" x-data="{ submitting: false }" @submit="if (!confirm('Kesiapan fisik alat telah dipastikan? Lanjutkan persetujuan?')) { $event.preventDefault(); return false; } submitting = true" x-init="window.addEventListener('pageshow', () => submitting = false)">
                    @csrf
                    <button type="submit" 
                        :disabled="submitting"
                        :class="{ 'opacity-75 pointer-events-none': submitting }"
                        class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/30 transition-all flex items-center gap-1.5 cursor-pointer disabled:cursor-not-allowed">
                        <i class="fa-solid fa-spinner fa-spin" x-show="submitting" x-cloak></i>
                        <i class="fa-solid fa-check" x-show="!submitting"></i>
                        <span x-text="submitting ? 'Menyetujui...' : 'Setujui Peminjaman Alat'">Setujui Peminjaman Alat</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
    @elseif($borrowing->status === 'Disetujui')
    <!-- Status Disetujui: Menunggu Pembayaran Deposit & Serah Terima Fisik -->
    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 sm:p-5 shadow-sm space-y-2">
        <div class="flex items-center gap-2 text-blue-900 font-bold text-sm">
            <i class="fa-solid fa-circle-check text-blue-600"></i>
            <span>Peminjaman Telah Disetujui Staf / Admin</span>
        </div>
        <p class="text-xs text-blue-800 leading-relaxed">
            Permohonan peminjaman ini telah diotorisasi. Status akan otomatis berubah menjadi <strong>Dipinjam</strong> setelah customer menyelesaikan pembayaran deposit jaminan melalui payment gateway, atau saat penyerahan alat fisik dikonfirmasi.
        </p>
    </div>
    @elseif(in_array($borrowing->status, ['Dipinjam', 'Terlambat']))
    <!-- Return Form -->
    <form action="{{ route('staff.borrowings.return', $borrowing) }}" method="POST" x-data="{ submitting: false }" @submit="if ($el.checkValidity()) submitting = true" x-init="window.addEventListener('pageshow', () => submitting = false)" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden space-y-5">
        @csrf

        <div class="p-4 sm:p-5 border-b border-slate-200 bg-slate-50">
            <h3 class="text-sm font-bold text-slate-800">Form Inspeksi Fisik & Pengembalian Stok</h3>
            <p class="text-xs text-slate-500 mt-0.5">Periksa kondisi kelengkapan alat yang dikembalikan oleh customer.</p>
        </div>

        <div class="p-4 sm:p-5 space-y-4">
            <!-- Tanggal Pengembalian Fisik -->
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tanggal Pengembalian Fisik / Riil <span class="text-rose-500">*</span></label>
                        <input type="date" name="actual_return_date" value="{{ old('actual_return_date', date('Y-m-d')) }}" required class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Batas Waktu Pengembalian (Jadwal)</label>
                        <input type="text" readonly value="{{ ($borrowing->extended_end_date ?? $borrowing->end_date)->format('d F Y') }}" class="w-full bg-slate-100 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-500 font-medium">
                    </div>
                </div>
            </div>

            @foreach($borrowing->borrowingDetails as $idx => $detail)
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 space-y-3">
                    <input type="hidden" name="conditions[{{ $idx }}][detail_id]" value="{{ $detail->id }}">

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h4 class="font-bold text-slate-800 text-sm">{{ $detail->equipment->name }}</h4>
                            <p class="text-xs text-slate-500">Kode: {{ $detail->equipment->code }} | Merk: {{ $detail->equipment->brand ?? 'N/A' }}</p>
                        </div>
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-2.5 py-1 rounded-lg">
                            Jumlah Dikembalikan: {{ $detail->quantity }} Unit
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Kondisi Saat Dikembalikan <span class="text-rose-500">*</span></label>
                            <x-custom-select 
                                name="conditions[{{ $idx }}][condition_return]"
                                :options="[
                                    'Baik' => 'Baik & Lengkap (Tanpa Kerusakan)',
                                    'Rusak Ringan' => 'Rusak Ringan (Bisa Diperbaiki)',
                                    'Rusak Berat' => 'Rusak Berat',
                                    'Hilang' => 'Hilang'
                                ]"
                                :selected="old('conditions.'.$idx.'.condition_return', $detail->condition_return ?? 'Baik')"
                                required
                                aria-label="Kondisi Saat Dikembalikan"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1">Catatan Inspeksi Staff</label>
                            <input type="text" name="conditions[{{ $idx }}][notes]" value="{{ $detail->notes }}" placeholder="Keterangan kondisi fisik..." class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:border-emerald-500">
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Financial Adjustments (Fine / Penalty) -->
            <div class="p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/40 space-y-3">
                <h4 class="font-bold text-amber-900 dark:text-amber-200 text-xs uppercase tracking-wider"><i class="fa-solid fa-calculator mr-1 text-amber-600 dark:text-amber-400"></i> Perhitungan Denda & Pengembalian Deposit</h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Denda Keterlambatan / Kerusakan (Rp)</label>
                        <input type="number" name="fine_amount" min="0" step="10000" value="{{ old('fine_amount', $borrowing->fine_amount) }}" class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 font-bold text-rose-700 dark:text-rose-400 text-xs">
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 block">Diisi jika ada denda keterlambatan atau klaim biaya perbaikan.</span>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-200 mb-1">Catatan Denda (Opsional)</label>
                        <input type="text" name="fine_reason" value="{{ old('fine_reason', $borrowing->fine_reason) }}" placeholder="Contoh: Terlambat 2 hari / Ganti rugi kabel adaptor..." class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-100">
                    </div>
                </div>
            </div>
        </div>

        <div class="p-4 bg-slate-50 dark:bg-slate-900/40 border-t border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <span class="text-xs text-slate-500 font-semibold"><i class="fa-solid fa-boxes-packing text-emerald-600 mr-1"></i> Stok akan dikembalikan ke inventori secara otomatis</span>
            <button type="submit" 
                :disabled="submitting"
                :class="{ 'opacity-75 pointer-events-none': submitting }"
                class="w-full sm:w-auto px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-600/30 flex items-center justify-center gap-1.5 cursor-pointer disabled:cursor-not-allowed">
                <i class="fa-solid fa-spinner fa-spin" x-show="submitting" x-cloak></i>
                <span x-text="submitting ? 'Memproses Pengembalian...' : 'Selesaikan Pengembalian Alat'">Selesaikan Pengembalian Alat</span>
                <i class="fa-solid fa-check-circle" x-show="!submitting"></i>
            </button>
        </div>
    </form>
    @elseif($borrowing->status === 'Dikembalikan')
    <!-- Returned Inspection Results Summary Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden space-y-4">
        <div class="p-4 sm:p-5 border-b border-slate-200 bg-emerald-50/70 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Hasil Inspeksi Pengembalian Alat Selesai</h3>
                <p class="text-xs text-slate-500 mt-0.5">Dikembalikan pada: <strong>{{ $borrowing->actual_return_date ? $borrowing->actual_return_date->format('d F Y') : '-' }}</strong></p>
            </div>
            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center gap-1.5">
                <i class="fa-solid fa-circle-check text-emerald-600"></i> Dikembalikan
            </span>
        </div>

        <div class="p-4 sm:p-5 space-y-4">
            <div class="space-y-3">
                @foreach($borrowing->borrowingDetails as $detail)
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                        <div>
                            <h4 class="font-bold text-slate-800 text-sm">{{ $detail->equipment->name }} ({{ $detail->equipment->code }})</h4>
                            <p class="text-slate-500 mt-0.5">Catatan Inspeksi: <em>{{ $detail->condition_notes ?: ($detail->notes ?: 'Kondisi normal terverifikasi.') }}</em></p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold {{ $detail->condition_return === 'Baik' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                Kondisi: {{ $detail->condition_return ?? 'Baik' }}
                            </span>
                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-white border border-slate-200 text-slate-700">
                                {{ $detail->quantity }} Unit
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @elseif($borrowing->status === 'Ditolak')
    <!-- Status Ditolak Banner -->
    <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 sm:p-5 shadow-sm space-y-2">
        <div class="flex items-center gap-2 text-rose-900 font-bold text-sm">
            <i class="fa-solid fa-ban text-rose-600"></i>
            <span>Pengajuan Peminjaman Telah Ditolak</span>
        </div>
        <p class="text-xs text-rose-800 leading-relaxed">
            Catatan Penolakan: <em>{{ $borrowing->notes ?: 'Alat laboratorium tidak memenuhi syarat kesiapan fisik atau sedang dijadwalkan perawatan/kalibrasi.' }}</em>
        </p>
    </div>
    @elseif($borrowing->status === 'Dibatalkan')
    <!-- Status Dibatalkan Banner -->
    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-sm space-y-2">
        <div class="flex items-center gap-2 text-slate-800 font-bold text-sm">
            <i class="fa-solid fa-circle-xmark text-slate-500"></i>
            <span>Peminjaman Dibatalkan</span>
        </div>
        <p class="text-xs text-slate-600 leading-relaxed">
            Transaksi peminjaman ini telah dibatalkan dan tidak lagi aktif.
        </p>
    </div>
    @endif
</div>
@endsection
