import React, { useState } from 'react';

function PreguntaEmparejar({ pregunta, respuesta, onRespuestaChange }) {
  const alternativas = pregunta.alternativas || [];
  const emparejamientos = respuesta || {};
  const [seleccionadoIzquierda, setSeleccionadoIzquierda] = useState(null);

  // Dividir en dos columnas (izquierda y derecha)
  const mitad = Math.ceil(alternativas.length / 2);
  const columnaIzquierda = alternativas.slice(0, mitad);
  const columnaDerecha = alternativas.slice(mitad);

  const handleEmparejar = (izquierdaId, derechaId) => {
    const nuevoEmparejamiento = { ...emparejamientos };
    
    // Si ya estaba emparejado, desemparejar
    if (nuevoEmparejamiento[izquierdaId] === derechaId) {
      delete nuevoEmparejamiento[izquierdaId];
    } else {
      // Si la derecha ya estaba emparejada con otra, desemparejar primero
      Object.keys(nuevoEmparejamiento).forEach(key => {
        if (nuevoEmparejamiento[key] === derechaId) {
          delete nuevoEmparejamiento[key];
        }
      });
      
      nuevoEmparejamiento[izquierdaId] = derechaId;
    }
    
    onRespuestaChange(nuevoEmparejamiento);
    setSeleccionadoIzquierda(null);
  };

  return (
    <div className="pregunta-emparejar">
      <div 
        className="pregunta-descripcion"
        dangerouslySetInnerHTML={{ __html: pregunta.descripcion }}
      />
      
      <p className="instrucciones-emparejar">
        📌 Haz clic en un elemento de la izquierda y luego en uno de la derecha para emparejarlos
      </p>

      <div className="emparejar-container">
        <div className="emparejar-columna izquierda">
          <h3>Columna A</h3>
          {columnaIzquierda.map((alt) => (
            <div
              key={alt.id}
              className={`emparejar-item izquierda ${seleccionadoIzquierda === alt.id ? 'seleccionado' : ''} ${emparejamientos[alt.id] ? 'emparejado' : ''}`}
              onClick={() => setSeleccionadoIzquierda(alt.id)}
            >
              <div 
                dangerouslySetInnerHTML={{ __html: alt.descripcion }}
              />
            </div>
          ))}
        </div>

        <div className="emparejar-columna derecha">
          <h3>Columna B</h3>
          {columnaDerecha.map((alt) => (
            <div
              key={alt.id}
              className={`emparejar-item derecha ${Object.values(emparejamientos).includes(alt.id) ? 'emparejado' : ''} ${seleccionadoIzquierda && emparejamientos[seleccionadoIzquierda] === alt.id ? 'conectado' : ''}`}
              onClick={() => {
                if (seleccionadoIzquierda) {
                  handleEmparejar(seleccionadoIzquierda, alt.id);
                }
              }}
            >
              <div 
                dangerouslySetInnerHTML={{ __html: alt.descripcion }}
              />
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}

export default PreguntaEmparejar;

