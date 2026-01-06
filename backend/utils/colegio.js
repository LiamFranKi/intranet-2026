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
    let configuracion = {};
    try {
      const configs = await query(
        `SELECT nombre_empresa, logo, color_principal, color_secundario
         FROM config 
         WHERE colegio_id = ? 
         LIMIT 1`,
        [colegioId]
      );

      if (configs.length > 0) {
        configuracion = {
          nombre_intranet: configs[0].nombre_empresa || colegio.nombre,
          logo: configs[0].logo || null,
          color_principal: configs[0].color_principal || null,
          color_secundario: configs[0].color_secundario || null,
        };
      } else {
        // Si no hay registro en tabla config, usar valores por defecto
        configuracion = {
          nombre_intranet: colegio.nombre,
          logo: null,
          color_principal: null,
          color_secundario: null,
        };
      }
    } catch (error) {
      // Si no existe tabla config, usar valores por defecto
      console.log('Tabla config no encontrada, usando valores por defecto');
      configuracion = {
        nombre_intranet: colegio.nombre,
        logo: null,
        color_principal: null,
        color_secundario: null,
      };
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

