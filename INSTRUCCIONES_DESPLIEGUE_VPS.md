# 🚀 Instrucciones de Despliegue en VPS

## 📋 Información del VPS

- **IP:** 89.117.52.9
- **Usuario:** vanguard
- **Contraseña:** CtxADB8q0SaVYox
- **Carpeta destino:** intranet206
- **Repositorio GitHub:** https://github.com/LiamFranKi/intranet-2026

## 🔧 Paso 1: Conectarse al VPS

```bash
ssh vanguard@89.117.52.9
# Contraseña: CtxADB8q0SaVYox
```

## 🔧 Paso 2: Navegar a la carpeta destino

```bash
cd ~
cd intranet206
```

## 🔧 Paso 3: Clonar el repositorio

```bash
# Si la carpeta está vacía
git clone https://github.com/LiamFranKi/intranet-2026.git .

# O si ya existe, actualizar
git pull origin main
```

## 🔧 Paso 4: Instalar Node.js (si no está instalado)

```bash
# Verificar si Node.js está instalado
node --version

# Si no está instalado, instalar Node.js 18
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# Verificar instalación
node --version
npm --version
```

## 🔧 Paso 5: Instalar dependencias

```bash
# Instalar dependencias del backend
cd backend
npm install --production

# Instalar dependencias del frontend
cd ../frontend
npm install
```

## 🔧 Paso 6: Configurar variables de entorno

### Backend (.env)

```bash
cd ~/intranet206/backend
nano .env
```

Contenido del archivo `.env`:

```env
NODE_ENV=production
PORT=5000

# MySQL - Ajustar según tu configuración
MYSQL_HOST=localhost
MYSQL_PORT=3306
MYSQL_USER=tu_usuario_mysql
MYSQL_PASSWORD=tu_password_mysql
MYSQL_DATABASE=tu_base_datos

# JWT
JWT_SECRET=tu_secret_key_muy_segura_aqui_cambiar_en_produccion

# CORS - Agregar tu dominio
ALLOWED_ORIGINS=http://tu-dominio.com,https://tu-dominio.com

# FTP (si usas)
FTP_HOST=tu_ftp_host
FTP_USERNAME=tu_ftp_user
FTP_PASSWORD=tu_ftp_password
FTP_BASE_PATH=/ruta/base

# Frontend URL
FRONTEND_URL=http://tu-dominio.com
```

### Frontend (.env)

```bash
cd ~/intranet206/frontend
nano .env
```

Contenido del archivo `.env`:

```env
REACT_APP_API_URL=http://tu-dominio.com/api
```

## 🔧 Paso 7: Compilar el frontend

```bash
cd ~/intranet206/frontend
npm run build
```

Esto creará la carpeta `build/` con los archivos estáticos.

## 🔧 Paso 8: Instalar PM2

```bash
sudo npm install -g pm2
```

## 🔧 Paso 9: Configurar PM2

```bash
cd ~/intranet206
nano ecosystem.config.js
```

Contenido del archivo `ecosystem.config.js`:

```javascript
module.exports = {
  apps: [{
    name: 'intranet206-backend',
    script: './backend/server.js',
    cwd: '/home/vanguard/intranet206',
    instances: 1,
    exec_mode: 'fork',
    env: {
      NODE_ENV: 'production',
      PORT: 5000
    },
    error_file: './logs/backend-error.log',
    out_file: './logs/backend-out.log',
    log_date_format: 'YYYY-MM-DD HH:mm:ss Z',
    merge_logs: true,
    autorestart: true,
    watch: false,
    max_memory_restart: '1G'
  }]
};
```

## 🔧 Paso 10: Iniciar la aplicación con PM2

```bash
cd ~/intranet206
pm2 start ecosystem.config.js
pm2 save
pm2 startup
```

## 🔧 Paso 11: Verificar que funciona

```bash
# Ver estado de PM2
pm2 status

# Ver logs
pm2 logs intranet206-backend

# Verificar que el backend responde
curl http://localhost:5000/api/health
```

## 🔧 Paso 12: Configurar Nginx (Opcional pero Recomendado)

### Crear configuración de Nginx

```bash
sudo nano /etc/nginx/sites-available/intranet206
```

Contenido:

```nginx
server {
    listen 80;
    server_name tu-dominio.com;

    # Servir el frontend React
    root /home/vanguard/intranet206/frontend/build;
    index index.html;

    location / {
        try_files $uri $uri/ /index.html;
    }

    # Proxy para el backend API
    location /api {
        proxy_pass http://localhost:5000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;
    }

    # Servir archivos estáticos del backend (uploads)
    location /uploads {
        alias /home/vanguard/intranet206/backend/uploads;
    }

    # Servir assets del backend
    location /assets {
        alias /home/vanguard/intranet206/backend/public/assets;
    }
}
```

### Habilitar el sitio

```bash
sudo ln -s /etc/nginx/sites-available/intranet206 /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

## 🔧 Paso 13: Configurar permisos

```bash
# Dar permisos a la carpeta de uploads
sudo chown -R vanguard:vanguard ~/intranet206/backend/uploads
sudo chmod -R 755 ~/intranet206/backend/uploads

# Dar permisos a la carpeta build
sudo chown -R vanguard:vanguard ~/intranet206/frontend/build
sudo chmod -R 755 ~/intranet206/frontend/build
```

## 📝 Comandos Útiles

### PM2

```bash
pm2 status              # Ver estado
pm2 logs                # Ver logs
pm2 restart all         # Reiniciar todo
pm2 stop all            # Detener todo
pm2 delete all          # Eliminar procesos
pm2 monit               # Monitor en tiempo real
```

### Actualizar código

```bash
cd ~/intranet206
git pull origin main
cd frontend
npm run build
pm2 restart intranet206-backend
```

## 🐛 Solución de Problemas

### El backend no inicia

```bash
pm2 logs intranet206-backend
# Revisar errores en los logs
```

### Error de conexión a MySQL

```bash
# Verificar que MySQL está accesible
mysql -h localhost -u tu_usuario -p

# Verificar variables de entorno
cd ~/intranet206/backend
cat .env
```

### El frontend no carga

```bash
# Verificar que el build se creó correctamente
ls -la ~/intranet206/frontend/build

# Verificar permisos
sudo chown -R vanguard:vanguard ~/intranet206/frontend/build
```

## ✅ Checklist de Despliegue

- [ ] Repositorio clonado en `/home/vanguard/intranet206`
- [ ] Node.js instalado (versión 18+)
- [ ] Dependencias instaladas (backend y frontend)
- [ ] Variables de entorno configuradas (backend/.env y frontend/.env)
- [ ] Frontend compilado (`npm run build`)
- [ ] PM2 instalado y configurado
- [ ] Backend corriendo con PM2
- [ ] Nginx configurado (opcional)
- [ ] Permisos configurados correctamente
- [ ] Sistema accesible desde el navegador

## 📞 Notas Importantes

1. **Carpeta separada**: ✅ Correcto tener el sistema React en `intranet206` separado del sistema PHP
2. **Base de datos compartida**: El sistema React usa la misma base de datos MySQL que el sistema PHP
3. **Puertos**: El backend corre en el puerto 5000 (interno), Nginx lo expone en el puerto 80
4. **Archivos subidos**: Los archivos se guardan en `backend/uploads/` - asegúrate de que tenga permisos de escritura
5. **SSL/HTTPS**: Considera configurar SSL con Let's Encrypt para producción

