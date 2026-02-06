import React, { useState, useEffect, useRef, useCallback } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useExamProtection } from '../hooks/useExamProtection';
import api from '../services/api';
import Swal from 'sweetalert2';
import PreguntaAlternativas from '../components/examen/PreguntaAlternativas';
import PreguntaCompletar from '../components/examen/PreguntaCompletar';
import PreguntaVerdaderoFalso from '../components/examen/PreguntaVerdaderoFalso';
import PreguntaRespuestaCorta from '../components/examen/PreguntaRespuestaCorta';
import PreguntaOrdenar from '../components/examen/PreguntaOrdenar';
import PreguntaEmparejar from '../components/examen/PreguntaEmparejar';
import PreguntaArrastrarSoltar from '../components/examen/PreguntaArrastrarSoltar';
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
  const [error, setError] = useState(null);
  const [renderError, setRenderError] = useState(null);
  
  const intervaloGuardado = useRef(null);
  const intervaloReloj = useRef(null);
  const tiempoInicio = useRef(null);
  const tiempoExpiracion = useRef(null);
  const respuestasRef = useRef({});

  // Función para cargar examen
  const cargarExamen = useCallback(async () => {
    try {
      console.log('📝 Iniciando carga de examen:', examenId);
      setLoading(true);
      setError(null);
      
      // Iniciar examen (crear prueba)
      console.log('📝 Llamando a /alumno/examenes/' + examenId + '/iniciar');
      const iniciarRes = await api.post(`/alumno/examenes/${examenId}/iniciar`);
      console.log('📝 Respuesta iniciar:', iniciarRes.data);
      const fechaInicio = new Date(iniciarRes.data.fecha_inicio);
      tiempoInicio.current = fechaInicio;
      
      if (iniciarRes.data.fecha_expiracion) {
        tiempoExpiracion.current = new Date(iniciarRes.data.fecha_expiracion);
      }
      
      // Cargar datos del examen y preguntas
      console.log('📝 Cargando datos del examen y preguntas');
      const [examenRes, preguntasRes] = await Promise.all([
        api.get(`/alumno/examenes/${examenId}`),
        api.get(`/alumno/examenes/${examenId}/preguntas`)
      ]);

      console.log('📝 Examen cargado:', examenRes.data);
      console.log('📝 Preguntas cargadas:', preguntasRes.data.preguntas?.length || 0);
      setExamen(examenRes.data);
      
      // Cargar respuestas guardadas si existen
      if (iniciarRes.data.respuestas) {
        setRespuestas(iniciarRes.data.respuestas);
        respuestasRef.current = iniciarRes.data.respuestas;
      }
      
      // Ordenar preguntas según configuración del examen
      let preguntasOrdenadas = preguntasRes.data.preguntas || [];
      if (examenRes.data.orden_preguntas === 'ALEATORIO') {
        preguntasOrdenadas = [...preguntasOrdenadas].sort(() => Math.random() - 0.5);
      }
      
      // Aplicar límite de preguntas a mostrar (preguntas_max)
      if (examenRes.data.preguntas_max && examenRes.data.preguntas_max > 0) {
        preguntasOrdenadas = preguntasOrdenadas.slice(0, examenRes.data.preguntas_max);
      }
      
      // Aleatorizar alternativas si el orden es ALEATORIO
      if (examenRes.data.orden_preguntas === 'ALEATORIO') {
        preguntasOrdenadas = preguntasOrdenadas.map(pregunta => ({
          ...pregunta,
          alternativas: pregunta.alternativas 
            ? [...pregunta.alternativas].sort(() => Math.random() - 0.5)
            : []
        }));
      }
      
      // Guardar los IDs de las preguntas que verá el alumno (para que el docente vea las mismas)
      const preguntaIds = preguntasOrdenadas.map(p => p.id);
      try {
        await api.post(`/alumno/examenes/${examenId}/guardar-preguntas`, {
          pregunta_ids: preguntaIds
        });
      } catch (error) {
        console.warn('No se pudieron guardar las preguntas:', error);
      }
      
      setPreguntas(preguntasOrdenadas);
      console.log('📝 Preguntas ordenadas:', preguntasOrdenadas.length);
      setLoading(false);
      setError(null);
    } catch (error) {
      console.error('Error cargando examen:', error);
      setLoading(false);
      setError(error.response?.data?.error || 'No se pudo cargar el examen');
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: error.response?.data?.error || 'No se pudo cargar el examen',
        confirmButtonText: 'Volver'
      }).then(() => {
        navigate(-1);
      });
    }
  }, [examenId, navigate]);

  // Cargar examen al montar
  useEffect(() => {
    if (examenId) {
      cargarExamen();
    }
    return () => {
      if (intervaloGuardado.current) clearInterval(intervaloGuardado.current);
      if (intervaloReloj.current) clearInterval(intervaloReloj.current);
    };
  }, [examenId, cargarExamen]);

  // Protección de examen
  const handleViolation = useCallback((violation) => {
    // Registrar violación en el backend (sin bloquear si falla)
    api.post(`/alumno/examenes/${examenId}/violaciones`, {
      tipo: violation.type,
      timestamp: violation.timestamp
    }).catch(() => {
      // Silenciar errores de violaciones para no saturar la consola
    });
    
    // Solo mostrar advertencia si no es un blur repetido
    if (violation.count <= 3) {
      Swal.fire({
        icon: 'warning',
        title: '⚠️ Advertencia',
        text: 'Has salido de la ventana del examen. Por favor, mantén la ventana activa.',
        timer: 3000,
        showConfirmButton: false,
        toast: true,
        position: 'top-end'
      });
    }
  }, [examenId]);

  // Protección de examen (siempre activa, pero solo funciona cuando hay examen)
  // El hook debe estar siempre presente, pero el callback verifica si hay examen
  const safeHandleViolation = useCallback((violation) => {
    if (examen && !loading) {
      handleViolation(violation);
    }
  }, [examen, loading, handleViolation]);

  // Protección de examen - el hook debe estar siempre presente
  useExamProtection(safeHandleViolation, false);

  // Función para guardar respuestas (debe estar antes de las funciones que la usan)
  const guardarRespuestas = useCallback(async () => {
    if (guardando) return;
    
    setGuardando(true);
    try {
      // Usar respuestasRef para obtener el valor más actualizado
      await api.post(`/alumno/examenes/${examenId}/respuestas`, {
        respuestas: respuestasRef.current
      });
    } catch (error) {
      console.error('Error guardando respuestas:', error);
    } finally {
      setGuardando(false);
    }
  }, [examenId, guardando]);

  // Función para finalizar examen automáticamente
  const finalizarExamenAutomatico = useCallback(async () => {
    await guardarRespuestas();
    
    try {
      const response = await api.post(`/alumno/examenes/${examenId}/finalizar`);
      
        Swal.fire({
          icon: 'info',
          title: 'Tiempo agotado',
          text: 'El examen se ha finalizado automáticamente. Podrás ver tus resultados cuando el docente los publique.',
          confirmButtonText: 'Entendido'
        }).then(() => {
          // Volver al aula virtual
          navigate(-1);
        });
    } catch (error) {
      console.error('Error finalizando examen:', error);
    }
  }, [examenId, navigate, guardarRespuestas]);

  // Temporizador
  useEffect(() => {
    if (examen && examen.tiempo > 0 && (tiempoInicio.current || tiempoExpiracion.current)) {
      const calcularTiempoRestante = () => {
        const ahora = new Date();
        let tiempoLimite;
        
        if (tiempoExpiracion.current) {
          tiempoLimite = tiempoExpiracion.current;
        } else if (tiempoInicio.current) {
          tiempoLimite = new Date(tiempoInicio.current.getTime() + (examen.tiempo * 60 * 1000));
        } else {
          return;
        }
        
        const diferencia = Math.max(0, Math.floor((tiempoLimite - ahora) / 1000));
        setTiempoRestante(diferencia);
        
        if (diferencia <= 0) {
          finalizarExamenAutomatico();
        }
      };

      calcularTiempoRestante();
      
      intervaloReloj.current = setInterval(calcularTiempoRestante, 1000);

      return () => {
        if (intervaloReloj.current) {
          clearInterval(intervaloReloj.current);
        }
      };
    }
  }, [examen, finalizarExamenAutomatico]);

  // Alertas de tiempo
  useEffect(() => {
    if (examen && examen.tiempo > 0 && tiempoRestante > 0) {
      // Alerta cuando quedan 5 minutos
      if (tiempoRestante === 300) {
        Swal.fire({
          icon: 'warning',
          title: '⏰ 5 minutos restantes',
          text: 'Te quedan 5 minutos para finalizar el examen',
          timer: 5000,
          showConfirmButton: false
        });
      }

      // Alerta cuando quedan 1 minuto
      if (tiempoRestante === 60) {
        Swal.fire({
          icon: 'error',
          title: '⏰ 1 minuto restante',
          text: '¡Apúrate! Te queda 1 minuto',
          timer: 3000,
          showConfirmButton: false
        });
      }
    }
  }, [tiempoRestante, examen]);

  // Actualizar ref cuando cambian las respuestas
  useEffect(() => {
    respuestasRef.current = respuestas;
  }, [respuestas]);

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
  }, [guardarRespuestas]);

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
      
      try {
        await api.post(`/alumno/examenes/${examenId}/finalizar`);
        
        Swal.fire({
          icon: 'success',
          title: 'Examen finalizado',
          text: 'Tu examen ha sido enviado correctamente. Podrás ver tus resultados cuando el docente los publique.',
          confirmButtonText: 'Entendido'
        }).then(() => {
          // Volver al aula virtual
          navigate(-1);
        });
      } catch (error) {
        console.error('Error finalizando examen:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'No se pudo finalizar el examen'
        });
      }
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
      <div className="examen-loading" style={{ 
        minHeight: '100vh', 
        display: 'flex', 
        alignItems: 'center', 
        justifyContent: 'center',
        background: 'linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 50%, #f0fdf4 100%)'
      }}>
        <div className="loading-spinner" style={{ fontSize: '1.5rem', color: '#3b82f6' }}>
          Cargando examen...
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="examen-error" style={{ 
        minHeight: '100vh', 
        display: 'flex', 
        flexDirection: 'column',
        alignItems: 'center', 
        justifyContent: 'center',
        background: 'linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 50%, #f0fdf4 100%)',
        gap: '1rem',
        padding: '2rem'
      }}>
        <h2 style={{ color: '#ef4444', margin: 0 }}>❌ Error</h2>
        <p style={{ color: '#6b7280', fontSize: '1.1rem' }}>{error}</p>
        <button 
          onClick={() => navigate(-1)}
          style={{
            padding: '0.75rem 2rem',
            background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
            color: 'white',
            border: 'none',
            borderRadius: '8px',
            fontSize: '1rem',
            fontWeight: '600',
            cursor: 'pointer'
          }}
        >
          Volver
        </button>
      </div>
    );
  }

  if (!examen || preguntas.length === 0) {
    return (
      <div className="examen-error" style={{ 
        minHeight: '100vh', 
        display: 'flex', 
        flexDirection: 'column',
        alignItems: 'center', 
        justifyContent: 'center',
        background: 'linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 50%, #f0fdf4 100%)',
        gap: '1rem',
        padding: '2rem'
      }}>
        <h2 style={{ color: '#6b7280', margin: 0 }}>⚠️ No hay preguntas</h2>
        <p style={{ color: '#6b7280', fontSize: '1.1rem' }}>Este examen no tiene preguntas disponibles.</p>
        <button 
          onClick={() => navigate(-1)}
          style={{
            padding: '0.75rem 2rem',
            background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
            color: 'white',
            border: 'none',
            borderRadius: '8px',
            fontSize: '1rem',
            fontWeight: '600',
            cursor: 'pointer'
          }}
        >
          Volver
        </button>
      </div>
    );
  }

  // Verificar que tenemos datos válidos antes de renderizar
  if (!preguntas || preguntas.length === 0) {
    console.error('❌ No hay preguntas para renderizar');
    return (
      <div className="examen-error" style={{ 
        minHeight: '100vh', 
        display: 'flex', 
        flexDirection: 'column',
        alignItems: 'center', 
        justifyContent: 'center',
        background: 'linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 50%, #f0fdf4 100%)',
        gap: '1rem',
        padding: '2rem'
      }}>
        <h2 style={{ color: '#6b7280', margin: 0 }}>⚠️ No hay preguntas</h2>
        <p style={{ color: '#6b7280', fontSize: '1.1rem' }}>Este examen no tiene preguntas disponibles.</p>
        <button 
          onClick={() => navigate(-1)}
          style={{
            padding: '0.75rem 2rem',
            background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
            color: 'white',
            border: 'none',
            borderRadius: '8px',
            fontSize: '1rem',
            fontWeight: '600',
            cursor: 'pointer'
          }}
        >
          Volver
        </button>
      </div>
    );
  }

  const pregunta = preguntas[preguntaActual];
  if (!pregunta) {
    console.error('❌ Pregunta actual no existe:', preguntaActual, 'de', preguntas.length);
    return (
      <div className="examen-error" style={{ 
        minHeight: '100vh', 
        display: 'flex', 
        flexDirection: 'column',
        alignItems: 'center', 
        justifyContent: 'center',
        background: 'linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 50%, #f0fdf4 100%)',
        gap: '1rem',
        padding: '2rem'
      }}>
        <h2 style={{ color: '#ef4444', margin: 0 }}>❌ Error</h2>
        <p style={{ color: '#6b7280', fontSize: '1.1rem' }}>Error al cargar la pregunta actual.</p>
        <button 
          onClick={() => navigate(-1)}
          style={{
            padding: '0.75rem 2rem',
            background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
            color: 'white',
            border: 'none',
            borderRadius: '8px',
            fontSize: '1rem',
            fontWeight: '600',
            cursor: 'pointer'
          }}
        >
          Volver
        </button>
      </div>
    );
  }

  const totalPreguntas = preguntas.length;
  const progreso = ((preguntaActual + 1) / totalPreguntas) * 100;
  const porcentajeRestante = examen.tiempo > 0 
    ? (tiempoRestante / (examen.tiempo * 60)) * 100 
    : 100;
  const colorTimer = tiempoRestante <= 60 ? '#ef4444' : tiempoRestante <= 300 ? '#f59e0b' : '#10b981';

  // Log solo en desarrollo y solo una vez por cambio de pregunta
  if (process.env.NODE_ENV === 'development') {
    console.log('✅ Renderizando examen:', {
      examenId,
      totalPreguntas,
      preguntaActual,
      preguntaId: pregunta.id,
      preguntaTipo: pregunta.tipo
    });
  }

  // Función para formatear la respuesta según el tipo de pregunta
  const formatearRespuesta = (pregunta, respuesta) => {
    if (!respuesta || respuesta === '') return 'Sin responder';
    
    switch (pregunta.tipo) {
      case 'ALTERNATIVAS':
      case 'VERDADERO_FALSO':
        const alternativa = pregunta.alternativas?.find(alt => alt.id === respuesta);
        return alternativa ? alternativa.descripcion : 'Sin responder';
      
      case 'ORDENAR':
        if (Array.isArray(respuesta)) {
          const ordenadas = respuesta
            .map(id => pregunta.alternativas?.find(alt => alt.id === id))
            .filter(Boolean);
          return ordenadas.map((alt, idx) => `${idx + 1}. ${alt.descripcion}`).join(' | ');
        }
        return 'Sin responder';
      
      case 'COMPLETAR':
        if (typeof respuesta === 'object') {
          const texto = pregunta.descripcion || '';
          let textoCompleto = texto;
          Object.keys(respuesta).forEach(index => {
            const valor = respuesta[index] || '___';
            textoCompleto = textoCompleto.replace(/\[\[.*?\]\]/, `[${valor}]`);
          });
          return textoCompleto;
        }
        return 'Sin responder';
      
      case 'RESPUESTA_CORTA':
        return respuesta || 'Sin responder';
      
      case 'EMPAREJAR':
      case 'ARRASTRAR_Y_SOLTAR':
        return typeof respuesta === 'object' ? JSON.stringify(respuesta) : respuesta || 'Sin responder';
      
      default:
        return respuesta || 'Sin responder';
    }
  };

  if (mostrarResumen) {
    return (
      <div className="examen-resumen-container">
        <div className="examen-resumen-card">
          <h2>📋 Resumen del Examen</h2>
          <div className="resumen-preguntas">
            {preguntas.map((p, index) => {
              const respuesta = respuestas[p.id];
              const tieneRespuesta = respuesta !== null && respuesta !== undefined && respuesta !== '';
              const respuestaFormateada = formatearRespuesta(p, respuesta);
              
              return (
                <div key={p.id} className={`resumen-pregunta-item ${tieneRespuesta ? 'respondida' : 'sin-responder'}`}>
                  <div className="resumen-pregunta-numero">{index + 1}</div>
                  <div className="resumen-pregunta-info">
                    <div className="resumen-pregunta-titulo" dangerouslySetInnerHTML={{ __html: p.descripcion || 'Pregunta sin título' }} />
                    <div className="resumen-pregunta-respuesta">
                      <strong>Tu respuesta:</strong> {respuestaFormateada}
                    </div>
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
                setRespuestas(prev => {
                  const nuevasRespuestas = {
                    ...prev,
                    [pregunta.id]: respuesta
                  };
                  // Actualizar ref también
                  respuestasRef.current = nuevasRespuestas;
                  // Guardar inmediatamente al cambiar respuesta
                  setTimeout(() => {
                    guardarRespuestas();
                  }, 500);
                  return nuevasRespuestas;
                });
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
  if (!pregunta) {
    return (
      <div className="pregunta-container">
        <p style={{ color: '#ef4444' }}>❌ Error: No se pudo cargar la pregunta</p>
      </div>
    );
  }

  const props = {
    pregunta,
    respuesta,
    onRespuestaChange
  };

  try {
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
        return (
          <div className="pregunta-container">
            <div 
              className="pregunta-descripcion"
              dangerouslySetInnerHTML={{ __html: pregunta.descripcion || 'Sin descripción' }}
            />
            <p style={{ color: '#ef4444', marginTop: '1rem' }}>
              ⚠️ Tipo de pregunta no soportado: {pregunta.tipo}
            </p>
          </div>
        );
    }
  } catch (error) {
    console.error('Error renderizando pregunta:', error);
    return (
      <div className="pregunta-container">
        <div 
          className="pregunta-descripcion"
          dangerouslySetInnerHTML={{ __html: pregunta.descripcion || 'Sin descripción' }}
        />
        <p style={{ color: '#ef4444', marginTop: '1rem' }}>
          ❌ Error al renderizar la pregunta: {error.message}
        </p>
      </div>
    );
  }
}

export default AlumnoExamen;

