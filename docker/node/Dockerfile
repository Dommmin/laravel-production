FROM node:22-alpine

WORKDIR /var/www

COPY package*.json ./
RUN npm ci

COPY vite.config.ts tsconfig.json ./
COPY .env .env
RUN cat .env

COPY . .

RUN npm run build
