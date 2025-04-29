FROM php:8.3-fpm

# Установка зависимостей
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    curl \
    locales \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo_mysql opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Установка Node.js для Vue
RUN curl -sL https://deb.nodesource.com/setup_16.x | bash - \
    && apt-get install -y nodejs

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Копируем конфиги PHP
COPY docker/php/php.ini /usr/local/etc/php/php.ini
COPY docker/php/custom.ini /usr/local/etc/php/conf.d/custom.ini

# Настройка рабочей директории
WORKDIR /var/www/laravel

# Копируем файлы проекта
COPY ./laravel /var/www/laravel

# Настройка прав доступа
RUN chown -R www-data:www-data /var/www/laravel \
    && chmod -R 755 /var/www/laravel

# Переменные окружения
ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS="1" \
    PHP_OPCACHE_MEMORY_CONSUMPTION="128"

EXPOSE 9000