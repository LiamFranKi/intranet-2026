import React, { useState, useEffect, useCallback } from 'react';
import DashboardLayout from '../components/DashboardLayout';
import api from '../services/api';
import './DocenteActividades.css';

const MESES = [
  { num: 1, nombre: 'Enero', nombreCorto: 'Ene', icon: '❄️' },
  { num: 2, nombre: 'Febrero', nombreCorto: 'Feb', icon: '💝' },
  { num: 3, nombre: 'Marzo', nombreCorto: 'Mar', icon: '🌸' },
  { num: 4, nombre: 'Abril', nombreCorto: 'Abr', icon: '🌷' },
  { num: 5, nombre: 'Mayo', nombreCorto: 'May', icon: '🌺' },
  { num: 6, nombre: 'Junio', nombreCorto: 'Jun', icon: '☀️' },
  { num: 7, nombre: 'Julio', nombreCorto: 'Jul', icon: '🔥' },
  { num: 8, nombre: 'Agosto', nombreCorto: 'Ago', icon: '🌻' },
  { num: 9, nombre: 'Septiembre', nombreCorto: 'Sep', icon: '🍂' },
  { num: 10, nombre: 'Octubre', nombreCorto: 'Oct', icon: '🎃' },
  { num: 11, nombre: 'Noviembre', nombreCorto: 'Nov', icon: '🍁' },
  { num: 12, nombre: 'Diciembre', nombreCorto: 'Dic', icon: '🎄' }
];

function DocenteActividades() {
  const [loading, setLoading] = useState(true);
  const [loadingMes, setLoadingMes] = useState(false); // Loading solo para cambio de mes
  const [todasLasActividades, setTodasLasActividades] = useState([]); // TODAS las actividades del año (para conteos)
  const [actividadesFiltradas, setActividadesFiltradas] = useState([]); // Actividades filtradas por mes (para mostrar)
  const [mesSeleccionado, setMesSeleccionado] = useState(null); // null = todos los meses
  const [anio, setAnio] = useState(new Date().getFullYear());
  const [actividadesAgrupadas, setActividadesAgrupadas] = useState({});

  // Cargar TODAS las actividades del año una sola vez al inicio
  const cargarTodasLasActividades = useCallback(async () => {
    try {
      setLoading(true);
      const añoActual = new Date().getFullYear();
      const response = await api.get('/docente/actividades', {
        params: { anio: añoActual }
      });
      const actividadesData = response.data.actividades || [];
      setTodasLasActividades(actividadesData);
      
      if (response.data.anio) {
        setAnio(response.data.anio);
      } else {
        setAnio(añoActual);
      }

      console.log(`📅 Todas las actividades del año ${añoActual}:`, actividadesData.length);
      
      // Inicializar con todas las actividades
      filtrarYAgruparActividades(actividadesData, null);
    } catch (error) {
      console.error('Error cargando actividades:', error);
    } finally {
      setLoading(false);
    }
  }, [filtrarYAgruparActividades]);

  // Filtrar y agrupar actividades según el mes seleccionado
  const filtrarYAgruparActividades = useCallback((actividadesData, mes) => {
    // Filtrar por mes si se especifica
    let actividadesFiltradas = actividadesData;
    if (mes !== null) {
      actividadesFiltradas = actividadesData.filter(a => {
        const fecha = new Date(a.fecha_inicio);
        return fecha.getMonth() + 1 === mes;
      });
    }
    
    setActividadesFiltradas(actividadesFiltradas);

    // Agrupar actividades por día
    const agrupadas = {};
    actividadesFiltradas.forEach(actividad => {
      const fechaInicio = new Date(actividad.fecha_inicio);
      const fechaFin = actividad.fecha_fin ? new Date(actividad.fecha_fin) : fechaInicio;
      
      // Crear un rango de fechas
      const fechaActual = new Date(fechaInicio);
      while (fechaActual <= fechaFin) {
        const fechaKey = fechaActual.toISOString().split('T')[0];
        if (!agrupadas[fechaKey]) {
          agrupadas[fechaKey] = [];
        }
        // Evitar duplicados
        if (!agrupadas[fechaKey].some(a => a.id === actividad.id)) {
          agrupadas[fechaKey].push(actividad);
        }
        fechaActual.setDate(fechaActual.getDate() + 1);
      }
    });
    
    console.log(`📅 Actividades filtradas (mes ${mes || 'todos'}):`, actividadesFiltradas.length);
    console.log(`📅 Días con actividades agrupadas:`, Object.keys(agrupadas).length);
    setActividadesAgrupadas(agrupadas);
  }, []);

  // Cargar todas las actividades al inicio
  useEffect(() => {
    cargarTodasLasActividades();
  }, [cargarTodasLasActividades]);

  // Filtrar actividades cuando cambia el mes seleccionado
  useEffect(() => {
    if (todasLasActividades.length > 0) {
      setLoadingMes(true);
      // Simular un pequeño delay para mejor UX (opcional)
      const timeoutId = setTimeout(() => {
        filtrarYAgruparActividades(todasLasActividades, mesSeleccionado);
        setLoadingMes(false);
      }, 100);
      
      // Cleanup para evitar memory leaks
      return () => clearTimeout(timeoutId);
    }
  }, [mesSeleccionado, todasLasActividades]); // Removido filtrarYAgruparActividades de dependencias

  // Obtener días del mes seleccionado o todos los días con actividades
  const obtenerDiasConActividades = () => {
    const dias = Object.keys(actividadesAgrupadas).sort();
    if (!mesSeleccionado) return dias;
    
    // Filtrar por mes seleccionado
    return dias.filter(fecha => {
      const fechaObj = new Date(fecha);
      return fechaObj.getMonth() + 1 === mesSeleccionado;
    });
  };

  const formatearFecha = (fechaStr) => {
    const fecha = new Date(fechaStr);
    const diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                   'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    
    return {
      diaSemana: diasSemana[fecha.getDay()],
      dia: fecha.getDate(),
      mes: meses[fecha.getMonth()],
      anio: fecha.getFullYear(),
      fechaCompleta: fecha
    };
  };

  const obtenerColorActividad = (index) => {
    const colores = [
      'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
      'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
      'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
      'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
      'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
      'linear-gradient(135deg, #30cfd0 0%, #330867 100%)',
      'linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)',
      'linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%)'
    ];
    return colores[index % colores.length];
  };

  if (loading) {
    return (
      <DashboardLayout>
        <div className="actividades-loading">
          <div className="loading-spinner-actividades">
            <div className="spinner-ring"></div>
            <div className="spinner-ring"></div>
            <div className="spinner-ring"></div>
          </div>
          <p>Cargando actividades...</p>
        </div>
      </DashboardLayout>
    );
  }

  const diasConActividades = obtenerDiasConActividades();
  const mesActual = new Date().getMonth() + 1;

  return (
    <DashboardLayout>
      <div className="docente-actividades">
        <div className="actividades-header">
          <div className="header-content">
            <h1 className="actividades-title">
              <span className="title-icon">📅</span>
              Calendario de Actividades
            </h1>
            <p className="actividades-subtitle">Año Académico {anio}</p>
          </div>
        </div>

        <div className="actividades-main-container">
          {/* Sidebar de Filtro por Meses */}
          <div className="meses-sidebar">
            <div className="sidebar-header">
              <h3>Filtrar por Mes</h3>
            </div>
            <div className="meses-list">
              <button
                className={`mes-item ${mesSeleccionado === null ? 'active' : ''}`}
                onClick={() => setMesSeleccionado(null)}
                disabled={loadingMes}
              >
                <span className="mes-icon">📆</span>
                <span className="mes-nombre">Todos</span>
                <span className="mes-count">
                  {todasLasActividades.length}
                </span>
              </button>
              {MESES.map((mes) => {
                // Usar todasLasActividades para calcular conteos (no las filtradas)
                const actividadesDelMes = todasLasActividades.filter(a => {
                  const fecha = new Date(a.fecha_inicio);
                  return fecha.getMonth() + 1 === mes.num;
                });
                const tieneActividades = actividadesDelMes.length > 0;
                const esMesActual = mes.num === mesActual;
                
                return (
                  <button
                    key={mes.num}
                    className={`mes-item ${mesSeleccionado === mes.num ? 'active' : ''} ${!tieneActividades ? 'sin-actividades' : ''} ${esMesActual ? 'mes-actual' : ''} ${loadingMes ? 'loading' : ''}`}
                    onClick={() => setMesSeleccionado(mes.num)}
                    disabled={!tieneActividades || loadingMes}
                  >
                    <span className="mes-icon">{mes.icon}</span>
                    <span className="mes-nombre">{mes.nombreCorto}</span>
                    <span className="mes-count">
                      {actividadesDelMes.length}
                    </span>
                    {esMesActual && <span className="mes-badge">Hoy</span>}
                  </button>
                );
              })}
            </div>
          </div>

          {/* Contenido Principal - Timeline Vertical */}
          <div className="actividades-content">
            {loadingMes && (
              <div className="loading-mes-indicator">
                <div className="spinner-small"></div>
                <span>Cargando...</span>
              </div>
            )}
            {!loadingMes && diasConActividades.length > 0 ? (
              <div className="timeline-container">
                {diasConActividades.map((fechaKey, index) => {
                  const fechaInfo = formatearFecha(fechaKey);
                  const actividadesDelDia = actividadesAgrupadas[fechaKey];
                  const esHoy = fechaKey === new Date().toISOString().split('T')[0];
                  
                  return (
                    <div key={fechaKey} className="timeline-day">
                      <div className="day-header">
                        <div className="day-marker">
                          <div className="marker-dot"></div>
                          {index < diasConActividades.length - 1 && (
                            <div className="marker-line"></div>
                          )}
                        </div>
                        <div className="day-info">
                          <div className={`day-date ${esHoy ? 'hoy' : ''}`}>
                            <span className="day-number">{fechaInfo.dia}</span>
                            <div className="day-month-year">
                              <span className="day-month">{fechaInfo.mes}</span>
                              <span className="day-year">{fechaInfo.anio}</span>
                            </div>
                          </div>
                          <div className="day-weekday">{fechaInfo.diaSemana}</div>
                          {esHoy && <span className="hoy-badge">Hoy</span>}
                        </div>
                      </div>
                      
                      <div className="actividades-del-dia">
                        {actividadesDelDia.map((actividad, actIndex) => {
                          const fechaInicio = new Date(actividad.fecha_inicio);
                          const fechaFin = actividad.fecha_fin ? new Date(actividad.fecha_fin) : fechaInicio;
                          const esRango = fechaInicio.toISOString().split('T')[0] !== fechaFin.toISOString().split('T')[0];
                          const horaInicio = fechaInicio.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit' });
                          
                          return (
                            <div
                              key={`${actividad.id}-${actIndex}`}
                              className="actividad-card"
                              style={{ background: obtenerColorActividad(actIndex) }}
                            >
                              <div className="actividad-time">
                                <span className="time-icon">🕐</span>
                                {horaInicio}
                              </div>
                              <h3 className="actividad-titulo">{actividad.descripcion}</h3>
                              {actividad.lugar && (
                                <div className="actividad-lugar">
                                  <span className="lugar-icon">📍</span>
                                  {actividad.lugar}
                                </div>
                              )}
                              {actividad.detalles && (
                                <p className="actividad-detalles">{actividad.detalles}</p>
                              )}
                              {esRango && (
                                <div className="actividad-rango">
                                  <span className="rango-icon">📆</span>
                                  Del {fechaInicio.toLocaleDateString('es-PE')} al {fechaFin.toLocaleDateString('es-PE')}
                                </div>
                              )}
                            </div>
                          );
                        })}
                      </div>
                    </div>
                  );
                })}
              </div>
            ) : (
              <div className="empty-state-actividades">
                <div className="empty-icon">📅</div>
                <h3>No hay actividades</h3>
                <p>
                  {mesSeleccionado 
                    ? `No hay actividades programadas para ${MESES.find(m => m.num === mesSeleccionado)?.nombre || 'este mes'}`
                    : 'No hay actividades programadas para el año académico actual'}
                </p>
              </div>
            )}
          </div>
        </div>
      </div>
    </DashboardLayout>
  );
}

export default DocenteActividades;
