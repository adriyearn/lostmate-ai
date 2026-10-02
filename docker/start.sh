#!/bin/sh
set -e

# Railway may restart this same container several times, so every step
# below must be safe to run again (no "find and replace" that could stack up).

# Apache must load exactly ONE "MPM" (request-handling engine). mod_php needs
# prefork, so make sure the other two are switched off.
rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.*
a2enmod mpm_prefork >/dev/null 2>&1 || true

# Railway tells us which port to listen on in $PORT. Write the port settings
# from scratch each time instead of editing them in place.
PORT="${PORT:-8080}"
echo "Listen ${PORT}" > /etc/apache2/ports.conf
sed -i -E "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Uploaded photos live in a Railway volume; the public/storage link that
# points at them is part of the image, so create it only if it's missing.
[ -L public/storage ] || php artisan storage:link

php artisan config:cache
php artisan route:cache
php artisan view:cache

# --force skips the "are you sure, this is production" prompt; safe to
# run on every boot since already-applied migrations are skipped.
php artisan migrate --force

# The steps above ran as root, but Apache runs as www-data and must be able
# to write sessions, caches, and uploaded photos (including the Railway volume).
chown -R www-data:www-data storage bootstrap/cache || echo "WARNING: could not change owner of storage (continuing)"

exec apache2-foreground
