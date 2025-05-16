FROM dommin/php-8.4-fpm-alpine:latest

USER root

RUN sed -i 's/user = www-data/user = www-data/g' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/group = www-data/group = www-data/g' /usr/local/etc/php-fpm.d/www.conf

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts

COPY --chown=www-data:www-data . .
COPY --chown=www-data:www-data --from=node /var/www/public/build /var/www/public/build
COPY --chown=www-data:www-data docker/supervisord.conf /etc/supervisord.conf
COPY --chown=www-data:www-data docker/php/php.ini /usr/local/etc/php/php.ini
COPY --chown=www-data:www-data docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

RUN mkdir -p \
    storage/framework/{cache,sessions,views} \
    storage/app/public \
    public/storage \
    bootstrap/cache \
    storage/logs \
    /var/log/supervisor \
    /var/run/php \
    /var/log/php-fpm \
    /home/www-data/.cache/puppeteer \
 && chown -R www-data:www-data \
    storage \
    bootstrap/cache \
    public \
    /var/log/supervisor \
    /var/run/php \
    /var/log/php-fpm \
    /home/www-data/.cache/puppeteer \
 && chmod -R 775 storage bootstrap/cache

RUN ln -s /var/www/storage/app/public /var/www/public/storage

USER www-data

EXPOSE 9000

CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisord.conf"]
