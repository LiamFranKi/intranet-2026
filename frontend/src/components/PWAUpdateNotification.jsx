import React, { useState, useEffect } from 'react';
import Swal from 'sweetalert2';
import './PWAUpdateNotification.css';

function PWAUpdateNotification() {
  const [updateAvailable, setUpdateAvailable] = useState(false);
  const [registration, setRegistration] = useState(null);

  useEffect(() => {
    // Función global para que el Service Worker pueda notificar actualizaciones
    window.showUpdateNotification = (message) => {
      setUpdateAvailable(true);
    };

    // Registrar listener para actualizaciones del Service Worker
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.getRegistration().then((reg) => {
        if (reg) {
          setRegistration(reg);
          
          // Escuchar cuando hay una nueva versión instalada
          reg.addEventListener('updatefound', () => {
            const newWorker = reg.installing;
            
            if (newWorker) {
              newWorker.addEventListener('statechange', () => {
                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                  // Hay una nueva versión disponible
                  setUpdateAvailable(true);
                }
              });
            }
          });
        }
      });
    }

    return () => {
      delete window.showUpdateNotification;
    };
  }, []);

  const handleUpdate = async () => {
    if (!registration || !registration.waiting) {
      // Si no hay worker esperando, simplemente recargar
      window.location.reload();
      return;
    }

    // Enviar mensaje al Service Worker para que se active
    registration.waiting.postMessage({ type: 'SKIP_WAITING' });
    
    // Esperar un momento y luego recargar
    setTimeout(() => {
      window.location.reload();
    }, 500);
  };

  // Mostrar notificación cuando hay actualización disponible
  useEffect(() => {
    if (updateAvailable) {
      Swal.fire({
        icon: 'info',
        title: 'Nueva versión disponible',
        html: `
          <p>Hay una nueva versión de la aplicación disponible.</p>
          <p style="font-size: 0.9rem; color: #6b7280; margin-top: 0.5rem;">
            ¿Deseas actualizar ahora?
          </p>
        `,
        showCancelButton: true,
        confirmButtonText: 'Actualizar ahora',
        cancelButtonText: 'Más tarde',
        confirmButtonColor: '#667eea',
        cancelButtonColor: '#6b7280',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
          // Auto-cerrar después de 10 segundos si no hay interacción
          setTimeout(() => {
            if (Swal.isVisible()) {
              Swal.close();
            }
          }, 10000);
        }
      }).then((result) => {
        if (result.isConfirmed) {
          handleUpdate();
        } else {
          // Si el usuario cancela, ocultar la notificación pero mantener el estado
          // para que pueda actualizar manualmente más tarde
          setUpdateAvailable(false);
        }
      });
    }
  }, [updateAvailable]);

  // Botón flotante para actualizar manualmente (si hay actualización disponible)
  if (updateAvailable && registration?.waiting) {
    return (
      <div className="pwa-update-notification">
        <button 
          className="pwa-update-btn"
          onClick={handleUpdate}
          aria-label="Actualizar aplicación"
          title="Hay una nueva versión disponible. Haz clic para actualizar."
        >
          🔄 Actualizar
        </button>
      </div>
    );
  }

  return null;
}

export default PWAUpdateNotification;

