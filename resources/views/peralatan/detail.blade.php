<!DOCTYPE html>
<html lang="id" class="{{ request()->cookie('theme_preference') === 'dark' ? 'dark' : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Verifikasi Instrumen - {{ $equipment->name }} ({{ $equipment->code }})</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/favicon-192.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Theme Manager (Anti-FOUC) & Styles -->
    <script src="{{ asset('js/theme-switcher.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('css/theme-dark.css') }}">

    <!-- Tailwind CSS + Alpine.js -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"IBM Plex Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            navy: '#0e263e',
                            teal: '#168975',
                            cyan: '#4eb5d0',
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
    <style>
        html {
            font-size: 15px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        body { 
            font-family: 'IBM Plex Sans', sans-serif; 
            font-size: 0.875rem;
        }
        .guest-gradient-bg {
            background: linear-gradient(180deg, 
                #ffffff 0%, 
                #f8fbfb 14%, 
                #e0f7f4 30%, 
                #99f6e4 50%, 
                #38bdf8 70%, 
                #0f766e 86%, 
                #092635 100%
            );
            background-attachment: fixed;
        }
        @keyframes pageFadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .anim-fade-up {
            animation: pageFadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
        .card-hover {
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .card-hover:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 35px -10px rgba(14, 38, 62, 0.15);
        }
    </style>
</head>
<body class="guest-gradient-bg text-slate-800 min-h-screen flex flex-col justify-between p-4 sm:p-6 relative overflow-x-hidden">
    <!-- Ambient Luminous Background Elements -->
    <div class="fixed -bottom-24 -left-24 w-96 h-96 bg-emerald-400/25 rounded-full blur-3xl pointer-events-none"></div>
    <div class="fixed -bottom-24 -right-24 w-96 h-96 bg-cyan-400/30 rounded-full blur-3xl pointer-events-none"></div>
    <div class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-teal-100/25 rounded-full blur-3xl pointer-events-none"></div>

    <!-- Watermark Low-Opacity Logo Background -->
    <div class="fixed inset-0 pointer-events-none z-0 flex items-center justify-center overflow-hidden select-none" aria-hidden="true">
        <img src="{{ asset('images/logo_icon.png') }}" alt="" class="w-[480px] sm:w-[580px] max-w-[85vw] filter contrast-125 saturate-125 select-none pointer-events-none" style="opacity: 0.08;">
    </div>

    <!-- Main Container -->
    <div class="max-w-xl mx-auto w-full relative z-10 my-auto py-6 space-y-6 anim-fade-up">
        <!-- Header Navigasi & Logo -->
        <div class="flex items-center justify-between gap-3">
            <a href="{{ route('home') }}#peminjaman" onclick="if (history.length > 1) { history.back(); return false; }" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white/95 hover:bg-white text-slate-700 hover:text-emerald-700 font-bold text-xs border border-slate-200/90 shadow-2xs backdrop-blur-md transition-all cursor-pointer">
                <i class="fa-solid fa-arrow-left text-slate-400"></i> Kembali
            </a>
            <a href="/" class="inline-block hover:opacity-95 transition-opacity mx-auto">
                <!-- Logo Light Mode: teks gelap untuk latar terang -->
                <img src="{{ asset('images/logo_lab_lgn_rgst.png') }}" alt="Laboratorium Lingkungan Terpadu" class="h-12 sm:h-16 w-auto max-w-[140px] sm:max-w-[260px] object-contain filter drop-shadow-sm block dark:hidden">
                <!-- Logo Dark Mode: teks putih / terang untuk latar gelap -->
                <img src="{{ asset('images/logo_lab_navbar_clean.png') }}" alt="Laboratorium Lingkungan Terpadu" class="h-12 sm:h-16 w-auto max-w-[140px] sm:max-w-[260px] object-contain filter drop-shadow-sm hidden dark:block">
            </a>
            <div class="flex items-center">
                <x-theme-switcher align="right" />
            </div>
        </div>

        <!-- Detail Card -->
        <div class="bg-white/95 border border-slate-200/90 backdrop-blur-2xl rounded-3xl p-4 sm:p-8 shadow-2xl shadow-slate-900/10 space-y-6 card-hover relative overflow-hidden">
            <!-- Header Section -->
            <div class="border-b border-slate-100 pb-5">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-2.5">
                    <div class="flex items-center gap-2">
                        <span class="font-mono font-bold text-xs text-emerald-700 bg-emerald-50 border border-emerald-200/80 px-2.5 py-1 rounded-lg shadow-2xs">
                            {{ $equipment->code }}
                        </span>
                        <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-lg">
                            {{ $equipment->category }}
                        </span>
                    </div>

                    @php
                        $badgeClasses = match($equipment->status) {
                            'Tersedia' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'Dalam Kalibrasi' => 'bg-blue-50 text-blue-700 border-blue-200',
                            'Dipinjam' => 'bg-amber-50 text-amber-700 border-amber-200',
                            'Dalam Servis' => 'bg-rose-50 text-rose-700 border-rose-200',
                            default => 'bg-slate-100 text-slate-700 border-slate-200'
                        };
                    @endphp
                    <span class="px-3 py-1 rounded-full text-xs font-bold border inline-flex items-center gap-1.5 {{ $badgeClasses }} shadow-2xs">
                        <span class="w-1.5 h-1.5 rounded-full {{ $equipment->status === 'Tersedia' ? 'bg-emerald-500' : ($equipment->status === 'Dalam Kalibrasi' ? 'bg-blue-500' : 'bg-amber-500') }}"></span>
                        {{ $equipment->status }}
                    </span>
                </div>

                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight leading-snug">
                    {{ $equipment->name }}
                </h1>
                <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">
                    <i class="fa-solid fa-microchip text-slate-400"></i>
                    <span>Merk / Model: <strong class="text-slate-700 font-semibold">{{ $equipment->brand ?: 'Standar Laboratorium' }}</strong></span>
                </p>
            </div>

            <!-- Foto Instrumen Laboratorium -->
            <div class="w-full h-48 sm:h-56 bg-slate-50/90 border border-slate-100 rounded-2xl flex items-center justify-center overflow-hidden p-3.5 shadow-2xs">
                @if($equipment->photo_url)
                    <img src="{{ $equipment->photo_url }}" alt="{{ $equipment->name }}" class="max-h-full max-w-full object-contain mx-auto transition-transform duration-300 hover:scale-105" loading="lazy">
                @else
                    <div class="flex flex-col items-center justify-center text-slate-300 py-6 text-center">
                        <i class="fa-solid fa-microscope text-5xl mb-2 text-slate-300"></i>
                        <span class="text-xs font-semibold text-slate-400">Instrumen Laboratorium Terverifikasi</span>
                    </div>
                @endif
            </div>

            <!-- Key Information Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 text-xs">
                <!-- Lokasi -->
                <div class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-100 flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm flex-shrink-0">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>
                    <div>
                        <span class="text-slate-400 text-[11px] block">Lokasi Penyimpanan</span>
                        <span class="font-bold text-slate-800 text-xs">{{ $equipment->location }}</span>
                    </div>
                </div>

                <!-- Stok -->
                <div class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-100 flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm flex-shrink-0">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </div>
                    <div>
                        <span class="text-slate-400 text-[11px] block">Jumlah Stok Fisik</span>
                        <span class="font-bold text-slate-800 text-xs">{{ $equipment->quantity }} Unit</span>
                    </div>
                </div>

                <!-- Status Kalibrasi -->
                <div class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-100 flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm flex-shrink-0">
                        <i class="fa-solid fa-certificate"></i>
                    </div>
                    <div>
                        <span class="text-slate-400 text-[11px] block">Masa Kalibrasi</span>
                        @php
                            $lastCal = $equipment->calibrations->first();
                        @endphp
                        <span class="font-bold {{ $lastCal ? 'text-indigo-900' : 'text-slate-700' }} text-xs">
                            {{ $lastCal && $lastCal->next_calibration_date ? 'Valid s/d ' . $lastCal->next_calibration_date->format('d M Y') : 'Terkalibrasi Standar' }}
                        </span>
                    </div>
                </div>

                <!-- Tarif Sewa -->
                <div class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-100 flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm flex-shrink-0">
                        <i class="fa-solid fa-tag"></i>
                    </div>
                    <div>
                        <span class="text-slate-400 text-[11px] block">Tarif Sewa / Hari</span>
                        <span class="font-extrabold text-emerald-700 text-xs">Rp {{ number_format($equipment->rental_price_per_day, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Spesifikasi Teknis -->
            @if($equipment->specification)
                <div class="bg-slate-50/90 rounded-2xl p-4 border border-slate-200/80 space-y-1.5">
                    <div class="flex items-center gap-2 text-[11px] font-bold text-slate-700 uppercase tracking-wider">
                        <i class="fa-solid fa-sliders text-emerald-600"></i> Spesifikasi Teknis Instrumen
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">{{ $equipment->specification }}</p>
                </div>
            @endif

            <!-- Verification Footer Stamp -->
            <div class="flex items-center justify-between pt-4 border-t border-slate-100 text-[11px] text-slate-400">
                <span class="flex items-center gap-1.5 text-emerald-700 font-semibold">
                    <i class="fa-solid fa-shield-check text-emerald-600 text-sm"></i> Data Terverifikasi Resmi Sistem Lab
                </span>
                <span class="font-mono text-slate-400">Tahun: {{ $equipment->purchase_year ?: '2024' }}</span>
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
            <a href="{{ route('equipment.scan') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition-all shadow-md shadow-slate-900/20 cursor-pointer">
                <i class="fa-solid fa-qrcode text-emerald-400"></i> Scan Alat Lain
            </a>
            @auth
                @php
                    $dashRoute = match(auth()->user()->role) {
                        'admin' => route('admin.dashboard'),
                        'manager' => route('manager.dashboard'),
                        'staff' => route('staff.dashboard'),
                        default => route('customer.dashboard')
                    };
                @endphp
                <a href="{{ $dashRoute }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-white hover:bg-slate-100 text-slate-700 font-bold text-xs border border-slate-300 transition-all shadow-2xs cursor-pointer">
                    <i class="fa-solid fa-house text-slate-400"></i> Ke Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-white hover:bg-slate-100 text-slate-700 font-bold text-xs border border-slate-300 transition-all shadow-2xs cursor-pointer">
                    <i class="fa-solid fa-right-to-bracket text-slate-400"></i> Masuk ke Sistem
                </a>
            @endauth
        </div>
    </div>

    <!-- Page Footer -->
    <footer class="text-center text-xs text-slate-400 py-3 relative z-10">
        &copy; 2026 UPTD Laboratorium Lingkungan Terpadu. Verification Engine.
    </footer>
</body>
</html>
