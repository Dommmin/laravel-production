FROM node:22-alpine AS node-builder

WORKDIR /var/www

# Copy only package files first to leverage cache
COPY package*.json ./
RUN npm ci

# Copy remaining files needed for build
COPY vite.config.ts tsconfig.json ./
COPY resources resources
COPY public public

# Copy the rest of the application (including new/removed files)
COPY . .

RUN npm run build

FROM nginx:stable-alpine

# Copy built assets from node-builder
COPY --from=node-builder /var/www/public /var/www/public

# Copy nginx configuration
COPY docker/nginx/conf.d /etc/nginx/conf.d

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
