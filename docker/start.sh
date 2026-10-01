#!/bin/sh
set -e

# Railway injects $PORT - Apache's default config listens on 80, so rewrite it.
PORT="${PORT:-8080}"
sed -i "s/80/${PORT}/g" /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

# The storage/app/public -> public/storage symlink lives in the writable
# volume's target but the symlink itself is inside the image, so it has to
# be recreated on every boot of a fresh container.
php artisan storage:link || true

php artisan config:cache
php artisan route:cache
php artisan view:cache

# --force skips the "are you sure, this is production" prompt; safe to
# run on every boot since already-applied migrations are skipped.
php artisan migrate --force

exec apache2-foreground
