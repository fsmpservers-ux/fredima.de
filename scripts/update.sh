echo "Entering Root enviroment..."
sudo -s
echo "Pulling newest changes..."
cd /var/www/fredima.de/
git pull > ./null.txt
echo "Fixing permissions..."
chown -R http:http ./*
echo "Reloading Webserver..."
systemctl reload nginx.service

