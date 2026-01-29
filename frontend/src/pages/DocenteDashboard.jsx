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
      
      // Debug: Log detallado de eventos recibidos
      if (response.data.actividades) {
        console.log('📅 Actividades recibidas:', response.data.actividades.length);
        if (response.data.actividades.length > 0) {
          console.log('📅 Primeras actividades:', response.data.actividades.slice(0, 5).map(a => ({
            id: a.id,
            descripcion: a.descripcion,
            fecha_inicio: a.fecha_inicio,
            fecha_fin: a.fecha_fin,
            año: new Date(a.fecha_inicio).getFullYear()
          })));
        } else {
          console.warn('⚠️ No hay actividades en el dashboard (año activo filtrado)');
        }
      }
      if (response.data.proximosExamenes) {
        console.log('📝 Exámenes recibidos:', response.data.proximosExamenes.length);
        if (response.data.proximosExamenes.length > 0) {
          console.log('📝 Primeros exámenes:', response.data.proximosExamenes.slice(0, 3));
        }
      }
      if (response.data.proximasTareas) {
        console.log('📋 Tareas recibidas:', response.data.proximasTareas.length);
        if (response.data.proximasTareas.length > 0) {
          console.log('📋 Primeras tareas:', response.data.proximasTareas.slice(0, 3));
        }
      }
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

  // Función auxiliar para crear fecha desde string (ignora zona horaria, solo usa fecha)
  // SOLO para usar en "Próximos Eventos" del dashboard
  const crearFechaLima = (fechaString) => {
    if (!fechaString) return null;
    
    // Si es un objeto Date, extraer solo año, mes, día
    if (fechaString instanceof Date) {
      const year = fechaString.getFullYear();
      const month = fechaString.getMonth();
      const day = fechaString.getDate();
      const fecha = new Date(year, month, day);
      fecha.setHours(0, 0, 0, 0);
      return fecha;
    }
    
    // Si viene como string "YYYY-MM-DD" o "YYYY-MM-DD HH:MM:SS"
    // Extraer solo la parte de la fecha (YYYY-MM-DD)
    const fechaPart = fechaString.toString().split('T')[0].split(' ')[0];
    if (!fechaPart || fechaPart === '') return null;
    
    const [year, month, day] = fechaPart.split('-').map(Number);
    if (isNaN(year) || isNaN(month) || isNaN(day)) return null;
    
    // Crear fecha local (sin considerar zona horaria para comparación)
    const fecha = new Date(year, month - 1, day);
    fecha.setHours(0, 0, 0, 0);
    return fecha;
  };

  // Función auxiliar para obtener hoy en Lima (UTC-5)
  // SOLO para usar en "Próximos Eventos" del dashboard
  const getHoyLima = () => {
    try {
      // Obtener la fecha actual en zona horaria de Lima como string YYYY-MM-DD
      const ahora = new Date();
      const hoyLimaString = ahora.toLocaleDateString('en-CA', { 
        timeZone: 'America/Lima'
      });
      
      // Parsear la fecha en formato YYYY-MM-DD
      const [year, month, day] = hoyLimaString.split('-').map(Number);
      if (isNaN(year) || isNaN(month) || isNaN(day)) {
        throw new Error('Error parseando fecha de Lima');
      }
      
      const hoyLima = new Date(year, month - 1, day);
      hoyLima.setHours(0, 0, 0, 0);
      
      // Log para debug
      console.log('🗓️ Fecha de hoy en Lima:', hoyLimaString, '->', hoyLima.toISOString());
      
      return hoyLima;
    } catch (error) {
      console.error('Error obteniendo fecha de Lima:', error);
      // Fallback: usar fecha actual local
      const hoy = new Date();
      hoy.setHours(0, 0, 0, 0);
      console.log('🗓️ Usando fecha local como fallback:', hoy.toISOString());
      return hoy;
    }
  };
  
  // Función auxiliar para comparar solo fechas (sin horas) - SOLO para dashboard
  const compararSoloFechas = (fecha1, fecha2) => {
    if (!fecha1 || !fecha2) return false;
    const d1 = new Date(fecha1.getFullYear(), fecha1.getMonth(), fecha1.getDate());
    const d2 = new Date(fecha2.getFullYear(), fecha2.getMonth(), fecha2.getDate());
    return d1.getTime() - d2.getTime();
  };

  // Combinar y ordenar todos los eventos
  // IMPORTANTE: El backend ya filtra por año activo y fecha >= hoy
  // Solo mostramos TODO lo que viene del backend sin restricciones adicionales
  const todosEventos = useMemo(() => {
    const eventos = [];

    // Agregar exámenes (vienen ya filtrados del backend por año activo y fecha >= hoy)
    if (proximosExamenes && Array.isArray(proximosExamenes)) {
      proximosExamenes.forEach(examen => {
        // Usar fecha_desde o fecha_evento si está disponible
        const fechaExamen = crearFechaLima(examen.fecha_desde || examen.fecha_evento);
        if (fechaExamen) {
          eventos.push({
            ...examen,
            tipo: 'examen',
            fecha: fechaExamen
          });
        }
      });
    }

    // Agregar tareas (vienen ya filtradas del backend por año activo y fecha >= hoy)
    if (proximasTareas && Array.isArray(proximasTareas)) {
      proximasTareas.forEach(tarea => {
        // Las tareas tienen fecha_entrega (NO fecha_fin) - vienen de asignaturas_tareas
        const fechaTarea = crearFechaLima(tarea.fecha_entrega || tarea.fecha_evento);
        if (fechaTarea) {
          eventos.push({
            ...tarea,
            tipo: 'tarea',
            fecha: fechaTarea
          });
        }
      });
    }

    // Agregar actividades (vienen ya filtradas del backend por año activo y fecha >= hoy)
    if (actividades && Array.isArray(actividades)) {
      console.log('📅 Procesando actividades para Próximos Eventos:', actividades.length);
      actividades.forEach(actividad => {
        // Usar fecha_inicio o fecha_evento si está disponible
        const fechaActividad = crearFechaLima(actividad.fecha_inicio || actividad.fecha_evento);
        console.log('📅 Actividad procesada:', {
          id: actividad.id,
          descripcion: actividad.descripcion,
          fecha_inicio: actividad.fecha_inicio,
          fecha_evento: actividad.fecha_evento,
          fechaProcesada: fechaActividad ? fechaActividad.toISOString() : 'null'
        });
        if (fechaActividad) {
          eventos.push({
            ...actividad,
            tipo: 'actividad',
            fecha: fechaActividad
          });
        } else {
          console.warn('⚠️ Actividad sin fecha válida:', actividad);
        }
      });
    } else {
      console.warn('⚠️ actividades no es un array válido:', actividades);
    }

    // Ordenar por fecha (más próximos primero)
    const eventosOrdenados = eventos.sort((a, b) => a.fecha - b.fecha);
    
    console.log('📊 Total eventos combinados:', eventosOrdenados.length);
    console.log('📊 Eventos por tipo:', {
      examenes: eventosOrdenados.filter(e => e.tipo === 'examen').length,
      tareas: eventosOrdenados.filter(e => e.tipo === 'tarea').length,
      actividades: eventosOrdenados.filter(e => e.tipo === 'actividad').length
    });
    
    return eventosOrdenados;
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

  // Paginación: 12 eventos por página
  const eventosPorPagina = 12;
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
            <div className="stat-icon">🎓</div>
            <div className="stat-content">
              <div className="stat-number">{estadisticas?.gruposAsignados || 0}</div>
              <div className="stat-label">Grupos Asignados</div>
            </div>
          </div>

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
                  // Obtener título según el tipo de evento
                  let titulo = '';
                  if (evento.tipo === 'examen') {
                    titulo = evento.titulo || 'Examen';
                  } else if (evento.tipo === 'tarea') {
                    // Las tareas tienen 'titulo' en la tabla asignaturas_tareas
                    titulo = evento.titulo || evento.descripcion || 'Tarea';
                  } else if (evento.tipo === 'actividad') {
                    titulo = evento.descripcion || 'Actividad';
                  } else {
                    titulo = 'Evento';
                  }
                  
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



