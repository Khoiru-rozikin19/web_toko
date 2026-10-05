@extends('layouts.admin')

@section('title', 'Semua Transaksi H2H')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Transaksi H2H</h1>
            <p class="text-xs sm:text-sm text-slate-400">Seluruh riwayat transaksi paket data & pulsa ke server Okeconnect</p>
        </div>

        <form action="{{ route('admin.transactions') }}" method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari invoice/nomor hp..." class="bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white px-3.5 py-2 rounded-xl text-xs font-semibold">Cari</button>
        </form>
    </div>

    <div class="glass-sidebar rounded-2xl overflow-hidden border border-slate-800 shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-900 text-slate-400 uppercase text-[11px] font-bold border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Invoice / Waktu</th>
                        <th class="py-3 px-4">Member</th>
                        <th class="py-3 px-4">Produk / Kode</th>
                        <th class="py-3 px-4">Tujuan</th>
                        <th class="py-3 px-4">Harga / Profit</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Serial Number (SN)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80 text-slate-300">
                    @forelse($transactions as $trx)
                        <tr class="hover:bg-slate-900/50">
                            <td class="py-3.5 px-4">
                                <span class="font-mono font-bold text-indigo-400 block">{{ $trx->invoice_number }}</span>
                                <span class="text-[11px] text-slate-500">{{ $trx->created_at->format('d/m/Y H:i') }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-white block">{{ $trx->user->name ?? 'User' }}</span>
                                <span class="text-[11px] text-slate-400">{{ $trx->user->phone ?? $trx->user->email ?? '-' }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-white block">{{ $trx->product_name }}</span>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $trx->provider_code }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-200">
                                {{ $trx->destination_number }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-white block">Rp {{ number_format($trx->amount, 0, ',', '.') }}</span>
                                <span class="text-[10px] text-emerald-400 font-semibold">+Rp {{ number_format($trx->profit, 0, ',', '.') }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($trx->status === 'success')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">SUKSES</span>
                                @elseif($trx->status === 'processing' || $trx->status === 'pending')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30 animate-pulse">PROSES</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/30">GAGAL</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-mono text-xs">
                                @if($trx->sn_or_token)
                                    <span class="text-emerald-300 font-semibold select-all">{{ $trx->sn_or_token }}</span>
                                @else
                                    <span class="text-slate-500">{{ $trx->error_message ?: '-' }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-slate-500">Tidak ada transaksi ditemukan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
            <div class="p-4 border-t border-slate-800 bg-slate-900/50">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
