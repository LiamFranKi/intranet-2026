import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
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
} from '@mui/material';
import {
  Visibility,
  VisibilityOff,
  Person,
  Lock,
  School,
} from '@mui/icons-material';
import './Login.css';

function Login() {
  const [usuario, setUsuario] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [loading, setLoading] = useState(false);
  const { login, isAuthenticated } = useAuth();
  const navigate = useNavigate();

  useEffect(() => {
    // Si ya está autenticado, redirigir
    if (isAuthenticated) {
      navigate('/dashboard');
    }
  }, [isAuthenticated, navigate]);

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

  return (
    <Box className="login-container">
      <Container maxWidth="sm" className="login-wrapper">
        <Paper elevation={10} className="login-paper">
          <Box className="login-header">
            <School className="login-icon" />
            <Typography variant="h4" component="h1" className="login-title">
              Aula Virtual
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
              sx={{ mt: 3, mb: 2 }}
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
              Sistema de Aula Virtual
            </Typography>
          </Box>
        </Paper>
      </Container>
    </Box>
  );
}

export default Login;

