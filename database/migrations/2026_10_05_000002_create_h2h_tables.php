<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('brand'); // Telkomsel, Indosat, XL, Axis, Tri, Smartfren, PLN, dll
            $table->string('icon')->nullable();
            $table->string('type')->default('data'); // data, pulsa, pln, game, voucher
            $table->string('status')->default('active'); // active, inactive
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('provider_code')->unique(); // Kode produk dari Okeconnect (misal: TD5, ID10)
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price_original', 15, 2)->default(0); // Harga modal Okeconnect
            $table->decimal('price_selling', 15, 2)->default(0);  // Harga jual ke user
            $table->string('type')->default('data'); // data, pulsa, pln, game
            $table->string('status')->default('active'); // active, empty, inactive
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('topups', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique(); // TOP-20261005-XXXX
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->integer('unique_code')->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->text('qris_payload')->nullable(); // EMVCo QRIS dynamic string
            $table->string('status')->default('pending'); // pending, paid, expired, rejected
            $table->string('approved_by')->nullable(); // user_id or 'telegram_bot'
            $table->timestamp('approved_at')->nullable();
            $table->string('telegram_message_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique(); // TRX-20261005-XXXX
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('product_name');
            $table->string('provider_code');
            $table->string('destination_number'); // Nomor HP / ID Pelanggan
            $table->decimal('amount', 15, 2); // Harga jual
            $table->decimal('price_original', 15, 2)->default(0); // Harga modal
            $table->decimal('profit', 15, 2)->default(0);
            $table->string('payment_method')->default('balance'); // balance, qris
            $table->string('status')->default('pending'); // pending, processing, success, failed
            $table->string('sn_or_token')->nullable(); // Serial Number / Token PLN
            $table->string('provider_trx_id')->nullable();
            $table->json('provider_response')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        Schema::create('balance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['credit', 'debit']); // credit (+) or debit (-)
            $table->decimal('amount', 15, 2);
            $table->decimal('before_balance', 15, 2);
            $table->decimal('after_balance', 15, 2);
            $table->string('reference_type')->nullable(); // topup, transaction, refund, manual
            $table->string('reference_id')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general'); // okeconnect, telegram, qris, general
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('balance_logs');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('topups');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('settings');
    }
};
