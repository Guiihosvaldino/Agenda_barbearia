FROM php:8.2-apache

RUN docker-php-ext-install pdo_mysql

COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf
COPY . /var/www/html
COPY docker-entrypoint.sh /usr/local/bin/barbearia-entrypoint

RUN chmod +x /usr/local/bin/barbearia-entrypoint \
    && chown -R www-data:www-data /var/www/html

EXPOSE 8080

ENTRYPOINT ["barbearia-entrypoint"]
