# Script para iniciar túnel SSH a MySQL
Write-Host "🚇 Iniciando túnel SSH a MySQL..." -ForegroundColor Cyan
Write-Host "   Servidor MySQL: mysql.vanguardschools.edu.pe (89.117.52.9)" -ForegroundColor Gray
Write-Host "   Puerto local: 3306 -> remoto: 3306" -ForegroundColor Gray
Write-Host ""
Write-Host "⚠️  IMPORTANTE:" -ForegroundColor Yellow
Write-Host "   1. Deja esta ventana abierta mientras trabajas" -ForegroundColor Yellow
Write-Host "   2. Si cierras esta ventana, el túnel se cerrará" -ForegroundColor Yellow
Write-Host "   3. Ingresa la contraseña SSH cuando se solicite" -ForegroundColor Yellow
Write-Host ""
Write-Host "🔐 Credenciales SSH:" -ForegroundColor Cyan
Write-Host "   Usuario: vanguard" -ForegroundColor Gray
Write-Host "   Contraseña: CtxADB8q0SaVYox" -ForegroundColor Gray
Write-Host ""
Write-Host "Presiona Ctrl+C para cerrar el túnel" -ForegroundColor Gray
Write-Host ""

# Túnel directo al servidor MySQL
# Redirige localhost:3306 → MySQL en el servidor (localhost:3306 dentro del servidor)
ssh -L 3306:localhost:3306 vanguard@89.117.52.9

