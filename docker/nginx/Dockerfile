FROM nginx:stable-alpine

# Declare build arguments
ARG HTPASSWD_USER
ARG HTPASSWD_PASS

# Create necessary directories first
RUN mkdir -p /var/www/public

# Copy only the built assets from node-builder (not the entire public directory)
COPY --from=node /var/www/public/build /var/www/public/build

# Copy base public files (index.php, robots.txt, etc.)
COPY public/*.php public/*.txt public/*.ico /var/www/public/

# Copy nginx configuration
COPY docker/nginx/conf.d /etc/nginx/conf.d

# Create .htpasswd file for basic auth
RUN if [ -n "$HTPASSWD_USER" ] && [ -n "$HTPASSWD_PASS" ]; then \
    apk add --no-cache apache2-utils && \
    htpasswd -b -c /etc/nginx/.htpasswd "$HTPASSWD_USER" "$HTPASSWD_PASS"; \
    fi

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
