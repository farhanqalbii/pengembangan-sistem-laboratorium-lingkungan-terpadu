@extends('layouts.app')

@section('title', 'Inspeksi & Pengembalian ' . $borrowing->borrow_code . ' - Staff')
@section('page-title', 'Pemeriksaan Fisik & Pengembalian Alat')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3 mb-1">
                <span class="font-mono font-bold text-base sm:text-lg text-blue-600 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-200">{{ $borrowing->borrow_code }}</span>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $borrowing->status_badge_class }}">{{ $borrowing->status }}</span>
            </div>
            <h1 class="text-lg sm:text-xl font-bold text-slate-800">{{ $borrowing->customer->company_name ?? $borrowing->customer->name }}</h1>
            <p class="text-xs text-slate-500 mt-1">Target Kembali: <strong>{{ $borrowing->end_date->format('d F Y') }}</strong> (Deposit: Rp {{ number_format($borrowing->deposit_amount, 0, ',', '.') }})</p>
        </div>

        <a href="{{ route('staff.borrowings.index') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-colors">Kembali</a>
    </div>

    @if($borrowing->status === 'Menunggu')
    <!-- Staff Approval & Physical Readiness Check Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden space-y-4">
        <div class="p-4 sm:p-5 border-b border-slate-200 bg-emerald-50/50 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Verifikasi Kesiapan Fisik Alat & Otorisasi Peminjaman</h3>
                <p class="text-xs text-slate-500 mt-0.5">Periksa ketersediaan fisik instrumen alat sebelum menyetujui permohonan.</p>
            </div>
            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                Menunggu Otorisasi
            </span>
        </div>

        <div class="p-4 sm:p-5 space-y-4">
            <div class="border border-slate-200 rounded-xl overflow-hidden">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 text-slate-600 font-bold">
                        <tr>
                            <th class="px-4 py-2.5">Alat Laboratorium</th>
                            <th class="px-4 py-2.5">Jumlah Diminta</th>
                            <th class="px-4 py-2.5">Stok Fisik Tersedia</th>
                            <th class="px-4 py-2.5">Status Alat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($borrowing->borrowingDetails as $detail)
                        <tr>
                            <td class="px-4 py-3 font-bold text-slate-800">{{ $detail->equipment->name }} ({{ $detail->equipment->code }})</td>
                            <td class="px-4 py-3 font-bold text-blue-600">{{ $detail->quantity }} Unit</td>
                            <td class="px-4 py-3 font-bold text-emerald-600">{{ $detail->equipment->quantity }} Unit</td>
                            <td class="px-4 py-3"><span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-100 text-emerald-800">{{ $detail->equipment->status }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-3 pt-2">
                <!-- Reject Button & Modal/Form -->
                <form action="{{ route('staff.borrowings.reject', $borrowing->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menolak pengajuan peminjaman ini?')" class="inline-flex gap-2">
                    @csrf
                    <input type="hidden" name="notes" value="Alat tidak memenuhi syarat kesiapan fisik atau sedang dijadwalkan maintenance.">
                    <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs shadow transition-colors">
                        <i class="fa-solid fa-xmark mr-1"></i> Tolak Peminjaman
                    </button>
                </form>

                <!-- Approve Button -->
                <form action="{{ route('staff.borrowings.approve', $borrowing->id) }}" method="POST" onsubmit="return confirm('Kesiapan fisik alat telah dipastikan? Lanjutkan persetujuan?')">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/30 transition-all">
                        <i class="fa-solid fa-check mr-1"></i> Setujui Peminjaman Alat
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Return Form -->
    <form action="{{ route('staff.borrowings.return', $borrowing->id) }}" method="POST" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden space-y-5" @if($borrowing->status !== 'Dipinjam') style="display: none;" @endif>
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
                        <span class="text-xs font-bold text-slate-700 bg-white border border-slate-200 px-2.5 py-1 rounded-lg">
                            Jumlah Dikembalikan: {{ $detail->quantity }} Unit
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kondisi Saat Dikembalikan <span class="text-rose-500">*</span></label>
                            <select name="conditions[{{ $idx }}][condition_return]" required class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 focus:outline-none focus:border-emerald-500 cursor-pointer">
                                <option value="Baik" {{ ($detail->condition_return ?? 'Baik') === 'Baik' ? 'selected' : '' }}>Baik & Lengkap (Tanpa Kerusakan)</option>
                                <option value="Rusak Ringan" {{ $detail->condition_return === 'Rusak Ringan' ? 'selected' : '' }}>Rusak Ringan (Bisa Diperbaiki)</option>
                                <option value="Rusak Berat" {{ $detail->condition_return === 'Rusak Berat' ? 'selected' : '' }}>Rusak Berat</option>
                                <option value="Hilang" {{ $detail->condition_return === 'Hilang' ? 'selected' : '' }}>Hilang</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Inspeksi Staff</label>
                            <input type="text" name="conditions[{{ $idx }}][notes]" value="{{ $detail->notes }}" placeholder="Keterangan kondisi fisik..." class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-emerald-500">
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Financial Adjustments (Fine / Penalty) -->
            <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 space-y-3">
                <h4 class="font-bold text-amber-900 text-xs uppercase tracking-wider"><i class="fa-solid fa-calculator mr-1"></i> Perhitungan Denda & Pengembalian Deposit</h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Denda Keterlambatan / Kerusakan (Rp)</label>
                        <input type="number" name="fine_amount" min="0" step="10000" value="{{ old('fine_amount', $borrowing->fine_amount) }}" class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 font-bold text-rose-700 text-xs">
                        <span class="text-[11px] text-slate-500 mt-1 block">Diisi jika ada denda keterlambatan atau klaim biaya perbaikan.</span>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Catatan Denda (Opsional)</label>
                        <input type="text" name="fine_reason" value="{{ old('fine_reason', $borrowing->fine_reason) }}" placeholder="Contoh: Terlambat 2 hari / Ganti rugi kabel adaptor..." class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-800">
                    </div>
                </div>
            </div>
        </div>

        <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
            <span class="text-xs text-slate-500 font-semibold"><i class="fa-solid fa-boxes-packing text-emerald-600 mr-1"></i> Stok akan dikembalikan ke inventori secara otomatis</span>
            <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-600/30">
                Selesaikan Pengembalian Alat <i class="fa-solid fa-check-circle ml-1"></i>
            </button>
        </div>
    </form>
</div>
@endsection
