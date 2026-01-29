import React, { useState, useEffect, useCallback, useRef } from 'react';
import { createPortal } from 'react-dom';
import { useParams, useNavigate } from 'react-router-dom';
import { DndContext, closestCenter, KeyboardSensor, PointerSensor, useSensor, useSensors } from '@dnd-kit/core';
import { arrayMove, SortableContext, sortableKeyboardCoordinates, useSortable, verticalListSortingStrategy } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import DashboardLayout from '../components/DashboardLayout';
import api from '../services/api';
import Swal from 'sweetalert2';
import './DocenteAulaVirtual-gamificado.css';

// Componente SortableItem para drag and drop
function SortableItem({ archivo, onEdit, onDelete, openDropdown, toggleDropdown, dropdownPosition, buttonRef }) {
  const {
    attributes,
    listeners,
    setNodeRef,
    transform,
    transition,
    isDragging,
  } = useSortable({ id: archivo.id });

  const style = {
    transform: CSS.Transform.toString(transform),
    transition,
    opacity: isDragging ? 0.5 : 1,
    cursor: 'grab',
  };

  return (
    <tr 
      ref={setNodeRef} 
      style={style}
      className={isDragging ? 'dragging' : ''}
    >
      <td 
        {...attributes} 
        {...listeners}
        className="drag-handle"
        style={{ cursor: 'grab', userSelect: 'none' }}
      >
        {archivo.nombre}
      </td>
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
                <a href="#" onClick={(e) => { e.preventDefault(); onEdit(archivo); }}>
                  ✏️ Editar Tema
                </a>
                <a href="#" onClick={(e) => { e.preventDefault(); onDelete(archivo); }}>
                  🗑️ Borrar Tema
                </a>
              </div>
            </div>,
            document.body
          )}
        </div>
      </td>
    </tr>
  );
}

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
  const [mostrarFormVideo, setMostrarFormVideo] = useState(false);
  const [mostrarFormEnlace, setMostrarFormEnlace] = useState(false);
  const [videoEditando, setVideoEditando] = useState(null);
  const [enlaceEditando, setEnlaceEditando] = useState(null);
  const [tareaSeleccionada, setTareaSeleccionada] = useState(null);
  const [mostrarDetallesTarea, setMostrarDetallesTarea] = useState(false);
  const [tareaDetalle, setTareaDetalle] = useState(null);
  
  // Estados para dropdowns con portal
  const [openDropdown, setOpenDropdown] = useState(null);
  const [dropdownPosition, setDropdownPosition] = useState(null);
  const buttonRef = useRef({});
  
  // Estado para card expandido (versión gamificada)
  const [expandedCard, setExpandedCard] = useState(null);
  const [bimestreGlobal, setBimestreGlobal] = useState(1);
  
  // Formularios de video y enlace (después de bimestreGlobal)
  const [formVideo, setFormVideo] = useState({
    descripcion: '',
    enlace: '',
    ciclo: 1
  });
  const [formEnlace, setFormEnlace] = useState({
    descripcion: '',
    enlace: '',
    ciclo: 1
  });
  const [guardandoVideo, setGuardandoVideo] = useState(false);
  const [guardandoEnlace, setGuardandoEnlace] = useState(false);

  // Formulario de tema
  const [formTema, setFormTema] = useState({
    nombre: '',
    archivo: null,
    archivoNombre: '',
    enlace: '',
    ciclo: bimestreGlobal
  });
  const [temaEditando, setTemaEditando] = useState(null);
  const [guardandoTema, setGuardandoTema] = useState(false);
  
  // Drag and Drop sensors
  const sensors = useSensors(
    useSensor(PointerSensor),
    useSensor(KeyboardSensor, {
      coordinateGetter: sortableKeyboardCoordinates,
    })
  );

  // Formulario de tarea
  const [formTarea, setFormTarea] = useState({
    titulo: '',
    descripcion: '',
    fecha_entrega: '',
    ciclo: bimestreGlobal,
    archivo: null,
    archivoNombre: '',
    enlace: ''
  });
  const [guardandoTarea, setGuardandoTarea] = useState(false);
  const [tareaEditando, setTareaEditando] = useState(null);
  
  // Estado para modal de Marcar Entregas
  const [mostrarModalEntregas, setMostrarModalEntregas] = useState(false);
  const [tareaParaEntregas, setTareaParaEntregas] = useState(null);
  const [alumnosEntregas, setAlumnosEntregas] = useState([]);
  const [infoTareaEntregas, setInfoTareaEntregas] = useState(null);
  const [notasTemporales, setNotasTemporales] = useState({});
  const [loadingEntregas, setLoadingEntregas] = useState(false);
  
  // Estado para modal de Asignar a Registro
  const [mostrarModalAsignarRegistro, setMostrarModalAsignarRegistro] = useState(false);
  const [tareaParaAsignar, setTareaParaAsignar] = useState(null);
  const [datosAsignarRegistro, setDatosAsignarRegistro] = useState(null);
  const [criterioSeleccionado, setCriterioSeleccionado] = useState('');
  const [cuadroSeleccionado, setCuadroSeleccionado] = useState('0');
  const [loadingAsignarRegistro, setLoadingAsignarRegistro] = useState(false);
  const [guardandoAsignarRegistro, setGuardandoAsignarRegistro] = useState(false);

  // Formulario de examen
  const [formExamen, setFormExamen] = useState({
    titulo: '',
    tipo: 'VIRTUAL',
    tipo_puntaje: 'GENERAL',
    puntos_correcta: 1.0,
    penalizar_incorrecta: 'NO',
    penalizacion_incorrecta: 0.0,
    tiempo: 60,
    intentos: 1,
    orden_preguntas: 'PREDETERMINADO',
    preguntas_max: 1,
    ciclo: 1,
    estado: 'INACTIVO',
    habilitar_fecha_hora: false,
    fecha_desde: '',
    fecha_hasta: '',
    hora_desde: '08:00',
    hora_hasta: '20:00',
    archivo_pdf: null
  });
  const [examenEditando, setExamenEditando] = useState(null);
  const [guardandoExamen, setGuardandoExamen] = useState(false);

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

  // Actualizar ciclo del formulario cuando cambia bimestreGlobal
  useEffect(() => {
    if (!mostrarFormTarea) {
      setFormTarea(prev => ({ ...prev, ciclo: bimestreGlobal }));
    }
  }, [bimestreGlobal, mostrarFormTarea]);

  const cargarArchivos = useCallback(async (ciclo) => {
    try {
      const cicloFiltro = ciclo || cicloArchivos || bimestreGlobal;
      const response = await api.get('/docente/aula-virtual/archivos', {
        params: { asignatura_id: asignaturaId, ciclo: cicloFiltro }
      });
      // Mantener archivos de otros ciclos y actualizar solo los del ciclo actual
      setArchivos(prevArchivos => {
        const otrosCiclos = prevArchivos.filter(a => a.ciclo !== cicloFiltro);
        return [...otrosCiclos, ...(response.data.archivos || [])];
      });
    } catch (error) {
      console.error('Error cargando archivos:', error);
    }
  }, [asignaturaId, cicloArchivos, bimestreGlobal]);

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
        params: { asignatura_id: asignaturaId, ciclo: ciclo || bimestreGlobal }
      });
      setVideos(response.data.videos || []);
    } catch (error) {
      console.error('Error cargando videos:', error);
    }
  }, [asignaturaId, bimestreGlobal]);

  const cargarEnlaces = useCallback(async (ciclo) => {
    try {
      const response = await api.get('/docente/aula-virtual/enlaces', {
        params: { asignatura_id: asignaturaId, ciclo: ciclo || bimestreGlobal }
      });
      setEnlaces(response.data.enlaces || []);
    } catch (error) {
      console.error('Error cargando enlaces:', error);
    }
  }, [asignaturaId, bimestreGlobal]);

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
    // Actualizar ciclo del formulario cuando cambia el bimestre global
    if (!temaEditando) {
      setFormTema(prev => ({ ...prev, ciclo: bimestreGlobal }));
    }
  }, [bimestreGlobal, temaEditando]);

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

  // Cargar archivos cuando cambia el bimestre global
  useEffect(() => {
    if (!asignaturaId) return;
    cargarArchivos(bimestreGlobal);
  }, [bimestreGlobal, asignaturaId, cargarArchivos]);

  useEffect(() => {
    if (!asignaturaId) return;
    cargarTareas(cicloTareas);
  }, [cicloTareas, asignaturaId, cargarTareas]);

  useEffect(() => {
    if (!asignaturaId) return;
    cargarExamenes(cicloExamenes);
  }, [cicloExamenes, asignaturaId, cargarExamenes]);

  // Cargar videos cuando cambia el bimestre global
  useEffect(() => {
    if (!asignaturaId) return;
    cargarVideos(bimestreGlobal);
  }, [bimestreGlobal, asignaturaId, cargarVideos]);

  // Cargar enlaces cuando cambia el bimestre global
  useEffect(() => {
    if (!asignaturaId) return;
    cargarEnlaces(bimestreGlobal);
  }, [bimestreGlobal, asignaturaId, cargarEnlaces]);

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

  const handleGuardarTema = async (e) => {
    e.preventDefault();
    
    // Validar que al menos tenga nombre
    if (!formTema.nombre.trim()) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'El nombre del tema es requerido',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
      });
      return;
    }

    // Validar que tenga al menos archivo o enlace
    if (!formTema.archivo && !formTema.enlace.trim()) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Debe proporcionar al menos un archivo o una URL',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
      });
      return;
    }

    setGuardandoTema(true);
    try {
      const formData = new FormData();
      formData.append('asignatura_id', asignaturaId);
      formData.append('nombre', formTema.nombre.trim());
      formData.append('ciclo', formTema.ciclo);
      
      if (formTema.archivo) {
        formData.append('archivo', formTema.archivo);
      }
      
      if (formTema.enlace.trim()) {
        formData.append('enlace', formTema.enlace.trim());
      }

      if (temaEditando) {
        // Editar tema existente
        await api.put(`/docente/aula-virtual/archivos/${temaEditando.id}`, formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });
        
        Swal.fire({
          icon: 'success',
          title: '¡Tema actualizado!',
          text: 'El tema se ha actualizado correctamente',
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true
        });
      } else {
        // Crear nuevo tema
        await api.post('/docente/aula-virtual/archivos', formData, {
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
      }

      setMostrarFormTema(false);
      setTemaEditando(null);
      setFormTema({ nombre: '', archivo: null, archivoNombre: '', enlace: '', ciclo: bimestreGlobal });
      await cargarArchivos(bimestreGlobal);
    } catch (error) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: error.response?.data?.error || (temaEditando ? 'No se pudo actualizar el tema' : 'No se pudo crear el tema'),
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
      });
    } finally {
      setGuardandoTema(false);
    }
  };

  const handleEditarTema = (archivo) => {
    setTemaEditando(archivo);
    setFormTema({
      nombre: archivo.nombre || '',
      archivo: null,
      archivoNombre: archivo.archivo ? 'Archivo existente' : '',
      enlace: archivo.enlace || '',
      ciclo: archivo.ciclo || bimestreGlobal
    });
    setMostrarFormTema(true);
    setOpenDropdown(null);
  };

  const handleEliminarTema = async (archivo) => {
    const result = await Swal.fire({
      title: '¿Estás seguro?',
      text: `¿Deseas eliminar el tema "${archivo.nombre}"?`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar'
    });

    if (result.isConfirmed) {
      try {
        await api.delete(`/docente/aula-virtual/archivos/${archivo.id}`);
        
        Swal.fire({
          icon: 'success',
          title: '¡Tema eliminado!',
          text: 'El tema se ha eliminado correctamente',
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true
        });

        setOpenDropdown(null);
        await cargarArchivos(bimestreGlobal);
      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: error.response?.data?.error || 'No se pudo eliminar el tema',
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000
        });
      }
    }
  };

  const handleDragEnd = async (event) => {
    const { active, over } = event;

    if (over && active.id !== over.id) {
      // Filtrar archivos por el bimestre actual
      const archivosFiltrados = archivos.filter(archivo => archivo.ciclo === bimestreGlobal);
      
      const oldIndex = archivosFiltrados.findIndex(item => item.id === active.id);
      const newIndex = archivosFiltrados.findIndex(item => item.id === over.id);

      if (oldIndex === -1 || newIndex === -1) {
        console.error('Índices no encontrados en archivos filtrados');
        return;
      }

      const nuevosArchivosFiltrados = arrayMove(archivosFiltrados, oldIndex, newIndex);
      
      // Actualizar el estado con los nuevos órdenes, manteniendo los archivos de otros bimestres
      const otrosArchivos = archivos.filter(archivo => archivo.ciclo !== bimestreGlobal);
      const nuevosArchivos = [...otrosArchivos, ...nuevosArchivosFiltrados];
      setArchivos(nuevosArchivos);

      // Actualizar orden en el backend
      const ordenes = nuevosArchivosFiltrados
        .filter(archivo => archivo.ciclo === bimestreGlobal) // Asegurar que todos pertenezcan al ciclo
        .map((archivo, index) => ({
          id: parseInt(archivo.id),
          orden: index + 1
        }));

      try {
        if (!asignaturaId || !bimestreGlobal || !ordenes || ordenes.length === 0) {
          throw new Error('Datos incompletos para actualizar orden');
        }

        // Verificar que todos los archivos tengan ID válido
        const archivosInvalidos = ordenes.filter(item => !item.id || isNaN(item.id));
        if (archivosInvalidos.length > 0) {
          throw new Error(`Algunos archivos tienen IDs inválidos: ${JSON.stringify(archivosInvalidos)}`);
        }

        // Asegurar que los datos sean números
        const datosEnviar = {
          asignatura_id: parseInt(asignaturaId),
          ciclo: parseInt(bimestreGlobal),
          ordenes: ordenes
        };

        console.log('Enviando datos para ordenar:', datosEnviar);
        console.log('Cantidad de archivos:', ordenes.length);

        await api.put('/docente/aula-virtual/archivos/ordenar', datosEnviar);
      } catch (error) {
        console.error('Error actualizando orden:', error);
        console.error('Respuesta del servidor:', error.response?.data);
        console.error('Datos enviados:', {
          asignatura_id: asignaturaId,
          ciclo: bimestreGlobal,
          ordenes: ordenes,
          cantidad: ordenes.length
        });
        // Revertir cambios en caso de error
        await cargarArchivos(bimestreGlobal);
        const mensajeError = error.response?.data?.error || error.message || 'No se pudo actualizar el orden de los temas';
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: mensajeError,
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 5000
        });
      }
    }
  };

  const handleCrearTarea = async (e) => {
    e.preventDefault();
    
    if (!formTarea.titulo || !formTarea.fecha_entrega || !formTarea.ciclo) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Título, Fecha de Entrega y Bimestre son requeridos',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
      });
      return;
    }

    // Validar que tenga al menos archivo o enlace (solo para crear, no para editar)
    if (!tareaEditando && !formTarea.archivo && !formTarea.enlace.trim()) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Debe proporcionar al menos un archivo o una URL',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
      });
      return;
    }

    // Para editar, validar que tenga archivo nuevo, enlace nuevo, o que exista archivo/enlace previo
    if (tareaEditando && !formTarea.archivo && !formTarea.enlace.trim()) {
      const tieneArchivoExistente = tareaEditando.archivos && tareaEditando.archivos.length > 0;
      const tieneEnlaceExistente = tareaEditando.enlace_url || tareaEditando.enlace;
      
      if (!tieneArchivoExistente && !tieneEnlaceExistente) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Debe proporcionar al menos un archivo o una URL',
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000
        });
        return;
      }
    }

    setGuardandoTarea(true);
    try {
      const formData = new FormData();
      formData.append('asignatura_id', asignaturaId);
      formData.append('titulo', formTarea.titulo);
      formData.append('descripcion', formTarea.descripcion || '');
      formData.append('fecha_entrega', formTarea.fecha_entrega);
      formData.append('ciclo', formTarea.ciclo);
      
      if (formTarea.archivo) {
        formData.append('archivo', formTarea.archivo);
      }
      
      if (formTarea.enlace.trim()) {
        formData.append('enlace', formTarea.enlace.trim());
      }

      if (tareaEditando) {
        // Editar tarea existente
        await api.put(`/docente/aula-virtual/tareas/${tareaEditando.id}`, formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });
        
        Swal.fire({
          icon: 'success',
          title: '¡Tarea actualizada!',
          text: 'La tarea se ha actualizado correctamente',
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true
        });
      } else {
        // Crear nueva tarea
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
      }

      setMostrarFormTarea(false);
      setTareaEditando(null);
      setFormTarea({ 
        titulo: '', 
        descripcion: '', 
        fecha_entrega: '', 
        ciclo: bimestreGlobal,
        archivo: null,
        archivoNombre: '',
        enlace: '' 
      });
      await cargarTareas();
    } catch (error) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: error.response?.data?.error || (tareaEditando ? 'No se pudo actualizar la tarea' : 'No se pudo crear la tarea'),
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
      });
    } finally {
      setGuardandoTarea(false);
    }
  };

  // Función auxiliar para formatear fecha para input date (YYYY-MM-DD)
  const formatearFechaParaInput = (fecha) => {
    if (!fecha) return '';
    try {
      // Si ya está en formato YYYY-MM-DD, retornarlo
      if (typeof fecha === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(fecha)) {
        return fecha;
      }
      // Si tiene hora, extraer solo la fecha
      const fechaObj = new Date(fecha);
      if (isNaN(fechaObj.getTime())) return '';
      const año = fechaObj.getFullYear();
      const mes = String(fechaObj.getMonth() + 1).padStart(2, '0');
      const dia = String(fechaObj.getDate()).padStart(2, '0');
      return `${año}-${mes}-${dia}`;
    } catch (error) {
      console.error('Error formateando fecha:', error);
      return '';
    }
  };

  const handleEditarTarea = async (tarea) => {
    try {
      // Cargar tarea completa con archivos
      const response = await api.get('/docente/aula-virtual/tareas', {
        params: { asignatura_id: asignaturaId, ciclo: tarea.ciclo || bimestreGlobal }
      });
      const tareaCompleta = response.data.tareas.find(t => t.id === tarea.id);
      
      const fechaEntrega = tareaCompleta?.fecha_entrega || tarea.fecha_entrega || '';
      
      setTareaEditando(tareaCompleta || tarea);
      setFormTarea({
        titulo: tareaCompleta?.titulo || tarea.titulo || '',
        descripcion: tareaCompleta?.descripcion || tarea.descripcion || '',
        fecha_entrega: formatearFechaParaInput(fechaEntrega),
        ciclo: tareaCompleta?.ciclo || tarea.ciclo || bimestreGlobal,
        archivo: null,
        archivoNombre: tareaCompleta?.archivos && tareaCompleta.archivos.length > 0 ? 'Archivo existente' : '',
        enlace: tareaCompleta?.enlace_url || tareaCompleta?.enlace || tarea.enlace || ''
      });
      setMostrarFormTarea(true);
      setOpenDropdown(null);
    } catch (error) {
      console.error('Error cargando tarea para editar:', error);
      // Si falla, usar los datos básicos de la tarea
      setTareaEditando(tarea);
      setFormTarea({
        titulo: tarea.titulo || '',
        descripcion: tarea.descripcion || '',
        fecha_entrega: formatearFechaParaInput(tarea.fecha_entrega),
        ciclo: tarea.ciclo || bimestreGlobal,
        archivo: null,
        archivoNombre: '',
        enlace: tarea.enlace || ''
      });
      setMostrarFormTarea(true);
      setOpenDropdown(null);
    }
  };

  const handleEliminarTarea = async (tarea) => {
    const result = await Swal.fire({
      title: '¿Estás seguro?',
      text: `¿Deseas eliminar la tarea "${tarea.titulo}"?`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar'
    });

    if (result.isConfirmed) {
      try {
        await api.delete(`/docente/aula-virtual/tareas/${tarea.id}`);
        
        Swal.fire({
          icon: 'success',
          title: '¡Tarea eliminada!',
          text: 'La tarea se ha eliminado correctamente',
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true
        });

        setOpenDropdown(null);
        await cargarTareas();
      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: error.response?.data?.error || 'No se pudo eliminar la tarea',
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000
        });
      }
    }
  };

  const handleVerDetallesTarea = async (tarea) => {
    try {
      // Cargar tarea completa con archivos
      const response = await api.get('/docente/aula-virtual/tareas', {
        params: { asignatura_id: asignaturaId, ciclo: tarea.ciclo || bimestreGlobal }
      });
      const tareaCompleta = response.data.tareas.find(t => t.id === tarea.id);
      setTareaDetalle(tareaCompleta || tarea);
      setMostrarDetallesTarea(true);
      setOpenDropdown(null);
    } catch (error) {
      console.error('Error cargando detalles de tarea:', error);
      setTareaDetalle(tarea);
      setMostrarDetallesTarea(true);
      setOpenDropdown(null);
    }
  };

  const handleMarcarEntregas = async (tarea) => {
    try {
      setTareaParaEntregas(tarea);
      setLoadingEntregas(true);
      setMostrarModalEntregas(true);
      setOpenDropdown(null);

      const response = await api.get(`/docente/aula-virtual/tareas/${tarea.id}/entregas`);
      
      setAlumnosEntregas(response.data.alumnos || []);
      setInfoTareaEntregas(response.data.tarea);
      
      // Inicializar notas temporales
      const notasIniciales = {};
      response.data.alumnos.forEach(alumno => {
        notasIniciales[alumno.matricula_id] = alumno.nota || '';
      });
      setNotasTemporales(notasIniciales);
    } catch (error) {
      console.error('Error cargando entregas:', error);
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: error.response?.data?.error || 'No se pudieron cargar las entregas',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
      });
    } finally {
      setLoadingEntregas(false);
    }
  };

  const handleGuardarTodasLasNotas = async () => {
    if (!tareaParaEntregas) return;

    const notasAGuardar = Object.keys(notasTemporales).filter(matriculaId => {
      const nota = notasTemporales[matriculaId];
      return nota && nota.trim() !== '';
    });

    if (notasAGuardar.length === 0) {
      Swal.fire({
        icon: 'info',
        title: 'Sin notas',
        text: 'No hay notas para guardar',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2000
      });
      return;
    }

    try {
      // Guardar todas las notas
      await Promise.all(
        notasAGuardar.map(matriculaId =>
          api.put(`/docente/aula-virtual/tareas/${tareaParaEntregas.id}/entregas/${matriculaId}/nota`, {
            nota: notasTemporales[matriculaId]
          })
        )
      );

      Swal.fire({
        icon: 'success',
        title: '¡Notas guardadas!',
        text: `Se guardaron ${notasAGuardar.length} nota(s) correctamente`,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
      });
    } catch (error) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: error.response?.data?.error || 'No se pudieron guardar todas las notas',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
      });
    }
  };

  const handleAsignarRegistro = async (tarea) => {
    try {
      setTareaParaAsignar(tarea);
      setLoadingAsignarRegistro(true);
      setMostrarModalAsignarRegistro(true);
      setOpenDropdown(null);

      const response = await api.get(`/docente/aula-virtual/tareas/${tarea.id}/asignar-registro`);
      
      setDatosAsignarRegistro(response.data);
      
      // Seleccionar el primer criterio/indicador por defecto si hay
      if (response.data.criterios && response.data.criterios.length > 0) {
        const primerCriterio = response.data.criterios[0];
        if (primerCriterio.indicadores && primerCriterio.indicadores.length > 0) {
          const primerIndicador = primerCriterio.indicadores[0];
          setCriterioSeleccionado(`${primerCriterio.id}_${primerIndicador.id}`);
          setCuadroSeleccionado('0');
        }
      }
    } catch (error) {
      console.error('Error cargando datos para asignar registro:', error);
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: error.response?.data?.error || 'No se pudieron cargar los datos',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
      });
    } finally {
      setLoadingAsignarRegistro(false);
    }
  };

  const handleGuardarAsignarRegistro = async () => {
    if (!tareaParaAsignar || !criterioSeleccionado || cuadroSeleccionado === '') {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Debe seleccionar un criterio y un cuadro',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
      });
      return;
    }

    const result = await Swal.fire({
      title: '¿Estás seguro?',
      text: 'Se reemplazarán las notas en el registro del cuadro seleccionado',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Sí, asignar',
      cancelButtonText: 'Cancelar'
    });

    if (!result.isConfirmed) return;

    setGuardandoAsignarRegistro(true);

    try {
      await api.post(`/docente/aula-virtual/tareas/${tareaParaAsignar.id}/asignar-registro`, {
        criterio_id: criterioSeleccionado,
        cuadro: cuadroSeleccionado
      });

      Swal.fire({
        icon: 'success',
        title: '¡Notas asignadas!',
        text: 'Las notas se han asignado al registro correctamente',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
      });

      setMostrarModalAsignarRegistro(false);
      setTareaParaAsignar(null);
      setDatosAsignarRegistro(null);
      setCriterioSeleccionado('');
      setCuadroSeleccionado('0');
    } catch (error) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: error.response?.data?.error || 'No se pudieron asignar las notas',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
      });
    } finally {
      setGuardandoAsignarRegistro(false);
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

  const renderTemasContent = () => {
    // Filtrar archivos por el bimestre global
    const archivosFiltrados = archivos.filter(archivo => archivo.ciclo === bimestreGlobal);

    return (
      <div className="card-content-expanded">
        {loading ? (
          <div className="empty-state">
            <p>Cargando temas interactivos...</p>
          </div>
        ) : archivosFiltrados.length > 0 ? (
          <DndContext
            sensors={sensors}
            collisionDetection={closestCenter}
            onDragEnd={handleDragEnd}
          >
            <table>
              <thead>
                <tr>
                  <th>NOMBRE</th>
                  <th>ACCIONES</th>
                </tr>
              </thead>
              <SortableContext
                items={archivosFiltrados.map(archivo => archivo.id)}
                strategy={verticalListSortingStrategy}
              >
                <tbody>
                  {archivosFiltrados.map((archivo) => (
                    <SortableItem
                      key={archivo.id}
                      archivo={archivo}
                      onEdit={handleEditarTema}
                      onDelete={handleEliminarTema}
                      openDropdown={openDropdown}
                      toggleDropdown={toggleDropdown}
                      dropdownPosition={dropdownPosition}
                      buttonRef={buttonRef}
                    />
                  ))}
                </tbody>
              </SortableContext>
            </table>
          </DndContext>
        ) : (
          <div className="empty-state">
            <p>No hay temas interactivos para este bimestre</p>
          </div>
        )}
      </div>
    );
  };

  const renderTareasContent = () => (
    <div className="card-content-expanded">
      {/* Modal de Formulario de Tarea */}
      {mostrarFormTarea && createPortal(
        <div 
          className="modal-tema-overlay"
          onClick={() => {
            setMostrarFormTarea(false);
            setTareaEditando(null);
            setFormTarea({ 
              titulo: '', 
              descripcion: '', 
              fecha_entrega: '', 
              ciclo: bimestreGlobal,
              archivo: null,
              archivoNombre: '',
              enlace: '' 
            });
          }}
        >
          <div 
            className="modal-tema-container"
            onClick={(e) => e.stopPropagation()}
            role="dialog"
            aria-modal="true"
            aria-labelledby="modal-tarea-title"
          >
            <div className="modal-tema-header">
              <h2 id="modal-tarea-title">
                {tareaEditando ? '✏️ Editar Tarea' : '📝 Registrar Tarea'}
              </h2>
              <button
                className="modal-tema-close"
                onClick={() => {
                  setMostrarFormTarea(false);
                  setTareaEditando(null);
                  setFormTarea({ 
                    titulo: '', 
                    descripcion: '', 
                    fecha_entrega: '', 
                    ciclo: bimestreGlobal,
                    archivo: null,
                    archivoNombre: '',
                    enlace: '' 
                  });
                }}
                aria-label="Cerrar"
              >
                ×
              </button>
            </div>

            <div className="modal-tema-body">
              <form onSubmit={handleCrearTarea}>
                {/* Campo Título */}
                <div className="form-group">
                  <label htmlFor="tarea-titulo">
                    Título *
                  </label>
                  <input
                    type="text"
                    id="tarea-titulo"
                    className="form-input"
                    value={formTarea.titulo}
                    onChange={(e) => setFormTarea({ ...formTarea, titulo: e.target.value })}
                    placeholder="Ej: Tarea 1: Investigación sobre..."
                    required
                  />
                </div>

                {/* Campo Descripción */}
                <div className="form-group">
                  <label htmlFor="tarea-descripcion">
                    Descripción
                  </label>
                  <textarea
                    id="tarea-descripcion"
                    className="form-input"
                    value={formTarea.descripcion}
                    onChange={(e) => setFormTarea({ ...formTarea, descripcion: e.target.value })}
                    rows="5"
                    placeholder="Descripción detallada de la tarea..."
                  />
                </div>

                {/* Campo Fecha de Entrega */}
                <div className="form-group">
                  <label htmlFor="tarea-fecha-entrega">
                    Fecha de Entrega *
                  </label>
                  <input
                    type="date"
                    id="tarea-fecha-entrega"
                    className="form-input"
                    value={formTarea.fecha_entrega}
                    onChange={(e) => setFormTarea({ ...formTarea, fecha_entrega: e.target.value })}
                    required
                  />
                </div>

                {/* Campo Bimestre */}
                <div className="form-group">
                  <label htmlFor="tarea-ciclo">
                    Bimestre *
                  </label>
                  <select
                    id="tarea-ciclo"
                    className="form-input"
                    value={formTarea.ciclo}
                    onChange={(e) => setFormTarea({ ...formTarea, ciclo: parseInt(e.target.value) })}
                    required
                  >
                    {Array.from({ length: totalNotas }, (_, i) => i + 1).map((bim) => (
                      <option key={bim} value={bim}>
                        Bimestre {bim}
                      </option>
                    ))}
                  </select>
                </div>

                {/* Campo Archivo */}
                <div className="form-group">
                  <label htmlFor="tarea-archivo">
                    Archivos Adjuntos (Opcional)
                  </label>
                  <div className="file-input-wrapper">
                    <input
                      type="file"
                      id="tarea-archivo"
                      className="form-input-file"
                      accept=".pdf"
                      onChange={(e) => {
                        const file = e.target.files[0];
                        if (file) {
                          setFormTarea({
                            ...formTarea,
                            archivo: file,
                            archivoNombre: file.name
                          });
                        }
                      }}
                    />
                    <label htmlFor="tarea-archivo" className="file-input-label">
                      {formTarea.archivoNombre || (tareaEditando && tareaEditando.archivos && tareaEditando.archivos.length > 0 ? 'Archivo existente' : 'Elegir archivos')}
                    </label>
                    {formTarea.archivoNombre && (
                      <button
                        type="button"
                        className="file-clear-btn"
                        onClick={() => setFormTarea({ ...formTarea, archivo: null, archivoNombre: '' })}
                      >
                        ×
                      </button>
                    )}
                  </div>
                  <small className="form-help-text">
                    Puedes subir un archivo PDF o proporcionar una URL, o ambos
                  </small>
                </div>

                {/* Campo URL */}
                <div className="form-group">
                  <label htmlFor="tarea-enlace">
                    URL (Opcional)
                  </label>
                  <textarea
                    id="tarea-enlace"
                    className="form-input"
                    value={formTarea.enlace}
                    onChange={(e) => setFormTarea({ ...formTarea, enlace: e.target.value })}
                    rows="2"
                    placeholder="https://ejemplo.com/tarea"
                  />
                  <small className="form-help-text">
                    Enlace externo a la tarea
                  </small>
                </div>

                {/* Botones de Acción */}
                <div className="form-actions">
                  <button
                    type="button"
                    className="btn-cancelar"
                    onClick={() => {
                      setMostrarFormTarea(false);
                      setTareaEditando(null);
                      setFormTarea({ 
                        titulo: '', 
                        descripcion: '', 
                        fecha_entrega: '', 
                        ciclo: bimestreGlobal,
                        archivo: null,
                        archivoNombre: '',
                        enlace: '' 
                      });
                    }}
                    disabled={guardandoTarea}
                  >
                    Cancelar
                  </button>
                  <button
                    type="submit"
                    className="btn-guardar"
                    disabled={guardandoTarea}
                  >
                    {guardandoTarea ? '⏳ Guardando...' : (tareaEditando ? '💾 Actualizar' : '💾 Guardar Datos')}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>,
        document.body
      )}

      {/* Modal de Detalles de Tarea */}
      {mostrarDetallesTarea && tareaDetalle && createPortal(
        <div 
          className="modal-tarea-detalle-overlay"
          onClick={() => {
            setMostrarDetallesTarea(false);
            setTareaDetalle(null);
          }}
        >
          <div 
            className="modal-tarea-detalle-container"
            onClick={(e) => e.stopPropagation()}
            role="dialog"
            aria-modal="true"
            aria-labelledby="modal-tarea-detalle-title"
          >
            <div className="modal-tarea-detalle-header">
              <h2 id="modal-tarea-detalle-title">
                📝 Detalles de la Tarea
              </h2>
              <button
                className="modal-tarea-detalle-close"
                onClick={() => {
                  setMostrarDetallesTarea(false);
                  setTareaDetalle(null);
                }}
                aria-label="Cerrar"
              >
                ×
              </button>
            </div>

            <div className="modal-tarea-detalle-body">
              <table className="tarea-detalle-table">
                <tbody>
                  <tr>
                    <td className="tarea-detalle-label">TÍTULO</td>
                    <td className="tarea-detalle-value">{tareaDetalle.titulo || 'Sin título'}</td>
                  </tr>
                  <tr>
                    <td className="tarea-detalle-label">DESCRIPCIÓN</td>
                    <td className="tarea-detalle-value">
                      {tareaDetalle.descripcion || <span className="text-muted">Sin descripción</span>}
                    </td>
                  </tr>
                  <tr>
                    <td className="tarea-detalle-label">FECHA DE REGISTRO</td>
                    <td className="tarea-detalle-value">
                      {new Date(tareaDetalle.fecha_hora).toLocaleDateString('es-PE', { 
                        day: '2-digit', 
                        month: '2-digit', 
                        year: 'numeric' 
                      })}
                    </td>
                  </tr>
                  <tr>
                    <td className="tarea-detalle-label">FECHA DE ENTREGA</td>
                    <td className="tarea-detalle-value">
                      {new Date(tareaDetalle.fecha_entrega).toLocaleDateString('es-PE', { 
                        day: '2-digit', 
                        month: '2-digit', 
                        year: 'numeric' 
                      })}
                    </td>
                  </tr>
                  <tr>
                    <td className="tarea-detalle-label">ENVIADO POR</td>
                    <td className="tarea-detalle-value">
                      {tareaDetalle.docente_nombre || 'Docente'}
                    </td>
                  </tr>
                  <tr>
                    <td className="tarea-detalle-label">ARCHIVOS ADJUNTOS</td>
                    <td className="tarea-detalle-value">
                      {tareaDetalle.archivos && tareaDetalle.archivos.length > 0 ? (
                        <div className="tarea-archivos-list">
                          {tareaDetalle.archivos.map((archivo, idx) => (
                            <a
                              key={idx}
                              href={archivo.archivo_url}
                              target="_blank"
                              rel="noopener noreferrer"
                              className="tarea-archivo-link"
                            >
                              📎 {archivo.nombre}
                            </a>
                          ))}
                        </div>
                      ) : (
                        <span className="text-muted">Sin archivos adjuntos</span>
                      )}
                    </td>
                  </tr>
                  {tareaDetalle.enlace_url && (
                    <tr>
                      <td className="tarea-detalle-label">URL</td>
                      <td className="tarea-detalle-value">
                        <a
                          href={tareaDetalle.enlace_url}
                          target="_blank"
                          rel="noopener noreferrer"
                          className="tarea-url-link"
                        >
                          🔗 {tareaDetalle.enlace_url}
                        </a>
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
          </div>
        </div>,
        document.body
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
                <td className="text-center">{tarea.titulo}</td>
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
                          <a href="#" onClick={(e) => { e.preventDefault(); handleVerDetallesTarea(tarea); }}>
                            ℹ️ Ver Detalles
                          </a>
                          <a href="#" onClick={(e) => { e.preventDefault(); handleMarcarEntregas(tarea); }}>
                            ✓ Marcar Entregas
                          </a>
                          <a href="#" onClick={(e) => { e.preventDefault(); handleAsignarRegistro(tarea); }}>
                            📋 Asignar a Registro
                          </a>
                          <a href="#" onClick={(e) => { e.preventDefault(); handleEditarTarea(tarea); }}>
                            ✏️ Editar Tarea
                          </a>
                          <a href="#" onClick={(e) => { e.preventDefault(); handleEliminarTarea(tarea); }}>
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
                <td className="text-center">{examen.total_preguntas || 0}</td>
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
                          <a href="#" onClick={(e) => { e.preventDefault(); /* Asignar al Registro */ }}>
                            📋 Asignar al Registro
                          </a>
                          <a href="#" onClick={(e) => { e.preventDefault(); handleHabilitarDeshabilitarExamen(examen); }}>
                            🔒 Habilitar / Deshabilitar
                          </a>
                          <a href="#" onClick={(e) => { e.preventDefault(); handleEditarExamen(examen); }}>
                            ✏️ Editar Examen
                          </a>
                          <a href="#" onClick={(e) => { e.preventDefault(); handleEliminarExamen(examen); }}>
                            🗑️ Eliminar Examen
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

  // Funciones para manejar videos
  const handleCrearVideo = async (e) => {
    e.preventDefault();
    
    if (!formVideo.descripcion || !formVideo.enlace || !formVideo.ciclo) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Descripción, Enlace y Bimestre son requeridos',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
      });
      return;
    }

    setGuardandoVideo(true);
    try {
      if (videoEditando) {
        await api.put(`/docente/aula-virtual/videos/${videoEditando.id}`, {
          descripcion: formVideo.descripcion,
          enlace: formVideo.enlace,
          ciclo: formVideo.ciclo
        });
        Swal.fire({
          icon: 'success',
          title: '¡Video actualizado!',
          text: 'El video se ha actualizado correctamente',
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true
        });
      } else {
        await api.post('/docente/aula-virtual/videos', {
          asignatura_id: asignaturaId,
          descripcion: formVideo.descripcion,
          enlace: formVideo.enlace,
          ciclo: formVideo.ciclo
        });
        Swal.fire({
          icon: 'success',
          title: '¡Video creado!',
          text: 'El video se ha creado correctamente',
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true
        });
      }

      setMostrarFormVideo(false);
      setVideoEditando(null);
      setFormVideo({ descripcion: '', enlace: '', ciclo: bimestreGlobal });
      await cargarVideos(bimestreGlobal);
    } catch (error) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: error.response?.data?.error || 'No se pudo guardar el video',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
      });
    } finally {
      setGuardandoVideo(false);
    }
  };

  const handleEditarVideo = (video) => {
    setVideoEditando(video);
    setFormVideo({
      descripcion: video.descripcion || '',
      enlace: video.enlace || '',
      ciclo: video.ciclo || bimestreGlobal
    });
    setMostrarFormVideo(true);
    setOpenDropdown(null);
  };

  const handleEliminarVideo = async (video) => {
    const result = await Swal.fire({
      title: '¿Estás seguro?',
      text: `¿Deseas eliminar el video "${video.descripcion}"?`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar'
    });

    if (result.isConfirmed) {
      try {
        await api.delete(`/docente/aula-virtual/videos/${video.id}`);
        
        Swal.fire({
          icon: 'success',
          title: '¡Video eliminado!',
          text: 'El video se ha eliminado correctamente',
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true
        });

        setOpenDropdown(null);
        await cargarVideos(bimestreGlobal);
      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: error.response?.data?.error || 'No se pudo eliminar el video',
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000
        });
      }
    }
  };

  // Funciones para manejar exámenes
  const handleEditarExamen = (examen) => {
    // Determinar si tiene fecha y hora habilitada (si tiene valores distintos a los por defecto)
    const fechaActual = new Date().toISOString().split('T')[0];
    const tieneFechaHora = examen.fecha_desde && examen.fecha_desde !== fechaActual && 
                           examen.fecha_hasta && examen.fecha_hasta !== fechaActual &&
                           examen.hora_desde && examen.hora_desde !== '00:00:00' &&
                           examen.hora_hasta && examen.hora_hasta !== '23:59:59';

    setExamenEditando(examen);
    setFormExamen({
      titulo: examen.titulo || '',
      tipo: examen.tipo || 'VIRTUAL',
      tipo_puntaje: examen.tipo_puntaje || 'GENERAL',
      puntos_correcta: examen.puntos_correcta || 1.0,
      penalizar_incorrecta: examen.penalizar_incorrecta || 'NO',
      penalizacion_incorrecta: examen.penalizacion_incorrecta || 0.0,
      tiempo: examen.tiempo || 60,
      intentos: examen.intentos || 1,
      orden_preguntas: examen.orden_preguntas || 'PREDETERMINADO',
      preguntas_max: examen.preguntas_max || 1,
      ciclo: examen.ciclo || bimestreGlobal,
      estado: examen.estado || 'INACTIVO',
      habilitar_fecha_hora: tieneFechaHora,
      fecha_desde: examen.fecha_desde || '',
      fecha_hasta: examen.fecha_hasta || '',
      hora_desde: examen.hora_desde ? examen.hora_desde.substring(0, 5) : '08:00', // Convertir HH:MM:SS a HH:MM
      hora_hasta: examen.hora_hasta ? examen.hora_hasta.substring(0, 5) : '20:00',
      archivo_pdf: null // No cargamos el archivo existente, solo permitimos reemplazarlo
    });
    setMostrarFormExamen(true);
    setOpenDropdown(null);
  };

  const handleEliminarExamen = async (examen) => {
    const result = await Swal.fire({
      title: '¿Estás seguro?',
      text: `¿Deseas eliminar el examen "${examen.titulo}"? Esta acción también eliminará todas las preguntas y alternativas asociadas.`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar'
    });

    if (result.isConfirmed) {
      try {
        await api.delete(`/docente/aula-virtual/examenes/${examen.id}`);
        
        Swal.fire({
          icon: 'success',
          title: '¡Examen eliminado!',
          text: 'El examen y todos sus datos asociados se han eliminado correctamente',
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true
        });

        setOpenDropdown(null);
        await cargarExamenes();
      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: error.response?.data?.error || 'No se pudo eliminar el examen',
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000
        });
      }
    }
  };

  const handleHabilitarDeshabilitarExamen = async (examen) => {
    const nuevoEstado = examen.estado === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO';
    const accion = nuevoEstado === 'ACTIVO' ? 'habilitar' : 'deshabilitar';
    
    const result = await Swal.fire({
      title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} examen?`,
      text: `¿Deseas ${accion} el examen "${examen.titulo}"?`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: nuevoEstado === 'ACTIVO' ? '#10b981' : '#ef4444',
      cancelButtonColor: '#6b7280',
      confirmButtonText: `Sí, ${accion}`,
      cancelButtonText: 'Cancelar'
    });

    if (result.isConfirmed) {
      try {
        const response = await api.put(`/docente/aula-virtual/examenes/${examen.id}/estado`, {
          estado: nuevoEstado
        });
        
        Swal.fire({
          icon: 'success',
          title: `¡Examen ${nuevoEstado === 'ACTIVO' ? 'habilitado' : 'deshabilitado'}!`,
          text: response.data.message || `El examen se ha ${accion}do correctamente`,
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true
        });

        setOpenDropdown(null);
        await cargarExamenes(); // Recargar para actualizar la grilla
      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: error.response?.data?.error || `No se pudo ${accion} el examen`,
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000
        });
      }
    }
  };

  // Funciones para manejar enlaces
  const handleCrearEnlace = async (e) => {
    e.preventDefault();
    
    if (!formEnlace.descripcion || !formEnlace.enlace || !formEnlace.ciclo) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Descripción, Enlace y Bimestre son requeridos',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
      });
      return;
    }

    setGuardandoEnlace(true);
    try {
      if (enlaceEditando) {
        await api.put(`/docente/aula-virtual/enlaces/${enlaceEditando.id}`, {
          descripcion: formEnlace.descripcion,
          enlace: formEnlace.enlace,
          ciclo: formEnlace.ciclo
        });
        Swal.fire({
          icon: 'success',
          title: '¡Enlace actualizado!',
          text: 'El enlace se ha actualizado correctamente',
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true
        });
      } else {
        await api.post('/docente/aula-virtual/enlaces', {
          asignatura_id: asignaturaId,
          descripcion: formEnlace.descripcion,
          enlace: formEnlace.enlace,
          ciclo: formEnlace.ciclo
        });
        Swal.fire({
          icon: 'success',
          title: '¡Enlace creado!',
          text: 'El enlace se ha creado correctamente',
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true
        });
      }

      setMostrarFormEnlace(false);
      setEnlaceEditando(null);
      setFormEnlace({ descripcion: '', enlace: '', ciclo: bimestreGlobal });
      await cargarEnlaces(bimestreGlobal);
    } catch (error) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: error.response?.data?.error || 'No se pudo guardar el enlace',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
      });
    } finally {
      setGuardandoEnlace(false);
    }
  };

  const handleEditarEnlace = (enlace) => {
    setEnlaceEditando(enlace);
    setFormEnlace({
      descripcion: enlace.descripcion || '',
      enlace: enlace.enlace || '',
      ciclo: enlace.ciclo || bimestreGlobal
    });
    setMostrarFormEnlace(true);
    setOpenDropdown(null);
  };

  const handleEliminarEnlace = async (enlace) => {
    const result = await Swal.fire({
      title: '¿Estás seguro?',
      text: `¿Deseas eliminar el enlace "${enlace.descripcion}"?`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar'
    });

    if (result.isConfirmed) {
      try {
        await api.delete(`/docente/aula-virtual/enlaces/${enlace.id}`);
        
        Swal.fire({
          icon: 'success',
          title: '¡Enlace eliminado!',
          text: 'El enlace se ha eliminado correctamente',
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true
        });

        setOpenDropdown(null);
        await cargarEnlaces(bimestreGlobal);
      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: error.response?.data?.error || 'No se pudo eliminar el enlace',
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000
        });
      }
    }
  };

  const renderVideosContent = () => {
    // Obtener videos del ciclo actual (bimestre global)
    const videosCicloActual = videos.filter(v => v.ciclo === bimestreGlobal);
    
    return (
      <div className="card-content-expanded">
        {/* Modal de Formulario de Video */}
        {mostrarFormVideo && createPortal(
          <div 
            className="modal-tema-overlay"
            onClick={() => {
              setMostrarFormVideo(false);
              setVideoEditando(null);
              setFormVideo({ descripcion: '', enlace: '', ciclo: bimestreGlobal });
            }}
          >
            <div 
              className="modal-tema-container"
              onClick={(e) => e.stopPropagation()}
              role="dialog"
              aria-modal="true"
              aria-labelledby="modal-video-title"
            >
              <div className="modal-tema-header">
                <h2 id="modal-video-title">
                  {videoEditando ? '✏️ Editar Video' : '🎥 Registrar Video'}
                </h2>
                <button
                  className="modal-tema-close"
                  onClick={() => {
                    setMostrarFormVideo(false);
                    setVideoEditando(null);
                    setFormVideo({ descripcion: '', enlace: '', ciclo: bimestreGlobal });
                  }}
                  aria-label="Cerrar"
                >
                  ×
                </button>
              </div>

              <div className="modal-tema-body">
                <form onSubmit={handleCrearVideo}>
                  <div className="form-group">
                    <label htmlFor="video-descripcion">
                      Descripción *
                    </label>
                    <input
                      type="text"
                      id="video-descripcion"
                      className="form-input"
                      value={formVideo.descripcion}
                      onChange={(e) => setFormVideo({ ...formVideo, descripcion: e.target.value })}
                      placeholder="Ej: Video sobre el Sistema Solar"
                      required
                    />
                  </div>

                  <div className="form-group">
                    <label htmlFor="video-enlace">
                      Enlace *
                    </label>
                    <input
                      type="url"
                      id="video-enlace"
                      className="form-input"
                      value={formVideo.enlace}
                      onChange={(e) => setFormVideo({ ...formVideo, enlace: e.target.value })}
                      placeholder="https://www.youtube.com/watch?v=..."
                      required
                    />
                  </div>

                  <div className="form-group">
                    <label htmlFor="video-ciclo">
                      Bimestre *
                    </label>
                    <select
                      id="video-ciclo"
                      className="form-input"
                      value={formVideo.ciclo}
                      onChange={(e) => setFormVideo({ ...formVideo, ciclo: parseInt(e.target.value) })}
                      required
                    >
                      {Array.from({ length: totalNotas }, (_, i) => i + 1).map((bim) => (
                        <option key={bim} value={bim}>
                          Bimestre {bim}
                        </option>
                      ))}
                    </select>
                  </div>

                  <div className="form-actions">
                    <button
                      type="button"
                      className="btn-cancelar"
                      onClick={() => {
                        setMostrarFormVideo(false);
                        setVideoEditando(null);
                        setFormVideo({ descripcion: '', enlace: '', ciclo: bimestreGlobal });
                      }}
                      disabled={guardandoVideo}
                    >
                      Cancelar
                    </button>
                    <button
                      type="submit"
                      className="btn-guardar"
                      disabled={guardandoVideo}
                    >
                      {guardandoVideo ? '⏳ Guardando...' : '💾 Guardar Datos'}
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>,
          document.body
        )}

        {/* Tabla de videos */}
        {videosCicloActual.length > 0 ? (
          <table className="tabla-aula-virtual">
          <thead>
            <tr>
                <th>NOMBRE</th>
                <th>FECHA</th>
                <th className="text-center">Opciones</th>
            </tr>
          </thead>
          <tbody>
              {videosCicloActual.map((video) => (
              <tr key={video.id}>
                <td>{video.descripcion}</td>
                  <td>{new Date(video.fecha_hora).toLocaleString('es-PE', { 
                    day: '2-digit', 
                    month: '2-digit', 
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                  })}</td>
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
                            <a href="#" onClick={(e) => { e.preventDefault(); handleEditarVideo(video); }}>
                            ✏️ Editar Video
                          </a>
                            <a href="#" onClick={(e) => { e.preventDefault(); handleEliminarVideo(video); }}>
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
  };

  const renderEnlacesContent = () => {
    // Obtener enlaces del ciclo actual (bimestre global)
    const enlacesCicloActual = enlaces.filter(e => e.ciclo === bimestreGlobal);
    
    return (
      <div className="card-content-expanded">
        {/* Modal de Formulario de Enlace */}
        {mostrarFormEnlace && createPortal(
          <div 
            className="modal-tema-overlay"
            onClick={() => {
              setMostrarFormEnlace(false);
              setEnlaceEditando(null);
              setFormEnlace({ descripcion: '', enlace: '', ciclo: bimestreGlobal });
            }}
          >
            <div 
              className="modal-tema-container"
              onClick={(e) => e.stopPropagation()}
              role="dialog"
              aria-modal="true"
              aria-labelledby="modal-enlace-title"
            >
              <div className="modal-tema-header">
                <h2 id="modal-enlace-title">
                  {enlaceEditando ? '✏️ Editar Enlace' : '🔗 Registrar Enlace'}
                </h2>
                <button
                  className="modal-tema-close"
                  onClick={() => {
                    setMostrarFormEnlace(false);
                    setEnlaceEditando(null);
                    setFormEnlace({ descripcion: '', enlace: '', ciclo: bimestreGlobal });
                  }}
                  aria-label="Cerrar"
                >
                  ×
                </button>
              </div>

              <div className="modal-tema-body">
                <form onSubmit={handleCrearEnlace}>
                  <div className="form-group">
                    <label htmlFor="enlace-descripcion">
                      Descripción *
                    </label>
                    <input
                      type="text"
                      id="enlace-descripcion"
                      className="form-input"
                      value={formEnlace.descripcion}
                      onChange={(e) => setFormEnlace({ ...formEnlace, descripcion: e.target.value })}
                      placeholder="Ej: Reto #01: El Universo"
                      required
                    />
                  </div>

                  <div className="form-group">
                    <label htmlFor="enlace-url">
                      Enlace *
                    </label>
                    <input
                      type="url"
                      id="enlace-url"
                      className="form-input"
                      value={formEnlace.enlace}
                      onChange={(e) => setFormEnlace({ ...formEnlace, enlace: e.target.value })}
                      placeholder="https://ejemplo.com/reto"
                      required
                    />
                  </div>

                  <div className="form-group">
                    <label htmlFor="enlace-ciclo">
                      Bimestre *
                    </label>
                    <select
                      id="enlace-ciclo"
                      className="form-input"
                      value={formEnlace.ciclo}
                      onChange={(e) => setFormEnlace({ ...formEnlace, ciclo: parseInt(e.target.value) })}
                      required
                    >
                      {Array.from({ length: totalNotas }, (_, i) => i + 1).map((bim) => (
                        <option key={bim} value={bim}>
                          Bimestre {bim}
                        </option>
                      ))}
                    </select>
                  </div>

                  <div className="form-actions">
                    <button
                      type="button"
                      className="btn-cancelar"
                      onClick={() => {
                        setMostrarFormEnlace(false);
                        setEnlaceEditando(null);
                        setFormEnlace({ descripcion: '', enlace: '', ciclo: bimestreGlobal });
                      }}
                      disabled={guardandoEnlace}
                    >
                      Cancelar
                    </button>
                    <button
                      type="submit"
                      className="btn-guardar"
                      disabled={guardandoEnlace}
                    >
                      {guardandoEnlace ? '⏳ Guardando...' : '💾 Guardar Datos'}
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>,
          document.body
        )}

        {/* Tabla de enlaces */}
        {enlacesCicloActual.length > 0 ? (
          <table className="tabla-aula-virtual">
          <thead>
            <tr>
                <th>NOMBRE</th>
                <th>FECHA</th>
                <th className="text-center">Opciones</th>
            </tr>
          </thead>
          <tbody>
              {enlacesCicloActual.map((enlace) => (
              <tr key={enlace.id}>
                <td>{enlace.descripcion}</td>
                  <td>{new Date(enlace.fecha_hora).toLocaleString('es-PE', { 
                    day: '2-digit', 
                    month: '2-digit', 
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                  })}</td>
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
                            <a href="#" onClick={(e) => { e.preventDefault(); handleEditarEnlace(enlace); }}>
                            ✏️ Editar Enlace
                          </a>
                            <a href="#" onClick={(e) => { e.preventDefault(); handleEliminarEnlace(enlace); }}>
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
  };

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
                <p className="card-count">{archivos.filter(a => a.ciclo === bimestreGlobal).length}</p>
                <p className="card-subtitle">Temas disponibles</p>
              </div>
              <button 
                className="card-action-btn"
                onClick={(e) => { 
                  e.stopPropagation(); 
                  setTemaEditando(null);
                  setFormTema({ nombre: '', archivo: null, archivoNombre: '', enlace: '', ciclo: bimestreGlobal });
                  setMostrarFormTema(true); 
                }}
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
                onClick={(e) => { 
                  e.stopPropagation(); 
                  setFormExamen({
                    titulo: '',
                    tipo: 'VIRTUAL',
                    tipo_puntaje: 'GENERAL',
                    puntos_correcta: 1.0,
                    penalizar_incorrecta: 'NO',
                    penalizacion_incorrecta: 0.0,
                    tiempo: 60,
                    intentos: 1,
                    orden_preguntas: 'PREDETERMINADO',
                    preguntas_max: 1,
                    ciclo: bimestreGlobal,
                    estado: 'INACTIVO',
                    habilitar_fecha_hora: false,
                    fecha_desde: '',
                    fecha_hasta: '',
                    hora_desde: '08:00',
                    hora_hasta: '20:00',
                    archivo_pdf: null
                  });
                  setExamenEditando(null);
                  setMostrarFormExamen(true); 
                }}
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
                onClick={(e) => { 
                  e.stopPropagation(); 
                  if (expandedCard !== 'videos') {
                    toggleCard('videos');
                  }
                  setVideoEditando(null);
                  setFormVideo({ descripcion: '', enlace: '', ciclo: bimestreGlobal });
                  setMostrarFormVideo(true);
                }}
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
                onClick={(e) => { 
                  e.stopPropagation(); 
                  if (expandedCard !== 'enlaces') {
                    toggleCard('enlaces');
                  }
                  setEnlaceEditando(null);
                  setFormEnlace({ descripcion: '', enlace: '', ciclo: bimestreGlobal });
                  setMostrarFormEnlace(true);
                }}
              >
                + Nuevo
              </button>
            </div>
            {expandedCard === 'enlaces' && renderCardContent('enlaces')}
          </div>
        </div>

        {/* Modal de Formulario de Tema */}
        {mostrarFormTema && createPortal(
          <div 
            className="modal-tema-overlay"
            onClick={() => {
              setMostrarFormTema(false);
              setTemaEditando(null);
              setFormTema({ nombre: '', archivo: null, archivoNombre: '', enlace: '', ciclo: bimestreGlobal });
            }}
          >
            <div 
              className="modal-tema-container"
              onClick={(e) => e.stopPropagation()}
              role="dialog"
              aria-modal="true"
              aria-labelledby="modal-tema-title"
            >
              <div className="modal-tema-header">
                <h2 id="modal-tema-title">
                  {temaEditando ? '✏️ Editar Tema Interactivo' : '📚 Nuevo Tema Interactivo'}
                </h2>
                <button
                  className="modal-tema-close"
                  onClick={() => {
                    setMostrarFormTema(false);
                    setTemaEditando(null);
                    setFormTema({ nombre: '', archivo: null, archivoNombre: '', enlace: '', ciclo: bimestreGlobal });
                  }}
                  aria-label="Cerrar"
                >
                  ×
                </button>
              </div>

              <div className="modal-tema-body">
                <form onSubmit={handleGuardarTema}>
                  {/* Campo Nombre */}
                  <div className="form-group">
                    <label htmlFor="tema-nombre">
                      Nombre del Tema *
                    </label>
                    <input
                      type="text"
                      id="tema-nombre"
                      className="form-input"
                      value={formTema.nombre}
                      onChange={(e) => setFormTema({ ...formTema, nombre: e.target.value })}
                      placeholder="Ej: TEMA 1: MÉTODO CIENTÍFICO"
                      required
                    />
                  </div>

                  {/* Campo Bimestre */}
                  <div className="form-group">
                    <label htmlFor="tema-ciclo">
                      Bimestre *
                    </label>
                    <select
                      id="tema-ciclo"
                      className="form-input"
                      value={formTema.ciclo}
                      onChange={(e) => setFormTema({ ...formTema, ciclo: parseInt(e.target.value) })}
                      required
                    >
                      {Array.from({ length: totalNotas }, (_, i) => i + 1).map((bim) => (
                        <option key={bim} value={bim}>
                          Bimestre {bim}
                        </option>
                      ))}
                    </select>
                  </div>

                  {/* Campo Archivo */}
                  <div className="form-group">
                    <label htmlFor="tema-archivo">
                      Archivo PDF (Opcional)
                    </label>
                    <div className="file-input-wrapper">
                      <input
                        type="file"
                        id="tema-archivo"
                        className="form-input-file"
                        accept=".pdf"
                        onChange={(e) => {
                          const file = e.target.files[0];
                          if (file) {
                            setFormTema({
                              ...formTema,
                              archivo: file,
                              archivoNombre: file.name
                            });
                          }
                        }}
                      />
                      <label htmlFor="tema-archivo" className="file-input-label">
                        {formTema.archivoNombre || (temaEditando && temaEditando.archivo ? 'Archivo existente' : 'Seleccionar archivo')}
                      </label>
                      {formTema.archivoNombre && (
                        <button
                          type="button"
                          className="file-clear-btn"
                          onClick={() => setFormTema({ ...formTema, archivo: null, archivoNombre: '' })}
                        >
                          ×
                        </button>
                      )}
                    </div>
                    <small className="form-help-text">
                      Puedes subir un archivo PDF o proporcionar una URL, o ambos
                    </small>
                  </div>

                  {/* Campo URL */}
                  <div className="form-group">
                    <label htmlFor="tema-enlace">
                      URL (Opcional)
                    </label>
                    <input
                      type="url"
                      id="tema-enlace"
                      className="form-input"
                      value={formTema.enlace}
                      onChange={(e) => setFormTema({ ...formTema, enlace: e.target.value })}
                      placeholder="https://ejemplo.com/tema"
                    />
                    <small className="form-help-text">
                      Enlace externo al tema
                    </small>
                  </div>

                  {/* Botones de Acción */}
                  <div className="form-actions">
                    <button
                      type="button"
                      className="btn-cancelar-tema"
                      onClick={() => {
                        setMostrarFormTema(false);
                        setTemaEditando(null);
                        setFormTema({ nombre: '', archivo: null, archivoNombre: '', enlace: '', ciclo: bimestreGlobal });
                      }}
                      disabled={guardandoTema}
                    >
                      Cancelar
                    </button>
                    <button
                      type="submit"
                      className="btn-guardar-tema"
                      disabled={guardandoTema}
                    >
                      {guardandoTema ? '⏳ Guardando...' : (temaEditando ? '💾 Actualizar' : '💾 Guardar Datos')}
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>,
          document.body
        )}
      {/* Modal de Marcar Entregas */}
      {mostrarModalEntregas && createPortal(
        <div 
          className="modal-tema-overlay"
          onClick={() => {
            setMostrarModalEntregas(false);
            setTareaParaEntregas(null);
            setAlumnosEntregas([]);
            setNotasTemporales({});
          }}
        >
          <div 
            className="modal-tema-container"
            onClick={(e) => e.stopPropagation()}
            role="dialog"
            aria-modal="true"
            aria-labelledby="modal-entregas-title"
            style={{ maxWidth: '90%', width: '1200px', maxHeight: '90vh' }}
          >
            <div className="modal-tema-header">
              <h2 id="modal-entregas-title">
                📋 Registrar Entregas
                {infoTareaEntregas && (
                  <span style={{ fontSize: '0.9rem', fontWeight: 'normal', marginLeft: '10px', color: '#ffffff' }}>
                    - {infoTareaEntregas.curso_nombre} - {infoTareaEntregas.grado}° {infoTareaEntregas.seccion}
                  </span>
                )}
              </h2>
              <button
                className="modal-tema-close"
                onClick={() => {
                  setMostrarModalEntregas(false);
                  setTareaParaEntregas(null);
                  setAlumnosEntregas([]);
                  setNotasTemporales({});
                }}
                aria-label="Cerrar"
              >
                ×
              </button>
            </div>

            <div className="modal-tema-body" style={{ padding: '1.5rem', overflowY: 'auto', maxHeight: 'calc(90vh - 120px)' }}>
              {loadingEntregas ? (
                <div style={{ textAlign: 'center', padding: '2rem' }}>
                  <div className="loading-spinner-small"></div>
                  <p>Cargando entregas...</p>
                </div>
              ) : (
                <>
                  {/* Botón Guardar arriba */}
                  <div style={{ marginBottom: '1rem', textAlign: 'right' }}>
                    <button
                      type="button"
                      className="btn-guardar"
                      onClick={handleGuardarTodasLasNotas}
                      style={{ padding: '0.5rem 1.5rem' }}
                    >
                      💾 Guardar Todas las Notas
                    </button>
                  </div>

                  {/* Tabla de alumnos */}
                  <div style={{ overflowX: 'auto' }}>
                    <table style={{ width: '100%', borderCollapse: 'collapse', backgroundColor: '#fff' }}>
                      <thead>
                        <tr style={{ backgroundColor: '#e8f5e9', borderBottom: '2px solid #4caf50' }}>
                          <th style={{ padding: '12px', textAlign: 'center', fontWeight: 'bold', color: '#333', borderRight: '1px solid #ddd' }}>N°</th>
                          <th style={{ padding: '12px', textAlign: 'left', fontWeight: 'bold', color: '#333', borderRight: '1px solid #ddd' }}>APELLIDOS Y NOMBRES</th>
                          <th style={{ padding: '12px', textAlign: 'center', fontWeight: 'bold', color: '#333', borderRight: '1px solid #ddd', minWidth: '120px' }}>NOTA</th>
                          <th style={{ padding: '12px', textAlign: 'center', fontWeight: 'bold', color: '#333', borderRight: '1px solid #ddd', minWidth: '80px' }}>VISTO</th>
                          <th style={{ padding: '12px', textAlign: 'center', fontWeight: 'bold', color: '#333', minWidth: '200px' }}>ARCHIVO(S)</th>
                        </tr>
                      </thead>
                      <tbody>
                        {alumnosEntregas.map((alumno) => (
                          <tr key={alumno.matricula_id} style={{ borderBottom: '1px solid #eee' }}>
                            <td style={{ padding: '12px', textAlign: 'center', borderRight: '1px solid #ddd' }}>
                              {alumno.numero}
                            </td>
                            <td style={{ padding: '12px', textAlign: 'left', borderRight: '1px solid #ddd', fontWeight: '500' }}>
                              {alumno.nombre_completo}
                            </td>
                            <td style={{ padding: '8px', textAlign: 'center', borderRight: '1px solid #ddd' }}>
                              <input
                                type="text"
                                value={notasTemporales[alumno.matricula_id] || ''}
                                onChange={(e) => {
                                  setNotasTemporales({
                                    ...notasTemporales,
                                    [alumno.matricula_id]: e.target.value
                                  });
                                }}
                                placeholder="0-20"
                                style={{
                                  width: '100px',
                                  padding: '8px',
                                  border: '1px solid #ddd',
                                  borderRadius: '4px',
                                  textAlign: 'center',
                                  fontSize: '0.9rem'
                                }}
                              />
                            </td>
                            <td style={{ padding: '12px', textAlign: 'center', borderRight: '1px solid #ddd' }}>
                              <span style={{
                                padding: '4px 12px',
                                borderRadius: '12px',
                                fontSize: '0.85rem',
                                fontWeight: 'bold',
                                backgroundColor: alumno.visto === 'SI' ? '#e8f5e9' : '#ffebee',
                                color: alumno.visto === 'SI' ? '#2e7d32' : '#c62828'
                              }}>
                                {alumno.visto}
                              </span>
                            </td>
                            <td style={{ padding: '12px', textAlign: 'center' }}>
                              {alumno.archivos && alumno.archivos.length > 0 ? (
                                <div style={{ display: 'flex', flexDirection: 'column', gap: '4px', alignItems: 'center' }}>
                                  {alumno.archivos.map((archivo, idx) => (
                                    <a
                                      key={idx}
                                      href={archivo.url}
                                      target="_blank"
                                      rel="noopener noreferrer"
                                      style={{
                                        display: 'inline-block',
                                        padding: '4px 12px',
                                        backgroundColor: '#2196f3',
                                        color: 'white',
                                        borderRadius: '4px',
                                        textDecoration: 'none',
                                        fontSize: '0.85rem',
                                        margin: '2px'
                                      }}
                                    >
                                      📎 {archivo.nombre || 'Archivo'}
                                    </a>
                                  ))}
                                </div>
                              ) : (
                                <span style={{ color: '#999', fontStyle: 'italic' }}>NINGUNO</span>
                              )}
                            </td>
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  </div>

                  {/* Botón Guardar abajo */}
                  <div style={{ marginTop: '1.5rem', textAlign: 'right' }}>
                    <button
                      type="button"
                      className="btn-guardar"
                      onClick={handleGuardarTodasLasNotas}
                      style={{ padding: '0.5rem 1.5rem' }}
                    >
                      💾 Guardar Todas las Notas
                    </button>
                  </div>
                </>
              )}
            </div>
          </div>
        </div>,
        document.body
      )}

      {/* Modal de Asignar a Registro */}
      {mostrarModalAsignarRegistro && createPortal(
        <div 
          className="modal-tema-overlay"
          onClick={() => {
            setMostrarModalAsignarRegistro(false);
            setTareaParaAsignar(null);
            setDatosAsignarRegistro(null);
            setCriterioSeleccionado('');
            setCuadroSeleccionado('0');
          }}
        >
          <div 
            className="modal-tema-container"
            onClick={(e) => e.stopPropagation()}
            role="dialog"
            aria-modal="true"
            aria-labelledby="modal-asignar-registro-title"
            style={{ maxWidth: '600px', width: '90%' }}
          >
            <div className="modal-tema-header">
              <h2 id="modal-asignar-registro-title">
                📋 ASIGNAR A REGISTRO
              </h2>
              <button
                className="modal-tema-close"
                onClick={() => {
                  setMostrarModalAsignarRegistro(false);
                  setTareaParaAsignar(null);
                  setDatosAsignarRegistro(null);
                  setCriterioSeleccionado('');
                  setCuadroSeleccionado('0');
                }}
                aria-label="Cerrar"
              >
                ×
              </button>
            </div>

            <div className="modal-tema-body" style={{ padding: '1.5rem' }}>
              {loadingAsignarRegistro ? (
                <div style={{ textAlign: 'center', padding: '2rem' }}>
                  <div className="loading-spinner-small"></div>
                  <p>Cargando datos...</p>
                </div>
              ) : datosAsignarRegistro ? (
                <>
                  {/* Campos read-only */}
                  <div style={{ marginBottom: '1rem' }}>
                    <table style={{ width: '100%', borderCollapse: 'collapse', backgroundColor: '#fff' }}>
                      <tbody>
                        <tr style={{ borderBottom: '1px solid #eee' }}>
                          <td style={{ padding: '12px', backgroundColor: '#e8f5e9', fontWeight: 'bold', width: '40%', borderRight: '1px solid #ddd' }}>
                            TAREA
                          </td>
                          <td style={{ padding: '12px', backgroundColor: '#fff' }}>
                            {datosAsignarRegistro.tarea.titulo}
                          </td>
                        </tr>
                        <tr style={{ borderBottom: '1px solid #eee' }}>
                          <td style={{ padding: '12px', backgroundColor: '#e8f5e9', fontWeight: 'bold', borderRight: '1px solid #ddd' }}>
                            ASIGNATURA
                          </td>
                          <td style={{ padding: '12px', backgroundColor: '#fff' }}>
                            {datosAsignarRegistro.tarea.curso_nombre}
                          </td>
                        </tr>
                        <tr style={{ borderBottom: '1px solid #eee' }}>
                          <td style={{ padding: '12px', backgroundColor: '#e8f5e9', fontWeight: 'bold', borderRight: '1px solid #ddd' }}>
                            BIMESTRE
                          </td>
                          <td style={{ padding: '12px', backgroundColor: '#fff' }}>
                            {datosAsignarRegistro.tarea.ciclo}
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>

                  {/* Campo CRITERIO */}
                  <div className="form-group" style={{ marginBottom: '1rem' }}>
                    <label htmlFor="criterio-select" style={{ display: 'block', marginBottom: '8px', fontWeight: 'bold' }}>
                      CRITERIO
                    </label>
                    <select
                      id="criterio-select"
                      className="form-input"
                      value={criterioSeleccionado}
                      onChange={(e) => {
                        setCriterioSeleccionado(e.target.value);
                        // Resetear cuadro cuando cambia el criterio
                        if (e.target.value) {
                          const [criterioId, indicadorId] = e.target.value.split('_');
                          const criterio = datosAsignarRegistro.criterios.find(c => c.id === parseInt(criterioId));
                          if (criterio) {
                            const indicador = criterio.indicadores.find(i => i.id === parseInt(indicadorId));
                            if (indicador && indicador.cuadros > 0) {
                              setCuadroSeleccionado('0');
                            }
                          }
                        }
                      }}
                      style={{ width: '100%', padding: '10px', border: '1px solid #ddd', borderRadius: '4px' }}
                    >
                      <option value="">Seleccionar criterio...</option>
                      {datosAsignarRegistro.criterios.map(criterio => 
                        criterio.indicadores && criterio.indicadores.length > 0 ? (
                          criterio.indicadores.map(indicador => (
                            <option key={`${criterio.id}_${indicador.id}`} value={`${criterio.id}_${indicador.id}`}>
                              {criterio.descripcion} - {indicador.descripcion}
                            </option>
                          ))
                        ) : null
                      )}
                    </select>
                  </div>

                  {/* Campo CUADRO */}
                  {criterioSeleccionado && (() => {
                    const [criterioId, indicadorId] = criterioSeleccionado.split('_');
                    const criterio = datosAsignarRegistro.criterios.find(c => c.id === parseInt(criterioId));
                    if (criterio) {
                      const indicador = criterio.indicadores.find(i => i.id === parseInt(indicadorId));
                      if (indicador && indicador.cuadros > 0) {
                        return (
                          <div className="form-group" style={{ marginBottom: '1.5rem' }}>
                            <label htmlFor="cuadro-select" style={{ display: 'block', marginBottom: '8px', fontWeight: 'bold' }}>
                              CUADRO
                            </label>
                            <select
                              id="cuadro-select"
                              className="form-input"
                              value={cuadroSeleccionado}
                              onChange={(e) => setCuadroSeleccionado(e.target.value)}
                              style={{ width: '100px', padding: '10px', border: '1px solid #ddd', borderRadius: '4px' }}
                            >
                              {Array.from({ length: indicador.cuadros }, (_, i) => (
                                <option key={i} value={i}>{i + 1}</option>
                              ))}
                            </select>
                          </div>
                        );
                      }
                    }
                    return null;
                  })()}

                  {/* Warning */}
                  <div style={{
                    backgroundColor: '#fff3cd',
                    border: '1px solid #ffc107',
                    borderRadius: '4px',
                    padding: '12px',
                    marginBottom: '1.5rem',
                    textAlign: 'center',
                    color: '#856404',
                    fontWeight: '500'
                  }}>
                    Se reemplazarán las notas en el registro del cuadro seleccionado
                  </div>

                  {/* Botones */}
                  <div className="form-actions" style={{ display: 'flex', justifyContent: 'flex-end', gap: '10px' }}>
                    <button
                      type="button"
                      className="btn-cancelar"
                      onClick={() => {
                        setMostrarModalAsignarRegistro(false);
                        setTareaParaAsignar(null);
                        setDatosAsignarRegistro(null);
                        setCriterioSeleccionado('');
                        setCuadroSeleccionado('0');
                      }}
                      disabled={guardandoAsignarRegistro}
                    >
                      Cancelar
                    </button>
                    <button
                      type="button"
                      className="btn-guardar"
                      onClick={handleGuardarAsignarRegistro}
                      disabled={guardandoAsignarRegistro || !criterioSeleccionado}
                    >
                      {guardandoAsignarRegistro ? '⏳ Guardando...' : '💾 Guardar Datos'}
                    </button>
                  </div>
                </>
              ) : null}
            </div>
          </div>
        </div>,
        document.body
      )}

      {/* Modal de Examen - Fuera del renderExamenesContent para que funcione siempre */}
      {mostrarFormExamen && createPortal(
        <ExamenForm
          asignaturaId={asignaturaId}
          formExamen={formExamen}
          setFormExamen={setFormExamen}
          totalNotas={totalNotas}
          guardandoExamen={guardandoExamen}
          setGuardandoExamen={setGuardandoExamen}
          examenEditando={examenEditando}
          onClose={() => {
            setMostrarFormExamen(false);
            setExamenEditando(null);
            setFormExamen({
              titulo: '',
              tipo: 'VIRTUAL',
              tipo_puntaje: 'GENERAL',
              puntos_correcta: 1.0,
              penalizar_incorrecta: 'NO',
              penalizacion_incorrecta: 0.0,
              tiempo: 60,
              intentos: 1,
              orden_preguntas: 'PREDETERMINADO',
              preguntas_max: 1,
              ciclo: bimestreGlobal,
              estado: 'INACTIVO',
              habilitar_fecha_hora: false,
              fecha_desde: '',
              fecha_hasta: '',
              hora_desde: '08:00',
              hora_hasta: '20:00',
              archivo_pdf: null
            });
          }}
          onSuccess={() => {
            const cicloCreado = formExamen.ciclo || cicloExamenes || bimestreGlobal;
            setMostrarFormExamen(false);
            setExamenEditando(null);
            // Sincronizar cicloExamenes con el ciclo del examen creado
            if (cicloCreado !== cicloExamenes) {
              setCicloExamenes(cicloCreado);
            }
            // Recargar usando el ciclo del examen que se acaba de crear
            cargarExamenes(cicloCreado);
          }}
        />,
        document.body
      )}

      </div>
    </DashboardLayout>
  );
}

// Componente para el formulario de examen
function ExamenForm({ asignaturaId, formExamen, setFormExamen, onClose, onSuccess, totalNotas, guardandoExamen, setGuardandoExamen, examenEditando }) {
  const handleCrearExamen = async (e) => {
    e.preventDefault();
    
    if (!formExamen.titulo || !formExamen.tipo || !formExamen.ciclo || !formExamen.estado) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Título, Tipo, Bimestre y Estado son requeridos',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
      });
      return;
    }

    // Si es PDF, debe tener archivo (solo en creación, en edición puede mantener el existente)
    if (formExamen.tipo === 'PDF' && !examenEditando && !formExamen.archivo_pdf) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Debe subir un archivo PDF para exámenes tipo PDF',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
      });
      return;
    }

    // Si es VIRTUAL, validar campos requeridos
    if (formExamen.tipo === 'VIRTUAL') {
      if (!formExamen.tipo_puntaje || !formExamen.tiempo) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Tipo de Puntaje y Tiempo son requeridos para exámenes virtuales',
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000
        });
        return;
      }
      // Si es GENERAL, también requiere puntos_correcta
      if (formExamen.tipo_puntaje === 'GENERAL' && !formExamen.puntos_correcta) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Puntos por respuesta correcta es requerido cuando la calificación es GENERAL',
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000
        });
        return;
      }
    }

    setGuardandoExamen(true);
    try {
      const formData = new FormData();
      formData.append('asignatura_id', asignaturaId);
      formData.append('titulo', formExamen.titulo);
      formData.append('tipo', formExamen.tipo);
      formData.append('ciclo', formExamen.ciclo);
      formData.append('estado', formExamen.estado);
      formData.append('habilitar_fecha_hora', formExamen.habilitar_fecha_hora ? 'SI' : 'NO');

      if (formExamen.tipo === 'VIRTUAL') {
        formData.append('tipo_puntaje', formExamen.tipo_puntaje);
        // Solo enviar puntos_correcta si es GENERAL
        if (formExamen.tipo_puntaje === 'GENERAL') {
          formData.append('puntos_correcta', formExamen.puntos_correcta);
        } else {
          // Para INDIVIDUAL, enviar un valor por defecto (se asignará en cada pregunta)
          formData.append('puntos_correcta', 0);
        }
        formData.append('penalizar_incorrecta', formExamen.penalizar_incorrecta);
        formData.append('penalizacion_incorrecta', formExamen.penalizacion_incorrecta || 0);
        formData.append('tiempo', formExamen.tiempo);
        formData.append('intentos', formExamen.intentos);
        formData.append('orden_preguntas', formExamen.orden_preguntas);
        formData.append('preguntas_max', formExamen.preguntas_max);
      }

      if (formExamen.habilitar_fecha_hora) {
        if (formExamen.fecha_desde) formData.append('fecha_desde', formExamen.fecha_desde);
        if (formExamen.fecha_hasta) formData.append('fecha_hasta', formExamen.fecha_hasta);
        if (formExamen.hora_desde) formData.append('hora_desde', formExamen.hora_desde);
        if (formExamen.hora_hasta) formData.append('hora_hasta', formExamen.hora_hasta);
      }

      if (formExamen.archivo_pdf) {
        formData.append('archivo_pdf', formExamen.archivo_pdf);
      }

      // Si está editando, usar PUT, si no, usar POST
      if (examenEditando) {
        await api.put(`/docente/aula-virtual/examenes/${examenEditando.id}`, formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });

        Swal.fire({
          icon: 'success',
          title: '¡Examen actualizado!',
          text: 'El examen se ha actualizado correctamente',
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true
        });
      } else {
        await api.post('/docente/aula-virtual/examenes', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });

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
      }

      onSuccess();
    } catch (error) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: error.response?.data?.error || (examenEditando ? 'No se pudo actualizar el examen' : 'No se pudo crear el examen'),
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
      });
    } finally {
      setGuardandoExamen(false);
    }
  };

  return (
    <div className="modal-tema-overlay" onClick={onClose}>
      <div className="modal-tema-container" onClick={(e) => e.stopPropagation()} role="dialog" aria-modal="true">
        <div className="modal-tema-header">
          <h2>{examenEditando ? '✏️ Editar Examen' : '📝 Registrar Examen'}</h2>
          <button className="modal-tema-close" onClick={onClose} aria-label="Cerrar">×</button>
        </div>

        <div className="modal-tema-body">
          <form onSubmit={handleCrearExamen}>
            <div className="form-group">
              <label htmlFor="examen-titulo">Título *</label>
              <input
                type="text"
                id="examen-titulo"
                className="form-input"
                value={formExamen.titulo}
                onChange={(e) => setFormExamen({ ...formExamen, titulo: e.target.value })}
                placeholder="Ej: Práctica N° 1: El Clima"
                required
              />
            </div>

            <div className="form-group">
              <label htmlFor="examen-tipo">Tipo *</label>
              <select
                id="examen-tipo"
                className="form-input"
                value={formExamen.tipo}
                onChange={(e) => setFormExamen({ ...formExamen, tipo: e.target.value })}
                required
              >
                <option value="VIRTUAL">VIRTUAL</option>
                <option value="PDF">PDF</option>
              </select>
            </div>

            {/* Campos solo para VIRTUAL */}
            {formExamen.tipo === 'VIRTUAL' && (
              <>
                <div className="form-group">
                  <label htmlFor="examen-calificacion">Calificación</label>
                  <select
                    id="examen-calificacion"
                    className="form-input"
                    value={formExamen.tipo_puntaje}
                    onChange={(e) => setFormExamen({ ...formExamen, tipo_puntaje: e.target.value })}
                  >
                    <option value="INDIVIDUAL">INDIVIDUAL</option>
                    <option value="GENERAL">GENERAL</option>
                  </select>
                </div>

                {/* Solo mostrar "Puntos por respuesta correcta" si es GENERAL */}
                {formExamen.tipo_puntaje === 'GENERAL' && (
                  <div className="form-group">
                    <label htmlFor="examen-puntos-correcta">Puntos por respuesta correcta *</label>
                    <input
                      type="number"
                      id="examen-puntos-correcta"
                      className="form-input"
                      step="0.1"
                      min="0"
                      value={formExamen.puntos_correcta}
                      onChange={(e) => setFormExamen({ ...formExamen, puntos_correcta: parseFloat(e.target.value) || 1.0 })}
                      required
                    />
                  </div>
                )}

                {/* Solo mostrar "Penalizar Incorrecta" si es GENERAL */}
                {formExamen.tipo_puntaje === 'GENERAL' && (
                  <>
                    <div className="form-group">
                      <label htmlFor="examen-penalizar">Penalizar Incorrecta</label>
                      <select
                        id="examen-penalizar"
                        className="form-input"
                        value={formExamen.penalizar_incorrecta}
                        onChange={(e) => setFormExamen({ ...formExamen, penalizar_incorrecta: e.target.value })}
                      >
                        <option value="NO">NO</option>
                        <option value="SI">SI</option>
                      </select>
                    </div>

                    {formExamen.penalizar_incorrecta === 'SI' && (
                      <div className="form-group">
                        <label htmlFor="examen-penalizacion">Penalización por Incorrecta</label>
                        <input
                          type="number"
                          id="examen-penalizacion"
                          className="form-input"
                          step="0.1"
                          min="0"
                          value={formExamen.penalizacion_incorrecta}
                          onChange={(e) => setFormExamen({ ...formExamen, penalizacion_incorrecta: parseFloat(e.target.value) || 0 })}
                        />
                      </div>
                    )}
                  </>
                )}

                <div className="form-group">
                  <label htmlFor="examen-tiempo">Tiempo *</label>
                  <input
                    type="number"
                    id="examen-tiempo"
                    className="form-input"
                    min="1"
                    value={formExamen.tiempo}
                    onChange={(e) => setFormExamen({ ...formExamen, tiempo: parseInt(e.target.value) || 60 })}
                    required
                  />
                </div>

                <div className="form-group">
                  <label htmlFor="examen-intentos">Intentos</label>
                  <input
                    type="number"
                    id="examen-intentos"
                    className="form-input"
                    min="1"
                    value={formExamen.intentos}
                    onChange={(e) => setFormExamen({ ...formExamen, intentos: parseInt(e.target.value) || 1 })}
                  />
                </div>

                <div className="form-group">
                  <label htmlFor="examen-orden">Orden de Preguntas</label>
                  <select
                    id="examen-orden"
                    className="form-input"
                    value={formExamen.orden_preguntas}
                    onChange={(e) => setFormExamen({ ...formExamen, orden_preguntas: e.target.value })}
                  >
                    <option value="PREDETERMINADO">PREDETERMINADO</option>
                    <option value="ALEATORIO">ALEATORIO</option>
                  </select>
                </div>

                <div className="form-group">
                  <label htmlFor="examen-preguntas-max">Preguntas Max. *</label>
                  <input
                    type="number"
                    id="examen-preguntas-max"
                    className="form-input"
                    min="1"
                    value={formExamen.preguntas_max}
                    onChange={(e) => setFormExamen({ ...formExamen, preguntas_max: parseInt(e.target.value) || 1 })}
                    required
                  />
                </div>
              </>
            )}

            {/* Campo Archivo PDF solo para PDF */}
            {formExamen.tipo === 'PDF' && (
              <div className="form-group">
                <label htmlFor="examen-archivo-pdf">
                  Archivo PDF {examenEditando ? '(Opcional - dejar vacío para mantener el actual)' : '*'}
                </label>
                <input
                  type="file"
                  id="examen-archivo-pdf"
                  className="form-input"
                  accept=".pdf"
                  onChange={(e) => setFormExamen({ ...formExamen, archivo_pdf: e.target.files[0] || null })}
                  required={formExamen.tipo === 'PDF' && !examenEditando}
                />
                {examenEditando && examenEditando.archivo_pdf && (
                  <small style={{ display: 'block', marginTop: '0.5rem', color: '#6b7280' }}>
                    Archivo actual: {examenEditando.archivo_pdf.split('/').pop()}
                  </small>
                )}
              </div>
            )}

            <div className="form-group">
              <label htmlFor="examen-bimestre">Bimestre *</label>
              <select
                id="examen-bimestre"
                className="form-input"
                value={formExamen.ciclo}
                onChange={(e) => setFormExamen({ ...formExamen, ciclo: parseInt(e.target.value) })}
                required
              >
                {Array.from({ length: totalNotas }, (_, i) => i + 1).map((bim) => (
                  <option key={bim} value={bim}>
                    Bimestre {bim}
                  </option>
                ))}
              </select>
            </div>

            {/* Toggle Habilitar fecha y hora */}
            <div className="form-group">
              <label className="checkbox-label">
                <input
                  type="checkbox"
                  checked={formExamen.habilitar_fecha_hora}
                  onChange={(e) => setFormExamen({ ...formExamen, habilitar_fecha_hora: e.target.checked })}
                />
                Habilitar fecha y hora
              </label>
            </div>

            {/* Campos de fecha y hora solo si está habilitado */}
            {formExamen.habilitar_fecha_hora && (
              <>
                <div className="form-group">
                  <label htmlFor="examen-fecha-desde">Desde</label>
                  <input
                    type="date"
                    id="examen-fecha-desde"
                    className="form-input"
                    value={formExamen.fecha_desde}
                    onChange={(e) => setFormExamen({ ...formExamen, fecha_desde: e.target.value })}
                  />
                </div>

                <div className="form-group">
                  <label htmlFor="examen-hora-desde">Hora Desde</label>
                  <input
                    type="time"
                    id="examen-hora-desde"
                    className="form-input"
                    value={formExamen.hora_desde}
                    onChange={(e) => setFormExamen({ ...formExamen, hora_desde: e.target.value })}
                  />
                </div>

                <div className="form-group">
                  <label htmlFor="examen-fecha-hasta">Hasta</label>
                  <input
                    type="date"
                    id="examen-fecha-hasta"
                    className="form-input"
                    value={formExamen.fecha_hasta}
                    onChange={(e) => setFormExamen({ ...formExamen, fecha_hasta: e.target.value })}
                  />
                </div>

                <div className="form-group">
                  <label htmlFor="examen-hora-hasta">Hora Hasta</label>
                  <input
                    type="time"
                    id="examen-hora-hasta"
                    className="form-input"
                    value={formExamen.hora_hasta}
                    onChange={(e) => setFormExamen({ ...formExamen, hora_hasta: e.target.value })}
                  />
                </div>
              </>
            )}

            <div className="form-group">
              <label htmlFor="examen-estado">Estado *</label>
              <select
                id="examen-estado"
                className="form-input"
                value={formExamen.estado}
                onChange={(e) => setFormExamen({ ...formExamen, estado: e.target.value })}
                required
              >
                <option value="ACTIVO">ACTIVO</option>
                <option value="INACTIVO">INACTIVO</option>
              </select>
            </div>

            <div className="form-actions">
              <button
                type="button"
                className="btn-cancelar"
                onClick={onClose}
                disabled={guardandoExamen}
              >
                Cancelar
              </button>
              <button
                type="submit"
                className="btn-guardar"
                disabled={guardandoExamen}
              >
                {guardandoExamen ? 'Guardando...' : 'Guardar Datos'}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
}

export default DocenteAulaVirtual;
