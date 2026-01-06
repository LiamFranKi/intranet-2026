const fs = require('fs');
const path = require('path');
const { pool } = require('../../backend/utils/postgres');

async function runMigrations() {
  console.log('🔄 Ejecutando migraciones...\n');

  try {
    const migrationsDir = path.join(__dirname, '../migrations');
    const files = fs.readdirSync(migrationsDir)
      .filter(file => file.endsWith('.sql'))
      .sort();

    if (files.length === 0) {
      console.log('⚠️  No se encontraron archivos de migración\n');
      return;
    }

    console.log(`📁 Encontradas ${files.length} migración(es)\n`);

    for (const file of files) {
      console.log(`📄 Ejecutando: ${file}...`);
      const sql = fs.readFileSync(path.join(migrationsDir, file), 'utf8');
      
      await pool.query(sql);
      console.log(`✅ ${file} ejecutada correctamente\n`);
    }

    console.log('✅ Todas las migraciones ejecutadas exitosamente!\n');
    await pool.end();
    process.exit(0);
  } catch (error) {
    console.error('❌ Error ejecutando migraciones:', error.message);
    console.error(error);
    await pool.end();
    process.exit(1);
  }
}

runMigrations();

