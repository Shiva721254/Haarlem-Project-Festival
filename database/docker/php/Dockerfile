FROM php:8.3-fpm

# System deps + PDO MySQL (MariaDB)
RUN apt-get update && apt-get install -y \
  git unzip libzip-dev libpng-dev libjpeg-dev libfreetype6-dev msmtp msmtp-mta ca-certificates \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install pdo pdo_mysql gd \
  && rm -rf /var/lib/apt/lists/*

# Optional performance
RUN docker-php-ext-install opcache

# Configure sessions directory
RUN mkdir -p /var/lib/php/sessions && chown -R www-data:www-data /var/lib/php/sessions

# Create a PHP config file to set session properties
RUN printf "session.save_handler = files\nsession.save_path = /var/lib/php/sessions\n" > /usr/local/etc/php/conf.d/sessions.ini

# Route PHP mail() through MailHog for local development/testing.
RUN printf "defaults\nauth off\ntls off\nlogfile /var/log/msmtp.log\n\naccount default\nhost mailhog\nport 1025\nfrom noreply@haarlemfestival.nl\n" > /etc/msmtprc \
  && chmod 600 /etc/msmtprc \
  && touch /var/log/msmtp.log \
  && chmod 666 /var/log/msmtp.log \
  && printf "sendmail_path = /usr/bin/msmtp -t\n" > /usr/local/etc/php/conf.d/mailhog-mail.ini

