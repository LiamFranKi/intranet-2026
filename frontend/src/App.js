import React from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { ThemeProvider, createTheme } from '@mui/material/styles';
import CssBaseline from '@mui/material/CssBaseline';
import { AuthProvider, useAuth } from './context/AuthContext';
import { ColegioProvider } from './context/ColegioContext';
import Login from './pages/Login';
import Dashboard from './pages/Dashboard';
import AulaVirtual from './pages/AulaVirtual';
import DocenteDashboard from './pages/DocenteDashboard';
import DocentePerfil from './pages/DocentePerfil';
import DocenteGrupos from './pages/DocenteGrupos';
import DocenteCursos from './pages/DocenteCursos';
import DocenteHorario from './pages/DocenteHorario';
import DocenteComunicados from './pages/DocenteComunicados';
import DocenteActividades from './pages/DocenteActividades';
import DocenteMensajes from './pages/DocenteMensajes';
import DocenteAulaVirtual from './pages/DocenteAulaVirtual';
import './App.css';

// Configurar React Router para evitar warnings
const routerConfig = {
  future: {
    v7_startTransition: true,
    v7_relativeSplatPath: true,
  },
};

// Tema de Material-UI
const theme = createTheme({
  palette: {
    primary: {
      main: '#667eea',
    },
    secondary: {
      main: '#764ba2',
    },
  },
});

// Componente para rutas protegidas
function PrivateRoute({ children }) {
  const { isAuthenticated, loading } = useAuth();

  if (loading) {
    return <div>Cargando...</div>;
  }

  return isAuthenticated ? children : <Navigate to="/login" />;
}

// Componente para redirigir al dashboard correcto según el tipo de usuario
function NavigateToDashboard() {
  const { user } = useAuth();
  const tipo = user?.tipo;
  
  if (tipo === 'DOCENTE') {
    return <Navigate to="/docente/dashboard" />;
  }
  
  return <Navigate to="/dashboard" />;
}

function AppRoutes() {
  return (
    <Routes>
      <Route path="/login" element={<Login />} />
      <Route
        path="/dashboard"
        element={
          <PrivateRoute>
            <Dashboard />
          </PrivateRoute>
        }
      />
      <Route
        path="/aula"
        element={
          <PrivateRoute>
            <AulaVirtual />
          </PrivateRoute>
        }
      />
      {/* Placeholder: Auditoría UI se implementará en la siguiente iteración */}
      <Route
        path="/auditoria"
        element={
          <PrivateRoute>
            <Dashboard />
          </PrivateRoute>
        }
      />
      <Route
        path="/docente/dashboard"
        element={
          <PrivateRoute>
            <DocenteDashboard />
          </PrivateRoute>
        }
      />
      <Route
        path="/docente/perfil"
        element={
          <PrivateRoute>
            <DocentePerfil />
          </PrivateRoute>
        }
      />
      <Route
        path="/docente/grupos"
        element={
          <PrivateRoute>
            <DocenteGrupos />
          </PrivateRoute>
        }
      />
      <Route
        path="/docente/cursos"
        element={
          <PrivateRoute>
            <DocenteCursos />
          </PrivateRoute>
        }
      />
      <Route
        path="/docente/horario"
        element={
          <PrivateRoute>
            <DocenteHorario />
          </PrivateRoute>
        }
      />
      <Route
        path="/docente/comunicados"
        element={
          <PrivateRoute>
            <DocenteComunicados />
          </PrivateRoute>
        }
      />
      <Route
        path="/docente/actividades"
        element={
          <PrivateRoute>
            <DocenteActividades />
          </PrivateRoute>
        }
      />
      <Route
        path="/docente/mensajes"
        element={
          <PrivateRoute>
            <DocenteMensajes />
          </PrivateRoute>
        }
      />
      <Route
        path="/docente/cursos/:cursoId/aula"
        element={
          <PrivateRoute>
            <DocenteAulaVirtual />
          </PrivateRoute>
        }
      />
      <Route path="/" element={<NavigateToDashboard />} />
    </Routes>
  );
}

function App() {
  return (
    <ThemeProvider theme={theme}>
      <CssBaseline />
      <AuthProvider>
        <ColegioProvider colegioId={1}>
          <Router
            future={{
              v7_startTransition: true,
              v7_relativeSplatPath: true,
            }}
          >
            <AppRoutes />
          </Router>
        </ColegioProvider>
      </AuthProvider>
    </ThemeProvider>
  );
}

export default App;
