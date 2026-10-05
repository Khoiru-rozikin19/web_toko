#!/bin/bash
# ==============================================================================
# Script Auto-Deploy Website Toko Pulsa & Paket Data H2H (Okeconnect + QRIS Dinamis)
# OS Target: Ubuntu 24.04 LTS
# Author: Senior Developer
# ==============================================================================

set -e

# Warna Output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m'

echo -e "${CYAN}==============================================================================${NC}"
echo -e "${PURPLE}  🚀 DEPLOYMENT SERVER TOKO PULSA & PAKET DATA H2H (OKECONNECT + QRIS) ${NC}"
echo -e "${CYAN}  Target OS: Ubuntu 24.04 LTS (Noble Numbat) ${NC}"
echo -e "${CYAN}==============================================================================${NC}"

# Cek Root Privilege
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}[ERROR] Script ini harus dijalankan sebagai ROOT (sudo bash deploy-vps.sh)${NC}"
    exit 1
fi

# Input Parameter dari Pengguna
read -p "Masukkan Domain/Subdomain Anda (contoh: toko.domainanda.com): " DOMAIN_NAME
read -p "Masukkan Email untuk SSL Let's Encrypt: " SSL_EMAIL
read -p "Pilih Database [1] SQLite (Rekomendasi Cepat) / [2] MariaDB/MySQL [1/2]: " DB_CHOICE

APP_DIR="/var/www/web_toko"
PHP_VER="8.3"

echo -e "\n${YELLOW}>>> [1/9] Mengupdate sistem dan dependensi Ubuntu 24.04...${NC}"
export DEBIAN_FRONTEND=noninteractive
apt-get update -y
apt-get upgrade -y
apt-get install -y software-properties-common curl wget git unzip zip ufw certbot python3-certbot-nginx supervisor redis-server sqlite3

echo -e "\n${YELLOW}>>> [2/9] Menginstall Nginx & PHP ${PHP_VER}-FPM...${NC}"
add-apt-repository -y ppa:ondrej/php
apt-get update -y

apt-get install -y nginx \
    php${PHP_VER}-fpm \
    php${PHP_VER}-cli \
    php${PHP_VER}-common \
    php${PHP_VER}-mysql \
    php${PHP_VER}-sqlite3 \
    php${PHP_VER}-curl \
    php${PHP_VER}-mbstring \
    php${PHP_VER}-xml \
    php${PHP_VER}-zip \
    php${PHP_VER}-bcmath \
    php${PHP_VER}-intl \
    php${PHP_VER}-redis \
    php${PHP_VER}-gd

echo -e "\n${YELLOW}>>> [3/9] Menginstall Composer...${NC}"
if ! command -v composer &> /dev/null; then
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

# Salin file saat ini HANYA jika dijalankan dari luar folder /var/www/web_toko
CURRENT_PWD=$(pwd -P)
TARGET_PWD=$(mkdir -p $APP_DIR && cd $APP_DIR && pwd -P)

if [ "$CURRENT_PWD" != "$TARGET_PWD" ]; then
    if [ -f "./artisan" ]; then
        cp -r ./* $APP_DIR/ 2>/dev/null || true
        cp -r ./.env* $APP_DIR/ 2>/dev/null || true
    fi
fi

cd $APP_DIR

# Setup .env first
if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
    else
        touch .env
    fi
fi

sed -i "s|APP_URL=.*|APP_URL=https://${DOMAIN_NAME}|" .env
sed -i "s/APP_ENV=.*/APP_ENV=production/" .env
sed -i "s/APP_DEBUG=.*/APP_DEBUG=false/" .env

# Setup Database
echo -e "\n${YELLOW}>>> [5/9] Mengkonfigurasi Database...${NC}"
sed -i '/^DB_/d' .env

if [ "$DB_CHOICE" == "2" ]; then
    apt-get install -y mariadb-server
    systemctl start mariadb
    systemctl enable mariadb
    
    DB_NAME="web_toko"
    DB_USER="toko_user"
    DB_PASS=$(openssl rand -base64 16 | tr -dc 'a-zA-Z0-9' | head -c 16)
    
    mysql -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
    mysql -e "GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';"
    mysql -e "FLUSH PRIVILEGES;"
    
    cat >> .env <<EOF
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=${DB_NAME}
DB_USERNAME=${DB_USER}
DB_PASSWORD=${DB_PASS}
EOF
    echo -e "${GREEN}MariaDB Database '${DB_NAME}' & User '${DB_USER}' berhasil dibuat & disambungkan!${NC}"
else
    mkdir -p $APP_DIR/database
    touch $APP_DIR/database/database.sqlite
    cat >> .env <<EOF
DB_CONNECTION=sqlite
DB_DATABASE=${APP_DIR}/database/database.sqlite
EOF
    echo -e "${GREEN}Database SQLite aktif di ${APP_DIR}/database/database.sqlite${NC}"
fi

echo -e "\n${YELLOW}>>> [6/9] Menginstall Paket Composer & Optimasi Laravel...${NC}"
export COMPOSER_ALLOW_SUPERUSER=1
if ! composer install --no-dev --optimize-autoloader --ignore-platform-reqs; then
    echo -e "${YELLOW}Lock file berbeda platform, menjalankan composer update...${NC}"
    composer update --no-dev --optimize-autoloader --ignore-platform-reqs
fi

php artisan key:generate --force
php artisan config:clear
php artisan storage:link 2>/dev/null || true
php artisan migrate --force
php artisan db:seed --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set Permissions
chown -R www-data:www-data $APP_DIR
chmod -R 775 $APP_DIR/storage $APP_DIR/bootstrap/cache $APP_DIR/database

echo -e "\n${YELLOW}>>> [7/9] Mengkonfigurasi Nginx Virtual Host...${NC}"
NGINX_CONF="/etc/nginx/sites-available/web_toko"

cat > $NGINX_CONF <<EOF
server {
    listen 80;
    listen [::]:80;
    server_name ${DOMAIN_NAME};
    root ${APP_DIR}/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php index.html;
    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php${PHP_VER}-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

ln -sf $NGINX_CONF /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl restart nginx

echo -e "\n${YELLOW}>>> [8/9] Mengkonfigurasi Supervisor Queue Worker & Cron...${NC}"
SUPERVISOR_CONF="/etc/supervisor/conf.d/web_toko_worker.conf"
cat > $SUPERVISOR_CONF <<EOF
[program:web_toko_worker]
process_name=%(program_name)s_%(process_num)02d
command=php ${APP_DIR}/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=${APP_DIR}/storage/logs/worker.log
EOF

supervisorctl reread
supervisorctl update
supervisorctl start all

# Setup Crontab for Laravel Scheduler
(crontab -l 2>/dev/null | grep -v "artisan schedule:run"; echo "* * * * * cd ${APP_DIR} && php artisan schedule:run >> /dev/null 2>&1") | crontab -

echo -e "\n${YELLOW}>>> [9/9] Mengkonfigurasi Firewall (UFW) & SSL...${NC}"
ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw --force enable

if [ "$DOMAIN_NAME" != "localhost" ] && [ -n "$SSL_EMAIL" ]; then
    echo -e "${CYAN}Memasang Sertifikat SSL Let's Encrypt...${NC}"
    certbot --nginx -d ${DOMAIN_NAME} --non-interactive --agree-tos -m ${SSL_EMAIL} --redirect || echo -e "${YELLOW}Certbot SSL dilewati atau belum terhubung DNS.${NC}"
fi

# Berikan izin execute ke script manage
chmod +x ${APP_DIR}/manage.sh 2>/dev/null || true

echo -e "\n${GREEN}==============================================================================${NC}"
echo -e "${GREEN}  🎉 DEPLOYMENT SELESAI DENGAN SUKSES! ${NC}"
echo -e "${GREEN}==============================================================================${NC}"
echo -e "Web Toko URL: ${CYAN}http://${DOMAIN_NAME}${NC} (atau https://${DOMAIN_NAME})"
echo -e "Admin Login : ${CYAN}admin@webtoko.com${NC} | Password: ${CYAN}admin123${NC}"
echo -e "Demo User   : ${CYAN}user@webtoko.com${NC}  | Password: ${CYAN}user123${NC}"
echo -e "Manajemen   : Jalankan ${YELLOW}sudo bash ${APP_DIR}/manage.sh${NC} di terminal VPS Anda!"
echo -e "${GREEN}==============================================================================${NC}"
