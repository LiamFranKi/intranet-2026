# 📚 PLAN COMPLETO - REACT AULA VIRTUAL (Desde Cero)

## 🎯 OBJETIVO PRINCIPAL

**Crear un sistema de Aula Virtual en React/Node.js que se integre con el sistema PHP existente, leyendo datos maestros de MySQL y creando contenido académico interactivo.**

---

## 📋 CONTEXTO DEL SISTEMA PHP EXISTENTE

### Sistema PHP/MySQL (Ya Funcionando)
- ✅ **Multicolegio**: Soporta múltiples colegios
- ✅ **Gestión de Usuarios**: Alumnos, apoderados, docentes, personal
- ✅ **Matrículas**: Control de matrículas por año académico
- ✅ **Pagos y Deudas**: Sistema completo de pagos, pensiones, control de deudas
- ✅ **Facturación**: Boletas electrónicas, productos (buzos, uniformes)
- ✅ **Sistema Bancario**: Envío/recepción de archivos .txt para conciliación
- ✅ **Control de Acceso**: Bloquea usuarios con deudas (alumnos y apoderados)
- ✅ **Grados y Secciones**: Estructura académica completa
- ✅ **Cursos/Asignaturas**: Cursos definidos por grado
- ✅ **Personal Docente**: Docentes asignados a cursos

### Base de Datos MySQL (Estructura Existente)
- `usuarios`: Usuarios del sistema (alumnos, apoderados, docentes)
- `alumnos`: Datos de alumnos
- `apoderados`: Datos de apoderados
- `personal`: Datos de personal/docentes
- `matriculas`: Matrículas activas por año
- `grupos`: Grados y secciones (1°A, 2°B, etc.)
- `cursos`: Cursos/asignaturas (Matemática, Comunicación, etc.)
- `pagos`: Pagos y deudas
- `colegios`: Datos de colegios (multicolegio)
- Y muchas más tablas...

---

## 🚀 SISTEMA REACT - AULA VIRTUAL

### ¿Qué hará React?
**SOLO AULA VIRTUAL - Contenido Académico Interactivo**

React será responsable de:
- ✅ **Exámenes en línea**: Crear, gestionar y tomar exámenes
- ✅ **Tareas/Deberes**: Asignar y entregar tareas
- ✅ **Temas/Contenido**: Crear temas de estudio interactivos
- ✅ **Calificaciones**: Registrar notas de exámenes y tareas
- ✅ **Interfaz Interactiva**: UI moderna con PWA y notificaciones
- ✅ **Gamificación**: Sistema de puntos, logros, rankings

### ¿Qué NO hará React?
- ❌ **NO gestionará pagos** (PHP lo hace)
- ❌ **NO gestionará matrículas** (PHP lo hace)
- ❌ **NO gestionará facturación** (PHP lo hace)
- ❌ **NO gestionará productos** (PHP lo hace)
- ❌ **NO gestionará usuarios** (PHP lo hace, React solo lee)

---

## 🔌 ARQUITECTURA DE INTEGRACIÓN

### **Opción B: Lectura Directa + Escritura vía API** ⭐

```
┌─────────────────────────────────────────────────────────┐
│              MySQL (Base de Datos Compartida)           │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐ │
│  │   Usuarios   │  │   Alumnos    │  │  Matrículas │ │
│  │   Pagos      │  │   Grupos     │  │  Cursos     │ │
│  │   Deudas     │  │   Personal   │  │  Colegios   │ │
│  └──────────────┘  └──────────────┘  └──────────────┘ │
└─────────────────────────────────────────────────────────┘
         ▲                        ▲
         │                        │
    ┌────┴────┐            ┌────┴────┐
    │   PHP   │            │  Node   │
    │ (R/W)   │            │ (R/O)   │
    │         │            │         │
    │ - Login │            │ - Lee   │
    │ - Pagos │            │   datos │
    │ - Matrí-│            │   maestros│
    │   culas │            │         │
    └─────────┘            └─────────┘
         ▲                        │
         │                        │
         └──────────API───────────┘
         (Escritura de Notas)
```

### Flujo de Datos

#### 1. **LECTURA (Node.js → MySQL Directo)**
Node.js lee directamente de MySQL:
- ✅ Usuarios (para login y validación)
- ✅ Alumnos (datos de estudiantes)
- ✅ Apoderados (datos de padres)
- ✅ Matrículas (matrículas activas)
- ✅ Grupos (grados y secciones)
- ✅ Cursos (asignaturas por grado)
- ✅ Personal (docentes asignados)
- ✅ Deudas (para control de acceso)
- ✅ Colegios (datos del colegio)

**Usuario MySQL**: Solo lectura (SELECT únicamente)

#### 2. **ESCRITURA (Node.js → PHP API)**
Node.js escribe a través de API REST de PHP:
- ✅ Notas de exámenes
- ✅ Notas de tareas
- ✅ Calificaciones finales

**PHP valida y controla** toda la escritura de datos académicos.

---

## 🔐 SISTEMA DE AUTENTICACIÓN

### Flujo de Login

```
1. Usuario ingresa DNI en React
   ↓
2. React consulta MySQL directamente:
   SELECT * FROM usuarios WHERE usuario = 'DNI'
   ↓
3. React valida password (SHA1 como PHP)
   ↓
4. React verifica deudas en MySQL:
   SELECT ... FROM pagos WHERE estado_pago = 'PENDIENTE'
   ↓
5. Si tiene deudas → BLOQUEAR ACCESO
   Si NO tiene deudas → PERMITIR ACCESO
   ↓
6. React genera token JWT y permite acceso
```

### Control de Acceso por Deudas

**React lee directamente de MySQL:**
```sql
-- Verificar deudas del alumno
SELECT p.* FROM pagos p
INNER JOIN matriculas m ON m.id = p.matricula_id
WHERE m.alumno_id = ? 
  AND p.estado_pago = 'PENDIENTE'
  AND m.estado = 0
  AND m.grupo_id IN (
    SELECT id FROM grupos WHERE anio = ?
  );

-- Verificar deudas del apoderado (hijos)
SELECT p.* FROM pagos p
INNER JOIN matriculas m ON m.id = p.matricula_id
INNER JOIN alumnos a ON a.id = m.alumno_id
INNER JOIN familias f ON f.alumno_id = a.id
WHERE f.apoderado_id = ?
  AND p.estado_pago = 'PENDIENTE'
  AND m.estado = 0;
```

**Si tiene deudas:**
- ❌ Bloquear acceso al aula virtual
- ❌ Mostrar mensaje: "Acceso bloqueado por deudas pendientes"
- ❌ Redirigir a página de información

**Si NO tiene deudas:**
- ✅ Permitir acceso completo
- ✅ Mostrar dashboard del aula virtual

---

## 📚 ESTRUCTURA DE DATOS QUE REACT LEERÁ

### 1. **Usuarios y Autenticación**
```sql
-- Tabla: usuarios
- id
- usuario (DNI)
- password (SHA1)
- tipo (ALUMNO, APODERADO, DOCENTE, etc.)
- estado (ACTIVO, INACTIVO)
- colegio_id
- alumno_id (si es alumno)
- apoderado_id (si es apoderado)
- personal_id (si es docente)
```

### 2. **Alumnos**
```sql
-- Tabla: alumnos
- id
- nombres
- apellido_paterno
- apellido_materno
- nro_documento (DNI)
- email
- foto
- colegio_id
```

### 3. **Matrículas**
```sql
-- Tabla: matriculas
- id
- alumno_id
- grupo_id (grado y sección)
- estado (0=activo, 1=inactivo)
- fecha_registro
- colegio_id
```

### 4. **Grupos (Grados y Secciones)**
```sql
-- Tabla: grupos
- id
- grado (1, 2, 3, etc.)
- seccion (A, B, C, etc.)
- nivel_id (Inicial, Primaria, Secundaria)
- anio (2025, 2026, etc.)
- tutor_id (docente tutor)
- colegio_id
```

### 5. **Cursos/Asignaturas**
```sql
-- Tabla: cursos
- id
- nombre (Matemática, Comunicación, etc.)
- grupo_id (a qué grado pertenece)
- personal_id (docente asignado)
- colegio_id
```

### 6. **Personal (Docentes)**
```sql
-- Tabla: personal
- id
- nombres
- apellido_paterno
- apellido_materno
- nro_documento
- email
- colegio_id
```

### 7. **Pagos y Deudas**
```sql
-- Tabla: pagos
- id
- matricula_id
- nro_pago
- monto
- estado_pago (CANCELADO, PENDIENTE)
- fecha_hora
- tipo (0=matrícula, 1=pensión, etc.)
```

---

## 🎓 FUNCIONALIDADES DEL AULA VIRTUAL

### 1. **Exámenes en Línea**

**React crea y gestiona:**
- ✅ Crear examen (docente)
- ✅ Preguntas de opción múltiple
- ✅ Preguntas de verdadero/falso
- ✅ Preguntas de respuesta corta
- ✅ Tiempo límite
- ✅ Intentos permitidos
- ✅ Fecha de inicio y fin
- ✅ Asignar a grupos/cursos específicos

**Alumno toma examen:**
- ✅ Interfaz interactiva
- ✅ Bloqueo de pantalla (no puede salir)
- ✅ Temporizador visible
- ✅ Guardado automático
- ✅ Envío de respuestas

**React guarda resultados:**
- ✅ Respuestas del alumno
- ✅ Calificación automática
- ✅ Nota final
- ✅ Exporta a PHP vía API

### 2. **Tareas/Deberes**

**React crea y gestiona:**
- ✅ Crear tarea (docente)
- ✅ Descripción de la tarea
- ✅ Archivos adjuntos (PDF, Word, etc.)
- ✅ Fecha de entrega
- ✅ Puntos asignados
- ✅ Asignar a grupos/cursos

**Alumno entrega tarea:**
- ✅ Subir archivos
- ✅ Escribir respuesta en línea
- ✅ Ver fecha límite
- ✅ Confirmar entrega

**Docente califica:**
- ✅ Ver entregas
- ✅ Calificar (nota)
- ✅ Comentarios
- ✅ Exporta nota a PHP vía API

### 3. **Temas/Contenido**

**React crea y gestiona:**
- ✅ Crear tema (docente)
- ✅ Contenido interactivo (texto, imágenes, videos)
- ✅ Organizar por cursos
- ✅ Fechas de publicación
- ✅ Archivos adjuntos

**Alumno accede:**
- ✅ Ver temas del curso
- ✅ Descargar materiales
- ✅ Ver videos
- ✅ Navegación intuitiva

### 4. **Calificaciones**

**React gestiona:**
- ✅ Notas de exámenes
- ✅ Notas de tareas
- ✅ Promedio del curso
- ✅ Historial de calificaciones

**Exportación a PHP:**
- ✅ API REST para enviar notas
- ✅ Formato compatible con tabla `notas` de PHP
- ✅ Sincronización automática

---

## 🗄️ BASE DE DATOS PARA REACT

### PostgreSQL (Solo para Aula Virtual)

React usará PostgreSQL para almacenar:
- ✅ Exámenes creados
- ✅ Preguntas de exámenes
- ✅ Respuestas de alumnos
- ✅ Tareas creadas
- ✅ Entregas de tareas
- ✅ Temas/Contenido
- ✅ Calificaciones (antes de exportar a PHP)

**NO almacenará:**
- ❌ Usuarios (lee de MySQL)
- ❌ Alumnos (lee de MySQL)
- ❌ Matrículas (lee de MySQL)
- ❌ Pagos (lee de MySQL)

---

## 🔄 FLUJO COMPLETO DE TRABAJO

### Escenario 1: Docente crea un examen

```
1. Docente inicia sesión en React
   ↓
2. React valida usuario en MySQL
   ↓
3. React verifica deudas (si tiene)
   ↓
4. Docente accede al aula virtual
   ↓
5. Docente crea examen:
   - Título, descripción
   - Preguntas y respuestas
   - Fecha inicio/fin
   - Asignar a curso/grupo
   ↓
6. React guarda en PostgreSQL
   ↓
7. React muestra examen a alumnos asignados
```

### Escenario 2: Alumno toma examen

```
1. Alumno inicia sesión en React
   ↓
2. React valida usuario en MySQL
   ↓
3. React verifica deudas en MySQL
   - Si tiene deudas → BLOQUEAR
   - Si NO tiene → CONTINUAR
   ↓
4. Alumno ve exámenes disponibles
   ↓
5. Alumno inicia examen
   ↓
6. React bloquea pantalla (no puede salir)
   ↓
7. Alumno responde preguntas
   ↓
8. React guarda respuestas en PostgreSQL
   ↓
9. Alumno termina examen
   ↓
10. React calcula calificación
    ↓
11. React exporta nota a PHP vía API
```

### Escenario 3: Exportación de notas a PHP

```
1. Docente califica tarea en React
   ↓
2. React guarda nota en PostgreSQL
   ↓
3. React llama API de PHP:
   POST /api/notas/import
   {
     "matricula_id": 123,
     "asignatura_id": 45,
     "criterio_id": 10,
     "ciclo": 1,
     "nota": 15
   }
   ↓
4. PHP valida y guarda en MySQL
   ↓
5. PHP responde: { "success": true }
   ↓
6. React marca nota como "exportada"
```

---

## 🛠️ IMPLEMENTACIÓN TÉCNICA

### Stack Tecnológico

**Frontend:**
- React 18+
- React Router
- Axios (para APIs)
- SweetAlert2 (notificaciones)
- PWA (Service Workers)

**Backend:**
- Node.js + Express
- PostgreSQL (aula virtual)
- MySQL2 (lectura de datos maestros)
- JWT (autenticación)
- Bcrypt (passwords)

**Base de Datos:**
- MySQL (lectura) - Datos maestros del PHP
- PostgreSQL (lectura/escritura) - Aula virtual

### Estructura de Carpetas (Nueva)

```
react-aula-virtual/
├── frontend/
│   ├── src/
│   │   ├── pages/
│   │   │   ├── Login.jsx
│   │   │   ├── Dashboard.jsx
│   │   │   ├── Examenes/
│   │   │   ├── Tareas/
│   │   │   ├── Temas/
│   │   │   └── Calificaciones/
│   │   ├── components/
│   │   ├── services/
│   │   │   └── api.js (llamadas a APIs)
│   │   └── utils/
│   └── package.json
│
├── backend/
│   ├── routes/
│   │   ├── auth.routes.js
│   │   ├── examenes.routes.js
│   │   ├── tareas.routes.js
│   │   ├── temas.routes.js
│   │   └── notas.routes.js
│   ├── models/ (PostgreSQL)
│   ├── utils/
│   │   ├── mysql.js (lectura MySQL)
│   │   ├── postgres.js (PostgreSQL)
│   │   └── php-api.js (llamadas a PHP)
│   └── server.js
│
└── database/
    └── schema.sql (PostgreSQL para aula virtual)
```

---

## 📝 PLAN DE IMPLEMENTACIÓN

### FASE 1: Configuración Inicial (1 semana)
- [ ] Crear nueva carpeta `react-aula-virtual`
- [ ] Configurar React + Node.js
- [ ] Configurar conexión MySQL (solo lectura)
- [ ] Configurar PostgreSQL
- [ ] Crear usuario MySQL de solo lectura
- [ ] Configurar variables de entorno

### FASE 2: Autenticación (1 semana)
- [ ] Login con DNI (lee de MySQL)
- [ ] Validación de password (SHA1)
- [ ] Verificación de deudas (lee de MySQL)
- [ ] Generación de token JWT
- [ ] Bloqueo de acceso por deudas
- [ ] Middleware de autenticación

### FASE 3: Lectura de Datos Maestros (1 semana)
- [ ] Leer usuarios de MySQL
- [ ] Leer alumnos de MySQL
- [ ] Leer matrículas de MySQL
- [ ] Leer grupos (grados/secciones) de MySQL
- [ ] Leer cursos/asignaturas de MySQL
- [ ] Leer docentes de MySQL
- [ ] Sincronizar datos iniciales

### FASE 4: Módulo de Exámenes (2 semanas)
- [ ] Crear examen (docente)
- [ ] Gestionar preguntas
- [ ] Asignar a cursos/grupos
- [ ] Tomar examen (alumno)
- [ ] Bloqueo de pantalla
- [ ] Calificación automática
- [ ] Guardar en PostgreSQL

### FASE 5: Módulo de Tareas (1 semana)
- [ ] Crear tarea (docente)
- [ ] Subir archivos
- [ ] Entregar tarea (alumno)
- [ ] Calificar tarea (docente)
- [ ] Guardar en PostgreSQL

### FASE 6: Módulo de Temas (1 semana)
- [ ] Crear tema (docente)
- [ ] Contenido interactivo
- [ ] Archivos adjuntos
- [ ] Organizar por cursos
- [ ] Guardar en PostgreSQL

### FASE 7: Exportación a PHP (1 semana)
- [ ] API en PHP para recibir notas
- [ ] Endpoint en React para exportar
- [ ] Formato de datos compatible
- [ ] Sincronización automática
- [ ] Manejo de errores

### FASE 8: UI/UX y PWA (1 semana)
- [ ] Diseño moderno
- [ ] Responsive
- [ ] PWA (Service Workers)
- [ ] Notificaciones push
- [ ] Gamificación básica

### FASE 9: Pruebas y Ajustes (1 semana)
- [ ] Pruebas de integración
- [ ] Pruebas de seguridad
- [ ] Optimización
- [ ] Documentación

**TOTAL: 9-10 semanas (2-3 meses)**

---

## 🔒 SEGURIDAD

### 1. Usuario MySQL de Solo Lectura
```sql
CREATE USER 'react_readonly'@'localhost' IDENTIFIED BY 'password_segura';
GRANT SELECT ON vanguard_intranet.usuarios TO 'react_readonly'@'localhost';
GRANT SELECT ON vanguard_intranet.alumnos TO 'react_readonly'@'localhost';
GRANT SELECT ON vanguard_intranet.matriculas TO 'react_readonly'@'localhost';
-- ... más tablas según necesites
FLUSH PRIVILEGES;
```

### 2. Validación de Tokens
- JWT con expiración
- Validación en cada request
- Refresh tokens

### 3. Control de Acceso
- Verificación de deudas en cada login
- Bloqueo automático si tiene deudas
- Logs de acceso

---

## ✅ RESUMEN

### React hará:
- ✅ Aula virtual interactiva
- ✅ Exámenes en línea
- ✅ Tareas/deberes
- ✅ Temas/contenido
- ✅ Calificaciones
- ✅ Leer datos maestros de MySQL
- ✅ Exportar notas a PHP vía API

### PHP seguirá haciendo:
- ✅ Gestión de usuarios
- ✅ Matrículas
- ✅ Pagos y deudas
- ✅ Facturación
- ✅ Productos
- ✅ Sistema bancario
- ✅ Recibir notas de React

### Base de Datos:
- ✅ MySQL: Datos maestros (lectura desde React)
- ✅ PostgreSQL: Aula virtual (React crea y gestiona)

---

**¿Estamos listos para empezar?** 🚀

