import React, { useState, useEffect } from 'react';
import DashboardLayout from '../components/DashboardLayout';
import api from '../services/api';
import './AlumnoDashboard.css';

// Función para obtener iniciales del nombre
function getInitials(nombre) {
  if (!nombre) return 'A';
  const parts = String(nombre).trim().split(/\s+/).slice(0, 2);
  return parts.map((p) => p[0]?.toUpperCase()).join('') || 'A';
}

function AlumnoDashboard() {
  const [loading, setLoading] = useState(true);
  const [dashboardData, setDashboardData] = useState(null);

  useEffect(() => {
    cargarDashboard();
  }, []);

  const cargarDashboard = async () => {
    try {
      setLoading(true);
      const response = await api.get('/alumno/dashboard');
      console.log('📊 Dashboard data recibida:', response.data);
      setDashboardData(response.data);
    } catch (error) {
      console.error('Error cargando dashboard:', error);
      setDashboardData(null);
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return (
      <DashboardLayout>
        <div className="alumno-dashboard-loading">
          <div className="loading-spinner">Cargando...</div>
        </div>
      </DashboardLayout>
    );
  }

  if (!dashboardData) {
    return (
      <DashboardLayout>
        <div className="alumno-dashboard-error">
          <p>Error al cargar el dashboard</p>
        </div>
      </DashboardLayout>
    );
  }

  const { alumno, matricula, estadisticas, asignaturas, proximosExamenes, proximasTareas, proximasActividades } = dashboardData;
  
  // Combinar nombre completo del alumno
  const nombreCompleto = alumno?.nombres && alumno?.apellido_paterno 
    ? `${alumno.nombres} ${alumno.apellido_paterno}${alumno.apellido_materno ? ' ' + alumno.apellido_materno : ''}`
    : alumno?.nombres || 'Alumno';

  // Combinar exámenes y tareas para "Próximos Eventos"
  const proximosEventos = [
    ...(proximosExamenes || []).map(e => ({ ...e, tipo: 'examen' })),
    ...(proximasTareas || []).map(t => ({ ...t, tipo: 'tarea' }))
  ].sort((a, b) => {
    const fechaA = a.fecha_desde || a.fecha_entrega;
    const fechaB = b.fecha_desde || b.fecha_entrega;
    return new Date(fechaA) - new Date(fechaB);
  }).slice(0, 5); // Limitar a 5 eventos

  return (
    <DashboardLayout>
      <div className="alumno-dashboard">
        {/* Header con bienvenida */}
        <div className="dashboard-header-section">
          <div className="welcome-card">
            <div className="welcome-avatar">
              {alumno.foto && alumno.foto !== '' ? (
                <img 
                  src={alumno.foto.startsWith('http') 
                    ? alumno.foto 
                    : alumno.foto.startsWith('/')
                    ? `${window.location.protocol}//${window.location.hostname}:5000${alumno.foto}`
                    : `${window.location.protocol}//${window.location.hostname}:5000/uploads/alumnos/${alumno.foto}`} 
                  alt={nombreCompleto}
                  onError={(e) => {
                    e.target.style.display = 'none';
                    const placeholder = e.target.parentElement.querySelector('.avatar-placeholder');
                    if (placeholder) placeholder.style.display = 'flex';
                  }}
                />
              ) : null}
              <div className="avatar-placeholder" style={{ display: alumno.foto && alumno.foto !== '' ? 'none' : 'flex' }}>
                {getInitials(nombreCompleto)}
              </div>
            </div>
            <div className="welcome-text">
              <h1>¡Hola, {nombreCompleto}!</h1>
              <p>
                {matricula ? `${matricula.grado}° ${matricula.seccion} - ${matricula.nivel_nombre} (${matricula.turno_nombre})` : 'Bienvenido a tu aula virtual'}
              </p>
            </div>
          </div>
        </div>

        {/* Tarjetas de estadísticas */}
        <div className="stats-grid">
          <div className="stat-card mundo-card">
            <div className="stat-icon">📚</div>
            <div className="stat-content">
              <div className="stat-number">{estadisticas.cursosAsignados || 0}</div>
              <div className="stat-label">Mis Cursos</div>
            </div>
          </div>

          <div className="stat-card mundo-card">
            <div className="stat-icon">📝</div>
            <div className="stat-content">
              <div className="stat-number">{estadisticas.tareasPendientes || 0}</div>
              <div className="stat-label">Tareas Pendientes</div>
            </div>
          </div>

          <div className="stat-card mundo-card">
            <div className="stat-icon">📋</div>
            <div className="stat-content">
              <div className="stat-number">{estadisticas.examenesPendientes || 0}</div>
              <div className="stat-label">Exámenes Pendientes</div>
            </div>
          </div>

          <div className="stat-card mundo-card">
            <div className="stat-icon">✉️</div>
            <div className="stat-content">
              <div className="stat-number">{estadisticas.mensajesNoLeidos || 0}</div>
              <div className="stat-label">Mensajes No Leídos</div>
            </div>
          </div>
        </div>

        {/* Mis Cursos */}
        {asignaturas && asignaturas.length > 0 && (
          <div className="dashboard-section">
            <h2 className="section-title">📚 Mis Cursos</h2>
            <div className="cursos-grid">
              {asignaturas.slice(0, 6).map((asignatura) => (
                <div key={asignatura.id} className="curso-card mundo-card">
                  <div className="curso-icon">{asignatura.curso_imagen || '📖'}</div>
                  <div className="curso-info">
                    <h3>{asignatura.curso_nombre}</h3>
                    <p>{asignatura.area_nombre}</p>
                    <p className="curso-docente">
                      Prof. {asignatura.docente_nombres} {asignatura.docente_apellidos}
                    </p>
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}

        {/* Próximos Eventos */}
        <div className="dashboard-section">
          <h2 className="section-title">📅 Próximos Eventos</h2>
          {proximosEventos && proximosEventos.length > 0 ? (
            <div className="eventos-list">
              {proximosEventos.map((evento, index) => (
                <div key={evento.id || index} className="evento-card mundo-card">
                  <div className="evento-info">
                    <h3>
                      {evento.tipo === 'examen' ? '📋' : '📝'} {evento.titulo || 'Evento'}
                    </h3>
                    <p>
                      {evento.curso_nombre || evento.asignatura}
                    </p>
                    <span className="evento-fecha">
                      {new Date(evento.fecha_desde || evento.fecha_entrega).toLocaleDateString('es-PE', {
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric'
                      })}
                    </span>
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <div className="empty-state">
              <p>No se encontraron eventos próximos</p>
            </div>
          )}
        </div>

        {/* Próximas Actividades */}
        {proximasActividades && proximasActividades.length > 0 && (
          <div className="dashboard-section">
            <h2 className="section-title">📆 Próximas Actividades del Colegio</h2>
            <div className="actividades-list">
              {proximasActividades.map((actividad) => (
                <div key={actividad.id} className="actividad-card mundo-card">
                  <div className="actividad-info">
                    <h3>{actividad.descripcion}</h3>
                    <span className="actividad-fecha">
                      {new Date(actividad.fecha_inicio).toLocaleDateString('es-PE', {
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric'
                      })}
                    </span>
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}
      </div>
    </DashboardLayout>
  );
}

export default AlumnoDashboard;



