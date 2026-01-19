import React from 'react';
import './EventoDetalleModal.css';

function EventoDetalleModal({ evento, tipo, onClose }) {
  if (!evento) return null;

  const getFechaEvento = () => {
    if (tipo === 'examen') {
      return evento.fecha_desde ? new Date(evento.fecha_desde) : null;
    } else if (tipo === 'tarea') {
      return evento.fecha_fin ? new Date(evento.fecha_fin) : null;
    } else if (tipo === 'actividad') {
      return evento.fecha_inicio ? new Date(evento.fecha_inicio) : null;
    }
    return null;
  };

  const fechaEvento = getFechaEvento();
  const fechaFin = tipo === 'actividad' && evento.fecha_fin ? new Date(evento.fecha_fin) : null;

  return (
    <div className="evento-detalle-modal-overlay" onClick={onClose}>
      <div className="evento-detalle-modal" onClick={(e) => e.stopPropagation()}>
        <div className={`evento-detalle-modal-header evento-tipo-${tipo}`}>
          <h2>
            {tipo === 'examen' && '📋 Examen'}
            {tipo === 'tarea' && '📝 Tarea'}
            {tipo === 'actividad' && '📅 Actividad'}
          </h2>
          <button className="evento-detalle-modal-close" onClick={onClose}>✕</button>
        </div>
        
        <div className="evento-detalle-modal-body">
          {/* Título/Descripción */}
          <div className="evento-detalle-item">
            <span className="evento-detalle-icon">📌</span>
            <div className="evento-detalle-content">
              <strong>Título:</strong>
              <span>
                {tipo === 'examen' && (evento.titulo || 'Examen')}
                {tipo === 'tarea' && (evento.descripcion || 'Tarea')}
                {tipo === 'actividad' && (evento.descripcion || 'Actividad')}
              </span>
            </div>
          </div>

          {/* Fecha */}
          {fechaEvento && (
            <div className="evento-detalle-item">
              <span className="evento-detalle-icon">📅</span>
              <div className="evento-detalle-content">
                <strong>
                  {tipo === 'examen' && 'Fecha del Examen:'}
                  {tipo === 'tarea' && 'Fecha Límite:'}
                  {tipo === 'actividad' && 'Fecha:'}
                </strong>
                <span>
                  {fechaEvento.toLocaleDateString('es-PE', {
                    weekday: 'long',
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric'
                  })}
                  {fechaFin && tipo === 'actividad' && fechaEvento.getTime() !== fechaFin.getTime() && (
                    <> - {fechaFin.toLocaleDateString('es-PE', {
                      day: 'numeric',
                      month: 'long',
                      year: 'numeric'
                    })}</>
                  )}
                </span>
              </div>
            </div>
          )}

          {/* Hora (si aplica) */}
          {tipo === 'actividad' && evento.fecha_inicio && (
            <div className="evento-detalle-item">
              <span className="evento-detalle-icon">🕐</span>
              <div className="evento-detalle-content">
                <strong>Hora:</strong>
                <span>
                  {new Date(evento.fecha_inicio).toLocaleTimeString('es-PE', {
                    hour: '2-digit',
                    minute: '2-digit'
                  })}
                  {fechaFin && (
                    <> - {fechaFin.toLocaleTimeString('es-PE', {
                      hour: '2-digit',
                      minute: '2-digit'
                    })}</>
                  )}
                </span>
              </div>
            </div>
          )}

          {/* Asignatura (para exámenes y tareas) */}
          {(tipo === 'examen' || tipo === 'tarea') && evento.asignatura_nombre && (
            <div className="evento-detalle-item">
              <span className="evento-detalle-icon">📚</span>
              <div className="evento-detalle-content">
                <strong>Asignatura:</strong>
                <span>{evento.asignatura_nombre}</span>
              </div>
            </div>
          )}

          {/* Grupo (para exámenes y tareas) */}
          {(tipo === 'examen' || tipo === 'tarea') && (evento.grado || evento.seccion) && (
            <div className="evento-detalle-item">
              <span className="evento-detalle-icon">👥</span>
              <div className="evento-detalle-content">
                <strong>Grupo:</strong>
                <span>
                  {evento.nivel_nombre && `${evento.nivel_nombre} `}
                  {evento.grado && `${evento.grado}°`}
                  {evento.seccion && ` ${evento.seccion}`}
                </span>
              </div>
            </div>
          )}

          {/* Lugar (para actividades) */}
          {tipo === 'actividad' && evento.lugar && (
            <div className="evento-detalle-item">
              <span className="evento-detalle-icon">📍</span>
              <div className="evento-detalle-content">
                <strong>Lugar:</strong>
                <span>{evento.lugar}</span>
              </div>
            </div>
          )}

          {/* Detalles */}
          {((tipo === 'actividad' && evento.detalles) || (tipo === 'tarea' && evento.descripcion)) && (
            <div className="evento-detalle-item evento-detalle-detalles">
              <span className="evento-detalle-icon">📝</span>
              <div className="evento-detalle-content">
                <strong>Detalles:</strong>
                <p>
                  {tipo === 'actividad' && evento.detalles}
                  {tipo === 'tarea' && evento.descripcion}
                </p>
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

export default EventoDetalleModal;

