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
  SportsEsports,
  SmartToy,
  GetApp,
  ArrowBack,
  ArrowForward,
} from '@mui/icons-material';
import { getLogoUrl } from '../utils/theme';

// Detectar si estamos en desarrollo o producción
const isDevelopment = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
const isProduction = window.location.hostname === 'intranet.vanguardschools.com';

// Determinar URL base de API para logos
let apiBaseUrl;
if (isDevelopment) {
  apiBaseUrl = 'http://localhost:5000';
} else if (isProduction) {
  apiBaseUrl = window.location.protocol === 'https:' 
    ? 'https://intranet.vanguardschools.com'
    : 'http://intranet.vanguardschools.com';
} else {
  apiBaseUrl = process.env.REACT_APP_API_URL?.replace('/api', '') || 'http://localhost:5000';
}
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
        <Paper elevation={0} className="login-main-card">
          <Grid container>
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
                    {nombreIntranet?.toUpperCase() || 'AULA VIRTUAL'}
                  </Typography>
                </Box>

                <Typography variant="body1" className="login-description">
                  Accede a tu aula virtual gamificada, chatea con ASISTENTE IA y lleva tu aprendizaje al siguiente nivel.
                </Typography>

                <Box className="login-features">
                  <Card className="feature-card">
                    <CardContent className="feature-card-content">
                      <SportsEsports className="feature-icon feature-icon-purple" />
                      <Typography variant="body1" className="feature-text">
                        Aula Virtual Gamificada
                      </Typography>
                    </CardContent>
                  </Card>

                  <Card className="feature-card">
                    <CardContent className="feature-card-content">
                      <SmartToy className="feature-icon feature-icon-red" />
                      <Typography variant="body1" className="feature-text">
                        Asistente IA
                      </Typography>
                    </CardContent>
                  </Card>

                  <Card className="feature-card">
                    <CardContent className="feature-card-content">
                      <GetApp className="feature-icon feature-icon-multicolor" />
                      <Typography variant="body1" className="feature-text">
                        Aplicación PWA Instalable
                      </Typography>
                    </CardContent>
                  </Card>
                </Box>
              </Box>
            </Grid>

            {/* Sección de Login */}
            <Grid item xs={12} md={6} className="login-form-section">
              <Box className="login-form-wrapper">
                <Button
                  startIcon={<ArrowBack />}
                  className="back-button"
                  onClick={() => window.location.href = '/'}
                >
                  Volver al Inicio
                </Button>

                <Box className="login-header">
                  <Typography variant="h3" component="h1" className="login-title">
                    Iniciar Sesión
                  </Typography>
                  <Typography variant="body2" className="login-subtitle">
                    Ingresa tus credenciales para acceder
                  </Typography>
                </Box>

                <form onSubmit={handleSubmit} className="login-form">
                  <TextField
                    fullWidth
                    label="DNI"
                    placeholder="Ej: 12345678"
                    variant="outlined"
                    value={usuario}
                    onChange={(e) => setUsuario(e.target.value)}
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
                          <Person sx={{ color: '#667eea' }} />
                        </InputAdornment>
                      ),
                    }}
                  />

                  <TextField
                    fullWidth
                    label="Contraseña"
                    placeholder="Ingresa tu contraseña"
                    type={showPassword ? 'text' : 'password'}
                    variant="outlined"
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
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
                          <Lock sx={{ color: '#f59e0b' }} />
                        </InputAdornment>
                      ),
                      endAdornment: (
                        <InputAdornment position="end">
                          <IconButton
                            onClick={() => setShowPassword(!showPassword)}
                            edge="end"
                            sx={{ color: '#667eea' }}
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
                    endIcon={!loading && <ArrowForward />}
                    sx={{ 
                      mt: 3,
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
              </Box>
            </Grid>
          </Grid>
        </Paper>
      </Container>
    </Box>
  );
}

export default Login;

