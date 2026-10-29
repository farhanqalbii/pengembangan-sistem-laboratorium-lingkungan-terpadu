@extends('layouts.app')

@section('title', 'Manajemen Sampel Lab - Staff')
@section('page-title', 'Pendaftaran & Pengujian Sampel')

@section('content')
<div class="space-y-6">
    <!-- Filter & Toolbar Card -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4 anim-fade-up anim-delay-1 card-hover">
        <form action="{{ route('staff.samples.index') }}" method="GET" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <div class="relative w-full sm:w-64">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode / customer / jenis..." class="bg-slate-50 border border-slate-200 focus:bg-white text-xs rounded-xl pl-9 pr-3 py-2 text-slate-800 w-full focus:outline-none focus:border-emerald-500 transition-colors">
            </div>

            <div class="relative w-full sm:w-48">
                <select name="status" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 focus:bg-white text-xs rounded-xl px-3 py-2 text-slate-800 w-full focus:outline-none focus:border-emerald-500 transition-colors cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="Diajukan" {{ request('status') === 'Diajukan' ? 'selected' : '' }}>Diajukan</option>
                    <option value="Diterima" {{ request('status') === 'Diterima' ? 'selected' : '' }}>Diterima</option>
                    <option value="Diproses" {{ request('status') === 'Diproses' ? 'selected' : '' }}>Diproses</option>
                    <option value="Diuji" {{ request('status') === 'Diuji' ? 'selected' : '' }}>Diuji</option>
                    <option value="Menunggu Verifikasi" {{ request('status') === 'Menunggu Verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                    <option value="Revisi" {{ request('status') === 'Revisi' ? 'selected' : '' }}>Revisi</option>
                    <option value="Selesai" {{ request('status') === 'Selesai' ? 'selected' : '' }}>Selesai</option>
                </select>
            </div>

            <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-semibold shadow-xs transition-colors cursor-pointer">
                <i class="fa-solid fa-filter text-[10px]"></i> Filter
            </button>

            @if(request('search') || request('status'))
                <a href="{{ route('staff.samples.index') }}" class="inline-flex items-center gap-1 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl transition-colors">
                    <i class="fa-solid fa-rotate-left text-[10px]"></i> Reset
                </a>
            @endif
        </form>

        <div class="flex items-center gap-2 self-start md:self-center text-xs font-semibold text-slate-500 bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200">
            <i class="fa-solid fa-vials text-emerald-600 text-xs"></i>
            <span>Total: <strong class="text-slate-800">{{ $samples->total() }}</strong> Sampel</span>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden anim-fade-up anim-delay-2 card-hover">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[760px]">
                <thead class="bg-slate-50/80 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="px-4 py-3">Kode Sampel</th>
                        <th class="px-4 py-3">Customer / Pemohon</th>
                        <th class="px-4 py-3">Jenis Sampel</th>
                        <th class="px-4 py-3">Tgl Diterima</th>
                        <th class="px-4 py-3">Status Pengujian</th>
                        <th class="px-4 py-3">Pembayaran</th>
                        <th class="px-4 py-3 text-right">Aksi Operasional</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($samples as $sample)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="font-mono font-bold text-xs text-emerald-700 bg-emerald-50 border border-emerald-200/80 px-2.5 py-1 rounded-lg inline-block shadow-2xs">
                                    {{ $sample->sample_code }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="space-y-0.5 min-w-[160px]">
                                    <div class="font-bold text-slate-800 text-xs flex items-center gap-1.5">
                                        <i class="fa-regular fa-building text-slate-400 text-[11px] shrink-0"></i>
                                        <span class="truncate max-w-[220px]">{{ $sample->customer->company_name ?? $sample->customer->name }}</span>
                                    </div>
                                    <div class="text-[11px] text-slate-400 flex items-center gap-1.5 pl-4">
                                        <i class="fa-solid fa-phone text-[9px] text-slate-300 shrink-0"></i>
                                        <span>{{ $sample->customer->phone ?? '-' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="space-y-0.5 min-w-[150px]">
                                    <div class="font-semibold text-slate-800 text-xs flex items-center gap-1.5">
                                        <i class="fa-solid fa-flask text-emerald-600/80 text-[11px] shrink-0"></i>
                                        <span class="truncate max-w-[200px]">{{ $sample->sample_type }}</span>
                                    </div>
                                    @if($sample->sampleParameters->count() > 0)
                                        <div class="text-[10px] text-slate-400 pl-4 flex items-center gap-1">
                                            <i class="fa-solid fa-vials text-[9px] text-slate-300"></i>
                                            <span>{{ $sample->sampleParameters->count() }} Parameter Diuji</span>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-xs text-slate-600 flex items-center gap-1.5">
                                    <i class="fa-regular fa-calendar text-[11px] text-slate-400"></i>
                                    <span>{{ $sample->received_date ? $sample->received_date->format('d M Y') : 'Belum Diterima' }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold inline-block {{ $sample->status_badge_class }}">
                                    {{ $sample->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @php $pay = $sample->payments->first(); @endphp
                                @if($pay && $pay->status === 'Berhasil')
                                    <div>
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200/80">
                                            <i class="fa-solid fa-circle-check text-[10px] text-emerald-600"></i> Lunas
                                        </span>
                                        <span class="block text-[10px] text-slate-400 mt-0.5 truncate max-w-[140px]">{{ $pay->payment_method }}</span>
                                    </div>
                                @elseif($pay && in_array($pay->status, ['Gagal', 'Kadaluarsa']))
                                    <div>
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-rose-700 bg-rose-50 px-2.5 py-0.5 rounded-full border border-rose-200/80">
                                            <i class="fa-solid fa-circle-xmark text-[10px] text-rose-600"></i> {{ $pay->status }}
                                        </span>
                                        <span class="block text-[10px] text-slate-400 mt-0.5 truncate max-w-[140px]">{{ $pay->payment_method }}</span>
                                    </div>
                                @elseif($pay)
                                    <div>
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-700 bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-200/80">
                                            <i class="fa-solid fa-clock text-[10px] text-amber-500"></i> Menunggu Bayar
                                        </span>
                                        <span class="block text-[10px] text-slate-400 mt-0.5 truncate max-w-[140px]">{{ $pay->payment_method }}</span>
                                    </div>
                                @else
                                    <span class="text-slate-300 text-xs font-mono">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('staff.samples.show', $sample) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 font-semibold text-xs rounded-lg transition-colors shadow-2xs">
                                        <i class="fa-regular fa-eye text-[11px] text-slate-400"></i> Detail
                                    </a>
                                    @if(in_array($sample->status, ['Diterima', 'Diproses', 'Diuji', 'Revisi']))
                                        <a href="{{ route('staff.samples.input-results', $sample) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-lg shadow-sm transition-all hover:shadow-emerald-600/20 whitespace-nowrap">
                                            <i class="fa-solid fa-pen-to-square text-[11px]"></i> Input Hasil
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 px-4">
                                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center text-lg mb-3">
                                    <i class="fa-solid fa-vials"></i>
                                </div>
                                <p class="text-xs font-bold text-slate-700">Tidak Ada Sampel Ditemukan</p>
                                <p class="text-[11px] text-slate-400 mt-1">Coba sesuaikan kata kunci pencarian atau filter status Anda.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($samples->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50">
                {{ $samples->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
