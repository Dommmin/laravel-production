FROM node:22-alpine AS node-builder

WORKDIR /var/www

COPY package*.json vite.config.ts tsconfig.json ./
COPY resources resources
COPY public public

RUN npm ci
RUN npm run build

FROM nginx:stable-alpine

# Upewnij się, że katalog /var/www/public istnieje i zawiera build
COPY --from=node-builder /var/www/public /var/www/public

# Skopiuj konfigurację Nginxa
COPY docker/nginx/conf.d /etc/nginx/conf.d

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
