# 🚀 CONFIGURACIÓN MYSQL EN PRODUCCIÓN

## 📋 DIFERENCIA: DESARROLLO vs PRODUCCIÓN

### 🔧 DESARROLLO LOCAL (Tu PC)
- ❌ **NO** puede conectarse directamente a MySQL remoto (puerto 3306 cerrado)
- ✅ **Solución**: Túnel SSH (`ssh -L 3306:localhost:3306`)
- ✅ **Configuración**: `MYSQL_HOST=localhost` (a través del túnel)
- ⚠️ **Requiere**: Mantener el túnel SSH activo manualmente

### 🌐 PRODUCCIÓN (Hostinger VPS)
- ✅ **SÍ** puede conectarse directamente a MySQL remoto
- ✅ **NO necesita** túnel SSH
- ✅ **Configuración**: `MYSQL_HOST=89.117.52.9` o `mysql.vanguardschools.edu.pe`
- ✅ **Automático**: La conexión se mantiene activa siempre

---

## 🔄 CONFIGURACIÓN AUTOMÁTICA

### Opción 1: Detectar Automáticamente (Recomendado)

El sistema detecta automáticamente si está en desarrollo o producción:

```javascript
// backend/utils/mysql.js
const isProduction = process.env.NODE_ENV === 'production';
const mysqlHost = isProduction 
  ? process.env.MYSQL_HOST_PRODUCTION  // 89.117.52.9
  : process.env.MYSQL_HOST_DEVELOPMENT; // localhost (con túnel)
```

### Opción 2: Variables de Entorno Separadas

**Archivo: `backend/.env` (Desarrollo)**
```env
NODE_ENV=development
MYSQL_HOST=localhost
MYSQL_PORT=3306
MYSQL_USER=vanguard
MYSQL_PASSWORD=QI_jkA]RsHF_gUDN
MYSQL_DATABASE=vanguard_intranet
```

**Archivo: `backend/.env.production` (Producción - Hostinger)**
```env
NODE_ENV=production
MYSQL_HOST=89.117.52.9
MYSQL_PORT=3306
MYSQL_USER=vanguard
MYSQL_PASSWORD=QI_jkA]RsHF_gUDN
MYSQL_DATABASE=vanguard_intranet
```

---

## 🚀 DESPLIEGUE EN PRODUCCIÓN

### Paso 1: Subir Código a Hostinger

```bash
# En tu PC local
git push origin main

# En Hostinger VPS
cd /var/www/intranet
git pull origin main
npm install --production
```

### Paso 2: Configurar .env en Producción

**En el servidor Hostinger**, crea/edita `backend/.env`:

```env
# MySQL Remoto (Conexión Directa - NO TÚNEL)
MYSQL_HOST=89.117.52.9
MYSQL_PORT=3306
MYSQL_USER=vanguard
MYSQL_PASSWORD=QI_jkA]RsHF_gUDN
MYSQL_DATABASE=vanguard_intranet

# PostgreSQL (Local en Hostinger)
POSTGRES_HOST=localhost
POSTGRES_PORT=5432
POSTGRES_USER=postgres
POSTGRES_PASSWORD=Vanguard2025@&
POSTGRES_DATABASE=aula_virtual

# JWT
JWT_SECRET=Vanguard2025_AulaVirtual_SuperSecreto_JWT_Key_2025
JWT_EXPIRES_IN=24h

# Server
PORT=5000
NODE_ENV=production

# CORS
FRONTEND_URL=https://intranet.vanguardschools.com

# Email
EMAIL_HOST=smtp.gmail.com
EMAIL_PORT=587
EMAIL_USER=walterlozanoalcalde@gmail.com
EMAIL_PASSWORD=ldvkcmqxshxdkupv
EMAIL_FROM=walterlozanoalcalde@gmail.com
```

### Paso 3: Iniciar Servidor en Producción

**Opción A: PM2 (Recomendado - Se mantiene activo siempre)**

```bash
# Instalar PM2 globalmente
npm install -g pm2

# Iniciar backend
cd backend
pm2 start server.js --name "aula-virtual-backend"

# Iniciar frontend (si usas build estático)
cd ../frontend
npm run build
pm2 serve build 3000 --name "aula-virtual-frontend" --spa

# Guardar configuración PM2
pm2 save
pm2 startup  # Configurar para iniciar automáticamente al reiniciar servidor
```

**Opción B: systemd (Alternativa)**

```bash
# Crear servicio systemd
sudo nano /etc/systemd/system/aula-virtual.service
```

```ini
[Unit]
Description=Aula Virtual Node.js App
After=network.target

[Service]
Type=simple
User=root
WorkingDirectory=/var/www/intranet/backend
Environment=NODE_ENV=production
ExecStart=/usr/bin/node server.js
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

```bash
# Activar servicio
sudo systemctl enable aula-virtual
sudo systemctl start aula-virtual
sudo systemctl status aula-virtual
```

---

## ✅ VENTAJAS DE PRODUCCIÓN

### 1. **Conexión Directa**
- ✅ No necesita túnel SSH
- ✅ Conexión estable y permanente
- ✅ No se cierra nunca (a menos que el servidor se reinicie)

### 2. **Automático**
- ✅ Se conecta automáticamente al iniciar
- ✅ Se reconecta si hay interrupciones
- ✅ Pool de conexiones gestionado automáticamente

### 3. **Rendimiento**
- ✅ Latencia más baja (conexión directa)
- ✅ Sin overhead del túnel SSH
- ✅ Pool de conexiones optimizado

---

## 🔒 SEGURIDAD EN PRODUCCIÓN

### 1. **Firewall MySQL**
- ✅ El puerto 3306 está abierto **SOLO** para la IP de Hostinger
- ✅ No está abierto para todo el mundo
- ✅ Solo el servidor Hostinger puede conectarse

### 2. **Usuario MySQL**
- ✅ Usuario `vanguard` con permisos específicos
- ✅ Solo puede leer datos (SELECT)
- ✅ No puede modificar nada

### 3. **Variables de Entorno**
- ✅ `.env` no se sube a Git (está en `.gitignore`)
- ✅ Credenciales seguras en el servidor
- ✅ No expuestas en el código

---

## 📝 RESUMEN

| Aspecto | Desarrollo Local | Producción (Hostinger) |
|---------|------------------|----------------------|
| **Conexión** | Túnel SSH manual | Directa automática |
| **MYSQL_HOST** | `localhost` | `89.117.52.9` |
| **Túnel SSH** | ✅ Requerido | ❌ No necesario |
| **Mantenimiento** | Manual (abrir túnel) | Automático |
| **Estabilidad** | Depende del túnel | Permanente |

---

## 🎯 CONCLUSIÓN

**En producción:**
- ✅ **NO necesitas** túnel SSH
- ✅ **NO necesitas** hacer nada manualmente
- ✅ La conexión es **automática y permanente**
- ✅ Se mantiene activa **siempre** (incluso si reinicias el servidor con PM2/systemd)

**Solo necesitas:**
1. Configurar el `.env` correcto en Hostinger
2. Usar PM2 o systemd para mantener el servidor activo
3. ¡Listo! La conexión funciona automáticamente





