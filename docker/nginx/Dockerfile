FROM nginx:stable-alpine

# Remove default nginx configuration (if it exists)
RUN rm -f /etc/nginx/conf.d/default.conf

# Create custom nginx.conf that doesn't use the "user" directive
RUN sed -i '/user  nginx;/d' /etc/nginx/nginx.conf

# Configure nginx to use a PID file in a location the nginx user can write to
RUN mkdir -p /tmp/nginx && \
    sed -i 's|pid        /var/run/nginx.pid;|pid        /tmp/nginx/nginx.pid;|g' /etc/nginx/nginx.conf

# Create required directories with proper permissions
RUN mkdir -p /var/cache/nginx /tmp/nginx /var/log/nginx && \
    chmod -R 755 /var/cache/nginx /tmp/nginx /var/log/nginx

# Set proper permissions for nginx user
RUN chown -R nginx:nginx /var/cache/nginx /tmp/nginx /var/log/nginx /etc/nginx && \
    chmod -R 755 /var/cache/nginx /tmp/nginx /var/log/nginx /etc/nginx

COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/conf.d/default.conf /etc/nginx/conf.d/

USER nginx
EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
