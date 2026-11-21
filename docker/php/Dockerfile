FROM php:8.3-fpm-alpine

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

RUN apk add --no-cache \
        git \
        unzip \
        zlib \
        libzip \
        libpng \
        libjpeg-turbo \
        postgresql-libs \
    && apk add --no-cache --virtual .build-deps \
        zlib-dev \
        libzip-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        postgresql-dev \
        $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        opcache \
        zip \
        gd \
        bcmath \
        pgsql \
        pdo_pgsql \
    && apk del .build-deps

RUN { \
      echo 'memory_limit = -1'; \
      echo 'upload_max_filesize = 512M'; \
      echo 'post_max_size = 512M'; \
      echo 'max_file_uploads = 20'; \
    } > /usr/local/etc/php/conf.d/mealplanner.ini

COPY composer.json composer.lock* ./
RUN composer install --no-interaction --prefer-dist --no-scripts --no-autoloader

COPY . .
RUN composer dump-autoload --optimize

CMD ["sh", "-c", "if [ ! -f /app/vendor/autoload.php ]; then composer install --no-interaction --prefer-dist --optimize-autoloader; fi && exec php-fpm"]
