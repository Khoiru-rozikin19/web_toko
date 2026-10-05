<?php

namespace App\Http\Controllers;

use App\Models\BalanceLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Topup;
use App\Models\Transaction;
use App\Models\User;
use App\Services\OkeconnectService;
use App\Services\QrisService;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    protected OkeconnectService $okeconnect;
    protected TelegramService $telegram;
    protected QrisService $qris;

    public function __construct(OkeconnectService $okeconnect, TelegramService $telegram, QrisService $qris)
    {
        $this->okeconnect = $okeconnect;
        $this->telegram = $telegram;
        $this->qris = $qris;
    }

    public function dashboard()
    {
        $totalUsers = User::count();
        $totalTransactions = Transaction::count();
        $successTransactions = Transaction::where('status', 'success')->count();
        $totalRevenue = Transaction::where('status', 'success')->sum('amount');
        $totalProfit = Transaction::where('status', 'success')->sum('profit');
        $pendingTopups = Topup::where('status', 'pending')->count();
        $todayTransactions = Transaction::whereDate('created_at', today())->count();

        $h2hBalance = $this->okeconnect->checkH2HBalance();

        $recentTransactions = Transaction::with(['user', 'product'])->latest()->take(7)->get();
        $recentTopups = Topup::with('user')->latest()->take(7)->get();

        return view('admin.dashboard', compact(
            'totalUsers', 'totalTransactions', 'successTransactions',
            'totalRevenue', 'totalProfit', 'pendingTopups', 'todayTransactions',
            'h2hBalance', 'recentTransactions', 'recentTopups'
        ));
    }

    // ================= USERS =================
    public function users(Request $request)
    {
        $query = User::query();
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(15);
        return view('admin.users', compact('users'));
    }

    public function updateUserBalance(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:add,subtract,set',
            'amount' => 'required|numeric|min:0',
            'notes' => 'required|string|max:255',
        ]);

        $user = User::findOrFail($id);
        $amount = (float) $request->amount;
        $action = $request->action;

        DB::transaction(function () use ($user, $action, $amount, $request) {
            $lockedUser = User::where('id', $user->id)->lockForUpdate()->first();
            $before = $lockedUser->balance;

            if ($action === 'add') {
                $after = $before + $amount;
                $type = 'credit';
            } elseif ($action === 'subtract') {
                $after = max(0, $before - $amount);
                $type = 'debit';
            } else {
                $after = $amount;
                $type = $after >= $before ? 'credit' : 'debit';
            }

            $lockedUser->balance = $after;
            $lockedUser->save();

            BalanceLog::create([
                'user_id' => $lockedUser->id,
                'type' => $type,
                'amount' => abs($after - $before),
                'before_balance' => $before,
                'after_balance' => $after,
                'reference_type' => 'manual',
                'reference_id' => 'ADMIN-' . Auth::id(),
                'description' => $request->notes . ' (oleh Admin: ' . Auth::user()->name . ')',
            ]);
        });

        return back()->with('success', "Saldo pengguna {$user->name} berhasil diperbarui.");
    }

    // ================= PRODUCTS =================
    public function products(Request $request)
    {
        $categories = Category::orderBy('sort_order', 'asc')->get();
        $query = Product::with('category');

        if ($catId = $request->get('category_id')) {
            $query->where('category_id', $catId);
        }
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('provider_code', 'LIKE', "%{$search}%");
            });
        }

        $products = $query->orderBy('category_id')->orderBy('price_selling', 'asc')->paginate(20);
        return view('admin.products', compact('products', 'categories'));
    }

    public function storeProduct(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'provider_code' => 'required|string|unique:products,provider_code',
            'name' => 'required|string|max:255',
            'price_original' => 'required|numeric|min:0',
            'price_selling' => 'required|numeric|min:0',
            'type' => 'required|in:data,pulsa,pln,game,voucher',
            'status' => 'required|in:active,empty,inactive',
        ]);

        Product::create($request->all());
        return back()->with('success', 'Produk baru berhasil ditambahkan.');
    }

    public function updateProduct(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'price_original' => 'required|numeric|min:0',
            'price_selling' => 'required|numeric|min:0',
            'status' => 'required|in:active,empty,inactive',
        ]);

        $product->update($request->only(['name', 'price_original', 'price_selling', 'status', 'description']));
        return back()->with('success', "Produk {$product->name} berhasil diperbarui.");
    }

    // ================= TOPUPS =================
    public function topups(Request $request)
    {
        $query = Topup::with('user');
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        $topups = $query->latest()->paginate(15);
        return view('admin.topups', compact('topups'));
    }

    public function approveTopup(Request $request, $id)
    {
        $topup = Topup::findOrFail($id);
        $result = $this->telegram->processTopupApproval($topup->invoice_number, null, null, null, true, Auth::user()->name);

        if ($result['status'] === 'success') {
            return back()->with('success', "Topup #{$topup->invoice_number} berhasil disetujui.");
        }
        return back()->with('error', "Gagal: " . ($result['message'] ?? 'Sudah diproses'));
    }

    public function rejectTopup(Request $request, $id)
    {
        $topup = Topup::findOrFail($id);
        $result = $this->telegram->processTopupApproval($topup->invoice_number, null, null, null, false, Auth::user()->name);

        return back()->with('success', "Topup #{$topup->invoice_number} telah ditolak.");
    }

    // ================= TRANSACTIONS =================
    public function transactions(Request $request)
    {
        $query = Transaction::with(['user', 'product']);
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'LIKE', "%{$search}%")
                  ->orWhere('destination_number', 'LIKE', "%{$search}%");
            });
        }
        $transactions = $query->latest()->paginate(20);
        return view('admin.transactions', compact('transactions'));
    }

    // ================= SETTINGS =================
    public function settings()
    {
        $settings = Setting::all()->pluck('value', 'key');
        return view('admin.settings', compact('settings'));
    }

    public function saveSettings(Request $request)
    {
        $data = $request->except(['_token']);
        foreach ($data as $key => $val) {
            Setting::set($key, (string) $val);
        }

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }

    public function setTelegramWebhook(Request $request)
    {
        $webhookUrl = url('/api/telegram/webhook');
        $result = $this->telegram->setWebhook($webhookUrl);

        if ($result['ok'] ?? false) {
            return back()->with('success', "Webhook Telegram berhasil dipasang ke: {$webhookUrl}");
        }

        return back()->with('error', "Gagal memasang webhook: " . ($result['description'] ?? 'Periksa Bot Token Anda'));
    }
}
