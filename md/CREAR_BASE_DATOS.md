# 🗄️ CREAR BASE DE DATOS POSTGRESQL

## 📋 PREREQUISITOS

- ✅ PostgreSQL instalado
- ✅ Servicio PostgreSQL corriendo
- ✅ Archivo `backend/.env` configurado con credenciales

---

## 🚀 OPCIÓN 1: Script Automático (Recomendado)

```bash
# Desde la raíz del proyecto
npm run postgres:create
```

Este script:
- ✅ Se conecta a PostgreSQL
- ✅ Crea la base de datos `aula_virtual`
- ✅ Crea el usuario (si es necesario)
- ✅ Otorga permisos

---

## 🔧 OPCIÓN 2: Manual (Línea de Comandos)

### Windows

```powershell
# Conectar a PostgreSQL
psql -U postgres

# Crear base de datos
CREATE DATABASE aula_virtual;

# Crear usuario (opcional, si no usas postgres)
CREATE USER aula_virtual_user WITH PASSWORD 'Vanguard2025@&';

# Otorgar permisos
GRANT ALL PRIVILEGES ON DATABASE aula_virtual TO aula_virtual_user;

# Salir
\q
```

### Linux/Mac

```bash
# Conectar a PostgreSQL
sudo -u postgres psql

# Crear base de datos
CREATE DATABASE aula_virtual;

# Crear usuario (opcional)
CREATE USER aula_virtual_user WITH PASSWORD 'Vanguard2025@&';

# Otorgar permisos
GRANT ALL PRIVILEGES ON DATABASE aula_virtual TO aula_virtual_user;

# Salir
\q
```

---

## ⚙️ CONFIGURACIÓN EN .env

Asegúrate de que `backend/.env` tenga:

```env
POSTGRES_HOST=localhost
POSTGRES_PORT=5432
POSTGRES_USER=postgres
POSTGRES_PASSWORD=Vanguard2025@&
POSTGRES_DATABASE=aula_virtual
```

---

## ✅ VERIFICACIÓN

### Verificar que la base de datos existe:

```bash
# Conectar y listar bases de datos
psql -U postgres -c "\l"
```

Deberías ver `aula_virtual` en la lista.

### Probar conexión desde Node.js:

```bash
cd backend
node -e "const {pool} = require('./utils/postgres'); pool.query('SELECT NOW()').then(r => {console.log('✅ PostgreSQL OK:', r.rows[0]); process.exit(0)}).catch(e => {console.error('❌ Error:', e.message); process.exit(1)})"
```

---

## 🔄 RECREAR BASE DE DATOS

Si necesitas eliminar y recrear:

```bash
# ⚠️ ADVERTENCIA: Esto eliminará todos los datos
node database/scripts/drop-database.js
npm run postgres:create
```

---

## 🐛 TROUBLESHOOTING

### Error: "password authentication failed"
- Verifica la contraseña en `backend/.env`
- Verifica que el usuario existe en PostgreSQL

### Error: "database does not exist"
- Ejecuta `npm run postgres:create`
- O crea manualmente con los comandos arriba

### Error: "connection refused"
- Verifica que PostgreSQL esté corriendo:
  - Windows: Servicios → PostgreSQL
  - Linux: `sudo systemctl status postgresql`

---

**Una vez creada la base de datos, las tablas se crearán automáticamente cuando inicies la aplicación.** 🚀

