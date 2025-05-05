FROM nginx:stable-alpine

COPY docker/nginx/conf.d /etc/nginx/conf.d
COPY --from=php-stage /var/www/public /var/www/public

EXPOSE 80
