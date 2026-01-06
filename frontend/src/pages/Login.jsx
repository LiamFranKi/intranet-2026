import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { useColegio } from '../context/ColegioContext';
import Swal from 'sweetalert2';
import {
  Container,
  Paper,
  TextField,
  Button,
  Typography,
  Box,
  CircularProgress,
  InputAdornment,
  IconButton,
  Grid,
  Card,
  CardContent,
} from '@mui/material';
import {
  Visibility,
  VisibilityOff,
  Person,
  Lock,
  Quiz,
  Assignment,
  Book,
  Assessment,
  Security,
  Speed,
} from '@mui/icons-material';
import { getLogoUrl } from '../utils/theme';

// Detectar si estamos en desarrollo
const isDevelopment = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
const apiBaseUrl = isDevelopment 
  ? 'http://localhost:5000'
  : (process.env.REACT_APP_API_URL?.replace('/api', '') || 'https://intranet.vanguardschools.com');
import './Login.css';

function Login() {
  const [usuario, setUsuario] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [loading, setLoading] = useState(false);
  const [loadingColegio, setLoadingColegio] = useState(true);
  const { login, isAuthenticated } = useAuth();
  const { colegioData, nombreIntranet, logo, loading: loadingColegioData } = useColegio(1); // colegio_id = 1 por defecto
  const navigate = useNavigate();

  useEffect(() => {
    // Si ya está autenticado, redirigir
    if (isAuthenticated) {
      navigate('/dashboard');
    }
    // Cuando termine de cargar datos del colegio
    if (!loadingColegioData) {
      setLoadingColegio(false);
    }
  }, [isAuthenticated, navigate, loadingColegioData]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    if (!usuario || !password) {
      Swal.fire({
        icon: 'warning',
        title: 'Campos requeridos',
        text: 'Por favor ingresa tu DNI y contraseña',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
      });
      return;
    }

    setLoading(true);

    try {
      const result = await login(usuario, password);

      if (result.success) {
        Swal.fire({
          icon: 'success',
          title: '¡Bienvenido!',
          text: `Hola ${result.user.nombres || result.user.usuario}`,
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 2000,
          timerProgressBar: true,
        });

        // Redirigir según tipo de usuario
        setTimeout(() => {
          navigate('/dashboard');
        }, 500);
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error de autenticación',
          text: result.error,
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 4000,
          timerProgressBar: true,
        });
      }
    } catch (error) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Error al conectar con el servidor',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true,
      });
    } finally {
      setLoading(false);
    }
  };

  const logoUrl = logo ? getLogoUrl(logo, `${apiBaseUrl}/api`) : null;

  if (loadingColegio) {
    return (
      <Box className="login-container" sx={{ display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
        <CircularProgress sx={{ color: 'white' }} />
      </Box>
    );
  }

  return (
    <Box className="login-container">
      <Container maxWidth="lg" className="login-wrapper">
        <Grid container spacing={3} alignItems="center">
          {/* Sección de Información / Landing */}
          <Grid item xs={12} md={6} className="login-info-section">
            <Box className="login-info-content">
              <Box className="login-info-header">
                {logoUrl ? (
                  <img src={logoUrl} alt={nombreIntranet} className="login-logo" />
                ) : (
                  <Box className="login-logo-placeholder" />
                )}
                <Typography variant="h3" component="h1" className="login-info-title">
                  {nombreIntranet || 'Aula Virtual'}
                </Typography>
                <Typography variant="h6" className="login-info-subtitle">
                  Tu plataforma educativa completa
                </Typography>
              </Box>

              <Box className="login-features">
                <Card className="feature-card">
                  <CardContent>
                    <Box className="feature-item">
                      <Quiz className="feature-icon" />
                      <Box>
                        <Typography variant="h6" className="feature-title">
                          Exámenes en Línea
                        </Typography>
                        <Typography variant="body2" color="text.secondary">
                          Realiza exámenes interactivos con calificación automática
                        </Typography>
                      </Box>
                    </Box>
                  </CardContent>
                </Card>

                <Card className="feature-card">
                  <CardContent>
                    <Box className="feature-item">
                      <Assignment className="feature-icon" />
                      <Box>
                        <Typography variant="h6" className="feature-title">
                          Gestión de Tareas
                        </Typography>
                        <Typography variant="body2" color="text.secondary">
                          Asigna y entrega tareas de forma sencilla y organizada
                        </Typography>
                      </Box>
                    </Box>
                  </CardContent>
                </Card>

                <Card className="feature-card">
                  <CardContent>
                    <Box className="feature-item">
                      <Book className="feature-icon" />
                      <Box>
                        <Typography variant="h6" className="feature-title">
                          Contenido Interactivo
                        </Typography>
                        <Typography variant="body2" color="text.secondary">
                          Accede a temas, videos y materiales de estudio
                        </Typography>
                      </Box>
                    </Box>
                  </CardContent>
                </Card>

                <Card className="feature-card">
                  <CardContent>
                    <Box className="feature-item">
                      <Assessment className="feature-icon" />
                      <Box>
                        <Typography variant="h6" className="feature-title">
                          Calificaciones
                        </Typography>
                        <Typography variant="body2" color="text.secondary">
                          Consulta tus notas y seguimiento académico
                        </Typography>
                      </Box>
                    </Box>
                  </CardContent>
                </Card>
              </Box>

              <Box className="login-benefits">
                <Box className="benefit-item">
                  <Security className="benefit-icon" />
                  <Typography variant="body2">Acceso seguro y protegido</Typography>
                </Box>
                <Box className="benefit-item">
                  <Speed className="benefit-icon" />
                  <Typography variant="body2">Rápido y fácil de usar</Typography>
                </Box>
              </Box>
            </Box>
          </Grid>

          {/* Sección de Login */}
          <Grid item xs={12} md={6}>
            <Paper elevation={10} className="login-paper">
              <Box className="login-header">
                {logoUrl ? (
                  <img src={logoUrl} alt={nombreIntranet} className="login-icon-img" />
                ) : (
                  <Box className="login-icon-placeholder" />
                )}
                <Typography variant="h4" component="h1" className="login-title">
                  Iniciar Sesión
                </Typography>
                <Typography variant="body2" color="text.secondary">
                  Ingresa con tu DNI y contraseña
                </Typography>
              </Box>

              <form onSubmit={handleSubmit} className="login-form">
                <TextField
                  fullWidth
                  label="DNI"
                  variant="outlined"
                  value={usuario}
                  onChange={(e) => setUsuario(e.target.value)}
                  margin="normal"
                  required
                  autoComplete="username"
                  className="login-input"
                  sx={{
                    '& .MuiOutlinedInput-root': {
                      borderRadius: '12px',
                    },
                  }}
                  InputProps={{
                    startAdornment: (
                      <InputAdornment position="start">
                        <Person />
                      </InputAdornment>
                    ),
                  }}
                />

                <TextField
                  fullWidth
                  label="Contraseña"
                  type={showPassword ? 'text' : 'password'}
                  variant="outlined"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  margin="normal"
                  required
                  autoComplete="current-password"
                  className="login-input"
                  sx={{
                    '& .MuiOutlinedInput-root': {
                      borderRadius: '12px',
                    },
                  }}
                  InputProps={{
                    startAdornment: (
                      <InputAdornment position="start">
                        <Lock />
                      </InputAdornment>
                    ),
                    endAdornment: (
                      <InputAdornment position="end">
                        <IconButton
                          onClick={() => setShowPassword(!showPassword)}
                          edge="end"
                        >
                          {showPassword ? <VisibilityOff /> : <Visibility />}
                        </IconButton>
                      </InputAdornment>
                    ),
                  }}
                />

                <Button
                  type="submit"
                  fullWidth
                  variant="contained"
                  size="large"
                  disabled={loading}
                  className="login-button"
                  sx={{ 
                    mt: 3, 
                    mb: 2,
                    borderRadius: '12px',
                    padding: '14px',
                    fontSize: '16px',
                    fontWeight: 600,
                  }}
                >
                  {loading ? (
                    <CircularProgress size={24} color="inherit" />
                  ) : (
                    'Iniciar Sesión'
                  )}
                </Button>
              </form>

              <Box className="login-footer">
                <Typography variant="caption" color="text.secondary">
                  Sistema de Aula Virtual © 2025
                </Typography>
              </Box>
            </Paper>
          </Grid>
        </Grid>
      </Container>
    </Box>
  );
}

export default Login;

