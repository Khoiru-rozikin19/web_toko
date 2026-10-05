<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Default Admin & Demo User
        $admin = User::firstOrCreate(
            ['email' => 'admin@webtoko.com'],
            [
                'name' => 'Administrator Toko',
                'phone' => '081234567890',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'balance' => 1000000.00,
                'is_active' => true,
            ]
        );

        $demoUser = User::firstOrCreate(
            ['email' => 'user@webtoko.com'],
            [
                'name' => 'John Doe Demo',
                'phone' => '081298765432',
                'password' => Hash::make('user123'),
                'role' => 'user',
                'balance' => 150000.00,
                'is_active' => true,
            ]
        );

        // 2. Default Settings
        $defaultSettings = [
            'app_title' => 'Tokonet H2H - Server Paket Data & Pulsa Termurah',
            'okeconnect_member_id' => 'OK12345',
            'okeconnect_pin' => '1234',
            'okeconnect_password' => 'password123',
            'okeconnect_base_url' => 'https://h2h.okeconnect.com',
            'okeconnect_sandbox_mode' => '1',
            'telegram_bot_token' => '',
            'telegram_admin_chat_id' => '',
            // Standard static QRIS template for testing dynamic conversion
            'qris_static_string' => '00020101021126590014ID.LINKAJA.WWW0118936009110021200388021000012345670303UMI51440014ID.CO.QRIS.WWW0215ID10200212003880303UMI5204581253033605802ID5913WEB TOKO H2H6007JAKARTA61051234062070703A016304',
            'qris_merchant_name' => 'WEB TOKO H2H STORE',
        ];

        foreach ($defaultSettings as $k => $v) {
            Setting::firstOrCreate(['key' => $k], ['value' => $v, 'group' => 'general']);
        }

        // 3. Categories & Products
        $categoriesData = [
            [
                'name' => 'Telkomsel Data Flash & OMG',
                'slug' => 'telkomsel-data',
                'brand' => 'Telkomsel',
                'type' => 'data',
                'sort_order' => 1,
                'products' => [
                    ['provider_code' => 'TDF1', 'name' => 'Telkomsel Flash 1.5 GB 30 Hari', 'desc' => '1.5 GB Flash + Kuota Utama 24 Jam', 'original' => 13500, 'selling' => 14500],
                    ['provider_code' => 'TDF3', 'name' => 'Telkomsel Flash 3 GB 30 Hari', 'desc' => '3 GB Kuota Nasional 24 Jam 30 Hari', 'original' => 24000, 'selling' => 25500],
                    ['provider_code' => 'TDF6', 'name' => 'Telkomsel Flash 6.5 GB OMG! 30 Hari', 'desc' => '4.5 GB Utama + 2 GB OMG! Sosmed', 'original' => 41500, 'selling' => 43500],
                    ['provider_code' => 'TDF10', 'name' => 'Telkomsel Flash 10 GB OMG! 30 Hari', 'desc' => '8 GB Utama + 2 GB OMG! Sosmed', 'original' => 61000, 'selling' => 63500],
                    ['provider_code' => 'TDF17', 'name' => 'Telkomsel Flash 17 GB OMG! 30 Hari', 'desc' => '15 GB Utama + 2 GB OMG!', 'original' => 86000, 'selling' => 89500],
                    ['provider_code' => 'TDF28', 'name' => 'Telkomsel Flash 28 GB OMG! 30 Hari', 'desc' => '26 GB Utama + 2 GB OMG! Sosmed', 'original' => 116000, 'selling' => 119500],
                ]
            ],
            [
                'name' => 'Indosat Data Freedom Internet',
                'slug' => 'indosat-data',
                'brand' => 'Indosat',
                'type' => 'data',
                'sort_order' => 2,
                'products' => [
                    ['provider_code' => 'IDF3', 'name' => 'Indosat Freedom Internet 3 GB 30 Hari', 'desc' => '3 GB Kuota Utama 24 Jam Tanpa Pembagian', 'original' => 18000, 'selling' => 19500],
                    ['provider_code' => 'IDF7', 'name' => 'Indosat Freedom Internet 7 GB 30 Hari', 'desc' => '7 GB Kuota Utama 24 Jam', 'original' => 31000, 'selling' => 33000],
                    ['provider_code' => 'IDF10', 'name' => 'Indosat Freedom Internet 10 GB 30 Hari', 'desc' => '10 GB Kuota Utama 24 Jam', 'original' => 43000, 'selling' => 45500],
                    ['provider_code' => 'IDF18', 'name' => 'Indosat Freedom Internet 18 GB 30 Hari', 'desc' => '18 GB Kuota Utama 24 Jam', 'original' => 64000, 'selling' => 67000],
                    ['provider_code' => 'IDF32', 'name' => 'Indosat Freedom Internet 32 GB 30 Hari', 'desc' => '32 GB Kuota Utama 24 Jam', 'original' => 89000, 'selling' => 92500],
                ]
            ],
            [
                'name' => 'XL Data Xtra Combo Flex',
                'slug' => 'xl-data',
                'brand' => 'XL',
                'type' => 'data',
                'sort_order' => 3,
                'products' => [
                    ['provider_code' => 'XLC3', 'name' => 'XL Xtra Combo Flex S 3.5 GB', 'desc' => '3.5 GB Kuota Utama + Bonus Vidio / YouTube', 'original' => 19500, 'selling' => 21000],
                    ['provider_code' => 'XLC7', 'name' => 'XL Xtra Combo Flex M 7 GB', 'desc' => '7 GB Kuota Utama + Bonus Flexing', 'original' => 34000, 'selling' => 36500],
                    ['provider_code' => 'XLC15', 'name' => 'XL Xtra Combo Flex L 15 GB', 'desc' => '15 GB Kuota Utama + Unlimited WA/Line', 'original' => 54000, 'selling' => 57000],
                    ['provider_code' => 'XLC26', 'name' => 'XL Xtra Combo Flex XL 26 GB', 'desc' => '26 GB Kuota Utama 24 Jam', 'original' => 77000, 'selling' => 80500],
                ]
            ],
            [
                'name' => 'Axis Data Bronet & AIGO',
                'slug' => 'axis-data',
                'brand' => 'Axis',
                'type' => 'data',
                'sort_order' => 4,
                'products' => [
                    ['provider_code' => 'AXB2', 'name' => 'Axis Bronet 2 GB 30 Hari', 'desc' => '2 GB 24 Jam Semua Jaringan', 'original' => 14000, 'selling' => 15500],
                    ['provider_code' => 'AXB5', 'name' => 'Axis Bronet 5 GB 30 Hari', 'desc' => '5 GB 24 Jam Kuota Utama', 'original' => 26000, 'selling' => 28000],
                    ['provider_code' => 'AXB10', 'name' => 'Axis Bronet 10 GB 30 Hari', 'desc' => '10 GB 24 Jam Kuota Utama', 'original' => 43000, 'selling' => 45500],
                    ['provider_code' => 'AXB16', 'name' => 'Axis Bronet 16 GB 30 Hari', 'desc' => '16 GB 24 Jam Kuota Utama', 'original' => 61000, 'selling' => 64000],
                ]
            ],
            [
                'name' => 'Tri Data Happy & AlwaysOn',
                'slug' => 'tri-data',
                'brand' => 'Tri',
                'type' => 'data',
                'sort_order' => 5,
                'products' => [
                    ['provider_code' => 'TRH3', 'name' => 'Tri Happy 3 GB 30 Hari', 'desc' => '3 GB Kuota Utama 24 Jam', 'original' => 15000, 'selling' => 16500],
                    ['provider_code' => 'TRH6', 'name' => 'Tri Happy 6 GB 30 Hari', 'desc' => '6 GB Kuota Utama 24 Jam', 'original' => 25000, 'selling' => 27000],
                    ['provider_code' => 'TRH12', 'name' => 'Tri Happy 12 GB 30 Hari', 'desc' => '12 GB Kuota Utama 24 Jam', 'original' => 44000, 'selling' => 46500],
                    ['provider_code' => 'TRH25', 'name' => 'Tri Happy 25 GB 30 Hari', 'desc' => '25 GB Kuota Utama 24 Jam', 'original' => 69000, 'selling' => 72000],
                ]
            ],
            [
                'name' => 'Smartfren Data Kuota Nonstop',
                'slug' => 'smartfren-data',
                'brand' => 'Smartfren',
                'type' => 'data',
                'sort_order' => 6,
                'products' => [
                    ['provider_code' => 'SMN4', 'name' => 'Smartfren Kuota 4 GB 14 Hari', 'desc' => '4 GB Kuota Nasional 24 Jam', 'original' => 14000, 'selling' => 15500],
                    ['provider_code' => 'SMN10', 'name' => 'Smartfren Kuota 10 GB 30 Hari', 'desc' => '10 GB Kuota Nasional 24 Jam', 'original' => 29000, 'selling' => 31000],
                    ['provider_code' => 'SMN18', 'name' => 'Smartfren Kuota 18 GB 30 Hari', 'desc' => '18 GB Kuota Nasional 24 Jam', 'original' => 47000, 'selling' => 50000],
                ]
            ],
            [
                'name' => 'PLN Token Listrik Prabayar',
                'slug' => 'pln-token',
                'brand' => 'PLN',
                'type' => 'pln',
                'sort_order' => 7,
                'products' => [
                    ['provider_code' => 'PLN20', 'name' => 'PLN Token Rp 20.000', 'desc' => 'Token Listrik Prabayar Nominal 20k', 'original' => 20100, 'selling' => 20750],
                    ['provider_code' => 'PLN50', 'name' => 'PLN Token Rp 50.000', 'desc' => 'Token Listrik Prabayar Nominal 50k', 'original' => 50100, 'selling' => 50750],
                    ['provider_code' => 'PLN100', 'name' => 'PLN Token Rp 100.000', 'desc' => 'Token Listrik Prabayar Nominal 100k', 'original' => 100100, 'selling' => 100750],
                    ['provider_code' => 'PLN200', 'name' => 'PLN Token Rp 200.000', 'desc' => 'Token Listrik Prabayar Nominal 200k', 'original' => 200100, 'selling' => 200750],
                ]
            ],
        ];

        foreach ($categoriesData as $catData) {
            $products = $catData['products'];
            unset($catData['products']);

            $category = Category::firstOrCreate(['slug' => $catData['slug']], $catData);

            foreach ($products as $p) {
                Product::firstOrCreate(
                    ['provider_code' => $p['provider_code']],
                    [
                        'category_id' => $category->id,
                        'name' => $p['name'],
                        'description' => $p['desc'],
                        'price_original' => $p['original'],
                        'price_selling' => $p['selling'],
                        'type' => $category->type,
                        'status' => 'active',
                    ]
                );
            }
        }
    }
}
