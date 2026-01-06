# ⚙️ INSTRUCCIONES PARA CREAR ARCHIVOS .env

## 🚀 OPCIÓN 1: Script Automático (Windows PowerShell)

```powershell
# Ejecutar el script
.\crear-env.ps1
```

Esto creará automáticamente todos los archivos `.env` con tus configuraciones.

---

## 📝 OPCIÓN 2: Crear Manualmente

### Backend .env

Crear archivo: `backend/.env`

```env
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
```

### Frontend .env

Crear archivo: `frontend/.env`

```env
REACT_APP_API_URL=https://intranet.vanguardschools.com/api
REACT_APP_VAPID_PUBLIC_KEY=temporal_public_key
```

### Para Desarrollo Local

**Backend:** `backend/.env.development`
- Cambiar `NODE_ENV=development`
- Cambiar `FRONTEND_URL=http://localhost:3000`
- Cambiar `POSTGRES_PASSWORD` a tu password local

**Frontend:** `frontend/.env.development`
- Cambiar `REACT_APP_API_URL=http://localhost:5000/api`

---

## ✅ VERIFICACIÓN

Después de crear los archivos:

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

## 🔒 SEGURIDAD

- ✅ Los archivos `.env` están en `.gitignore`
- ✅ NO se subirán a Git
- ✅ Mantén las credenciales seguras
- ✅ Usa diferentes valores en desarrollo y producción

---

**Los archivos .env son esenciales para que la aplicación funcione.** ⚙️

