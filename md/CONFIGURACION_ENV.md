# ⚙️ CONFIGURACIÓN DE VARIABLES DE ENTORNO

## 📋 PASO 1: BACKEND (.env)

### Ubicación
```
backend/.env
```

### Contenido a Configurar

```env
# MySQL Remoto (Hostinger) - SOLO LECTURA
MYSQL_HOST=tu_ip_o_dominio_hostinger
MYSQL_PORT=3306
MYSQL_USER=react_readonly
MYSQL_PASSWORD=tu_password_segura
MYSQL_DATABASE=vanguard_intranet

# PostgreSQL (Local o servidor React)
POSTGRES_HOST=localhost
POSTGRES_PORT=5432
POSTGRES_USER=postgres
POSTGRES_PASSWORD=tu_password_postgres
POSTGRES_DATABASE=aula_virtual

# PHP API (Para exportar notas)
PHP_API_URL=http://tu_dominio_php/api
PHP_API_TOKEN=token_secreto

# JWT
JWT_SECRET=tu_secreto_jwt_super_seguro_2025
JWT_EXPIRES_IN=24h

# Server (se detectará automáticamente)
PORT=5000
NODE_ENV=development

# CORS
FRONTEND_URL=http://localhost:3000

# Email (Para notificaciones y emails HTML)
EMAIL_HOST=smtp.gmail.com
EMAIL_PORT=587
EMAIL_USER=tu_email@gmail.com
EMAIL_PASSWORD=tu_app_password
EMAIL_FROM=noreply@tudominio.com

# PWA - Notificaciones Push
VAPID_PUBLIC_KEY=tu_vapid_public_key
VAPID_PRIVATE_KEY=tu_vapid_private_key
VAPID_EMAIL=tu_email@tudominio.com

# ============================================
# CONFIGURACIÓN FTP/SFTP PARA SUBIDA AUTOMÁTICA
# ============================================
# Solo funcionará en producción (Hostinger)
# En desarrollo local, las fotos se guardan solo localmente

# IP o dominio del servidor PHP (VPS)
FTP_HOST=89.117.52.9

# Usuario SSH/SFTP del servidor PHP
FTP_USER=vanguard

# Contraseña SSH/SFTP del servidor PHP
FTP_PASSWORD=CtxADB8q0SaVYox

# Puerto SFTP (22 es el estándar)
FTP_PORT=22

# Protocolo a usar (sftp o ftp)
FTP_PROTOCOL=sftp

# RUTA BASE donde está instalado el sistema PHP en el servidor
# IMPORTANTE: Esta es la ruta ABSOLUTA en el servidor PHP
# Ejemplos comunes:
#   - /var/www/html (Apache estándar)
#   - /home/vanguard/public_html (cPanel)
#   - /var/www/vhosts/dominio.com/httpdocs (Plesk)
# 
# Para saber cuál es la correcta:
# 1. Conectarte al servidor PHP vía SSH: ssh vanguard@89.117.52.9
# 2. Navegar hasta donde está tu sistema PHP
# 3. Ejecutar 'pwd' para ver la ruta
# 4. Esa es tu FTP_BASE_PATH
# RUTA CONFIRMADA: /home/vanguard/nuevo.vanguardschools.edu.pe/
FTP_BASE_PATH=/home/vanguard/nuevo.vanguardschools.edu.pe
```

### Valores a Cambiar

1. **MYSQL_HOST**: IP o dominio de tu servidor Hostinger
2. **MYSQL_USER**: Usuario MySQL de solo lectura (ej: `react_readonly`)
3. **MYSQL_PASSWORD**: Contraseña del usuario MySQL
4. **POSTGRES_PASSWORD**: Contraseña de PostgreSQL
5. **JWT_SECRET**: Genera un secreto seguro (puede ser cualquier string largo)
6. **EMAIL_***: Configuración de email (Gmail u otro)
7. **VAPID_***: Claves para notificaciones push (se generarán después)

---

## 📋 PASO 2: FRONTEND (.env)

### Ubicación
```
frontend/.env
```

### Contenido a Configurar

```env
REACT_APP_API_URL=http://localhost:5000/api
REACT_APP_VAPID_PUBLIC_KEY=tu_vapid_public_key
```

### Valores a Cambiar

1. **REACT_APP_API_URL**: URL del backend (por defecto `http://localhost:5000/api`)
2. **REACT_APP_VAPID_PUBLIC_KEY**: Clave pública VAPID (igual que en backend)

---

## 🔧 PASO 3: CREAR ARCHIVOS .env

### Windows (PowerShell)

```powershell
# Backend
cd backend
copy .env.example .env

# Frontend
cd ..\frontend
copy .env.example .env
```

### Linux/Mac

```bash
# Backend
cd backend
cp .env.example .env

# Frontend
cd ../frontend
cp .env.example .env
```

---

## 📝 PASO 4: EDITAR ARCHIVOS .env

Abre los archivos `.env` creados y edita los valores según tu configuración.

### Ejemplo Backend:

```env
MYSQL_HOST=192.168.1.100
MYSQL_USER=react_readonly
MYSQL_PASSWORD=mi_password_segura_123
POSTGRES_PASSWORD=postgres_password_123
JWT_SECRET=mi_secreto_super_seguro_2025_aula_virtual
```

### Ejemplo Frontend:

```env
REACT_APP_API_URL=http://localhost:5000/api
```

---

## 🔐 GENERAR JWT_SECRET

Puedes generar un secreto seguro con:

```bash
# Node.js
node -e "console.log(require('crypto').randomBytes(64).toString('hex'))"

# O simplemente usa un string largo y seguro
```

---

## 📧 CONFIGURAR EMAIL (Gmail)

Si usas Gmail:

1. **Habilitar "Contraseñas de aplicaciones"**:
   - Ir a: https://myaccount.google.com/apppasswords
   - Generar una contraseña de aplicación
   - Usar esa contraseña en `EMAIL_PASSWORD`

2. **Configuración:**
   ```env
   EMAIL_HOST=smtp.gmail.com
   EMAIL_PORT=587
   EMAIL_USER=tu_email@gmail.com
   EMAIL_PASSWORD=tu_app_password_de_16_caracteres
   ```

---

## 🔔 GENERAR CLAVES VAPID (Notificaciones Push)

Las claves VAPID se generarán cuando implementemos las notificaciones. Por ahora puedes dejar valores temporales:

```env
VAPID_PUBLIC_KEY=temporal_public_key
VAPID_PRIVATE_KEY=temporal_private_key
VAPID_EMAIL=tu_email@tudominio.com
```

---

## ✅ CHECKLIST

- [ ] Crear `backend/.env` desde `.env.example`
- [ ] Crear `frontend/.env` desde `.env.example`
- [ ] Configurar `MYSQL_HOST` (IP o dominio Hostinger)
- [ ] Configurar `MYSQL_USER` y `MYSQL_PASSWORD`
- [ ] Configurar `POSTGRES_PASSWORD`
- [ ] Generar y configurar `JWT_SECRET`
- [ ] Configurar `EMAIL_*` (si usas emails)
- [ ] Configurar `REACT_APP_API_URL` (frontend)
- [ ] Verificar que los archivos `.env` no se suben a Git (están en `.gitignore`)

---

## ⚠️ IMPORTANTE

- ✅ Los archivos `.env` están en `.gitignore` (no se suben a Git)
- ✅ Nunca compartas tus archivos `.env`
- ✅ Usa valores diferentes en desarrollo y producción
- ✅ El `JWT_SECRET` debe ser único y seguro

---

## 🧪 PROBAR CONFIGURACIÓN

Después de configurar, prueba:

```bash
# Backend
cd backend
npm run dev
# Debería iniciar sin errores

# Frontend
cd frontend
npm start
# Debería iniciar sin errores
```

---

**Una vez configurado el .env, podrás probar la conexión a MySQL y PostgreSQL.** ⚙️

