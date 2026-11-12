$ErrorActionPreference = 'Stop'
Set-Location (Split-Path $PSScriptRoot -Parent)
function Invoke-Docker {
    & docker @args
    if ($LASTEXITCODE -ne 0) { throw "Docker failed: $args" }
}
Invoke-Docker compose build frontend
Invoke-Docker compose up -d
Invoke-Docker compose exec -T backend php artisan config:clear
Invoke-Docker compose exec -T backend php artisan migrate --force
& docker compose exec -T backend test -L public/storage
if ($LASTEXITCODE -ne 0) {
    Invoke-Docker compose exec -T backend php artisan storage:link
}
Invoke-Docker compose exec -T backend php artisan config:cache
Invoke-Docker compose exec -T backend php artisan route:cache
Invoke-Docker compose exec -T webserver nginx -s reload
Write-Host 'IN-HC siap: http://localhost:5173 atau http://IP-KOMPUTER-SERVER:5173'