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
echo "📁 Setting up data directory..."
mkdir -p /var/www/fredima.de/data
chmod 755 /var/www/fredima.de/data
chown -R http:http /var/www/fredima.de/data

echo ""
echo "🔧 Checking PHP-FPM..."
if systemctl is-active --quiet php-fpm; then
    echo "✅ PHP-FPM is running"
else
    echo "⚠️  Starting PHP-FPM..."
    systemctl start php-fpm
fi

# Ensure PHP-FPM is enabled on boot
systemctl enable php-fpm

echo ""
echo "🔒 Fixing permissions..."
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

echo ""
echo "🔄 Restarting services..."
systemctl restart php-fpm
systemctl restart nginx

echo ""
echo "========================================"
echo "✅ Update complete!"
echo "========================================"
echo ""
echo "Test your site:"
echo "  • https://fredima.de"
echo "  • https://fredima.de/stats"
echo "  • https://fredima.de/api/stats.php"
echo ""
echo "Services status:"
systemctl is-active --quiet nginx && echo "  ✅ Nginx: Running" || echo "  ❌ Nginx: Failed"
systemctl is-active --quiet php-fpm && echo "  ✅ PHP-FPM: Running" || echo "  ❌ PHP-FPM: Failed"
echo ""
