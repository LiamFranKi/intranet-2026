# 🚀 GUÍA DE INICIO - IMPLEMENTACIÓN REACT AULA VIRTUAL

## 📋 PASO 1: VERIFICAR PREREQUISITOS

### Software Necesario:

- [ ] **Node.js** (v18 o superior)
  - Descargar: https://nodejs.org/
  - Verificar: `node --version`
  - Verificar: `npm --version`

- [ ] **PostgreSQL** (v14 o superior)
  - Descargar: https://www.postgresql.org/download/
  - Verificar: `psql --version`

- [ ] **Git** (opcional, para control de versiones)
  - Descargar: https://git-scm.com/
  - Verificar: `git --version`

- [ ] **Editor de código** (VS Code recomendado)
  - Descargar: https://code.visualstudio.com/

- [ ] **Acceso a MySQL remoto del VPS**
  - IP o dominio del servidor
  - Credenciales de usuario de solo lectura
  - Puerto MySQL (generalmente 3306)

---

## 📦 PASO 2: CREAR ESTRUCTURA DEL PROYECTO

### 2.1 Crear Carpetas

```bash
# En la carpeta react-aula-virtual
mkdir backend frontend database
mkdir backend/routes backend/models backend/utils backend/middleware backend/controllers
mkdir frontend/src frontend/public
mkdir frontend/src/pages frontend/src/components frontend/src/services frontend/src/utils frontend/src/context
mkdir database/migrations database/seeds
```

### 2.2 Inicializar Backend

```bash
cd backend
npm init -y
```

**Editar `backend/package.json`:**

```json
{
  "name": "react-aula-virtual-backend",
  "version": "1.0.0",
  "description": "Backend para sistema de aula virtual",
  "main": "server.js",
  "scripts": {
    "start": "node server.js",
    "dev": "nodemon server.js",
    "test": "echo \"Error: no test specified\" && exit 1"
  },
  "keywords": ["aula-virtual", "react", "node"],
  "author": "",
  "license": "ISC"
}
```

### 2.3 Instalar Dependencias del Backend

```bash
# Dependencias principales
npm install express mysql2 pg jsonwebtoken bcryptjs dotenv cors helmet express-rate-limit

# Dependencias de desarrollo
npm install -D nodemon
```

### 2.4 Inicializar Frontend

```bash
cd ../frontend
npx create-react-app . --template minimal
```

**Instalar dependencias adicionales:**

```bash
npm install react-router-dom axios sweetalert2
```

---

## ⚙️ PASO 3: CONFIGURAR VARIABLES DE ENTORNO

### 3.1 Backend - `.env`

**Crear archivo: `backend/.env`**

```env
# MySQL Remoto (VPS) - SOLO LECTURA
MYSQL_HOST=tu_vps_ip_o_dominio
MYSQL_PORT=3306
MYSQL_USER=react_readonly
MYSQL_PASSWORD=tu_password_segura
MYSQL_DATABASE=vanguard_intranet

# PostgreSQL (Local o remoto)
POSTGRES_HOST=localhost
POSTGRES_PORT=5432
POSTGRES_USER=postgres
POSTGRES_PASSWORD=tu_password_postgres
POSTGRES_DATABASE=aula_virtual

# PHP API (Para exportar notas)
PHP_API_URL=http://localhost/php-api
PHP_API_TOKEN=token_secreto

# JWT
JWT_SECRET=tu_secreto_jwt_super_seguro_2025
JWT_EXPIRES_IN=24h

# Server
PORT=5000
NODE_ENV=development

# CORS
FRONTEND_URL=http://localhost:3000
```

### 3.2 Frontend - `.env`

**Crear archivo: `frontend/.env`**

```env
REACT_APP_API_URL=http://localhost:5000/api
```

---

## 🗄️ PASO 4: CONFIGURAR POSTGRESQL

### 4.1 Crear Base de Datos

**Opción A: Usando pgAdmin**
1. Abrir pgAdmin
2. Click derecho en "Databases" → "Create" → "Database"
3. Nombre: `aula_virtual`
4. Owner: `postgres`
5. Click en "Save"

**Opción B: Usando línea de comandos**
```bash
psql -U postgres
CREATE DATABASE aula_virtual;
\q
```

### 4.2 Probar Conexión

**Crear archivo: `backend/test-postgres.js`**

```javascript
const { Pool } = require('pg');
require('dotenv').config();

const pool = new Pool({
  host: process.env.POSTGRES_HOST,
  port: process.env.POSTGRES_PORT,
  user: process.env.POSTGRES_USER,
  password: process.env.POSTGRES_PASSWORD,
  database: process.env.POSTGRES_DATABASE,
});

async function test() {
  try {
    const result = await pool.query('SELECT NOW()');
    console.log('✅ Conexión a PostgreSQL exitosa!');
    console.log('Fecha/Hora del servidor:', result.rows[0].now);
    await pool.end();
  } catch (error) {
    console.error('❌ Error:', error.message);
  }
  process.exit(0);
}

test();
```

**Ejecutar:**
```bash
cd backend
node test-postgres.js
```

---

## 🔌 PASO 5: CONFIGURAR CONEXIÓN MYSQL REMOTO

### 5.1 Crear Usuario en MySQL del VPS

**En el servidor MySQL (VPS), ejecutar:**

```sql
-- Crear usuario solo lectura
CREATE USER 'react_readonly'@'%' IDENTIFIED BY 'password_segura_solo_lectura';

-- Permisos SOLO LECTURA
GRANT SELECT ON vanguard_intranet.usuarios TO 'react_readonly'@'%';
GRANT SELECT ON vanguard_intranet.alumnos TO 'react_readonly'@'%';
GRANT SELECT ON vanguard_intranet.apoderados TO 'react_readonly'@'%';
GRANT SELECT ON vanguard_intranet.personal TO 'react_readonly'@'%';
GRANT SELECT ON vanguard_intranet.matriculas TO 'react_readonly'@'%';
GRANT SELECT ON vanguard_intranet.grupos TO 'react_readonly'@'%';
GRANT SELECT ON vanguard_intranet.cursos TO 'react_readonly'@'%';
GRANT SELECT ON vanguard_intranet.pagos TO 'react_readonly'@'%';
GRANT SELECT ON vanguard_intranet.colegios TO 'react_readonly'@'%';
GRANT SELECT ON vanguard_intranet.familias TO 'react_readonly'@'%';
GRANT SELECT ON vanguard_intranet.niveles TO 'react_readonly'@'%';
GRANT SELECT ON vanguard_intranet.sedes TO 'react_readonly'@'%';
GRANT SELECT ON vanguard_intranet.costos TO 'react_readonly'@'%';

FLUSH PRIVILEGES;
```

### 5.2 Probar Conexión

**Crear archivo: `backend/test-mysql.js`**

```javascript
const mysql = require('mysql2/promise');
require('dotenv').config();

async function test() {
  try {
    const connection = await mysql.createConnection({
      host: process.env.MYSQL_HOST,
      port: process.env.MYSQL_PORT,
      user: process.env.MYSQL_USER,
      password: process.env.MYSQL_PASSWORD,
      database: process.env.MYSQL_DATABASE,
    });

    const [rows] = await connection.execute('SELECT COUNT(*) as total FROM usuarios');
    console.log('✅ Conexión a MySQL remoto exitosa!');
    console.log('Total usuarios:', rows[0].total);

    // Obtener año activo
    const [colegios] = await connection.execute('SELECT anio_activo FROM colegios WHERE id = 1');
    if (colegios.length > 0) {
      console.log('✅ Año activo:', colegios[0].anio_activo);
    }

    await connection.end();
  } catch (error) {
    console.error('❌ Error:', error.message);
  }
  process.exit(0);
}

test();
```

**Ejecutar:**
```bash
cd backend
node test-mysql.js
```

---

## 📁 PASO 6: CREAR ARCHIVOS BASE

### 6.1 Backend - Estructura Base

**Crear: `backend/server.js`**

```javascript
require('dotenv').config();
const express = require('express');
const cors = require('cors');
const helmet = require('helmet');
const rateLimit = require('express-rate-limit');

const app = express();
const PORT = process.env.PORT || 5000;

// Rate limiting
const limiter = rateLimit({
  windowMs: 15 * 60 * 1000, // 15 minutos
  max: 100 // máximo 100 requests por ventana
});

// Middleware
app.use(helmet());
app.use(cors({
  origin: process.env.FRONTEND_URL || 'http://localhost:3000',
  credentials: true
}));
app.use(express.json());
app.use(limiter);

// Routes
app.get('/api/health', (req, res) => {
  res.json({ 
    status: 'OK', 
    message: 'Aula Virtual API',
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
  console.log(`✅ Servidor corriendo en puerto ${PORT}`);
  console.log(`🌐 URL: http://localhost:${PORT}`);
});
```

**Crear: `backend/utils/mysql.js`**

```javascript
const mysql = require('mysql2/promise');
require('dotenv').config();

// Pool de conexiones SOLO LECTURA
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

// Función helper para queries SOLO SELECT
async function query(sql, params = []) {
  try {
    // Validar que solo sean SELECT
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

// Obtener año activo del colegio
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

**Crear: `backend/utils/postgres.js`**

```javascript
const { Pool } = require('pg');
require('dotenv').config();

// Pool de conexiones PostgreSQL
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

// Función helper para queries
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

### 6.2 Frontend - Estructura Base

**Crear: `frontend/src/services/api.js`**

```javascript
import axios from 'axios';

const api = axios.create({
  baseURL: process.env.REACT_APP_API_URL || 'http://localhost:5000/api',
  headers: {
    'Content-Type': 'application/json',
  },
});

// Interceptor para agregar token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Interceptor para manejar errores
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default api;
```

---

## ✅ PASO 7: VERIFICAR INSTALACIÓN

### 7.1 Probar Backend

```bash
cd backend
npm run dev
```

**Debería mostrar:**
```
✅ Servidor corriendo en puerto 5000
🌐 URL: http://localhost:5000
```

**Probar en navegador:**
```
http://localhost:5000/api/health
```

**Debería responder:**
```json
{
  "status": "OK",
  "message": "Aula Virtual API",
  "timestamp": "2025-01-XX..."
}
```

### 7.2 Probar Frontend

```bash
cd frontend
npm start
```

**Debería abrir:**
```
http://localhost:3000
```

---

## 📝 PASO 8: CHECKLIST INICIAL

### Configuración:
- [ ] Node.js instalado
- [ ] PostgreSQL instalado y base de datos creada
- [ ] Usuario MySQL remoto creado en VPS
- [ ] Variables de entorno configuradas
- [ ] Estructura de carpetas creada
- [ ] Dependencias instaladas

### Pruebas:
- [ ] Conexión a PostgreSQL funciona
- [ ] Conexión a MySQL remoto funciona
- [ ] Backend inicia correctamente
- [ ] Frontend inicia correctamente
- [ ] Endpoint `/api/health` responde

---

## 🎯 PRÓXIMOS PASOS

Una vez completado este checklist:

1. **Implementar autenticación** (Fase 2)
   - Login con DNI
   - Validación SHA1
   - Verificación de deudas
   - Generación de token JWT
   - Filtro por año activo

2. **Implementar lectura de datos maestros** (Fase 3)
   - Leer usuarios
   - Leer alumnos
   - Leer grupos
   - Leer cursos
   - Todo filtrado por año activo

3. **Desarrollar módulos del aula virtual** (Fases 4-6)
   - Exámenes
   - Tareas
   - Temas

---

## 📚 DOCUMENTOS DE REFERENCIA

- `CONTEXTO_COMPLETO_SISTEMA.md` - Contexto completo del sistema
- `PLAN_COMPLETO_REACT_AULA_VIRTUAL.md` - Plan de implementación
- `REQUISITOS_USUARIOS_PERMISOS.md` - Requisitos de usuarios
- `CONFIGURACION_MYSQL_REMOTO.md` - Configuración MySQL remoto
- `FILTRADO_POR_ANIO_ACTIVO.md` - Filtrado por año activo (CRÍTICO)

---

## ⚠️ NOTAS IMPORTANTES

1. **Solo lectura de MySQL**: Nunca modificar datos en MySQL remoto
2. **Año activo**: Siempre filtrar por `colegios.anio_activo`
3. **Seguridad**: Usar variables de entorno, nunca hardcodear credenciales
4. **Validación**: Validar que queries MySQL sean solo SELECT

---

**¡Listo para empezar!** 🚀

