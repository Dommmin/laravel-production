FROM nginx:stable-alpine

# Create necessary directories first
RUN mkdir -p /var/www/public

# Copy only the built assets from node-builder (not the entire public directory)
COPY --from=node /var/www/public/build /var/www/public/build

# Copy base public files (index.php, robots.txt, etc.)
COPY public/*.php public/*.txt public/*.ico /var/www/public/

# Copy nginx configuration
COPY docker/nginx/conf.d /etc/nginx/conf.d

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
