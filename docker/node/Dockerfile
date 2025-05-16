FROM node:22-alpine

ARG ENV_FILE

WORKDIR /var/www

COPY package*.json ./
RUN npm ci

COPY vite.config.ts tsconfig.json ./

RUN if [ -n "$ENV_FILE" ]; then \
      echo "$ENV_FILE" | while IFS= read -r line; do \
        echo "$line" >> .env; \
      done; \
    fi
RUN cat .env

COPY . .

RUN npm run build
