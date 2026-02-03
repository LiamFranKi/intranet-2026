# 🚀 Guía Completa: Crear Subdominio y Desplegar Sistema React

## 📋 Resumen

- **Sistema PHP (existente)**: `https://nuevo.vanguardschools.edu.pe` ✅ (No se toca)
- **Sistema React (nuevo)**: `https://sistema.vanguardschools.edu.pe` 🆕
- **Mismo VPS**: 89.117.52.9
- **Misma Base de Datos**: vanguard_intranet

---

## 🔧 PASO 1: Crear el Subdominio en DNS

### Opción A: Panel de Control del Proveedor de Dominio

1. **Accede al panel de control** donde gestionas el dominio `vanguardschools.edu.pe`
   - Puede ser: cPanel, Plesk, Cloudflare, Namecheap, GoDaddy, etc.

2. **Busca la sección de DNS** o "Zone Records" o "DNS Management"

3. **Agrega un nuevo registro DNS:**
   - **Tipo**: `A` (Address)
   - **Nombre/Host**: `sistema` (solo la palabra, sin el dominio completo)
   - **Valor/IP**: `89.117.52.9` (IP del VPS)
   - **TTL**: `3600` (o el valor por defecto)

4. **Guarda los cambios**

5. **Espera la propagación DNS** (puede tardar de 5 minutos a 24 horas, normalmente 15-30 minutos)

### Opción B: Cloudflare (si usas Cloudflare)

1. Ve a tu cuenta de Cloudflare
2. Selecciona el dominio `vanguardschools.edu.pe`
3. Ve a la pestaña **DNS**
4. Haz clic en **"Add record"**
5. Completa:
   - **Type**: A
   - **Name**: sistema
   - **IPv4 address**: 89.117.52.9
   - **Proxy status**: Puedes dejarlo en "DNS only" (nube gris) o "Proxied" (nube naranja)
   - **TTL**: Auto
6. Guarda

### Verificar que el DNS funciona

Después de unos minutos, verifica en tu terminal:

```bash
# Windows (PowerShell o CMD)
nslookup sistema.vanguardschools.edu.pe

# Linux/Mac
dig sistema.vanguardschools.edu.pe
# o
nslookup sistema.vanguardschools.edu.pe
```

Debería mostrar la IP `89.117.52.9`

---

## 🔧 PASO 2: Conectarse al VPS

```bash
ssh vanguard@89.117.52.9
# Contraseña: CtxADB8q0SaVYox
```

---

## 🔧 PASO 3: Ir a la carpeta del proyecto y actualizar código

```bash
cd ~/intranet2026
```

**Si ya tienes el código clonado** (tu caso):

```bash
# Actualizar código desde GitHub
git pull origin main
```

**Si la carpeta no existe o está vacía** (solo si es primera vez):

```bash
cd ~
mkdir -p intranet2026
cd intranet2026
git clone https://github.com/LiamFranKi/intranet-2026.git .
```

---

## 🔧 PASO 4: Instalar/Actualizar dependencias

**Si ya instalaste las dependencias antes**, solo actualiza si hay cambios:

```bash
# Backend
cd ~/intranet2026/backend
npm install --production

# Frontend
cd ~/intranet2026/frontend
npm install
```

**Nota**: Si ya las instalaste antes y no hubo cambios en `package.json`, puedes saltar este paso.

---

## 🔧 PASO 5: Configurar variables de entorno

**⚠️ IMPORTANTE**: Si ya tienes archivos `.env`, verifica que tengan los valores correctos para `sistema.vanguardschools.edu.pe`

### Backend

```bash
cd ~/intranet2026/backend
nano .env
```

**Si NO tienes archivo `.env`**, crea uno nuevo copiando desde el ejemplo:

```bash
cp env.production.example .env
nano .env
```

Copia y pega esto (verifica que los valores sean correctos):

```env
NODE_ENV=production
PORT=5000

# MySQL - Producción
MYSQL_HOST=localhost
MYSQL_PORT=3306
MYSQL_USER=vanguard
MYSQL_PASSWORD=QI_jkA]RsHF_gUDN
MYSQL_DATABASE=vanguard_intranet

# JWT Secret
JWT_SECRET=VgS2026_React_Intranet_Production_Key_ChangeMe

# CORS - Subdominio para el sistema React
ALLOWED_ORIGINS=https://sistema.vanguardschools.edu.pe

# Frontend URL - Subdominio para el sistema React
FRONTEND_URL=https://sistema.vanguardschools.edu.pe
```

Guarda con `Ctrl + O`, luego `Enter`, luego `Ctrl + X`

### Frontend

```bash
cd ~/intranet2026/frontend
nano .env
```

**Si NO tienes archivo `.env`**, crea uno nuevo copiando desde el ejemplo:

```bash
cp env.production.example .env
nano .env
```

Copia y pega (o verifica que tenga):

```env
REACT_APP_API_URL=https://sistema.vanguardschools.edu.pe/api
```

Guarda con `Ctrl + O`, luego `Enter`, luego `Ctrl + X`

---

## 🔧 PASO 6: Compilar el Frontend

```bash
cd ~/intranet2026/frontend
npm run build
```

Esto puede tardar unos minutos. Al finalizar, deberías ver:
```
Compiled successfully!
```

---

## 🔧 PASO 7: Configurar Nginx

### Crear archivo de configuración

```bash
sudo nano /etc/nginx/sites-available/sistema.vanguardschools.edu.pe
```

Copia y pega esta configuración:

```nginx
server {
    listen 80;
    server_name sistema.vanguardschools.edu.pe;

    # Redirigir HTTP a HTTPS (después de configurar SSL)
    # Por ahora, comentamos esto y lo activamos después del SSL
    # return 301 https://$server_name$request_uri;

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

    # Assets del backend
    location /assets {
        alias /home/vanguard/intranet2026/backend/public/assets;
    }
}
```

Guarda con `Ctrl + O`, luego `Enter`, luego `Ctrl + X`

### Habilitar el sitio

```bash
# Crear enlace simbólico
sudo ln -s /etc/nginx/sites-available/sistema.vanguardschools.edu.pe /etc/nginx/sites-enabled/

# Verificar que la configuración es correcta
sudo nginx -t

# Si todo está bien, recargar Nginx
sudo systemctl reload nginx
```

---

## 🔧 PASO 8: Instalar y Configurar PM2

### Instalar PM2 (si no está instalado)

```bash
sudo npm install -g pm2
```

### Crear archivo de configuración PM2

```bash
cd ~/intranet2026
nano ecosystem.config.js
```

Copia y pega:

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

Guarda con `Ctrl + O`, luego `Enter`, luego `Ctrl + X`

### Crear carpeta de logs

```bash
mkdir -p ~/intranet2026/logs
```

### Iniciar el backend con PM2

```bash
cd ~/intranet2026
pm2 start ecosystem.config.js
pm2 save
pm2 startup
```

El último comando (`pm2 startup`) te dará un comando para copiar y pegar. Ejecútalo para que PM2 inicie automáticamente al reiniciar el servidor.

---

## 🔧 PASO 9: Configurar Permisos

```bash
# Carpeta de uploads
sudo chown -R vanguard:vanguard ~/intranet2026/backend/uploads
sudo chmod -R 755 ~/intranet2026/backend/uploads

# Carpeta build del frontend
sudo chown -R vanguard:vanguard ~/intranet2026/frontend/build
sudo chmod -R 755 ~/intranet2026/frontend/build
```

---

## 🔧 PASO 10: Verificar que todo funciona

### Verificar PM2

```bash
pm2 status
```

Deberías ver `intranet2026-backend` con estado `online`

### Ver logs

```bash
pm2 logs intranet2026-backend
```

Presiona `Ctrl + C` para salir de los logs

### Verificar que el backend responde

```bash
curl http://localhost:5000/api/health
```

O desde tu navegador local, prueba:
```
http://89.117.52.9:5000/api/health
```

### Verificar Nginx

```bash
sudo systemctl status nginx
```

### Probar el subdominio

Abre en tu navegador:
```
http://sistema.vanguardschools.edu.pe
```

**Nota**: Si el DNS aún no se ha propagado, puedes probar directamente con la IP:
```
http://89.117.52.9
```
(Pero necesitarás configurar Nginx para aceptar requests sin hostname, o esperar a que el DNS se propague)

---

## 🔧 PASO 11: Configurar SSL (HTTPS) - OPCIONAL pero RECOMENDADO

Una vez que el DNS esté propagado y el sitio funcione en HTTP:

### Instalar Certbot para Apache

```bash
sudo apt-get update
sudo apt-get install certbot python3-certbot-apache -y
```

### Obtener certificado SSL

```bash
sudo certbot --apache -d sistema.vanguardschools.edu.pe
```

Sigue las instrucciones:
1. Ingresa tu email
2. Acepta los términos
3. Elige si quieres redirigir HTTP a HTTPS (recomendado: Sí - opción 2)

Certbot modificará automáticamente tu configuración de Apache para:
- Agregar el certificado SSL
- Configurar el VirtualHost para HTTPS (puerto 443)
- Redirigir HTTP a HTTPS (si elegiste la opción)

### Verificar renovación automática

```bash
sudo certbot renew --dry-run
```

### Verificar que SSL funciona

Después de configurar SSL, prueba en tu navegador:
```
https://sistema.vanguardschools.edu.pe
```

Deberías ver el candado verde 🔒 en la barra de direcciones.

---

## ✅ Checklist Final

- [ ] DNS configurado y propagado
- [ ] Código clonado/actualizado en el VPS
- [ ] Dependencias instaladas
- [ ] Archivos `.env` creados con valores correctos
- [ ] Frontend compilado (`npm run build`)
- [ ] Nginx configurado y recargado
- [ ] PM2 instalado y backend corriendo
- [ ] Permisos configurados
- [ ] Sitio accesible en `http://sistema.vanguardschools.edu.pe`
- [ ] SSL configurado (opcional pero recomendado)

---

## 🐛 Solución de Problemas

### El DNS no resuelve

```bash
# Verificar desde el servidor
nslookup sistema.vanguardschools.edu.pe

# Si no funciona, espera más tiempo (hasta 24 horas)
# O verifica que el registro DNS esté correcto en el panel
```

### El backend no inicia

```bash
# Ver logs
pm2 logs intranet2026-backend

# Verificar que el puerto 5000 no esté en uso
sudo netstat -tulpn | grep 5000

# Reiniciar PM2
pm2 restart intranet2026-backend
```

### Error 502 Bad Gateway

```bash
# Verificar que el backend esté corriendo
pm2 status

# Verificar que Nginx pueda conectarse al backend
curl http://localhost:5000/api/health

# Ver logs de Nginx
sudo tail -f /var/log/nginx/error.log
```

### El frontend muestra página en blanco

```bash
# Verificar que el build se creó
ls -la ~/intranet2026/frontend/build

# Verificar permisos
ls -la ~/intranet2026/frontend/build/index.html

# Verificar consola del navegador (F12) para errores
```

---

## 📝 Comandos Útiles

### Actualizar código

```bash
cd ~/intranet2026
git pull origin main
cd frontend
npm run build
pm2 restart intranet2026-backend
```

### Ver estado de PM2

```bash
pm2 status
pm2 logs
pm2 monit  # Monitor en tiempo real
```

### Reiniciar servicios

```bash
pm2 restart intranet2026-backend
sudo systemctl reload nginx
```

---

## 🎉 ¡Listo!

Una vez completados todos los pasos, tu sistema React estará disponible en:
- **HTTP**: `http://sistema.vanguardschools.edu.pe`
- **HTTPS**: `https://sistema.vanguardschools.edu.pe` (después de configurar SSL)

Y el sistema PHP seguirá funcionando normalmente en:
- `https://nuevo.vanguardschools.edu.pe`

¡Ambos sistemas funcionando en paralelo sin conflictos! 🚀

