# Docker Deployment Guide for Laravel on VPS with GitHub Actions

This guide will help you set up your VPS for Laravel deployment with Docker.

## 0. Create User (optional—you can use your non-root user)

```bash
# Create user with proper primary group
sudo adduser deployer --ingroup www-data
sudo usermod -aG sudo deployer
```

## 1. Initial Server Setup

```bash
# Install Docker and Docker Compose
curl -fsSL https://get.docker.com | sudo sh

# Add user to Docker group
sudo usermod -aG docker deployer
```

## 2. Set Up SSH Key for GitHub Actions (as deployer user)

```bash
# Create SSH directory
mkdir -p ~/.ssh
chmod 700 ~/.ssh

# Generate SSH key
ssh-keygen -t rsa -b 4096 -C "github-actions-deploy"

# Add public key to authorized_keys
cat ~/.ssh/id_rsa.pub >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys

# Display the private key
cat ~/.ssh/id_rsa
```

## 3. Add GitHub Secrets

Add the following secrets to your GitHub repository:

- `SSH_HOST`: Your VPS IP address or domain
- `SSH_USER`: deployer
- `SSH_KEY`: The private SSH key generated above
- `SSH_PORT`: 22 (or your custom SSH port)

Add variable for .env production file:
- `ENV_FILE`: The contents of your .env file

## 4. Application Setup

Create the following files in your Laravel project:

### 4.1 Docker Configuration Files

1. Create `docker/php/Dockerfile`:

```dockerfile
FROM node:22-alpine AS node-builder

WORKDIR /var/www

# Copy only package files first to leverage cache
COPY package*.json ./
RUN npm ci

# Copy remaining files needed for build
COPY vite.config.ts tsconfig.json ./
COPY resources resources
COPY public public

RUN npm run build

FROM php:8.4-fpm-alpine AS php-stage

# Install system dependencies
RUN apk add --no-cache \
    git bash curl libpng-dev libxml2-dev zip unzip \
    freetype-dev libjpeg-turbo-dev libwebp-dev zlib-dev libzip-dev oniguruma-dev \
    postgresql-dev supervisor autoconf g++ make

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
 && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Create user
RUN addgroup -g 1000 appuser && \
    adduser -D -u 1000 -G appuser appuser

# Configure PHP-FPM
RUN sed -i 's/user = www-data/user = appuser/g' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/group = www-data/group = appuser/g' /usr/local/etc/php-fpm.d/www.conf

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy composer files first to leverage cache
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts

# Copy application files
COPY --chown=appuser:appuser . .

# Copy built assets from node-builder
COPY --chown=appuser:appuser --from=node-builder /var/www/public/build /var/www/public/build

# Copy configuration files
COPY --chown=appuser:appuser docker/supervisord.conf /etc/supervisord.conf
COPY --chown=appuser:appuser docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY --chown=appuser:appuser docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

# Create required directories
RUN mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/app/public \
    public/storage \
    bootstrap/cache \
    storage/logs \
    /var/log/supervisor \
    /var/run/php \
    /var/log/php-fpm

# Set permissions
RUN chown -R appuser:appuser \
    storage \
    bootstrap/cache \
    storage/logs \
    /var/log/supervisor \
    /var/run/php \
    /var/log/php-fpm \
    public \
    public/build \
 && chmod -R 775 storage bootstrap/cache public public/build

USER appuser

EXPOSE 9000

CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisord.conf"]
```

2. Create `docker/nginx/Dockerfile`:

```dockerfile
FROM node:22-alpine AS node-builder

WORKDIR /var/www

# Copy only package files first to leverage cache
COPY package*.json ./
RUN npm ci

# Copy remaining files needed for build
COPY vite.config.ts tsconfig.json ./
COPY resources resources
COPY public public

RUN npm run build

FROM nginx:stable-alpine

# Copy built assets from node-builder
COPY --from=node-builder /var/www/public /var/www/public

# Copy nginx configuration
COPY docker/nginx/conf.d /etc/nginx/conf.d

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
```

3. Create `docker-compose.production.yml`:

```yaml
services:
  app:
    image: ${REGISTRY}/${PHP_IMAGE_NAME}:${TAG:-latest}
    container_name: laravel_app
    restart: unless-stopped
    working_dir: /var/www
    env_file: .env
    volumes:
      - laravel_storage:/var/www/storage
      - laravel_public:/var/www/public
    networks:
        - laravel_network
    depends_on:
      - redis

  nginx:
    image: ${REGISTRY}/${NGINX_IMAGE_NAME}:${TAG:-latest}
    container_name: laravel_nginx
    restart: unless-stopped
    ports:
      - "80:80"
    volumes:
      - laravel_storage:/var/www/storage:ro
      - laravel_public:/var/www/public:ro
    networks:
      - laravel_network
    depends_on:
      - app

  redis:
    image: redis:alpine
    container_name: laravel_redis
    restart: unless-stopped
    networks:
      - laravel_network
    volumes:
      - redis_data:/data

networks:
  laravel_network:
    driver: bridge

volumes:
  laravel_storage:
  laravel_public:
  redis_data:
```

### 4.2 GitHub Actions Workflow

Create `.github/workflows/workflow.yml`:
```yaml
name: Build and Deploy Docker Images

on:
  push:
    branches:
      - main

env:
  REGISTRY: ghcr.io
  PHP_IMAGE_NAME: dommmin/laravel-production-php
  NGINX_IMAGE_NAME: dommmin/laravel-production-nginx

jobs:
  build:
    name: 🏗️ Build and Push Docker Images
    runs-on: ubuntu-latest
    permissions:
      contents: read
      packages: write

    steps:
      - name: Checkout repository
        uses: actions/checkout@v4

      - name: Set up Docker Buildx
        uses: docker/setup-buildx-action@v3

      - name: Log in to the Container registry
        uses: docker/login-action@v3
        with:
          registry: ${{ env.REGISTRY }}
          username: ${{ github.actor }}
          password: ${{ secrets.GITHUB_TOKEN }}

      - name: Extract metadata for PHP image
        id: meta-php
        uses: docker/metadata-action@v5
        with:
          images: ${{ env.REGISTRY }}/${{ env.PHP_IMAGE_NAME }}
          tags: |
            type=raw,value=latest
            type=raw,value=main

      - name: Extract metadata for Nginx image
        id: meta-nginx
        uses: docker/metadata-action@v5
        with:
          images: ${{ env.REGISTRY }}/${{ env.NGINX_IMAGE_NAME }}
          tags: |
            type=raw,value=latest
            type=raw,value=main

      - name: Build and push PHP image
        uses: docker/build-push-action@v5
        with:
          context: .
          file: docker/php/Dockerfile
          push: true
          tags: ${{ steps.meta-php.outputs.tags }}
          labels: ${{ steps.meta-php.outputs.labels }}
          platforms: linux/amd64
          cache-from: type=gha
          cache-to: type=gha,mode=max

      - name: Build and push Nginx image
        uses: docker/build-push-action@v5
        with:
          context: .
          file: docker/nginx/Dockerfile
          push: true
          tags: ${{ steps.meta-nginx.outputs.tags }}
          labels: ${{ steps.meta-nginx.outputs.labels }}
          platforms: linux/amd64
          cache-from: type=gha
          cache-to: type=gha,mode=max

  deploy:
    name: 🚀 Deploy to Server
    needs: build
    runs-on: ubuntu-latest
    steps:
      - name: Checkout repository
        uses: actions/checkout@v4

      - name: Setup SSH Key
        uses: webfactory/ssh-agent@v0.9.1
        with:
          ssh-private-key: ${{ secrets.SSH_KEY }}

      - name: Setup known_hosts
        run: |
          mkdir -p ~/.ssh
          ssh-keyscan -p ${{ secrets.SSH_PORT }} ${{ secrets.SSH_HOST }} >> ~/.ssh/known_hosts

      - name: Create .env file
        run: |
          echo "${{ vars.ENV_FILE }}" > .env
          {
            echo "REGISTRY=${{ env.REGISTRY }}"
            echo "PHP_IMAGE_NAME=${{ env.PHP_IMAGE_NAME }}"
            echo "NGINX_IMAGE_NAME=${{ env.NGINX_IMAGE_NAME }}"
            echo "TAG=latest"
            echo "TAG_BRANCH=main"
            echo "GITHUB_USER=${{ github.actor }}"
          } >> .env

      - name: Prepare remote directories
        run: |
          ssh -p ${{ secrets.SSH_PORT }} ${{ secrets.SSH_USER }}@${{ secrets.SSH_HOST }} << 'EOF'
            mkdir -p ~/laravel/docker/nginx/conf.d
          EOF

      - name: Upload configuration files
        run: |
          scp -P ${{ secrets.SSH_PORT }} docker-compose.production.yml ${{ secrets.SSH_USER }}@${{ secrets.SSH_HOST }}:~/laravel/docker-compose.yml
          scp -P ${{ secrets.SSH_PORT }} .env deploy.sh ${{ secrets.SSH_USER }}@${{ secrets.SSH_HOST }}:~/laravel/
          scp -P ${{ secrets.SSH_PORT }} docker/nginx/conf.d/default.conf ${{ secrets.SSH_USER }}@${{ secrets.SSH_HOST }}:~/laravel/docker/nginx/conf.d/

      - name: Deploy on server
        run: |
          ssh -p ${{ secrets.SSH_PORT }} ${{ secrets.SSH_USER }}@${{ secrets.SSH_HOST }} << 'EOF'
            cd ~/laravel
            chmod +x deploy.sh
            ./deploy.sh
          EOF
```

### 4.3 Deployment Script

Create `deploy.sh`:
```bash
#!/bin/bash

# Exit on error
set -e

echo "🚀 Starting deployment..."

# Pull latest Docker images
echo "📥 Pulling latest Docker images..."
docker compose pull

# Restart containers
echo "🔄 Restarting containers..."
docker compose down
docker compose up -d

# Run Laravel commands
echo "⚡ Running Laravel commands..."
docker compose exec -T app php artisan optimize:clear
docker compose exec -T app php artisan optimize
docker compose exec -T app php artisan storage:link
docker compose exec -T app php artisan migrate --force

# Clean up old releases
echo "🧹 Cleaning up old releases..."
docker system prune -f

echo "✅ Deployment completed successfully!"
```

### 4.4 Directory Structure

Your Laravel project should have the following structure:
```
.
├── .github
│   └── workflows
│       └── workflow.yml
├── docker
│   ├── nginx
│   │   ├── Dockerfile
│   │   └── conf.d
│   │       └── default.conf
│   └── php
│       ├── Dockerfile
│       ├── php.ini
│       ├── supervisord.conf
│       └── www.conf
├── docker-compose.production.yml
├── deploy.sh
└── ... (other Laravel files)
```

# Troubleshooting

- **Permission Issues**: 
  ```bash
  # Check Docker access
  docker ps
  ```

- **Docker Issues**:
  ```bash
  # Check Docker status
  sudo systemctl status docker
    ```
- **Deployment Failures**: Check the GitHub Actions logs for detailed error messages.


## Conclusion

After completing these steps, your server will be ready for Docker-based Laravel deployment. The setup ensures:
- Proper permissions for Docker and Laravel
- Secure SSH access for GitHub Actions
- Persistent storage for Laravel data
- Security best practices implementation 
