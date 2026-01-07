# Script para iniciar túnel SSH a MySQL
Write-Host "🚇 Iniciando túnel SSH a MySQL..." -ForegroundColor Cyan
Write-Host "   Servidor MySQL: 89.117.52.9" -ForegroundColor Gray
Write-Host "   Puerto local: 3306 -> remoto: 3306" -ForegroundColor Gray
Write-Host ""
Write-Host "⚠️  IMPORTANTE:" -ForegroundColor Yellow
Write-Host "   1. Necesitas acceso SSH al servidor MySQL (89.117.52.9)" -ForegroundColor Yellow
Write-Host "   2. Deja esta ventana abierta mientras trabajas" -ForegroundColor Yellow
Write-Host "   3. Si cierras esta ventana, el túnel se cerrará" -ForegroundColor Yellow
Write-Host ""
Write-Host "📝 NOTA: Si no tienes acceso SSH directo al servidor MySQL," -ForegroundColor Cyan
Write-Host "   puedes usar el servidor Hostinger como intermediario:" -ForegroundColor Cyan
Write-Host "   ssh -L 3306:89.117.52.9:3306 root@72.60.172.101" -ForegroundColor Gray
Write-Host ""
Write-Host "Presiona Ctrl+C para cerrar el túnel" -ForegroundColor Gray
Write-Host ""

# OPCIÓN 1: Si tienes acceso SSH directo al servidor MySQL
# ssh -L 3306:localhost:3306 root@89.117.52.9

# OPCIÓN 2: Si NO tienes acceso directo, usa Hostinger como intermediario
ssh -L 3306:89.117.52.9:3306 root@72.60.172.101

