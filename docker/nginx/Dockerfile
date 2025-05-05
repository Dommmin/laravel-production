FROM node:22-alpine AS node-builder

WORKDIR /var/www

COPY package*.json vite.config.ts tsconfig.json ./
COPY resources resources

RUN npm ci
RUN npm run build

FROM nginx:stable-alpine

# Copy the entire public directory
COPY --from=node-builder /var/www/public /var/www/public

# Copy Nginx configuration
COPY docker/nginx/conf.d /etc/nginx/conf.d

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
