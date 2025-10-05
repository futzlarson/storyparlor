#!/bin/sh

# Generate application key if needed
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate
fi

# Migrations
php artisan migrate --force

# Caching
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run
exec /start.sh
