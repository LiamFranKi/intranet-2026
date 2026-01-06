# 🌐 CONFIGURACIÓN - MYSQL REMOTO DEL VPS

## 🎯 SITUACIÓN ACTUAL

- ❌ **No hay sistema PHP local** para llenar datos
- ❌ **No se pueden crear datos de prueba** (estudiantes, docentes, cursos, grados)
- ✅ **Solución**: Usar MySQL remoto del VPS/servidor para desarrollo
- ✅ **Ventaja**: Trabajar con datos reales del sistema en producción

---

## ⚠️ REGLAS CRÍTICAS

### 1. **SOLO LECTURA (SELECT)**
- ✅ **Solo leer datos** de MySQL remoto
- ❌ **NO modificar** nada en el servidor
- ❌ **NO insertar** datos
- ❌ **NO actualizar** datos
- ❌ **NO eliminar** datos

### 2. **NO MOVER NADA**
- ❌ **NO mover** datos del servidor
- ❌ **NO modificar** estructura de tablas
- ❌ **NO cambiar** configuraciones
- ✅ **Solo usar** para generar código React

### 3. **USUARIO DE SOLO LECTURA**
- Crear usuario MySQL con permisos **SOLO SELECT**
- Sin permisos INSERT, UPDATE, DELETE
- Sin permisos de estructura (ALTER, DROP, etc.)

---

## 🔧 CONFIGURACIÓN DE CONEXIÓN REMOTA

### 1. Variables de Entorno

**Archivo: `backend/.env`**

```env
# MySQL Remoto (VPS) - SOLO LECTURA
MYSQL_HOST=tu_vps_ip_o_dominio
MYSQL_PORT=3306
MYSQL_USER=react_readonly
MYSQL_PASSWORD=password_segura_solo_lectura
MYSQL_DATABASE=vanguard_intranet

# Configuración de conexión
MYSQL_CONNECTION_LIMIT=10
MYSQL_QUEUE_LIMIT=0
MYSQL_TIMEOUT=10000
```

### 2. Configuración en el VPS

**En el servidor MySQL (VPS), crear usuario de solo lectura:**

```sql
-- Crear usuario solo lectura desde cualquier IP (o IP específica)
CREATE USER 'react_readonly'@'%' IDENTIFIED BY 'password_segura_solo_lectura';

-- O desde IP específica (más seguro)
CREATE USER 'react_readonly'@'tu_ip_desarrollo' IDENTIFIED BY 'password_segura_solo_lectura';

-- Permisos SOLO LECTURA en tablas necesarias
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

-- Aplicar cambios
FLUSH PRIVILEGES;
```

### 3. Habilitar Conexión Remota en MySQL

**En el servidor MySQL (VPS), editar configuración:**

```bash
# Editar /etc/mysql/mysql.conf.d/mysqld.cnf
# O en Windows: my.ini

# Comentar o cambiar:
# bind-address = 127.0.0.1
# Por:
bind-address = 0.0.0.0

# Reiniciar MySQL
sudo systemctl restart mysql
# O en Windows: Reiniciar servicio MySQL
```

**⚠️ IMPORTANTE**: Asegurar que el firewall del VPS permita conexiones en el puerto 3306.

---

## 💻 CÓDIGO DE CONEXIÓN EN NODE.JS

**Archivo: `backend/utils/mysql.js`**

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
  connectionLimit: parseInt(process.env.MYSQL_CONNECTION_LIMIT) || 10,
  queueLimit: parseInt(process.env.MYSQL_QUEUE_LIMIT) || 0,
  connectTimeout: parseInt(process.env.MYSQL_TIMEOUT) || 10000,
  // Solo SELECT permitido (validación adicional)
  multipleStatements: false
});

// Función helper para queries SOLO SELECT
async function query(sql, params = []) {
  try {
    // Validar que solo sean SELECT (seguridad adicional)
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

// Función para obtener año activo del colegio
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

module.exports = { 
  query, 
  mysqlReadPool,
  getAnioActivo
};
```

---

## 🧪 PRUEBA DE CONEXIÓN

**Archivo: `backend/test-mysql-remote.js`**

```javascript
const { query, getAnioActivo } = require('./utils/mysql');

async function testConnection() {
  try {
    console.log('🔌 Probando conexión a MySQL remoto...\n');

    // Test 1: Contar usuarios
    const usuarios = await query('SELECT COUNT(*) as total FROM usuarios');
    console.log('✅ Usuarios encontrados:', usuarios[0].total);

    // Test 2: Obtener año activo
    const anioActivo = await getAnioActivo(1); // colegio_id = 1
    console.log('✅ Año activo:', anioActivo);

    // Test 3: Obtener grupos del año activo
    const grupos = await query(
      'SELECT * FROM grupos WHERE anio = ? AND colegio_id = ? LIMIT 5',
      [anioActivo, 1]
    );
    console.log('✅ Grupos encontrados:', grupos.length);

    console.log('\n✅ Conexión exitosa!');
  } catch (error) {
    console.error('❌ Error:', error.message);
  }
  process.exit(0);
}

testConnection();
```

**Ejecutar:**
```bash
cd backend
node test-mysql-remote.js
```

---

## 🔒 SEGURIDAD ADICIONAL

### 1. Validación de Queries
- ✅ Validar que solo sean SELECT
- ✅ Rechazar cualquier INSERT, UPDATE, DELETE
- ✅ Logs de todas las consultas

### 2. Rate Limiting
- ✅ Limitar número de consultas por minuto
- ✅ Protección contra abuso
- ✅ Timeout de conexión

### 3. Logs y Monitoreo
- ✅ Registrar todas las conexiones
- ✅ Alertas si hay intentos de modificación
- ✅ Monitoreo de uso

---

## ✅ CHECKLIST DE CONFIGURACIÓN

- [ ] Usuario MySQL `react_readonly` creado en VPS
- [ ] Permisos SOLO SELECT asignados
- [ ] Conexión remota habilitada en MySQL del VPS
- [ ] Firewall del VPS permite puerto 3306
- [ ] Variables de entorno configuradas en `.env`
- [ ] Código de conexión implementado
- [ ] Prueba de conexión exitosa
- [ ] Validación de queries (solo SELECT) implementada

---

## 📝 NOTAS IMPORTANTES

1. **Desarrollo con datos reales:**
   - Trabajar con datos reales del servidor
   - No necesitas crear datos de prueba
   - Todo se lee directamente del sistema en producción

2. **No modificar nada:**
   - El sistema PHP sigue funcionando normalmente
   - React solo lee, no modifica
   - Seguridad garantizada con usuario de solo lectura

3. **Año activo:**
   - Siempre filtrar por `colegios.anio_activo`
   - Ver documento `FILTRADO_POR_ANIO_ACTIVO.md`

4. **Múltiples tutores:**
   - Ver documento `MULTIPLES_TUTORES.md` para implementación

---

**Esta configuración permite desarrollar React usando datos reales del servidor sin riesgo de modificar nada.** 🔒

