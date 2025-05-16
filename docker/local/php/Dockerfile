FROM dommin/php-8.4-fpm:latest

COPY docker/local/start.sh /usr/local/bin/start.sh

USER root

ARG USER_ID=1000
ARG GROUP_ID=1000

WORKDIR /var/www

USER www-data

EXPOSE 9000
