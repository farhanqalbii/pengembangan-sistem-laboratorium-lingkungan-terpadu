<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scanner QR & Barcode Instrumen - LIMS 2026</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/favicon-192.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
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

        /* HTML5 QR Code Styling Overrides */
        #reader {
            border: 1px solid #e2e8f0 !important;
            border-radius: 1rem !important;
            background-color: #f8fafc !important;
            padding: 0.75rem !important;
        }
        #reader button {
            background-color: #0e263e !important;
            color: #ffffff !important;
            padding: 0.45rem 1rem !important;
            border-radius: 0.75rem !important;
            font-size: 0.75rem !important;
            font-weight: 600 !important;
            border: none !important;
            margin: 0.35rem !important;
            cursor: pointer !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.06) !important;
            transition: all 0.2s ease !important;
        }
        #reader button:hover {
            background-color: #168975 !important;
        }
        #reader select {
            background-color: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 0.75rem !important;
            padding: 0.45rem 0.75rem !important;
            font-size: 0.75rem !important;
            color: #1e293b !important;
            margin: 0.25rem !important;
            outline: none !important;
        }
        #reader a {
            color: #168975 !important;
            font-size: 0.75rem !important;
            text-decoration: underline !important;
        }
        #reader img {
            margin: 0 auto !important;
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
        <!-- Logo Header -->
        <div class="text-center">
            <a href="/" class="inline-block hover:opacity-95 transition-opacity">
                <img src="{{ asset('images/logo_lab_lgn_rgst.png') }}" alt="Laboratorium Lingkungan Terpadu" class="h-16 sm:h-20 w-auto max-w-[280px] sm:max-w-[340px] object-contain mx-auto filter drop-shadow-sm">
            </a>
        </div>

        <!-- Camera Scanner Card -->
        <div class="bg-white/95 border border-slate-200/90 backdrop-blur-2xl rounded-3xl p-6 sm:p-8 shadow-2xl shadow-slate-900/10 space-y-5 card-hover relative overflow-hidden">
            <div class="text-center border-b border-slate-100 pb-4">
                <h2 class="text-base font-extrabold text-slate-800">Arahkan Kamera ke QR / Barcode Alat</h2>
                <p class="text-xs text-slate-500 mt-1">Sistem secara otomatis mengenali dan membuka detail spesifikasi instrumen laboratorium.</p>
            </div>

            <!-- Viewport Kamera Scanner -->
            <div id="reader" class="overflow-hidden shadow-inner"></div>

            <!-- Divider -->
            <div class="relative flex py-1 items-center">
                <div class="flex-grow border-t border-slate-200"></div>
                <span class="flex-shrink mx-4 text-[11px] text-slate-400 font-bold uppercase tracking-wider">Atau Input Kode Manual</span>
                <div class="flex-grow border-t border-slate-200"></div>
            </div>

            <!-- Form Manual Kode Alat -->
            <form onsubmit="handleManualSearch(event)" class="space-y-3">
                <div>
                    <input 
                        type="text" 
                        id="equipmentCodeInput" 
                        placeholder="Masukkan Kode Alat (misal: EQP-AAS-003)" 
                        required 
                        class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 font-mono text-center transition-all uppercase tracking-wider"
                    >
                </div>
                <button 
                    type="submit" 
                    class="w-full py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-600/30 transition-all flex items-center justify-center gap-2 cursor-pointer hover:shadow-emerald-600/40"
                >
                    <i class="fa-solid fa-magnifying-glass text-xs"></i> Cari Detail Instrumen
                </button>
            </form>
        </div>

        <!-- Navigation Link -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
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
                    <i class="fa-solid fa-arrow-left text-slate-400"></i> Kembali ke Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-white hover:bg-slate-100 text-slate-700 font-bold text-xs border border-slate-300 transition-all shadow-2xs cursor-pointer">
                    <i class="fa-solid fa-arrow-left text-slate-400"></i> Kembali ke Halaman Login
                </a>
            @endauth
        </div>
    </div>

    <!-- Page Footer -->
    <footer class="text-center text-xs text-slate-400 py-3 relative z-10">
        &copy; 2026 UPTD Laboratorium Lingkungan Terpadu. Verification Engine.
    </footer>

    <script>
        function handleManualSearch(e) {
            e.preventDefault();
            const input = document.getElementById('equipmentCodeInput');
            const code = input ? input.value.trim() : '';
            if (code) {
                window.location.href = '/equipment/detail/' + encodeURIComponent(code);
            }
        }

        function onScanSuccess(decodedText, decodedResult) {
            if (decodedText) {
                let cleanCode = decodedText;
                if (decodedText.includes('/equipment/detail/')) {
                    cleanCode = decodedText.split('/equipment/detail/')[1];
                }
                window.location.href = '/equipment/detail/' + encodeURIComponent(cleanCode);
            }
        }

        try {
            const html5QrcodeScanner = new Html5QrcodeScanner(
                "reader",
                { fps: 10, qrbox: { width: 220, height: 220 } },
                /* verbose= */ false
            );
            html5QrcodeScanner.render(onScanSuccess);
        } catch (err) {
            console.warn("QR Scanner init notice:", err);
        }
    </script>
</body>
</html>
