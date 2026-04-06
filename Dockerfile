# Stage 1: Build base dependencies
FROM php:8.3-fpm-alpine AS base

# Install system dependencies required for PHP extensions
RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    zip \
    unzip \
    linux-headers \
    $PHPIZE_DEPS

# Install PHP extensions required for Laravel and PostgreSQL
RUN docker-php-ext-install pdo_pgsql pgsql zip bcmath pcntl

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Stage 2: Development environment
FROM base AS dev

# Install Xdebug and PCOV for testing and coverage
RUN pecl install xdebug pcov && docker-php-ext-enable xdebug pcov

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Expose port (handled by Nginx but exposing php-fpm port for documentation)
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]

# Stage 3: Builder for Production (Optimizes assets and dependencies)
FROM base AS builder

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /app
COPY . .

# Install production dependencies only, optimize autoloader
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Stage 4: Final Production image
FROM base AS prod

# Copy custom PHP configuration for production
COPY docker/php/php-prod.ini /usr/local/etc/php/conf.d/custom.ini

WORKDIR /var/www

# Copy files from builder stage
COPY --from=builder /app /var/www

# Fix permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Switch to non-root user
USER www-data

EXPOSE 9000

CMD ["php-fpm"]
