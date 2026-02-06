import React from 'react';

function PreguntaVerdaderoFalso({ pregunta, respuesta, onRespuestaChange }) {
  return (
    <div className="pregunta-verdadero-falso">
      <div 
        className="pregunta-descripcion"
        dangerouslySetInnerHTML={{ __html: pregunta.descripcion }}
      />
      
      <div className="vf-opciones">
        <button
          type="button"
          className={`vf-btn ${respuesta === 'VERDADERO' ? 'selected' : ''}`}
          onClick={() => onRespuestaChange('VERDADERO')}
        >
          ✅ Verdadero
        </button>
        <button
          type="button"
          className={`vf-btn ${respuesta === 'FALSO' ? 'selected' : ''}`}
          onClick={() => onRespuestaChange('FALSO')}
        >
          ❌ Falso
        </button>
      </div>
    </div>
  );
}

export default PreguntaVerdaderoFalso;

