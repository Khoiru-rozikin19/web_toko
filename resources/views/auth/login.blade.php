@extends('layouts.app')

@section('title', 'Masuk ke Akun - Tokonet H2H')

@section('content')
<div class="max-w-md mx-auto py-6 sm:py-12">
    <div class="glass-card rounded-2xl p-6 sm:p-8 shadow-2xl relative overflow-hidden">
        <!-- Background Gradient Glow -->
        <div class="absolute -top-12 -right-12 w-40 h-40 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-indigo-600/20 border border-indigo-500/30 text-indigo-400 mb-3">
                <i data-lucide="log-in" class="w-6 h-6"></i>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Selamat Datang Kembali</h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">Masuk dengan Nomor HP atau Email Anda</p>
        </div>

        <form action="{{ route('login') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="login" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Nomor HP atau Email</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                        <i data-lucide="user" class="w-4 h-4"></i>
                    </span>
                    <input type="text" name="login" id="login" value="{{ old('login') }}" required autofocus placeholder="08123456789 atau user@mail.com" class="w-full bg-slate-900/90 border border-slate-700/80 rounded-xl pl-10 pr-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                </div>
                @error('login')
                    <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">Password</label>
                </div>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                        <i data-lucide="lock" class="w-4 h-4"></i>
                    </span>
                    <input type="password" name="password" id="password" required placeholder="••••••••" class="w-full bg-slate-900/90 border border-slate-700/80 rounded-xl pl-10 pr-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                </div>
                @error('password')
                    <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between text-xs pt-1">
                <label class="flex items-center gap-2 cursor-pointer text-slate-400 hover:text-slate-300">
                    <input type="checkbox" name="remember" class="rounded bg-slate-900 border-slate-700 text-indigo-600 focus:ring-0">
                    <span>Ingat Saya</span>
                </label>
            </div>

            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-3 px-4 rounded-xl transition shadow-lg shadow-indigo-600/30 flex items-center justify-center gap-2 text-sm mt-2">
                <span>Masuk Sekarang</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </button>
        </form>

        <div class="mt-6 pt-6 border-t border-slate-800 text-center text-xs text-slate-400">
            Belum memiliki akun?
            <a href="{{ route('register') }}" class="font-semibold text-indigo-400 hover:text-indigo-300 ml-1">Daftar Akun Baru</a>
        </div>

        <!-- Demo Account Helper Box -->
        <div class="mt-6 p-3.5 rounded-xl bg-slate-900/80 border border-slate-800 text-xs text-slate-400">
            <div class="font-semibold text-slate-300 mb-1 flex items-center gap-1.5">
                <i data-lucide="info" class="w-3.5 h-3.5 text-indigo-400"></i> Akun Uji Coba:
            </div>
            <div class="grid grid-cols-2 gap-2 text-[11px] mt-2">
                <div class="bg-slate-950 p-2 rounded-lg border border-slate-800">
                    <span class="text-indigo-300 font-bold block">User Biasa:</span>
                    <span>user@webtoko.com</span><br>
                    <span class="text-slate-500">Pass: user123</span>
                </div>
                <div class="bg-slate-950 p-2 rounded-lg border border-slate-800">
                    <span class="text-amber-300 font-bold block">Administrator:</span>
                    <span>admin@webtoko.com</span><br>
                    <span class="text-slate-500">Pass: admin123</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
