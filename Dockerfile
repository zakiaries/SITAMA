# Image PHP-FPM untuk SIMAMA (Laravel 9).
FROM php:8.2-fpm

# Ekstensi & tools yang dibutuhkan: Laravel, Sastrawi (chatbot), Excel, PDF, gambar.
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip \
        libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql mbstring gd zip bcmath intl \
    && rm -rf /var/lib/apt/lists/*

# Composer (disalin dari image resmi).
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Kode di-bind-mount lewat docker-compose (lihat DEPLOY_DOCKER.md), jadi image
# ini fokus menyediakan runtime PHP. Menjalankan php-fpm (default image).
