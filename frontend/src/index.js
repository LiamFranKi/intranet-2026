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

// Registrar Service Worker para PWA
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/service-worker.js')
      .then((registration) => {
        console.log('✅ Service Worker registrado exitosamente:', registration.scope);
        
        // Verificar actualizaciones cada hora
        setInterval(() => {
          registration.update();
        }, 3600000); // 1 hora
      })
      .catch((error) => {
        console.warn('⚠️ Service Worker no se pudo registrar:', error);
      });
  });
}

const root = ReactDOM.createRoot(document.getElementById('root'));
root.render(
  <React.StrictMode>
    <App />
  </React.StrictMode>
);

