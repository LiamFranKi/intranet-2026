import React, { useState, useEffect } from 'react';
import api from '../services/api';
import EventoModal from './EventoModal';
import './CalendarioWidget.css';

function CalendarioWidget() {
  const [fechaActual, setFechaActual] = useState(new Date());
  const [fechaSeleccionada, setFechaSeleccionada] = useState(new Date());
  const [actividades, setActividades] = useState([]);
  const [actividadesDelDia, setActividadesDelDia] = useState([]);
  const [mostrarModal, setMostrarModal] = useState(false);
  const [actividadSeleccionada, setActividadSeleccionada] = useState(null);

  const mesActual = fechaActual.getMonth();
  const añoActual = fechaActual.getFullYear();

  const primerDiaMes = new Date(añoActual, mesActual, 1).getDay();
  const diasEnMes = new Date(añoActual, mesActual + 1, 0).getDate();

  const diasSemana = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
  const meses = [
    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
  ];

  // Cargar actividades del mes actual
  useEffect(() => {
    cargarActividades();
  }, [añoActual, mesActual]);

  // Cargar actividades del día seleccionado
  useEffect(() => {
    cargarActividadesDelDia();
  }, [fechaSeleccionada]);

  const cargarActividades = async () => {
    try {
      const response = await api.get('/docente/actividades');
      setActividades(response.data.actividades || []);
    } catch (error) {
      console.error('Error cargando actividades:', error);
    }
  };

  const cargarActividadesDelDia = async () => {
    try {
      const fechaStr = fechaSeleccionada.toISOString().split('T')[0];
      const response = await api.get('/docente/actividades', {
        params: { fecha: fechaStr }
      });
      setActividadesDelDia(response.data.actividades || []);
    } catch (error) {
      console.error('Error cargando actividades del día:', error);
    }
  };

  const cambiarMes = (direccion) => {
    setFechaActual(new Date(añoActual, mesActual + direccion, 1));
  };

  const irAHoy = () => {
    const hoy = new Date();
    setFechaActual(hoy);
    setFechaSeleccionada(hoy);
  };

  const esHoy = (dia) => {
    const hoy = new Date();
    return (
      dia === hoy.getDate() &&
      mesActual === hoy.getMonth() &&
      añoActual === hoy.getFullYear()
    );
  };

  const esSeleccionado = (dia) => {
    return (
      dia === fechaSeleccionada.getDate() &&
      mesActual === fechaSeleccionada.getMonth() &&
      añoActual === fechaSeleccionada.getFullYear()
    );
  };

  // Verificar si un día tiene actividades
  const tieneActividades = (dia) => {
    if (!dia) return false;
    const fecha = new Date(añoActual, mesActual, dia);
    return actividades.some(act => {
      const fechaInicio = new Date(act.fecha_inicio);
      const fechaFin = act.fecha_fin ? new Date(act.fecha_fin) : fechaInicio;
      return fecha >= fechaInicio && fecha <= fechaFin;
    });
  };

  const handleDiaClick = (dia) => {
    if (dia) {
      const nuevaFecha = new Date(añoActual, mesActual, dia);
      setFechaSeleccionada(nuevaFecha);
    }
  };

  const handleActividadClick = (actividad) => {
    setActividadSeleccionada(actividad);
    setMostrarModal(true);
  };

  const dias = [];
  // Días del mes anterior (para completar la primera semana)
  for (let i = 0; i < primerDiaMes; i++) {
    dias.push(null);
  }
  // Días del mes actual
  for (let dia = 1; dia <= diasEnMes; dia++) {
    dias.push(dia);
  }

  return (
    <>
      <div className="calendario-widget">
        <h3 className="widget-title">Calendario</h3>
        <div className="calendario-header">
          <button className="btn-nav" onClick={() => cambiarMes(-1)}>‹</button>
          <div className="mes-ano">
            <span>{meses[mesActual]} {añoActual}</span>
            <button className="btn-hoy" onClick={irAHoy}>Hoy</button>
          </div>
          <button className="btn-nav" onClick={() => cambiarMes(1)}>›</button>
        </div>

        <div className="calendario-dias-semana">
          {diasSemana.map((dia) => (
            <div key={dia} className="dia-semana">
              {dia}
            </div>
          ))}
        </div>

        <div className="calendario-grid">
          {dias.map((dia, index) => (
            <div
              key={index}
              className={`calendario-dia ${dia === null ? 'empty' : ''} ${dia && esHoy(dia) ? 'hoy' : ''} ${dia && esSeleccionado(dia) ? 'seleccionado' : ''} ${dia && tieneActividades(dia) ? 'tiene-actividades' : ''}`}
              onClick={() => handleDiaClick(dia)}
            >
              {dia}
              {dia && tieneActividades(dia) && (
                <span className="actividad-indicador"></span>
              )}
            </div>
          ))}
        </div>

        {/* Lista de actividades del día seleccionado */}
        {actividadesDelDia.length > 0 && (
          <div className="actividades-del-dia">
            <h4 className="actividades-del-dia-title">
              {fechaSeleccionada.toLocaleDateString('es-PE', { 
                weekday: 'long', 
                day: 'numeric', 
                month: 'long' 
              })}
            </h4>
            <div className="actividades-lista-mini">
              {actividadesDelDia.slice(0, 3).map((act) => (
                <div 
                  key={act.id} 
                  className="actividad-mini-card"
                  onClick={() => handleActividadClick(act)}
                >
                  <span className="actividad-mini-hora">
                    {new Date(act.fecha_inicio).toLocaleTimeString('es-PE', { 
                      hour: '2-digit', 
                      minute: '2-digit' 
                    })}
                  </span>
                  <span className="actividad-mini-titulo">{act.descripcion}</span>
                </div>
              ))}
              {actividadesDelDia.length > 3 && (
                <div className="actividad-mas">
                  +{actividadesDelDia.length - 3} más
                </div>
              )}
            </div>
          </div>
        )}
      </div>

      {mostrarModal && actividadSeleccionada && (
        <EventoModal
          actividad={actividadSeleccionada}
          onClose={() => {
            setMostrarModal(false);
            setActividadSeleccionada(null);
          }}
        />
      )}
    </>
  );
}

export default CalendarioWidget;

