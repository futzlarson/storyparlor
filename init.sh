#!/bin/sh

# If .env missing and no APP_KEY, generate application key (local Docker)
if [ ! -f .env ] && [ -z "$APP_KEY" ]; then
    echo "Generating .env for local Docker"
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
