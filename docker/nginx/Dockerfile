FROM node:22-alpine AS node-builder

WORKDIR /var/www

COPY package*.json vite.config.ts tsconfig.json ./
COPY resources resources

RUN npm ci
RUN npm run build

FROM nginx:stable-alpine

# Copy static files from node-builder
COPY --from=node-builder /var/www/public/build /var/www/public/build

# Copy Nginx configuration
COPY docker/nginx/conf.d /etc/nginx/conf.d

EXPOSE 80
