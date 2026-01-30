-- ============================================================
-- BACKUP DE SEGURIDAD - ANTES DE MODIFICAR ESTRUCTURA
-- Fecha: 2026-01-29
-- Tablas: asignaturas_examenes_preguntas y asignaturas_examenes_preguntas_alternativas
-- ============================================================

-- Crear tablas de respaldo con timestamp
CREATE TABLE IF NOT EXISTS `asignaturas_examenes_preguntas_backup_20260129` LIKE `asignaturas_examenes_preguntas`;
INSERT INTO `asignaturas_examenes_preguntas_backup_20260129` SELECT * FROM `asignaturas_examenes_preguntas`;

CREATE TABLE IF NOT EXISTS `asignaturas_examenes_preguntas_alternativas_backup_20260129` LIKE `asignaturas_examenes_preguntas_alternativas`;
INSERT INTO `asignaturas_examenes_preguntas_alternativas_backup_20260129` SELECT * FROM `asignaturas_examenes_preguntas_alternativas`;

-- Verificar que el backup se creó correctamente
SELECT 
    'asignaturas_examenes_preguntas' AS tabla,
    COUNT(*) AS registros_originales
FROM asignaturas_examenes_preguntas
UNION ALL
SELECT 
    'asignaturas_examenes_preguntas_backup_20260129' AS tabla,
    COUNT(*) AS registros_backup
FROM asignaturas_examenes_preguntas_backup_20260129
UNION ALL
SELECT 
    'asignaturas_examenes_preguntas_alternativas' AS tabla,
    COUNT(*) AS registros_originales
FROM asignaturas_examenes_preguntas_alternativas
UNION ALL
SELECT 
    'asignaturas_examenes_preguntas_alternativas_backup_20260129' AS tabla,
    COUNT(*) AS registros_backup
FROM asignaturas_examenes_preguntas_alternativas_backup_20260129;

