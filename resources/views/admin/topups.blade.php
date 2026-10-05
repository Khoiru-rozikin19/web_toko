@extends('layouts.admin')

@section('title', 'Kelola Topup Saldo')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Antrean Topup Saldo</h1>
            <p class="text-xs sm:text-sm text-slate-400">Verifikasi deposit QRIS dinamis (Dapat juga dikonfirmasi langsung via Bot Telegram)</p>
        </div>

        <!-- Filter Status -->
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.topups') }}" class="px-3 py-1.5 rounded-xl text-xs font-bold border transition {{ !request('status') ? 'bg-indigo-600 text-white border-indigo-500' : 'bg-slate-900 border-slate-800 text-slate-400' }}">
                Semua
            </a>
            <a href="{{ route('admin.topups', ['status' => 'pending']) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold border transition {{ request('status') === 'pending' ? 'bg-amber-500 text-slate-950 border-amber-400' : 'bg-slate-900 border-slate-800 text-slate-400' }}">
                Pending
            </a>
            <a href="{{ route('admin.topups', ['status' => 'paid']) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold border transition {{ request('status') === 'paid' ? 'bg-emerald-600 text-white border-emerald-500' : 'bg-slate-900 border-slate-800 text-slate-400' }}">
                Lunas
            </a>
        </div>
    </div>

    <div class="glass-sidebar rounded-2xl overflow-hidden border border-slate-800 shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-900 text-slate-400 uppercase text-[11px] font-bold border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Invoice / Waktu</th>
                        <th class="py-3 px-4">Pengguna</th>
                        <th class="py-3 px-4">Nominal</th>
                        <th class="py-3 px-4">Kode Unik</th>
                        <th class="py-3 px-4">Total Transfer</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-center">Tindakan Admin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80 text-slate-300">
                    @forelse($topups as $t)
                        <tr class="hover:bg-slate-900/50">
                            <td class="py-3.5 px-4">
                                <span class="font-mono font-bold text-indigo-400 block">{{ $t->invoice_number }}</span>
                                <span class="text-[11px] text-slate-500">{{ $t->created_at->format('d/m/Y H:i') }} WIB</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-white block">{{ $t->user->name ?? 'User' }}</span>
                                <span class="text-[11px] text-slate-400">{{ $t->user->phone ?? $t->user->email ?? '-' }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-300">
                                Rp {{ number_format($t->amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-amber-400">
                                +{{ $t->unique_code }}
                            </td>
                            <td class="py-3.5 px-4 font-extrabold text-emerald-400 text-sm">
                                Rp {{ number_format($t->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4">
                                @if($t->status === 'paid')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                                        LUNAS ({{ $t->approved_by ?? 'Telegram' }})
                                    </span>
                                @elseif($t->status === 'pending')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30 animate-pulse">
                                        PENDING
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/30">
                                        DITOLAK
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($t->status === 'pending')
                                    <div class="flex items-center justify-center gap-1.5">
                                        <form action="{{ route('admin.topups.approve', $t->id) }}" method="POST" onsubmit="return confirm('Setujui topup Rp {{ number_format($t->amount, 0, ',', '.') }} untuk {{ $t->user->name ?? 'User' }}?')">
                                            @csrf
                                            <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white px-2.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1 shadow-md shadow-emerald-600/20">
                                                <i data-lucide="check" class="w-3.5 h-3.5"></i> Terima
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.topups.reject', $t->id) }}" method="POST" onsubmit="return confirm('Tolak topup ini?')">
                                            @csrf
                                            <button type="submit" class="bg-rose-600 hover:bg-rose-500 text-white px-2.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1">
                                                <i data-lucide="x" class="w-3.5 h-3.5"></i> Tolak
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-[11px] text-slate-500">Selesai ({{ $t->approved_at ? $t->approved_at->format('d/m H:i') : '-' }})</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-slate-500">Tidak ada data topup</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($topups->hasPages())
            <div class="p-4 border-t border-slate-800 bg-slate-900/50">
                {{ $topups->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
