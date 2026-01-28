-- ============================================================
-- SCRIPT PARA AGREGAR NUEVOS PERFILES A LA TABLA usuarios
-- ============================================================
-- Fecha: Enero 2026
-- Descripción: Agrega los perfiles CONTADOR, TUTOR y MANTENIMIENTO
--              al campo tipo de la tabla usuarios
-- ============================================================
-- ⚠️ IMPORTANTE: Este script es SEGURO y NO afecta datos existentes
-- ============================================================

-- PASO 1: Verificar valores actuales del ENUM (solo para referencia)
-- Ejecuta esto primero para ver los valores actuales:
SHOW COLUMNS FROM `usuarios` LIKE 'tipo';

-- ============================================================
-- PASO 2: MODIFICAR EL ENUM (AGREGAR NUEVOS PERFILES)
-- ============================================================
-- Este comando agrega los nuevos valores al final del ENUM
-- manteniendo TODOS los valores existentes intactos

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
    -- NUEVOS PERFILES AGREGADOS:
    'CONTADOR',
    'TUTOR',
    'MANTENIMIENTO'
) NOT NULL;

-- ============================================================
-- PASO 3: VERIFICACIÓN (Opcional - para confirmar el cambio)
-- ============================================================
-- Ejecuta esto después para verificar que el cambio se aplicó correctamente:
SHOW COLUMNS FROM `usuarios` LIKE 'tipo';

-- ============================================================
-- VERIFICACIÓN DE DATOS EXISTENTES (Opcional)
-- ============================================================
-- Verifica que todos los usuarios existentes siguen teniendo sus tipos válidos:
SELECT tipo, COUNT(*) as cantidad 
FROM usuarios 
GROUP BY tipo 
ORDER BY cantidad DESC;

-- ============================================================
-- NOTAS IMPORTANTES:
-- ============================================================
-- ✅ Este cambio es SEGURO porque:
--    1. Mantiene TODOS los valores existentes del ENUM
--    2. Solo AGREGA nuevos valores al final
--    3. NO modifica ningún dato existente en la tabla
--    4. NO cambia el orden de los valores existentes
--
-- ✅ Compatibilidad con sistema anterior:
--    - El sistema PHP anterior seguirá funcionando normalmente
--    - Los usuarios existentes mantendrán sus tipos actuales
--    - Solo se agregan nuevas opciones disponibles
--
-- ✅ Los nuevos perfiles disponibles serán:
--    - CONTADOR
--    - TUTOR
--    - MANTENIMIENTO
--
-- ============================================================

