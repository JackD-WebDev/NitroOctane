FROM nginx:stable-alpine

WORKDIR /etc/nginx/conf.d

COPY nginx/nginx.conf .

RUN mv nginx.conf default.conf

WORKDIR /var/www/html

COPY api .

COPY client .

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]