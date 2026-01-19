import React, { useState, useEffect } from 'react';
import DashboardLayout from '../components/DashboardLayout';
import api from '../services/api';
import './DocenteDashboard.css';

function DocenteDashboard() {
  const [loading, setLoading] = useState(true);
  const [dashboardData, setDashboardData] = useState(null);

  useEffect(() => {
    cargarDashboard();
  }, []);

  const cargarDashboard = async () => {
    try {
      setLoading(true);
      const response = await api.get('/docente/dashboard');
      console.log('📊 Dashboard data recibida:', response.data);
      setDashboardData(response.data);
    } catch (error) {
      console.error('❌ Error cargando dashboard:', error);
      console.error('Error details:', error.response?.data || error.message);
      setDashboardData(null);
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return (
      <DashboardLayout>
        <div className="docente-dashboard-loading">
          <div className="loading-spinner">Cargando...</div>
        </div>
      </DashboardLayout>
    );
  }

  if (!dashboardData) {
    return (
      <DashboardLayout>
        <div className="docente-dashboard-error">
          <p>Error al cargar el dashboard</p>
        </div>
      </DashboardLayout>
    );
  }

  const { docente, estadisticas, proximosExamenes, proximasTareas } = dashboardData || {};

  return (
    <DashboardLayout>
      <div className="docente-dashboard">
        {/* Header con bienvenida */}
        <div className="dashboard-header-section">
          <div className="welcome-card">
            <div className="welcome-avatar">
              {docente.foto && docente.foto !== '' ? (
                <img 
                  src={docente.foto.startsWith('http') 
                    ? docente.foto 
                    : `${window.location.protocol}//${window.location.hostname}:5000${docente.foto}`} 
                  alt={docente.nombres} 
                />
              ) : (
                <div className="avatar-placeholder">
                  {docente.nombres?.charAt(0) || 'D'}
                </div>
              )}
            </div>
            <div className="welcome-text">
              <h1>¡Bienvenido, {docente.nombres || 'Docente'}!</h1>
              <p>Gestiona tus cursos, grupos y actividades desde aquí</p>
            </div>
          </div>
        </div>

        {/* Tarjetas de estadísticas */}
        <div className="stats-grid">
          <div className="stat-card mundo-card">
            <div className="stat-icon">📚</div>
            <div className="stat-content">
              <div className="stat-number">{estadisticas?.cursosAsignados || 0}</div>
              <div className="stat-label">Cursos Asignados</div>
            </div>
          </div>

          <div className="stat-card mundo-card">
            <div className="stat-icon">👥</div>
            <div className="stat-content">
              <div className="stat-number">{estadisticas?.estudiantes || 0}</div>
              <div className="stat-label">Estudiantes</div>
            </div>
          </div>
        </div>

        {/* Próximos exámenes */}
        <div className="dashboard-section">
          <h2 className="section-title">📋 Próximos Exámenes</h2>
          {proximosExamenes && Array.isArray(proximosExamenes) && proximosExamenes.length > 0 ? (
            <div className="examenes-list">
              {proximosExamenes.map((examen) => (
                <div key={examen.id} className="examen-card mundo-card">
                  <div className="examen-info">
                    <h3>{examen.titulo || 'Examen'}</h3>
                    <p>
                      {examen.asignatura_nombre || 'Asignatura'} 
                      {examen.nivel_nombre && ` - ${examen.nivel_nombre}`}
                      {examen.grado && ` ${examen.grado}°`}
                      {examen.seccion && ` ${examen.seccion}`}
                    </p>
                    {examen.fecha_desde && (
                      <span className="examen-fecha">
                        {new Date(examen.fecha_desde).toLocaleDateString('es-PE', {
                          day: 'numeric',
                          month: 'long',
                          year: 'numeric'
                        })}
                      </span>
                    )}
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <div className="empty-state">
              <p>No se encontraron exámenes próximos</p>
            </div>
          )}
        </div>

        {/* Próximas tareas */}
        <div className="dashboard-section">
          <h2 className="section-title">📝 Próximas Tareas</h2>
          {proximasTareas && Array.isArray(proximasTareas) && proximasTareas.length > 0 ? (
            <div className="tareas-list">
              {proximasTareas.map((tarea) => (
                <div key={tarea.id} className="tarea-card mundo-card">
                  <div className="tarea-info">
                    <h3>{tarea.descripcion || tarea.titulo || 'Tarea'}</h3>
                    <p>
                      {tarea.asignatura_nombre || 'Asignatura'}
                      {tarea.grado && ` - ${tarea.grado}°`}
                      {tarea.seccion && ` ${tarea.seccion}`}
                    </p>
                    {tarea.fecha_fin && (
                      <span className="tarea-fecha">
                        Fecha límite: {new Date(tarea.fecha_fin).toLocaleDateString('es-PE', {
                          day: 'numeric',
                          month: 'long',
                          year: 'numeric'
                        })}
                      </span>
                    )}
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <div className="empty-state">
              <p>No se encontraron tareas próximas</p>
            </div>
          )}
        </div>
      </div>
    </DashboardLayout>
  );
}

export default DocenteDashboard;



