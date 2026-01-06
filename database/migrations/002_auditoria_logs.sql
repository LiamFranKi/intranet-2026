-- Tabla de Auditoría y Logs de Actividades
-- Registra TODAS las acciones de los usuarios en el sistema

CREATE TABLE IF NOT EXISTS auditoria_logs (
  id SERIAL PRIMARY KEY,
  usuario_id INTEGER NOT NULL, -- ID del usuario en MySQL
  colegio_id INTEGER NOT NULL,
  tipo_usuario VARCHAR(50) NOT NULL, -- ALUMNO, DOCENTE, TUTOR, APODERADO, ADMINISTRADOR
  accion VARCHAR(100) NOT NULL, -- LOGIN, LOGOUT, CREAR, EDITAR, ELIMINAR, VER, DESCARGAR, etc.
  modulo VARCHAR(100), -- EXAMENES, TAREAS, TEMAS, CALIFICACIONES, etc.
  entidad VARCHAR(100), -- examen, tarea, tema, etc.
  entidad_id INTEGER, -- ID de la entidad afectada (si aplica)
  descripcion TEXT, -- Descripción detallada de la acción
  url VARCHAR(500), -- URL de la página/endpoint accedido
  metodo_http VARCHAR(10), -- GET, POST, PUT, DELETE
  ip_address VARCHAR(45), -- IPv4 o IPv6
  user_agent TEXT, -- Navegador y sistema operativo
  datos_anteriores JSONB, -- Datos antes de la modificación (para editar/eliminar)
  datos_nuevos JSONB, -- Datos después de la modificación (para crear/editar)
  resultado VARCHAR(50), -- EXITOSO, ERROR, CANCELADO
  mensaje_error TEXT, -- Si hubo error, el mensaje
  duracion_ms INTEGER, -- Tiempo que tomó la acción en milisegundos
  fecha_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  fecha DATE GENERATED ALWAYS AS (DATE(fecha_hora)) STORED, -- Para búsquedas por fecha
  hora TIME GENERATED ALWAYS AS (TIME(fecha_hora)) STORED, -- Para búsquedas por hora
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Índices para búsquedas rápidas
CREATE INDEX IF NOT EXISTS idx_auditoria_usuario ON auditoria_logs(usuario_id);
CREATE INDEX IF NOT EXISTS idx_auditoria_colegio ON auditoria_logs(colegio_id);
CREATE INDEX IF NOT EXISTS idx_auditoria_tipo_usuario ON auditoria_logs(tipo_usuario);
CREATE INDEX IF NOT EXISTS idx_auditoria_accion ON auditoria_logs(accion);
CREATE INDEX IF NOT EXISTS idx_auditoria_modulo ON auditoria_logs(modulo);
CREATE INDEX IF NOT EXISTS idx_auditoria_entidad ON auditoria_logs(entidad, entidad_id);
CREATE INDEX IF NOT EXISTS idx_auditoria_fecha ON auditoria_logs(fecha);
CREATE INDEX IF NOT EXISTS idx_auditoria_fecha_hora ON auditoria_logs(fecha_hora);
CREATE INDEX IF NOT EXISTS idx_auditoria_resultado ON auditoria_logs(resultado);

-- Índice compuesto para búsquedas comunes
CREATE INDEX IF NOT EXISTS idx_auditoria_usuario_fecha ON auditoria_logs(usuario_id, fecha DESC);
CREATE INDEX IF NOT EXISTS idx_auditoria_colegio_fecha ON auditoria_logs(colegio_id, fecha DESC);

-- Comentarios en la tabla
COMMENT ON TABLE auditoria_logs IS 'Registro completo de todas las acciones de los usuarios en el sistema';
COMMENT ON COLUMN auditoria_logs.accion IS 'Tipo de acción: LOGIN, LOGOUT, CREAR, EDITAR, ELIMINAR, VER, DESCARGAR, CALIFICAR, etc.';
COMMENT ON COLUMN auditoria_logs.modulo IS 'Módulo del sistema donde ocurrió la acción: EXAMENES, TAREAS, TEMAS, etc.';
COMMENT ON COLUMN auditoria_logs.entidad IS 'Tipo de entidad afectada: examen, tarea, tema, etc.';
COMMENT ON COLUMN auditoria_logs.datos_anteriores IS 'Datos antes de modificar (para auditoría de cambios)';
COMMENT ON COLUMN auditoria_logs.datos_nuevos IS 'Datos después de modificar (para auditoría de cambios)';

