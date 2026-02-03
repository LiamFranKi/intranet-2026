# 📋 Documentación: Campo VISTO en Tareas

## 🔍 Contexto

El campo `visto` en la tabla `asignaturas_tareas` se utiliza para rastrear qué alumnos han visto una tarea. Este campo se actualiza automáticamente cuando un alumno accede a los detalles de una tarea.

## 🗄️ Estructura en Base de Datos

### Tabla: `asignaturas_tareas`
- **Campo**: `visto`
- **Tipo**: `TEXT` (serializado en formato PHP)
- **Formato**: Array serializado de `alumno_id`
- **Ejemplo**: `a:2:{i:0;i:123;i:1;i:456;}` (alumnos con ID 123 y 456 han visto la tarea)

## 🔄 Funcionamiento en Sistema PHP Anterior

### Modelo: `Asignatura_Tarea.php`

```php
function setView($alumno_id){
   $data = !empty($this->visto) ? unserialize($this->visto) : array();
   if(!in_array($alumno_id, $data)){
       $data[] = $alumno_id;
       $this->visto = serialize($data);
   }
   return $this->save();
}

function getView($alumno_id){
   $data = !empty($this->visto) ? unserialize($this->visto) : array();
   return in_array($alumno_id, $data);
}
```

### Controlador: `asignaturas_tareas/index.php`

```php
function detalles(){
    if($this->USUARIO->is('ALUMNO')){
        $this->asignatura_tarea->setView($this->USUARIO->alumno_id);
        $matricula = Matricula::find_by_alumno_id_and_grupo_id($this->USUARIO->alumno_id, $this->asignatura_tarea->asignatura->grupo_id);
    }
    $this->render(['matricula' => $matricula]);
}
```

**Comportamiento**: Cuando un alumno accede a los detalles de una tarea, automáticamente se marca como vista agregando su `alumno_id` al array serializado.

## 🔧 Implementación en Sistema Nuevo (Node.js)

### Ruta API Creada

**Endpoint**: `POST /api/docente/aula-virtual/tareas/:tareaId/marcar-visto`

**Ubicación**: `backend/routes/docente.routes.js` (línea ~5525)

**Funcionalidad**:
1. Recibe `tareaId` y `alumno_id` (del token JWT)
2. Deserializa el campo `visto` actual
3. Si el `alumno_id` no está en el array, lo agrega
4. Serializa el array actualizado
5. Actualiza la base de datos

**Código**:
```javascript
router.post('/aula-virtual/tareas/:tareaId/marcar-visto', async (req, res) => {
  // Ver implementación completa en backend/routes/docente.routes.js
});
```

### Lectura del Campo VISTO

**Endpoint**: `GET /api/docente/aula-virtual/tareas/:tareaId/entregas`

**Ubicación**: `backend/routes/docente.routes.js` (línea ~5399)

**Funcionalidad**:
1. Deserializa el campo `visto` como array (no objeto)
2. Verifica si cada `alumno_id` está en el array
3. Retorna 'SI' o 'NO' para cada alumno

**Código relevante**:
```javascript
// Deserializar campo "visto" (formato PHP serialized)
// IMPORTANTE: El sistema PHP guarda un array de alumno_id, no un objeto
let vistos = [];
try {
  if (tareaInfo.visto && tareaInfo.visto !== '') {
    const phpSerialize = require('php-serialize');
    const deserialized = phpSerialize.unserialize(tareaInfo.visto);
    vistos = Array.isArray(deserialized) ? deserialized : [];
  }
} catch (error) {
  console.warn('Error deserializando campo visto:', error);
  vistos = [];
}

// Verificar si el alumno ha visto la tarea
const visto = Array.isArray(vistos) && vistos.includes(alumno.alumno_id) ? 'SI' : 'NO';
```

## 📝 Notas Importantes

### ⚠️ Diferencia Clave: `alumno_id` vs `matricula_id`

- **El campo `visto` usa `alumno_id`**, NO `matricula_id`
- Un alumno puede tener múltiples matrículas (años diferentes), pero el `alumno_id` es único
- El sistema PHP usa `$this->USUARIO->alumno_id` para marcar como vista

### 🔄 Compatibilidad con Sistema PHP

- El formato de serialización es **idéntico** al sistema PHP
- Usa el paquete `php-serialize` para mantener compatibilidad
- Los datos guardados por el sistema PHP se leen correctamente en el sistema nuevo
- Los datos guardados por el sistema nuevo se leen correctamente en el sistema PHP

## 🚀 Implementación Futura: Módulo Alumno (Frontend)

### Cuándo llamar a la ruta

La ruta `POST /api/docente/aula-virtual/tareas/:tareaId/marcar-visto` debe ser llamada cuando:

1. **Un alumno accede a los detalles de una tarea**
   - Al abrir el modal/detalles de una tarea
   - Al cargar la página de detalles de una tarea

2. **Momentos apropiados**:
   - En `useEffect` cuando se monta el componente de detalles
   - Al hacer click en "Ver Detalles" de una tarea
   - Al navegar a la página de detalles de una tarea

### Ejemplo de Implementación (Frontend)

```javascript
// En el componente de detalles de tarea del módulo alumno
import { useEffect } from 'react';
import api from '../services/api';

function DetallesTarea({ tareaId }) {
  useEffect(() => {
    // Marcar como vista cuando se cargan los detalles
    const marcarComoVista = async () => {
      try {
        await api.post(`/docente/aula-virtual/tareas/${tareaId}/marcar-visto`);
        console.log('✅ Tarea marcada como vista');
      } catch (error) {
        console.error('Error marcando tarea como vista:', error);
        // No mostrar error al usuario, es una operación silenciosa
      }
    };

    if (tareaId) {
      marcarComoVista();
    }
  }, [tareaId]);

  // ... resto del componente
}
```

### Consideraciones

1. **Operación silenciosa**: No mostrar errores al usuario si falla
2. **No bloquear UI**: La llamada debe ser asíncrona y no bloquear la carga
3. **Idempotente**: Puede llamarse múltiples veces sin problemas
4. **Solo para alumnos**: Verificar que el usuario sea tipo 'ALUMNO'

## 🔍 Verificación

### Consulta SQL para verificar

```sql
-- Ver tareas con alumnos que las han visto
SELECT 
  t.id,
  t.titulo,
  t.visto,
  -- Deserializar manualmente (solo para verificación)
  -- El campo visto contiene: a:2:{i:0;i:123;i:1;i:456;}
  -- Donde 123 y 456 son los alumno_id que han visto la tarea
FROM asignaturas_tareas t
WHERE t.visto IS NOT NULL AND t.visto != ''
LIMIT 10;
```

### Verificar en el sistema

1. Un alumno accede a los detalles de una tarea
2. El docente revisa "Marcar Entregas" de esa tarea
3. La columna "VISTO" debe mostrar "SI" para ese alumno

## 📚 Referencias

- **Sistema PHP**: `sistema-anterior/Main/Models/Asignatura_Tarea.php`
- **Sistema PHP**: `sistema-anterior/Main/Applications/asignaturas_tareas/index.php`
- **Sistema Nuevo**: `backend/routes/docente.routes.js` (líneas ~5456-5500, ~5525-5580)
- **Paquete npm**: `php-serialize` (ya instalado en el proyecto)

---

**Última actualización**: 2025-01-XX
**Estado**: ✅ Implementado y probado con sistema PHP anterior
**Pendiente**: Integración en módulo alumno del frontend

