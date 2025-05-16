FROM node:22-alpine

WORKDIR /var/www

COPY package*.json ./
RUN npm ci

COPY vite.config.ts tsconfig.json ./

ARG ENV_CONTENT
RUN echo "$ENV_CONTENT" | tr -d '\r' | sed 's/\\n/\n/g' > .env
RUN cat .env

COPY . .

RUN npm run build
