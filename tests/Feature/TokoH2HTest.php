<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Topup;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TokoH2HTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Budi Tester',
            'phone' => '08123456789',
            'email' => 'budi@test.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'balance' => 100000,
            'is_active' => true,
        ]);

        $this->admin = User::create([
            'name' => 'Admin Tester',
            'phone' => '08129999999',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'balance' => 500000,
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Telkomsel Data',
            'slug' => 'telkomsel-data',
            'brand' => 'Telkomsel',
            'type' => 'data',
            'status' => 'active',
        ]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'provider_code' => 'TDF5',
            'name' => 'Telkomsel Flash 5 GB',
            'price_original' => 45000,
            'price_selling' => 48000,
            'type' => 'data',
            'status' => 'active',
        ]);
    }

    public function test_user_can_create_topup_and_view_qris(): void
    {
        $response = $this->actingAs($this->user)->post('/topup', [
            'amount' => 50000,
        ]);

        $topup = Topup::where('user_id', $this->user->id)->first();
        $this->assertNotNull($topup);
        $this->assertEquals(50000, $topup->amount);
        $this->assertGreaterThan(50000, $topup->total_amount); // amount + unique_code
        $this->assertNotEmpty($topup->qris_payload);

        $response->assertRedirect(route('topup.show', $topup->invoice_number));

        // Check show page
        $showRes = $this->actingAs($this->user)->get(route('topup.show', $topup->invoice_number));
        $showRes->assertStatus(200);
        $showRes->assertSee($topup->invoice_number);
    }

    public function test_telegram_approval_adds_balance_atomically(): void
    {
        $topup = Topup::create([
            'invoice_number' => 'TOP-TEST-12345',
            'user_id' => $this->user->id,
            'amount' => 50000,
            'unique_code' => 125,
            'total_amount' => 50125,
            'status' => 'pending',
        ]);

        $telegramService = app(TelegramService::class);
        $result = $telegramService->processTopupApproval($topup->invoice_number, null, null, null, true, 'Test Admin');

        $this->assertEquals('success', $result['status']);
        $this->assertEquals('approved', $result['action']);

        $this->user->refresh();
        $topup->refresh();

        $this->assertEquals(150000, $this->user->balance); // 100k + 50k
        $this->assertEquals('paid', $topup->status);
    }

    public function test_checkout_deducts_balance_and_creates_transaction(): void
    {
        $response = $this->actingAs($this->user)->post('/checkout', [
            'product_id' => $this->product->id,
            'destination_number' => '08123456789',
            'payment_method' => 'balance',
        ]);

        $this->user->refresh();
        $this->assertEquals(52000, $this->user->balance); // 100k - 48k

        $response->assertRedirect();
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'destination_number' => '08123456789',
            'amount' => 48000,
        ]);
    }
}
