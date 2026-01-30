-- ============================================================
-- SCRIPT DE RESTAURACIÓN - EN CASO DE PROBLEMAS
-- ============================================================
-- SOLO EJECUTAR SI ALGO SALE MAL CON LAS MODIFICACIONES
-- ============================================================

-- PASO 1: Eliminar las tablas modificadas (CUIDADO: Esto borra los datos actuales)
-- Descomentar solo si necesitas restaurar completamente
-- DROP TABLE IF EXISTS `asignaturas_examenes_preguntas`;
-- DROP TABLE IF EXISTS `asignaturas_examenes_preguntas_alternativas`;

-- PASO 2: Restaurar desde el backup
-- RENAME TABLE `asignaturas_examenes_preguntas_backup_20260129` TO `asignaturas_examenes_preguntas`;
-- RENAME TABLE `asignaturas_examenes_preguntas_alternativas_backup_20260129` TO `asignaturas_examenes_preguntas_alternativas`;

-- PASO 3: Verificar que se restauró correctamente
-- SELECT COUNT(*) FROM asignaturas_examenes_preguntas;
-- SELECT COUNT(*) FROM asignaturas_examenes_preguntas_alternativas;

