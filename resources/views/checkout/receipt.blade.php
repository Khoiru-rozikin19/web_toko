@extends('layouts.app')

@section('title', 'Struk Pembelian #' . $transaction->invoice_number)

@section('content')
<div class="max-w-lg mx-auto py-4 sm:py-8">
    <div class="glass-card rounded-3xl p-6 sm:p-8 shadow-2xl relative overflow-hidden border border-slate-700/80">
        <!-- Status Header Icon -->
        <div class="text-center pb-6 border-b border-slate-800">
            @if($transaction->status === 'success')
                <div class="w-16 h-16 rounded-full bg-emerald-500/20 border-2 border-emerald-500/40 text-emerald-400 flex items-center justify-center mx-auto mb-3 shadow-lg shadow-emerald-500/20">
                    <i data-lucide="check-circle" class="w-8 h-8"></i>
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-white">Transaksi Berhasil</h1>
                <p class="text-xs text-emerald-400 font-semibold mt-1">Paket / Pulsa telah aktif dan dikirim ke nomor tujuan</p>
            @elseif($transaction->status === 'processing' || $transaction->status === 'pending')
                <div class="w-16 h-16 rounded-full bg-amber-500/20 border-2 border-amber-500/40 text-amber-400 flex items-center justify-center mx-auto mb-3 animate-pulse">
                    <i data-lucide="loader" class="w-8 h-8 animate-spin"></i>
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-white">Transaksi Sedang Diproses</h1>
                <p class="text-xs text-amber-400 font-semibold mt-1">Sistem sedang memproses request ke operator</p>
            @else
                <div class="w-16 h-16 rounded-full bg-rose-500/20 border-2 border-rose-500/40 text-rose-400 flex items-center justify-center mx-auto mb-3">
                    <i data-lucide="x-circle" class="w-8 h-8"></i>
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-white">Transaksi Gagal</h1>
                <p class="text-xs text-rose-400 font-semibold mt-1">{{ $transaction->error_message ?: 'Gangguan operator / nomor salah. Saldo telah dikembalikan.' }}</p>
            @endif
        </div>

        <!-- Serial Number / Token Box (High Visibility) -->
        @if($transaction->sn_or_token)
            <div class="mt-6 p-4 rounded-2xl bg-indigo-950/40 border border-indigo-500/40 text-center">
                <span class="text-[11px] uppercase tracking-wider font-bold text-indigo-300 block mb-1">
                    {{ str_contains($transaction->product_name, 'PLN') ? 'Token Listrik PLN' : 'Serial Number (SN)' }}
                </span>
                <div class="flex items-center justify-center gap-2">
                    <span id="snText" class="font-mono text-base sm:text-lg font-black text-white tracking-wider select-all">{{ $transaction->sn_or_token }}</span>
                    <button onclick="copyToClipboard('{{ $transaction->sn_or_token }}', 'SN berhasil disalin!')" class="p-1.5 bg-indigo-600/30 hover:bg-indigo-600 text-indigo-300 hover:text-white rounded-lg transition" title="Salin SN">
                        <i data-lucide="copy" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
        @endif

        <!-- Details List -->
        <div class="mt-6 space-y-3 text-xs sm:text-sm">
            <div class="flex justify-between py-2 border-b border-slate-800">
                <span class="text-slate-400">Nomor Invoice</span>
                <span class="font-mono font-bold text-indigo-400">{{ $transaction->invoice_number }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-slate-800">
                <span class="text-slate-400">Waktu Transaksi</span>
                <span class="font-semibold text-slate-200">{{ $transaction->created_at->format('d F Y, H:i:s') }} WIB</span>
            </div>
            <div class="flex justify-between py-2 border-b border-slate-800">
                <span class="text-slate-400">Nomor Tujuan</span>
                <span class="font-mono font-bold text-slate-100">{{ $transaction->destination_number }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-slate-800">
                <span class="text-slate-400">Nama Produk</span>
                <span class="font-bold text-white text-right">{{ $transaction->product_name }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-slate-800">
                <span class="text-slate-400">Metode Pembayaran</span>
                <span class="font-semibold text-slate-200 uppercase">Saldo Web (H2H)</span>
            </div>
            <div class="flex justify-between py-2 text-base font-black">
                <span class="text-slate-300">Total Harga</span>
                <span class="text-emerald-400">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-8 flex flex-col sm:flex-row gap-2.5">
            <a href="{{ route('dashboard') }}" class="flex-1 bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 px-4 rounded-xl text-center text-xs transition shadow-lg shadow-indigo-600/30 flex items-center justify-center gap-1.5">
                <i data-lucide="shopping-cart" class="w-4 h-4"></i> Beli Produk Lain
            </a>
            <a href="{{ route('history') }}" class="flex-1 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold py-3 px-4 rounded-xl text-center text-xs transition flex items-center justify-center gap-1.5">
                <i data-lucide="receipt" class="w-4 h-4"></i> Lihat Riwayat
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function copyToClipboard(text, msg) {
        navigator.clipboard.writeText(text).then(() => {
            alert(msg || 'Disalin ke clipboard!');
        });
    }
</script>
@endpush
@endsection
