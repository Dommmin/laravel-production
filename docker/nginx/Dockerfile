FROM node:22-alpine AS node-builder

WORKDIR /var/www

# Dodajemy argumenty budowania dla zmiennych Vite
ARG VITE_REVERB_APP_KEY
ARG VITE_REVERB_HOST
ARG VITE_REVERB_PORT
ARG VITE_REVERB_SCHEME

# Ustawiamy zmienne środowiskowe dla etapu budowania
ENV VITE_REVERB_APP_KEY=$VITE_REVERB_APP_KEY
ENV VITE_REVERB_HOST=$VITE_REVERB_HOST
ENV VITE_REVERB_PORT=$VITE_REVERB_PORT
ENV VITE_REVERB_SCHEME=$VITE_REVERB_SCHEME

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
