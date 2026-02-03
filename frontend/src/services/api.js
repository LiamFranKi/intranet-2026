import axios from 'axios';

// Detectar si estamos en desarrollo o producción
const hostname = window.location.hostname || '';
const isLocalhost = hostname === 'localhost' || hostname === '127.0.0.1';
const isDevelopment = isLocalhost; // Solo localhost es desarrollo

// Determinar URL de API
let apiUrl;
const currentProtocol = window.location.protocol === 'https:' ? 'https:' : 'http:';

if (isDevelopment) {
  // Desarrollo: solo si es localhost
  apiUrl = 'http://localhost:5000/api';
  console.log('🔧 Modo desarrollo: usando API local en', apiUrl);
} else {
  // Producción: usar el mismo dominio con /api (NO es localhost)
  apiUrl = `${currentProtocol}//${hostname}/api`;
  console.log('🌐 Modo producción: usando API en', apiUrl);
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
      if (!isDevelopment && apiUrl.startsWith('https://')) {
        console.warn('Intentando con HTTP en lugar de HTTPS...');
        // No hacer nada automáticamente, dejar que el usuario lo maneje
      }
    }
    
    if (error.response?.status === 401) {
      // Limpiar token y usuario, pero no redirigir automáticamente
      // Dejar que el AuthContext y PrivateRoute manejen la redirección
      localStorage.removeItem('token');
      localStorage.removeItem('user');
    }
    
    // Si es un error de red (sin respuesta), mostrar mensaje más claro
    if (!error.response) {
      console.error('Error de conexión:', error.message);
    }
    
    return Promise.reject(error);
  }
);

export default api;

