const mysql = require('mysql2/promise');
require('dotenv').config();

// Detectar si estamos en producción o desarrollo
const isProduction = process.env.NODE_ENV === 'production';

// En producción: conexión directa al MySQL remoto
// En desarrollo: conexión a través del túnel SSH (localhost)
const mysqlHost = isProduction 
  ? (process.env.MYSQL_HOST_PRODUCTION || process.env.MYSQL_HOST || '89.117.52.9')
  : (process.env.MYSQL_HOST_DEVELOPMENT || process.env.MYSQL_HOST || 'localhost');

console.log(`🔌 MySQL: ${isProduction ? 'PRODUCCIÓN' : 'DESARROLLO'} - Host: ${mysqlHost}`);

const mysqlReadPool = mysql.createPool({
  host: mysqlHost,
  port: process.env.MYSQL_PORT || 3306,
  user: process.env.MYSQL_USER,
  password: process.env.MYSQL_PASSWORD,
  database: process.env.MYSQL_DATABASE,
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0,
  multipleStatements: false,
  // Configuración adicional para producción
  reconnect: true,
  enableKeepAlive: true,
  keepAliveInitialDelay: 0
});

async function query(sql, params = []) {
  try {
    const sqlUpper = sql.trim().toUpperCase();
    if (!sqlUpper.startsWith('SELECT')) {
      throw new Error('Solo se permiten consultas SELECT. Modificación de datos no permitida.');
    }
    const [rows] = await mysqlReadPool.execute(sql, params);
    return rows;
  } catch (error) {
    console.error('MySQL Error:', error);
    throw error;
  }
}

async function getAnioActivo(colegioId) {
  try {
    const [rows] = await mysqlReadPool.execute(
      'SELECT anio_activo FROM colegios WHERE id = ?',
      [colegioId]
    );
    return rows.length > 0 ? rows[0].anio_activo : null;
  } catch (error) {
    console.error('Error obteniendo año activo:', error);
    throw error;
  }
}

module.exports = { query, mysqlReadPool, getAnioActivo };

