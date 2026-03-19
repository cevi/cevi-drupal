#!/bin/sh
set -e

# Guard: fail fast if required env vars are missing.
for var in DRUPAL_DB_NAME DRUPAL_DB_USER DRUPAL_DB_PASS DRUPAL_HASH_SALT; do
  eval val=\$$var
  if [ -z "$val" ]; then
    echo "[entrypoint] ERROR: required environment variable $var is not set."
    echo "[entrypoint] Copy .env.example to .env and fill in all values."
    exit 1
  fi
done

echo "[entrypoint] Waiting for database..."
until php -r "\$h=getenv('DRUPAL_DB_HOST')?:'mariadb'; \$d=getenv('DRUPAL_DB_NAME'); \$u=getenv('DRUPAL_DB_USER'); \$p=getenv('DRUPAL_DB_PASS'); new PDO(\"mysql:host=\$h;dbname=\$d\",\$u,\$p);" 2>/dev/null; do
  sleep 2
done
echo "[entrypoint] Database is ready."

cd /var/www/html

# Run database updates and config import if Drupal is already installed
if php vendor/bin/drush status --field=bootstrap 2>/dev/null | grep -q "Successful"; then
  echo "[entrypoint] Running database updates..."
  php vendor/bin/drush updb -y --no-cache-clear || true
  echo "[entrypoint] Rebuilding cache..."
  php vendor/bin/drush cr || true
  # Config import is intentionally skipped here;
  # run manually: docker compose exec drupal php vendor/bin/drush cim -y
  echo "[entrypoint] Site is ready."
else
  echo "[entrypoint] Drupal not yet installed — skipping drush deploy."
  echo "[entrypoint] To install: docker compose exec drupal php vendor/bin/drush si --locale=de -y"
fi

exec "$@"
