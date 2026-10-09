
<!DOCTYPE html>
<html lang="id">
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
    
    <!-- Tailwind CSS CDN Fallback + Alpine.js -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <script>
        tailwind.config = {
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
                transform: translateY(0);
            }
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(18px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
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
    </style>
    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col" x-data="{ sidebarOpen: false, showLogoutModal: false }" x-effect="document.body.classList.toggle('overflow-hidden', showLogoutModal); document.body.classList.toggle('modal-open', showLogoutModal)">

    <div class="flex min-h-screen">
        <!-- Sidebar Navigation -->
        <aside 
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            class="fixed inset-y-0 left-0 z-40 w-64 bg-slate-900 text-slate-100 transition-transform duration-300 ease-in-out flex flex-col justify-between border-r border-slate-800 shadow-xl"
        >
            <div>
                <!-- Brand Header -->
                <div class="p-3.5 sm:p-4 flex items-center justify-between border-b border-slate-800 bg-slate-950/70">
                    <a href="/" class="block flex-1 mr-2">
                        <div class="flex items-center justify-center py-0.5 px-1">
                            <img src="{{ asset('images/logo_lab_navbar_clean.png') }}" alt="Laboratorium Lingkungan Terpadu" class="h-9 sm:h-10 w-auto max-w-full object-contain filter drop-shadow">
                        </div>
                    </a>
                    <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white p-1">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <!-- Navigation Items based on Role -->
                <nav class="p-3 space-y-0.5 overflow-y-auto max-h-[calc(100vh-85px)] custom-scrollbar">
                    @auth
                        @php
                            $unreadNotifs = \App\Models\Notification::where('user_id', auth()->id())->whereNull('read_at')->latest()->get();
                            
                            $notifSampleCount = $unreadNotifs->filter(fn($n) => (str_contains($n->link ?? '', 'sample') || str_contains(strtolower(($n->title ?? '').' '.($n->message ?? '')), 'sampel') || str_contains(strtolower(($n->title ?? '').' '.($n->message ?? '')), 'pengujian')) && !str_contains(strtolower(($n->title ?? '').' '.($n->message ?? '')), 'sertifikat'))->count();
                            
                            $notifBorrowingCount = $unreadNotifs->filter(fn($n) => str_contains($n->link ?? '', 'borrow') || str_contains(strtolower(($n->title ?? '').' '.($n->message ?? '')), 'pinjam'))->count();
                            
                            $notifCoaCount = $unreadNotifs->filter(fn($n) => str_contains($n->link ?? '', 'certificate') || str_contains(strtolower(($n->title ?? '').' '.($n->message ?? '')), 'coa') || str_contains(strtolower(($n->title ?? '').' '.($n->message ?? '')), 'sertifikat'))->count();
                            
                            $notifPaymentCount = $unreadNotifs->filter(fn($n) => str_contains($n->link ?? '', 'payment') || str_contains(strtolower(($n->title ?? '').' '.($n->message ?? '')), 'bayar') || str_contains(strtolower(($n->title ?? '').' '.($n->message ?? '')), 'invoice') || str_contains(strtolower(($n->title ?? '').' '.($n->message ?? '')), 'tagihan'))->count();

                            $notifEquipCount = $unreadNotifs->filter(fn($n) => str_contains($n->link ?? '', 'equipment') || str_contains(strtolower(($n->title ?? '').' '.($n->message ?? '')), 'stok'))->count();

                            $notifVerifCount = $unreadNotifs->filter(fn($n) => str_contains($n->link ?? '', 'verifikasi') || str_contains(strtolower(($n->title ?? '').' '.($n->message ?? '')), 'verifikasi'))->count();

                            $notifFinanceCount = $unreadNotifs->filter(fn($n) => str_contains($n->link ?? '', 'finance') || str_contains(strtolower(($n->title ?? '').' '.($n->message ?? '')), 'refund') || str_contains(strtolower(($n->title ?? '').' '.($n->message ?? '')), 'keuangan'))->count();
                        @endphp

                        @if(auth()->user()->isCustomer())
                            <div class="px-3 pt-2 pb-1 text-[10px] font-bold uppercase text-slate-400 tracking-wider">Customer Portal</div>
                            <a href="{{ route('customer.dashboard') }}" class="flex items-center space-x-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('customer.dashboard') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <i class="fa-solid fa-chart-line w-4 text-center"></i>
                                <span>Dashboard</span>
                            </a>
                            <a href="{{ route('customer.samples.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('customer.samples.*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <div class="flex items-center space-x-2.5">
                                    <i class="fa-solid fa-vial-circle-check w-4 text-center"></i>
                                    <span>Pengujian Sampel</span>
                                </div>
                                @if($notifSampleCount > 0)
                                    <span class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-extrabold rounded-full {{ request()->routeIs('customer.samples.*') ? 'bg-white text-emerald-800' : 'bg-rose-500 text-white' }} min-w-[18px] h-[18px] shadow-xs">
                                        {{ $notifSampleCount }}
                                    </span>
                                @endif
                            </a>
                            <a href="{{ route('customer.borrowings.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('customer.borrowings.*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <div class="flex items-center space-x-2.5">
                                    <i class="fa-solid fa-toolbox w-4 text-center"></i>
                                    <span>Peminjaman Alat</span>
                                </div>
                                @if($notifBorrowingCount > 0)
                                    <span class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-extrabold rounded-full {{ request()->routeIs('customer.borrowings.*') ? 'bg-white text-emerald-800' : 'bg-rose-500 text-white' }} min-w-[18px] h-[18px] shadow-xs">
                                        {{ $notifBorrowingCount }}
                                    </span>
                                @endif
                            </a>
                            <a href="{{ route('customer.certificates.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('customer.certificates.*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <div class="flex items-center space-x-2.5">
                                    <i class="fa-solid fa-file-contract w-4 text-center"></i>
                                    <span>Sertifikat (COA)</span>
                                </div>
                                @if($notifCoaCount > 0)
                                    <span class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-extrabold rounded-full {{ request()->routeIs('customer.certificates.*') ? 'bg-white text-emerald-800' : 'bg-rose-500 text-white' }} min-w-[18px] h-[18px] shadow-xs">
                                        {{ $notifCoaCount }}
                                    </span>
                                @endif
                            </a>
                            <a href="{{ route('customer.payments.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('customer.payments.*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <div class="flex items-center space-x-2.5">
                                    <i class="fa-solid fa-file-invoice-dollar w-4 text-center"></i>
                                    <span>Invoice & Pembayaran</span>
                                </div>
                                @if($notifPaymentCount > 0)
                                    <span class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-extrabold rounded-full {{ request()->routeIs('customer.payments.*') ? 'bg-white text-emerald-800' : 'bg-rose-500 text-white' }} min-w-[18px] h-[18px] shadow-xs">
                                        {{ $notifPaymentCount }}
                                    </span>
                                @endif
                            </a>
                            <a href="{{ route('customer.satisfaction.create') }}" class="flex items-center space-x-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('customer.satisfaction.*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <i class="fa-solid fa-face-smile w-4 text-center"></i>
                                <span>Kepuasan Pelanggan</span>
                            </a>
                        @elseif(auth()->user()->isStaff())
                            <div class="px-3 pt-2 pb-1 text-[10px] font-bold uppercase text-slate-400 tracking-wider">Staff / Analis Portal</div>
                            <a href="{{ route('staff.dashboard') }}" class="flex items-center space-x-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('staff.dashboard') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <i class="fa-solid fa-table-cells-large w-4 text-center"></i>
                                <span>Dashboard Staf</span>
                            </a>
                            <a href="{{ route('staff.samples.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('staff.samples.*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <div class="flex items-center space-x-2.5">
                                    <i class="fa-solid fa-vials w-4 text-center"></i>
                                    <span>Manajemen Sampel</span>
                                </div>
                                @if($notifSampleCount > 0)
                                    <span class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-extrabold rounded-full {{ request()->routeIs('staff.samples.*') ? 'bg-white text-emerald-800' : 'bg-rose-500 text-white' }} min-w-[18px] h-[18px] shadow-xs">
                                        {{ $notifSampleCount }}
                                    </span>
                                @endif
                            </a>
                            <a href="{{ route('staff.equipment.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('staff.equipment.*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <div class="flex items-center space-x-2.5">
                                    <i class="fa-solid fa-microscope w-4 text-center"></i>
                                    <span>Inventori Alat</span>
                                </div>
                                @if($notifEquipCount > 0)
                                    <span class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-extrabold rounded-full {{ request()->routeIs('staff.equipment.*') ? 'bg-white text-emerald-800' : 'bg-rose-500 text-white' }} min-w-[18px] h-[18px] shadow-xs">
                                        {{ $notifEquipCount }}
                                    </span>
                                @endif
                            </a>
                            <a href="{{ route('staff.borrowings.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('staff.borrowings.*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <div class="flex items-center space-x-2.5">
                                    <i class="fa-solid fa-hand-holding-hand w-4 text-center"></i>
                                    <span>Pengembalian Alat</span>
                                </div>
                                @if($notifBorrowingCount > 0)
                                    <span class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-extrabold rounded-full {{ request()->routeIs('staff.borrowings.*') ? 'bg-white text-emerald-800' : 'bg-rose-500 text-white' }} min-w-[18px] h-[18px] shadow-xs">
                                        {{ $notifBorrowingCount }}
                                    </span>
                                @endif
                            </a>
                        @elseif(auth()->user()->isManager())
                            <div class="px-3 pt-2 pb-1 text-[10px] font-bold uppercase text-slate-400 tracking-wider">Manajer Lab Portal</div>
                            <a href="{{ route('manager.dashboard') }}" class="flex items-center space-x-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('manager.dashboard') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <i class="fa-solid fa-chart-pie w-4 text-center"></i>
                                <span>Dashboard Manajer</span>
                            </a>
                            <a href="{{ route('manager.verifications.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('manager.verifications.*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <div class="flex items-center space-x-2.5">
                                    <i class="fa-solid fa-user-check w-4 text-center"></i>
                                    <span>Verifikasi Technical</span>
                                </div>
                                @if($notifVerifCount > 0)
                                    <span class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-extrabold rounded-full {{ request()->routeIs('manager.verifications.*') ? 'bg-white text-emerald-800' : 'bg-rose-500 text-white' }} min-w-[18px] h-[18px] shadow-xs">
                                        {{ $notifVerifCount }}
                                    </span>
                                @endif
                            </a>
                        @elseif(auth()->user()->isAdmin())
                            <div class="px-3 pt-2 pb-1 text-[10px] font-bold uppercase text-slate-400 tracking-wider">Administrator Portal</div>
                            <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-2.5 px-3 py-1.5 sm:py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <i class="fa-solid fa-gauge-high w-4 text-center"></i>
                                <span>Dashboard Admin</span>
                            </a>
                            <a href="{{ route('admin.users.index') }}" class="flex items-center space-x-2.5 px-3 py-1.5 sm:py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.users.*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <i class="fa-solid fa-users-gear w-4 text-center"></i>
                                <span>Manajemen Pengguna</span>
                            </a>
                            <a href="{{ route('admin.borrowings.index') }}" class="flex items-center justify-between px-3 py-1.5 sm:py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.borrowings.*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <div class="flex items-center space-x-2.5">
                                    <i class="fa-solid fa-boxes-packing w-4 text-center"></i>
                                    <span>Approval Peminjaman</span>
                                </div>
                                @if($notifBorrowingCount > 0)
                                    <span class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-extrabold rounded-full {{ request()->routeIs('admin.borrowings.*') ? 'bg-white text-emerald-800' : 'bg-rose-500 text-white' }} min-w-[18px] h-[18px] shadow-xs">
                                        {{ $notifBorrowingCount }}
                                    </span>
                                @endif
                            </a>
                            <a href="{{ route('admin.finance.index') }}" class="flex items-center justify-between px-3 py-1.5 sm:py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.finance.*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <div class="flex items-center space-x-2.5">
                                    <i class="fa-solid fa-wallet w-4 text-center"></i>
                                    <span>Keuangan & Refund</span>
                                </div>
                                @if($notifFinanceCount > 0)
                                    <span class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-extrabold rounded-full {{ request()->routeIs('admin.finance.*') ? 'bg-white text-emerald-800' : 'bg-rose-500 text-white' }} min-w-[18px] h-[18px] shadow-xs">
                                        {{ $notifFinanceCount }}
                                    </span>
                                @endif
                            </a>
                            <a href="{{ route('admin.parameters.index') }}" class="flex items-center space-x-2.5 px-3 py-1.5 sm:py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.parameters.*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <i class="fa-solid fa-sliders w-4 text-center"></i>
                                <span>Parameter & Baku Mutu</span>
                            </a>
                            <a href="{{ route('staff.equipment.index') }}" class="flex items-center justify-between px-3 py-1.5 sm:py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('staff.equipment.*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <div class="flex items-center space-x-2.5">
                                    <i class="fa-solid fa-microscope w-4 text-center"></i>
                                    <span>Inventori Alat</span>
                                </div>
                                @if($notifEquipCount > 0)
                                    <span class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-extrabold rounded-full {{ request()->routeIs('staff.equipment.*') ? 'bg-white text-emerald-800' : 'bg-rose-500 text-white' }} min-w-[18px] h-[18px] shadow-xs">
                                        {{ $notifEquipCount }}
                                    </span>
                                @endif
                            </a>
                            <div class="px-3 pt-3 pb-1 text-[10px] font-bold uppercase text-slate-400 tracking-wider">Laporan & Audit</div>
                            <a href="{{ route('admin.reports.samples') }}" class="flex items-center space-x-2.5 px-3 py-1.5 sm:py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.reports.samples') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <i class="fa-solid fa-file-pdf w-4 text-center"></i>
                                <span>Laporan Sampel</span>
                            </a>
                            <a href="{{ route('admin.reports.inventory') }}" class="flex items-center space-x-2.5 px-3 py-1.5 sm:py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.reports.inventory') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <i class="fa-solid fa-file-csv w-4 text-center"></i>
                                <span>Laporan Inventori</span>
                            </a>
                            <a href="{{ route('admin.reports.borrowings') }}" class="flex items-center space-x-2.5 px-3 py-1.5 sm:py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.reports.borrowings') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <i class="fa-solid fa-file-lines w-4 text-center"></i>
                                <span>Laporan Peminjaman</span>
                            </a>
                            <a href="{{ route('admin.reports.financial') }}" class="flex items-center space-x-2.5 px-3 py-1.5 sm:py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.reports.financial') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <i class="fa-solid fa-receipt w-4 text-center"></i>
                                <span>Laporan Keuangan</span>
                            </a>
                            <a href="{{ route('admin.reports.satisfaction') }}" class="flex items-center space-x-2.5 px-3 py-1.5 sm:py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.reports.satisfaction') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <i class="fa-solid fa-star w-4 text-center"></i>
                                <span>Laporan Kepuasan</span>
                            </a>
                            <a href="{{ route('admin.activity-logs.index') }}" class="flex items-center space-x-2.5 px-3 py-1.5 sm:py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.activity-logs.*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <i class="fa-solid fa-shield-cat w-4 text-center"></i>
                                <span>Log Aktivitas</span>
                            </a>
                        @endif
                    @endauth

                    <div class="px-3 pt-3 pb-1 text-[10px] font-bold uppercase text-slate-400 tracking-wider">Tools</div>
                    <a href="{{ route('equipment.scan') }}" class="flex items-center space-x-2.5 px-3 py-1.5 sm:py-2 rounded-xl text-xs font-semibold text-slate-300 hover:bg-slate-800 hover:text-white transition-all">
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
                    <div class="flex items-center space-x-4">
                        <!-- Notification Center -->
                        @auth
                        @php
                            $unreadNotifs = $unreadNotifs ?? \App\Models\Notification::where('user_id', auth()->id())->whereNull('read_at')->latest()->get();
                        @endphp
                        <div class="relative" x-data="{ 
                            open: false,
                            unreadCount: {{ $unreadNotifs->count() }},
                            readAllDone: false,
                            marking: false,
                            markAllRead() {
                                if (this.marking || this.unreadCount === 0) return;
                                this.marking = true;
                                fetch('{{ route('notifications.read-all') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json'
                                    }
                                })
                                .then(res => res.json())
                                .then(() => {
                                    this.marking = false;
                                    this.unreadCount = 0;
                                    this.readAllDone = true;
                                })
                                .catch(() => {
                                    this.marking = false;
                                    this.unreadCount = 0;
                                    this.readAllDone = true;
                                });
                            }
                        }">
                            <button @click="open = !open" class="relative p-2 text-slate-600 hover:text-emerald-600 hover:bg-slate-100 rounded-lg transition-all cursor-pointer">
                                <i class="fa-solid fa-bell text-lg"></i>
                                <template x-if="unreadCount > 0 && !readAllDone">
                                    <span class="absolute top-1 right-1 w-2.5 h-2.5 bg-rose-500 rounded-full ring-2 ring-white"></span>
                                </template>
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
                                class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-xl shadow-2xl border border-slate-200 py-2 z-50 overflow-hidden"
                            >
                                <div class="px-4 py-2 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                                    <span class="font-bold text-xs uppercase tracking-wider text-slate-700">
                                        Notifikasi (<span x-text="readAllDone ? 0 : unreadCount"></span>)
                                    </span>
                                    <div class="flex items-center gap-2">
                                        <template x-if="readAllDone">
                                            <span class="text-[11px] text-emerald-600 font-semibold flex items-center gap-1">
                                                <i class="fa-solid fa-check"></i> Sudah dibaca
                                            </span>
                                        </template>
                                        <template x-if="!readAllDone && unreadCount > 0">
                                            <button type="button" @click="markAllRead()" :disabled="marking" class="text-[11px] text-emerald-600 hover:text-emerald-700 hover:underline font-medium cursor-pointer">
                                                <span x-text="marking ? 'Menandai...' : 'Tandai Dibaca'"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                                <div class="max-h-80 overflow-y-auto divide-y divide-slate-100">
                                    <template x-if="readAllDone || unreadCount === 0">
                                        <div class="p-6 text-center text-slate-400 text-xs">Tidak ada notifikasi baru</div>
                                    </template>
                                    <template x-if="!readAllDone && unreadCount > 0">
                                        <div>
                                            @foreach($unreadNotifs as $n)
                                                <a href="{{ route('notifications.read', $n->id) }}" class="block p-3.5 hover:bg-slate-50 transition-colors">
                                                    <p class="font-semibold text-xs text-slate-800 mb-0.5">{{ $n->title }}</p>
                                                    <p class="text-xs text-slate-600 line-clamp-2">{{ $n->message }}</p>
                                                    <span class="text-[10px] text-slate-400 mt-1 block">{{ $n->created_at->diffForHumans() }}</span>
                                                </a>
                                            @endforeach
                                        </div>
                                    </template>
                                </div>
                                <a href="{{ route('notifications.index') }}" class="block text-center py-2.5 bg-slate-50 border-t border-slate-100 text-xs font-semibold text-slate-600 hover:text-emerald-600">
                                    Lihat Semua Notifikasi
                                </a>
                            </div>
                        </div>
                        @endauth

                        <!-- User Profile Dropdown -->
                        @auth
                        <div class="relative" x-data="{ userMenuOpen: false }">
                            <button @click="userMenuOpen = !userMenuOpen" type="button" class="flex items-center gap-2 p-1 sm:px-2.5 sm:py-1.5 rounded-full hover:bg-slate-100 transition-all focus:outline-none focus:ring-2 focus:ring-emerald-500/30 group">
                                <div class="w-8 h-8 rounded-full overflow-hidden bg-slate-100 border border-slate-300 group-hover:border-emerald-500 flex items-center justify-center font-bold text-emerald-700 text-xs flex-shrink-0 transition-colors shadow-sm">
                                    @if(auth()->user()->avatar_url)
                                        <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                                    @else
                                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                                    @endif
                                </div>
                                <span class="hidden md:inline text-xs font-semibold text-slate-700 group-hover:text-emerald-700 transition-colors max-w-[130px] truncate">
                                    {{ auth()->user()->name }}
                                </span>
                            </button>

                            <!-- Dropdown Card -->
                            <div 
                                x-show="userMenuOpen" 
                                @click.outside="userMenuOpen = false" 
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                x-cloak
                                class="absolute right-0 mt-2.5 w-72 bg-[#0e1e2e] text-white rounded-2xl shadow-2xl border border-slate-700/80 p-2 z-50 overflow-hidden"
                            >
                                <!-- Header Profile Info -->
                                <div class="p-3 bg-slate-900/70 rounded-xl mb-1 flex items-center gap-3 border border-slate-800">
                                    <div class="w-11 h-11 rounded-xl overflow-hidden bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-cyan-300 text-sm flex-shrink-0 shadow-inner">
                                        @if(auth()->user()->avatar_url)
                                            <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                                        @else
                                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                                        @endif
                                    </div>
                                    <div class="overflow-hidden flex-1 min-w-0">
                                        <div class="flex items-center gap-1.5 mb-0.5">
                                            <p class="text-xs font-bold text-white truncate">{{ auth()->user()->name }}</p>
                                            <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold capitalize flex-shrink-0
                                                {{ auth()->user()->role === 'admin' ? 'bg-purple-500/20 text-purple-300 border border-purple-400/30' : '' }}
                                                {{ auth()->user()->role === 'manager' ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-400/30' : '' }}
                                                {{ auth()->user()->role === 'staff' ? 'bg-blue-500/20 text-blue-300 border border-blue-400/30' : '' }}
                                                {{ auth()->user()->role === 'customer' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-400/30' : '' }}
                                            ">
                                                {{ auth()->user()->role }}
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-slate-400 truncate">{{ auth()->user()->email }}</p>
                                    </div>
                                </div>

                                <!-- Menu Items -->
                                <div class="space-y-0.5 text-xs pt-1">
                                    <a href="{{ route('profile.edit') }}" @click="userMenuOpen = false" class="flex items-center gap-3 px-3 py-2.5 font-medium text-slate-300 hover:text-white hover:bg-slate-800/80 rounded-xl transition-all group">
                                        <i class="fa-regular fa-user w-4 text-center text-slate-400 group-hover:text-emerald-400 transition-colors"></i>
                                        <span>Profile</span>
                                    </a>

                                    <div class="h-px bg-slate-800 my-1"></div>

                                    <button type="button" @click="userMenuOpen = false; showLogoutModal = true" class="w-full flex items-center gap-3 px-3 py-2.5 font-medium text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 rounded-xl transition-all text-left cursor-pointer">
                                        <i class="fa-solid fa-arrow-right-from-bracket w-4 text-center text-rose-400"></i>
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
</body>
</html>
