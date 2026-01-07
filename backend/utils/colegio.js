const { query } = require('./mysql');

/**
 * Obtener datos del colegio desde MySQL
 * Incluye: nombre, logo, colores, configuración
 */
async function getColegioData(colegioId) {
  try {
    const colegios = await query(
      `SELECT 
        id,
        nombre,
        anio_activo,
        bloquear_deudores,
        dias_tolerancia
       FROM colegios 
       WHERE id = ?`,
      [colegioId]
    );

    if (colegios.length === 0) {
      return null;
    }

    const colegio = colegios[0];

    // Buscar configuración adicional (tabla config)
    // Si la tabla config no existe o no tiene las columnas esperadas, usar valores por defecto
    let configuracion = {
      nombre_intranet: colegio.nombre,
      logo: null,
      color_principal: null,
      color_secundario: null,
    };

    try {
      // Primero verificar si la tabla config existe
      const tables = await query(
        `SELECT TABLE_NAME 
         FROM INFORMATION_SCHEMA.TABLES 
         WHERE TABLE_SCHEMA = DATABASE() 
         AND TABLE_NAME = 'config'`
      );

      if (tables.length === 0) {
        // Tabla config no existe, usar valores por defecto
        console.log('Tabla config no existe, usando valores por defecto');
        return {
          id: colegio.id,
          nombre: colegio.nombre,
          nombre_intranet: colegio.nombre,
          anio_activo: colegio.anio_activo,
          bloquear_deudores: colegio.bloquear_deudores,
          dias_tolerancia: colegio.dias_tolerancia,
          logo: null,
          color_principal: null,
          color_secundario: null,
        };
      }

      // Si la tabla existe, intentar obtener solo las columnas que existen
      // Usar SELECT * y luego mapear solo las columnas disponibles
      const configs = await query(
        `SELECT * FROM config WHERE colegio_id = ? LIMIT 1`,
        [colegioId]
      );

      if (configs.length > 0) {
        const configRow = configs[0];
        // Mapear solo las columnas que existen
        configuracion = {
          nombre_intranet: configRow.nombre_intranet || 
                          configRow.nombre_empresa || 
                          colegio.nombre,
          logo: configRow.logo || null,
          color_principal: configRow.color_principal || null,
          color_secundario: configRow.color_secundario || null,
        };
      }
    } catch (error) {
      // Si hay cualquier error, usar valores por defecto
      console.log('Error obteniendo configuración de tabla config, usando valores por defecto:', error.message);
    }

    return {
      id: colegio.id,
      nombre: colegio.nombre,
      nombre_intranet: configuracion.nombre_intranet,
      anio_activo: colegio.anio_activo,
      bloquear_deudores: colegio.bloquear_deudores,
      dias_tolerancia: colegio.dias_tolerancia,
      logo: configuracion.logo,
      color_principal: configuracion.color_principal,
      color_secundario: configuracion.color_secundario,
    };
  } catch (error) {
    console.error('Error obteniendo datos del colegio:', error);
    throw error;
  }
}

/**
 * Obtener solo el nombre de la intranet
 */
async function getNombreIntranet(colegioId) {
  try {
    const data = await getColegioData(colegioId);
    return data ? data.nombre_intranet : null;
  } catch (error) {
    console.error('Error obteniendo nombre de intranet:', error);
    return null;
  }
}

module.exports = { getColegioData, getNombreIntranet };

