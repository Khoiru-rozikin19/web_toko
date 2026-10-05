<?php

namespace App\Services;

use App\Models\BalanceLog;
use App\Models\Setting;
use App\Models\Topup;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected string $botToken;
    protected string $adminChatId;

    public function __construct()
    {
        $this->botToken = (string) Setting::get('telegram_bot_token', env('TELEGRAM_BOT_TOKEN', ''));
        $this->adminChatId = (string) Setting::get('telegram_admin_chat_id', env('TELEGRAM_ADMIN_CHAT_ID', ''));
    }

    public function isConfigured(): bool
    {
        return !empty($this->botToken) && !empty($this->adminChatId);
    }

    /**
     * Send Topup Notification to Admin Telegram with Interactive Action Buttons
     */
    public function sendTopupNotification(Topup $topup): ?string
    {
        if (!$this->isConfigured()) {
            Log::info("Telegram not configured. Skipping topup notification for {$topup->invoice_number}");
            return null;
        }

        $user = $topup->user;
        $userName = $user ? $user->name : 'User';
        $userPhone = $user ? ($user->phone ?? $user->email) : '-';
        $formattedAmount = number_format($topup->amount, 0, ',', '.');
        $formattedTotal = number_format($topup->total_amount, 0, ',', '.');
        $time = $topup->created_at->format('d M Y H:i:s');

        $message = "🔔 <b>PERMINTAAN TOPUP SALDO BARU</b>\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "📄 <b>Invoice:</b> <code>{$topup->invoice_number}</code>\n";
        $message .= "👤 <b>User:</b> {$userName} ({$userPhone})\n";
        $message .= "💰 <b>Nominal:</b> Rp {$formattedAmount}\n";
        $message .= "🔢 <b>Kode Unik:</b> {$topup->unique_code}\n";
        $message .= "💵 <b>Total Bayar:</b> <b>Rp {$formattedTotal}</b>\n";
        $message .= "🕒 <b>Waktu:</b> {$time} WIB\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "Silakan cek mutasi QRIS / rekening Anda lalu pilih tindakan di bawah:";

        $keyboard = [
            'inline_keyboard' => [
                [
                    [
                        'text' => '✅ Terima / Accept (Rp ' . $formattedTotal . ')',
                        'callback_data' => 'topup_accept_' . $topup->invoice_number,
                    ],
                    [
                        'text' => '❌ Tolak / Reject',
                        'callback_data' => 'topup_reject_' . $topup->invoice_number,
                    ],
                ]
            ]
        ];

        try {
            $response = Http::post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
                'chat_id' => $this->adminChatId,
                'text' => $message,
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode($keyboard),
            ]);

            if ($response->successful()) {
                $result = $response->json();
                $messageId = $result['result']['message_id'] ?? null;
                if ($messageId) {
                    $topup->update(['telegram_message_id' => (string) $messageId]);
                }
                return (string) $messageId;
            } else {
                Log::error("Telegram error sending notification: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("Telegram exception: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Handle incoming Webhook update from Telegram
     */
    public function handleWebhook(array $update): array
    {
        // Check for callback query (button clicks)
        if (isset($update['callback_query'])) {
            return $this->handleCallbackQuery($update['callback_query']);
        }

        // Check for normal text message
        if (isset($update['message'])) {
            return $this->handleTextMessage($update['message']);
        }

        return ['status' => 'ignored'];
    }

    /**
     * Handle inline button click (Accept / Reject)
     */
    protected function handleCallbackQuery(array $callbackQuery): array
    {
        $callbackId = $callbackQuery['id'];
        $data = $callbackQuery['data'] ?? '';
        $fromId = (string) ($callbackQuery['from']['id'] ?? '');
        $messageId = $callbackQuery['message']['message_id'] ?? null;
        $chatId = $callbackQuery['message']['chat']['id'] ?? null;

        // Verify if user is admin chat id (allow if matches or admin group)
        if ($this->adminChatId && $fromId !== $this->adminChatId && (string) $chatId !== $this->adminChatId) {
            $this->answerCallbackQuery($callbackId, 'Akses ditolak! Anda bukan admin resmi.', true);
            return ['status' => 'unauthorized'];
        }

        if (str_starts_with($data, 'topup_accept_')) {
            $invoice = substr($data, strlen('topup_accept_'));
            return $this->processTopupApproval($invoice, $callbackId, $chatId, $messageId, true, $callbackQuery['from']['first_name'] ?? 'Admin');
        }

        if (str_starts_with($data, 'topup_reject_')) {
            $invoice = substr($data, strlen('topup_reject_'));
            return $this->processTopupApproval($invoice, $callbackId, $chatId, $messageId, false, $callbackQuery['from']['first_name'] ?? 'Admin');
        }

        $this->answerCallbackQuery($callbackId, 'Perintah tidak dikenal.');
        return ['status' => 'unknown_action'];
    }

    /**
     * Process Topup Approval / Rejection with strict database locking
     */
    public function processTopupApproval(string $invoice, ?string $callbackId = null, $chatId = null, $messageId = null, bool $isAccept = true, string $adminName = 'Admin'): array
    {
        return DB::transaction(function () use ($invoice, $callbackId, $chatId, $messageId, $isAccept, $adminName) {
            $topup = Topup::where('invoice_number', $invoice)->lockForUpdate()->first();

            if (!$topup) {
                if ($callbackId) {
                    $this->answerCallbackQuery($callbackId, "Invoice #{$invoice} tidak ditemukan!", true);
                }
                return ['status' => 'error', 'message' => 'Invoice not found'];
            }

            if ($topup->status !== 'pending') {
                $statusText = $topup->status === 'paid' ? 'SUDAH DITERIMA' : 'SUDAH DITOLAK';
                if ($callbackId) {
                    $this->answerCallbackQuery($callbackId, "Topup #{$invoice} sudah diproses sebelumnya ({$statusText})!", true);
                }
                return ['status' => 'already_processed', 'current_status' => $topup->status];
            }

            $user = User::where('id', $topup->user_id)->lockForUpdate()->first();

            if ($isAccept) {
                // Accept: Add balance
                $amountToAdd = $topup->amount; // Nominal yang ditambahkan (bisa total_amount jika kode unik ikut masuk)
                $beforeBalance = $user->balance;
                $afterBalance = $beforeBalance + $amountToAdd;

                $user->balance = $afterBalance;
                $user->save();

                $topup->status = 'paid';
                $topup->approved_by = "telegram:{$adminName}";
                $topup->approved_at = now();
                $topup->save();

                BalanceLog::create([
                    'user_id' => $user->id,
                    'type' => 'credit',
                    'amount' => $amountToAdd,
                    'before_balance' => $beforeBalance,
                    'after_balance' => $afterBalance,
                    'reference_type' => 'topup',
                    'reference_id' => $topup->invoice_number,
                    'description' => "Topup Saldo via QRIS Dinamis (Approved via Telegram)",
                ]);

                if ($callbackId) {
                    $this->answerCallbackQuery($callbackId, "✅ BERHASIL! Saldo Rp " . number_format($amountToAdd, 0, ',', '.') . " telah ditambahkan ke {$user->name}.", true);
                }

                $this->updateTelegramMessageStatus($chatId, $messageId, $topup, 'APPROVED', $adminName);
                return ['status' => 'success', 'action' => 'approved', 'invoice' => $invoice];
            } else {
                // Reject
                $topup->status = 'rejected';
                $topup->approved_by = "telegram:{$adminName}";
                $topup->approved_at = now();
                $topup->save();

                if ($callbackId) {
                    $this->answerCallbackQuery($callbackId, "❌ Topup #{$invoice} telah DITOLAK.", true);
                }

                $this->updateTelegramMessageStatus($chatId, $messageId, $topup, 'REJECTED', $adminName);
                return ['status' => 'success', 'action' => 'rejected', 'invoice' => $invoice];
            }
        });
    }

    /**
     * Update Telegram Message Text to reflect approval status & remove buttons
     */
    protected function updateTelegramMessageStatus($chatId, $messageId, Topup $topup, string $status, string $adminName): void
    {
        if (!$chatId || !$messageId || !$this->isConfigured()) {
            return;
        }

        $user = $topup->user;
        $userName = $user ? $user->name : 'User';
        $userPhone = $user ? ($user->phone ?? $user->email) : '-';
        $formattedAmount = number_format($topup->amount, 0, ',', '.');
        $formattedTotal = number_format($topup->total_amount, 0, ',', '.');
        $time = $topup->created_at->format('d M Y H:i:s');
        $processedTime = now()->format('d M Y H:i:s');

        if ($status === 'APPROVED') {
            $header = "✅ <b>TOPUP SALDO BERHASIL DISETUJUI</b>";
            $statusBadge = "🟢 <b>STATUS:</b> DITERIMA (Saldo masuk Rp {$formattedAmount})";
        } else {
            $header = "❌ <b>TOPUP SALDO DITOLAK</b>";
            $statusBadge = "🔴 <b>STATUS:</b> DITOLAK OLEH ADMIN";
        }

        $message = "{$header}\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "📄 <b>Invoice:</b> <code>{$topup->invoice_number}</code>\n";
        $message .= "👤 <b>User:</b> {$userName} ({$userPhone})\n";
        $message .= "💰 <b>Nominal:</b> Rp {$formattedAmount}\n";
        $message .= "🔢 <b>Kode Unik:</b> {$topup->unique_code}\n";
        $message .= "💵 <b>Total:</b> Rp {$formattedTotal}\n";
        $message .= "🕒 <b>Request:</b> {$time} WIB\n";
        $message .= "⚡ <b>Diproses:</b> {$processedTime} WIB oleh {$adminName}\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "{$statusBadge}";

        try {
            Http::post("https://api.telegram.org/bot{$this->botToken}/editMessageText", [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => $message,
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode(['inline_keyboard' => []]), // clear buttons
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to edit Telegram message: " . $e->getMessage());
        }
    }

    /**
     * Handle basic commands (/start, /saldo, /help)
     */
    protected function handleTextMessage(array $message): array
    {
        $text = trim($message['text'] ?? '');
        $chatId = $message['chat']['id'] ?? null;

        if (!$chatId) return ['status' => 'no_chat_id'];

        if ($text === '/start' || $text === '/help') {
            $msg = "👋 <b>Halo Admin Web Toko!</b>\n\n";
            $msg .= "Bot ini digunakan untuk mengkonfirmasi topup saldo QRIS & notifikasi transaksi H2H.\n\n";
            $msg .= "<b>Perintah tersedia:</b>\n";
            $msg .= "• <code>/status</code> - Cek status server & jumlah transaksi hari ini\n";
            $msg .= "• <code>/pending</code> - Cek daftar topup pending\n";
            $msg .= "• <code>/chatid</code> - Cek ID chat Anda";

            $this->sendMessage($chatId, $msg);
            return ['status' => 'help_sent'];
        }

        if ($text === '/chatid') {
            $this->sendMessage($chatId, "🆔 Chat ID Anda: <code>{$chatId}</code>");
            return ['status' => 'chatid_sent'];
        }

        if ($text === '/pending') {
            $pendingTopups = Topup::with('user')->where('status', 'pending')->latest()->take(5)->get();
            if ($pendingTopups->isEmpty()) {
                $this->sendMessage($chatId, "✨ Tidak ada topup yang pending saat ini.");
            } else {
                $reply = "📋 <b>Topup Pending Terkini:</b>\n\n";
                foreach ($pendingTopups as $t) {
                    $reply .= "• <code>{$t->invoice_number}</code> - Rp " . number_format($t->total_amount, 0, ',', '.') . " ({$t->user->name})\n";
                }
                $this->sendMessage($chatId, $reply);
            }
            return ['status' => 'pending_sent'];
        }

        return ['status' => 'unhandled_command'];
    }

    public function answerCallbackQuery(string $callbackId, string $text, bool $showAlert = false): void
    {
        try {
            Http::post("https://api.telegram.org/bot{$this->botToken}/answerCallbackQuery", [
                'callback_query_id' => $callbackId,
                'text' => $text,
                'show_alert' => $showAlert,
            ]);
        } catch (\Exception $e) {
            Log::error("Telegram answer callback error: " . $e->getMessage());
        }
    }

    public function sendMessage($chatId, string $text): void
    {
        try {
            Http::post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
            ]);
        } catch (\Exception $e) {
            Log::error("Telegram send message error: " . $e->getMessage());
        }
    }

    public function setWebhook(string $url): array
    {
        try {
            $response = Http::post("https://api.telegram.org/bot{$this->botToken}/setWebhook", [
                'url' => $url,
            ]);
            return $response->json();
        } catch (\Exception $e) {
            return ['ok' => false, 'description' => $e->getMessage()];
        }
    }
}
