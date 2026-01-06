# 🏗️ ARQUITECTURA HÍBRIDA FINAL - PHP + REACT

## 📋 DECISIÓN TOMADA

### **Sistema Híbrido Compartido - Multicolegio**

- ✅ **PHP/MySQL**: Matrículas, pagos, facturación, productos, bancos
- ✅ **React/Node**: Solo aula virtual (exámenes, tareas, notas)
- ✅ **Multicolegio**: Mantener como está en PHP
- ✅ **Comunicación**: APIs REST entre sistemas

---

## 🔌 OPCIONES DE CONEXIÓN A BASE DE DATOS

### **OPCIÓN A: Node.js se conecta DIRECTAMENTE a MySQL** ⚠️

#### ¿Es posible?
**✅ SÍ, es totalmente posible**

Node.js puede conectarse directamente a MySQL usando:
- `mysql2` (driver oficial)
- `mysql` (driver alternativo)
- `sequelize` (ORM)

#### Código de ejemplo:
```javascript
// backend/utils/mysql.js
const mysql = require('mysql2/promise');

const pool = mysql.createPool({
  host: 'localhost',
  user: 'vanguard',
  password: 'password',
  database: 'vanguard_intranet',
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0
});

module.exports = pool;
```

#### Ventajas ✅
1. **Rapidez** ⚡
   - Acceso directo a datos
   - Sin latencia de API
   - Consultas SQL directas

2. **Simplicidad** 🎯
   - No necesitas crear APIs en PHP
   - Acceso directo a tablas
   - Menos código

3. **Performance** 📈
   - Menos capas (sin HTTP)
   - Consultas optimizadas
   - Transacciones directas

#### Desventajas ❌
1. **Seguridad** 🔴
   - Node.js necesita credenciales de MySQL
   - Si Node.js es comprometido, MySQL también
   - Acceso directo a datos sensibles (pagos, facturación)

2. **Acoplamiento** 🔗
   - Node.js depende de estructura de MySQL
   - Si PHP cambia tablas, Node.js se rompe
   - No hay capa de abstracción

3. **Concurrencia** ⚠️
   - PHP y Node.js escribiendo a la vez
   - Posibles conflictos de transacciones
   - Race conditions

4. **Mantenimiento** 🔧
   - Cambios en MySQL afectan ambos sistemas
   - Difícil de versionar
   - Sin control de acceso granular

5. **Escalabilidad** 📊
   - Si escalas Node.js, necesitas más conexiones MySQL
   - Pool de conexiones compartido
   - Posibles cuellos de botella

---

### **OPCIÓN B: Node.js se conecta a MySQL SOLO LECTURA + APIs REST para escritura** ⭐ RECOMENDADA

#### Descripción
- **Lectura directa**: Node.js lee directamente de MySQL (usuarios, alumnos, matrículas)
- **Escritura vía API**: Node.js escribe a través de APIs REST de PHP (notas, exámenes)

#### Arquitectura:
```
┌─────────────────────────────────────────────────────────┐
│                    MySQL (Compartida)                    │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐ │
│  │   Usuarios   │  │   Alumnos    │  │  Matrículas │ │
│  │   Pagos      │  │   Productos  │  │  Costos     │ │
│  │   Deudas     │  │   Bancos     │  │  Boletas    │ │
│  └──────────────┘  └──────────────┘  └──────────────┘ │
└─────────────────────────────────────────────────────────┘
         ▲                        ▲
         │                        │
    ┌────┴────┐            ┌────┴────┐
    │   PHP   │            │  Node   │
    │ (R/W)   │            │ (R/O)   │
    └─────────┘            └─────────┘
         ▲                        │
         │                        │
         └──────────API───────────┘
              (Escritura)
```

#### Ventajas ✅
1. **Seguridad** 🔒
   - Node.js solo LEE (menos riesgo)
   - PHP controla escritura de datos sensibles
   - Validaciones centralizadas en PHP

2. **Separación de responsabilidades** 🎯
   - PHP: Datos administrativos (R/W)
   - Node.js: Datos académicos (R/O + escritura vía API)
   - Cada sistema controla su dominio

3. **Mantenibilidad** 🔧
   - Cambios en MySQL no rompen Node.js (solo lectura)
   - PHP controla estructura de datos
   - APIs versionadas

4. **Control de acceso** 🔐
   - PHP valida permisos antes de escribir
   - Node.js no puede modificar pagos/facturación
   - Auditoría centralizada

#### Desventajas ❌
1. **Latencia en escritura** ⏱️
   - Escritura vía API (HTTP) es más lenta
   - Depende de disponibilidad de PHP

2. **Complejidad** 🔴
   - Necesitas crear APIs en PHP
   - Manejo de errores en APIs
   - Más código

---

### **OPCIÓN C: Solo APIs REST (sin conexión directa)** ⭐⭐ ALTERNATIVA SEGURA

#### Descripción
- **Todo vía API**: Node.js NO se conecta a MySQL
- **PHP expone APIs**: Para lectura y escritura
- **Separación total**: Node.js no conoce estructura de MySQL

#### Arquitectura:
```
┌─────────────────────────────────────────────────────────┐
│                    MySQL (Solo PHP)                      │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐ │
│  │   Usuarios   │  │   Alumnos    │  │  Matrículas │ │
│  │   Pagos      │  │   Productos  │  │  Costos     │ │
│  └──────────────┘  └──────────────┘  └──────────────┘ │
└─────────────────────────────────────────────────────────┘
         ▲                        ▲
         │                        │
    ┌────┴────┐            ┌────┴────┐
    │   PHP   │            │  Node   │
    │ (R/W)   │            │ (API)   │
    └─────────┘            └─────────┘
         ▲                        │
         │                        │
         └──────────API───────────┘
         (Lectura + Escritura)
```

#### Ventajas ✅
1. **Máxima seguridad** 🔒🔒🔒
   - Node.js NO tiene acceso a MySQL
   - PHP controla TODO
   - Separación total

2. **Flexibilidad** 🎯
   - Puedes cambiar estructura de MySQL sin afectar Node.js
   - PHP puede validar/transformar datos
   - Versionado de APIs

3. **Escalabilidad** 📈
   - PHP puede cachear respuestas
   - Rate limiting
   - Load balancing

#### Desventajas ❌
1. **Latencia** ⏱️
   - Todo vía HTTP (más lento)
   - Depende de disponibilidad de PHP

2. **Complejidad** 🔴
   - Necesitas crear muchas APIs
   - Manejo de errores
   - Más código

3. **Performance** 📊
   - HTTP overhead
   - Serialización/deserialización
   - Más recursos

---

## 🎯 RECOMENDACIÓN FINAL

### **OPCIÓN B: Lectura Directa + Escritura vía API** ⭐⭐⭐

#### ¿Por qué?
1. **Balance perfecto** ⚖️
   - Lectura rápida (directa)
   - Escritura segura (vía API)

2. **Seguridad** 🔒
   - Node.js solo lee datos no sensibles
   - PHP controla escritura de datos críticos

3. **Performance** ⚡
   - Lectura directa (rápida)
   - Escritura vía API (aceptable)

4. **Mantenibilidad** 🔧
   - Separación clara de responsabilidades
   - PHP controla estructura
   - APIs versionadas

---

## 📋 IMPLEMENTACIÓN - OPCIÓN B

### 1. Node.js se conecta a MySQL (SOLO LECTURA)

**Archivo: `backend/utils/mysql.js`**
```javascript
const mysql = require('mysql2/promise');

// Pool de conexiones SOLO LECTURA
const mysqlReadPool = mysql.createPool({
  host: process.env.MYSQL_HOST || 'localhost',
  user: process.env.MYSQL_USER || 'vanguard_readonly', // Usuario SOLO lectura
  password: process.env.MYSQL_PASSWORD,
  database: process.env.MYSQL_DATABASE || 'vanguard_intranet',
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0,
  // Solo SELECT permitido
  multipleStatements: false
});

// Función helper para queries
async function query(sql, params) {
  try {
    const [rows] = await mysqlReadPool.execute(sql, params);
    return rows;
  } catch (error) {
    console.error('MySQL Error:', error);
    throw error;
  }
}

module.exports = { query, mysqlReadPool };
```

**Usuario MySQL (SOLO LECTURA):**
```sql
-- Crear usuario solo lectura
CREATE USER 'vanguard_readonly'@'localhost' IDENTIFIED BY 'password_segura';

-- Permisos solo lectura en tablas necesarias
GRANT SELECT ON vanguard_intranet.usuarios TO 'vanguard_readonly'@'localhost';
GRANT SELECT ON vanguard_intranet.alumnos TO 'vanguard_readonly'@'localhost';
GRANT SELECT ON vanguard_intranet.matriculas TO 'vanguard_readonly'@'localhost';
GRANT SELECT ON vanguard_intranet.grupos TO 'vanguard_readonly'@'localhost';
GRANT SELECT ON vanguard_intranet.colegios TO 'vanguard_readonly'@'localhost';
-- ... más tablas según necesites

FLUSH PRIVILEGES;
```

### 2. Node.js lee datos directamente

**Ejemplo: Obtener usuario**
```javascript
// backend/routes/auth.routes.js
const { query } = require('../utils/mysql');

router.post('/validate-php-token', async (req, res) => {
  try {
    const { usuario_id } = req.body;
    
    // Lectura directa de MySQL
    const usuarios = await query(
      'SELECT * FROM usuarios WHERE id = ? AND estado = ?',
      [usuario_id, 'ACTIVO']
    );
    
    if (usuarios.length === 0) {
      return res.status(401).json({ error: 'Usuario no encontrado' });
    }
    
    const usuario = usuarios[0];
    
    // Verificar deudas (lectura directa)
    const deudas = await getDeudas(usuario.id);
    
    res.json({ usuario, deudas });
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
});
```

### 3. Node.js escribe vía API REST de PHP

**Ejemplo: Exportar notas**
```javascript
// backend/routes/notas.routes.js
const axios = require('axios');

router.post('/export', async (req, res) => {
  try {
    const { notas } = req.body; // Array de notas
    
    // Escritura vía API de PHP
    const response = await axios.post(
      `${process.env.PHP_API_URL}/api/notas/import`,
      { notas },
      {
        headers: {
          'Authorization': `Bearer ${process.env.PHP_API_TOKEN}`,
          'Content-Type': 'application/json'
        }
      }
    );
    
    res.json({ success: true, data: response.data });
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
});
```

### 4. PHP expone APIs REST

**Archivo: `sistema-anterior/api/notas.php`**
```php
<?php
require '../Settings.php';
require '../Core/Autoloader.php';
// ... inicialización

header('Content-Type: application/json');

// Validar token
$token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
if (!validateToken($token)) {
    http_response_code(401);
    echo json_encode(['error' => 'Token inválido']);
    exit;
}

// POST /api/notas/import
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['PATH_INFO'] === '/import') {
    $data = json_decode(file_get_contents('php://input'), true);
    $notas = $data['notas'] ?? [];
    
    foreach ($notas as $notaData) {
        Nota::create([
            'matricula_id' => $notaData['matricula_id'],
            'criterio_id' => $notaData['criterio_id'],
            'ciclo' => $notaData['ciclo'],
            'asignatura_id' => $notaData['asignatura_id'],
            'nota' => $notaData['nota']
        ]);
    }
    
    echo json_encode(['success' => true, 'imported' => count($notas)]);
    exit;
}
```

---

## 🔐 SEGURIDAD

### 1. Usuario MySQL de solo lectura
- ✅ Usuario dedicado con permisos SELECT únicamente
- ✅ Sin permisos INSERT, UPDATE, DELETE
- ✅ Sin acceso a tablas sensibles (pagos, boletas)

### 2. Validación de tokens
- ✅ PHP valida tokens antes de permitir escritura
- ✅ Node.js valida tokens antes de leer datos
- ✅ Tokens con expiración

### 3. Rate limiting
- ✅ Limitar requests por minuto
- ✅ Protección contra abuso
- ✅ Logs de acceso

---

## 📊 COMPARACIÓN DE OPCIONES

| Criterio | Opción A: Directa R/W | Opción B: Lectura + API | Opción C: Solo API |
|----------|----------------------|------------------------|-------------------|
| **Seguridad** | ⭐ Baja | ⭐⭐⭐ Alta | ⭐⭐⭐ Muy Alta |
| **Performance Lectura** | ⭐⭐⭐ Muy Rápida | ⭐⭐⭐ Muy Rápida | ⭐⭐ Rápida |
| **Performance Escritura** | ⭐⭐⭐ Muy Rápida | ⭐⭐ Rápida | ⭐⭐ Rápida |
| **Complejidad** | ⭐⭐ Media | ⭐⭐ Media | ⭐⭐⭐ Alta |
| **Mantenibilidad** | ⭐ Baja | ⭐⭐⭐ Alta | ⭐⭐⭐ Alta |
| **Escalabilidad** | ⭐⭐ Media | ⭐⭐⭐ Alta | ⭐⭐⭐ Alta |

---

## ✅ CONCLUSIÓN

**Recomendación: OPCIÓN B - Lectura Directa + Escritura vía API**

- ✅ Lectura rápida (directa a MySQL)
- ✅ Escritura segura (vía API de PHP)
- ✅ Separación de responsabilidades
- ✅ Seguridad adecuada
- ✅ Mantenibilidad

**Próximos pasos:**
1. Crear usuario MySQL de solo lectura
2. Configurar conexión MySQL en Node.js
3. Crear APIs REST en PHP
4. Implementar validación de tokens
5. Probar lectura y escritura

---

**¿Te parece bien esta arquitectura?** 🤔

