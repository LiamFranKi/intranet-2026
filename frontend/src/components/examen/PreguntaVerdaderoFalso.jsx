import React from 'react';

function PreguntaVerdaderoFalso({ pregunta, respuesta, onRespuestaChange }) {
  // Buscar las alternativas "Verdadero" y "Falso"
  const alternativas = pregunta.alternativas || [];
  const alternativaVerdadero = alternativas.find(alt => {
    const desc = alt.descripcion ? alt.descripcion.replace(/<[^>]*>/g, '').trim().toLowerCase() : '';
    return desc === 'verdadero' || desc.includes('verdadero');
  });
  const alternativaFalso = alternativas.find(alt => {
    const desc = alt.descripcion ? alt.descripcion.replace(/<[^>]*>/g, '').trim().toLowerCase() : '';
    return desc === 'falso' || desc.includes('falso');
  });

  // Determinar qué alternativa está seleccionada (puede ser ID numérico o string 'VERDADERO'/'FALSO' para compatibilidad)
  const respuestaId = typeof respuesta === 'number' || (typeof respuesta === 'string' && !isNaN(parseInt(respuesta)))
    ? parseInt(respuesta)
    : null;
  
  const estaSeleccionadoVerdadero = alternativaVerdadero && (
    respuestaId === alternativaVerdadero.id || 
    respuesta === 'VERDADERO' || 
    respuesta === alternativaVerdadero.id
  );
  
  const estaSeleccionadoFalso = alternativaFalso && (
    respuestaId === alternativaFalso.id || 
    respuesta === 'FALSO' || 
    respuesta === alternativaFalso.id
  );

  const handleSeleccionar = (alternativa) => {
    if (alternativa && alternativa.id) {
      // Guardar el ID de la alternativa
      onRespuestaChange(alternativa.id);
    }
  };

  return (
    <div className="pregunta-verdadero-falso">
      <div 
        className="pregunta-descripcion"
        dangerouslySetInnerHTML={{ __html: pregunta.descripcion }}
      />
      
      <div className="vf-opciones">
        {alternativaVerdadero ? (
          <button
            type="button"
            className={`vf-btn ${estaSeleccionadoVerdadero ? 'selected' : ''}`}
            onClick={() => handleSeleccionar(alternativaVerdadero)}
          >
            ✅ Verdadero
          </button>
        ) : (
          <button
            type="button"
            className={`vf-btn ${estaSeleccionadoVerdadero ? 'selected' : ''}`}
            onClick={() => onRespuestaChange('VERDADERO')}
          >
            ✅ Verdadero
          </button>
        )}
        
        {alternativaFalso ? (
          <button
            type="button"
            className={`vf-btn ${estaSeleccionadoFalso ? 'selected' : ''}`}
            onClick={() => handleSeleccionar(alternativaFalso)}
          >
            ❌ Falso
          </button>
        ) : (
          <button
            type="button"
            className={`vf-btn ${estaSeleccionadoFalso ? 'selected' : ''}`}
            onClick={() => onRespuestaChange('FALSO')}
          >
            ❌ Falso
          </button>
        )}
      </div>
    </div>
  );
}

export default PreguntaVerdaderoFalso;

