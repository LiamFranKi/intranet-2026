-- ================================================================
-- CONSULTA FINAL: ALUMNOS MATRICULADOS EN 2026 CON PAGO DE MATRÍCULA
-- ================================================================
-- 
-- Lista alumnos matriculados en 2026 que han pagado su matrícula,
-- con clasificación NUEVO/ANTIGUO según estas reglas:
--
-- REGLAS PARA "NUEVO":
-- 1. Si el alumno NO tiene matrículas antes de 2026 → NUEVO
-- 2. Si tiene matrículas antes de 2026 pero NINGUNA es del 2025 → NUEVO  
--
-- REGLA PARA "ANTIGUO":
-- 3. Si tiene matrícula del 2025 → ANTIGUO
--
-- Campos retornados:
-- - DNI (nro_documento)
-- - Nombre
-- - Apellidos
-- - Nivel
-- - Grado y Sección
-- - Nuevo o Antiguo
-- ================================================================

SELECT 
    a.nro_documento AS DNI,
    a.nombres AS Nombre,
    CONCAT(a.apellido_paterno, ' ', a.apellido_materno) AS Apellidos,
    n.nombre AS Nivel,
    CONCAT(g.grado, '° ', g.seccion) AS 'Grado y Sección',
    CASE 
        -- Si tiene matrícula en 2025, es ANTIGUO
        WHEN EXISTS (
            SELECT 1 
            FROM matriculas m_antigua
            INNER JOIN grupos g_antigua ON m_antigua.grupo_id = g_antigua.id
            WHERE m_antigua.alumno_id = a.id 
            AND g_antigua.anio = 2025
        ) THEN 'ANTIGUO'
        -- Si no tiene matrícula en 2025, es NUEVO
        ELSE 'NUEVO'
    END AS 'Nuevo o Antiguo'
FROM 
    alumnos a
    INNER JOIN matriculas m ON a.id = m.alumno_id
    INNER JOIN grupos g ON m.grupo_id = g.id
    INNER JOIN niveles n ON g.nivel_id = n.id
WHERE 
    -- Solo matrículas del año 2026
    g.anio = 2026
    -- Solo alumnos que han pagado la matrícula 2026
    -- tipo = 0 corresponde a pago de MATRÍCULA (confirmado del sistema anterior)
    AND EXISTS (
        SELECT 1 
        FROM pagos p
        WHERE p.matricula_id = m.id
        AND p.tipo = 0  -- 0 = Matrícula
        AND p.estado_pago = 'CANCELADO'  -- Pago completado
        AND p.estado = 'ACTIVO'  -- Pago activo, no anulado
    )
ORDER BY 
    -- Ordenar por nivel: Inicial, Primaria, Secundaria
    FIELD(
        n.nombre, 
        'Inicial', 
        'Primaria', 
        'Secundaria'
    ),
    -- Si el nombre del nivel no coincide exactamente, ordenar alfabéticamente
    n.nombre,
    -- Luego por grado (numéricamente)
    g.grado,
    -- Luego por sección (alfabéticamente)
    g.seccion,
    -- Luego por Nuevo/Antiguo (Antiguo primero, luego Nuevo)
    CASE 
        WHEN EXISTS (
            SELECT 1 
            FROM matriculas m_antigua
            INNER JOIN grupos g_antigua ON m_antigua.grupo_id = g_antigua.id
            WHERE m_antigua.alumno_id = a.id 
            AND g_antigua.anio = 2025
        ) THEN 1  -- Antiguo primero
        ELSE 2     -- Nuevo después
    END,
    -- Finalmente por apellidos y nombres
    a.apellido_paterno, 
    a.apellido_materno, 
    a.nombres;

-- ================================================================
-- NOTAS IMPORTANTES ANTES DE EJECUTAR:
-- ================================================================
-- 
-- 1. Si necesitas filtrar por colegio específico, agrega al WHERE:
--    AND a.colegio_id = 1  -- Reemplaza 1 con el ID de tu colegio
--
-- 2. Si tienes un campo de estado en alumnos y quieres solo activos:
--    AND a.estado = 1  -- O el valor que uses para alumnos activos
--
-- 3. Si tienes un campo de estado en matriculas y quieres solo activas:
--    AND m.estado = 1  -- O el valor que uses para matrículas activas
--
-- 4. Para verificar qué tipos de pago existen en tu BD, ejecuta:
--    SELECT DISTINCT tipo, COUNT(*) as cantidad 
--    FROM pagos 
--    WHERE matricula_id IN (
--        SELECT id FROM matriculas WHERE grupo_id IN (
--            SELECT id FROM grupos WHERE anio = 2026
--        )
--    )
--    GROUP BY tipo;
--
-- 5. Si el resultado no muestra datos, verifica:
--    - Que existan matrículas con grupos del año 2026
--    - Que existan pagos de tipo 0 (matrícula) para esas matrículas
--    - Que los pagos tengan estado_pago = 'CANCELADO'
-- ================================================================

