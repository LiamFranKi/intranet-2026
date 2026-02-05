// Cargar variables de entorno desde el archivo .env del backend manualmente
const fs = require('fs');
const path = require('path');

// Función para leer y parsear el archivo .env
function loadEnvFile(envPath) {
  const envVars = {};
  try {
    if (fs.existsSync(envPath)) {
      const envContent = fs.readFileSync(envPath, 'utf8');
      envContent.split('\n').forEach(line => {
        line = line.trim();
        // Ignorar comentarios y líneas vacías
        if (line && !line.startsWith('#')) {
          const equalIndex = line.indexOf('=');
          if (equalIndex > 0) {
            const key = line.substring(0, equalIndex).trim();
            const value = line.substring(equalIndex + 1).trim();
            // Remover comillas si existen
            const cleanValue = value.replace(/^["']|["']$/g, '');
            envVars[key] = cleanValue;
          }
        }
      });
    }
  } catch (error) {
    console.error('Error cargando .env:', error);
  }
  return envVars;
}

// Cargar variables desde el .env del backend
const envPath = path.join(__dirname, 'backend', '.env');
const envVars = loadEnvFile(envPath);

module.exports = {
  apps: [{
    name: 'intranet2026-backend',
    script: './backend/server.js',
    cwd: '/home/vanguard/intranet2026',
    instances: 1,
    exec_mode: 'fork',
    // Las variables de entorno se cargan desde .env
    env: {
      NODE_ENV: envVars.NODE_ENV || 'production',
      PORT: envVars.PORT || 5000,
      PHP_SYSTEM_URL: envVars.PHP_SYSTEM_URL,
      MYSQL_HOST: envVars.MYSQL_HOST,
      MYSQL_PORT: envVars.MYSQL_PORT,
      MYSQL_USER: envVars.MYSQL_USER,
      MYSQL_PASSWORD: envVars.MYSQL_PASSWORD,
      MYSQL_DATABASE: envVars.MYSQL_DATABASE,
      JWT_SECRET: envVars.JWT_SECRET,
      ALLOWED_ORIGINS: envVars.ALLOWED_ORIGINS,
      FRONTEND_URL: envVars.FRONTEND_URL
    },
    error_file: './logs/backend-error.log',
    out_file: './logs/backend-out.log',
    log_date_format: 'YYYY-MM-DD HH:mm:ss Z',
    merge_logs: true,
    autorestart: true,
    watch: false,
    max_memory_restart: '1G',
    min_uptime: '10s',
    max_restarts: 10
  }]
};
