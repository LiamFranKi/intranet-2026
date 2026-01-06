import React from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import DashboardLayout from '../components/DashboardLayout';
import './Dashboard.css';

function Dashboard() {
  const { user } = useAuth();
  const navigate = useNavigate();

  const nombre = user?.nombres || user?.usuario || 'Usuario';

  return (
    <DashboardLayout>
      <div className="dashboard-container">
        <div className="dashboard-welcome">
          <h1>Bienvenido, {nombre}</h1>
          <p>
            Tipo de usuario: <strong>{user?.tipo || 'USUARIO'}</strong> · Colegio ID: <strong>{user?.colegio_id}</strong> · Año activo:{' '}
            <strong>{user?.anio_activo}</strong>
          </p>
        </div>

        <div className="stats-grid">
          <div className="stat-card" style={{ '--card-color': '#667eea' }}>
            <div className="stat-icon">👨‍🎓</div>
            <div className="stat-content">
              <p className="stat-title">Estudiantes</p>
              <div className="stat-value">—</div>
              <p className="stat-subtitle">Por cargar (MySQL)</p>
            </div>
          </div>

          <div className="stat-card" style={{ '--card-color': '#43e97b' }}>
            <div className="stat-icon">👩‍🏫</div>
            <div className="stat-content">
              <p className="stat-title">Docentes</p>
              <div className="stat-value">—</div>
              <p className="stat-subtitle">Por cargar (MySQL)</p>
            </div>
          </div>

          <div className="stat-card" style={{ '--card-color': '#4facfe' }}>
            <div className="stat-icon">📚</div>
            <div className="stat-content">
              <p className="stat-title">Cursos</p>
              <div className="stat-value">—</div>
              <p className="stat-subtitle">Por cargar (MySQL)</p>
            </div>
          </div>

          <div className="stat-card" style={{ '--card-color': '#ff9800' }}>
            <div className="stat-icon">📝</div>
            <div className="stat-content">
              <p className="stat-title">Tareas pendientes</p>
              <div className="stat-value">—</div>
              <p className="stat-subtitle">Por cargar (PostgreSQL)</p>
            </div>
          </div>
        </div>

        <div className="dashboard-grid">
          <div className="dashboard-card">
            <div className="card-header">
              <h2>Actividad Reciente</h2>
            </div>
            <div className="card-body">
              <div className="activity-list">
                <div className="activity-item">
                  <div className="activity-icon">✅</div>
                  <div>
                    <p className="activity-text">Login exitoso</p>
                    <div className="activity-time">Hace unos segundos</div>
                  </div>
                </div>
                <div className="activity-item">
                  <div className="activity-icon">🧾</div>
                  <div>
                    <p className="activity-text">Acciones auditadas automáticamente</p>
                    <div className="activity-time">En tiempo real</div>
                  </div>
                </div>
                <div className="activity-item">
                  <div className="activity-icon">🎓</div>
                  <div>
                    <p className="activity-text">Aula Virtual lista (estructura UI)</p>
                    <div className="activity-time">Listo para conectar datos</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div className="dashboard-card">
            <div className="card-header">
              <h2>Accesos Rápidos</h2>
            </div>
            <div className="card-body">
              <div className="quick-access-grid">
                <div className="quick-access-item" role="button" tabIndex={0} onClick={() => navigate('/aula')}>
                  <div className="quick-icon">🎓</div>
                  <div className="quick-title">Ir al Aula</div>
                </div>
                <div className="quick-access-item" role="button" tabIndex={0} onClick={() => navigate('/auditoria')}>
                  <div className="quick-icon">🧾</div>
                  <div className="quick-title">Ver Auditoría</div>
                </div>
                <div className="quick-access-item" role="button" tabIndex={0} onClick={() => navigate('/dashboard')}>
                  <div className="quick-icon">📊</div>
                  <div className="quick-title">Estadísticas</div>
                </div>
                <div className="quick-access-item" role="button" tabIndex={0} onClick={() => navigate('/aula')}>
                  <div className="quick-icon">📝</div>
                  <div className="quick-title">Tareas</div>
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

