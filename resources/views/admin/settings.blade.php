@extends('layouts.admin')

@section('title', 'Pengaturan API & Bot Telegram')

@section('content')
<div class="space-y-6 max-w-4xl">
    <div>
        <h1 class="text-2xl font-black text-white tracking-tight">Pengaturan Server & Integrasi</h1>
        <p class="text-xs sm:text-sm text-slate-400">Konfigurasi API Okeconnect H2H, Bot Telegram Verifier, & String QRIS Statis</p>
    </div>

    <form action="{{ route('admin.settings.save') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Card 1: Okeconnect H2H Settings -->
        <div class="glass-sidebar rounded-3xl p-6 border border-slate-800 shadow-xl space-y-4">
            <div class="flex items-center gap-3 pb-3 border-b border-slate-800">
                <div class="w-10 h-10 rounded-xl bg-indigo-600/20 text-indigo-400 flex items-center justify-center">
                    <i data-lucide="server" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base text-white">1. Konfigurasi API Okeconnect (H2H)</h3>
                    <p class="text-xs text-slate-400">Kredensial akun server pulsa / paket data dari portal Okeconnect</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Member ID</label>
                    <input type="text" name="okeconnect_member_id" value="{{ $settings['okeconnect_member_id'] ?? '' }}" placeholder="Contoh: OK12345" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">PIN Transaksi</label>
                    <input type="password" name="okeconnect_pin" value="{{ $settings['okeconnect_pin'] ?? '' }}" placeholder="1234" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-white">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Password API</label>
                    <input type="password" name="okeconnect_password" value="{{ $settings['okeconnect_password'] ?? '' }}" placeholder="••••••••" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Base URL H2H</label>
                    <input type="text" name="okeconnect_base_url" value="{{ $settings['okeconnect_base_url'] ?? 'https://h2h.okeconnect.com' }}" placeholder="https://h2h.okeconnect.com" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-white font-mono">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Mode Operasi</label>
                <select name="okeconnect_sandbox_mode" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-white">
                    <option value="1" {{ ($settings['okeconnect_sandbox_mode'] ?? '1') === '1' ? 'selected' : '' }}>Mode Simulasi Sandbox (Bisa testing tanpa potong saldo live)</option>
                    <option value="0" {{ ($settings['okeconnect_sandbox_mode'] ?? '') === '0' ? 'selected' : '' }}>Mode Live Production (Transaksi asli kirim ke Okeconnect)</option>
                </select>
            </div>
        </div>

        <!-- Card 2: Telegram Bot Notification & Approval -->
        <div class="glass-sidebar rounded-3xl p-6 border border-slate-800 shadow-xl space-y-4">
            <div class="flex items-center gap-3 pb-3 border-b border-slate-800">
                <div class="w-10 h-10 rounded-xl bg-sky-500/20 text-sky-400 flex items-center justify-center">
                    <i data-lucide="bot" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base text-white">2. Konfigurasi Bot Telegram Verifier</h3>
                    <p class="text-xs text-slate-400">Notifikasi topup otomatis dengan tombol konfirmasi [Accept / Reject]</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Telegram Bot Token</label>
                    <input type="text" name="telegram_bot_token" value="{{ $settings['telegram_bot_token'] ?? '' }}" placeholder="123456789:ABCdefGhIJKlmNoPQRstuvWXyz" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-white font-mono">
                    <span class="text-[10px] text-slate-400 mt-1 block">Dapatkan token dari @BotFather di Telegram.</span>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Admin Chat ID</label>
                    <input type="text" name="telegram_admin_chat_id" value="{{ $settings['telegram_admin_chat_id'] ?? '' }}" placeholder="Contoh: 123456789" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-white font-mono">
                    <span class="text-[10px] text-slate-400 mt-1 block">Kirim /chatid ke bot Anda untuk melihat ID chat Anda.</span>
                </div>
            </div>

            <div class="p-3.5 rounded-2xl bg-slate-900 border border-slate-800 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <div>
                    <span class="text-xs font-bold text-white block">URL Webhook Telegram Anda:</span>
                    <code class="text-[11px] text-indigo-400 select-all font-mono">{{ url('/api/telegram/webhook') }}</code>
                </div>
            </div>
        </div>

        <!-- Card 3: QRIS Statis to Dynamic Converter -->
        <div class="glass-sidebar rounded-3xl p-6 border border-slate-800 shadow-xl space-y-4">
            <div class="flex items-center gap-3 pb-3 border-b border-slate-800">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center">
                    <i data-lucide="qr-code" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base text-white">3. Konfigurasi QRIS Statis Toko</h3>
                    <p class="text-xs text-slate-400">String payload QRIS statis dari BCA / ShopeePay / GoPay / Dana / Nobu / LinkAja</p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Payload String QRIS Statis</label>
                <textarea name="qris_static_string" rows="3" placeholder="0002010102112659..." class="w-full bg-slate-900 border border-slate-700 rounded-xl p-3 text-xs font-mono text-emerald-300">{{ $settings['qris_static_string'] ?? '' }}</textarea>
                <span class="text-[10px] text-slate-400 mt-1 block">
                    Cara mendapatkan: Scan QRIS statis toko Anda dengan scanner QR code (seperti Google Lens / Barcode Scanner) lalu copy text hasilnya ke sini.
                </span>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Nama Merchant / Toko di QRIS</label>
                <input type="text" name="qris_merchant_name" value="{{ $settings['qris_merchant_name'] ?? 'WEB TOKO H2H' }}" placeholder="WEB TOKO H2H" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-white">
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end gap-3 pt-2">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 px-6 rounded-2xl text-xs transition shadow-lg shadow-indigo-600/30 flex items-center gap-2">
                <i data-lucide="save" class="w-4 h-4"></i> Simpan Semua Pengaturan
            </button>
        </div>
    </form>

    <!-- Separate Webhook Registration Form -->
    <div class="glass-sidebar rounded-3xl p-6 border border-slate-800 shadow-xl flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
            <h4 class="text-sm font-bold text-white">Daftarkan Webhook Bot Telegram ke Server</h4>
            <p class="text-xs text-slate-400">Klik tombol ini setelah menyimpan Bot Token agar bot Telegram bisa menerima event klik tombol [Accept/Reject].</p>
        </div>
        <form action="{{ route('admin.settings.telegram.webhook') }}" method="POST">
            @csrf
            <button type="submit" class="bg-sky-600 hover:bg-sky-500 text-white font-bold py-2.5 px-4 rounded-xl text-xs transition shadow-md shadow-sky-600/30 flex items-center gap-1.5 whitespace-nowrap">
                <i data-lucide="link" class="w-4 h-4"></i> Pasang Webhook Telegram
            </button>
        </form>
    </div>
</div>
@endsection
