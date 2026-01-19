const Client = require('ssh2-sftp-client');
const path = require('path');
const fs = require('fs');
require('dotenv').config();

/**
 * Configuración del servidor FTP/SFTP PHP
 */
const FTP_CONFIG = {
  host: process.env.FTP_HOST || '89.117.52.9',
  username: process.env.FTP_USER || 'vanguard',
  password: process.env.FTP_PASSWORD || 'CtxADB8q0SaVYox',
  port: process.env.FTP_PORT || 22, // 22 para SFTP, 21 para FTP
  protocol: process.env.FTP_PROTOCOL || 'sftp' // 'sftp' o 'ftp'
};

/**
 * Mapeo de tipos de archivo a carpetas en el servidor PHP
 */
const UPLOAD_PATHS = {
  'personal_foto': '/Static/Image/Fotos',
  'alumno_foto': '/Static/Image/Fotos',
  'archivo_pdf': '/Static/Archivos',
  'documento': '/Static/Documentos',
  'fondo': '/Static/Image/Fondos',
  'avatar': '/Static/Image/Avatars',
  'publicacion': '/Static/Archivos',
  'tarea': '/Static/Archivos',
  'examen': '/Static/Archivos',
  'tema': '/Static/Archivos'
};

/**
 * Subir archivo al servidor PHP vía SFTP
 * @param {Object} file - Objeto de archivo de multer (req.file)
 * @param {String} fileType - Tipo de archivo (ej: 'personal_foto', 'archivo_pdf')
 * @param {String} basePath - Ruta base en el servidor (ej: '/var/www/html' o '/home/vanguard/public_html')
 * @returns {Promise<String>} - Nombre del archivo subido (solo el nombre, no la ruta completa)
 */
async function uploadToPHPServer(file, fileType, basePath = '') {
  if (!file) {
    throw new Error('No se proporcionó archivo para subir');
  }

  if (!UPLOAD_PATHS[fileType]) {
    throw new Error(`Tipo de archivo no válido: ${fileType}`);
  }

  // En desarrollo local, NO subir al servidor PHP
  // Solo guardar localmente
  const isProduction = process.env.NODE_ENV === 'production';
  if (!isProduction) {
    console.log('⚠️  Desarrollo local: Archivo guardado solo localmente (no se sube al servidor PHP)');
    return file.filename; // Retornar solo el nombre del archivo
  }

  const sftp = new Client();
  const remotePath = basePath + UPLOAD_PATHS[fileType];
  const remoteFilePath = `${remotePath}/${file.filename}`;
  const localFilePath = file.path;

  try {
    // Conectar al servidor SFTP
    await sftp.connect({
      host: FTP_CONFIG.host,
      username: FTP_CONFIG.username,
      password: FTP_CONFIG.password,
      port: FTP_CONFIG.port
    });

    // Verificar que el archivo local existe
    if (!fs.existsSync(localFilePath)) {
      throw new Error(`El archivo local no existe: ${localFilePath}`);
    }

    // Crear directorio remoto si no existe
    try {
      await sftp.mkdir(remotePath, true); // true = crear directorios padres si no existen
    } catch (error) {
      // Si el directorio ya existe, ignorar el error
      if (error.code !== 4) { // 4 = SSH2_FX_FAILURE (directorio ya existe)
        console.warn(`Advertencia al crear directorio: ${error.message}`);
      }
    }

    // Subir archivo
    await sftp.put(localFilePath, remoteFilePath);

    console.log(`✅ Archivo subido exitosamente: ${remoteFilePath}`);

    // Retornar solo el nombre del archivo (como lo hace PHP)
    return file.filename;

  } catch (error) {
    console.error('❌ Error subiendo archivo vía SFTP:', error);
    throw new Error(`Error al subir archivo al servidor PHP: ${error.message}`);
  } finally {
    // Cerrar conexión
    if (sftp) {
      await sftp.end();
    }
  }
}

/**
 * Eliminar archivo del servidor PHP
 * @param {String} filename - Nombre del archivo a eliminar
 * @param {String} fileType - Tipo de archivo
 * @param {String} basePath - Ruta base en el servidor
 */
async function deleteFromPHPServer(filename, fileType, basePath = '') {
  if (!filename) return;

  const sftp = new Client();
  const remotePath = basePath + UPLOAD_PATHS[fileType];
  const remoteFilePath = `${remotePath}/${filename}`;

  try {
    await sftp.connect({
      host: FTP_CONFIG.host,
      username: FTP_CONFIG.username,
      password: FTP_CONFIG.password,
      port: FTP_CONFIG.port
    });

    // Verificar si el archivo existe antes de eliminarlo
    const exists = await sftp.exists(remoteFilePath);
    if (exists) {
      await sftp.delete(remoteFilePath);
      console.log(`✅ Archivo eliminado del servidor PHP: ${remoteFilePath}`);
    }
  } catch (error) {
    console.error('❌ Error eliminando archivo del servidor PHP:', error);
    // No lanzar error, solo registrar
  } finally {
    if (sftp) {
      await sftp.end();
    }
  }
}

module.exports = {
  uploadToPHPServer,
  deleteFromPHPServer,
  UPLOAD_PATHS
};

