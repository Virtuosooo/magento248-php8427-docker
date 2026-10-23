# syntax=docker/dockerfile:1
FROM php:8.4.27-apache-bookworm AS runtime
RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip openssl libicu-dev libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
    libonig-dev libxml2-dev libxslt1-dev libzip-dev libsodium-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j2 bcmath gd intl mbstring mysqli opcache pdo_mysql soap sockets sodium xsl zip \
 && rm -rf /var/lib/apt/lists/* \
 && a2enmod rewrite ssl
RUN docker-php-ext-install -j2 ftp
RUN apt-get update && DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends msmtp && rm -rf /var/lib/apt/lists/*
COPY msmtprc /etc/msmtprc
COPY composer.phar /usr/local/bin/composer
RUN chmod +x /usr/local/bin/composer
COPY php.ini /usr/local/etc/php/conf.d/zz-magento.ini
COPY apache.conf /etc/apache2/sites-available/000-default.conf
WORKDIR /var/www/html

FROM runtime AS app
COPY --chown=www-data:www-data src/ /opt/magento/
COPY --chmod=755 entrypoint.sh /usr/local/bin/magento-entrypoint
COPY install.php /usr/local/bin/install-magento.php
VOLUME /var/www/html /etc/apache2/local-tls
ENTRYPOINT ["magento-entrypoint"]
CMD ["apache2-foreground"]
