# Script para actualizar contraseña de PostgreSQL en .env

$password = "waltito10"
$envFile = "backend\.env"

if (Test-Path $envFile) {
    $content = Get-Content $envFile -Raw
    
    # Reemplazar la línea POSTGRES_PASSWORD
    $content = $content -replace "POSTGRES_PASSWORD=.*", "POSTGRES_PASSWORD=$password"
    
    Set-Content -Path $envFile -Value $content -NoNewline
    Write-Host "✅ Contraseña de PostgreSQL actualizada en backend/.env" -ForegroundColor Green
} else {
    Write-Host "❌ No se encontró backend/.env" -ForegroundColor Red
}

