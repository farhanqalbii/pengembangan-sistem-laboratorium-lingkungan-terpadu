@extends('layouts.app')

@section('title', 'Pusat Notifikasi - LIMS 2026')
@section('page-title', 'Pusat Notifikasi System')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800">Semua Notifikasi Saya</h3>
            <form action="{{ route('notifications.read-all') }}" method="POST">
                @csrf
                <button type="submit" class="text-xs text-emerald-600 hover:underline font-bold">Tandai Semua Dibaca</button>
            </form>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($notifications as $n)
                <div class="p-3.5 sm:p-4 flex items-start justify-between hover:bg-slate-50 transition-colors {{ $n->read_at ? 'opacity-75' : 'bg-emerald-50/20' }}">
                    <div class="flex items-start space-x-3">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-sm font-bold flex-shrink-0 {{ $n->read_at ? 'bg-slate-100 text-slate-500' : 'bg-emerald-100 text-emerald-700' }}">
                            <i class="fa-solid fa-bell"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-800 text-xs">{{ $n->title }}</h4>
                            <p class="text-xs text-slate-600 mt-0.5">{{ $n->message }}</p>
                            <span class="text-[10px] text-slate-400 mt-1 block">{{ $n->created_at->diffForHumans() }}</span>
                        </div>
                    </div>

                    @if($n->link)
                        <a href="{{ route('notifications.read', $n->id) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-lg flex-shrink-0">
                            Buka Link &rarr;
                        </a>
                    @endif
                </div>
            @empty
                <div class="text-center py-12 text-slate-400 text-xs">Belum ada notifikasi.</div>
            @endforelse
        </div>

        @if($notifications->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
