# ⚡ Pasos Rápidos para Desplegar (Ya tienes código clonado)

## ✅ Lo que ya tienes hecho:
- ✅ Código clonado en `~/intranet2026`
- ✅ Dependencias instaladas (probablemente)

## 🔧 Lo que necesitas hacer ahora:

### 1. Actualizar código desde GitHub

```bash
cd ~/intranet2026
git pull origin main
```

### 2. Verificar/Actualizar archivos .env

#### Backend:

```bash
cd ~/intranet2026/backend

# Si NO tienes .env, copia desde el ejemplo
cp env.production.example .env

# Editar .env
nano .env
```

**Verifica que tenga estos valores** (especialmente las URLs):

```env
NODE_ENV=production
PORT=5000

MYSQL_HOST=localhost
MYSQL_PORT=3306
MYSQL_USER=vanguard
MYSQL_PASSWORD=QI_jkA]RsHF_gUDN
MYSQL_DATABASE=vanguard_intranet

JWT_SECRET=VgS2026_React_Intranet_Production_Key_ChangeMe

# ⚠️ IMPORTANTE: Verifica que sea sistema.vanguardschools.edu.pe
ALLOWED_ORIGINS=https://sistema.vanguardschools.edu.pe
FRONTEND_URL=https://sistema.vanguardschools.edu.pe
```

Guarda: `Ctrl + O`, `Enter`, `Ctrl + X`

#### Frontend:

```bash
cd ~/intranet2026/frontend

# Si NO tienes .env, copia desde el ejemplo
cp env.production.example .env

# Editar .env
nano .env
```

**Verifica que tenga**:

```env
REACT_APP_API_URL=https://sistema.vanguardschools.edu.pe/api
```

Guarda: `Ctrl + O`, `Enter`, `Ctrl + X`

### 3. Recompilar Frontend

```bash
cd ~/intranet2026/frontend
npm run build
```

Esto puede tardar 2-5 minutos.

### 4. Configurar Nginx

```bash
# Crear archivo de configuración
sudo nano /etc/nginx/sites-available/sistema.vanguardschools.edu.pe
```

Pega esto:

```nginx
server {
    listen 80;
    server_name sistema.vanguardschools.edu.pe;

    # Frontend React
    root /home/vanguard/intranet2026/frontend/build;
    index index.html;

    location / {
        try_files $uri $uri/ /index.html;
    }

    # Backend API
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

    # Archivos subidos
    location /uploads {
        alias /home/vanguard/intranet2026/backend/uploads;
    }

    # Assets
    location /assets {
        alias /home/vanguard/intranet2026/backend/public/assets;
    }
}
```

Guarda: `Ctrl + O`, `Enter`, `Ctrl + X`

```bash
# Habilitar sitio
sudo ln -s /etc/nginx/sites-available/sistema.vanguardschools.edu.pe /etc/nginx/sites-enabled/

# Verificar configuración
sudo nginx -t

# Si todo está bien, recargar
sudo systemctl reload nginx
```

### 5. Configurar PM2 (si no lo tienes)

```bash
# Instalar PM2 (si no está instalado)
sudo npm install -g pm2

# Crear archivo de configuración
cd ~/intranet2026
nano ecosystem.config.js
```

Pega esto:

```javascript
module.exports = {
  apps: [{
    name: 'intranet2026-backend',
    script: './backend/server.js',
    cwd: '/home/vanguard/intranet2026',
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

Guarda: `Ctrl + O`, `Enter`, `Ctrl + X`

```bash
# Crear carpeta de logs
mkdir -p ~/intranet2026/logs

# Iniciar backend
pm2 start ecosystem.config.js
pm2 save
pm2 startup
```

El último comando te dará un comando para copiar y ejecutar. Hazlo.

### 6. Configurar Permisos

```bash
# Uploads
sudo chown -R vanguard:vanguard ~/intranet2026/backend/uploads
sudo chmod -R 755 ~/intranet2026/backend/uploads

# Build
sudo chown -R vanguard:vanguard ~/intranet2026/frontend/build
sudo chmod -R 755 ~/intranet2026/frontend/build
```

### 7. Verificar

```bash
# Ver estado de PM2
pm2 status

# Ver logs
pm2 logs intranet2026-backend

# Probar backend
curl http://localhost:5000/api/health
```

### 8. Probar en el navegador

**Primero verifica que el DNS esté configurado** (PASO 1 de la guía completa).

Luego abre:
```
http://sistema.vanguardschools.edu.pe
```

---

## 🔄 Si ya tienes PM2 corriendo

Si ya tenías el backend corriendo con PM2, solo necesitas:

```bash
# Reiniciar para cargar los nuevos cambios
pm2 restart intranet2026-backend

# Ver logs
pm2 logs intranet2026-backend
```

---

## ✅ Checklist

- [ ] Código actualizado (`git pull`)
- [ ] Archivos `.env` verificados/actualizados con `sistema.vanguardschools.edu.pe`
- [ ] Frontend recompilado (`npm run build`)
- [ ] Nginx configurado y recargado
- [ ] PM2 configurado y backend corriendo
- [ ] Permisos configurados
- [ ] DNS configurado (PASO 1 de la guía completa)
- [ ] Sitio accesible en `http://sistema.vanguardschools.edu.pe`

---

## 🐛 Problemas Comunes

### "502 Bad Gateway"
- Verifica que PM2 esté corriendo: `pm2 status`
- Verifica logs: `pm2 logs intranet2026-backend`

### "DNS no resuelve"
- Espera 15-30 minutos después de crear el registro DNS
- Verifica: `nslookup sistema.vanguardschools.edu.pe`

### "Página en blanco"
- Verifica que el build se creó: `ls -la ~/intranet2026/frontend/build`
- Abre la consola del navegador (F12) para ver errores

