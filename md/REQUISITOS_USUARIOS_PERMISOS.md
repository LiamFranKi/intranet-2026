# 👥 REQUISITOS DE USUARIOS Y PERMISOS - AULA VIRTUAL

## 🎯 TIPOS DE USUARIOS QUE PUEDEN ACCEDER

El sistema permitirá acceso a los siguientes tipos de usuarios (todos desde MySQL):

1. **ALUMNO** (Estudiante)
2. **DOCENTE** (Profesor)
3. **TUTOR/ASISTENTE** (Tutor de grado/aula)
4. **APODERADO** (Padre de familia)
5. **ADMINISTRADOR** (Control total)

---

## 📚 PERMISOS Y VISTAS POR TIPO DE USUARIO

### 1. 👨‍🎓 ALUMNO (Estudiante)

**Vista Principal:**
- ✅ Accede a su aula virtual personal
- ✅ Ve los cursos/asignaturas de su grado al que está matriculado
- ✅ Ve los profesores que tienen cada curso a cargo en su grado

**Por cada curso/asignatura:**
- ✅ **Temas**: Ver temas del curso
- ✅ **Enlaces de ayuda**: Ver enlaces de ayuda
- ✅ **Videos de ayuda**: Ver videos de ayuda
- ✅ **Tareas**: Ver sus tareas asignadas
- ✅ **Exámenes**: Ver sus exámenes (por curso, por grado)
- ✅ **Notas**: Ver sus calificaciones

**Restricciones:**
- ❌ NO puede crear contenido
- ❌ NO puede modificar nada
- ❌ Solo puede entregar tareas y tomar exámenes

---

### 2. 👨‍🏫 DOCENTE (Profesor)

**Vista Principal:**
- ✅ Ve sus grupos a cargo (puede tener varios grupos/grados)
- ✅ En cada grado ve sus diferentes cursos/asignaturas que enseña
- ✅ En cada grado puede ver la lista de estudiantes matriculados

**Funcionalidades:**
- ✅ **Crear contenido por curso:**
  - Temas
  - Prácticas
  - Teoría
  - Tema de ayuda
  - Enlace de ayuda
  - Video de ayuda
  - Exámenes
  - Tareas
- ✅ **Gestionar estudiantes:**
  - Ver lista de estudiantes por grado
  - Calificar tareas
  - Calificar exámenes
  - Registrar notas
- ✅ **Ver estadísticas:**
  - Rendimiento de estudiantes
  - Entregas de tareas
  - Resultados de exámenes

**Estructura de navegación:**
```
Docente
  └── Grupos a cargo
      └── Grado 1°A
          └── Cursos que enseña
              ├── Matemática
              │   ├── Estudiantes (lista)
              │   ├── Temas
              │   ├── Tareas
              │   ├── Exámenes
              │   └── Notas
              ├── Comunicación
              │   └── ...
              └── Ciencias
                  └── ...
      └── Grado 2°B
          └── ...
```

---

### 3. 👨‍💼 TUTOR/ASISTENTE (Tutor de grado/aula)

**Vista Principal:**
- ✅ Está a cargo de un grado/aula específico
- ✅ Puede ver todo el contenido del grado que supervisa

**Funcionalidades:**
- ✅ **Supervisión de contenido:**
  - Ver si el docente subió contenido
  - Ver si el docente subió tareas
  - Ver si el docente subió exámenes
  - Ver si el docente subió temas
- ✅ **Supervisión de estudiantes:**
  - Ver cuándo tiene notas cada estudiante
  - Ver rendimiento general del grado
  - Ver entregas de tareas
  - Ver resultados de exámenes
- ✅ **Reportes:**
  - Estado de contenido por curso
  - Estado de calificaciones
  - Actividad de estudiantes

**Restricciones:**
- ❌ NO puede crear contenido (solo ver)
- ❌ NO puede calificar (solo ver notas)
- ❌ Solo puede supervisar y ver reportes

---

### 4. 👨‍👩‍👧 APODERADO (Padre de familia)

**Vista Principal:**
- ✅ Puede tener uno o más hijos a cargo
- ✅ Puede ver cada uno de sus hijos
- ✅ Puede elegir un hijo y ver en "voz" de ese hijo (como si fuera el hijo)

**Funcionalidades:**
- ✅ **Selección de hijo:**
  - Lista de hijos a cargo
  - Seleccionar un hijo específico
- ✅ **Vista como hijo:**
  - Ver lo que el hijo ve (aula virtual del hijo)
  - Ver cursos del hijo
  - Ver temas, tareas, exámenes del hijo
- ✅ **Información académica:**
  - Ver notas del hijo
  - Ver lo que los profesores han dejado
  - Ver entregas de tareas
  - Ver resultados de exámenes

**Restricciones:**
- ❌ NO puede manipular nada
- ❌ NO puede crear contenido
- ❌ NO puede entregar tareas por el hijo
- ❌ NO puede tomar exámenes por el hijo
- ✅ Solo puede VER (modo lectura)

**Estructura de navegación:**
```
Apoderado
  └── Mis hijos
      ├── Hijo 1: Juan Pérez
      │   └── [Vista como si fuera Juan]
      │       └── Aula Virtual de Juan
      │           └── Cursos
      │               └── ...
      ├── Hijo 2: María Pérez
      │   └── [Vista como si fuera María]
      │       └── Aula Virtual de María
      │           └── ...
      └── Hijo 3: ...
```

---

### 5. 🔐 ADMINISTRADOR

**Vista Principal:**
- ✅ Control total del sistema
- ✅ Acceso a todas las funcionalidades

**Funcionalidades:**
- ✅ **Gestión completa:**
  - Agregar contenido
  - Eliminar contenido
  - Modificar contenido
  - Gestionar usuarios
  - Gestionar grupos
  - Gestionar cursos
- ✅ **Supervisión:**
  - Ver todo el contenido del sistema
  - Ver todas las notas
  - Ver todos los usuarios
  - Ver reportes completos
- ✅ **Configuración:**
  - Configurar sistema
  - Gestionar permisos
  - Gestionar estructura académica

**Sin restricciones:**
- ✅ Puede hacer TODO

---

## 🏗️ ESTRUCTURA DE DATOS - CONTENIDO POR CURSO

Cada curso/asignatura dentro de un grupo tendrá la siguiente estructura:

```
Grupo (Grado/Sección)
  └── Curso/Asignatura
      ├── 📚 Temas
      │   ├── Teoría
      │   └── Práctica
      ├── 📝 Tareas
      ├── 📋 Exámenes
      ├── 📊 Notas
      ├── 🔗 Enlaces de ayuda
      ├── 🎥 Videos de ayuda
      └── 📖 Tema de ayuda
```

### Detalles de cada sección:

**Temas:**
- Contenido teórico
- Contenido práctico
- Archivos adjuntos
- Organización por unidades

**Tareas:**
- Tareas asignadas por el docente
- Fechas de entrega
- Entregas de estudiantes
- Calificaciones

**Exámenes:**
- Exámenes creados por el docente
- Fechas de inicio y fin
- Resultados de estudiantes
- Calificaciones automáticas

**Notas:**
- Notas de exámenes
- Notas de tareas
- Promedios
- Historial

**Enlaces de ayuda:**
- Enlaces externos
- Recursos adicionales
- Material complementario

**Videos de ayuda:**
- Videos educativos
- Tutoriales
- Explicaciones

**Tema de ayuda:**
- Contenido de ayuda específico
- Guías
- Material de apoyo

---

## 🔄 FLUJO DE DATOS

### Lectura desde MySQL (Solo lectura):
- ✅ Usuarios (todos los tipos)
- ✅ Alumnos
- ✅ Apoderados
- ✅ Personal (docentes, tutores)
- ✅ Matrículas
- ✅ Grupos (grados/secciones)
- ✅ Cursos/asignaturas
- ✅ Relaciones docente-curso-grupo
- ✅ Relaciones apoderado-hijo (familias)

### Creación en React/PostgreSQL:
- ✅ Temas
- ✅ Tareas
- ✅ Exámenes
- ✅ Contenido de ayuda (enlaces, videos)
- ✅ Entregas de tareas
- ✅ Respuestas de exámenes
- ✅ Calificaciones (temporalmente)

### Exportación a MySQL vía API PHP:
- ✅ Notas finales
- ✅ Calificaciones
- ✅ Resultados de exámenes
- ✅ Resultados de tareas

---

## 📊 QUERIES IMPORTANTES PARA CADA USUARIO

### Para DOCENTE - Obtener sus grupos y cursos:

```sql
-- Obtener grupos donde el docente tiene cursos asignados
SELECT DISTINCT g.*, c.nombre as curso_nombre, c.id as curso_id
FROM grupos g
INNER JOIN cursos c ON c.grupo_id = g.id
WHERE c.personal_id = ? -- ID del docente
  AND g.colegio_id = ?
  AND g.anio = ? -- Año activo
ORDER BY g.grado, g.seccion;
```

### Para DOCENTE - Obtener estudiantes de un curso:

```sql
-- Obtener estudiantes matriculados en un grupo específico
SELECT a.*, m.id as matricula_id
FROM alumnos a
INNER JOIN matriculas m ON m.alumno_id = a.id
WHERE m.grupo_id = ? -- ID del grupo
  AND m.estado = 0 -- Activo
  AND m.colegio_id = ?
ORDER BY a.apellido_paterno, a.apellido_materno, a.nombres;
```

### Para ALUMNO - Obtener sus cursos:

```sql
-- Obtener cursos del alumno según su matrícula
SELECT c.*, g.grado, g.seccion, p.nombres as docente_nombres, 
       p.apellido_paterno as docente_apellido
FROM cursos c
INNER JOIN grupos g ON g.id = c.grupo_id
INNER JOIN matriculas m ON m.grupo_id = g.id
INNER JOIN personal p ON p.id = c.personal_id
WHERE m.alumno_id = ? -- ID del alumno
  AND m.estado = 0 -- Matrícula activa
  AND g.anio = ? -- Año activo
  AND m.colegio_id = ?
ORDER BY c.nombre;
```

### Para APODERADO - Obtener sus hijos:

```sql
-- Obtener hijos del apoderado
SELECT a.*, m.grupo_id, g.grado, g.seccion
FROM alumnos a
INNER JOIN familias f ON f.alumno_id = a.id
INNER JOIN matriculas m ON m.alumno_id = a.id
INNER JOIN grupos g ON g.id = m.grupo_id
WHERE f.apoderado_id = ? -- ID del apoderado
  AND m.estado = 0 -- Matrícula activa
  AND g.anio = ? -- Año activo
  AND a.colegio_id = ?
ORDER BY a.apellido_paterno, a.apellido_materno, a.nombres;
```

### Para TUTOR - Obtener grupos a cargo:

```sql
-- Obtener grupos donde el personal es tutor
SELECT g.*
FROM grupos g
WHERE g.tutor_id = ? -- ID del tutor
  AND g.colegio_id = ?
  AND g.anio = ? -- Año activo
ORDER BY g.grado, g.seccion;
```

---

## 🎨 INTERFAZ DE USUARIO - CONSIDERACIONES

### Navegación por tipo de usuario:

**ALUMNO:**
```
Dashboard
  └── Mis Cursos
      └── [Curso]
          ├── Temas
          ├── Tareas
          ├── Exámenes
          ├── Notas
          ├── Enlaces de ayuda
          └── Videos de ayuda
```

**DOCENTE:**
```
Dashboard
  └── Mis Grupos
      └── [Grado/Sección]
          └── Mis Cursos
              └── [Curso]
                  ├── Estudiantes
                  ├── Temas (crear/editar)
                  ├── Tareas (crear/editar)
                  ├── Exámenes (crear/editar)
                  ├── Notas (calificar)
                  ├── Enlaces de ayuda (crear/editar)
                  └── Videos de ayuda (crear/editar)
```

**APODERADO:**
```
Dashboard
  └── Mis Hijos
      └── [Seleccionar Hijo]
          └── [Vista como hijo]
              └── Aula Virtual del hijo
                  └── (Misma vista que alumno, pero solo lectura)
```

**TUTOR:**
```
Dashboard
  └── Mi Grado
      └── [Grado/Sección]
          ├── Cursos
          │   └── [Curso]
          │       ├── Estado de contenido
          │       ├── Estudiantes y notas
          │       └── Reportes
          └── Reporte general
```

**ADMINISTRADOR:**
```
Dashboard
  ├── Gestión de usuarios
  ├── Gestión de grupos
  ├── Gestión de cursos
  ├── Supervisión de contenido
  ├── Reportes completos
  └── Configuración
```

---

## ✅ RESUMEN DE PERMISOS

| Funcionalidad | Alumno | Docente | Tutor | Apoderado | Admin |
|--------------|--------|---------|-------|-----------|-------|
| Ver temas | ✅ | ✅ | ✅ | ✅ | ✅ |
| Crear temas | ❌ | ✅ | ❌ | ❌ | ✅ |
| Ver tareas | ✅ | ✅ | ✅ | ✅ | ✅ |
| Crear tareas | ❌ | ✅ | ❌ | ❌ | ✅ |
| Entregar tareas | ✅ | ❌ | ❌ | ❌ | ✅ |
| Ver exámenes | ✅ | ✅ | ✅ | ✅ | ✅ |
| Crear exámenes | ❌ | ✅ | ❌ | ❌ | ✅ |
| Tomar exámenes | ✅ | ❌ | ❌ | ❌ | ✅ |
| Ver notas | ✅ | ✅ | ✅ | ✅ | ✅ |
| Calificar | ❌ | ✅ | ❌ | ❌ | ✅ |
| Ver estudiantes | ❌ | ✅ | ✅ | ❌ | ✅ |
| Supervisar contenido | ❌ | ❌ | ✅ | ❌ | ✅ |
| Eliminar contenido | ❌ | ✅ | ❌ | ❌ | ✅ |
| Control total | ❌ | ❌ | ❌ | ❌ | ✅ |

---

## 🔐 VALIDACIONES IMPORTANTES

1. **Alumno solo ve sus propios datos:**
   - Solo ve cursos de su matrícula activa
   - Solo ve sus propias tareas y exámenes
   - Solo ve sus propias notas

2. **Docente solo ve sus grupos/cursos:**
   - Solo ve grupos donde tiene cursos asignados
   - Solo ve estudiantes de sus grupos
   - Solo puede crear contenido en sus cursos

3. **Apoderado solo ve hijos a cargo:**
   - Solo ve hijos relacionados en tabla `familias`
   - Solo puede ver, no puede modificar
   - Vista es "como si fuera el hijo"

4. **Tutor solo ve su grado:**
   - Solo ve grupos donde es tutor (`grupos.tutor_id`)
   - Solo puede ver, no puede crear contenido
   - Puede ver reportes y estadísticas

5. **Administrador ve todo:**
   - Acceso sin restricciones
   - Puede ver y modificar todo

---

**Este documento debe ser consultado durante toda la implementación para asegurar que cada tipo de usuario tenga los permisos y vistas correctas.** 📚

