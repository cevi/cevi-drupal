#!/bin/sh
# Usage (local):  cd drupal && sh cevi-install.sh
# Usage (Docker): docker compose exec drupal sh /cevi-install.sh
set -e

# In Docker the image is already built with composer install; skip if vendor exists.
if [ ! -f vendor/bin/drush ]; then
  composer install
fi

DRUSH="vendor/bin/drush"

echo "[cevi-install] Installing Drupal (standard profile, German locale)..."
$DRUSH si standard --locale=de --site-name="Cevi Drupal" \
  --site-mail="drupal@cevi.ch" --account-name=admin -y

echo "[cevi-install] Setting site/entity UUIDs..."
$DRUSH config-set system.site uuid eb76eb7c-70c3-4296-960b-673b5f7702af -y
$DRUSH config-set language.entity.de uuid 7e2040a3-be26-44ad-ba1e-c5902dded730 -y
$DRUSH config-set shortcut.set.default uuid 6e0504ef-46e5-42bf-a1a2-cd097c1689ce -y

echo "[cevi-install] Removing default fields that conflict with cevi config..."
$DRUSH cdel field.field.node.article.body
$DRUSH cdel field.field.node.page.body
$DRUSH ev '\Drupal::service("entity_type.manager")->getStorage("shortcut_set")->load("default")->delete();'

echo "[cevi-install] Importing cevi configuration..."
$DRUSH cim -y

echo ""
echo "Done! Use the link below to log in and set your password:"
$DRUSH uli --uri=http://localhost
