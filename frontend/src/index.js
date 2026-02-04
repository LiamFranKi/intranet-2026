import React from 'react';
import ReactDOM from 'react-dom/client';
import './index.css';
import App from './App';

// Suprimir advertencia de findDOMNode de ReactQuill (problema conocido de la librería)
// Esta advertencia no afecta la funcionalidad y será resuelta en futuras versiones de ReactQuill
const originalWarn = console.warn;
const originalError = console.error;

console.warn = (...args) => {
  const message = args[0];
  if (
    (typeof message === 'string' && message.includes('findDOMNode is deprecated')) ||
    (typeof message === 'string' && message.includes('findDOMNode'))
  ) {
    // Suprimir solo la advertencia de findDOMNode
    return;
  }
  originalWarn.apply(console, args);
};

console.error = (...args) => {
  const message = args[0];
  if (
    (typeof message === 'string' && message.includes('findDOMNode is deprecated')) ||
    (typeof message === 'string' && message.includes('findDOMNode'))
  ) {
    // Suprimir solo el error de findDOMNode
    return;
  }
  originalError.apply(console, args);
};

// Registrar Service Worker para PWA con detección de actualizaciones
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/service-worker.js')
      .then((registration) => {
        console.log('✅ Service Worker registrado exitosamente:', registration.scope);
        
        // Verificar actualizaciones periódicamente
        // Cada vez que el usuario visita la página, se verifica automáticamente
        // También verificamos cada hora en segundo plano
        setInterval(() => {
          registration.update();
        }, 3600000); // 1 hora
        
        // Escuchar cuando hay una nueva versión del Service Worker disponible
        registration.addEventListener('updatefound', () => {
          const newWorker = registration.installing;
          
          if (newWorker) {
            newWorker.addEventListener('statechange', () => {
              if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                // Hay una nueva versión disponible
                console.log('🔄 Nueva versión del Service Worker disponible');
                
                // Notificar al usuario (opcional: puedes mostrar un toast o banner)
                if (window.showUpdateNotification) {
                  window.showUpdateNotification();
                } else {
                  // Notificación por defecto en consola
                  console.log('💡 Hay una nueva versión disponible. Recarga la página para actualizar.');
                }
              }
            });
          }
        });
        
        // Escuchar mensajes del Service Worker
        navigator.serviceWorker.addEventListener('message', (event) => {
          if (event.data && event.data.type === 'SW_UPDATED') {
            console.log('🔄 Service Worker actualizado:', event.data.version);
            // Opcional: mostrar notificación al usuario
            if (window.showUpdateNotification) {
              window.showUpdateNotification(event.data.message);
            }
          }
        });
      })
      .catch((error) => {
        console.warn('⚠️ Service Worker no se pudo registrar:', error);
      });
    
    // Verificar actualizaciones cuando la página recupera el foco
    window.addEventListener('focus', () => {
      navigator.serviceWorker.getRegistration().then((registration) => {
        if (registration) {
          registration.update();
        }
      });
    });
  });
}

const root = ReactDOM.createRoot(document.getElementById('root'));
root.render(
  <React.StrictMode>
    <App />
  </React.StrictMode>
);

