const { Client } = require('pg');
require('dotenv').config({ path: '../../backend/.env' });

async function dropDatabase() {
  console.log('⚠️  ADVERTENCIA: Esto eliminará la base de datos y todos sus datos!\n');
  
  const readline = require('readline').createInterface({
    input: process.stdin,
    output: process.stdout
  });

  readline.question('¿Estás seguro? Escribe "SI" para confirmar: ', async (answer) => {
    if (answer !== 'SI') {
      console.log('❌ Operación cancelada\n');
      readline.close();
      process.exit(0);
    }

    readline.close();

    const adminClient = new Client({
      host: process.env.POSTGRES_HOST || 'localhost',
      port: process.env.POSTGRES_PORT || 5432,
      user: process.env.POSTGRES_USER || 'postgres',
      password: process.env.POSTGRES_PASSWORD,
      database: 'postgres'
    });

    try {
      await adminClient.connect();
      const dbName = process.env.POSTGRES_DATABASE || 'aula_virtual';
      
      // Terminar conexiones activas
      await adminClient.query(`
        SELECT pg_terminate_backend(pg_stat_activity.pid)
        FROM pg_stat_activity
        WHERE pg_stat_activity.datname = $1
          AND pid <> pg_backend_pid()
      `, [dbName]);

      // Eliminar base de datos
      await adminClient.query(`DROP DATABASE ${dbName}`);
      console.log(`✅ Base de datos '${dbName}' eliminada\n`);
      
      await adminClient.end();
      process.exit(0);
    } catch (error) {
      console.error('❌ Error:', error.message);
      process.exit(1);
    }
  });
}

dropDatabase();

