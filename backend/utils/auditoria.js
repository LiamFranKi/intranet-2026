const { query } = require('./postgres');

/**
 * Registrar una acción en el log de auditoría
 */
async function registrarAccion({
  usuario_id,
  colegio_id,
  tipo_usuario,
  accion,
  modulo = null,
  entidad = null,
  entidad_id = null,
  descripcion = null,
  url = null,
  metodo_http = null,
  ip_address = null,
  user_agent = null,
  datos_anteriores = null,
  datos_nuevos = null,
  resultado = 'EXITOSO',
  mensaje_error = null,
  duracion_ms = null,
}) {
  try {
    await query(
      `INSERT INTO auditoria_logs (
        usuario_id, colegio_id, tipo_usuario, accion, modulo, entidad, entidad_id,
        descripcion, url, metodo_http, ip_address, user_agent,
        datos_anteriores, datos_nuevos, resultado, mensaje_error, duracion_ms
      ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, $16, $17)`,
      [
        usuario_id,
        colegio_id,
        tipo_usuario,
        accion,
        modulo,
        entidad,
        entidad_id,
        descripcion,
        url,
        metodo_http,
        ip_address,
        user_agent,
        datos_anteriores ? JSON.stringify(datos_anteriores) : null,
        datos_nuevos ? JSON.stringify(datos_nuevos) : null,
        resultado,
        mensaje_error,
        duracion_ms,
      ]
    );
  } catch (error) {
    // No lanzar error para no interrumpir el flujo principal
    // Solo loguear el error
    console.error('Error registrando en auditoría:', error);
  }
}

/**
 * Obtener logs de un usuario
 */
async function obtenerLogsUsuario(usuarioId, fechaDesde = null, fechaHasta = null, limite = 100) {
  try {
    let sql = `
      SELECT * FROM auditoria_logs
      WHERE usuario_id = $1
    `;
    const params = [usuarioId];

    if (fechaDesde) {
      sql += ` AND fecha >= $${params.length + 1}`;
      params.push(fechaDesde);
    }

    if (fechaHasta) {
      sql += ` AND fecha <= $${params.length + 1}`;
      params.push(fechaHasta);
    }

    sql += ` ORDER BY fecha_hora DESC LIMIT $${params.length + 1}`;
    params.push(limite);

    const result = await query(sql, params);
    return result.rows;
  } catch (error) {
    console.error('Error obteniendo logs:', error);
    throw error;
  }
}

/**
 * Obtener logs por módulo
 */
async function obtenerLogsModulo(modulo, fechaDesde = null, fechaHasta = null, limite = 100) {
  try {
    let sql = `
      SELECT * FROM auditoria_logs
      WHERE modulo = $1
    `;
    const params = [modulo];

    if (fechaDesde) {
      sql += ` AND fecha >= $${params.length + 1}`;
      params.push(fechaDesde);
    }

    if (fechaHasta) {
      sql += ` AND fecha <= $${params.length + 1}`;
      params.push(fechaHasta);
    }

    sql += ` ORDER BY fecha_hora DESC LIMIT $${params.length + 1}`;
    params.push(limite);

    const result = await query(sql, params);
    return result.rows;
  } catch (error) {
    console.error('Error obteniendo logs:', error);
    throw error;
  }
}

module.exports = {
  registrarAccion,
  obtenerLogsUsuario,
  obtenerLogsModulo,
};

