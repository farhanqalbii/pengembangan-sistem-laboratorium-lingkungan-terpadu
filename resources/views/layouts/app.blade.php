
<!DOCTYPE html>
<html lang="id" class="{{ request()->cookie('theme_preference') === 'dark' ? 'dark' : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistem Laboratorium Lingkungan Terpadu 2026')</title>
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

    <!-- Tailwind CSS CDN Fallback + Alpine.js -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @auth
        @php
            $unreadCounts = \App\Models\Notification::getUnreadCountsFor(auth()->id());
            $defaultCounts = ['total' => 0, 'sample' => 0, 'borrowing' => 0, 'coa' => 0, 'payment' => 0, 'equipment' => 0, 'verification' => 0, 'finance' => 0];
            $storeCounts = array_merge($defaultCounts, $unreadCounts ?? []);
        @endphp
        <script>
            (function() {
                const initNotificationStore = () => {
                    if (window.Alpine && window.Alpine.store && !window.Alpine.store('notifications')) {
                        window.Alpine.store('notifications', {
                            counts: {{ Js::from($storeCounts) }},
                            updateCounts(newCounts) {
                                this.counts = Object.assign({}, this.counts, newCounts);
                            },
                            decrement(category) {
                                if (this.counts.total > 0) this.counts.total--;
                                if (category && this.counts[category] > 0) this.counts[category]--;
                            },
                            zeroAll() {
                                this.counts = { total: 0, sample: 0, borrowing: 0, coa: 0, payment: 0, equipment: 0, verification: 0, finance: 0 };
                            }
                        });
                    }
                };
                document.addEventListener('alpine:init', initNotificationStore);
                if (window.Alpine && window.Alpine.store) {
                    initNotificationStore();
                }
            })();
        </script>
    @endauth

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
                            600: '#168975',
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
                            400: '#4eb5d0',
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
                            500: '#4eb5d0',
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
                            900: '#0e263e',
                            950: '#081726',
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
        @media (min-width: 1536px) {
            html {
                font-size: 16px;
            }
        }
        body { 
            font-family: 'IBM Plex Sans', sans-serif; 
            font-size: 0.875rem;
            color: #1e293b;
        }
        [x-cloak] { display: none !important; }

        /* Custom Scrollbar for compact panels */
        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.25);
            border-radius: 9999px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(148, 163, 184, 0.45);
        }

        /* Animation Keyframes */
        @keyframes pageFadeInUp {
            from {
                opacity: 0;
                transform: translateY(14px);
            }
            to {
                opacity: 1;
                transform: none;
            }
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(18px);
            }
            to {
                opacity: 1;
                transform: none;
            }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Dynamic Dropdown Stacking Context Elevation (prevents any clipping from sibling cards/tables) */
        :has(> [data-dropdown-open="true"]),
        :has([data-dropdown-open="true"]),
        .card-hover:has([data-dropdown-open="true"]),
        form:has([data-dropdown-open="true"]) {
            z-index: 50 !important;
            position: relative !important;
        }
        @keyframes pulseSoft {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.04); opacity: 0.88; }
        }
        @keyframes pingDot {
            75%, 100% {
                transform: scale(2.2);
                opacity: 0;
            }
        }

        /* Page Level Smooth Entry */
        .page-entry {
            animation: pageFadeInUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        /* General Section Entry & Delays */
        .anim-fade-up {
            animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
        .anim-delay-1 { animation-delay: 0.06s; }
        .anim-delay-2 { animation-delay: 0.14s; }
        .anim-delay-3 { animation-delay: 0.22s; }
        .anim-delay-4 { animation-delay: 0.30s; }
        .anim-delay-5 { animation-delay: 0.38s; }

        /* Stagger Animation for Metric Cards and Grids */
        .stagger-grid > :nth-child(1) { animation: fadeInUp 0.45s cubic-bezier(0.16, 1, 0.3, 1) 0.04s both; }
        .stagger-grid > :nth-child(2) { animation: fadeInUp 0.45s cubic-bezier(0.16, 1, 0.3, 1) 0.09s both; }
        .stagger-grid > :nth-child(3) { animation: fadeInUp 0.45s cubic-bezier(0.16, 1, 0.3, 1) 0.14s both; }
        .stagger-grid > :nth-child(4) { animation: fadeInUp 0.45s cubic-bezier(0.16, 1, 0.3, 1) 0.19s both; }
        .stagger-grid > :nth-child(5) { animation: fadeInUp 0.45s cubic-bezier(0.16, 1, 0.3, 1) 0.24s both; }
        .stagger-grid > :nth-child(6) { animation: fadeInUp 0.45s cubic-bezier(0.16, 1, 0.3, 1) 0.29s both; }
        .stagger-grid > :nth-child(7) { animation: fadeInUp 0.45s cubic-bezier(0.16, 1, 0.3, 1) 0.34s both; }
        .stagger-grid > :nth-child(8) { animation: fadeInUp 0.45s cubic-bezier(0.16, 1, 0.3, 1) 0.39s both; }

        /* Card Hover Elevation */
        .card-hover {
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.25s ease;
        }
        .card-hover:hover {
            transform: translateY(-3px);
            box-shadow: 0 14px 28px -10px rgba(14, 38, 62, 0.10);
        }

        /* Interactive Table Row Hover */
        tbody tr {
            transition: background-color 0.18s ease, transform 0.18s ease;
        }
        tbody tr:hover {
            background-color: rgba(241, 245, 249, 0.8) !important;
        }

        /* Status Badge Transition & Glow */
        span[class*="rounded-full"], .badge-trans {
            transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.2s ease, background-color 0.2s ease;
            display: inline-flex;
            align-items: center;
        }
        span[class*="rounded-full"]:hover, .badge-trans:hover {
            transform: scale(1.07);
        }

        /* Subtle Pulse for Critical / Alert indicators */
        .animate-pulse-subtle {
            animation: pulseSoft 2.2s infinite ease-in-out;
        }

        /* Stock Warning Pulse Ping */
        .stock-pulse-dot {
            position: relative;
            display: inline-flex;
        }
        .stock-pulse-dot::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 9999px;
            background-color: currentColor;
            opacity: 0.6;
            animation: pingDot 1.8s cubic-bezier(0, 0, 0.2, 1) infinite;
        }

        /* Sembunyikan Watermark Web saat Modal / Pop-up Aktif */
        body.overflow-hidden #app-watermark,
        body.modal-open #app-watermark {
            display: none !important;
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
        }

        /* Alpine.js Cloak utility */
        [x-cloak] {
            display: none !important;
        }

        /* =========================================================================
           Standardized Dropdown & Select Styling (Mentor Evaluation Poin 3)
           ========================================================================= */
        select,
        .form-select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2310b981' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.75rem center;
            background-repeat: no-repeat;
            background-size: 1.15em 1.15em;
            padding-right: 2.5rem !important;
            padding-left: 0.875rem;
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
            border: 1px solid #cbd5e1;
            border-radius: 0.75rem;
            font-size: 0.8125rem;
            line-height: 1.25rem;
            min-height: 2.375rem;
            color: #1e293b;
            background-color: #ffffff;
            cursor: pointer;
            transition: border-color 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
        }

        select option {
            background-color: #ffffff;
            color: #1e293b;
            padding: 8px 12px;
            font-size: 0.8125rem;
        }

        select:hover:not(:disabled),
        .form-select:hover:not(:disabled) {
            border-color: #10b981;
        }

        select:focus,
        .form-select:focus {
            outline: none !important;
            border-color: #168975 !important;
            box-shadow: 0 0 0 3px rgba(22, 137, 117, 0.18) !important;
        }

        select:disabled,
        .form-select:disabled {
            background-color: #f1f5f9;
            color: #94a3b8;
            cursor: not-allowed;
            opacity: 0.75;
        }

        html.dark select,
        html.dark .form-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2334d399' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            border-color: #334155;
            background-color: #0f172a;
            color: #f1f5f9;
        }

        html.dark select option {
            background-color: #0f172a;
            color: #f1f5f9;
        }

        html.dark select:focus,
        html.dark .form-select:focus {
            border-color: #3ebca8 !important;
            box-shadow: 0 0 0 3px rgba(62, 188, 168, 0.25) !important;
        }

        /* Slim Modern Scrollbar for Custom Dropdowns */
        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
        html.dark .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #475569;
        }

        /* =========================================================================
           Sidebar Navigation Theming
           Light Mode: Soft Blue (#EAF2F8)
           Dark Mode:  Midnight  (#071521)
           ========================================================================= */
        :root {
            --sidebar-bg: #EAF2F8;
            --sidebar-logo-bg: #EAF2F8;
            --sidebar-hover-bg: #D8E7F3;
            --sidebar-text: #17324D;
            --sidebar-icon: #36536D;
            --sidebar-active-bg: #168F7A;
            --sidebar-active-text: #FFFFFF;
            --sidebar-border: #D1DFEB;
            --sidebar-section-title: #36536D;
        }

        html.dark {
            --sidebar-bg: #071521;
            --sidebar-logo-bg: #071521;
            --sidebar-hover-bg: #142A3D;
            --sidebar-text: #C4D2DF;
            --sidebar-icon: #C4D2DF;
            --sidebar-active-bg: #168F7A;
            --sidebar-active-text: #FFFFFF;
            --sidebar-border: #1C3448;
            --sidebar-section-title: #7E93A7;
        }

        .app-sidebar {
            background-color: var(--sidebar-bg) !important;
            border-right: 1px solid var(--sidebar-border) !important;
            color: var(--sidebar-text) !important;
        }

        .app-sidebar-logo {
            background-color: var(--sidebar-logo-bg) !important;
            border-bottom: 1px solid var(--sidebar-border) !important;
        }

        .app-sidebar-section-title {
            color: var(--sidebar-section-title) !important;
        }

        .app-sidebar-link {
            color: var(--sidebar-text) !important;
            background-color: transparent;
            transition: background-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
        }

        .app-sidebar-link i {
            color: var(--sidebar-icon) !important;
            transition: color 0.2s ease;
        }

        .app-sidebar-link:hover {
            background-color: var(--sidebar-hover-bg) !important;
            color: var(--sidebar-text) !important;
        }

        .app-sidebar-link:hover i {
            color: var(--sidebar-text) !important;
        }

        .app-sidebar-link.is-active,
        .app-sidebar-link.is-active:hover {
            background-color: var(--sidebar-active-bg) !important;
            color: var(--sidebar-active-text) !important;
            box-shadow: 0 10px 18px -4px rgba(22, 143, 122, 0.35) !important;
        }

        .app-sidebar-link.is-active i {
            color: var(--sidebar-active-text) !important;
        }

        .app-sidebar-close-btn {
            color: var(--sidebar-icon) !important;
        }

        .app-sidebar-close-btn:hover {
            color: var(--sidebar-text) !important;
        }

        .app-sidebar .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(54, 83, 109, 0.22);
        }
        .app-sidebar .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(54, 83, 109, 0.40);
        }
        html.dark .app-sidebar .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(196, 210, 223, 0.22);
        }
        html.dark .app-sidebar .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(196, 210, 223, 0.40);
        }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col" x-data="{ sidebarOpen: false, showLogoutModal: false }" x-effect="document.body.classList.toggle('overflow-hidden', showLogoutModal); document.body.classList.toggle('modal-open', showLogoutModal)">

    <!-- Top Navigation Progress Bar -->
    <div id="top-nav-loader" class="fixed top-0 left-0 w-full h-[3px] z-[99999] pointer-events-none opacity-0 transition-opacity duration-200" style="z-index: 99999;">
        <div id="top-nav-loader-bar" class="h-full w-0 bg-gradient-to-r from-emerald-500 via-teal-400 to-cyan-400 shadow-[0_0_8px_rgba(20,184,166,0.7)] transition-all duration-300 ease-out"></div>
    </div>

    <div class="flex min-h-screen">
        <!-- Mobile Sidebar Backdrop Overlay -->
        <div 
            x-show="sidebarOpen" 
            @click="sidebarOpen = false" 
            x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-30 bg-slate-900/60 backdrop-blur-xs lg:hidden"
            x-cloak
        ></div>

        <!-- Sidebar Navigation -->
        <aside 
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            class="fixed inset-y-0 left-0 z-40 w-64 app-sidebar transition-transform duration-300 ease-in-out flex flex-col justify-between shadow-xl"
        >
            <div>
                <!-- Brand Header -->
                <div class="p-3.5 sm:p-4 flex items-center justify-between app-sidebar-logo">
                    <a href="/" class="block flex-1 mr-2">
                        <div class="flex items-center justify-center py-0.5 px-1">
                            <!-- Light Mode Logo: Soft Blue Background (#EAF2F8) with Clean Navy Text -->
                            <img src="{{ asset('images/logo_lab_lgn_rgst.png') }}" alt="Laboratorium Lingkungan Terpadu" class="h-9 sm:h-10 w-auto max-w-full object-contain filter drop-shadow block dark:hidden">
                            <!-- Dark Mode Logo: Midnight Background (#071521) with Clean White Text -->
                            <img src="{{ asset('images/logo_lab_navbar_clean.png') }}" alt="Laboratorium Lingkungan Terpadu" class="h-9 sm:h-10 w-auto max-w-full object-contain filter drop-shadow hidden dark:block">
                        </div>
                    </a>
                    <button @click="sidebarOpen = false" class="lg:hidden app-sidebar-close-btn p-1 transition-colors" aria-label="Tutup navigasi sidebar">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <!-- Navigation Items based on Role -->
                <nav class="p-3 space-y-0.5 overflow-y-auto max-h-[calc(100vh-85px)] custom-scrollbar">
                    @auth
                        @if(auth()->user()->isCustomer())
                            <div class="px-3 pt-2 pb-1 text-[10px] font-bold uppercase app-sidebar-section-title tracking-wider">Customer Portal</div>
                            <a href="{{ route('customer.dashboard') }}" class="flex items-center space-x-2.5 px-3 py-2 rounded-xl text-xs font-semibold app-sidebar-link {{ request()->routeIs('customer.dashboard') ? 'is-active' : '' }}">
                                <i class="fa-solid fa-chart-line w-4 text-center"></i>
                                <span>Dashboard</span>
                            </a>
                            <a href="{{ route('customer.samples.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold app-sidebar-link {{ request()->routeIs('customer.samples.*') ? 'is-active' : '' }}">
                                <div class="flex items-center space-x-2.5">
                                    <i class="fa-solid fa-vial-circle-check w-4 text-center"></i>
                                    <span>Pengujian Sampel</span>
                                </div>
                                <span 
                                    x-show="($store?.notifications?.counts?.sample ?? 0) > 0" 
                                    x-text="$store?.notifications?.counts?.sample ?? 0"
                                    class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-extrabold rounded-full {{ request()->routeIs('customer.samples.*') ? 'bg-white text-emerald-800' : 'bg-rose-500 text-white' }} min-w-[18px] h-[18px] shadow-xs"
                                    @if(($unreadCounts['sample'] ?? 0) === 0) style="display: none;" @endif
                                >{{ $unreadCounts['sample'] ?? 0 }}</span>
                            </a>
                            <a href="{{ route('customer.borrowings.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold app-sidebar-link {{ request()->routeIs('customer.borrowings.*') ? 'is-active' : '' }}">
                                <div class="flex items-center space-x-2.5">
                                    <i class="fa-solid fa-toolbox w-4 text-center"></i>
                                    <span>Peminjaman Alat</span>
                                </div>
                                <span 
                                    x-show="($store?.notifications?.counts?.borrowing ?? 0) > 0" 
                                    x-text="$store?.notifications?.counts?.borrowing ?? 0"
                                    class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-extrabold rounded-full {{ request()->routeIs('customer.borrowings.*') ? 'bg-white text-emerald-800' : 'bg-rose-500 text-white' }} min-w-[18px] h-[18px] shadow-xs"
                                    @if(($unreadCounts['borrowing'] ?? 0) === 0) style="display: none;" @endif
                                >{{ $unreadCounts['borrowing'] ?? 0 }}</span>
                            </a>
                            <a href="{{ route('customer.certificates.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold app-sidebar-link {{ request()->routeIs('customer.certificates.*') ? 'is-active' : '' }}">
                                <div class="flex items-center space-x-2.5">
                                    <i class="fa-solid fa-file-contract w-4 text-center"></i>
                                    <span>Sertifikat (COA)</span>
                                </div>
                                <span 
                                    x-show="($store?.notifications?.counts?.coa ?? 0) > 0" 
                                    x-text="$store?.notifications?.counts?.coa ?? 0"
                                    class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-extrabold rounded-full {{ request()->routeIs('customer.certificates.*') ? 'bg-white text-emerald-800' : 'bg-rose-500 text-white' }} min-w-[18px] h-[18px] shadow-xs"
                                    @if(($unreadCounts['coa'] ?? 0) === 0) style="display: none;" @endif
                                >{{ $unreadCounts['coa'] ?? 0 }}</span>
                            </a>
                            <a href="{{ route('customer.payments.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold app-sidebar-link {{ request()->routeIs('customer.payments.*') ? 'is-active' : '' }}">
                                <div class="flex items-center space-x-2.5">
                                    <i class="fa-solid fa-file-invoice-dollar w-4 text-center"></i>
                                    <span>Invoice & Pembayaran</span>
                                </div>
                                <span 
                                    x-show="($store?.notifications?.counts?.payment ?? 0) > 0" 
                                    x-text="$store?.notifications?.counts?.payment ?? 0"
                                    class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-extrabold rounded-full {{ request()->routeIs('customer.payments.*') ? 'bg-white text-emerald-800' : 'bg-rose-500 text-white' }} min-w-[18px] h-[18px] shadow-xs"
                                    @if(($unreadCounts['payment'] ?? 0) === 0) style="display: none;" @endif
                                >{{ $unreadCounts['payment'] ?? 0 }}</span>
                            </a>
                            <a href="{{ route('customer.satisfaction.create') }}" class="flex items-center space-x-2.5 px-3 py-2 rounded-xl text-xs font-semibold app-sidebar-link {{ request()->routeIs('customer.satisfaction.*') ? 'is-active' : '' }}">
                                <i class="fa-solid fa-face-smile w-4 text-center"></i>
                                <span>Kepuasan Pelanggan</span>
                            </a>
                        @elseif(auth()->user()->isStaff())
                            <div class="px-3 pt-2 pb-1 text-[10px] font-bold uppercase app-sidebar-section-title tracking-wider">Staff / Analis Portal</div>
                            <a href="{{ route('staff.dashboard') }}" class="flex items-center space-x-2.5 px-3 py-2 rounded-xl text-xs font-semibold app-sidebar-link {{ request()->routeIs('staff.dashboard') ? 'is-active' : '' }}">
                                <i class="fa-solid fa-table-cells-large w-4 text-center"></i>
                                <span>Dashboard Staf</span>
                            </a>
                            <a href="{{ route('staff.samples.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold app-sidebar-link {{ request()->routeIs('staff.samples.*') ? 'is-active' : '' }}">
                                <div class="flex items-center space-x-2.5">
                                    <i class="fa-solid fa-vials w-4 text-center"></i>
                                    <span>Manajemen Sampel</span>
                                </div>
                                <span 
                                    x-show="($store?.notifications?.counts?.sample ?? 0) > 0" 
                                    x-text="$store?.notifications?.counts?.sample ?? 0"
                                    class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-extrabold rounded-full {{ request()->routeIs('staff.samples.*') ? 'bg-white text-emerald-800' : 'bg-rose-500 text-white' }} min-w-[18px] h-[18px] shadow-xs"
                                    @if(($unreadCounts['sample'] ?? 0) === 0) style="display: none;" @endif
                                >{{ $unreadCounts['sample'] ?? 0 }}</span>
                            </a>
                            <a href="{{ route('staff.equipment.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold app-sidebar-link {{ request()->routeIs('staff.equipment.*') ? 'is-active' : '' }}">
                                <div class="flex items-center space-x-2.5">
                                    <i class="fa-solid fa-microscope w-4 text-center"></i>
                                    <span>Inventori Alat</span>
                                </div>
                                <span 
                                    x-show="($store?.notifications?.counts?.equipment ?? 0) > 0" 
                                    x-text="$store?.notifications?.counts?.equipment ?? 0"
                                    class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-extrabold rounded-full {{ request()->routeIs('staff.equipment.*') ? 'bg-white text-emerald-800' : 'bg-rose-500 text-white' }} min-w-[18px] h-[18px] shadow-xs"
                                    @if(($unreadCounts['equipment'] ?? 0) === 0) style="display: none;" @endif
                                >{{ $unreadCounts['equipment'] ?? 0 }}</span>
                            </a>
                            <a href="{{ route('staff.borrowings.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold app-sidebar-link {{ request()->routeIs('staff.borrowings.*') ? 'is-active' : '' }}">
                                <div class="flex items-center space-x-2.5">
                                    <i class="fa-solid fa-hand-holding-hand w-4 text-center"></i>
                                    <span>Pengembalian Alat</span>
                                </div>
                                <span 
                                    x-show="($store?.notifications?.counts?.borrowing ?? 0) > 0" 
                                    x-text="$store?.notifications?.counts?.borrowing ?? 0"
                                    class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-extrabold rounded-full {{ request()->routeIs('staff.borrowings.*') ? 'bg-white text-emerald-800' : 'bg-rose-500 text-white' }} min-w-[18px] h-[18px] shadow-xs"
                                    @if(($unreadCounts['borrowing'] ?? 0) === 0) style="display: none;" @endif
                                >{{ $unreadCounts['borrowing'] ?? 0 }}</span>
                            </a>
                        @elseif(auth()->user()->isManager())
                            <div class="px-3 pt-2 pb-1 text-[10px] font-bold uppercase app-sidebar-section-title tracking-wider">Manajer Lab Portal</div>
                            <a href="{{ route('manager.dashboard') }}" class="flex items-center space-x-2.5 px-3 py-2 rounded-xl text-xs font-semibold app-sidebar-link {{ request()->routeIs('manager.dashboard') ? 'is-active' : '' }}">
                                <i class="fa-solid fa-chart-pie w-4 text-center"></i>
                                <span>Dashboard Manajer</span>
                            </a>
                            <a href="{{ route('manager.verifications.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold app-sidebar-link {{ request()->routeIs('manager.verifications.*') ? 'is-active' : '' }}">
                                <div class="flex items-center space-x-2.5">
                                    <i class="fa-solid fa-user-check w-4 text-center"></i>
                                    <span>Verifikasi Technical</span>
                                </div>
                                <span 
                                    x-show="($store?.notifications?.counts?.verification ?? 0) > 0" 
                                    x-text="$store?.notifications?.counts?.verification ?? 0"
                                    class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-extrabold rounded-full {{ request()->routeIs('manager.verifications.*') ? 'bg-white text-emerald-800' : 'bg-rose-500 text-white' }} min-w-[18px] h-[18px] shadow-xs"
                                    @if(($unreadCounts['verification'] ?? 0) === 0) style="display: none;" @endif
                                >{{ $unreadCounts['verification'] ?? 0 }}</span>
                            </a>
                            <a href="{{ route('manager.reports.satisfaction') }}" class="flex items-center space-x-2.5 px-3 py-2 rounded-xl text-xs font-semibold app-sidebar-link {{ request()->routeIs('manager.reports.*') ? 'is-active' : '' }}">
                                <i class="fa-solid fa-star w-4 text-center"></i>
                                <span>Laporan Kepuasan</span>
                            </a>
                        @elseif(auth()->user()->isAdmin())
                            <div class="px-3 pt-2 pb-1 text-[10px] font-bold uppercase app-sidebar-section-title tracking-wider">Administrator Portal</div>
                            <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-2.5 px-3 py-1.5 sm:py-2 rounded-xl text-xs font-semibold app-sidebar-link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">
                                <i class="fa-solid fa-gauge-high w-4 text-center"></i>
                                <span>Dashboard Admin</span>
                            </a>
                            <a href="{{ route('admin.users.index') }}" class="flex items-center space-x-2.5 px-3 py-1.5 sm:py-2 rounded-xl text-xs font-semibold app-sidebar-link {{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}">
                                <i class="fa-solid fa-users-gear w-4 text-center"></i>
                                <span>Manajemen Pengguna</span>
                            </a>
                            <a href="{{ route('admin.borrowings.index') }}" class="flex items-center justify-between px-3 py-1.5 sm:py-2 rounded-xl text-xs font-semibold app-sidebar-link {{ request()->routeIs('admin.borrowings.*') ? 'is-active' : '' }}">
                                <div class="flex items-center space-x-2.5">
                                    <i class="fa-solid fa-boxes-packing w-4 text-center"></i>
                                    <span>Approval Peminjaman</span>
                                </div>
                                <span 
                                    x-show="($store?.notifications?.counts?.borrowing ?? 0) > 0" 
                                    x-text="$store?.notifications?.counts?.borrowing ?? 0"
                                    class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-extrabold rounded-full {{ request()->routeIs('admin.borrowings.*') ? 'bg-white text-emerald-800' : 'bg-rose-500 text-white' }} min-w-[18px] h-[18px] shadow-xs"
                                    @if(($unreadCounts['borrowing'] ?? 0) === 0) style="display: none;" @endif
                                >{{ $unreadCounts['borrowing'] ?? 0 }}</span>
                            </a>
                            <a href="{{ route('admin.finance.index') }}" class="flex items-center justify-between px-3 py-1.5 sm:py-2 rounded-xl text-xs font-semibold app-sidebar-link {{ request()->routeIs('admin.finance.*') ? 'is-active' : '' }}">
                                <div class="flex items-center space-x-2.5">
                                    <i class="fa-solid fa-wallet w-4 text-center"></i>
                                    <span>Keuangan & Refund</span>
                                </div>
                                <span 
                                    x-show="($store?.notifications?.counts?.finance ?? 0) > 0" 
                                    x-text="$store?.notifications?.counts?.finance ?? 0"
                                    class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-extrabold rounded-full {{ request()->routeIs('admin.finance.*') ? 'bg-white text-emerald-800' : 'bg-rose-500 text-white' }} min-w-[18px] h-[18px] shadow-xs"
                                    @if(($unreadCounts['finance'] ?? 0) === 0) style="display: none;" @endif
                                >{{ $unreadCounts['finance'] ?? 0 }}</span>
                            </a>
                            <a href="{{ route('admin.parameters.index') }}" class="flex items-center space-x-2.5 px-3 py-1.5 sm:py-2 rounded-xl text-xs font-semibold app-sidebar-link {{ request()->routeIs('admin.parameters.*') ? 'is-active' : '' }}">
                                <i class="fa-solid fa-sliders w-4 text-center"></i>
                                <span>Parameter & Baku Mutu</span>
                            </a>
                            <a href="{{ route('admin.equipment.index') }}" class="flex items-center justify-between px-3 py-1.5 sm:py-2 rounded-xl text-xs font-semibold app-sidebar-link {{ request()->routeIs('admin.equipment.*') ? 'is-active' : '' }}">
                                <div class="flex items-center space-x-2.5">
                                    <i class="fa-solid fa-microscope w-4 text-center"></i>
                                    <span>Inventori Alat</span>
                                </div>
                                <span 
                                    x-show="($store?.notifications?.counts?.equipment ?? 0) > 0" 
                                    x-text="$store?.notifications?.counts?.equipment ?? 0"
                                    class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-extrabold rounded-full {{ request()->routeIs('admin.equipment.*') ? 'bg-white text-emerald-800' : 'bg-rose-500 text-white' }} min-w-[18px] h-[18px] shadow-xs"
                                    @if(($unreadCounts['equipment'] ?? 0) === 0) style="display: none;" @endif
                                >{{ $unreadCounts['equipment'] ?? 0 }}</span>
                            </a>
                            <div class="px-3 pt-3 pb-1 text-[10px] font-bold uppercase app-sidebar-section-title tracking-wider">Laporan & Audit</div>
                            <a href="{{ route('admin.reports.samples') }}" class="flex items-center space-x-2.5 px-3 py-1.5 sm:py-2 rounded-xl text-xs font-semibold app-sidebar-link {{ request()->routeIs('admin.reports.samples') ? 'is-active' : '' }}">
                                <i class="fa-solid fa-file-pdf w-4 text-center"></i>
                                <span>Laporan Sampel</span>
                            </a>
                            <a href="{{ route('admin.reports.inventory') }}" class="flex items-center space-x-2.5 px-3 py-1.5 sm:py-2 rounded-xl text-xs font-semibold app-sidebar-link {{ request()->routeIs('admin.reports.inventory') ? 'is-active' : '' }}">
                                <i class="fa-solid fa-file-csv w-4 text-center"></i>
                                <span>Laporan Inventori</span>
                            </a>
                            <a href="{{ route('admin.reports.borrowings') }}" class="flex items-center space-x-2.5 px-3 py-1.5 sm:py-2 rounded-xl text-xs font-semibold app-sidebar-link {{ request()->routeIs('admin.reports.borrowings') ? 'is-active' : '' }}">
                                <i class="fa-solid fa-file-lines w-4 text-center"></i>
                                <span>Laporan Peminjaman</span>
                            </a>
                            <a href="{{ route('admin.reports.financial') }}" class="flex items-center space-x-2.5 px-3 py-1.5 sm:py-2 rounded-xl text-xs font-semibold app-sidebar-link {{ request()->routeIs('admin.reports.financial') ? 'is-active' : '' }}">
                                <i class="fa-solid fa-receipt w-4 text-center"></i>
                                <span>Laporan Keuangan</span>
                            </a>
                            <a href="{{ route('admin.reports.satisfaction') }}" class="flex items-center space-x-2.5 px-3 py-1.5 sm:py-2 rounded-xl text-xs font-semibold app-sidebar-link {{ request()->routeIs('admin.reports.satisfaction') ? 'is-active' : '' }}">
                                <i class="fa-solid fa-star w-4 text-center"></i>
                                <span>Laporan Kepuasan</span>
                            </a>
                            <a href="{{ route('admin.activity-logs.index') }}" class="flex items-center space-x-2.5 px-3 py-1.5 sm:py-2 rounded-xl text-xs font-semibold app-sidebar-link {{ request()->routeIs('admin.activity-logs.*') ? 'is-active' : '' }}">
                                <i class="fa-solid fa-shield-cat w-4 text-center"></i>
                                <span>Log Aktivitas</span>
                            </a>
                        @endif
                    @endauth

                    <div class="px-3 pt-3 pb-1 text-[10px] font-bold uppercase app-sidebar-section-title tracking-wider">Tools</div>
                    <a href="{{ route('equipment.scan') }}" class="flex items-center space-x-2.5 px-3 py-1.5 sm:py-2 rounded-xl text-xs font-semibold app-sidebar-link {{ request()->routeIs('equipment.scan') ? 'is-active' : '' }}">
                        <i class="fa-solid fa-qrcode w-4 text-center"></i>
                        <span>Scan QR / Barcode</span>
                    </a>
                </nav>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="flex-1 lg:pl-64 flex flex-col min-w-0 relative">
            <!-- Watermark Low-Opacity Logo Background -->
            <div id="app-watermark" class="fixed inset-0 lg:left-64 pointer-events-none z-0 flex items-center justify-center overflow-hidden select-none transition-opacity duration-200" aria-hidden="true">
                <img src="{{ asset('images/logo_icon.png') }}" alt="" class="w-[320px] sm:w-[440px] lg:w-[500px] max-w-[65vw] select-none pointer-events-none filter grayscale contrast-125" style="opacity: 0.035;">
            </div>

            <!-- Topbar Header -->
            <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-sm">
                <div class="px-4 sm:px-6 py-3 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100">
                            <i class="fa-solid fa-bars text-lg"></i>
                        </button>
                        <h2 class="text-lg font-bold text-slate-800 hidden sm:block">@yield('page-title', 'Dashboard')</h2>
                    </div>

                    <!-- Right Topbar Controls -->
                    <div class="flex items-center space-x-2 sm:space-x-3">
                        <!-- Theme Switcher Control -->
                        <x-theme-switcher />

                        <!-- Notification Center -->
                        @auth
                        @php
                            $recentNotifs = \App\Models\Notification::where('user_id', auth()->id())->latest()->take(10)->get();
                            $notifsList = $recentNotifs->map(function($n) {
                                return [
                                    'id' => $n->id,
                                    'title' => $n->title,
                                    'message' => $n->message,
                                    'link' => $n->link,
                                    'is_read' => $n->read_at !== null,
                                    'time' => $n->created_at->diffForHumans(),
                                ];
                            })->values()->all();
                        @endphp
                        <div class="relative" x-data="{ 
                            open: false,
                            marking: false,
                            notifications: {{ Js::from($notifsList) }},
                            async markItemAsRead(item) {
                                if (!item.is_read) {
                                    try {
                                        const res = await fetch('/notifications/' + item.id + '/read', {
                                            method: 'PATCH',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                'Accept': 'application/json'
                                            }
                                        });
                                        if (res.ok) {
                                            const data = await res.json();
                                            item.is_read = true;
                                            if (data.counts && this.$store && this.$store.notifications) {
                                                this.$store.notifications.updateCounts(data.counts);
                                            } else if (this.$store && this.$store.notifications) {
                                                this.$store.notifications.decrement();
                                            }
                                        }
                                    } catch (err) {
                                        console.error('Gagal menandai notifikasi dibaca', err);
                                    }
                                }
                                if (item.link) {
                                    window.location.href = item.link;
                                }
                            },
                            async markAllRead() {
                                if (this.marking || (this.$store?.notifications?.counts?.total ?? 0) === 0) return;
                                this.marking = true;
                                try {
                                    const res = await fetch('{{ route('notifications.read-all') }}', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                            'Accept': 'application/json'
                                        }
                                    });
                                    if (res.ok) {
                                        const data = await res.json();
                                        this.notifications.forEach(n => n.is_read = true);
                                        if (data.counts && this.$store && this.$store.notifications) {
                                            this.$store.notifications.updateCounts(data.counts);
                                        } else if (this.$store && this.$store.notifications) {
                                            this.$store.notifications.zeroAll();
                                        }
                                    } else {
                                        alert('Gagal menandai semua notifikasi dibaca.');
                                    }
                                } catch (err) {
                                    console.error('Gagal menandai semua notifikasi dibaca', err);
                                    alert('Terjadi kesalahan jaringan.');
                                } finally {
                                    this.marking = false;
                                }
                            }
                        }">
                            <button @click="open = !open" class="relative p-2 text-slate-600 dark:text-slate-300 hover:text-emerald-600 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-all cursor-pointer" aria-label="Notifikasi">
                                <i class="fa-solid fa-bell text-lg"></i>
                                <span 
                                    x-show="($store?.notifications?.counts?.total ?? 0) > 0" 
                                    x-text="($store?.notifications?.counts?.total ?? 0) > 99 ? '99+' : ($store?.notifications?.counts?.total ?? 0)" 
                                    class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 bg-rose-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center ring-2 ring-white dark:ring-slate-900 shadow-sm leading-none"
                                    @if(($unreadCounts['total'] ?? 0) === 0) style="display: none;" @endif
                                    x-cloak
                                >{{ ($unreadCounts['total'] ?? 0) > 99 ? '99+' : ($unreadCounts['total'] ?? 0) }}</span>
                            </button>

                            <!-- Dropdown -->
                            <div 
                                x-show="open" 
                                @click.outside="open = false" 
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                                x-cloak
                                class="absolute right-0 mt-2 w-[calc(100vw-2rem)] max-w-sm sm:w-96 bg-white dark:bg-[#0e1e2e] rounded-xl shadow-2xl border border-slate-200 dark:border-slate-700/80 py-2 z-50 overflow-hidden"
                            >
                                <div class="px-4 py-2 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50 dark:bg-slate-900/70">
                                    <span class="font-bold text-xs uppercase tracking-wider text-slate-700 dark:text-slate-200">
                                        Notifikasi (<span x-text="$store?.notifications?.counts?.total ?? 0"></span>)
                                    </span>
                                    <div class="flex items-center gap-2">
                                        <template x-if="($store?.notifications?.counts?.total ?? 0) === 0 && notifications.length > 0">
                                            <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1">
                                                <i class="fa-solid fa-check-double"></i> Sudah dibaca
                                            </span>
                                        </template>
                                        <template x-if="($store?.notifications?.counts?.total ?? 0) > 0">
                                            <button type="button" @click="markAllRead()" :disabled="marking" class="text-[11px] text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 hover:underline font-semibold cursor-pointer inline-flex items-center gap-1.5">
                                                <i class="fa-solid fa-spinner fa-spin text-[10px]" x-show="marking" x-cloak></i>
                                                <span x-text="marking ? 'Menandai...' : 'Tandai Dibaca'"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                                <div class="max-h-80 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800 custom-scrollbar">
                                    <template x-if="notifications.length === 0">
                                        <div class="p-6 text-center text-slate-400 dark:text-slate-500 text-xs">Belum ada notifikasi</div>
                                    </template>
                                    <template x-for="n in notifications" :key="n.id">
                                        <div 
                                            @click="markItemAsRead(n)"
                                            class="w-full text-left p-3.5 transition-colors block cursor-pointer"
                                            :class="!n.is_read ? 'bg-emerald-50/50 dark:bg-emerald-950/20 hover:bg-emerald-50 dark:hover:bg-emerald-900/30' : 'hover:bg-slate-50 dark:hover:bg-slate-800/60 opacity-80'"
                                        >
                                            <div class="flex items-start justify-between gap-2">
                                                <p class="text-xs text-slate-800 dark:text-white mb-0.5" :class="!n.is_read ? 'font-bold' : 'font-medium'" x-text="n.title"></p>
                                                <template x-if="!n.is_read">
                                                    <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0 mt-1" title="Belum dibaca"></span>
                                                </template>
                                            </div>
                                            <p class="text-xs text-slate-600 dark:text-slate-300 line-clamp-2" x-text="n.message"></p>
                                            <div class="flex items-center justify-between mt-1 text-[10px] text-slate-400 dark:text-slate-500">
                                                <span x-text="n.time"></span>
                                                <template x-if="n.link">
                                                    <span class="text-emerald-600 dark:text-emerald-400 font-medium inline-flex items-center gap-0.5">
                                                        Lihat detail <i class="fa-solid fa-arrow-right text-[8px]"></i>
                                                    </span>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                <a href="{{ route('notifications.index') }}" class="block text-center py-2.5 bg-slate-50 dark:bg-slate-900/70 border-t border-slate-100 dark:border-slate-800 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">
                                    Lihat Semua Notifikasi
                                </a>
                            </div>
                        </div>
                        @endauth

                        <!-- User Profile Dropdown -->
                        @auth
                        <div class="relative" x-data="{ userMenuOpen: false }">
                            <button @click="userMenuOpen = !userMenuOpen" type="button" class="flex items-center gap-2 p-1 sm:px-2.5 sm:py-1.5 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 transition-all focus:outline-none focus:ring-2 focus:ring-emerald-500/30 group" :class="userMenuOpen ? 'bg-slate-100 dark:bg-slate-800' : ''">
                                <div class="w-8 h-8 rounded-full overflow-hidden bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 group-hover:border-emerald-500 flex items-center justify-center font-bold text-emerald-700 dark:text-emerald-400 text-xs flex-shrink-0 transition-colors shadow-sm">
                                    @if(auth()->user()->avatar_url)
                                        <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                                    @else
                                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                                    @endif
                                </div>
                                <span class="hidden md:inline text-xs font-semibold text-slate-700 dark:text-slate-200 group-hover:text-emerald-700 dark:group-hover:text-emerald-400 transition-colors max-w-[130px] truncate">
                                    {{ auth()->user()->name }}
                                </span>
                            </button>

                            <!-- Dropdown Card -->
                            <div 
                                x-show="userMenuOpen" 
                                @click.outside="userMenuOpen = false" 
                                @keydown.escape.window="userMenuOpen = false"
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                x-cloak
                                class="absolute right-0 mt-2.5 w-[calc(100vw-2rem)] max-w-xs sm:w-72 bg-white dark:bg-[#0e1e2e] text-slate-800 dark:text-white rounded-2xl shadow-xl shadow-slate-900/10 dark:shadow-2xl border border-slate-200 dark:border-slate-700/80 p-2 z-50 overflow-hidden"
                            >
                                <!-- Header Profile Info -->
                                <div class="p-3 bg-slate-50 dark:bg-slate-900/70 rounded-xl mb-1 flex items-center gap-3 border border-slate-200/80 dark:border-slate-800">
                                    <div class="w-11 h-11 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center font-bold text-emerald-700 dark:text-cyan-300 text-sm flex-shrink-0 shadow-xs dark:shadow-inner">
                                        @if(auth()->user()->avatar_url)
                                            <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                                        @else
                                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                                        @endif
                                    </div>
                                    <div class="overflow-hidden flex-1 min-w-0">
                                        <div class="flex items-center gap-1.5 mb-0.5">
                                            <p class="text-xs font-bold text-slate-800 dark:text-white truncate">{{ auth()->user()->name }}</p>
                                            <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold capitalize flex-shrink-0
                                                {{ auth()->user()->role === 'admin' ? 'bg-purple-100 dark:bg-purple-500/20 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-400/30' : '' }}
                                                {{ auth()->user()->role === 'manager' ? 'bg-indigo-100 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-400/30' : '' }}
                                                {{ auth()->user()->role === 'staff' ? 'bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-400/30' : '' }}
                                                {{ auth()->user()->role === 'customer' ? 'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-400/30' : '' }}
                                            ">
                                                {{ auth()->user()->role }}
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">{{ auth()->user()->email }}</p>
                                    </div>
                                </div>

                                <!-- Menu Items -->
                                <div class="space-y-0.5 text-xs pt-1">
                                    <a href="{{ route('profile.edit') }}" @click="userMenuOpen = false" class="flex items-center gap-3 px-3 py-2.5 font-medium text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/80 rounded-xl transition-all group">
                                        <i class="fa-regular fa-user w-4 text-center text-slate-400 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors"></i>
                                        <span>Profile</span>
                                    </a>

                                    <div class="h-px bg-slate-200 dark:bg-slate-800 my-1"></div>

                                    <button type="button" @click="userMenuOpen = false; showLogoutModal = true" class="w-full flex items-center gap-3 px-3 py-2.5 font-medium text-rose-600 dark:text-rose-400 hover:text-rose-700 dark:hover:text-rose-300 hover:bg-rose-50 dark:hover:bg-rose-500/10 rounded-xl transition-all text-left cursor-pointer">
                                        <i class="fa-solid fa-arrow-right-from-bracket w-4 text-center text-rose-500 dark:text-rose-400"></i>
                                        <span>Sign Out</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endauth
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 p-4 sm:p-6 lg:p-7 relative z-10 page-entry">
                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-sm">
                        <div class="flex items-center space-x-3">
                            <i class="fa-solid fa-circle-check text-emerald-500 text-lg"></i>
                            <span class="text-sm font-semibold">{{ session('success') }}</span>
                        </div>
                        <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 flex items-center justify-between shadow-sm">
                        <div class="flex items-center space-x-3">
                            <i class="fa-solid fa-triangle-exclamation text-amber-500 text-lg"></i>
                            <span class="text-sm font-semibold">{{ session('warning') }}</span>
                        </div>
                        <button onclick="this.parentElement.remove()" class="text-amber-500 hover:text-amber-800"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between shadow-sm">
                        <div class="flex items-center space-x-3">
                            <i class="fa-solid fa-circle-exclamation text-rose-500 text-lg"></i>
                            <span class="text-sm font-semibold">{{ session('error') }}</span>
                        </div>
                        <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-800"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                @endif

                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="bg-white/90 backdrop-blur-xs border-t border-slate-200 py-4 px-6 text-center text-xs text-slate-500 relative z-10">
                &copy; 2026 Sistem Laboratorium Lingkungan Terpadu (LIMS). Hak Cipta Dilindungi Undang-Undang.
            </footer>
        </div>
    </div>

    <!-- Modal Konfirmasi Logout -->
    @auth
    <div 
        x-show="showLogoutModal" 
        x-cloak
        @keydown.escape.window="showLogoutModal = false"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[9999] flex items-center justify-center p-4"
        style="z-index: 99999;"
    >
        <div 
            @click.outside="showLogoutModal = false"
            x-show="showLogoutModal"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95 translate-y-3"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-3"
            class="bg-white rounded-3xl max-w-sm w-full p-6 sm:p-7 shadow-2xl border border-slate-200 text-center relative overflow-hidden"
        >
            <!-- Decorative Accent Background -->
            <div class="absolute -top-10 -right-10 w-24 h-24 bg-rose-50 rounded-full pointer-events-none"></div>

            <!-- Icon Header -->
            <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-600 border border-rose-100 flex items-center justify-center text-2xl mx-auto mb-4 shadow-sm">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
            </div>

            <!-- Title & Subtitle -->
            <h3 class="text-lg font-bold text-slate-800">Konfirmasi Keluar</h3>
            <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                Apakah Anda yakin ingin keluar dari sistem? Sesi aktif Anda akan diakhiri dan Anda perlu masuk kembali untuk mengakses portal laboratorium.
            </p>

            <!-- Buttons Action -->
            <div class="mt-6 flex items-center gap-3">
                <button 
                    type="button" 
                    @click="showLogoutModal = false" 
                    class="flex-1 py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition-colors cursor-pointer"
                >
                    Batal
                </button>
                <form action="{{ route('logout') }}" method="POST" class="flex-1 m-0">
                    @csrf
                    <button 
                        type="submit" 
                        class="w-full py-2.5 px-4 bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-rose-600/30 flex items-center justify-center gap-1.5 transition-all cursor-pointer hover:shadow-rose-600/40"
                    >
                        <i class="fa-solid fa-arrow-right-from-bracket text-[11px]"></i> Ya, Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endauth

    @stack('scripts')
    <!-- Script Loading Navigasi Antar Halaman Ringan -->
    <script>
        (function() {
            const loader = document.getElementById('top-nav-loader');
            const bar = document.getElementById('top-nav-loader-bar');
            if (!loader || !bar) return;

            let timer = null;
            let progress = 0;

            function startLoading() {
                if (timer) clearInterval(timer);
                progress = 12;
                bar.style.width = '12%';
                loader.classList.remove('opacity-0');
                loader.classList.add('opacity-100');

                timer = setInterval(function() {
                    if (progress < 85) {
                        progress += Math.max(1, (85 - progress) * 0.15);
                        bar.style.width = progress + '%';
                    }
                }, 180);
            }

            function stopLoading() {
                if (timer) {
                    clearInterval(timer);
                    timer = null;
                }
                bar.style.width = '100%';
                setTimeout(function() {
                    loader.classList.remove('opacity-100');
                    loader.classList.add('opacity-0');
                    setTimeout(function() {
                        bar.style.width = '0%';
                        progress = 0;
                    }, 250);
                }, 150);
            }

            document.addEventListener('click', function(e) {
                const link = e.target.closest('a');
                if (!link) return;

                if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
                if (link.target && link.target !== '_self') return;
                if (link.hasAttribute('download')) return;

                const href = link.getAttribute('href');
                if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:')) return;

                try {
                    const url = new URL(link.href, window.location.href);
                    if (url.origin !== window.location.origin) return;
                    if (url.pathname === window.location.pathname && url.search === window.location.search && url.hash) return;
                    if (/\.(pdf|xlsx|xls|csv|doc|docx|zip)$/i.test(url.pathname) || url.pathname.includes('/download')) return;

                    startLoading();
                } catch (err) {
                    // Ignore invalid url
                }
            });

            window.addEventListener('pageshow', function(event) {
                if (event.persisted) {
                    window.location.reload();
                }
                stopLoading();
            });

            window.addEventListener('pagehide', function() {
                stopLoading();
            });
        })();
    </script>
</body>
</html>
