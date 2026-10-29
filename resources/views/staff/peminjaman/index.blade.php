@extends('layouts.app')

@section('title', 'Manajemen Peminjaman & Pengembalian Alat - Staff')
@section('page-title', 'Peminjaman, Otorisasi & Pengembalian Alat')

@section('content')
<div class="space-y-6">
    <!-- Filter & Search Bar -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-sm space-y-4">
        <form action="{{ route('staff.borrowings.index') }}" method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <div class="flex-1 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <div class="relative flex-1">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Cari kode pinjam, nama customer, atau nama alat..." 
                           class="w-full bg-slate-50 border border-slate-300 text-xs rounded-xl pl-9 pr-4 py-2.5 text-slate-800 placeholder-slate-400 focus:outline-none focus:border-emerald-500 focus:bg-white transition-all">
                </div>

                <div class="w-full sm:w-48">
                    <select name="status" onchange="this.form.submit()" class="w-full text-xs rounded-xl px-3 py-2.5 bg-slate-50 border border-slate-300 text-slate-800 focus:outline-none focus:border-emerald-500">
                        <option value="">-- Semua Status --</option>
                        <option value="Diajukan" {{ request('status') === 'Diajukan' ? 'selected' : '' }}>Diajukan</option>
                        <option value="Disetujui" {{ request('status') === 'Disetujui' ? 'selected' : '' }}>Disetujui</option>
                        <option value="Dipinjam" {{ request('status') === 'Dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                        <option value="Dikembalikan" {{ request('status') === 'Dikembalikan' ? 'selected' : '' }}>Dikembalikan</option>
                        <option value="Terlambat" {{ request('status') === 'Terlambat' ? 'selected' : '' }}>Terlambat</option>
                        <option value="Ditolak" {{ request('status') === 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-semibold shadow-xs transition-colors cursor-pointer">
                    Filter
                </button>
                @if(request()->filled('search') || request()->filled('status'))
                    <a href="{{ route('staff.borrowings.index') }}" class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition-colors text-center" title="Reset Filter">
                        <i class="fa-solid fa-arrow-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Main List Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-200 bg-slate-50 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Daftar Peminjaman Instrumen Laboratorium</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Otorisasi kesiapan fisik alat, pemantauan jadwal, dan inspeksi pengembalian stok</p>
            </div>
        </div>

        <!-- Desktop Table View (Hidden on mobile) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[700px]">
                <thead class="bg-slate-100 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-4 sm:px-5 py-3">Kode Pinjam</th>
                        <th class="px-4 sm:px-5 py-3">Customer</th>
                        <th class="px-4 sm:px-5 py-3">Instrumen Alat</th>
                        <th class="px-4 sm:px-5 py-3">Periode Pinjam</th>
                        <th class="px-4 sm:px-5 py-3">Status</th>
                        <th class="px-4 sm:px-5 py-3 text-center">Aksi Staff</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($borrowings as $b)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 sm:px-5 py-3 whitespace-nowrap">
                                <a href="{{ route('staff.borrowings.show', $b) }}" 
                                   class="font-mono font-bold text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:underline transition-colors inline-flex items-center gap-1.5"
                                   title="Lihat Detail Peminjaman {{ $b->borrow_code }}">
                                    <i class="fa-solid fa-file-contract text-blue-500/70 text-[11px]"></i>
                                    <span>{{ $b->borrow_code }}</span>
                                </a>
                                @if($b->perpanjangan_diajukan && $b->status_perpanjangan === 'Pending')
                                    <span class="block text-[10px] font-bold text-amber-700 bg-amber-50 border border-amber-200 rounded px-1.5 py-0.5 mt-1 w-fit">
                                        Perpanjangan Diajukan
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 sm:px-5 py-3">
                                <div class="font-bold text-slate-800">{{ $b->customer->company_name ?? $b->customer->name }}</div>
                                <div class="text-[11px] text-slate-500">{{ $b->customer->phone ?? $b->customer->email }}</div>
                            </td>
                            <td class="px-4 sm:px-5 py-3 font-semibold text-slate-700">
                                @if($b->borrowingDetails->isNotEmpty())
                                    <div class="font-bold text-slate-800">{{ $b->borrowingDetails->first()->equipment->name ?? 'Alat Lab' }}</div>
                                    @if($b->borrowingDetails->count() > 1)
                                        <div class="text-[10px] text-slate-400">+{{ $b->borrowingDetails->count() - 1 }} alat lainnya</div>
                                    @endif
                                @else
                                    <span class="text-slate-400 italic">Tidak ada rincian alat</span>
                                @endif
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-slate-600">
                                <div>{{ $b->start_date->format('d/m/Y') }} s/d {{ $b->end_date->format('d/m/Y') }}</div>
                                <div class="text-[10px] text-slate-400">Durasi: {{ $b->start_date->diffInDays($b->end_date) + 1 }} hari</div>
                            </td>
                            <td class="px-4 sm:px-5 py-3">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $b->status_badge_class }}">
                                    {{ $b->status }}
                                </span>
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-center">
                                <a href="{{ route('staff.borrowings.show', $b) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl shadow-xs text-xs transition-colors">
                                    <span>{{ in_array($b->status, ['Diajukan', 'Menunggu']) ? 'Otorisasi & Kesiapan' : (in_array($b->status, ['Dipinjam', 'Terlambat']) ? 'Inspeksi & Pengembalian' : 'Lihat Detail') }}</span>
                                    <i class="fa-solid fa-chevron-right text-[10px]"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-slate-400">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <i class="fa-solid fa-box-open text-3xl text-slate-300"></i>
                                    <p class="font-semibold text-slate-500">Tidak ada data peminjaman yang ditemukan.</p>
                                    @if(request()->filled('search') || request()->filled('status'))
                                        <a href="{{ route('staff.borrowings.index') }}" class="text-xs text-emerald-600 hover:underline">Reset pencarian & filter</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View (Visible only on mobile) -->
        <div class="block md:hidden p-4 space-y-3.5 divide-y divide-slate-100">
            @forelse($borrowings as $b)
                <div class="pt-3.5 first:pt-0 space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <a href="{{ route('staff.borrowings.show', $b) }}" 
                           class="font-mono font-bold text-xs text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/40 px-2 py-0.5 rounded border border-blue-200 dark:border-blue-800/60 hover:underline inline-flex items-center gap-1"
                           title="Lihat Detail Peminjaman {{ $b->borrow_code }}">
                            <i class="fa-solid fa-file-contract text-[10px] opacity-75"></i>
                            <span>{{ $b->borrow_code }}</span>
                        </a>
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $b->status_badge_class }}">
                            {{ $b->status }}
                        </span>
                    </div>

                    <div>
                        <h4 class="font-bold text-slate-800 text-sm">{{ $b->customer->company_name ?? $b->customer->name }}</h4>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $b->customer->phone ?? $b->customer->email }}</p>
                    </div>

                    <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100 space-y-1 text-xs">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-400">Instrumen:</span>
                            <span class="font-bold text-slate-700">
                                {{ $b->borrowingDetails->first()->equipment->name ?? 'Alat Lab' }}
                                @if($b->borrowingDetails->count() > 1)
                                    <span class="text-[10px] text-slate-400 font-normal">(+{{ $b->borrowingDetails->count() - 1 }})</span>
                                @endif
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-400">Periode:</span>
                            <span class="font-semibold text-slate-700">{{ $b->start_date->format('d/m/Y') }} s/d {{ $b->end_date->format('d/m/Y') }}</span>
                        </div>
                    </div>

                    <a href="{{ route('staff.borrowings.show', $b) }}" class="w-full py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl shadow-xs text-xs flex items-center justify-center gap-1.5 transition-colors">
                        <span>{{ in_array($b->status, ['Diajukan', 'Menunggu']) ? 'Otorisasi & Kesiapan' : (in_array($b->status, ['Dipinjam', 'Terlambat']) ? 'Inspeksi & Pengembalian' : 'Lihat Detail') }}</span>
                        <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    </a>
                </div>
            @empty
                <div class="text-center py-10 text-slate-400 space-y-2">
                    <i class="fa-solid fa-box-open text-3xl text-slate-300"></i>
                    <p class="font-semibold text-slate-500 text-xs">Tidak ada data peminjaman yang ditemukan.</p>
                </div>
            @endforelse
        </div>

        @if($borrowings->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50">
                {{ $borrowings->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
