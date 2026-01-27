/**
 * Script para listar usuarios disponibles en la base de datos
 * Útil para encontrar credenciales para importar actividades
 */

require('dotenv').config();
const path = require('path');

// Cargar variables de entorno desde backend/.env
const envPath = path.join(__dirname, 'backend', '.env');
require('dotenv').config({ path: envPath });

const { query } = require('./backend/utils/mysql');

async function listarUsuarios() {
  try {
    console.log('🔍 Buscando usuarios disponibles...\n');

    // Obtener usuarios DOCENTE y ADMINISTRADOR
    const usuarios = await query(
      `SELECT 
        u.id,
        u.usuario as dni,
        u.tipo,
        u.estado,
        u.colegio_id,
        COALESCE(p.nombres, a.nombres) as nombres,
        COALESCE(p.apellidos, CONCAT(a.apellido_paterno, ' ', a.apellido_materno)) as apellidos
       FROM usuarios u
       LEFT JOIN personal p ON p.id = u.personal_id
       LEFT JOIN alumnos a ON a.id = u.alumno_id
       WHERE u.tipo IN ('DOCENTE', 'ADMINISTRADOR', 'DIRECTOR')
       AND u.estado = 'ACTIVO'
       ORDER BY u.tipo, nombres, apellidos
       LIMIT 20`
    );

    if (usuarios.length === 0) {
      console.log('❌ No se encontraron usuarios DOCENTE o ADMINISTRADOR activos');
      return;
    }

    console.log('='.repeat(80));
    console.log('📋 USUARIOS DISPONIBLES PARA IMPORTAR ACTIVIDADES');
    console.log('='.repeat(80));
    console.log('');

    usuarios.forEach((usuario, index) => {
      console.log(`${index + 1}. ${usuario.tipo}`);
      console.log(`   DNI/Usuario: ${usuario.dni}`);
      console.log(`   Nombre: ${usuario.nombres || 'N/A'} ${usuario.apellidos || ''}`);
      console.log(`   Estado: ${usuario.estado}`);
      console.log(`   Colegio ID: ${usuario.colegio_id}`);
      console.log('');
    });

    console.log('='.repeat(80));
    console.log('💡 INSTRUCCIONES:');
    console.log('='.repeat(80));
    console.log('1. Usa el DNI/Usuario de cualquiera de los usuarios listados arriba');
    console.log('2. La contraseña es la que configuraste para ese usuario');
    console.log('3. Si no recuerdas la contraseña, puedes:');
    console.log('   - Consultar con el administrador del sistema');
    console.log('   - O usar un usuario de administrador que conozcas');
    console.log('');
    console.log('Ejemplo de uso:');
    console.log(`   node importar-actividades.js ${usuarios[0].dni} TU_CONTRASEÑA`);
    console.log('');

  } catch (error) {
    console.error('❌ Error listando usuarios:', error.message);
    console.error('');
    console.error('💡 Verifica que:');
    console.error('   1. El archivo .env esté configurado correctamente');
    console.error('   2. La conexión a MySQL esté funcionando');
    console.error('   3. Las credenciales de la base de datos sean correctas');
  } finally {
    process.exit(0);
  }
}

listarUsuarios();

