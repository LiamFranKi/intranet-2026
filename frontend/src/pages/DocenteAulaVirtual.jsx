import React, { useState, useEffect, useCallback, useRef } from 'react';
import { createPortal } from 'react-dom';
import { useParams, useNavigate } from 'react-router-dom';
import DashboardLayout from '../components/DashboardLayout';
import api from '../services/api';
import Swal from 'sweetalert2';
import './DocenteAulaVirtual-gamificado.css';

function DocenteAulaVirtual() {
  const { cursoId } = useParams(); // En realidad es asignatura_id
  const navigate = useNavigate();
  const asignaturaId = parseInt(cursoId);
  
  const [loading, setLoading] = useState(true);
  const [curso, setCurso] = useState(null);
  const [totalNotas, setTotalNotas] = useState(4); // Número de bimestres por defecto
  
  // Estados para cada sección con su ciclo activo (bimestre)
  const [cicloArchivos, setCicloArchivos] = useState(1);
  const [cicloTareas, setCicloTareas] = useState(1);
  const [cicloExamenes, setCicloExamenes] = useState(1);
  const [cicloVideos, setCicloVideos] = useState(1);
  const [cicloEnlaces, setCicloEnlaces] = useState(1);
  const [tareas, setTareas] = useState([]);
  const [examenes, setExamenes] = useState([]);
  const [videos, setVideos] = useState([]);
  const [enlaces, setEnlaces] = useState([]);
  const [temas, setTemas] = useState([]);
  const [archivos, setArchivos] = useState([]);

  // Estados para modales/formularios
  const [mostrarFormTema, setMostrarFormTema] = useState(false);
  const [mostrarFormTarea, setMostrarFormTarea] = useState(false);
  const [mostrarFormExamen, setMostrarFormExamen] = useState(false);
  const [tareaSeleccionada, setTareaSeleccionada] = useState(null);
  
  // Estados para dropdowns con portal
  const [openDropdown, setOpenDropdown] = useState(null);
  const [dropdownPosition, setDropdownPosition] = useState(null);
  const buttonRef = useRef({});
  
  // Estado para card expandido (versión gamificada)
  const [expandedCard, setExpandedCard] = useState(null);
  const [bimestreGlobal, setBimestreGlobal] = useState(1);

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
      
      // Obtener configuración (total_notas)
      const configResponse = await api.get('/docente/aula-virtual/config');
      setTotalNotas(configResponse.data.total_notas || 4);
    } catch (error) {
      console.error('Error cargando curso:', error);
    }
  }, [asignaturaId]);

  const cargarArchivos = useCallback(async (ciclo) => {
    try {
      const response = await api.get('/docente/aula-virtual/archivos', {
        params: { asignatura_id: asignaturaId, ciclo: ciclo || cicloArchivos }
      });
      setArchivos(response.data.archivos || []);
    } catch (error) {
      console.error('Error cargando archivos:', error);
    }
  }, [asignaturaId, cicloArchivos]);

  const cargarTemas = useCallback(async (ciclo) => {
    try {
      const response = await api.get('/docente/aula-virtual/temas', {
        params: { asignatura_id: asignaturaId, ciclo: ciclo || 1 }
      });
      setTemas(response.data.temas || []);
    } catch (error) {
      console.error('Error cargando temas:', error);
    }
  }, [asignaturaId]);

  const cargarTareas = useCallback(async (ciclo) => {
    try {
      const response = await api.get('/docente/aula-virtual/tareas', {
        params: { asignatura_id: asignaturaId, ciclo: ciclo || cicloTareas }
      });
      setTareas(response.data.tareas || []);
    } catch (error) {
      console.error('Error cargando tareas:', error);
    }
  }, [asignaturaId, cicloTareas]);

  const cargarExamenes = useCallback(async (ciclo) => {
    try {
      const response = await api.get('/docente/aula-virtual/examenes', {
        params: { asignatura_id: asignaturaId, ciclo: ciclo || cicloExamenes },
        timeout: 60000
      });
      setExamenes(response.data.examenes || []);
    } catch (error) {
      console.error('Error cargando exámenes:', error);
    }
  }, [asignaturaId, cicloExamenes]);

  const cargarVideos = useCallback(async (ciclo) => {
    try {
      const response = await api.get('/docente/aula-virtual/videos', {
        params: { asignatura_id: asignaturaId, ciclo: ciclo || cicloVideos }
      });
      setVideos(response.data.videos || []);
    } catch (error) {
      console.error('Error cargando videos:', error);
    }
  }, [asignaturaId, cicloVideos]);

  const cargarEnlaces = useCallback(async (ciclo) => {
    try {
      const response = await api.get('/docente/aula-virtual/enlaces', {
        params: { asignatura_id: asignaturaId, ciclo: ciclo || cicloEnlaces }
      });
      setEnlaces(response.data.enlaces || []);
    } catch (error) {
      console.error('Error cargando enlaces:', error);
    }
  }, [asignaturaId, cicloEnlaces]);

  // Cargar datos del curso y configuración
  useEffect(() => {
    if (asignaturaId) {
      setLoading(true);
      cargarDatosCurso().finally(() => setLoading(false));
    }
  }, [asignaturaId, cargarDatosCurso]);

  // Sincronizar bimestre global con los ciclos individuales
  useEffect(() => {
    setCicloArchivos(bimestreGlobal);
    setCicloTareas(bimestreGlobal);
    setCicloExamenes(bimestreGlobal);
    setCicloVideos(bimestreGlobal);
    setCicloEnlaces(bimestreGlobal);
  }, [bimestreGlobal]);

  // Cargar todos los datos inicialmente con ciclo 1
  useEffect(() => {
    if (!asignaturaId || loading) return;
    
    cargarArchivos(1);
    cargarTareas(1);
    cargarExamenes(1);
    cargarVideos(1);
    cargarEnlaces(1);
    cargarTemas(1);
  }, [asignaturaId, loading]);

  // Cargar datos cuando cambia el ciclo de cada sección
  useEffect(() => {
    if (!asignaturaId) return;
    cargarArchivos(cicloArchivos);
  }, [cicloArchivos, asignaturaId, cargarArchivos]);

  useEffect(() => {
    if (!asignaturaId) return;
    cargarTareas(cicloTareas);
  }, [cicloTareas, asignaturaId, cargarTareas]);

  useEffect(() => {
    if (!asignaturaId) return;
    cargarExamenes(cicloExamenes);
  }, [cicloExamenes, asignaturaId, cargarExamenes]);

  useEffect(() => {
    if (!asignaturaId) return;
    cargarVideos(cicloVideos);
  }, [cicloVideos, asignaturaId, cargarVideos]);

  useEffect(() => {
    if (!asignaturaId) return;
    cargarEnlaces(cicloEnlaces);
  }, [cicloEnlaces, asignaturaId, cargarEnlaces]);

  // Cerrar dropdowns al hacer click fuera
  useEffect(() => {
    const handleClickOutside = (event) => {
      // Solo cerrar si es click del botón izquierdo (button === 0)
      // Ignorar botón del medio (wheel, button === 1) y botón derecho (button === 2)
      if (event.button !== 0) return;
      
      if (openDropdown !== null) {
        const dropdownElement = document.querySelector('.dropdown-menu-portal-aula');
        const buttonElement = buttonRef.current[openDropdown];
        
        // Verificar si el click fue fuera del dropdown y del botón
        if (
          dropdownElement && 
          !dropdownElement.contains(event.target) &&
          buttonElement &&
          !buttonElement.contains(event.target)
        ) {
          setOpenDropdown(null);
          setDropdownPosition(null);
        }
      }
    };

    if (openDropdown !== null) {
      // Usar mousedown en lugar de click para mejor control
      setTimeout(() => {
        document.addEventListener('mousedown', handleClickOutside);
      }, 0);
      
      return () => {
        document.removeEventListener('mousedown', handleClickOutside);
      };
    }
  }, [openDropdown]);

  // Función para toggle dropdown
  const toggleDropdown = (itemId, event, tipo) => {
    event.preventDefault();
    event.stopPropagation();
    if (openDropdown === `${tipo}-${itemId}`) {
      setOpenDropdown(null);
      setDropdownPosition(null);
    } else {
      const button = event.currentTarget;
      const rect = button.getBoundingClientRect();
      setDropdownPosition({
        top: rect.bottom + 8,
        right: window.innerWidth - rect.right,
        width: rect.width > 200 ? rect.width : 200
      });
      setOpenDropdown(`${tipo}-${itemId}`);
    }
  };

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

  const toggleCard = (cardName) => {
    if (expandedCard === cardName) {
      setExpandedCard(null);
    } else {
      setExpandedCard(cardName);
    }
  };

  const renderCardContent = (cardName) => {
    switch(cardName) {
      case 'temas':
        return renderTemasContent();
      case 'tareas':
        return renderTareasContent();
      case 'examenes':
        return renderExamenesContent();
      case 'videos':
        return renderVideosContent();
      case 'enlaces':
        return renderEnlacesContent();
      default:
        return null;
    }
  };

  const renderTemasContent = () => (
    <div className="card-content-expanded">
      {loading ? (
        <div className="empty-state">
          <p>Cargando temas interactivos...</p>
        </div>
      ) : archivos.length > 0 ? (
        <table>
          <thead>
            <tr>
              <th>NOMBRE</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            {archivos.map((archivo) => (
              <tr key={archivo.id}>
                <td>{archivo.nombre}</td>
                <td className="text-center">
                  <div className="btn-group-opciones">
                    <button 
                      className="btn-opciones"
                      ref={(el) => (buttonRef.current[`archivo-${archivo.id}`] = el)}
                      onClick={(e) => toggleDropdown(archivo.id, e, 'archivo')}
                    >
                      Opciones {openDropdown === `archivo-${archivo.id}` ? '▲' : '▼'}
                    </button>
                    {openDropdown === `archivo-${archivo.id}` && dropdownPosition && createPortal(
                      <div 
                        className="dropdown-menu-portal-aula"
                        style={{
                          position: 'fixed',
                          top: `${dropdownPosition.top}px`,
                          right: `${dropdownPosition.right}px`,
                          width: `${dropdownPosition.width}px`,
                          zIndex: 10000
                        }}
                        onMouseDown={(e) => e.stopPropagation()}
                        onClick={(e) => e.stopPropagation()}
                      >
                        <div className="dropdown-menu-opciones">
                          {archivo.archivo_url && (
                            <a href={archivo.archivo_url} target="_blank" rel="noopener noreferrer">
                              📄 Ver Archivo
                            </a>
                          )}
                          {archivo.enlace_url && (
                            <a href={archivo.enlace_url} target="_blank" rel="noopener noreferrer">
                              🔗 Abrir URL
                            </a>
                          )}
                          <a href="#" onClick={(e) => { e.preventDefault(); /* Editar tema */ }}>
                            ✏️ Editar Tema
                          </a>
                          <a href="#" onClick={(e) => { e.preventDefault(); /* Borrar tema */ }}>
                            🗑️ Borrar Tema
                          </a>
                        </div>
                      </div>,
                      document.body
                    )}
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      ) : (
        <div className="empty-state">
          <p>No hay temas interactivos para este bimestre</p>
        </div>
      )}
    </div>
  );

  const renderTareasContent = () => (
    <div className="card-content-expanded">
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

      {tareas.length > 0 ? (
        <table>
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Fecha de Registro</th>
              <th>Fecha de Entrega</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            {tareas.map((tarea) => (
              <tr key={tarea.id}>
                <td>{tarea.titulo}</td>
                <td className="text-center">{new Date(tarea.fecha_hora).toLocaleDateString('es-PE')}</td>
                <td className="text-center">{new Date(tarea.fecha_entrega).toLocaleDateString('es-PE')}</td>
                <td className="text-center">
                  <div className="btn-group-opciones">
                    <button 
                      className="btn-opciones"
                      ref={(el) => (buttonRef.current[`tarea-${tarea.id}`] = el)}
                      onClick={(e) => toggleDropdown(tarea.id, e, 'tarea')}
                    >
                      Opciones {openDropdown === `tarea-${tarea.id}` ? '▲' : '▼'}
                    </button>
                    {openDropdown === `tarea-${tarea.id}` && dropdownPosition && createPortal(
                      <div 
                        className="dropdown-menu-portal-aula"
                        style={{
                          position: 'fixed',
                          top: `${dropdownPosition.top}px`,
                          right: `${dropdownPosition.right}px`,
                          width: `${dropdownPosition.width}px`,
                          zIndex: 10000
                        }}
                        onMouseDown={(e) => e.stopPropagation()}
                        onClick={(e) => e.stopPropagation()}
                      >
                        <div className="dropdown-menu-opciones">
                          <a href="#" onClick={(e) => { e.preventDefault(); handleVerEntregas(tarea); setOpenDropdown(null); }}>
                            ℹ️ Ver Detalles
                          </a>
                          <a href="#" onClick={(e) => { e.preventDefault(); /* Marcar entregas */ }}>
                            ✓ Marcar Entregas
                          </a>
                          <a href="#" onClick={(e) => { e.preventDefault(); /* Asignar a registro */ }}>
                            📋 Asignar a Registro
                          </a>
                          <a href="#" onClick={(e) => { e.preventDefault(); /* Editar tarea */ }}>
                            ✏️ Editar Tarea
                          </a>
                          <a href="#" onClick={(e) => { e.preventDefault(); /* Borrar tarea */ }}>
                            🗑️ Borrar Tarea
                          </a>
                        </div>
                      </div>,
                      document.body
                    )}
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      ) : (
        <div className="empty-state">
          <p>No hay tareas para este bimestre</p>
        </div>
      )}
    </div>
  );

  const renderExamenesContent = () => (
    <div className="card-content-expanded">
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

      {examenes.length > 0 ? (
        <table>
          <thead>
            <tr>
              <th>EXAMEN</th>
              <th>TIEMPO (MIN.)</th>
              <th>PREGUNTAS</th>
              <th>ESTADO</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            {examenes.map((examen) => (
              <tr key={examen.id}>
                <td>{examen.titulo}</td>
                <td className="text-center">{examen.tiempo === 0 || !examen.tiempo ? 'ILIMITADO' : examen.tiempo}</td>
                <td className="text-center">{examen.preguntas?.length || 0}</td>
                <td className="text-center">
                  <span className={`estado-badge ${examen.estado?.toLowerCase() || 'inactivo'}`}>
                    {examen.estado || 'INACTIVO'}
                  </span>
                </td>
                <td className="text-center">
                  <div className="btn-group-opciones">
                    <button 
                      className="btn-opciones"
                      ref={(el) => (buttonRef.current[`examen-${examen.id}`] = el)}
                      onClick={(e) => toggleDropdown(examen.id, e, 'examen')}
                    >
                      Opciones {openDropdown === `examen-${examen.id}` ? '▲' : '▼'}
                    </button>
                    {openDropdown === `examen-${examen.id}` && dropdownPosition && createPortal(
                      <div 
                        className="dropdown-menu-portal-aula"
                        style={{
                          position: 'fixed',
                          top: `${dropdownPosition.top}px`,
                          right: `${dropdownPosition.right}px`,
                          width: `${dropdownPosition.width}px`,
                          zIndex: 10000
                        }}
                        onMouseDown={(e) => e.stopPropagation()}
                        onClick={(e) => e.stopPropagation()}
                      >
                        <div className="dropdown-menu-opciones">
                          <a href="#" onClick={(e) => { e.preventDefault(); /* Preguntas/Alternativas */ }}>
                            📝 Preguntas / Alternativas
                          </a>
                          <a href="#" onClick={(e) => { e.preventDefault(); /* Ver resultados */ }}>
                            📊 Ver Resultados
                          </a>
                          <a href="#" onClick={(e) => { e.preventDefault(); /* Asignar a registro */ }}>
                            📋 Asignar a Registro
                          </a>
                          <a href="#" onClick={(e) => { e.preventDefault(); /* Habilitar/Deshabilitar */ }}>
                            🔒 Habilitar / Deshabilitar
                          </a>
                          <a href="#" onClick={(e) => { e.preventDefault(); /* Editar examen */ }}>
                            ✏️ Editar Examen
                          </a>
                        </div>
                      </div>,
                      document.body
                    )}
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      ) : (
        <div className="empty-state">
          <p>No hay exámenes para este bimestre</p>
        </div>
      )}
    </div>
  );

  const renderVideosContent = () => (
    <div className="card-content-expanded">
      {videos.length > 0 ? (
        <table>
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Fecha</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            {videos.map((video) => (
              <tr key={video.id}>
                <td>{video.descripcion}</td>
                <td>{new Date(video.fecha_hora).toLocaleString('es-PE')}</td>
                <td className="text-center">
                  <div className="btn-group-opciones">
                    <button 
                      className="btn-opciones"
                      ref={(el) => (buttonRef.current[`video-${video.id}`] = el)}
                      onClick={(e) => toggleDropdown(video.id, e, 'video')}
                    >
                      Opciones {openDropdown === `video-${video.id}` ? '▲' : '▼'}
                    </button>
                    {openDropdown === `video-${video.id}` && dropdownPosition && createPortal(
                      <div 
                        className="dropdown-menu-portal-aula"
                        style={{
                          position: 'fixed',
                          top: `${dropdownPosition.top}px`,
                          right: `${dropdownPosition.right}px`,
                          width: `${dropdownPosition.width}px`,
                          zIndex: 10000
                        }}
                        onMouseDown={(e) => e.stopPropagation()}
                        onClick={(e) => e.stopPropagation()}
                      >
                        <div className="dropdown-menu-opciones">
                          {video.enlace && (
                            <a href={video.enlace} target="_blank" rel="noopener noreferrer">
                              🎥 Ver Video
                            </a>
                          )}
                          <a href="#" onClick={(e) => { e.preventDefault(); /* Editar video */ }}>
                            ✏️ Editar Video
                          </a>
                          <a href="#" onClick={(e) => { e.preventDefault(); /* Borrar video */ }}>
                            🗑️ Borrar Video
                          </a>
                        </div>
                      </div>,
                      document.body
                    )}
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      ) : (
        <div className="empty-state">
          <p>No hay videos para este bimestre</p>
        </div>
      )}
    </div>
  );

  const renderEnlacesContent = () => (
    <div className="card-content-expanded">
      {enlaces.length > 0 ? (
        <table>
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Fecha</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            {enlaces.map((enlace) => (
              <tr key={enlace.id}>
                <td>{enlace.descripcion}</td>
                <td>{new Date(enlace.fecha_hora).toLocaleString('es-PE')}</td>
                <td className="text-center">
                  <div className="btn-group-opciones">
                    <button 
                      className="btn-opciones"
                      ref={(el) => (buttonRef.current[`enlace-${enlace.id}`] = el)}
                      onClick={(e) => toggleDropdown(enlace.id, e, 'enlace')}
                    >
                      Opciones {openDropdown === `enlace-${enlace.id}` ? '▲' : '▼'}
                    </button>
                    {openDropdown === `enlace-${enlace.id}` && dropdownPosition && createPortal(
                      <div 
                        className="dropdown-menu-portal-aula"
                        style={{
                          position: 'fixed',
                          top: `${dropdownPosition.top}px`,
                          right: `${dropdownPosition.right}px`,
                          width: `${dropdownPosition.width}px`,
                          zIndex: 10000
                        }}
                        onMouseDown={(e) => e.stopPropagation()}
                        onClick={(e) => e.stopPropagation()}
                      >
                        <div className="dropdown-menu-opciones">
                          {enlace.enlace && (
                            <a href={enlace.enlace} target="_blank" rel="noopener noreferrer">
                              🔗 Visitar Enlace
                            </a>
                          )}
                          <a href="#" onClick={(e) => { e.preventDefault(); /* Editar enlace */ }}>
                            ✏️ Editar Enlace
                          </a>
                          <a href="#" onClick={(e) => { e.preventDefault(); /* Borrar enlace */ }}>
                            🗑️ Borrar Enlace
                          </a>
                        </div>
                      </div>,
                      document.body
                    )}
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      ) : (
        <div className="empty-state">
          <p>No hay enlaces para este bimestre</p>
        </div>
      )}
    </div>
  );

  return (
    <DashboardLayout>
      <div className="docente-aula-virtual">
        <div className="page-header">
          <button className="btn-back" onClick={() => navigate('/docente/cursos')}>
            ← Volver a Cursos
          </button>
          <h1>🎓 Aula Virtual</h1>
          <p>{curso?.curso_nombre || 'Curso'} - {curso?.grado}° {curso?.seccion} - {curso?.anio}</p>
        </div>

        {/* Selector Global de Bimestre */}
        <div className="bimestre-selector-global">
          {Array.from({ length: totalNotas }, (_, i) => i + 1).map((bim) => (
            <button
              key={bim}
              className={`bimestre-tab-global ${bimestreGlobal === bim ? 'active' : ''}`}
              onClick={() => setBimestreGlobal(bim)}
            >
              Bimestre {bim}
            </button>
          ))}
        </div>

        {/* Grid de Cards Gamificado */}
        <div className="aula-dashboard-grid">
          {/* Card: TEMAS */}
          <div 
            className={`aula-section-card temas-card ${expandedCard === 'temas' ? 'expanded' : ''}`}
            onClick={() => expandedCard !== 'temas' && toggleCard('temas')}
          >
            <div className="card-header">
              <div className="card-icon">📚</div>
              <div className="card-info">
                <h3 className="card-title">Temas Interactivos</h3>
                <p className="card-count">{archivos.length}</p>
                <p className="card-subtitle">Temas disponibles</p>
              </div>
              <button 
                className="card-action-btn"
                onClick={(e) => { e.stopPropagation(); setMostrarFormTema(true); }}
              >
                + Nuevo
              </button>
            </div>
            {expandedCard === 'temas' && renderCardContent('temas')}
          </div>

          {/* Card: TAREAS */}
          <div 
            className={`aula-section-card tareas-card ${expandedCard === 'tareas' ? 'expanded' : ''}`}
            onClick={() => expandedCard !== 'tareas' && toggleCard('tareas')}
          >
            <div className="card-header">
              <div className="card-icon">📝</div>
              <div className="card-info">
                <h3 className="card-title">Tareas Virtuales</h3>
                <p className="card-count">{tareas.length}</p>
                <p className="card-subtitle">Tareas asignadas</p>
              </div>
              <button 
                className="card-action-btn"
                onClick={(e) => { e.stopPropagation(); setMostrarFormTarea(true); }}
              >
                + Nuevo
              </button>
            </div>
            {expandedCard === 'tareas' && renderCardContent('tareas')}
          </div>

          {/* Card: EXÁMENES */}
          <div 
            className={`aula-section-card examenes-card ${expandedCard === 'examenes' ? 'expanded' : ''}`}
            onClick={() => expandedCard !== 'examenes' && toggleCard('examenes')}
          >
            <div className="card-header">
              <div className="card-icon">📊</div>
              <div className="card-info">
                <h3 className="card-title">Exámenes</h3>
                <p className="card-count">{examenes.length}</p>
                <p className="card-subtitle">Exámenes creados</p>
              </div>
              <button 
                className="card-action-btn"
                onClick={(e) => { e.stopPropagation(); setMostrarFormExamen(true); }}
              >
                + Nuevo
              </button>
            </div>
            {expandedCard === 'examenes' && renderCardContent('examenes')}
          </div>

          {/* Card: VIDEOTECA */}
          <div 
            className={`aula-section-card videos-card ${expandedCard === 'videos' ? 'expanded' : ''}`}
            onClick={() => expandedCard !== 'videos' && toggleCard('videos')}
          >
            <div className="card-header">
              <div className="card-icon">🎥</div>
              <div className="card-info">
                <h3 className="card-title">Videoteca</h3>
                <p className="card-count">{videos.length}</p>
                <p className="card-subtitle">Videos disponibles</p>
              </div>
              <button 
                className="card-action-btn"
                onClick={(e) => { e.stopPropagation(); /* Registrar nuevo video */ }}
              >
                + Nuevo
              </button>
            </div>
            {expandedCard === 'videos' && renderCardContent('videos')}
          </div>

          {/* Card: ENLACES */}
          <div 
            className={`aula-section-card enlaces-card ${expandedCard === 'enlaces' ? 'expanded' : ''}`}
            onClick={() => expandedCard !== 'enlaces' && toggleCard('enlaces')}
          >
            <div className="card-header">
              <div className="card-icon">🔗</div>
              <div className="card-info">
                <h3 className="card-title">Enlaces de Ayuda</h3>
                <p className="card-count">{enlaces.length}</p>
                <p className="card-subtitle">Enlaces compartidos</p>
              </div>
              <button 
                className="card-action-btn"
                onClick={(e) => { e.stopPropagation(); /* Registrar nuevo enlace */ }}
              >
                + Nuevo
              </button>
            </div>
            {expandedCard === 'enlaces' && renderCardContent('enlaces')}
          </div>
        </div>
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
