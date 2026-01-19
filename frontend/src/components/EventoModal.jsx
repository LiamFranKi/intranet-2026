import React from 'react';
import './EventoModal.css';

function EventoModal({ actividad, onClose }) {
  if (!actividad) return null;

  const fechaInicio = new Date(actividad.fecha_inicio);
  const fechaFin = actividad.fecha_fin ? new Date(actividad.fecha_fin) : null;

  return (
    <div className="evento-modal-overlay" onClick={onClose}>
      <div className="evento-modal" onClick={(e) => e.stopPropagation()}>
        <div className="evento-modal-header">
          <h2>{actividad.descripcion}</h2>
          <button className="evento-modal-close" onClick={onClose}>✕</button>
        </div>
        
        <div className="evento-modal-body">
          <div className="evento-info-item">
            <span className="evento-info-icon">📅</span>
            <div className="evento-info-content">
              <strong>Fecha:</strong>
              <span>
                {fechaInicio.toLocaleDateString('es-PE', {
                  weekday: 'long',
                  day: 'numeric',
                  month: 'long',
                  year: 'numeric'
                })}
                {fechaFin && fechaInicio.getTime() !== fechaFin.getTime() && (
                  <> - {fechaFin.toLocaleDateString('es-PE', {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric'
                  })}</>
                )}
              </span>
            </div>
          </div>

          {actividad.fecha_inicio && (
            <div className="evento-info-item">
              <span className="evento-info-icon">🕐</span>
              <div className="evento-info-content">
                <strong>Hora:</strong>
                <span>
                  {fechaInicio.toLocaleTimeString('es-PE', {
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

          {actividad.lugar && (
            <div className="evento-info-item">
              <span className="evento-info-icon">📍</span>
              <div className="evento-info-content">
                <strong>Lugar:</strong>
                <span>{actividad.lugar}</span>
              </div>
            </div>
          )}

          {actividad.detalles && (
            <div className="evento-info-item evento-detalles">
              <span className="evento-info-icon">📝</span>
              <div className="evento-info-content">
                <strong>Detalles:</strong>
                <p>{actividad.detalles}</p>
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

export default EventoModal;
