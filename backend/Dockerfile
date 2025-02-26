
FROM php:8.2-fpm


WORKDIR /var/www/html


RUN apt-get update && apt-get install -y \
    libpq-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_pgsql


COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer


COPY . .


RUN chmod -R 775 storage bootstrap/cache


EXPOSE 8000


CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
