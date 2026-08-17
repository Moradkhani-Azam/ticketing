FROM php:8.3-fpm


WORKDIR /var/www/html


RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libsqlite3-dev \
    supervisor


RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql pdo_sqlite mbstring exif pcntl bcmath zip gd calendar \
    && apt-get clean && rm -rf /var/lib/apt/lists/*


COPY --from=composer:2 /usr/bin/composer /usr/bin/composer


COPY composer.json composer.lock ./


RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts

COPY docker/supervisor/supervisord.conf \
     /etc/supervisor/supervisord.conf

COPY . .


RUN composer dump-autoload --optimize


EXPOSE 9000

CMD ["php-fpm"]