# 📋 INSTRUCCIONES: Agregar Nuevos Perfiles a la Tabla usuarios

## 🎯 Objetivo
Agregar los perfiles **CONTADOR**, **TUTOR** y **MANTENIMIENTO** al campo `tipo` de la tabla `usuarios` sin afectar datos existentes.

---

## ✅ PASO A PASO EN phpMyAdmin

### **PASO 1: Hacer Backup (MUY IMPORTANTE)**
1. Abre phpMyAdmin
2. Selecciona la base de datos `vanguard_intranet`
3. Ve a la pestaña **"Exportar"**
4. Selecciona la tabla `usuarios`
5. Elige formato **SQL**
6. Haz clic en **"Continuar"** para descargar el backup
7. **Guarda el archivo en un lugar seguro**

> ⚠️ **IMPORTANTE**: Siempre haz backup antes de modificar la estructura de tablas

---

### **PASO 2: Abrir la Pestaña SQL**
1. En phpMyAdmin, asegúrate de estar en la base de datos `vanguard_intranet`
2. Haz clic en la pestaña **"SQL"** (arriba del área de trabajo)

---

### **PASO 3: Verificar Valores Actuales (Opcional pero Recomendado)**
1. Copia y pega este comando en el área SQL:
```sql
SHOW COLUMNS FROM `usuarios` LIKE 'tipo';
```
2. Haz clic en **"Continuar"** o presiona **Ctrl+Enter**
3. Verás los valores actuales del ENUM (esto es solo para verificación)

---

### **PASO 4: Ejecutar el ALTER TABLE**
1. Abre el archivo `agregar-perfiles-usuarios.sql` que está en la raíz del proyecto
2. Copia **SOLO** esta parte del script (desde `ALTER TABLE` hasta el `;` final):

```sql
ALTER TABLE `usuarios` 
MODIFY COLUMN `tipo` enum(
    'ADMINISTRADOR',
    'DIRECTOR',
    'ALUMNO',
    'APODERADO',
    'DOCENTE',
    'AUXILIAR',
    'SECRETARIA',
    'CAJERO',
    'ENFERMERA',
    'PROMOTORIA',
    'COORDINADOR',
    'PSICOLOGA',
    'PERSONALIZADO',
    'ASISTENCIA',
    'CONTADOR',
    'TUTOR',
    'MANTENIMIENTO'
) NOT NULL;
```

3. Pega el comando en el área SQL de phpMyAdmin
4. Haz clic en **"Continuar"** o presiona **Ctrl+Enter**

---

### **PASO 5: Verificar que el Cambio se Aplicó**
1. Ejecuta este comando para verificar:
```sql
SHOW COLUMNS FROM `usuarios` LIKE 'tipo';
```
2. Deberías ver que el ENUM ahora incluye los nuevos valores:
   - `'ADMINISTRADOR'`, `'DIRECTOR'`, `'ALUMNO'`, etc. (valores originales)
   - `'CONTADOR'`, `'TUTOR'`, `'MANTENIMIENTO'` (nuevos valores)

---

### **PASO 6: Verificar Datos Existentes (Opcional)**
1. Ejecuta este comando para ver que todos los usuarios existentes están bien:
```sql
SELECT tipo, COUNT(*) as cantidad 
FROM usuarios 
GROUP BY tipo 
ORDER BY cantidad DESC;
```
2. Verifica que todos los tipos mostrados son válidos y no hay errores

---

## ✅ ¿Por qué es SEGURO este cambio?

1. **No modifica datos existentes**: Solo agrega nuevas opciones al ENUM
2. **Mantiene todos los valores originales**: Todos los tipos existentes siguen funcionando
3. **No cambia el orden**: Los valores originales mantienen su posición
4. **Compatible con sistema anterior**: El sistema PHP seguirá funcionando normalmente

---

## 🔍 Verificación Final

Después de ejecutar el script, verifica que:

- ✅ No hay errores en phpMyAdmin
- ✅ La tabla `usuarios` sigue funcionando normalmente
- ✅ Los usuarios existentes mantienen sus tipos actuales
- ✅ Puedes ver los nuevos valores en el ENUM cuando editas un usuario

---

## 📝 Notas Adicionales

- **Tiempo de ejecución**: El comando debería ejecutarse en menos de 1 segundo
- **Sin downtime**: No requiere detener el sistema
- **Reversible**: Si necesitas revertir, puedes ejecutar un ALTER TABLE sin los nuevos valores (pero primero haz backup)

---

## ⚠️ Si algo sale mal

1. **NO entres en pánico**
2. Ve a la pestaña **"Importar"** en phpMyAdmin
3. Selecciona el backup que hiciste en el PASO 1
4. Haz clic en **"Continuar"** para restaurar

---

## 🎉 Resultado Esperado

Después de ejecutar el script, podrás:
- Crear usuarios con tipo `CONTADOR`
- Crear usuarios con tipo `TUTOR`
- Crear usuarios con tipo `MANTENIMIENTO`
- Todos los tipos anteriores seguirán funcionando normalmente

---

**¿Listo para proceder?** Sigue los pasos en orden y todo saldrá bien. 🚀

