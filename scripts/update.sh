#!/bin/bash
if [[ $EUID -ne 0 ]]; then
   echo "This script must be run as root"
   exit 1
fi

echo "========================================"
echo "🚀 fredima.de Update Script"
echo "========================================"

echo ""
echo "📥 Pulling newest changes..."
cd /var/www/fredima.de/
git pull

echo ""
echo "🔧 Checking PHP-FPM..."
if systemctl is-active --quiet php-fpm; then
    echo "✅ PHP-FPM is running"
else
    echo "⚠️  Starting PHP-FPM..."
    systemctl start php-fpm
fi

echo ""
echo "permissions..."
chown -R http:http /var/www/fredima.de/public
chown -R http:http /var/www/fredima.de/nginx
chown -R http:http /var/www/fredima.de/data
find /var/www/fredima.de/public -type f -exec chmod 644 {} \;
find /var/www/fredima.de/public -type d -exec chmod 755 {} \;

echo ""
echo "🌐 Refreshing Nginx config..."
cp /var/www/fredima.de/nginx/fredimade.conf /etc/nginx/sites-enabled/fredima.de

# Test nginx config before reloading
if nginx -t; then
    echo "✅ Nginx config is valid"
else
    echo "❌ Nginx config has errors! Aborting."
    exit 1
fi

# Erstelle .htpasswd Datei wenn sie nicht existiert
if [ ! -f /var/www/fredima.de/.htpasswd ]; then
    echo "🔐 Creating .htpasswd file..."
    touch /var/www/fredima.de/.htpasswd
fi

echo ""
echo "🔄 Restarting services..."
systemctl restart nginx

systemctl is-active --quiet nginx && echo "  ✅ Nginx: Running" || echo "  ❌ Nginx: Failed"
echo ""
