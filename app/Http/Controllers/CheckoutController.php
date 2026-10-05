<?php

namespace App\Http\Controllers;

use App\Models\BalanceLog;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Services\OkeconnectService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    protected OkeconnectService $okeconnect;

    public function __construct(OkeconnectService $okeconnect)
    {
        $this->okeconnect = $okeconnect;
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'destination_number' => 'required|string|min:4|max:25',
            'payment_method' => 'required|in:balance',
        ], [
            'product_id.required' => 'Silakan pilih produk terlebih dahulu.',
            'destination_number.required' => 'Nomor tujuan/HP wajib diisi.',
        ]);

        $user = Auth::user();
        $product = Product::findOrFail($request->product_id);

        if ($product->status !== 'active') {
            return back()->with('error', 'Maaf, produk ini sedang tidak tersedia atau gangguan.');
        }

        // Clean destination number
        $destination = preg_replace('/[^0-9]/', '', $request->destination_number);
        if (str_starts_with($destination, '628')) {
            $destination = '08' . substr($destination, 3);
        }

        $invoiceNumber = 'TRX-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        $sellingPrice = $product->price_selling;
        $originalPrice = $product->price_original;
        $profit = $sellingPrice - $originalPrice;

        // DB Transaction for Atomic Balance Check & Deduction
        try {
            $trx = DB::transaction(function () use ($user, $product, $destination, $invoiceNumber, $sellingPrice, $originalPrice, $profit) {
                // Lock user record
                $lockedUser = User::where('id', $user->id)->lockForUpdate()->first();

                if ($lockedUser->balance < $sellingPrice) {
                    throw new \Exception("Saldo Anda tidak mencukupi (Rp " . number_format($lockedUser->balance, 0, ',', '.') . "). Silakan lakukan Topup terlebih dahulu.");
                }

                // Deduct Balance
                $beforeBalance = $lockedUser->balance;
                $afterBalance = $beforeBalance - $sellingPrice;
                $lockedUser->balance = $afterBalance;
                $lockedUser->save();

                // Create Transaction
                $transaction = Transaction::create([
                    'invoice_number' => $invoiceNumber,
                    'user_id' => $lockedUser->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'provider_code' => $product->provider_code,
                    'destination_number' => $destination,
                    'amount' => $sellingPrice,
                    'price_original' => $originalPrice,
                    'profit' => $profit,
                    'payment_method' => 'balance',
                    'status' => 'processing',
                ]);

                // Create Balance Log
                BalanceLog::create([
                    'user_id' => $lockedUser->id,
                    'type' => 'debit',
                    'amount' => $sellingPrice,
                    'before_balance' => $beforeBalance,
                    'after_balance' => $afterBalance,
                    'reference_type' => 'transaction',
                    'reference_id' => $invoiceNumber,
                    'description' => "Pembelian {$product->name} ke {$destination}",
                ]);

                return $transaction;
            });

            // Send H2H request to Okeconnect
            $h2hResult = $this->okeconnect->submitTransaction($product->provider_code, $destination, $trx->invoice_number);

            if ($h2hResult['status'] === 'success') {
                $trx->update([
                    'status' => 'success',
                    'sn_or_token' => $h2hResult['sn_or_token'] ?? null,
                    'provider_trx_id' => $h2hResult['provider_trx_id'] ?? null,
                    'provider_response' => $h2hResult['raw'] ?? null,
                ]);
            } elseif ($h2hResult['status'] === 'processing') {
                $trx->update([
                    'status' => 'processing',
                    'provider_trx_id' => $h2hResult['provider_trx_id'] ?? null,
                    'provider_response' => $h2hResult['raw'] ?? null,
                ]);
            } else {
                // If Okeconnect rejected/failed, refund user balance
                DB::transaction(function () use ($trx, $user, $sellingPrice, $h2hResult) {
                    $lockedUser = User::where('id', $user->id)->lockForUpdate()->first();
                    $before = $lockedUser->balance;
                    $after = $before + $sellingPrice;
                    $lockedUser->balance = $after;
                    $lockedUser->save();

                    $trx->update([
                        'status' => 'failed',
                        'error_message' => $h2hResult['message'] ?? 'Transaksi gagal dari operator.',
                        'provider_response' => $h2hResult['raw'] ?? null,
                    ]);

                    BalanceLog::create([
                        'user_id' => $lockedUser->id,
                        'type' => 'credit',
                        'amount' => $sellingPrice,
                        'before_balance' => $before,
                        'after_balance' => $after,
                        'reference_type' => 'refund',
                        'reference_id' => $trx->invoice_number,
                        'description' => "Pengembalian Dana Transaksi Gagal #{$trx->invoice_number}",
                    ]);
                });

                return redirect()->route('checkout.receipt', $trx->invoice_number)->with('error', 'Transaksi gagal diproses oleh operator. Saldo telah dikembalikan ke akun Anda.');
            }

            return redirect()->route('checkout.receipt', $trx->invoice_number)->with('success', 'Transaksi berhasil diproses!');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function receipt(string $invoiceNumber)
    {
        $transaction = Transaction::where('invoice_number', $invoiceNumber)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('checkout.receipt', compact('transaction'));
    }
}
