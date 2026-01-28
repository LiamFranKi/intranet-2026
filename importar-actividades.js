/**
 * Script para importar actividades desde calendarizacion.json
 * 
 * Uso:
 *   node importar-actividades.js
 * 
 * O con credenciales personalizadas:
 *   node importar-actividades.js usuario password
 */

require('dotenv').config();
const axios = require('axios');
const readline = require('readline');

const API_URL = process.env.REACT_APP_API_URL || 'http://localhost:5000/api';

// Crear interfaz para leer input del usuario
const rl = readline.createInterface({
  input: process.stdin,
  output: process.stdout
});

function pregunta(pregunta) {
  return new Promise((resolve) => {
    rl.question(pregunta, (respuesta) => {
      resolve(respuesta);
    });
  });
}

async function importarActividades() {
  try {
    console.log('🚀 Iniciando importación de actividades...\n');

    // Obtener credenciales
    let usuario, password;
    
    if (process.argv[2] && process.argv[3]) {
      // Credenciales desde argumentos
      usuario = process.argv[2];
      password = process.argv[3];
      console.log(`📝 Usando credenciales proporcionadas: ${usuario}\n`);
    } else {
      // Pedir credenciales interactivamente
      console.log('Por favor, ingresa tus credenciales:');
      usuario = await pregunta('Usuario (DNI): ');
      password = await pregunta('Contraseña: ');
      console.log('');
    }

    // Paso 1: Hacer login
    console.log('🔐 Iniciando sesión...');
    const loginResponse = await axios.post(`${API_URL}/auth/login`, {
      usuario,
      password
    });

    if (!loginResponse.data.token) {
      console.error('❌ Error: No se recibió token de autenticación');
      process.exit(1);
    }

    const token = loginResponse.data.token;
    console.log('✅ Login exitoso\n');

    // Paso 2: Importar actividades
    console.log('📅 Importando actividades desde calendarizacion.json...');
    console.log('⏳ Esto puede tomar unos momentos...\n');

    const importResponse = await axios.post(
      `${API_URL}/docente/actividades/importar-calendario`,
      {},
      {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        }
      }
    );

    // Mostrar resultados
    console.log('\n' + '='.repeat(60));
    console.log('📊 RESULTADOS DE LA IMPORTACIÓN');
    console.log('='.repeat(60));
    console.log(`✅ Actividades insertadas: ${importResponse.data.actividades_insertadas}`);
    console.log(`📅 Año: ${importResponse.data.año}`);
    
    if (importResponse.data.actividades_con_error > 0) {
      console.log(`⚠️  Actividades con error: ${importResponse.data.actividades_con_error}`);
      if (importResponse.data.errores) {
        console.log('\n❌ Errores:');
        importResponse.data.errores.forEach((error, index) => {
          console.log(`   ${index + 1}. ${error.evento}: ${error.error}`);
        });
      }
    } else {
      console.log('✅ Todas las actividades se importaron correctamente');
    }
    
    console.log('\n' + '='.repeat(60));
    console.log('✨ Importación completada exitosamente!');
    console.log('='.repeat(60) + '\n');

    rl.close();
    process.exit(0);

  } catch (error) {
    console.error('\n❌ Error durante la importación:');
    
    if (error.response) {
      // Error de respuesta del servidor
      console.error(`   Status: ${error.response.status}`);
      console.error(`   Mensaje: ${error.response.data?.error || error.response.data?.message || 'Error desconocido'}`);
      
      if (error.response.data?.detalles) {
        console.error(`   Detalles: ${error.response.data.detalles}`);
      }
    } else if (error.request) {
      // Error de conexión
      console.error('   No se pudo conectar al servidor');
      console.error('   Verifica que el servidor esté corriendo en:', API_URL);
    } else {
      // Otro error
      console.error(`   ${error.message}`);
    }

    rl.close();
    process.exit(1);
  }
}

// Ejecutar
importarActividades();

