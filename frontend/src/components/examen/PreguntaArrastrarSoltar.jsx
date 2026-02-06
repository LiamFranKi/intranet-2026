import React from 'react';
import { DndContext, DragOverlay, closestCenter, PointerSensor, useSensor, useSensors } from '@dnd-kit/core';
import { useDraggable, useDroppable } from '@dnd-kit/core';

function PreguntaArrastrarSoltar({ pregunta, respuesta, onRespuestaChange }) {
  const alternativas = pregunta.alternativas || [];
  const respuestasActuales = respuesta || {};
  const [activeId, setActiveId] = React.useState(null);

  // Obtener zonas únicas
  const zonasUnicas = [...new Set(alternativas
    .map(alt => alt.zona_drop)
    .filter(zona => zona && zona.trim() !== ''))];

  // Alternativas que el estudiante puede arrastrar (sin zona asignada aún)
  const alternativasArrastrables = alternativas.filter(alt => {
    const respuestaAlt = respuestasActuales[alt.id];
    return !respuestaAlt || !respuestaAlt.zona;
  });

  const sensors = useSensors(useSensor(PointerSensor));

  const handleDragStart = (event) => {
    setActiveId(event.active.id);
  };

  const handleDragEnd = (event) => {
    const { active, over } = event;
    setActiveId(null);
    
    if (!over) return;

    const alternativaId = active.id;
    const zonaId = over.id;

    onRespuestaChange({
      ...respuestasActuales,
      [alternativaId]: { zona: zonaId }
    });
  };

  return (
    <div className="pregunta-arrastrar-soltar">
      <div 
        className="pregunta-descripcion"
        dangerouslySetInnerHTML={{ __html: pregunta.descripcion }}
      />
      
      <p className="instrucciones-arrastrar">
        📌 Arrastra cada elemento a su zona correcta
      </p>

      <DndContext 
        sensors={sensors} 
        collisionDetection={closestCenter} 
        onDragStart={handleDragStart}
        onDragEnd={handleDragEnd}
      >
        <div className="arrastrar-soltar-container">
          {/* Zonas de Destino */}
          <div className="zonas-destino">
            {zonasUnicas.map((zona) => (
              <ZonaDrop 
                key={zona} 
                zona={zona} 
                alternativasEnZona={
                  Object.entries(respuestasActuales)
                    .filter(([_, resp]) => resp.zona === zona)
                    .map(([altId, _]) => alternativas.find(a => a.id === parseInt(altId)))
                    .filter(Boolean)
                } 
              />
            ))}
          </div>

          {/* Elementos a Arrastrar */}
          <div className="elementos-arrastrables">
            <h3>Arrastra estos elementos:</h3>
            {alternativasArrastrables.map((alt) => (
              <ElementoArrastrable key={alt.id} alternativa={alt} />
            ))}
          </div>
        </div>

        <DragOverlay>
          {activeId ? (
            <div className="elemento-arrastrable dragging-overlay">
              {alternativas.find(a => a.id === activeId)?.descripcion}
            </div>
          ) : null}
        </DragOverlay>
      </DndContext>
    </div>
  );
}

function ElementoArrastrable({ alternativa }) {
  const {
    attributes,
    listeners,
    setNodeRef,
    transform,
    isDragging,
  } = useDraggable({
    id: alternativa.id,
  });

  const style = {
    transform: transform ? `translate3d(${transform.x}px, ${transform.y}px, 0)` : undefined,
    opacity: isDragging ? 0.5 : 1,
  };

  return (
    <div
      ref={setNodeRef}
      style={style}
      className="elemento-arrastrable"
      {...listeners}
      {...attributes}
    >
      <div dangerouslySetInnerHTML={{ __html: alternativa.descripcion }} />
    </div>
  );
}

function ZonaDrop({ zona, alternativasEnZona }) {
  const { setNodeRef, isOver } = useDroppable({
    id: zona,
  });

  return (
    <div
      ref={setNodeRef}
      className={`zona-drop ${isOver ? 'drag-over' : ''}`}
    >
      <h4>{zona}</h4>
      <div className="zona-drop-content">
        {alternativasEnZona.map((alt) => (
          <div key={alt.id} className="elemento-en-zona">
            <div dangerouslySetInnerHTML={{ __html: alt.descripcion }} />
          </div>
        ))}
        {alternativasEnZona.length === 0 && (
          <div className="zona-vacia">Suelta aquí</div>
        )}
      </div>
    </div>
  );
}

export default PreguntaArrastrarSoltar;

