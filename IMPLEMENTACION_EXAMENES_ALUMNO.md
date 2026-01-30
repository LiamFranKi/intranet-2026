# 📝 IMPLEMENTACIÓN DE EXÁMENES PARA ALUMNOS - GUÍA TÉCNICA

**Fecha:** Enero 2026  
**Estado:** Documentación Técnica - Pendiente de Implementación  
**Módulo:** Alumno → Exámenes

---

## 📋 ÍNDICE

1. [Resumen Ejecutivo](#resumen-ejecutivo)
2. [Arquitectura de la Vista del Estudiante](#arquitectura-de-la-vista-del-estudiante)
3. [Prevención de Salir de la Ventana](#prevención-de-salir-de-la-ventana)
4. [Diseño de la Interfaz](#diseño-de-la-interfaz)
5. [Implementación por Tipo de Pregunta](#implementación-por-tipo-de-pregunta)
6. [Temporizador y Guardado Automático](#temporizador-y-guardado-automático)
7. [Endpoints del Backend Necesarios](#endpoints-del-backend-necesarios)
8. [Estructura de Componentes](#estructura-de-componentes)

---

## 🎯 RESUMEN EJECUTIVO

### Objetivo
Crear una interfaz moderna, intuitiva y segura para que los estudiantes puedan tomar exámenes en línea desde tablets, celulares o computadoras, con las siguientes características:

- ✅ **Diseño moderno y responsive**
- ✅ **Prevención de salir de la ventana/pestaña**
- ✅ **Soporte para todos los tipos de preguntas**
- ✅ **Temporizador visible**
- ✅ **Guardado automático de respuestas**
- ✅ **Interfaz drag & drop para ARRASTRAR_Y_SOLTAR**
- ✅ **Interfaz visual para EMPAREJAR**
- ✅ **Vista previa antes de enviar**

---

## 🏗️ ARQUITECTURA DE LA VISTA DEL ESTUDIANTE

### Flujo del Examen

```
1. Alumno ve lista de exámenes disponibles
   ↓
2. Alumno selecciona un examen
   ↓
3. Sistema verifica:
   - ¿Tiene intentos disponibles?
   - ¿Está en el rango de fechas?
   - ¿Ya lo completó?
   ↓
4. Si puede hacerlo → Inicia examen
   - Activa modo pantalla completa
   - Activa prevención de salida
   - Carga preguntas
   - Inicia temporizador
   ↓
5. Alumno responde preguntas
   - Guardado automático cada X segundos
   - Temporizador visible
   ↓
6. Alumno finaliza examen
   - Confirmación
   - Envío de respuestas
   - Calificación automática
   - Mostrar resultados
```

---

## 🔒 PREVENCIÓN DE SALIR DE LA VENTANA

### Implementación Completa

```javascript
// hooks/useExamProtection.js
import { useEffect, useRef, useState } from 'react';

export const useExamProtection = (onViolation, autoFinish = false) => {
  const [violations, setViolations] = useState(0);
  const violationCountRef = useRef(0);
  const fullscreenEnabled = useRef(false);

  useEffect(() => {
    // 1. Intentar entrar en pantalla completa
    const enableFullscreen = async () => {
      try {
        if (document.documentElement.requestFullscreen) {
          await document.documentElement.requestFullscreen();
          fullscreenEnabled.current = true;
        } else if (document.documentElement.webkitRequestFullscreen) {
          await document.documentElement.webkitRequestFullscreen();
          fullscreenEnabled.current = true;
        } else if (document.documentElement.mozRequestFullScreen) {
          await document.documentElement.mozRequestFullScreen();
          fullscreenEnabled.current = true;
        }
      } catch (error) {
        console.warn('No se pudo activar pantalla completa:', error);
      }
    };

    enableFullscreen();

    // 2. Detectar cambio de pestaña/ventana (Page Visibility API)
    const handleVisibilityChange = () => {
      if (document.hidden) {
        violationCountRef.current += 1;
        setViolations(violationCountRef.current);
        
        if (onViolation) {
          onViolation({
            type: 'TAB_CHANGE',
            count: violationCountRef.current,
            timestamp: new Date()
          });
        }

        // Si autoFinish está activado y hay muchas violaciones
        if (autoFinish && violationCountRef.current >= 3) {
          // Finalizar examen automáticamente
          if (window.confirm('Has salido de la ventana del examen múltiples veces. ¿Deseas finalizar el examen ahora?')) {
            // Llamar función para finalizar
          }
        }
      }
    };

    // 3. Detectar pérdida de foco de la ventana
    const handleBlur = () => {
      violationCountRef.current += 1;
      setViolations(violationCountRef.current);
      
      if (onViolation) {
        onViolation({
          type: 'WINDOW_BLUR',
          count: violationCountRef.current,
          timestamp: new Date()
        });
      }
    };

    // 4. Prevenir cierre accidental del navegador
    const handleBeforeUnload = (e) => {
      e.preventDefault();
      e.returnValue = '⚠️ ¿Estás seguro de salir? El examen se finalizará automáticamente.';
      return e.returnValue;
    };

    // 5. Detectar teclas de atajo (F11, Alt+Tab, etc.)
    const handleKeyDown = (e) => {
      // Bloquear F11 (salir de pantalla completa)
      if (e.key === 'F11') {
        e.preventDefault();
      }
      
      // Bloquear Alt+Tab (en algunos navegadores)
      if (e.altKey && e.key === 'Tab') {
        e.preventDefault();
      }
      
      // Bloquear Ctrl+W (cerrar pestaña)
      if (e.ctrlKey && e.key === 'w') {
        e.preventDefault();
        alert('⚠️ No puedes cerrar la pestaña durante el examen');
      }
    };

    // 6. Detectar intento de salir de pantalla completa
    const handleFullscreenChange = () => {
      if (!document.fullscreenElement && 
          !document.webkitFullscreenElement && 
          !document.mozFullScreenElement) {
        // El usuario salió de pantalla completa
        violationCountRef.current += 1;
        setViolations(violationCountRef.current);
        
        if (onViolation) {
          onViolation({
            type: 'FULLSCREEN_EXIT',
            count: violationCountRef.current,
            timestamp: new Date()
          });
        }

        // Intentar volver a pantalla completa
        enableFullscreen();
      }
    };

    // Registrar event listeners
    document.addEventListener('visibilitychange', handleVisibilityChange);
    window.addEventListener('blur', handleBlur);
    window.addEventListener('beforeunload', handleBeforeUnload);
    document.addEventListener('keydown', handleKeyDown);
    document.addEventListener('fullscreenchange', handleFullscreenChange);
    document.addEventListener('webkitfullscreenchange', handleFullscreenChange);
    document.addEventListener('mozfullscreenchange', handleFullscreenChange);

    // Cleanup
    return () => {
      document.removeEventListener('visibilitychange', handleVisibilityChange);
      window.removeEventListener('blur', handleBlur);
      window.removeEventListener('beforeunload', handleBeforeUnload);
      document.removeEventListener('keydown', handleKeyDown);
      document.removeEventListener('fullscreenchange', handleFullscreenChange);
      document.removeEventListener('webkitfullscreenchange', handleFullscreenChange);
      document.removeEventListener('mozfullscreenchange', handleFullscreenChange);
    };
  }, [onViolation, autoFinish]);

  return { violations, fullscreenEnabled: fullscreenEnabled.current };
};
```

### Uso en el Componente de Examen

```javascript
import { useExamProtection } from '../hooks/useExamProtection';

function ExamenAlumno({ examenId, pruebaId }) {
  const [violations, setViolations] = useState([]);

  const handleViolation = (violation) => {
    setViolations(prev => [...prev, violation]);
    
    // Registrar en el backend
    api.post(`/alumno/examenes/${examenId}/violaciones`, {
      tipo: violation.type,
      timestamp: violation.timestamp
    });

    // Mostrar advertencia al estudiante
    Swal.fire({
      icon: 'warning',
      title: '⚠️ Advertencia',
      text: 'Has salido de la ventana del examen. Por favor, mantén la ventana activa.',
      timer: 3000,
      showConfirmButton: false
    });
  };

  useExamProtection(handleViolation, false); // autoFinish = false por defecto

  // ... resto del componente
}
```

---

## 🎨 DISEÑO DE LA INTERFAZ

### Estructura Visual

```
┌─────────────────────────────────────────────────────────┐
│  [EXAMEN: Nombre del Examen]        ⏱️ 45:30  [X] Salir  │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  📝 Pregunta 3 de 10                                     │
│  ────────────────────────────────────────────────────  │
│                                                          │
│  ¿Cuál es la capital del Perú?                          │
│                                                          │
│  ○ A) Cusco                                             │
│  ○ B) Lima                                              │
│  ○ C) Arequipa                                          │
│  ○ D) Trujillo                                          │
│                                                          │
│  [← Anterior]              [Siguiente →]                │
│                                                          │
├─────────────────────────────────────────────────────────┤
│  Progreso: ████████░░░░░░░░░░ 30%                       │
│  [1] [2] [3] [4] [5] [6] [7] [8] [9] [10]              │
└─────────────────────────────────────────────────────────┘
```

### Componente Principal

```javascript
// pages/AlumnoExamen.jsx
import React, { useState, useEffect, useRef } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useExamProtection } from '../hooks/useExamProtection';
import api from '../services/api';
import Swal from 'sweetalert2';
import './AlumnoExamen.css';

function AlumnoExamen() {
  const { examenId, pruebaId } = useParams();
  const navigate = useNavigate();
  
  const [examen, setExamen] = useState(null);
  const [preguntas, setPreguntas] = useState([]);
  const [respuestas, setRespuestas] = useState({});
  const [preguntaActual, setPreguntaActual] = useState(0);
  const [tiempoRestante, setTiempoRestante] = useState(0);
  const [loading, setLoading] = useState(true);
  const [guardando, setGuardando] = useState(false);
  
  const intervaloGuardado = useRef(null);
  const intervaloReloj = useRef(null);

  // Cargar examen y preguntas
  useEffect(() => {
    cargarExamen();
  }, [examenId, pruebaId]);

  // Protección de examen
  const handleViolation = (violation) => {
    // Registrar violación
    api.post(`/alumno/examenes/${examenId}/violaciones`, violation);
    
    Swal.fire({
      icon: 'warning',
      title: '⚠️ Advertencia',
      text: 'Has salido de la ventana del examen. Por favor, mantén la ventana activa.',
      timer: 3000,
      showConfirmButton: false,
      toast: true,
      position: 'top-end'
    });
  };

  useExamProtection(handleViolation, false);

  // Temporizador
  useEffect(() => {
    if (examen && examen.tiempo > 0) {
      const calcularTiempoRestante = async () => {
        const response = await api.get(`/alumno/examenes/${examenId}/tiempo-restante`);
        setTiempoRestante(response.data.tiempo_restante);
      };

      calcularTiempoRestante();
      
      intervaloReloj.current = setInterval(() => {
        setTiempoRestante(prev => {
          if (prev <= 1) {
            finalizarExamenAutomatico();
            return 0;
          }
          return prev - 1;
        });
      }, 1000);
    }

    return () => {
      if (intervaloReloj.current) {
        clearInterval(intervaloReloj.current);
      }
    };
  }, [examen]);

  // Guardado automático cada 30 segundos
  useEffect(() => {
    intervaloGuardado.current = setInterval(() => {
      guardarRespuestas();
    }, 30000); // Cada 30 segundos

    return () => {
      if (intervaloGuardado.current) {
        clearInterval(intervaloGuardado.current);
      }
    };
  }, [respuestas]);

  const cargarExamen = async () => {
    try {
      const [examenRes, preguntasRes] = await Promise.all([
        api.get(`/alumno/examenes/${examenId}`),
        api.get(`/alumno/examenes/${examenId}/preguntas`)
      ]);

      setExamen(examenRes.data);
      setPreguntas(preguntasRes.data.preguntas || []);
      setLoading(false);
    } catch (error) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'No se pudo cargar el examen'
      });
      navigate('/alumno/aula-virtual');
    }
  };

  const guardarRespuestas = async () => {
    if (guardando) return;
    
    setGuardando(true);
    try {
      await api.post(`/alumno/examenes/${examenId}/respuestas`, {
        respuestas: respuestas
      });
    } catch (error) {
      console.error('Error guardando respuestas:', error);
    } finally {
      setGuardando(false);
    }
  };

  const finalizarExamenAutomatico = async () => {
    await guardarRespuestas();
    
    try {
      await api.post(`/alumno/examenes/${examenId}/finalizar`);
      
      Swal.fire({
        icon: 'info',
        title: 'Tiempo agotado',
        text: 'El examen se ha finalizado automáticamente',
        confirmButtonText: 'Ver resultados'
      }).then(() => {
        navigate(`/alumno/examenes/${examenId}/resultados`);
      });
    } catch (error) {
      console.error('Error finalizando examen:', error);
    }
  };

  const handleFinalizarExamen = async () => {
    const result = await Swal.fire({
      title: '¿Finalizar examen?',
      text: '¿Estás seguro de que deseas finalizar el examen? No podrás modificarlo después.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Sí, finalizar',
      cancelButtonText: 'Cancelar'
    });

    if (result.isConfirmed) {
      await finalizarExamenAutomatico();
    }
  };

  if (loading) {
    return <div className="examen-loading">Cargando examen...</div>;
  }

  const pregunta = preguntas[preguntaActual];
  const totalPreguntas = preguntas.length;
  const progreso = ((preguntaActual + 1) / totalPreguntas) * 100;

  return (
    <div className="examen-container">
      {/* Header del Examen */}
      <div className="examen-header">
        <div className="examen-header-left">
          <h2>📝 {examen.titulo}</h2>
        </div>
        <div className="examen-header-right">
          {examen.tiempo > 0 && (
            <div className="examen-timer">
              ⏱️ {formatearTiempo(tiempoRestante)}
            </div>
          )}
          <button 
            className="btn-salir-examen"
            onClick={handleFinalizarExamen}
          >
            Finalizar Examen
          </button>
        </div>
      </div>

      {/* Contenido Principal */}
      <div className="examen-content">
        {/* Barra de Progreso */}
        <div className="examen-progress">
          <div className="examen-progress-bar">
            <div 
              className="examen-progress-fill" 
              style={{ width: `${progreso}%` }}
            ></div>
          </div>
          <div className="examen-progress-text">
            Pregunta {preguntaActual + 1} de {totalPreguntas}
          </div>
        </div>

        {/* Navegación de Preguntas (Miniaturas) */}
        <div className="examen-navegacion">
          {preguntas.map((p, index) => (
            <button
              key={p.id}
              className={`examen-nav-btn ${index === preguntaActual ? 'active' : ''} ${respuestas[p.id] ? 'answered' : ''}`}
              onClick={() => setPreguntaActual(index)}
            >
              {index + 1}
            </button>
          ))}
        </div>

        {/* Pregunta Actual */}
        <div className="examen-pregunta-container">
          {pregunta && (
            <RenderizarPregunta
              pregunta={pregunta}
              respuesta={respuestas[pregunta.id] || null}
              onRespuestaChange={(respuesta) => {
                setRespuestas(prev => ({
                  ...prev,
                  [pregunta.id]: respuesta
                }));
              }}
            />
          )}
        </div>

        {/* Navegación */}
        <div className="examen-navegacion-botones">
          <button
            className="btn-examen-nav"
            onClick={() => setPreguntaActual(prev => Math.max(0, prev - 1))}
            disabled={preguntaActual === 0}
          >
            ← Anterior
          </button>
          <button
            className="btn-examen-nav"
            onClick={() => setPreguntaActual(prev => Math.min(totalPreguntas - 1, prev + 1))}
            disabled={preguntaActual === totalPreguntas - 1}
          >
            Siguiente →
          </button>
        </div>
      </div>

      {/* Indicador de Guardado */}
      {guardando && (
        <div className="examen-guardando">
          💾 Guardando respuestas...
        </div>
      )}
    </div>
  );
}

// Función auxiliar para formatear tiempo
function formatearTiempo(segundos) {
  const horas = Math.floor(segundos / 3600);
  const minutos = Math.floor((segundos % 3600) / 60);
  const segs = segundos % 60;
  
  if (horas > 0) {
    return `${horas}:${minutos.toString().padStart(2, '0')}:${segs.toString().padStart(2, '0')}`;
  }
  return `${minutos}:${segs.toString().padStart(2, '0')}`;
}

export default AlumnoExamen;
```

---

## 📝 IMPLEMENTACIÓN POR TIPO DE PREGUNTA

### Componente RenderizarPregunta

```javascript
// components/RenderizarPregunta.jsx
import React from 'react';
import PreguntaAlternativas from './tipos/PreguntaAlternativas';
import PreguntaCompletar from './tipos/PreguntaCompletar';
import PreguntaVerdaderoFalso from './tipos/PreguntaVerdaderoFalso';
import PreguntaRespuestaCorta from './tipos/PreguntaRespuestaCorta';
import PreguntaOrdenar from './tipos/PreguntaOrdenar';
import PreguntaEmparejar from './tipos/PreguntaEmparejar';
import PreguntaArrastrarSoltar from './tipos/PreguntaArrastrarSoltar';

function RenderizarPregunta({ pregunta, respuesta, onRespuestaChange }) {
  const props = {
    pregunta,
    respuesta,
    onRespuestaChange
  };

  switch (pregunta.tipo) {
    case 'ALTERNATIVAS':
      return <PreguntaAlternativas {...props} />;
    case 'COMPLETAR':
      return <PreguntaCompletar {...props} />;
    case 'VERDADERO_FALSO':
      return <PreguntaVerdaderoFalso {...props} />;
    case 'RESPUESTA_CORTA':
      return <PreguntaRespuestaCorta {...props} />;
    case 'ORDENAR':
      return <PreguntaOrdenar {...props} />;
    case 'EMPAREJAR':
      return <PreguntaEmparejar {...props} />;
    case 'ARRASTRAR_Y_SOLTAR':
      return <PreguntaArrastrarSoltar {...props} />;
    default:
      return <div>Tipo de pregunta no soportado</div>;
  }
}

export default RenderizarPregunta;
```

### 1. ALTERNATIVAS (Opción Múltiple)

```javascript
// components/tipos/PreguntaAlternativas.jsx
function PreguntaAlternativas({ pregunta, respuesta, onRespuestaChange }) {
  return (
    <div className="pregunta-alternativas">
      <div 
        className="pregunta-descripcion"
        dangerouslySetInnerHTML={{ __html: pregunta.descripcion }}
      />
      
      <div className="alternativas-lista">
        {pregunta.alternativas.map((alt) => (
          <label 
            key={alt.id} 
            className={`alternativa-item ${respuesta === alt.id ? 'selected' : ''}`}
          >
            <input
              type="radio"
              name={`pregunta-${pregunta.id}`}
              value={alt.id}
              checked={respuesta === alt.id}
              onChange={(e) => onRespuestaChange(parseInt(e.target.value))}
            />
            <span 
              className="alternativa-texto"
              dangerouslySetInnerHTML={{ __html: alt.descripcion }}
            />
          </label>
        ))}
      </div>
    </div>
  );
}
```

### 2. VERDADERO_FALSO

```javascript
// components/tipos/PreguntaVerdaderoFalso.jsx
function PreguntaVerdaderoFalso({ pregunta, respuesta, onRespuestaChange }) {
  return (
    <div className="pregunta-verdadero-falso">
      <div 
        className="pregunta-descripcion"
        dangerouslySetInnerHTML={{ __html: pregunta.descripcion }}
      />
      
      <div className="vf-opciones">
        <button
          className={`vf-btn ${respuesta === 'VERDADERO' ? 'selected' : ''}`}
          onClick={() => onRespuestaChange('VERDADERO')}
        >
          ✅ Verdadero
        </button>
        <button
          className={`vf-btn ${respuesta === 'FALSO' ? 'selected' : ''}`}
          onClick={() => onRespuestaChange('FALSO')}
        >
          ❌ Falso
        </button>
      </div>
    </div>
  );
}
```

### 3. COMPLETAR

```javascript
// components/tipos/PreguntaCompletar.jsx
function PreguntaCompletar({ pregunta, respuesta, onRespuestaChange }) {
  // Parsear la descripción para encontrar los campos [[...]]
  const parsearCompletar = (texto) => {
    const regex = /\[\[(.*?)\]\]/g;
    const partes = [];
    let lastIndex = 0;
    let match;
    let campoIndex = 0;

    while ((match = regex.exec(texto)) !== null) {
      // Texto antes del campo
      if (match.index > lastIndex) {
        partes.push({
          tipo: 'texto',
          contenido: texto.substring(lastIndex, match.index)
        });
      }
      
      // Campo a completar
      partes.push({
        tipo: 'campo',
        index: campoIndex++,
        respuestaCorrecta: match[1]
      });
      
      lastIndex = regex.lastIndex;
    }

    // Texto después del último campo
    if (lastIndex < texto.length) {
      partes.push({
        tipo: 'texto',
        contenido: texto.substring(lastIndex)
      });
    }

    return partes;
  };

  const partes = parsearCompletar(pregunta.descripcion);
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
```

### 4. RESPUESTA_CORTA

```javascript
// components/tipos/PreguntaRespuestaCorta.jsx
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
        rows="6"
      />
    </div>
  );
}
```

### 5. ORDENAR

```javascript
// components/tipos/PreguntaOrdenar.jsx
import { DndContext, closestCenter, KeyboardSensor, PointerSensor, useSensor, useSensors } from '@dnd-kit/core';
import { arrayMove, SortableContext, sortableKeyboardCoordinates, useSortable, verticalListSortingStrategy } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';

function PreguntaOrdenar({ pregunta, respuesta, onRespuestaChange }) {
  const alternativas = pregunta.alternativas || [];
  const ordenActual = respuesta || alternativas.map((alt, i) => alt.id);

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
```

### 6. EMPAREJAR

```javascript
// components/tipos/PreguntaEmparejar.jsx
import { useState, useEffect } from 'react';

function PreguntaEmparejar({ pregunta, respuesta, onRespuestaChange }) {
  const alternativas = pregunta.alternativas || [];
  const emparejamientos = respuesta || {};

  // Dividir en dos columnas (izquierda y derecha)
  // Por ahora, asumimos que las alternativas están en orden
  // La primera mitad va a la izquierda, la segunda a la derecha
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
              className={`emparejar-item izquierda ${emparejamientos[alt.id] ? 'emparejado' : ''}`}
              onClick={() => {
                // Lógica para seleccionar y emparejar
                setSeleccionadoIzquierda(alt.id);
              }}
            >
              <div 
                dangerouslySetInnerHTML={{ __html: alt.descripcion }}
              />
            </div>
          ))}
        </div>

        <div className="emparejar-líneas">
          {/* Mostrar líneas de conexión */}
          {Object.entries(emparejamientos).map(([izqId, derId]) => {
            const izq = columnaIzquierda.find(a => a.id === parseInt(izqId));
            const der = columnaDerecha.find(a => a.id === derId);
            if (!izq || !der) return null;
            
            return (
              <div key={`${izqId}-${derId}`} className="linea-emparejamiento">
                {/* SVG línea de conexión */}
              </div>
            );
          })}
        </div>

        <div className="emparejar-columna derecha">
          <h3>Columna B</h3>
          {columnaDerecha.map((alt) => (
            <div
              key={alt.id}
              className={`emparejar-item derecha ${Object.values(emparejamientos).includes(alt.id) ? 'emparejado' : ''}`}
              onClick={() => {
                if (seleccionadoIzquierda) {
                  handleEmparejar(seleccionadoIzquierda, alt.id);
                  setSeleccionadoIzquierda(null);
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
```

### 7. ARRASTRAR_Y_SOLTAR

```javascript
// components/tipos/PreguntaArrastrarSoltar.jsx
import { DndContext, DragOverlay, closestCenter, PointerSensor, useSensor, useSensors } from '@dnd-kit/core';
import { useDraggable, useDroppable } from '@dnd-kit/core';

function PreguntaArrastrarSoltar({ pregunta, respuesta, onRespuestaChange }) {
  const alternativas = pregunta.alternativas || [];
  const respuestasActuales = respuesta || {};

  // Obtener zonas únicas
  const zonasUnicas = [...new Set(alternativas
    .map(alt => alt.zona_drop)
    .filter(zona => zona && zona.trim() !== ''))];

  // Agrupar alternativas por zona
  const alternativasPorZona = zonasUnicas.reduce((acc, zona) => {
    acc[zona] = alternativas.filter(alt => alt.zona_drop === zona);
    return acc;
  }, {});

  // Alternativas que el estudiante puede arrastrar (sin zona asignada aún)
  const alternativasArrastrables = alternativas.filter(alt => 
    !respuestasActuales[alt.id] || !respuestasActuales[alt.id].zona
  );

  const sensors = useSensors(useSensor(PointerSensor));

  const handleDragEnd = (event) => {
    const { active, over } = event;
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

      <DndContext sensors={sensors} collisionDetection={closestCenter} onDragEnd={handleDragEnd}>
        <div className="arrastrar-soltar-container">
          {/* Zonas de Destino */}
          <div className="zonas-destino">
            {zonasUnicas.map((zona) => (
              <ZonaDrop key={zona} zona={zona} alternativasEnZona={
                Object.entries(respuestasActuales)
                  .filter(([_, resp]) => resp.zona === zona)
                  .map(([altId, _]) => alternativas.find(a => a.id === parseInt(altId)))
                  .filter(Boolean)
              } />
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
```

---

## ⏱️ TEMPORIZADOR Y GUARDADO AUTOMÁTICO

### Temporizador Visual

```javascript
// components/ExamenTimer.jsx
function ExamenTimer({ tiempoRestante, onTimeUp }) {
  const [alertaMostrada, setAlertaMostrada] = useState(false);

  useEffect(() => {
    // Alerta cuando quedan 5 minutos
    if (tiempoRestante <= 300 && tiempoRestante > 0 && !alertaMostrada) {
      setAlertaMostrada(true);
      Swal.fire({
        icon: 'warning',
        title: '⏰ 5 minutos restantes',
        text: 'Te quedan 5 minutos para finalizar el examen',
        timer: 5000,
        showConfirmButton: false
      });
    }

    // Alerta cuando quedan 1 minuto
    if (tiempoRestante <= 60 && tiempoRestante > 0) {
      Swal.fire({
        icon: 'error',
        title: '⏰ 1 minuto restante',
        text: '¡Apúrate! Te queda 1 minuto',
        timer: 3000,
        showConfirmButton: false
      });
    }

    if (tiempoRestante <= 0) {
      onTimeUp();
    }
  }, [tiempoRestante, onTimeUp]);

  const formatearTiempo = (segundos) => {
    const horas = Math.floor(segundos / 3600);
    const minutos = Math.floor((segundos % 3600) / 60);
    const segs = segundos % 60;
    
    if (horas > 0) {
      return `${horas}:${minutos.toString().padStart(2, '0')}:${segs.toString().padStart(2, '0')}`;
    }
    return `${minutos}:${segs.toString().padStart(2, '0')}`;
  };

  const porcentajeRestante = examen.tiempo > 0 
    ? (tiempoRestante / (examen.tiempo * 60)) * 100 
    : 100;

  const colorTimer = tiempoRestante <= 300 ? '#ef4444' : tiempoRestante <= 600 ? '#f59e0b' : '#10b981';

  return (
    <div className="examen-timer" style={{ color: colorTimer }}>
      <div className="timer-icon">⏱️</div>
      <div className="timer-text">{formatearTiempo(tiempoRestante)}</div>
      <div className="timer-bar">
        <div 
          className="timer-bar-fill" 
          style={{ 
            width: `${porcentajeRestante}%`,
            backgroundColor: colorTimer
          }}
        ></div>
      </div>
    </div>
  );
}
```

---

## 🔌 ENDPOINTS DEL BACKEND NECESARIOS

### Endpoints Requeridos

```javascript
// backend/routes/alumno.routes.js

/**
 * GET /api/alumno/examenes
 * Listar exámenes disponibles para el alumno
 */
router.get('/examenes', async (req, res) => {
  // Retornar exámenes activos, en rango de fechas, con intentos disponibles
});

/**
 * GET /api/alumno/examenes/:examenId
 * Obtener detalles de un examen
 */
router.get('/examenes/:examenId', async (req, res) => {
  // Retornar información del examen
});

/**
 * POST /api/alumno/examenes/:examenId/iniciar
 * Iniciar un examen (crear prueba)
 */
router.post('/examenes/:examenId/iniciar', async (req, res) => {
  // Crear registro en asignaturas_examenes_pruebas
  // Retornar prueba_id y token
});

/**
 * GET /api/alumno/examenes/:examenId/preguntas
 * Obtener preguntas del examen (con alternativas)
 */
router.get('/examenes/:examenId/preguntas', async (req, res) => {
  // Retornar preguntas con sus alternativas
  // NO incluir información de respuestas correctas
});

/**
 * GET /api/alumno/examenes/:examenId/tiempo-restante
 * Obtener tiempo restante del examen
 */
router.get('/examenes/:examenId/tiempo-restante', async (req, res) => {
  // Calcular tiempo restante basado en expiracion
});

/**
 * POST /api/alumno/examenes/:examenId/respuestas
 * Guardar respuestas del estudiante
 */
router.post('/examenes/:examenId/respuestas', async (req, res) => {
  // Guardar respuestas en asignaturas_examenes_pruebas_respuestas
});

/**
 * POST /api/alumno/examenes/:examenId/finalizar
 * Finalizar examen
 */
router.post('/examenes/:examenId/finalizar', async (req, res) => {
  // Calificar examen
  // Actualizar estado a FINALIZADA
  // Retornar resultados
});

/**
 * GET /api/alumno/examenes/:examenId/resultados
 * Obtener resultados del examen
 */
router.get('/examenes/:examenId/resultados', async (req, res) => {
  // Retornar calificación, respuestas correctas/incorrectas, etc.
});

/**
 * POST /api/alumno/examenes/:examenId/violaciones
 * Registrar violación (salir de ventana)
 */
router.post('/examenes/:examenId/violaciones', async (req, res) => {
  // Guardar registro de violación en auditoría o tabla específica
});
```

---

## 🎨 ESTRUCTURA DE COMPONENTES

```
frontend/src/
├── pages/
│   └── AlumnoExamen.jsx          # Componente principal
├── components/
│   ├── examen/
│   │   ├── RenderizarPregunta.jsx
│   │   ├── ExamenTimer.jsx
│   │   ├── ExamenProgress.jsx
│   │   └── ExamenNavigation.jsx
│   └── tipos/
│       ├── PreguntaAlternativas.jsx
│       ├── PreguntaCompletar.jsx
│       ├── PreguntaVerdaderoFalso.jsx
│       ├── PreguntaRespuestaCorta.jsx
│       ├── PreguntaOrdenar.jsx
│       ├── PreguntaEmparejar.jsx
│       └── PreguntaArrastrarSoltar.jsx
├── hooks/
│   └── useExamProtection.js      # Hook de protección
└── styles/
    └── AlumnoExamen.css          # Estilos del examen
```

---

## 📱 DISEÑO RESPONSIVE

### Breakpoints

```css
/* AlumnoExamen.css */

.examen-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 1rem;
}

/* Tablet */
@media (max-width: 768px) {
  .examen-container {
    padding: 0.5rem;
  }
  
  .examen-header {
    flex-direction: column;
    gap: 1rem;
  }
  
  .emparejar-container {
    flex-direction: column;
  }
}

/* Mobile */
@media (max-width: 480px) {
  .examen-navegacion {
    flex-wrap: wrap;
    gap: 0.25rem;
  }
  
  .examen-nav-btn {
    width: 32px;
    height: 32px;
    font-size: 0.75rem;
  }
}
```

---

## ✅ CHECKLIST DE IMPLEMENTACIÓN

### Fase 1: Estructura Base
- [ ] Crear componente `AlumnoExamen.jsx`
- [ ] Crear hook `useExamProtection.js`
- [ ] Crear estilos base `AlumnoExamen.css`
- [ ] Implementar endpoints del backend

### Fase 2: Tipos de Preguntas
- [ ] ALTERNATIVAS
- [ ] VERDADERO_FALSO
- [ ] COMPLETAR
- [ ] RESPUESTA_CORTA
- [ ] ORDENAR
- [ ] EMPAREJAR
- [ ] ARRASTRAR_Y_SOLTAR

### Fase 3: Funcionalidades
- [ ] Temporizador
- [ ] Guardado automático
- [ ] Navegación entre preguntas
- [ ] Barra de progreso
- [ ] Vista previa antes de enviar

### Fase 4: Protección
- [ ] Pantalla completa
- [ ] Detección de cambio de pestaña
- [ ] Prevención de cierre
- [ ] Registro de violaciones

### Fase 5: Resultados
- [ ] Página de resultados
- [ ] Mostrar calificación
- [ ] Mostrar respuestas correctas/incorrectas
- [ ] Feedback por pregunta

---

## 📝 NOTAS IMPORTANTES

1. **Seguridad**: Las respuestas correctas NO deben enviarse al frontend hasta que el examen esté finalizado.

2. **Performance**: El guardado automático debe ser eficiente y no bloquear la UI.

3. **UX**: El diseño debe ser claro, intuitivo y funcionar bien en móviles.

4. **Accesibilidad**: Asegurar que sea accesible para todos los estudiantes.

5. **Testing**: Probar en diferentes dispositivos y navegadores antes de lanzar.

---

**Este documento será la guía cuando implementemos el módulo de alumnos. ¿Quieres que agregue algo más o que detalle alguna sección específica?**

