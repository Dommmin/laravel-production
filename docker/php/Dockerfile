FROM dommin/php-8.4-fpm-alpine:latest

USER root

RUN addgroup -g 1000 appuser && \
    adduser -D -u 1000 -G appuser appuser

RUN sed -i 's/user = www-data/user = appuser/g' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/group = www-data/group = appuser/g' /usr/local/etc/php-fpm.d/www.conf

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY composer.json composer.lock ./
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

# Declare build arguments
ARG HTPASSWD_USER
ARG HTPASSWD_PASS

# Debug step to print environment variables
RUN echo "HTPASSWD_USER: $HTPASSWD_USER" && echo "HTPASSWD_PASS: $HTPASSWD_PASS"

# Install apache2-utils and create .htpasswd using secrets
RUN apk update && apk add --no-cache apache2-utils \
    && htpasswd -bc /etc/nginx/.htpasswd $HTPASSWD_USER $HTPASSWD_PASS

USER appuser

EXPOSE 9000

CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisord.conf"]
