FROM php:8.3-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
      libpng-dev libjpeg62-turbo-dev libfreetype6-dev libzip-dev libicu-dev \
      libwebp-dev less default-mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" mysqli gd zip intl exif opcache \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

RUN curl -sSL -o /usr/local/bin/wp \
      https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && chmod +x /usr/local/bin/wp

RUN { \
      echo 'upload_max_filesize = 128M'; \
      echo 'post_max_size = 128M'; \
      echo 'memory_limit = 512M'; \
      echo 'max_execution_time = 300'; \
      echo 'display_errors = Off'; \
    } > /usr/local/etc/php/conf.d/zz-local.ini
