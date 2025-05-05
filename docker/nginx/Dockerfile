FROM nginx:stable-alpine

COPY docker/nginx/conf.d /etc/nginx/conf.d
COPY --from=node:22-alpine /dev/null /dummy # hack to prevent multistage confusion
COPY . /var/www

EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
