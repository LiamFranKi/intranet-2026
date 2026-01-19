const { query, execute } = require('./mysql');

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
    const ahora = new Date();
    const fecha = ahora.toISOString().split('T')[0]; // YYYY-MM-DD
    const hora = ahora.toTimeString().split(' ')[0]; // HH:MM:SS
    
    // Convertir datos a JSON string para MySQL
    const datosAnterioresJson = datos_anteriores ? JSON.stringify(datos_anteriores) : null;
    const datosNuevosJson = datos_nuevos ? JSON.stringify(datos_nuevos) : null;
    
    await execute(
      `INSERT INTO auditoria_logs (
        usuario_id, colegio_id, tipo_usuario, accion, modulo, entidad, entidad_id,
        descripcion, url, metodo_http, ip_address, user_agent,
        datos_anteriores, datos_nuevos, resultado, mensaje_error, duracion_ms,
        fecha_hora, fecha, hora
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
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
        datosAnterioresJson,
        datosNuevosJson,
        resultado,
        mensaje_error,
        duracion_ms,
        ahora,
        fecha,
        hora,
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
    let sql = `SELECT * FROM auditoria_logs WHERE usuario_id = ?`;
    const params = [usuarioId];

    if (fechaDesde) {
      sql += ` AND fecha >= ?`;
      params.push(fechaDesde);
    }

    if (fechaHasta) {
      sql += ` AND fecha <= ?`;
      params.push(fechaHasta);
    }

    sql += ` ORDER BY fecha_hora DESC LIMIT ?`;
    params.push(limite);

    const result = await query(sql, params);
    return result;
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
    let sql = `SELECT * FROM auditoria_logs WHERE modulo = ?`;
    const params = [modulo];

    if (fechaDesde) {
      sql += ` AND fecha >= ?`;
      params.push(fechaDesde);
    }

    if (fechaHasta) {
      sql += ` AND fecha <= ?`;
      params.push(fechaHasta);
    }

    sql += ` ORDER BY fecha_hora DESC LIMIT ?`;
    params.push(limite);

    const result = await query(sql, params);
    return result;
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

