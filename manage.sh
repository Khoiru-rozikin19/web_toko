#!/bin/bash
# ==============================================================================
# Script Manajemen Toko Pulsa & H2H Server (CLI Management Tool)
# Author: Senior Developer
# ==============================================================================

# Warna Output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m'

# Pastikan berada di root direktori project
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$SCRIPT_DIR"

check_artisan() {
    if [ ! -f "artisan" ]; then
        echo -e "${RED}[ERROR] File artisan tidak ditemukan di $(pwd). Jalankan script dari folder project!${NC}"
        exit 1
    fi
}
check_artisan

header() {
    clear
    echo -e "${CYAN}==============================================================================${NC}"
    echo -e "${PURPLE}${BOLD}   ⚡ TOKONET H2H - SERVER MANAGEMENT CONTROL PANEL (UBUNTU 24.04) ⚡   ${NC}"
    echo -e "${CYAN}==============================================================================${NC}"
}

pause() {
    echo ""
    read -p "Tekan [ENTER] untuk kembali ke menu..."
}

# ================= MENU 1: KELOLA USER =================
menu_user() {
    while true; do
        header
        echo -e "${YELLOW}[ MENU KELOLA PENGGUNA ]${NC}"
        echo "1. Lihat Daftar Pengguna & Saldo"
        echo "2. Tambah Pengguna / Admin Baru"
        echo "3. Ubah / Tambah Saldo Pengguna"
        echo "4. Reset Password Pengguna"
        echo "0. Kembali ke Menu Utama"
        echo ""
        read -p "Pilih menu [0-4]: " u_opt

        case $u_opt in
            1)
                echo -e "\n${CYAN}--- DAFTAR PENGGUNA TERDAFTAR ---${NC}"
                php artisan tinker --execute="
                    echo str_pad('ID', 4) . str_pad('NAMA', 22) . str_pad('HP / EMAIL', 28) . str_pad('ROLE', 8) . 'SALDO (RP)' . PHP_EOL;
                    echo str_repeat('-', 75) . PHP_EOL;
                    foreach(App\Models\User::all() as \$u) {
                        echo str_pad(\$u->id, 4) . str_pad(substr(\$u->name,0,20), 22) . str_pad(substr(\$u->phone ?: \$u->email, 0, 26), 28) . str_pad(\$u->role, 8) . number_format(\$u->balance, 0, ',', '.') . PHP_EOL;
                    }
                "
                pause
                ;;
            2)
                echo -e "\n${CYAN}--- TAMBAH PENGGUNA BARU ---${NC}"
                read -p "Nama Lengkap: " u_name
                read -p "Nomor HP (WhatsApp): " u_phone
                read -p "Email: " u_email
                read -p "Password: " u_pass
                read -p "Role (user/admin) [default: user]: " u_role
                u_role=${u_role:-user}
                read -p "Saldo Awal (Rp) [default: 0]: " u_bal
                u_bal=${u_bal:-0}

                php artisan tinker --execute="
                    \$user = App\Models\User::create([
                        'name' => '$u_name',
                        'phone' => '$u_phone',
                        'email' => '$u_email',
                        'password' => Illuminate\Support\Facades\Hash::make('$u_pass'),
                        'role' => '$u_role',
                        'balance' => $u_bal,
                        'is_active' => true
                    ]);
                    echo 'Pengguna ID ' . \$user->id . ' berhasil dibuat!' . PHP_EOL;
                "
                pause
                ;;
            3)
                echo -e "\n${CYAN}--- UBAH / TAMBAH SALDO PENGGUNA ---${NC}"
                read -p "Masukkan ID atau Email Pengguna: " u_ident
                read -p "Aksi (+ untuk tambah, - untuk kurang, = untuk set): " u_act
                read -p "Nominal (Rp): " u_amt

                php artisan tinker --execute="
                    \$user = is_numeric('$u_ident') ? App\Models\User::find('$u_ident') : App\Models\User::where('email', '$u_ident')->orWhere('phone', '$u_ident')->first();
                    if (!\$user) { echo 'User tidak ditemukan!' . PHP_EOL; exit; }
                    \$before = \$user->balance;
                    \$amt = (float) '$u_amt';
                    if ('$u_act' == '+') { \$user->balance += \$amt; \$type = 'credit'; }
                    elseif ('$u_act' == '-') { \$user->balance = max(0, \$user->balance - \$amt); \$type = 'debit'; }
                    else { \$user->balance = \$amt; \$type = \$amt >= \$before ? 'credit' : 'debit'; }
                    \$user->save();
                    App\Models\BalanceLog::create([
                        'user_id' => \$user->id,
                        'type' => \$type,
                        'amount' => abs(\$user->balance - \$before),
                        'before_balance' => \$before,
                        'after_balance' => \$user->balance,
                        'reference_type' => 'manual',
                        'reference_id' => 'CLI-ADMIN',
                        'description' => 'Penyesuaian saldo via CLI manage.sh'
                    ]);
                    echo 'Saldo ' . \$user->name . ' diperbarui: Rp ' . number_format(\$before,0,',','.') . ' -> Rp ' . number_format(\$user->balance,0,',','.') . PHP_EOL;
                "
                pause
                ;;
            4)
                echo -e "\n${CYAN}--- RESET PASSWORD PENGGUNA ---${NC}"
                read -p "Masukkan ID atau Email Pengguna: " u_ident
                read -p "Password Baru: " u_newpass
                php artisan tinker --execute="
                    \$user = is_numeric('$u_ident') ? App\Models\User::find('$u_ident') : App\Models\User::where('email', '$u_ident')->first();
                    if (!\$user) { echo 'User tidak ditemukan!' . PHP_EOL; exit; }
                    \$user->password = Illuminate\Support\Facades\Hash::make('$u_newpass');
                    \$user->save();
                    echo 'Password user ' . \$user->email . ' berhasil diubah!' . PHP_EOL;
                "
                pause
                ;;
            0)
                break
                ;;
        esac
    done
}

# ================= MENU 2: KELOLA PRODUK =================
menu_produk() {
    while true; do
        header
        echo -e "${YELLOW}[ MENU KELOLA PRODUK & HARGA H2H ]${NC}"
        echo "1. Lihat Semua Produk & Harga Jual"
        echo "2. Tambah Produk Baru"
        echo "3. Ubah Harga Jual / Modal Produk"
        echo "4. Ubah Status Produk (active/empty/inactive)"
        echo "0. Kembali ke Menu Utama"
        echo ""
        read -p "Pilih menu [0-4]: " p_opt

        case $p_opt in
            1)
                echo -e "\n${CYAN}--- DAFTAR PRODUK TOKO ---${NC}"
                php artisan tinker --execute="
                    echo str_pad('KODE', 8) . str_pad('NAMA PRODUK', 36) . str_pad('MODAL', 12) . str_pad('JUAL', 12) . 'STATUS' . PHP_EOL;
                    echo str_repeat('-', 75) . PHP_EOL;
                    foreach(App\Models\Product::orderBy('category_id')->get() as \$p) {
                        echo str_pad(\$p->provider_code, 8) . str_pad(substr(\$p->name,0,34), 36) . str_pad('Rp ' . number_format(\$p->price_original, 0, ',', '.'), 12) . str_pad('Rp ' . number_format(\$p->price_selling, 0, ',', '.'), 12) . \$p->status . PHP_EOL;
                    }
                "
                pause
                ;;
            2)
                echo -e "\n${CYAN}--- TAMBAH PRODUK BARU ---${NC}"
                read -p "ID Kategori (1:Telkomsel, 2:Indosat, 3:XL, 4:Axis, 5:Tri, 6:Smartfren, 7:PLN): " p_cat
                read -p "Kode Provider Okeconnect (contoh: TD5): " p_code
                read -p "Nama Produk: " p_name
                read -p "Harga Modal Okeconnect (Rp): " p_orig
                read -p "Harga Jual ke User (Rp): " p_sell
                read -p "Tipe (data/pulsa/pln): " p_type
                read -p "Deskripsi: " p_desc

                php artisan tinker --execute="
                    \$prod = App\Models\Product::create([
                        'category_id' => '$p_cat',
                        'provider_code' => strtoupper('$p_code'),
                        'name' => '$p_name',
                        'price_original' => $p_orig,
                        'price_selling' => $p_sell,
                        'type' => '$p_type',
                        'status' => 'active',
                        'description' => '$p_desc'
                    ]);
                    echo 'Produk ' . \$prod->name . ' (' . \$prod->provider_code . ') berhasil ditambahkan!' . PHP_EOL;
                "
                pause
                ;;
            3)
                echo -e "\n${CYAN}--- UBAH HARGA PRODUK ---${NC}"
                read -p "Masukkan Kode Provider Produk (contoh: TDF3): " p_code
                read -p "Harga Jual Baru (Rp): " p_sell
                read -p "Harga Modal Baru (Rp) [biarkan kosong jika tidak diubah]: " p_orig

                php artisan tinker --execute="
                    \$p = App\Models\Product::where('provider_code', strtoupper('$p_code'))->first();
                    if (!\$p) { echo 'Produk tidak ditemukan!' . PHP_EOL; exit; }
                    \$p->price_selling = (float) '$p_sell';
                    if (!empty('$p_orig')) { \$p->price_original = (float) '$p_orig'; }
                    \$p->save();
                    echo 'Harga ' . \$p->name . ' diubah: Modal=Rp ' . number_format(\$p->price_original,0,',','.') . ', Jual=Rp ' . number_format(\$p->price_selling,0,',','.') . PHP_EOL;
                "
                pause
                ;;
            4)
                echo -e "\n${CYAN}--- UBAH STATUS PRODUK ---${NC}"
                read -p "Masukkan Kode Provider Produk: " p_code
                read -p "Status Baru (active / empty / inactive): " p_st
                php artisan tinker --execute="
                    \$p = App\Models\Product::where('provider_code', strtoupper('$p_code'))->first();
                    if (!\$p) { echo 'Produk tidak ditemukan!' . PHP_EOL; exit; }
                    \$p->status = '$p_st';
                    \$p->save();
                    echo 'Status produk ' . \$p->name . ' diubah menjadi ' . \$p->status . PHP_EOL;
                "
                pause
                ;;
            0)
                break
                ;;
        esac
    done
}

# ================= MENU 3: KELOLA TOPUP & SALDO =================
menu_topup() {
    while true; do
        header
        echo -e "${YELLOW}[ MENU ANTREAN TOPUP & SALDO ]${NC}"
        echo "1. Cek Daftar Permintaan Topup Pending"
        echo "2. Manual Approve Topup (Konfirmasi Masuk Saldo)"
        echo "3. Manual Reject Topup (Tolak Permintaan)"
        echo "4. Cek 10 Transaksi Pembelian Terkini"
        echo "0. Kembali ke Menu Utama"
        echo ""
        read -p "Pilih menu [0-4]: " t_opt

        case $t_opt in
            1)
                echo -e "\n${CYAN}--- ANTREAN TOPUP PENDING ---${NC}"
                php artisan tinker --execute="
                    \$pending = App\Models\Topup::with('user')->where('status', 'pending')->latest()->get();
                    if (\$pending->isEmpty()) { echo 'Tidak ada topup yang pending saat ini.' . PHP_EOL; exit; }
                    echo str_pad('INVOICE', 22) . str_pad('NAMA USER', 20) . str_pad('TOTAL BAYAR', 16) . 'WAKTU' . PHP_EOL;
                    echo str_repeat('-', 70) . PHP_EOL;
                    foreach(\$pending as \$t) {
                        echo str_pad(\$t->invoice_number, 22) . str_pad(substr(\$t->user->name ?? 'User', 0, 18), 20) . str_pad('Rp ' . number_format(\$t->total_amount, 0, ',', '.'), 16) . \$t->created_at->format('d/m H:i') . PHP_EOL;
                    }
                "
                pause
                ;;
            2)
                echo -e "\n${CYAN}--- APPROVE / TERIMA TOPUP ---${NC}"
                read -p "Masukkan Nomor Invoice Topup (contoh: TOP-20261005-XXXXX): " t_inv
                php artisan tinker --execute="
                    \$tele = app(App\Services\TelegramService::class);
                    \$res = \$tele->processTopupApproval('$t_inv', null, null, null, true, 'Admin CLI');
                    print_r(\$res);
                "
                pause
                ;;
            3)
                echo -e "\n${CYAN}--- REJECT / TOLAK TOPUP ---${NC}"
                read -p "Masukkan Nomor Invoice Topup: " t_inv
                php artisan tinker --execute="
                    \$tele = app(App\Services\TelegramService::class);
                    \$res = \$tele->processTopupApproval('$t_inv', null, null, null, false, 'Admin CLI');
                    print_r(\$res);
                "
                pause
                ;;
            4)
                echo -e "\n${CYAN}--- 10 TRANSAKSI PEMBELIAN TERBARU ---${NC}"
                php artisan tinker --execute="
                    foreach(App\Models\Transaction::latest()->take(10)->get() as \$trx) {
                        echo '[' . \$trx->status . '] ' . \$trx->invoice_number . ' | ' . \$trx->destination_number . ' | ' . \$trx->product_name . ' | Rp ' . number_format(\$trx->amount,0,',','.') . ' (SN: ' . (\$trx->sn_or_token ?: '-') . ')' . PHP_EOL;
                    }
                "
                pause
                ;;
            0)
                break
                ;;
        esac
    done
}

# ================= MENU 4: KELOLA API & TELEGRAM =================
menu_api_tele() {
    while true; do
        header
        echo -e "${YELLOW}[ MENU PENGATURAN API & BOT TELEGRAM ]${NC}"
        echo "1. Cek Konfigurasi API & Telegram Saat Ini"
        echo "2. Set Kredensial API Okeconnect (Member ID, PIN, Password, Base URL)"
        echo "3. Cek Saldo H2H Server Okeconnect"
        echo "4. Set Telegram Bot Token & Admin Chat ID"
        echo "5. Pasang / Update Webhook Telegram"
        echo "6. Test Kirim Pesan Uji Coba ke Admin Telegram"
        echo "0. Kembali ke Menu Utama"
        echo ""
        read -p "Pilih menu [0-6]: " a_opt

        case $a_opt in
            1)
                echo -e "\n${CYAN}--- KONFIGURASI SAAT INI ---${NC}"
                php artisan tinker --execute="
                    echo 'Okeconnect Member ID : ' . App\Models\Setting::get('okeconnect_member_id', '-') . PHP_EOL;
                    echo 'Okeconnect Base URL   : ' . App\Models\Setting::get('okeconnect_base_url', '-') . PHP_EOL;
                    echo 'Okeconnect Sandbox    : ' . (App\Models\Setting::get('okeconnect_sandbox_mode', '1') == '1' ? 'AKTIF (Simulasi)' : 'LIVE') . PHP_EOL;
                    echo 'Telegram Bot Token    : ' . substr(App\Models\Setting::get('telegram_bot_token', ''), 0, 15) . '...' . PHP_EOL;
                    echo 'Telegram Admin Chat ID: ' . App\Models\Setting::get('telegram_admin_chat_id', '-') . PHP_EOL;
                "
                pause
                ;;
            2)
                echo -e "\n${CYAN}--- SET KREDENSIAL OKECONNECT ---${NC}"
                read -p "Member ID (contoh: OK12345): " ok_id
                read -p "PIN Transaksi: " ok_pin
                read -p "Password API: " ok_pw
                read -p "Base URL [default: https://h2h.okeconnect.com]: " ok_url
                ok_url=${ok_url:-https://h2h.okeconnect.com}
                read -p "Gunakan Sandbox Simulasi? (1 = Ya, 0 = Live) [default: 1]: " ok_sb
                ok_sb=${ok_sb:-1}

                php artisan tinker --execute="
                    App\Models\Setting::set('okeconnect_member_id', '$ok_id');
                    App\Models\Setting::set('okeconnect_pin', '$ok_pin');
                    App\Models\Setting::set('okeconnect_password', '$ok_pw');
                    App\Models\Setting::set('okeconnect_base_url', '$ok_url');
                    App\Models\Setting::set('okeconnect_sandbox_mode', '$ok_sb');
                    echo 'Kredensial Okeconnect berhasil disimpan!' . PHP_EOL;
                "
                pause
                ;;
            3)
                echo -e "\n${CYAN}--- CEK SALDO H2H SERVER OKECONNECT ---${NC}"
                php artisan tinker --execute="
                    \$svc = app(App\Services\OkeconnectService::class);
                    \$res = \$svc->checkH2HBalance();
                    print_r(\$res);
                "
                pause
                ;;
            4)
                echo -e "\n${CYAN}--- SET TELEGRAM BOT TOKEN & ADMIN CHAT ID ---${NC}"
                read -p "Bot Token dari @BotFather: " tele_token
                read -p "Admin Chat ID: " tele_chat

                php artisan tinker --execute="
                    App\Models\Setting::set('telegram_bot_token', '$tele_token');
                    App\Models\Setting::set('telegram_admin_chat_id', '$tele_chat');
                    echo 'Pengaturan Telegram berhasil disimpan!' . PHP_EOL;
                "
                pause
                ;;
            5)
                echo -e "\n${CYAN}--- PASANG WEBHOOK TELEGRAM ---${NC}"
                read -p "Masukkan URL Web Toko Anda (contoh: https://tokoanda.com): " site_url
                webhook_url="${site_url%/}/api/telegram/webhook"
                php artisan tinker --execute="
                    \$tele = app(App\Services\TelegramService::class);
                    \$res = \$tele->setWebhook('$webhook_url');
                    print_r(\$res);
                "
                pause
                ;;
            6)
                echo -e "\n${CYAN}--- TEST KIRIM PESAN TELEGRAM ---${NC}"
                php artisan tinker --execute="
                    \$tele = app(App\Services\TelegramService::class);
                    \$chatId = App\Models\Setting::get('telegram_admin_chat_id');
                    if (!\$chatId) { echo 'Admin Chat ID belum diisi!' . PHP_EOL; exit; }
                    \$tele->sendMessage(\$chatId, '🚀 <b>Test Notifikasi Berhasil!</b>\nServer Tokonet H2H terhubung dengan lancar ke Bot Telegram Anda.');
                    echo 'Pesan test telah dikirim ke chat ID ' . \$chatId . PHP_EOL;
                "
                pause
                ;;
            0)
                break
                ;;
        esac
    done
}

# ================= MENU 5: KELOLA QRIS STATIS =================
menu_qris() {
    while true; do
        header
        echo -e "${YELLOW}[ MENU PENGATURAN QRIS STATIS & TEST CONVERTER ]${NC}"
        echo "1. Cek String QRIS Statis Saat Ini"
        echo "2. Set String Payload QRIS Statis Toko"
        echo "3. Test Generate String QRIS Dinamis dengan Nominal Tertentu"
        echo "0. Kembali ke Menu Utama"
        echo ""
        read -p "Pilih menu [0-3]: " q_opt

        case $q_opt in
            1)
                echo -e "\n${CYAN}--- PAYLOAD QRIS STATIS SAAT INI ---${NC}"
                php artisan tinker --execute="
                    echo App\Models\Setting::get('qris_static_string', 'Belum disetel') . PHP_EOL;
                "
                pause
                ;;
            2)
                echo -e "\n${CYAN}--- SET PAYLOAD QRIS STATIS ---${NC}"
                echo "Tempelkan string hasil scan QRIS Statis (BCA/Shopee/GoPay/Dana/LinkAja/Nobu):"
                read -p "String QRIS: " q_str
                php artisan tinker --execute="
                    App\Models\Setting::set('qris_static_string', trim('$q_str'));
                    echo 'String QRIS Statis berhasil disimpan!' . PHP_EOL;
                "
                pause
                ;;
            3)
                echo -e "\n${CYAN}--- TEST DECODE & CONVERT KE QRIS DINAMIS ---${NC}"
                read -p "Masukkan Nominal Uji Coba (contoh: 50125): " q_test_amt
                php artisan tinker --execute="
                    \$qris = app(App\Services\QrisService::class);
                    \$static = App\Models\Setting::get('qris_static_string');
                    \$dynamic = \$qris->convertStaticToDynamic(\$static, $q_test_amt);
                    echo 'Static QRIS : ' . \$static . PHP_EOL . PHP_EOL;
                    echo 'Dynamic QRIS: ' . \$dynamic . PHP_EOL . PHP_EOL;
                    echo 'QR Preview URL: ' . \$qris->getQrCodeSvgUrl(\$dynamic) . PHP_EOL;
                "
                pause
                ;;
            0)
                break
                ;;
        esac
    done
}

# ================= MENU 6: SERVER MAINTENANCE =================
menu_maintenance() {
    while true; do
        header
        echo -e "${YELLOW}[ MENU MAINTENANCE & SYSTEM TOOLS ]${NC}"
        echo "1. Bersihkan & Optimasi Cache Laravel (config, route, view)"
        echo "2. Jalankan Database Migration"
        echo "3. Restart Nginx & PHP-FPM Service"
        echo "4. Restart Queue Worker (Supervisor)"
        echo "5. Backup Database"
        echo "6. Lihat Live Log Laravel (storage/logs/laravel.log)"
        echo "7. Tarik Pembaruan Terbaru dari GitHub (Git Pull & Update)"
        echo "0. Kembali ke Menu Utama"
        echo ""
        read -p "Pilih menu [0-7]: " m_opt

        case $m_opt in
            1)
                echo -e "\n${CYAN}Membersihkan & Mengoptimasi Cache...${NC}"
                php artisan optimize:clear
                php artisan optimize
                pause
                ;;
            2)
                echo -e "\n${CYAN}Menjalankan Migrasi Database...${NC}"
                php artisan migrate --force
                pause
                ;;
            3)
                echo -e "\n${CYAN}Merestart Web Server & PHP-FPM...${NC}"
                systemctl restart nginx
                systemctl restart php8.3-fpm 2>/dev/null || systemctl restart php8.2-fpm 2>/dev/null || true
                echo -e "${GREEN}Web server & PHP berhasil direstart!${NC}"
                pause
                ;;
            4)
                echo -e "\n${CYAN}Merestart Supervisor Queue Worker...${NC}"
                supervisorctl restart all 2>/dev/null || php artisan queue:restart
                echo -e "${GREEN}Queue worker direstart!${NC}"
                pause
                ;;
            5)
                echo -e "\n${CYAN}Membuat Backup Database...${NC}"
                BACKUP_FILE="storage/app/backup_$(date +%Y%m%d_%H%M%S).sqlite"
                if [ -f "database/database.sqlite" ]; then
                    cp database/database.sqlite "$BACKUP_FILE"
                    echo -e "${GREEN}Database SQLite berhasil dibackup ke: ${BACKUP_FILE}${NC}"
                else
                    echo -e "${YELLOW}Gunakan mysqldump untuk database MySQL/MariaDB.${NC}"
                fi
                pause
                ;;
            6)
                echo -e "\n${CYAN}Menampilkan 30 baris log terakhir (Tekan Ctrl+C untuk keluar):${NC}\n"
                tail -n 30 -f storage/logs/laravel.log 2>/dev/null || echo "Belum ada file log."
                pause
                ;;
            7)
                echo -e "\n${CYAN}Menarik update dari GitHub...${NC}"
                bash update-vps.sh 2>/dev/null || (git pull && composer install --no-dev --optimize-autoloader --ignore-platform-reqs && php artisan migrate --force && php artisan optimize)
                pause
                ;;
            0)
                break
                ;;
        esac
    done
}

# ================= MAIN LOOP =================
while true; do
    header
    echo -e "${BOLD}Pilih Menu Manajemen:${NC}"
    echo -e "  ${GREEN}1.${NC} Kelola Pengguna & Saldo (User / Admin)"
    echo -e "  ${GREEN}2.${NC} Kelola Produk & Harga Jual H2H"
    echo -e "  ${GREEN}3.${NC} Kelola Antrean Topup QRIS & Transaksi"
    echo -e "  ${GREEN}4.${NC} Konfigurasi API Okeconnect & Bot Telegram"
    echo -e "  ${GREEN}5.${NC} Konfigurasi & Test QRIS Statis / Dinamis"
    echo -e "  ${GREEN}6.${NC} Maintenance Server, Cache, & Database"
    echo -e "  ${RED}0.${NC} Keluar dari Script"
    echo ""
    read -p "Masukkan pilihan Anda [0-6]: " main_opt

    case $main_opt in
        1) menu_user ;;
        2) menu_produk ;;
        3) menu_topup ;;
        4) menu_api_tele ;;
        5) menu_qris ;;
        6) menu_maintenance ;;
        0)
            echo -e "\n${GREEN}Terima kasih. Sampai jumpa!${NC}\n"
            exit 0
            ;;
        *)
            echo -e "\n${RED}Pilihan tidak valid!${NC}"
            sleep 1
            ;;
    esac
done
