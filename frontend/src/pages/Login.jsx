import React, { useEffect, useMemo, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import Swal from 'sweetalert2';
import { useAuth } from '../context/AuthContext';
import { useColegio } from '../context/ColegioContext';
import './Login.css';

function Login() {
  const [usuario, setUsuario] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [submitting, setSubmitting] = useState(false);

  const { login, isAuthenticated, user } = useAuth();
  const { loading: loadingColegio } = useColegio();
  const navigate = useNavigate();

  // Resolver URL base en tiempo de ejecución (no en compilación)
  const apiBaseUrl = useMemo(() => {
    const hostname = window.location.hostname || '';
    const isLocalhost = hostname === 'localhost' || hostname === '127.0.0.1';
    
    if (isLocalhost) {
      return 'http://localhost:5000';
    }
    // Producción: usar el mismo dominio
    const protocol = window.location.protocol === 'https:' ? 'https:' : 'http:';
    return `${protocol}//${hostname}`;
  }, []); // Solo calcular una vez al montar el componente

  // Logo siempre desde la carpeta local, sin depender de MySQL
  const logoUrl = useMemo(() => {
    const url = `${apiBaseUrl}/assets/logos/logo.png`;
    console.log('🔍 Logo URL:', url); // Debug
    return url;
  }, [apiBaseUrl]);

  useEffect(() => {
    if (isAuthenticated && user) {
      // Redirigir según el tipo de usuario
      if (user.tipo === 'DOCENTE') {
        navigate('/docente/dashboard');
      } else if (user.tipo === 'ALUMNO') {
        navigate('/alumno/dashboard');
      } else if (user.tipo === 'ADMINISTRADOR') {
        navigate('/admin/dashboard');
      } else {
        navigate('/admin/dashboard');
      }
    }
  }, [isAuthenticated, user, navigate]);

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

    setSubmitting(true);
    try {
      const result = await login(usuario, password);
      if (!result?.success) {
        Swal.fire({
          icon: 'error',
          title: 'Error de autenticación',
          text: result?.error || 'No se pudo iniciar sesión',
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 4000,
          timerProgressBar: true,
        });
        return;
      }

      Swal.fire({
        icon: 'success',
        title: '¡Bienvenido!',
        text: `Hola ${result.user?.nombres || result.user?.usuario || usuario}`,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 1800,
        timerProgressBar: true,
      });

      // Redirigir según el tipo de usuario
      if (result.user?.tipo === 'DOCENTE') {
        navigate('/docente/dashboard');
      } else if (result.user?.tipo === 'ALUMNO') {
        navigate('/alumno/dashboard');
      } else if (result.user?.tipo === 'ADMINISTRADOR') {
        navigate('/admin/dashboard');
      } else {
        navigate('/admin/dashboard');
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
      setSubmitting(false);
    }
  };

  return (
    <div className="login-page">
      <div className="login-background" />

      <div className="login-container-simple">
        <div className="login-card-wide">
          {/* Panel Izquierdo: Branding */}
          <div className="login-branding-left">
            <div className="branding-content">
              <div className="brand-logo-main">
                <h1>VanguardNet</h1>
              </div>

              <p className="brand-description">
                Accede a tu aula virtual gamificada, chatea con ASISTENTE IA y lleva tu aprendizaje al siguiente nivel.
              </p>

              <div className="brand-features-list">
                <div className="feature-item">
                  <span className="feature-icon">🎮</span>
                  <span>Aula Virtual Gamificada</span>
                </div>
                <div className="feature-item">
                  <span className="feature-icon">🤖</span>
                  <span>Asistente IA</span>
                </div>
                <div className="feature-item">
                  <span className="feature-icon">📲</span>
                  <span>Aplicación PWA Instalable</span>
                </div>
              </div>
            </div>
          </div>

          {/* Panel Derecho: Formulario */}
          <div className="login-form-right">
            {/* Logo en esquina superior derecha */}
            <div className="form-logo-container">
              <img 
                src={logoUrl} 
                alt="Logo" 
                className="form-logo"
                crossOrigin="anonymous"
                onLoad={() => {
                  console.log('✅ Logo cargado correctamente desde:', logoUrl);
                }}
                onError={(e) => {
                  console.error('❌ Error cargando logo:', logoUrl);
                  console.error('Detalles del error:', e);
                  // Intentar cargar con timestamp para evitar cache
                  const newUrl = `${logoUrl}?t=${Date.now()}`;
                  console.log('🔄 Intentando recargar desde:', newUrl);
                  e.target.src = newUrl;
                }}
              />
            </div>

            <div className="form-header">
              <h2>Iniciar Sesión</h2>
              <p>Ingresa tus credenciales para acceder</p>
            </div>

            <form className="login-form" onSubmit={handleSubmit}>
              <div className="form-group">
                <label htmlFor="dni">DNI</label>
                <div className="input-wrapper">
                  <span className="input-icon">👤</span>
                  <input
                    id="dni"
                    type="text"
                    inputMode="numeric"
                    placeholder="Ej: 12345678"
                    value={usuario}
                    onChange={(e) => setUsuario(e.target.value)}
                    autoComplete="username"
                    disabled={submitting}
                  />
                </div>
              </div>

              <div className="form-group">
                <label htmlFor="password">Contraseña</label>
                <div className="input-wrapper input-wrapper-password">
                  <span className="input-icon">🔒</span>
                  <input
                    id="password"
                    type={showPassword ? 'text' : 'password'}
                    placeholder="Ingresa tu contraseña"
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    autoComplete="current-password"
                    disabled={submitting}
                  />
                  <button
                    className="toggle-password"
                    type="button"
                    onClick={() => setShowPassword((v) => !v)}
                    aria-label={showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'}
                  >
                    {showPassword ? '🙈' : '👁️'}
                  </button>
                </div>
              </div>

              <button className="btn-submit" type="submit" disabled={submitting}>
                {submitting ? (
                  <>
                    <span className="spinner" /> <span>Ingresando...</span>
                  </>
                ) : (
                  <>
                    <span>Iniciar Sesión</span> <span>→</span>
                  </>
                )}
              </button>

              {loadingColegio ? (
                <div className="login-hint">Cargando configuración del colegio...</div>
              ) : null}
            </form>
          </div>
        </div>
      </div>
    </div>
  );
}

export default Login;

