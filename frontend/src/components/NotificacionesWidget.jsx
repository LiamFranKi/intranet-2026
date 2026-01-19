import React, { useState, useEffect } from 'react';
import api from '../services/api';
import './NotificacionesWidget.css';

function NotificacionesWidget() {
  const [notificaciones, setNotificaciones] = useState([]);
  const [mostrarDropdown, setMostrarDropdown] = useState(false);

  useEffect(() => {
    cargarNotificaciones();
  }, []);

  const cargarNotificaciones = async () => {
    try {
      const response = await api.get('/docente/notificaciones');
      setNotificaciones(response.data.notificaciones || []);
    } catch (error) {
      console.error('Error cargando notificaciones:', error);
    }
  };

  const notificacionesNoLeidas = notificaciones.filter(n => n.estado === 'NO_LEIDO').length;

  return (
    <div className="notificaciones-widget">
      <button
        className="notificaciones-btn"
        onClick={() => setMostrarDropdown(!mostrarDropdown)}
        title="Notificaciones"
      >
        🔔
        {notificacionesNoLeidas > 0 && (
          <span className="notification-badge">{notificacionesNoLeidas}</span>
        )}
      </button>

      {mostrarDropdown && (
        <>
          <div
            className="notificaciones-overlay"
            onClick={() => setMostrarDropdown(false)}
          />
          <div className="notificaciones-dropdown">
            <div className="dropdown-header">
              <h3>Notificaciones</h3>
              {notificacionesNoLeidas > 0 && (
                <span className="badge-count">{notificacionesNoLeidas} nuevas</span>
              )}
            </div>
            <div className="notificaciones-list">
              {notificaciones.length > 0 ? (
                notificaciones.map((notif) => (
                  <div
                    key={notif.id}
                    className={`notificacion-item ${notif.estado === 'NO_LEIDO' ? 'no-leida' : ''}`}
                  >
                    <div className="notificacion-icon">🔔</div>
                    <div className="notificacion-content">
                      <h4>{notif.titulo}</h4>
                      <p>{notif.mensaje}</p>
                      <span className="notificacion-fecha">
                        {new Date(notif.fecha).toLocaleDateString('es-PE')}
                      </span>
                    </div>
                  </div>
                ))
              ) : (
                <div className="empty-notificaciones">
                  <p>No hay notificaciones</p>
                </div>
              )}
            </div>
          </div>
        </>
      )}
    </div>
  );
}

export default NotificacionesWidget;

