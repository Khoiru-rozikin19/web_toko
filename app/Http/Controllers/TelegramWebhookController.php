<?php

namespace App\Http\Controllers;

use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    protected TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    public function handle(Request $request)
    {
        $update = $request->all();
        Log::info("Incoming Telegram Webhook:", $update);

        $result = $this->telegramService->handleWebhook($update);

        return response()->json(['ok' => true, 'result' => $result]);
    }
}
