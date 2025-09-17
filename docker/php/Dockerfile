FROM php:8.2-fpm

# Dependencias del sistema y extensiones PHP habituales para Symfony
RUN apt-get update && apt-get install -y --no-install-recommends git unzip libicu-dev libzip-dev libonig-dev libxml2-dev zlib1g-dev libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure zip \
    && docker-php-ext-install -j$(nproc) intl pdo pdo_mysql zip opcache gd \
    && rm -rf /var/lib/apt/lists/*

# Xdebug
RUN pecl install xdebug \
&& docker-php-ext-enable xdebug

# Composer desde imagen oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/app

# Opcional: usa www-data por defecto
RUN chown -R www-data:www-data /var/www
USER www-data