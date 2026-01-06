-- Esquema inicial para Aula Virtual
-- Base de datos: aula_virtual

-- NOTA: No necesitamos tabla usuarios_sync porque leemos directamente de MySQL
-- El login se hace directamente desde MySQL, no necesitamos sincronizar usuarios

-- Tabla de exámenes
CREATE TABLE IF NOT EXISTS examenes (
  id SERIAL PRIMARY KEY,
  colegio_id INTEGER NOT NULL,
  curso_id INTEGER NOT NULL, -- ID del curso en MySQL
  grupo_id INTEGER NOT NULL, -- ID del grupo en MySQL
  personal_id INTEGER NOT NULL, -- ID del docente en MySQL
  titulo VARCHAR(500) NOT NULL,
  descripcion TEXT,
  fecha_inicio TIMESTAMP NOT NULL,
  fecha_fin TIMESTAMP NOT NULL,
  tiempo_limite INTEGER, -- En minutos (null = sin límite)
  intentos_permitidos INTEGER DEFAULT 1,
  estado VARCHAR(20) DEFAULT 'ACTIVO', -- ACTIVO, FINALIZADO, CANCELADO
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de preguntas de exámenes
CREATE TABLE IF NOT EXISTS preguntas_examenes (
  id SERIAL PRIMARY KEY,
  examen_id INTEGER NOT NULL REFERENCES examenes(id) ON DELETE CASCADE,
  tipo VARCHAR(50) NOT NULL, -- OPCION_MULTIPLE, VERDADERO_FALSO, RESPUESTA_CORTA
  pregunta TEXT NOT NULL,
  opciones JSONB, -- Para opción múltiple: {"A": "Opción 1", "B": "Opción 2", ...}
  respuesta_correcta TEXT NOT NULL,
  puntos DECIMAL(5,2) DEFAULT 1.0,
  orden INTEGER NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de respuestas de estudiantes
CREATE TABLE IF NOT EXISTS respuestas_examenes (
  id SERIAL PRIMARY KEY,
  examen_id INTEGER NOT NULL REFERENCES examenes(id) ON DELETE CASCADE,
  pregunta_id INTEGER NOT NULL REFERENCES preguntas_examenes(id) ON DELETE CASCADE,
  alumno_id INTEGER NOT NULL, -- ID del alumno en MySQL
  respuesta TEXT,
  es_correcta BOOLEAN DEFAULT FALSE,
  puntos_obtenidos DECIMAL(5,2) DEFAULT 0,
  intento INTEGER DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de resultados de exámenes
CREATE TABLE IF NOT EXISTS resultados_examenes (
  id SERIAL PRIMARY KEY,
  examen_id INTEGER NOT NULL REFERENCES examenes(id) ON DELETE CASCADE,
  alumno_id INTEGER NOT NULL, -- ID del alumno en MySQL
  nota DECIMAL(5,2) NOT NULL,
  puntos_obtenidos DECIMAL(5,2) NOT NULL,
  puntos_totales DECIMAL(5,2) NOT NULL,
  porcentaje DECIMAL(5,2) NOT NULL,
  intento INTEGER DEFAULT 1,
  tiempo_utilizado INTEGER, -- En minutos
  fecha_inicio TIMESTAMP,
  fecha_finalizacion TIMESTAMP,
  estado VARCHAR(20) DEFAULT 'EN_PROCESO', -- EN_PROCESO, FINALIZADO, ABANDONADO
  exportado_a_mysql BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE(examen_id, alumno_id, intento)
);

-- Tabla de tareas
CREATE TABLE IF NOT EXISTS tareas (
  id SERIAL PRIMARY KEY,
  colegio_id INTEGER NOT NULL,
  curso_id INTEGER NOT NULL, -- ID del curso en MySQL
  grupo_id INTEGER NOT NULL, -- ID del grupo en MySQL
  personal_id INTEGER NOT NULL, -- ID del docente en MySQL
  titulo VARCHAR(500) NOT NULL,
  descripcion TEXT,
  fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  fecha_entrega TIMESTAMP NOT NULL,
  puntos_totales DECIMAL(5,2) DEFAULT 10.0,
  archivos_adjuntos JSONB, -- Array de URLs de archivos
  estado VARCHAR(20) DEFAULT 'ACTIVO', -- ACTIVO, FINALIZADO, CANCELADO
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de entregas de tareas
CREATE TABLE IF NOT EXISTS entregas_tareas (
  id SERIAL PRIMARY KEY,
  tarea_id INTEGER NOT NULL REFERENCES tareas(id) ON DELETE CASCADE,
  alumno_id INTEGER NOT NULL, -- ID del alumno en MySQL
  respuesta TEXT,
  archivos_adjuntos JSONB, -- Array de URLs de archivos
  fecha_entrega TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  nota DECIMAL(5,2),
  puntos_obtenidos DECIMAL(5,2) DEFAULT 0,
  comentario_docente TEXT,
  estado VARCHAR(20) DEFAULT 'ENTREGADO', -- ENTREGADO, CALIFICADO, DEVUELTO
  exportado_a_mysql BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE(tarea_id, alumno_id)
);

-- Tabla de temas/contenido
CREATE TABLE IF NOT EXISTS temas (
  id SERIAL PRIMARY KEY,
  colegio_id INTEGER NOT NULL,
  curso_id INTEGER NOT NULL, -- ID del curso en MySQL
  grupo_id INTEGER NOT NULL, -- ID del grupo en MySQL
  personal_id INTEGER NOT NULL, -- ID del docente en MySQL
  titulo VARCHAR(500) NOT NULL,
  contenido TEXT NOT NULL,
  tipo VARCHAR(50) DEFAULT 'TEORIA', -- TEORIA, PRACTICA, AYUDA
  archivos_adjuntos JSONB, -- Array de URLs de archivos
  enlaces_ayuda JSONB, -- Array de URLs
  videos_ayuda JSONB, -- Array de URLs de videos
  orden INTEGER DEFAULT 0,
  estado VARCHAR(20) DEFAULT 'ACTIVO', -- ACTIVO, INACTIVO
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de notificaciones
CREATE TABLE IF NOT EXISTS notificaciones (
  id SERIAL PRIMARY KEY,
  usuario_id INTEGER NOT NULL, -- ID del usuario en MySQL
  tipo VARCHAR(50) NOT NULL, -- EXAMEN, TAREA, TEMA, SISTEMA
  titulo VARCHAR(500) NOT NULL,
  mensaje TEXT NOT NULL,
  enlace VARCHAR(500),
  leida BOOLEAN DEFAULT FALSE,
  enviada BOOLEAN DEFAULT FALSE, -- Si se envió por email/push
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Índices para mejor performance
CREATE INDEX IF NOT EXISTS idx_examenes_colegio ON examenes(colegio_id);
CREATE INDEX IF NOT EXISTS idx_examenes_curso ON examenes(curso_id);
CREATE INDEX IF NOT EXISTS idx_examenes_grupo ON examenes(grupo_id);
CREATE INDEX IF NOT EXISTS idx_preguntas_examen ON preguntas_examenes(examen_id);
CREATE INDEX IF NOT EXISTS idx_respuestas_examen ON respuestas_examenes(examen_id);
CREATE INDEX IF NOT EXISTS idx_respuestas_alumno ON respuestas_examenes(alumno_id);
CREATE INDEX IF NOT EXISTS idx_resultados_examen ON resultados_examenes(examen_id);
CREATE INDEX IF NOT EXISTS idx_resultados_alumno ON resultados_examenes(alumno_id);
CREATE INDEX IF NOT EXISTS idx_tareas_colegio ON tareas(colegio_id);
CREATE INDEX IF NOT EXISTS idx_tareas_curso ON tareas(curso_id);
CREATE INDEX IF NOT EXISTS idx_tareas_grupo ON tareas(grupo_id);
CREATE INDEX IF NOT EXISTS idx_entregas_tarea ON entregas_tareas(tarea_id);
CREATE INDEX IF NOT EXISTS idx_entregas_alumno ON entregas_tareas(alumno_id);
CREATE INDEX IF NOT EXISTS idx_temas_colegio ON temas(colegio_id);
CREATE INDEX IF NOT EXISTS idx_temas_curso ON temas(curso_id);
CREATE INDEX IF NOT EXISTS idx_temas_grupo ON temas(grupo_id);
CREATE INDEX IF NOT EXISTS idx_notificaciones_usuario ON notificaciones(usuario_id);
CREATE INDEX IF NOT EXISTS idx_notificaciones_leida ON notificaciones(leida);

-- Función para actualizar updated_at automáticamente
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Triggers para updated_at
CREATE TRIGGER update_examenes_updated_at BEFORE UPDATE ON examenes
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_tareas_updated_at BEFORE UPDATE ON tareas
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_temas_updated_at BEFORE UPDATE ON temas
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_resultados_examenes_updated_at BEFORE UPDATE ON resultados_examenes
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_entregas_tareas_updated_at BEFORE UPDATE ON entregas_tareas
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

