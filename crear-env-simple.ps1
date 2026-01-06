# Script simple para crear archivos .env

Write-Host "Creando archivos .env..." -ForegroundColor Green

# Backend .env
@"
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

# CORS - Subdominio de produccion
FRONTEND_URL=https://intranet.vanguardschools.com

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
"@ | Out-File -FilePath "backend\.env" -Encoding utf8

Write-Host "OK backend/.env creado" -ForegroundColor Green

# Frontend .env
@"
REACT_APP_API_URL=https://intranet.vanguardschools.com/api
REACT_APP_VAPID_PUBLIC_KEY=temporal_public_key
"@ | Out-File -FilePath "frontend\.env" -Encoding utf8

Write-Host "OK frontend/.env creado" -ForegroundColor Green
Write-Host ""
Write-Host "Archivos .env creados exitosamente!" -ForegroundColor Green

