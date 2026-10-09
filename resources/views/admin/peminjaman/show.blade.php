@extends('layouts.app')

@section('title', 'Detail Peminjaman ' . $borrowing->borrow_code . ' - Admin')
@section('page-title', 'Detail & Management Approval Peminjaman')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3 mb-1">
                <span class="font-mono font-bold text-lg text-blue-600 bg-blue-50 px-3 py-1 rounded-lg border border-blue-200">{{ $borrowing->borrow_code }}</span>
                <span class="px-3 py-1 rounded-full text-xs font-bold {{ $borrowing->status_badge_class }}">{{ $borrowing->status }}</span>
            </div>
            <h1 class="text-xl font-bold text-slate-800">{{ $borrowing->customer->company_name ?? $borrowing->customer->name }}</h1>
            <p class="text-xs text-slate-500 mt-1">Masa Pinjam: <strong>{{ $borrowing->start_date->format('d F Y') }}</strong> s/d <strong>{{ $borrowing->end_date->format('d F Y') }}</strong></p>
        </div>

        <div class="flex items-center space-x-3">
            @if($borrowing->status === 'Diajukan')
                <form action="{{ route('admin.borrowings.approve', $borrowing->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-md">
                        <i class="fa-solid fa-check mr-1"></i> Setujui
                    </button>
                </form>
                <form action="{{ route('admin.borrowings.reject', $borrowing->id) }}" method="POST" onsubmit="return confirm('Tolak pengajuan peminjaman ini?')">
                    @csrf
                    <input type="hidden" name="notes" value="Ditolak oleh Administrator">
                    <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs rounded-xl shadow-md">
                        <i class="fa-solid fa-xmark mr-1"></i> Tolak
                    </button>
                </form>
            @endif
            <a href="{{ route('admin.borrowings.index') }}" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold text-xs rounded-xl">Kembali</a>
        </div>
    </div>

    <!-- Equipment List -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
        <h3 class="font-bold text-slate-800 text-sm border-b border-slate-100 pb-3">Daftar Alat Dipinjam</h3>
        <div class="space-y-3">
            @foreach($borrowing->borrowingDetails as $detail)
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between text-xs">
                    <div>
                        <h4 class="font-bold text-slate-800 text-sm">{{ $detail->equipment->name }}</h4>
                        <p class="text-slate-500 mt-0.5">Kode: {{ $detail->equipment->code }} | Merk: {{ $detail->equipment->brand ?? 'N/A' }}</p>
                    </div>
                    <div class="text-right">
                        <span class="font-bold text-slate-800 block">{{ $detail->quantity }} Unit</span>
                        <span class="text-slate-500">Rp {{ number_format($detail->equipment->rental_price_per_day, 0, ',', '.') }}/hari</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Financial Breakdown -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
        <h3 class="font-bold text-slate-800 text-sm border-b border-slate-100 pb-3">Rincian Deposit & Keuangan</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
            <div class="p-4 rounded-xl bg-blue-50 border border-blue-100">
                <span class="text-slate-500 block">Total Deposit</span>
                <span class="text-lg font-extrabold text-blue-700">Rp {{ number_format($borrowing->deposit_amount, 0, ',', '.') }}</span>
            </div>
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-100">
                <span class="text-slate-500 block">Denda / Keterlambatan</span>
                <span class="text-lg font-extrabold text-rose-700">Rp {{ number_format($borrowing->fine_amount, 0, ',', '.') }}</span>
            </div>
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-100">
                <span class="text-slate-500 block">Refund Deposit</span>
                <span class="text-lg font-extrabold text-emerald-700">Rp {{ number_format($borrowing->refund_amount, 0, ',', '.') }}</span>
                <span class="text-[11px] block mt-1 text-slate-500 font-semibold">Status Refund: {{ $borrowing->refund_status }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
