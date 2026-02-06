import React, { useState } from 'react';

function PreguntaEmparejar({ pregunta, respuesta, onRespuestaChange }) {
  const alternativas = pregunta.alternativas || [];
  const emparejamientos = respuesta || {};
  const [seleccionadoIzquierda, setSeleccionadoIzquierda] = useState(null);

  // Dividir en dos columnas usando par_id
  // Columna izquierda: alternativas que tienen par_id (son el origen, se emparejan con otras)
  // Columna derecha: alternativas que son referenciadas por par_id de otras (son el destino)
  const columnaIzquierda = alternativas.filter(alt => {
    // Si tiene par_id, va a la izquierda (es el origen)
    return alt.par_id !== null && alt.par_id !== undefined;
  });
  
  // Columna derecha: todas las alternativas que son referenciadas por par_id
  const idsDerecha = new Set(columnaIzquierda.map(alt => alt.par_id).filter(Boolean));
  const columnaDerecha = alternativas.filter(alt => idsDerecha.has(alt.id));
  
  // Si no hay par_id definido, usar división por mitad como fallback
  let colIzqFinal = columnaIzquierda;
  let colDerFinal = columnaDerecha;
  
  if (colIzqFinal.length === 0 || colDerFinal.length === 0) {
    const mitad = Math.ceil(alternativas.length / 2);
    colIzqFinal = alternativas.slice(0, mitad);
    colDerFinal = alternativas.slice(mitad);
  }

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
          {colIzqFinal.map((alt) => (
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
          {colDerFinal.map((alt) => (
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

