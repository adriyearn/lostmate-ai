web: php artisan config:cache && php artisan route:cache && php artisan view:cache && (php artisan storage:link --force || true) && vendor/bin/heroku-php-apache2 public/
release: php artisan migrate --force
worker: php artisan schedule:work & php artisan queue:work --tries=3 --max-time=3600
