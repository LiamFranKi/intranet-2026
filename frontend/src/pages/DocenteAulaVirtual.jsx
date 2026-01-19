import React, { useState, useEffect, useCallback } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import DashboardLayout from '../components/DashboardLayout';
import api from '../services/api';
import Swal from 'sweetalert2';
import './DocenteAulaVirtual.css';

function DocenteAulaVirtual() {
  const { cursoId } = useParams(); // En realidad es asignatura_id
  const navigate = useNavigate();
  const asignaturaId = parseInt(cursoId);
  
  const [loading, setLoading] = useState(true);
  const [curso, setCurso] = useState(null);
  const [seccionActiva, setSeccionActiva] = useState('temas'); // temas, tareas, examenes
  const [temas, setTemas] = useState([]);
  const [tareas, setTareas] = useState([]);
  const [examenes, setExamenes] = useState([]);

  // Estados para modales/formularios
  const [mostrarFormTema, setMostrarFormTema] = useState(false);
  const [mostrarFormTarea, setMostrarFormTarea] = useState(false);
  const [mostrarFormExamen, setMostrarFormExamen] = useState(false);
  const [tareaSeleccionada, setTareaSeleccionada] = useState(null);

  // Formulario de tema
  const [formTema, setFormTema] = useState({
    tema: '',
    contenido_html: '',
    archivo: null
  });

  // Formulario de tarea
  const [formTarea, setFormTarea] = useState({
    titulo: '',
    descripcion: '',
    fecha_entrega: '',
    enlace: ''
  });

  // Formulario de examen
  const [formExamen, setFormExamen] = useState({
    titulo: '',
    tipo_puntaje: 'INDIVIDUAL',
    puntos_correcta: 1.0,
    penalizar_incorrecta: 'NO',
    penalizacion_incorrecta: 0.0,
    tiempo: 60,
    intentos: 1,
    orden_preguntas: 'PREDETERMINADO',
    fecha_desde: '',
    fecha_hasta: '',
    hora_desde: '08:00',
    hora_hasta: '20:00',
    preguntas_max: 10,
    tipo: 'VIRTUAL',
    preguntas: []
  });

  const cargarDatosCurso = useCallback(async () => {
    try {
      // Obtener información del curso/asignatura desde la lista de cursos
      const response = await api.get('/docente/cursos');
      const cursoEncontrado = response.data.cursos.find(c => c.id === asignaturaId);
      setCurso(cursoEncontrado || null);
    } catch (error) {
      console.error('Error cargando curso:', error);
    }
  }, [asignaturaId]);

  const cargarTemas = useCallback(async () => {
    try {
      setLoading(true);
      const response = await api.get('/docente/aula-virtual/temas', {
        params: { asignatura_id: asignaturaId }
      });
      setTemas(response.data.temas || []);
    } catch (error) {
      console.error('Error cargando temas:', error);
    } finally {
      setLoading(false);
    }
  }, [asignaturaId]);

  const cargarTareas = useCallback(async () => {
    try {
      setLoading(true);
      const response = await api.get('/docente/aula-virtual/tareas', {
        params: { asignatura_id: asignaturaId }
      });
      setTareas(response.data.tareas || []);
    } catch (error) {
      console.error('Error cargando tareas:', error);
    } finally {
      setLoading(false);
    }
  }, [asignaturaId]);

  const cargarExamenes = useCallback(async () => {
    try {
      setLoading(true);
      const response = await api.get('/docente/aula-virtual/examenes', {
        params: { asignatura_id: asignaturaId },
        timeout: 60000 // Aumentar timeout a 60 segundos para exámenes grandes
      });
      setExamenes(response.data.examenes || []);
    } catch (error) {
      console.error('Error cargando exámenes:', error);
      if (error.code === 'ECONNABORTED') {
        Swal.fire({
          icon: 'warning',
          title: 'Tiempo de espera agotado',
          text: 'El examen es muy grande. Intenta recargar la página.',
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 5000
        });
      }
    } finally {
      setLoading(false);
    }
  }, [asignaturaId]);

  // Cargar datos del curso solo una vez cuando cambia asignaturaId
  useEffect(() => {
    if (asignaturaId) {
      cargarDatosCurso();
    }
  }, [asignaturaId, cargarDatosCurso]);

  // Cargar datos según la sección activa
  useEffect(() => {
    if (!asignaturaId) return;
    
    // Cargar solo los datos de la sección activa
    if (seccionActiva === 'temas') {
      cargarTemas();
    } else if (seccionActiva === 'tareas') {
      cargarTareas();
    } else if (seccionActiva === 'examenes') {
      cargarExamenes();
    }
  }, [asignaturaId, seccionActiva, cargarTemas, cargarTareas, cargarExamenes]);

  const handleCrearTema = async (e) => {
    e.preventDefault();
    try {
      const formData = new FormData();
      formData.append('asignatura_id', asignaturaId);
      formData.append('tema', formTema.tema);
      if (formTema.contenido_html) {
        formData.append('contenido_html', formTema.contenido_html);
      }
      if (formTema.archivo) {
        formData.append('archivo', formTema.archivo);
      }

      await api.post('/docente/aula-virtual/temas', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });

      Swal.fire({
        icon: 'success',
        title: '¡Tema creado!',
        text: 'El tema se ha creado correctamente',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
      });

      setMostrarFormTema(false);
      setFormTema({ tema: '', contenido_html: '', archivo: null });
      await cargarTemas();
    } catch (error) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: error.response?.data?.error || 'No se pudo crear el tema',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
      });
    }
  };

  const handleCrearTarea = async (e) => {
    e.preventDefault();
    try {
      const formData = new FormData();
      formData.append('asignatura_id', asignaturaId);
      formData.append('titulo', formTarea.titulo);
      formData.append('descripcion', formTarea.descripcion || '');
      formData.append('fecha_entrega', formTarea.fecha_entrega);
      if (formTarea.enlace) {
        formData.append('enlace', formTarea.enlace);
      }

      await api.post('/docente/aula-virtual/tareas', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });

      Swal.fire({
        icon: 'success',
        title: '¡Tarea creada!',
        text: 'La tarea se ha creado correctamente',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
      });

      setMostrarFormTarea(false);
      setFormTarea({ titulo: '', descripcion: '', fecha_entrega: '', enlace: '' });
      await cargarTareas();
    } catch (error) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: error.response?.data?.error || 'No se pudo crear la tarea',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
      });
    }
  };

  const handleVerEntregas = async (tarea) => {
    try {
      // Cargar entregas de la tarea
      const response = await api.get('/docente/aula-virtual/tareas', {
        params: { asignatura_id: asignaturaId }
      });
      const tareaConEntregas = response.data.tareas.find(t => t.id === tarea.id);
      setTareaSeleccionada(tareaConEntregas || tarea);
    } catch (error) {
      console.error('Error cargando entregas:', error);
      setTareaSeleccionada(tarea);
    }
  };

  const handleCalificarTarea = async (entregaId, nota) => {
    if (!tareaSeleccionada) return;
    
    try {
      await api.put(`/docente/aula-virtual/tareas/${tareaSeleccionada.id}/calificar`, {
        entrega_id: entregaId,
        nota: parseFloat(nota)
      });

      Swal.fire({
        icon: 'success',
        title: '¡Tarea calificada!',
        text: 'La calificación se ha guardado correctamente',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
      });

      await cargarTareas();
      // Actualizar entregas de la tarea seleccionada
      if (tareaSeleccionada) {
        await handleVerEntregas(tareaSeleccionada);
      }
    } catch (error) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: error.response?.data?.error || 'No se pudo calificar la tarea',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
      });
    }
  };

  if (loading) {
    return (
      <DashboardLayout>
        <div className="docente-aula-loading">
          <div className="loading-spinner">Cargando aula virtual...</div>
        </div>
      </DashboardLayout>
    );
  }

  return (
    <DashboardLayout>
      <div className="docente-aula-virtual">
        <div className="page-header">
          <button className="btn-back" onClick={() => navigate('/docente/cursos')}>
            ← Volver a Cursos
          </button>
          <h1>Aula Virtual</h1>
          <p>{curso?.curso_nombre || 'Curso'} - {curso?.grado}° {curso?.seccion} - {curso?.anio}</p>
        </div>

        {/* Tabs de sección */}
        <div className="aula-tabs">
          <button 
            className={`tab-btn ${seccionActiva === 'temas' ? 'active' : ''}`}
            onClick={() => setSeccionActiva('temas')}
          >
            📚 Temas
          </button>
          <button 
            className={`tab-btn ${seccionActiva === 'tareas' ? 'active' : ''}`}
            onClick={() => setSeccionActiva('tareas')}
          >
            📝 Tareas
          </button>
          <button 
            className={`tab-btn ${seccionActiva === 'examenes' ? 'active' : ''}`}
            onClick={() => setSeccionActiva('examenes')}
          >
            ✏️ Exámenes
          </button>
        </div>

        {/* TEMAS */}
        {seccionActiva === 'temas' && (
          <div className="aula-seccion mundo-card">
            <div className="aula-seccion-header">
              <h2>Temas</h2>
              <button className="btn-agregar" onClick={() => setMostrarFormTema(true)}>
                + Agregar Tema
              </button>
            </div>

            {mostrarFormTema && (
              <div className="form-modal">
                <h3>Crear Nuevo Tema</h3>
                <form onSubmit={handleCrearTema}>
                  <div className="form-group">
                    <label>Tema *</label>
                    <input
                      type="text"
                      value={formTema.tema}
                      onChange={(e) => setFormTema({ ...formTema, tema: e.target.value })}
                      required
                      placeholder="Nombre del tema"
                    />
                  </div>
                  <div className="form-group">
                    <label>Contenido (Opcional - Editor HTML)</label>
                    <textarea
                      value={formTema.contenido_html}
                      onChange={(e) => setFormTema({ ...formTema, contenido_html: e.target.value })}
                      rows="10"
                      placeholder="Escribe el contenido del tema aquí o sube un archivo PDF"
                    />
                    <small>Puedes crear contenido directo con HTML o subir un archivo PDF</small>
                  </div>
                  <div className="form-group">
                    <label>O subir archivo (PDF, Word) - Opcional</label>
                    <input
                      type="file"
                      accept=".pdf,.doc,.docx"
                      onChange={(e) => setFormTema({ ...formTema, archivo: e.target.files[0] })}
                    />
                  </div>
                  <div className="form-actions">
                    <button type="button" onClick={() => {
                      setMostrarFormTema(false);
                      setFormTema({ tema: '', contenido_html: '', archivo: null });
                    }}>
                      Cancelar
                    </button>
                    <button type="submit">Crear Tema</button>
                  </div>
                </form>
              </div>
            )}

            <div className="temas-list">
              {temas.length > 0 ? (
                temas.map((tema) => (
                  <div key={tema.id} className="tema-item mundo-card">
                    <div className="tema-fecha">
                      {new Date(tema.fecha).toLocaleDateString('es-PE')}
                    </div>
                    <h3>{tema.tema}</h3>
                    {tema.archivos && tema.archivos.length > 0 && (
                      <div className="tema-archivos">
                        {tema.archivos.map((archivo, idx) => (
                          <a 
                            key={idx} 
                            href={`${api.defaults.baseURL.replace('/api', '')}/uploads/temas/${archivo.archivo}`}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="archivo-link"
                          >
                            📎 {archivo.nombre}
                          </a>
                        ))}
                      </div>
                    )}
                  </div>
                ))
              ) : (
                <div className="empty-state">
                  <p>No hay temas registrados</p>
                </div>
              )}
            </div>
          </div>
        )}

        {/* TAREAS */}
        {seccionActiva === 'tareas' && (
          <div className="aula-seccion mundo-card">
            <div className="aula-seccion-header">
              <h2>Tareas</h2>
              <button className="btn-agregar" onClick={() => setMostrarFormTarea(true)}>
                + Agregar Tarea
              </button>
            </div>

            {mostrarFormTarea && (
              <div className="form-modal">
                <h3>Crear Nueva Tarea</h3>
                <form onSubmit={handleCrearTarea}>
                  <div className="form-group">
                    <label>Título *</label>
                    <input
                      type="text"
                      value={formTarea.titulo}
                      onChange={(e) => setFormTarea({ ...formTarea, titulo: e.target.value })}
                      required
                    />
                  </div>
                  <div className="form-group">
                    <label>Descripción</label>
                    <textarea
                      value={formTarea.descripcion}
                      onChange={(e) => setFormTarea({ ...formTarea, descripcion: e.target.value })}
                      rows="5"
                    />
                  </div>
                  <div className="form-group">
                    <label>Fecha de Entrega *</label>
                    <input
                      type="date"
                      value={formTarea.fecha_entrega}
                      onChange={(e) => setFormTarea({ ...formTarea, fecha_entrega: e.target.value })}
                      required
                    />
                  </div>
                  <div className="form-group">
                    <label>Enlace (Opcional)</label>
                    <input
                      type="url"
                      value={formTarea.enlace}
                      onChange={(e) => setFormTarea({ ...formTarea, enlace: e.target.value })}
                      placeholder="https://..."
                    />
                  </div>
                  <div className="form-actions">
                    <button type="button" onClick={() => {
                      setMostrarFormTarea(false);
                      setFormTarea({ titulo: '', descripcion: '', fecha_entrega: '', enlace: '' });
                    }}>
                      Cancelar
                    </button>
                    <button type="submit">Crear Tarea</button>
                  </div>
                </form>
              </div>
            )}

            <div className="tareas-list">
              {tareas.length > 0 ? (
                tareas.map((tarea) => (
                  <div key={tarea.id} className="tarea-item mundo-card">
                    <div className="tarea-header">
                      <h3>{tarea.titulo}</h3>
                      <button 
                        className="btn-ver-entregas"
                        onClick={() => handleVerEntregas(tarea)}
                      >
                        Ver Entregas ({tarea.entregas?.length || 0})
                      </button>
                    </div>
                    <p>{tarea.descripcion}</p>
                    <div className="tarea-info">
                      <span>📅 Entrega: {new Date(tarea.fecha_entrega).toLocaleDateString('es-PE')}</span>
                      {tarea.archivos && tarea.archivos.length > 0 && (
                        <div className="tarea-archivos">
                          {tarea.archivos.map((archivo, idx) => (
                            <a 
                              key={idx} 
                              href={`${api.defaults.baseURL.replace('/api', '')}/uploads/tareas/${archivo.archivo}`}
                              target="_blank"
                              rel="noopener noreferrer"
                            >
                              📎 {archivo.nombre}
                            </a>
                          ))}
                        </div>
                      )}
                    </div>

                    {/* Modal de entregas */}
                    {tareaSeleccionada?.id === tarea.id && (
                      <div className="entregas-modal">
                        <h4>Entregas de Alumnos</h4>
                        <button className="btn-cerrar" onClick={() => setTareaSeleccionada(null)}>
                          ✕
                        </button>
                        {tarea.entregas && tarea.entregas.length > 0 ? (
                          <div className="entregas-list">
                            {tarea.entregas.map((entrega) => (
                              <div key={entrega.id} className="entrega-item">
                                <div className="entrega-info">
                                  <strong>{entrega.apellido_paterno} {entrega.apellido_materno}, {entrega.nombres}</strong>
                                  <span>{new Date(entrega.fecha_hora).toLocaleString('es-PE')}</span>
                                </div>
                                <div className="entrega-contenido">
                                  {entrega.mensaje && <p>{entrega.mensaje}</p>}
                                  {entrega.url && (
                                    <a 
                                      href={entrega.url} 
                                      target="_blank" 
                                      rel="noopener noreferrer"
                                      className="drive-link"
                                    >
                                      🔗 Link de Drive: {entrega.url}
                                    </a>
                                  )}
                                  {entrega.mensaje && <p className="entrega-mensaje">{entrega.mensaje}</p>}
                                </div>
                                <div className="entrega-calificar">
                                  <label>Nota:</label>
                                  <input
                                    type="number"
                                    min="0"
                                    max="20"
                                    step="0.1"
                                    placeholder={entrega.nota ? entrega.nota : "0.0"}
                                    defaultValue={entrega.nota || ''}
                                    onBlur={(e) => {
                                      if (e.target.value && parseFloat(e.target.value) !== parseFloat(entrega.nota || 0)) {
                                        handleCalificarTarea(entrega.id, e.target.value);
                                      }
                                    }}
                                  />
                                </div>
                              </div>
                            ))}
                          </div>
                        ) : (
                          <div className="empty-state">
                            <p>No hay entregas aún</p>
                          </div>
                        )}
                      </div>
                    )}
                  </div>
                ))
              ) : (
                <div className="empty-state">
                  <p>No hay tareas registradas</p>
                </div>
              )}
            </div>
          </div>
        )}

        {/* EXÁMENES */}
        {seccionActiva === 'examenes' && (
          <div className="aula-seccion mundo-card">
            <div className="aula-seccion-header">
              <h2>Exámenes</h2>
              <button className="btn-agregar" onClick={() => setMostrarFormExamen(true)}>
                + Agregar Examen
              </button>
            </div>

            {mostrarFormExamen && (
              <ExamenForm
                asignaturaId={asignaturaId}
                formExamen={formExamen}
                setFormExamen={setFormExamen}
                onClose={() => {
                  setMostrarFormExamen(false);
                  setFormExamen({
                    titulo: '',
                    tipo_puntaje: 'INDIVIDUAL',
                    puntos_correcta: 1.0,
                    penalizar_incorrecta: 'NO',
                    penalizacion_incorrecta: 0.0,
                    tiempo: 60,
                    intentos: 1,
                    orden_preguntas: 'PREDETERMINADO',
                    fecha_desde: '',
                    fecha_hasta: '',
                    hora_desde: '08:00',
                    hora_hasta: '20:00',
                    preguntas_max: 10,
                    tipo: 'VIRTUAL',
                    preguntas: []
                  });
                }}
                onSuccess={() => {
                  setMostrarFormExamen(false);
                  cargarExamenes();
                }}
              />
            )}

            <div className="examenes-list">
              {examenes.length > 0 ? (
                examenes.map((examen) => (
                  <div key={examen.id} className="examen-item mundo-card">
                    <div className="examen-header">
                      <h3>{examen.titulo}</h3>
                      <span className={`estado-badge ${examen.estado?.toLowerCase()}`}>
                        {examen.estado}
                      </span>
                    </div>
                    <div className="examen-info">
                      <div className="info-item">
                        <span className="info-label">Fecha:</span>
                        <span>{new Date(examen.fecha_desde).toLocaleDateString('es-PE')} - {new Date(examen.fecha_hasta).toLocaleDateString('es-PE')}</span>
                      </div>
                      <div className="info-item">
                        <span className="info-label">Hora:</span>
                        <span>{examen.hora_desde} - {examen.hora_hasta}</span>
                      </div>
                      <div className="info-item">
                        <span className="info-label">Tiempo:</span>
                        <span>{examen.tiempo} minutos</span>
                      </div>
                      <div className="info-item">
                        <span className="info-label">Preguntas:</span>
                        <span>{examen.preguntas?.length || 0}</span>
                      </div>
                      <div className="info-item">
                        <span className="info-label">Puntos por respuesta correcta:</span>
                        <span>{examen.puntos_correcta}</span>
                      </div>
                    </div>
                    {examen.preguntas && examen.preguntas.length > 0 && (
                      <div className="examen-preguntas-preview">
                        <details>
                          <summary>Ver Preguntas ({examen.preguntas.length})</summary>
                          <div className="preguntas-list">
                            {examen.preguntas.map((pregunta, idx) => (
                              <div key={pregunta.id} className="pregunta-preview">
                                <strong>Pregunta {idx + 1} ({pregunta.puntos} pts)</strong>
                                <div dangerouslySetInnerHTML={{ __html: pregunta.descripcion }} />
                                {pregunta.tipo === 'ALTERNATIVAS' && pregunta.alternativas && (
                                  <ul>
                                    {pregunta.alternativas.map((alt, altIdx) => (
                                      <li key={altIdx}>
                                        {alt.descripcion} {alt.correcta === 'SI' && '✓'}
                                      </li>
                                    ))}
                                  </ul>
                                )}
                              </div>
                            ))}
                          </div>
                        </details>
                      </div>
                    )}
                  </div>
                ))
              ) : (
                <div className="empty-state">
                  <p>No hay exámenes registrados</p>
                </div>
              )}
            </div>
          </div>
        )}
      </div>
    </DashboardLayout>
  );
}

// Componente para el formulario de examen (con tipos extendidos)
function ExamenForm({ asignaturaId, formExamen, setFormExamen, onClose, onSuccess }) {
  const [preguntaActual, setPreguntaActual] = useState({
    descripcion: '',
    tipo_pregunta: 'ALTERNATIVAS',
    puntos: 1.0,
    alternativas: [],
    respuesta_correcta: '',
    metadata: {}
  });

  const tiposPregunta = [
    { value: 'ALTERNATIVAS', label: 'Opción Múltiple' },
    { value: 'COMPLETAR', label: 'Completar Espacios' },
    { value: 'VERDADERO_FALSO', label: 'Verdadero/Falso' },
    { value: 'RELACIONAR', label: 'Relacionar' },
    { value: 'ORGANIZAR', label: 'Organizar/Ordenar' }
  ];

  const handleAgregarAlternativa = () => {
    setPreguntaActual({
      ...preguntaActual,
      alternativas: [...preguntaActual.alternativas, { descripcion: '', correcta: false }]
    });
  };

  const handleAgregarPregunta = () => {
    if (!preguntaActual.descripcion.trim()) {
      Swal.fire({
        icon: 'warning',
        title: 'Advertencia',
        text: 'La pregunta debe tener una descripción',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
      });
      return;
    }

    setFormExamen({
      ...formExamen,
      preguntas: [...formExamen.preguntas, { ...preguntaActual }]
    });

    // Reset pregunta actual
    setPreguntaActual({
      descripcion: '',
      tipo_pregunta: 'ALTERNATIVAS',
      puntos: 1.0,
      alternativas: [],
      respuesta_correcta: '',
      metadata: {}
    });
  };

  const handleCrearExamen = async (e) => {
    e.preventDefault();
    
    if (formExamen.preguntas.length === 0) {
      Swal.fire({
        icon: 'warning',
        title: 'Advertencia',
        text: 'Debes agregar al menos una pregunta',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
      });
      return;
    }

    try {
      const examenData = {
        ...formExamen,
        asignatura_id: asignaturaId
      };

      await api.post('/docente/aula-virtual/examenes', examenData);

      Swal.fire({
        icon: 'success',
        title: '¡Examen creado!',
        text: 'El examen se ha creado correctamente',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
      });

      onSuccess();
    } catch (error) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: error.response?.data?.error || 'No se pudo crear el examen',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
      });
    }
  };

  return (
    <div className="form-modal examen-form-modal">
      <h3>Crear Nuevo Examen</h3>
      <form onSubmit={handleCrearExamen}>
        {/* Información del examen */}
        <div className="form-section">
          <h4>Información General</h4>
          <div className="form-grid">
            <div className="form-group">
              <label>Título del Examen *</label>
              <input
                type="text"
                value={formExamen.titulo}
                onChange={(e) => setFormExamen({ ...formExamen, titulo: e.target.value })}
                required
              />
            </div>
            <div className="form-group">
              <label>Tipo de Puntaje</label>
              <select
                value={formExamen.tipo_puntaje}
                onChange={(e) => setFormExamen({ ...formExamen, tipo_puntaje: e.target.value })}
              >
                <option value="INDIVIDUAL">Individual</option>
                <option value="GENERAL">General</option>
              </select>
            </div>
            <div className="form-group">
              <label>Puntos por respuesta correcta *</label>
              <input
                type="number"
                step="0.1"
                min="0"
                value={formExamen.puntos_correcta}
                onChange={(e) => setFormExamen({ ...formExamen, puntos_correcta: parseFloat(e.target.value) })}
                required
              />
            </div>
            <div className="form-group">
              <label>Penalizar incorrecta</label>
              <select
                value={formExamen.penalizar_incorrecta}
                onChange={(e) => setFormExamen({ ...formExamen, penalizar_incorrecta: e.target.value })}
              >
                <option value="NO">No</option>
                <option value="SI">Sí</option>
              </select>
            </div>
            {formExamen.penalizar_incorrecta === 'SI' && (
              <div className="form-group">
                <label>Penalización por incorrecta</label>
                <input
                  type="number"
                  step="0.1"
                  min="0"
                  value={formExamen.penalizacion_incorrecta}
                  onChange={(e) => setFormExamen({ ...formExamen, penalizacion_incorrecta: parseFloat(e.target.value) })}
                />
              </div>
            )}
            <div className="form-group">
              <label>Tiempo (minutos) *</label>
              <input
                type="number"
                min="1"
                value={formExamen.tiempo}
                onChange={(e) => setFormExamen({ ...formExamen, tiempo: parseInt(e.target.value) })}
                required
              />
            </div>
            <div className="form-group">
              <label>Intentos permitidos</label>
              <input
                type="number"
                min="1"
                value={formExamen.intentos}
                onChange={(e) => setFormExamen({ ...formExamen, intentos: parseInt(e.target.value) })}
              />
            </div>
            <div className="form-group">
              <label>Orden de preguntas</label>
              <select
                value={formExamen.orden_preguntas}
                onChange={(e) => setFormExamen({ ...formExamen, orden_preguntas: e.target.value })}
              >
                <option value="PREDETERMINADO">Predeterminado</option>
                <option value="ALEATORIO">Aleatorio</option>
              </select>
            </div>
            <div className="form-group">
              <label>Preguntas a mostrar</label>
              <input
                type="number"
                min="1"
                value={formExamen.preguntas_max}
                onChange={(e) => setFormExamen({ ...formExamen, preguntas_max: parseInt(e.target.value) })}
              />
            </div>
            <div className="form-group">
              <label>Fecha desde *</label>
              <input
                type="date"
                value={formExamen.fecha_desde}
                onChange={(e) => setFormExamen({ ...formExamen, fecha_desde: e.target.value })}
                required
              />
            </div>
            <div className="form-group">
              <label>Fecha hasta *</label>
              <input
                type="date"
                value={formExamen.fecha_hasta}
                onChange={(e) => setFormExamen({ ...formExamen, fecha_hasta: e.target.value })}
                required
              />
            </div>
            <div className="form-group">
              <label>Hora desde *</label>
              <input
                type="time"
                value={formExamen.hora_desde}
                onChange={(e) => setFormExamen({ ...formExamen, hora_desde: e.target.value })}
                required
              />
            </div>
            <div className="form-group">
              <label>Hora hasta *</label>
              <input
                type="time"
                value={formExamen.hora_hasta}
                onChange={(e) => setFormExamen({ ...formExamen, hora_hasta: e.target.value })}
                required
              />
            </div>
          </div>
        </div>

        {/* Agregar preguntas */}
        <div className="form-section">
          <h4>Preguntas ({formExamen.preguntas.length})</h4>
          
          <div className="pregunta-editor">
            <div className="form-group">
              <label>Tipo de Pregunta</label>
              <select
                value={preguntaActual.tipo_pregunta}
                onChange={(e) => {
                  setPreguntaActual({
                    ...preguntaActual,
                    tipo_pregunta: e.target.value,
                    alternativas: e.target.value === 'ALTERNATIVAS' || e.target.value === 'VERDADERO_FALSO' ? preguntaActual.alternativas : [],
                    metadata: {}
                  });
                }}
              >
                {tiposPregunta.map((tipo) => (
                  <option key={tipo.value} value={tipo.value}>
                    {tipo.label}
                  </option>
                ))}
              </select>
            </div>

            <div className="form-group">
              <label>Pregunta *</label>
              <textarea
                value={preguntaActual.descripcion}
                onChange={(e) => setPreguntaActual({ ...preguntaActual, descripcion: e.target.value })}
                rows="4"
                placeholder="Escribe la pregunta aquí..."
                required
              />
            </div>

            <div className="form-group">
              <label>Puntos</label>
              <input
                type="number"
                step="0.1"
                min="0"
                value={preguntaActual.puntos}
                onChange={(e) => setPreguntaActual({ ...preguntaActual, puntos: parseFloat(e.target.value) })}
              />
            </div>

            {/* Para tipos ALTERNATIVAS o VERDADERO_FALSO */}
            {(preguntaActual.tipo_pregunta === 'ALTERNATIVAS' || preguntaActual.tipo_pregunta === 'VERDADERO_FALSO') && (
              <div className="alternativas-editor">
                <label>Alternativas</label>
                {preguntaActual.tipo_pregunta === 'VERDADERO_FALSO' ? (
                  <div className="verdadero-falso-options">
                    <label>
                      <input
                        type="radio"
                        name="vf_respuesta"
                        value="VERDADERO"
                        onChange={(e) => setPreguntaActual({
                          ...preguntaActual,
                          alternativas: [
                            { descripcion: 'VERDADERO', correcta: true },
                            { descripcion: 'FALSO', correcta: false }
                          ],
                          respuesta_correcta: e.target.value
                        })}
                      />
                      Verdadero (Correcto)
                    </label>
                    <label>
                      <input
                        type="radio"
                        name="vf_respuesta"
                        value="FALSO"
                        onChange={(e) => setPreguntaActual({
                          ...preguntaActual,
                          alternativas: [
                            { descripcion: 'VERDADERO', correcta: false },
                            { descripcion: 'FALSO', correcta: true }
                          ],
                          respuesta_correcta: e.target.value
                        })}
                      />
                      Falso (Correcto)
                    </label>
                  </div>
                ) : (
                  <>
                    {preguntaActual.alternativas.map((alt, idx) => (
                      <div key={idx} className="alternativa-item">
                        <input
                          type="text"
                          value={alt.descripcion}
                          onChange={(e) => {
                            const nuevasAlternativas = [...preguntaActual.alternativas];
                            nuevasAlternativas[idx].descripcion = e.target.value;
                            setPreguntaActual({ ...preguntaActual, alternativas: nuevasAlternativas });
                          }}
                          placeholder={`Alternativa ${idx + 1}`}
                        />
                        <label>
                          <input
                            type="radio"
                            name="correcta"
                            checked={alt.correcta}
                            onChange={() => {
                              const nuevasAlternativas = preguntaActual.alternativas.map((a, i) => ({
                                ...a,
                                correcta: i === idx
                              }));
                              setPreguntaActual({
                                ...preguntaActual,
                                alternativas: nuevasAlternativas,
                                respuesta_correcta: alt.descripcion
                              });
                            }}
                          />
                          Correcta
                        </label>
                        <button
                          type="button"
                          onClick={() => {
                            const nuevasAlternativas = preguntaActual.alternativas.filter((_, i) => i !== idx);
                            setPreguntaActual({ ...preguntaActual, alternativas: nuevasAlternativas });
                          }}
                        >
                          ✕
                        </button>
                      </div>
                    ))}
                    <button type="button" onClick={handleAgregarAlternativa} className="btn-agregar-alt">
                      + Agregar Alternativa
                    </button>
                  </>
                )}
              </div>
            )}

            {/* Para tipo COMPLETAR */}
            {preguntaActual.tipo_pregunta === 'COMPLETAR' && (
              <div className="form-group">
                <label>Respuesta Correcta *</label>
                <input
                  type="text"
                  value={preguntaActual.respuesta_correcta}
                  onChange={(e) => setPreguntaActual({ ...preguntaActual, respuesta_correcta: e.target.value })}
                  placeholder="Ej: La respuesta correcta es..."
                  required
                />
                <small>El docente evaluará manualmente las respuestas de completar</small>
              </div>
            )}

            {/* Para tipos RELACIONAR u ORGANIZAR - guardar metadata en JSON */}
            {(preguntaActual.tipo_pregunta === 'RELACIONAR' || preguntaActual.tipo_pregunta === 'ORGANIZAR') && (
              <div className="form-group">
                <label>Configuración Especial (JSON)</label>
                <textarea
                  value={JSON.stringify(preguntaActual.metadata, null, 2)}
                  onChange={(e) => {
                    try {
                      const metadata = JSON.parse(e.target.value);
                      setPreguntaActual({ ...preguntaActual, metadata });
                    } catch (e) {
                      // JSON inválido, pero continuamos
                    }
                  }}
                  rows="6"
                  placeholder='{"columnas": ["A", "B"], "items": [...]}'
                />
                <small>Para relacionar: define columnas e items a relacionar. Para organizar: define el orden correcto.</small>
              </div>
            )}

            <button type="button" onClick={handleAgregarPregunta} className="btn-agregar-pregunta">
              + Agregar Pregunta al Examen
            </button>
          </div>

          {/* Lista de preguntas agregadas */}
          {formExamen.preguntas.length > 0 && (
            <div className="preguntas-agregadas">
              <h5>Preguntas Agregadas:</h5>
              <ul>
                {formExamen.preguntas.map((pregunta, idx) => (
                  <li key={idx}>
                    <strong>Pregunta {idx + 1}</strong> ({pregunta.tipo_pregunta}) - {pregunta.puntos} pts
                    <button
                      type="button"
                      onClick={() => {
                        const nuevasPreguntas = formExamen.preguntas.filter((_, i) => i !== idx);
                        setFormExamen({ ...formExamen, preguntas: nuevasPreguntas });
                      }}
                    >
                      Eliminar
                    </button>
                  </li>
                ))}
              </ul>
            </div>
          )}
        </div>

        <div className="form-actions">
          <button type="button" onClick={onClose}>
            Cancelar
          </button>
          <button type="submit" disabled={formExamen.preguntas.length === 0}>
            Crear Examen
          </button>
        </div>
      </form>
    </div>
  );
}

export default DocenteAulaVirtual;
