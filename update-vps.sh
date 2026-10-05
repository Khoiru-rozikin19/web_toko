#!/bin/bash
# ==============================================================================
# Script Update Cepat Tokonet H2H dari GitHub ke VPS
# Author: Senior Developer
# ==============================================================================

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$SCRIPT_DIR"

echo -e "${CYAN}======================================================${NC}"
echo -e "${GREEN}  🔄 MENGUPDATE TOKONET H2H DARI GITHUB KE VPS  ${NC}"
echo -e "${CYAN}======================================================${NC}"

echo -e "\n${YELLOW}>>> [1/5] Menarik update terbaru dari GitHub (git pull)...${NC}"
git pull origin main || git pull origin master

echo -e "\n${YELLOW}>>> [2/5] Memperbarui dependensi Composer...${NC}"
export COMPOSER_ALLOW_SUPERUSER=1
if ! composer install --no-dev --optimize-autoloader --ignore-platform-reqs; then
    composer update --no-dev --optimize-autoloader --ignore-platform-reqs
fi

echo -e "\n${YELLOW}>>> [3/5] Menjalankan migrasi database...${NC}"
php artisan migrate --force

echo -e "\n${YELLOW}>>> [4/5] Memperbarui & optimasi cache Laravel...${NC}"
php artisan optimize:clear
php artisan optimize

echo -e "\n${YELLOW}>>> [5/5] Merestart worker & permission...${NC}"
chown -R www-data:www-data storage bootstrap/cache database 2>/dev/null || true
chmod -R 775 storage bootstrap/cache database 2>/dev/null || true
supervisorctl restart all 2>/dev/null || php artisan queue:restart || true

echo -e "\n${GREEN}======================================================${NC}"
echo -e "${GREEN}  ✅ UPDATE BERHASIL DITERAPKAN DI SERVER VPS! ${NC}"
echo -e "${GREEN}======================================================${NC}"
