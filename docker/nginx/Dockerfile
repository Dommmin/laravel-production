FROM nginx:stable-alpine

# Remove default nginx configuration
RUN rm /etc/nginx/conf.d/default.conf

# Create nginx user if it doesn't exist
RUN adduser -D -H -u 101 -s /sbin/nologin nginx || true

# Create required directories with proper permissions
RUN mkdir -p /var/cache/nginx /var/run /var/log/nginx /var/www && \
    chown -R nginx:nginx /var/cache/nginx /var/run /var/log/nginx /var/www && \
    chmod -R 755 /var/cache/nginx /var/run /var/log/nginx /var/www

# Copy custom configuration
COPY docker/nginx/conf.d/default.conf /etc/nginx/conf.d/

# Switch to non-root user
USER nginx

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"] 