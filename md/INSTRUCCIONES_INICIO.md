# 🚀 INSTRUCCIONES PARA EMPEZAR - PASO A PASO

## 📋 PASO 1: PREPARAR CARPETA Y CONTEXTO

### 1.1 Crear Nueva Carpeta

```bash
# En la raíz de tu proyecto
mkdir react-aula-virtual
cd react-aula-virtual
```

### 1.2 Copiar Contexto

**Archivo a copiar**: `CONTEXTO_COMPLETO_SISTEMA.md`

Este archivo contiene TODO el contexto que necesitas:
- ✅ Análisis completo del sistema PHP
- ✅ Estructura de base de datos MySQL
- ✅ Lógica de negocio identificada
- ✅ Arquitectura de integración
- ✅ Plan de implementación

**Copia este archivo a tu nueva carpeta:**
```bash
# Desde la raíz del proyecto actual
copy CONTEXTO_COMPLETO_SISTEMA.md react-aula-virtual\
```

---

## 🗄️ PASO 2: CONFIGURAR MYSQL EN XAMPP

### 2.1 Ubicación del Archivo SQL

**Archivo**: `sistema-anterior/base de datos/vanguard_intranet_2.sql`

Este archivo contiene la estructura completa de la base de datos (sin datos).

### 2.2 Importar en XAMPP (Local)

#### Opción A: Usando phpMyAdmin (Recomendado)

1. **Iniciar XAMPP**
   - Abrir XAMPP Control Panel
   - Iniciar Apache
   - Iniciar MySQL

2. **Abrir phpMyAdmin**
   - Ir a: `http://localhost/phpmyadmin`
   - O hacer click en "Admin" junto a MySQL en XAMPP

3. **Crear Base de Datos**
   - Click en "Nueva" (New) en el menú lateral
   - Nombre: `vanguard_intranet`
   - Cotejamiento: `utf8mb3_general_ci` (o `utf8mb4_unicode_ci`)
   - Click en "Crear"

4. **Importar Archivo SQL**
   - Seleccionar la base de datos `vanguard_intranet`
   - Click en la pestaña "Importar" (Import)
   - Click en "Seleccionar archivo"
   - Buscar: `sistema-anterior/base de datos/vanguard_intranet_2.sql`
   - Click en "Continuar" (Go)
   - Esperar a que termine (puede tardar unos minutos)

5. **Verificar Importación**
   - Deberías ver todas las tablas en el menú lateral
   - Verificar que existan: `usuarios`, `alumnos`, `matriculas`, `grupos`, `cursos`, `pagos`, etc.

#### Opción B: Usando Línea de Comandos

```bash
# Abrir terminal en la carpeta del archivo SQL
cd "sistema-anterior/base de datos"

# Importar usando MySQL
mysql -u root -p vanguard_intranet < vanguard_intranet_2.sql
```

**Nota**: Si no tienes contraseña para root, usa:
```bash
mysql -u root vanguard_intranet < vanguard_intranet_2.sql
```

### 2.3 Crear Usuario de Solo Lectura

**IMPORTANTE**: Crear un usuario MySQL con permisos de solo lectura para React.

1. **Abrir phpMyAdmin**
   - Ir a: `http://localhost/phpmyadmin`

2. **Ir a la pestaña "SQL"**
   - Click en "SQL" en el menú superior

3. **Ejecutar este script:**

```sql
-- Crear usuario solo lectura
CREATE USER 'react_readonly'@'localhost' IDENTIFIED BY 'react_readonly_2025';

-- Permisos solo lectura en tablas necesarias
GRANT SELECT ON vanguard_intranet.usuarios TO 'react_readonly'@'localhost';
GRANT SELECT ON vanguard_intranet.alumnos TO 'react_readonly'@'localhost';
GRANT SELECT ON vanguard_intranet.apoderados TO 'react_readonly'@'localhost';
GRANT SELECT ON vanguard_intranet.personal TO 'react_readonly'@'localhost';
GRANT SELECT ON vanguard_intranet.matriculas TO 'react_readonly'@'localhost';
GRANT SELECT ON vanguard_intranet.grupos TO 'react_readonly'@'localhost';
GRANT SELECT ON vanguard_intranet.cursos TO 'react_readonly'@'localhost';
GRANT SELECT ON vanguard_intranet.pagos TO 'react_readonly'@'localhost';
GRANT SELECT ON vanguard_intranet.colegios TO 'react_readonly'@'localhost';
GRANT SELECT ON vanguard_intranet.familias TO 'react_readonly'@'localhost';
GRANT SELECT ON vanguard_intranet.niveles TO 'react_readonly'@'localhost';
GRANT SELECT ON vanguard_intranet.sedes TO 'react_readonly'@'localhost';
GRANT SELECT ON vanguard_intranet.costos TO 'react_readonly'@'localhost';

-- Aplicar cambios
FLUSH PRIVILEGES;
```

4. **Verificar Usuario**
   - Ir a pestaña "Cuentas de usuario" (User accounts)
   - Deberías ver `react_readonly@localhost`

### 2.4 Verificar Conexión

**Probar conexión desde Node.js:**
```javascript
// test-mysql.js
const mysql = require('mysql2/promise');

async function test() {
  try {
    const connection = await mysql.createConnection({
      host: 'localhost',
      user: 'react_readonly',
      password: 'react_readonly_2025',
      database: 'vanguard_intranet'
    });
    
    const [rows] = await connection.execute('SELECT COUNT(*) as total FROM usuarios');
    console.log('✅ Conexión exitosa!');
    console.log('Total usuarios:', rows[0].total);
    
    await connection.end();
  } catch (error) {
    console.error('❌ Error:', error.message);
  }
}

test();
```

---

## 🐘 PASO 3: CONFIGURAR POSTGRESQL

### 3.1 Instalar PostgreSQL

Si no lo tienes instalado:
- Descargar desde: https://www.postgresql.org/download/
- Instalar con configuración por defecto
- Recordar la contraseña del usuario `postgres`

### 3.2 Crear Base de Datos

**Opción A: Usando pgAdmin**
1. Abrir pgAdmin
2. Click derecho en "Databases" → "Create" → "Database"
3. Nombre: `aula_virtual`
4. Owner: `postgres`
5. Click en "Save"

**Opción B: Usando Línea de Comandos**
```bash
# Abrir psql
psql -U postgres

# Crear base de datos
CREATE DATABASE aula_virtual;

# Salir
\q
```

### 3.3 Crear Esquema Inicial

El esquema se creará automáticamente cuando implementemos el módulo de exámenes.

---

## 📦 PASO 4: CREAR ESTRUCTURA DEL PROYECTO

### 4.1 Inicializar Proyecto

```bash
# En la carpeta react-aula-virtual
npm init -y

# Instalar dependencias del backend
npm install express mysql2 pg jsonwebtoken bcryptjs dotenv cors helmet express-rate-limit
npm install -D nodemon

# Crear estructura de carpetas
mkdir backend frontend database
mkdir backend/routes backend/models backend/utils backend/middleware
mkdir frontend/src frontend/public
mkdir frontend/src/pages frontend/src/components frontend/src/services frontend/src/utils
```

### 4.2 Crear Archivos Base

**backend/.env**
```env
# MySQL (Lectura)
MYSQL_HOST=localhost
MYSQL_PORT=3306
MYSQL_USER=react_readonly
MYSQL_PASSWORD=react_readonly_2025
MYSQL_DATABASE=vanguard_intranet

# PostgreSQL (Aula Virtual)
POSTGRES_HOST=localhost
POSTGRES_PORT=5432
POSTGRES_USER=postgres
POSTGRES_PASSWORD=tu_password_postgres
POSTGRES_DATABASE=aula_virtual

# PHP API
PHP_API_URL=http://localhost/php-api
PHP_API_TOKEN=token_secreto

# JWT
JWT_SECRET=tu_secreto_jwt_super_seguro_2025
JWT_EXPIRES_IN=24h

# Server
PORT=5000
NODE_ENV=development
```

**backend/package.json**
```json
{
  "name": "react-aula-virtual-backend",
  "version": "1.0.0",
  "scripts": {
    "start": "node server.js",
    "dev": "nodemon server.js"
  }
}
```

**backend/server.js**
```javascript
require('dotenv').config();
const express = require('express');
const cors = require('cors');
const helmet = require('helmet');

const app = express();
const PORT = process.env.PORT || 5000;

// Middleware
app.use(helmet());
app.use(cors());
app.use(express.json());

// Routes
app.get('/api/health', (req, res) => {
  res.json({ status: 'OK', message: 'Aula Virtual API' });
});

// Start server
app.listen(PORT, () => {
  console.log(`✅ Server running on port ${PORT}`);
});
```

### 4.3 Configurar Frontend (React)

```bash
# En la carpeta frontend
npx create-react-app . --template minimal

# Instalar dependencias adicionales
npm install react-router-dom axios sweetalert2
```

---

## ✅ PASO 5: VERIFICAR CONFIGURACIÓN

### 5.1 Checklist

- [ ] Carpeta `react-aula-virtual` creada
- [ ] Archivo `CONTEXTO_COMPLETO_SISTEMA.md` copiado
- [ ] MySQL importado en XAMPP
- [ ] Usuario `react_readonly` creado
- [ ] PostgreSQL instalado y base de datos creada
- [ ] Estructura del proyecto creada
- [ ] Archivo `.env` configurado
- [ ] Dependencias instaladas

### 5.2 Probar Conexiones

**Probar MySQL:**
```bash
cd backend
node test-mysql.js
```

**Probar PostgreSQL:**
```bash
cd backend
node test-postgres.js
```

**Probar Servidor:**
```bash
cd backend
npm run dev
# Debería mostrar: ✅ Server running on port 5000
```

---

## 🎯 PRÓXIMOS PASOS

Una vez completados estos pasos:

1. **Implementar autenticación** (Fase 2)
2. **Implementar lectura de datos maestros** (Fase 3)
3. **Desarrollar módulos del aula virtual** (Fases 4-6)

---

## 📝 NOTAS IMPORTANTES

### Sobre MySQL
- ✅ Usa XAMPP para desarrollo local
- ✅ El archivo SQL está en: `sistema-anterior/base de datos/vanguard_intranet_2.sql`
- ✅ Usuario de solo lectura: `react_readonly`
- ✅ No modifiques la estructura de MySQL (PHP la usa)

### Sobre PostgreSQL
- ✅ Solo para datos del aula virtual
- ✅ React crea y gestiona todo aquí
- ✅ No afecta al sistema PHP

### Sobre el Proyecto
- ✅ Empieza desde cero en `react-aula-virtual/`
- ✅ Lee `CONTEXTO_COMPLETO_SISTEMA.md` para entender todo
- ✅ Sigue el plan de implementación fase por fase

---

**¿Listo para empezar?** 🚀

