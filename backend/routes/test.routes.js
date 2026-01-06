const express = require('express');
const router = express.Router();
const { query } = require('../utils/mysql');

/**
 * GET /api/test/mysql
 * Probar conexión a MySQL y obtener datos reales
 */
router.get('/mysql', async (req, res) => {
  try {
    // Probar conexión básica
    const [usuarios] = await query('SELECT COUNT(*) as total FROM usuarios');
    
    // Obtener algunos datos reales
    const usuariosEjemplo = await query(
      'SELECT id, usuario, tipo, estado FROM usuarios LIMIT 5'
    );
    
    const colegios = await query('SELECT id, nombre, anio_activo FROM colegios LIMIT 5');
    
    const grupos = await query(
      'SELECT id, grado, seccion, anio FROM grupos WHERE anio = (SELECT anio_activo FROM colegios LIMIT 1) LIMIT 5'
    );

    res.json({
      success: true,
      message: '✅ Conexión a MySQL exitosa - Usando datos REALES',
      datos: {
        total_usuarios: usuarios[0].total,
        usuarios_ejemplo: usuariosEjemplo,
        colegios: colegios,
        grupos: grupos,
      },
      configuracion: {
        host: process.env.MYSQL_HOST,
        database: process.env.MYSQL_DATABASE,
        tipo: 'SOLO LECTURA (SELECT únicamente)',
      }
    });
  } catch (error) {
    console.error('Error probando MySQL:', error);
    res.status(500).json({
      success: false,
      error: error.message,
      message: '❌ Error conectando a MySQL'
    });
  }
});

module.exports = router;

