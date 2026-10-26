#!/bin/bash
set -e

# Installe les dépendances Composer si le volume monté n'a pas encore de vendor/
if [ -f composer.json ] && [ ! -f vendor/autoload.php ]; then
  echo "Running composer install..."
  composer install --no-interaction --prefer-dist
fi

if [[ -n "$1" ]]; then
  exec "$@"
else
  exec apache2-foreground
fi
