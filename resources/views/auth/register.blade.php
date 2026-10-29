@extends('layouts.guest')

@section('title', 'Daftar Akun Customer - Sistem Laboratorium Lingkungan Terpadu 2026')
@section('hide-header-logo', true)
@section('container-width', 'max-w-4xl lg:max-w-5xl')

@section('content')
<div class="bg-white border border-slate-200/80 rounded-3xl shadow-xl shadow-slate-900/5 overflow-hidden flex flex-col md:flex-row items-stretch">
    <!-- 1. BAGIAN KIRI: Panel Visual & Branding (Tanpa Logo, Identik dengan Halaman Login) -->
    <div class="w-full md:w-[42%] lg:w-[40%] p-4 sm:p-7 lg:p-8 flex flex-col justify-between relative overflow-hidden text-white shrink-0">
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

        <!-- Area Tengah: Judul, Deskripsi & Fitur (Vertically Centered) -->
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
            <div class="space-y-2.5 pt-1">
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

    <!-- 2. BAGIAN KANAN: Form Register (Rapi, Padat, & Proporsional) -->
    <div class="w-full md:w-[58%] lg:w-[60%] p-4 sm:p-7 lg:p-8 flex flex-col justify-center bg-white relative">
        <!-- Header -->
        <div class="border-b border-slate-100 pb-2.5 mb-3">
            <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">Pendaftaran Akun Customer</h2>
            <p class="text-xs text-slate-500 mt-0.5">Lengkapi formulir di bawah ini untuk memulai pengujian sampel dan peminjaman alat.</p>
        </div>

        @if($errors->any())
            <div class="mb-3 p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                <div class="flex items-center gap-2 font-bold mb-1 text-rose-600">
                    <i class="fa-solid fa-circle-exclamation"></i> Terdapat beberapa kesalahan pengisian:
                </div>
                <ul class="list-disc list-inside space-y-0.5 ml-1 text-rose-700">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register') }}" method="POST" class="space-y-3" x-data="{ submitting: false }" @submit="if ($el.checkValidity()) submitting = true" x-init="window.addEventListener('pageshow', () => submitting = false)">
            @csrf

            <!-- Baris 1: Nama Lengkap & Alamat Email -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Nama Lengkap / Penanggung Jawab
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" required autofocus
                        placeholder="Nama lengkap Anda"
                        class="w-full bg-slate-50/80 border border-slate-200 rounded-xl px-3 py-2 text-xs sm:text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Alamat Email
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        placeholder="nama@email.com"
                        class="w-full bg-slate-50/80 border border-slate-200 rounded-xl px-3 py-2 text-xs sm:text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                </div>
            </div>

            <!-- Baris 2: Nomor WhatsApp & Nama Perusahaan -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Nomor WhatsApp / Telepon
                    </label>
                    <input type="text" name="phone" value="{{ old('phone') }}" required
                        placeholder="Contoh: 081234567890"
                        class="w-full bg-slate-50/80 border border-slate-200 rounded-xl px-3 py-2 text-xs sm:text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1 flex items-center justify-between">
                        <span>Nama Perusahaan / Instansi</span>
                        <span class="text-[10px] text-slate-400 font-normal px-1.5 py-0.2 rounded bg-slate-100 border border-slate-200">Opsional</span>
                    </label>
                    <input type="text" name="company_name" value="{{ old('company_name') }}"
                        placeholder="PT / CV / Instansi / Pribadi"
                        class="w-full bg-slate-50/80 border border-slate-200 rounded-xl px-3 py-2 text-xs sm:text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                </div>
            </div>

            <!-- Baris 3: Alamat Lengkap / Domisili -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                    Alamat Lengkap / Domisili
                </label>
                <textarea name="address" rows="2" placeholder="Alamat jalan, gedung, kota, atau kawasan industri..."
                    class="w-full bg-slate-50/80 border border-slate-200 rounded-xl px-3 py-1.5 text-xs sm:text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 transition-all">{{ old('address') }}</textarea>
            </div>

            <!-- Baris 4: Keamanan Kata Sandi -->
            <div class="pt-2 border-t border-slate-100">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">
                    Keamanan Kata Sandi
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kata Sandi Baru</label>
                        <div class="relative">
                            <input type="password" id="reg_password" name="password" required placeholder="Minimal 8 karakter"
                                class="w-full bg-slate-50/80 border border-slate-200 rounded-xl pl-3 pr-10 py-1.5 sm:py-2 text-xs sm:text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                            <button type="button" onclick="togglePassword('reg_password', this)" title="Tampilkan / Sembunyikan Kata Sandi"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors focus:outline-none p-1 cursor-pointer">
                                <i class="fa-solid fa-eye text-xs"></i>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Konfirmasi Kata Sandi</label>
                        <div class="relative">
                            <input type="password" id="reg_password_confirmation" name="password_confirmation" required placeholder="Ulangi kata sandi"
                                class="w-full bg-slate-50/80 border border-slate-200 rounded-xl pl-3 pr-10 py-1.5 sm:py-2 text-xs sm:text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                            <button type="button" onclick="togglePassword('reg_password_confirmation', this)" title="Tampilkan / Sembunyikan Kata Sandi"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors focus:outline-none p-1 cursor-pointer">
                                <i class="fa-solid fa-eye text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tombol Daftar Akun Baru -->
            <button type="submit" 
                :disabled="submitting"
                :class="{ 'opacity-75 pointer-events-none': submitting }"
                class="w-full bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-500 hover:to-teal-600 active:scale-[0.99] text-white font-bold py-2.5 px-4 rounded-xl shadow-md shadow-emerald-700/15 hover:shadow-emerald-700/25 transition-all text-sm mt-1.5 flex items-center justify-center gap-2 cursor-pointer disabled:cursor-not-allowed">
                <i class="fa-solid fa-spinner fa-spin text-sm" x-show="submitting" x-cloak></i>
                <span x-text="submitting ? 'Mendaftarkan Akun...' : 'Daftar'">Daftar</span>
            </button>
        </form>

        <!-- Footer: Login -->
        <div class="mt-3.5 pt-2.5 border-t border-slate-100 text-center space-y-1">
            <p class="text-xs text-slate-500">Sudah memiliki akun terdaftar?</p>
            <div>
                <a href="{{ route('login') }}" class="inline-block text-xs font-bold text-emerald-700 hover:text-emerald-800 transition-colors">
                    Masuk ke Halaman Login
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
