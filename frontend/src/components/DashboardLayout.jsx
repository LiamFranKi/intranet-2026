import React, { useMemo, useState } from 'react';
import { NavLink, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import CalendarioWidget from './CalendarioWidget';
import PublicacionesWidget from './PublicacionesWidget';
import NotificacionesWidget from './NotificacionesWidget';
import './DashboardLayout.css';

function getInitials(nombre) {
  if (!nombre) return 'U';
  const parts = String(nombre).trim().split(/\s+/).slice(0, 2);
  return parts.map((p) => p[0]?.toUpperCase()).join('') || 'U';
}

export default function DashboardLayout({ children }) {
  const { user, logout } = useAuth();
  const navigate = useNavigate();
  const [sidebarOpen, setSidebarOpen] = useState(false);

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

  // Logo blanco para el sidebar
  const logoUrl = useMemo(() => `${apiBaseUrl}/assets/logos/logoblanco.png`, [apiBaseUrl]);

  // Manejar nombres para alumnos (tienen apellido_paterno y apellido_materno separados)
  let displayName = '';
  if (user?.tipo === 'ALUMNO') {
    displayName = user?.nombres && user?.apellido_paterno 
      ? `${user.nombres} ${user.apellido_paterno}${user.apellido_materno ? ' ' + user.apellido_materno : ''}`
      : user?.nombres || user?.usuario || 'Alumno';
  } else {
    displayName = user?.nombres && user?.apellidos 
      ? `${user.nombres} ${user.apellidos}` 
      : user?.nombres || user?.usuario || 'Usuario';
  }
  const initials = getInitials(displayName);
  const role = user?.tipo || 'USUARIO';
  
  // Construir URL de la foto del usuario
  // La foto debería venir como URL completa del backend, pero manejamos todos los casos por compatibilidad
  const userFotoUrl = useMemo(() => {
    if (!user?.foto) return null;
    
    // Si ya es una URL completa (http/https), usarla directamente
    if (user.foto.startsWith('http')) {
      return user.foto;
    }
    
    // Si es una ruta relativa que empieza con /, construir URL completa
    if (user.foto.startsWith('/')) {
      return `${window.location.protocol}//${window.location.hostname}:5000${user.foto}`;
    }
    
    // Si solo tenemos el nombre del archivo (caso legacy o localStorage antiguo)
    // Para alumnos usar /uploads/alumnos/, para docentes /uploads/personal/
    const fotoNombre = user.foto_nombre || user.foto;
    const uploadPath = role === 'ALUMNO' ? 'uploads/alumnos' : 'uploads/personal';
    return `${window.location.protocol}//${window.location.hostname}:5000/${uploadPath}/${fotoNombre}`;
  }, [user?.foto, user?.foto_nombre, role]);

  return (
    <div className="dashboard-layout">
      <div
        className={`dashboard-overlay ${sidebarOpen ? 'open' : ''}`}
        role="button"
        tabIndex={0}
        onClick={() => setSidebarOpen(false)}
        onKeyDown={(e) => {
          if (e.key === 'Enter' || e.key === ' ') setSidebarOpen(false);
        }}
      />

      <aside className={`dashboard-sidebar ${sidebarOpen ? 'open' : ''}`}>
        <div className="sidebar-header">
          <div className="sidebar-logo" role="button" tabIndex={0} onClick={() => navigate(role === 'DOCENTE' ? '/docente/dashboard' : role === 'ALUMNO' ? '/alumno/dashboard' : '/dashboard')}>
            {logoUrl ? (
              <img className="sidebar-logo-image" src={logoUrl} alt="Logo" />
            ) : (
              <div className="sidebar-logo-fallback">🏫</div>
            )}
            <div className="logo-text">VanguardNet</div>
          </div>

          <button className="sidebar-close-btn" type="button" onClick={() => setSidebarOpen(false)} aria-label="Cerrar menú">
            ✕
          </button>
        </div>

        <nav className="sidebar-nav">
          {role === 'DOCENTE' ? (
            <>
              {/* MENÚ PRINCIPAL */}
              <div className="sidebar-section">
                <div className="sidebar-section-title">MENÚ PRINCIPAL</div>
                <div className="sidebar-item">
                  <NavLink className={({ isActive }) => `sidebar-link ${isActive ? 'active' : ''}`} to="/docente/dashboard" end>
                    <span className="sidebar-icon">📊</span>
                    <span className="sidebar-label">Dashboard</span>
                  </NavLink>
                </div>
                <div className="sidebar-item">
                  <NavLink className={({ isActive }) => `sidebar-link ${isActive ? 'active' : ''}`} to="/docente/perfil">
                    <span className="sidebar-icon">👤</span>
                    <span className="sidebar-label">Mi Perfil</span>
                  </NavLink>
                </div>
                <div className="sidebar-item">
                  <NavLink className={({ isActive }) => `sidebar-link ${isActive ? 'active' : ''}`} to="/docente/grupos">
                    <span className="sidebar-icon">👥</span>
                    <span className="sidebar-label">Grupos Asignados</span>
                  </NavLink>
                </div>
                <div className="sidebar-item">
                  <NavLink className={({ isActive }) => `sidebar-link ${isActive ? 'active' : ''}`} to="/docente/cursos">
                    <span className="sidebar-icon">📚</span>
                    <span className="sidebar-label">Cursos Asignados</span>
                  </NavLink>
                </div>
                <div className="sidebar-item">
                  <NavLink className={({ isActive }) => `sidebar-link ${isActive ? 'active' : ''}`} to="/docente/horario">
                    <span className="sidebar-icon">📅</span>
                    <span className="sidebar-label">Mi Horario</span>
                  </NavLink>
                </div>
              </div>

              {/* COMUNICADOS */}
              <div className="sidebar-section">
                <div className="sidebar-section-title">COMUNICADOS</div>
                <div className="sidebar-item">
                  <NavLink className={({ isActive }) => `sidebar-link ${isActive ? 'active' : ''}`} to="/docente/comunicados">
                    <span className="sidebar-icon">📢</span>
                    <span className="sidebar-label">Comunicados</span>
                  </NavLink>
                </div>
              </div>

              {/* CALENDARIO */}
              <div className="sidebar-section">
                <div className="sidebar-section-title">CALENDARIO</div>
                <div className="sidebar-item">
                  <NavLink className={({ isActive }) => `sidebar-link ${isActive ? 'active' : ''}`} to="/docente/actividades">
                    <span className="sidebar-icon">📆</span>
                    <span className="sidebar-label">Actividades</span>
                  </NavLink>
                </div>
              </div>

              {/* MENSAJERÍA */}
              <div className="sidebar-section">
                <div className="sidebar-section-title">MENSAJERÍA</div>
                <div className="sidebar-item">
                  <NavLink className={({ isActive }) => `sidebar-link ${isActive ? 'active' : ''}`} to="/docente/mensajes">
                    <span className="sidebar-icon">✉️</span>
                    <span className="sidebar-label">Mensajes</span>
                  </NavLink>
                </div>
              </div>
            </>
          ) : role === 'ALUMNO' ? (
            <>
              {/* MENÚ PRINCIPAL */}
              <div className="sidebar-section">
                <div className="sidebar-section-title">MENÚ PRINCIPAL</div>
                <div className="sidebar-item">
                  <NavLink className={({ isActive }) => `sidebar-link ${isActive ? 'active' : ''}`} to="/alumno/dashboard" end>
                    <span className="sidebar-icon">📊</span>
                    <span className="sidebar-label">Dashboard</span>
                  </NavLink>
                </div>
                <div className="sidebar-item">
                  <NavLink className={({ isActive }) => `sidebar-link ${isActive ? 'active' : ''}`} to="/alumno/perfil">
                    <span className="sidebar-icon">👤</span>
                    <span className="sidebar-label">Mi Perfil</span>
                  </NavLink>
                </div>
                <div className="sidebar-item">
                  <NavLink className={({ isActive }) => `sidebar-link ${isActive ? 'active' : ''}`} to="/alumno/cursos">
                    <span className="sidebar-icon">📚</span>
                    <span className="sidebar-label">Mis Cursos</span>
                  </NavLink>
                </div>
                <div className="sidebar-item">
                  <NavLink className={({ isActive }) => `sidebar-link ${isActive ? 'active' : ''}`} to="/alumno/calificaciones">
                    <span className="sidebar-icon">📊</span>
                    <span className="sidebar-label">Calificaciones</span>
                  </NavLink>
                </div>
                <div className="sidebar-item">
                  <NavLink className={({ isActive }) => `sidebar-link ${isActive ? 'active' : ''}`} to="/alumno/horario">
                    <span className="sidebar-icon">📅</span>
                    <span className="sidebar-label">Mi Horario</span>
                  </NavLink>
                </div>
              </div>

              {/* AULA VIRTUAL */}
              <div className="sidebar-section">
                <div className="sidebar-section-title">AULA VIRTUAL</div>
                <div className="sidebar-item">
                  <NavLink className={({ isActive }) => `sidebar-link ${isActive ? 'active' : ''}`} to="/alumno/aula-virtual">
                    <span className="sidebar-icon">🌍</span>
                    <span className="sidebar-label">Mundos</span>
                  </NavLink>
                </div>
              </div>

              {/* COMUNICADOS */}
              <div className="sidebar-section">
                <div className="sidebar-section-title">COMUNICADOS</div>
                <div className="sidebar-item">
                  <NavLink className={({ isActive }) => `sidebar-link ${isActive ? 'active' : ''}`} to="/alumno/comunicados">
                    <span className="sidebar-icon">📢</span>
                    <span className="sidebar-label">Comunicados</span>
                  </NavLink>
                </div>
              </div>

              {/* CALENDARIO */}
              <div className="sidebar-section">
                <div className="sidebar-section-title">CALENDARIO</div>
                <div className="sidebar-item">
                  <NavLink className={({ isActive }) => `sidebar-link ${isActive ? 'active' : ''}`} to="/alumno/actividades">
                    <span className="sidebar-icon">📆</span>
                    <span className="sidebar-label">Actividades</span>
                  </NavLink>
                </div>
              </div>

              {/* MENSAJERÍA */}
              <div className="sidebar-section">
                <div className="sidebar-section-title">MENSAJERÍA</div>
                <div className="sidebar-item">
                  <NavLink className={({ isActive }) => `sidebar-link ${isActive ? 'active' : ''}`} to="/alumno/mensajes">
                    <span className="sidebar-icon">✉️</span>
                    <span className="sidebar-label">Mensajes</span>
                  </NavLink>
                </div>
              </div>
            </>
          ) : (
            <>
              <div className="sidebar-item">
                <NavLink className={({ isActive }) => `sidebar-link ${isActive ? 'active' : ''}`} to="/dashboard" end>
                  <span className="sidebar-icon">📊</span>
                  <span className="sidebar-label">Dashboard</span>
                </NavLink>
              </div>
              <div className="sidebar-item">
                <NavLink className={({ isActive }) => `sidebar-link ${isActive ? 'active' : ''}`} to="/aula">
                  <span className="sidebar-icon">🎓</span>
                  <span className="sidebar-label">Aula Virtual</span>
                </NavLink>
              </div>
              <div className="sidebar-item">
                <NavLink className={({ isActive }) => `sidebar-link ${isActive ? 'active' : ''}`} to="/auditoria">
                  <span className="sidebar-icon">🧾</span>
                  <span className="sidebar-label">Auditoría</span>
                </NavLink>
              </div>
            </>
          )}

          <div className="sidebar-item">
            <button className="sidebar-link sidebar-link-button" type="button" onClick={logout}>
              <span className="sidebar-icon">⏻</span>
              <span className="sidebar-label">Cerrar sesión</span>
            </button>
          </div>
        </nav>
      </aside>

      <div className="dashboard-content">
        <header className="dashboard-header">
          <button className="header-menu-btn" type="button" onClick={() => setSidebarOpen(true)} aria-label="Abrir menú">
            ☰
          </button>

          <div className="header-left">
            <div className="header-user">
              <div className="user-avatar">
                {userFotoUrl ? (
                  <img 
                    src={userFotoUrl} 
                    alt={displayName}
                    onError={(e) => {
                      e.target.style.display = 'none';
                      const span = e.target.parentElement.querySelector('span');
                      if (span) span.style.display = 'flex';
                    }}
                  />
                ) : null}
                <span style={{ display: userFotoUrl ? 'none' : 'flex' }}>{initials}</span>
              </div>
              <div className="user-info">
                <div className="user-name">{displayName}</div>
                <div className="user-role">{role}</div>
              </div>
            </div>
          </div>

          <div className="header-actions">
            {(role === 'DOCENTE' || role === 'ALUMNO') && <NotificacionesWidget />}
            <button className="header-btn" type="button" title="Mi Perfil" onClick={() => navigate(role === 'DOCENTE' ? '/docente/perfil' : role === 'ALUMNO' ? '/alumno/perfil' : '/perfil')}>
              👤
            </button>
            <button className="header-btn header-btn-logout" type="button" title="Cerrar sesión" onClick={logout}>
              <svg className="logout-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="12" cy="12" r="9" stroke="#ef4444" strokeWidth="2" fill="none"/>
                <line x1="12" y1="3" x2="12" y2="12" stroke="#ef4444" strokeWidth="2" strokeLinecap="round"/>
              </svg>
            </button>
          </div>
        </header>

        <div className="dashboard-main-wrapper">
          <main className="dashboard-main">{children}</main>
          
          {(role === 'DOCENTE' || role === 'ALUMNO') && (
            <aside className="dashboard-sidebar-right">
              <CalendarioWidget />
              {role === 'DOCENTE' && <PublicacionesWidget />}
            </aside>
          )}
        </div>
      </div>
    </div>
  );
}


