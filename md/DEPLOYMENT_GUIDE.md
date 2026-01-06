# 📦 GUÍA DE DEPLOYMENT - RESUMEN RÁPIDO

## 🎯 CONFIGURACIÓN ACTUAL

### Archivos .env Creados:
- ✅ `backend/.env` - Producción
- ✅ `backend/.env.development` - Desarrollo
- ✅ `frontend/.env` - Producción
- ✅ `frontend/.env.development` - Desarrollo

### Configuraciones:
- ✅ MySQL: mysql.vanguardschools.edu.pe
- ✅ PostgreSQL: localhost (en Hostinger)
- ✅ SMTP: Gmail configurado
- ✅ Subdominio: intranet.vanguardschools.com

---

## 🚀 DEPLOYMENT RÁPIDO

### 1. Conectar al Servidor
```bash
ssh root@72.60.172.101
```

### 2. Instalar Dependencias del Sistema
```bash
apt update && apt upgrade -y
curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
apt install -y nodejs postgresql postgresql-contrib nginx
```

### 3. Configurar PostgreSQL
```bash
su - postgres
psql
CREATE DATABASE aula_virtual;
CREATE USER aula_virtual_user WITH PASSWORD 'Vanguard2025@&';
GRANT ALL PRIVILEGES ON DATABASE aula_virtual TO aula_virtual_user;
\q
exit
```

### 4. Subir Código
```bash
cd /var/www/html
mkdir -p intranet
# Subir código aquí (Git, SCP, etc.)
```

### 5. Instalar y Compilar
```bash
cd /var/www/html/intranet/backend
npm install --production

cd ../frontend
npm install
npm run build
```

### 6. Iniciar con PM2
```bash
npm install -g pm2
cd /var/www/html/intranet/backend
pm2 start server.js --name aula-virtual-backend
pm2 startup
pm2 save
```

### 7. Configurar Nginx
Ver `PASO_2_CONFIGURACION_SERVIDOR.md` para configuración completa.

---

## 📝 NOTAS IMPORTANTES

1. **MySQL está en otro servidor** - No necesita instalación en Hostinger
2. **PostgreSQL va en Hostinger** - Se instala y configura ahí
3. **Frontend se compila** - `npm run build` genera carpeta `build/`
4. **Backend corre con PM2** - Para mantenerlo activo
5. **Nginx sirve frontend y proxy a backend**

---

**Todo está configurado y listo para deployment.** 🚀

