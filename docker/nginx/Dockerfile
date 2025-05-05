FROM nginx:stable-alpine

# Remove default nginx configuration
RUN rm /etc/nginx/conf.d/default.conf

# Create required directories with proper permissions
RUN mkdir -p /var/cache/nginx /var/run /var/log/nginx && \
    chmod -R 755 /var/cache/nginx /var/run /var/log/nginx

# Copy custom configuration
COPY docker/nginx/conf.d/default.conf /etc/nginx/conf.d/

# Create nginx user if it doesn't exist
RUN adduser -D -H -u 101 -s /sbin/nologin nginx || true

# Set proper permissions for nginx user
RUN chown -R nginx:nginx /var/cache/nginx /var/run /var/log/nginx /etc/nginx && \
    chmod -R 755 /var/cache/nginx /var/run /var/log/nginx /etc/nginx

# Create pid directory with proper permissions
RUN mkdir -p /run && \
    chown -R nginx:nginx /run && \
    chmod -R 755 /run

# Switch to non-root user
USER nginx

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"] 