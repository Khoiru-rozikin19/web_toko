@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Dashboard Administrator</h1>
            <p class="text-xs sm:text-sm text-slate-400">Ringkasan operasional H2H server, transaksi, & saldo web</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-xs font-semibold text-slate-300">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span> H2H: {{ $h2hBalance['formatted_balance'] }}
            </span>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Revenue -->
        <div class="glass-sidebar rounded-2xl p-5 border border-slate-800 relative overflow-hidden">
            <div class="flex justify-between items-start">
                <div>
                    <span class="text-xs font-semibold text-slate-400">Total Omset Sukses</span>
                    <h3 class="text-xl font-black text-white mt-1">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                    <i data-lucide="trending-up" class="w-5 h-5"></i>
                </div>
            </div>
            <span class="text-[11px] text-emerald-400 font-bold mt-2 block">Profit: Rp {{ number_format($totalProfit, 0, ',', '.') }}</span>
        </div>

        <!-- Transactions -->
        <div class="glass-sidebar rounded-2xl p-5 border border-slate-800 relative overflow-hidden">
            <div class="flex justify-between items-start">
                <div>
                    <span class="text-xs font-semibold text-slate-400">Total Transaksi H2H</span>
                    <h3 class="text-xl font-black text-white mt-1">{{ number_format($totalTransactions) }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center">
                    <i data-lucide="arrow-left-right" class="w-5 h-5"></i>
                </div>
            </div>
            <span class="text-[11px] text-slate-400 mt-2 block">{{ $todayTransactions }} Transaksi hari ini</span>
        </div>

        <!-- Pending Topups -->
        <div class="glass-sidebar rounded-2xl p-5 border border-slate-800 relative overflow-hidden">
            <div class="flex justify-between items-start">
                <div>
                    <span class="text-xs font-semibold text-slate-400">Topup Pending</span>
                    <h3 class="text-xl font-black {{ $pendingTopups > 0 ? 'text-amber-400 animate-pulse' : 'text-white' }} mt-1">{{ $pendingTopups }} Permintaan</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center">
                    <i data-lucide="clock" class="w-5 h-5"></i>
                </div>
            </div>
            <a href="{{ route('admin.topups') }}" class="text-[11px] text-indigo-400 hover:underline font-semibold mt-2 inline-block">Kelola Antrean Topup &rarr;</a>
        </div>

        <!-- Total Users -->
        <div class="glass-sidebar rounded-2xl p-5 border border-slate-800 relative overflow-hidden">
            <div class="flex justify-between items-start">
                <div>
                    <span class="text-xs font-semibold text-slate-400">Total Pengguna</span>
                    <h3 class="text-xl font-black text-white mt-1">{{ number_format($totalUsers) }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-sky-500/10 text-sky-400 flex items-center justify-center">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </div>
            </div>
            <a href="{{ route('admin.users') }}" class="text-[11px] text-indigo-400 hover:underline font-semibold mt-2 inline-block">Lihat Semua User &rarr;</a>
        </div>
    </div>

    <!-- Two Column: Recent Transactions & Recent Topups -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Transactions -->
        <div class="glass-sidebar rounded-2xl p-5 border border-slate-800">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold text-sm text-white flex items-center gap-2">
                    <i data-lucide="receipt" class="w-4 h-4 text-indigo-400"></i> Transaksi Terkini
                </h2>
                <a href="{{ route('admin.transactions') }}" class="text-xs text-indigo-400 hover:underline font-semibold">Semua &rarr;</a>
            </div>

            <div class="divide-y divide-slate-800/80 text-xs">
                @forelse($recentTransactions as $trx)
                    <div class="py-3 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-white block">{{ $trx->product_name }}</span>
                            <span class="text-[10px] text-slate-400 font-mono">{{ $trx->destination_number }} ({{ $trx->user->name ?? 'User' }})</span>
                        </div>
                        <div class="text-right">
                            <span class="font-bold text-white block">Rp {{ number_format($trx->amount, 0, ',', '.') }}</span>
                            @if($trx->status === 'success')
                                <span class="text-[10px] text-emerald-400 font-bold">SUKSES</span>
                            @elseif($trx->status === 'processing' || $trx->status === 'pending')
                                <span class="text-[10px] text-amber-400 font-bold">PROSES</span>
                            @else
                                <span class="text-[10px] text-rose-400 font-bold">GAGAL</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-center py-6 text-slate-500 text-xs">Belum ada transaksi</p>
                @endforelse
            </div>
        </div>

        <!-- Recent Topups -->
        <div class="glass-sidebar rounded-2xl p-5 border border-slate-800">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold text-sm text-white flex items-center gap-2">
                    <i data-lucide="wallet" class="w-4 h-4 text-amber-400"></i> Permintaan Topup Saldo
                </h2>
                <a href="{{ route('admin.topups') }}" class="text-xs text-indigo-400 hover:underline font-semibold">Semua &rarr;</a>
            </div>

            <div class="divide-y divide-slate-800/80 text-xs">
                @forelse($recentTopups as $topup)
                    <div class="py-3 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-white block">{{ $topup->user->name ?? 'User' }}</span>
                            <span class="text-[10px] text-slate-400 font-mono">{{ $topup->invoice_number }}</span>
                        </div>
                        <div class="text-right">
                            <span class="font-bold text-emerald-400 block">Rp {{ number_format($topup->total_amount, 0, ',', '.') }}</span>
                            @if($topup->status === 'paid')
                                <span class="text-[10px] text-emerald-400 font-bold">LUNAS</span>
                            @elseif($topup->status === 'pending')
                                <span class="text-[10px] text-amber-400 font-bold">MENUNGGU</span>
                            @else
                                <span class="text-[10px] text-rose-400 font-bold">DITOLAK</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-center py-6 text-slate-500 text-xs">Belum ada permintaan topup</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
