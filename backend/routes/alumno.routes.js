const express = require('express');
const router = express.Router();
const { query, getAnioActivo } = require('../utils/mysql');
const { authenticateToken, requireUserType } = require('../middleware/auth');
const phpSerialize = require('php-serialize');

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

    // Contar cursos asignados (asignaturas del grupo del alumno)
    const cursosAsignados = grupoId ? await query(
      `SELECT COUNT(DISTINCT a.id) as total
       FROM asignaturas a
       INNER JOIN grupos g ON g.id = a.grupo_id
       WHERE a.grupo_id = ? AND a.colegio_id = ? AND g.anio = ?`,
      [grupoId, colegio_id, anio_activo]
    ) : [{ total: 0 }];

    // Contar tareas pendientes (tareas del grupo del alumno que aún no han vencido)
    const tareasPendientes = grupoId ? await query(
      `SELECT COUNT(DISTINCT t.id) as total
       FROM asignaturas_tareas t
       INNER JOIN asignaturas a ON a.id = t.asignatura_id
       INNER JOIN grupos g ON g.id = a.grupo_id
       WHERE a.grupo_id = ? AND a.colegio_id = ? AND g.anio = ?
       AND DATE(t.fecha_entrega) >= DATE(NOW())`,
      [grupoId, colegio_id, anio_activo]
    ) : [{ total: 0 }];

    // Contar exámenes pendientes (exámenes del grupo del alumno que aún no han vencido)
    const examenesPendientes = grupoId ? await query(
      `SELECT COUNT(DISTINCT ae.id) as total
       FROM asignaturas_examenes ae
       INNER JOIN asignaturas a ON a.id = ae.asignatura_id
       INNER JOIN grupos g ON g.id = a.grupo_id
       WHERE a.grupo_id = ? AND a.colegio_id = ? AND g.anio = ?
       AND ae.estado = 'ACTIVO'
       AND (ae.fecha_hasta IS NULL OR DATE(ae.fecha_hasta) >= DATE(NOW()))`,
      [grupoId, colegio_id, anio_activo]
    ) : [{ total: 0 }];

    // Contar mensajes no leídos (simplificado, se puede mejorar después)
    const mensajesNoLeidos = await query(
      `SELECT COUNT(*) as total
       FROM mensajes m
       WHERE m.destinatario_id = ? AND m.estado = 'NO_LEIDO'`,
      [usuario_id]
    );

    // Obtener asignaturas (cursos) del alumno
    const asignaturas = grupoId ? await query(
      `SELECT a.id,
              c.nombre as curso_nombre,
              c.imagen as curso_imagen,
              ar.nombre as area_nombre,
              CONCAT(p.nombres, ' ', p.apellidos) as docente_nombres,
              p.apellidos as docente_apellidos
       FROM asignaturas a
       INNER JOIN grupos g ON g.id = a.grupo_id
       INNER JOIN cursos c ON c.id = a.curso_id
       LEFT JOIN areas_curso ar ON ar.id = c.area_curso_id
       INNER JOIN personal p ON p.id = a.personal_id
       WHERE a.grupo_id = ? AND a.colegio_id = ? AND g.anio = ?
       ORDER BY c.nombre ASC`,
      [grupoId, colegio_id, anio_activo]
    ) : [];

    // Próximos exámenes (del grupo del alumno)
    const proximosExamenes = grupoId ? await query(
      `SELECT ae.*,
              c.nombre as curso_nombre,
              a.id as asignatura_id,
              g.grado,
              g.seccion,
              n.nombre as nivel_nombre,
              DATE(ae.fecha_desde) as fecha_evento
       FROM asignaturas_examenes ae
       INNER JOIN asignaturas a ON a.id = ae.asignatura_id
       INNER JOIN grupos g ON g.id = a.grupo_id
       INNER JOIN niveles n ON n.id = g.nivel_id
       INNER JOIN cursos c ON c.id = a.curso_id
       WHERE a.grupo_id = ? AND a.colegio_id = ? AND g.anio = ?
       AND ae.estado = 'ACTIVO'
       AND (ae.fecha_desde IS NULL OR DATE(ae.fecha_desde) >= DATE(NOW()))
       ORDER BY ae.fecha_desde ASC
       LIMIT 10`,
      [grupoId, colegio_id, anio_activo]
    ) : [];

    // Próximas tareas (del grupo del alumno)
    const proximasTareas = grupoId ? await query(
      `SELECT t.*,
              t.titulo,
              c.nombre as curso_nombre,
              a.id as asignatura_id,
              g.grado,
              g.seccion,
              n.nombre as nivel_nombre,
              DATE(t.fecha_entrega) as fecha_evento
       FROM asignaturas_tareas t
       INNER JOIN asignaturas a ON a.id = t.asignatura_id
       INNER JOIN grupos g ON g.id = a.grupo_id
       INNER JOIN niveles n ON n.id = g.nivel_id
       INNER JOIN cursos c ON c.id = a.curso_id
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
        tareasPendientes: tareasPendientes[0]?.total || 0,
        examenesPendientes: examenesPendientes[0]?.total || 0,
        mensajesNoLeidos: mensajesNoLeidos[0]?.total || 0
      },
      asignaturas: asignaturas || [],
      proximosExamenes: proximosExamenes || [],
      proximasTareas: proximasTareas || [],
      proximasActividades: actividades || []
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

      // Decodificar archivos (formato PHP: serialize(array))
      let archivos = [];
      if (pub.archivos && pub.archivos.trim() !== '') {
        try {
          const deserialized = phpSerialize.unserialize(pub.archivos);
          if (Array.isArray(deserialized)) {
            archivos = deserialized;
          }
        } catch (error) {
          console.warn('Error decodificando archivos de publicación:', error);
        }
      }

      // Construir URL de foto del autor
      let autorFotoUrl = null;
      if (pub.autor_foto && pub.autor_foto !== '') {
        if (isProduction) {
          autorFotoUrl = `${phpSystemUrl}/Static/Image/Fotos/${pub.autor_foto}`;
        } else {
          autorFotoUrl = `http://localhost:5000/Static/Image/Fotos/${pub.autor_foto}`;
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

      return {
        id: pub.id,
        contenido: pub.contenido || '',
        images: images,
        archivos: archivos,
        compartir_con: compartirCon,
        grupos: gruposNombres,
        fecha_hora: pub.fecha_hora,
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

module.exports = router;
