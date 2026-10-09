@extends('layouts.app')

@section('title', 'Keuangan & Deposit Refund - Admin')
@section('page-title', 'Manajemen Keuangan & Deposit Refund')

@section('content')
<div class="space-y-6">
    <!-- Top Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/90 shadow-sm">
            <span class="text-slate-400 text-[11px] font-bold uppercase tracking-wider block">Total Pendapatan Terverifikasi</span>
            <h3 class="text-xl sm:text-2xl font-black text-emerald-700 mt-1">Rp {{ number_format($totalIncome, 0, ',', '.') }}</h3>
        </div>
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/90 shadow-sm">
            <span class="text-slate-400 text-[11px] font-bold uppercase tracking-wider block">Pembayaran Menunggu Konfirmasi</span>
            <h3 class="text-xl sm:text-2xl font-black text-amber-700 mt-1">{{ $pendingPayments->count() }}</h3>
        </div>
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/90 shadow-sm">
            <span class="text-slate-400 text-[11px] font-bold uppercase tracking-wider block">Refund Deposit Menunggu Cair</span>
            <h3 class="text-xl sm:text-2xl font-black text-indigo-700 mt-1">{{ $pendingRefunds->count() }}</h3>
        </div>
    </div>

    <!-- Refund Deposit Processing Section -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
            <h3 class="text-xs sm:text-sm font-bold text-slate-800">Antrean Pengembalian Jaminan (Refund Deposit Peminjaman)</h3>
            <span class="text-xs text-slate-500">Pencairan Dana ke Rekening Customer</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-100 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-4 sm:px-5 py-3">Kode Pinjam</th>
                        <th class="px-4 sm:px-5 py-3">Customer</th>
                        <th class="px-4 sm:px-5 py-3">Deposit Awal</th>
                        <th class="px-4 sm:px-5 py-3">Denda</th>
                        <th class="px-4 sm:px-5 py-3">Jumlah Refund</th>
                        <th class="px-4 sm:px-5 py-3 text-center">Aksi Process Refund</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($pendingRefunds as $b)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 sm:px-5 py-3 font-mono font-bold text-blue-600">
                                {{ $b->borrow_code }}
                            </td>
                            <td class="px-4 sm:px-5 py-3 font-bold text-slate-800">
                                {{ $b->customer->company_name ?? $b->customer->name }}
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-slate-600">
                                Rp {{ number_format($b->deposit_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 sm:px-5 py-3 font-bold text-rose-600">
                                Rp {{ number_format($b->fine_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 sm:px-5 py-3 font-black text-emerald-700">
                                Rp {{ number_format($b->refund_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-center whitespace-nowrap">
                                <form action="{{ route('admin.finance.refund', $b->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-lg shadow-sm">
                                        <i class="fa-solid fa-check-circle mr-1"></i> Cairkan Refund
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-slate-400">Tidak ada antrean refund deposit.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
