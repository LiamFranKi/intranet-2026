const express = require('express');
const router = express.Router();
const { authenticateToken, requireUserType } = require('../middleware/auth');
const { obtenerLogsUsuario, obtenerLogsModulo } = require('../utils/auditoria');
const { query } = require('../utils/postgres');

/**
 * GET /api/auditoria/mis-logs
 * Obtener logs del usuario autenticado
 */
router.get('/mis-logs', authenticateToken, async (req, res) => {
  try {
    const { usuario_id } = req.user;
    const { fechaDesde, fechaHasta, limite = 100 } = req.query;

    const logs = await obtenerLogsUsuario(
      usuario_id,
      fechaDesde || null,
      fechaHasta || null,
      parseInt(limite)
    );

    res.json({ logs, total: logs.length });
  } catch (error) {
    console.error('Error obteniendo logs:', error);
    res.status(500).json({ error: 'Error obteniendo logs' });
  }
});

/**
 * GET /api/auditoria/usuario/:usuarioId
 * Obtener logs de un usuario específico (solo administradores)
 */
router.get('/usuario/:usuarioId', authenticateToken, requireUserType('ADMINISTRADOR'), async (req, res) => {
  try {
    const { usuarioId } = req.params;
    const { fechaDesde, fechaHasta, limite = 100 } = req.query;

    const logs = await obtenerLogsUsuario(
      parseInt(usuarioId),
      fechaDesde || null,
      fechaHasta || null,
      parseInt(limite)
    );

    res.json({ logs, total: logs.length });
  } catch (error) {
    console.error('Error obteniendo logs:', error);
    res.status(500).json({ error: 'Error obteniendo logs' });
  }
});

/**
 * GET /api/auditoria/modulo/:modulo
 * Obtener logs de un módulo específico (solo administradores)
 */
router.get('/modulo/:modulo', authenticateToken, requireUserType('ADMINISTRADOR'), async (req, res) => {
  try {
    const { modulo } = req.params;
    const { fechaDesde, fechaHasta, limite = 100 } = req.query;

    const logs = await obtenerLogsModulo(
      modulo,
      fechaDesde || null,
      fechaHasta || null,
      parseInt(limite)
    );

    res.json({ logs, total: logs.length });
  } catch (error) {
    console.error('Error obteniendo logs:', error);
    res.status(500).json({ error: 'Error obteniendo logs' });
  }
});

/**
 * GET /api/auditoria/estadisticas
 * Obtener estadísticas de actividad (solo administradores)
 */
router.get('/estadisticas', authenticateToken, requireUserType('ADMINISTRADOR'), async (req, res) => {
  try {
    const { fechaDesde, fechaHasta } = req.query;
    const colegioId = req.user.colegio_id;

    let sql = `
      SELECT 
        tipo_usuario,
        accion,
        COUNT(*) as total,
        COUNT(DISTINCT usuario_id) as usuarios_unicos
      FROM auditoria_logs
      WHERE colegio_id = $1
    `;
    const params = [colegioId];

    if (fechaDesde) {
      sql += ` AND fecha >= $${params.length + 1}`;
      params.push(fechaDesde);
    }

    if (fechaHasta) {
      sql += ` AND fecha <= $${params.length + 1}`;
      params.push(fechaHasta);
    }

    sql += ` GROUP BY tipo_usuario, accion ORDER BY total DESC`;

    const result = await query(sql, params);

    res.json({ estadisticas: result.rows });
  } catch (error) {
    console.error('Error obteniendo estadísticas:', error);
    res.status(500).json({ error: 'Error obteniendo estadísticas' });
  }
});

module.exports = router;

