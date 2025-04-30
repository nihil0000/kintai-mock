FROM php:8.3-fpm

COPY php.ini /usr/local/etc/php/

RUN apt update && apt install -y \
    default-mysql-client \
    zlib1g-dev \
    libzip-dev \
    unzip \
    curl \
    npm \
    nodejs \
  && docker-php-ext-install pdo_mysql zip

RUN curl -fsSL https://getcomposer.org/installer | php \
  && mv composer.phar /usr/local/bin/composer \
  && composer self-update \
  && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
  && apt-get install -y nodejs \
  && apt-get install -y make build-essential

WORKDIR /var/www
