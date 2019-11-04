#!/usr/bin/env bash

#reglage de l'heure sur Paris
mv /etc/localtime /etc/localtime-old
ln -s /usr/share/zoneinfo/Europe/Paris /etc/localtime

#Suppression de AppArmor
/etc/init.d/apparmor stop
update-rc.d -f apparmor remove
apt-get --purge remove apparmor apparmor-utils libapparmor-perl libapparmor1 -y

#mongo key
wget -qO - https://www.mongodb.org/static/pgp/server-4.2.asc | sudo apt-key add -

#elastic search
wget -qO - https://artifacts.elastic.co/GPG-KEY-elasticsearch | sudo apt-key add -

#mongo repo
echo "deb [ arch=amd64 ] https://repo.mongodb.org/apt/ubuntu bionic/mongodb-org/4.2 multiverse" | sudo tee /etc/apt/sources.list.d/mongodb-org-4.2.list

#repo elastic
echo "deb https://artifacts.elastic.co/packages/7.x/apt stable main" | sudo tee -a /etc/apt/sources.list.d/elastic-7.x.list

# add php repo
add-apt-repository ppa:ondrej/php
add-apt-repository ppa:ondrej/apache2

#certbot
add-apt-repository ppa:certbot/certbot

#update
apt-get update
apt-get upgrade -y

#APACHE PHP..
apt-get install -y apt-transport-https elasticsearch mongodb-org certbot python3-pip awscli xfonts-75dpi xfonts-base whois openssl htop zip unzip software-properties-common libimage-exiftool-perl poppler-utils apache2 git-all libapache2-mod-php7.3 php7.3 php7.3-curl php7.3-soap php7.3-json php7.3-gd php-pear php7.3-dev php7.3-zip php7.3-mbstring php7.3-xml php-imagick php7.3-tidy php-mongodb

#certbot dns route53
pip3 install certbot-dns-route53

#WKHTMLTOPDF
wget https://downloads.wkhtmltopdf.org/0.12/0.12.5/wkhtmltox_0.12.5-1.bionic_amd64.deb
dpkg -i wkhtmltox_0.12.5-1.bionic_amd64.deb
apt-get -f install -y

#deployer php
curl -LO https://deployer.org/deployer.phar
mv deployer.phar /usr/local/bin/dep
chmod +x /usr/local/bin/dep

#storage www
mkdir -p /storage/www/framework
mkdir -p /storage/www/cert
chown -R modelo:www-data /storage/www
chmod -R 0777 /storage/www

#creation page index.php
echo '<?php phpinfo() ?>' >/storage/www/index.php
echo '<?php echo "ok"; ?>' >/storage/www/lbazure.php

# parametres apache security access
echo '<Directory /storage/www/>
        Options FollowSymLinks
        AllowOverride None
        Require all granted
</Directory>' >>/etc/apache2/apache2.conf

# parametres apache default vhost
echo '<VirtualHost *:80>
        ServerAdmin jp.domingo@gmail.com
        DocumentRoot /storage/www/framework/test/app/public/
        ServerName framework.modelo.test
        RewriteEngine On
        RewriteCond %{DOCUMENT_ROOT}%{REQUEST_FILENAME} !-f
        RewriteRule ^/(.+) /index.php [NC,L]
        <Directory /storage/www/framework/test/app/public/>
            DirectoryIndex index.php
            Options -Indexes
            AllowOverride None
            Require all granted
        </Directory>
</VirtualHost>' >/etc/apache2/sites-available/framework.conf

echo '<IfModule mod_alias.c>
      Alias /favicon.ico "/storage/www/framework/public/favicon.ico"
      </IfModule>
' >>/etc/apache2/apache2.conf
echo '<IfModule mod_ssl.c>
      <VirtualHost *:443>
      ServerAdmin jp.domingo@gmail.com
      DocumentRoot /storage/www/framework/test/app/public/
      ServerName framework.modelo.test
      RewriteEngine On
      RewriteCond %{DOCUMENT_ROOT}%{REQUEST_FILENAME} !-f
      RewriteRule ^/(.+) /index.php [NC,L]
      <Directory /storage/www/framework/test/app/public/>
      DirectoryIndex index.php
      Options -Indexes
      AllowOverride None
      Require all granted
      </Directory>
      SSLEngine on
      SSLCertificateFile /storage/www/cert/framework.crt
      SSLCertificateKeyFile /storage/www/cert/framework.key
      </VirtualHost>
      </IfModule>' >/etc/apache2/sites-available/framework-ssl.conf

echo '# mongodb.conf
storage:
  dbPath: /var/lib/mongodb
  journal:
    enabled: true
systemLog:
  destination: file
  logAppend: true
  path: /var/log/mongodb/mongod.log
net:
  port: 27017
  bindIp: 0.0.0.0
replication:
  replSetName: "rs0"
' >/etc/mongod.conf

#ssl
openssl req \
-x509 \
-nodes \
-new \
-newkey rsa:2048 \
-keyout /storage/www/cert/framework.key \
-out /storage/www/cert/framework.crt \
-sha256 \
-days 3650 \
-config <(
  cat <<EOF

[ req ]
prompt = no
distinguished_name = subject
x509_extensions    = x509_ext

[ subject ]
commonName = app.modelo.test

[ x509_ext ]
subjectAltName = @alternate_names

[ alternate_names ]
DNS.1 = framework.modelo.test
DNS.2 = *.modelo.test

EOF
)

# install composer
curl -sS https://getcomposer.org/installer -o composer-setup.php
php composer-setup.php --install-dir=/usr/local/bin --filename=composer

# active parametres apache
a2enmod php7.3
a2enmod ssl
a2enmod rewrite
a2enmod headers
service apache2 reload
a2ensite framework.conf
a2ensite framework-ssl.conf
systemctl enable apache2.service
update-rc.d apache2 enable
service apache2 start
systemctl enable mongod.service
service mongod start
systemctl enable elasticsearch
update-rc.d elasticsearch enable
service elasticsearch start

# lancer mongo et faire rs.initiate() pour activer un replicat set ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€šÃ‚Â ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¾Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¾ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã¢â‚¬Â¦Ãƒâ€šÃ‚Â¡ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â  un noeud (essentiel pour les transactions)
# faire un php /storage/www/modelo/composer.phar update
# lors de la premiere connexion doc.modelo.test/init.php (initialise les repertoires temporaires)
