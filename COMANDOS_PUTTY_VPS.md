# Comandos para PuTTY y Despliegue en VPS

## 1. CONECTARSE AL VPS CON PUTTY

### Configuración inicial en PuTTY:
- **Host Name (or IP address)**: `tu-ip-vps` o `tu-dominio.com`
- **Port**: `22`
- **Connection type**: `SSH`
- **Saved Sessions**: Guarda la sesión con un nombre (ej: "VPS Vanguard")

### Primera conexión:
1. Abre PuTTY
2. Ingresa la IP o dominio del VPS
3. Click en "Open"
4. Acepta la clave SSH (primera vez)
5. Ingresa usuario: `root` (o el usuario que tengas configurado)
6. Ingresa la contraseña

---

## 2. COMANDOS PARA DESCARGAR Y CONFIGURAR EN EL VPS

### Paso 1: Conectarse al VPS
```bash
ssh root@tu-ip-vps
# o
ssh root@tu-dominio.com
```

### Paso 2: Navegar al directorio del proyecto
```bash
# La ruta puede variar según tu configuración:
# Opción 1 (más común):
cd /home/vanguard/intranet2026

# Opción 2 (si está en /var/www):
cd /var/www/intranet-2026

# Verifica primero dónde está tu proyecto:
ls -la /home/vanguard/
# o
ls -la /var/www/
```

### Paso 3: Clonar/Actualizar el repositorio desde GitHub
```bash
# Si es la primera vez, clonar:
git clone https://github.com/LiamFranKi/intranet-2026.git

# Si ya existe, actualizar:
cd intranet-2026
# o si está en otra ubicación:
cd /home/vanguard/intranet2026
git pull origin main
```

### Paso 4: Configurar Backend
```bash
cd backend
npm install
# o si usas yarn:
yarn install
```

### Paso 5: Configurar variables de entorno del backend
```bash
nano .env
# o
vi .env
```

**Contenido del .env del backend:**
```env
NODE_ENV=production
PORT=5000
DB_HOST=localhost
DB_USER=tu_usuario_db
DB_PASSWORD=tu_password_db
DB_NAME=nombre_base_datos
JWT_SECRET=tu_jwt_secret_muy_seguro
```

### Paso 6: Configurar Frontend
```bash
cd ../frontend
npm install
# o si usas yarn:
yarn install
```

### Paso 7: Configurar variables de entorno del frontend
```bash
cp env.production.example .env.production
nano .env.production
```

**Contenido del .env.production:**
```env
REACT_APP_API_URL=https://sistema.vanguardschools.edu.pe/api
```

### Paso 8: Compilar el frontend
```bash
npm run build
# o
yarn build
```

### Paso 9: Reiniciar servicios (si usas PM2)
```bash
# Para el backend (nombre correcto del proceso):
pm2 restart intranet2026-backend
# o
pm2 restart all

# Ver estado:
pm2 status
pm2 logs
```

### Paso 10: Si usas Nginx, reiniciar
```bash
sudo nginx -t  # Verificar configuración
sudo systemctl restart nginx
# o
sudo service nginx restart
```

---

## 3. COMANDOS ÚTILES ADICIONALES

### Ver logs del backend (PM2):
```bash
pm2 logs intranet2026-backend
pm2 logs intranet2026-backend --lines 100  # últimas 100 líneas
```

### Ver logs de Nginx:
```bash
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/log/nginx/access.log
```

### Verificar que los puertos estén abiertos:
```bash
sudo netstat -tulpn | grep :5000  # Backend
sudo netstat -tulpn | grep :80    # HTTP
sudo netstat -tulpn | grep :443   # HTTPS
```

### Reiniciar todo el sistema:
```bash
cd /home/vanguard/intranet2026/backend
pm2 restart intranet2026-backend

cd /home/vanguard/intranet2026/frontend
npm run build

sudo systemctl restart nginx
```

### Verificar estado de servicios:
```bash
pm2 status
sudo systemctl status nginx
sudo systemctl status mysql  # o postgresql según uses
```

---

## 4. COMANDOS RÁPIDOS (COPIA Y PEGA)

### Actualización completa rápida:
```bash
cd /home/vanguard/intranet2026 && \
git pull origin main && \
cd backend && npm install && pm2 restart intranet2026-backend && \
cd ../frontend && npm install && npm run build && \
sudo systemctl restart nginx
```

### Solo actualizar código (sin reinstalar):
```bash
cd ~/intranet2026 && \
git pull origin main && \
cd frontend && npm run build && \
cd ../backend && pm2 restart intranet2026-backend && \
sudo systemctl reload nginx
```

**NOTA:** Si ya estás en `~/intranet2026/backend`, usa estos comandos:
```bash
cd ~/intranet2026
git pull origin main
cd frontend
npm run build
cd ../backend
pm2 restart intranet2026-backend
sudo systemctl reload nginx
```

---

## 5. TROUBLESHOOTING

### Si hay problemas con permisos:
```bash
sudo chown -R www-data:www-data /home/vanguard/intranet2026
sudo chmod -R 755 /home/vanguard/intranet2026
```

### Si el frontend no se actualiza:
```bash
cd /home/vanguard/intranet2026/frontend
rm -rf build
npm run build
sudo systemctl restart nginx
```

### Si el backend no inicia:
```bash
cd /home/vanguard/intranet2026
pm2 delete intranet2026-backend
pm2 start ecosystem.config.js
# o manualmente:
# cd backend
# pm2 start server.js --name intranet2026-backend
pm2 save
```

---

## 6. CONFIGURACIÓN NGINX (si necesitas actualizarla)

```bash
sudo nano /etc/nginx/sites-available/intranet-2026
```

**Ejemplo de configuración:**
```nginx
server {
    listen 80;
    server_name sistema.vanguardschools.edu.pe;

    # Redirigir a HTTPS (si tienes SSL)
    # return 301 https://$server_name$request_uri;

    # Frontend
    location / {
        root /home/vanguard/intranet2026/frontend/build;
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
}
```

Después de editar:
```bash
sudo nginx -t
sudo systemctl reload nginx
```

---

**NOTAS IMPORTANTES**: 
- Reemplaza `tu-ip-vps`, `tu-dominio.com`, y las credenciales de base de datos con tus valores reales.
- El nombre del proceso PM2 es: **`intranet2026-backend`** (NO "backend")
- La ruta del proyecto puede ser `/home/vanguard/intranet2026` o `/var/www/intranet-2026` según tu configuración
- Verifica primero la ruta correcta con: `pm2 list` o `ls -la /home/vanguard/`

