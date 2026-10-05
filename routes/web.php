<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TelegramWebhookController;
use App\Http\Controllers\TopupController;
use Illuminate\Support\Facades\Route;

// Public & Customer Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/api/detect-operator', [DashboardController::class, 'detectOperator'])->name('api.detect-operator');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Customer Authenticated Routes
Route::middleware('auth')->group(function () {
    // History
    Route::get('/history', [DashboardController::class, 'history'])->name('history');

    // Checkout
    Route::post('/checkout', [CheckoutController::class, 'checkout'])->name('checkout');
    Route::get('/checkout/{invoiceNumber}', [CheckoutController::class, 'receipt'])->name('checkout.receipt');

    // Topup Saldo Web
    Route::get('/topup', [TopupController::class, 'index'])->name('topup.index');
    Route::post('/topup', [TopupController::class, 'store'])->name('topup.store');
    Route::get('/topup/{invoiceNumber}', [TopupController::class, 'show'])->name('topup.show');
    Route::get('/topup/{invoiceNumber}/status', [TopupController::class, 'checkStatus'])->name('topup.status');
});

// Admin Panel Routes
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    // User Management
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::post('/users/{id}/balance', [AdminController::class, 'updateUserBalance'])->name('users.balance');

    // Product Management
    Route::get('/products', [AdminController::class, 'products'])->name('products');
    Route::post('/products', [AdminController::class, 'storeProduct'])->name('products.store');
    Route::put('/products/{id}', [AdminController::class, 'updateProduct'])->name('products.update');

    // Topup Management
    Route::get('/topups', [AdminController::class, 'topups'])->name('topups');
    Route::post('/topups/{id}/approve', [AdminController::class, 'approveTopup'])->name('topups.approve');
    Route::post('/topups/{id}/reject', [AdminController::class, 'rejectTopup'])->name('topups.reject');

    // Transactions
    Route::get('/transactions', [AdminController::class, 'transactions'])->name('transactions');

    // Settings (Okeconnect, Telegram, QRIS)
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [AdminController::class, 'saveSettings'])->name('settings.save');
    Route::post('/settings/telegram-webhook', [AdminController::class, 'setTelegramWebhook'])->name('settings.telegram.webhook');
});

// Telegram Bot Webhook (Excluded from CSRF in bootstrap/app.php)
Route::match(['get', 'post'], '/api/telegram/webhook', [TelegramWebhookController::class, 'handle'])->name('telegram.webhook');
