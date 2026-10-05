<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Topup;
use App\Services\QrisService;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TopupController extends Controller
{
    protected QrisService $qrisService;
    protected TelegramService $telegramService;

    public function __construct(QrisService $qrisService, TelegramService $telegramService)
    {
        $this->qrisService = $qrisService;
        $this->telegramService = $telegramService;
    }

    public function index()
    {
        $user = Auth::user();
        $recentTopups = Topup::where('user_id', $user->id)->latest()->take(5)->get();
        return view('topup.index', compact('user', 'recentTopups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10000|max:10000000',
        ], [
            'amount.required' => 'Nominal topup wajib diisi.',
            'amount.min' => 'Minimal topup saldo adalah Rp 10.000.',
            'amount.max' => 'Maksimal topup saldo adalah Rp 10.000.000.',
        ]);

        $user = Auth::user();
        $amount = (float) $request->amount;
        $uniqueCode = rand(100, 899);
        $totalAmount = $amount + $uniqueCode;
        $invoiceNumber = 'TOP-' . date('Ymd') . '-' . strtoupper(Str::random(5));

        // Get static QRIS string from settings
        $staticQris = Setting::get('qris_static_string', '00020101021126590014ID.LINKAJA.WWW0118936009110021200388021000012345670303UMI51440014ID.CO.QRIS.WWW0215ID10200212003880303UMI5204581253033605802ID5913WEB TOKO H2H6007JAKARTA61051234062070703A016304');

        // Convert to Dynamic QRIS
        $dynamicQris = $this->qrisService->convertStaticToDynamic($staticQris, $totalAmount);

        $topup = Topup::create([
            'invoice_number' => $invoiceNumber,
            'user_id' => $user->id,
            'amount' => $amount,
            'unique_code' => $uniqueCode,
            'total_amount' => $totalAmount,
            'qris_payload' => $dynamicQris,
            'status' => 'pending',
            'expired_at' => now()->addMinutes(60),
        ]);

        // Send Notification to Telegram Admin with Accept / Reject buttons
        $this->telegramService->sendTopupNotification($topup);

        return redirect()->route('topup.show', $topup->invoice_number);
    }

    public function show(string $invoiceNumber)
    {
        $topup = Topup::where('invoice_number', $invoiceNumber)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $qrCodeUrl = $this->qrisService->getQrCodeSvgUrl($topup->qris_payload ?: 'QRIS-DEMO');

        return view('topup.show', compact('topup', 'qrCodeUrl'));
    }

    /**
     * Polling JSON status check for live frontend status updates
     */
    public function checkStatus(string $invoiceNumber)
    {
        $topup = Topup::where('invoice_number', $invoiceNumber)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return response()->json([
            'invoice_number' => $topup->invoice_number,
            'status' => $topup->status, // pending, paid, rejected, expired
            'amount' => $topup->amount,
            'total_amount' => $topup->total_amount,
            'approved_at' => $topup->approved_at ? $topup->approved_at->format('d M Y H:i:s') : null,
        ]);
    }
}
