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

## 🚇 Soluciones Posibles

### Opción 1: Túnel SSH Directo al Servidor MySQL (Recomendado)

Si tienes acceso SSH al servidor MySQL (89.117.52.9):

```powershell
ssh -L 3306:localhost:3306 root@89.117.52.9
```

**Nota:** Necesitas las credenciales SSH del servidor MySQL (no las de Hostinger).

**Cómo funciona:**
1. Te conectas directamente al servidor MySQL
2. Rediriges el puerto 3306 local → MySQL en el servidor
3. Tu PC ve MySQL como si estuviera en `localhost:3306`

---

### Opción 2: Configurar MySQL para Aceptar Conexiones Remotas

Si tienes acceso al servidor MySQL, puedes configurarlo para aceptar conexiones remotas desde tu IP:

1. Conectarte al servidor MySQL (SSH)
2. Configurar `bind-address = 0.0.0.0` en MySQL
3. Dar permisos al usuario `vanguard` desde tu IP
4. Abrir puerto 3306 en firewall

**Ver detalles en:** `VERIFICAR_MYSQL_REMOTO.md`

---

### Opción 3: Usar Hostinger como Intermediario (Solo si no tienes acceso directo)

**⚠️ Solo usar si NO tienes acceso SSH directo al servidor MySQL**

```powershell
ssh -L 3306:mysql.vanguardschools.edu.pe:3306 root@72.60.172.101
```

**Credenciales Hostinger:**
- Usuario: `root`
- Contraseña: `Vanguard2025@&`

**Cómo funciona:**
1. Te conectas a Hostinger (72.60.172.101)
2. Hostinger redirige el puerto 3306 local → MySQL remoto (mysql.vanguardschools.edu.pe:3306)
3. Tu PC ve MySQL como si estuviera en `localhost:3306`

**Nota:** Esto solo es necesario si Hostinger tiene acceso al servidor MySQL y tú no tienes acceso directo.

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

