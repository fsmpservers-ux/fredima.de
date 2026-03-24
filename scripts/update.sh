if [[ $EUID -ne 0 ]]; then
   echo "This script must be run as root" 
   exit 1
fi
echo "Pulling newest changes..."
cd /var/www/fredima.de/
git pull
echo "Fixing permissions..."
chown -R http:http ./*
echo "Refreshing Nginx config..."
cp /var/www/fredima.de/nginx/fredimade.conf /etc/nginx/sites-enabled/fredima.de
echo "Reloading Webserver..."
systemctl reload nginx.service


