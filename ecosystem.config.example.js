// Cargar variables de entorno desde el archivo .env del backend
const path = require('path');
require('dotenv').config({ path: path.join(__dirname, 'backend', '.env') });

module.exports = {
  apps: [{
    name: 'intranet2026-backend',
    script: './backend/server.js',
    cwd: '/home/vanguard/intranet2026',
    instances: 1,
    exec_mode: 'fork',
    // Las variables de entorno se cargan desde .env usando dotenv arriba
    // Estas se pueden sobrescribir aquí si es necesario
    env: {
      NODE_ENV: process.env.NODE_ENV || 'production',
      PORT: process.env.PORT || 5000,
      PHP_SYSTEM_URL: process.env.PHP_SYSTEM_URL,
      MYSQL_HOST: process.env.MYSQL_HOST,
      MYSQL_PORT: process.env.MYSQL_PORT,
      MYSQL_USER: process.env.MYSQL_USER,
      MYSQL_PASSWORD: process.env.MYSQL_PASSWORD,
      MYSQL_DATABASE: process.env.MYSQL_DATABASE,
      JWT_SECRET: process.env.JWT_SECRET,
      ALLOWED_ORIGINS: process.env.ALLOWED_ORIGINS,
      FRONTEND_URL: process.env.FRONTEND_URL
    },
    error_file: './logs/backend-error.log',
    out_file: './logs/backend-out.log',
    log_date_format: 'YYYY-MM-DD HH:mm:ss Z',
    merge_logs: true,
    autorestart: true,
    watch: false,
    max_memory_restart: '1G',
    // Reiniciar si el proceso usa más de 1GB de RAM
    min_uptime: '10s',
    max_restarts: 10
  }]
};

