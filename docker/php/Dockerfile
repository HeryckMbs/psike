FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    postgresql-dev \
    oniguruma-dev \
    libxml2-dev

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Create composer cache directory and set permissions
RUN mkdir -p /var/www/.composer && \
    chmod -R 777 /var/www/.composer

# Set permissions for the working directory (permissive para desenvolvimento)
RUN chmod -R 777 /var/www/html

# Copy custom PHP-FPM configuration
COPY www.conf /usr/local/etc/php-fpm.d/www.conf

# Keep root user for file operations
# USER www-data

# Expose port 9000
EXPOSE 9000

CMD ["php-fpm"]
