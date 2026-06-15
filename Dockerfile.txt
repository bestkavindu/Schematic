FROM php:8.4-fpm

ENV PGSSLCERT="/tmp/postgresql.crt"

RUN apt-get update && apt-get install -y \
    curl wget unzip libzip-dev libpq-dev libicu-dev \
    supervisor git ca-certificates apt-transport-https \
    nginx libnginx-mod-http-brotli-filter \
    libnginx-mod-http-brotli-static dos2unix \
    && rm -rf /var/lib/apt/lists/*

ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN install-php-extensions \
    pdo_pgsql pgsql bcmath intl zip opcache \
    gd exif pcntl sockets msgpack igbinary

RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
    apt-get install -y nodejs

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer


WORKDIR /var/www/html

COPY composer.json composer.lock ./

# ARG FLUX_EMAIL
# ARG FLUX_KEY
# RUN if [ -n "$FLUX_EMAIL" ]; then \
#        composer config http-basic.composer.fluxui.dev "$FLUX_EMAIL" "$FLUX_KEY"; \
#     fi
RUN composer install --no-dev --no-scripts --no-autoloader --no-progress

COPY package.json package-lock.json ./

RUN npm install

COPY . .

RUN composer dump-autoload --optimize --no-dev --classmap-authoritative
RUN npm run build

COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY nginx.conf /etc/nginx/sites-enabled/default

RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.memory_consumption=256'; \
        echo 'opcache.max_accelerated_files=10000'; \
        echo 'opcache.validate_timestamps=0'; \
    } > /usr/local/etc/php/conf.d/opcache-recommended.ini

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/public

EXPOSE 80
EXPOSE 8080

COPY docker-entrypoint.sh /usr/local/bin/
RUN dos2unix /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["/usr/bin/supervisord"]