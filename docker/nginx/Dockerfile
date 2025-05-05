FROM nginx:stable-alpine

# Remove default nginx configuration (if it exists)
RUN rm -f /etc/nginx/conf.d/default.conf

# Create required directories with proper permissions
RUN mkdir -p /var/cache/nginx /var/run /var/log/nginx && \
    chmod -R 755 /var/cache/nginx /var/run /var/log/nginx

# Set proper permissions for nginx user
RUN chown -R nginx:nginx /var/cache/nginx /var/run /var/log/nginx /etc/nginx && \
    chmod -R 755 /var/cache/nginx /var/run /var/log/nginx /etc/nginx

COPY docker/nginx/conf.d/default.conf /etc/nginx/conf.d/

USER nginx
EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
