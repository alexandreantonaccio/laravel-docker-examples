#!/bin/sh
set -e

# Clear configurations to avoid caching issues in development. The source
# directory is bind-mounted in Compose, so dependencies may not have been
# installed on the host yet.
if [ -f /var/www/vendor/autoload.php ]; then
  echo "Clearing configurations..."
  php artisan config:clear
  php artisan route:clear
  php artisan view:clear
else
  echo "Laravel dependencies are not installed. Run 'docker compose -f compose.dev.yaml run --rm workspace composer install' before using the application."
fi

# Run the default command (e.g., php-fpm or bash)
exec "$@"
