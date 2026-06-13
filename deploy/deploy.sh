#!/bin/bash
#
# Re-deploy script for subsequent updates after deploy/setup.sh has been run
# once. Pulls the latest code, installs dependencies, runs migrations, and
# refreshes caches.
#
# Usage (from inside the app directory):
#   bash deploy/deploy.sh

set -euo pipefail

cd "$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

echo "==> Pulling latest code"
git pull

echo "==> Installing PHP dependencies"
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Running migrations"
php artisan migrate --force

echo "==> Refreshing caches"
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Reloading PHP-FPM"
sudo systemctl reload php8.3-fpm

echo "Deploy complete."
