FROM dommin/php-8.4-fpm-alpine:latest

WORKDIR /var/www

COPY composer.json composer.lock ./
COPY docker/php/.env .env

RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts

COPY --chown=appuser:appuser . .

COPY --chown=appuser:appuser --from=node /var/www/public/build /var/www/public/build

COPY --chown=appuser:appuser docker/supervisord.conf /etc/supervisord.conf
COPY --chown=appuser:appuser docker/php/php.ini /usr/local/etc/php/php.ini
COPY --chown=appuser:appuser docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

RUN mkdir -p \
    storage/framework/{cache,sessions,views} \
    storage/app/public \
    public/storage \
    bootstrap/cache \
    storage/logs \
    /var/log/supervisor \
    /var/run/php \
    /var/log/php-fpm \
    /home/appuser/.cache/puppeteer \
 && chown -R appuser:appuser \
    storage \
    bootstrap/cache \
    public \
    /var/log/supervisor \
    /var/run/php \
    /var/log/php-fpm \
    /home/appuser/.cache/puppeteer \
 && chmod -R 775 storage bootstrap/cache

RUN ln -s /var/www/storage/app/public /var/www/public/storage

EXPOSE 9000

USER appuser

CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisord.conf"]
