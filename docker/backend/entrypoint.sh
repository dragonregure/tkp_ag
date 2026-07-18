#!/usr/bin/env sh
set -e

if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
fi

set_env() {
    key="$1"
    value="$2"
    escaped_value="$(printf '%s' "$value" | sed 's/[\/&]/\\&/g')"

    if grep -q "^${key}=" .env; then
        sed -i "s/^${key}=.*/${key}=\"${escaped_value}\"/" .env
    else
        printf '\n%s="%s"\n' "$key" "$value" >> .env
    fi
}

for key in \
    APP_NAME APP_ENV APP_DEBUG APP_URL \
    DB_CONNECTION DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD \
    SESSION_DRIVER CACHE_STORE QUEUE_CONNECTION \
    REDIS_CLIENT REDIS_HOST REDIS_PORT REDIS_PASSWORD \
    MAIL_MAILER MAIL_HOST MAIL_PORT MAIL_FROM_ADDRESS MAIL_FROM_NAME
do
    value="$(printenv "$key" || true)"

    if [ -n "$value" ]; then
        set_env "$key" "$value"
    fi
done

if [ ! -f vendor/autoload.php ]; then
    if mkdir vendor/.installing 2>/dev/null; then
        trap 'rmdir vendor/.installing 2>/dev/null || true' EXIT
        composer install --no-interaction --prefer-dist
        rmdir vendor/.installing 2>/dev/null || true
        trap - EXIT
    else
        until [ -f vendor/autoload.php ]; do
            sleep 2
        done
    fi
fi

if ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
    php artisan key:generate --force
fi

php artisan config:clear
php artisan storage:link || true

if [ "${TKP_AG_RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force
fi

if [ "${TKP_AG_RUN_SEEDERS:-false}" = "true" ]; then
    php artisan db:seed --force
fi

exec "$@"
