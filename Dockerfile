FROM php:7.3-fpm

# Install packages
RUN apt update \
    && curl -fsSL https://deb.nodesource.com/setup_16.x | bash - \
    && apt install -y nodejs zlib1g-dev g++ git libicu-dev zip libzip-dev zip libcurl4-openssl-dev \
    && docker-php-ext-install intl exif curl opcache pdo pdo_mysql \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/ecs