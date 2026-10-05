<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Tokonet H2H - Server Paket Data & Pulsa Termurah')</title>
    <meta name="description" content="Layanan H2H Paket Data, Pulsa, Token Listrik PLN otomatis 24 Jam dengan metode QRIS Dinamis dan konfirmasi instan.">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            900: '#312e81',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .glass-card {
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .glass-header {
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.07);
        }
        .glow-brand {
            box-shadow: 0 0 35px -5px rgba(79, 70, 229, 0.35);
        }
        /* Hide scrollbars for cleaner horizontal pills */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>
<body class="h-full text-slate-100 antialiased selection:bg-indigo-500 selection:text-white flex flex-col min-h-screen">
    <!-- Navbar Header -->
    <header class="sticky top-0 z-40 glass-header">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <!-- Brand Logo -->
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center text-white font-black text-xl shadow-lg shadow-indigo-500/30 group-hover:scale-105 transition">
                    <i data-lucide="zap" class="w-5 h-5"></i>
                </div>
                <div>
                    <span class="text-lg font-extrabold tracking-tight bg-gradient-to-r from-white via-slate-100 to-indigo-200 bg-clip-text text-transparent">TOKOPULSA</span>
                    <span class="text-[10px] block font-semibold text-indigo-400 uppercase tracking-wider">H2H Fast Server</span>
                </div>
            </a>

            <!-- Right Actions (Desktop) -->
            <div class="hidden md:flex items-center gap-4">
                <a href="{{ route('dashboard') }}" class="text-sm font-medium text-slate-300 hover:text-white transition flex items-center gap-1.5 px-3 py-2 rounded-lg hover:bg-slate-800/60">
                    <i data-lucide="layout-grid" class="w-4 h-4 text-indigo-400"></i> Produk
                </a>

                @auth
                    <a href="{{ route('history') }}" class="text-sm font-medium text-slate-300 hover:text-white transition flex items-center gap-1.5 px-3 py-2 rounded-lg hover:bg-slate-800/60">
                        <i data-lucide="receipt" class="w-4 h-4 text-emerald-400"></i> Riwayat
                    </a>

                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="text-sm font-medium text-amber-300 hover:text-amber-200 transition flex items-center gap-1.5 px-3 py-2 rounded-lg bg-amber-500/10 border border-amber-500/30">
                            <i data-lucide="shield-check" class="w-4 h-4 text-amber-400"></i> Admin Panel
                        </a>
                    @endif

                    <!-- User Balance Card -->
                    <div class="flex items-center gap-3 bg-slate-900/90 border border-indigo-500/20 rounded-xl px-3.5 py-1.5">
                        <div class="text-right">
                            <span class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold block">Saldo Anda</span>
                            <span class="text-sm font-bold text-emerald-400">Rp {{ number_format(auth()->user()->balance, 0, ',', '.') }}</span>
                        </div>
                        <a href="{{ route('topup.index') }}" class="bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition shadow-md shadow-indigo-600/30 flex items-center gap-1">
                            <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i> Topup
                        </a>
                    </div>

                    <!-- User Dropdown / Logout -->
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="p-2 text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition" title="Logout">
                            <i data-lucide="log-out" class="w-5 h-5"></i>
                        </button>
                    </form>
                @else
                    <div class="flex items-center gap-2">
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-300 hover:text-white px-4 py-2 rounded-xl hover:bg-slate-800 transition">
                            Masuk
                        </a>
                        <a href="{{ route('register') }}" class="text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 px-4 py-2 rounded-xl transition shadow-md shadow-indigo-600/30">
                            Daftar
                        </a>
                    </div>
                @endauth
            </div>

            <!-- Mobile Balance / Auth Header Info -->
            <div class="flex md:hidden items-center gap-2">
                @auth
                    <a href="{{ route('topup.index') }}" class="flex items-center gap-1.5 bg-slate-900 border border-indigo-500/30 px-3 py-1.5 rounded-xl">
                        <i data-lucide="wallet" class="w-4 h-4 text-emerald-400"></i>
                        <span class="text-xs font-bold text-emerald-400">Rp {{ number_format(auth()->user()->balance, 0, ',', '.') }}</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-xs font-semibold text-white bg-indigo-600 px-3 py-1.5 rounded-lg">
                        Masuk
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Notification Toasts -->
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 w-full mt-4">
        @if(session('success'))
            <div class="p-4 mb-4 rounded-xl bg-emerald-950/70 border border-emerald-500/30 text-emerald-300 flex items-center justify-between text-sm shadow-lg backdrop-blur">
                <div class="flex items-center gap-3">
                    <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-400 shrink-0"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-emerald-200"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 mb-4 rounded-xl bg-rose-950/70 border border-rose-500/30 text-rose-300 flex items-center justify-between text-sm shadow-lg backdrop-blur">
                <div class="flex items-center gap-3">
                    <i data-lucide="alert-circle" class="w-5 h-5 text-rose-400 shrink-0"></i>
                    <span>{{ session('error') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-rose-400 hover:text-rose-200"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>
        @endif
    </div>

    <!-- Main Content Area -->
    <main class="flex-1 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 w-full mb-16 md:mb-8">
        @yield('content')
    </main>

    <!-- Bottom Navigation Bar for Mobile -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-slate-900/95 backdrop-blur-lg border-t border-slate-800 px-2 py-2">
        <div class="flex items-center justify-around">
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center py-1 px-3 rounded-lg {{ request()->routeIs('dashboard') ? 'text-indigo-400' : 'text-slate-400 hover:text-slate-200' }}">
                <i data-lucide="home" class="w-5 h-5 mb-0.5"></i>
                <span class="text-[10px] font-semibold">Beranda</span>
            </a>

            <a href="{{ route('topup.index') }}" class="flex flex-col items-center py-1 px-3 rounded-lg {{ request()->routeIs('topup.*') ? 'text-indigo-400' : 'text-slate-400 hover:text-slate-200' }}">
                <i data-lucide="wallet" class="w-5 h-5 mb-0.5"></i>
                <span class="text-[10px] font-semibold">Topup</span>
            </a>

            <a href="{{ route('history') }}" class="flex flex-col items-center py-1 px-3 rounded-lg {{ request()->routeIs('history') ? 'text-indigo-400' : 'text-slate-400 hover:text-slate-200' }}">
                <i data-lucide="history" class="w-5 h-5 mb-0.5"></i>
                <span class="text-[10px] font-semibold">Riwayat</span>
            </a>

            @auth
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="flex flex-col items-center py-1 px-3 rounded-lg text-amber-400">
                        <i data-lucide="shield" class="w-5 h-5 mb-0.5"></i>
                        <span class="text-[10px] font-semibold">Admin</span>
                    </a>
                @else
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="flex flex-col items-center py-1 px-3 rounded-lg text-slate-400 hover:text-rose-400">
                            <i data-lucide="log-out" class="w-5 h-5 mb-0.5"></i>
                            <span class="text-[10px] font-semibold">Keluar</span>
                        </button>
                    </form>
                @endif
            @else
                <a href="{{ route('login') }}" class="flex flex-col items-center py-1 px-3 rounded-lg text-slate-400 hover:text-white">
                    <i data-lucide="user" class="w-5 h-5 mb-0.5"></i>
                    <span class="text-[10px] font-semibold">Akun</span>
                </a>
            @endauth
        </div>
    </nav>

    <!-- Footer for Desktop -->
    <footer class="hidden md:block border-t border-slate-800/80 py-6 text-center text-xs text-slate-500">
        <div class="max-w-5xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-3">
            <span>&copy; {{ date('Y') }} Tokonet H2H. Sistem Transaksi Otomatis 24 Jam.</span>
            <div class="flex items-center gap-4 text-slate-400">
                <span class="flex items-center gap-1"><i data-lucide="check" class="w-3.5 h-3.5 text-emerald-400"></i> API Okeconnect H2H</span>
                <span class="flex items-center gap-1"><i data-lucide="qr-code" class="w-3.5 h-3.5 text-indigo-400"></i> QRIS Dinamis Instan</span>
                <span class="flex items-center gap-1"><i data-lucide="bot" class="w-3.5 h-3.5 text-sky-400"></i> Bot Telegram Verifier</span>
            </div>
        </div>
    </footer>

    <script>
        lucide.createIcons();
    </script>
    @stack('scripts')
</body>
</html>
