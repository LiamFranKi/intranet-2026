# 🗄️ CONFIGURAR POSTGRESQL LOCAL

## ⚠️ PROBLEMA ACTUAL

La contraseña en `backend/.env` es para el servidor de producción. Para desarrollo local, necesitas usar tu contraseña local de PostgreSQL.

---

## 🔧 SOLUCIÓN

### Opción 1: Crear archivo .env.development

El archivo `backend/.env.development` ya existe. Solo necesitas ajustar la contraseña de PostgreSQL:

```env
POSTGRES_PASSWORD=tu_password_postgres_local
```

Luego ejecuta las migraciones con:
```bash
NODE_ENV=development npm run postgres:migrate
```

### Opción 2: Cambiar temporalmente backend/.env

Edita `backend/.env` y cambia:
```env
POSTGRES_PASSWORD=tu_password_postgres_local
```

Luego ejecuta:
```bash
npm run postgres:migrate
```

---

## 🔍 VERIFICAR CONTRASEÑA DE POSTGRESQL

### Windows
1. Abre "Servicios" (services.msc)
2. Busca "PostgreSQL"
3. Click derecho → Propiedades
4. Ve a la pestaña "Iniciar sesión"
5. Ahí verás el usuario (generalmente "postgres")

Para cambiar la contraseña:
```powershell
psql -U postgres
ALTER USER postgres WITH PASSWORD 'nueva_password';
\q
```

### Linux/Mac
```bash
sudo -u postgres psql
ALTER USER postgres WITH PASSWORD 'nueva_password';
\q
```

---

## ✅ PROBAR CONEXIÓN

```bash
psql -U postgres -h localhost
# Ingresa tu contraseña local
```

Si funciona, usa esa contraseña en `backend/.env` o `backend/.env.development`.

---

**Una vez configurada la contraseña correcta, ejecuta `npm run postgres:migrate`** 🚀

