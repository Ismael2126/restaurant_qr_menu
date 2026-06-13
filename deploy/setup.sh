#!/bin/bash
#
# One-time server setup for a fresh Ubuntu 24.04 Oracle Cloud "Always Free"
# instance (Ampere A1 / ARM64). Installs Nginx, PHP 8.3, MariaDB, Composer,
# and Certbot, then configures and deploys this application.
#
# Usage (run from inside the cloned repo):
#   sudo bash deploy/setup.sh yourdomain.com you@example.com
#
# If you don't have a domain yet, pass the server's public IP instead and
# skip the SSL step at the end.

set -euo pipefail

DOMAIN="${1:-}"
EMAIL="${2:-}"
APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
APP_USER="${SUDO_USER:-ubuntu}"

if [ -z "$DOMAIN" ]; then
    echo "Usage: sudo bash deploy/setup.sh <domain-or-ip> <email-for-ssl>"
    exit 1
fi

echo "==> Installing system packages"
apt update
apt install -y nginx mariadb-server \
    php8.3-fpm php8.3-cli php8.3-mysql php8.3-mbstring php8.3-xml \
    php8.3-curl php8.3-zip php8.3-bcmath php8.3-gd php8.3-intl \
    unzip git certbot python3-certbot-nginx

echo "==> Installing Composer"
if ! command -v composer >/dev/null; then
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

echo "==> Opening firewall ports (ufw)"
ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw --force enable

echo "==> Allowing HTTP/HTTPS through iptables"
# Oracle's stock Ubuntu images ship iptables rules that drop everything
# except SSH, in addition to the VCN security list. Insert ACCEPT rules
# at the top so they're evaluated before any later DROP/REJECT rules.
iptables -C INPUT -p tcp --dport 80 -j ACCEPT 2>/dev/null || iptables -I INPUT -p tcp --dport 80 -j ACCEPT
iptables -C INPUT -p tcp --dport 443 -j ACCEPT 2>/dev/null || iptables -I INPUT -p tcp --dport 443 -j ACCEPT
netfilter-persistent save 2>/dev/null || true

echo "==> Creating MySQL database"
DB_NAME="restaurant_qr_menu"
DB_USER="restaurant_app"
DB_PASS="$(openssl rand -base64 24 | tr -dc 'A-Za-z0-9' | head -c 24)"

mysql -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
mysql -e "GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost'; FLUSH PRIVILEGES;"

echo "==> Configuring application .env"
cd "$APP_DIR"
if [ ! -f .env ]; then
    cp .env.example .env
fi

sed -i \
    -e "s|^APP_NAME=.*|APP_NAME=\"Restaurant QR Menu\"|" \
    -e "s|^APP_ENV=.*|APP_ENV=production|" \
    -e "s|^APP_DEBUG=.*|APP_DEBUG=false|" \
    -e "s|^APP_URL=.*|APP_URL=https://${DOMAIN}|" \
    -e "s|^DB_CONNECTION=.*|DB_CONNECTION=mysql|" \
    -e "s|^# *DB_HOST=.*|DB_HOST=127.0.0.1|" \
    -e "s|^# *DB_PORT=.*|DB_PORT=3306|" \
    -e "s|^# *DB_DATABASE=.*|DB_DATABASE=${DB_NAME}|" \
    -e "s|^# *DB_USERNAME=.*|DB_USERNAME=${DB_USER}|" \
    -e "s|^# *DB_PASSWORD=.*|DB_PASSWORD=${DB_PASS}|" \
    -e "s|^DB_HOST=.*|DB_HOST=127.0.0.1|" \
    -e "s|^DB_PORT=.*|DB_PORT=3306|" \
    -e "s|^DB_DATABASE=.*|DB_DATABASE=${DB_NAME}|" \
    -e "s|^DB_USERNAME=.*|DB_USERNAME=${DB_USER}|" \
    -e "s|^DB_PASSWORD=.*|DB_PASSWORD=${DB_PASS}|" \
    .env

echo "==> Installing PHP dependencies"
sudo -u "$APP_USER" composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Generating app key"
php artisan key:generate --force

echo "==> Running migrations"
php artisan migrate --force

echo "==> Linking storage"
php artisan storage:link

echo "==> Caching config/routes/views"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Setting permissions"
chown -R "$APP_USER":www-data "$APP_DIR"
chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
chmod 640 "$APP_DIR/.env"

echo "==> Configuring Nginx"
cat > /etc/nginx/sites-available/app <<NGINX
server {
    listen 80;
    listen [::]:80;
    server_name ${DOMAIN};
    root ${APP_DIR}/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php\$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
NGINX

ln -sf /etc/nginx/sites-available/app /etc/nginx/sites-enabled/app
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx

echo ""
echo "================================================================"
echo " Setup complete!"
echo ""
echo " Database:"
echo "   name:     ${DB_NAME}"
echo "   user:     ${DB_USER}"
echo "   password: ${DB_PASS}"
echo "   (already written into ${APP_DIR}/.env)"
echo ""
echo " Visit: http://${DOMAIN}"
echo ""
echo " Next steps:"
echo "  1. In the Oracle Cloud console, make sure your VCN Security List"
echo "     allows ingress TCP 80 and 443 from 0.0.0.0/0."
echo "  2. Point your domain's A record to this server's public IP."
echo "  3. Once DNS resolves, enable SSL:"
echo "     sudo certbot --nginx -d ${DOMAIN} -m ${EMAIL} --agree-tos --redirect"
echo "  4. Create your first admin user:"
echo "     cd ${APP_DIR} && php artisan tinker"
echo "     >>> App\\Models\\User::create(['name'=>'Admin','email'=>'you@example.com','password'=>'changeme','role'=>'admin']);"
echo "================================================================"
