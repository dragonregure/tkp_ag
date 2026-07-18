FROM php:8.4-cli

WORKDIR /var/www/html

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev \
        libpng-dev \
        libonig-dev \
        default-mysql-client \
        libsqlite3-dev \
    && docker-php-ext-install \
        bcmath \
        gd \
        mbstring \
        pdo_mysql \
        pdo_sqlite \
        zip \
    && pecl install redis xdebug \
    && docker-php-ext-enable redis xdebug \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/backend/entrypoint.sh /usr/local/bin/tkp-ag-entrypoint
RUN chmod +x /usr/local/bin/tkp-ag-entrypoint

EXPOSE 8000

ENTRYPOINT ["tkp-ag-entrypoint"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
