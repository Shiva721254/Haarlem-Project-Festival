FROM php:8.3-fpm

# System deps + PDO MySQL (MariaDB)
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev \
  && docker-php-ext-install pdo pdo_mysql \
  && rm -rf /var/lib/apt/lists/*

# Optional performance
RUN docker-php-ext-install opcache

# Configure sessions directory
RUN mkdir -p /var/lib/php/sessions && chown -R www-data:www-data /var/lib/php/sessions

# Create a PHP config file to set session properties
RUN printf "session.save_handler = files\nsession.save_path = /var/lib/php/sessions\n" > /usr/local/etc/php/conf.d/sessions.ini

