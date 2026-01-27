const express = require('express');
const router = express.Router();
const multer = require('multer');
const path = require('path');
const fs = require('fs');
const { query, execute, getAnioActivo } = require('../utils/mysql');
const { authenticateToken, requireUserType } = require('../middleware/auth');
const { registrarAccion } = require('../utils/auditoria');
const { uploadToPHPServer } = require('../utils/ftpUpload');

// Configurar multer para subir fotos de personal
const personalStorage = multer.diskStorage({
  destination: (req, file, cb) => {
    const uploadPath = path.join(__dirname, '../../backend/uploads/personal');
    if (!fs.existsSync(uploadPath)) {
      fs.mkdirSync(uploadPath, { recursive: true });
    }
    cb(null, uploadPath);
  },
  filename: (req, file, cb) => {
    const uniqueSuffix = Date.now() + '-' + Math.round(Math.random() * 1E9);
    cb(null, `personal-${uniqueSuffix}${path.extname(file.originalname)}`);
  }
});

// Configurar multer para subir imágenes de publicaciones
const publicacionesStorage = multer.diskStorage({
  destination: (req, file, cb) => {
    const uploadPath = path.join(__dirname, '../../backend/uploads/publicaciones');
    if (!fs.existsSync(uploadPath)) {
      fs.mkdirSync(uploadPath, { recursive: true });
    }
    cb(null, uploadPath);
  },
  filename: (req, file, cb) => {
    const uniqueSuffix = Date.now() + '-' + Math.round(Math.random() * 1E9);
    cb(null, `publicacion-${uniqueSuffix}${path.extname(file.originalname)}`);
  }
});

// Configurar multer para subir archivos de publicaciones
const archivosStorage = multer.diskStorage({
  destination: (req, file, cb) => {
    const uploadPath = path.join(__dirname, '../../backend/uploads/archivos');
    if (!fs.existsSync(uploadPath)) {
      fs.mkdirSync(uploadPath, { recursive: true });
    }
    cb(null, uploadPath);
  },
  filename: (req, file, cb) => {
    const uniqueSuffix = Date.now() + '-' + Math.round(Math.random() * 1E9);
    cb(null, `archivo-${uniqueSuffix}${path.extname(file.originalname)}`);
  }
});

const fileFilter = (req, file, cb) => {
  const allowedTypes = /jpeg|jpg|png|gif|webp/;
  const extname = allowedTypes.test(path.extname(file.originalname).toLowerCase());
  const mimetype = allowedTypes.test(file.mimetype);
  
  if (mimetype && extname) {
    return cb(null, true);
  } else {
    cb(new Error('Solo se permiten imágenes (JPEG, JPG, PNG, GIF, WEBP)'));
  }
};

const uploadPersonal = multer({
  storage: personalStorage,
  limits: { fileSize: 5 * 1024 * 1024 }, // 5MB
  fileFilter: fileFilter
});

const uploadPublicaciones = multer({
  storage: publicacionesStorage,
  limits: { fileSize: 10 * 1024 * 1024 }, // 10MB
  fileFilter: fileFilter
});

const fileFilterArchivos = (req, file, cb) => {
  const allowedTypes = /pdf|doc|docx|xls|xlsx|ppt|pptx|txt|zip|rar/;
  const extname = allowedTypes.test(path.extname(file.originalname).toLowerCase());
  
  if (extname) {
    return cb(null, true);
  } else {
    cb(new Error('Tipo de archivo no permitido. Solo se permiten: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, ZIP, RAR'));
  }
};

const uploadArchivos = multer({
  storage: archivosStorage,
  limits: { fileSize: 50 * 1024 * 1024 }, // 50MB
  fileFilter: fileFilterArchivos
});

// Configurar multer para subir archivos de mensajes
const mensajesStorage = multer.diskStorage({
  destination: (req, file, cb) => {
    const uploadPath = path.join(__dirname, '../../backend/uploads/mensajes');
    if (!fs.existsSync(uploadPath)) {
      fs.mkdirSync(uploadPath, { recursive: true });
    }
    cb(null, uploadPath);
  },
  filename: (req, file, cb) => {
    const uniqueSuffix = Date.now() + '-' + Math.round(Math.random() * 1E9);
    cb(null, `mensaje-${uniqueSuffix}${path.extname(file.originalname)}`);
  }
});

const fileFilterMensajes = (req, file, cb) => {
  // Permitir cualquier tipo de archivo para mensajes
  cb(null, true);
};

const uploadMensajes = multer({
  storage: mensajesStorage,
  limits: { fileSize: 50 * 1024 * 1024 }, // 50MB por archivo
  fileFilter: fileFilterMensajes
});

// Todas las rutas requieren autenticación y ser DOCENTE
router.use(authenticateToken);
router.use(requireUserType('DOCENTE'));

/**
 * GET /api/docente/dashboard
 * Obtener datos del dashboard del docente
 */
router.get('/dashboard', async (req, res) => {
  try {
    const { usuario_id, colegio_id, anio_activo } = req.user;

    // Obtener datos del personal/docente
    const personal = await query(
      `SELECT p.*, u.tipo as tipo_usuario
       FROM personal p
       INNER JOIN usuarios u ON u.personal_id = p.id
       WHERE u.id = ? AND u.colegio_id = ? AND u.estado = 'ACTIVO'`,
      [usuario_id, colegio_id]
    );

    if (personal.length === 0) {
      return res.status(404).json({ error: 'Docente no encontrado' });
    }

    const docente = personal[0];

    // Contar grupos asignados (grupos distintos donde el docente tiene asignaturas)
    const gruposAsignados = await query(
      `SELECT COUNT(DISTINCT g.id) as total
       FROM asignaturas a
       INNER JOIN grupos g ON g.id = a.grupo_id
       WHERE a.personal_id = ? AND a.colegio_id = ? AND g.anio = ?`,
      [docente.id, colegio_id, anio_activo]
    );

    // Contar cursos asignados
    // asignaturas tiene grupo_id directamente, no area_curso_id. El año viene de grupos.anio
    const cursosAsignados = await query(
      `SELECT COUNT(DISTINCT a.id) as total
       FROM asignaturas a
       INNER JOIN grupos g ON g.id = a.grupo_id
       WHERE a.personal_id = ? AND a.colegio_id = ? AND g.anio = ?`,
      [docente.id, colegio_id, anio_activo]
    );

    // Contar estudiantes totales
    const estudiantes = await query(
      `SELECT COUNT(DISTINCT m.alumno_id) as total
       FROM asignaturas a
       INNER JOIN grupos g ON g.id = a.grupo_id
       INNER JOIN matriculas m ON m.grupo_id = g.id
       WHERE a.personal_id = ? AND a.colegio_id = ? AND g.anio = ? 
       AND m.estado = 0`,
      [docente.id, colegio_id, anio_activo]
    );

    // Próximos exámenes (todos los futuros, sin límite de días)
    // asignaturas_examenes tiene fecha_desde, fecha_hasta, hora_desde, hora_hasta (NO tiene fecha_inicio)
    // asignaturas_examenes SÍ tiene asignatura_id y titulo (NO tiene descripcion)
    // IMPORTANTE: Comparar solo fechas (sin hora) para incluir eventos de hoy
    // Usar DATE() en ambos lados para comparar solo fechas
    const proximosExamenes = await query(
      `SELECT ae.*, 
              COALESCE(ae.titulo, 'Examen') as titulo,
              c.nombre as asignatura_nombre, 
              g.grado, 
              g.seccion, 
              n.nombre as nivel_nombre,
              DATE(ae.fecha_desde) as fecha_evento
       FROM asignaturas_examenes ae
       INNER JOIN asignaturas a ON a.id = ae.asignatura_id
       INNER JOIN grupos g ON g.id = a.grupo_id
       INNER JOIN niveles n ON n.id = g.nivel_id
       INNER JOIN cursos c ON c.id = a.curso_id
       WHERE a.personal_id = ? AND a.colegio_id = ? AND g.anio = ?
       AND DATE(ae.fecha_desde) >= DATE(NOW())
       ORDER BY ae.fecha_desde ASC`,
      [docente.id, colegio_id, anio_activo]
    );

    // Próximas tareas (todas las futuras, sin límite de días)
    // asignaturas_actividades tiene fecha_inicio y fecha_fin (NO tiene fecha_limite)
    // IMPORTANTE: Comparar solo fechas (sin hora) para incluir eventos de hoy
    // Usar DATE() en ambos lados para comparar solo fechas
    const proximasTareas = await query(
      `SELECT aa.*, 
              c.nombre as asignatura_nombre, 
              g.grado, 
              g.seccion,
              DATE(aa.fecha_fin) as fecha_evento
       FROM asignaturas_actividades aa
       INNER JOIN asignaturas a ON a.id = aa.asignatura_id
       INNER JOIN grupos g ON g.id = a.grupo_id
       INNER JOIN cursos c ON c.id = a.curso_id
       WHERE a.personal_id = ? AND a.colegio_id = ? AND g.anio = ?
       AND DATE(aa.fecha_fin) >= DATE(NOW())
       ORDER BY aa.fecha_fin ASC`,
      [docente.id, colegio_id, anio_activo]
    );

    // Actividades próximas (solo futuras, no pasadas)
    // IMPORTANTE: Comparar solo fechas (sin hora) para incluir eventos de hoy
    // Usar DATE() en ambos lados para comparar solo fechas
    const actividades = await query(
      `SELECT a.*,
              DATE(a.fecha_inicio) as fecha_evento
       FROM actividades a
       WHERE a.colegio_id = ?
       AND DATE(a.fecha_inicio) >= DATE(NOW())
       ORDER BY a.fecha_inicio ASC`,
      [colegio_id]
    );

    // Construir nombre completo
    const nombreCompleto = `${docente.nombres || ''} ${docente.apellidos || ''}`.trim();
    
    // Construir URL de foto
    let fotoUrl = null;
    if (docente.foto && docente.foto !== '') {
      const isProduction = process.env.NODE_ENV === 'production';
      if (isProduction) {
        fotoUrl = `https://vanguardschools.edu.pe/Static/Image/Fotos/${docente.foto}`;
      } else {
        fotoUrl = `/uploads/personal/${docente.foto}`;
      }
    }

    res.json({
      docente: {
        id: docente.id,
        nombres: nombreCompleto || docente.nombres || 'Docente',
        apellidos: docente.apellidos || '',
        foto: fotoUrl,
        email: docente.email || ''
      },
      estadisticas: {
        gruposAsignados: gruposAsignados[0]?.total || 0,
        cursosAsignados: cursosAsignados[0]?.total || 0,
        estudiantes: estudiantes[0]?.total || 0
      },
      proximosExamenes: proximosExamenes || [],
      proximasTareas: proximasTareas || [],
      actividades: actividades || []
    });
  } catch (error) {
    console.error('Error en dashboard docente:', error);
    res.status(500).json({ error: 'Error al obtener datos del dashboard' });
  }
});

/**
 * GET /api/docente/perfil
 * Obtener perfil del docente
 */
router.get('/perfil', async (req, res) => {
  try {
    const { usuario_id, colegio_id } = req.user;

    const personal = await query(
      `SELECT p.*, u.tipo as tipo_usuario, u.usuario as dni
       FROM personal p
       INNER JOIN usuarios u ON u.personal_id = p.id
       WHERE u.id = ? AND u.colegio_id = ? AND u.estado = 'ACTIVO'`,
      [usuario_id, colegio_id]
    );

    if (personal.length === 0) {
      return res.status(404).json({ error: 'Docente no encontrado' });
    }

    const docente = personal[0];

    // Construir URL de foto
    let fotoUrl = null;
    if (docente.foto && docente.foto !== '') {
      const isProduction = process.env.NODE_ENV === 'production';
      if (isProduction) {
        fotoUrl = `https://vanguardschools.edu.pe/Static/Image/Fotos/${docente.foto}`;
      } else {
        fotoUrl = `/uploads/personal/${docente.foto}`;
      }
    }

    res.json({
      id: docente.id,
      nombres: docente.nombres,
      apellidos: docente.apellidos,
      dni: docente.dni,
      email: docente.email,
      telefono_fijo: docente.telefono_fijo,
      telefono_celular: docente.telefono_celular,
      direccion: docente.direccion,
      foto: fotoUrl, // URL completa en lugar de solo el nombre
      cargo: docente.cargo,
      profesion: docente.profesion,
      fecha_nacimiento: docente.fecha_nacimiento,
      fecha_ingreso: docente.fecha_ingreso,
      hora_entrada: docente.hora_entrada,
      hora_salida: docente.hora_salida
    });
  } catch (error) {
    console.error('Error obteniendo perfil:', error);
    res.status(500).json({ error: 'Error al obtener perfil' });
  }
});

/**
 * PUT /api/docente/perfil
 * Actualizar perfil del docente (incluyendo foto)
 */
router.put('/perfil', uploadPersonal.single('foto'), async (req, res) => {
  try {
    const { usuario_id, colegio_id } = req.user;
    const { nombres, apellidos, email, telefono_fijo, telefono_celular, direccion, fecha_nacimiento } = req.body;

    // Obtener docente actual
    const personal = await query(
      `SELECT p.* FROM personal p
       INNER JOIN usuarios u ON u.personal_id = p.id
       WHERE u.id = ? AND u.colegio_id = ?`,
      [usuario_id, colegio_id]
    );

    if (personal.length === 0) {
      return res.status(404).json({ error: 'Docente no encontrado' });
    }

    const docente = personal[0];
    
    // Guardar datos anteriores para auditoría
    const datosAnteriores = {
      nombres: docente.nombres,
      apellidos: docente.apellidos,
      email: docente.email,
      telefono_fijo: docente.telefono_fijo,
      telefono_celular: docente.telefono_celular,
      direccion: docente.direccion,
      fecha_nacimiento: docente.fecha_nacimiento,
      foto: docente.foto
    };
    
    let fotoPath = docente.foto;

    // Si se subió una nueva foto
    if (req.file) {
      // Eliminar foto anterior si existe
      if (docente.foto && docente.foto !== '') {
        const oldFotoPath = path.join(__dirname, '../../backend/uploads/personal', path.basename(docente.foto));
        if (fs.existsSync(oldFotoPath)) {
          fs.unlinkSync(oldFotoPath);
        }
      }
      
      // Guardar nueva foto - solo el nombre del archivo, no la ruta completa
      fotoPath = req.file.filename;
    }

    // ACTUALIZAR DATOS EN LA BASE DE DATOS
    await execute(
      `UPDATE personal SET
        nombres = ?,
        apellidos = ?,
        email = ?,
        telefono_fijo = ?,
        telefono_celular = ?,
        direccion = ?,
        foto = ?,
        fecha_nacimiento = ?
      WHERE id = ?`,
      [
        nombres || docente.nombres,
        apellidos || docente.apellidos,
        email || docente.email,
        telefono_fijo || docente.telefono_fijo,
        telefono_celular || docente.telefono_celular,
        direccion || docente.direccion,
        fotoPath,
        fecha_nacimiento || docente.fecha_nacimiento,
        docente.id
      ]
    );

    // Registrar acción en auditoría
    await registrarAccion({
      usuario_id,
      colegio_id,
      tipo_usuario: req.user.tipo || 'DOCENTE',
      accion: 'EDITAR',
      modulo: 'PERFIL',
      entidad: 'personal',
      entidad_id: docente.id,
      descripcion: 'Docente actualizó su perfil',
      url: req.originalUrl,
      metodo_http: req.method,
      ip_address: req.ip || req.connection.remoteAddress,
      user_agent: req.get('user-agent'),
      datos_anteriores: datosAnteriores,
      datos_nuevos: {
        nombres: nombres || docente.nombres,
        apellidos: apellidos || docente.apellidos,
        email: email || docente.email,
        telefono_fijo: telefono_fijo || docente.telefono_fijo,
        telefono_celular: telefono_celular || docente.telefono_celular,
        direccion: direccion || docente.direccion,
        fecha_nacimiento: fecha_nacimiento || docente.fecha_nacimiento,
        foto: fotoPath
      },
      resultado: 'EXITOSO'
    });

    // Obtener el perfil completo actualizado para devolverlo
    const personalActualizado = await query(
      `SELECT p.*, u.tipo as tipo_usuario, u.usuario as dni
       FROM personal p
       INNER JOIN usuarios u ON u.personal_id = p.id
       WHERE u.id = ? AND u.colegio_id = ? AND u.estado = 'ACTIVO'`,
      [usuario_id, colegio_id]
    );

    const docenteActualizado = personalActualizado[0];

    // Construir URL de foto desde la BD actualizada
    let fotoUrl = null;
    const fotoEnBD = docenteActualizado.foto;
    if (fotoEnBD && fotoEnBD !== '') {
      const isProduction = process.env.NODE_ENV === 'production';
      // Si ya es una ruta completa (empieza con /uploads/), construir URL completa
      if (fotoEnBD.startsWith('/uploads/')) {
        if (isProduction) {
          fotoUrl = `https://vanguardschools.edu.pe${fotoEnBD}`;
        } else {
          fotoUrl = `http://localhost:5000${fotoEnBD}`;
        }
      } else if (fotoEnBD.startsWith('http')) {
        // Si ya es una URL completa, usarla directamente
        fotoUrl = fotoEnBD;
      } else {
        // Es solo el nombre del archivo
        if (isProduction) {
          fotoUrl = `https://vanguardschools.edu.pe/Static/Image/Fotos/${fotoEnBD}`;
        } else {
          fotoUrl = `http://localhost:5000/uploads/personal/${fotoEnBD}`;
        }
      }
    }

    res.json({
      success: true,
      message: 'Perfil actualizado correctamente',
      docente: {
        id: docenteActualizado.id,
        nombres: docenteActualizado.nombres,
        apellidos: docenteActualizado.apellidos,
        dni: docenteActualizado.dni,
        email: docenteActualizado.email,
        telefono_fijo: docenteActualizado.telefono_fijo,
        telefono_celular: docenteActualizado.telefono_celular,
        direccion: docenteActualizado.direccion,
        foto: fotoUrl,
        cargo: docenteActualizado.cargo,
        profesion: docenteActualizado.profesion,
        fecha_nacimiento: docenteActualizado.fecha_nacimiento,
        fecha_ingreso: docenteActualizado.fecha_ingreso,
        hora_entrada: docenteActualizado.hora_entrada,
        hora_salida: docenteActualizado.hora_salida
      },
      // También devolver el perfil completo para que el frontend pueda actualizar el estado
      perfil: {
        id: docenteActualizado.id,
        nombres: docenteActualizado.nombres,
        apellidos: docenteActualizado.apellidos,
        dni: docenteActualizado.dni,
        email: docenteActualizado.email,
        telefono_fijo: docenteActualizado.telefono_fijo,
        telefono_celular: docenteActualizado.telefono_celular,
        direccion: docenteActualizado.direccion,
        foto: fotoUrl,
        cargo: docenteActualizado.cargo,
        profesion: docenteActualizado.profesion,
        fecha_nacimiento: docenteActualizado.fecha_nacimiento,
        fecha_ingreso: docenteActualizado.fecha_ingreso,
        hora_entrada: docenteActualizado.hora_entrada,
        hora_salida: docenteActualizado.hora_salida
      }
    });
  } catch (error) {
    console.error('Error actualizando perfil:', error);
    res.status(500).json({ error: 'Error al actualizar perfil' });
  }
});

/**
 * PUT /api/docente/perfil/password
 * Cambiar contraseña del docente
 */
router.put('/perfil/password', async (req, res) => {
  try {
    const { usuario_id } = req.user;
    const { password_actual, password_nueva } = req.body;

    if (!password_actual || !password_nueva) {
      return res.status(400).json({ error: 'Contraseña actual y nueva son requeridas' });
    }

    if (password_nueva.length < 6) {
      return res.status(400).json({ error: 'La nueva contraseña debe tener al menos 6 caracteres' });
    }

    // Obtener usuario actual
    const usuarios = await query(
      `SELECT u.id, u.password, u.tipo, u.colegio_id FROM usuarios u WHERE u.id = ?`,
      [usuario_id]
    );

    if (usuarios.length === 0) {
      return res.status(404).json({ error: 'Usuario no encontrado' });
    }

    const usuarioDB = usuarios[0];

    // Validar contraseña actual (SHA1 como en PHP)
    const crypto = require('crypto');
    const passwordHashActual = crypto.createHash('sha1').update(password_actual).digest('hex');
    
    if (usuarioDB.password !== passwordHashActual) {
      return res.status(401).json({ error: 'Contraseña actual incorrecta' });
    }

    // Generar hash de la nueva contraseña
    const passwordHashNueva = crypto.createHash('sha1').update(password_nueva).digest('hex');

    // Actualizar contraseña en la base de datos
    await execute(
      `UPDATE usuarios SET password = ? WHERE id = ?`,
      [passwordHashNueva, usuario_id]
    );

    // Registrar acción en auditoría
    await registrarAccion({
      usuario_id,
      colegio_id: usuarioDB.colegio_id,
      tipo_usuario: usuarioDB.tipo,
      accion: 'CAMBIAR_PASSWORD',
      modulo: 'PERFIL',
      entidad: 'usuario',
      entidad_id: usuario_id,
      descripcion: 'Docente cambió su contraseña',
      url: req.originalUrl,
      metodo_http: req.method,
      ip_address: req.ip || req.connection.remoteAddress,
      user_agent: req.get('user-agent'),
      resultado: 'EXITOSO'
    });

    res.json({ 
      success: true, 
      message: 'Contraseña actualizada correctamente' 
    });
  } catch (error) {
    console.error('Error cambiando contraseña:', error);
    res.status(500).json({ error: 'Error al cambiar la contraseña' });
  }
});

/**
 * GET /api/docente/grupos
 * Obtener grupos asignados al docente
 */
router.get('/grupos', async (req, res) => {
  try {
    const { usuario_id, colegio_id, anio_activo } = req.user;

    // Obtener personal_id del token
    const personalId = req.user.personal_id;
    
    if (!personalId) {
      return res.status(404).json({ error: 'Docente no encontrado' });
    }

    // Obtener grupos asignados con conteo de alumnos
    // asignaturas tiene grupo_id directamente, no area_curso_id
    // El conteo de alumnos se hace igual que en el sistema anterior: estado = 0 OR estado = 4
    const grupos = await query(
      `SELECT DISTINCT g.*, 
              n.nombre as nivel_nombre, 
              t.nombre as turno_nombre,
              (SELECT COUNT(*) 
               FROM matriculas m 
               WHERE m.grupo_id = g.id 
               AND m.colegio_id = ? 
               AND (m.estado = 0 OR m.estado = 4)) as total_alumnos
       FROM grupos g
       INNER JOIN niveles n ON n.id = g.nivel_id
       INNER JOIN turnos t ON t.id = g.turno_id
       INNER JOIN asignaturas a ON a.grupo_id = g.id
       WHERE a.personal_id = ? AND g.colegio_id = ? AND g.anio = ?
       ORDER BY n.nombre, g.grado, g.seccion`,
      [colegio_id, personalId, colegio_id, anio_activo]
    );

    res.json({ grupos: grupos || [] });
  } catch (error) {
    console.error('Error obteniendo grupos:', error);
    res.status(500).json({ error: 'Error al obtener grupos asignados' });
  }
});

/**
 * GET /api/docente/grupos/:grupoId/alumnos
 * Obtener lista de alumnos de un grupo
 */
router.get('/grupos/:grupoId/alumnos', async (req, res) => {
  try {
    const { grupoId } = req.params;
    const { colegio_id, anio_activo } = req.user;

    // Validar parámetros
    if (!grupoId) {
      return res.status(400).json({ error: 'ID de grupo es requerido' });
    }
    if (!colegio_id) {
      return res.status(400).json({ error: 'ID de colegio es requerido' });
    }
    if (!anio_activo) {
      return res.status(400).json({ error: 'Año académico es requerido' });
    }

    // Verificar que el docente tiene acceso a este grupo
    const personalId = req.user.personal_id;
    
    if (!personalId) {
      return res.status(404).json({ error: 'Docente no encontrado' });
    }

    const tieneAcceso = await query(
      `SELECT COUNT(*) as count
       FROM asignaturas a
       INNER JOIN grupos g ON g.id = a.grupo_id
       WHERE a.personal_id = ? AND a.grupo_id = ? AND a.colegio_id = ? AND g.anio = ?`,
      [personalId, grupoId, colegio_id, anio_activo]
    );

    if (!tieneAcceso || tieneAcceso.length === 0 || tieneAcceso[0].count === 0) {
      return res.status(403).json({ error: 'No tienes acceso a este grupo' });
    }

    // Obtener alumnos del grupo con fecha de nacimiento y teléfono (del alumno o apoderado)
    // Los alumnos se relacionan con apoderados a través de la tabla familias
    const alumnos = await query(
      `SELECT a.*, 
              m.id as matricula_id,
              m.fecha_registro, 
              m.estado as estado_matricula,
              a.fecha_nacimiento,
              COALESCE(
                NULLIF(ap.telefono_celular, ''), 
                NULLIF(ap.telefono_fijo, ''),
                'N/A'
              ) as telefono
       FROM alumnos a
       INNER JOIN matriculas m ON m.alumno_id = a.id
       LEFT JOIN familias f ON f.alumno_id = a.id
       LEFT JOIN apoderados ap ON ap.id = f.apoderado_id
       WHERE m.grupo_id = ? AND m.colegio_id = ? AND m.estado = 0
       ORDER BY a.apellido_paterno, a.apellido_materno, a.nombres`,
      [grupoId, colegio_id]
    );

    res.json({ alumnos: alumnos || [] });
  } catch (error) {
    console.error('Error obteniendo alumnos:', error);
    console.error('Stack:', error.stack);
    res.status(500).json({ error: 'Error al obtener lista de alumnos' });
  }
});

/**
 * GET /api/docente/alumnos/:alumnoId/info
 * Obtener información completa del alumno con historial de matrículas
 */
router.get('/alumnos/:alumnoId/info', async (req, res) => {
  try {
    const { alumnoId } = req.params;
    const { colegio_id, anio_activo } = req.user;

    // Obtener información del alumno
    const alumno = await query(
      `SELECT a.*, 
              p.nombre as pais_nacimiento_nombre
       FROM alumnos a
       LEFT JOIN paises p ON p.id = a.pais_nacimiento_id
       WHERE a.id = ? AND a.colegio_id = ?`,
      [alumnoId, colegio_id]
    );

    if (!alumno || alumno.length === 0) {
      return res.status(404).json({ error: 'Alumno no encontrado' });
    }

    // Obtener apoderados (padre y madre)
    const apoderados = await query(
      `SELECT ap.*, 
              f.alumno_id,
              CASE 
                WHEN ap.parentesco = 0 THEN 'Padre'
                WHEN ap.parentesco = 1 THEN 'Madre'
                ELSE 'Otro'
              END as parentesco_nombre
       FROM apoderados ap
       INNER JOIN familias f ON f.apoderado_id = ap.id
       WHERE f.alumno_id = ? AND ap.colegio_id = ?
       AND ap.parentesco IN (0, 1)
       ORDER BY ap.parentesco ASC`,
      [alumnoId, colegio_id]
    );

    // Separar padre y madre
    const padre = apoderados.find(a => a.parentesco === 0) || null;
    const madre = apoderados.find(a => a.parentesco === 1) || null;

    // Obtener nivel actual del alumno (desde la matrícula actual)
    const nivelActual = await query(
      `SELECT n.id as nivel_id, 
              n.nombre as nivel_nombre,
              g.grado,
              g.seccion
       FROM matriculas m
       INNER JOIN grupos g ON g.id = m.grupo_id
       INNER JOIN niveles n ON n.id = g.nivel_id
       WHERE m.alumno_id = ? AND m.colegio_id = ? AND g.anio = ? 
       AND (m.estado = 0 OR m.estado = 4)
       LIMIT 1`,
      [alumnoId, colegio_id, anio_activo]
    );

    // Obtener avatar actual del alumno según su sexo y nivel
    // IMPORTANTE: 
    // - Alumnos: sexo = 0 es Masculino, sexo = 1 es Femenino
    // - Avatares: sexo = 0 es Femenino, sexo = 1 es Masculino (opuesto)
    // Si alumno es masculino (sexo=0), buscar avatar con sexo=1
    // Si alumno es femenino (sexo=1), buscar avatar con sexo=0
    const sexoAlumno = alumno[0].sexo;
    const sexoAvatarBuscado = sexoAlumno === 0 ? 1 : (sexoAlumno === 1 ? 0 : null);
    const nivelId = nivelActual && nivelActual.length > 0 ? nivelActual[0].nivel_id : null;
    
    // Buscar avatar del alumno con el sexo correcto
    // IMPORTANTE: asi.level es el nivel del avatar (1, 2, 3...), no el nivel_id de la tabla niveles
    let avatarAlumno = [];
    if (sexoAvatarBuscado !== null) {
      // Buscar avatar con sexo correcto del alumno
      avatarAlumno = await query(
        `SELECT asi.*, 
                ass.created_at as fecha_compra,
                ass.student_id
         FROM avatar_shop_sales ass
         INNER JOIN avatar_shop_items asi ON asi.id = ass.item_id
         WHERE ass.student_id = ? 
         AND (asi.sexo = ? OR asi.sexo IS NULL)
         ORDER BY asi.level DESC, ass.created_at DESC
         LIMIT 1`,
        [alumnoId, sexoAvatarBuscado]
      );
    } else {
      // Si no tiene sexo definido, buscar cualquier avatar
      avatarAlumno = await query(
        `SELECT asi.*, 
                ass.created_at as fecha_compra,
                ass.student_id
         FROM avatar_shop_sales ass
         INNER JOIN avatar_shop_items asi ON asi.id = ass.item_id
         WHERE ass.student_id = ?
         ORDER BY asi.level DESC, ass.created_at DESC
         LIMIT 1`,
        [alumnoId]
      );
    }

    // Obtener historial de matrículas agrupado por nivel
    const matriculas = await query(
      `SELECT m.id, m.grupo_id, m.fecha_registro, m.estado,
              g.grado, g.seccion, g.anio,
              n.id as nivel_id, n.nombre as nivel_nombre,
              t.nombre as turno_nombre
       FROM matriculas m
       INNER JOIN grupos g ON g.id = m.grupo_id
       INNER JOIN niveles n ON n.id = g.nivel_id
       LEFT JOIN turnos t ON t.id = g.turno_id
       WHERE m.alumno_id = ? AND m.colegio_id = ?
       ORDER BY n.id, g.anio DESC, g.grado, g.seccion`,
      [alumnoId, colegio_id]
    );

    // Obtener la matrícula actual del año activo (si existe) para el QR
    const matriculaActual = await query(
      `SELECT m.id
       FROM matriculas m
       INNER JOIN grupos g ON g.id = m.grupo_id
       WHERE m.alumno_id = ? AND m.colegio_id = ? AND g.anio = ? AND (m.estado = 0 OR m.estado = 4)
       LIMIT 1`,
      [alumnoId, colegio_id, anio_activo]
    );

    // Agrupar matrículas por nivel
    const matriculasPorNivel = {};
    matriculas.forEach(matricula => {
      if (!matriculasPorNivel[matricula.nivel_id]) {
        matriculasPorNivel[matricula.nivel_id] = {
          nivel_id: matricula.nivel_id,
          nivel_nombre: matricula.nivel_nombre,
          matriculas: []
        };
      }
      matriculasPorNivel[matricula.nivel_id].matriculas.push({
        id: matricula.id,
        grado: matricula.grado,
        seccion: matricula.seccion,
        anio: matricula.anio,
        turno_nombre: matricula.turno_nombre,
        fecha_registro: matricula.fecha_registro,
        estado: matricula.estado
      });
    });

    // Construir URL de la foto del alumno
    let fotoUrl = null;
    if (alumno[0].foto) {
      const baseUrl = process.env.BASE_URL || 'http://localhost:5000';
      fotoUrl = `${baseUrl}/Static/Image/Fotos/${alumno[0].foto}`;
    }

    // Construir URL del avatar del alumno
    let avatarUrl = null;
    if (avatarAlumno && avatarAlumno.length > 0) {
      const baseUrl = process.env.BASE_URL || 'http://localhost:5000';
      avatarUrl = avatarAlumno[0].image 
        ? `${baseUrl}/Static/Image/Avatars/${avatarAlumno[0].image}`
        : null;
    }

    // Generar hash SHA1 del matricula_id para el QR (igual que sistema anterior)
    const crypto = require('crypto');
    const matriculaId = matriculaActual && matriculaActual.length > 0 ? matriculaActual[0].id : null;
    const qrCode = matriculaId ? crypto.createHash('sha1').update(String(matriculaId)).digest('hex') : null;

    // Contar estrellas del alumno (sumar niveles de todos los avatares comprados)
    const estrellasTotal = avatarAlumno && avatarAlumno.length > 0 
      ? avatarAlumno[0].level || 0 
      : 0;

    res.json({
      alumno: {
        ...alumno[0],
        foto_url: fotoUrl,
        avatar: avatarAlumno && avatarAlumno.length > 0 ? {
          id: avatarAlumno[0].id,
          name: avatarAlumno[0].name,
          description: avatarAlumno[0].description,
          level: avatarAlumno[0].level,
          image: avatarAlumno[0].image,
          image_url: avatarUrl,
          fecha_compra: avatarAlumno[0].fecha_compra
        } : null,
        nivel_actual: nivelActual && nivelActual.length > 0 ? {
          nivel_id: nivelActual[0].nivel_id,
          nivel_nombre: nivelActual[0].nivel_nombre,
          grado: nivelActual[0].grado,
          seccion: nivelActual[0].seccion
        } : null,
        estrellas: estrellasTotal
      },
      apoderados: {
        padre: padre ? {
          nombres: padre.nombres,
          apellido_paterno: padre.apellido_paterno,
          apellido_materno: padre.apellido_materno,
          nro_documento: padre.nro_documento,
          telefono_fijo: padre.telefono_fijo,
          telefono_celular: padre.telefono_celular,
          email: padre.email,
          direccion: padre.direccion,
          ocupacion: padre.ocupacion,
          centro_trabajo_direccion: padre.centro_trabajo_direccion
        } : null,
        madre: madre ? {
          nombres: madre.nombres,
          apellido_paterno: madre.apellido_paterno,
          apellido_materno: madre.apellido_materno,
          nro_documento: madre.nro_documento,
          telefono_fijo: madre.telefono_fijo,
          telefono_celular: madre.telefono_celular,
          email: madre.email,
          direccion: madre.direccion,
          ocupacion: madre.ocupacion,
          centro_trabajo_direccion: madre.centro_trabajo_direccion
        } : null
      },
      matriculas_por_nivel: Object.values(matriculasPorNivel),
      matricula_actual_id: matriculaId,
      qr_code: qrCode
    });
  } catch (error) {
    console.error('Error obteniendo información del alumno:', error);
    res.status(500).json({ error: 'Error al obtener información del alumno' });
  }
});

/**
 * GET /api/docente/cursos
 * Obtener cursos asignados al docente
 */
router.get('/cursos', async (req, res) => {
  try {
    const { usuario_id, colegio_id, anio_activo } = req.user;

    // Obtener personal_id del token
    const personalId = req.user.personal_id;
    
    if (!personalId) {
      return res.status(404).json({ error: 'Docente no encontrado' });
    }

    // Obtener cursos asignados
    // asignaturas tiene grupo_id directamente, no area_curso_id
    const cursos = await query(
      `SELECT a.*, c.nombre as curso_nombre, c.imagen as curso_imagen, 
              g.grado, g.seccion, g.anio, 
              n.nombre as nivel_nombre, t.nombre as turno_nombre
       FROM asignaturas a
       INNER JOIN grupos g ON g.id = a.grupo_id
       INNER JOIN cursos c ON c.id = a.curso_id
       INNER JOIN niveles n ON n.id = g.nivel_id
       INNER JOIN turnos t ON t.id = g.turno_id
       WHERE a.personal_id = ? AND a.colegio_id = ? AND g.anio = ?
       ORDER BY n.nombre, g.grado, g.seccion, c.nombre`,
      [personalId, colegio_id, anio_activo]
    );

    // Construir URLs completas para las imágenes de cursos
    // Según el modelo anterior, las imágenes están en /Static/Archivos/
    const cursosConImagenes = cursos.map(curso => {
      let cursoImagenUrl = null;
      if (curso.curso_imagen && curso.curso_imagen !== '') {
        const isProduction = process.env.NODE_ENV === 'production';
        
        // Si ya es una URL completa, usarla directamente
        if (curso.curso_imagen.startsWith('http')) {
          cursoImagenUrl = curso.curso_imagen;
        } else if (curso.curso_imagen.startsWith('/Static/')) {
          // Ruta del sistema anterior
          if (isProduction) {
            cursoImagenUrl = `https://vanguardschools.edu.pe${curso.curso_imagen}`;
          } else {
            cursoImagenUrl = `http://localhost:5000${curso.curso_imagen}`;
          }
        } else {
          // Solo el nombre del archivo, construir ruta completa en /Static/Archivos/
          if (isProduction) {
            cursoImagenUrl = `https://vanguardschools.edu.pe/Static/Archivos/${curso.curso_imagen}`;
          } else {
            cursoImagenUrl = `http://localhost:5000/Static/Archivos/${curso.curso_imagen}`;
          }
        }
      }
      
      return {
        ...curso,
        curso_imagen_url: cursoImagenUrl
      };
    });

    res.json({ cursos: cursosConImagenes || [] });
  } catch (error) {
    console.error('Error obteniendo cursos:', error);
    res.status(500).json({ error: 'Error al obtener cursos asignados' });
  }
});

/**
 * GET /api/docente/cursos/:cursoId/alumnos
 * Obtener lista de alumnos de un curso (asignatura) con conteo de estrellas
 */
router.get('/cursos/:cursoId/alumnos', async (req, res) => {
  try {
    const { cursoId } = req.params;
    const { colegio_id, anio_activo } = req.user;

    // Validar parámetros
    if (!cursoId) {
      return res.status(400).json({ error: 'ID de curso es requerido' });
    }

    // Obtener personal_id del token
    const personalId = req.user.personal_id;
    
    if (!personalId) {
      return res.status(404).json({ error: 'Docente no encontrado' });
    }

    // Verificar que el docente tiene acceso a este curso (asignatura)
    const asignatura = await query(
      `SELECT a.*, g.id as grupo_id, g.grado, g.seccion, g.anio,
              n.nombre as nivel_nombre, t.nombre as turno_nombre,
              c.nombre as curso_nombre
       FROM asignaturas a
       INNER JOIN grupos g ON g.id = a.grupo_id
       INNER JOIN niveles n ON n.id = g.nivel_id
       INNER JOIN turnos t ON t.id = g.turno_id
       INNER JOIN cursos c ON c.id = a.curso_id
       WHERE a.id = ? AND a.personal_id = ? AND a.colegio_id = ? AND g.anio = ?`,
      [cursoId, personalId, colegio_id, anio_activo]
    );

    if (!asignatura || asignatura.length === 0) {
      return res.status(403).json({ error: 'No tienes acceso a este curso' });
    }

    const cursoInfo = asignatura[0];

    // Obtener alumnos del grupo con conteo de estrellas e incidencias
    // IMPORTANTE: Las estrellas e incidencias se calculan solo del año activo
    // Las estrellas se calculan sumando los puntos de enrollment_incidents (type = 2)
    // Las incidencias se cuentan de enrollment_incidents (type = 1)
    // Todo relacionado con las matrículas del alumno y del año activo
    // El promedio final se calcula como el promedio de TODOS los bimestres que tengan notas registradas
    const alumnos = await query(
      `SELECT a.id, 
              a.nombres,
              a.apellido_paterno,
              a.apellido_materno,
              CONCAT(a.apellido_paterno, ' ', a.apellido_materno, ', ', a.nombres) as nombre_completo,
              m.id as matricula_id,
              COALESCE(SUM(CASE WHEN ei_estrellas.type = 2 AND g_estrellas.anio = ? THEN ei_estrellas.points ELSE 0 END), 0) as total_estrellas,
              COUNT(DISTINCT CASE WHEN ei_incidencias.type = 1 AND g_incidencias.anio = ? THEN ei_incidencias.id END) as total_incidencias,
              (SELECT CASE 
                 WHEN COUNT(*) > 0 THEN 
                   ROUND(AVG(CAST(p.promedio AS DECIMAL(5,2))), 0)
                 ELSE NULL
               END
               FROM promedios p 
               WHERE p.matricula_id = m.id 
                 AND p.asignatura_id = ?
                 AND p.promedio IS NOT NULL
                 AND p.promedio != ''
                 AND p.promedio != '-'
                 AND p.promedio REGEXP '^[0-9]+\.?[0-9]*$') as promedio_final
       FROM alumnos a
       INNER JOIN matriculas m ON m.alumno_id = a.id
       INNER JOIN grupos g ON g.id = m.grupo_id
       LEFT JOIN enrollment_incidents ei_estrellas ON ei_estrellas.enrollment_id = m.id AND ei_estrellas.type = 2
       LEFT JOIN matriculas m_estrellas ON m_estrellas.id = ei_estrellas.enrollment_id
       LEFT JOIN grupos g_estrellas ON g_estrellas.id = m_estrellas.grupo_id
       LEFT JOIN enrollment_incidents ei_incidencias ON ei_incidencias.enrollment_id = m.id AND ei_incidencias.type = 1
       LEFT JOIN matriculas m_incidencias ON m_incidencias.id = ei_incidencias.enrollment_id
       LEFT JOIN grupos g_incidencias ON g_incidencias.id = m_incidencias.grupo_id
       WHERE m.grupo_id = ? 
         AND m.colegio_id = ? 
         AND (m.estado = 0 OR m.estado = 4)
         AND g.anio = ?
       GROUP BY a.id, a.nombres, a.apellido_paterno, a.apellido_materno, m.id
       ORDER BY a.apellido_paterno, a.apellido_materno, a.nombres`,
      [anio_activo, anio_activo, cursoInfo.id, cursoInfo.grupo_id, colegio_id, anio_activo]
    );

    res.json({ 
      alumnos: alumnos || [],
      curso: {
        id: cursoInfo.id,
        curso_nombre: cursoInfo.curso_nombre,
        grado: cursoInfo.grado,
        seccion: cursoInfo.seccion,
        nivel_nombre: cursoInfo.nivel_nombre,
        turno_nombre: cursoInfo.turno_nombre,
        grupo_id: cursoInfo.grupo_id
      }
    });
  } catch (error) {
    console.error('Error obteniendo alumnos del curso:', error);
    console.error('Stack:', error.stack);
    res.status(500).json({ error: 'Error al obtener lista de alumnos del curso' });
  }
});

/**
 * GET /api/docente/cursos/:cursoId/alumnos/:alumnoId/estrellas
 * Obtener historial de estrellas de un alumno
 */
router.get('/cursos/:cursoId/alumnos/:alumnoId/estrellas', async (req, res) => {
  try {
    const { cursoId, alumnoId } = req.params;
    const { colegio_id, anio_activo, personal_id } = req.user;

    if (!personal_id) {
      return res.status(404).json({ error: 'Docente no encontrado' });
    }

    // Verificar acceso al curso
    const asignatura = await query(
      `SELECT a.* FROM asignaturas a
       INNER JOIN grupos g ON g.id = a.grupo_id
       WHERE a.id = ? AND a.personal_id = ? AND a.colegio_id = ? AND g.anio = ?`,
      [cursoId, personal_id, colegio_id, anio_activo]
    );

    if (!asignatura || asignatura.length === 0) {
      return res.status(403).json({ error: 'No tienes acceso a este curso' });
    }

    // Obtener matrícula del alumno en el grupo del curso
    // IMPORTANTE: Verificar que la matrícula pertenezca al año activo
    const matricula = await query(
      `SELECT m.id FROM matriculas m
       INNER JOIN asignaturas a ON a.grupo_id = m.grupo_id
       INNER JOIN grupos g ON g.id = m.grupo_id
       WHERE m.alumno_id = ? 
         AND a.id = ? 
         AND m.colegio_id = ? 
         AND (m.estado = 0 OR m.estado = 4)
         AND g.anio = ?`,
      [alumnoId, cursoId, colegio_id, anio_activo]
    );

    if (!matricula || matricula.length === 0) {
      return res.status(404).json({ error: 'Alumno no encontrado en este curso para el año activo' });
    }

    const matriculaId = matricula[0].id;

    // Obtener historial de estrellas (type = 2 según el sistema anterior)
    // IMPORTANTE: Filtrar solo estrellas del año activo
    // Incluir información del docente que dio las estrellas
    const estrellas = await query(
      `SELECT ei.id, ei.points, ei.description, ei.created_at, ei.worker_id,
              CONCAT(p.nombres, ' ', p.apellidos) as docente_nombre,
              CASE WHEN ei.worker_id = ? THEN 1 ELSE 0 END as puede_eliminar
       FROM enrollment_incidents ei
       INNER JOIN matriculas m ON m.id = ei.enrollment_id
       INNER JOIN grupos g ON g.id = m.grupo_id
       LEFT JOIN personal p ON p.id = ei.worker_id
       WHERE ei.enrollment_id = ? 
         AND ei.assignment_id = ? 
         AND ei.type = 2
         AND g.anio = ?
       ORDER BY ei.created_at DESC`,
      [personal_id, matriculaId, cursoId, anio_activo]
    );

    // Calcular total de estrellas
    const totalEstrellas = estrellas.reduce((sum, e) => sum + (e.points || 0), 0);

    res.json({
      estrellas: estrellas || [],
      total_estrellas: totalEstrellas,
      matricula_id: matriculaId
    });
  } catch (error) {
    console.error('Error obteniendo estrellas:', error);
    res.status(500).json({ error: 'Error al obtener historial de estrellas' });
  }
});

/**
 * POST /api/docente/cursos/:cursoId/alumnos/:alumnoId/estrellas
 * Dar estrellas a un alumno
 */
router.post('/cursos/:cursoId/alumnos/:alumnoId/estrellas', async (req, res) => {
  try {
    const { cursoId, alumnoId } = req.params;
    const { colegio_id, anio_activo, personal_id } = req.user;
    const { points, description } = req.body;

    if (!personal_id) {
      return res.status(404).json({ error: 'Docente no encontrado' });
    }

    // Validar datos
    if (!points || points <= 0) {
      return res.status(400).json({ error: 'La cantidad de estrellas debe ser mayor a 0' });
    }

    if (!description || description.trim() === '') {
      return res.status(400).json({ error: 'La descripción es requerida' });
    }

    // Verificar acceso al curso
    const asignatura = await query(
      `SELECT a.* FROM asignaturas a
       INNER JOIN grupos g ON g.id = a.grupo_id
       WHERE a.id = ? AND a.personal_id = ? AND a.colegio_id = ? AND g.anio = ?`,
      [cursoId, personal_id, colegio_id, anio_activo]
    );

    if (!asignatura || asignatura.length === 0) {
      return res.status(403).json({ error: 'No tienes acceso a este curso' });
    }

    // Obtener matrícula del alumno
    // IMPORTANTE: Verificar que la matrícula pertenezca al año activo
    const matricula = await query(
      `SELECT m.id FROM matriculas m
       INNER JOIN asignaturas a ON a.grupo_id = m.grupo_id
       INNER JOIN grupos g ON g.id = m.grupo_id
       WHERE m.alumno_id = ? 
         AND a.id = ? 
         AND m.colegio_id = ? 
         AND (m.estado = 0 OR m.estado = 4)
         AND g.anio = ?`,
      [alumnoId, cursoId, colegio_id, anio_activo]
    );

    if (!matricula || matricula.length === 0) {
      return res.status(404).json({ error: 'Alumno no encontrado en este curso para el año activo' });
    }

    const matriculaId = matricula[0].id;

    // Crear registro de estrellas
    const now = new Date().toISOString().slice(0, 19).replace('T', ' ');
    const result = await execute(
      `INSERT INTO enrollment_incidents 
       (enrollment_id, assignment_id, worker_id, type, points, description, created_at, updated_at)
       VALUES (?, ?, ?, 2, ?, ?, ?, ?)`,
      [matriculaId, cursoId, personal_id, points, description.trim(), now, now]
    );

    // Registrar en auditoría
    await registrarAccion({
      usuario_id: req.user.usuario_id,
      colegio_id: colegio_id,
      tipo_usuario: 'DOCENTE',
      accion: 'CREAR',
      modulo: 'ESTRELLAS',
      entidad: 'enrollment_incidents',
      entidad_id: result.insertId,
      descripcion: `Dio ${points} estrella(s) al alumno ID ${alumnoId} en el curso ID ${cursoId}`,
      url: req.originalUrl
    });

    res.json({
      success: true,
      message: `${points} estrella(s) asignada(s) correctamente`,
      incident_id: result.insertId
    });
  } catch (error) {
    console.error('Error dando estrellas:', error);
    res.status(500).json({ error: 'Error al asignar estrellas' });
  }
});

/**
 * DELETE /api/docente/cursos/:cursoId/alumnos/:alumnoId/estrellas/:incidentId
 * Eliminar estrellas (solo las que el docente actual dio)
 */
router.delete('/cursos/:cursoId/alumnos/:alumnoId/estrellas/:incidentId', async (req, res) => {
  try {
    const { cursoId, alumnoId, incidentId } = req.params;
    const { colegio_id, anio_activo, personal_id } = req.user;

    if (!personal_id) {
      return res.status(404).json({ error: 'Docente no encontrado' });
    }

    // Verificar que el incidente existe y pertenece al docente actual
    // IMPORTANTE: Verificar que pertenezca al año activo
    const incidente = await query(
      `SELECT ei.* FROM enrollment_incidents ei
       INNER JOIN matriculas m ON m.id = ei.enrollment_id
       INNER JOIN grupos g ON g.id = m.grupo_id
       WHERE ei.id = ? 
         AND ei.worker_id = ? 
         AND ei.type = 2
         AND g.anio = ?`,
      [incidentId, personal_id, anio_activo]
    );

    if (!incidente || incidente.length === 0) {
      return res.status(403).json({ 
        error: 'No puedes eliminar estas estrellas. Solo puedes eliminar las que tú mismo diste del año activo.' 
      });
    }

    // Eliminar el incidente
    await execute(
      `DELETE FROM enrollment_incidents WHERE id = ?`,
      [incidentId]
    );

    // Registrar en auditoría
    await registrarAccion({
      usuario_id: req.user.usuario_id,
      colegio_id: colegio_id,
      tipo_usuario: 'DOCENTE',
      accion: 'ELIMINAR',
      modulo: 'ESTRELLAS',
      entidad: 'enrollment_incidents',
      entidad_id: incidentId,
      descripcion: `Eliminó ${incidente[0].points} estrella(s) del alumno ID ${alumnoId}`,
      url: req.originalUrl
    });

    res.json({
      success: true,
      message: 'Estrellas eliminadas correctamente'
    });
  } catch (error) {
    console.error('Error eliminando estrellas:', error);
    res.status(500).json({ error: 'Error al eliminar estrellas' });
  }
});

/**
 * GET /api/docente/cursos/:cursoId/alumnos/:alumnoId/incidencias
 * Obtener historial de incidencias de un alumno
 */
router.get('/cursos/:cursoId/alumnos/:alumnoId/incidencias', async (req, res) => {
  try {
    const { cursoId, alumnoId } = req.params;
    const { colegio_id, anio_activo, personal_id } = req.user;

    if (!personal_id) {
      return res.status(404).json({ error: 'Docente no encontrado' });
    }

    // Verificar acceso al curso
    const asignatura = await query(
      `SELECT a.* FROM asignaturas a
       INNER JOIN grupos g ON g.id = a.grupo_id
       WHERE a.id = ? AND a.personal_id = ? AND a.colegio_id = ? AND g.anio = ?`,
      [cursoId, personal_id, colegio_id, anio_activo]
    );

    if (!asignatura || asignatura.length === 0) {
      return res.status(403).json({ error: 'No tienes acceso a este curso' });
    }

    // Obtener matrícula del alumno en el grupo del curso
    // IMPORTANTE: Verificar que la matrícula pertenezca al año activo
    const matricula = await query(
      `SELECT m.id FROM matriculas m
       INNER JOIN asignaturas a ON a.grupo_id = m.grupo_id
       INNER JOIN grupos g ON g.id = m.grupo_id
       WHERE m.alumno_id = ? 
         AND a.id = ? 
         AND m.colegio_id = ? 
         AND (m.estado = 0 OR m.estado = 4)
         AND g.anio = ?`,
      [alumnoId, cursoId, colegio_id, anio_activo]
    );

    if (!matricula || matricula.length === 0) {
      return res.status(404).json({ error: 'Alumno no encontrado en este curso para el año activo' });
    }

    const matriculaId = matricula[0].id;

    // Obtener historial de incidencias (type = 1 según el sistema anterior)
    // IMPORTANTE: 
    // - Filtrar solo incidencias del año activo
    // - Mostrar TODAS las incidencias del alumno sin filtrar por curso (assignment_id)
    // - Incluir información del docente que registró la incidencia y el curso donde se registró
    const incidencias = await query(
      `SELECT ei.id, ei.description, ei.created_at, ei.worker_id, ei.assignment_id,
              CONCAT(p.nombres, ' ', p.apellidos) as docente_nombre,
              c.nombre as curso_nombre,
              CASE WHEN ei.worker_id = ? THEN 1 ELSE 0 END as puede_eliminar
       FROM enrollment_incidents ei
       INNER JOIN matriculas m ON m.id = ei.enrollment_id
       INNER JOIN grupos g ON g.id = m.grupo_id
       LEFT JOIN personal p ON p.id = ei.worker_id
       LEFT JOIN asignaturas a ON a.id = ei.assignment_id
       LEFT JOIN cursos c ON c.id = a.curso_id
       WHERE ei.enrollment_id = ? 
         AND ei.type = 1
         AND g.anio = ?
       ORDER BY ei.created_at DESC`,
      [personal_id, matriculaId, anio_activo]
    );

    res.json({
      incidencias: incidencias || [],
      total_incidencias: incidencias.length,
      matricula_id: matriculaId
    });
  } catch (error) {
    console.error('Error obteniendo incidencias:', error);
    res.status(500).json({ error: 'Error al obtener historial de incidencias' });
  }
});

/**
 * POST /api/docente/cursos/:cursoId/alumnos/:alumnoId/incidencias
 * Registrar una incidencia para un alumno
 */
router.post('/cursos/:cursoId/alumnos/:alumnoId/incidencias', async (req, res) => {
  try {
    const { cursoId, alumnoId } = req.params;
    const { colegio_id, anio_activo, personal_id } = req.user;
    const { description } = req.body;

    if (!personal_id) {
      return res.status(404).json({ error: 'Docente no encontrado' });
    }

    // Validar datos
    if (!description || description.trim() === '') {
      return res.status(400).json({ error: 'La descripción es requerida' });
    }

    // Verificar acceso al curso
    const asignatura = await query(
      `SELECT a.* FROM asignaturas a
       INNER JOIN grupos g ON g.id = a.grupo_id
       WHERE a.id = ? AND a.personal_id = ? AND a.colegio_id = ? AND g.anio = ?`,
      [cursoId, personal_id, colegio_id, anio_activo]
    );

    if (!asignatura || asignatura.length === 0) {
      return res.status(403).json({ error: 'No tienes acceso a este curso' });
    }

    // Obtener matrícula del alumno
    // IMPORTANTE: Verificar que la matrícula pertenezca al año activo
    const matricula = await query(
      `SELECT m.id FROM matriculas m
       INNER JOIN asignaturas a ON a.grupo_id = m.grupo_id
       INNER JOIN grupos g ON g.id = m.grupo_id
       WHERE m.alumno_id = ? 
         AND a.id = ? 
         AND m.colegio_id = ? 
         AND (m.estado = 0 OR m.estado = 4)
         AND g.anio = ?`,
      [alumnoId, cursoId, colegio_id, anio_activo]
    );

    if (!matricula || matricula.length === 0) {
      return res.status(404).json({ error: 'Alumno no encontrado en este curso para el año activo' });
    }

    const matriculaId = matricula[0].id;

    // Crear registro de incidencia (type = 1, points = 0)
    const now = new Date().toISOString().slice(0, 19).replace('T', ' ');
    const result = await execute(
      `INSERT INTO enrollment_incidents 
       (enrollment_id, assignment_id, worker_id, type, points, description, created_at, updated_at)
       VALUES (?, ?, ?, 1, 0, ?, ?, ?)`,
      [matriculaId, cursoId, personal_id, description.trim(), now, now]
    );

    // Registrar en auditoría
    await registrarAccion({
      usuario_id: req.user.usuario_id,
      colegio_id: colegio_id,
      tipo_usuario: 'DOCENTE',
      accion: 'CREAR',
      modulo: 'INCIDENCIAS',
      entidad: 'enrollment_incidents',
      entidad_id: result.insertId,
      descripcion: `Registró una incidencia para el alumno ID ${alumnoId} en el curso ID ${cursoId}`,
      url: req.originalUrl
    });

    res.json({
      success: true,
      message: 'Incidencia registrada correctamente',
      incident_id: result.insertId
    });
  } catch (error) {
    console.error('Error registrando incidencia:', error);
    res.status(500).json({ error: 'Error al registrar la incidencia' });
  }
});

/**
 * DELETE /api/docente/cursos/:cursoId/alumnos/:alumnoId/incidencias/:incidentId
 * Eliminar incidencia (solo las que el docente actual registró)
 */
router.delete('/cursos/:cursoId/alumnos/:alumnoId/incidencias/:incidentId', async (req, res) => {
  try {
    const { cursoId, alumnoId, incidentId } = req.params;
    const { colegio_id, anio_activo, personal_id } = req.user;

    if (!personal_id) {
      return res.status(404).json({ error: 'Docente no encontrado' });
    }

    // Verificar que el incidente existe y pertenece al docente actual
    // IMPORTANTE: Verificar que pertenezca al año activo
    const incidente = await query(
      `SELECT ei.* FROM enrollment_incidents ei
       INNER JOIN matriculas m ON m.id = ei.enrollment_id
       INNER JOIN grupos g ON g.id = m.grupo_id
       WHERE ei.id = ? 
         AND ei.worker_id = ? 
         AND ei.type = 1
         AND g.anio = ?`,
      [incidentId, personal_id, anio_activo]
    );

    if (!incidente || incidente.length === 0) {
      return res.status(403).json({ 
        error: 'No puedes eliminar esta incidencia. Solo puedes eliminar las que tú mismo registraste del año activo.' 
      });
    }

    // Eliminar el incidente
    await execute(
      `DELETE FROM enrollment_incidents WHERE id = ?`,
      [incidentId]
    );

    // Registrar en auditoría
    await registrarAccion({
      usuario_id: req.user.usuario_id,
      colegio_id: colegio_id,
      tipo_usuario: 'DOCENTE',
      accion: 'ELIMINAR',
      modulo: 'INCIDENCIAS',
      entidad: 'enrollment_incidents',
      entidad_id: incidentId,
      descripcion: `Eliminó una incidencia del alumno ID ${alumnoId}`,
      url: req.originalUrl
    });

    res.json({
      success: true,
      message: 'Incidencia eliminada correctamente'
    });
  } catch (error) {
    console.error('Error eliminando incidencia:', error);
    res.status(500).json({ error: 'Error al eliminar la incidencia' });
  }
});

/**
 * Función auxiliar para deserializar datos de notas_detalles
 * El campo data viene serializado en formato PHP: serialize(array)
 */
function deserializarNotasDetalles(dataString) {
  if (!dataString || dataString === '') {
    return {};
  }

  try {
    const phpSerialize = require('php-serialize');
    
    // Intentar deserializar directamente
    try {
      const deserialized = phpSerialize.unserialize(dataString);
      return deserialized || {};
    } catch (e) {
      // Si falla, intentar decodificar base64 primero
      try {
        const decoded = Buffer.from(dataString, 'base64').toString('utf-8');
        const deserialized = phpSerialize.unserialize(decoded);
        return deserialized || {};
      } catch (e2) {
        console.warn('Error deserializando notas_detalles:', e2);
        return {};
      }
    }
  } catch (error) {
    console.warn('Error deserializando notas_detalles:', error);
    return {};
  }
}

/**
 * GET /api/docente/cursos/:cursoId/alumnos/:alumnoId/notas-detalladas
 * Obtener notas detalladas de un alumno para un curso específico
 */
router.get('/cursos/:cursoId/alumnos/:alumnoId/notas-detalladas', async (req, res) => {
  try {
    const { colegio_id, anio_activo, personal_id } = req.user;
    const { cursoId, alumnoId } = req.params;

    // Validar que el curso pertenece al docente
    const asignatura = await query(
      `SELECT a.*, g.anio, g.nivel_id, n.tipo_calificacion, n.tipo_calificacion_final,
              n.nota_aprobatoria, n.nota_maxima, n.nota_minima, c.nombre as curso_nombre,
              c.peso_examen_mensual, c.examen_mensual
       FROM asignaturas a
       INNER JOIN grupos g ON g.id = a.grupo_id
       INNER JOIN niveles n ON n.id = g.nivel_id
       INNER JOIN cursos c ON c.id = a.curso_id
       WHERE a.id = ? AND a.personal_id = ? AND a.colegio_id = ? AND g.anio = ?`,
      [cursoId, personal_id, colegio_id, anio_activo]
    );

    if (asignatura.length === 0) {
      return res.status(404).json({ error: 'Curso no encontrado o no asignado' });
    }

    const cursoInfo = asignatura[0];

    // Obtener matrícula del alumno
    const matricula = await query(
      `SELECT m.id, m.alumno_id, m.grupo_id
       FROM matriculas m
       INNER JOIN grupos g ON g.id = m.grupo_id
       WHERE m.alumno_id = ? AND m.grupo_id = ? AND m.colegio_id = ? 
         AND g.anio = ? AND (m.estado = 0 OR m.estado = 4)`,
      [alumnoId, cursoInfo.grupo_id, colegio_id, anio_activo]
    );

    if (matricula.length === 0) {
      return res.status(404).json({ error: 'Alumno no encontrado en este grupo' });
    }

    const matriculaId = matricula[0].id;

    // Obtener criterios de la asignatura (para todos los ciclos o ciclo específico)
    const criterios = await query(
      `SELECT * FROM asignaturas_criterios
       WHERE asignatura_id = ? AND colegio_id = ?
       ORDER BY orden ASC`,
      [cursoId, colegio_id]
    );

    // Obtener todos los indicadores de una vez (optimización)
    const criterioIds = criterios.map(c => c.id);
    let todosIndicadores = [];
    if (criterioIds.length > 0) {
      todosIndicadores = await query(
        `SELECT * FROM asignaturas_indicadores
         WHERE criterio_id IN (${criterioIds.map(() => '?').join(',')})
         ORDER BY criterio_id ASC, id ASC`,
        criterioIds
      );
    }

    // Agrupar indicadores por criterio
    const indicadoresPorCriterio = {};
    todosIndicadores.forEach(indicador => {
      if (!indicadoresPorCriterio[indicador.criterio_id]) {
        indicadoresPorCriterio[indicador.criterio_id] = [];
      }
      indicadoresPorCriterio[indicador.criterio_id].push(indicador);
    });

    // Agregar indicadores a cada criterio
    const criteriosConIndicadores = criterios.map(criterio => ({
      ...criterio,
      indicadores: indicadoresPorCriterio[criterio.id] || []
    }));

    // Obtener todas las notas detalladas de una vez (optimización)
    const todasNotasDetalles = await query(
      `SELECT ciclo, data FROM notas_detalles
       WHERE matricula_id = ? AND asignatura_id = ? AND ciclo IN (1, 2, 3, 4)`,
      [matriculaId, cursoId]
    );

    // Obtener todas las notas de criterios de una vez (optimización)
    const todasNotasCriterios = await query(
      `SELECT criterio_id, nota, ciclo
       FROM notas n
       WHERE n.matricula_id = ? AND n.asignatura_id = ? AND n.ciclo IN (1, 2, 3, 4)`,
      [matriculaId, cursoId]
    );

    // Obtener todos los exámenes mensuales de una vez (optimización)
    let todasExamenesMensuales = [];
    if (cursoInfo.examen_mensual === 'SI') {
      todasExamenesMensuales = await query(
        `SELECT nro, nota, ciclo FROM notas_examen_mensual
         WHERE matricula_id = ? AND asignatura_id = ? AND ciclo IN (1, 2, 3, 4)
         ORDER BY ciclo ASC, nro ASC`,
        [matriculaId, cursoId]
      );
    }

    // Obtener todos los promedios finales de una vez (optimización)
    const todosPromedios = await query(
      `SELECT promedio, ciclo FROM promedios
       WHERE matricula_id = ? AND asignatura_id = ? AND ciclo IN (1, 2, 3, 4)`,
      [matriculaId, cursoId]
    );

    // Organizar datos por ciclo
    const notasDetalladas = {};
    for (let ciclo = 1; ciclo <= 4; ciclo++) {
      // Obtener notas detalladas del ciclo
      const notaDetalle = todasNotasDetalles.find(nd => nd.ciclo === ciclo);
      if (notaDetalle && notaDetalle.data) {
        notasDetalladas[ciclo] = deserializarNotasDetalles(notaDetalle.data);
      } else {
        notasDetalladas[ciclo] = {};
      }

      // Crear mapa de notas por criterio para este ciclo
      const notasMap = {};
      todasNotasCriterios
        .filter(n => n.ciclo === ciclo)
        .forEach(nota => {
          notasMap[nota.criterio_id] = nota.nota;
        });

      // Agregar notas de criterios a cada criterio
      criteriosConIndicadores.forEach(criterio => {
        if (!criterio.notas) criterio.notas = {};
        criterio.notas[ciclo] = notasMap[criterio.id] || null;
      });

      // Agregar exámenes mensuales del ciclo
      if (cursoInfo.examen_mensual === 'SI') {
        const examenesCiclo = todasExamenesMensuales.filter(e => e.ciclo === ciclo);
        if (examenesCiclo.length > 0) {
          if (!notasDetalladas[ciclo].examen_mensual) {
            notasDetalladas[ciclo].examen_mensual = {};
          }
          examenesCiclo.forEach(examen => {
            notasDetalladas[ciclo].examen_mensual[examen.nro] = examen.nota;
          });
        }
      }

      // Agregar promedio final del ciclo
      const promedioCiclo = todosPromedios.find(p => p.ciclo === ciclo);
      notasDetalladas[ciclo].promedio_final = promedioCiclo ? promedioCiclo.promedio : null;
    }

    // Obtener información del alumno
    const alumno = await query(
      `SELECT id, nombres, apellido_paterno, apellido_materno,
              CONCAT(apellido_paterno, ' ', apellido_materno, ', ', nombres) as nombre_completo
       FROM alumnos
       WHERE id = ?`,
      [alumnoId]
    );

    res.json({
      alumno: alumno[0] || null,
      curso: {
        id: cursoInfo.id,
        nombre: cursoInfo.curso_nombre,
        grupo_id: cursoInfo.grupo_id,
        nivel: {
          tipo_calificacion: cursoInfo.tipo_calificacion, // 0 = Cualitativa, 1 = Cuantitativa
          tipo_calificacion_final: cursoInfo.tipo_calificacion_final, // 0 = Promedio, 1 = Porcentaje
          nota_aprobatoria: cursoInfo.nota_aprobatoria,
          nota_maxima: cursoInfo.nota_maxima,
          nota_minima: cursoInfo.nota_minima
        },
        examen_mensual: cursoInfo.examen_mensual === 'SI',
        peso_examen_mensual: cursoInfo.peso_examen_mensual || 0
      },
      criterios: criteriosConIndicadores,
      notas: notasDetalladas
    });
  } catch (error) {
    console.error('Error obteniendo notas detalladas:', error);
    res.status(500).json({ error: 'Error al obtener notas detalladas' });
  }
});

/**
 * GET /api/docente/horario
 * Obtener horario del docente
 */
router.get('/horario', async (req, res) => {
  try {
    const { colegio_id, anio_activo } = req.user;
    const personalId = req.user.personal_id;

    if (!personalId) {
      return res.status(404).json({ error: 'Docente no encontrado' });
    }

    // =============================
    // Obtener horario del docente
    // Basado en la lógica de imprimir_horario_docente del sistema anterior,
    // usando la tabla grupos_horarios + asignaturas + grupos + niveles + cursos
    // =============================

    const anioActivoInt = parseInt(anio_activo);

    // Ver qué años de grupos_horarios hay para este docente
    const aniosDisponibles = await query(
      `SELECT DISTINCT gh.anio
       FROM grupos_horarios gh
       LEFT JOIN asignaturas a ON a.id = gh.asignatura_id
       LEFT JOIN grupos g ON g.id = a.grupo_id
       WHERE (a.personal_id = ? OR gh.personal_id = ?)
       ORDER BY gh.anio DESC`,
      [personalId, personalId]
    );

    if (!aniosDisponibles || aniosDisponibles.length === 0) {
      console.log('📅 No se encontraron registros en grupos_horarios para este docente.');
      return res.json({ horario: [] });
    }

    // Usar primero el año activo si existe en grupos_horarios, si no, el más reciente disponible
    let anioUsar = anioActivoInt;
    const existeAnioActivo = aniosDisponibles.some((row) => parseInt(row.anio) === anioActivoInt);
    if (!existeAnioActivo) {
      anioUsar = parseInt(aniosDisponibles[0].anio);
    }

    console.log(
      `📅 Años disponibles en grupos_horarios para este docente:`,
      aniosDisponibles.map((a) => a.anio)
    );
    console.log(`📅 Usando año para horario docente: ${anioUsar}`);

    // Traer todas las filas de horario con curso y grupo ya resueltos
    const horarioCrudo = await query(
      `SELECT 
         gh.id,
         gh.dia,
         gh.hora_inicio,
         gh.hora_final,
         gh.anio,
         c.nombre        AS curso_nombre,
         gh.descripcion  AS descripcion,
         g.grado,
         g.seccion,
         n.nombre        AS nivel_nombre
       FROM grupos_horarios gh
       LEFT JOIN asignaturas a ON a.id = gh.asignatura_id
       LEFT JOIN grupos g      ON g.id = a.grupo_id
       LEFT JOIN niveles n     ON n.id = g.nivel_id
       LEFT JOIN cursos c      ON c.id = a.curso_id
       WHERE gh.anio = ?
         AND (a.personal_id = ? OR gh.personal_id = ?)
       ORDER BY gh.dia, STR_TO_DATE(gh.hora_inicio, '%l:%i %p')`,
      [anioUsar, personalId, personalId]
    );

    console.log(`📅 Filas de horario crudo encontradas: ${horarioCrudo ? horarioCrudo.length : 0}`);
    if (horarioCrudo && horarioCrudo.length > 0) {
      console.log('📅 Primera fila de horario crudo:', horarioCrudo[0]);
    }

    // Mapear a un formato estándar para el frontend
    const horario = (horarioCrudo || []).map((row) => {
      // Texto del grupo: NIVEL - X° SECCION - AÑO (igual al PDF)
      let grupoTexto = '';
      if (row.nivel_nombre || row.grado || row.seccion) {
        const gradoTexto = row.grado ? `${row.grado}°` : '';
        grupoTexto = `${row.nivel_nombre || ''} ${gradoTexto} ${row.seccion || ''}`.trim();
        if (row.anio) {
          grupoTexto = `${grupoTexto} - ${row.anio}`;
        }
      }

      const titulo = row.curso_nombre || row.descripcion || '';

      return {
        id: row.id,
        dia: typeof row.dia === 'number' ? row.dia : parseInt(row.dia || 0, 10),
        inicio: row.hora_inicio,
        fin: row.hora_final,
        anio: row.anio,
        titulo,
        grupo: grupoTexto
      };
    });

    res.json({ horario });
  } catch (error) {
    console.error('Error obteniendo horario:', error);
    res.status(500).json({ error: 'Error al obtener horario' });
  }
});

/**
 * GET /api/docente/tutoria
 * Verificar si el docente es tutor y obtener grupo
 */
router.get('/tutoria', async (req, res) => {
  try {
    const { colegio_id, anio_activo } = req.user;
    const personalId = req.user.personal_id;

    if (!personalId) {
      return res.status(404).json({ error: 'Docente no encontrado' });
    }

    // Verificar si es tutor
    const gruposTutor = await query(
      `SELECT g.*, n.nombre as nivel_nombre, t.nombre as turno_nombre
       FROM grupos g
       INNER JOIN niveles n ON n.id = g.nivel_id
       INNER JOIN turnos t ON t.id = g.turno_id
       WHERE g.tutor_id = ? AND g.colegio_id = ? AND g.anio = ?`,
      [personalId, colegio_id, anio_activo]
    );

    res.json({
      es_tutor: gruposTutor.length > 0,
      grupo: gruposTutor.length > 0 ? gruposTutor[0] : null
    });
  } catch (error) {
    console.error('Error verificando tutoría:', error);
    res.status(500).json({ error: 'Error al verificar tutoría' });
  }
});

/**
 * GET /api/docente/comunicados
 * Obtener comunicados (solo lectura, generados por admin)
 */
router.get('/comunicados', async (req, res) => {
  try {
    const { colegio_id } = req.user;
    const { page = 1, limit = 12, search = '' } = req.query;
    const offset = (parseInt(page) - 1) * parseInt(limit);

    // Construir query base
    let querySql = `
      SELECT c.*
       FROM comunicados c
       WHERE c.colegio_id = ? AND c.estado = 'ACTIVO'
    `;
    const params = [colegio_id];

    // Agregar búsqueda si existe
    if (search && search.trim() !== '') {
      querySql += ` AND (c.descripcion LIKE ? OR c.contenido LIKE ?)`;
      const searchPattern = `%${search.trim()}%`;
      params.push(searchPattern, searchPattern);
    }

    // Obtener total para paginación
    const countQuery = querySql.replace('SELECT c.*', 'SELECT COUNT(*) as total');
    const countResult = await query(countQuery, params);
    const total = countResult[0]?.total || 0;

    // Agregar ordenamiento y límites
    querySql += ` ORDER BY c.fecha_hora DESC LIMIT ? OFFSET ?`;
    params.push(parseInt(limit), offset);

    const comunicados = await query(querySql, params);

    // Construir URLs de archivos
    // IMPORTANTE: Los comunicados pueden venir de dos sistemas:
    // 1. Sistema PHP anterior: archivos en /Static/Archivos/
    // 2. Sistema nuevo (React/Node.js): archivos en /uploads/comunicados/
    // El backend detecta automáticamente el origen y construye la URL correcta
    const comunicadosConUrls = (comunicados || []).map(com => {
      let archivoUrl = null;
      if (com.archivo && com.archivo.trim() !== '') {
        let nombreArchivo = com.archivo.trim();
        const isProduction = process.env.NODE_ENV === 'production';
        
        // DETECTAR ORIGEN DEL ARCHIVO:
        // Si empieza con /uploads/comunicados/ → Es del sistema nuevo (React/Node.js)
        const esSistemaNuevo = nombreArchivo.startsWith('/uploads/comunicados/') || 
                               nombreArchivo.startsWith('uploads/comunicados/');
        
        if (esSistemaNuevo) {
          // SISTEMA NUEVO: Archivo en /uploads/comunicados/
          // Construir URL desde el servidor Node.js
          if (nombreArchivo.startsWith('/uploads/')) {
            // Ya tiene la ruta completa relativa
            if (isProduction) {
              // En producción, usar el mismo dominio del backend
              archivoUrl = `https://nuevo.vanguardschools.edu.pe${nombreArchivo}`;
            } else {
              // En desarrollo, usar localhost:5000
              archivoUrl = `http://localhost:5000${nombreArchivo}`;
            }
          } else {
            // Agregar la barra inicial si falta
            archivoUrl = isProduction
              ? `https://nuevo.vanguardschools.edu.pe/${nombreArchivo}`
              : `http://localhost:5000/${nombreArchivo}`;
          }
          console.log(`📄 Comunicado ID ${com.id} (SISTEMA NUEVO): ${com.archivo} -> ${archivoUrl}`);
        } else {
          // SISTEMA ANTERIOR (PHP): Archivo en /Static/Archivos/
          // Usar el subdominio correcto: nuevo.vanguardschools.edu.pe
          const dominioBase = 'https://nuevo.vanguardschools.edu.pe';
          
          // Si ya es una URL completa, validar y corregir si es necesario
          if (nombreArchivo.startsWith('http://') || nombreArchivo.startsWith('https://')) {
            // Corregir errores comunes en URLs existentes
            archivoUrl = nombreArchivo
              .replace(/https?:\/\/(www\.)?vanguardschools\.edu\.pe/gi, dominioBase)
              .replace(/vanguardschools\.comstatic/gi, `${dominioBase}/Static`)
              .replace(/vanguardschools\.com\/static/gi, `${dominioBase}/Static`)
              .replace(/vanguardschools\.com\/Static/gi, `${dominioBase}/Static`)
              .replace(/([^:]\/)\/+/g, '$1'); // Limpiar múltiples barras
          }
          // Si empieza con /Static/, construir URL completa
          else if (nombreArchivo.startsWith('/Static/')) {
            archivoUrl = `${dominioBase}${nombreArchivo}`;
          }
          // Si empieza con Static/ (sin barra inicial), agregar la barra
          else if (nombreArchivo.startsWith('Static/')) {
            archivoUrl = `${dominioBase}/${nombreArchivo}`;
          }
          // Si es solo el nombre del archivo, construir ruta completa
          else {
            // Limpiar barras iniciales y construir URL correcta
            nombreArchivo = nombreArchivo.replace(/^\/+/, '');
            archivoUrl = `${dominioBase}/Static/Archivos/${nombreArchivo}`;
          }
          console.log(`📄 Comunicado ID ${com.id} (SISTEMA ANTERIOR): ${com.archivo} -> ${archivoUrl}`);
        }
      }

      return {
        ...com,
        archivo_url: archivoUrl
      };
    });

    res.json({
      comunicados: comunicadosConUrls,
      pagination: {
        total,
        page: parseInt(page),
        limit: parseInt(limit),
        totalPages: Math.ceil(total / parseInt(limit))
      }
    });
  } catch (error) {
    console.error('Error obteniendo comunicados:', error);
    res.status(500).json({ error: 'Error al obtener comunicados' });
  }
});

/**
 * GET /api/docente/actividades
 * Obtener actividades del calendario (solo lectura)
 * Filtra por año actual (no año activo) basándose en el año de fecha_inicio
 */
router.get('/actividades', async (req, res) => {
  try {
    const { colegio_id } = req.user;
    const { fecha, anio } = req.query;

    // Obtener año actual o el año solicitado
    const anioFiltro = anio ? parseInt(anio) : new Date().getFullYear();

    // actividades NO tiene anio ni estado. Tiene fecha_inicio y fecha_fin (datetime)
    // Filtrar por año actual (o año solicitado): el año de fecha_inicio debe coincidir
    // Si se pasa fecha, filtrar solo las que incluyen ese día en su rango
    let querySql = `
      SELECT a.*
      FROM actividades a
      WHERE a.colegio_id = ?
        AND YEAR(a.fecha_inicio) = ?
    `;
    const params = [colegio_id, anioFiltro];

    if (fecha) {
      // Filtrar actividades que incluyen esta fecha en su rango
      // La fecha puede estar entre fecha_inicio y fecha_fin
      querySql += ` AND DATE(?) BETWEEN DATE(a.fecha_inicio) AND COALESCE(DATE(a.fecha_fin), DATE(a.fecha_inicio))`;
      params.push(fecha);
    }

    querySql += ` ORDER BY a.fecha_inicio ASC`;

    const actividades = await query(querySql, params);

    res.json({ actividades: actividades || [] });
  } catch (error) {
    console.error('Error obteniendo actividades:', error);
    res.status(500).json({ error: 'Error al obtener actividades' });
  }
});

/**
 * GET /api/docente/mensajes/recibidos
 * Obtener mensajes recibidos con información completa del remitente
 */
router.get('/mensajes/recibidos', async (req, res) => {
  try {
    const { usuario_id, colegio_id, anio_activo } = req.user;
    const { page = 1, limit = 50 } = req.query;
    const offset = (parseInt(page) - 1) * parseInt(limit);

    const mensajes = await query(
      `SELECT m.*, 
              CONCAT(
                COALESCE(p.nombres, a.nombres, ap.nombres, ''), ' ',
                COALESCE(
                  p.apellidos, 
                  CONCAT(a.apellido_paterno, ' ', a.apellido_materno),
                  CONCAT(ap.apellido_paterno, ' ', ap.apellido_materno),
                  ''
                )
              ) as remitente_nombre_completo,
              u1.tipo as remitente_tipo,
              u1.usuario as remitente_usuario
       FROM mensajes m
       INNER JOIN usuarios u1 ON u1.id = m.remitente_id
       LEFT JOIN personal p ON p.id = u1.personal_id
       LEFT JOIN alumnos a ON a.id = u1.alumno_id
       LEFT JOIN apoderados ap ON ap.id = u1.apoderado_id
       WHERE m.destinatario_id = ? 
         AND m.tipo = 'RECIBIDO'
         AND m.borrado = 'NO'
         AND YEAR(m.fecha_hora) = ?
       ORDER BY m.fecha_hora DESC
       LIMIT ? OFFSET ?`,
      [usuario_id, anio_activo, parseInt(limit), offset]
    );

    // Obtener archivos adjuntos para cada mensaje
    for (const mensaje of mensajes) {
      const archivos = await query(
        `SELECT id, nombre_archivo, archivo
         FROM mensajes_archivos
         WHERE mensaje_id = ?`,
        [mensaje.id]
      );
      // Construir URLs completas para los archivos
      // IMPORTANTE: Los archivos se guardan en backend/uploads/mensajes/ y se sirven desde el servidor Node.js
      // Igual que en Publicaciones, usar /uploads/mensajes/ para que el servidor Node.js los sirva
      mensaje.archivos = (archivos || []).map(archivo => {
        // Construir ruta relativa como en Publicaciones: /uploads/mensajes/filename
        const rutaArchivo = `/uploads/mensajes/${archivo.archivo}`;
        return {
          ...archivo,
          archivo_url: rutaArchivo // Ruta relativa, el frontend construirá la URL completa
        };
      });
    }

    const total = await query(
      `SELECT COUNT(*) as count
       FROM mensajes m
       WHERE m.destinatario_id = ? 
         AND m.tipo = 'RECIBIDO'
         AND m.borrado = 'NO'
         AND YEAR(m.fecha_hora) = ?`,
      [usuario_id, anio_activo]
    );

    res.json({ 
      mensajes: mensajes || [],
      pagination: {
        total: total[0]?.count || 0,
        page: parseInt(page),
        limit: parseInt(limit),
        totalPages: Math.ceil((total[0]?.count || 0) / parseInt(limit))
      }
    });
  } catch (error) {
    console.error('Error obteniendo mensajes recibidos:', error);
    res.status(500).json({ error: 'Error al obtener mensajes recibidos' });
  }
});

/**
 * GET /api/docente/mensajes/enviados
 * Obtener mensajes enviados con información completa del destinatario
 */
router.get('/mensajes/enviados', async (req, res) => {
  try {
    const { usuario_id, colegio_id, anio_activo } = req.user;
    const { page = 1, limit = 50 } = req.query;
    const offset = (parseInt(page) - 1) * parseInt(limit);

    const mensajes = await query(
      `SELECT m.*,
              CONCAT(
                COALESCE(p.nombres, a.nombres, ap.nombres, ''), ' ',
                COALESCE(
                  p.apellidos, 
                  CONCAT(a.apellido_paterno, ' ', a.apellido_materno),
                  CONCAT(ap.apellido_paterno, ' ', ap.apellido_materno),
                  ''
                )
              ) as destinatario_nombre_completo,
              u2.tipo as destinatario_tipo,
              u2.usuario as destinatario_usuario
       FROM mensajes m
       INNER JOIN usuarios u2 ON u2.id = m.destinatario_id
       LEFT JOIN personal p ON p.id = u2.personal_id
       LEFT JOIN alumnos a ON a.id = u2.alumno_id
       LEFT JOIN apoderados ap ON ap.id = u2.apoderado_id
       WHERE m.remitente_id = ? 
         AND m.tipo = 'ENVIADO'
         AND m.borrado = 'NO'
         AND YEAR(m.fecha_hora) = ?
       ORDER BY m.fecha_hora DESC
       LIMIT ? OFFSET ?`,
      [usuario_id, anio_activo, parseInt(limit), offset]
    );

    // Obtener archivos adjuntos para cada mensaje
    for (const mensaje of mensajes) {
      const archivos = await query(
        `SELECT id, nombre_archivo, archivo
         FROM mensajes_archivos
         WHERE mensaje_id = ?`,
        [mensaje.id]
      );
      // Construir URLs completas para los archivos
      // IMPORTANTE: Los archivos se guardan en backend/uploads/mensajes/ y se sirven desde el servidor Node.js
      // Igual que en Publicaciones, usar /uploads/mensajes/ para que el servidor Node.js los sirva
      mensaje.archivos = (archivos || []).map(archivo => {
        // Construir ruta relativa como en Publicaciones: /uploads/mensajes/filename
        const rutaArchivo = `/uploads/mensajes/${archivo.archivo}`;
        return {
          ...archivo,
          archivo_url: rutaArchivo // Ruta relativa, el frontend construirá la URL completa
        };
      });
    }

    const total = await query(
      `SELECT COUNT(*) as count
       FROM mensajes m
       WHERE m.remitente_id = ? 
         AND m.tipo = 'ENVIADO'
         AND m.borrado = 'NO'
         AND YEAR(m.fecha_hora) = ?`,
      [usuario_id, anio_activo]
    );

    res.json({ 
      mensajes: mensajes || [],
      pagination: {
        total: total[0]?.count || 0,
        page: parseInt(page),
        limit: parseInt(limit),
        totalPages: Math.ceil((total[0]?.count || 0) / parseInt(limit))
      }
    });
  } catch (error) {
    console.error('Error obteniendo mensajes enviados:', error);
    res.status(500).json({ error: 'Error al obtener mensajes enviados' });
  }
});

/**
 * GET /api/docente/mensajes/buscar-destinatarios
 * Buscar destinatarios (alumnos, apoderados, grupos, personal) con búsqueda automática
 */
router.get('/mensajes/buscar-destinatarios', async (req, res) => {
  try {
    const { colegio_id, anio_activo } = req.user;
    const { q = '' } = req.query;

    if (!q || q.trim().length < 2) {
      return res.json({ resultados: [] });
    }

    const searchTerm = `%${q.trim()}%`;
    const resultados = [];

    // Buscar alumnos (del año activo)
    const alumnos = await query(
      `SELECT DISTINCT u.id as usuario_id,
              CONCAT(a.nombres, ' ', a.apellido_paterno, ' ', a.apellido_materno) as nombre_completo,
              'ALUMNO' as tipo,
              CONCAT(g.grado, '° ', g.seccion, ' - ', n.nombre) as info_adicional,
              a.foto
       FROM alumnos a
       INNER JOIN usuarios u ON u.alumno_id = a.id AND u.estado = 'ACTIVO'
       INNER JOIN matriculas m ON m.alumno_id = a.id
       INNER JOIN grupos g ON g.id = m.grupo_id
       INNER JOIN niveles n ON n.id = g.nivel_id
       WHERE a.colegio_id = ?
         AND g.anio = ?
         AND m.estado = 0
         AND (
           a.nombres LIKE ? OR
           a.apellido_paterno LIKE ? OR
           a.apellido_materno LIKE ? OR
           CONCAT(a.nombres, ' ', a.apellido_paterno, ' ', a.apellido_materno) LIKE ?
         )
       LIMIT 20`,
      [colegio_id, anio_activo, searchTerm, searchTerm, searchTerm, searchTerm]
    );

    alumnos.forEach(al => {
      resultados.push({
        usuario_id: al.usuario_id,
        nombre_completo: al.nombre_completo.trim(),
        tipo: al.tipo,
        info_adicional: al.info_adicional,
        foto: al.foto
      });
    });

    // Buscar apoderados
    const apoderados = await query(
      `SELECT DISTINCT u.id as usuario_id,
              CONCAT(ap.nombres, ' ', ap.apellido_paterno, ' ', ap.apellido_materno) as nombre_completo,
              'APODERADO' as tipo,
              CONCAT('Padre/Madre de: ', a.nombres, ' ', a.apellido_paterno) as info_adicional,
              '' as foto
       FROM apoderados ap
       INNER JOIN usuarios u ON u.apoderado_id = ap.id AND u.estado = 'ACTIVO'
       INNER JOIN familias f ON f.apoderado_id = ap.id
       INNER JOIN alumnos a ON a.id = f.alumno_id
       WHERE ap.colegio_id = ?
         AND (
           ap.nombres LIKE ? OR
           ap.apellido_paterno LIKE ? OR
           ap.apellido_materno LIKE ? OR
           CONCAT(ap.nombres, ' ', ap.apellido_paterno, ' ', ap.apellido_materno) LIKE ?
         )
       LIMIT 20`,
      [colegio_id, searchTerm, searchTerm, searchTerm, searchTerm]
    );

    apoderados.forEach(ap => {
      resultados.push({
        usuario_id: ap.usuario_id,
        nombre_completo: ap.nombre_completo.trim(),
        tipo: ap.tipo,
        info_adicional: ap.info_adicional,
        foto: ap.foto
      });
    });

    // Buscar personal/docentes
    const personal = await query(
      `SELECT DISTINCT u.id as usuario_id,
              CONCAT(p.nombres, ' ', p.apellidos) as nombre_completo,
              'PERSONAL' as tipo,
              'Docente/Personal' as info_adicional,
              p.foto
       FROM personal p
       INNER JOIN usuarios u ON u.personal_id = p.id AND u.estado = 'ACTIVO'
       WHERE p.colegio_id = ?
         AND (
           p.nombres LIKE ? OR
           p.apellidos LIKE ? OR
           CONCAT(p.nombres, ' ', p.apellidos) LIKE ?
         )
       LIMIT 20`,
      [colegio_id, searchTerm, searchTerm, searchTerm]
    );

    personal.forEach(p => {
      resultados.push({
        usuario_id: p.usuario_id,
        nombre_completo: p.nombre_completo.trim(),
        tipo: p.tipo,
        info_adicional: p.info_adicional,
        foto: p.foto
      });
    });

    // Buscar grupos (para enviar a todos los alumnos del grupo)
    const grupos = await query(
      `SELECT g.id as grupo_id,
              CONCAT(g.grado, '° ', g.seccion, ' - ', n.nombre) as nombre_completo,
              'GRUPO' as tipo,
              CONCAT(COUNT(DISTINCT m.alumno_id), ' alumnos') as info_adicional,
              '' as foto
       FROM grupos g
       INNER JOIN niveles n ON n.id = g.nivel_id
       INNER JOIN matriculas m ON m.grupo_id = g.id AND m.estado = 0
       WHERE g.colegio_id = ?
         AND g.anio = ?
         AND (
           CONCAT(g.grado, '° ', g.seccion) LIKE ? OR
           n.nombre LIKE ?
         )
       GROUP BY g.id, g.grado, g.seccion, n.nombre
       LIMIT 10`,
      [colegio_id, anio_activo, searchTerm, searchTerm]
    );

    grupos.forEach(g => {
      resultados.push({
        grupo_id: g.grupo_id,
        nombre_completo: String(g.nombre_completo || ''),
        tipo: String(g.tipo || 'GRUPO'),
        info_adicional: String(g.info_adicional || '0 alumnos'),
        foto: String(g.foto || '')
      });
    });

    res.json({ resultados: resultados.slice(0, 50) });
  } catch (error) {
    console.error('Error buscando destinatarios:', error);
    res.status(500).json({ error: 'Error al buscar destinatarios' });
  }
});

/**
 * POST /api/docente/mensajes/subir-imagen
 * Subir imagen desde el editor de texto enriquecido
 */
router.post('/mensajes/subir-imagen', uploadMensajes.single('imagen'), async (req, res) => {
  try {
    if (!req.file) {
      return res.status(400).json({ error: 'No se proporcionó ninguna imagen' });
    }

    const imagenUrl = `/uploads/mensajes/${req.file.filename}`;
    res.json({ url: imagenUrl });
  } catch (error) {
    console.error('Error subiendo imagen:', error);
    res.status(500).json({ error: 'Error al subir la imagen' });
  }
});

/**
 * POST /api/docente/mensajes/enviar
 * Enviar mensaje (soporta múltiples destinatarios y grupos, archivos adjuntos y texto enriquecido)
 */
router.post('/mensajes/enviar', uploadMensajes.array('archivos', 10), async (req, res) => {
  try {
    const { usuario_id, colegio_id } = req.user;
    const { destinatarios, grupos, asunto, mensaje } = req.body;

    // Parsear destinatarios y grupos si vienen como string JSON
    const destinatariosArray = typeof destinatarios === 'string' ? JSON.parse(destinatarios) : destinatarios;
    const gruposArray = typeof grupos === 'string' ? JSON.parse(grupos) : grupos;

    if ((!destinatariosArray || destinatariosArray.length === 0) && (!gruposArray || gruposArray.length === 0)) {
      return res.status(400).json({ error: 'Debe seleccionar al menos un destinatario o grupo' });
    }

    if (!asunto || !mensaje) {
      return res.status(400).json({ error: 'Asunto y mensaje son requeridos' });
    }

    // Responder inmediatamente al cliente
    res.json({
      success: true,
      message: 'Mensaje Enviado',
      procesando: false
    });

    // Continuar el procesamiento en segundo plano
    setImmediate(async () => {
      try {
        // Obtener todos los usuarios destinatarios
        const usuariosDestinatarios = [];

        // Agregar destinatarios directos
        if (destinatariosArray && destinatariosArray.length > 0) {
          usuariosDestinatarios.push(...destinatariosArray);
        }

        // Si hay grupos, obtener todos los alumnos de esos grupos
        let totalAlumnosGrupos = 0;
        if (gruposArray && gruposArray.length > 0) {
          const alumnosGrupos = await query(
            `SELECT DISTINCT u.id as usuario_id
             FROM grupos g
             INNER JOIN matriculas m ON m.grupo_id = g.id AND m.estado = 0
             INNER JOIN alumnos a ON a.id = m.alumno_id
             INNER JOIN usuarios u ON u.alumno_id = a.id AND u.estado = 'ACTIVO'
             WHERE g.id IN (${gruposArray.map(() => '?').join(',')})
               AND g.colegio_id = ?`,
            [...gruposArray, colegio_id]
          );

          totalAlumnosGrupos = alumnosGrupos.length;
          alumnosGrupos.forEach(al => {
            if (!usuariosDestinatarios.includes(al.usuario_id)) {
              usuariosDestinatarios.push(al.usuario_id);
            }
          });
        }

        // Crear un mensaje para cada destinatario
        const fechaHora = new Date().toISOString().slice(0, 19).replace('T', ' ');
        let mensajesInsertados = 0;
        const mensajesIds = []; // Para asociar archivos

        // Insertar mensajes en la base de datos
        for (const destinatarioId of usuariosDestinatarios) {
          try {
            // Mensaje para el remitente (tipo ENVIADO)
            const resultEnviado = await execute(
              `INSERT INTO mensajes (remitente_id, destinatario_id, asunto, mensaje, fecha_hora, estado, tipo, borrado, favorito)
               VALUES (?, ?, ?, ?, ?, 'NO_LEIDO', 'ENVIADO', 'NO', 'NO')`,
              [usuario_id, destinatarioId, asunto, mensaje, fechaHora]
            );

            // Mensaje para el destinatario (tipo RECIBIDO)
            const resultRecibido = await execute(
              `INSERT INTO mensajes (remitente_id, destinatario_id, asunto, mensaje, fecha_hora, estado, tipo, borrado, favorito)
               VALUES (?, ?, ?, ?, ?, 'NO_LEIDO', 'RECIBIDO', 'NO', 'NO')`,
              [usuario_id, destinatarioId, asunto, mensaje, fechaHora]
            );

            mensajesIds.push(resultEnviado.insertId, resultRecibido.insertId);
            mensajesInsertados += 2;
          } catch (error) {
            console.error(`Error insertando mensaje para destinatario ${destinatarioId}:`, error);
            // Continuar con los demás destinatarios aunque uno falle
          }
        }

        // Guardar archivos adjuntos si existen
        // IMPORTANTE: Los archivos se guardan localmente en backend/uploads/mensajes/
        // y se sirven desde el servidor Node.js, igual que en Publicaciones
        // NO se suben al servidor PHP porque el servidor Node.js los sirve directamente
        if (req.files && req.files.length > 0) {
          for (const file of req.files) {
            try {
              // Insertar archivo para cada mensaje creado
              // El archivo ya está guardado en backend/uploads/mensajes/ por multer
              for (const mensajeId of mensajesIds) {
                await execute(
                  `INSERT INTO mensajes_archivos (mensaje_id, nombre_archivo, archivo)
                   VALUES (?, ?, ?)`,
                  [mensajeId, file.originalname, file.filename]
                );
              }
            } catch (error) {
              console.error(`Error guardando archivo ${file.originalname}:`, error);
              // Continuar con los demás archivos aunque uno falle
            }
          }
        }

        // Registrar en auditoría (con formato correcto)
        try {
          let descripcionAuditoria = `Envió mensaje a ${usuariosDestinatarios.length} destinatario(s)`;
          if (gruposArray && gruposArray.length > 0) {
            descripcionAuditoria += ` (${gruposArray.length} grupo(s) con ${totalAlumnosGrupos} alumno(s))`;
          }
          if (destinatariosArray && destinatariosArray.length > 0) {
            descripcionAuditoria += ` y ${destinatariosArray.length} destinatario(s) directo(s)`;
          }
          if (req.files && req.files.length > 0) {
            descripcionAuditoria += ` con ${req.files.length} archivo(s) adjunto(s)`;
          }
          
          await registrarAccion({
            usuario_id: usuario_id,
            colegio_id: colegio_id,
            tipo_usuario: req.user.tipo || 'DOCENTE',
            accion: 'ENVIAR_MENSAJE',
            modulo: 'MENSAJES',
            entidad: 'mensajes',
            descripcion: descripcionAuditoria,
            datos_nuevos: {
              asunto: asunto,
              total_destinatarios: usuariosDestinatarios.length,
              grupos_seleccionados: gruposArray?.length || 0,
              alumnos_en_grupos: totalAlumnosGrupos,
              destinatarios_directos: destinatariosArray?.length || 0,
              archivos_adjuntos: req.files?.length || 0
            },
            resultado: mensajesInsertados > 0 ? 'EXITOSO' : 'ERROR'
          });
        } catch (auditError) {
          console.error('Error en auditoría (no crítico):', auditError);
        }

        if (mensajesInsertados === 0) {
          console.error('No se pudieron insertar los mensajes');
          return;
        }

        let mensajeRespuesta = `Mensaje enviado a ${usuariosDestinatarios.length} destinatario(s)`;
        if (gruposArray && gruposArray.length > 0) {
          mensajeRespuesta += ` (${gruposArray.length} grupo(s) con ${totalAlumnosGrupos} alumno(s))`;
        }
        if (destinatariosArray && destinatariosArray.length > 0) {
          mensajeRespuesta += ` y ${destinatariosArray.length} destinatario(s) directo(s)`;
        }

        console.log('✅ Mensaje enviado exitosamente:', mensajeRespuesta);
      } catch (bgError) {
        console.error('Error en procesamiento en segundo plano:', bgError);
      }
    });
  } catch (error) {
    console.error('Error enviando mensaje:', error);
    res.status(500).json({ error: 'Error al enviar mensaje' });
  }
});

/**
 * GET /api/docente/notificaciones
 * Obtener notificaciones (solo lectura, generadas por admin)
 */
router.get('/notificaciones', async (req, res) => {
  try {
    const { usuario_id, colegio_id } = req.user;

    // Obtener notificaciones para el docente
    // notificaciones NO tiene colegio_id. Tiene para (TODOS/USUARIO), usuario_id, destinatario_id
    // estado es enum('NO ENVIADO','ENVIADO'), no 'ACTIVO'
    const notificaciones = await query(
      `SELECT n.*
       FROM notificaciones n
       WHERE (n.para = 'TODOS' OR (n.para = 'USUARIO' AND n.destinatario_id = ?))
       ORDER BY n.fecha_hora DESC
       LIMIT 20`,
      [usuario_id]
    );

    res.json({ notificaciones: notificaciones || [] });
  } catch (error) {
    console.error('Error obteniendo notificaciones:', error);
    res.status(500).json({ error: 'Error al obtener notificaciones' });
  }
});

/**
 * Función auxiliar para decodificar imágenes de publicaciones
 * Las imágenes vienen como base64_encode(serialize(array)) desde PHP
 */
function decodePublicacionImages(imagesString) {
  if (!imagesString || imagesString === '') {
    return [];
  }

  try {
    // Decodificar base64
    const decoded = Buffer.from(imagesString, 'base64').toString('utf-8');
    
    // El formato serialize de PHP para arrays es: a:N:{i:key1;s:len:"value1";i:key2;s:len:"value2";}
    // Ejemplo: a:2:{i:0;s:25:"/Static/Image/Publicaciones/img1.jpg";i:1;s:25:"/Static/Image/Publicaciones/img2.jpg";}
    if (decoded.startsWith('a:')) {
      const imagenes = [];
      // Extraer todas las rutas de imágenes del formato serialize
      const regex = /s:\d+:"([^"]+)"/g;
      let match;
      while ((match = regex.exec(decoded)) !== null) {
        const ruta = match[1];
        // Convertir ruta relativa a URL completa
        if (ruta.startsWith('/Static/')) {
          const isProduction = process.env.NODE_ENV === 'production';
          if (isProduction) {
            imagenes.push(`https://vanguardschools.edu.pe${ruta}`);
          } else {
            // En desarrollo, las imágenes están en el servidor PHP
            imagenes.push(`http://localhost:5000${ruta}`);
          }
        } else if (ruta.startsWith('/uploads/')) {
          // Rutas de uploads del backend Node.js
          const isProduction = process.env.NODE_ENV === 'production';
          if (isProduction) {
            imagenes.push(`https://vanguardschools.edu.pe${ruta}`);
          } else {
            imagenes.push(`http://localhost:5000${ruta}`);
          }
        } else {
          // Si ya es una URL completa o ruta relativa, usarla tal cual
          imagenes.push(ruta);
        }
      }
      return imagenes;
    } else {
      // Intentar como JSON (por si acaso)
      try {
        const parsed = JSON.parse(decoded);
        return Array.isArray(parsed) ? parsed : [parsed];
      } catch (e) {
        // Si no es JSON, tratar como string simple
        return [imagesString];
      }
    }
  } catch (error) {
    console.warn('Error decodificando imágenes de publicación:', error);
    return [];
  }
}

/**
 * GET /api/docente/publicaciones
 * Obtener publicaciones (feed tipo Facebook)
 */
router.get('/publicaciones', async (req, res) => {
  try {
    const { colegio_id } = req.user;

    // Obtener publicaciones con nombre completo del autor y foto
    // publicaciones NO tiene estado ni autor_id. Tiene usuario_id y fecha_hora
    const publicaciones = await query(
      `SELECT p.*, 
              u.usuario as autor_usuario,
              u.id as autor_id,
              u.tipo as autor_tipo,
              COALESCE(
                CONCAT(pers.nombres, ' ', pers.apellidos),
                CONCAT(al.nombres, ' ', al.apellido_paterno, ' ', al.apellido_materno),
                u.usuario
              ) as autor_nombre_completo,
              COALESCE(pers.foto, al.foto) as autor_foto
       FROM publicaciones p
       LEFT JOIN usuarios u ON u.id = p.usuario_id
       LEFT JOIN personal pers ON pers.id = u.personal_id
       LEFT JOIN alumnos al ON al.id = u.alumno_id
       WHERE p.colegio_id = ?
       ORDER BY p.fecha_hora DESC
       LIMIT 50`,
      [colegio_id]
    );

    // Obtener todos los grupos del colegio para mapear IDs a nombres
    const gruposMap = {};
    try {
      const grupos = await query(
        `SELECT g.id, n.nombre as nivel_nombre, g.grado, g.seccion
         FROM grupos g
         INNER JOIN niveles n ON n.id = g.nivel_id
         WHERE g.colegio_id = ?`,
        [colegio_id]
      );
      grupos.forEach(grupo => {
        gruposMap[grupo.id] = `${grupo.nivel_nombre} ${grupo.grado}° ${grupo.seccion}`;
      });
    } catch (error) {
      console.warn('Error obteniendo grupos para publicaciones:', error);
    }

    // Decodificar imágenes y archivos de cada publicación
    const publicacionesConImagenes = publicaciones.map(pub => {
      // Decodificar privacidad (puede ser "TODOS", "-1", "-2", o IDs de grupos separados por comas)
      let paraTexto = 'Todos';
      if (pub.privacidad && pub.privacidad !== '' && pub.privacidad !== '-1') {
        if (pub.privacidad === '-2') {
          paraTexto = 'Personal Administrativo';
        } else {
          // Es una lista de IDs de grupos separados por comas
          const grupoIds = pub.privacidad.split(',').map(id => parseInt(id.trim())).filter(id => !isNaN(id) && id > 0);
          if (grupoIds.length > 0) {
            // Obtener nombres de los grupos desde el mapa
            const nombresGrupos = grupoIds
              .map(id => gruposMap[id])
              .filter(nombre => nombre); // Filtrar grupos que no se encontraron
            
            if (nombresGrupos.length > 0) {
              if (nombresGrupos.length === 1) {
                paraTexto = nombresGrupos[0];
              } else {
                paraTexto = nombresGrupos.join(', ');
              }
            } else {
              paraTexto = grupoIds.length === 1 ? '1 Grupo' : `${grupoIds.length} Grupos`;
            }
          }
        }
      }

      // Decodificar archivos (similar a imágenes)
      let archivos = [];
      if (pub.archivos && pub.archivos !== '') {
        try {
          const decoded = Buffer.from(pub.archivos, 'base64').toString('utf-8');
          if (decoded.startsWith('a:')) {
            const regex = /s:\d+:"([^"]+)"/g;
            let match;
            while ((match = regex.exec(decoded)) !== null) {
              archivos.push(match[1]);
            }
          }
        } catch (e) {
          console.warn('Error decodificando archivos:', e);
        }
      }

      const imagenesDecodificadas = decodePublicacionImages(pub.images);
      console.log(`📰 Publicación ID ${pub.id}: ${imagenesDecodificadas.length} imagen(es) decodificada(s)`, imagenesDecodificadas);
      
      // Construir URL completa de la foto del autor
      let autorFotoUrl = null;
      if (pub.autor_foto && pub.autor_foto !== '') {
        const isProduction = process.env.NODE_ENV === 'production';
        
        // Si ya es una URL completa, usarla directamente
        if (pub.autor_foto.startsWith('http')) {
          autorFotoUrl = pub.autor_foto;
        } else if (pub.autor_foto.startsWith('/uploads/')) {
          // Si ya tiene la ruta /uploads/, construir URL completa
          if (isProduction) {
            autorFotoUrl = `https://vanguardschools.edu.pe${pub.autor_foto}`;
          } else {
            autorFotoUrl = `http://localhost:5000${pub.autor_foto}`;
          }
        } else {
          // Es solo el nombre del archivo, determinar si es personal o alumno según el tipo de usuario
          const esPersonal = pub.autor_tipo === 'DOCENTE' || pub.autor_tipo === 'DIRECTOR' || pub.autor_tipo === 'ADMINISTRADOR';
          
          if (isProduction) {
            autorFotoUrl = `https://vanguardschools.edu.pe/Static/Image/Fotos/${pub.autor_foto}`;
          } else {
            // En desarrollo, usar la ruta de uploads según el tipo
            const uploadPath = esPersonal ? 'uploads/personal' : 'uploads/alumnos';
            autorFotoUrl = `http://localhost:5000/${uploadPath}/${pub.autor_foto}`;
          }
        }
      }

      return {
        ...pub,
        images: imagenesDecodificadas,
        archivos: archivos,
        para_texto: paraTexto,
        autor_foto_url: autorFotoUrl
      };
    });

    res.json({ publicaciones: publicacionesConImagenes || [] });
  } catch (error) {
    console.error('Error obteniendo publicaciones:', error);
    res.status(500).json({ error: 'Error al obtener publicaciones' });
  }
});

/**
 * POST /api/docente/publicaciones
 * Crear publicación (tipo Facebook)
 */
// Configurar multer para aceptar tanto imágenes como archivos
// Usar storage dinámico según el tipo de archivo
const uploadPublicacionesCompleto = multer({
  storage: multer.diskStorage({
    destination: (req, file, cb) => {
      const isImage = /jpeg|jpg|png|gif|webp/i.test(path.extname(file.originalname)) || file.mimetype.startsWith('image/');
      if (isImage) {
        const uploadPath = path.join(__dirname, '../../backend/uploads/publicaciones');
        if (!fs.existsSync(uploadPath)) {
          fs.mkdirSync(uploadPath, { recursive: true });
        }
        cb(null, uploadPath);
      } else {
        const uploadPath = path.join(__dirname, '../../backend/uploads/archivos');
        if (!fs.existsSync(uploadPath)) {
          fs.mkdirSync(uploadPath, { recursive: true });
        }
        cb(null, uploadPath);
      }
    },
    filename: (req, file, cb) => {
      const isImage = /jpeg|jpg|png|gif|webp/i.test(path.extname(file.originalname)) || file.mimetype.startsWith('image/');
      const uniqueSuffix = Date.now() + '-' + Math.round(Math.random() * 1E9);
      if (isImage) {
        cb(null, `publicacion-${uniqueSuffix}${path.extname(file.originalname)}`);
      } else {
        cb(null, `archivo-${uniqueSuffix}${path.extname(file.originalname)}`);
      }
    }
  }),
  limits: { fileSize: 50 * 1024 * 1024 }, // 50MB
  fileFilter: (req, file, cb) => {
    // Permitir imágenes y archivos
    const isImage = /jpeg|jpg|png|gif|webp/i.test(path.extname(file.originalname)) || file.mimetype.startsWith('image/');
    const isFile = /pdf|doc|docx|xls|xlsx|ppt|pptx|txt|zip|rar/i.test(path.extname(file.originalname));
    
    if (isImage || isFile) {
      return cb(null, true);
    } else {
      cb(new Error('Tipo de archivo no permitido'));
    }
  }
});

router.post('/publicaciones', uploadPublicacionesCompleto.fields([
  { name: 'imagen', maxCount: 1 },
  { name: 'archivo', maxCount: 1 }
]), async (req, res) => {
  try {
    const { usuario_id, colegio_id } = req.user;
    const { contenido, compartir_con, grupos_ids } = req.body; // compartir_con: 'todos' o 'grupos', grupos_ids: array de IDs

    if (!contenido) {
      return res.status(400).json({ error: 'El contenido es requerido' });
    }

    let imagenPath = '';
    if (req.files && req.files.imagen && req.files.imagen[0]) {
      imagenPath = `/uploads/publicaciones/${req.files.imagen[0].filename}`;
    }

    let archivoPath = '';
    if (req.files && req.files.archivo && req.files.archivo[0]) {
      // Verificar si el archivo es realmente un archivo (no imagen)
      const isImage = /jpeg|jpg|png|gif|webp/i.test(path.extname(req.files.archivo[0].originalname)) || 
                      req.files.archivo[0].mimetype.startsWith('image/');
      if (!isImage) {
        archivoPath = `/uploads/archivos/${req.files.archivo[0].filename}`;
      } else {
        // Si es una imagen pero viene en el campo "archivo", tratarla como imagen
        if (!imagenPath) {
          imagenPath = `/uploads/publicaciones/${req.files.archivo[0].filename}`;
        }
      }
    }

    // Preparar privacidad según compartir_con
    let privacidad = '-1'; // -1 = Todos por defecto
    if (compartir_con === 'grupos' && grupos_ids) {
      const gruposArray = typeof grupos_ids === 'string' ? JSON.parse(grupos_ids) : grupos_ids;
      if (gruposArray.length > 0) {
        privacidad = gruposArray.join(',');
      }
    }

    // Preparar imágenes (serializar como PHP: base64_encode(serialize(array)))
    let imagesSerialized = '';
    if (imagenPath) {
      console.log('📸 Guardando imagen:', imagenPath);
      const imagesArray = [imagenPath];
      // Formato serialize de PHP: a:N:{i:0;s:len:"value0";i:1;s:len:"value1";}
      const serialized = `a:${imagesArray.length}:{${imagesArray.map((img, idx) => `i:${idx};s:${img.length}:"${img}"`).join(';')}}`;
      imagesSerialized = Buffer.from(serialized).toString('base64');
      console.log('✅ Imagen serializada correctamente');
    } else {
      console.log('⚠️ No hay imagen para guardar');
    }

    // Preparar archivos (serializar como PHP: base64_encode(serialize(array)))
    let archivosSerialized = '';
    if (archivoPath) {
      const archivosArray = [archivoPath];
      // Formato serialize de PHP: a:N:{i:0;s:len:"value0";i:1;s:len:"value1";}
      const serialized = `a:${archivosArray.length}:{${archivosArray.map((arch, idx) => `i:${idx};s:${arch.length}:"${arch}"`).join(';')}}`;
      archivosSerialized = Buffer.from(serialized).toString('base64');
    }

    // Insertar publicación en MySQL
    // La tabla publicaciones tiene: id, colegio_id, usuario_id, contenido, tipo_video, video_id, images, archivos, privacidad, fecha_hora
    const fechaHora = new Date().toISOString().slice(0, 19).replace('T', ' ');
    const result = await execute(
      `INSERT INTO publicaciones 
       (colegio_id, usuario_id, contenido, tipo_video, video_id, images, archivos, privacidad, fecha_hora)
       VALUES (?, ?, ?, '', '', ?, ?, ?, ?)`,
      [colegio_id, usuario_id, contenido, imagesSerialized, archivosSerialized, privacidad, fechaHora]
    );

    await registrarAccion(usuario_id, colegio_id, 'CREAR_PUBLICACION', `Docente creó publicación ID: ${result.insertId}`);

    res.json({
      success: true,
      message: 'Publicación creada correctamente',
      publicacion: {
        id: result.insertId,
        contenido,
        compartir_con,
        grupos_ids: grupos_ids ? (typeof grupos_ids === 'string' ? JSON.parse(grupos_ids) : grupos_ids) : [],
        imagen: imagenPath,
        archivo: archivoPath
      }
    });
  } catch (error) {
    console.error('Error creando publicación:', error);
    res.status(500).json({ error: 'Error al crear publicación' });
  }
});

/**
 * DELETE /api/docente/publicaciones/:publicacionId
 * Eliminar publicación (solo el autor puede eliminar)
 */
router.delete('/publicaciones/:publicacionId', async (req, res) => {
  try {
    const { usuario_id, colegio_id } = req.user;
    const { publicacionId } = req.params;

    // Verificar que la publicación existe y pertenece al usuario
    const publicaciones = await query(
      `SELECT p.* FROM publicaciones p
       WHERE p.id = ? AND p.usuario_id = ? AND p.colegio_id = ?`,
      [publicacionId, usuario_id, colegio_id]
    );

    if (publicaciones.length === 0) {
      return res.status(404).json({ error: 'Publicación no encontrada o no tienes permiso para eliminarla' });
    }

    // Eliminar publicación de MySQL
    await execute(
      `DELETE FROM publicaciones WHERE id = ? AND usuario_id = ? AND colegio_id = ?`,
      [publicacionId, usuario_id, colegio_id]
    );

    await registrarAccion(usuario_id, colegio_id, 'ELIMINAR_PUBLICACION', `Docente eliminó publicación ${publicacionId}`);

    res.json({
      success: true,
      message: 'Publicación eliminada correctamente'
    });
  } catch (error) {
    console.error('Error eliminando publicación:', error);
    res.status(500).json({ error: 'Error al eliminar publicación' });
  }
});

/**
 * ============================================
 * AULA VIRTUAL - ENDPOINTS
 * ============================================
 */

/**
 * GET /api/docente/aula-virtual/config
 * Obtener configuración del aula virtual (total_notas del colegio)
 */
router.get('/aula-virtual/config', async (req, res) => {
  try {
    const { colegio_id } = req.user;
    
    const colegio = await query(
      `SELECT total_notas FROM colegios WHERE id = ?`,
      [colegio_id]
    );

    if (colegio.length === 0) {
      return res.status(404).json({ error: 'Colegio no encontrado' });
    }

    res.json({ 
      total_notas: colegio[0].total_notas || 4 // Por defecto 4 bimestres
    });
  } catch (error) {
    console.error('Error obteniendo configuración:', error);
    res.status(500).json({ error: 'Error al obtener configuración' });
  }
});

/**
 * GET /api/docente/aula-virtual/temas
 * Obtener temas de una asignatura (filtrados por ciclo/bimestre)
 */
router.get('/aula-virtual/temas', async (req, res) => {
  try {
    const { usuario_id, colegio_id, anio_activo } = req.user;
    const { asignatura_id, ciclo } = req.query;

    if (!asignatura_id) {
      return res.status(400).json({ error: 'asignatura_id es requerido' });
    }

    // Si no se proporciona ciclo, usar 1 por defecto
    const cicloFiltro = ciclo ? parseInt(ciclo) : 1;

    // Verificar que el docente tiene acceso a esta asignatura
    const asignatura = await query(
      `SELECT a.* FROM asignaturas a
       INNER JOIN grupos g ON g.id = a.grupo_id
       WHERE a.id = ? AND a.personal_id = ? AND a.colegio_id = ? AND g.anio = ?`,
      [asignatura_id, req.user.personal_id, colegio_id, anio_activo]
    );

    if (asignatura.length === 0) {
      return res.status(403).json({ error: 'No tienes acceso a esta asignatura' });
    }

    // Obtener temas de la asignatura (asignaturas_temas NO tiene ciclo, se muestra todos)
    const temas = await query(
      `SELECT * FROM asignaturas_temas
       WHERE asignatura_id = ?
       ORDER BY fecha DESC, id DESC`,
      [asignatura_id]
    );

    res.json({ temas: temas || [] });
  } catch (error) {
    console.error('Error obteniendo temas:', error);
    res.status(500).json({ error: 'Error al obtener temas' });
  }
});

/**
 * GET /api/docente/aula-virtual/tareas
 * Obtener tareas de una asignatura (filtradas por ciclo/bimestre)
 */
router.get('/aula-virtual/tareas', async (req, res) => {
  try {
    const { usuario_id, colegio_id, anio_activo } = req.user;
    const { asignatura_id, ciclo } = req.query;

    if (!asignatura_id) {
      return res.status(400).json({ error: 'asignatura_id es requerido' });
    }

    // Si no se proporciona ciclo, usar 1 por defecto
    const cicloFiltro = ciclo ? parseInt(ciclo) : 1;

    // Verificar que el docente tiene acceso a esta asignatura
    const asignatura = await query(
      `SELECT a.* FROM asignaturas a
       INNER JOIN grupos g ON g.id = a.grupo_id
       WHERE a.id = ? AND a.personal_id = ? AND a.colegio_id = ? AND g.anio = ?`,
      [asignatura_id, req.user.personal_id, colegio_id, anio_activo]
    );

    if (asignatura.length === 0) {
      return res.status(403).json({ error: 'No tienes acceso a esta asignatura' });
    }

    // Obtener tareas de la asignatura filtradas por ciclo
    const tareas = await query(
      `SELECT * FROM asignaturas_tareas
       WHERE asignatura_id = ? AND ciclo = ?
       ORDER BY fecha_entrega DESC, fecha_hora DESC`,
      [asignatura_id, cicloFiltro]
    );

    res.json({ tareas: tareas || [] });
  } catch (error) {
    console.error('Error obteniendo tareas:', error);
    res.status(500).json({ error: 'Error al obtener tareas' });
  }
});

/**
 * GET /api/docente/aula-virtual/examenes
 * Obtener exámenes de una asignatura (filtrados por ciclo/bimestre)
 */
router.get('/aula-virtual/examenes', async (req, res) => {
  try {
    const { usuario_id, colegio_id, anio_activo } = req.user;
    const { asignatura_id, ciclo } = req.query;

    if (!asignatura_id) {
      return res.status(400).json({ error: 'asignatura_id es requerido' });
    }

    // Si no se proporciona ciclo, usar 1 por defecto
    const cicloFiltro = ciclo ? parseInt(ciclo) : 1;

    // Verificar que el docente tiene acceso a esta asignatura
    const asignatura = await query(
      `SELECT a.* FROM asignaturas a
       INNER JOIN grupos g ON g.id = a.grupo_id
       WHERE a.id = ? AND a.personal_id = ? AND a.colegio_id = ? AND g.anio = ?`,
      [asignatura_id, req.user.personal_id, colegio_id, anio_activo]
    );

    if (asignatura.length === 0) {
      return res.status(403).json({ error: 'No tienes acceso a esta asignatura' });
    }

    // Obtener exámenes de la asignatura filtrados por ciclo
    const examenes = await query(
      `SELECT * FROM asignaturas_examenes
       WHERE asignatura_id = ? AND ciclo = ?
       ORDER BY fecha_desde DESC`,
      [asignatura_id, cicloFiltro]
    );

    res.json({ examenes: examenes || [] });
  } catch (error) {
    console.error('Error obteniendo exámenes:', error);
    res.status(500).json({ error: 'Error al obtener exámenes' });
  }
});

/**
 * GET /api/docente/aula-virtual/archivos
 * Obtener archivos/temas interactivos de una asignatura (filtrados por ciclo/bimestre)
 */
router.get('/aula-virtual/archivos', async (req, res) => {
  try {
    const { usuario_id, colegio_id, anio_activo } = req.user;
    const { asignatura_id, ciclo } = req.query;

    if (!asignatura_id) {
      return res.status(400).json({ error: 'asignatura_id es requerido' });
    }

    // Si no se proporciona ciclo, usar 1 por defecto
    const cicloFiltro = ciclo ? parseInt(ciclo) : 1;

    // Verificar que el docente tiene acceso a esta asignatura
    const asignatura = await query(
      `SELECT a.* FROM asignaturas a
       INNER JOIN grupos g ON g.id = a.grupo_id
       WHERE a.id = ? AND a.personal_id = ? AND a.colegio_id = ? AND g.anio = ?`,
      [asignatura_id, req.user.personal_id, colegio_id, anio_activo]
    );

    if (asignatura.length === 0) {
      return res.status(403).json({ error: 'No tienes acceso a esta asignatura' });
    }

    // Obtener archivos de la asignatura filtrados por ciclo
    const archivos = await query(
      `SELECT * FROM asignaturas_archivos
       WHERE asignatura_id = ? AND ciclo = ?
       ORDER BY orden ASC, fecha_hora DESC`,
      [asignatura_id, cicloFiltro]
    );

    // Construir URLs completas para los archivos
    const isProduction = process.env.NODE_ENV === 'production';
    const archivosConUrls = archivos.map(archivo => {
      let archivoUrl = null;
      let enlaceUrl = null;

      if (archivo.archivo && archivo.archivo !== '') {
        if (archivo.archivo.startsWith('http')) {
          archivoUrl = archivo.archivo;
        } else if (archivo.archivo.startsWith('/Static/')) {
          archivoUrl = isProduction 
            ? `https://vanguardschools.edu.pe${archivo.archivo}`
            : `http://localhost:5000${archivo.archivo}`;
        } else {
          archivoUrl = isProduction
            ? `https://vanguardschools.edu.pe/Static/Archivos/${archivo.archivo}`
            : `http://localhost:5000/Static/Archivos/${archivo.archivo}`;
        }
      }

      if (archivo.enlace && archivo.enlace !== '') {
        enlaceUrl = archivo.enlace;
      }

      return {
        ...archivo,
        archivo_url: archivoUrl,
        enlace_url: enlaceUrl
      };
    });

    res.json({ archivos: archivosConUrls || [] });
  } catch (error) {
    console.error('Error obteniendo archivos:', error);
    res.status(500).json({ error: 'Error al obtener archivos' });
  }
});

/**
 * GET /api/docente/aula-virtual/videos
 * Obtener videos de una asignatura (filtrados por ciclo/bimestre)
 */
router.get('/aula-virtual/videos', async (req, res) => {
  try {
    const { usuario_id, colegio_id, anio_activo } = req.user;
    const { asignatura_id, ciclo } = req.query;

    if (!asignatura_id) {
      return res.status(400).json({ error: 'asignatura_id es requerido' });
    }

    // Si no se proporciona ciclo, usar 1 por defecto
    const cicloFiltro = ciclo ? parseInt(ciclo) : 1;

    // Verificar que el docente tiene acceso a esta asignatura
    const asignatura = await query(
      `SELECT a.* FROM asignaturas a
       INNER JOIN grupos g ON g.id = a.grupo_id
       WHERE a.id = ? AND a.personal_id = ? AND a.colegio_id = ? AND g.anio = ?`,
      [asignatura_id, req.user.personal_id, colegio_id, anio_activo]
    );

    if (asignatura.length === 0) {
      return res.status(403).json({ error: 'No tienes acceso a esta asignatura' });
    }

    // Obtener videos de la asignatura filtrados por ciclo
    const videos = await query(
      `SELECT * FROM asignaturas_videos
       WHERE asignatura_id = ? AND ciclo = ?
       ORDER BY fecha_hora DESC`,
      [asignatura_id, cicloFiltro]
    );

    res.json({ videos: videos || [] });
  } catch (error) {
    console.error('Error obteniendo videos:', error);
    res.status(500).json({ error: 'Error al obtener videos' });
  }
});

/**
 * GET /api/docente/aula-virtual/enlaces
 * Obtener enlaces de ayuda de una asignatura (filtrados por ciclo/bimestre)
 */
router.get('/aula-virtual/enlaces', async (req, res) => {
  try {
    const { usuario_id, colegio_id, anio_activo } = req.user;
    const { asignatura_id, ciclo } = req.query;

    if (!asignatura_id) {
      return res.status(400).json({ error: 'asignatura_id es requerido' });
    }

    // Si no se proporciona ciclo, usar 1 por defecto
    const cicloFiltro = ciclo ? parseInt(ciclo) : 1;

    // Verificar que el docente tiene acceso a esta asignatura
    const asignatura = await query(
      `SELECT a.* FROM asignaturas a
       INNER JOIN grupos g ON g.id = a.grupo_id
       WHERE a.id = ? AND a.personal_id = ? AND a.colegio_id = ? AND g.anio = ?`,
      [asignatura_id, req.user.personal_id, colegio_id, anio_activo]
    );

    if (asignatura.length === 0) {
      return res.status(403).json({ error: 'No tienes acceso a esta asignatura' });
    }

    // Obtener enlaces de la asignatura filtrados por ciclo
    const enlaces = await query(
      `SELECT * FROM asignaturas_enlaces
       WHERE asignatura_id = ? AND ciclo = ?
       ORDER BY fecha_hora DESC`,
      [asignatura_id, cicloFiltro]
    );

    res.json({ enlaces: enlaces || [] });
  } catch (error) {
    console.error('Error obteniendo enlaces:', error);
    res.status(500).json({ error: 'Error al obtener enlaces' });
  }
});

module.exports = router;

