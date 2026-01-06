# 🔐 LOGIN COMO ADMINISTRADOR

## ✅ CONFIRMACIÓN

**SÍ, puedes loguearte como administrador.**

### Cómo funciona:

1. **Tabla:** `usuarios` en MySQL
2. **Tipo:** `tipo = 'ADMINISTRADOR'`
3. **Estado:** `estado = 'ACTIVO'`
4. **Login:** Usa el mismo DNI y contraseña que en PHP

---

## 📋 REQUISITOS PARA LOGIN DE ADMINISTRADOR

### En MySQL debe existir:

```sql
SELECT * FROM usuarios 
WHERE tipo = 'ADMINISTRADOR' 
  AND estado = 'ACTIVO';
```

**Campos necesarios:**
- `usuario` - DNI del administrador
- `password` - Contraseña en SHA1 (igual que PHP)
- `tipo` - Debe ser `'ADMINISTRADOR'`
- `estado` - Debe ser `'ACTIVO'`
- `colegio_id` - ID del colegio

---

## 🔒 VENTAJAS DEL ADMINISTRADOR

1. **NO se bloquea por deudas:**
   - La verificación de deudas solo aplica a ALUMNOS y APODERADOS
   - Los administradores siempre pueden acceder

2. **Control total:**
   - Puede ver todo el sistema
   - Puede crear, editar, eliminar contenido
   - Acceso a todas las funcionalidades

3. **Mismo login que PHP:**
   - Usa la misma tabla `usuarios`
   - Misma contraseña
   - Mismo DNI

---

## 🧪 PROBAR LOGIN COMO ADMINISTRADOR

1. **Verificar que existe en MySQL:**
   ```sql
   SELECT id, usuario, tipo, estado, colegio_id 
   FROM usuarios 
   WHERE tipo = 'ADMINISTRADOR' 
     AND estado = 'ACTIVO';
   ```

2. **Hacer login:**
   - DNI: El `usuario` (DNI) del administrador
   - Contraseña: La misma que en PHP

3. **Debería funcionar:**
   - Login exitoso
   - Token JWT generado
   - Acceso al dashboard de administrador

---

## 📝 NOTA IMPORTANTE

**Diferencia entre tipos de administradores:**

1. **Administradores de Colegio** (tabla `usuarios` con tipo='ADMINISTRADOR')
   - ✅ Estos SÍ pueden hacer login en React
   - ✅ Tienen `colegio_id`
   - ✅ Son los que gestionan el aula virtual

2. **Administradores del Sistema** (tabla `administradores`)
   - ❌ Estos NO pueden hacer login en React
   - ❌ Están en otra base de datos
   - ❌ Son para gestión multicolegio (no se usan en React)

---

## ✅ CONFIRMACIÓN FINAL

**SÍ, puedes loguearte como administrador usando:**
- Tabla: `usuarios`
- Tipo: `'ADMINISTRADOR'`
- DNI y contraseña reales de MySQL

**El sistema está configurado para aceptar administradores.** 🔐

