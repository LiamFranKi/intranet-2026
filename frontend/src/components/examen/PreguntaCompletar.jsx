import React, { useMemo } from 'react';

function PreguntaCompletar({ pregunta, respuesta, onRespuestaChange }) {
  // Parsear la descripción para encontrar los campos [[...]]
  const partes = useMemo(() => {
    const texto = pregunta.descripcion || '';
    const regex = /\[\[(.*?)\]\]/g;
    const resultado = [];
    let lastIndex = 0;
    let match;
    let campoIndex = 0;

    while ((match = regex.exec(texto)) !== null) {
      // Texto antes del campo
      if (match.index > lastIndex) {
        resultado.push({
          tipo: 'texto',
          contenido: texto.substring(lastIndex, match.index)
        });
      }
      
      // Campo a completar
      resultado.push({
        tipo: 'campo',
        index: campoIndex++,
        respuestaCorrecta: match[1]
      });
      
      lastIndex = regex.lastIndex;
    }

    // Texto después del último campo
    if (lastIndex < texto.length) {
      resultado.push({
        tipo: 'texto',
        contenido: texto.substring(lastIndex)
      });
    }

    return resultado;
  }, [pregunta.descripcion]);

  const respuestasActuales = respuesta || {};

  const actualizarCampo = (index, valor) => {
    onRespuestaChange({
      ...respuestasActuales,
      [index]: valor
    });
  };

  return (
    <div className="pregunta-completar">
      <div className="pregunta-completar-texto">
        {partes.map((parte, i) => {
          if (parte.tipo === 'texto') {
            return (
              <span 
                key={i}
                dangerouslySetInnerHTML={{ __html: parte.contenido }}
              />
            );
          } else {
            return (
              <input
                key={i}
                type="text"
                className="campo-completar"
                value={respuestasActuales[parte.index] || ''}
                onChange={(e) => actualizarCampo(parte.index, e.target.value)}
                placeholder="Escribe aquí..."
              />
            );
          }
        })}
      </div>
    </div>
  );
}

export default PreguntaCompletar;

