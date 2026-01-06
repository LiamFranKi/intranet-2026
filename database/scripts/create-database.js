const { Client } = require('pg');
require('dotenv').config({ path: '../backend/.env' });

async function createDatabase() {
  console.log('🔧 Creando base de datos PostgreSQL...\n');

  // Primero conectarse a la base de datos 'postgres' para crear la nueva
  const adminClient = new Client({
    host: process.env.POSTGRES_HOST || 'localhost',
    port: process.env.POSTGRES_PORT || 5432,
    user: process.env.POSTGRES_USER || 'postgres',
    password: process.env.POSTGRES_PASSWORD,
    database: 'postgres' // Conectar a postgres para crear nueva BD
  });

  try {
    await adminClient.connect();
    console.log('✅ Conectado a PostgreSQL\n');

    const dbName = process.env.POSTGRES_DATABASE || 'aula_virtual';
    const dbUser = process.env.POSTGRES_USER || 'postgres';
    const dbPassword = process.env.POSTGRES_PASSWORD;

    // Verificar si la base de datos ya existe
    const checkDb = await adminClient.query(
      `SELECT 1 FROM pg_database WHERE datname = $1`,
      [dbName]
    );

    if (checkDb.rows.length > 0) {
      console.log(`⚠️  La base de datos '${dbName}' ya existe.`);
      console.log('   Si quieres recrearla, elimínala primero.\n');
      await adminClient.end();
      return;
    }

    // Crear base de datos
    await adminClient.query(`CREATE DATABASE ${dbName}`);
    console.log(`✅ Base de datos '${dbName}' creada\n`);

    // Crear usuario si no existe (solo si no es postgres)
    if (dbUser !== 'postgres') {
      try {
        await adminClient.query(
          `CREATE USER ${dbUser} WITH PASSWORD $1`,
          [dbPassword]
        );
        console.log(`✅ Usuario '${dbUser}' creado\n`);
      } catch (error) {
        if (error.message.includes('already exists')) {
          console.log(`ℹ️  Usuario '${dbUser}' ya existe\n`);
        } else {
          throw error;
        }
      }

      // Dar permisos
      await adminClient.query(
        `GRANT ALL PRIVILEGES ON DATABASE ${dbName} TO ${dbUser}`
      );
      console.log(`✅ Permisos otorgados a '${dbUser}'\n`);
    }

    await adminClient.end();

    // Conectar a la nueva base de datos para crear esquema inicial
    const dbClient = new Client({
      host: process.env.POSTGRES_HOST || 'localhost',
      port: process.env.POSTGRES_PORT || 5432,
      user: dbUser,
      password: dbPassword,
      database: dbName
    });

    await dbClient.connect();
    console.log(`✅ Conectado a la base de datos '${dbName}'\n`);

    // Crear esquema inicial (tablas básicas se crearán después)
    console.log('✅ Base de datos lista para usar\n');
    console.log('📝 Próximo paso: Las tablas se crearán automáticamente cuando inicies la aplicación\n');

    await dbClient.end();
    console.log('✅ Proceso completado exitosamente!\n');

  } catch (error) {
    console.error('❌ Error:', error.message);
    if (error.code === '3D000') {
      console.error('\n💡 Sugerencia: Verifica que PostgreSQL esté instalado y corriendo.');
      console.error('   En Windows: Verifica el servicio PostgreSQL en Servicios.');
      console.error('   En Linux: sudo systemctl status postgresql\n');
    } else if (error.code === '28P01') {
      console.error('\n💡 Sugerencia: Verifica la contraseña de PostgreSQL en backend/.env\n');
    }
    process.exit(1);
  }
}

createDatabase();

