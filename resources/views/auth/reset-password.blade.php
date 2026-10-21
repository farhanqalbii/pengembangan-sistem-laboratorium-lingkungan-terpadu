@extends('layouts.guest')

@section('title', 'Perbarui Kata Sandi - Sistem Laboratorium Lingkungan Terpadu 2026')
@section('container-width', 'max-w-lg')

@section('content')
<div class="bg-white/95 border border-slate-200/80 backdrop-blur-2xl rounded-3xl p-4 sm:p-10 shadow-2xl shadow-slate-300/40 relative">
    <!-- Header -->
    <div class="border-b border-slate-100 pb-5 mb-6">
        <h2 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Buat Kata Sandi Baru</h2>
        <p class="text-xs text-slate-500 mt-1">Silakan masukkan email akun dan tentukan kata sandi baru Anda.</p>
    </div>

    @if($errors->any())
        <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium flex items-center gap-2">
            <i class="fa-solid fa-circle-exclamation text-rose-600"></i>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <form action="{{ route('password.update') }}" method="POST" class="space-y-5" x-data="{ submitting: false }" @submit="if ($el.checkValidity()) submitting = true" x-init="window.addEventListener('pageshow', () => submitting = false)">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5 flex items-center gap-1.5">
                <i class="fa-regular fa-envelope text-emerald-600 text-xs"></i> Alamat Email
            </label>
            <input type="email" name="email" value="{{ old('email', $email) }}" required
                class="w-full bg-slate-50/80 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 transition-all">
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5 flex items-center gap-1.5">
                <i class="fa-solid fa-lock text-emerald-600 text-xs"></i> Kata Sandi Baru
            </label>
            <div class="relative">
                <input type="password" id="reset_password" name="password" required placeholder="Minimal 8 karakter"
                    class="w-full bg-slate-50/80 border border-slate-200 rounded-xl pl-4 pr-11 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                <button type="button" onclick="togglePassword('reset_password', this)" title="Tampilkan / Sembunyikan Kata Sandi"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors focus:outline-none p-1.5 cursor-pointer">
                    <i class="fa-solid fa-eye text-sm"></i>
                </button>
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5 flex items-center gap-1.5">
                <i class="fa-solid fa-shield-halved text-emerald-600 text-xs"></i> Konfirmasi Kata Sandi Baru
            </label>
            <div class="relative">
                <input type="password" id="reset_password_confirmation" name="password_confirmation" required placeholder="Ulangi kata sandi baru"
                    class="w-full bg-slate-50/80 border border-slate-200 rounded-xl pl-4 pr-11 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                <button type="button" onclick="togglePassword('reset_password_confirmation', this)" title="Tampilkan / Sembunyikan Kata Sandi"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors focus:outline-none p-1.5 cursor-pointer">
                    <i class="fa-solid fa-eye text-sm"></i>
                </button>
            </div>
        </div>

        <button type="submit" 
            :disabled="submitting"
            :class="{ 'opacity-75 pointer-events-none': submitting }"
            class="w-full bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-500 hover:to-teal-600 active:scale-[0.99] text-white font-bold py-3.5 px-4 rounded-xl shadow-lg shadow-emerald-700/25 hover:shadow-emerald-700/35 transition-all text-sm mt-3 flex items-center justify-center gap-2 cursor-pointer disabled:cursor-not-allowed">
            <i class="fa-solid fa-spinner fa-spin text-sm" x-show="submitting" x-cloak></i>
            <span x-text="submitting ? 'Menyimpan Kata Sandi...' : 'Simpan Kata Sandi Baru'">Simpan Kata Sandi Baru</span>
            <i class="fa-solid fa-circle-check" x-show="!submitting"></i>
        </button>
    </form>
</div>
@endsection
