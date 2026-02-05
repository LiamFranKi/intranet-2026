import React, { useState, useEffect, useCallback } from 'react';
import DashboardLayout from '../components/DashboardLayout';
import api from '../services/api';
import { useAuth } from '../context/AuthContext';
import './DocenteActividades.css';
import Swal from 'sweetalert2';

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

function AdminActividades() {
  const { user } = useAuth();
  const [loading, setLoading] = useState(true);
  const [loadingMes, setLoadingMes] = useState(false);
  const [todasLasActividades, setTodasLasActividades] = useState([]);
  const [actividadesFiltradas, setActividadesFiltradas] = useState([]);
  const [mesSeleccionado, setMesSeleccionado] = useState(null);
  const [anio, setAnio] = useState(new Date().getFullYear());
  const [actividadesAgrupadas, setActividadesAgrupadas] = useState({});
  const [mostrarFormulario, setMostrarFormulario] = useState(false);
  const [formulario, setFormulario] = useState({
    descripcion: '',
    lugar: '',
    detalles: '',
    fecha_inicio: '',
    fecha_fin: ''
  });

  const filtrarYAgruparActividades = useCallback((actividadesData, mes) => {
    let actividadesFiltradas = actividadesData;
    if (mes !== null) {
      actividadesFiltradas = actividadesData.filter(a => {
        const fecha = new Date(a.fecha_inicio);
        return fecha.getMonth() + 1 === mes;
      });
    }
    
    setActividadesFiltradas(actividadesFiltradas);

    const agrupadas = {};
    actividadesFiltradas.forEach(actividad => {
      const fechaInicio = new Date(actividad.fecha_inicio);
      const fechaFin = actividad.fecha_fin ? new Date(actividad.fecha_fin) : fechaInicio;
      
      const fechaActual = new Date(fechaInicio);
      while (fechaActual <= fechaFin) {
        const fechaKey = fechaActual.toISOString().split('T')[0];
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

      filtrarYAgruparActividades(actividadesData, null);
    } catch (error) {
      console.error('Error cargando actividades:', error);
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

  const handleCrearActividad = async (e) => {
    e.preventDefault();
    
    try {
      const response = await api.post('/docente/actividades', formulario);
      
      if (response.data.success) {
        Swal.fire({
          icon: 'success',
          title: '¡Éxito!',
          text: 'Actividad creada correctamente',
          timer: 2000,
          showConfirmButton: false
        });
        
        setFormulario({
          descripcion: '',
          lugar: '',
          detalles: '',
          fecha_inicio: '',
          fecha_fin: ''
        });
        setMostrarFormulario(false);
        cargarTodasLasActividades();
      }
    } catch (error) {
      console.error('Error creando actividad:', error);
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: error.response?.data?.error || 'Error al crear actividad'
      });
    }
  };

  const obtenerDiasConActividades = () => {
    const dias = Object.keys(actividadesAgrupadas).sort();
    if (!mesSeleccionado) return dias;
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
          <button 
            className="btn-crear-actividad"
            onClick={() => setMostrarFormulario(!mostrarFormulario)}
            style={{
              padding: '0.75rem 1.5rem',
              background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
              color: 'white',
              border: 'none',
              borderRadius: '10px',
              cursor: 'pointer',
              fontSize: '1rem',
              fontWeight: '600',
              boxShadow: '0 4px 12px rgba(102, 126, 234, 0.3)'
            }}
          >
            ➕ Crear Actividad
          </button>
        </div>

        {mostrarFormulario && (
          <div className="formulario-actividad" style={{
            background: 'white',
            padding: '2rem',
            borderRadius: '16px',
            marginBottom: '2rem',
            boxShadow: '0 2px 8px rgba(0,0,0,0.1)'
          }}>
            <h2 style={{ marginBottom: '1.5rem', color: '#1f2937' }}>Nueva Actividad</h2>
            <form onSubmit={handleCrearActividad}>
              <div style={{ marginBottom: '1rem' }}>
                <label style={{ display: 'block', marginBottom: '0.5rem', fontWeight: '600', color: '#374151' }}>
                  Descripción *
                </label>
                <input
                  type="text"
                  required
                  value={formulario.descripcion}
                  onChange={(e) => setFormulario({...formulario, descripcion: e.target.value})}
                  style={{
                    width: '100%',
                    padding: '0.75rem',
                    border: '1px solid #e5e7eb',
                    borderRadius: '8px',
                    fontSize: '1rem'
                  }}
                />
              </div>
              <div style={{ marginBottom: '1rem' }}>
                <label style={{ display: 'block', marginBottom: '0.5rem', fontWeight: '600', color: '#374151' }}>
                  Lugar
                </label>
                <input
                  type="text"
                  value={formulario.lugar}
                  onChange={(e) => setFormulario({...formulario, lugar: e.target.value})}
                  style={{
                    width: '100%',
                    padding: '0.75rem',
                    border: '1px solid #e5e7eb',
                    borderRadius: '8px',
                    fontSize: '1rem'
                  }}
                />
              </div>
              <div style={{ marginBottom: '1rem' }}>
                <label style={{ display: 'block', marginBottom: '0.5rem', fontWeight: '600', color: '#374151' }}>
                  Detalles
                </label>
                <textarea
                  value={formulario.detalles}
                  onChange={(e) => setFormulario({...formulario, detalles: e.target.value})}
                  rows="4"
                  style={{
                    width: '100%',
                    padding: '0.75rem',
                    border: '1px solid #e5e7eb',
                    borderRadius: '8px',
                    fontSize: '1rem',
                    resize: 'vertical'
                  }}
                />
              </div>
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '1rem', marginBottom: '1rem' }}>
                <div>
                  <label style={{ display: 'block', marginBottom: '0.5rem', fontWeight: '600', color: '#374151' }}>
                    Fecha Inicio *
                  </label>
                  <input
                    type="datetime-local"
                    required
                    value={formulario.fecha_inicio}
                    onChange={(e) => setFormulario({...formulario, fecha_inicio: e.target.value})}
                    style={{
                      width: '100%',
                      padding: '0.75rem',
                      border: '1px solid #e5e7eb',
                      borderRadius: '8px',
                      fontSize: '1rem'
                    }}
                  />
                </div>
                <div>
                  <label style={{ display: 'block', marginBottom: '0.5rem', fontWeight: '600', color: '#374151' }}>
                    Fecha Fin
                  </label>
                  <input
                    type="datetime-local"
                    value={formulario.fecha_fin}
                    onChange={(e) => setFormulario({...formulario, fecha_fin: e.target.value})}
                    style={{
                      width: '100%',
                      padding: '0.75rem',
                      border: '1px solid #e5e7eb',
                      borderRadius: '8px',
                      fontSize: '1rem'
                    }}
                  />
                </div>
              </div>
              <div style={{ display: 'flex', gap: '1rem', justifyContent: 'flex-end' }}>
                <button
                  type="button"
                  onClick={() => {
                    setMostrarFormulario(false);
                    setFormulario({
                      descripcion: '',
                      lugar: '',
                      detalles: '',
                      fecha_inicio: '',
                      fecha_fin: ''
                    });
                  }}
                  style={{
                    padding: '0.75rem 1.5rem',
                    background: '#f3f4f6',
                    color: '#374151',
                    border: 'none',
                    borderRadius: '8px',
                    cursor: 'pointer',
                    fontSize: '1rem',
                    fontWeight: '600'
                  }}
                >
                  Cancelar
                </button>
                <button
                  type="submit"
                  style={{
                    padding: '0.75rem 1.5rem',
                    background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                    color: 'white',
                    border: 'none',
                    borderRadius: '8px',
                    cursor: 'pointer',
                    fontSize: '1rem',
                    fontWeight: '600'
                  }}
                >
                  Crear Actividad
                </button>
              </div>
            </form>
          </div>
        )}

        <div className="actividades-main-container">
          <div className="meses-sidebar">
            <div className="sidebar-header">
              <h3>Filtrar por Mes</h3>
            </div>
            <div className="meses-list">
              <button
                className={`mes-item ${mesSeleccionado === null ? 'active' : ''}`}
                onClick={() => setMesSeleccionado(null)}
              >
                <span className="mes-icon">📆</span>
                <span className="mes-nombre">Todos</span>
                <span className="mes-count">
                  {todasLasActividades.length}
                </span>
              </button>
              {MESES.map((mes) => {
                const actividadesDelMes = todasLasActividades.filter(a => {
                  const fecha = new Date(a.fecha_inicio);
                  return fecha.getMonth() + 1 === mes.num;
                });
                const tieneActividades = actividadesDelMes.length > 0;
                const esMesActual = mes.num === mesActual;
                
                return (
                  <button
                    key={mes.num}
                    className={`mes-item ${mesSeleccionado === mes.num ? 'active' : ''} ${!tieneActividades ? 'sin-actividades' : ''} ${esMesActual ? 'mes-actual' : ''}`}
                    onClick={() => setMesSeleccionado(mes.num)}
                    disabled={!tieneActividades}
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
            {diasConActividades.length > 0 ? (
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

export default AdminActividades;

