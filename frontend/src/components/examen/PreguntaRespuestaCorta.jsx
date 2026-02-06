import React from 'react';

function PreguntaRespuestaCorta({ pregunta, respuesta, onRespuestaChange }) {
  return (
    <div className="pregunta-respuesta-corta">
      <div 
        className="pregunta-descripcion"
        dangerouslySetInnerHTML={{ __html: pregunta.descripcion }}
      />
      
      <textarea
        className="respuesta-corta-input"
        value={respuesta || ''}
        onChange={(e) => onRespuestaChange(e.target.value)}
        placeholder="Escribe tu respuesta aquí..."
        rows="8"
      />
    </div>
  );
}

export default PreguntaRespuestaCorta;

