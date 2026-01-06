# 📚 CONTEXTO COMPLETO DEL SISTEMA - TODO LO QUE NECESITAS SABER

## 🎯 PROPÓSITO DE ESTE DOCUMENTO

Este documento contiene **TODO el contexto** necesario para crear el nuevo sistema de Aula Virtual en React desde cero. Incluye:
- ✅ Análisis completo del sistema PHP
- ✅ Estructura de base de datos MySQL
- ✅ Lógica de negocio identificada
- ✅ Arquitectura de integración
- ✅ Plan de implementación

**Copia este archivo completo a tu nueva carpeta `react-aula-virtual/`**

---

## 📋 PARTE 1: SISTEMA PHP EXISTENTE

### 1.1 Descripción General

**Sistema PHP/MySQL (Funcionando)**
- Framework: CrystalTools (framework propio)
- Base de datos: MySQL
- Ubicación: `sistema-anterior/`
- Estado: ✅ Funcionando completamente
- Código: ✅ 100% legible (NO está encriptado)

### 1.2 Funcionalidades del Sistema PHP

#### A. Gestión de Usuarios
- **Tabla**: `usuarios`
- **Tipos**: ADMINISTRADOR, DIRECTOR, ALUMNO, APODERADO, DOCENTE, SECRETARIA, CAJERO, etc.
- **Autenticación**: SHA1 (40 caracteres)
- **Validación**: `Usuario::find_by_usuario_and_password(usuario, sha1(password))`
- **Estado**: Solo usuarios con `estado = 'ACTIVO'`

#### B. Control de Deudas
- **Configuración**: `$colegio->bloquear_deudores` (SI/NO)
- **Verificación**: `$usuario->getDeudas()` → `$matricula->getDeudas()`
- **Bloqueo**: Si tiene deudas → cierra sesión automáticamente
- **Afecta**: ALUMNOS y APODERADOS (de sus hijos)

**Lógica de deudas:**
```php
// Main/Models/Matricula.php - getDeudas()
function getDeudas(){
    $currentMonth = intval(date('m'));
    $nroPago = $currentMonth - 2; // Mes actual - 2
    $deudas = array();
    
    if($this->costo->pension > 0){
        for($i=1; $i <= $nroPago; ++$i){
            // Verifica si tiene pago cancelado
            if(!$this->hasPagoCancelado(1, $i) && 
               strtotime(date('Y-m-d')) > ($fechaVencimiento + $tolerancia)){
                $deudas[] = 'Pensión '.$this->colegio->getCicloPensionesSingle($i)
                          .' - Vencimiento: '.date('d-m-Y', $fechaVencimiento);
            }
        }
    }
    return $deudas;
}
```

#### C. Sistema Multicolegio
- **Tabla**: `colegios`
- **Campo**: `colegio_id` en todas las tablas principales
- **Filtrado**: Todos los queries filtran por `colegio_id`

#### D. Matrículas
- **Tabla**: `matriculas`
- **Relaciones**: `alumno_id`, `grupo_id`, `colegio_id`
- **Estado**: 0=activo, 1=inactivo
- **Año**: Filtrado por `grupos.anio`

#### E. Grupos (Grados y Secciones)
- **Tabla**: `grupos`
- **Campos**: `grado` (1, 2, 3...), `seccion` (A, B, C...), `nivel_id`, `anio`
- **Ejemplo**: 1°A, 2°B, 3°C

#### F. Cursos/Asignaturas
- **Tabla**: `cursos`
- **Relación**: Con `grupos` (grado/sección)
- **Asignación**: `personal_id` (docente asignado)

#### G. Pagos y Facturación
- **Tabla**: `pagos`
- **Estados**: `estado_pago` (CANCELADO, PENDIENTE)
- **Tipos**: 0=matrícula, 1=pensión, 2=agenda, etc.
- **Número de pago**: `nro_pago` (1-10 para pensiones)

#### H. Facturación (Boletas)
- **Tabla**: `boletas`, `boletas_detalles`
- **Funcionalidad**: Boletas electrónicas, productos (buzos, uniformes)
- **Control de stock**: `Boleta_Concepto->controlarStock()`

#### I. Sistema Bancario
- **Archivos**: `.txt` formato posición fija
- **Envío**: Genera archivo para banco (BCP, BBVA)
- **Recepción**: Procesa archivo del banco (conciliación)

---

## 📊 PARTE 2: ESTRUCTURA DE BASE DE DATOS MYSQL

### 2.1 Tablas Principales

#### **usuarios**
```sql
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `alumno_id` int(11) NOT NULL,
  `personal_id` int(11) NOT NULL,
  `apoderado_id` int(11) NOT NULL,
  `usuario` varchar(30) NOT NULL, -- DNI
  `password` varchar(40) NOT NULL, -- SHA1 (40 caracteres)
  `tipo` enum('ADMINISTRADOR','DIRECTOR','ALUMNO','APODERADO','DOCENTE',...) NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') NOT NULL,
  `cambiar_password` enum('NO','SI') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
```

#### **alumnos**
```sql
CREATE TABLE `alumnos` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `apellido_paterno` varchar(100) NOT NULL,
  `apellido_materno` varchar(100) NOT NULL,
  `nombres` varchar(200) NOT NULL,
  `nro_documento` varchar(20) NOT NULL, -- DNI
  `fecha_nacimiento` date NOT NULL,
  `email` varchar(200) NOT NULL,
  `foto` varchar(500) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
```

#### **matriculas**
```sql
CREATE TABLE `matriculas` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `grupo_id` int(11) NOT NULL, -- Grado y sección
  `alumno_id` int(11) NOT NULL,
  `fecha_registro` date NOT NULL,
  `estado` int(11) NOT NULL, -- 0=activo, 1=inactivo
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
```

#### **grupos**
```sql
CREATE TABLE `grupos` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `sede_id` int(11) NOT NULL,
  `nivel_id` int(11) NOT NULL,
  `grado` int(11) NOT NULL, -- 1, 2, 3...
  `seccion` varchar(50) NOT NULL, -- A, B, C...
  `anio` int(11) NOT NULL, -- 2025, 2026...
  `tutor_id` int(11) NOT NULL, -- Docente tutor
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
```

#### **cursos**
```sql
CREATE TABLE `cursos` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `grupo_id` int(11) NOT NULL, -- A qué grado pertenece
  `nombre` varchar(200) NOT NULL, -- Matemática, Comunicación, etc.
  `personal_id` int(11) NOT NULL, -- Docente asignado
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
```

#### **pagos**
```sql
CREATE TABLE `pagos` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `matricula_id` int(11) NOT NULL,
  `nro_pago` int(11) NOT NULL, -- 1-10 para pensiones
  `monto` float(8,2) NOT NULL,
  `mora` float(8,2) NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `tipo` int(11) NOT NULL, -- 0=matrícula, 1=pensión, 2=agenda
  `estado_pago` enum('CANCELADO','PENDIENTE') NOT NULL,
  `estado` enum('ACTIVO','ANULADO') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
```

#### **personal**
```sql
CREATE TABLE `personal` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `nombres` varchar(200) NOT NULL,
  `apellido_paterno` varchar(100) NOT NULL,
  `apellido_materno` varchar(100) NOT NULL,
  `nro_documento` varchar(20) NOT NULL,
  `email` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
```

#### **colegios**
```sql
CREATE TABLE `colegios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `anio_activo` int(11) NOT NULL, -- 2025, 2026...
  `bloquear_deudores` enum('SI','NO') NOT NULL,
  `dias_tolerancia` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
```

### 2.2 Relaciones Importantes

```
usuarios
  ├── alumno_id → alumnos.id
  ├── apoderado_id → apoderados.id
  ├── personal_id → personal.id
  └── colegio_id → colegios.id

matriculas
  ├── alumno_id → alumnos.id
  ├── grupo_id → grupos.id
  └── colegio_id → colegios.id

grupos
  ├── nivel_id → niveles.id
  ├── tutor_id → personal.id
  └── colegio_id → colegios.id

cursos
  ├── grupo_id → grupos.id
  ├── personal_id → personal.id
  └── colegio_id → colegios.id

pagos
  ├── matricula_id → matriculas.id
  └── colegio_id → colegios.id
```

---

## 🔐 PARTE 3: LÓGICA DE NEGOCIO IDENTIFICADA

### 3.1 Autenticación

**Archivo PHP**: `Main/Applications/usuarios/index.php`

```php
function do_login(){
    $usuario = Usuario::find_by_usuario_and_password(
        $this->post->usuario, 
        sha1($this->post->password)  // SHA1
    );
    
    if($usuario && $usuario->estado == "ACTIVO"){
        $this->session->USUARIO_ID = $usuario->id;
        $this->session->{$usuario->tipo} = $usuario->tipo;
        $code = 1;  // Login exitoso
    }
}
```

**Para React:**
- Usuario ingresa DNI
- Validar password con SHA1
- Verificar estado = 'ACTIVO'
- Verificar deudas antes de permitir acceso

### 3.2 Verificación de Deudas

**Archivo PHP**: `Main/Models/Usuario.php` - `getDeudas()`

```php
function getDeudas(){
    if($this->tipo == 'ALUMNO'){
        $matricula = $this->alumno->getMatriculaByAnio($this->colegio->anio_activo);
        if(!$matricula) return array();
        return $matricula->getDeudas();
    }

    if($this->tipo == 'APODERADO'){
        // Obtiene deudas de todos los hijos
        $alumnos = Alumno::find_by_sql('...');
        $deudas = array();
        foreach($alumnos As $alumno){
            $matricula = $alumno->getMatriculaByAnio(...);
            if($matricula){
                $deudasAlumno = $matricula->getDeudas();
                if(count($deudasAlumno) > 0){
                    $deudas[] = $alumno->getFullName().' - ('.implode(', ', $deudasAlumno).')';
                }
            }
        }
        return $deudas;
    }
    
    return array();
}
```

**Para React:**
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

### 3.3 Control de Acceso

**Archivo PHP**: `Main/Applications/home/index.php` (Línea 169-175)

```php
if($this->COLEGIO->bloquear_deudores == "SI"){
    $deudas = $this->USUARIO->getDeudas();
    
    // ✅ MODIFICAR: Bloquear también APODERADOS
    if(count($deudas) > 0 && 
       ($this->USUARIO->is('ALUMNO') || $this->USUARIO->is('APODERADO'))){
        $this->session->DEUDAS = base64_encode(serialize($deudas));
        return header('Location: /usuarios/logout');  // Cierra sesión
    }
}
```

**Para React:**
- Verificar `colegios.bloquear_deudores = 'SI'`
- Si tiene deudas Y es ALUMNO o APODERADO → BLOQUEAR
- Mostrar mensaje y redirigir

---

## 🏗️ PARTE 4: ARQUITECTURA DE INTEGRACIÓN

### 4.1 Opción B: Lectura Directa + Escritura vía API

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

### 4.2 Flujo de Datos

#### **LECTURA (Node.js → MySQL Directo)**
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

#### **ESCRITURA (Node.js → PHP API)**
- ✅ Notas de exámenes
- ✅ Notas de tareas
- ✅ Calificaciones finales

**PHP valida y controla** toda la escritura.

---

## 📝 PARTE 5: QUÉ HARÁ REACT

### 5.1 Funcionalidades

1. **Exámenes en Línea**
   - Docente crea exámenes
   - Alumno toma exámenes
   - Calificación automática
   - Bloqueo de pantalla

2. **Tareas/Deberes**
   - Docente crea tareas
   - Alumno entrega tareas
   - Docente califica

3. **Temas/Contenido**
   - Docente crea temas interactivos
   - Alumno accede a contenido
   - Archivos, videos, imágenes

4. **Calificaciones**
   - Notas de exámenes
   - Notas de tareas
   - Promedios
   - Exporta a PHP vía API

### 5.2 Base de Datos

- **MySQL**: Lectura de datos maestros
- **PostgreSQL**: Aula virtual (exámenes, tareas, temas, calificaciones)

---

## 🔧 PARTE 6: CONFIGURACIÓN TÉCNICA

### 6.1 Stack Tecnológico

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
- Crypto (SHA1 para passwords)

### 6.2 Variables de Entorno

```env
# MySQL (Lectura)
MYSQL_HOST=localhost
MYSQL_PORT=3306
MYSQL_USER=react_readonly
MYSQL_PASSWORD=password_segura
MYSQL_DATABASE=vanguard_intranet

# PostgreSQL (Aula Virtual)
POSTGRES_HOST=localhost
POSTGRES_PORT=5432
POSTGRES_USER=postgres
POSTGRES_PASSWORD=password
POSTGRES_DATABASE=aula_virtual

# PHP API
PHP_API_URL=http://localhost/php-api
PHP_API_TOKEN=token_secreto

# JWT
JWT_SECRET=tu_secreto_jwt
JWT_EXPIRES_IN=24h

# Server
PORT=5000
NODE_ENV=development
```

---

## 📋 PARTE 7: PLAN DE IMPLEMENTACIÓN

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

## 🔒 PARTE 8: SEGURIDAD

### 8.1 Usuario MySQL de Solo Lectura

```sql
-- Crear usuario solo lectura
CREATE USER 'react_readonly'@'localhost' IDENTIFIED BY 'password_segura';

-- Permisos solo lectura en tablas necesarias
GRANT SELECT ON vanguard_intranet.usuarios TO 'react_readonly'@'localhost';
GRANT SELECT ON vanguard_intranet.alumnos TO 'react_readonly'@'localhost';
GRANT SELECT ON vanguard_intranet.matriculas TO 'react_readonly'@'localhost';
GRANT SELECT ON vanguard_intranet.grupos TO 'react_readonly'@'localhost';
GRANT SELECT ON vanguard_intranet.cursos TO 'react_readonly'@'localhost';
GRANT SELECT ON vanguard_intranet.personal TO 'react_readonly'@'localhost';
GRANT SELECT ON vanguard_intranet.pagos TO 'react_readonly'@'localhost';
GRANT SELECT ON vanguard_intranet.colegios TO 'react_readonly'@'localhost';
GRANT SELECT ON vanguard_intranet.apoderados TO 'react_readonly'@'localhost';
GRANT SELECT ON vanguard_intranet.familias TO 'react_readonly'@'localhost';

FLUSH PRIVILEGES;
```

### 8.2 Validación de Tokens
- JWT con expiración
- Validación en cada request
- Refresh tokens

### 8.3 Control de Acceso
- Verificación de deudas en cada login
- Bloqueo automático si tiene deudas
- Logs de acceso

---

## 📦 PARTE 9: ARCHIVOS SQL

### 9.1 Ubicación del Archivo SQL

**Archivo**: `sistema-anterior/base de datos/vanguard_intranet_2.sql`

Este archivo contiene:
- ✅ Estructura completa de la base de datos
- ✅ Sin datos (solo estructura)
- ✅ Todas las tablas necesarias

### 9.2 Cómo Importar en XAMPP

1. Abrir phpMyAdmin: `http://localhost/phpmyadmin`
2. Crear nueva base de datos: `vanguard_intranet`
3. Seleccionar la base de datos
4. Ir a "Importar"
5. Seleccionar archivo: `vanguard_intranet_2.sql`
6. Click en "Continuar"
7. Esperar a que termine la importación

---

## ✅ RESUMEN FINAL

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

**Este documento contiene TODO el contexto necesario para empezar.** 📚

