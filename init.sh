#!/bin/sh

# Generate application key if needed
if [ ! -f .env ]; then
    php artisan key:generate
fi

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache