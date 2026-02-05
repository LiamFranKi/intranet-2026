import React, { useState, useEffect, useCallback } from 'react';
import DashboardLayout from '../components/DashboardLayout';
import api from '../services/api';
import './AlumnoActividades.css';

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

// Función para crear fecha desde string interpretándola como hora local (no UTC)
const crearFechaLocal = (fechaString) => {
  if (!fechaString) return null;
  
  // Si viene como "YYYY-MM-DD HH:mm:ss" o "YYYY-MM-DDTHH:mm:ss"
  // Extraer componentes y crear fecha en hora local
  const fechaPart = fechaString.toString().replace('T', ' ').split(' ')[0];
  const horaPart = fechaString.toString().replace('T', ' ').split(' ')[1] || '00:00:00';
  
  const [year, month, day] = fechaPart.split('-').map(Number);
  const [hours, minutes, seconds] = horaPart.split(':').map(Number);
  
  // Crear fecha en hora local (no UTC)
  return new Date(year, month - 1, day, hours || 0, minutes || 0, seconds || 0);
};

function AlumnoActividades() {
  console.log('✅ [ALUMNO ACTIVIDADES] Componente montado');
  const [loading, setLoading] = useState(true);
  const [loadingMes, setLoadingMes] = useState(false);
  const [todasLasActividades, setTodasLasActividades] = useState([]);
  const [actividadesFiltradas, setActividadesFiltradas] = useState([]);
  const [mesSeleccionado, setMesSeleccionado] = useState(null);
  const [anio, setAnio] = useState(new Date().getFullYear());
  const [actividadesAgrupadas, setActividadesAgrupadas] = useState({});

  const filtrarYAgruparActividades = useCallback((actividadesData, mes) => {
    let actividadesFiltradas = actividadesData;
    if (mes !== null) {
      actividadesFiltradas = actividadesData.filter(a => {
        const fecha = crearFechaLocal(a.fecha_inicio);
        return fecha && fecha.getMonth() + 1 === mes;
      });
    }
    
    setActividadesFiltradas(actividadesFiltradas);

    const agrupadas = {};
    actividadesFiltradas.forEach(actividad => {
      const fechaInicio = crearFechaLocal(actividad.fecha_inicio);
      const fechaFin = actividad.fecha_fin ? crearFechaLocal(actividad.fecha_fin) : fechaInicio;
      
      if (!fechaInicio) return;
      
      const fechaActual = new Date(fechaInicio);
      while (fechaActual <= fechaFin) {
        // Usar formato YYYY-MM-DD en hora local
        const year = fechaActual.getFullYear();
        const month = String(fechaActual.getMonth() + 1).padStart(2, '0');
        const day = String(fechaActual.getDate()).padStart(2, '0');
        const fechaKey = `${year}-${month}-${day}`;
        
        if (!agrupadas[fechaKey]) {
          agrupadas[fechaKey] = [];
        }
        if (!agrupadas[fechaKey].some(a => a.id === actividad.id)) {
          agrupadas[fechaKey].push(actividad);
        }
        fechaActual.setDate(fechaActual.getDate() + 1);
      }
    });
    
    setActividadesAgrupadas(agrupadas);
  }, []);

  const cargarTodasLasActividades = useCallback(async () => {
    try {
      setLoading(true);
      console.log('📥 [ALUMNO ACTIVIDADES] Cargando actividades...');
      const añoActual = new Date().getFullYear();
      const response = await api.get('/alumno/actividades', {
        params: { anio: añoActual }
      });
      const actividadesData = response.data.actividades || [];
      console.log('✅ [ALUMNO ACTIVIDADES] Actividades cargadas:', actividadesData.length);
      setTodasLasActividades(actividadesData);
      
      if (response.data.anio) {
        setAnio(response.data.anio);
      } else {
        setAnio(añoActual);
      }
      
      filtrarYAgruparActividades(actividadesData, null);
    } catch (error) {
      console.error('❌ [ALUMNO ACTIVIDADES] Error cargando actividades:', error);
      console.error('Error details:', error.response?.data || error.message);
      setTodasLasActividades([]);
    } finally {
      setLoading(false);
    }
  }, [filtrarYAgruparActividades]);

  useEffect(() => {
    cargarTodasLasActividades();
  }, [cargarTodasLasActividades]);

  useEffect(() => {
    if (todasLasActividades.length > 0) {
      setLoadingMes(true);
      const timeoutId = setTimeout(() => {
        filtrarYAgruparActividades(todasLasActividades, mesSeleccionado);
        setLoadingMes(false);
      }, 100);
      
      return () => clearTimeout(timeoutId);
    }
  }, [mesSeleccionado, todasLasActividades, filtrarYAgruparActividades]);

  const obtenerDiasConActividades = () => {
    const dias = Object.keys(actividadesAgrupadas).sort();
    if (!mesSeleccionado) return dias;
    
    return dias.filter(fecha => {
      const fechaObj = crearFechaLocal(fecha);
      return fechaObj && fechaObj.getMonth() + 1 === mesSeleccionado;
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
      <div className="alumno-actividades">
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
                const actividadesDelMes = todasLasActividades.filter(a => {
                  const fecha = crearFechaLocal(a.fecha_inicio);
                  return fecha && fecha.getMonth() + 1 === mes.num;
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
                  
                  // Comparar con hoy en hora local
                  const hoy = new Date();
                  const hoyKey = `${hoy.getFullYear()}-${String(hoy.getMonth() + 1).padStart(2, '0')}-${String(hoy.getDate()).padStart(2, '0')}`;
                  const esHoy = fechaKey === hoyKey;
                  
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
                          const fechaInicio = crearFechaLocal(actividad.fecha_inicio);
                          const fechaFin = actividad.fecha_fin ? crearFechaLocal(actividad.fecha_fin) : fechaInicio;
                          
                          if (!fechaInicio) return null;
                          
                          // Comparar fechas en formato local
                          const fechaInicioKey = `${fechaInicio.getFullYear()}-${String(fechaInicio.getMonth() + 1).padStart(2, '0')}-${String(fechaInicio.getDate()).padStart(2, '0')}`;
                          const fechaFinKey = `${fechaFin.getFullYear()}-${String(fechaFin.getMonth() + 1).padStart(2, '0')}-${String(fechaFin.getDate()).padStart(2, '0')}`;
                          const esRango = fechaInicioKey !== fechaFinKey;
                          
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
                                  Del {fechaInicio.toLocaleDateString('es-PE', { year: 'numeric', month: 'long', day: 'numeric' })} al {fechaFin.toLocaleDateString('es-PE', { year: 'numeric', month: 'long', day: 'numeric' })}
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

export default AlumnoActividades;

