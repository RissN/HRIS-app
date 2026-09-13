#!/bin/sh
set -e

# Change directory
cd /var/www/html

# Create .env if not exists
if [ ! -f .env ]; then
    if [ -f .env.docker ]; then
        echo "Creating .env from .env.docker..."
        cp .env.docker .env
    elif [ -f .env.docker.example ]; then
        echo "Creating .env from .env.docker.example..."
        cp .env.docker.example .env
    elif [ -f .env.example ]; then
        echo "Creating .env from .env.example..."
        cp .env.example .env
    fi
fi

# Ensure storage and bootstrap/cache directories exist and have proper permissions
mkdir -p storage/app/public \
         storage/app/public/selfies \
         storage/app/public/avatars \
         storage/app/public/attachments \
         storage/framework/cache/data \
         storage/framework/sessions \
         storage/framework/views \
         storage/logs \
         bootstrap/cache

chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

# Wait for Database to be ready if DB_HOST is configured
if [ -n "$DB_HOST" ] && [ "$DB_CONNECTION" = "mysql" ]; then
    echo "Waiting for MySQL database at $DB_HOST:${DB_PORT:-3306}..."
    max_tries=30
    counter=0
    until php -r "
        try {
            new PDO('mysql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: '3306'), getenv('DB_USERNAME') ?: 'root', getenv('DB_PASSWORD') ?: '');
            exit(0);
        } catch (Exception \$e) {
            exit(1);
        }
    " 2>/dev/null; do
        counter=$((counter + 1))
        if [ $counter -gt $max_tries ]; then
            echo "Warning: Database connection timed out after $max_tries seconds. Continuing anyway..."
            break
        fi
        sleep 1
    done
    echo "MySQL database is ready!"
fi

# Generate APP_KEY if empty
if [ -f .env ]; then
    APP_KEY=$(grep -E '^APP_KEY=' .env | cut -d '=' -f 2-)
    if [ -z "$APP_KEY" ]; then
        echo "Generating application encryption key..."
        php artisan key:generate --force
    fi
fi

# Create storage link if not already present
if [ ! -L public/storage ]; then
    echo "Creating storage symlink..."
    php artisan storage:link --force 2>/dev/null || true
fi

# Run composer install if vendor doesn't exist
if [ ! -d vendor ]; then
    echo "Vendor folder not found. Running composer install..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Run migrations if RUN_MIGRATIONS=true
if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "Running database migrations..."
    php artisan migrate --force
fi

echo "Starting application with command: $@"
exec "$@"
