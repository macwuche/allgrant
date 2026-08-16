#!/bin/bash
set -e

PHP=/nix/store/x1xg8d0gnhg1f0n3jwl9lc5dnjf0y6ly-php-with-extensions-8.3.15/bin/php
COMPOSER=/tmp/composer

# Download composer if missing
if [ ! -f "$COMPOSER" ]; then
  curl -sS https://getcomposer.org/installer | $PHP -- --install-dir=/tmp --filename=composer
fi

# Install dependencies
if [ ! -d "vendor" ]; then
  echo "Installing composer dependencies..."
  $PHP $COMPOSER install --no-interaction --optimize-autoloader
fi

# Generate app key if missing
if grep -q "^APP_KEY=$" .env 2>/dev/null; then
  $PHP artisan key:generate
fi

# Skip migrations — already run directly against Supabase
# Run config/cache clear
$PHP artisan config:clear
$PHP artisan cache:clear

echo "Starting Laravel server..."
PHP_CLI_SERVER_WORKERS=8 $PHP -S 0.0.0.0:8000 index.php
