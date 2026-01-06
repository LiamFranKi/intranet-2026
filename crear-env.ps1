# Script para crear archivos .env en Windows PowerShell

Write-Host "🔧 Creando archivos .env..." -ForegroundColor Green

# Backend .env (Producción)
$backendEnv = @"
# MySQL Remoto (VPS MySQL - NO Hostinger)
MYSQL_HOST=mysql.vanguardschools.edu.pe
MYSQL_PORT=3306
MYSQL_USER=vanguard
MYSQL_PASSWORD=QI_jkA]RsHF_gUDN
MYSQL_DATABASE=vanguard_intranet

# PostgreSQL (Local o servidor Hostinger)
POSTGRES_HOST=localhost
POSTGRES_PORT=5432
POSTGRES_USER=postgres
POSTGRES_PASSWORD=Vanguard2025@&
POSTGRES_DATABASE=aula_virtual

# PHP API (Para exportar notas)
PHP_API_URL=https://vanguardschools.edu.pe/api
PHP_API_TOKEN=token_secreto

# JWT
JWT_SECRET=Vanguard2025_AulaVirtual_SuperSecreto_JWT_Key_2025
JWT_EXPIRES_IN=24h

# Server
PORT=5000
NODE_ENV=production

# CORS - Subdominio de producción
FRONTEND_URL=https://intranet.vanguardschools.com

# Email (SMTP Gmail)
EMAIL_HOST=smtp.gmail.com
EMAIL_PORT=587
EMAIL_USER=walterlozanoalcalde@gmail.com
EMAIL_PASSWORD=ldvkcmqxshxdkupv
EMAIL_FROM=walterlozanoalcalde@gmail.com

# PWA - Notificaciones Push (se generarán después)
VAPID_PUBLIC_KEY=temporal_public_key
VAPID_PRIVATE_KEY=temporal_private_key
VAPID_EMAIL=walterlozanoalcalde@gmail.com
"@

# Backend .env.development
$backendDevEnv = @"
# MySQL Remoto (VPS MySQL - NO Hostinger)
MYSQL_HOST=mysql.vanguardschools.edu.pe
MYSQL_PORT=3306
MYSQL_USER=vanguard
MYSQL_PASSWORD=QI_jkA]RsHF_gUDN
MYSQL_DATABASE=vanguard_intranet

# PostgreSQL (Local)
POSTGRES_HOST=localhost
POSTGRES_PORT=5432
POSTGRES_USER=postgres
POSTGRES_PASSWORD=tu_password_postgres_local
POSTGRES_DATABASE=aula_virtual

# PHP API (Para exportar notas)
PHP_API_URL=http://localhost/php-api
PHP_API_TOKEN=token_secreto

# JWT
JWT_SECRET=Vanguard2025_AulaVirtual_Dev_Key_2025
JWT_EXPIRES_IN=24h

# Server
PORT=5000
NODE_ENV=development

# CORS - Desarrollo local
FRONTEND_URL=http://localhost:3000

# Email (SMTP Gmail)
EMAIL_HOST=smtp.gmail.com
EMAIL_PORT=587
EMAIL_USER=walterlozanoalcalde@gmail.com
EMAIL_PASSWORD=ldvkcmqxshxdkupv
EMAIL_FROM=walterlozanoalcalde@gmail.com

# PWA - Notificaciones Push
VAPID_PUBLIC_KEY=temporal_public_key
VAPID_PRIVATE_KEY=temporal_private_key
VAPID_EMAIL=walterlozanoalcalde@gmail.com
"@

# Frontend .env (Producción)
$frontendEnv = @"
REACT_APP_API_URL=https://intranet.vanguardschools.com/api
REACT_APP_VAPID_PUBLIC_KEY=temporal_public_key
"@

# Frontend .env.development
$frontendDevEnv = @"
REACT_APP_API_URL=http://localhost:5000/api
REACT_APP_VAPID_PUBLIC_KEY=temporal_public_key
"@

# Crear archivos
$backendEnv | Out-File -FilePath "backend\.env" -Encoding utf8 -NoNewline
Write-Host "✅ backend/.env creado" -ForegroundColor Green

$backendDevEnv | Out-File -FilePath "backend\.env.development" -Encoding utf8 -NoNewline
Write-Host "✅ backend/.env.development creado" -ForegroundColor Green

$frontendEnv | Out-File -FilePath "frontend\.env" -Encoding utf8 -NoNewline
Write-Host "✅ frontend/.env creado" -ForegroundColor Green

$frontendDevEnv | Out-File -FilePath "frontend\.env.development" -Encoding utf8 -NoNewline
Write-Host "✅ frontend/.env.development creado" -ForegroundColor Green

Write-Host "`n✅ Todos los archivos .env han sido creados!" -ForegroundColor Green
Write-Host "⚠️  Recuerda: Los archivos .env están en .gitignore y NO se subirán a Git" -ForegroundColor Yellow

