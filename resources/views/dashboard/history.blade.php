@extends('layouts.app')

@section('title', 'Riwayat Transaksi - Tokonet H2H')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Riwayat Transaksi</h1>
            <p class="text-xs sm:text-sm text-slate-400">Daftar semua pembelian paket data & pulsa Anda</p>
        </div>
        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold px-3.5 py-2 rounded-xl transition shadow-md shadow-indigo-600/30">
            <i data-lucide="plus" class="w-4 h-4"></i> Transaksi Baru
        </a>
    </div>

    <div class="glass-card rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-900/90 text-slate-400 uppercase text-[11px] font-bold border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4">Invoice / Waktu</th>
                        <th class="py-3.5 px-4">Produk</th>
                        <th class="py-3.5 px-4">Nomor Tujuan</th>
                        <th class="py-3.5 px-4">Harga</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4">SN / Token</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80 text-slate-300">
                    @forelse($transactions as $trx)
                        <tr class="hover:bg-slate-900/50 transition">
                            <td class="py-3.5 px-4">
                                <span class="font-mono font-bold text-indigo-400 block">{{ $trx->invoice_number }}</span>
                                <span class="text-[11px] text-slate-500">{{ $trx->created_at->format('d/m/Y H:i') }} WIB</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-white block">{{ $trx->product_name }}</span>
                                <span class="text-[10px] text-slate-400">{{ $trx->provider_code }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-semibold text-slate-200">
                                {{ $trx->destination_number }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-white">
                                Rp {{ number_format($trx->amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4">
                                @if($trx->status === 'success')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Sukses
                                    </span>
                                @elseif($trx->status === 'processing' || $trx->status === 'pending')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30 animate-pulse">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span> Diproses
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-500/10 text-rose-400 border border-rose-500/30">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span> Gagal
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-mono text-xs">
                                @if($trx->sn_or_token)
                                    <span class="text-emerald-300 font-semibold select-all">{{ $trx->sn_or_token }}</span>
                                @else
                                    <span class="text-slate-500">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <a href="{{ route('checkout.receipt', $trx->invoice_number) }}" class="p-1.5 text-indigo-400 hover:text-indigo-300 hover:bg-indigo-600/20 rounded-lg inline-flex" title="Lihat Struk">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-slate-500">
                                <i data-lucide="inbox" class="w-10 h-10 mx-auto mb-2 text-slate-600"></i>
                                <span>Belum ada transaksi dilakukan.</span>
                            </td>
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
