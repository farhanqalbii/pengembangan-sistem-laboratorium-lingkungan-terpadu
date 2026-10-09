@extends('layouts.app')

@section('title', 'Laporan Keuangan - Admin')
@section('page-title', 'Laporan Penerimaan Keuangan & Transaksi')

@section('content')
<div class="space-y-6">
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <form action="{{ route('admin.reports.financial') }}" method="GET" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="bg-slate-50 border border-slate-300 text-xs rounded-xl px-3 py-1.5 text-slate-800">
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="bg-slate-50 border border-slate-300 text-xs rounded-xl px-3 py-1.5 text-slate-800">
            </div>
            <div class="pt-4">
                <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-xl text-xs font-semibold hover:bg-slate-700">Filter Periode</button>
            </div>
        </form>

        <a href="{{ route('admin.reports.financial', array_merge(request()->all(), ['export' => 'pdf'])) }}" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-emerald-600/30 flex items-center justify-center">
            <i class="fa-solid fa-file-pdf mr-2"></i> Export Laporan PDF
        </a>
    </div>

    <!-- Total Income Card -->
    <div class="p-6 rounded-2xl bg-gradient-to-r from-slate-900 to-emerald-950 text-white flex items-center justify-between shadow-xl">
        <div>
            <span class="text-xs text-emerald-400 uppercase font-bold tracking-wider">Total Pendapatan Terverifikasi</span>
            <h2 class="text-3xl font-extrabold text-white mt-1">Rp {{ number_format($totalIncome, 0, ',', '.') }}</h2>
            <p class="text-xs text-slate-400 mt-1">Total {{ $payments->count() }} transaksi pembayaran berhasil</p>
        </div>
        <div class="w-14 h-14 rounded-2xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-2xl font-bold">
            <i class="fa-solid fa-receipt"></i>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-100 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-4 sm:px-5 py-3">Kode Transaksi</th>
                        <th class="px-4 sm:px-5 py-3">Customer</th>
                        <th class="px-4 sm:px-5 py-3">Jenis Layanan</th>
                        <th class="px-4 sm:px-5 py-3">Metode Bayar</th>
                        <th class="px-4 sm:px-5 py-3">Tgl Bayar</th>
                        <th class="px-4 sm:px-5 py-3 text-right">Jumlah Pembayaran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($payments as $p)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 sm:px-5 py-3 font-mono font-bold text-slate-800">
                                {{ $p->payment_code }}
                            </td>
                            <td class="px-4 sm:px-5 py-3 font-bold text-slate-800">
                                {{ $p->customer->company_name ?? $p->customer->name }}
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-slate-700">
                                {{ $p->type }}
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-slate-600">
                                {{ $p->payment_method }}
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-slate-500">
                                {{ $p->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-right font-extrabold text-emerald-700 text-xs sm:text-sm">
                                Rp {{ number_format($p->amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-slate-400">Tidak ada data transaksi pembayaran pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
