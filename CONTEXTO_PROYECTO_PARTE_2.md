# 📚 CONTEXTO DEL PROYECTO - PARTE 2: MÓDULOS Y FUNCIONALIDADES

**Fecha de Documentación:** Enero 2026  
**Estado:** Desarrollo Activo  
**Versión:** 1.0.0 - Parte 2

---

## 📋 ÍNDICE

1. [Módulos Implementados](#módulos-implementados)
2. [Funcionalidades por Módulo](#funcionalidades-por-módulo)
3. [Widgets y Componentes](#widgets-y-componentes)
4. [Sistema de Publicaciones](#sistema-de-publicaciones)
5. [Sistema de Calendario](#sistema-de-calendario)

---

## 🎓 MÓDULOS IMPLEMENTADOS

### 1. Módulo de Administrador

**Estado:** ✅ Implementado

**Rutas:**
- `/dashboard` - Dashboard principal
- `/aula` - Aula virtual
- `/auditoria` - Logs de auditoría

**Funcionalidades:**
- Dashboard con estadísticas
- Visualización de auditoría
- Gestión del sistema

---

### 2. Módulo de Docente

**Estado:** ✅ Completamente Implementado

**Rutas:**
- `/docente/dashboard` - Dashboard del docente
- `/docente/perfil` - Perfil y edición
- `/docente/grupos` - Grupos asignados
- `/docente/cursos` - Cursos asignados
- `/docente/horario` - Horario semanal
- `/docente/tutoria` - Tutoría de grupos
- `/docente/comunicados` - Comunicados
- `/docente/actividades` - Calendario de actividades
- `/docente/mensajes` - Mensajería
- `/docente/cursos/:cursoId/aula` - Aula virtual por curso

---

### 3. Módulo de Alumno

**Estado:** 🔄 Parcialmente Implementado

**Rutas:**
- `/alumno/dashboard` - Dashboard del alumno
- `/alumno/perfil` - Perfil del alumno
- `/alumno/cursos` - Mis cursos
- `/alumno/calificaciones` - Calificaciones
- `/alumno/horario` - Mi horario
- `/alumno/aula-virtual` - Aula virtual
- `/alumno/comunicados` - Comunicados
- `/alumno/actividades` - Actividades
- `/alumno/mensajes` - Mensajería

**Funcionalidades:**
- Dashboard básico
- Perfil con foto
- Estructura de rutas lista

---

## 🔧 FUNCIONALIDADES POR MÓDULO

### Dashboard del Docente

**Componente:** `DocenteDashboard.jsx`

**Funcionalidades:**
- ✅ Estadísticas: Cursos asignados, Estudiantes, Tardanzas del mes
- ✅ Sección unificada "Próximos Eventos" (reemplaza Exámenes y Tareas separadas)
- ✅ Grid de 4 columnas con paginación (8 eventos por página)
- ✅ Cards diferenciadas por color según tipo (Examen/Tarea/Actividad)
- ✅ Modal de detalles de eventos (`EventoDetalleModal`)
- ✅ Widgets: Calendario, Publicaciones, Notificaciones
- ✅ Diseño centrado y limpio (sin tarjeta de bienvenida)

**Tarjetas de Estadísticas:**
- `estadisticas.cursosAsignados` - Número de cursos asignados
- `estadisticas.estudiantes` - Número total de estudiantes
- `Tardanzas de [Mes]` - Tardanzas del mes actual (valor inicial: 0, pendiente implementación)

**Sección Próximos Eventos:**
- Combina exámenes, tareas y actividades en una sola vista
- Solo muestra eventos futuros (fecha >= hoy)
- Grid responsive: 4 columnas (desktop), 3 (tablet), 2 (móvil), 1 (pequeño)
- Paginación: 8 eventos por página (2 filas × 4 columnas)
- Cada card muestra:
  - Fecha (día y mes)
  - Título completo (sin truncar)
  - Tipo de evento (Examen/Tarea/Actividad)
  - Color diferenciado por tipo:
    - **Examen**: Gradiente naranja/amarillo
    - **Tarea**: Gradiente verde
    - **Actividad**: Gradiente azul

**Modal EventoDetalleModal:**
- Muestra detalles completos del evento seleccionado
- Información específica según tipo:
  - **Exámenes**: Título, fecha, asignatura, grupo
  - **Tareas**: Descripción, fecha límite, asignatura, grupo
  - **Actividades**: Descripción, fecha, hora, lugar, detalles
- Header con color según tipo de evento

**Datos mostrados:**
- `proximosExamenes[]` - Array de exámenes futuros (sin límite de días)
- `proximasTareas[]` - Array de tareas futuras (sin límite de días)
- `actividades[]` - Array de actividades futuras del colegio

---

### Perfil del Docente

**Componente:** `DocentePerfil.jsx`

**Funcionalidades:**
- ✅ Ver y editar datos personales
- ✅ Subir/actualizar foto de perfil
- ✅ Visualización de foto con URL completa
- ✅ Construcción correcta de URLs (desarrollo/producción)

**Endpoints:**
- `GET /api/docente/perfil` - Obtener perfil
- `PUT /api/docente/perfil` - Actualizar perfil (con foto)

---

### Grupos Asignados

**Componente:** `DocenteGrupos.jsx`

**Funcionalidades:**
- ✅ Lista de grupos asignados al docente
- ✅ Ver estudiantes por grupo
- ✅ Información: Nivel, Grado, Sección

**Endpoints:**
- `GET /api/docente/grupos` - Lista de grupos
- `GET /api/docente/grupos/:grupoId/alumnos` - Estudiantes del grupo

---

### Cursos Asignados

**Componente:** `DocenteCursos.jsx`

**Funcionalidades:**
- ✅ Lista de cursos asignados
- ✅ Información del curso
- ✅ Relación con grupos

**Endpoints:**
- `GET /api/docente/cursos` - Lista de cursos

---

### Horario

**Componente:** `DocenteHorario.jsx`

**Funcionalidades:**
- ✅ Horario semanal del docente
- ✅ Tabla `grupos_horarios` (NO `personal_horario`)

**Endpoints:**
- `GET /api/docente/horario` - Horario semanal

---

## 🧩 WIDGETS Y COMPONENTES

### PublicacionesWidget

**Componente:** `PublicacionesWidget.jsx`

**Funcionalidades Implementadas:**

#### Crear Publicaciones
- ✅ Crear publicaciones con texto
- ✅ Subir imágenes (desde archivo o cámara)
- ✅ Subir archivos adjuntos (PDF, DOC, etc.)
- ✅ Compartir con "Todos" o grupos específicos
- ✅ Detección de dispositivo (móvil vs desktop)
- ✅ Acceso a cámara/webcam con fallback a upload

#### Ver Publicaciones
- ✅ Feed de publicaciones tipo Facebook
- ✅ Foto del autor o placeholder con iniciales
- ✅ Nombre completo del autor
- ✅ Fecha y hora de publicación
- ✅ Contenido completo (sin limitación de scroll)
- ✅ Imágenes con modal para ver en grande
- ✅ Archivos adjuntos descargables
- ✅ Información "Para" (grupos o "Todos")
- ✅ Botón eliminar (solo para el autor)

#### Paginación
- ✅ Mostrar 5 publicaciones inicialmente
- ✅ Botón "Cargar Más" cada 5 publicaciones
- ✅ Contenedor dinámico que crece

#### Diseño
- ✅ Icono de eliminar pequeño y discreto
- ✅ Nombre del autor en azul oscuro
- ✅ Tamaño de fuente ajustado
- ✅ Sin badge de archivos adjuntos
- ✅ Botón "Cargar Más" con estilo primario

**Endpoints:**
- `GET /api/docente/publicaciones` - Obtener feed
- `POST /api/docente/publicaciones` - Crear publicación
- `DELETE /api/docente/publicaciones/:id` - Eliminar publicación

**Formato de Datos:**
- `images`: Serializado como PHP `base64_encode(serialize(array))`
- `archivos`: Serializado como PHP `base64_encode(serialize(array))`
- `privacidad`: "-1" = Todos, IDs de grupos separados por comas

---

### EventoDetalleModal

**Componente:** `EventoDetalleModal.jsx`

**Funcionalidades Implementadas:**
- ✅ Modal para mostrar detalles completos de eventos
- ✅ Header con color según tipo de evento
- ✅ Información específica según tipo:
  - **Exámenes**: Título, fecha del examen, asignatura, grupo
  - **Tareas**: Descripción, fecha límite, asignatura, grupo
  - **Actividades**: Descripción, fecha, hora, lugar, detalles
- ✅ Diseño responsive y animaciones
- ✅ Cierre al hacer clic fuera del modal o en botón X

**Uso:**
- Se abre al hacer clic en cualquier card de evento en el Dashboard
- Recibe props: `evento`, `tipo` ('examen'|'tarea'|'actividad'), `onClose`

---

### CalendarioWidget

**Componente:** `CalendarioWidget.jsx`

**Funcionalidades Implementadas:**

#### Visualización
- ✅ Calendario mensual interactivo
- ✅ Navegación entre meses (‹ ›)
- ✅ Botón "Hoy" para ir al día actual
- ✅ Días con eventos marcados visualmente (fondo azul claro)
- ✅ Indicador de punto azul en días con eventos
- ✅ Día actual destacado (gradiente azul)
- ✅ Día seleccionado destacado (borde azul)

#### Eventos
- ✅ Muestra eventos de TODOS los años (sin restricción de año activo)
- ✅ Detecta eventos de múltiples días (rango de fechas)
- ✅ Marca todos los días que pertenecen a un evento
- ✅ Tooltip con cantidad de eventos al pasar el mouse

#### Modal de Eventos
- ✅ Se abre al hacer clic en un día con eventos
- ✅ Muestra TODOS los eventos del día seleccionado
- ✅ Lista expandible de eventos
- ✅ Click en evento para ver detalles completos
- ✅ Muestra: hora, lugar, detalles, rango de fechas
- ✅ Contador de eventos en el header
- ✅ Diseño limpio y organizado

**Endpoints:**
- `GET /api/docente/actividades` - Obtener todas las actividades (sin filtro de año)

**Notas Importantes:**
- ❌ NO filtra por año activo - Muestra eventos de todos los años
- ✅ Compara rangos de fechas correctamente
- ✅ Normaliza fechas para comparación (solo día, mes, año)

---

### NotificacionesWidget

**Componente:** `NotificacionesWidget.jsx`

**Funcionalidades:**
- ✅ Lista de notificaciones recibidas
- ✅ Estado: ENVIADO/NO ENVIADO

**Endpoints:**
- `GET /api/docente/notificaciones` - Obtener notificaciones

---

### EventoModal

**Componente:** `EventoModal.jsx`

**Funcionalidades:**
- ✅ Muestra todos los eventos de un día
- ✅ Lista vertical de eventos
- ✅ Expandir/colapsar detalles al hacer clic
- ✅ Información completa: hora, lugar, detalles, fechas
- ✅ Diseño responsive y accesible

---

## 📢 SISTEMA DE PUBLICACIONES

### Estructura de Datos

**Tabla:** `publicaciones`

```sql
- id (PK)
- colegio_id
- usuario_id (FK)
- contenido (text)
- images (text) - base64_encode(serialize(array))
- archivos (text) - base64_encode(serialize(array))
- privacidad (varchar) - "-1" = Todos, IDs de grupos separados por comas
- fecha_hora (datetime)
```

### Serialización PHP Legacy

**Formato de `images` y `archivos`:**
- PHP: `base64_encode(serialize(array))`
- Node.js: Usa librería `php-serialize` o implementación propia
- Al guardar: Serializar array a formato PHP
- Al leer: Deserializar desde formato PHP

### Privacidad

**Valores:**
- `"-1"` = Todos
- `"1,2,3"` = Grupos específicos (IDs separados por comas)
- `"-2"` = Personal Administrativo (si se implementa)

### Uploads

**Rutas:**
- Imágenes: `/uploads/publicaciones/`
- Archivos: `/uploads/archivos/`
- Personal: `/uploads/personal/`
- Alumnos: `/uploads/alumnos/`

**Multer Configuration:**
- Límite de tamaño: 50MB para archivos, 10MB para imágenes
- Tipos permitidos: JPEG, JPG, PNG, GIF, WEBP (imágenes)
- Tipos permitidos: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, ZIP, RAR (archivos)

---

## 📅 SISTEMA DE CALENDARIO

### Estructura de Datos

**Tabla:** `actividades`

```sql
- id (PK)
- colegio_id
- descripcion (varchar 500)
- lugar (varchar 500)
- detalles (text)
- fecha_inicio (datetime)
- fecha_fin (datetime)
- usuario_id (FK)
```

### Funcionalidad

**Carga de Eventos:**
- ✅ Sin restricción de año activo
- ✅ Muestra eventos de todos los años
- ✅ Filtra solo por `colegio_id`

**Detección de Eventos:**
- ✅ Compara rangos de fechas correctamente
- ✅ Marca todos los días dentro del rango
- ✅ Normaliza fechas (solo día, mes, año)

**Visualización:**
- ✅ Días con eventos: fondo azul claro + borde azul
- ✅ Indicador de punto azul
- ✅ Tooltip con cantidad de eventos

**Modal:**
- ✅ Lista todos los eventos del día
- ✅ Expandible al hacer clic
- ✅ Muestra detalles completos

---

## 🎨 DISEÑO Y UI/UX

### Paleta de Colores

**Colores Principales:**
- Primary: `#667eea` (Azul púrpura)
- Secondary: `#764ba2` (Púrpura oscuro)
- Accent: `#ff6f00` (Naranja)

**Colores del Sistema:**
- Botón Primary: `linear-gradient(135deg, #6ba3d4 0%, #4a83c1 100%)`
- Texto Principal: `#1f2937`
- Texto Secundario: `#6b7280`
- Fondo: `#f5f5f5`
- Azul Oscuro (nombres): `#1e40af`

### Componentes de Diseño

#### Botones
- `.btn-primary` - Gradiente azul, texto blanco
- `.btn-secondary` - Fondo gris claro, texto azul
- `.btn-outline` - Borde azul, fondo transparente
- `.btn-ghost` - Sin borde ni fondo
- `.btn-block` - Ancho completo
- `.btn-sm`, `.btn-lg` - Tamaños

#### Cards
- Fondo: `linear-gradient(145deg, #ffffff, #f9fafb)`
- Border-radius: `16px`
- Sombra: `0 2px 8px rgba(0, 0, 0, 0.06)`
- Hover: Elevación y sombra aumentada

#### Iconos
- Iconos grandes (módulos): `4rem` (64px)
- Iconos en botones: `1.2rem` (19.2px)
- Iconos en sidebar: `1rem` (16px)

---

## 📝 NOTAS IMPORTANTES

### Base de Datos

⚠️ **CAMBIOS CRÍTICOS:**
- ❌ **NO se usa PostgreSQL** - Todo es MySQL
- ✅ **NO existe `areas_cursos`** - `asignaturas` se relaciona directamente con `grupos`
- ✅ **`asignaturas.anio` NO existe** - El año viene de `grupos.anio`
- ✅ **`asignaturas_examenes.fecha_inicio` NO existe** - Usar `fecha_desde`
- ✅ **`asignaturas_actividades.fecha_limite` NO existe** - Usar `fecha_fin`
- ✅ **`asignaturas_examenes.nombre` NO existe** - Usar `titulo`
- ✅ **`asignaturas_actividades.titulo` NO existe** - Usar `descripcion`

### Serialización PHP

**Importante:**
- Las publicaciones usan formato PHP legacy para `images` y `archivos`
- Debe mantenerse compatibilidad con sistema anterior
- Usar `base64_encode(serialize(array))` al guardar
- Deserializar al leer

### Calendario

**Sin Restricción de Año:**
- El calendario muestra eventos de TODOS los años
- No filtra por `anio_activo`
- Permite ver eventos históricos y futuros

---

**Ver también:**
- `CONTEXTO_PROYECTO_PARTE_1.md` - Fundamentos y arquitectura
- `CONTEXTO_PROYECTO_PARTE_3.md` - API y endpoints detallados

---

**Última Actualización:** Enero 2026  
**Versión del Documento:** 1.0.0 - Parte 2  
**Mantenido por:** Equipo de Desarrollo

