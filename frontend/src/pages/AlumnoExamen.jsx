import React, { useState, useEffect, useRef, useCallback } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useExamProtection } from '../hooks/useExamProtection';
import api from '../services/api';
import Swal from 'sweetalert2';
import './AlumnoExamen.css';

function AlumnoExamen() {
  const { examenId } = useParams();
  const navigate = useNavigate();
  
  const [examen, setExamen] = useState(null);
  const [preguntas, setPreguntas] = useState([]);
  const [respuestas, setRespuestas] = useState({});
  const [preguntaActual, setPreguntaActual] = useState(0);
  const [tiempoRestante, setTiempoRestante] = useState(0);
  const [loading, setLoading] = useState(true);
  const [guardando, setGuardando] = useState(false);
  const [mostrarResumen, setMostrarResumen] = useState(false);
  
  const intervaloGuardado = useRef(null);
  const intervaloReloj = useRef(null);
  const tiempoInicio = useRef(null);

  // Cargar examen y preguntas
  useEffect(() => {
    cargarExamen();
    return () => {
      if (intervaloGuardado.current) clearInterval(intervaloGuardado.current);
      if (intervaloReloj.current) clearInterval(intervaloReloj.current);
    };
  }, [examenId]);

  // Protección de examen
  const handleViolation = useCallback((violation) => {
    // Registrar violación en el backend
    api.post(`/alumno/examenes/${examenId}/violaciones`, {
      tipo: violation.type,
      timestamp: violation.timestamp
    }).catch(err => console.error('Error registrando violación:', err));
    
    Swal.fire({
      icon: 'warning',
      title: '⚠️ Advertencia',
      text: 'Has salido de la ventana del examen. Por favor, mantén la ventana activa.',
      timer: 3000,
      showConfirmButton: false,
      toast: true,
      position: 'top-end'
    });
  }, [examenId]);

  useExamProtection(handleViolation, false);

  // Temporizador
  useEffect(() => {
    if (examen && examen.tiempo > 0 && tiempoInicio.current) {
      const calcularTiempoRestante = () => {
        const ahora = new Date();
        const diferencia = Math.max(0, Math.floor((tiempoInicio.current + (examen.tiempo * 60 * 1000) - ahora) / 1000));
        setTiempoRestante(diferencia);
        
        if (diferencia <= 0) {
          finalizarExamenAutomatico();
        }
      };

      calcularTiempoRestante();
      
      intervaloReloj.current = setInterval(calcularTiempoRestante, 1000);

      // Alerta cuando quedan 5 minutos
      if (tiempoRestante <= 300 && tiempoRestante > 0) {
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

      return () => {
        if (intervaloReloj.current) {
          clearInterval(intervaloReloj.current);
        }
      };
    }
  }, [examen, tiempoRestante]);

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
      setLoading(true);
      
      // Iniciar examen (crear prueba)
      const iniciarRes = await api.post(`/alumno/examenes/${examenId}/iniciar`);
      tiempoInicio.current = new Date(iniciarRes.data.fecha_inicio);
      
      // Cargar datos del examen y preguntas
      const [examenRes, preguntasRes] = await Promise.all([
        api.get(`/alumno/examenes/${examenId}`),
        api.get(`/alumno/examenes/${examenId}/preguntas`)
      ]);

      setExamen(examenRes.data);
      
      // Cargar respuestas guardadas si existen
      if (iniciarRes.data.respuestas) {
        setRespuestas(iniciarRes.data.respuestas);
      }
      
      // Ordenar preguntas según configuración del examen
      let preguntasOrdenadas = preguntasRes.data.preguntas || [];
      if (examenRes.data.orden_preguntas === 'ALEATORIO') {
        preguntasOrdenadas = [...preguntasOrdenadas].sort(() => Math.random() - 0.5);
      }
      
      setPreguntas(preguntasOrdenadas);
      setLoading(false);
    } catch (error) {
      console.error('Error cargando examen:', error);
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: error.response?.data?.error || 'No se pudo cargar el examen'
      }).then(() => {
        navigate(-1);
      });
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
      const response = await api.post(`/alumno/examenes/${examenId}/finalizar`);
      
      Swal.fire({
        icon: 'info',
        title: 'Tiempo agotado',
        text: 'El examen se ha finalizado automáticamente',
        confirmButtonText: 'Ver resultados'
      }).then(() => {
        navigate(`/alumno/examen/${examenId}/resultados`);
      });
    } catch (error) {
      console.error('Error finalizando examen:', error);
    }
  };

  const handleFinalizarExamen = async () => {
    // Mostrar resumen primero
    setMostrarResumen(true);
  };

  const handleConfirmarFinalizar = async () => {
    setMostrarResumen(false);
    
    const result = await Swal.fire({
      title: '¿Finalizar examen?',
      text: '¿Estás seguro de que deseas finalizar el examen? No podrás modificarlo después.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Sí, finalizar',
      cancelButtonText: 'No, volver al examen'
    });

    if (result.isConfirmed) {
      await guardarRespuestas();
      await finalizarExamenAutomatico();
    }
  };

  const handleCancelarFinalizar = () => {
    setMostrarResumen(false);
  };

  const formatearTiempo = (segundos) => {
    const horas = Math.floor(segundos / 3600);
    const minutos = Math.floor((segundos % 3600) / 60);
    const segs = segundos % 60;
    
    if (horas > 0) {
      return `${horas}:${minutos.toString().padStart(2, '0')}:${segs.toString().padStart(2, '0')}`;
    }
    return `${minutos}:${segs.toString().padStart(2, '0')}`;
  };

  if (loading) {
    return (
      <div className="examen-loading">
        <div className="loading-spinner">Cargando examen...</div>
      </div>
    );
  }

  if (!examen || preguntas.length === 0) {
    return (
      <div className="examen-error">
        <p>No se pudo cargar el examen</p>
        <button onClick={() => navigate(-1)}>Volver</button>
      </div>
    );
  }

  const pregunta = preguntas[preguntaActual];
  const totalPreguntas = preguntas.length;
  const progreso = ((preguntaActual + 1) / totalPreguntas) * 100;
  const porcentajeRestante = examen.tiempo > 0 
    ? (tiempoRestante / (examen.tiempo * 60)) * 100 
    : 100;
  const colorTimer = tiempoRestante <= 60 ? '#ef4444' : tiempoRestante <= 300 ? '#f59e0b' : '#10b981';

  if (mostrarResumen) {
    return (
      <div className="examen-resumen-container">
        <div className="examen-resumen-card">
          <h2>📋 Resumen del Examen</h2>
          <div className="resumen-preguntas">
            {preguntas.map((p, index) => {
              const respuesta = respuestas[p.id];
              const tieneRespuesta = respuesta !== null && respuesta !== undefined && respuesta !== '';
              return (
                <div key={p.id} className={`resumen-pregunta-item ${tieneRespuesta ? 'respondida' : 'sin-responder'}`}>
                  <div className="resumen-pregunta-numero">{index + 1}</div>
                  <div className="resumen-pregunta-info">
                    <div className="resumen-pregunta-titulo" dangerouslySetInnerHTML={{ __html: p.descripcion?.substring(0, 100) + '...' || 'Pregunta sin título' }} />
                    <div className="resumen-pregunta-estado">
                      {tieneRespuesta ? '✓ Respondida' : '⚠️ Sin responder'}
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
          <div className="resumen-acciones">
            <button className="btn-resumen-cancelar" onClick={handleCancelarFinalizar}>
              ← Volver al Examen
            </button>
            <button className="btn-resumen-finalizar" onClick={handleConfirmarFinalizar}>
              ✓ Finalizar Examen
            </button>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="examen-container">
      {/* Header del Examen */}
      <div className="examen-header">
        <div className="examen-header-left">
          <h2>📝 {examen.titulo}</h2>
        </div>
        <div className="examen-header-right">
          {examen.tiempo > 0 && (
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
          )}
          <button 
            className="btn-finalizar-examen"
            onClick={handleFinalizarExamen}
          >
            ✓ Finalizar Examen
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
                // Guardar inmediatamente al cambiar respuesta
                setTimeout(() => guardarRespuestas(), 500);
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

// Componente para renderizar preguntas según su tipo
function RenderizarPregunta({ pregunta, respuesta, onRespuestaChange }) {
  // Por ahora, implementación básica - se expandirá después
  return (
    <div className="pregunta-container">
      <div 
        className="pregunta-descripcion"
        dangerouslySetInnerHTML={{ __html: pregunta.descripcion }}
      />
      <p style={{ color: '#6b7280', marginTop: '1rem' }}>
        Tipo: {pregunta.tipo} - Implementación pendiente
      </p>
    </div>
  );
}

export default AlumnoExamen;

