# Documentación: Habilitación Automática de Exámenes

Este documento detalla la funcionalidad de habilitación automática de exámenes basada en fechas y horas, implementada en el sistema nuevo (Node.js/React).

---

## 1. Descripción General

La habilitación automática permite que los exámenes se activen o desactiven automáticamente según las fechas y horas configuradas, sin intervención manual del docente. Esta funcionalidad está implementada en el backend Node.js y funciona cuando los módulos del sistema nuevo (React) consultan la lista de exámenes.

---

## 2. Funcionamiento

### 2.1. Cuándo se Ejecuta

La habilitación automática se ejecuta cada vez que se consulta la lista de exámenes a través de la API:

- **Ruta:** `GET /api/docente/aula-virtual/examenes`
- **Módulos que la activan:**
  - ✅ Módulo Docente (React) - **Ya implementado y funcionando**
  - ✅ Módulo Alumno (React) - **Funcionará cuando se implemente**

### 2.2. Condiciones para Habilitación Automática

Un examen se habilita automáticamente cuando se cumplen **TODAS** estas condiciones:

1. El examen está en estado `INACTIVO`
2. Tiene `fecha_desde` y `hora_desde` válidas (no `0000-00-00` ni `00:00:00`)
3. Tiene `fecha_hasta` y `hora_hasta` válidas (no `0000-00-00` ni `00:00:00`)
4. **La fecha/hora de inicio ya pasó** (al menos 1 minuto)
5. **La fecha/hora de fin aún no ha llegado**

### 2.3. Condiciones para Deshabilitación Automática

Un examen se deshabilita automáticamente cuando:

1. El examen está en estado `ACTIVO`
2. Tiene fechas/horas configuradas
3. **La fecha/hora de fin ya pasó**

---

## 3. Comportamiento al Crear/Editar Exámenes

### 3.1. Al Configurar Fechas/Horas

Cuando un docente crea o edita un examen y habilita "Fecha y hora":

- **El estado se fuerza automáticamente a `INACTIVO`**
- Esto asegura que el examen solo se active cuando llegue la hora configurada
- El docente no puede ponerlo en `ACTIVO` manualmente si tiene fechas/horas configuradas

**Ejemplo:**
```javascript
// Si el docente intenta crear un examen con:
{
  estado: 'ACTIVO',
  habilitar_fecha_hora: true,
  fecha_desde: '2026-02-03',
  hora_desde: '19:55',
  fecha_hasta: '2026-02-03',
  hora_hasta: '20:00'
}

// El backend automáticamente cambia:
{
  estado: 'INACTIVO', // ← Forzado automáticamente
  // ... resto de campos
}
```

### 3.2. Sin Fechas/Horas Configuradas

Si el examen **NO** tiene fechas/horas configuradas:

- El docente puede ponerlo en `ACTIVO` o `INACTIVO` manualmente
- No hay habilitación automática
- El examen se comporta como antes (control manual completo)

---

## 4. Implementación Técnica

### 4.1. Zona Horaria

La comparación de fechas/horas se realiza usando la zona horaria de Lima, Perú (`America/Lima`, UTC-5):

```javascript
const moment = require('moment-timezone');
const ahoraLima = moment.tz('America/Lima');
const fechaActual = ahoraLima.format('YYYY-MM-DD');
const horaActual = ahoraLima.format('HH:mm');
```

### 4.2. Comparación de Fechas

Se usa `moment-timezone` para comparaciones precisas:

```javascript
const fechaInicioMoment = moment.tz(`${fechaInicio} ${horaInicio}:00`, 'YYYY-MM-DD HH:mm:ss', 'America/Lima');
const fechaFinMoment = moment.tz(`${fechaFin} ${horaFin}:00`, 'YYYY-MM-DD HH:mm:ss', 'America/Lima');

const diferenciaInicioMs = ahoraLima.diff(fechaInicioMoment, 'milliseconds');
const diferenciaFinMs = fechaFinMoment.diff(ahoraLima, 'milliseconds');
```

### 4.3. Lógica de Activación

```javascript
// Solo habilitar si:
// 1. Pasó al menos 1 minuto desde la fecha/hora de inicio (diferenciaInicioMs >= 60000)
// 2. Aún no ha llegado la fecha/hora de fin (diferenciaFinMs > 0)
if (diferenciaInicioMs >= 60000 && diferenciaFinMs > 0) {
  // Activar examen
  await execute(
    `UPDATE asignaturas_examenes SET estado = 'ACTIVO' WHERE id = ?`,
    [examen.id]
  );
}
```

### 4.4. Logs de Debugging

El sistema incluye logs detallados para debugging:

```javascript
console.log(`🔍 [DEBUG] Examen ${examen.id} "${examen.titulo}":`, {
  fechaInicio: fechaInicioMoment.format('YYYY-MM-DD HH:mm:ss'),
  fechaFin: fechaFinMoment.format('YYYY-MM-DD HH:mm:ss'),
  ahora: ahoraLima.format('YYYY-MM-DD HH:mm:ss'),
  diferenciaInicioMs: diferenciaInicioMs,
  diferenciaFinMs: diferenciaFinMs,
  diferenciaInicioMin: (diferenciaInicioMs / 60000).toFixed(2),
  diferenciaFinMin: (diferenciaFinMs / 60000).toFixed(2)
});
```

---

## 5. Compatibilidad con Sistema PHP Anterior

### 5.1. Sistema PHP

El sistema PHP anterior **NO** utiliza las rutas de la API Node.js, por lo tanto:

- ❌ **NO** se activa automáticamente desde el sistema PHP
- ✅ El sistema PHP sigue funcionando como antes (sin habilitación automática)
- ✅ Los exámenes creados desde el sistema PHP pueden tener fechas/horas, pero requieren activación manual

### 5.2. Sistema Nuevo (React)

El sistema nuevo (React) **SÍ** utiliza las rutas de la API Node.js:

- ✅ **SÍ** se activa automáticamente cuando se consulta la lista de exámenes
- ✅ Funciona tanto en el módulo de Docente como en el módulo de Alumno (cuando se implemente)

---

## 6. Flujo de Ejecución

### 6.1. Consulta de Exámenes (Docente o Alumno)

```
1. Usuario (Docente/Alumno) consulta lista de exámenes
   ↓
2. Frontend React hace petición: GET /api/docente/aula-virtual/examenes
   ↓
3. Backend Node.js:
   a. Obtiene exámenes de la base de datos
   b. Para cada examen INACTIVO con fechas/horas válidas:
      - Calcula hora actual en Lima
      - Compara con fecha/hora de inicio y fin
      - Si está en rango → Actualiza estado a ACTIVO
      - Si pasó la fecha/hora de fin → Actualiza estado a INACTIVO
   c. Retorna lista de exámenes (con estados actualizados)
   ↓
4. Frontend muestra exámenes con estados correctos
```

### 6.2. Creación/Edición de Examen

```
1. Docente crea/edita examen y habilita "Fecha y hora"
   ↓
2. Frontend envía: POST/PUT /api/docente/aula-virtual/examenes
   ↓
3. Backend Node.js:
   a. Si habilitar_fecha_hora === 'SI' y estado === 'ACTIVO':
      - Fuerza estado a 'INACTIVO'
      - Guarda examen en base de datos
   b. Si habilitar_fecha_hora === 'NO':
      - Respeta el estado enviado por el docente
      - Guarda examen en base de datos
   ↓
4. Examen queda guardado con estado correcto
```

---

## 7. Ejemplos de Uso

### 7.1. Examen que se Activa Automáticamente

**Configuración:**
- Fecha desde: `2026-02-03`
- Hora desde: `19:55`
- Fecha hasta: `2026-02-03`
- Hora hasta: `20:00`
- Estado inicial: `INACTIVO` (forzado automáticamente)

**Comportamiento:**
- Antes de las 19:55 → Estado: `INACTIVO` (no se puede dar)
- A las 19:56 (1 minuto después) → Estado: `ACTIVO` (se puede dar)
- Después de las 20:00 → Estado: `INACTIVO` (ya no se puede dar)

### 7.2. Examen sin Fechas/Horas

**Configuración:**
- Habilitar fecha y hora: `NO`
- Estado: `ACTIVO` (control manual)

**Comportamiento:**
- Siempre respeta el estado manual del docente
- No hay habilitación automática
- El docente puede cambiar el estado cuando quiera

---

## 8. Archivos Relacionados

### 8.1. Backend

- **`backend/routes/docente.routes.js`**
  - Ruta: `GET /api/docente/aula-virtual/examenes` (líneas ~6132-6356)
  - Lógica de habilitación automática (líneas ~6170-6295)
  - Forzar INACTIVO al crear/editar (líneas ~6422-6427, ~6646-6651)

### 8.2. Frontend

- **`frontend/src/pages/DocenteAulaVirtual.jsx`**
  - Función `cargarExamenes` (línea ~319)
  - Formulario de creación/edición de exámenes

### 8.3. Dependencias

- **`backend/package.json`**
  - `moment-timezone`: ^0.5.45 (para manejo preciso de zonas horarias)

---

## 9. Troubleshooting

### 9.1. El Examen No Se Activa Automáticamente

**Posibles causas:**

1. **El examen no está en estado INACTIVO**
   - Verificar en base de datos: `SELECT estado FROM asignaturas_examenes WHERE id = ?`

2. **Las fechas/horas no son válidas**
   - Verificar: `SELECT fecha_desde, hora_desde, fecha_hasta, hora_hasta FROM asignaturas_examenes WHERE id = ?`
   - Deben ser diferentes de `0000-00-00` y `00:00:00`

3. **La fecha/hora de inicio aún no ha llegado**
   - Revisar logs del backend para ver la comparación de fechas
   - Verificar zona horaria del servidor

4. **La fecha/hora de fin ya pasó**
   - El examen se desactivará automáticamente si ya pasó la fecha/hora de fin

**Solución:**
- Revisar logs del backend: `pm2 logs intranet2026-backend --lines 50`
- Buscar logs con `[DEBUG] Examen` para ver la comparación de fechas

### 9.2. El Examen Se Activa Muy Temprano o Muy Tarde

**Causa:**
- Problema con la zona horaria del servidor

**Solución:**
- Verificar que `process.env.TZ = 'America/Lima'` esté configurado en `backend/server.js`
- Verificar logs de inicio del servidor para confirmar la zona horaria

---

## 10. Notas Importantes

1. **Margen de 1 minuto:** El examen se activa 1 minuto después de la hora de inicio para evitar problemas de sincronización.

2. **Solo en sistema nuevo:** La habilitación automática solo funciona cuando se consulta desde el sistema nuevo (React), no desde el sistema PHP anterior.

3. **Estado manual:** Si un examen está en `ACTIVO` manualmente (sin fechas/horas), el alumno puede darlo sin importar las restricciones de hora.

4. **Zona horaria:** Todas las comparaciones se realizan en la zona horaria de Lima, Perú (UTC-5).

---

## 11. Próximos Pasos

Cuando se implemente el módulo de Alumno en React:

1. El módulo de Alumno consultará exámenes a través de su propia ruta API
2. La habilitación automática funcionará automáticamente (misma lógica)
3. Los alumnos verán los exámenes activos según las fechas/horas configuradas

**No se requiere ningún cambio adicional** - la funcionalidad ya está implementada y lista para usar.

---

**Última actualización:** 2026-02-03  
**Versión:** 1.0

