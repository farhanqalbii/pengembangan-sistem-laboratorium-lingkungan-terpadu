@extends('layouts.guest')

@section('title', 'Login - Sistem Laboratorium Lingkungan Terpadu 2026')
@section('hide-header-logo', true)
@section('container-width', 'max-w-4xl')

@section('content')
<div class="bg-white border border-slate-200/80 rounded-3xl shadow-xl shadow-slate-900/5 overflow-hidden flex flex-col md:flex-row items-stretch">
    <!-- 1. BAGIAN KIRI: Panel Visual & Branding (Foto Nyata Lab + Overlay Branding) -->
    <div class="w-full md:w-[45%] p-4 sm:p-8 flex flex-col justify-between relative overflow-hidden text-white shrink-0">
        <!-- Real Lab Photo Background -->
        <img src="{{ asset('images/login_lab_real.jpg') }}" alt="Laboratorium Lingkungan Terpadu" class="absolute inset-0 w-full h-full object-cover object-center">
        
        <!-- Gradient Overlay matching Logo & Theme Colors (Emerald / Teal / Navy) -->
        <div class="absolute inset-0 bg-gradient-to-b from-[#06332c]/80 via-[#0a483e]/85 to-[#041f1a]/95"></div>

        <!-- Ambient Watermark Beaker Silhouette (Sangat Halus) -->
        <div class="absolute -right-6 -bottom-6 w-44 h-44 opacity-[0.06] pointer-events-none">
            <img src="{{ asset('images/logo_icon.png') }}" alt="" class="w-full h-full object-contain filter brightness-200">
        </div>

        <!-- Tombol Back ke Halaman Utama (Pojok Kiri Atas Box) -->
        <div class="relative z-10">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-100 hover:text-white transition-colors bg-white/10 hover:bg-white/20 backdrop-blur-md px-3 py-1.5 rounded-full border border-white/15 group">
                <i class="fa-solid fa-arrow-left text-[10px] group-hover:-translate-x-0.5 transition-transform"></i>
                <span>Kembali ke Halaman Utama</span>
            </a>
        </div>

        <!-- Area Tengah: Judul, Deskripsi & Fitur -->
        <div class="my-auto py-4 relative z-10 space-y-4">
            <div>
                <h2 class="text-xl sm:text-2xl font-extrabold text-white leading-tight tracking-tight drop-shadow-sm">
                    Sistem Laboratorium Terpadu
                </h2>
                <p class="text-xs sm:text-[13px] text-emerald-100 mt-2 leading-relaxed drop-shadow-sm">
                    Pelayanan pengujian sampel lingkungan, peminjaman alat laboratorium, dan penerbitan sertifikat hasil uji secara akurat dan transparan.
                </p>
            </div>

            <!-- Tiga Fitur -->
            <div class="space-y-2.5 pt-2">
                <div class="flex items-center gap-2.5 text-xs text-emerald-100">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 shrink-0 shadow-xs"></span>
                    <span class="drop-shadow-sm">Pengujian Parameter Lingkungan</span>
                </div>
                <div class="flex items-center gap-2.5 text-xs text-emerald-100">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 shrink-0 shadow-xs"></span>
                    <span class="drop-shadow-sm">Peminjaman Instrumen Laboratorium</span>
                </div>
                <div class="flex items-center gap-2.5 text-xs text-emerald-100">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 shrink-0 shadow-xs"></span>
                    <span class="drop-shadow-sm">Sertifikat Hasil Uji (LHU) Digital</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. BAGIAN KANAN: Form Login (55% lebar container) -->
    <div class="w-full md:w-[55%] p-4 sm:p-8 flex flex-col justify-center bg-white relative">
        <!-- Header -->
        <div class="border-b border-slate-100 pb-3 mb-4">
            <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">Login Portal Laboratorium</h2>
            <p class="text-xs text-slate-500 mt-1">Masukkan email dan kata sandi Anda untuk mengakses portal.</p>
        </div>

        @if(session('success'))
            <div class="mb-3.5 p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-600"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-3.5 p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation text-rose-600"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <!-- Form Login -->
        <form action="{{ route('login') }}" method="POST" class="space-y-3.5" x-data="{ submitting: false }" @submit="if ($el.checkValidity()) submitting = true" x-init="window.addEventListener('pageshow', () => submitting = false)">
            @csrf

            <!-- Email Input -->
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-700 mb-1">
                    Alamat Email
                </label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                    placeholder="nama@email.com"
                    class="w-full bg-slate-50/80 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 transition-all">
            </div>

            <!-- Password Input -->
            <div>
                <label for="password" class="block text-xs font-semibold text-slate-700 mb-1">
                    Kata Sandi
                </label>
                <div class="relative">
                    <input type="password" id="password" name="password" required
                        placeholder="Masukkan kata sandi Anda"
                        class="w-full bg-slate-50/80 border border-slate-200 rounded-xl pl-3.5 pr-11 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                    <button type="button" onclick="togglePassword('password', this)" title="Tampilkan / Sembunyikan Kata Sandi"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors focus:outline-none p-1.5 cursor-pointer">
                        <i class="fa-solid fa-eye text-sm"></i>
                    </button>
                </div>
            </div>

            <!-- Ingat Saya & Lupa Kata Sandi (Sejajar Satu Baris) -->
            <div class="flex items-center justify-between text-xs pt-0.5">
                <label class="flex items-center text-slate-600 hover:text-slate-800 cursor-pointer select-none">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 focus:ring-offset-0">
                    <span class="ml-2 font-medium">Ingat Saya</span>
                </label>
                <a href="{{ route('password.request') }}" class="text-emerald-700 hover:text-emerald-800 font-semibold transition-colors">
                    Lupa Kata Sandi?
                </a>
            </div>

            <!-- Tombol Masuk ke Akun -->
            <button type="submit" 
                :disabled="submitting"
                :class="{ 'opacity-75 pointer-events-none': submitting }"
                class="w-full bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-500 hover:to-teal-600 active:scale-[0.99] text-white font-bold py-2.5 px-4 rounded-xl shadow-md shadow-emerald-700/15 hover:shadow-emerald-700/25 transition-all text-sm mt-1 flex items-center justify-center gap-2 cursor-pointer disabled:cursor-not-allowed">
                <i class="fa-solid fa-spinner fa-spin text-sm" x-show="submitting" x-cloak></i>
                <span x-text="submitting ? 'Memproses Masuk...' : 'Masuk'">Masuk</span>
            </button>
        </form>

        <!-- Link Registrasi -->
        <div class="mt-4 pt-3.5 border-t border-slate-100 text-center space-y-1.5">
            <p class="text-xs text-slate-500">Belum memiliki akun Customer?</p>
            <div>
                <a href="{{ route('register') }}" class="inline-block text-xs font-bold text-emerald-700 hover:text-emerald-800 transition-colors">
                    Daftar Akun Customer Baru
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
