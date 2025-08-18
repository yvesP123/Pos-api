FROM php:8.1-apache

# Set memory limit for Composer
ENV COMPOSER_MEMORY_LIMIT=-1

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    zip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql \
        pdo_pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        zip \
        gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache mods
RUN a2enmod rewrite headers

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files
COPY composer.json composer.lock ./

# Install PHP dependencies with proper flags
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --optimize-autoloader \
    --ignore-platform-reqs \
    --no-scripts \
    --no-cache

# Copy rest of application
COPY . .

# Set up Apache document root
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Create required directories first
RUN mkdir -p storage/logs \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    bootstrap/cache

# Set permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 775 storage bootstrap/cache

# Laravel setup - ensure .env exists
RUN if [ ! -f .env ]; then cp .env.example .env; fi

# Generate app key
RUN php artisan key:generate --force

# Run post-install Composer scripts
RUN composer run-script post-autoload-dump

# Clear and cache config for production
RUN php artisan config:clear
RUN php artisan cache:clear
RUN php artisan view:clear

EXPOSE 80

CMD ["apache2-foreground"]