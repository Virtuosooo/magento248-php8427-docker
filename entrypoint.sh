#!/bin/sh
set -eu
if [ ! -f /var/www/html/bin/magento ]; then
    cp -a /opt/magento/. /var/www/html/
    chown -R www-data:www-data /var/www/html
fi
if [ ! -f /etc/apache2/local-tls/localhost.key ]; then
    openssl req -x509 -nodes -newkey rsa:2048 -days 365 \
      -subj /CN=localhost -addext 'subjectAltName=DNS:localhost,IP:127.0.0.1' \
      -keyout /etc/apache2/local-tls/localhost.key \
      -out /etc/apache2/local-tls/localhost.crt >/dev/null 2>&1
    chmod 600 /etc/apache2/local-tls/localhost.key
fi
exec docker-php-entrypoint "$@"
