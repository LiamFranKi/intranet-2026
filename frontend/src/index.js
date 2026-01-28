import React from 'react';
import ReactDOM from 'react-dom/client';
import './index.css';
import App from './App';

// Suprimir advertencia de findDOMNode de ReactQuill (problema conocido de la librería)
// Esta advertencia no afecta la funcionalidad y será resuelta en futuras versiones de ReactQuill
const originalWarn = console.warn;
console.warn = (...args) => {
  if (
    typeof args[0] === 'string' &&
    args[0].includes('findDOMNode is deprecated')
  ) {
    // Suprimir solo la advertencia de findDOMNode
    return;
  }
  originalWarn.apply(console, args);
};

const root = ReactDOM.createRoot(document.getElementById('root'));
root.render(
  <React.StrictMode>
    <App />
  </React.StrictMode>
);

