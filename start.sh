#!/usr/bin/env bash
set -e

echo "Clearing Laravel config, routes, and views..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "Running migrations..."
php artisan migrate --force

if [ "$APP_SEED_DATABASE" = "true" ]; then
    echo "Seeding database..."
    php artisan db:seed --force
else
    echo "Skipping database seeding."
fi

echo "Linking storage..."
php artisan storage:link || true

echo "Caching Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Starting Apache..."
apache2-foreground
