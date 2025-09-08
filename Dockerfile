# Stage 1: Builder
# This stage installs all dependencies and builds the application assets.
FROM php:8.2-fpm as builder

# Set working directory
WORKDIR /var/www/html

# Install system dependencies for building
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy only composer files to leverage Docker cache
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --ignore-platform-reqs

# Copy the rest of the application source code
COPY . .

# Run composer scripts and generate autoloader
RUN composer dump-autoload --no-dev --optimize && \
    composer run-script post-autoload-dump

# Set permissions for storage and bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Stage 2: Production
# This is the final, lean image that will run in production.
FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www/html

# Install only necessary production dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libzip-dev \
    && docker-php-ext-install pdo_mysql zip

# Copy the application code with dependencies from the builder stage
COPY --from=builder /var/www/html .

# Copy the staging-ready .env file
COPY .env.staging .env

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
