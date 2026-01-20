#!/usr/bin/env bash
set -euo pipefail

APP_DIR="${APP_DIR:-$(pwd)}"
BRANCH="${BRANCH:-main}"

cd "$APP_DIR"

php artisan down || true

git fetch --all --prune
git reset --hard "origin/${BRANCH}"

composer install --no-interaction --no-dev --prefer-dist --optimize-autoloader

php artisan migrate --force
php artisan storage:link || true

npm ci
npm run build

php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan queue:restart || true

php artisan up || true

