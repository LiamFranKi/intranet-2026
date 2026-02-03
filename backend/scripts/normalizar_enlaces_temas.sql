-- Script para normalizar enlaces en asignaturas_archivos que no tengan protocolo
-- Esto corrige enlaces existentes que fueron guardados sin http:// o https://

-- Actualizar enlaces que no tienen protocolo (agregar https://)
UPDATE asignaturas_archivos
SET enlace = CONCAT('https://', enlace)
WHERE enlace IS NOT NULL 
  AND enlace != ''
  AND enlace NOT LIKE 'http://%'
  AND enlace NOT LIKE 'https://%'
  AND enlace NOT LIKE '/%'  -- No modificar rutas relativas que empiezan con /
  AND enlace NOT LIKE 'Static/%';  -- No modificar rutas que empiezan con Static/

-- Verificar resultados
SELECT 
  id,
  nombre,
  enlace,
  CASE 
    WHEN enlace LIKE 'http://%' OR enlace LIKE 'https://%' THEN '✅ Tiene protocolo'
    WHEN enlace LIKE '/%' OR enlace LIKE 'Static/%' THEN '⚠️ Ruta relativa'
    ELSE '❌ Sin protocolo'
  END as estado
FROM asignaturas_archivos
WHERE enlace IS NOT NULL AND enlace != ''
ORDER BY id DESC
LIMIT 20;

