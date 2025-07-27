FROM oven/bun:1.1.27-alpine

WORKDIR /var/www/nuxt

RUN apk add --no-cache nodejs npm

COPY . .

EXPOSE 3000

USER bun

CMD ["bun", "run", "dev"]