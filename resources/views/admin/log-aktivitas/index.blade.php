@extends('layouts.app')

@section('title', 'Log Aktivitas & Audit Trail - Admin')
@section('page-title', 'Audit Trail Log Aktivitas Sistem')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-200 bg-slate-50 flex flex-col sm:flex-row sm:items-center justify-between gap-1 sm:gap-2">
            <h3 class="text-sm font-bold text-slate-800">Catatan Aktivitas Pengguna & Log Keamanan</h3>
            <span class="text-xs text-slate-500">Perekaman Aktivitas ISO/IEC 17025 Compliance</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[650px]">
                <thead class="bg-slate-100 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-4 sm:px-5 py-3">Waktu</th>
                        <th class="px-4 sm:px-5 py-3">Pengguna</th>
                        <th class="px-4 sm:px-5 py-3">Role</th>
                        <th class="px-4 sm:px-5 py-3">Aktivitas / Deskripsi</th>
                        <th class="px-4 sm:px-5 py-3">Alamat IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 sm:px-5 py-3 text-slate-600 font-mono text-[11px] whitespace-nowrap">
                                {{ $log->created_at->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="px-4 sm:px-5 py-3 font-bold text-slate-800">
                                {{ $log->user->name ?? 'Sistem / Anonim' }}
                            </td>
                            <td class="px-4 sm:px-5 py-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold capitalize bg-slate-100 text-slate-700">
                                    {{ $log->user->role ?? 'system' }}
                                </span>
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-slate-700">
                                {{ $log->description }}
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-slate-500 font-mono text-[11px]">
                                {{ $log->ip_address ?? '127.0.0.1' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-12 text-slate-400">Belum ada catatan aktivitas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
