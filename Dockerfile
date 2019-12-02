# Use Apache server with PHP version 7.2.
FROM php:7.2-apache

# Install Postgres PDO
RUN apt-get update && \
    apt-get install -y libpq-dev && \
    docker-php-ext-install pdo_pgsql

# Copy composer
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Certs
# ADD .certs/server.crt /etc/apache2/ssl/server.crt
# ADD .certs/server.key /etc/apache2/ssl/server.key

# Apache config
ADD .deploy/apache/apache-config.conf /etc/apache2/sites-enabled/000-default.conf

# Enable modules
RUN a2enmod rewrite
RUN a2enmod headers
RUN a2enmod ssl

# PHP ini
CMD cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini
ADD .deploy/apache/php-dev.ini /usr/local/etc/php/conf.d/xx_lmanager.ini

# Servername config
CMD echo "ServerName dev.lmanager.test" >> /etc/apache2/conf-available/servername.conf
CMD a2enconf servername
CMD service apache2 reload

# Composer - install
CMD /usr/bin/composer install
#CMD /usr/sbin/apache2ctl -D FOREGROUND
