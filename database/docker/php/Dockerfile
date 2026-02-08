FROM php:8.3-fpm

# System deps + PDO MySQL (MariaDB)
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev \
  && docker-php-ext-install pdo pdo_mysql \
  && rm -rf /var/lib/apt/lists/*

# Optional performance
RUN docker-php-ext-install opcache

WORKDIR /app
