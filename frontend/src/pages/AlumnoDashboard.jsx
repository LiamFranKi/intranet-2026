import React, { useState, useEffect, useMemo } from 'react';
import { createPortal } from 'react-dom';
import DashboardLayout from '../components/DashboardLayout';
import api from '../services/api';
import EventoDetalleModal from '../components/EventoDetalleModal';
import { normalizeStaticFileUrl } from '../config/staticFiles';
import './AlumnoDashboard.css';
import './DocenteGrupos.css';

function AlumnoDashboard() {
  const [loading, setLoading] = useState(true);
  const [dashboardData, setDashboardData] = useState(null);
  const [paginaActual, setPaginaActual] = useState(1);
  const [eventoSeleccionado, setEventoSeleccionado] = useState(null);
  const [tipoEventoSeleccionado, setTipoEventoSeleccionado] = useState(null);
  const [mostrarComunicadoHome, setMostrarComunicadoHome] = useState(false);

  const cargarDashboard = async () => {
    try {
      setLoading(true);
      const response = await api.get('/alumno/dashboard');
      console.log('📊 Dashboard data recibida:', response.data);
      
      // Debug: Log detallado de eventos recibidos
      if (response.data.proximasActividades) {
        console.log('📅 Actividades recibidas:', response.data.proximasActividades.length);
        if (response.data.proximasActividades.length > 0) {
          console.log('📅 Primeras actividades:', response.data.proximasActividades.slice(0, 5).map(a => ({
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
  const { alumno, matricula, estadisticas, asignaturas, proximosExamenes, proximasTareas, proximasActividades, comunicadoHome } = dashboardData || {};

  // Mostrar modal de comunicado home si existe
  useEffect(() => {
    if (comunicadoHome && !loading) {
      // Verificar si ya se mostró este comunicado en esta sesión
      const comunicadoMostrado = sessionStorage.getItem(`comunicado_home_${comunicadoHome.id}`);
      if (!comunicadoMostrado) {
        setMostrarComunicadoHome(true);
      }
    }
  }, [comunicadoHome, loading]);

  const cerrarComunicadoHome = () => {
    if (comunicadoHome) {
      sessionStorage.setItem(`comunicado_home_${comunicadoHome.id}`, 'true');
    }
    setMostrarComunicadoHome(false);
  };

  const formatearFecha = (fechaHora) => {
    if (!fechaHora) return '';
    const fecha = new Date(fechaHora);
    const dia = String(fecha.getDate()).padStart(2, '0');
    const mes = String(fecha.getMonth() + 1).padStart(2, '0');
    const año = fecha.getFullYear();
    let horas = fecha.getHours();
    const minutos = String(fecha.getMinutes()).padStart(2, '0');
    const ampm = horas >= 12 ? 'PM' : 'AM';
    horas = horas % 12;
    horas = horas ? horas : 12;
    return `${dia}-${mes}-${año} ${String(horas).padStart(2, '0')}:${minutos} ${ampm}`;
  };

  const handleVerArchivo = (archivoUrl) => {
    if (archivoUrl) {
      let urlFinal = archivoUrl.trim();
      
      if (!urlFinal.startsWith('http://') && !urlFinal.startsWith('https://')) {
        urlFinal = normalizeStaticFileUrl(archivoUrl);
      }
      
      if (!urlFinal) {
        alert('Error: No se pudo construir la URL del archivo');
        return;
      }
      
      try {
        const urlObj = new URL(urlFinal);
        const nuevaVentana = window.open(urlObj.href, '_blank', 'noopener,noreferrer');
        
        if (!nuevaVentana) {
          const link = document.createElement('a');
          link.href = urlObj.href;
          link.target = '_blank';
          link.rel = 'noopener noreferrer';
          document.body.appendChild(link);
          link.click();
          setTimeout(() => {
            document.body.removeChild(link);
          }, 100);
        }
      } catch (error) {
        console.error('Error al procesar URL:', error);
        alert(`Error al abrir el archivo.\nURL: ${urlFinal}\n\nError: ${error.message}`);
      }
    }
  };

  // Obtener el mes actual en español
  const meses = [
    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
  ];
  const mesActual = meses[new Date().getMonth()];

  // Función auxiliar para crear fecha desde string (ignora zona horaria, solo usa fecha)
  // SOLO para usar en "Próximos Eventos" del dashboard
  // IGUAL QUE EN DOCENTEDASHBOARD - Extrae solo la fecha y crea Date directamente en hora local
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
    // IMPORTANTE: new Date(year, month - 1, day) crea la fecha directamente en hora local
    const fecha = new Date(year, month - 1, day);
    fecha.setHours(0, 0, 0, 0);
    return fecha;
  };

  // Función auxiliar para obtener hoy en Lima (UTC-5)
  const obtenerHoyLima = () => {
    const ahora = new Date();
    // Ajustar a UTC-5 (Lima)
    const offsetLima = -5 * 60; // -5 horas en minutos
    const utc = ahora.getTime() + (ahora.getTimezoneOffset() * 60000);
    const lima = new Date(utc + (offsetLima * 60000));
    // Solo usar la fecha (sin hora) para comparación
    lima.setHours(0, 0, 0, 0);
    return lima;
  };

  // Combinar todos los eventos en un solo array
  const todosEventos = useMemo(() => {
    const eventos = [];
    const hoy = obtenerHoyLima();

    // Agregar exámenes
    if (proximosExamenes && Array.isArray(proximosExamenes)) {
      console.log('📝 Procesando exámenes:', proximosExamenes.length);
      proximosExamenes.forEach(examen => {
        const fecha = crearFechaLima(examen.fecha_desde || examen.fecha_evento);
        // Si no tiene fecha, usar una fecha futura para que aparezca
        const fechaFinal = fecha || new Date('9999-12-31');
        // Incluir si no tiene fecha o si la fecha es >= hoy
        if (!fecha || fecha >= hoy) {
          eventos.push({
            ...examen,
            tipo: 'examen',
            fecha: fechaFinal
          });
          console.log('✅ Examen agregado:', examen.titulo || examen.id, 'fecha:', fechaFinal);
        } else {
          console.log('⚠️ Examen filtrado (fecha pasada):', examen.titulo || examen.id, 'fecha:', fecha);
        }
      });
      console.log('📝 Total exámenes agregados:', eventos.filter(e => e.tipo === 'examen').length);
    } else {
      console.warn('⚠️ proximosExamenes no es un array válido:', proximosExamenes);
    }

    // Agregar tareas
    if (proximasTareas && Array.isArray(proximasTareas)) {
      proximasTareas.forEach(tarea => {
        const fecha = crearFechaLima(tarea.fecha_entrega || tarea.fecha_evento);
        if (fecha && fecha >= hoy) {
          eventos.push({
            ...tarea,
            tipo: 'tarea',
            fecha: fecha
          });
        }
      });
    } else {
      console.warn('⚠️ proximasTareas no es un array válido:', proximasTareas);
    }

    // Agregar actividades (igual que en DocenteDashboard)
    if (proximasActividades && Array.isArray(proximasActividades)) {
      console.log('📅 Procesando actividades para Próximos Eventos:', proximasActividades.length);
      proximasActividades.forEach(actividad => {
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
          console.log('⚠️ Actividad sin fecha válida:', actividad.id);
        }
      });
      console.log('📅 Total actividades agregadas:', eventos.filter(e => e.tipo === 'actividad').length);
    } else {
      console.warn('⚠️ proximasActividades no es un array válido:', proximasActividades);
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
  }, [proximosExamenes, proximasTareas, proximasActividades]);

  // Resetear página cuando cambian los eventos
  useEffect(() => {
    setPaginaActual(1);
  }, [todosEventos.length]);

  // Ahora sí, los returns condicionales DESPUÉS de todos los hooks
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

  // Combinar nombre completo del alumno
  const nombreCompleto = alumno?.nombres && alumno?.apellido_paterno 
    ? `${alumno.nombres} ${alumno.apellido_paterno}${alumno.apellido_materno ? ' ' + alumno.apellido_materno : ''}`
    : alumno?.nombres || 'Alumno';

  return (
    <DashboardLayout>
      <div className="alumno-dashboard">
        {/* Tarjetas de estadísticas */}
        <div className="stats-grid">
          <div className="stat-card mundo-card">
            <div className="stat-icon">📚</div>
            <div className="stat-content">
              <div className="stat-number">{estadisticas?.cursosAsignados || 0}</div>
              <div className="stat-label">Mis Cursos</div>
            </div>
          </div>

          <div className="stat-card mundo-card">
            <div className="stat-icon">👨‍🏫</div>
            <div className="stat-content">
              <div className="stat-number">{estadisticas?.totalDocentes || 0}</div>
              <div className="stat-label">Total Docentes</div>
            </div>
          </div>

          <div className="stat-card mundo-card">
            <div className="stat-icon">📋</div>
            <div className="stat-content">
              <div className="stat-number">{estadisticas?.totalMatriculas || 0}</div>
              <div className="stat-label">Total Matrículas</div>
            </div>
          </div>

          <div className="stat-card mundo-card">
            <div className="stat-icon">⏰</div>
            <div className="stat-content">
              <div className="stat-number">{estadisticas?.totalTardanzas || 0}</div>
              <div className="stat-label">Total Tardanzas</div>
            </div>
          </div>

          <div className="stat-card mundo-card">
            <div className="stat-icon">🚫</div>
            <div className="stat-content">
              <div className="stat-number">{estadisticas?.totalFaltas || 0}</div>
              <div className="stat-label">Total Faltas</div>
            </div>
          </div>

          <div className="stat-card mundo-card">
            <div className="stat-icon">✉️</div>
            <div className="stat-content">
              <div className="stat-number">{estadisticas?.mensajesNoLeidos || 0}</div>
              <div className="stat-label">Mensajes No Leídos</div>
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

      {/* Modal de Comunicado Home */}
      {mostrarComunicadoHome && comunicadoHome && createPortal(
        <div 
          className="modal-mensaje-overlay" 
          onClick={cerrarComunicadoHome}
          style={{ zIndex: 100001 }}
        >
          <div 
            className="modal-mensaje-container" 
            onClick={(e) => e.stopPropagation()}
            role="dialog"
            aria-modal="true"
            aria-labelledby="modal-comunicado-home-title"
            style={{ maxWidth: '700px' }}
          >
            <div className="modal-mensaje-header">
              <h2 id="modal-comunicado-home-title">
                📢 Comunicado Importante
              </h2>
              <button
                className="modal-mensaje-close"
                onClick={cerrarComunicadoHome}
                type="button"
                aria-label="Cerrar modal"
              >
                ✕
              </button>
            </div>

            <div className="modal-mensaje-body">
              <div style={{ marginBottom: '1.5rem' }}>
                <h3 style={{ 
                  margin: '0 0 1rem 0', 
                  fontSize: '1.5rem', 
                  color: '#1f2937',
                  fontWeight: '700'
                }}>
                  {comunicadoHome.descripcion}
                </h3>
                {comunicadoHome.contenido && (
                  <div 
                    style={{ 
                      marginBottom: '1rem',
                      color: '#374151',
                      lineHeight: '1.6'
                    }}
                    dangerouslySetInnerHTML={{ __html: comunicadoHome.contenido }}
                  />
                )}
                <div style={{ 
                  fontSize: '0.9rem', 
                  color: '#6b7280',
                  marginTop: '1rem'
                }}>
                  📅 {formatearFecha(comunicadoHome.fecha_hora)}
                </div>
              </div>
              {comunicadoHome.archivo_url && (
                <div style={{ 
                  marginTop: '1.5rem',
                  padding: '1rem',
                  background: 'linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%)',
                  borderRadius: '10px',
                  border: '2px solid #bae6fd'
                }}>
                  <button
                    onClick={() => handleVerArchivo(comunicadoHome.archivo_url)}
                    style={{
                      display: 'flex',
                      alignItems: 'center',
                      gap: '0.75rem',
                      padding: '0.75rem 1.5rem',
                      background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                      color: 'white',
                      border: 'none',
                      borderRadius: '8px',
                      cursor: 'pointer',
                      fontSize: '1rem',
                      fontWeight: '600',
                      width: '100%',
                      justifyContent: 'center',
                      transition: 'all 0.2s'
                    }}
                    onMouseEnter={(e) => e.target.style.transform = 'translateY(-2px)'}
                    onMouseLeave={(e) => e.target.style.transform = 'translateY(0)'}
                  >
                    <span style={{ fontSize: '1.2rem' }}>📎</span>
                    Ver Archivo Adjunto
                  </button>
                </div>
              )}
              <div style={{ 
                display: 'flex', 
                justifyContent: 'center', 
                marginTop: '1.5rem' 
              }}>
                <button
                  onClick={cerrarComunicadoHome}
                  style={{
                    padding: '0.75rem 2rem',
                    background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                    color: 'white',
                    border: 'none',
                    borderRadius: '8px',
                    cursor: 'pointer',
                    fontSize: '1rem',
                    fontWeight: '600'
                  }}
                >
                  Entendido
                </button>
              </div>
            </div>
          </div>
        </div>,
        document.body
      )}
    </DashboardLayout>
  );
}

export default AlumnoDashboard;
