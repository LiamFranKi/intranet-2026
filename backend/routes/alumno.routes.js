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

    // Contar mensajes no leídos
    const mensajesNoLeidos = await query(
      `SELECT COUNT(*) as total
       FROM mensajes m
       WHERE m.destinatario_id = ? AND m.estado = 'NO_LEIDO' AND m.borrado = 'NO'`,
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
      fecha_nacimiento: alumnoData.fecha_nacimiento
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

module.exports = router;
