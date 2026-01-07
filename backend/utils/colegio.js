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

    // No usar tabla config - todo se maneja directamente
    // Logo y nombre se obtienen desde archivos locales o valores por defecto
    return {
      id: colegio.id,
      nombre: colegio.nombre,
      nombre_intranet: colegio.nombre, // Usar nombre del colegio como nombre de intranet
      anio_activo: colegio.anio_activo,
      bloquear_deudores: colegio.bloquear_deudores,
      dias_tolerancia: colegio.dias_tolerancia,
      logo: null, // Logo se maneja directamente desde archivos locales
      color_principal: null, // Colores se pueden configurar después si es necesario
      color_secundario: null,
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

