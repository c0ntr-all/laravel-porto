# Dockerfile
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
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo_mysql

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Настройка рабочей директории
WORKDIR /var/www/laravel

# Копируем все файлы проекта
COPY ./laravel /var/www/laravel

# Настройка прав доступа
RUN chown -R www-data:www-data /var/www/laravel \
    && chmod -R 755 /var/www/laravel

# Установка переменных окружения
ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS="1" \
    PHP_OPCACHE_MEMORY_CONSUMPTION="128"

# Экспонируем порт для доступа
EXPOSE 9000