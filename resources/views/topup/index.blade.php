@extends('layouts.app')

@section('title', 'Topup Saldo Web - QRIS Dinamis Instan')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Header Card -->
    <div class="glass-card rounded-3xl p-6 sm:p-8 shadow-2xl relative overflow-hidden border border-indigo-500/20">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-600/20 border border-indigo-500/40 flex items-center justify-center text-indigo-400">
                <i data-lucide="wallet" class="w-6 h-6"></i>
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Topup Saldo Web</h1>
                <p class="text-xs sm:text-sm text-slate-400">Isi saldo otomatis via QRIS Dinamis Bebas Nominal</p>
            </div>
        </div>

        <!-- Current Balance -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 flex items-center justify-between mb-6">
            <div>
                <span class="text-xs text-slate-400 block font-semibold">Saldo Saat Ini</span>
                <span class="text-xl font-extrabold text-emerald-400">Rp {{ number_format(auth()->user()->balance, 0, ',', '.') }}</span>
            </div>
            <span class="px-3 py-1 bg-indigo-500/10 border border-indigo-400/30 text-indigo-300 text-xs font-semibold rounded-full flex items-center gap-1.5">
                <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-400"></i> Bot Telegram Verifier
            </span>
        </div>

        <!-- Form Topup -->
        <form action="{{ route('topup.store') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Quick Nominal Pills -->
            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2.5">
                    Pilih Cepat Nominal
                </label>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                    @php
                        $nominals = [10000, 25000, 50000, 100000, 250000, 500000];
                    @endphp
                    @foreach($nominals as $nom)
                        <button type="button" onclick="setNominal({{ $nom }})" class="nominal-btn py-3 px-4 rounded-xl text-xs sm:text-sm font-bold border border-slate-800 bg-slate-900/80 hover:bg-indigo-600/20 hover:border-indigo-500 text-slate-200 hover:text-indigo-300 transition text-center">
                            Rp {{ number_format($nom, 0, ',', '.') }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Custom Amount Input -->
            <div>
                <label for="amountInput" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">
                    Atau Masukkan Nominal Sendiri
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 font-bold text-slate-400 text-sm">
                        Rp
                    </span>
                    <input type="number" name="amount" id="amountInput" value="{{ old('amount', 50000) }}" min="10000" max="10000000" step="1000" required placeholder="50000" class="w-full bg-slate-900 border-2 border-slate-700/80 rounded-2xl pl-12 pr-4 py-3.5 text-lg font-black text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                </div>
                <span class="text-[11px] text-slate-400 mt-1 block">Minimal Rp 10.000 • Maksimal Rp 10.000.000 (Bebas tentukan sendiri).</span>
                @error('amount')
                    <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Flow Information Box -->
            <div class="p-4 rounded-2xl bg-indigo-950/40 border border-indigo-500/30 text-xs text-slate-300 space-y-2">
                <div class="font-bold text-indigo-300 flex items-center gap-1.5">
                    <i data-lucide="sparkles" class="w-4 h-4"></i> Cara Kerja Topup QRIS Dinamis:
                </div>
                <ol class="list-decimal list-inside space-y-1 text-slate-400 text-[11px] leading-relaxed">
                    <li>Sistem men-decode QRIS statis toko dan meng-inject nominal transfer Anda (EMVCo Dynamic).</li>
                    <li>Notifikasi otomatis terkirim langsung ke Bot Telegram Admin dengan opsi <b>[Accept / Reject]</b>.</li>
                    <li>Begitu pembayaran Anda terverifikasi di Telegram, saldo Anda seketika bertambah otomatis!</li>
                </ol>
            </div>

            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3.5 px-4 rounded-2xl transition shadow-xl shadow-indigo-600/30 flex items-center justify-center gap-2 text-sm">
                <i data-lucide="qr-code" class="w-5 h-5"></i>
                <span>Lanjutkan & Generate QRIS Dinamis</span>
            </button>
        </form>
    </div>

    <!-- Recent Topup Requests -->
    @if($recentTopups->isNotEmpty())
        <div class="glass-card rounded-2xl p-5 shadow-xl">
            <h2 class="text-sm font-bold text-white mb-3 flex items-center gap-2">
                <i data-lucide="history" class="w-4 h-4 text-indigo-400"></i> Riwayat Permintaan Topup
            </h2>
            <div class="divide-y divide-slate-800/80 text-xs">
                @foreach($recentTopups as $t)
                    <div class="py-3 flex items-center justify-between">
                        <div>
                            <span class="font-mono font-bold text-indigo-400 block">{{ $t->invoice_number }}</span>
                            <span class="text-[10px] text-slate-500">{{ $t->created_at->format('d M Y, H:i') }} WIB</span>
                        </div>
                        <div class="text-right">
                            <span class="font-bold text-white block">Rp {{ number_format($t->total_amount, 0, ',', '.') }}</span>
                            @if($t->status === 'paid')
                                <span class="text-[10px] text-emerald-400 font-bold">LUNAS</span>
                            @elseif($t->status === 'pending')
                                <a href="{{ route('topup.show', $t->invoice_number) }}" class="text-[10px] text-amber-400 underline font-bold">Menunggu Bayar &rarr;</a>
                            @else
                                <span class="text-[10px] text-rose-400 font-bold">Ditolak</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    function setNominal(val) {
        document.getElementById('amountInput').value = val;
    }
</script>
@endpush
@endsection
