FROM php:8.2-cli-alpine

# Install system dependencies & PHP extensions (including SQLite & MySQL)
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    sqlite \
    sqlite-dev \
    linux-headers \
    libzip-dev \
    oniguruma-dev \
    && docker-php-ext-install pdo pdo_sqlite pdo_mysql bcmath mbstring zip opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy backend files
COPY . .

# Install production PHP dependencies
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

# Setup environment & SQLite database
RUN if [ -f .env.example ]; then cp .env.example .env; else touch .env; fi && \
    echo "APP_ENV=production" >> .env && \
    echo "APP_DEBUG=false" >> .env && \
    echo "DB_CONNECTION=sqlite" >> .env && \
    echo "DB_DATABASE=/app/database/database.sqlite" >> .env && \
    echo "ML_SERVICE_URL=https://hamna911-careerpath-ml.hf.space" >> .env && \
    mkdir -p database && \
    touch database/database.sqlite && \
    php artisan key:generate --force

# Set directory permissions for Laravel
RUN chmod -R 777 storage bootstrap/cache database

EXPOSE 8000

# Run migrations & serve dynamically on platform PORT
CMD sh -c "php artisan config:clear && php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=\${PORT:-8000}"
