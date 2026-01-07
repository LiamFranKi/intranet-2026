const express = require('express');
const router = express.Router();
const crypto = require('crypto');
const jwt = require('jsonwebtoken');
const { query, getAnioActivo } = require('../utils/mysql');
const { getColegioData } = require('../utils/colegio');
const { registrarAccion } = require('../utils/auditoria');

/**
 * POST /api/auth/login
 * Login con DNI y password
 */
router.post('/login', async (req, res) => {
  try {
    const { usuario, password } = req.body;

    if (!usuario || !password) {
      return res.status(400).json({ 
        error: 'Usuario y contraseña son requeridos' 
      });
    }

    // Buscar usuario en MySQL
    // Estructura real según vanguard_intranet_2.sql:
    // - alumnos: apellido_paterno, apellido_materno, nombres
    // - personal: nombres, apellidos (un solo campo)
    const usuarios = await query(
      `SELECT u.*, 
              a.nombres as alumno_nombres, 
              a.apellido_paterno as alumno_apellido_paterno,
              a.apellido_materno as alumno_apellido_materno,
              p.nombres as personal_nombres,
              p.apellidos as personal_apellidos
       FROM usuarios u
       LEFT JOIN alumnos a ON a.id = u.alumno_id
       LEFT JOIN personal p ON p.id = u.personal_id
       WHERE u.usuario = ? AND u.estado = 'ACTIVO'`,
      [usuario]
    );

    if (usuarios.length === 0) {
      return res.status(401).json({ error: 'Usuario o contraseña incorrectos' });
    }

    const usuarioDB = usuarios[0];

    // Validar password (SHA1 como en PHP)
    const passwordHash = crypto.createHash('sha1').update(password).digest('hex');
    
    if (usuarioDB.password !== passwordHash) {
      return res.status(401).json({ error: 'Usuario o contraseña incorrectos' });
    }

    // Obtener año activo
    const anioActivo = await getAnioActivo(usuarioDB.colegio_id);
    if (!anioActivo) {
      return res.status(500).json({ 
        error: 'No se pudo obtener el año activo del colegio' 
      });
    }

    // Verificar deudas (si aplica)
    const tieneDeudas = await verificarDeudas(usuarioDB, anioActivo);
    
    if (tieneDeudas) {
      return res.status(403).json({ 
        error: 'Acceso bloqueado por deudas pendientes',
        tieneDeudas: true
      });
    }

    // Obtener datos del colegio
    const colegioData = await getColegioData(usuarioDB.colegio_id);

    // Generar token JWT
    const token = jwt.sign(
      {
        usuario_id: usuarioDB.id,
        colegio_id: usuarioDB.colegio_id,
        tipo: usuarioDB.tipo,
        alumno_id: usuarioDB.alumno_id,
        personal_id: usuarioDB.personal_id,
        apoderado_id: usuarioDB.apoderado_id,
        anio_activo: anioActivo
      },
      process.env.JWT_SECRET,
      { expiresIn: process.env.JWT_EXPIRES_IN || '24h' }
    );

    // Preparar datos del usuario para el frontend
    // Mapear nombres y apellidos según la estructura real de la base de datos
    let nombres = '';
    let apellidos = '';
    
    // Si es alumno: tiene apellido_paterno, apellido_materno separados
    if (usuarioDB.alumno_id && usuarioDB.alumno_nombres) {
      nombres = usuarioDB.alumno_nombres || '';
      apellidos = (usuarioDB.alumno_apellido_paterno || '') + 
                  (usuarioDB.alumno_apellido_materno ? ' ' + usuarioDB.alumno_apellido_materno : '');
    } 
    // Si es personal/docente: tiene apellidos (un solo campo)
    else if (usuarioDB.personal_id && usuarioDB.personal_nombres) {
      nombres = usuarioDB.personal_nombres || '';
      apellidos = usuarioDB.personal_apellidos || '';
    }
    
    const userData = {
      id: usuarioDB.id,
      usuario: usuarioDB.usuario,
      tipo: usuarioDB.tipo,
      colegio_id: usuarioDB.colegio_id,
      anio_activo: anioActivo,
      nombres: nombres.trim() || usuarioDB.usuario,
      apellidos: apellidos.trim(),
      colegio: colegioData
    };

    // Registrar login exitoso en auditoría
    registrarAccion({
      usuario_id: usuarioDB.id,
      colegio_id: usuarioDB.colegio_id,
      tipo_usuario: usuarioDB.tipo,
      accion: 'LOGIN',
      modulo: 'AUTENTICACION',
      descripcion: 'Inicio de sesión exitoso',
      url: req.originalUrl,
      metodo_http: 'POST',
      ip_address: req.ip || req.connection.remoteAddress,
      user_agent: req.get('user-agent'),
      resultado: 'EXITOSO',
    }).catch(err => console.error('Error registrando login:', err));

    res.json({
      success: true,
      token,
      user: userData
    });

  } catch (error) {
    console.error('Error en login:', error);
    res.status(500).json({ error: 'Error interno del servidor' });
  }
});

/**
 * GET /api/auth/me
 * Obtener información del usuario autenticado
 */
router.get('/me', async (req, res) => {
  try {
    // El middleware authenticateToken ya agregó req.user
    const { usuario_id, colegio_id, anio_activo } = req.user;

    // Obtener datos actualizados del usuario
    // Estructura real según vanguard_intranet_2.sql:
    // - alumnos: apellido_paterno, apellido_materno, nombres
    // - personal: nombres, apellidos (un solo campo)
    const usuarios = await query(
      `SELECT u.*, 
              a.nombres as alumno_nombres, 
              a.apellido_paterno as alumno_apellido_paterno,
              a.apellido_materno as alumno_apellido_materno,
              p.nombres as personal_nombres,
              p.apellidos as personal_apellidos
       FROM usuarios u
       LEFT JOIN alumnos a ON a.id = u.alumno_id
       LEFT JOIN personal p ON p.id = u.personal_id
       WHERE u.id = ? AND u.estado = 'ACTIVO'`,
      [usuario_id]
    );

    if (usuarios.length === 0) {
      return res.status(404).json({ error: 'Usuario no encontrado' });
    }

    const usuarioDB = usuarios[0];
    const colegioData = await getColegioData(colegio_id);

    // Mapear nombres y apellidos según la estructura real de la base de datos
    let nombres = '';
    let apellidos = '';
    
    // Si es alumno: tiene apellido_paterno, apellido_materno separados
    if (usuarioDB.alumno_id && usuarioDB.alumno_nombres) {
      nombres = usuarioDB.alumno_nombres || '';
      apellidos = (usuarioDB.alumno_apellido_paterno || '') + 
                  (usuarioDB.alumno_apellido_materno ? ' ' + usuarioDB.alumno_apellido_materno : '');
    } 
    // Si es personal/docente: tiene apellidos (un solo campo)
    else if (usuarioDB.personal_id && usuarioDB.personal_nombres) {
      nombres = usuarioDB.personal_nombres || '';
      apellidos = usuarioDB.personal_apellidos || '';
    }

    const userData = {
      id: usuarioDB.id,
      usuario: usuarioDB.usuario,
      tipo: usuarioDB.tipo,
      colegio_id: usuarioDB.colegio_id,
      anio_activo: anio_activo,
      nombres: nombres.trim() || usuarioDB.usuario,
      apellidos: apellidos.trim(),
      colegio: colegioData
    };

    res.json({ user: userData });

  } catch (error) {
    console.error('Error obteniendo usuario:', error);
    res.status(500).json({ error: 'Error interno del servidor' });
  }
});

/**
 * Función auxiliar para verificar deudas
 */
async function verificarDeudas(usuario, anioActivo) {
  try {
    // Obtener configuración del colegio
    const colegios = await query(
      'SELECT bloquear_deudores FROM colegios WHERE id = ?',
      [usuario.colegio_id]
    );

    if (colegios.length === 0 || colegios[0].bloquear_deudores !== 'SI') {
      return false; // No bloquea deudores
    }

    // Solo verificar para ALUMNOS y APODERADOS
    if (usuario.tipo !== 'ALUMNO' && usuario.tipo !== 'APODERADO') {
      return false;
    }

    if (usuario.tipo === 'ALUMNO') {
      // Verificar deudas del alumno
      const deudas = await query(
        `SELECT COUNT(*) as total
         FROM pagos p
         INNER JOIN matriculas m ON m.id = p.matricula_id
         INNER JOIN grupos g ON g.id = m.grupo_id
         WHERE m.alumno_id = ?
           AND p.estado_pago = 'PENDIENTE'
           AND m.estado = 0
           AND g.anio = ?`,
        [usuario.alumno_id, anioActivo]
      );

      return deudas[0].total > 0;
    }

    if (usuario.tipo === 'APODERADO') {
      // Verificar deudas de los hijos del apoderado
      const deudas = await query(
        `SELECT COUNT(*) as total
         FROM pagos p
         INNER JOIN matriculas m ON m.id = p.matricula_id
         INNER JOIN alumnos a ON a.id = m.alumno_id
         INNER JOIN familias f ON f.alumno_id = a.id
         INNER JOIN grupos g ON g.id = m.grupo_id
         WHERE f.apoderado_id = ?
           AND p.estado_pago = 'PENDIENTE'
           AND m.estado = 0
           AND g.anio = ?`,
        [usuario.apoderado_id, anioActivo]
      );

      return deudas[0].total > 0;
    }

    return false;
  } catch (error) {
    console.error('Error verificando deudas:', error);
    return false; // En caso de error, permitir acceso
  }
}

module.exports = router;

