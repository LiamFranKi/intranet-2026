import React from 'react';
import { useAuth } from '../context/AuthContext';
import { Container, Typography, Box, Paper } from '@mui/material';
import { useNavigate } from 'react-router-dom';

function Dashboard() {
  const { user, logout } = useAuth();
  const navigate = useNavigate();

  if (!user) {
    return <div>Cargando...</div>;
  }

  return (
    <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }}>
      <Paper sx={{ p: 4 }}>
        <Box sx={{ mb: 3 }}>
          <Typography variant="h4" component="h1" gutterBottom>
            Bienvenido, {user.nombres || user.usuario}
          </Typography>
          <Typography variant="body1" color="text.secondary">
            Tipo de usuario: {user.tipo}
          </Typography>
          <Typography variant="body2" color="text.secondary">
            Colegio ID: {user.colegio_id} | Año activo: {user.anio_activo}
          </Typography>
        </Box>

        <Box sx={{ mt: 4 }}>
          <button onClick={logout}>Cerrar Sesión</button>
        </Box>
      </Paper>
    </Container>
  );
}

export default Dashboard;

