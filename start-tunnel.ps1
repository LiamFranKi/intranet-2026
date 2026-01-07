# Script para iniciar túnel SSH a MySQL
Write-Host "🚇 Iniciando túnel SSH a MySQL..." -ForegroundColor Cyan
Write-Host "   Servidor MySQL: mysql.vanguardschools.edu.pe (89.117.52.9)" -ForegroundColor Gray
Write-Host "   Puerto local: 3306 -> remoto: 3306" -ForegroundColor Gray
Write-Host ""
Write-Host "⚠️  IMPORTANTE:" -ForegroundColor Yellow
Write-Host "   1. Necesitas acceso SSH al servidor MySQL" -ForegroundColor Yellow
Write-Host "   2. Deja esta ventana abierta mientras trabajas" -ForegroundColor Yellow
Write-Host "   3. Si cierras esta ventana, el túnel se cerrará" -ForegroundColor Yellow
Write-Host ""
Write-Host "📝 OPCIONES:" -ForegroundColor Cyan
Write-Host "   OPCIÓN 1 (Recomendada): Túnel directo al servidor MySQL" -ForegroundColor Green
Write-Host "      ssh -L 3306:localhost:3306 root@89.117.52.9" -ForegroundColor Gray
Write-Host ""
Write-Host "   OPCIÓN 2: Usar Hostinger como intermediario (solo si no tienes acceso directo)" -ForegroundColor Yellow
Write-Host "      ssh -L 3306:mysql.vanguardschools.edu.pe:3306 root@72.60.172.101" -ForegroundColor Gray
Write-Host ""
Write-Host "Presiona Ctrl+C para cerrar el túnel" -ForegroundColor Gray
Write-Host ""

# OPCIÓN 1: Túnel directo al servidor MySQL (Recomendado)
# Necesitas las credenciales SSH del servidor MySQL (89.117.52.9)
# Descomenta y ajusta según tus credenciales:
# ssh -L 3306:localhost:3306 root@89.117.52.9

# OPCIÓN 2: Usar Hostinger como intermediario
# Solo usar si NO tienes acceso SSH directo al servidor MySQL
# Esto redirige localhost:3306 → mysql.vanguardschools.edu.pe:3306 a través de Hostinger
Write-Host "⚠️  Usando Hostinger como intermediario..." -ForegroundColor Yellow
Write-Host "   Si tienes acceso SSH directo al MySQL, mejor usa la OPCIÓN 1" -ForegroundColor Yellow
Write-Host ""
ssh -L 3306:mysql.vanguardschools.edu.pe:3306 root@72.60.172.101

