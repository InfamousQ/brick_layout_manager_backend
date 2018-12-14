# Use Apache server with PHP version 7.2.
FROM php:7.2-apache

ADD .deploy/apache-config.conf /etc/apache2/sites-enabled/000-default.conf
RUN a2enmod rewrite
CMD echo "ServerName localhost" >> /etc/apache2/apache2.conf
