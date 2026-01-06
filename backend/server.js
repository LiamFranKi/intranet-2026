require('dotenv').config();
const express = require('express');
const cors = require('cors');
const helmet = require('helmet');
const rateLimit = require('express-rate-limit');
const fs = require('fs');
const path = require('path');

const app = express();

// Leer puerto del archivo o usar default
let PORT = process.env.PORT || 5000;
try {
  const portFile = path.join(__dirname, '.port');
  if (fs.existsSync(portFile)) {
    PORT = parseInt(fs.readFileSync(portFile, 'utf8'));
  }
} catch (error) {
  console.log('Usando puerto por defecto:', PORT);
}

// Rate limiting
const limiter = rateLimit({
  windowMs: 15 * 60 * 1000, // 15 minutos
  max: 100 // máximo 100 requests
});

// Middleware
app.use(helmet());
app.use(cors({
  origin: process.env.FRONTEND_URL || 'http://localhost:3000',
  credentials: true
}));
app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true, limit: '10mb' }));
app.use(limiter);

// Middleware de auditoría (registra todas las acciones)
const middlewareAuditoria = require('./middleware/auditoria');
app.use(middlewareAuditoria);

// Servir archivos estáticos (logos, assets)
app.use('/assets', express.static(path.join(__dirname, 'public', 'assets')));

// Routes
app.get('/api/health', (req, res) => {
  res.json({ 
    status: 'OK', 
    message: 'Aula Virtual API',
    port: PORT,
    timestamp: new Date().toISOString()
  });
});

// Rutas del colegio
const colegioRoutes = require('./routes/colegio.routes');
app.use('/api/colegio', colegioRoutes);

// Rutas de autenticación
const authRoutes = require('./routes/auth.routes');
app.use('/api/auth', authRoutes);

// Rutas de auditoría
const auditoriaRoutes = require('./routes/auditoria.routes');
app.use('/api/auditoria', auditoriaRoutes);

// Rutas de prueba (solo desarrollo)
if (process.env.NODE_ENV === 'development') {
  const testRoutes = require('./routes/test.routes');
  app.use('/api/test', testRoutes);
}

// 404
app.use((req, res) => {
  res.status(404).json({ error: 'Ruta no encontrada' });
});

// Error handler
app.use((err, req, res, next) => {
  console.error('Error:', err);
  res.status(500).json({ error: 'Error interno del servidor' });
});

// Start server
app.listen(PORT, () => {
  console.log(`\n✅ Servidor corriendo en puerto ${PORT}`);
  console.log(`🌐 URL: http://localhost:${PORT}`);
  console.log(`📡 Health: http://localhost:${PORT}/api/health\n`);
});
