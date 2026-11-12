#!/bin/sh
set -e

if [ -z "$APP_KEY" ]; then
    echo "ERREUR : la variable APP_KEY est obligatoire." >&2
    echo "Génère-en une avec : docker run --rm <image> php artisan key:generate --show" >&2
    exit 1
fi

# Les hébergeurs (Render, Railway...) peuvent générer une clé sans préfixe "base64:"
case "$APP_KEY" in
    base64:*) ;;
    *) export APP_KEY="base64:$APP_KEY" ;;
esac

# Adresse publique fournie par Render si APP_URL n'est pas définie
if [ -z "$APP_URL" ] && [ -n "$RENDER_EXTERNAL_URL" ]; then
    export APP_URL="$RENDER_EXTERNAL_URL"
fi

# Port imposé par l'hébergeur (variable PORT), sinon 80
if [ -n "$PORT" ] && [ "$PORT" != "80" ]; then
    sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
    sed -i "s/:80>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf
fi

# Base SQLite (persistante si /data est un volume)
DB_FILE="${DB_DATABASE:-/data/database.sqlite}"
mkdir -p "$(dirname "$DB_FILE")"
FRESH_DB=false
if [ ! -s "$DB_FILE" ]; then
    FRESH_DB=true
    touch "$DB_FILE"
fi
chown -R www-data:www-data "$(dirname "$DB_FILE")" storage bootstrap/cache

# Tables (sans danger si elles existent déjà)
php artisan migrate --force

# Données de démonstration : seulement si demandé ET si la base vient d'être créée
if [ "$SEED_DEMO" = "true" ] && [ "$FRESH_DB" = "true" ]; then
    php artisan db:seed --force
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

chown -R www-data:www-data storage bootstrap/cache

exec "$@"
