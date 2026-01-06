# 📅 FILTRADO POR AÑO ACTIVO - CRÍTICO

## 🎯 IMPORTANCIA

**TODO el sistema debe filtrarse por el año activo configurado en PHP (`colegios.anio_activo`).**

- ✅ Si PHP está configurado en **2025**, solo se ve todo de **2025**
- ✅ Si PHP está configurado en **2026**, solo se ve todo de **2026**
- ✅ Esto incluye: estudiantes, apoderados, docentes, cursos, grados, matrículas, TODO

---

## 🔍 CÓMO FUNCIONA

### 1. Año Activo en PHP

**Tabla: `colegios`**
```sql
SELECT anio_activo FROM colegios WHERE id = ?;
-- Ejemplo: 2025, 2026, etc.
```

**En PHP:**
- El administrador configura el año activo en la configuración del colegio
- Este año determina qué datos se muestran en todo el sistema
- Si cambia a 2026, solo se ven datos de 2026

### 2. React DEBE Hacer lo Mismo

**React debe:**
1. ✅ Leer `colegios.anio_activo` al iniciar sesión
2. ✅ Guardar en contexto/sesión
3. ✅ Filtrar TODAS las consultas por este año
4. ✅ Si el año cambia en PHP, React automáticamente muestra el nuevo año

---

## 📊 QUERIES QUE DEBEN FILTRAR POR AÑO ACTIVO

### 1. Obtener Año Activo

```javascript
// backend/utils/mysql.js
async function getAnioActivo(colegioId) {
  const [rows] = await mysqlReadPool.execute(
    'SELECT anio_activo FROM colegios WHERE id = ?',
    [colegioId]
  );
  return rows.length > 0 ? rows[0].anio_activo : null;
}
```

### 2. Grupos (Grados/Secciones)

```sql
-- ✅ CORRECTO: Filtrar por año activo
SELECT * FROM grupos 
WHERE colegio_id = ? 
  AND anio = ? -- Año activo
ORDER BY grado, seccion;

-- ❌ INCORRECTO: Sin filtrar por año
SELECT * FROM grupos WHERE colegio_id = ?;
```

### 3. Matrículas

```sql
-- ✅ CORRECTO: Filtrar por año activo
SELECT m.*, a.*, g.grado, g.seccion
FROM matriculas m
INNER JOIN alumnos a ON a.id = m.alumno_id
INNER JOIN grupos g ON g.id = m.grupo_id
WHERE m.colegio_id = ?
  AND m.estado = 0 -- Activo
  AND g.anio = ? -- Año activo
ORDER BY a.apellido_paterno, a.apellido_materno;

-- ❌ INCORRECTO: Sin filtrar por año
SELECT * FROM matriculas WHERE colegio_id = ?;
```

### 4. Cursos

```sql
-- ✅ CORRECTO: Filtrar por año activo
SELECT c.*, g.grado, g.seccion, p.nombres as docente_nombres
FROM cursos c
INNER JOIN grupos g ON g.id = c.grupo_id
INNER JOIN personal p ON p.id = c.personal_id
WHERE c.colegio_id = ?
  AND g.anio = ? -- Año activo
ORDER BY c.nombre;

-- ❌ INCORRECTO: Sin filtrar por año
SELECT * FROM cursos WHERE colegio_id = ?;
```

### 5. Estudiantes de un Docente

```sql
-- ✅ CORRECTO: Filtrar por año activo
SELECT DISTINCT a.*, g.grado, g.seccion
FROM alumnos a
INNER JOIN matriculas m ON m.alumno_id = a.id
INNER JOIN grupos g ON g.id = m.grupo_id
INNER JOIN cursos c ON c.grupo_id = g.id
WHERE c.personal_id = ? -- ID del docente
  AND m.estado = 0 -- Matrícula activa
  AND g.anio = ? -- Año activo
  AND a.colegio_id = ?
ORDER BY a.apellido_paterno, a.apellido_materno;

-- ❌ INCORRECTO: Sin filtrar por año
SELECT * FROM alumnos WHERE colegio_id = ?;
```

### 6. Hijos de Apoderado

```sql
-- ✅ CORRECTO: Filtrar por año activo
SELECT a.*, m.grupo_id, g.grado, g.seccion
FROM alumnos a
INNER JOIN familias f ON f.alumno_id = a.id
INNER JOIN matriculas m ON m.alumno_id = a.id
INNER JOIN grupos g ON g.id = m.grupo_id
WHERE f.apoderado_id = ? -- ID del apoderado
  AND m.estado = 0 -- Matrícula activa
  AND g.anio = ? -- Año activo
  AND a.colegio_id = ?
ORDER BY a.apellido_paterno, a.apellido_materno;

-- ❌ INCORRECTO: Sin filtrar por año
SELECT * FROM alumnos WHERE apoderado_id = ?;
```

### 7. Grupos de Tutor

```sql
-- ✅ CORRECTO: Filtrar por año activo
SELECT g.*
FROM grupos g
WHERE g.tutor_id = ? -- ID del tutor
  AND g.colegio_id = ?
  AND g.anio = ? -- Año activo
ORDER BY g.grado, g.seccion;

-- ❌ INCORRECTO: Sin filtrar por año
SELECT * FROM grupos WHERE tutor_id = ?;
```

### 8. Deudas

```sql
-- ✅ CORRECTO: Filtrar por año activo
SELECT p.*
FROM pagos p
INNER JOIN matriculas m ON m.id = p.matricula_id
INNER JOIN grupos g ON g.id = m.grupo_id
WHERE m.alumno_id = ?
  AND p.estado_pago = 'PENDIENTE'
  AND m.estado = 0
  AND g.anio = ? -- Año activo
  AND m.colegio_id = ?;

-- ❌ INCORRECTO: Sin filtrar por año
SELECT * FROM pagos WHERE matricula_id = ?;
```

---

## 💻 IMPLEMENTACIÓN EN NODE.JS

### 1. Middleware para Obtener Año Activo

**Archivo: `backend/middleware/anioActivo.js`**

```javascript
const { getAnioActivo } = require('../utils/mysql');

// Middleware para obtener y validar año activo
async function obtenerAnioActivo(req, res, next) {
  try {
    const colegioId = req.user?.colegio_id || req.body?.colegio_id || req.query?.colegio_id;
    
    if (!colegioId) {
      return res.status(400).json({ 
        error: 'colegio_id es requerido' 
      });
    }

    const anioActivo = await getAnioActivo(colegioId);
    
    if (!anioActivo) {
      return res.status(404).json({ 
        error: 'Colegio no encontrado o sin año activo configurado' 
      });
    }

    // Agregar año activo al request
    req.anioActivo = anioActivo;
    req.colegioId = colegioId;
    
    next();
  } catch (error) {
    console.error('Error obteniendo año activo:', error);
    res.status(500).json({ error: 'Error obteniendo año activo' });
  }
}

module.exports = { obtenerAnioActivo };
```

### 2. Helper para Queries con Año Activo

**Archivo: `backend/utils/queryHelpers.js`**

```javascript
const { query } = require('./mysql');

// Helper para queries que requieren año activo
async function queryConAnioActivo(sql, params, anioActivo, colegioId) {
  // Asegurar que el SQL incluya filtro por año
  if (!sql.includes('g.anio') && !sql.includes('grupos.anio')) {
    console.warn('⚠️ Query sin filtro por año activo:', sql);
  }

  // Agregar año activo y colegio_id a los parámetros si no están
  const paramsFinal = [...params];
  
  // Si el SQL tiene placeholder para año, agregarlo
  if (sql.includes('?') && params.length < sql.match(/\?/g).length) {
    paramsFinal.push(anioActivo);
  }

  return await query(sql, paramsFinal);
}

module.exports = { queryConAnioActivo };
```

### 3. Ejemplo de Uso en Routes

**Archivo: `backend/routes/grupos.routes.js`**

```javascript
const express = require('express');
const router = express.Router();
const { obtenerAnioActivo } = require('../middleware/anioActivo');
const { query } = require('../utils/mysql');
const { authenticateToken } = require('../middleware/auth');

// Obtener grupos del año activo
router.get('/mis-grupos', authenticateToken, obtenerAnioActivo, async (req, res) => {
  try {
    const { anioActivo, colegioId } = req;
    const personalId = req.user.personal_id;

    // Query con filtro por año activo
    const grupos = await query(
      `SELECT DISTINCT g.*, c.nombre as curso_nombre, c.id as curso_id
       FROM grupos g
       INNER JOIN cursos c ON c.grupo_id = g.id
       WHERE c.personal_id = ?
         AND g.colegio_id = ?
         AND g.anio = ? -- Año activo
       ORDER BY g.grado, g.seccion`,
      [personalId, colegioId, anioActivo]
    );

    res.json({ grupos, anioActivo });
  } catch (error) {
    console.error('Error obteniendo grupos:', error);
    res.status(500).json({ error: 'Error obteniendo grupos' });
  }
});

module.exports = router;
```

---

## 🎯 CONTEXTO EN REACT

### 1. Context para Año Activo

**Archivo: `frontend/src/context/AnioActivoContext.jsx`**

```javascript
import React, { createContext, useContext, useState, useEffect } from 'react';
import { useAuth } from './AuthContext';
import api from '../services/api';

const AnioActivoContext = createContext();

export const useAnioActivo = () => {
  const context = useContext(AnioActivoContext);
  if (!context) {
    throw new Error('useAnioActivo debe usarse dentro de AnioActivoProvider');
  }
  return context;
};

export const AnioActivoProvider = ({ children }) => {
  const { user } = useAuth();
  const [anioActivo, setAnioActivo] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const obtenerAnioActivo = async () => {
      if (!user?.colegio_id) {
        setLoading(false);
        return;
      }

      try {
        const response = await api.get(`/colegios/${user.colegio_id}/anio-activo`);
        setAnioActivo(response.data.anioActivo);
      } catch (error) {
        console.error('Error obteniendo año activo:', error);
      } finally {
        setLoading(false);
      }
    };

    obtenerAnioActivo();
  }, [user]);

  return (
    <AnioActivoContext.Provider value={{ anioActivo, loading }}>
      {children}
    </AnioActivoContext.Provider>
  );
};
```

### 2. Uso en Componentes

```javascript
import { useAnioActivo } from '../context/AnioActivoContext';

function MisCursos() {
  const { anioActivo, loading } = useAnioActivo();

  if (loading) return <div>Cargando...</div>;
  if (!anioActivo) return <div>No hay año activo configurado</div>;

  return (
    <div>
      <h2>Mis Cursos - Año {anioActivo}</h2>
      {/* Resto del componente */}
    </div>
  );
}
```

---

## ✅ VALIDACIONES CRÍTICAS

### 1. Todas las Queries DEBEN Incluir:

```sql
-- ✅ SIEMPRE incluir:
AND g.anio = ? -- Año activo
AND tabla.colegio_id = ? -- Colegio del usuario
```

### 2. No Mostrar Datos de Otros Años

- ❌ NO mostrar grupos de años anteriores
- ❌ NO mostrar matrículas de años anteriores
- ❌ NO mostrar estudiantes de años anteriores
- ✅ SOLO mostrar datos del año activo

### 3. Si Cambia el Año en PHP

- ✅ React debe detectar el cambio
- ✅ Actualizar automáticamente
- ✅ Mostrar solo el nuevo año activo

---

## 📝 CHECKLIST

- [ ] Función `getAnioActivo()` implementada
- [ ] Middleware `obtenerAnioActivo` creado
- [ ] Todas las queries filtran por año activo
- [ ] Context de React para año activo
- [ ] Validación en cada query
- [ ] Logs de advertencia si falta filtro
- [ ] Pruebas con diferentes años activos

---

## ⚠️ ERRORES COMUNES

### ❌ Error 1: Olvidar filtrar por año
```sql
-- INCORRECTO
SELECT * FROM grupos WHERE colegio_id = ?;
```

### ✅ Solución:
```sql
-- CORRECTO
SELECT * FROM grupos 
WHERE colegio_id = ? 
  AND anio = ?;
```

### ❌ Error 2: Usar año hardcodeado
```javascript
// INCORRECTO
const grupos = await query('SELECT * FROM grupos WHERE anio = 2025');
```

### ✅ Solución:
```javascript
// CORRECTO
const anioActivo = await getAnioActivo(colegioId);
const grupos = await query('SELECT * FROM grupos WHERE anio = ?', [anioActivo]);
```

---

**Este filtrado es CRÍTICO. Sin él, el sistema mostrará datos incorrectos de años anteriores.** 📅

