import React, { useMemo, useState } from 'react';
import { NavLink, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { useColegio } from '../context/ColegioContext';
import { getLogoUrl } from '../utils/theme';
import './DashboardLayout.css';

// Detectar si estamos en desarrollo o producción
const isDevelopment = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
const isProduction = window.location.hostname === 'intranet.vanguardschools.com';

function resolveApiBaseUrl() {
  if (isDevelopment) return 'http://localhost:5000';
  if (isProduction) return window.location.protocol === 'https:' ? 'https://intranet.vanguardschools.com' : 'http://intranet.vanguardschools.com';
  return process.env.REACT_APP_API_URL?.replace('/api', '') || 'http://localhost:5000';
}

function getInitials(nombre) {
  if (!nombre) return 'U';
  const parts = String(nombre).trim().split(/\s+/).slice(0, 2);
  return parts.map((p) => p[0]?.toUpperCase()).join('') || 'U';
}

export default function DashboardLayout({ children }) {
  const { user, logout } = useAuth();
  const { nombreIntranet, logo } = useColegio();
  const navigate = useNavigate();
  const [sidebarOpen, setSidebarOpen] = useState(false);

  const apiBaseUrl = useMemo(() => resolveApiBaseUrl(), []);
  // Logo blanco para el sidebar
  const logoUrl = useMemo(() => `${apiBaseUrl}/assets/logos/logoblanco.png`, [apiBaseUrl]);

  const displayName = user?.nombres || user?.usuario || 'Usuario';
  const initials = getInitials(displayName);
  const role = user?.tipo || 'USUARIO';

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
          <div className="sidebar-logo" role="button" tabIndex={0} onClick={() => navigate('/dashboard')}>
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
              <div className="user-avatar">{initials}</div>
              <div className="user-info">
                <div className="user-name">{displayName}</div>
                <div className="user-role">{role}</div>
              </div>
            </div>
          </div>

          <div className="header-actions">
            <button className="header-btn" type="button" title="Notificaciones">
              🔔
              <span className="notification-badge">3</span>
            </button>
            <button className="header-btn" type="button" title="Ir al Dashboard" onClick={() => navigate('/dashboard')}>
              🏠
            </button>
            <button className="header-btn header-btn-logout" type="button" title="Cerrar sesión" onClick={logout}>
              <svg className="logout-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="12" cy="12" r="9" stroke="#ef4444" strokeWidth="2" fill="none"/>
                <path d="M12 7V12" stroke="#ef4444" strokeWidth="2" strokeLinecap="round"/>
                <path d="M9 9L12 12L15 9" stroke="#ef4444" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
              </svg>
            </button>
          </div>
        </header>

        <main className="dashboard-main">{children}</main>
      </div>
    </div>
  );
}


