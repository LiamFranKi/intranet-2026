const express = require('express');
const router = express.Router();
const multer = require('multer');
const path = require('path');
const fs = require('fs');
const { query, execute, getAnioActivo } = require('../utils/mysql');
const { authenticateToken, requireUserType } = require('../middleware/auth');
const phpSerialize = require('php-serialize');

// Configurar multer para subir archivos de mensajes
const mensajesStorage = multer.diskStorage({
  destination: (req, file, cb) => {
    // Guardar en Static/Archivos/ del sistema PHP (compartido con ambos sistemas)
    const uploadPath = '/home/vanguard/nuevo.vanguardschools.edu.pe/Static/Archivos';
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

// Todas las rutas requieren autenticación y ser ALUMNO
router.use(authenticateToken);
router.use(requireUserType('ALUMNO'));

/**
 * GET /api/alumno/dashboard
 * Obtener datos del dashboard del alumno
 */
router.get('/dashboard', async (req, res) => {
  try {
    const { usuario_id, colegio_id, anio_activo } = req.user;
    
    console.log('📊 [ALUMNO DASHBOARD] Iniciando carga para usuario:', usuario_id, 'colegio:', colegio_id, 'año:', anio_activo);

    // Obtener datos del alumno
    let alumno = [];
    try {
      alumno = await query(
        `SELECT a.*, u.tipo as tipo_usuario
         FROM alumnos a
         INNER JOIN usuarios u ON u.alumno_id = a.id
         WHERE u.id = ? AND u.colegio_id = ? AND u.estado = 'ACTIVO'`,
        [usuario_id, colegio_id]
      );
    } catch (error) {
      console.error('❌ [ALUMNO DASHBOARD] Error obteniendo datos del alumno:', error);
      return res.status(500).json({ error: 'Error al obtener datos del alumno', details: error.message });
    }

    if (alumno.length === 0) {
      console.warn('⚠️ [ALUMNO DASHBOARD] Alumno no encontrado para usuario:', usuario_id);
      return res.status(404).json({ error: 'Alumno no encontrado' });
    }

    const alumnoData = alumno[0];
    console.log('✅ [ALUMNO DASHBOARD] Alumno encontrado:', alumnoData.id);

    // Obtener matrícula actual del alumno
    let matricula = [];
    try {
      matricula = await query(
        `SELECT m.*, 
                g.grado, 
                g.seccion,
                n.nombre as nivel_nombre,
                t.nombre as turno_nombre
         FROM matriculas m
         INNER JOIN grupos g ON g.id = m.grupo_id
         INNER JOIN niveles n ON n.id = g.nivel_id
         INNER JOIN turnos t ON t.id = g.turno_id
         WHERE m.alumno_id = ? AND m.colegio_id = ? AND g.anio = ? 
         AND (m.estado = 0 OR m.estado = 4)
         LIMIT 1`,
        [alumnoData.id, colegio_id, anio_activo]
      );
    } catch (error) {
      console.error('❌ [ALUMNO DASHBOARD] Error obteniendo matrícula:', error);
      // Continuar sin matrícula si hay error
      matricula = [];
    }

    const matriculaData = matricula.length > 0 ? matricula[0] : null;
    const grupoId = matriculaData ? matriculaData.grupo_id : null;
    console.log('✅ [ALUMNO DASHBOARD] Matrícula:', matriculaData ? `Grupo ${grupoId}` : 'Sin matrícula');

    // Contar cursos asignados (asignaturas del grupo del alumno en el año activo)
    const cursosAsignados = grupoId ? await query(
      `SELECT COUNT(DISTINCT a.id) as total
       FROM asignaturas a
       INNER JOIN grupos g ON g.id = a.grupo_id
       WHERE a.grupo_id = ? AND a.colegio_id = ? AND g.anio = ?`,
      [grupoId, colegio_id, anio_activo]
    ) : [{ total: 0 }];

    // Contar docentes únicos que le enseñan (sin repetir docente)
    const totalDocentes = grupoId ? await query(
      `SELECT COUNT(DISTINCT a.personal_id) as total
       FROM asignaturas a
       INNER JOIN grupos g ON g.id = a.grupo_id
       WHERE a.grupo_id = ? AND a.colegio_id = ? AND g.anio = ?`,
      [grupoId, colegio_id, anio_activo]
    ) : [{ total: 0 }];

    // Contar total de matrículas registradas (una por año)
    const totalMatriculas = await query(
      `SELECT COUNT(DISTINCT g.anio) as total
       FROM matriculas m
       INNER JOIN grupos g ON g.id = m.grupo_id
       WHERE m.alumno_id = ? AND m.colegio_id = ?`,
      [alumnoData.id, colegio_id]
    );

    // Total Tardanzas (por ahora 0)
    const totalTardanzas = [{ total: 0 }];

    // Total Faltas (por ahora 0)
    const totalFaltas = [{ total: 0 }];

    // Contar mensajes no leídos (solo tipo RECIBIDO, igual que la lista de mensajes)
    const mensajesNoLeidos = await query(
      `SELECT COUNT(*) as total
       FROM mensajes m
       WHERE m.destinatario_id = ? 
         AND m.estado = 'NO_LEIDO' 
         AND m.tipo = 'RECIBIDO'
         AND m.borrado = 'NO'`,
      [usuario_id]
    );

    // Obtener asignaturas (cursos) del alumno
    // NOTA: No usar LEFT JOIN areas_curso porque esa tabla no existe en la BD
    const asignaturas = grupoId ? await query(
      `SELECT a.id,
              c.nombre as curso_nombre,
              c.imagen as curso_imagen,
              NULL as area_nombre,
              CONCAT(p.nombres, ' ', p.apellidos) as docente_nombres,
              p.apellidos as docente_apellidos
       FROM asignaturas a
       INNER JOIN grupos g ON g.id = a.grupo_id
       INNER JOIN cursos c ON c.id = a.curso_id
       INNER JOIN personal p ON p.id = a.personal_id
       WHERE a.grupo_id = ? AND a.colegio_id = ? AND g.anio = ?
       ORDER BY c.nombre ASC`,
      [grupoId, colegio_id, anio_activo]
    ) : [];

    // Próximos exámenes (del grupo del alumno)
    // IMPORTANTE: Incluir exámenes ACTIVOS e INACTIVOS con fecha >= hoy
    // Los exámenes INACTIVOS con fecha/hora se activarán automáticamente cuando llegue la hora
    // Similar a cómo funciona en el dashboard del docente (no filtra solo por ACTIVO)
    // IMPORTANTE: Devolver los mismos campos que el dashboard del docente para que el modal funcione igual
    const proximosExamenes = grupoId ? await query(
      `SELECT ae.*,
              COALESCE(ae.titulo, 'Examen') as titulo,
              c.nombre as asignatura_nombre,
              a.id as asignatura_id,
              g.grado,
              g.seccion,
              n.nombre as nivel_nombre,
              CONCAT(p.nombres, ' ', p.apellidos) as docente_nombre,
              DATE(ae.fecha_desde) as fecha_evento
       FROM asignaturas_examenes ae
       INNER JOIN asignaturas a ON a.id = ae.asignatura_id
       INNER JOIN grupos g ON g.id = a.grupo_id
       INNER JOIN niveles n ON n.id = g.nivel_id
       INNER JOIN cursos c ON c.id = a.curso_id
       INNER JOIN personal p ON p.id = a.personal_id
       WHERE a.grupo_id = ? AND a.colegio_id = ? AND g.anio = ?
       AND DATE(ae.fecha_desde) >= CURDATE()
       ORDER BY COALESCE(ae.fecha_desde, '9999-12-31') ASC
       LIMIT 10`,
      [grupoId, colegio_id, anio_activo]
    ) : [];

    // Próximas tareas (del grupo del alumno)
    // IMPORTANTE: Devolver los mismos campos que el dashboard del docente para que el modal funcione igual
    const proximasTareas = grupoId ? await query(
      `SELECT t.*,
              t.titulo,
              c.nombre as asignatura_nombre,
              a.id as asignatura_id,
              g.grado,
              g.seccion,
              n.nombre as nivel_nombre,
              CONCAT(p.nombres, ' ', p.apellidos) as docente_nombre,
              DATE(t.fecha_entrega) as fecha_evento
       FROM asignaturas_tareas t
       INNER JOIN asignaturas a ON a.id = t.asignatura_id
       INNER JOIN grupos g ON g.id = a.grupo_id
       INNER JOIN niveles n ON n.id = g.nivel_id
       INNER JOIN cursos c ON c.id = a.curso_id
       INNER JOIN personal p ON p.id = a.personal_id
       WHERE a.grupo_id = ? AND a.colegio_id = ? AND g.anio = ?
       AND DATE(t.fecha_entrega) >= DATE(NOW())
       ORDER BY t.fecha_entrega ASC
       LIMIT 10`,
      [grupoId, colegio_id, anio_activo]
    ) : [];

    // Actividades próximas (solo futuras del año actual)
    const añoActual = new Date().getFullYear();
    const actividades = await query(
      `SELECT a.*,
              DATE(a.fecha_inicio) as fecha_evento
       FROM actividades a
       WHERE a.colegio_id = ?
       AND YEAR(a.fecha_inicio) = ?
       AND DATE(a.fecha_inicio) >= DATE(NOW())
       ORDER BY a.fecha_inicio ASC
       LIMIT 10`,
      [colegio_id, añoActual]
    );

    // Obtener comunicado con show_in_home activo (más reciente)
    const comunicadoHome = await query(
      `SELECT c.*
       FROM comunicados c
       WHERE c.colegio_id = ? AND c.estado = 'ACTIVO' AND c.show_in_home = 1
       ORDER BY c.fecha_hora DESC
       LIMIT 1`,
      [colegio_id]
    );

    // Construir URL del archivo del comunicado si existe
    let comunicadoHomeData = null;
    if (comunicadoHome.length > 0) {
      const com = comunicadoHome[0];
      let archivoUrl = null;
      
      if (com.archivo && com.archivo.trim() !== '') {
        const nombreArchivo = com.archivo.trim();
        const esSistemaNuevo = nombreArchivo.startsWith('/uploads/comunicados/') || 
                               nombreArchivo.startsWith('uploads/comunicados/');
        const isProduction = process.env.NODE_ENV === 'production';
        const dominioBase = 'https://nuevo.vanguardschools.edu.pe';
        
        if (esSistemaNuevo) {
          archivoUrl = isProduction
            ? `https://nuevo.vanguardschools.edu.pe${nombreArchivo.startsWith('/') ? nombreArchivo : '/' + nombreArchivo}`
            : `http://localhost:5000${nombreArchivo.startsWith('/') ? nombreArchivo : '/' + nombreArchivo}`;
        } else {
          if (nombreArchivo.startsWith('/Static/')) {
            archivoUrl = `${dominioBase}${nombreArchivo}`;
          } else if (nombreArchivo.startsWith('Static/')) {
            archivoUrl = `${dominioBase}/${nombreArchivo}`;
          } else {
            archivoUrl = `${dominioBase}/Static/Archivos/${nombreArchivo}`;
          }
        }
      }
      
      comunicadoHomeData = {
        ...com,
        archivo_url: archivoUrl
      };
    }

    // Construir URL de foto
    let fotoUrl = null;
    if (alumnoData.foto && alumnoData.foto !== '') {
      const phpSystemUrl = process.env.PHP_SYSTEM_URL || 'https://nuevo.vanguardschools.edu.pe';
      const isProduction = process.env.NODE_ENV === 'production';
      if (isProduction) {
        fotoUrl = `${phpSystemUrl}/Static/Image/Fotos/${alumnoData.foto}`;
      } else {
        fotoUrl = `http://localhost:5000/Static/Image/Fotos/${alumnoData.foto}`;
      }
    }

    const responseData = {
      alumno: {
        id: alumnoData.id,
        nombres: alumnoData.nombres || 'Alumno',
        apellido_paterno: alumnoData.apellido_paterno || '',
        apellido_materno: alumnoData.apellido_materno || '',
        foto: fotoUrl
      },
      matricula: matriculaData ? {
        id: matriculaData.id,
        grado: matriculaData.grado,
        seccion: matriculaData.seccion,
        nivel_nombre: matriculaData.nivel_nombre,
        turno_nombre: matriculaData.turno_nombre,
        grupo_id: matriculaData.grupo_id
      } : null,
      estadisticas: {
        cursosAsignados: cursosAsignados[0]?.total || 0,
        totalDocentes: totalDocentes[0]?.total || 0,
        totalMatriculas: totalMatriculas[0]?.total || 0,
        totalTardanzas: totalTardanzas[0]?.total || 0,
        totalFaltas: totalFaltas[0]?.total || 0,
        mensajesNoLeidos: mensajesNoLeidos[0]?.total || 0
      },
      asignaturas: asignaturas || [],
      proximosExamenes: proximosExamenes || [],
      proximasTareas: proximasTareas || [],
      proximasActividades: actividades || [],
      comunicadoHome: comunicadoHomeData
    };
    
    console.log('✅ [ALUMNO DASHBOARD] Datos cargados exitosamente:', {
      estadisticas: responseData.estadisticas,
      asignaturas: responseData.asignaturas.length,
      examenes: responseData.proximosExamenes.length,
      tareas: responseData.proximasTareas.length,
      actividades: responseData.proximasActividades.length
    });
    
    res.json(responseData);
  } catch (error) {
    console.error('❌ [ALUMNO DASHBOARD] Error general:', error);
    console.error('❌ [ALUMNO DASHBOARD] Stack:', error.stack);
    res.status(500).json({ 
      error: 'Error al obtener datos del dashboard',
      details: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
});

/**
 * GET /api/alumno/cursos
 * Obtener cursos (asignaturas) del alumno
 */
router.get('/cursos', async (req, res) => {
  try {
    const { usuario_id, colegio_id, anio_activo } = req.user;
    
    // Obtener datos del alumno
    const alumno = await query(
      `SELECT a.*, u.tipo as tipo_usuario
       FROM alumnos a
       INNER JOIN usuarios u ON u.alumno_id = a.id
       WHERE u.id = ? AND u.colegio_id = ? AND u.estado = 'ACTIVO'`,
      [usuario_id, colegio_id]
    );

    if (alumno.length === 0) {
      return res.status(404).json({ error: 'Alumno no encontrado' });
    }

    const alumnoData = alumno[0];

    // Obtener matrícula actual del alumno
    const matricula = await query(
      `SELECT m.*, m.grupo_id
       FROM matriculas m
       INNER JOIN grupos g ON g.id = m.grupo_id
       WHERE m.alumno_id = ? AND m.colegio_id = ? AND g.anio = ? 
       AND (m.estado = 0 OR m.estado = 4)
       LIMIT 1`,
      [alumnoData.id, colegio_id, anio_activo]
    );

    const matriculaData = matricula.length > 0 ? matricula[0] : null;
    const grupoId = matriculaData ? matriculaData.grupo_id : null;

    if (!grupoId) {
      return res.json({ cursos: [] });
    }

    // Obtener asignaturas (cursos) del alumno
    // IMPORTANTE: Igual que el sistema anterior - obtener asignaturas del grupo sin filtrar por año
    // El año ya está implícito en el grupo (grupoId ya corresponde al año activo)
    const cursos = await query(
      `SELECT a.id as asignatura_id,
              c.id as curso_id,
              c.nombre as curso_nombre,
              c.imagen as curso_imagen,
              CONCAT(p.nombres, ' ', p.apellidos) as docente_nombre,
              p.id as docente_id,
              p.foto as docente_foto,
              u.id as docente_usuario_id,
              a.aula_virtual as link_aula_virtual
       FROM asignaturas a
       INNER JOIN cursos c ON c.id = a.curso_id
       INNER JOIN personal p ON p.id = a.personal_id
       INNER JOIN usuarios u ON u.personal_id = p.id AND u.estado = 'ACTIVO'
       WHERE a.grupo_id = ? AND a.colegio_id = ?
       ORDER BY c.orden ASC, c.nombre ASC`,
      [grupoId, colegio_id]
    );

      // Construir URLs de imágenes
      const cursosConImagenes = cursos.map(curso => {
        let cursoImagenUrl = null;
        if (curso.curso_imagen && curso.curso_imagen !== '') {
          const phpSystemUrl = process.env.PHP_SYSTEM_URL || 'https://nuevo.vanguardschools.edu.pe';
          const isProduction = process.env.NODE_ENV === 'production';
          if (isProduction) {
            cursoImagenUrl = `${phpSystemUrl}/Static/Image/Cursos/${curso.curso_imagen}`;
          } else {
            cursoImagenUrl = `http://localhost:5000/Static/Image/Cursos/${curso.curso_imagen}`;
          }
        }
        
        // Construir URL de foto del docente
        let docenteFotoUrl = null;
        if (curso.docente_foto && curso.docente_foto !== '') {
          const phpSystemUrl = process.env.PHP_SYSTEM_URL || 'https://nuevo.vanguardschools.edu.pe';
          const isProduction = process.env.NODE_ENV === 'production';
          if (isProduction) {
            docenteFotoUrl = `${phpSystemUrl}/Static/Image/Fotos/${curso.docente_foto}`;
          } else {
            docenteFotoUrl = `http://localhost:5000/Static/Image/Fotos/${curso.docente_foto}`;
          }
        }
        
        return {
          ...curso,
          curso_imagen_url: cursoImagenUrl,
          docente_foto_url: docenteFotoUrl,
          docente_usuario_id: curso.docente_usuario_id // Asegurar que se incluya el usuario_id
        };
      });

    res.json({ cursos: cursosConImagenes });
  } catch (error) {
    console.error('Error obteniendo cursos del alumno:', error);
    res.status(500).json({ error: 'Error al obtener cursos' });
  }
});

/**
 * GET /api/alumno/notificaciones
 * Obtener notificaciones del alumno
 */
router.get('/notificaciones', async (req, res) => {
  try {
    const { usuario_id, colegio_id } = req.user;

    // Obtener notificaciones para el alumno (igual que docente)
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
 * GET /api/alumno/actividades
 * Obtener actividades del colegio (solo lectura para alumno)
 */
router.get('/actividades', async (req, res) => {
  try {
    const { colegio_id } = req.user;
    const { anio } = req.query;

    // Si no se especifica año, usar año actual
    const añoFiltro = anio ? parseInt(anio) : new Date().getFullYear();

    // Obtener todas las actividades del año especificado
    const actividades = await query(
      `SELECT a.*
       FROM actividades a
       WHERE a.colegio_id = ?
       AND YEAR(a.fecha_inicio) = ?
       ORDER BY a.fecha_inicio ASC`,
      [colegio_id, añoFiltro]
    );

    res.json({
      actividades: actividades || [],
      anio: añoFiltro
    });
  } catch (error) {
    console.error('Error obteniendo actividades:', error);
    res.status(500).json({ error: 'Error al obtener actividades' });
  }
});

/**
 * GET /api/alumno/publicaciones
 * Obtener publicaciones (solo las de TODOS y las de su grupo)
 */
router.get('/publicaciones', async (req, res) => {
  try {
    const { usuario_id, colegio_id, anio_activo } = req.user;

    // Obtener grupo del alumno
    const alumno = await query(
      `SELECT a.id FROM alumnos a
       INNER JOIN usuarios u ON u.alumno_id = a.id
       WHERE u.id = ? AND u.colegio_id = ?`,
      [usuario_id, colegio_id]
    );

    if (alumno.length === 0) {
      return res.status(404).json({ error: 'Alumno no encontrado' });
    }

    const alumnoId = alumno[0].id;

    // Obtener matrícula y grupo del alumno
    const matricula = await query(
      `SELECT m.grupo_id
       FROM matriculas m
       INNER JOIN grupos g ON g.id = m.grupo_id
       WHERE m.alumno_id = ? AND m.colegio_id = ? AND g.anio = ?
       AND (m.estado = 0 OR m.estado = 4)
       LIMIT 1`,
      [alumnoId, colegio_id, anio_activo]
    );

    const grupoId = matricula.length > 0 ? matricula[0].grupo_id : null;

    // Obtener publicaciones: solo las de TODOS y las de su grupo
    // El campo en la BD es 'privacidad': '-1' = TODOS, '-2' = Personal Administrativo, o IDs de grupos separados por comas
    let publicaciones = [];
    try {
      if (grupoId) {
        publicaciones = await query(
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
           AND (p.privacidad = '-1' OR p.privacidad = '' OR p.privacidad IS NULL 
                OR (p.privacidad LIKE ? OR p.privacidad LIKE ? OR p.privacidad = ?))
           ORDER BY p.fecha_hora DESC
           LIMIT 50`,
          [colegio_id, `%,${grupoId},%`, `${grupoId},%`, String(grupoId)]
        );
      } else {
        // Si no tiene grupo, solo mostrar publicaciones de TODOS
        publicaciones = await query(
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
           AND (p.privacidad = '-1' OR p.privacidad = '' OR p.privacidad IS NULL)
           ORDER BY p.fecha_hora DESC
           LIMIT 50`,
          [colegio_id]
        );
      }
    } catch (error) {
      console.error('Error obteniendo publicaciones:', error);
      // Continuar con array vacío en caso de error
      publicaciones = [];
    }

    // Obtener todos los grupos del colegio para mapear IDs a nombres
    const gruposMap = {};
    try {
      const grupos = await query(
        `SELECT id, CONCAT(grado, '° ', seccion) as nombre
         FROM grupos
         WHERE colegio_id = ? AND anio = ?`,
        [colegio_id, anio_activo]
      );
      grupos.forEach(g => {
        gruposMap[g.id] = g.nombre;
      });
    } catch (error) {
      console.warn('Error obteniendo grupos para mapeo:', error);
    }

    // Procesar publicaciones
    const phpSystemUrl = process.env.PHP_SYSTEM_URL || 'https://nuevo.vanguardschools.edu.pe';
    const frontendUrl = process.env.FRONTEND_URL || 'https://sistema.vanguardschools.edu.pe';
    const isProduction = process.env.NODE_ENV === 'production';

    const publicacionesProcesadas = publicaciones.map(pub => {
      // Decodificar imágenes (formato PHP: base64_encode(serialize(array)))
      let images = [];
      if (pub.images && pub.images.trim() !== '') {
        try {
          const decoded = Buffer.from(pub.images, 'base64').toString('utf-8');
          const deserialized = phpSerialize.unserialize(decoded);
          if (Array.isArray(deserialized)) {
            images = deserialized.map(img => {
              if (img.startsWith('http')) {
                return img;
              } else if (img.startsWith('/Static/')) {
                return `${phpSystemUrl}${img}`;
              } else {
                return `${phpSystemUrl}/Static/Image/Publicaciones/${img}`;
              }
            });
          }
        } catch (error) {
          console.warn('Error decodificando imágenes de publicación:', error);
        }
      }

      // Decodificar archivos (igual que docente: usar regex en lugar de unserialize directo)
      let archivos = [];
      if (pub.archivos && pub.archivos.trim() !== '') {
        try {
          let decoded = pub.archivos;
          // Intentar decodificar base64 si es necesario
          try {
            const base64Decoded = Buffer.from(pub.archivos, 'base64').toString('utf-8');
            if (base64Decoded.startsWith('a:')) {
              decoded = base64Decoded;
            }
          } catch (e) {
            // Si no es base64, usar directamente
            decoded = pub.archivos;
          }
          
          if (decoded.startsWith('a:')) {
            // Usar regex para extraer nombres de archivos (mismo método que docente)
            const regex = /s:\d+:"([^"]+)"/g;
            let match;
            while ((match = regex.exec(decoded)) !== null) {
              archivos.push(match[1]);
            }
          }
        } catch (error) {
          console.warn('Error decodificando archivos de publicación:', error);
        }
      }

      // Construir URL completa de la foto del autor (igual que docente)
      let autorFotoUrl = null;
      if (pub.autor_foto && pub.autor_foto !== '') {
        // Si ya es una URL completa, usarla directamente
        if (pub.autor_foto.startsWith('http')) {
          autorFotoUrl = pub.autor_foto;
        } else if (pub.autor_foto.startsWith('/uploads/')) {
          // Si ya tiene la ruta /uploads/, construir URL completa
          if (isProduction) {
            autorFotoUrl = `${frontendUrl}${pub.autor_foto}`;
          } else {
            autorFotoUrl = `http://localhost:5000${pub.autor_foto}`;
          }
        } else {
          // Es solo el nombre del archivo, determinar si es personal o alumno según el tipo de usuario
          const esPersonal = pub.autor_tipo === 'DOCENTE' || pub.autor_tipo === 'DIRECTOR' || pub.autor_tipo === 'ADMINISTRADOR';
          
          if (isProduction) {
            // Usar el dominio del sistema PHP (nuevo.vanguardschools.edu.pe)
            autorFotoUrl = `${phpSystemUrl}/Static/Image/Fotos/${pub.autor_foto}`;
          } else {
            // En desarrollo, usar la ruta de uploads según el tipo
            const uploadPath = esPersonal ? 'uploads/personal' : 'uploads/alumnos';
            autorFotoUrl = `http://localhost:5000/${uploadPath}/${pub.autor_foto}`;
          }
        }
      }

      // Procesar grupos (si privacidad contiene IDs de grupos)
      let gruposNombres = [];
      if (pub.privacidad && pub.privacidad !== '' && pub.privacidad !== '-1' && pub.privacidad !== '-2') {
        const grupoIds = pub.privacidad.split(',').map(id => parseInt(id.trim())).filter(id => !isNaN(id) && id > 0);
        gruposNombres = grupoIds.map(id => gruposMap[id] || `Grupo ${id}`).filter(Boolean);
      }
      
      // Determinar compartir_con basado en privacidad
      let compartirCon = 'TODOS';
      if (pub.privacidad === '-2') {
        compartirCon = 'PERSONAL';
      } else if (pub.privacidad && pub.privacidad !== '' && pub.privacidad !== '-1' && pub.privacidad !== '-2') {
        compartirCon = 'GRUPO';
      }

      // Devolver en el mismo formato que docente (para compatibilidad con PublicacionesWidget)
      return {
        id: pub.id,
        contenido: pub.contenido || '',
        images: images,
        archivos: archivos,
        compartir_con: compartirCon,
        grupos: gruposNombres,
        fecha_hora: pub.fecha_hora,
        para_texto: compartirCon === 'TODOS' ? 'Todos' : (gruposNombres.length > 0 ? gruposNombres.join(', ') : 'Grupos específicos'),
        autor_id: pub.autor_id,
        autor_usuario: pub.autor_usuario,
        autor_nombre_completo: pub.autor_nombre_completo || pub.autor_usuario || 'Usuario',
        autor_foto_url: autorFotoUrl, // Campo que usa el frontend
        autor_tipo: pub.autor_tipo,
        // Mantener también el objeto autor para compatibilidad
        autor: {
          id: pub.autor_id,
          usuario: pub.autor_usuario,
          nombre_completo: pub.autor_nombre_completo || pub.autor_usuario || 'Usuario',
          foto: autorFotoUrl,
          tipo: pub.autor_tipo
        }
      };
    });

    res.json({ publicaciones: publicacionesProcesadas || [] });
  } catch (error) {
    console.error('Error obteniendo publicaciones:', error);
    res.status(500).json({ error: 'Error al obtener publicaciones' });
  }
});

/**
 * GET /api/alumno/perfil
 * Obtener perfil del alumno
 */
router.get('/perfil', async (req, res) => {
  try {
    const { usuario_id, colegio_id } = req.user;

    const alumno = await query(
      `SELECT a.*, u.tipo as tipo_usuario, u.usuario as dni
       FROM alumnos a
       INNER JOIN usuarios u ON u.alumno_id = a.id
       WHERE u.id = ? AND u.colegio_id = ? AND u.estado = 'ACTIVO'`,
      [usuario_id, colegio_id]
    );

    if (alumno.length === 0) {
      return res.status(404).json({ error: 'Alumno no encontrado' });
    }

    const alumnoData = alumno[0];

    // Obtener apoderados (padre y madre)
    const apoderados = await query(
      `SELECT ap.*, 
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
      [alumnoData.id, colegio_id]
    );

    // Separar padre y madre
    const padre = apoderados.find(a => a.parentesco === 0) || null;
    const madre = apoderados.find(a => a.parentesco === 1) || null;

    // Construir URL de foto
    let fotoUrl = null;
    if (alumnoData.foto && alumnoData.foto !== '') {
      const phpSystemUrl = process.env.PHP_SYSTEM_URL || 'https://nuevo.vanguardschools.edu.pe';
      const isProduction = process.env.NODE_ENV === 'production';
      if (isProduction) {
        fotoUrl = `${phpSystemUrl}/Static/Image/Fotos/${alumnoData.foto}`;
      } else {
        fotoUrl = `http://localhost:5000/Static/Image/Fotos/${alumnoData.foto}`;
      }
    }

    res.json({
      id: alumnoData.id,
      nombres: alumnoData.nombres,
      apellido_paterno: alumnoData.apellido_paterno,
      apellido_materno: alumnoData.apellido_materno,
      dni: alumnoData.dni,
      email: alumnoData.email || null,
      sexo: alumnoData.sexo !== undefined ? alumnoData.sexo : null,
      foto: fotoUrl,
      fecha_nacimiento: alumnoData.fecha_nacimiento,
      padre: padre ? {
        nombres: padre.nombres || '',
        apellido_paterno: padre.apellido_paterno || '',
        apellido_materno: padre.apellido_materno || '',
        telefono_fijo: padre.telefono_fijo || '',
        telefono_celular: padre.telefono_celular || '',
        email: padre.email || ''
      } : null,
      madre: madre ? {
        nombres: madre.nombres || '',
        apellido_paterno: madre.apellido_paterno || '',
        apellido_materno: madre.apellido_materno || '',
        telefono_fijo: madre.telefono_fijo || '',
        telefono_celular: madre.telefono_celular || '',
        email: madre.email || ''
      } : null
    });
  } catch (error) {
    console.error('Error obteniendo perfil:', error);
    res.status(500).json({ error: 'Error al obtener perfil' });
  }
});

/**
 * PUT /api/alumno/perfil
 * Actualizar perfil del alumno (incluyendo foto)
 */
const alumnoStorage = multer.diskStorage({
  destination: (req, file, cb) => {
    const uploadPath = '/home/vanguard/nuevo.vanguardschools.edu.pe/Static/Image/Fotos';
    if (!fs.existsSync(uploadPath)) {
      fs.mkdirSync(uploadPath, { recursive: true });
    }
    cb(null, uploadPath);
  },
  filename: (req, file, cb) => {
    const uniqueSuffix = Date.now() + '-' + Math.round(Math.random() * 1E9);
    cb(null, `alumno-${uniqueSuffix}${path.extname(file.originalname)}`);
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

const uploadAlumno = multer({
  storage: alumnoStorage,
  limits: { fileSize: 5 * 1024 * 1024 }, // 5MB
  fileFilter: fileFilter
});

router.put('/perfil', uploadAlumno.single('foto'), async (req, res) => {
  try {
    const { usuario_id, colegio_id } = req.user;
    const { nombres, apellido_paterno, apellido_materno, email, sexo, fecha_nacimiento } = req.body;

    // Obtener alumno actual
    const alumno = await query(
      `SELECT a.* FROM alumnos a
       INNER JOIN usuarios u ON u.alumno_id = a.id
       WHERE u.id = ? AND u.colegio_id = ?`,
      [usuario_id, colegio_id]
    );

    if (alumno.length === 0) {
      return res.status(404).json({ error: 'Alumno no encontrado' });
    }

    const alumnoData = alumno[0];
    
    let fotoPath = alumnoData.foto;

    // Si se subió una nueva foto
    if (req.file) {
      // Eliminar foto anterior si existe
      if (alumnoData.foto && alumnoData.foto !== '') {
        const oldFotoPath = `/home/vanguard/nuevo.vanguardschools.edu.pe/Static/Image/Fotos/${path.basename(alumnoData.foto)}`;
        if (fs.existsSync(oldFotoPath)) {
          fs.unlinkSync(oldFotoPath);
        }
      }
      
      // Guardar nueva foto - solo el nombre del archivo
      fotoPath = req.file.filename;
    }

    // Actualizar datos (solo campos que existen en la tabla alumnos)
    await execute(
      `UPDATE alumnos SET
        nombres = ?,
        apellido_paterno = ?,
        apellido_materno = ?,
        email = ?,
        sexo = ?,
        foto = ?,
        fecha_nacimiento = ?
      WHERE id = ?`,
      [
        nombres || alumnoData.nombres,
        apellido_paterno || alumnoData.apellido_paterno,
        apellido_materno || alumnoData.apellido_materno,
        email || alumnoData.email || null,
        sexo !== undefined ? sexo : (alumnoData.sexo !== undefined ? alumnoData.sexo : null),
        fotoPath,
        fecha_nacimiento || alumnoData.fecha_nacimiento || null,
        alumnoData.id
      ]
    );

    // Obtener perfil actualizado
    const alumnoActualizado = await query(
      `SELECT a.*, u.tipo as tipo_usuario, u.usuario as dni
       FROM alumnos a
       INNER JOIN usuarios u ON u.alumno_id = a.id
       WHERE u.id = ? AND u.colegio_id = ? AND u.estado = 'ACTIVO'`,
      [usuario_id, colegio_id]
    );

    const alumnoDataActualizado = alumnoActualizado[0];

    // Construir URL de foto
    let fotoUrl = null;
    if (alumnoDataActualizado.foto && alumnoDataActualizado.foto !== '') {
      const phpSystemUrl = process.env.PHP_SYSTEM_URL || 'https://nuevo.vanguardschools.edu.pe';
      const isProduction = process.env.NODE_ENV === 'production';
      if (isProduction) {
        fotoUrl = `${phpSystemUrl}/Static/Image/Fotos/${alumnoDataActualizado.foto}`;
      } else {
        fotoUrl = `http://localhost:5000/Static/Image/Fotos/${alumnoDataActualizado.foto}`;
      }
    }

    res.json({
      id: alumnoDataActualizado.id,
      nombres: alumnoDataActualizado.nombres,
      apellido_paterno: alumnoDataActualizado.apellido_paterno,
      apellido_materno: alumnoDataActualizado.apellido_materno,
      dni: alumnoDataActualizado.dni,
      email: alumnoDataActualizado.email || null,
      sexo: alumnoDataActualizado.sexo !== undefined ? alumnoDataActualizado.sexo : null,
      foto: fotoUrl,
      fecha_nacimiento: alumnoDataActualizado.fecha_nacimiento
    });
  } catch (error) {
    console.error('Error actualizando perfil:', error);
    res.status(500).json({ error: 'Error al actualizar perfil' });
  }
});

/**
 * PUT /api/alumno/perfil/password
 * Cambiar contraseña del alumno
 */
router.put('/perfil/password', async (req, res) => {
  try {
    const { usuario_id, colegio_id } = req.user;
    const { password_actual, password_nueva } = req.body;

    if (!password_actual || !password_nueva) {
      return res.status(400).json({ error: 'Contraseña actual y nueva son requeridas' });
    }

    // Obtener usuario
    const usuarios = await query(
      `SELECT u.* FROM usuarios u
       WHERE u.id = ? AND u.colegio_id = ? AND u.estado = 'ACTIVO'`,
      [usuario_id, colegio_id]
    );

    if (usuarios.length === 0) {
      return res.status(404).json({ error: 'Usuario no encontrado' });
    }

    const usuario = usuarios[0];

    // Validar contraseña actual
    const crypto = require('crypto');
    const passwordHash = crypto.createHash('sha1').update(password_actual).digest('hex');
    
    if (usuario.password !== passwordHash) {
      return res.status(401).json({ error: 'Contraseña actual incorrecta' });
    }

    // Actualizar contraseña
    const newPasswordHash = crypto.createHash('sha1').update(password_nueva).digest('hex');
    await execute(
      `UPDATE usuarios SET password = ? WHERE id = ?`,
      [newPasswordHash, usuario_id]
    );

    res.json({ success: true, message: 'Contraseña actualizada correctamente' });
  } catch (error) {
    console.error('Error cambiando contraseña:', error);
    res.status(500).json({ error: 'Error al cambiar contraseña' });
  }
});

/**
 * GET /api/alumno/comunicados
 * Obtener comunicados (solo lectura, generados por admin)
 */
router.get('/comunicados', async (req, res) => {
  try {
    console.log('📥 [ALUMNO COMUNICADOS] GET /comunicados - Iniciando, año activo:', req.user?.anio_activo);
    const { colegio_id, anio_activo } = req.user;
    const { page = 1, limit = 12, search = '' } = req.query;
    const offset = (parseInt(page) - 1) * parseInt(limit);

    // Construir query base - Filtrar por año activo
    let querySql = `
      SELECT c.*
       FROM comunicados c
       WHERE c.colegio_id = ? AND c.estado = 'ACTIVO' AND YEAR(c.fecha_hora) = ?
    `;
    const params = [colegio_id, anio_activo];

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

    // Construir URLs de archivos (igual que en docente)
    const fs = require('fs');
    const path = require('path');
    const staticArchivosPath = '/home/vanguard/nuevo.vanguardschools.edu.pe/Static/Archivos';
    
    const comunicadosConUrls = (comunicados || []).map(com => {
      let archivoUrl = null;
      let archivoExiste = false;
      
      if (com.archivo && com.archivo.trim() !== '') {
        let nombreArchivo = com.archivo.trim();
        const isProduction = process.env.NODE_ENV === 'production';
        
        const esSistemaNuevo = nombreArchivo.startsWith('/uploads/comunicados/') || 
                               nombreArchivo.startsWith('uploads/comunicados/');
        
        if (esSistemaNuevo) {
          if (nombreArchivo.startsWith('/uploads/')) {
            archivoUrl = isProduction
              ? `https://nuevo.vanguardschools.edu.pe${nombreArchivo}`
              : `http://localhost:5000${nombreArchivo}`;
          } else {
            archivoUrl = isProduction
              ? `https://nuevo.vanguardschools.edu.pe/${nombreArchivo}`
              : `http://localhost:5000/${nombreArchivo}`;
          }
          // Para sistema nuevo, verificar en backend/uploads/comunicados/
          const uploadsPath = path.join(__dirname, '../../uploads/comunicados', nombreArchivo.replace(/^\/?uploads\/comunicados\//, ''));
          archivoExiste = fs.existsSync(uploadsPath);
        } else {
          const dominioBase = 'https://nuevo.vanguardschools.edu.pe';
          
          // Extraer solo el nombre del archivo para verificar existencia
          let nombreArchivoLimpio = nombreArchivo;
          if (nombreArchivo.startsWith('http://') || nombreArchivo.startsWith('https://')) {
            nombreArchivoLimpio = nombreArchivo.split('/').pop();
          } else if (nombreArchivo.startsWith('/Static/Archivos/')) {
            nombreArchivoLimpio = nombreArchivo.replace('/Static/Archivos/', '');
          } else if (nombreArchivo.startsWith('Static/Archivos/')) {
            nombreArchivoLimpio = nombreArchivo.replace('Static/Archivos/', '');
          } else if (nombreArchivo.startsWith('/Static/')) {
            nombreArchivoLimpio = nombreArchivo.replace('/Static/', '');
          } else if (nombreArchivo.startsWith('Static/')) {
            nombreArchivoLimpio = nombreArchivo.replace('Static/', '');
          }
          nombreArchivoLimpio = nombreArchivoLimpio.replace(/^\/+/, '');
          
          if (nombreArchivo.startsWith('http://') || nombreArchivo.startsWith('https://')) {
            archivoUrl = nombreArchivo
              .replace(/https?:\/\/(www\.)?vanguardschools\.edu\.pe/gi, dominioBase)
              .replace(/vanguardschools\.comstatic/gi, `${dominioBase}/Static`)
              .replace(/vanguardschools\.com\/static/gi, `${dominioBase}/Static`)
              .replace(/vanguardschools\.com\/Static/gi, `${dominioBase}/Static`)
              .replace(/([^:]\/)\/+/g, '$1');
          } else if (nombreArchivo.startsWith('/Static/')) {
            archivoUrl = `${dominioBase}${nombreArchivo}`;
          } else if (nombreArchivo.startsWith('Static/')) {
            archivoUrl = `${dominioBase}/${nombreArchivo}`;
          } else {
            nombreArchivo = nombreArchivo.replace(/^\/+/, '');
            archivoUrl = `${dominioBase}/Static/Archivos/${nombreArchivo}`;
          }
          
          // Verificar si el archivo existe físicamente (solo para logging, no para bloquear)
          const archivoPath = path.join(staticArchivosPath, nombreArchivoLimpio);
          archivoExiste = fs.existsSync(archivoPath);
        }
      }

      return {
        ...com,
        archivo_url: archivoUrl, // Siempre devolver la URL (el sistema PHP puede manejarla aunque no exista físicamente)
        archivo_existe: archivoExiste // Información adicional para debugging
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
 * GET /api/alumno/mensajes/recibidos
 * Obtener mensajes recibidos con información completa del remitente
 */
router.get('/mensajes/recibidos', async (req, res) => {
  try {
    console.log('📥 [ALUMNO MENSAJES] GET /mensajes/recibidos - Iniciando');
    const { usuario_id, colegio_id } = req.user;
    const { page = 1, limit = 50 } = req.query;
    const offset = (parseInt(page) - 1) * parseInt(limit);

    const anioFiltro = req.query.anio ? parseInt(req.query.anio) : null;
    
    let querySQL = `SELECT m.*, 
              CASE
                WHEN p.id IS NOT NULL THEN CONCAT(p.nombres, ' ', p.apellidos)
                WHEN a.id IS NOT NULL THEN CONCAT(a.nombres, ' ', a.apellido_paterno, ' ', a.apellido_materno)
                WHEN ap.id IS NOT NULL THEN CONCAT(ap.nombres, ' ', ap.apellido_paterno, ' ', ap.apellido_materno)
                ELSE 'Usuario desconocido'
              END as remitente_nombre_completo,
              u1.tipo as remitente_tipo,
              u1.usuario as remitente_usuario
       FROM mensajes m
       INNER JOIN usuarios u1 ON u1.id = m.remitente_id
       LEFT JOIN personal p ON p.id = u1.personal_id
       LEFT JOIN alumnos a ON a.id = u1.alumno_id
       LEFT JOIN apoderados ap ON ap.id = u1.apoderado_id
       WHERE m.destinatario_id = ? 
         AND m.tipo = 'RECIBIDO'
         AND m.borrado = 'NO'`;
    
    const queryParams = [usuario_id];
    
    if (anioFiltro) {
      querySQL += ` AND YEAR(m.fecha_hora) = ?`;
      queryParams.push(anioFiltro);
    }
    
    querySQL += ` ORDER BY m.fecha_hora DESC LIMIT ? OFFSET ?`;
    queryParams.push(parseInt(limit), offset);
    
    const mensajes = await query(querySQL, queryParams);

    // Obtener archivos adjuntos para cada mensaje
    const phpSystemUrl = process.env.PHP_SYSTEM_URL || 'https://nuevo.vanguardschools.edu.pe';
    const isProduction = process.env.NODE_ENV === 'production';
    
    for (const mensaje of mensajes) {
      const archivos = await query(
        `SELECT id, nombre_archivo, archivo
         FROM mensajes_archivos
         WHERE mensaje_id = ?`,
        [mensaje.id]
      );
      
      mensaje.archivos = (archivos || []).map(archivo => {
        let archivoUrl;
        if (isProduction) {
          archivoUrl = `${phpSystemUrl}/Static/Archivos/${archivo.archivo}`;
        } else {
          archivoUrl = `http://localhost:5000/Static/Archivos/${archivo.archivo}`;
        }
        return {
          ...archivo,
          archivo_url: archivoUrl
        };
      });
    }

    // Contar total
    let countSQL = `SELECT COUNT(*) as count
       FROM mensajes m
       WHERE m.destinatario_id = ? 
         AND m.tipo = 'RECIBIDO'
         AND m.borrado = 'NO'`;
    const countParams = [usuario_id];
    
    if (anioFiltro) {
      countSQL += ` AND YEAR(m.fecha_hora) = ?`;
      countParams.push(anioFiltro);
    }
    
    const total = await query(countSQL, countParams);

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
 * GET /api/alumno/mensajes/enviados
 * Obtener mensajes enviados con información completa del destinatario
 */
router.get('/mensajes/enviados', async (req, res) => {
  try {
    const { usuario_id, colegio_id } = req.user;
    const { page = 1, limit = 50 } = req.query;
    const offset = (parseInt(page) - 1) * parseInt(limit);

    const anioFiltro = req.query.anio ? parseInt(req.query.anio) : null;
    
    let querySQL = `SELECT m.*,
              CASE
                WHEN p.id IS NOT NULL THEN CONCAT(p.nombres, ' ', p.apellidos)
                WHEN a.id IS NOT NULL THEN CONCAT(a.nombres, ' ', a.apellido_paterno, ' ', a.apellido_materno)
                WHEN ap.id IS NOT NULL THEN CONCAT(ap.nombres, ' ', ap.apellido_paterno, ' ', ap.apellido_materno)
                ELSE 'Usuario desconocido'
              END as destinatario_nombre_completo,
              u2.tipo as destinatario_tipo,
              u2.usuario as destinatario_usuario
       FROM mensajes m
       INNER JOIN usuarios u2 ON u2.id = m.destinatario_id
       LEFT JOIN personal p ON p.id = u2.personal_id
       LEFT JOIN alumnos a ON a.id = u2.alumno_id
       LEFT JOIN apoderados ap ON ap.id = u2.apoderado_id
       WHERE m.remitente_id = ? 
         AND m.tipo = 'ENVIADO'
         AND m.borrado = 'NO'`;
    
    const queryParams = [usuario_id];
    
    if (anioFiltro) {
      querySQL += ` AND YEAR(m.fecha_hora) = ?`;
      queryParams.push(anioFiltro);
    }
    
    querySQL += ` ORDER BY m.fecha_hora DESC LIMIT ? OFFSET ?`;
    queryParams.push(parseInt(limit), offset);
    
    const mensajes = await query(querySQL, queryParams);

    // Obtener archivos adjuntos para cada mensaje
    const phpSystemUrl = process.env.PHP_SYSTEM_URL || 'https://nuevo.vanguardschools.edu.pe';
    const isProduction = process.env.NODE_ENV === 'production';
    
    for (const mensaje of mensajes) {
      const archivos = await query(
        `SELECT id, nombre_archivo, archivo
         FROM mensajes_archivos
         WHERE mensaje_id = ?`,
        [mensaje.id]
      );
      
      mensaje.archivos = (archivos || []).map(archivo => {
        let archivoUrl;
        if (isProduction) {
          archivoUrl = `${phpSystemUrl}/Static/Archivos/${archivo.archivo}`;
        } else {
          archivoUrl = `http://localhost:5000/Static/Archivos/${archivo.archivo}`;
        }
        return {
          ...archivo,
          archivo_url: archivoUrl
        };
      });
    }

    // Contar total
    let countSQL = `SELECT COUNT(*) as count
       FROM mensajes m
       WHERE m.remitente_id = ? 
         AND m.tipo = 'ENVIADO'
         AND m.borrado = 'NO'`;
    const countParams = [usuario_id];
    
    if (anioFiltro) {
      countSQL += ` AND YEAR(m.fecha_hora) = ?`;
      countParams.push(anioFiltro);
    }
    
    const total = await query(countSQL, countParams);

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
 * GET /api/alumno/mensajes/buscar-destinatarios
 * Buscar destinatarios (solo docentes y personal administrativo, NO otros alumnos)
 */
router.get('/mensajes/buscar-destinatarios', async (req, res) => {
  try {
    const { colegio_id } = req.user;
    const { q = '' } = req.query;

    if (!q || q.trim().length < 2) {
      return res.json({ resultados: [] });
    }

    const searchTerm = `%${q.trim()}%`;
    const resultados = [];

    // Buscar personal (docentes, directores, administradores)
    const personal = await query(
      `SELECT DISTINCT u.id as usuario_id,
              CONCAT(p.nombres, ' ', p.apellidos) as nombre_completo,
              u.tipo as tipo,
              p.cargo as info_adicional
       FROM personal p
       INNER JOIN usuarios u ON u.personal_id = p.id AND u.estado = 'ACTIVO'
       WHERE p.colegio_id = ?
         AND (p.nombres LIKE ? OR p.apellidos LIKE ? OR u.usuario LIKE ?)
       ORDER BY p.nombres, p.apellidos
       LIMIT 20`,
      [colegio_id, searchTerm, searchTerm, searchTerm]
    );

    personal.forEach(p => {
      resultados.push({
        usuario_id: p.usuario_id,
        nombre_completo: p.nombre_completo,
        tipo: p.tipo,
        info_adicional: p.info_adicional || p.tipo
      });
    });

    res.json({ resultados: resultados.slice(0, 20) });
  } catch (error) {
    console.error('Error buscando destinatarios:', error);
    res.status(500).json({ error: 'Error al buscar destinatarios' });
  }
});

/**
 * POST /api/alumno/mensajes/subir-imagen
 * Subir imagen desde el editor de texto enriquecido
 */
router.post('/mensajes/subir-imagen', uploadMensajes.single('imagen'), async (req, res) => {
  try {
    if (!req.file) {
      return res.status(400).json({ error: 'No se proporcionó ninguna imagen' });
    }

    const phpSystemUrl = process.env.PHP_SYSTEM_URL || 'https://nuevo.vanguardschools.edu.pe';
    const isProduction = process.env.NODE_ENV === 'production';
    
    let imagenUrl;
    if (isProduction) {
      imagenUrl = `${phpSystemUrl}/Static/Archivos/${req.file.filename}`;
    } else {
      imagenUrl = `http://localhost:5000/Static/Archivos/${req.file.filename}`;
    }
    
    res.json({ url: imagenUrl });
  } catch (error) {
    console.error('Error subiendo imagen:', error);
    res.status(500).json({ error: 'Error al subir la imagen' });
  }
});

/**
 * POST /api/alumno/mensajes/enviar
 * Enviar mensaje (solo a docentes y personal administrativo, NO a otros alumnos)
 */
router.post('/mensajes/enviar', uploadMensajes.array('archivos', 10), async (req, res) => {
  try {
    const { usuario_id, colegio_id } = req.user;
    const { destinatarios, asunto, mensaje } = req.body;

    const destinatariosArray = typeof destinatarios === 'string' ? JSON.parse(destinatarios) : destinatarios;

    if (!destinatariosArray || destinatariosArray.length === 0) {
      return res.status(400).json({ error: 'Debe seleccionar al menos un destinatario' });
    }

    if (!asunto || !mensaje) {
      return res.status(400).json({ error: 'Asunto y mensaje son requeridos' });
    }

    // Responder inmediatamente
    res.json({
      success: true,
      message: 'Mensaje Enviado',
      procesando: false
    });

    // Procesar en segundo plano
    setImmediate(async () => {
      try {
        const fechaHora = new Date().toISOString().slice(0, 19).replace('T', ' ');
        let mensajesInsertados = 0;
        const mensajesIds = [];

        for (const destinatarioId of destinatariosArray) {
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
          }
        }

        // Guardar archivos adjuntos si existen
        if (req.files && req.files.length > 0) {
          for (const file of req.files) {
            try {
              for (const mensajeId of mensajesIds) {
                await execute(
                  `INSERT INTO mensajes_archivos (mensaje_id, nombre_archivo, archivo)
                   VALUES (?, ?, ?)`,
                  [mensajeId, file.originalname, file.filename]
                );
              }
            } catch (error) {
              console.error(`Error guardando archivo ${file.originalname}:`, error);
            }
          }
        }

        console.log('✅ Mensaje enviado exitosamente');
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
 * PUT /api/alumno/mensajes/:mensajeId/marcar-leido
 * Marcar un mensaje como leído
 */
router.put('/mensajes/:mensajeId/marcar-leido', async (req, res) => {
  try {
    const { mensajeId } = req.params;
    const { usuario_id } = req.user;

    const mensaje = await query(
      `SELECT id, estado FROM mensajes 
       WHERE id = ? AND destinatario_id = ? AND tipo = 'RECIBIDO' AND borrado = 'NO'`,
      [mensajeId, usuario_id]
    );

    if (mensaje.length === 0) {
      return res.status(404).json({ error: 'Mensaje no encontrado' });
    }

    if (mensaje[0].estado === 'LEIDO') {
      return res.json({ success: true, message: 'Mensaje ya estaba marcado como leído' });
    }

    await execute(
      `UPDATE mensajes SET estado = 'LEIDO' WHERE id = ?`,
      [mensajeId]
    );

    res.json({ success: true, message: 'Mensaje marcado como leído' });
  } catch (error) {
    console.error('Error marcando mensaje como leído:', error);
    res.status(500).json({ error: 'Error al marcar mensaje como leído' });
  }
});

/**
 * GET /api/alumno/mensajes/anios-disponibles
 * Obtener lista de años disponibles en mensajes
 */
router.get('/mensajes/anios-disponibles', async (req, res) => {
  try {
    console.log('📥 [ALUMNO MENSAJES] GET /mensajes/anios-disponibles - Iniciando');
    const { usuario_id } = req.user;

    const aniosRecibidos = await query(
      `SELECT DISTINCT YEAR(fecha_hora) as anio
       FROM mensajes
       WHERE destinatario_id = ? 
         AND tipo = 'RECIBIDO'
         AND borrado = 'NO'
       ORDER BY anio DESC`,
      [usuario_id]
    );

    const aniosEnviados = await query(
      `SELECT DISTINCT YEAR(fecha_hora) as anio
       FROM mensajes
       WHERE remitente_id = ? 
         AND tipo = 'ENVIADO'
         AND borrado = 'NO'
       ORDER BY anio DESC`,
      [usuario_id]
    );

    const todosAnios = new Set();
    aniosRecibidos.forEach(r => todosAnios.add(r.anio));
    aniosEnviados.forEach(r => todosAnios.add(r.anio));

    const aniosArray = Array.from(todosAnios).sort((a, b) => b - a);

    res.json({ anios: aniosArray });
  } catch (error) {
    console.error('Error obteniendo años disponibles:', error);
    res.status(500).json({ error: 'Error al obtener años disponibles' });
  }
});

/**
 * DELETE /api/alumno/mensajes
 * Eliminar múltiples mensajes
 */
router.delete('/mensajes', async (req, res) => {
  try {
    const { usuario_id } = req.user;
    const { mensajesIds } = req.body;

    if (!mensajesIds || !Array.isArray(mensajesIds) || mensajesIds.length === 0) {
      return res.status(400).json({ error: 'Debe proporcionar al menos un ID de mensaje' });
    }

    const placeholders = mensajesIds.map(() => '?').join(',');
    const mensajes = await query(
      `SELECT id, remitente_id, destinatario_id, tipo 
       FROM mensajes 
       WHERE id IN (${placeholders}) AND borrado = 'NO'`,
      mensajesIds
    );

    if (mensajes.length === 0) {
      return res.status(404).json({ error: 'No se encontraron mensajes válidos' });
    }

    const mensajesAEliminar = mensajes.filter(m => 
      (m.tipo === 'ENVIADO' && m.remitente_id === usuario_id) ||
      (m.tipo === 'RECIBIDO' && m.destinatario_id === usuario_id)
    );

    if (mensajesAEliminar.length === 0) {
      return res.status(403).json({ error: 'No tienes permiso para eliminar estos mensajes' });
    }

    const idsAEliminar = mensajesAEliminar.map(m => m.id);
    const placeholdersEliminar = idsAEliminar.map(() => '?').join(',');

    await execute(
      `UPDATE mensajes SET borrado = 'SI' WHERE id IN (${placeholdersEliminar})`,
      idsAEliminar
    );

    res.json({ 
      success: true, 
      message: `${idsAEliminar.length} mensaje(s) eliminado(s) correctamente`,
      eliminados: idsAEliminar.length
    });
  } catch (error) {
    console.error('Error eliminando mensajes:', error);
    res.status(500).json({ error: 'Error al eliminar mensajes' });
  }
});

/**
 * GET /api/alumno/aula-virtual/archivos
 * Obtener archivos/temas interactivos de una asignatura (solo lectura para alumnos)
 */
router.get('/aula-virtual/archivos', async (req, res) => {
  try {
    const { usuario_id, colegio_id, anio_activo, alumno_id } = req.user;
    const { asignatura_id, ciclo } = req.query;

    if (!asignatura_id) {
      return res.status(400).json({ error: 'asignatura_id es requerido' });
    }

    // Si no se proporciona ciclo, usar 1 por defecto
    const cicloFiltro = ciclo ? parseInt(ciclo) : 1;

    // Verificar que el alumno tiene acceso a esta asignatura (está matriculado en el grupo)
    const asignatura = await query(
      `SELECT a.*, g.id as grupo_id
       FROM asignaturas a
       INNER JOIN grupos g ON g.id = a.grupo_id
       INNER JOIN matriculas m ON m.grupo_id = g.id
       WHERE a.id = ? AND m.alumno_id = ? AND m.colegio_id = ? AND g.anio = ? 
       AND (m.estado = 0 OR m.estado = 4)
       LIMIT 1`,
      [asignatura_id, alumno_id, colegio_id, anio_activo]
    );

    if (asignatura.length === 0) {
      return res.status(403).json({ error: 'No tienes acceso a esta asignatura' });
    }

    // Obtener archivos de la asignatura filtrados por ciclo
    const archivos = await query(
      `SELECT * FROM asignaturas_archivos
       WHERE asignatura_id = ? AND ciclo = ?
       ORDER BY orden ASC, id ASC`,
      [asignatura_id, cicloFiltro]
    );

    // Construir URLs completas para los archivos
    const phpSystemUrl = process.env.PHP_SYSTEM_URL || 'https://nuevo.vanguardschools.edu.pe';
    const isDevelopment = process.env.NODE_ENV !== 'production';
    const archivosConUrls = archivos.map(archivo => {
      let archivoUrl = null;
      let enlaceUrl = null;

      if (archivo.archivo && archivo.archivo !== '') {
        if (archivo.archivo.startsWith('http')) {
          archivoUrl = archivo.archivo
            .replace(/https?:\/\/(www\.)?vanguardschools\.edu\.pe/gi, phpSystemUrl)
            .replace(/vanguardschools\.comstatic/gi, `${phpSystemUrl}/Static`)
            .replace(/vanguardschools\.com\/static/gi, `${phpSystemUrl}/Static`)
            .replace(/vanguardschools\.com\/Static/gi, `${phpSystemUrl}/Static`);
        } else if (archivo.archivo.startsWith('/Static/')) {
          const nombreArchivo = archivo.archivo.replace(/^\/Static\/Archivos\//, '');
          archivoUrl = isDevelopment
            ? `http://localhost:5000/Static/Archivos/${nombreArchivo}`
            : `${phpSystemUrl}/Static/Archivos/${nombreArchivo}`;
        } else if (archivo.archivo.startsWith('/uploads/')) {
          archivoUrl = isDevelopment
            ? `http://localhost:5000${archivo.archivo}`
            : `${phpSystemUrl}${archivo.archivo}`;
        } else {
          archivoUrl = isDevelopment
            ? `http://localhost:5000/Static/Archivos/${archivo.archivo}`
            : `${phpSystemUrl}/Static/Archivos/${archivo.archivo}`;
        }
      }

      if (archivo.enlace && archivo.enlace !== '') {
        enlaceUrl = archivo.enlace.trim();
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
 * GET /api/alumno/aula-virtual/tareas
 * Obtener tareas de una asignatura (solo lectura para alumnos)
 */
router.get('/aula-virtual/tareas', async (req, res) => {
  try {
    const { usuario_id, colegio_id, anio_activo, alumno_id } = req.user;
    const { asignatura_id, ciclo } = req.query;

    if (!asignatura_id) {
      return res.status(400).json({ error: 'asignatura_id es requerido' });
    }

    const cicloFiltro = ciclo ? parseInt(ciclo) : 1;

    // Verificar que el alumno tiene acceso a esta asignatura
    const asignatura = await query(
      `SELECT a.*, g.id as grupo_id
       FROM asignaturas a
       INNER JOIN grupos g ON g.id = a.grupo_id
       INNER JOIN matriculas m ON m.grupo_id = g.id
       WHERE a.id = ? AND m.alumno_id = ? AND m.colegio_id = ? AND g.anio = ? 
       AND (m.estado = 0 OR m.estado = 4)
       LIMIT 1`,
      [asignatura_id, alumno_id, colegio_id, anio_activo]
    );

    if (asignatura.length === 0) {
      return res.status(403).json({ error: 'No tienes acceso a esta asignatura' });
    }

    // Obtener tareas de la asignatura filtradas por ciclo
    const tareas = await query(
      `SELECT t.*, 
              CONCAT(p.nombres, ' ', p.apellidos) as docente_nombre
       FROM asignaturas_tareas t
       INNER JOIN personal p ON p.id = t.trabajador_id
       WHERE t.asignatura_id = ? AND t.ciclo = ?
       ORDER BY t.id ASC`,
      [asignatura_id, cicloFiltro]
    );

    // Obtener archivos de cada tarea y construir URLs
    const phpSystemUrl = process.env.PHP_SYSTEM_URL || 'https://nuevo.vanguardschools.edu.pe';
    const isDevelopment = process.env.NODE_ENV !== 'production';
    const tareasConArchivos = await Promise.all(tareas.map(async (tarea) => {
      const archivos = await query(
        `SELECT * FROM asignaturas_tareas_archivos WHERE tarea_id = ?`,
        [tarea.id]
      );

      const archivosConUrls = archivos.map(archivo => {
        let archivoUrl = null;
        if (archivo.archivo && archivo.archivo !== '') {
          if (archivo.archivo.startsWith('http')) {
            archivoUrl = archivo.archivo.replace(/https?:\/\/(www\.)?vanguardschools\.edu\.pe/gi, phpSystemUrl);
          } else if (archivo.archivo.startsWith('/Static/')) {
            const nombreArchivo = archivo.archivo.replace(/^\/Static\/Archivos\//, '');
            archivoUrl = isDevelopment
              ? `http://localhost:5000/Static/Archivos/${nombreArchivo}`
              : `${phpSystemUrl}/Static/Archivos/${nombreArchivo}`;
          } else {
            archivoUrl = isDevelopment
              ? `http://localhost:5000/Static/Archivos/${archivo.archivo}`
              : `${phpSystemUrl}/Static/Archivos/${archivo.archivo}`;
          }
        }
        return {
          ...archivo,
          archivo_url: archivoUrl
        };
      });

      let enlaceUrl = null;
      if (tarea.enlace && tarea.enlace !== '') {
        enlaceUrl = tarea.enlace.trim();
      }

      return {
        ...tarea,
        archivos: archivosConUrls,
        enlace_url: enlaceUrl
      };
    }));

    res.json({ tareas: tareasConArchivos || [] });
  } catch (error) {
    console.error('Error obteniendo tareas:', error);
    res.status(500).json({ error: 'Error al obtener tareas' });
  }
});

/**
 * GET /api/alumno/aula-virtual/examenes
 * Obtener exámenes de una asignatura (solo lectura para alumnos)
 */
router.get('/aula-virtual/examenes', async (req, res) => {
  try {
    const { usuario_id, colegio_id, anio_activo, alumno_id } = req.user;
    const { asignatura_id, ciclo } = req.query;

    if (!asignatura_id) {
      return res.status(400).json({ error: 'asignatura_id es requerido' });
    }

    const cicloFiltro = ciclo ? parseInt(ciclo) : 1;

    // Verificar que el alumno tiene acceso a esta asignatura
    const asignatura = await query(
      `SELECT a.*, g.id as grupo_id
       FROM asignaturas a
       INNER JOIN grupos g ON g.id = a.grupo_id
       INNER JOIN matriculas m ON m.grupo_id = g.id
       WHERE a.id = ? AND m.alumno_id = ? AND m.colegio_id = ? AND g.anio = ? 
       AND (m.estado = 0 OR m.estado = 4)
       LIMIT 1`,
      [asignatura_id, alumno_id, colegio_id, anio_activo]
    );

    if (asignatura.length === 0) {
      return res.status(403).json({ error: 'No tienes acceso a esta asignatura' });
    }

    // Obtener exámenes de la asignatura filtrados por ciclo
    // IMPORTANTE: Mostrar TODOS los exámenes, independientemente de si tienen preguntas o no
    const examenes = await query(
      `SELECT ae.*,
              (SELECT COUNT(*) FROM asignaturas_examenes_preguntas WHERE examen_id = ae.id) as total_preguntas
       FROM asignaturas_examenes ae
       WHERE ae.asignatura_id = ? AND ae.ciclo = ?
       ORDER BY ae.id ASC`,
      [asignatura_id, cicloFiltro]
    );
    
    console.log(`📝 Exámenes encontrados para asignatura ${asignatura_id}, ciclo ${cicloFiltro}:`, examenes.length);

    // Obtener matrícula del alumno
    const matricula = await query(
      `SELECT m.id as matricula_id
       FROM matriculas m
       INNER JOIN grupos g ON g.id = m.grupo_id
       WHERE m.alumno_id = ? AND m.colegio_id = ? AND g.anio = ?
       AND (m.estado = 0 OR m.estado = 4)
       LIMIT 1`,
      [alumno_id, colegio_id, anio_activo]
    );

    // Para cada examen, verificar si el alumno ya tiene nota
    const examenesConInfo = await Promise.all(examenes.map(async (examen) => {
      let tiene_nota = false;
      let intentos_usados = 0;
      
      if (matricula.length > 0) {
        // Verificar si tiene nota en la tabla de pruebas (la tabla asignaturas_examenes_notas no existe)
        // La tabla asignaturas_examenes_pruebas usa matricula_id y puntaje
        try {
          const pruebaConNota = await query(
            `SELECT puntaje FROM asignaturas_examenes_pruebas
             WHERE examen_id = ? AND matricula_id = ? AND estado = 'FINALIZADA' AND puntaje IS NOT NULL
             ORDER BY fecha_hora DESC
             LIMIT 1`,
            [examen.id, matricula[0].matricula_id]
          );
          
          tiene_nota = pruebaConNota.length > 0 && pruebaConNota[0].puntaje !== null;
        } catch (err) {
          console.error('Error verificando nota en pruebas:', err);
          tiene_nota = false;
        }
        
        // Contar intentos usados
        // La tabla usa matricula_id (no alumno_id)
        try {
          const intentosData = await query(
            `SELECT COUNT(*) as total FROM asignaturas_examenes_pruebas
             WHERE examen_id = ? AND matricula_id = ? AND estado = 'FINALIZADA'`,
            [examen.id, matricula[0].matricula_id]
          );
          
          intentos_usados = intentosData[0]?.total || 0;
        } catch (error) {
          console.error('Error contando intentos:', error);
          intentos_usados = 0;
        }
      }
      
      return {
        ...examen,
        tiene_nota,
        intentos_usados,
        puede_iniciar: examen.estado === 'ACTIVO' && !tiene_nota && (examen.intentos === 0 || intentos_usados < examen.intentos)
      };
    }));

    res.json({ examenes: examenesConInfo || [] });
  } catch (error) {
    console.error('Error obteniendo exámenes:', error);
    res.status(500).json({ error: 'Error al obtener exámenes' });
  }
});

/**
 * GET /api/alumno/examenes/:examenId
 * Obtener detalles de un examen
 */
router.get('/examenes/:examenId', async (req, res) => {
  try {
    const { usuario_id, colegio_id, anio_activo, alumno_id } = req.user;
    const { examenId } = req.params;

    // Verificar acceso al examen
    const examen = await query(
      `SELECT ae.*, a.id as asignatura_id, g.id as grupo_id
       FROM asignaturas_examenes ae
       INNER JOIN asignaturas a ON a.id = ae.asignatura_id
       INNER JOIN grupos g ON g.id = a.grupo_id
       INNER JOIN matriculas m ON m.grupo_id = g.id
       WHERE ae.id = ? AND m.alumno_id = ? AND m.colegio_id = ? AND g.anio = ?
       AND (m.estado = 0 OR m.estado = 4)
       LIMIT 1`,
      [examenId, alumno_id, colegio_id, anio_activo]
    );

    if (examen.length === 0) {
      return res.status(404).json({ error: 'Examen no encontrado o sin acceso' });
    }

    res.json(examen[0]);
  } catch (error) {
    console.error('Error obteniendo examen:', error);
    res.status(500).json({ error: 'Error al obtener examen' });
  }
});

/**
 * POST /api/alumno/examenes/:examenId/iniciar
 * Iniciar un examen (crear prueba)
 */
router.post('/examenes/:examenId/iniciar', async (req, res) => {
  try {
    const { usuario_id, colegio_id, anio_activo, alumno_id } = req.user;
    const { examenId } = req.params;

    // Verificar acceso y que el examen esté activo
    const examen = await query(
      `SELECT ae.*, a.id as asignatura_id, g.id as grupo_id
       FROM asignaturas_examenes ae
       INNER JOIN asignaturas a ON a.id = ae.asignatura_id
       INNER JOIN grupos g ON g.id = a.grupo_id
       INNER JOIN matriculas m ON m.grupo_id = g.id
       WHERE ae.id = ? AND m.alumno_id = ? AND m.colegio_id = ? AND g.anio = ?
       AND (m.estado = 0 OR m.estado = 4)
       LIMIT 1`,
      [examenId, alumno_id, colegio_id, anio_activo]
    );

    if (examen.length === 0) {
      return res.status(404).json({ error: 'Examen no encontrado o sin acceso' });
    }

    if (examen[0].estado !== 'ACTIVO') {
      return res.status(400).json({ error: 'El examen no está activo' });
    }

    // Obtener matrícula del alumno
    const matricula = await query(
      `SELECT m.id as matricula_id
       FROM matriculas m
       INNER JOIN grupos g ON g.id = m.grupo_id
       WHERE m.alumno_id = ? AND m.colegio_id = ? AND g.anio = ?
       AND (m.estado = 0 OR m.estado = 4)
       LIMIT 1`,
      [alumno_id, colegio_id, anio_activo]
    );

    if (matricula.length === 0) {
      return res.status(404).json({ error: 'No se encontró la matrícula del alumno' });
    }

    // Verificar intentos (la tabla usa matricula_id, no alumno_id)
    const intentosUsados = await query(
      `SELECT COUNT(*) as total FROM asignaturas_examenes_pruebas
       WHERE examen_id = ? AND matricula_id = ? AND estado = 'FINALIZADA'`,
      [examenId, matricula[0].matricula_id]
    );

    if (examen[0].intentos > 0 && (intentosUsados[0]?.total || 0) >= examen[0].intentos) {
      return res.status(400).json({ error: 'Has agotado todos los intentos disponibles' });
    }

    // Verificar si ya tiene una prueba en progreso (la tabla usa matricula_id, fecha_hora y estado 'ACTIVO')
    const pruebaEnProgreso = await query(
      `SELECT * FROM asignaturas_examenes_pruebas
       WHERE examen_id = ? AND matricula_id = ? AND estado = 'ACTIVO'
       ORDER BY fecha_hora DESC
       LIMIT 1`,
      [examenId, matricula[0].matricula_id]
    );

    if (pruebaEnProgreso.length > 0) {
      // Retornar la prueba existente
      return res.json({
        prueba_id: pruebaEnProgreso[0].id,
        fecha_inicio: pruebaEnProgreso[0].fecha_hora,
        fecha_expiracion: pruebaEnProgreso[0].expiracion,
        respuestas: pruebaEnProgreso[0].respuestas ? JSON.parse(pruebaEnProgreso[0].respuestas) : {}
      });
    }

    // Crear nueva prueba
    const fechaInicio = new Date();
    const fechaExpiracion = examen[0].tiempo > 0 
      ? new Date(fechaInicio.getTime() + (examen[0].tiempo * 60 * 1000))
      : null;

    const resultado = await execute(
      `INSERT INTO asignaturas_examenes_pruebas 
       (examen_id, matricula_id, fecha_hora, expiracion, estado, respuestas, preguntas)
       VALUES (?, ?, ?, ?, 'ACTIVO', '{}', '')`,
      [examenId, matricula[0].matricula_id, fechaInicio, fechaExpiracion]
    );

    res.json({
      prueba_id: resultado.insertId,
      fecha_inicio: fechaInicio,
      fecha_expiracion: fechaExpiracion,
      respuestas: {}
    });
  } catch (error) {
    console.error('Error iniciando examen:', error);
    res.status(500).json({ error: 'Error al iniciar examen' });
  }
});

/**
 * GET /api/alumno/examenes/:examenId/preguntas
 * Obtener preguntas del examen (con alternativas)
 */
router.get('/examenes/:examenId/preguntas', async (req, res) => {
  try {
    const { usuario_id, colegio_id, anio_activo, alumno_id } = req.user;
    const { examenId } = req.params;

    // Verificar acceso
    const examen = await query(
      `SELECT ae.* FROM asignaturas_examenes ae
       INNER JOIN asignaturas a ON a.id = ae.asignatura_id
       INNER JOIN grupos g ON g.id = a.grupo_id
       INNER JOIN matriculas m ON m.grupo_id = g.id
       WHERE ae.id = ? AND m.alumno_id = ? AND m.colegio_id = ? AND g.anio = ?
       AND (m.estado = 0 OR m.estado = 4)
       LIMIT 1`,
      [examenId, alumno_id, colegio_id, anio_activo]
    );

    if (examen.length === 0) {
      return res.status(404).json({ error: 'Examen no encontrado o sin acceso' });
    }

    // Obtener preguntas
    const preguntas = await query(
      `SELECT * FROM asignaturas_examenes_preguntas
       WHERE examen_id = ?
       ORDER BY orden ASC`,
      [examenId]
    );

    // Obtener alternativas para cada pregunta
    const preguntasConAlternativas = await Promise.all(preguntas.map(async (pregunta) => {
      const alternativas = await query(
        `SELECT * FROM asignaturas_examenes_preguntas_alternativas
         WHERE pregunta_id = ?
         ORDER BY id ASC`,
        [pregunta.id]
      );

      return {
        ...pregunta,
        alternativas: alternativas || []
      };
    }));

    res.json({ preguntas: preguntasConAlternativas });
  } catch (error) {
    console.error('Error obteniendo preguntas:', error);
    res.status(500).json({ error: 'Error al obtener preguntas' });
  }
});

/**
 * POST /api/alumno/examenes/:examenId/respuestas
 * Guardar respuestas del estudiante
 */
router.post('/examenes/:examenId/respuestas', async (req, res) => {
  try {
    const { usuario_id, colegio_id, anio_activo, alumno_id } = req.user;
    const { examenId } = req.params;
    const { respuestas } = req.body;

    // Obtener matrícula del alumno
    const matricula = await query(
      `SELECT m.id as matricula_id
       FROM matriculas m
       INNER JOIN grupos g ON g.id = m.grupo_id
       WHERE m.alumno_id = ? AND m.colegio_id = ? AND g.anio = ?
       AND (m.estado = 0 OR m.estado = 4)
       LIMIT 1`,
      [alumno_id, colegio_id, anio_activo]
    );

    if (matricula.length === 0) {
      return res.status(404).json({ error: 'No se encontró la matrícula del alumno' });
    }

    // Obtener prueba en progreso (la tabla usa matricula_id y estado 'ACTIVO')
    const prueba = await query(
      `SELECT * FROM asignaturas_examenes_pruebas
       WHERE examen_id = ? AND matricula_id = ? AND estado = 'ACTIVO'
       ORDER BY fecha_hora DESC
       LIMIT 1`,
      [examenId, matricula[0].matricula_id]
    );

    if (prueba.length === 0) {
      return res.status(404).json({ error: 'No hay una prueba en progreso' });
    }

    // Actualizar respuestas
    await execute(
      `UPDATE asignaturas_examenes_pruebas
       SET respuestas = ?
       WHERE id = ?`,
      [JSON.stringify(respuestas), prueba[0].id]
    );

    res.json({ success: true });
  } catch (error) {
    console.error('Error guardando respuestas:', error);
    res.status(500).json({ error: 'Error al guardar respuestas' });
  }
});

/**
 * POST /api/alumno/examenes/:examenId/finalizar
 * Finalizar examen
 */
router.post('/examenes/:examenId/finalizar', async (req, res) => {
  try {
    const { usuario_id, colegio_id, anio_activo, alumno_id } = req.user;
    const { examenId } = req.params;

    // Obtener matrícula del alumno
    const matricula = await query(
      `SELECT m.id as matricula_id
       FROM matriculas m
       INNER JOIN grupos g ON g.id = m.grupo_id
       WHERE m.alumno_id = ? AND m.colegio_id = ? AND g.anio = ?
       AND (m.estado = 0 OR m.estado = 4)
       LIMIT 1`,
      [alumno_id, colegio_id, anio_activo]
    );

    if (matricula.length === 0) {
      return res.status(404).json({ error: 'No se encontró la matrícula del alumno' });
    }

    // Obtener prueba en progreso (la tabla usa matricula_id y estado 'ACTIVO')
    const prueba = await query(
      `SELECT * FROM asignaturas_examenes_pruebas
       WHERE examen_id = ? AND matricula_id = ? AND estado = 'ACTIVO'
       ORDER BY fecha_hora DESC
       LIMIT 1`,
      [examenId, matricula[0].matricula_id]
    );

    if (prueba.length === 0) {
      return res.status(404).json({ error: 'No hay una prueba en progreso' });
    }

    // Obtener examen y preguntas
    const examen = await query(
      `SELECT * FROM asignaturas_examenes WHERE id = ?`,
      [examenId]
    );

    if (examen.length === 0) {
      return res.status(404).json({ error: 'Examen no encontrado' });
    }

    const respuestas = prueba[0].respuestas ? JSON.parse(prueba[0].respuestas) : {};
    
    // Obtener todas las preguntas con alternativas completas
    const preguntas = await query(
      `SELECT p.*, 
              (SELECT GROUP_CONCAT(
                CONCAT(a.id, ':', a.descripcion, ':', a.correcta, ':', COALESCE(a.orden_posicion, ''), ':', COALESCE(a.par_id, ''), ':', COALESCE(a.zona_drop, ''))
                ORDER BY a.id ASC SEPARATOR '||'
              ) FROM asignaturas_examenes_preguntas_alternativas a 
              WHERE a.pregunta_id = p.id) as alternativas_data
       FROM asignaturas_examenes_preguntas p
       WHERE p.examen_id = ?
       ORDER BY p.orden ASC`,
      [examenId]
    );

    // Calificar examen
    let puntosTotal = 0;
    let puntosObtenidos = 0;
    let correctas = 0;
    let incorrectas = 0;
    const detalles = [];

    for (const pregunta of preguntas) {
      const respuestaAlumno = respuestas[pregunta.id.toString()] || respuestas[pregunta.id];
      let puntosPregunta = 0;
      let esCorrecta = false;

      // Calcular puntos totales de la pregunta
      puntosTotal += examen[0].tipo_puntaje === 'GENERAL' 
        ? (examen[0].puntos_correcta || 0)
        : (pregunta.puntos || 0);

      // Procesar alternativas
      let alternativas = [];
      if (pregunta.alternativas_data) {
        alternativas = pregunta.alternativas_data.split('||').map(alt => {
          // Formato: id:descripcion:correcta:orden_posicion:par_id:zona_drop
          const parts = alt.split(':');
          return {
            id: parseInt(parts[0]) || 0,
            descripcion: parts[1] || '',
            correcta: parts[2] || 'NO',
            orden_posicion: parts[3] ? parseInt(parts[3]) : null,
            par_id: parts[4] ? parseInt(parts[4]) : null,
            zona_drop: parts[5] || null
          };
        });
      }

      // Evaluar según el tipo de pregunta
      switch (pregunta.tipo) {
        case 'ALTERNATIVAS':
        case 'VERDADERO_FALSO':
          if (respuestaAlumno) {
            const alternativaMarcada = alternativas.find(alt => alt.id === parseInt(respuestaAlumno));
            if (alternativaMarcada && alternativaMarcada.correcta === 'SI') {
              esCorrecta = true;
              puntosPregunta = examen[0].tipo_puntaje === 'GENERAL' 
                ? (examen[0].puntos_correcta || 0)
                : (pregunta.puntos || 0);
            } else if (examen[0].penalizar_incorrecta === 'SI' && respuestaAlumno) {
              puntosPregunta = -(examen[0].penalizacion_incorrecta || 0);
            }
          }
          break;

        case 'COMPLETAR':
          // La respuesta es un objeto con índices: {0: "lima", 1: "otro"}
          if (respuestaAlumno && typeof respuestaAlumno === 'object') {
            // Obtener todas las respuestas del alumno
            const respuestasCompletar = Object.values(respuestaAlumno).map(r => String(r).trim().toLowerCase()).filter(r => r);
            // Obtener todas las respuestas correctas de las alternativas
            const respuestasCorrectas = alternativas
              .filter(alt => alt.correcta === 'SI')
              .map(alt => alt.descripcion.replace(/<[^>]*>/g, '').trim().toLowerCase());
            
            // Comparar cada respuesta del alumno con las correctas
            let todasCorrectas = true;
            if (respuestasCompletar.length === respuestasCorrectas.length) {
              for (let i = 0; i < respuestasCompletar.length; i++) {
                if (!respuestasCorrectas.includes(respuestasCompletar[i])) {
                  todasCorrectas = false;
                  break;
                }
              }
            } else {
              todasCorrectas = false;
            }
            
            if (todasCorrectas) {
              esCorrecta = true;
              puntosPregunta = examen[0].tipo_puntaje === 'GENERAL' 
                ? (examen[0].puntos_correcta || 0)
                : (pregunta.puntos || 0);
            } else if (examen[0].penalizar_incorrecta === 'SI' && respuestasCompletar.length > 0) {
              puntosPregunta = -(examen[0].penalizacion_incorrecta || 0);
            }
          }
          break;

        case 'ORDENAR':
          // La respuesta es un array de IDs: [id1, id2, id3, ...]
          if (respuestaAlumno && Array.isArray(respuestaAlumno)) {
            // Verificar que el orden sea correcto comparando con orden_posicion
            let ordenCorrecto = true;
            for (let i = 0; i < respuestaAlumno.length; i++) {
              const altId = parseInt(respuestaAlumno[i]);
              const alternativa = alternativas.find(alt => alt.id === altId);
              if (!alternativa || alternativa.orden_posicion !== (i + 1)) {
                ordenCorrecto = false;
                break;
              }
            }
            
            if (ordenCorrecto) {
              esCorrecta = true;
              puntosPregunta = examen[0].tipo_puntaje === 'GENERAL' 
                ? (examen[0].puntos_correcta || 0)
                : (pregunta.puntos || 0);
            } else if (examen[0].penalizar_incorrecta === 'SI' && respuestaAlumno.length > 0) {
              puntosPregunta = -(examen[0].penalizacion_incorrecta || 0);
            }
          }
          break;

        case 'EMPAREJAR':
          // La respuesta es un objeto con pares: {altId1: parId1, altId2: parId2}
          if (respuestaAlumno && typeof respuestaAlumno === 'object') {
            let todosParesCorrectos = true;
            for (const [altId, parId] of Object.entries(respuestaAlumno)) {
              const alternativa = alternativas.find(alt => alt.id === parseInt(altId));
              const parEsperado = alternativa?.par_id;
              if (!parEsperado || parseInt(parId) !== parEsperado) {
                todosParesCorrectos = false;
                break;
              }
            }
            
            if (todosParesCorrectos) {
              esCorrecta = true;
              puntosPregunta = examen[0].tipo_puntaje === 'GENERAL' 
                ? (examen[0].puntos_correcta || 0)
                : (pregunta.puntos || 0);
            } else if (examen[0].penalizar_incorrecta === 'SI' && Object.keys(respuestaAlumno).length > 0) {
              puntosPregunta = -(examen[0].penalizacion_incorrecta || 0);
            }
          }
          break;

        case 'ARRASTRAR_Y_SOLTAR':
          // La respuesta es un objeto con zonas: {altId: zona}
          if (respuestaAlumno && typeof respuestaAlumno === 'object') {
            let todasZonasCorrectas = true;
            for (const [altId, zona] of Object.entries(respuestaAlumno)) {
              const alternativa = alternativas.find(alt => alt.id === parseInt(altId));
              const zonaEsperada = alternativa?.zona_drop?.trim().toLowerCase();
              const zonaAlumno = String(zona).trim().toLowerCase();
              if (!zonaEsperada || zonaAlumno !== zonaEsperada) {
                todasZonasCorrectas = false;
                break;
              }
            }
            
            if (todasZonasCorrectas) {
              esCorrecta = true;
              puntosPregunta = examen[0].tipo_puntaje === 'GENERAL' 
                ? (examen[0].puntos_correcta || 0)
                : (pregunta.puntos || 0);
            } else if (examen[0].penalizar_incorrecta === 'SI' && Object.keys(respuestaAlumno).length > 0) {
              puntosPregunta = -(examen[0].penalizacion_incorrecta || 0);
            }
          }
          break;

        case 'RESPUESTA_CORTA':
          // La respuesta es un string
          if (respuestaAlumno) {
            const respuestaNormalizada = String(respuestaAlumno).trim().toLowerCase();
            const alternativaCorrecta = alternativas.find(alt => alt.correcta === 'SI');
            if (alternativaCorrecta) {
              const correctaNormalizada = alternativaCorrecta.descripcion.replace(/<[^>]*>/g, '').trim().toLowerCase();
              if (respuestaNormalizada === correctaNormalizada) {
                esCorrecta = true;
                puntosPregunta = examen[0].tipo_puntaje === 'GENERAL' 
                  ? (examen[0].puntos_correcta || 0)
                  : (pregunta.puntos || 0);
              } else if (examen[0].penalizar_incorrecta === 'SI') {
                puntosPregunta = -(examen[0].penalizacion_incorrecta || 0);
              }
            }
          }
          break;
      }

      puntosObtenidos += puntosPregunta;
      
      if (esCorrecta) {
        correctas++;
      } else if (respuestaAlumno !== null && respuestaAlumno !== undefined && respuestaAlumno !== '') {
        incorrectas++;
      }

      detalles.push({
        pregunta_id: pregunta.id,
        es_correcta: esCorrecta,
        puntos: puntosPregunta
      });
    }

    // Limitar puntaje mínimo a 0
    if (puntosObtenidos < 0) puntosObtenidos = 0;

    // Calcular nota (0-20)
    const nota = puntosTotal > 0 
      ? Math.max(0, Math.min(20, (puntosObtenidos / puntosTotal) * 20))
      : 0;

    // Actualizar prueba con puntaje, correctas e incorrectas
    await execute(
      `UPDATE asignaturas_examenes_pruebas
       SET estado = 'FINALIZADA',
           puntaje = ?,
           correctas = ?,
           incorrectas = ?
       WHERE id = ?`,
      [nota, correctas, incorrectas, prueba[0].id]
    );

    // La nota ya se guardó en la tabla asignaturas_examenes_pruebas con el UPDATE anterior
    // No es necesario guardar en otra tabla porque asignaturas_examenes_notas no existe

    res.json({
      success: true,
      nota: nota.toFixed(2),
      puntos_obtenidos: puntosObtenidos,
      puntos_total: puntosTotal
    });
  } catch (error) {
    console.error('Error finalizando examen:', error);
    res.status(500).json({ error: 'Error al finalizar examen' });
  }
});

/**
 * POST /api/alumno/examenes/:examenId/violaciones
 * Registrar violación (salir de ventana)
 */
router.post('/examenes/:examenId/violaciones', async (req, res) => {
  try {
    const { usuario_id, colegio_id } = req.user || {};
    const { examenId } = req.params;
    const { tipo, timestamp } = req.body;

    // Registrar en auditoría usando la estructura correcta
    try {
      const ahora = new Date();
      const fecha = ahora.toISOString().split('T')[0]; // YYYY-MM-DD
      const hora = ahora.toTimeString().split(' ')[0]; // HH:MM:SS
      
      await execute(
        `INSERT INTO auditoria_logs 
         (usuario_id, colegio_id, tipo_usuario, accion, modulo, entidad, entidad_id,
          descripcion, datos_nuevos, resultado, fecha_hora, fecha, hora)
         VALUES (?, ?, 'ALUMNO', 'VIOLACION_EXAMEN', 'EXAMENES', 'examen', ?, ?, ?, 'EXITOSO', ?, ?, ?)`,
        [
          usuario_id || null, 
          colegio_id || null, 
          examenId, 
          `Violación de examen: ${tipo}`, 
          JSON.stringify({ tipo, timestamp }), 
          ahora, 
          fecha, 
          hora
        ]
      );
    } catch (auditError) {
      // Si hay error en auditoría, solo loguear pero no fallar
      console.warn('No se pudo registrar violación en auditoría:', auditError.message);
    }

    res.json({ success: true });
  } catch (error) {
    console.error('Error registrando violación:', error);
    // No devolver error 500, solo loguear
    res.json({ success: false, message: 'Violación registrada localmente' });
  }
});

/**
 * GET /api/alumno/aula-virtual/videos
 * Obtener videos de una asignatura (solo lectura para alumnos)
 */
router.get('/aula-virtual/videos', async (req, res) => {
  try {
    const { usuario_id, colegio_id, anio_activo, alumno_id } = req.user;
    const { asignatura_id, ciclo } = req.query;

    if (!asignatura_id) {
      return res.status(400).json({ error: 'asignatura_id es requerido' });
    }

    const cicloFiltro = ciclo ? parseInt(ciclo) : 1;

    // Verificar que el alumno tiene acceso a esta asignatura
    const asignatura = await query(
      `SELECT a.*, g.id as grupo_id
       FROM asignaturas a
       INNER JOIN grupos g ON g.id = a.grupo_id
       INNER JOIN matriculas m ON m.grupo_id = g.id
       WHERE a.id = ? AND m.alumno_id = ? AND m.colegio_id = ? AND g.anio = ? 
       AND (m.estado = 0 OR m.estado = 4)
       LIMIT 1`,
      [asignatura_id, alumno_id, colegio_id, anio_activo]
    );

    if (asignatura.length === 0) {
      return res.status(403).json({ error: 'No tienes acceso a esta asignatura' });
    }

    // Obtener videos de la asignatura filtrados por ciclo
    const videos = await query(
      `SELECT * FROM asignaturas_videos
       WHERE asignatura_id = ? AND ciclo = ?
       ORDER BY id ASC`,
      [asignatura_id, cicloFiltro]
    );

    res.json({ videos: videos || [] });
  } catch (error) {
    console.error('Error obteniendo videos:', error);
    res.status(500).json({ error: 'Error al obtener videos' });
  }
});

/**
 * GET /api/alumno/aula-virtual/enlaces
 * Obtener enlaces de ayuda de una asignatura (solo lectura para alumnos)
 */
router.get('/aula-virtual/enlaces', async (req, res) => {
  try {
    const { usuario_id, colegio_id, anio_activo, alumno_id } = req.user;
    const { asignatura_id, ciclo } = req.query;

    if (!asignatura_id) {
      return res.status(400).json({ error: 'asignatura_id es requerido' });
    }

    const cicloFiltro = ciclo ? parseInt(ciclo) : 1;

    // Verificar que el alumno tiene acceso a esta asignatura
    const asignatura = await query(
      `SELECT a.*, g.id as grupo_id
       FROM asignaturas a
       INNER JOIN grupos g ON g.id = a.grupo_id
       INNER JOIN matriculas m ON m.grupo_id = g.id
       WHERE a.id = ? AND m.alumno_id = ? AND m.colegio_id = ? AND g.anio = ? 
       AND (m.estado = 0 OR m.estado = 4)
       LIMIT 1`,
      [asignatura_id, alumno_id, colegio_id, anio_activo]
    );

    if (asignatura.length === 0) {
      return res.status(403).json({ error: 'No tienes acceso a esta asignatura' });
    }

    // Obtener enlaces de la asignatura filtrados por ciclo
    const enlaces = await query(
      `SELECT * FROM asignaturas_enlaces
       WHERE asignatura_id = ? AND ciclo = ?
       ORDER BY id ASC`,
      [asignatura_id, cicloFiltro]
    );

    res.json({ enlaces: enlaces || [] });
  } catch (error) {
    console.error('Error obteniendo enlaces:', error);
    res.status(500).json({ error: 'Error al obtener enlaces' });
  }
});

/**
 * GET /api/alumno/aula-virtual/tareas/:tareaId
 * Obtener detalles completos de una tarea (con archivos, nota, entregas del alumno)
 */
router.get('/aula-virtual/tareas/:tareaId', async (req, res) => {
  try {
    const { usuario_id, colegio_id, anio_activo, alumno_id } = req.user;
    const { tareaId } = req.params;

    // Verificar que el alumno tiene acceso a esta tarea
    const tarea = await query(
      `SELECT t.*, 
              CONCAT(p.nombres, ' ', p.apellidos) as docente_nombre,
              a.id as asignatura_id,
              c.nombre as curso_nombre
       FROM asignaturas_tareas t
       INNER JOIN asignaturas a ON a.id = t.asignatura_id
       INNER JOIN grupos g ON g.id = a.grupo_id
       INNER JOIN cursos c ON c.id = a.curso_id
       INNER JOIN personal p ON p.id = a.personal_id
       INNER JOIN matriculas m ON m.grupo_id = g.id
       WHERE t.id = ? AND m.alumno_id = ? AND m.colegio_id = ? AND g.anio = ?
       AND (m.estado = 0 OR m.estado = 4)
       LIMIT 1`,
      [tareaId, alumno_id, colegio_id, anio_activo]
    );

    if (tarea.length === 0) {
      return res.status(404).json({ error: 'Tarea no encontrada o sin permisos' });
    }

    const tareaData = tarea[0];

    // Obtener archivos adjuntos de la tarea
    const archivos = await query(
      `SELECT * FROM asignaturas_tareas_archivos WHERE tarea_id = ?`,
      [tareaId]
    );

    // Construir URLs de archivos
    const phpSystemUrl = process.env.PHP_SYSTEM_URL || 'https://nuevo.vanguardschools.edu.pe';
    const isDevelopment = process.env.NODE_ENV !== 'production';
    const archivosConUrls = archivos.map(archivo => {
      let archivoUrl = null;
      if (archivo.archivo && archivo.archivo !== '') {
        archivoUrl = isDevelopment
          ? `http://localhost:5000/Static/Archivos/${archivo.archivo}`
          : `${phpSystemUrl}/Static/Archivos/${archivo.archivo}`;
      }
      return {
        ...archivo,
        archivo_url: archivoUrl
      };
    });

    // Obtener entregas del alumno para esta tarea
    const entregas = await query(
      `SELECT * FROM asignaturas_tareas_entregas
       WHERE tarea_id = ? AND alumno_id = ? AND tipo = 'ALUMNO'
       ORDER BY fecha_hora DESC`,
      [tareaId, alumno_id]
    );

    // Obtener matrícula del alumno para obtener la nota
    const matricula = await query(
      `SELECT m.id as matricula_id
       FROM matriculas m
       INNER JOIN grupos g ON g.id = m.grupo_id
       WHERE m.alumno_id = ? AND m.colegio_id = ? AND g.anio = ?
       AND (m.estado = 0 OR m.estado = 4)
       LIMIT 1`,
      [alumno_id, colegio_id, anio_activo]
    );

    let nota = null;
    if (matricula.length > 0) {
      const notaData = await query(
        `SELECT nota FROM asignaturas_tareas_notas
         WHERE tarea_id = ? AND matricula_id = ?`,
        [tareaId, matricula[0].matricula_id]
      );
      if (notaData.length > 0) {
        nota = notaData[0].nota || '-';
      }
    }

    res.json({
      tarea: {
        id: tareaData.id,
        titulo: tareaData.titulo,
        descripcion: tareaData.descripcion,
        fecha_hora: tareaData.fecha_hora,
        fecha_entrega: tareaData.fecha_entrega,
        docente_nombre: tareaData.docente_nombre,
        curso_nombre: tareaData.curso_nombre,
        enlace: tareaData.enlace || null,
        archivos: archivosConUrls,
        entregas: entregas,
        nota: nota || '-'
      }
    });
  } catch (error) {
    console.error('Error obteniendo detalles de tarea:', error);
    res.status(500).json({ error: 'Error al obtener detalles de la tarea' });
  }
});

/**
 * POST /api/alumno/aula-virtual/tareas/:tareaId/entregar
 * Enviar la URL de la entrega del alumno
 */
router.post('/aula-virtual/tareas/:tareaId/entregar', async (req, res) => {
  try {
    const { usuario_id, colegio_id, anio_activo, alumno_id } = req.user;
    const { tareaId } = req.params;
    const { url } = req.body;

    if (!url || url.trim() === '') {
      return res.status(400).json({ error: 'La URL es requerida' });
    }

    // Verificar que el alumno tiene acceso a esta tarea
    const tarea = await query(
      `SELECT t.*, a.grupo_id
       FROM asignaturas_tareas t
       INNER JOIN asignaturas a ON a.id = t.asignatura_id
       INNER JOIN grupos g ON g.id = a.grupo_id
       INNER JOIN matriculas m ON m.grupo_id = g.id
       WHERE t.id = ? AND m.alumno_id = ? AND m.colegio_id = ? AND g.anio = ?
       AND (m.estado = 0 OR m.estado = 4)
       LIMIT 1`,
      [tareaId, alumno_id, colegio_id, anio_activo]
    );

    if (tarea.length === 0) {
      return res.status(404).json({ error: 'Tarea no encontrada o sin permisos' });
    }

    // Verificar que la fecha de entrega no haya pasado
    const fechaEntrega = new Date(tarea[0].fecha_entrega);
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);
    fechaEntrega.setHours(0, 0, 0, 0);

    if (hoy > fechaEntrega) {
      return res.status(400).json({ error: 'La fecha de entrega ya ha pasado' });
    }

    // Insertar la entrega
    const fechaHora = new Date().toISOString().slice(0, 19).replace('T', ' ');
    await execute(
      `INSERT INTO asignaturas_tareas_entregas 
       (alumno_id, tarea_id, archivo, url, nombre, fecha_hora, tipo, mensaje)
       VALUES (?, ?, '', ?, ?, ?, 'ALUMNO', '')`,
      [alumno_id, tareaId, url.trim(), url.trim(), fechaHora]
    );

    res.json({
      success: true,
      message: 'Entrega enviada correctamente'
    });
  } catch (error) {
    console.error('Error enviando entrega:', error);
    res.status(500).json({ error: 'Error al enviar la entrega' });
  }
});

/**
 * POST /api/alumno/aula-virtual/tareas/:tareaId/marcar-visto
 * Marcar una tarea como vista por el alumno
 */
router.post('/aula-virtual/tareas/:tareaId/marcar-visto', async (req, res) => {
  try {
    const { tareaId } = req.params;
    const { alumno_id } = req.user;

    if (!alumno_id) {
      return res.status(400).json({ error: 'alumno_id es requerido' });
    }

    // Obtener la tarea actual
    const tarea = await query(
      `SELECT * FROM asignaturas_tareas WHERE id = ?`,
      [tareaId]
    );

    if (tarea.length === 0) {
      return res.status(404).json({ error: 'Tarea no encontrada' });
    }

    const tareaActual = tarea[0];

    // Deserializar campo "visto" (formato PHP serialized)
    let vistos = [];
    try {
      if (tareaActual.visto && tareaActual.visto !== '') {
        const deserialized = phpSerialize.unserialize(tareaActual.visto);
        vistos = Array.isArray(deserialized) ? deserialized : [];
      }
    } catch (error) {
      console.warn('Error deserializando campo visto:', error);
      vistos = [];
    }

    // Si el alumno_id no está en el array, agregarlo
    if (!vistos.includes(alumno_id)) {
      vistos.push(alumno_id);
      
      // Serializar el array actualizado
      const vistoSerializado = phpSerialize.serialize(vistos);

      // Actualizar el campo visto en la BD
      await execute(
        `UPDATE asignaturas_tareas SET visto = ? WHERE id = ?`,
        [vistoSerializado, tareaId]
      );

      console.log('✅ [TAREA VISTO] Alumno marcó tarea como vista:', {
        tarea_id: tareaId,
        alumno_id: alumno_id,
        vistos_actualizados: vistos
      });
    }

    res.json({ 
      success: true, 
      message: 'Tarea marcada como vista',
      visto: true
    });
  } catch (error) {
    console.error('Error marcando tarea como vista:', error);
    res.status(500).json({ error: 'Error al marcar tarea como vista' });
  }
});

module.exports = router;
