FROM node:22-alpine AS node-builder

WORKDIR /var/www

# Copy only package files first to leverage cache
COPY package*.json ./
RUN npm ci

# Copy remaining files needed for build
COPY vite.config.ts tsconfig.json ./
COPY . .

RUN npm run build

FROM nginx:stable-alpine

# Create necessary directories first
RUN mkdir -p /var/www/public

# Copy only the built assets from node-builder (not the entire public directory)
COPY --from=node-builder /var/www/public/build /var/www/public/build

# Copy base public files (index.php, robots.txt, etc.)
COPY public/*.php public/*.txt public/*.ico /var/www/public/

# Copy nginx configuration
COPY docker/nginx/conf.d /etc/nginx/conf.d

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
