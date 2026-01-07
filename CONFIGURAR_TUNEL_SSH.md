# Configurar Túnel SSH para MySQL

## Problema Confirmado
El test muestra:
- ✅ `PingSucceeded : True` - El servidor está online
- ❌ `TcpTestSucceeded : False` - El puerto 3306 está cerrado/bloqueado

**Solución:** Usar túnel SSH para redirigir el puerto MySQL localmente.

---

## Paso 1: Crear Túnel SSH

### ⚠️ IMPORTANTE: El MySQL está en otro servidor (89.117.52.9), NO en Hostinger

### Opción A: Si tienes acceso SSH directo al servidor MySQL

```powershell
ssh -L 3306:localhost:3306 root@89.117.52.9
```

**Nota:** Necesitas las credenciales SSH del servidor MySQL (89.117.52.9)

---

### Opción B: Usar Hostinger como intermediario (Recomendado)

Si NO tienes acceso SSH directo al servidor MySQL, puedes usar el servidor Hostinger como puente:

```powershell
ssh -L 3306:89.117.52.9:3306 root@72.60.172.101
```

**Credenciales Hostinger:**
- Usuario: `root`
- Contraseña: `Vanguard2025@&`

**Cómo funciona:**
1. Te conectas a Hostinger (72.60.172.101)
2. Hostinger redirige el puerto 3306 al servidor MySQL (89.117.52.9:3306)
3. Tu PC local ve MySQL como si estuviera en `localhost:3306`

**Importante:**
- Deja esta ventana abierta mientras trabajas
- El túnel se mantiene activo mientras esta terminal esté abierta
- Si cierras la terminal, el túnel se cierra

---

## Paso 2: Verificar que el Túnel Funciona

### En otra terminal PowerShell:

```powershell
Test-NetConnection -ComputerName localhost -Port 3306
```

**Debería mostrar:**
- `TcpTestSucceeded : True` ✅

---

## Paso 3: Configurar Backend para Usar Túnel

### Editar `backend/.env`:

```env
# Cambiar de:
MYSQL_HOST=89.117.52.9

# A:
MYSQL_HOST=localhost
MYSQL_PORT=3306
```

---

## Paso 4: Reiniciar Backend

```powershell
# Detener el servidor actual (Ctrl+C)
# Luego:
npm run dev
```

---

## Verificación Final

1. ✅ Túnel SSH activo (terminal abierta)
2. ✅ `backend/.env` con `MYSQL_HOST=localhost`
3. ✅ Backend reiniciado
4. ✅ No más errores `ECONNREFUSED`

---

## Alternativa: Script Automático

Puedes crear un script `start-tunnel.ps1`:

```powershell
# start-tunnel.ps1
Write-Host "Iniciando túnel SSH..." -ForegroundColor Green
ssh -L 3306:localhost:3306 root@89.117.52.9
```

Ejecutar:
```powershell
.\start-tunnel.ps1
```

---

## Nota Importante

**El túnel SSH es solo para desarrollo local.**

Para producción, necesitarás:
1. Configurar MySQL para aceptar conexiones remotas
2. O usar el túnel SSH en el servidor de producción
3. O configurar un proxy/load balancer

