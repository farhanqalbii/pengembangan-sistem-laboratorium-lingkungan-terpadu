<!DOCTYPE html>
<html lang="id" class="{{ request()->cookie('theme_preference') === 'dark' ? 'dark' : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Login - Sistem Laboratorium Lingkungan Terpadu 2026')</title>
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

    <!-- Tailwind CSS CDN + Alpine.js -->
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
                            600: '#168975', // Exact logo leaf teal
                            700: '#126e5e',
                            800: '#10564b',
                            900: '#0d473e',
                            950: '#062924',
                        },
                        teal: {
                            50: '#eefcfb',
                            100: '#d7f7f5',
                            200: '#b2efec',
                            300: '#7ce1dc',
                            400: '#4eb5d0', // Exact logo water drop cyan
                            500: '#2b9cb8',
                            600: '#168975',
                            700: '#126e5e',
                            800: '#10564b',
                            900: '#0e263e',
                            950: '#081726',
                        },
                        cyan: {
                            50: '#f0f9fb',
                            100: '#dcf1f6',
                            200: '#bee3ed',
                            300: '#8ecede',
                            400: '#56bdd0',
                            500: '#4eb5d0', // Logo water drop
                            600: '#2898b3',
                            700: '#237a90',
                            800: '#226476',
                            900: '#205363',
                        },
                        slate: {
                            50: '#f8fafc',
                            100: '#f1f5f9',
                            200: '#e2e8f0',
                            300: '#cbd5e1',
                            400: '#94a3b8',
                            500: '#64748b',
                            600: '#475569',
                            700: '#1c446c',
                            750: '#16395b',
                            800: '#123250',
                            850: '#0f2942',
                            900: '#0e263e', // Logo ocean navy
                            950: '#081726',
                        }
                    }
                }
            }
        }
    </script>
    <style> 
        html {
            font-size: 16px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        body { 
            font-family: 'IBM Plex Sans', sans-serif; 
            font-size: 0.9375rem;
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

        /* Staggered Entry Animation for Logo and Form */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-28px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @keyframes fadeInUpCard {
            from {
                opacity: 0;
                transform: translateY(32px) scale(0.96);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        .anim-logo-entry {
            animation: fadeInDown 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
        .anim-card-entry {
            animation: fadeInUpCard 0.7s cubic-bezier(0.16, 1, 0.3, 1) 0.16s both;
        }
    </style>
</head>
<body class="guest-gradient-bg text-slate-800 min-h-screen flex items-center justify-center p-4 relative overflow-x-hidden">
    <!-- Floating Theme Switcher -->
    <div class="fixed top-4 right-4 z-50">
        <x-theme-switcher />
    </div>

    <!-- Ambient Luminous Background Elements matching Logo Palette -->
    <div class="fixed -bottom-24 -left-24 w-96 h-96 bg-emerald-400/25 rounded-full blur-3xl pointer-events-none"></div>
    <div class="fixed -bottom-24 -right-24 w-96 h-96 bg-cyan-400/30 rounded-full blur-3xl pointer-events-none"></div>
    <div class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-teal-100/25 rounded-full blur-3xl pointer-events-none"></div>

    <!-- Watermark Low-Opacity Logo Background -->
    <div class="fixed inset-0 pointer-events-none z-0 flex items-center justify-center overflow-hidden select-none" aria-hidden="true">
        <img src="{{ asset('images/logo_icon.png') }}" alt="" class="w-[440px] sm:w-[580px] max-w-[80vw] select-none pointer-events-none" style="opacity: 0.035;">
    </div>

    <div class="w-full @yield('container-width', 'max-w-md') relative z-10 my-auto py-4 sm:py-6 transition-all">
        @if(!View::hasSection('hide-header-logo'))
        <!-- Logo Header (Animasi Bertahap 1) -->
        <div class="text-center mb-6 sm:mb-7 anim-logo-entry flex justify-center">
            <a href="/" class="inline-flex items-center justify-center hover:opacity-95 transition-all group">
                <!-- Logo Light Mode: teks gelap untuk latar terang -->
                <img src="{{ asset('images/logo_lab_lgn_rgst_clean.png') }}" 
                     alt="Laboratorium Lingkungan Terpadu" 
                     class="w-56 sm:w-64 md:w-72 max-w-[85vw] h-auto object-contain filter drop-shadow-md group-hover:scale-105 transition-transform duration-300 block dark:hidden">
                <!-- Logo Dark Mode: teks putih / terang untuk latar gelap -->
                <img src="{{ asset('images/logo_lab_navbar_clean.png') }}" 
                     alt="Laboratorium Lingkungan Terpadu" 
                     class="w-56 sm:w-64 md:w-72 max-w-[85vw] h-auto object-contain filter drop-shadow-md group-hover:scale-105 transition-transform duration-300 hidden dark:block">
            </a>
        </div>
        @endif

        <!-- Form Card (Animasi Bertahap 2) -->
        <div class="anim-card-entry">
            @yield('content')
        </div>
    </div>

    <script>
        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId) || document.querySelector(`input[name="${inputId}"]`);
            if (!input) return;
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                if (icon) {
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash', 'text-emerald-400');
                }
            } else {
                input.type = 'password';
                if (icon) {
                    icon.classList.remove('fa-eye-slash', 'text-emerald-400');
                    icon.classList.add('fa-eye');
                }
            }
        }
    </script>
</body>
</html>
