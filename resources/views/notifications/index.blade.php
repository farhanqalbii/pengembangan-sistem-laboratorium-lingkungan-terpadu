@extends('layouts.app')

@section('title', 'Pusat Notifikasi - LIMS 2026')
@section('page-title', 'Pusat Notifikasi System')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-800 dark:text-white">Pusat Notifikasi</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Daftar seluruh pemberitahuan dan status aktivitas akun Anda</p>
            </div>
            <form action="{{ route('notifications.read-all') }}" method="POST">
                @csrf
                <button type="submit" class="text-xs text-emerald-600 dark:text-emerald-400 hover:underline font-bold inline-flex items-center gap-1 cursor-pointer">
                    <i class="fa-solid fa-check-double text-[11px]"></i> Tandai Semua Dibaca
                </button>
            </form>
        </div>

        <div class="divide-y divide-slate-100 dark:divide-slate-800">
            @forelse($notifications as $n)
                <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-start justify-between gap-3.5 transition-colors {{ $n->read_at ? 'bg-white dark:bg-slate-900 hover:bg-slate-50/80 dark:hover:bg-slate-800/40 opacity-85' : 'bg-emerald-50/40 dark:bg-emerald-950/20 hover:bg-emerald-50/70 dark:hover:bg-emerald-900/30' }}">
                    <div class="flex items-start space-x-3.5 min-w-0 flex-1">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm font-bold flex-shrink-0 {{ $n->read_at ? 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500' : 'bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-400 ring-2 ring-emerald-500/20' }}">
                            <i class="fa-solid fa-bell"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <h4 class="font-bold text-slate-800 dark:text-white text-sm">{{ $n->title }}</h4>
                                @if(!$n->read_at)
                                    <span class="inline-flex items-center px-1.5 py-0.5 text-[10px] font-bold rounded-full bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300">
                                        Baru
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-600 dark:text-slate-300 break-words leading-relaxed">{{ $n->message }}</p>
                            <div class="flex items-center gap-3 mt-2 text-[11px] text-slate-400 dark:text-slate-500">
                                <span class="flex items-center gap-1">
                                    <i class="fa-regular fa-clock text-[10px]"></i> {{ $n->created_at->diffForHumans() }}
                                </span>
                                @if($n->read_at)
                                    <span class="text-emerald-600 dark:text-emerald-400 flex items-center gap-1 font-medium">
                                        <i class="fa-solid fa-check text-[10px]"></i> Dibaca {{ $n->read_at->diffForHumans() }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 self-start sm:self-center flex-shrink-0 pt-2 sm:pt-0">
                        @if(!$n->read_at)
                            <form action="{{ route('notifications.read', $n->id) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-lg cursor-pointer inline-flex items-center gap-1.5 shadow-xs transition-colors">
                                    @if($n->link)
                                        <span>Buka & Baca</span>
                                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                    @else
                                        <i class="fa-solid fa-check text-[10px]"></i>
                                        <span>Tandai Dibaca</span>
                                    @endif
                                </button>
                            </form>
                        @elseif($n->link)
                            <a href="{{ $n->link }}" class="px-3.5 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold text-xs rounded-lg inline-flex items-center gap-1.5 transition-colors">
                                <span>Buka Link</span>
                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-16 text-slate-400 dark:text-slate-500 text-xs">
                    <i class="fa-regular fa-bell-slash text-3xl mb-2 block opacity-50"></i>
                    Belum ada riwayat notifikasi.
                </div>
            @endforelse
        </div>

        @if($notifications->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-900">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
