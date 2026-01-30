-- ============================================================
-- MODIFICACIÓN DE ESTRUCTURA - PREGUNTAS Y ALTERNATIVAS
-- Fecha: 2026-01-29
-- Objetivo: Agregar nuevos tipos de preguntas y campos adicionales
-- ============================================================
-- IMPORTANTE: Ejecutar primero el backup_preguntas_antes_modificacion.sql
-- ============================================================

-- ============================================================
-- PASO 1: Modificar ENUM de tipo en asignaturas_examenes_preguntas
-- ============================================================
-- Esto agregará los nuevos tipos: VERDADERO_FALSO, RESPUESTA_CORTA, ORDENAR, EMPAREJAR, ARRASTRAR_Y_SOLTAR
-- Los tipos existentes (ALTERNATIVAS, COMPLETAR) se mantienen intactos

ALTER TABLE `asignaturas_examenes_preguntas` 
MODIFY COLUMN `tipo` ENUM(
    'ALTERNATIVAS',
    'COMPLETAR',
    'VERDADERO_FALSO',
    'RESPUESTA_CORTA',
    'ORDENAR',
    'EMPAREJAR',
    'ARRASTRAR_Y_SOLTAR'
) NOT NULL COMMENT 'Tipo de pregunta del examen';

-- ============================================================
-- PASO 2: Agregar campo JSON para datos adicionales
-- ============================================================
-- Este campo permitirá almacenar información específica de cada tipo de pregunta
-- sin necesidad de crear múltiples columnas

ALTER TABLE `asignaturas_examenes_preguntas` 
ADD COLUMN `datos_adicionales` JSON NULL COMMENT 'Datos específicos del tipo de pregunta (orden, pares, zonas, etc.)' 
AFTER `imagen_puzzle`;

-- ============================================================
-- PASO 3: Extender tabla de alternativas con campos opcionales
-- ============================================================
-- Estos campos solo se usarán para tipos específicos de preguntas

-- Campo para tipo ORDENAR: posición correcta del elemento
ALTER TABLE `asignaturas_examenes_preguntas_alternativas` 
ADD COLUMN `orden_posicion` INT(11) NULL COMMENT 'Para tipo ORDENAR: posición correcta del elemento (1, 2, 3, etc.)' 
AFTER `correcta`;

-- Campo para tipo EMPAREJAR: ID del elemento con el que se empareja
ALTER TABLE `asignaturas_examenes_preguntas_alternativas` 
ADD COLUMN `par_id` INT(11) NULL COMMENT 'Para tipo EMPAREJAR: ID de la alternativa con la que se empareja' 
AFTER `orden_posicion`;

-- Campo para tipo ARRASTRAR_Y_SOLTAR: zona donde debe soltarse
ALTER TABLE `asignaturas_examenes_preguntas_alternativas` 
ADD COLUMN `zona_drop` VARCHAR(100) NULL COMMENT 'Para tipo ARRASTRAR_Y_SOLTAR: identificador de la zona donde debe soltarse' 
AFTER `par_id`;

-- ============================================================
-- PASO 4: Verificar que las modificaciones se aplicaron correctamente
-- ============================================================

-- Verificar estructura de asignaturas_examenes_preguntas
DESCRIBE `asignaturas_examenes_preguntas`;

-- Verificar estructura de asignaturas_examenes_preguntas_alternativas
DESCRIBE `asignaturas_examenes_preguntas_alternativas`;

-- Verificar que los datos existentes no se afectaron
SELECT 
    tipo,
    COUNT(*) AS cantidad
FROM asignaturas_examenes_preguntas
GROUP BY tipo;

-- ============================================================
-- FIN DE MODIFICACIONES
-- ============================================================
-- Si algo sale mal, puedes restaurar desde las tablas de backup:
-- DROP TABLE asignaturas_examenes_preguntas;
-- RENAME TABLE asignaturas_examenes_preguntas_backup_20260129 TO asignaturas_examenes_preguntas;
-- (Y lo mismo para alternativas)

