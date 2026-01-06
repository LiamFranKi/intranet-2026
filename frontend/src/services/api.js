import axios from 'axios';

// Detectar si estamos en desarrollo o producción
const isDevelopment = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
const isProduction = window.location.hostname === 'intranet.vanguardschools.com';

// Determinar URL de API
let apiUrl;
if (process.env.REACT_APP_API_URL) {
  // Si está definida en .env, usarla
  apiUrl = process.env.REACT_APP_API_URL;
} else if (isDevelopment) {
  // Desarrollo: usar HTTP local
  apiUrl = 'http://localhost:5000/api';
} else if (isProduction) {
  // Producción: Si hay error de certificado, usar HTTP directamente
  // Por ahora, usar HTTP hasta que se configure SSL correctamente
  apiUrl = 'http://intranet.vanguardschools.com/api';
} else {
  // Fallback
  apiUrl = 'http://localhost:5000/api';
}

const api = axios.create({
  baseURL: apiUrl,
  headers: {
    'Content-Type': 'application/json',
  },
  timeout: 30000, // 30 segundos de timeout para conexiones remotas
});

// Interceptor para agregar token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Interceptor para manejar errores
api.interceptors.response.use(
  (response) => response,
  (error) => {
    // Manejar error de certificado SSL
    if (error.code === 'ERR_CERT_COMMON_NAME_INVALID' || error.message?.includes('certificate')) {
      console.error('Error de certificado SSL. Verifica la configuración HTTPS.');
      // Si estamos en producción y hay error de certificado, intentar con HTTP
      if (isProduction && apiUrl.startsWith('https://')) {
        console.warn('Intentando con HTTP en lugar de HTTPS...');
        // No hacer nada automáticamente, dejar que el usuario lo maneje
      }
    }
    
    if (error.response?.status === 401) {
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      window.location.href = '/login';
    }
    
    // Si es un error de red (sin respuesta), mostrar mensaje más claro
    if (!error.response) {
      console.error('Error de conexión:', error.message);
    }
    
    return Promise.reject(error);
  }
);

export default api;

