# 🔌 Configurar Túnel SSH para MySQL

## 📋 Información del MySQL

- **Host:** `mysql.vanguardschools.edu.pe` (o IP: `89.117.52.9`)
- **Puerto:** `3306`
- **Usuario:** `vanguard`
- **Contraseña:** `QI_jkA]RsHF_gUDN`
- **Base de datos:** `vanguard_intranet`

---

## ⚠️ Problema

El puerto 3306 está cerrado desde tu IP local. Necesitas usar un túnel SSH.

---

## 🚇 Solución: Túnel SSH Directo al Servidor MySQL

### Paso 1: Crear Túnel SSH

Abre una **nueva terminal PowerShell** y ejecuta:

```powershell
ssh -L 3306:localhost:3306 vanguard@89.117.52.9
```

**Credenciales SSH:**
- Usuario: `vanguard`
- Contraseña: `CtxADB8q0SaVYox`

**Cómo funciona:**
1. Te conectas directamente al servidor MySQL (89.117.52.9)
2. Rediriges el puerto 3306 local → MySQL en el servidor (localhost:3306 dentro del servidor)
3. Tu PC ve MySQL como si estuviera en `localhost:3306`

---

---

### Paso 2: Dejar el Túnel Activo

**⚠️ IMPORTANTE:**
- Deja esta terminal abierta mientras trabajas
- El túnel se mantiene activo mientras esta terminal esté abierta
- Si cierras la terminal, el túnel se cierra

---

### Paso 3: Configurar Backend

Edita `backend/.env` y cambia:

```env
# Cambiar de:
MYSQL_HOST=mysql.vanguardschools.edu.pe
# O
MYSQL_HOST=89.117.52.9

# A:
MYSQL_HOST=localhost
MYSQL_PORT=3306
MYSQL_USER=vanguard
MYSQL_PASSWORD=QI_jkA]RsHF_gUDN
MYSQL_DATABASE=vanguard_intranet
```

---

### Paso 4: Verificar Túnel

En otra terminal PowerShell:

```powershell
Test-NetConnection -ComputerName localhost -Port 3306
```

**Debería mostrar:**
- `TcpTestSucceeded : True` ✅

---

### Paso 5: Reiniciar Backend

1. Detén el servidor actual (`Ctrl+C`)
2. Reinicia:
   ```powershell
   npm run dev
   ```

---

## ✅ Verificación Final

1. ✅ Túnel SSH activo (terminal abierta)
2. ✅ `backend/.env` con `MYSQL_HOST=localhost`
3. ✅ Backend reiniciado
4. ✅ No más errores `ECONNREFUSED`
5. ✅ Login funciona con usuarios reales

---

## 🔄 Script Automático

He creado `start-tunnel.ps1` que puedes usar:

```powershell
.\start-tunnel.ps1
```

---

## 📝 Notas

- **El túnel SSH es solo para desarrollo local**
- **Para producción:** El servidor Hostinger se conectará directamente a MySQL (sin túnel)
- **Si el túnel se cierra:** Simplemente vuelve a ejecutar el comando SSH

