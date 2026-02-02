# 🚀 Guía de Deployment en Producción

## 📋 Pre-requisitos

1. **VPS con acceso SSH**
2. **Node.js instalado** (versión 18 o superior)
3. **MySQL accesible** (mismo servidor o remoto)
4. **Git instalado** en el VPS
5. **Nginx o Apache** configurado (opcional, para servir el frontend)

## 📁 Estructura Recomendada en el VPS

```
/home/usuario/
├── sistema-php/          # Sistema PHP existente (no tocar)
└── react-aula-virtual/   # Nuevo sistema React (nueva carpeta)
    ├── backend/
    ├── frontend/
    └── ...
```

## 🔧 Paso 1: Preparar el Repositorio en GitHub

### 1.1 Crear repositorio en GitHub
1. Ve a GitHub y crea un nuevo repositorio (público o privado)
2. Copia la URL del repositorio (ej: `https://github.com/tu-usuario/react-aula-virtual.git`)

### 1.2 Subir código a GitHub

```bash
# En tu máquina local (desde la carpeta del proyecto)
git remote add origin https://github.com/tu-usuario/react-aula-virtual.git
git branch -M main
git push -u origin main
```

## 🔧 Paso 2: Configurar el VPS

### 2.1 Conectarse al VPS

```bash
ssh usuario@tu-vps-ip
```

### 2.2 Instalar Node.js (si no está instalado)

```bash
# Para Ubuntu/Debian
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# Verificar instalación
node --version
npm --version
```

### 2.3 Clonar el repositorio

```bash
cd /home/usuario
git clone https://github.com/tu-usuario/react-aula-virtual.git
cd react-aula-virtual
```

### 2.4 Instalar dependencias

```bash
# Instalar todas las dependencias
npm run install:all
```

## 🔧 Paso 3: Configurar Variables de Entorno

### 3.1 Crear archivo .env en el backend

```bash
cd backend
nano .env
```

### 3.2 Contenido del archivo .env

```env
# Entorno
NODE_ENV=production
PORT=5000

# MySQL - Producción
MYSQL_HOST=89.117.52.9
MYSQL_PORT=3306
MYSQL_USER=tu_usuario_mysql
MYSQL_PASSWORD=tu_password_mysql
MYSQL_DATABASE=tu_base_datos

# JWT
JWT_SECRET=tu_secret_key_muy_segura_aqui

# CORS - Agregar tu dominio
ALLOWED_ORIGINS=http://intranet.vanguardschools.com,https://intranet.vanguardschools.com

# Email (si usas notificaciones)
EMAIL_HOST=smtp.gmail.com
EMAIL_PORT=587
EMAIL_USER=tu_email@gmail.com
EMAIL_PASSWORD=tu_password_email
```

### 3.3 Crear archivo .env en el frontend (opcional)

```bash
cd ../frontend
nano .env
```

```env
REACT_APP_API_URL=http://intranet.vanguardschools.com/api
```

## 🔧 Paso 4: Compilar el Frontend

```bash
cd frontend
npm run build
```

Esto creará una carpeta `build/` con los archivos estáticos.

## 🔧 Paso 5: Configurar Nginx (Recomendado)

### 5.1 Crear configuración de Nginx

```bash
sudo nano /etc/nginx/sites-available/react-aula-virtual
```

### 5.2 Configuración para servir el frontend

```nginx
server {
    listen 80;
    server_name intranet.vanguardschools.com;

    # Servir el frontend React
    root /home/usuario/react-aula-virtual/frontend/build;
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
        alias /home/usuario/react-aula-virtual/backend/uploads;
    }

    # Servir assets del backend
    location /assets {
        alias /home/usuario/react-aula-virtual/backend/public/assets;
    }
}
```

### 5.3 Habilitar el sitio

```bash
sudo ln -s /etc/nginx/sites-available/react-aula-virtual /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

## 🔧 Paso 6: Configurar PM2 (Gestor de Procesos)

### 6.1 Instalar PM2

```bash
sudo npm install -g pm2
```

### 6.2 Crear archivo de configuración PM2

```bash
cd /home/usuario/react-aula-virtual
nano ecosystem.config.js
```

### 6.3 Contenido de ecosystem.config.js

```javascript
module.exports = {
  apps: [{
    name: 'react-aula-virtual-backend',
    script: './backend/server.js',
    cwd: '/home/usuario/react-aula-virtual',
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

### 6.4 Iniciar la aplicación con PM2

```bash
pm2 start ecosystem.config.js
pm2 save
pm2 startup
```

## 🔧 Paso 7: Verificar que Todo Funciona

### 7.1 Verificar que el backend está corriendo

```bash
pm2 status
pm2 logs react-aula-virtual-backend
```

### 7.2 Verificar que Nginx está sirviendo el frontend

```bash
curl http://localhost
```

### 7.3 Verificar que la API responde

```bash
curl http://localhost/api/health
```

## 🔧 Paso 8: Actualizar el Código (Futuras Actualizaciones)

```bash
cd /home/usuario/react-aula-virtual
git pull origin main
cd frontend
npm run build
pm2 restart react-aula-virtual-backend
```

## 🔒 Seguridad Adicional

### Firewall

```bash
# Permitir puertos necesarios
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 22/tcp
sudo ufw enable
```

### SSL/HTTPS (Recomendado)

Usa Let's Encrypt con Certbot:

```bash
sudo apt-get install certbot python3-certbot-nginx
sudo certbot --nginx -d intranet.vanguardschools.com
```

## 📝 Notas Importantes

1. **Carpeta separada**: ✅ Correcto tener el sistema React en una carpeta distinta al PHP
2. **Base de datos compartida**: El sistema React usa la misma base de datos MySQL que el sistema PHP
3. **Puertos**: El backend corre en el puerto 5000 (interno), Nginx lo expone en el puerto 80
4. **Archivos subidos**: Los archivos se guardan en `backend/uploads/` - asegúrate de que tenga permisos de escritura

## 🐛 Solución de Problemas

### El backend no inicia
```bash
pm2 logs react-aula-virtual-backend
# Revisar errores en los logs
```

### El frontend no carga
```bash
# Verificar que el build se creó correctamente
ls -la frontend/build
# Verificar permisos
sudo chown -R www-data:www-data frontend/build
```

### Error de conexión a MySQL
```bash
# Verificar que MySQL está accesible
mysql -h 89.117.52.9 -u tu_usuario -p
# Verificar variables de entorno
cd backend
cat .env
```

## 📞 Comandos Útiles PM2

```bash
pm2 status              # Ver estado
pm2 logs                # Ver logs
pm2 restart all         # Reiniciar todo
pm2 stop all            # Detener todo
pm2 delete all          # Eliminar procesos
pm2 monit               # Monitor en tiempo real
```

