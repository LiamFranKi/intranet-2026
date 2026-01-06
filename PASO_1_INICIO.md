# 🚀 PASO 1: INICIO DEL PROYECTO - CONFIGURACIÓN INICIAL

## 📋 REQUISITOS COMPLETOS DEL PROYECTO

### Funcionalidades Principales:
- ✅ **Login único** para todos (alumno, docente, tutor, apoderado, administrador)
- ✅ **PWA** (Progressive Web App) - Instalable en Android e iOS
- ✅ **Diseño responsive** (celulares, tablets, computadoras)
- ✅ **Emails HTML** bonitos con iconos y URLs
- ✅ **Sistema de notificaciones** completo (push notifications)
- ✅ **Diseño moderno y atractivo**
- ✅ **Gamificación**: Grados → Mundos, Avatares
- ✅ **Gestión completa**: Tareas, Temas, Exámenes

---

## 📦 PASO 1.1: CREAR ESTRUCTURA DEL PROYECTO

### 1. Crear Carpetas

```bash
# En la carpeta react-aula-virtual
mkdir backend frontend database
mkdir backend/routes backend/models backend/utils backend/middleware backend/controllers backend/services
mkdir frontend/src frontend/public
mkdir frontend/src/pages frontend/src/components frontend/src/services frontend/src/utils frontend/src/context frontend/src/hooks
mkdir database/migrations database/seeds
```

### 2. Inicializar Backend

```bash
cd backend
npm init -y
```

### 3. Editar `backend/package.json`

```json
{
  "name": "react-aula-virtual-backend",
  "version": "1.0.0",
  "description": "Backend para sistema de aula virtual",
  "main": "server.js",
  "scripts": {
    "start": "node server.js",
    "dev": "node scripts/find-port.js && nodemon server.js",
    "kill": "node scripts/kill-ports.js",
    "test": "echo \"Error: no test specified\" && exit 1"
  },
  "keywords": ["aula-virtual", "react", "node", "pwa"],
  "author": "",
  "license": "ISC"
}
```

---

## 📦 PASO 1.2: INSTALAR DEPENDENCIAS

### Backend

```bash
cd backend

# Dependencias principales
npm install express mysql2 pg jsonwebtoken bcryptjs dotenv cors helmet express-rate-limit

# Para emails HTML
npm install nodemailer handlebars

# Para notificaciones push
npm install web-push

# Para subida de archivos
npm install multer

# Dependencias de desarrollo
npm install -D nodemon
```

### Frontend

```bash
cd ../frontend
npx create-react-app . --template minimal

# Dependencias principales
npm install react-router-dom axios sweetalert2

# Para PWA
npm install workbox-webpack-plugin

# Para notificaciones
npm install react-toastify

# Para diseño responsive
npm install @mui/material @emotion/react @emotion/styled
npm install @mui/icons-material

# Para avatares
npm install react-avatar
```

---

## 🔧 PASO 1.3: CREAR SCRIPTS DE GESTIÓN DE PUERTOS

### Script para encontrar puerto libre

**Crear: `backend/scripts/find-port.js`**

```javascript
const net = require('net');

function findFreePort(startPort = 5000) {
  return new Promise((resolve, reject) => {
    const server = net.createServer();
    
    server.listen(startPort, () => {
      const port = server.address().port;
      server.close(() => {
        resolve(port);
      });
    });
    
    server.on('error', (err) => {
      if (err.code === 'EADDRINUSE') {
        // Puerto ocupado, probar siguiente
        findFreePort(startPort + 1).then(resolve).catch(reject);
      } else {
        reject(err);
      }
    });
  });
}

// Ejecutar y guardar puerto en archivo
async function main() {
  try {
    const port = await findFreePort(5000);
    const fs = require('fs');
    fs.writeFileSync('.port', port.toString());
    console.log(`✅ Puerto libre encontrado: ${port}`);
    process.exit(0);
  } catch (error) {
    console.error('❌ Error:', error);
    process.exit(1);
  }
}

main();
```

### Script para cerrar puertos

**Crear: `backend/scripts/kill-ports.js`**

```javascript
const { exec } = require('child_process');
const os = require('os');

const ports = [5000, 3000, 5001, 5002]; // Puertos comunes

function killPort(port) {
  return new Promise((resolve, reject) => {
    const platform = os.platform();
    
    let command;
    if (platform === 'win32') {
      // Windows
      command = `netstat -ano | findstr :${port}`;
      exec(command, (error, stdout) => {
        if (stdout) {
          const lines = stdout.trim().split('\n');
          const pids = new Set();
          
          lines.forEach(line => {
            const parts = line.trim().split(/\s+/);
            const pid = parts[parts.length - 1];
            if (pid && !isNaN(pid)) {
              pids.add(pid);
            }
          });
          
          pids.forEach(pid => {
            exec(`taskkill /PID ${pid} /F`, (err) => {
              if (!err) {
                console.log(`✅ Puerto ${port} (PID ${pid}) cerrado`);
              }
            });
          });
        }
        resolve();
      });
    } else {
      // Linux/Mac
      command = `lsof -ti:${port} | xargs kill -9 2>/dev/null || true`;
      exec(command, (error) => {
        if (!error) {
          console.log(`✅ Puerto ${port} cerrado`);
        }
        resolve();
      });
    }
  });
}

async function main() {
  console.log('🔄 Cerrando puertos...\n');
  
  for (const port of ports) {
    await killPort(port);
  }
  
  console.log('\n✅ Proceso completado');
  process.exit(0);
}

main();
```

---

## ⚙️ PASO 1.4: CONFIGURAR VARIABLES DE ENTORNO

### Backend - `.env`

**Crear: `backend/.env`**

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
```

### Frontend - `.env`

**Crear: `frontend/.env`**

```env
REACT_APP_API_URL=http://localhost:5000/api
REACT_APP_VAPID_PUBLIC_KEY=tu_vapid_public_key
```

---

## 📁 PASO 1.5: CREAR ARCHIVOS BASE

### Backend - Server Principal

**Crear: `backend/server.js`**

```javascript
require('dotenv').config();
const express = require('express');
const cors = require('cors');
const helmet = require('helmet');
const rateLimit = require('express-rate-limit');
const fs = require('fs');
const path = require('path');

const app = express();

// Leer puerto del archivo o usar default
let PORT = process.env.PORT || 5000;
try {
  const portFile = path.join(__dirname, '.port');
  if (fs.existsSync(portFile)) {
    PORT = parseInt(fs.readFileSync(portFile, 'utf8'));
  }
} catch (error) {
  console.log('Usando puerto por defecto:', PORT);
}

// Rate limiting
const limiter = rateLimit({
  windowMs: 15 * 60 * 1000, // 15 minutos
  max: 100 // máximo 100 requests
});

// Middleware
app.use(helmet());
app.use(cors({
  origin: process.env.FRONTEND_URL || 'http://localhost:3000',
  credentials: true
}));
app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true, limit: '10mb' }));
app.use(limiter);

// Routes
app.get('/api/health', (req, res) => {
  res.json({ 
    status: 'OK', 
    message: 'Aula Virtual API',
    port: PORT,
    timestamp: new Date().toISOString()
  });
});

// 404
app.use((req, res) => {
  res.status(404).json({ error: 'Ruta no encontrada' });
});

// Error handler
app.use((err, req, res, next) => {
  console.error('Error:', err);
  res.status(500).json({ error: 'Error interno del servidor' });
});

// Start server
app.listen(PORT, () => {
  console.log(`\n✅ Servidor corriendo en puerto ${PORT}`);
  console.log(`🌐 URL: http://localhost:${PORT}`);
  console.log(`📡 Health: http://localhost:${PORT}/api/health\n`);
});
```

### Backend - Utilidades MySQL

**Crear: `backend/utils/mysql.js`**

```javascript
const mysql = require('mysql2/promise');
require('dotenv').config();

const mysqlReadPool = mysql.createPool({
  host: process.env.MYSQL_HOST,
  port: process.env.MYSQL_PORT || 3306,
  user: process.env.MYSQL_USER,
  password: process.env.MYSQL_PASSWORD,
  database: process.env.MYSQL_DATABASE,
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0,
  multipleStatements: false
});

async function query(sql, params = []) {
  try {
    const sqlUpper = sql.trim().toUpperCase();
    if (!sqlUpper.startsWith('SELECT')) {
      throw new Error('Solo se permiten consultas SELECT. Modificación de datos no permitida.');
    }
    const [rows] = await mysqlReadPool.execute(sql, params);
    return rows;
  } catch (error) {
    console.error('MySQL Error:', error);
    throw error;
  }
}

async function getAnioActivo(colegioId) {
  try {
    const [rows] = await mysqlReadPool.execute(
      'SELECT anio_activo FROM colegios WHERE id = ?',
      [colegioId]
    );
    return rows.length > 0 ? rows[0].anio_activo : null;
  } catch (error) {
    console.error('Error obteniendo año activo:', error);
    throw error;
  }
}

module.exports = { query, mysqlReadPool, getAnioActivo };
```

### Backend - Utilidades PostgreSQL

**Crear: `backend/utils/postgres.js`**

```javascript
const { Pool } = require('pg');
require('dotenv').config();

const pool = new Pool({
  host: process.env.POSTGRES_HOST,
  port: process.env.POSTGRES_PORT,
  user: process.env.POSTGRES_USER,
  password: process.env.POSTGRES_PASSWORD,
  database: process.env.POSTGRES_DATABASE,
  max: 20,
  idleTimeoutMillis: 30000,
  connectionTimeoutMillis: 2000,
});

async function query(text, params) {
  try {
    const result = await pool.query(text, params);
    return result;
  } catch (error) {
    console.error('PostgreSQL Error:', error);
    throw error;
  }
}

module.exports = { query, pool };
```

---

## ✅ PASO 1.6: VERIFICAR INSTALACIÓN

### 1. Cerrar puertos anteriores

```bash
cd backend
npm run kill
```

### 2. Iniciar backend

```bash
npm run dev
```

**Debería mostrar:**
```
✅ Puerto libre encontrado: 5000
✅ Servidor corriendo en puerto 5000
🌐 URL: http://localhost:5000
```

### 3. Probar en navegador

```
http://localhost:5000/api/health
```

**Debería responder:**
```json
{
  "status": "OK",
  "message": "Aula Virtual API",
  "port": 5000,
  "timestamp": "2025-01-XX..."
}
```

---

## 📝 CHECKLIST PASO 1

- [ ] Estructura de carpetas creada
- [ ] Backend inicializado (`npm init`)
- [ ] Frontend inicializado (`create-react-app`)
- [ ] Dependencias instaladas (backend y frontend)
- [ ] Scripts `kill` y `dev` creados
- [ ] Variables de entorno configuradas
- [ ] Archivos base creados (server.js, utils)
- [ ] Backend inicia correctamente
- [ ] Endpoint `/api/health` responde

---

## 🎯 PRÓXIMO PASO

Una vez completado el Paso 1, continuar con:

**PASO 2: Configurar MySQL remoto y PostgreSQL**
- Crear usuario MySQL de solo lectura en Hostinger
- Configurar PostgreSQL
- Probar conexiones

---

**¿Listo para continuar con el Paso 2?** 🚀

