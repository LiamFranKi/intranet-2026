import React from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import DashboardLayout from '../components/DashboardLayout';
import './Dashboard.css';

function Dashboard() {
  const { user } = useAuth();
  const navigate = useNavigate();

  const nombreCompleto = `${user?.nombres || ''} ${user?.apellidos || ''}`.trim() || user?.usuario || 'Usuario';

  return (
    <DashboardLayout>
      <div className="dashboard-container">
        {/* Sección de Bienvenida */}
        <div className="dashboard-welcome">
          <h1>¡Bienvenido de vuelta, {nombreCompleto}! 👋</h1>
          <p>Aquí tienes un resumen de tu sistema educativo</p>
        </div>

        {/* Stat Cards */}
        <div className="stats-grid">
          <div className="stat-card" style={{ '--card-color': '#667eea' }}>
            <div className="stat-icon">👨‍🎓</div>
            <div className="stat-content">
              <h3 className="stat-title">Total Alumnos</h3>
              <div className="stat-value">—</div>
            </div>
          </div>

          <div className="stat-card" style={{ '--card-color': '#f093fb' }}>
            <div className="stat-icon">👨‍🏫</div>
            <div className="stat-content">
              <h3 className="stat-title">Total Docentes</h3>
              <div className="stat-value">—</div>
            </div>
          </div>

          <div className="stat-card" style={{ '--card-color': '#4facfe' }}>
            <div className="stat-icon">📚</div>
            <div className="stat-content">
              <h3 className="stat-title">Total Grados</h3>
              <div className="stat-value">—</div>
            </div>
          </div>

          <div className="stat-card" style={{ '--card-color': '#43e97b' }}>
            <div className="stat-icon">💰</div>
            <div className="stat-content">
              <h3 className="stat-title">Total Ingresos</h3>
              <div className="stat-value">—</div>
            </div>
          </div>
        </div>

        {/* Cards Principales */}
        <div className="dashboard-grid">
          {/* Actividad Reciente */}
          <div className="dashboard-card">
            <div className="card-header">
              <h2>📋 Actividad Reciente</h2>
            </div>
            <div className="card-body">
              <div className="activity-list">
                <div className="activity-item">
                  <span className="activity-icon">✅</span>
                  <div className="activity-content">
                    <p className="activity-text">Login exitoso</p>
                    <span className="activity-time">Hace unos segundos</span>
                  </div>
                </div>
                <div className="activity-item">
                  <span className="activity-icon">🧾</span>
                  <div className="activity-content">
                    <p className="activity-text">Acciones auditadas automáticamente</p>
                    <span className="activity-time">En tiempo real</span>
                  </div>
                </div>
                <div className="activity-item">
                  <span className="activity-icon">🎓</span>
                  <div className="activity-content">
                    <p className="activity-text">Aula Virtual lista (estructura UI)</p>
                    <span className="activity-time">Listo para conectar datos</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {/* Accesos Rápidos */}
          <div className="dashboard-card">
            <div className="card-header">
              <h2>🚀 Accesos Rápidos</h2>
            </div>
            <div className="card-body">
              <div className="quick-access-grid">
                <div className="quick-access-item" role="button" tabIndex={0} onClick={() => navigate('/aula')}>
                  <span className="quick-icon">🎓</span>
                  <span className="quick-title">Aula Virtual</span>
                </div>
                <div className="quick-access-item" role="button" tabIndex={0} onClick={() => navigate('/auditoria')}>
                  <span className="quick-icon">📊</span>
                  <span className="quick-title">Reportes</span>
                </div>
                <div className="quick-access-item" role="button" tabIndex={0} onClick={() => navigate('/dashboard')}>
                  <span className="quick-icon">⚙️</span>
                  <span className="quick-title">Configuración</span>
                </div>
                <div className="quick-access-item" role="button" tabIndex={0} onClick={() => navigate('/aula')}>
                  <span className="quick-icon">👥</span>
                  <span className="quick-title">Ver Alumnos</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </DashboardLayout>
  );
}

export default Dashboard;

