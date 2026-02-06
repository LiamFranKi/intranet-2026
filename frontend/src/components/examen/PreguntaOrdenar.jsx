import React from 'react';
import { DndContext, closestCenter, KeyboardSensor, PointerSensor, useSensor, useSensors } from '@dnd-kit/core';
import { arrayMove, SortableContext, sortableKeyboardCoordinates, useSortable, verticalListSortingStrategy } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';

function PreguntaOrdenar({ pregunta, respuesta, onRespuestaChange }) {
  const alternativas = pregunta.alternativas || [];
  const ordenActual = respuesta || alternativas.map((alt) => alt.id);

  const sensors = useSensors(
    useSensor(PointerSensor),
    useSensor(KeyboardSensor, {
      coordinateGetter: sortableKeyboardCoordinates,
    })
  );

  const handleDragEnd = (event) => {
    const { active, over } = event;
    if (!over || active.id === over.id) return;

    const oldIndex = ordenActual.findIndex(id => id === active.id);
    const newIndex = ordenActual.findIndex(id => id === over.id);

    if (oldIndex !== -1 && newIndex !== -1) {
      const nuevoOrden = arrayMove(ordenActual, oldIndex, newIndex);
      onRespuestaChange(nuevoOrden);
    }
  };

  const alternativasOrdenadas = ordenActual
    .map(id => alternativas.find(alt => alt.id === id))
    .filter(Boolean);

  return (
    <div className="pregunta-ordenar">
      <div 
        className="pregunta-descripcion"
        dangerouslySetInnerHTML={{ __html: pregunta.descripcion }}
      />
      
      <p className="instrucciones-ordenar">
        📌 Arrastra los elementos para ordenarlos correctamente
      </p>

      <DndContext sensors={sensors} collisionDetection={closestCenter} onDragEnd={handleDragEnd}>
        <SortableContext items={ordenActual} strategy={verticalListSortingStrategy}>
          <div className="ordenar-lista">
            {alternativasOrdenadas.map((alt, index) => (
              <ItemOrdenable
                key={alt.id}
                id={alt.id}
                alternativa={alt}
                posicion={index + 1}
              />
            ))}
          </div>
        </SortableContext>
      </DndContext>
    </div>
  );
}

function ItemOrdenable({ id, alternativa, posicion }) {
  const {
    attributes,
    listeners,
    setNodeRef,
    transform,
    transition,
    isDragging,
  } = useSortable({ id });

  const style = {
    transform: CSS.Transform.toString(transform),
    transition,
    opacity: isDragging ? 0.5 : 1,
  };

  return (
    <div
      ref={setNodeRef}
      style={style}
      className={`item-ordenable ${isDragging ? 'dragging' : ''}`}
    >
      <div className="item-ordenable-handle" {...attributes} {...listeners}>
        ⋮⋮
      </div>
      <div className="item-ordenable-posicion">{posicion}</div>
      <div 
        className="item-ordenable-texto"
        dangerouslySetInnerHTML={{ __html: alternativa.descripcion }}
      />
    </div>
  );
}

export default PreguntaOrdenar;

