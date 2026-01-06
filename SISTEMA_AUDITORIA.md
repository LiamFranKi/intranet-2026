# 📊 SISTEMA DE AUDITORÍA Y LOGS

## 🎯 PROPÓSITO

Registrar **TODAS** las acciones de los usuarios en el sistema para tener control completo de:
- ✅ Qué hizo cada usuario
- ✅ Cuándo lo hizo (fecha y hora exacta)
- ✅ Qué páginas/URLs visitó
- ✅ Qué acciones realizó (crear, editar, eliminar, ver, etc.)
- ✅ Qué datos modificó (antes y después)
- ✅ Si la acción fue exitosa o falló
- ✅ Cuánto tiempo tomó cada acción

---

## 📋 TABLA: `auditoria_logs`

### Campos Principales:

- **usuario_id** - ID del usuario en MySQL
- **tipo_usuario** - ALUMNO, DOCENTE, TUTOR, APODERADO, ADMINISTRADOR
- **accion** - LOGIN, LOGOUT, CREAR, EDITAR, ELIMINAR, VER, DESCARGAR, CALIFICAR, etc.
- **modulo** - EXAMENES, TAREAS, TEMAS, CALIFICACIONES, etc.
- **entidad** - examen, tarea, tema, etc.
- **entidad_id** - ID de la entidad afectada
- **descripcion** - Descripción detallada de la acción
- **url** - URL completa accedida
- **metodo_http** - GET, POST, PUT, DELETE
- **ip_address** - IP del usuario
- **user_agent** - Navegador y sistema operativo
- **datos_anteriores** - Datos antes de modificar (JSON)
- **datos_nuevos** - Datos después de modificar (JSON)
- **resultado** - EXITOSO, ERROR, CANCELADO
- **mensaje_error** - Mensaje de error si hubo
- **duracion_ms** - Tiempo en milisegundos
- **fecha_hora** - Timestamp exacto
- **fecha** - Fecha (generada automáticamente)
- **hora** - Hora (generada automáticamente)

---

## 🔄 FUNCIONAMIENTO AUTOMÁTICO

### Middleware de Auditoría

El middleware `auditoria.js` se ejecuta automáticamente en **TODAS** las rutas y registra:

1. **Login/Logout** - Cada inicio y cierre de sesión
2. **Navegación** - Cada página/URL visitada
3. **Acciones** - Cada crear, editar, eliminar
4. **Consultas** - Cada GET/consulta de datos
5. **Errores** - Cada error que ocurra

### Ejemplos de Registros:

**Login:**
```json
{
  "usuario_id": 123,
  "tipo_usuario": "DOCENTE",
  "accion": "LOGIN",
  "modulo": "AUTENTICACION",
  "url": "/api/auth/login",
  "resultado": "EXITOSO"
}
```

**Crear Examen:**
```json
{
  "usuario_id": 123,
  "tipo_usuario": "DOCENTE",
  "accion": "CREAR",
  "modulo": "EXAMENES",
  "entidad": "examen",
  "entidad_id": 456,
  "descripcion": "Creó un examen",
  "url": "/api/examenes",
  "datos_nuevos": {"titulo": "Examen de Matemática", ...},
  "resultado": "EXITOSO"
}
```

**Editar Tarea:**
```json
{
  "usuario_id": 123,
  "tipo_usuario": "DOCENTE",
  "accion": "EDITAR",
  "modulo": "TAREAS",
  "entidad": "tarea",
  "entidad_id": 789,
  "descripcion": "Editó tarea ID 789",
  "url": "/api/tareas/789",
  "datos_anteriores": {"titulo": "Tarea antigua", ...},
  "datos_nuevos": {"titulo": "Tarea nueva", ...},
  "resultado": "EXITOSO"
}
```

---

## 📊 CONSULTAS ÚTILES

### Ver logs de un usuario específico:
```sql
SELECT * FROM auditoria_logs 
WHERE usuario_id = 123 
ORDER BY fecha_hora DESC 
LIMIT 100;
```

### Ver qué hizo un docente hoy:
```sql
SELECT accion, modulo, descripcion, fecha_hora 
FROM auditoria_logs 
WHERE usuario_id = 123 
  AND tipo_usuario = 'DOCENTE'
  AND fecha = CURRENT_DATE
ORDER BY fecha_hora DESC;
```

### Ver si un docente realmente creó una tarea:
```sql
SELECT * FROM auditoria_logs 
WHERE usuario_id = 123 
  AND accion = 'CREAR'
  AND modulo = 'TAREAS'
  AND entidad_id = 789;
```

### Ver todas las acciones de un módulo:
```sql
SELECT * FROM auditoria_logs 
WHERE modulo = 'EXAMENES'
  AND fecha >= '2025-01-01'
ORDER BY fecha_hora DESC;
```

---

## 🔍 ENDPOINTS DE LA API

### GET `/api/auditoria/mis-logs`
Obtener logs del usuario autenticado:
```javascript
// Query params: fechaDesde, fechaHasta, limite
GET /api/auditoria/mis-logs?fechaDesde=2025-01-01&limite=50
```

### GET `/api/auditoria/usuario/:usuarioId` (Solo Admin)
Obtener logs de un usuario específico:
```javascript
GET /api/auditoria/usuario/123?fechaDesde=2025-01-01
```

### GET `/api/auditoria/modulo/:modulo` (Solo Admin)
Obtener logs de un módulo:
```javascript
GET /api/auditoria/modulo/EXAMENES?fechaDesde=2025-01-01
```

### GET `/api/auditoria/estadisticas` (Solo Admin)
Obtener estadísticas de actividad:
```javascript
GET /api/auditoria/estadisticas?fechaDesde=2025-01-01
```

---

## ✅ VENTAJAS

1. **Control Total** - Sabes exactamente qué hizo cada usuario
2. **Resolución de Conflictos** - Puedes verificar si realmente hizo algo
3. **Seguridad** - Registro de accesos y modificaciones
4. **Análisis** - Estadísticas de uso del sistema
5. **Auditoría** - Cumplimiento y trazabilidad

---

## 📝 NOTA SOBRE TABLA `usuarios_sync`

**Eliminada** - No es necesaria porque:
- ✅ El login lee directamente de MySQL
- ✅ No necesitamos sincronizar usuarios
- ✅ Toda la autenticación se hace desde MySQL

---

**El sistema de auditoría registra automáticamente TODO lo que hacen los usuarios.** 📊

