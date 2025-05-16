FROM node:22-alpine

ARG ENV_FILE

WORKDIR /var/www

COPY package*.json ./
RUN npm ci

COPY vite.config.ts tsconfig.json ./
COPY ${ENV_FILE} .env
RUN cat .env

COPY . .

RUN npm run build
