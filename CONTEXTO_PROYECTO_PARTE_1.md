# 📚 CONTEXTO DEL PROYECTO - PARTE 1: FUNDAMENTOS

**Fecha de Documentación:** Enero 2026  
**Estado:** Desarrollo Activo  
**Versión:** 1.0.0 - Parte 1

---

## 📋 ÍNDICE

1. [Resumen Ejecutivo](#resumen-ejecutivo)
2. [Arquitectura del Sistema](#arquitectura-del-sistema)
3. [Base de Datos MySQL](#base-de-datos-mysql)
4. [Configuración del Entorno](#configuración-del-entorno)
5. [Estructura del Proyecto](#estructura-del-proyecto)

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
│  │  Tablas      │  │  auditoria_   │  │  Estructura  │  │
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

### ⚠️ NOTAS CRÍTICAS SOBRE LA ESTRUCTURA

**CAMBIOS CRÍTICOS RESPECTO A POSTGRESQL:**
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
│   │   ├── docente.routes.js   # Todas las rutas del docente
│   │   ├── alumno.routes.js     # Rutas del alumno
│   │   ├── colegio.routes.js    # Configuración del colegio
│   │   └── auditoria.routes.js  # Logs de auditoría
│   ├── utils/
│   │   ├── mysql.js             # Pool de conexiones MySQL
│   │   ├── auditoria.js         # Funciones de auditoría
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
│   │   │   ├── DocenteAulaVirtual.jsx   # Aula virtual
│   │   │   ├── AlumnoDashboard.jsx     # Dashboard del alumno
│   │   │   ├── AlumnoPerfil.jsx         # Perfil del alumno
│   │   │   └── AlumnoAulaVirtual.jsx    # Aula virtual del alumno
│   │   ├── context/
│   │   │   ├── AuthContext.jsx          # Context de autenticación
│   │   │   └── ColegioContext.jsx      # Context del colegio
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
├── auditoria_logs_mysql.sql            # Script de tabla de auditoría
├── vanguard_intranet_2.sql             # Copia del schema
├── CONTEXTO_PROYECTO_PARTE_1.md        # Este documento
├── CONTEXTO_PROYECTO_PARTE_2.md        # Módulos y funcionalidades
├── CONTEXTO_PROYECTO_PARTE_3.md        # API y endpoints
└── md/                                  # Documentación anterior
```

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

## 📞 INFORMACIÓN DE CONFIGURACIÓN

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

**Ver también:**
- `CONTEXTO_PROYECTO_PARTE_2.md` - Módulos implementados y funcionalidades
- `CONTEXTO_PROYECTO_PARTE_3.md` - API, endpoints y configuración avanzada

---

**Última Actualización:** Enero 2026  
**Versión del Documento:** 1.0.0 - Parte 1  
**Mantenido por:** Equipo de Desarrollo

