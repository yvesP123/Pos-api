# Use official PHP image with Apache
FROM php:8.1-apache

# Install system dependencies
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
    nodejs \
    npm \
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
COPY --from=composer:2.5 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files first (for better Docker layer caching)
COPY composer.json composer.lock ./

# Install PHP dependencies (should work now!)
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

# Copy package.json for Node dependencies
COPY package.json package-lock.json* ./

# Install Node dependencies and build assets
RUN if [ -f "package.json" ]; then \
        npm ci --only=production || npm install --only=production; \
        npm run production; \
    fi

# Copy rest of the application code
COPY . .

# Set permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 775 storage bootstrap/cache

# Create storage directories if they don't exist
RUN mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views

# Generate application key if .env doesn't exist
RUN if [ ! -f .env ]; then cp .env.example .env; fi
RUN php artisan key:generate

# Optimize Laravel for production
RUN php artisan config:cache || true
RUN php artisan route:cache || true
RUN php artisan view:cache || true

# Create storage symlink
RUN php artisan storage:link || true

# Apache vhost config
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        Options -Indexes +FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Expose port 80
EXPOSE 80

# Start Apache in the foreground
CMD ["apache2-foreground"]