# 🚀 GUÍA DE DESPLIEGUE EN HOSTINGER

## 📋 PREPARACIÓN LOCAL

### 1. Build del Frontend
```bash
cd frontend
npm run build
```
Esto creará la carpeta `frontend/build` con los archivos optimizados.

### 2. Preparar variables de entorno

Crear `backend/.env.production`:
```env
# MySQL Remoto (VPS MySQL - NO Hostinger)
MYSQL_HOST=localhost
MYSQL_PORT=3306
MYSQL_USER=vanguard
MYSQL_PASSWORD=QI_jkA]RsHF_gUDN
MYSQL_DATABASE=vanguard_intranet

# PostgreSQL (Local o servidor Hostinger)
POSTGRES_HOST=localhost
POSTGRES_PORT=5432
POSTGRES_USER=postgres
POSTGRES_PASSWORD=waltito10
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

# CORS - Subdominio de produccion
FRONTEND_URL=https://intranet.vanguardschools.com

# Email (SMTP Gmail)
EMAIL_HOST=smtp.gmail.com
EMAIL_PORT=587
EMAIL_USER=walterlozanoalcalde@gmail.com
EMAIL_PASSWORD=ldvkcmqxshxdkupv
EMAIL_FROM=walterlozanoalcalde@gmail.com

# PWA - Notificaciones Push
VAPID_PUBLIC_KEY=temporal_public_key
VAPID_PRIVATE_KEY=temporal_private_key
VAPID_EMAIL=walterlozanoalcalde@gmail.com

# Configuración FTP/SFTP para subida automática al servidor PHP
FTP_HOST=89.117.52.9
FTP_USER=vanguard
FTP_PASSWORD=CtxADB8q0SaVYox
FTP_PORT=22
FTP_PROTOCOL=sftp
FTP_BASE_PATH=/home/vanguard/nuevo.vanguardschools.edu.pe
```

---

## 📦 ESTRUCTURA DE ARCHIVOS PARA SUBIR

### Opción A: Subir todo el proyecto (recomendado para primera vez)

```
hostinger/
├── backend/
│   ├── server.js
│   ├── package.json
│   ├── .env (copiar de .env.production)
│   ├── routes/
│   ├── utils/
│   ├── middleware/
│   └── uploads/ (crear carpeta, permisos 755)
├── frontend/
│   └── build/ (resultado de npm run build)
└── package.json (raíz, si existe)
```

### Opción B: Solo archivos necesarios (despliegues posteriores)

**Backend:**
- `backend/server.js`
- `backend/package.json`
- `backend/.env`
- `backend/routes/` (todos los archivos)
- `backend/utils/` (todos los archivos)
- `backend/middleware/` (todos los archivos)
- `backend/uploads/` (carpeta vacía, permisos 755)

**Frontend:**
- `frontend/build/` (todo el contenido)

---

## 🔧 PASOS DE DESPLIEGUE EN HOSTINGER

### Paso 1: Conectar por SSH (PuTTY o WinSCP)

```bash
# Credenciales SSH (el usuario las proporcionará)
Host: [IP o dominio de Hostinger]
Puerto: 22
Usuario: [usuario SSH]
Password: [contraseña SSH]
```

### Paso 2: Preparar el servidor

```bash
# 1. Instalar Node.js (si no está instalado)
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# 2. Instalar PM2 (gestor de procesos)
sudo npm install -g pm2

# 3. Instalar PostgreSQL (si no está instalado)
sudo apt-get install postgresql postgresql-contrib

# 4. Crear usuario y base de datos PostgreSQL
sudo -u postgres psql
CREATE DATABASE aula_virtual;
CREATE USER postgres WITH PASSWORD 'waltito10';
GRANT ALL PRIVILEGES ON DATABASE aula_virtual TO postgres;
\q
```

### Paso 3: Subir archivos (WinSCP)

1. Conectar con WinSCP
2. Navegar a la carpeta del proyecto (ej: `/home/usuario/react-aula-virtual`)
3. Subir:
   - Carpeta `backend/` completa
   - Carpeta `frontend/build/` completa
4. Establecer permisos:
   ```bash
   chmod 755 backend/uploads
   chmod 644 backend/.env
   ```

### Paso 4: Instalar dependencias

```bash
cd /ruta/al/proyecto
cd backend
npm install --production
```

### Paso 5: Configurar variables de entorno

```bash
# Editar .env con los valores de producción
nano backend/.env
# O subir el archivo .env.production como .env
```

### Paso 6: Iniciar el servidor

**Opción A: Con PM2 (recomendado)**
```bash
cd backend
pm2 start server.js --name "react-aula-virtual"
pm2 save
pm2 startup  # Seguir las instrucciones para iniciar al boot
```

**Opción B: Con nohup (alternativa simple)**
```bash
cd backend
nohup node server.js > server.log 2>&1 &
```

### Paso 7: Configurar Nginx (Proxy Reverso)

Si Hostinger usa Nginx, crear archivo `/etc/nginx/sites-available/react-aula-virtual`:

```nginx
server {
    listen 80;
    server_name intranet.vanguardschools.com;

    # Frontend (React build)
    location / {
        root /ruta/al/proyecto/frontend/build;
        try_files $uri $uri/ /index.html;
    }

    # Backend API
    location /api {
        proxy_pass http://localhost:5000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    # Uploads
    location /uploads {
        proxy_pass http://localhost:5000;
        proxy_set_header Host $host;
    }
}
```

Activar sitio:
```bash
sudo ln -s /etc/nginx/sites-available/react-aula-virtual /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Paso 8: Configurar SSL (Let's Encrypt)

```bash
sudo apt-get install certbot python3-certbot-nginx
sudo certbot --nginx -d intranet.vanguardschools.com
```

---

## 🔄 ACTUALIZACIONES POSTERIORES

### Subir solo cambios:

1. **Backend:**
   ```bash
   # En local, hacer cambios y commit
   # Subir solo archivos modificados por WinSCP
   # En servidor:
   cd backend
   pm2 restart react-aula-virtual
   ```

2. **Frontend:**
   ```bash
   # En local:
   cd frontend
   npm run build
   # Subir carpeta build/ completa por WinSCP
   # Reiniciar Nginx si es necesario:
   sudo systemctl reload nginx
   ```

---

## 🐛 VERIFICACIÓN Y DEBUGGING

### Ver logs del servidor:
```bash
pm2 logs react-aula-virtual
# O si usas nohup:
tail -f backend/server.log
```

### Verificar que el servidor esté corriendo:
```bash
pm2 status
# O:
ps aux | grep node
```

### Probar API:
```bash
curl http://localhost:5000/api/auth/me
# (requiere token en header)
```

### Verificar puerto:
```bash
netstat -tulpn | grep 5000
```

---

## ⚠️ PROBLEMAS COMUNES

### Error: Puerto 5000 ya en uso
```bash
# Ver qué proceso usa el puerto
lsof -i :5000
# Matar proceso
kill -9 [PID]
```

### Error: Permisos denegados
```bash
chmod -R 755 backend/uploads
chmod 644 backend/.env
```

### Error: Node modules no encontrados
```bash
cd backend
rm -rf node_modules
npm install --production
```

### Error: PostgreSQL no conecta
```bash
# Verificar que PostgreSQL esté corriendo
sudo systemctl status postgresql
# Verificar conexión
psql -h localhost -U postgres -d aula_virtual
```

---

## 📝 NOTAS IMPORTANTES

1. **Nunca subir `node_modules/`** - Instalar en el servidor
2. **Nunca subir `.env` con datos sensibles** en commits
3. **Siempre usar `NODE_ENV=production`** en servidor
4. **Verificar rutas de archivos** en el servidor
5. **Configurar firewall** si es necesario
6. **Hacer backups** antes de actualizar

---

## 🔐 SEGURIDAD

- ✅ Variables de entorno en `.env` (no en código)
- ✅ JWT secret fuerte
- ✅ HTTPS habilitado (SSL)
- ✅ CORS configurado correctamente
- ✅ Helmet para headers de seguridad
- ✅ Rate limiting activo
- ⚠️ Verificar permisos de archivos
- ⚠️ Configurar firewall
- ⚠️ Backup de base de datos regular




