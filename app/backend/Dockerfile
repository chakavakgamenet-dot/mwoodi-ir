FROM composer:2 AS vendor
WORKDIR /build
RUN composer create-project laravel/laravel:^12.0 app --no-interaction --prefer-dist
WORKDIR /build/app
RUN composer require laravel/sanctum:^4.0 predis/predis:^3.0 --no-interaction

FROM php:8.4-cli
WORKDIR /var/www
RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev \
    && docker-php-ext-install pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*
COPY --from=vendor /build/app /var/www
COPY . /tmp/mwoodi-backend
COPY app /var/www/app
COPY bootstrap /var/www/bootstrap
COPY routes /var/www/routes
COPY database /var/www/database
COPY config/cors.php /var/www/config/cors.php
COPY config/database.php /var/www/config/database.php
COPY scripts /var/www/scripts
COPY start.sh /usr/local/bin/start.sh
RUN sed -i 's/\r$//' /usr/local/bin/start.sh && chmod +x /usr/local/bin/start.sh
EXPOSE 8000
CMD ["/usr/local/bin/start.sh"]
