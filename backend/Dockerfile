FROM php:8.2-cli-alpine

# Install system dependencies & PHP extensions required by Laravel 11
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    linux-headers \
    libzip-dev \
    oniguruma-dev \
    && docker-php-ext-install pdo pdo_mysql bcmath mbstring zip opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy codebase
COPY . .

# Install production PHP dependencies
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

# Set permissions for Laravel storage & cache
RUN chmod -R 777 storage bootstrap/cache

EXPOSE 8000

# Run Laravel on the dynamic port assigned by the cloud platform
CMD sh -c "php artisan config:clear && php artisan serve --host=0.0.0.0 --port=\${PORT:-8000}"
