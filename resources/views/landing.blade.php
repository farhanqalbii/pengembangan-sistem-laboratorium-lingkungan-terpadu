<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Laboratorium Lingkungan Terpadu 2026 - Layanan Pengujian & Peminjaman Alat Terakreditasi</title>
    <meta name="description" content="Sistem Informasi Manajemen Laboratorium (LIMS) dan Peminjaman Alat UPTD Laboratorium Lingkungan. Layanan pengujian air, udara, tanah terakreditasi ISO/IEC 17025.">
    <meta name="keywords" content="laboratorium lingkungan, uji kualitas air, uji emisi udara, peminjaman alat lab, COA, certificate of analysis, LIMS">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/favicon-192.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=IBM+Plex+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', '"IBM Plex Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        brand: {
                            navy: '#0e263e',
                            'navy-dark': '#081726',
                            'navy-light': '#184a75',
                            teal: '#168975',
                            'teal-hover': '#1ba089',
                            'teal-light': '#edfbf7',
                            cyan: '#4eb5d0',
                            'cyan-light': '#e8f7fa',
                        },
                        emerald: {
                            50: '#edfbf7',
                            100: '#d3f5eb',
                            200: '#abebdc',
                            300: '#75dcc6',
                            400: '#3ebca8',
                            500: '#1aa089',
                            600: '#168975',
                            700: '#126e5e',
                            800: '#10564b',
                            900: '#0d473e',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        
        .hero-pattern {
            background-color: #f8fafc;
            background-image: radial-gradient(rgba(22, 137, 117, 0.08) 1.5px, transparent 1.5px), radial-gradient(rgba(78, 181, 208, 0.08) 1.5px, #f8fafc 1.5px);
            background-size: 36px 36px;
            background-position: 0 0, 18px 18px;
        }

        .glass-header {
            background: rgba(255, 255, 255, 0.90);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.85);
        }

        .glass-card {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            box-shadow: 0 10px 25px -5px rgba(14, 38, 62, 0.08), 0 4px 6px -2px rgba(14, 38, 62, 0.03);
            position: relative;
            overflow: hidden;
        }

        .glass-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #168975, #4eb5d0);
            opacity: 0;
            transition: opacity 0.35s ease;
        }

        .glass-card:hover::before {
            opacity: 1;
        }

        .badge-glow {
            box-shadow: 0 0 20px rgba(22, 137, 117, 0.25);
        }

        .btn-glow {
            box-shadow: 0 10px 20px -5px rgba(22, 137, 117, 0.4);
        }
        .btn-glow:hover {
            box-shadow: 0 14px 26px -4px rgba(22, 137, 117, 0.5);
        }

        .card-lift {
            transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.35s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.35s ease;
        }
        .card-lift:hover {
            transform: translateY(-6px);
            border-color: #168975;
            box-shadow: 0 20px 35px -8px rgba(14, 38, 62, 0.14), 0 8px 16px -4px rgba(22, 137, 117, 0.10);
        }

        @keyframes pulseSlow {
            0%, 100% { transform: scale(1); opacity: 0.15; }
            50% { transform: scale(1.08); opacity: 0.25; }
        }
        .anim-pulse-slow {
            animation: pulseSlow 8s ease-in-out infinite;
        }

        /* Scroll Reveal Animations */
        .reveal-on-scroll {
            opacity: 0;
            transform: translateY(32px);
            transition: opacity 0.75s cubic-bezier(0.16, 1, 0.3, 1), transform 0.75s cubic-bezier(0.16, 1, 0.3, 1);
            will-change: opacity, transform;
        }

        .reveal-on-scroll.reveal-left {
            transform: translateX(-36px);
        }

        .reveal-on-scroll.reveal-right {
            transform: translateX(36px);
        }

        .reveal-on-scroll.reveal-scale {
            transform: scale(0.94) translateY(24px);
        }

        .reveal-on-scroll.reveal-active {
            opacity: 1;
            transform: translateY(0) translateX(0) scale(1);
        }

        .reveal-on-scroll.reveal-active.card-lift:hover {
            transform: translateY(-6px);
            border-color: #168975;
            box-shadow: 0 20px 35px -8px rgba(14, 38, 62, 0.14), 0 8px 16px -4px rgba(22, 137, 117, 0.10);
        }

        .delay-100 { transition-delay: 100ms; }
        .delay-150 { transition-delay: 150ms; }
        .delay-200 { transition-delay: 200ms; }
        .delay-250 { transition-delay: 250ms; }
        .delay-300 { transition-delay: 300ms; }
        .delay-400 { transition-delay: 400ms; }
        .delay-500 { transition-delay: 500ms; }

        @media (prefers-reduced-motion: reduce) {
            .reveal-on-scroll {
                opacity: 1 !important;
                transform: none !important;
                transition: none !important;
            }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased font-sans selection:bg-emerald-500 selection:text-white" x-data="{ mobileMenuOpen: false, trackCode: '', trackingLoading: false, trackResult: null, trackError: '' }">

    <!-- Top Announcement Bar -->
    <div class="bg-gradient-to-r from-brand-navy via-slate-900 to-brand-navy text-white text-xs py-2 px-4 border-b border-slate-800">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2 text-center sm:text-left">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping mr-1.5"></span> ISO/IEC 17025:2017
                </span>
                <span class="text-slate-300 text-[11px] sm:text-xs">
                    Sistem Informasi Manajemen Laboratorium (LIMS) & Peminjaman Alat UPTD Laboratorium Lingkungan Hidup
                </span>
            </div>
            <div class="flex items-center gap-4 text-[11px] text-slate-300">
                <span class="hidden md:inline"><i class="fa-solid fa-clock text-emerald-400 mr-1"></i> Jam Layanan: 08:00 - 15:30 WIB</span>
                <a href="#kontak" class="hover:text-emerald-300 transition-colors"><i class="fa-solid fa-location-dot text-cyan-400 mr-1"></i> Kontak & Lokasi</a>
            </div>
        </div>
    </div>

    <!-- Sticky Main Navigation -->
    <header class="sticky top-0 z-50 glass-header">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between gap-4 lg:gap-8 h-22 sm:h-24">
                
                <!-- Logo Brand -->
                <div class="flex items-center flex-shrink-0">
                    <a href="{{ route('home') }}" class="flex items-center group focus:outline-none py-1">
                        <img src="{{ asset('images/logo_lab_lgn_rgst_clean.png') }}" alt="UPTD Laboratorium Lingkungan" class="h-16 sm:h-[72px] w-auto max-w-[310px] object-contain transition-transform group-hover:scale-[1.02] filter drop-shadow-2xs">
                    </a>
                </div>

                <!-- Desktop Nav Menu -->
                <nav class="hidden lg:flex items-center justify-center gap-5 xl:gap-6 text-xs font-semibold text-brand-navy/85 flex-1 px-4">
                    <a href="#layanan" class="hover:text-emerald-700 transition-colors py-1">Layanan</a>
                    <a href="#parameter" class="hover:text-emerald-700 transition-colors py-1">Parameter & Baku Mutu</a>
                    <a href="#peminjaman" class="hover:text-emerald-700 transition-colors py-1">Peminjaman Alat</a>
                    <a href="#alur" class="hover:text-emerald-700 transition-colors py-1">Alur Uji</a>
                    <a href="#tracking" class="hover:text-emerald-700 transition-colors py-1">Cek Status</a>
                    <a href="#standar-mutu" class="hover:text-emerald-700 transition-colors py-1">Akreditasi</a>
                </nav>

                <!-- Action CTA Buttons -->
                <div class="hidden sm:flex items-center gap-3 flex-shrink-0">
                    @auth
                        @php
                            $dashRoute = match(auth()->user()->role) {
                                'admin' => route('admin.dashboard'),
                                'manager' => route('manager.dashboard'),
                                'staff' => route('staff.dashboard'),
                                default => route('customer.dashboard')
                            };
                            $roleLabel = match(auth()->user()->role) {
                                'admin' => 'Admin',
                                'manager' => 'Manajer Lab',
                                'staff' => 'Staf / Analis',
                                default => 'Customer'
                            };
                        @endphp
                        <a href="{{ $dashRoute }}" class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 rounded-xl shadow-md btn-glow transition-all">
                            <i class="fa-solid fa-gauge-high text-emerald-100"></i>
                            <span>Dashboard ({{ $roleLabel }})</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-3.5 py-2 text-xs font-bold text-brand-navy hover:text-emerald-700 transition-colors">
                            Masuk
                        </a>
                        <a href="{{ route('register') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 text-xs font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 rounded-xl shadow-md btn-glow transition-all">
                            <i class="fa-solid fa-user-plus text-emerald-100"></i>
                            <span>Daftar Akun</span>
                        </a>
                    @endauth
                </div>

                <!-- Mobile Hamburger Button -->
                <div class="lg:hidden flex items-center">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="p-2 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 focus:outline-none">
                        <i class="fa-solid" :class="mobileMenuOpen ? 'fa-xmark text-lg' : 'fa-bars text-lg'"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Drawer -->
        <div x-show="mobileMenuOpen" x-cloak class="lg:hidden border-t border-slate-200 bg-white/95 backdrop-blur-xl px-4 pt-3 pb-6 space-y-2.5">
            <a href="#layanan" @click="mobileMenuOpen = false" class="block px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 rounded-lg">Layanan</a>
            <a href="#parameter" @click="mobileMenuOpen = false" class="block px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 rounded-lg">Parameter & Baku Mutu</a>
            <a href="#peminjaman" @click="mobileMenuOpen = false" class="block px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 rounded-lg">Peminjaman Alat</a>
            <a href="#alur" @click="mobileMenuOpen = false" class="block px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 rounded-lg">Alur Uji</a>
            <a href="#tracking" @click="mobileMenuOpen = false" class="block px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 rounded-lg">Cek Status Sampel</a>
            <a href="#standar-mutu" @click="mobileMenuOpen = false" class="block px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 rounded-lg">Akreditasi</a>
            <a href="#kontak" @click="mobileMenuOpen = false" class="block px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 rounded-lg">Kontak & Lokasi</a>

            <div class="pt-3 border-t border-slate-100 flex flex-col gap-2">
                @auth
                    <a href="{{ $dashRoute }}" class="w-full py-2.5 text-center text-xs font-bold text-white bg-emerald-600 rounded-xl">Buka Dashboard ({{ $roleLabel }})</a>
                @else
                    <a href="{{ route('login') }}" class="w-full py-2 text-center text-xs font-bold text-slate-700 border border-slate-300 rounded-xl">Masuk Sistem</a>
                    <a href="{{ route('register') }}" class="w-full py-2 text-center text-xs font-bold text-white bg-emerald-600 rounded-xl">Daftar Akun Customer</a>
                @endauth
            </div>
        </div>
    </header>

    <!-- HERO SECTION -->
    <section class="relative bg-gradient-to-b from-white via-slate-50/70 to-white pt-6 pb-8 sm:pt-8 sm:pb-10 overflow-hidden">
        <!-- Ambient Glowing Orbs -->
        <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-[700px] h-[700px] bg-gradient-to-tr from-emerald-100/40 via-teal-50/30 to-cyan-100/40 rounded-full blur-3xl pointer-events-none anim-pulse-slow"></div>
        <div class="absolute top-1/3 -right-20 w-80 h-80 bg-cyan-100/30 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-4xl mx-auto space-y-3.5 sm:space-y-4 reveal-on-scroll">
                
                <!-- Pill Tag -->
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/95 border border-emerald-200/90 text-emerald-800 text-xs font-bold shadow-2xs backdrop-blur-md">
                    <span class="flex h-2 w-2 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span>Sistem Terintegrasi LIMS &bull; Akreditasi KAN ISO/IEC 17025</span>
                </div>

                <!-- Main Heading -->
                <h1 class="text-2xl sm:text-4xl lg:text-[38px] xl:text-[40px] font-black text-slate-900 tracking-tight leading-[1.2] max-w-3xl mx-auto">
                    Layanan Pengujian Lingkungan Terpercaya, Terakreditasi & <span class="bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 bg-clip-text text-transparent">Terintegrasi Digital</span>
                </h1>

                <!-- Subtitle -->
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed font-normal max-w-2xl mx-auto">
                    Platform resmi LIMS UPTD Laboratorium Lingkungan Hidup untuk pengujian baku mutu air, udara, dan tanah berstandar SNI, penerbitan Certificate of Analysis (COA) digital sah, serta peminjaman alat laboratorium riset.
                </p>

                <!-- Action Button Group -->
                <div class="flex flex-wrap items-center justify-center gap-2.5 sm:gap-3 pt-1">
                    @auth
                        <a href="{{ $dashRoute }}" class="px-6 py-2.5 sm:py-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold text-xs sm:text-sm rounded-xl shadow-lg shadow-emerald-700/20 transition-all flex items-center gap-2">
                            <i class="fa-solid fa-gauge-high"></i> Buka Dashboard ({{ $roleLabel }})
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="px-6 py-2.5 sm:py-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold text-xs sm:text-sm rounded-xl shadow-lg shadow-emerald-700/20 hover:shadow-emerald-700/30 transition-all flex items-center gap-2">
                            <i class="fa-solid fa-flask"></i> Ajukan Pengujian Sampel
                        </a>
                    @endauth

                    <a href="{{ route('equipment.scan') }}" class="px-6 py-2.5 sm:py-3 bg-slate-900 hover:bg-brand-navy text-white font-bold text-xs sm:text-sm rounded-xl shadow-md transition-all flex items-center gap-2">
                        <i class="fa-solid fa-qrcode text-emerald-400"></i> Scan QR Alat Lab
                    </a>
                </div>

                <!-- Trust Badges / Stats Strip -->
                <div class="pt-2 sm:pt-3 max-w-4xl mx-auto reveal-on-scroll delay-150">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-3 p-3.5 sm:p-5 rounded-3xl bg-white border border-slate-300 shadow-xl shadow-slate-900/8 text-center">
                        <div class="p-1.5 sm:p-2.5">
                            <span class="block text-xl sm:text-2xl lg:text-3xl font-black text-emerald-700">{{ $stats['total_parameters'] }}+</span>
                            <span class="text-[11px] sm:text-xs text-slate-500 font-semibold mt-0.5 block">Parameter Baku Mutu</span>
                        </div>
                        <div class="p-1.5 sm:p-2.5 border-l border-slate-200">
                            <span class="block text-xl sm:text-2xl lg:text-3xl font-black text-brand-navy">{{ $stats['total_equipments'] }}+</span>
                            <span class="text-[11px] sm:text-xs text-slate-500 font-semibold mt-0.5 block">Alat Terkalibrasi</span>
                        </div>
                        <div class="p-1.5 sm:p-2.5 border-l border-slate-200">
                            <span class="block text-xl sm:text-2xl lg:text-3xl font-black text-teal-600">{{ $stats['total_samples'] }}+</span>
                            <span class="text-[11px] sm:text-xs text-slate-500 font-semibold mt-0.5 block">Sampel Terverifikasi</span>
                        </div>
                        <div class="p-1.5 sm:p-2.5 border-l border-slate-200">
                            <span class="block text-xl sm:text-2xl lg:text-3xl font-black text-amber-500 flex items-center justify-center gap-1">
                                <i class="fa-solid fa-star text-amber-400 text-base sm:text-lg"></i> {{ $stats['avg_rating'] }}
                            </span>
                            <span class="text-[11px] sm:text-xs text-slate-500 font-semibold mt-0.5 block">Indeks Kepuasan (IKP)</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- LIVE TRACKING & QR LOOKUP SECTION -->
    <section id="tracking" class="py-12 bg-white border-y border-slate-200/80">
        <div class="max-w-5xl mx-auto px-4 sm:px-6">
            <div class="p-6 sm:p-8 rounded-3xl bg-gradient-to-br from-slate-900 via-brand-navy to-slate-900 text-white shadow-xl relative overflow-hidden reveal-on-scroll">
                <!-- Ambient blur -->
                <div class="absolute -right-20 -bottom-20 w-80 h-80 bg-emerald-500/20 rounded-full blur-3xl pointer-events-none"></div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center relative z-10">
                    <div class="lg:col-span-5 space-y-3 reveal-on-scroll reveal-left delay-100">
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-[11px] font-bold">
                            <i class="fa-solid fa-magnifying-glass"></i> Cek Status Terkini
                        </div>
                        <h2 class="text-xl sm:text-2xl font-black text-white">Lacak Pengujian Sampel Anda</h2>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Ketik kode sampel yang tertera pada bukti serah terima Anda, atau klik contoh: 
                            <button type="button" @click="trackCode = 'SMP-20260901-0001'" class="inline-flex items-center gap-1 font-mono text-[11px] text-emerald-300 hover:text-white underline underline-offset-4 decoration-emerald-500/70 hover:decoration-white bg-emerald-950/80 px-2.5 py-1 rounded-lg border border-emerald-500/40 cursor-pointer transition-all shadow-xs" title="Klik untuk mencoba contoh kode">
                                <i class="fa-solid fa-arrow-pointer text-[10px]"></i> SMP-20260901-0001
                            </button>
                            untuk simulasi status instan.
                        </p>
                    </div>

                    <div class="lg:col-span-7 space-y-4 reveal-on-scroll reveal-right delay-200">
                        <form @submit.prevent="
                            if (!trackCode.trim()) return;
                            trackingLoading = true;
                            trackError = '';
                            trackResult = null;
                            fetch('{{ route('sample.track') }}?code=' + encodeURIComponent(trackCode.trim()))
                                .then(res => res.json())
                                .then(data => {
                                     trackingLoading = false;
                                    if (data.success) {
                                        trackResult = data.data;
                                    } else {
                                        trackError = data.message || 'Data sampel tidak ditemukan di sistem.';
                                    }
                                })
                                .catch(() => {
                                    trackingLoading = false;
                                    trackError = 'Terjadi kesalahan sistem saat melacak sampel.';
                                })
                        " class="flex flex-col sm:flex-row gap-2">
                            <div class="relative flex-grow">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-barcode"></i>
                                </span>
                                <input 
                                    type="text" 
                                    x-model="trackCode" 
                                    @input="trackCode = trackCode.toUpperCase()"
                                    placeholder="Masukkan Kode Sampel (contoh: SMP-20260901-0001)" 
                                    class="w-full bg-slate-800/90 text-white placeholder-slate-400 border border-slate-700 rounded-xl pl-10 pr-4 py-3 text-xs font-mono uppercase tracking-wider focus:outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20"
                                    required
                                >
                            </div>
                            <button 
                                type="submit" 
                                :disabled="trackingLoading"
                                class="px-5 py-3 bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 text-white text-xs font-bold rounded-xl shadow-md transition-all flex items-center justify-center gap-2 flex-shrink-0 cursor-pointer"
                            >
                                <span x-show="!trackingLoading"><i class="fa-solid fa-search"></i> Cek Status</span>
                                <span x-show="trackingLoading" x-cloak><i class="fa-solid fa-spinner fa-spin"></i> Mencari...</span>
                            </button>
                        </form>

                        <!-- Error Message -->
                        <div x-show="trackError" x-cloak class="p-3 bg-rose-500/20 border border-rose-500/40 rounded-xl text-xs text-rose-200 flex items-center gap-2">
                            <i class="fa-solid fa-circle-exclamation text-rose-400"></i>
                            <span x-text="trackError"></span>
                        </div>

                        <!-- Result Card -->
                        <div x-show="trackResult" x-cloak class="p-4 bg-slate-800/90 border border-emerald-500/40 rounded-2xl space-y-3">
                            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-700 pb-2.5">
                                <div>
                                    <span class="text-[10px] text-slate-400 block">Kode Sampel Terdaftar</span>
                                    <span class="font-mono font-bold text-sm text-emerald-300" x-text="trackResult?.sample_code"></span>
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold border" 
                                    :class="{
                                        'bg-emerald-500/20 text-emerald-300 border-emerald-400/30': trackResult?.status === 'Selesai',
                                        'bg-blue-500/20 text-blue-300 border-blue-400/30': trackResult?.status === 'Diuji' || trackResult?.status === 'Menunggu Verifikasi',
                                        'bg-amber-500/20 text-amber-300 border-amber-400/30': trackResult?.status === 'Diterima' || trackResult?.status === 'Diajukan',
                                        'bg-rose-500/20 text-rose-300 border-rose-400/30': trackResult?.status === 'Revisi'
                                    }"
                                    x-text="'Status: ' + trackResult?.status">
                                </span>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 text-[11px]">
                                <div>
                                    <span class="text-slate-400 block">Jenis Sampel:</span>
                                    <span class="font-semibold text-slate-200" x-text="trackResult?.sample_type"></span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block">Lokasi Sampling:</span>
                                    <span class="font-semibold text-slate-200" x-text="trackResult?.sampling_location"></span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block">Total Parameter:</span>
                                    <span class="font-semibold text-slate-200" x-text="trackResult?.parameters_count + ' Parameter'"></span>
                                </div>
                            </div>

                            <div class="pt-2 border-t border-slate-700/80 flex items-center justify-between text-[11px]">
                                <span class="text-slate-300" x-show="trackResult?.has_certificate">
                                    <i class="fa-solid fa-file-circle-check text-emerald-400"></i> COA Terbit: <strong class="font-mono" x-text="trackResult?.certificate_number"></strong>
                                </span>
                                <span class="text-slate-400" x-show="!trackResult?.has_certificate">
                                    <i class="fa-solid fa-hourglass-half text-amber-400"></i> COA dalam proses verifikasi teknis
                                </span>
                                <a href="{{ route('login') }}" class="text-emerald-400 hover:text-emerald-300 font-bold underline">
                                    Masuk untuk Unduh COA &rarr;
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CORE SERVICES SECTION -->
    <section id="layanan" class="py-20 bg-slate-100/75 border-t border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto space-y-3 mb-14 reveal-on-scroll">
                <span class="text-emerald-700 text-xs font-extrabold uppercase tracking-wider bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
                    Bidang Layanan Laboratorium
                </span>
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                    Solusi Pengujian Komprehensif Berstandar Nasional (SNI)
                </h2>
                <p class="text-xs sm:text-sm text-slate-600">
                    Didukung peralatan instrumentasi mutakhir dan analis bersertifikasi untuk menjamin akurasi dan kepatuhan baku mutu lingkungan.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                
                <!-- Service 1: Air & Limbah -->
                <div class="p-6 sm:p-7 rounded-3xl glass-card card-lift space-y-4 reveal-on-scroll delay-100">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-700 border border-emerald-200/80 flex items-center justify-center text-xl shadow-2xs">
                        <i class="fa-solid fa-droplet"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">Pengujian Air & Air Limbah</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Analisis parameter fisika dan kimia: BOD5, COD, TSS, Minyak & Lemak, pH, Amonia, serta kandungan Logam Berat (Pb, Cd, Hg, Cu) sesuai Permen LHK.
                    </p>
                    <ul class="text-[11px] text-slate-500 space-y-1.5 pt-2 border-t border-slate-100">
                        <li class="flex items-center gap-1.5"><i class="fa-solid fa-check text-emerald-600"></i> Air Limbah Domestik & Industri</li>
                        <li class="flex items-center gap-1.5"><i class="fa-solid fa-check text-emerald-600"></i> Air Permukaan (Sungai & Danau)</li>
                        <li class="flex items-center gap-1.5"><i class="fa-solid fa-check text-emerald-600"></i> Air Bersih & Air Minum Higiene</li>
                    </ul>
                </div>

                <!-- Service 2: Udara & Emisi -->
                <div class="p-6 sm:p-7 rounded-3xl glass-card card-lift space-y-4 reveal-on-scroll delay-200">
                    <div class="w-12 h-12 rounded-2xl bg-cyan-50 text-cyan-600 border border-cyan-200/80 flex items-center justify-center text-xl shadow-2xs">
                        <i class="fa-solid fa-wind"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">Kualitas Udara & Emisi</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Pemantauan kualitas udara ambien 24 jam dan uji emisi sumber tidak bergerak (cerobong pabrik/genset): SO2, NO2, CO, TSP/PM2.5, serta tingkat kebisingan.
                    </p>
                    <ul class="text-[11px] text-slate-500 space-y-1.5 pt-2 border-t border-slate-100">
                        <li class="flex items-center gap-1.5"><i class="fa-solid fa-check text-cyan-600"></i> Udara Ambien Kawasan Industri</li>
                        <li class="flex items-center gap-1.5"><i class="fa-solid fa-check text-cyan-600"></i> Emisi Cerobong Industri & Boiler</li>
                        <li class="flex items-center gap-1.5"><i class="fa-solid fa-check text-cyan-600"></i> Uji Kebisingan & Getaran Lingkungan</li>
                    </ul>
                </div>

                <!-- Service 3: Tanah & Limbah B3 -->
                <div class="p-6 sm:p-7 rounded-3xl glass-card card-lift space-y-4 reveal-on-scroll delay-300">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 border border-amber-200/80 flex items-center justify-center text-xl shadow-2xs">
                        <i class="fa-solid fa-mountain"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">Tanah & Karakteristik Limbah</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Karakterisasi sedimen dan limbah padat: Uji TCLP (Toxicity Characteristic Leaching Procedure), Total Logam Berat, pH tanah, dan kesuburan tanah.
                    </p>
                    <ul class="text-[11px] text-slate-500 space-y-1.5 pt-2 border-t border-slate-100">
                        <li class="flex items-center gap-1.5"><i class="fa-solid fa-check text-amber-600"></i> Uji Toksisitas TCLP Limbah B3</li>
                        <li class="flex items-center gap-1.5"><i class="fa-solid fa-check text-amber-600"></i> Analisis Kesuburan & Sifat Tanah</li>
                        <li class="flex items-center gap-1.5"><i class="fa-solid fa-check text-amber-600"></i> Pemantauan Sedimen Muara & Pantai</li>
                    </ul>
                </div>

                <!-- Service 4: Peminjaman Alat -->
                <div class="p-6 sm:p-7 rounded-3xl glass-card card-lift space-y-4 reveal-on-scroll delay-400">
                    <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-700 border border-teal-200/80 flex items-center justify-center text-xl shadow-2xs">
                        <i class="fa-solid fa-microscope"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">Peminjaman Alat Laboratorium</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Fasilitas penyewaan instrumen ukur lapangan dan lab analitik untuk kegiatan riset universitas, instansi pemerintah, dan praktisi lingkungan.
                    </p>
                    <ul class="text-[11px] text-slate-500 space-y-1.5 pt-2 border-t border-slate-100">
                        <li class="flex items-center gap-1.5"><i class="fa-solid fa-check text-teal-600"></i> Seluruh Alat Terkalibrasi Sah</li>
                        <li class="flex items-center gap-1.5"><i class="fa-solid fa-check text-teal-600"></i> Label QR Code & Tracking Status</li>
                        <li class="flex items-center gap-1.5"><i class="fa-solid fa-check text-teal-600"></i> Tarif Resmi & Transparan</li>
                    </ul>
                </div>

            </div>
        </div>
    </section>

    <!-- PARAMETER & BAKU MUTU CATALOG SECTION -->
    <section id="parameter" class="py-20 bg-white border-t border-slate-200" x-data="{ searchParam: '', activeCategory: 'Semua' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10 reveal-on-scroll">
                <div class="space-y-2 max-w-xl">
                    <span class="text-emerald-700 text-xs font-extrabold uppercase tracking-wider bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
                        Standar & Baku Mutu
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                        Katalog Parameter Uji Terakreditasi
                    </h2>
                    <p class="text-xs text-slate-600">
                        Telusuri baku mutu lingkungan, metode uji SNI yang digunakan, serta rincian biaya uji resmi per parameter.
                    </p>
                </div>

                <!-- Instant Filter Input -->
                <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
                    <div class="relative w-full sm:w-64">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                            <i class="fa-solid fa-magnifying-glass text-xs"></i>
                        </span>
                        <input 
                            type="text" 
                            x-model="searchParam" 
                            placeholder="Cari parameter / SNI..." 
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-3 py-2 text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20"
                        >
                    </div>
                </div>
            </div>

            <!-- Table of Parameters -->
            <div class="overflow-x-auto rounded-3xl border border-slate-300 shadow-lg shadow-slate-900/5 bg-white reveal-on-scroll delay-150">
                <table class="w-full text-left text-xs text-slate-700">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase text-[11px] tracking-wider">
                        <tr>
                            <th class="px-5 py-3.5">Nama Parameter Uji</th>
                            <th class="px-5 py-3.5">Kategori</th>
                            <th class="px-5 py-3.5">Satuan</th>
                            <th class="px-5 py-3.5">Metode Pengujian (SNI)</th>
                            <th class="px-5 py-3.5">Baku Mutu Acuan</th>
                            <th class="px-5 py-3.5 text-right">Tarif Pengujian</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($parameters as $param)
                            <tr class="hover:bg-slate-50/80 transition-colors"
                                x-show="
                                    (searchParam === '' || '{{ strtolower($param->name) }} {{ strtolower($param->category) }} {{ strtolower($param->test_method) }}'.includes(searchParam.toLowerCase()))
                                "
                            >
                                <td class="px-5 py-4 font-bold text-slate-900">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        <span>{{ $param->name }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="px-2.5 py-1 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-600">
                                        {{ $param->category }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 font-mono text-slate-600">
                                    {{ $param->unit ?: '-' }}
                                </td>
                                <td class="px-5 py-4 font-mono text-xs text-slate-600">
                                    {{ $param->test_method ?: 'Metode Standar SNI' }}
                                </td>
                                <td class="px-5 py-4 text-xs">
                                    @if($param->qualityStandard)
                                        <span class="font-bold text-slate-800">
                                            @if($param->qualityStandard->nilai_minimum && $param->qualityStandard->nilai_maksimum)
                                                {{ $param->qualityStandard->nilai_minimum }} - {{ $param->qualityStandard->nilai_maksimum }} {{ $param->qualityStandard->unit }}
                                            @elseif($param->qualityStandard->nilai_maksimum)
                                                Maks. {{ $param->qualityStandard->nilai_maksimum }} {{ $param->qualityStandard->unit }}
                                            @elseif($param->qualityStandard->nilai_minimum)
                                                Min. {{ $param->qualityStandard->nilai_minimum }} {{ $param->qualityStandard->unit }}
                                            @else
                                                -
                                            @endif
                                        </span>
                                        <span class="text-[10px] text-slate-400 block truncate max-w-xs" title="{{ $param->qualityStandard->sumber_regulasi }}">
                                            {{ $param->qualityStandard->sumber_regulasi ?: 'Regulasi Nasional' }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 italic">Standar Acuan</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right font-mono font-bold text-emerald-700">
                                    Rp {{ number_format($param->price, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-slate-400">
                                    Belum ada data parameter terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Bottom Note -->
            <div class="mt-4 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-2">
                <span>* Standar baku mutu mengacu pada regulasi Kementerian Lingkungan Hidup dan Kehutanan (KLHK) Republik Indonesia.</span>
                <a href="{{ route('register') }}" class="text-emerald-700 font-bold hover:underline">
                    Daftar Sekarang untuk Ajukan Sampel &rarr;
                </a>
            </div>
        </div>
    </section>

    <!-- EQUIPMENT RENTAL CATALOG SECTION -->
    <section id="peminjaman" class="py-20 bg-slate-100/75 border-t border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12 reveal-on-scroll">
                <div class="space-y-2 max-w-2xl">
                    <span class="text-emerald-700 text-xs font-extrabold uppercase tracking-wider bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
                        Inventori & Instrumen Lab
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                        Alat Laboratorium Siap Dipinjam
                    </h2>
                    <p class="text-xs text-slate-600">
                        Seluruh instrumen dirawat berkala dan terkalibrasi oleh laboratorium rujukan. Dilengkapi QR & Barcode digital untuk verifikasi spesifikasi di lapangan.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('equipment.scan') }}" class="px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 rounded-xl text-xs font-bold shadow-2xs flex items-center gap-2">
                        <i class="fa-solid fa-qrcode text-emerald-600"></i> Buka Scanner QR
                    </a>
                </div>
            </div>

            <!-- Equipment Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($equipments as $index => $eq)
                    <div class="rounded-3xl glass-card card-lift p-6 sm:p-7 flex flex-col justify-between space-y-4 reveal-on-scroll delay-{{ (($index % 3) + 1) * 100 }}">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-mono text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200/80 px-2.5 py-1 rounded-lg">
                                    {{ $eq->code }}
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100/70 text-emerald-800 border border-emerald-200 flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> {{ $eq->status }}
                                </span>
                            </div>

                            <h3 class="text-base font-bold text-slate-900 leading-snug">
                                {{ $eq->name }}
                            </h3>

                            <p class="text-xs text-slate-500 flex items-center gap-1.5">
                                <i class="fa-solid fa-tag text-slate-400"></i>
                                <span>Merk / Brand: <strong class="text-slate-700">{{ $eq->brand ?: 'Standar Industri' }}</strong></span>
                            </p>

                            <p class="text-xs text-slate-600 line-clamp-2">
                                {{ $eq->specification ?: 'Instrumen pengujian presisi tinggi untuk analisis lingkungan terpadu.' }}
                            </p>

                            <div class="p-3.5 bg-slate-50/90 rounded-2xl border border-slate-200 space-y-1.5 text-xs">
                                <div class="flex justify-between">
                                    <span class="text-slate-500 text-[11px] font-medium">Tarif Sewa:</span>
                                    <span class="font-bold text-slate-900 font-mono">Rp {{ number_format($eq->rental_price_per_day, 0, ',', '.') }} / hari</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500 text-[11px] font-medium">Deposit Jaminan:</span>
                                    <span class="font-medium text-slate-700 font-mono">Rp {{ number_format($eq->deposit_amount, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500 text-[11px] font-medium">Lokasi Lab:</span>
                                    <span class="font-semibold text-slate-800">{{ $eq->location }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                            <a href="{{ route('equipment.detail', $eq->code) }}" class="text-xs text-emerald-700 hover:text-emerald-800 font-bold flex items-center gap-1">
                                <i class="fa-solid fa-circle-info"></i> Detail & QR
                            </a>
                            <a href="{{ route('customer.borrowings.create') }}" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-sm transition-all flex items-center gap-1.5">
                                <i class="fa-solid fa-calendar-plus"></i> Pinjam Alat
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 text-center py-12 text-slate-400">
                        Tidak ada alat yang siap dipinjam saat ini.
                    </div>
                @endforelse
            </div>

            <!-- View More Banner -->
            <div class="mt-10 p-6 sm:p-7 rounded-3xl bg-emerald-900 text-white flex flex-col sm:flex-row items-center justify-between gap-4 border border-emerald-800/80 shadow-xl shadow-emerald-950/20 reveal-on-scroll delay-150">
                <div class="space-y-1 text-center sm:text-left">
                    <h4 class="text-sm sm:text-base font-bold text-white">Membutuhkan Peminjaman Alat untuk Proyek / Riset?</h4>
                    <p class="text-xs text-emerald-200">Daftarkan akun institusi Anda untuk proses peminjaman resmi, persetujuan cepat, dan invoice otomatis.</p>
                </div>
                <a href="{{ route('register') }}" class="px-5 py-2.5 bg-emerald-400 hover:bg-emerald-300 text-slate-900 font-bold text-xs rounded-xl shadow transition-all flex-shrink-0">
                    Ajukan Peminjaman Sekarang
                </a>
            </div>
        </div>
    </section>

    <!-- 4-STEP TRANSPARENT WORKFLOW SECTION -->
    <section id="alur" class="py-20 bg-slate-50 border-t border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto space-y-3 mb-16 reveal-on-scroll">
                <span class="text-emerald-700 text-xs font-extrabold uppercase tracking-wider bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
                    Alur Layanan Digital
                </span>
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                    4 Langkah Mudah Pengujian Sampel
                </h2>
                <p class="text-xs sm:text-sm text-slate-600">
                    Proses transparan terintegrasi dari pendaftaran mandiri hingga penerbitan sertifikat hasil uji sah.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 relative">
                <!-- Connecting Line (Desktop) -->
                <div class="hidden md:block absolute top-1/2 left-0 w-full h-0.5 bg-gradient-to-r from-emerald-500 via-teal-500 to-cyan-500 -translate-y-8 z-0"></div>

                <!-- Step 1 -->
                <div class="relative z-10 bg-white p-6 sm:p-7 rounded-3xl border border-slate-300 shadow-md shadow-slate-900/5 card-lift text-center space-y-3.5 reveal-on-scroll delay-100">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-600 text-white font-black text-lg mx-auto flex items-center justify-center shadow-lg shadow-emerald-600/30">
                        1
                    </div>
                    <h3 class="text-sm font-bold text-slate-900">Registrasi Sampel Online</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Customer mengajukan sampel melalui sistem, memilih parameter uji, dan menerima tanda terima sampel dengan kode unik.
                    </p>
                </div>

                <!-- Step 2 -->
                <div class="relative z-10 bg-white p-6 sm:p-7 rounded-3xl border border-slate-300 shadow-md shadow-slate-900/5 card-lift text-center space-y-3.5 reveal-on-scroll delay-200">
                    <div class="w-14 h-14 rounded-2xl bg-teal-600 text-white font-black text-lg mx-auto flex items-center justify-center shadow-lg shadow-teal-600/30">
                        2
                    </div>
                    <h3 class="text-sm font-bold text-slate-900">Penerimaan & Uji Analis</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Staf memverifikasi kondisi fisik sampel, menugaskan analis kompeten, serta menguji parameter dengan alat terkalibrasi.
                    </p>
                </div>

                <!-- Step 3 -->
                <div class="relative z-10 bg-white p-6 sm:p-7 rounded-3xl border border-slate-300 shadow-md shadow-slate-900/5 card-lift text-center space-y-3.5 reveal-on-scroll delay-300">
                    <div class="w-14 h-14 rounded-2xl bg-teal-600 text-white font-black text-lg mx-auto flex items-center justify-center shadow-lg shadow-teal-600/30">
                        3
                    </div>
                    <h3 class="text-sm font-bold text-slate-900">Verifikasi Manajer Lab</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Manajer Lab memeriksa kesesuaian data analitis terhadap batas baku mutu lingkungan sebelum persetujuan resmi.
                    </p>
                </div>

                <!-- Step 4 -->
                <div class="relative z-10 bg-white p-6 sm:p-7 rounded-3xl border border-slate-300 shadow-md shadow-slate-900/5 card-lift text-center space-y-3.5 reveal-on-scroll delay-400">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-500 text-white font-black text-lg mx-auto flex items-center justify-center shadow-lg shadow-emerald-500/30">
                        4
                    </div>
                    <h3 class="text-sm font-bold text-slate-900">Penerbitan COA Digital</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Sertifikat Hasil Uji resmi (COA) diterbitkan secara otomatis dengan nomor sah dan QR Code yang dapat langsung diunduh PDF.
                    </p>
                </div>

            </div>
        </div>
    </section>

    <!-- ACCREDITATION & QUALITY ASSURANCE SECTION -->
    <section id="standar-mutu" class="py-20 bg-slate-900 text-white relative overflow-hidden">
        <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#168975_1px,transparent_1px)] [background-size:24px_24px]"></div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                
                <div class="lg:col-span-6 space-y-6 reveal-on-scroll reveal-left">
                    <span class="text-emerald-400 text-xs font-extrabold uppercase tracking-wider bg-emerald-950/80 px-3 py-1 rounded-full border border-emerald-500/30">
                        Jaminan Mutu & Integritas Data
                    </span>
                    <h2 class="text-2xl sm:text-4xl font-black text-white tracking-tight leading-snug">
                        Terakreditasi KAN Sesuai Standar Internasional ISO/IEC 17025
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                        Sebagai laboratorium lingkungan pemerintah daerah, kami menerapkan sistem manajemen mutu terpadu untuk memastikan setiap hasil pengujian memiliki ketertelusuran yang sah dan dapat dipertanggungjawabkan secara hukum.
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                        <div class="p-4 rounded-2xl bg-slate-800/80 border border-slate-700/80 space-y-2">
                            <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-sm">
                                <i class="fa-solid fa-certificate"></i>
                            </div>
                            <h4 class="text-xs font-bold text-white">ISO/IEC 17025:2017</h4>
                            <p class="text-[11px] text-slate-400">Kompetensi teknis laboratorium pengujian dan kalibrasi terakreditasi KAN.</p>
                        </div>

                        <div class="p-4 rounded-2xl bg-slate-800/80 border border-slate-700/80 space-y-2">
                            <div class="w-8 h-8 rounded-xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center text-sm">
                                <i class="fa-solid fa-shield-halved"></i>
                            </div>
                            <h4 class="text-xs font-bold text-white">ISO 27001 & ISO 9001</h4>
                            <p class="text-[11px] text-slate-400">Keamanan data hasil uji serta konsistensi mutu manajemen pelayanan publik.</p>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-6 reveal-on-scroll reveal-right delay-150">
                    <div class="p-8 rounded-3xl bg-slate-800/90 border border-slate-700/90 space-y-6 shadow-2xl">
                        <h3 class="text-base font-bold text-white flex items-center gap-2 border-b border-slate-700 pb-3">
                            <i class="fa-solid fa-handshake-angle text-emerald-400"></i> Komitmen Pelayanan Prima
                        </h3>

                        <div class="space-y-4 text-xs">
                            <div class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center flex-shrink-0 text-[11px] mt-0.5">
                                    <i class="fa-solid fa-check"></i>
                                </div>
                                <div>
                                    <strong class="text-white block font-semibold">Kecepatan & Ketepatan Penerbitan COA</strong>
                                    <span class="text-slate-400">Rata-rata waktu penyelesaian uji sesuai batas waktu baku mutu resmi.</span>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center flex-shrink-0 text-[11px] mt-0.5">
                                    <i class="fa-solid fa-check"></i>
                                </div>
                                <div>
                                    <strong class="text-white block font-semibold">Validasi Digital Anti Pemalsuan</strong>
                                    <span class="text-slate-400">Sertifikat hasil uji dilengkapi QR Code dinamis untuk verifikasi keaslian dokumen secara online kapan pun.</span>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center flex-shrink-0 text-[11px] mt-0.5">
                                    <i class="fa-solid fa-check"></i>
                                </div>
                                <div>
                                    <strong class="text-white block font-semibold">Penyimpanan Arsip Digital Terenkripsi</strong>
                                    <span class="text-slate-400">Riwayat pengujian pelanggan tersimpan aman di cloud server dengan pencatatan audit log aktivitas.</span>
                                </div>
                            </div>
                        </div>

                        <div class="pt-2">
                            <a href="{{ route('register') }}" class="w-full py-3 bg-emerald-500 hover:bg-emerald-400 text-slate-900 font-extrabold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-2">
                                Daftar dan Mulai Pengujian Sekarang &rarr;
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- TESTIMONIALS SECTION -->
    <section class="py-20 bg-slate-100/75 border-t border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto space-y-3 mb-14 reveal-on-scroll">
                <span class="text-emerald-700 text-xs font-extrabold uppercase tracking-wider bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
                    Ulasan & Evaluasi Layanan
                </span>
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                    Kepuasan Pengguna Laboratorium
                </h2>
                <p class="text-xs sm:text-sm text-slate-600">
                    Transparansi evaluasi kualitas layanan dari pelaku industri, akademisi, dan instansi mitra kerja.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @forelse($testimonials as $index => $fb)
                    <div class="p-6 sm:p-7 rounded-3xl glass-card card-lift space-y-4 flex flex-col justify-between reveal-on-scroll delay-{{ (($index % 3) + 1) * 100 }}">
                        <div class="space-y-3">
                            <div class="flex items-center gap-1 text-amber-400 text-xs">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fa-solid fa-star {{ $i <= $fb->rating ? 'text-amber-400' : 'text-slate-200' }}"></i>
                                @endfor
                                <span class="font-bold text-slate-800 ml-1">({{ $fb->rating }}.0)</span>
                            </div>

                            <p class="text-xs text-slate-600 italic leading-relaxed">
                                "{{ $fb->saran_masukan ?: 'Layanan pengujian sangat memuaskan, proses cepat dan sertifikat COA sah mudah diakses.' }}"
                            </p>
                        </div>

                        <div class="pt-3 border-t border-slate-100 flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-xs">
                                {{ strtoupper(substr($fb->customer->name ?? 'C', 0, 2)) }}
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-slate-900">{{ $fb->customer->name ?? 'Pelanggan Terverifikasi' }}</span>
                                <span class="text-[11px] text-slate-400 block">{{ $fb->customer->company_name ?? 'Mitra Pengujian Lingkungan' }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 text-center py-8 text-slate-400">
                        Belum ada ulasan yang ditampilkan.
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- CONTACT & LOCATION FOOTER SECTION -->
    <section id="kontak" class="py-16 bg-brand-navy text-white border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-10">
                
                <!-- Col 1: About UPTD -->
                <div class="md:col-span-5 space-y-4 reveal-on-scroll delay-100">
                    <a href="{{ route('home') }}" class="inline-block group focus:outline-none">
                        <img src="{{ asset('images/logo_lab_navbar_clean.png') }}" alt="UPTD Laboratorium Lingkungan" class="h-12 sm:h-14 w-auto max-w-[280px] sm:max-w-[320px] object-contain transition-transform group-hover:scale-[1.02]">
                    </a>
                    <p class="text-xs text-slate-300 leading-relaxed max-w-sm">
                        UPTD Laboratorium Lingkungan Hidup adalah unit pelaksana teknis pengujian kualitas lingkungan bersertifikat KAN ISO/IEC 17025. Melayani masyarakat, pelaku usaha, dan lembaga penelitian dengan integritas, akurasi, dan kecepatan data.
                    </p>
                    <div class="flex items-center gap-3 pt-2 text-slate-300 text-sm">
                        <a href="https://wa.me/6285742153548?text=Halo%20Admin%20Laboratorium%20Lingkungan,%20saya%20ingin%20konsultasi%20layanan" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-slate-800/90 hover:bg-emerald-600 text-slate-300 hover:text-white transition-all shadow-sm" title="WhatsApp Konsultasi: 0857-4215-3548">
                            <i class="fa-brands fa-whatsapp text-sm"></i>
                        </a>
                        <a href="mailto:layanan@lab-lingkungan.go.id" class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-slate-800/90 hover:bg-emerald-600 text-slate-300 hover:text-white transition-all shadow-sm" title="Email Resmi">
                            <i class="fa-solid fa-envelope text-sm"></i>
                        </a>
                        <a href="tel:0218765432" class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-slate-800/90 hover:bg-emerald-600 text-slate-300 hover:text-white transition-all shadow-sm" title="Telepon Kantor">
                            <i class="fa-solid fa-phone text-sm"></i>
                        </a>
                    </div>
                </div>

                <!-- Col 2: Navigasi Cepat -->
                <div class="md:col-span-3 space-y-3.5 reveal-on-scroll delay-200">
                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-emerald-400 flex items-center gap-2">
                        <i class="fa-solid fa-compass text-emerald-400"></i> Navigasi Utama
                    </h4>
                    <ul class="text-xs text-slate-300 space-y-2.5">
                        <li><a href="#layanan" class="hover:text-emerald-300 transition-colors flex items-center gap-1.5"><i class="fa-solid fa-angle-right text-[10px] text-emerald-500"></i> Layanan Pengujian</a></li>
                        <li><a href="#parameter" class="hover:text-emerald-300 transition-colors flex items-center gap-1.5"><i class="fa-solid fa-angle-right text-[10px] text-emerald-500"></i> Katalog Parameter SNI</a></li>
                        <li><a href="#peminjaman" class="hover:text-emerald-300 transition-colors flex items-center gap-1.5"><i class="fa-solid fa-angle-right text-[10px] text-emerald-500"></i> Peminjaman Alat Lab</a></li>
                        <li><a href="#alur" class="hover:text-emerald-300 transition-colors flex items-center gap-1.5"><i class="fa-solid fa-angle-right text-[10px] text-emerald-500"></i> Alur 4 Langkah Uji</a></li>
                        <li><a href="#tracking" class="hover:text-emerald-300 transition-colors flex items-center gap-1.5"><i class="fa-solid fa-angle-right text-[10px] text-emerald-500"></i> Cek Status Sampel</a></li>
                        <li><a href="{{ route('equipment.scan') }}" class="hover:text-emerald-300 transition-colors flex items-center gap-1.5"><i class="fa-solid fa-qrcode text-[10px] text-emerald-400"></i> Scanner QR Instrumen</a></li>
                    </ul>
                </div>

                <!-- Col 3: Informasi Kontak -->
                <div class="md:col-span-4 space-y-3.5 reveal-on-scroll delay-300">
                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-emerald-400 flex items-center gap-2">
                        <i class="fa-solid fa-building-circle-check text-emerald-400"></i> Alamat & Kontak Layanan
                    </h4>
                    <div class="text-xs text-slate-300 space-y-3">
                        <p class="flex items-start gap-2.5">
                            <i class="fa-solid fa-location-dot text-cyan-400 mt-1 flex-shrink-0"></i>
                            <span>Kantor Pojok, Jl. Kusbini No. H55, Klitren, Kec. Gondokusuman, Kota Yogyakarta, Daerah Istimewa Yogyakarta 55222.</span>
                        </p>
                        <p class="flex items-center gap-2.5">
                            <i class="fa-solid fa-clock text-cyan-400 flex-shrink-0"></i>
                            <span>Senin - Jumat: 08:00 - 15:30 WIB (Penerimaan Sampel)</span>
                        </p>
                        <p class="flex items-center gap-2.5">
                            <i class="fa-solid fa-envelope text-cyan-400 flex-shrink-0"></i>
                            <span>layanan@lab-lingkungan.go.id</span>
                        </p>
                        <p class="flex items-center gap-2.5">
                            <i class="fa-solid fa-phone text-cyan-400 flex-shrink-0"></i>
                            <span>(021) 876-5432 / <a href="https://wa.me/6285742153548" target="_blank" rel="noopener noreferrer" class="hover:text-emerald-400 transition-colors underline decoration-dotted">+62 857-4215-3548 (WA)</a></span>
                        </p>
                    </div>
                </div>

            </div>

            <!-- Bottom Copyright Strip -->
            <div class="pt-8 mt-12 border-t border-slate-800/80 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400 text-center">
                <p>&copy; 2026 PT. Razen Smart Service &bull; UPTD Laboratorium Lingkungan Terpadu. All rights reserved.</p>
                <div class="flex items-center gap-3 text-[11px]">
                    <a href="{{ route('login') }}" class="hover:text-emerald-300 transition-colors">Masuk Portal</a>
                    <span class="text-slate-600">&bull;</span>
                    <a href="{{ route('register') }}" class="hover:text-emerald-300 transition-colors">Registrasi Customer</a>
                    <span class="text-slate-600">&bull;</span>
                    <a href="{{ route('equipment.scan') }}" class="hover:text-emerald-300 transition-colors">Scanner QR Instrumen</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Floating WhatsApp Interactive Chat Widget -->
    <div x-data="{
        isOpen: false,
        message: '',
        defaultMessage: 'Halo Admin Laboratorium Lingkungan, saya ingin konsultasi terkait layanan pengujian dan peminjaman alat lab.',
        currentTime: '',
        init() {
            const now = new Date();
            this.currentTime = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
        },
        sendMessage() {
            const textToSend = this.message.trim() ? this.message.trim() : this.defaultMessage;
            const targetUrl = 'https://wa.me/6285742153548?text=' + encodeURIComponent(textToSend);
            window.open(targetUrl, '_blank', 'noopener,noreferrer');
        },
        setQuickMessage(text) {
            this.message = text;
            this.$nextTick(() => {
                if (this.$refs.chatInput) this.$refs.chatInput.focus();
            });
        }
    }" class="fixed bottom-6 right-6 z-50 flex flex-col items-end">

        <!-- Chat Popup Card -->
        <div x-show="isOpen" 
             x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 scale-95"
             class="mb-3 w-[92vw] sm:w-[360px] bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col">
            
            <!-- Header -->
            <div class="bg-gradient-to-r from-emerald-700 to-teal-800 text-white p-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center text-white border border-white/20 text-lg">
                            <i class="fa-solid fa-headset"></i>
                        </div>
                        <span class="absolute bottom-0 right-0 w-3 h-3 bg-emerald-400 border-2 border-emerald-800 rounded-full"></span>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm leading-tight">Admin Layanan Lab</h4>
                        <p class="text-[11px] text-emerald-200 flex items-center gap-1.5 mt-0.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            Online &bull; Siap Membantu
                        </p>
                    </div>
                </div>
                <button @click="isOpen = false" 
                        class="text-white/80 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-colors focus:outline-none"
                        aria-label="Tutup Percakapan">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Chat Body -->
            <div class="p-4 bg-slate-50/80 max-h-[300px] overflow-y-auto space-y-3 text-xs">
                <!-- Timestamp Indicator -->
                <div class="text-center my-1">
                    <span class="bg-slate-200/80 text-slate-600 text-[10px] px-2.5 py-0.5 rounded-full font-medium">Hari ini</span>
                </div>

                <!-- Admin Bubble -->
                <div class="flex items-start gap-2 max-w-[88%]">
                    <div class="w-7 h-7 rounded-full bg-emerald-600 text-white flex items-center justify-center text-[11px] flex-shrink-0 mt-0.5 shadow-sm">
                        <i class="fa-solid fa-flask"></i>
                    </div>
                    <div class="bg-white p-3 rounded-2xl rounded-tl-none shadow-sm border border-slate-100 text-slate-700 leading-relaxed space-y-1">
                        <p class="font-bold text-emerald-800 text-[11px]">UPTD Laboratorium Lingkungan</p>
                        <p>Halo! 👋 Ada yang bisa kami bantu seputar uji sampel baku mutu air, udara, tanah atau peminjaman alat lab?</p>
                        <p class="text-[9px] text-slate-400 text-right mt-1" x-text="currentTime"></p>
                    </div>
                </div>

                <!-- Quick Prompt Chips -->
                <div class="pt-2">
                    <p class="text-[10px] text-slate-500 font-medium mb-1.5 flex items-center gap-1">
                        <i class="fa-solid fa-bolt text-amber-500 text-[10px]"></i> Pertanyaan Cepat:
                    </p>
                    <div class="flex flex-wrap gap-1.5">
                        <button type="button" 
                                @click="setQuickMessage('Halo Admin, saya ingin konsultasi mengenai biaya & prosedur pengujian sampel air/udara.')"
                                class="text-[11px] bg-white hover:bg-emerald-50 text-slate-700 hover:text-emerald-700 border border-slate-200 hover:border-emerald-300 px-2.5 py-1 rounded-full transition-all text-left shadow-xs">
                            🧪 Tanya Uji Sampel
                        </button>
                        <button type="button" 
                                @click="setQuickMessage('Halo Admin, saya ingin menanyakan ketersediaan dan syarat peminjaman alat laboratorium.')"
                                class="text-[11px] bg-white hover:bg-emerald-50 text-slate-700 hover:text-emerald-700 border border-slate-200 hover:border-emerald-300 px-2.5 py-1 rounded-full transition-all text-left shadow-xs">
                            🔬 Peminjaman Alat
                        </button>
                        <button type="button" 
                                @click="setQuickMessage('Halo Admin, saya ingin cek status progres sampel yang sedang diuji.')"
                                class="text-[11px] bg-white hover:bg-emerald-50 text-slate-700 hover:text-emerald-700 border border-slate-200 hover:border-emerald-300 px-2.5 py-1 rounded-full transition-all text-left shadow-xs">
                            📋 Cek Status Sampel
                        </button>
                    </div>
                </div>
            </div>

            <!-- Input & Send Area -->
            <div class="p-3 bg-white border-t border-slate-200">
                <form @submit.prevent="sendMessage()" class="flex items-center gap-2">
                    <input type="text" 
                           x-ref="chatInput"
                           x-model="message"
                           @keydown.enter.prevent="sendMessage()"
                           placeholder="Ketik pesan konsultasi Anda..." 
                           class="flex-1 text-xs px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 text-slate-800 placeholder-slate-400 transition-all">
                    <button type="submit" 
                            class="w-10 h-10 rounded-xl bg-emerald-600 hover:bg-emerald-500 active:scale-95 text-white flex items-center justify-center transition-all shadow-md shadow-emerald-600/30 group focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
                            title="Kirim ke WhatsApp (0857-4215-3548)">
                        <i class="fa-solid fa-paper-plane text-sm group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-transform"></i>
                    </button>
                </form>
                <div class="flex items-center justify-between text-[10px] text-slate-400 mt-2 px-1">
                    <span>Langsung terhubung ke WA resmi</span>
                    <span class="font-mono text-emerald-600 font-semibold">+62 857-4215-3548</span>
                </div>
            </div>
        </div>

        <!-- Floating Trigger Button -->
        <button type="button" 
                @click="isOpen = !isOpen"
                class="flex items-center gap-2.5 bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-3 rounded-full shadow-lg shadow-emerald-900/30 hover:shadow-xl hover:shadow-emerald-600/40 hover:-translate-y-0.5 transition-all duration-300 group focus:outline-none focus:ring-4 focus:ring-emerald-400/50"
                aria-label="Buka Chat WhatsApp Layanan 0857-4215-3548">
            <span class="relative flex h-3 w-3" x-show="!isOpen">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-white"></span>
            </span>
            <i class="fa-brands fa-whatsapp text-xl" x-show="!isOpen"></i>
            <i class="fa-solid fa-xmark text-lg" x-show="isOpen" x-cloak></i>
            <span class="text-xs font-bold hidden sm:inline-block tracking-wide" x-text="isOpen ? 'Tutup Chat' : 'Konsultasi WA'"></span>
        </button>

    </div>

    <!-- Scroll Reveal Observer Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const revealElements = document.querySelectorAll('.reveal-on-scroll');
            if (!revealElements.length) return;

            if (!('IntersectionObserver' in window)) {
                revealElements.forEach(el => el.classList.add('reveal-active'));
                return;
            }

            const revealObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('reveal-active');
                    } else {
                        // Jika elemen berada di bawah batas pandang layar (misal di-scroll kembali ke atas),
                        // reset status agar saat di-scroll ke bawah kembali muncul efek animasi mulus
                        if (entry.boundingClientRect.top > 0) {
                            entry.target.classList.remove('reveal-active');
                        }
                    }
                });
            }, {
                root: null,
                rootMargin: '0px 0px -40px 0px',
                threshold: 0.12
            });

            revealElements.forEach(el => revealObserver.observe(el));
        });
    </script>
</body>
</html>
