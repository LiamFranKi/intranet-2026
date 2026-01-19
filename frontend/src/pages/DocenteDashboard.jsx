import React, { useState, useEffect, useMemo } from 'react';
import DashboardLayout from '../components/DashboardLayout';
import api from '../services/api';
import EventoDetalleModal from '../components/EventoDetalleModal';
import './DocenteDashboard.css';

function DocenteDashboard() {
  const [loading, setLoading] = useState(true);
  const [dashboardData, setDashboardData] = useState(null);
  const [paginaActual, setPaginaActual] = useState(1);
  const [eventoSeleccionado, setEventoSeleccionado] = useState(null);
  const [tipoEventoSeleccionado, setTipoEventoSeleccionado] = useState(null);

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

  useEffect(() => {
    cargarDashboard();
  }, []);

  // Extraer datos del dashboard (con valores por defecto para evitar errores)
  const { docente, estadisticas, proximosExamenes, proximasTareas, actividades } = dashboardData || {};

  // Obtener el mes actual en español
  const meses = [
    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
  ];
  const mesActual = meses[new Date().getMonth()];

  // Combinar y ordenar todos los eventos (solo próximos, no pasados)
  // IMPORTANTE: Este hook debe ejecutarse SIEMPRE, incluso si dashboardData es null
  const todosEventos = useMemo(() => {
    const eventos = [];
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0); // Normalizar a inicio del día

    // Agregar exámenes (ya vienen filtrados del backend como futuros)
    if (proximosExamenes && Array.isArray(proximosExamenes)) {
      proximosExamenes.forEach(examen => {
        const fechaExamen = examen.fecha_desde ? new Date(examen.fecha_desde) : null;
        if (fechaExamen) {
          fechaExamen.setHours(0, 0, 0, 0);
          // Solo agregar si es hoy o futuro
          if (fechaExamen >= hoy) {
            eventos.push({
              ...examen,
              tipo: 'examen',
              fecha: fechaExamen
            });
          }
        }
      });
    }

    // Agregar tareas (ya vienen filtradas del backend como futuras)
    if (proximasTareas && Array.isArray(proximasTareas)) {
      proximasTareas.forEach(tarea => {
        const fechaTarea = tarea.fecha_fin ? new Date(tarea.fecha_fin) : null;
        if (fechaTarea) {
          fechaTarea.setHours(0, 0, 0, 0);
          // Solo agregar si es hoy o futuro
          if (fechaTarea >= hoy) {
            eventos.push({
              ...tarea,
              tipo: 'tarea',
              fecha: fechaTarea
            });
          }
        }
      });
    }

    // Agregar actividades (solo próximas, no pasadas)
    if (actividades && Array.isArray(actividades)) {
      actividades.forEach(actividad => {
        const fechaActividad = actividad.fecha_inicio ? new Date(actividad.fecha_inicio) : null;
        if (fechaActividad) {
          fechaActividad.setHours(0, 0, 0, 0);
          // Solo agregar si es hoy o futuro
          if (fechaActividad >= hoy) {
            eventos.push({
              ...actividad,
              tipo: 'actividad',
              fecha: fechaActividad
            });
          }
        }
      });
    }

    // Ordenar por fecha (más próximos primero)
    return eventos.sort((a, b) => a.fecha - b.fecha);
  }, [proximosExamenes, proximasTareas, actividades]);

  // Resetear página cuando cambian los eventos
  useEffect(() => {
    setPaginaActual(1);
  }, [todosEventos.length]);

  // Ahora sí, los returns condicionales DESPUÉS de todos los hooks
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

  // Paginación: 8 eventos por página
  const eventosPorPagina = 8;
  const totalPaginas = Math.ceil(todosEventos.length / eventosPorPagina);
  const inicio = (paginaActual - 1) * eventosPorPagina;
  const fin = inicio + eventosPorPagina;
  const eventosPagina = todosEventos.slice(inicio, fin);

  const handleEventoClick = (evento, tipo) => {
    setEventoSeleccionado(evento);
    setTipoEventoSeleccionado(tipo);
  };

  const truncarTexto = (texto, maxLength = 10) => {
    if (!texto) return '';
    if (texto.length <= maxLength) return texto;
    return texto.substring(0, maxLength) + '...';
  };

  const getColorCard = (tipo) => {
    switch (tipo) {
      case 'examen':
        return 'evento-card-examen';
      case 'tarea':
        return 'evento-card-tarea';
      case 'actividad':
        return 'evento-card-actividad';
      default:
        return '';
    }
  };

  const getTipoTexto = (tipo) => {
    switch (tipo) {
      case 'examen':
        return 'Examen';
      case 'tarea':
        return 'Tarea';
      case 'actividad':
        return 'Actividad';
      default:
        return '';
    }
  };

  return (
    <DashboardLayout>
      <div className="docente-dashboard">
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

          <div className="stat-card mundo-card">
            <div className="stat-icon">⏰</div>
            <div className="stat-content">
              <div className="stat-number">0</div>
              <div className="stat-label">Tardanzas de {mesActual}</div>
            </div>
          </div>
        </div>

        {/* Próximos Eventos */}
        <div className="dashboard-section">
          <h2 className="section-title">📅 Próximos Eventos</h2>
          {todosEventos.length > 0 ? (
            <>
              <div className="eventos-grid">
                {eventosPagina.map((evento, index) => {
                  const fecha = evento.fecha;
                  const dia = fecha.getDate();
                  const mes = meses[fecha.getMonth()];
                  const titulo = evento.tipo === 'examen' 
                    ? (evento.titulo || 'Examen')
                    : evento.tipo === 'tarea'
                    ? (evento.descripcion || 'Tarea')
                    : (evento.descripcion || 'Actividad');
                  
                  return (
                    <div
                      key={`${evento.tipo}-${evento.id}-${index}`}
                      className={`evento-card ${getColorCard(evento.tipo)}`}
                      onClick={() => handleEventoClick(evento, evento.tipo)}
                    >
                      <div className="evento-card-fecha">
                        <span className="evento-card-dia">{dia}</span>
                        <span className="evento-card-mes">{mes}</span>
                      </div>
                      <div className="evento-card-contenido">
                        <h3 className="evento-card-titulo">{titulo}</h3>
                        <span className="evento-card-tipo">{getTipoTexto(evento.tipo)}</span>
                      </div>
                    </div>
                  );
                })}
              </div>

              {/* Paginación */}
              {totalPaginas > 1 && (
                <div className="eventos-paginacion">
                  <button
                    className="btn-paginacion"
                    onClick={() => setPaginaActual(prev => Math.max(1, prev - 1))}
                    disabled={paginaActual === 1}
                  >
                    ‹ Anterior
                  </button>
                  <span className="paginacion-info">
                    Página {paginaActual} de {totalPaginas}
                  </span>
                  <button
                    className="btn-paginacion"
                    onClick={() => setPaginaActual(prev => Math.min(totalPaginas, prev + 1))}
                    disabled={paginaActual === totalPaginas}
                  >
                    Siguiente ›
                  </button>
                </div>
              )}
            </>
          ) : (
            <div className="empty-state">
              <p>No se encontraron eventos próximos</p>
            </div>
          )}
        </div>
      </div>

      {/* Modal de detalle de evento */}
      {eventoSeleccionado && tipoEventoSeleccionado && (
        <EventoDetalleModal
          evento={eventoSeleccionado}
          tipo={tipoEventoSeleccionado}
          onClose={() => {
            setEventoSeleccionado(null);
            setTipoEventoSeleccionado(null);
          }}
        />
      )}
    </DashboardLayout>
  );
}

export default DocenteDashboard;



