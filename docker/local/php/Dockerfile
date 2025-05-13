FROM dommin/php-8.4-fpm:latest

COPY docker/local/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/local/php/www.conf /usr/local/etc/php-fpm.d/www.conf

COPY docker/local/start.sh /usr/local/bin/start.sh
COPY docker/local/supervisord.conf /etc/supervisor/supervisord.conf

USER root

ARG USER_ID=1000
ARG GROUP_ID=1000

RUN usermod -u ${USER_ID} www-data \
    && groupmod -g ${GROUP_ID} www-data \
    && chmod +x /usr/local/bin/start.sh \
    && mkdir -p /var/log/supervisor /var/run/supervisor \
    && chown -R www-data:www-data /var/www /var/log/supervisor /var/run/supervisor \
    && apt-get update \
    && apt-get install -y wget gnupg2 \
    && wget -q -O - https://dl-ssl.google.com/linux/linux_signing_key.pub | apt-key add - \
    && echo "deb [arch=amd64] http://dl.google.com/linux/chrome/deb/ stable main" > /etc/apt/sources.list.d/google-chrome.list \
    && apt-get update \
    && apt-get install -y google-chrome-stable \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www
USER www-data

EXPOSE 9000
