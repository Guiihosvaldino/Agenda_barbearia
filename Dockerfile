FROM php:8.2-apache

RUN docker-php-ext-install pdo_mysql \
    && for module in mpm_event mpm_worker; do \
        if [ -e "/etc/apache2/mods-enabled/${module}.load" ]; then \
            a2dismod "$module"; \
        fi; \
    done \
    && a2enmod mpm_prefork \
    && test "$(apache2ctl -M 2>/dev/null | grep -c 'mpm_')" -eq 1

COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf
COPY . /var/www/html
COPY docker-entrypoint.sh /usr/local/bin/barbearia-entrypoint

RUN chmod +x /usr/local/bin/barbearia-entrypoint \
    && chown -R www-data:www-data /var/www/html

EXPOSE 8080

ENTRYPOINT ["barbearia-entrypoint"]
