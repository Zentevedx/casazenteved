#!/bin/bash
set -e

# Asegurar permisos finales por si cambian durante el montaje o deploy
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Instalar variables de entorno si no existen
if [ ! -f ".env" ]; then
    cp .env.example .env
    php artisan key:generate
fi

# Optimizar cachés
php artisan optimize:clear

# Las migraciones se omiten porque la BD ya viene completa de un dump
# php artisan migrate --force

# Cede el control al comando por defecto (Ej: apache2-foreground)
exec "$@"
