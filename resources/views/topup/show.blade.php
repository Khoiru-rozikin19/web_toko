@extends('layouts.app')

@section('title', 'Pembayaran QRIS #' . $topup->invoice_number)

@section('content')
<div class="max-w-md mx-auto py-4 sm:py-6">
    <div id="paymentCard" class="glass-card rounded-3xl p-6 sm:p-8 shadow-2xl relative overflow-hidden border border-indigo-500/30">
        <!-- Status Header -->
        <div id="statusHeaderSection" class="text-center pb-4 border-b border-slate-800">
            @if($topup->status === 'paid')
                <div class="w-14 h-14 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center mx-auto mb-2 border border-emerald-500/40">
                    <i data-lucide="check-circle" class="w-7 h-7"></i>
                </div>
                <h1 class="text-xl font-black text-emerald-400">Pembayaran Diterima</h1>
                <p class="text-xs text-slate-300 mt-1">Saldo Rp {{ number_format($topup->amount, 0, ',', '.') }} telah masuk ke akun Anda.</p>
            @elseif($topup->status === 'pending')
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-400 text-xs font-bold mb-2">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                    <span>Menunggu Pembayaran</span>
                </div>
                <h1 class="text-lg sm:text-xl font-bold text-white tracking-tight">Scan QRIS Dinamis</h1>
                <p class="text-xs text-slate-400 mt-0.5">Buka BCA, GoPay, OVO, Dana, ShopeePay, atau Mobile Banking apa saja</p>
            @else
                <div class="w-14 h-14 rounded-full bg-rose-500/20 text-rose-400 flex items-center justify-center mx-auto mb-2 border border-rose-500/40">
                    <i data-lucide="x-circle" class="w-7 h-7"></i>
                </div>
                <h1 class="text-xl font-black text-rose-400">Topup Ditolak / Kadaluarsa</h1>
            @endif
        </div>

        @if($topup->status === 'pending')
            <!-- QR Code Display Box -->
            <div id="qrCodeContainer" class="my-6 text-center">
                <div class="inline-block p-4 bg-white rounded-3xl shadow-2xl shadow-indigo-500/10 border-4 border-indigo-500/30">
                    <img src="{{ $qrCodeUrl }}" alt="QRIS Dinamis" class="w-56 h-56 mx-auto rounded-xl">
                    <div class="mt-2 text-[10px] text-slate-800 font-extrabold tracking-wider uppercase">
                        QRIS Standar Pembayaran Nasional
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-center gap-2 text-xs text-slate-400">
                    <i data-lucide="clock" class="w-4 h-4 text-indigo-400"></i>
                    <span>Batas Waktu: <strong id="countdownTimer" class="text-indigo-300 font-mono">30:00</strong></span>
                </div>
            </div>

            <!-- Transfer Amount Box with Copy -->
            <div id="amountBox" class="bg-slate-900/90 border border-slate-700/80 rounded-2xl p-4 space-y-3">
                <div class="flex justify-between items-center text-xs text-slate-400">
                    <span>Nominal Topup:</span>
                    <span class="font-bold text-slate-200">Rp {{ number_format($topup->amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center text-xs text-slate-400">
                    <span>Kode Unik (Identifikasi):</span>
                    <span class="font-bold text-amber-400 font-mono">+{{ $topup->unique_code }}</span>
                </div>
                <div class="pt-2 border-t border-slate-800 flex justify-between items-center">
                    <div>
                        <span class="text-[10px] text-slate-400 block uppercase font-semibold">Total Transfer Pas</span>
                        <span id="totalAmountText" class="text-lg sm:text-xl font-black text-emerald-400">
                            Rp {{ number_format($topup->total_amount, 0, ',', '.') }}
                        </span>
                    </div>
                    <button type="button" onclick="copyTotal({{ $topup->total_amount }})" class="bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white px-3 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 border border-slate-700">
                        <i data-lucide="copy" class="w-3.5 h-3.5"></i> Salin Total
                    </button>
                </div>
            </div>

            <!-- Live Telegram Bot Status Card -->
            <div id="telegramStatusNotice" class="mt-4 p-3.5 rounded-2xl bg-indigo-950/40 border border-indigo-500/30 text-xs flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-indigo-600/30 text-indigo-300 flex items-center justify-center shrink-0">
                    <i data-lucide="bot" class="w-4 h-4 animate-bounce"></i>
                </div>
                <div>
                    <span class="font-bold text-indigo-300 block">Notifikasi Bot Telegram Terkirim</span>
                    <span class="text-[11px] text-slate-400">Admin akan segera meng-approve via Bot Telegram. Halaman ini akan otomatis update.</span>
                </div>
            </div>
        @endif

        <!-- Dynamic Success / Paid Screen (Hidden until paid) -->
        <div id="successScreen" class="{{ $topup->status === 'paid' ? '' : 'hidden' }} my-6 text-center space-y-4">
            <div class="w-20 h-20 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center mx-auto border-2 border-emerald-500/50 shadow-xl shadow-emerald-500/30 animate-pulse">
                <i data-lucide="check" class="w-10 h-10"></i>
            </div>
            <h2 class="text-2xl font-black text-white">Topup Berhasil!</h2>
            <p class="text-xs text-slate-300">Saldo sebesar <strong class="text-emerald-400">Rp {{ number_format($topup->amount, 0, ',', '.') }}</strong> telah masuk ke dompet Anda.</p>
            <div class="pt-4 flex flex-col gap-2">
                <a href="{{ route('dashboard') }}" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 rounded-xl text-xs transition shadow-lg shadow-indigo-600/30 flex items-center justify-center gap-2">
                    <i data-lucide="shopping-cart" class="w-4 h-4"></i> Mulai Belanja Paket Data
                </a>
            </div>
        </div>

        <!-- Invoice Footer Info -->
        <div class="mt-6 pt-4 border-t border-slate-800 text-[11px] text-slate-500 flex justify-between items-center">
            <span>Invoice: <code>{{ $topup->invoice_number }}</code></span>
            <span>{{ $topup->created_at->format('d/m/Y H:i') }}</span>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function copyTotal(val) {
        navigator.clipboard.writeText(val).then(() => {
            alert('Total transfer Rp ' + val.toLocaleString('id-ID') + ' berhasil disalin!');
        });
    }

    // Countdown Timer (30 minutes)
    let timeLeft = 30 * 60;
    const timerElem = document.getElementById('countdownTimer');
    if (timerElem) {
        const timerInterval = setInterval(() => {
            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                timerElem.innerText = "Kadaluarsa";
                return;
            }
            timeLeft--;
            const m = Math.floor(timeLeft / 60).toString().padStart(2, '0');
            const s = (timeLeft % 60).toString().padStart(2, '0');
            timerElem.innerText = `${m}:${s}`;
        }, 1000);
    }

    // Real-time Polling for Telegram Bot Approval
    const invoiceNum = "{{ $topup->invoice_number }}";
    const currentStatus = "{{ $topup->status }}";

    if (currentStatus === 'pending') {
        const pollInterval = setInterval(() => {
            fetch(`/topup/${invoiceNum}/status`)
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'paid') {
                        clearInterval(pollInterval);
                        // Trigger UI update
                        document.getElementById('qrCodeContainer')?.classList.add('hidden');
                        document.getElementById('amountBox')?.classList.add('hidden');
                        document.getElementById('telegramStatusNotice')?.classList.add('hidden');
                        document.getElementById('statusHeaderSection')?.classList.add('hidden');
                        document.getElementById('successScreen')?.classList.remove('hidden');
                        lucide.createIcons();
                    } else if (data.status === 'rejected' || data.status === 'expired') {
                        clearInterval(pollInterval);
                        window.location.reload();
                    }
                })
                .catch(() => {});
        }, 3000);
    }
</script>
@endpush
@endsection
