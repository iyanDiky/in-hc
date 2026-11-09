#!/bin/sh
set -eu
cd "$(dirname "$0")/.."

docker compose build frontend
docker compose up -d
docker compose exec -T backend php artisan config:clear
docker compose exec -T backend php artisan migrate --force
if ! docker compose exec -T backend test -L public/storage; then
    docker compose exec -T backend php artisan storage:link
fi
docker compose exec -T backend php artisan config:cache
docker compose exec -T backend php artisan route:cache
docker compose exec -T webserver nginx -s reload
echo 'IN-HC siap: http://localhost:5173 atau http://IP-KOMPUTER-SERVER:5173'
