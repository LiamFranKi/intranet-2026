# 📚 CONTEXTO COMPLETO DEL PROYECTO - AULA VIRTUAL

**Fecha de Documentación:** Enero 2026  
**Estado:** Desarrollo Activo  
**Versión:** 1.0.0

---

## 📋 ÍNDICE

1. [Resumen Ejecutivo](#resumen-ejecutivo)
2. [Arquitectura del Sistema](#arquitectura-del-sistema)
3. [Base de Datos MySQL](#base-de-datos-mysql)
4. [Configuración del Entorno](#configuración-del-entorno)
5. [Estructura del Proyecto](#estructura-del-proyecto)
6. [Módulos Implementados](#módulos-implementados)
7. [Diseño y UI/UX](#diseño-y-uiux)
8. [PWA y Notificaciones Push](#pwa-y-notificaciones-push)
9. [Sistema de Auditoría](#sistema-de-auditoría)
10. [API y Endpoints](#api-y-endpoints)
11. [Configuración VPS y Hosting](#configuración-vps-y-hosting)
12. [Estado Actual y Próximos Pasos](#estado-actual-y-próximos-pasos)

---

## 🎯 RESUMEN EJECUTIVO

### Descripción del Proyecto
Sistema integral de gestión educativa (Aula Virtual) desarrollado en **React + Node.js**, que reemplaza y moderniza el sistema PHP anterior. El sistema permite gestionar cursos, grupos, estudiantes, docentes, actividades académicas, publicaciones, mensajería y más.

### Tecnologías Principales
- **Frontend:** React 18.2.0, React Router, Material-UI, SweetAlert2
- **Backend:** Node.js/Express 5.2.1, MySQL2, JWT, Multer
- **Base de Datos:** MySQL (remoto en VPS)
- **PWA:** Workbox, Service Workers, Manifest
- **Notificaciones:** Web Push (VAPID)

### Estado Actual
- ✅ **Módulos Completados:** Administrador, Docente, Alumno (parcial)
- ✅ **Base de Datos:** MySQL remoto funcionando
- ✅ **Autenticación:** JWT implementado
- ✅ **Auditoría:** Sistema completo de logs
- ✅ **PWA:** Configurado y funcionando
- 🔄 **En Desarrollo:** Módulos de alumno, notificaciones push

---

## 🏗️ ARQUITECTURA DEL SISTEMA

### Arquitectura General

```
┌─────────────────────────────────────────────────────────┐
│                    FRONTEND (React)                      │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  │
│  │   Login      │  │  Dashboard   │  │  Componentes │  │
│  │   Auth       │  │  Layout      │  │  Widgets    │  │
│  └──────────────┘  └──────────────┘  └──────────────┘  │
└─────────────────────────────────────────────────────────┘
                        ↕ HTTP/REST API
┌─────────────────────────────────────────────────────────┐
│                 BACKEND (Node.js/Express)               │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  │
│  │   Routes     │  │  Middleware  │  │   Utils       │  │
│  │   Auth      │  │   Auditoría  │  │   MySQL      │  │
│  └──────────────┘  └──────────────┘  └──────────────┘  │
└─────────────────────────────────────────────────────────┘
                        ↕ MySQL Connection Pool
┌─────────────────────────────────────────────────────────┐
│              BASE DE DATOS (MySQL Remoto)                │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  │
│  │  Tablas      │  │  auditoria_  │  │  Estructura  │  │
│  │  Maestras    │  │  logs        │  │  vanguard_   │  │
│  └──────────────┘  └──────────────┘  └──────────────┘  │
└─────────────────────────────────────────────────────────┘
```

### Flujo de Datos

1. **Usuario** → Frontend (React)
2. **Frontend** → API REST (Express)
3. **Backend** → MySQL (Pool de conexiones)
4. **MySQL** → Datos retornados
5. **Backend** → Procesa y serializa (formato PHP legacy)
6. **Frontend** → Renderiza componentes

### Conexión a Base de Datos

- **Desarrollo:** Túnel SSH a MySQL remoto (localhost:3306)
- **Producción:** Conexión directa a MySQL remoto
- **Pool de Conexiones:** 10 conexiones simultáneas
- **Solo Lectura:** Usuario `react_readonly` (desarrollo)
- **Lectura/Escritura:** Usuario con permisos completos (producción)

---

## 🗄️ BASE DE DATOS MYSQL

### Estructura Principal

**Ubicación del Schema:** `sistema-anterior/base de datos/vanguard_intranet_2.sql`

Este archivo SQL contiene **TODA** la estructura de la base de datos MySQL y es la **fuente de verdad** para:
- Nombres de tablas
- Nombres de columnas
- Tipos de datos
- Relaciones entre tablas
- Índices y constraints

### Tablas Principales

#### **usuarios**
```sql
- id (PK)
- colegio_id
- usuario (DNI)
- password (SHA1)
- tipo (enum: ADMINISTRADOR, DOCENTE, ALUMNO, APODERADO, etc.)
- estado (ACTIVO/INACTIVO)
- personal_id (FK)
- alumno_id (FK)
- apoderado_id (FK)
```

#### **colegios**
```sql
- id (PK)
- nombre
- anio_activo
- logo
- nombre_empresa
- bloquear_deudores
- dias_tolerancia
- titulo_intranet
```

#### **personal**
```sql
- id (PK)
- colegio_id
- nombres
- apellidos (un solo campo)
- foto (varchar 100)
- email
- telefono_celular
- ...
```

#### **alumnos**
```sql
- id (PK)
- colegio_id
- nombres
- apellido_paterno (separado)
- apellido_materno (separado)
- foto (varchar 500)
- nro_documento (DNI)
- ...
```

#### **grupos**
```sql
- id (PK)
- colegio_id
- nivel_id (FK)
- grado (1, 2, 3...)
- seccion (A, B, C...)
- anio (2025, 2026...)
- tutor_id (FK a personal)
```

#### **asignaturas**
```sql
- id (PK)
- grupo_id (FK) - Relación directa, NO hay areas_cursos
- curso_id (FK)
- personal_id (FK)
- anio
```

#### **publicaciones**
```sql
- id (PK)
- colegio_id
- usuario_id (FK)
- contenido (text)
- images (text) - base64_encode(serialize(array))
- archivos (text) - base64_encode(serialize(array))
- privacidad (varchar) - "-1" = Todos, IDs de grupos separados por comas
- fecha_hora (datetime)
- tipo_video (varchar)
- video_id (varchar)
```

#### **notificaciones**
```sql
- id (PK)
- usuario_id (FK)
- destinatario_id (FK)
- para (enum: TODOS, USUARIO)
- asunto (varchar)
- contenido (text)
- estado (enum: NO ENVIADO, ENVIADO)
- fecha_hora (datetime)
```

#### **actividades**
```sql
- id (PK)
- colegio_id
- titulo (varchar)
- descripcion (text)
- fecha_inicio (datetime)
- fecha_fin (datetime)
- tipo (varchar)
```

#### **asignaturas_examenes**
```sql
- id (PK)
- asignatura_id (FK)
- titulo (varchar) - NO tiene "nombre" ni "descripcion"
- fecha_desde (date) - NO es "fecha_inicio"
- fecha_hasta (date)
- hora_inicio (time)
- hora_fin (time)
```

#### **asignaturas_actividades**
```sql
- id (PK)
- asignatura_id (FK)
- descripcion (text) - NO es "titulo"
- fecha_fin (date) - NO es "fecha_limite"
- fecha_inicio (date)
```

### Tabla de Auditoría (Creada Adicionalmente)

**Tabla:** `auditoria_logs`

Esta tabla **NO está** en `vanguard_intranet_2.sql` pero fue creada para registrar todas las acciones del sistema.

```sql
CREATE TABLE auditoria_logs (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  colegio_id INT NOT NULL,
  tipo_usuario VARCHAR(50),
  accion VARCHAR(100),
  modulo VARCHAR(100),
  entidad VARCHAR(100),
  entidad_id INT,
  descripcion TEXT,
  url VARCHAR(500),
  metodo_http VARCHAR(10),
  ip_address VARCHAR(45),
  user_agent TEXT,
  datos_anteriores JSON,
  datos_nuevos JSON,
  resultado ENUM('EXITOSO', 'ERROR'),
  mensaje_error TEXT,
  duracion_ms INT,
  fecha_hora DATETIME,
  fecha DATE,
  hora TIME,
  INDEX idx_usuario (usuario_id),
  INDEX idx_colegio (colegio_id),
  INDEX idx_fecha (fecha),
  INDEX idx_modulo (modulo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Ubicación del Script:** `auditoria_logs_mysql.sql` (raíz del proyecto)

### Relaciones Importantes

1. **usuarios** → **personal** (uno a uno, vía `personal_id`)
2. **usuarios** → **alumnos** (uno a uno, vía `alumno_id`)
3. **asignaturas** → **grupos** (muchos a uno, vía `grupo_id`)
4. **asignaturas** → **cursos** (muchos a uno, vía `curso_id`)
5. **asignaturas** → **personal** (muchos a uno, vía `personal_id`)
6. **publicaciones** → **usuarios** (muchos a uno, vía `usuario_id`)

### Notas Importantes sobre la Estructura

⚠️ **CAMBIOS CRÍTICOS RESPECTO A POSTGRESQL:**
- ❌ **NO se usa PostgreSQL** - Todo es MySQL
- ✅ **NO existe `areas_cursos`** - `asignaturas` se relaciona directamente con `grupos`
- ✅ **`asignaturas.anio` NO existe** - El año viene de `grupos.anio`
- ✅ **`asignaturas_examenes.fecha_inicio` NO existe** - Usar `fecha_desde`
- ✅ **`asignaturas_actividades.fecha_limite` NO existe** - Usar `fecha_fin`
- ✅ **`asignaturas_examenes.nombre` NO existe** - Usar `titulo`
- ✅ **`asignaturas_actividades.titulo` NO existe** - Usar `descripcion`

---

## ⚙️ CONFIGURACIÓN DEL ENTORNO

### Variables de Entorno - Backend

**Archivo:** `backend/.env`

```env
# MySQL Remoto (VPS)
MYSQL_HOST=mysql.vanguardschools.edu.pe  # O IP del VPS
MYSQL_PORT=3306
MYSQL_USER=vanguard  # O react_readonly para desarrollo
MYSQL_PASSWORD=tu_password
MYSQL_DATABASE=vanguard_intranet

# JWT
JWT_SECRET=tu_secreto_jwt_super_seguro_2025
JWT_EXPIRES_IN=24h

# Server
PORT=5000
NODE_ENV=development  # o production

# CORS
FRONTEND_URL=http://localhost:3000  # o https://intranet.vanguardschools.com

# Email (SMTP Gmail)
EMAIL_HOST=smtp.gmail.com
EMAIL_PORT=587
EMAIL_USER=tu_email@gmail.com
EMAIL_PASSWORD=tu_app_password
EMAIL_FROM=noreply@tudominio.com

# PWA - Notificaciones Push
VAPID_PUBLIC_KEY=tu_vapid_public_key
VAPID_PRIVATE_KEY=tu_vapid_private_key
VAPID_EMAIL=tu_email@tudominio.com

# FTP/SFTP (Para subida de archivos al servidor PHP en producción)
FTP_HOST=89.117.52.9
FTP_USER=vanguard
FTP_PASSWORD=tu_password
FTP_PORT=22
FTP_PROTOCOL=sftp
FTP_BASE_PATH=/home/vanguard/public_html
```

### Variables de Entorno - Frontend

**Archivo:** `frontend/.env`

```env
REACT_APP_API_URL=http://localhost:5000/api  # o https://intranet.vanguardschools.com/api
REACT_APP_VAPID_PUBLIC_KEY=tu_vapid_public_key
```

### Scripts de Configuración

**Windows PowerShell:**
- `crear-env.ps1` - Crea archivos .env con configuraciones
- `crear-env-simple.ps1` - Versión simplificada

---

## 📁 ESTRUCTURA DEL PROYECTO

```
react-aula-virtual/
├── backend/
│   ├── middleware/
│   │   ├── auth.js              # Autenticación JWT
│   │   └── auditoria.js         # Middleware de auditoría automática
│   ├── routes/
│   │   ├── auth.routes.js       # Login, /me, logout
│   │   ├── docente.routes.js    # Todas las rutas del docente
│   │   ├── alumno.routes.js     # Rutas del alumno
│   │   ├── colegio.routes.js    # Configuración del colegio
│   │   └── auditoria.routes.js  # Logs de auditoría
│   ├── utils/
│   │   ├── mysql.js             # Pool de conexiones MySQL
│   │   ├── auditoria.js          # Funciones de auditoría
│   │   └── colegio.js            # Utilidades del colegio
│   ├── uploads/
│   │   ├── personal/            # Fotos de personal/docentes
│   │   ├── alumnos/             # Fotos de alumnos
│   │   ├── publicaciones/       # Imágenes de publicaciones
│   │   └── archivos/            # Archivos adjuntos
│   ├── public/
│   │   └── assets/
│   │       └── logos/           # Logos del colegio
│   └── server.js                # Servidor Express principal
│
├── frontend/
│   ├── public/
│   │   ├── icons/               # Iconos PWA (72x72 a 512x512)
│   │   ├── manifest/
│   │   │   └── manifest.json    # Configuración PWA
│   │   └── index.html
│   ├── src/
│   │   ├── components/
│   │   │   ├── DashboardLayout.jsx      # Layout principal con sidebar
│   │   │   ├── PublicacionesWidget.jsx   # Widget de publicaciones
│   │   │   ├── CalendarioWidget.jsx     # Widget de calendario
│   │   │   ├── NotificacionesWidget.jsx # Widget de notificaciones
│   │   │   └── EventoModal.jsx          # Modal de eventos
│   │   ├── pages/
│   │   │   ├── Login.jsx                # Página de login
│   │   │   ├── DocenteDashboard.jsx     # Dashboard del docente
│   │   │   ├── DocentePerfil.jsx        # Perfil del docente
│   │   │   ├── DocenteGrupos.jsx        # Grupos asignados
│   │   │   ├── DocenteCursos.jsx        # Cursos asignados
│   │   │   ├── DocenteHorario.jsx       # Horario del docente
│   │   │   ├── DocenteTutoria.jsx       # Tutoría
│   │   │   ├── DocenteComunicados.jsx   # Comunicados
│   │   │   ├── DocenteActividades.jsx   # Actividades/Calendario
│   │   │   ├── DocenteMensajes.jsx      # Mensajería
│   │   │   ├── DocenteAulaVirtual.jsx    # Aula virtual
│   │   │   ├── AlumnoDashboard.jsx      # Dashboard del alumno
│   │   │   ├── AlumnoPerfil.jsx         # Perfil del alumno
│   │   │   └── AlumnoAulaVirtual.jsx    # Aula virtual del alumno
│   │   ├── context/
│   │   │   ├── AuthContext.jsx          # Context de autenticación
│   │   │   └── ColegioContext.jsx       # Context del colegio
│   │   ├── services/
│   │   │   └── api.js                   # Cliente Axios configurado
│   │   ├── index.css                    # Estilos globales y variables CSS
│   │   └── App.js                       # Rutas principales
│   └── package.json
│
├── sistema-anterior/
│   └── base de datos/
│       └── vanguard_intranet_2.sql      # ⭐ ESTRUCTURA DEFINITIVA MySQL
│
├── auditoria_logs_mysql.sql              # Script de tabla de auditoría
├── vanguard_intranet_2.sql               # Copia del schema
└── md/                                    # Documentación anterior
```

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

**Funcionalidades Implementadas:**

#### Dashboard
- Estadísticas: 4 cards en una fila (Grupos Asignados, Cursos Asignados, Estudiantes, Tardanzas)
- Próximos exámenes (con fecha_desde y titulo)
- Próximas tareas (con fecha_fin y descripcion)
- Sección unificada "Próximos Eventos" con paginación (8 por página)
- Widgets: Calendario, Publicaciones, Notificaciones

#### Perfil
- Ver y editar datos personales
- Subir/actualizar foto de perfil
- Visualización de foto con URL completa
- Botones con iconos: 💾 Guardar, ❌ Cancelar
- Botón Cancelar mejorado con fondo blanco y borde gris

#### Grupos
- Lista de grupos asignados al docente
- Columna "ALUMNOS" muestra cantidad de alumnos matriculados (estado 0 o 4)
- Vista de lista de alumnos en la misma página (no modal)
- Botón "Volver" en header de lista de alumnos
- Header compacto con información centrada
- Dropdown "Opciones" en cada alumno (Enviar Mensaje, Ver Información)
- Scroll automático al inicio al abrir lista de alumnos

#### Cursos
- Lista de cursos asignados
- Información del curso
- Relación con grupos

#### Horario
- Horario semanal del docente
- Tabla `grupos_horarios` (NO `personal_horario`)
- Días y horas de clases

#### Publicaciones (Widget)
- ✅ Crear publicaciones con texto
- ✅ Subir imágenes (desde archivo o cámara)
- ✅ Subir archivos adjuntos
- ✅ Compartir con "Todos" o grupos específicos
- ✅ Ver feed de publicaciones
- ✅ Eliminar propias publicaciones
- ✅ Paginación (5 por vez, botón "Cargar Más")
- ✅ Modal para ver imágenes en grande
- ✅ Foto del autor o placeholder con iniciales
- ✅ Mostrar nombres de grupos en "Para"

#### Calendario (Widget)
- Calendario mensual
- Actividades del día
- Navegación entre meses

#### Notificaciones (Widget)
- Lista de notificaciones recibidas
- Estado: ENVIADO/NO ENVIADO

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

## 🎨 DISEÑO Y UI/UX

### Paleta de Colores

**Colores Principales:**
- Primary: `#667eea` (Azul púrpura)
- Secondary: `#764ba2` (Púrpura oscuro)
- Accent: `#ff6f00` (Naranja)

**Colores del Sistema (DataCole Style):**
- Botón Primary: `linear-gradient(135deg, #6ba3d4 0%, #4a83c1 100%)`
- Texto Principal: `#1f2937`
- Texto Secundario: `#6b7280`
- Fondo: `#f5f5f5`

**Variables CSS (index.css):**
```css
--primary-color: #667eea;
--secondary-color: #764ba2;
--btn-primary-bg: linear-gradient(135deg, #6ba3d4 0%, #4a83c1 100%);
--btn-secondary-bg: #f3f4f6;
--btn-outline-border: #4a83c1;
```

### Tipografía

**Fuente Principal:**
```css
font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 
  'Oxygen', 'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif;
```

**Tamaños:**
- Títulos: `1.5rem - 2rem` (24px - 32px)
- Subtítulos: `1.1rem - 1.25rem` (17.6px - 20px)
- Texto: `0.95rem - 1rem` (15.2px - 16px)
- Metadata: `0.8rem - 0.85rem` (12.8px - 13.6px)

### Componentes de Diseño

#### Botones (Estilo DataCole)

**Variantes:**
- `.btn-primary` - Gradiente azul, texto blanco
- `.btn-secondary` - Fondo gris claro, texto azul
- `.btn-outline` - Borde azul, fondo transparente
- `.btn-ghost` - Sin borde ni fondo

**Tamaños:**
- `.btn-sm` - Pequeño
- `.btn-lg` - Grande
- `.btn-block` - Ancho completo

**Iconos en Botones:**
- Clase `.btn-icon` para iconos dentro de botones
- Tamaño: `1.2rem`

#### Cards

**Estilo:**
- Fondo: `linear-gradient(145deg, #ffffff, #f9fafb)`
- Border-radius: `16px`
- Sombra: `0 2px 8px rgba(0, 0, 0, 0.06)`
- Hover: Elevación y sombra aumentada

#### Iconos

**Tamaños:**
- Iconos grandes (módulos): `4rem` (64px)
- Iconos en botones: `1.2rem` (19.2px)
- Iconos en sidebar: `1rem` (16px)

**Estilo:**
- Emojis directamente en HTML (no SVG)
- Colores consistentes con el tema

### Layout Principal

**DashboardLayout.jsx:**
- Sidebar lateral colapsable
- Header con usuario y acciones
- Contenido principal
- Sidebar derecho (widgets) para DOCENTE y ALUMNO

**Sidebar:**
- Logo del colegio
- Menú por secciones (MENÚ PRINCIPAL, COMUNICADOS, CALENDARIO, MENSAJERÍA)
- Navegación por tipo de usuario
- Cerrar sesión

---

## 📱 PWA Y NOTIFICACIONES PUSH

### Configuración PWA

**Manifest:** `frontend/public/manifest/manifest.json`

```json
{
  "short_name": "Aula Virtual",
  "name": "Intranet del Colegio - Aula Virtual",
  "start_url": "/",
  "display": "standalone",
  "theme_color": "#1976d2",
  "background_color": "#ffffff",
  "orientation": "portrait-primary"
}
```

**Iconos Requeridos:**
- `icon-192x192.png` - **REQUERIDO**
- `icon-512x512.png` - **REQUERIDO**
- `apple-touch-icon.png` (180x180) - **REQUERIDO para iOS**

**Ubicación:** `frontend/public/icons/`

### Service Worker

**Configuración:** Workbox Webpack Plugin
- Cache de assets estáticos
- Cache de API responses
- Estrategia: Network First, Cache Fallback

### Notificaciones Push

**Estado:** 🔄 Configurado, pendiente implementación completa

**Librería:** `web-push` (backend)

**Configuración:**
- VAPID keys en `.env`
- Endpoint para suscripción
- Endpoint para enviar notificaciones

**Pendiente:**
- Frontend: Solicitar permisos y suscripción
- Backend: Envío de notificaciones cuando se crean publicaciones/actividades

---

## 📊 SISTEMA DE AUDITORÍA

### Tabla auditoria_logs

**Campos Principales:**
- `usuario_id` - ID del usuario que realizó la acción
- `colegio_id` - ID del colegio
- `tipo_usuario` - Tipo (DOCENTE, ALUMNO, etc.)
- `accion` - Acción realizada (LOGIN, CREAR, EDITAR, ELIMINAR, VER)
- `modulo` - Módulo afectado (AUTENTICACION, EXAMENES, TAREAS, etc.)
- `entidad` - Entidad afectada (examen, tarea, tema, etc.)
- `entidad_id` - ID de la entidad
- `descripcion` - Descripción de la acción
- `url` - URL de la petición
- `metodo_http` - GET, POST, PUT, DELETE
- `ip_address` - IP del usuario
- `user_agent` - Navegador/dispositivo
- `datos_anteriores` - JSON con datos antes del cambio
- `datos_nuevos` - JSON con datos después del cambio
- `resultado` - EXITOSO o ERROR
- `mensaje_error` - Mensaje si hubo error
- `duracion_ms` - Tiempo de ejecución
- `fecha_hora` - Timestamp completo
- `fecha` - Solo fecha (YYYY-MM-DD)
- `hora` - Solo hora (HH:MM:SS)

### Middleware de Auditoría

**Archivo:** `backend/middleware/auditoria.js`

**Funcionamiento:**
1. Intercepta todas las respuestas `res.json()`
2. Registra automáticamente cada acción
3. No bloquea la respuesta (asíncrono)
4. Sanitiza datos sensibles (password, token, secret)

**Acciones Registradas:**
- LOGIN / LOGOUT
- CREAR (POST)
- EDITAR (PUT/PATCH)
- ELIMINAR (DELETE)
- VER (GET)

### Utilidades de Auditoría

**Archivo:** `backend/utils/auditoria.js`

**Funciones:**
- `registrarAccion()` - Registrar una acción manualmente
- `obtenerLogsUsuario()` - Obtener logs de un usuario
- `obtenerLogsModulo()` - Obtener logs de un módulo

### Rutas de Auditoría

**Archivo:** `backend/routes/auditoria.routes.js`

- `GET /api/auditoria/mis-logs` - Logs del usuario autenticado
- `GET /api/auditoria/usuario/:usuarioId` - Logs de un usuario (solo admin)
- `GET /api/auditoria/modulo/:modulo` - Logs de un módulo (solo admin)

---

## 🔌 API Y ENDPOINTS

### Autenticación

**Base:** `/api/auth`

- `POST /login` - Login con DNI y password
- `GET /me` - Obtener usuario autenticado
- `POST /logout` - Logout (registra en auditoría)

### Colegio

**Base:** `/api/colegio`

- `GET /:colegioId` - Obtener datos del colegio (logo, nombre, configuraciones)

### Docente

**Base:** `/api/docente`

#### Dashboard
- `GET /dashboard` - Estadísticas, próximos exámenes, próximas tareas

#### Perfil
- `GET /perfil` - Obtener perfil del docente
- `PUT /perfil` - Actualizar perfil (incluyendo foto)

#### Grupos
- `GET /grupos` - Lista de grupos asignados (incluye `total_alumnos` por grupo)
- `GET /grupos/:grupoId/alumnos` - Estudiantes de un grupo

#### Cursos
- `GET /cursos` - Cursos asignados

#### Horario
- `GET /horario` - Horario semanal (tabla `grupos_horarios`)

#### Tutoria
- `GET /tutoria` - Grupos de tutoría

#### Comunicados
- `GET /comunicados` - Comunicados recibidos

#### Actividades
- `GET /actividades` - Actividades del calendario
- `GET /actividades?fecha=YYYY-MM-DD` - Actividades de un día específico

#### Notificaciones
- `GET /notificaciones` - Notificaciones recibidas

#### Mensajes
- `GET /mensajes` - Mensajes enviados y recibidos

#### Publicaciones
- `GET /publicaciones` - Feed de publicaciones (tipo Facebook)
- `POST /publicaciones` - Crear publicación (con imagen/archivo)
- `DELETE /publicaciones/:id` - Eliminar publicación (solo autor)

**Formato de Publicaciones:**
- `images`: Serializado como PHP `base64_encode(serialize(array))`
- `archivos`: Serializado como PHP `base64_encode(serialize(array))`
- `privacidad`: "-1" = Todos, IDs de grupos separados por comas

### Alumno

**Base:** `/api/alumno`

- `GET /dashboard` - Dashboard del alumno
- `GET /perfil` - Perfil del alumno
- `GET /cursos` - Cursos del alumno
- `GET /calificaciones` - Calificaciones
- `GET /horario` - Horario del alumno

### Auditoría

**Base:** `/api/auditoria`

- `GET /mis-logs` - Logs del usuario autenticado
- `GET /usuario/:usuarioId` - Logs de un usuario (solo admin)
- `GET /modulo/:modulo` - Logs de un módulo (solo admin)

---

## 🌐 CONFIGURACIÓN VPS Y HOSTING

### Servidor MySQL

**Ubicación:** VPS remoto
**Host:** `mysql.vanguardschools.edu.pe` (o IP: `89.117.52.9`)
**Puerto:** `3306`
**Base de Datos:** `vanguard_intranet`

### Servidor PHP (Sistema Anterior)

**Ubicación:** VPS remoto
**IP:** `89.117.52.9`
**Usuario SSH:** `vanguard`
**Ruta Base:** `/home/vanguard/public_html`

**Archivos Estáticos:**
- Logos: `/Static/Image/Logos/`
- Fotos: `/Static/Image/Fotos/`
- Publicaciones: `/Static/Image/Publicaciones/`
- Archivos: `/Static/Archivos/`

### Túnel SSH (Desarrollo)

**Configuración:**
- Host: `89.117.52.9`
- Usuario: `vanguard`
- Puerto Local: `3306` (redirige a MySQL remoto)
- Puerto Remoto: `3306`

**Script:** `start-tunnel.ps1` (Windows PowerShell)

### Producción

**Frontend:**
- URL: `https://intranet.vanguardschools.com`
- Hosting: DigitalOcean (según memoria)

**Backend:**
- URL: `https://intranet.vanguardschools.com/api`
- Puerto: `5000` (interno)
- Nginx como reverse proxy

### Archivos Estáticos

**Backend:**
- `/assets/logos/` - Logos del colegio
- `/uploads/personal/` - Fotos de personal
- `/uploads/alumnos/` - Fotos de alumnos
- `/uploads/publicaciones/` - Imágenes de publicaciones
- `/uploads/archivos/` - Archivos adjuntos

**Servicio:**
- Express static middleware
- Headers CORS configurados
- Cache headers para optimización

---

## 📝 ESTADO ACTUAL Y PRÓXIMOS PASOS

### ✅ Completado

1. **Autenticación JWT**
   - Login con DNI y password (SHA1)
   - Middleware de autenticación
   - Context de autenticación en React

2. **Módulo Docente Completo**
   - Dashboard con estadísticas
   - Perfil con foto
   - Grupos y cursos asignados
   - Horario semanal
   - Publicaciones (crear, ver, eliminar)
   - Calendario de actividades
   - Notificaciones
   - Mensajería (estructura)

3. **Sistema de Auditoría**
   - Tabla `auditoria_logs` creada
   - Middleware automático
   - Rutas de consulta

4. **Diseño y UI**
   - Estilo DataCole implementado
   - Iconos grandes en módulos
   - Botones con variantes
   - Cards con hover
   - Grid responsive

5. **PWA**
   - Manifest configurado
   - Iconos PWA
   - Service Worker (Workbox)

6. **Base de Datos**
   - Conexión MySQL remota funcionando
   - Pool de conexiones
   - Queries corregidas según schema real

### 🔄 En Desarrollo

1. **Módulo Alumno**
   - Dashboard básico
   - Falta completar funcionalidades

2. **Notificaciones Push**
   - Configuración lista
   - Falta implementar suscripción en frontend
   - Falta implementar envío automático

3. **Mensajería**
   - Estructura creada
   - Falta implementar envío/recepción

### 📋 Pendiente

1. **Módulo Apoderado**
   - Estructura de rutas
   - Dashboard
   - Ver hijos

2. **Exámenes en Línea**
   - Crear exámenes
   - Rendir exámenes
   - Bloqueo de pantalla durante examen

3. **Tareas**
   - Crear tareas
   - Entregar tareas
   - Calificar tareas

4. **Calificaciones**
   - Registrar calificaciones
   - Boletas de notas

5. **Aula Virtual**
   - Mundos/Temas
   - Contenido interactivo
   - Gamificación

---

## 🔑 PUNTOS CRÍTICOS Y NOTAS IMPORTANTES

### ⚠️ IMPORTANTE: Base de Datos

1. **Solo MySQL, NO PostgreSQL**
   - Toda la lógica debe usar MySQL
   - El schema definitivo está en `vanguard_intranet_2.sql`
   - NO usar referencias a PostgreSQL

2. **Estructura Real de Tablas**
   - `asignaturas` NO tiene `anio` - Usar `grupos.anio`
   - `asignaturas` NO tiene relación con `areas_cursos` - Relación directa con `grupos`
   - `asignaturas_examenes.fecha_inicio` NO existe - Usar `fecha_desde`
   - `asignaturas_actividades.fecha_limite` NO existe - Usar `fecha_fin`
   - `asignaturas_examenes.nombre` NO existe - Usar `titulo`
   - `asignaturas_actividades.titulo` NO existe - Usar `descripcion`

3. **Serialización PHP Legacy**
   - `publicaciones.images` y `publicaciones.archivos` están en formato PHP
   - Formato: `base64_encode(serialize(array))`
   - Al guardar: Serializar como PHP
   - Al leer: Deserializar desde PHP

### 🔐 Seguridad

1. **Rate Limiting**
   - Desarrollo: 1000 requests/15min
   - Producción: 200 requests/15min
   - Archivos estáticos excluidos

2. **Autenticación**
   - JWT con expiración de 24h
   - Password hasheado con SHA1 (legacy)
   - Middleware en todas las rutas protegidas

3. **Auditoría**
   - Todas las acciones se registran automáticamente
   - Datos sensibles se sanitizan antes de guardar

### 📦 Dependencias Principales

**Backend:**
- `express` 5.2.1
- `mysql2` 3.16.0
- `jsonwebtoken` 9.0.3
- `multer` 2.0.2 (uploads)
- `web-push` 3.6.7 (notificaciones)
- `nodemailer` 7.0.12 (emails)

**Frontend:**
- `react` 18.2.0
- `react-router-dom` 6.20.0
- `axios` 1.6.2
- `sweetalert2` 11.10.3
- `@mui/material` 5.14.20
- `workbox-webpack-plugin` 7.0.0 (PWA)

---

## 🚀 CÓMO RETOMAR EL PROYECTO

### 1. Configurar Entorno

```bash
# Backend
cd backend
npm install
# Crear backend/.env con las variables necesarias

# Frontend
cd frontend
npm install
# Crear frontend/.env con REACT_APP_API_URL
```

### 2. Configurar Túnel SSH (Desarrollo)

```powershell
# Windows
.\start-tunnel.ps1

# O manualmente:
ssh -L 3306:localhost:3306 vanguard@89.117.52.9
```

### 3. Iniciar Servidores

```bash
# Backend (puerto 5000)
cd backend
npm start

# Frontend (puerto 3000)
cd frontend
npm start
```

### 4. Verificar Conexión

- Backend: `http://localhost:5000/api/health`
- Frontend: `http://localhost:3000`
- Login: Usar DNI y password de la base de datos MySQL

### 5. Estructura de Base de Datos

**Siempre consultar:** `sistema-anterior/base de datos/vanguard_intranet_2.sql`

**Tabla adicional:** `auditoria_logs` (ver `auditoria_logs_mysql.sql`)

---

## 📞 INFORMACIÓN DE CONTACTO Y CONFIGURACIÓN

### VPS MySQL
- **Host:** `mysql.vanguardschools.edu.pe`
- **Puerto:** `3306`
- **Base de Datos:** `vanguard_intranet`

### VPS PHP (Sistema Anterior)
- **IP:** `89.117.52.9`
- **Usuario:** `vanguard`
- **Ruta:** `/home/vanguard/public_html`

### Producción
- **URL Frontend:** `https://intranet.vanguardschools.com`
- **URL Backend:** `https://intranet.vanguardschools.com/api`

---

## 📚 DOCUMENTACIÓN ADICIONAL

Todos los archivos `.md` en la carpeta `md/` contienen documentación detallada sobre:
- Configuración de entorno
- Guías de instalación
- Arquitectura del sistema
- Diseño y UI/UX
- Configuración de PWA
- Guías de despliegue

---

**Última Actualización:** Enero 2026  
**Versión del Documento:** 1.0.0  
**Mantenido por:** Equipo de Desarrollo

---

*Este documento debe actualizarse cada vez que se agreguen nuevas funcionalidades o se modifique la arquitectura del sistema.*

