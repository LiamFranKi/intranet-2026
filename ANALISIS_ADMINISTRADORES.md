# 🔍 ANÁLISIS: TABLA ADMINISTRADORES vs USUARIOS

## 📊 ESTRUCTURA ENCONTRADA

### 1. Tabla `administradores`
```sql
CREATE TABLE `administradores` (
  `id` int(11) NOT NULL,
  `nombres` varchar(500) NOT NULL,
  `apellidos` varchar(500) NOT NULL,
  `dni` varchar(10) NOT NULL,
  `email` varchar(300) NOT NULL,
  `usuario` varchar(30) NOT NULL,
  `password` varchar(40) NOT NULL,  -- SHA1
  `tipo` int(11) NOT NULL,
  `estado` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
```

**Características:**
- ✅ Tiene su propia tabla
- ✅ Tiene `usuario` y `password` propios
- ✅ Conexión: `'admin'` (base de datos diferente)
- ✅ NO tiene `colegio_id` (administradores del sistema, no de colegios)

### 2. Tabla `usuarios`
```sql
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,  -- ✅ Tiene colegio_id
  `alumno_id` int(11) NOT NULL,
  `personal_id` int(11) NOT NULL,
  `apoderado_id` int(11) NOT NULL,
  `usuario` varchar(30) NOT NULL,
  `password` varchar(40) NOT NULL,  -- SHA1
  `tipo` enum('ADMINISTRADOR','DIRECTOR','ALUMNO',...) NOT NULL,  -- ✅ Tipo ADMINISTRADOR
  `estado` enum('ACTIVO','INACTIVO') NOT NULL,
  ...
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
```

**Características:**
- ✅ Tiene `colegio_id` (pertenece a un colegio)
- ✅ Tipo puede ser `'ADMINISTRADOR'`
- ✅ Conexión: `'main'` (base de datos principal)
- ❌ NO tiene `administrador_id` (no se relaciona con tabla administradores)

---

## 🔍 ANÁLISIS DEL CÓDIGO PHP

### Login (`Main/Applications/usuarios/index.php`):
```php
function do_login(){
    // ✅ Usa tabla USUARIOS, NO tabla administradores
    $usuario = Usuario::find_by_usuario_and_password(
        $this->post->usuario, 
        sha1($this->post->password)
    );
    
    if($usuario && $usuario->estado == "ACTIVO" 
       && in_array($usuario->tipo, TraitConstants::ALLOWED_USER_TYPES)){
        // ✅ 'ADMINISTRADOR' está en ALLOWED_USER_TYPES
        $this->session->USUARIO_ID = $usuario->id;
        $this->session->{$usuario->tipo} = $usuario->tipo;
        $code = 1;
    }
}
```

**Conclusión:** El login usa la tabla `usuarios`, NO `administradores`.

### Modelo Usuario (`Main/Models/Usuario.php`):
```php
static $belongs_to = array(
    array('personal', 'class_name' => 'Personal'),
    array('alumno', 'class_name' => 'Alumno'),
    array('apoderado', 'class_name' => 'Apoderado'),
    array('colegio', 'class_name' => 'Colegio'),
);
// ❌ NO hay relación con Administrador
```

**Conclusión:** La tabla `usuarios` NO se relaciona con `administradores`.

---

## 🎯 CONCLUSIÓN

### **Hay DOS tipos de administradores:**

1. **Administradores del Sistema (tabla `administradores`)**
   - Administradores a nivel de sistema multicolegio
   - Base de datos: `admin` (diferente)
   - NO tienen `colegio_id`
   - Probablemente para gestionar múltiples colegios

2. **Administradores de Colegio (tabla `usuarios` con tipo='ADMINISTRADOR')**
   - Administradores de un colegio específico
   - Base de datos: `main` (principal)
   - Tienen `colegio_id`
   - Se autentican en la tabla `usuarios`

### **Para React (Sistema de Aula Virtual):**

**✅ DEBEMOS USAR: Tabla `usuarios` con tipo='ADMINISTRADOR'**

**Razones:**
- ✅ El login PHP usa la tabla `usuarios`
- ✅ Tiene `colegio_id` (necesario para multicolegio)
- ✅ Está en la misma base de datos que alumnos, docentes, etc.
- ✅ Es consistente con el resto del sistema

**❌ NO usar: Tabla `administradores`**
- ❌ Está en otra base de datos (`admin`)
- ❌ No tiene `colegio_id`
- ❌ El login no la usa

---

## ✅ CORRECCIÓN DEL SCRIPT

El script actual está **CORRECTO**. Los administradores se crean directamente en la tabla `usuarios` con:
- `tipo = 'ADMINISTRADOR'`
- `colegio_id = 1`
- `alumno_id = 0`
- `personal_id = 0`
- `apoderado_id = 0`

**NO necesitamos crear un registro en la tabla `administradores`** porque:
- Esa tabla es para administradores del sistema multicolegio
- React solo maneja un colegio específico
- El login usa la tabla `usuarios`

---

## 📝 NOTA IMPORTANTE

Si en el futuro necesitas administradores del sistema multicolegio, entonces sí necesitarías la tabla `administradores`, pero para el sistema React de aula virtual, **solo necesitas la tabla `usuarios`**.

