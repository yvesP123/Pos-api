#!/usr/bin/env bash
# Build script for Render

set -e  # Exit on any error

echo "Starting Laravel build process..."

# Install PHP dependencies
echo "Installing Composer dependencies..."
composer install --optimize-autoloader --no-dev --no-interaction --ignore-platform-reqs

# Ensure storage directories exist
echo "Creating storage directories..."
mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache

# Set proper permissions
echo "Setting permissions..."
chmod -R 775 storage bootstrap/cache

# Copy .env if it doesn't exist
if [ ! -f .env ]; then
    echo "Copying .env.example to .env..."
    cp .env.example .env
fi

# Generate application key if not set
echo "Generating application key..."
php artisan key:generate --force

# Clear and cache config
echo "Optimizing Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Only run migrations if DATABASE_URL is set
if [ ! -z "$DATABASE_URL" ]; then
    echo "Running database migrations..."
    php artisan migrate --force
else
    echo "Skipping migrations - no DATABASE_URL set"
fi

echo "Build completed successfully!"