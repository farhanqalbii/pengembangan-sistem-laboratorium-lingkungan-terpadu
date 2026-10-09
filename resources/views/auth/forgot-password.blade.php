@extends('layouts.guest')

@section('title', 'Pemulihan Kata Sandi - Sistem Laboratorium Lingkungan Terpadu 2026')
@section('container-width', 'max-w-md')

@section('content')
<div class="bg-white/95 border border-slate-200/80 backdrop-blur-2xl rounded-3xl p-6 sm:p-8 shadow-2xl shadow-slate-900/10 relative">
    <!-- Header -->
    <div class="border-b border-slate-100 pb-4 mb-5">
        <a href="{{ route('login') }}" class="text-xs text-emerald-700 hover:text-emerald-800 inline-flex items-center gap-1.5 transition-colors mb-2 font-semibold">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Halaman Login
        </a>
        <div class="mt-1">
            <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">Pemulihan Kata Sandi</h2>
            <p class="text-xs text-slate-500 mt-0.5">Masukkan email atau nomor HP terdaftar untuk reset kata sandi.</p>
        </div>
    </div>

    @if(session('status'))
        <div class="mb-5 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs leading-relaxed">
            <div class="flex items-center gap-2 font-bold mb-1.5 text-emerald-700">
                <i class="fa-solid fa-circle-check"></i> Permintaan Terkirim
            </div>
            <p>{{ session('status') }}</p>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium flex items-center gap-2">
            <i class="fa-solid fa-circle-exclamation text-rose-600"></i>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <form action="{{ route('password.email') }}" method="POST" class="space-y-5">
        @csrf

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5 flex items-center gap-1.5">
                <i class="fa-regular fa-envelope text-emerald-600 text-xs"></i> Email atau Nomor HP Terdaftar
            </label>
            <input type="text" name="email_or_phone" value="{{ old('email_or_phone') }}" required autofocus placeholder="nama@perusahaan.com atau 08123456789"
                class="w-full bg-slate-50/80 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 transition-all">
            <p class="text-[11px] text-slate-400 mt-1.5 flex items-center gap-1">
                <i class="fa-solid fa-circle-info text-slate-400 text-[10px]"></i> Sistem akan memvalidasi kecocokan data akun terdaftar Anda.
            </p>
        </div>

        <button type="submit" 
            class="w-full bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-500 hover:to-teal-600 active:scale-[0.99] text-white font-bold py-3.5 px-4 rounded-xl shadow-lg shadow-emerald-700/25 hover:shadow-emerald-700/35 transition-all text-sm mt-3 flex items-center justify-center gap-2">
            <span>Kirim Tautan Reset Kata Sandi</span>
            <i class="fa-solid fa-paper-plane"></i>
        </button>
    </form>
</div>
@endsection
