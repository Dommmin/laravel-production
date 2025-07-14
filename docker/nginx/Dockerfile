FROM nginx:stable-alpine

# Declare build arguments
ARG HTPASSWD_USER
ARG HTPASSWD_PASS

# Create necessary directories first
RUN mkdir -p /var/www/public

COPY --from=node /var/www/public/build /var/www/public/build

COPY public/*.php public/*.txt public/*.ico /var/www/public/

COPY docker/nginx/conf.d /etc/nginx/conf.d

# Create .htpasswd file for basic auth (optional)
RUN #if [ -n "$HTPASSWD_USER" ] && [ -n "$HTPASSWD_PASS" ]; then \
#    apk add --no-cache apache2-utils && \
#    htpasswd -b -c /etc/nginx/.htpasswd "$HTPASSWD_USER" "$HTPASSWD_PASS"; \
#fi

EXPOSE 80
EXPOSE 443

CMD ["nginx", "-g", "daemon off;"]
