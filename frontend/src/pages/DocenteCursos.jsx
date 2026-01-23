import React, { useState, useEffect, useRef } from 'react';
import { createPortal } from 'react-dom';
import { useNavigate } from 'react-router-dom';
import DashboardLayout from '../components/DashboardLayout';
import api from '../services/api';
import Swal from 'sweetalert2';
import './DocenteCursos.css';

function DocenteCursos() {
  const navigate = useNavigate();
  const [loading, setLoading] = useState(true);
  const [cursos, setCursos] = useState([]);
  const [selectedCurso, setSelectedCurso] = useState(null);
  const [cursoInfo, setCursoInfo] = useState(null); // Información del curso seleccionado
  const [alumnos, setAlumnos] = useState([]);
  const [loadingAlumnos, setLoadingAlumnos] = useState(false);
  const [openDropdown, setOpenDropdown] = useState(null);
  const [openDropdownAlumno, setOpenDropdownAlumno] = useState(null); // { id, top, left } o null
  const [dropdownPosition, setDropdownPosition] = useState(null);
  const [mostrarModalEstrellas, setMostrarModalEstrellas] = useState(false);
  const [mostrarModalIncidencias, setMostrarModalIncidencias] = useState(false);
  const [alumnoSeleccionado, setAlumnoSeleccionado] = useState(null);
  const [historialEstrellas, setHistorialEstrellas] = useState([]);
  const [historialIncidencias, setHistorialIncidencias] = useState([]);
  const [totalEstrellas, setTotalEstrellas] = useState(0);
  const [totalIncidencias, setTotalIncidencias] = useState(0);
  const [loadingEstrellas, setLoadingEstrellas] = useState(false);
  const [loadingIncidencias, setLoadingIncidencias] = useState(false);
  const [formEstrellas, setFormEstrellas] = useState({ points: '', description: '' });
  const [formIncidencias, setFormIncidencias] = useState({ description: '' });
  const [guardandoEstrellas, setGuardandoEstrellas] = useState(false);
  const [guardandoIncidencias, setGuardandoIncidencias] = useState(false);
  const dropdownRef = useRef({});
  const buttonRef = useRef({});

  useEffect(() => {
    cargarCursos();
  }, []);

  useEffect(() => {
    const handleClickOutside = (event) => {
      // Solo cerrar si es click del botón izquierdo (button === 0)
      // Ignorar botón del medio (wheel, button === 1) y botón derecho (button === 2)
      if (event.button !== 0) return;
      
      if (openDropdown !== null) {
        const dropdownElement = document.querySelector('.dropdown-menu-portal');
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

  // Cerrar dropdowns de alumnos al hacer clic fuera
  useEffect(() => {
    if (!openDropdownAlumno) return;
    
    const handleClickOutside = (event) => {
      const target = event.target;
      
      // Verificar si el clic está dentro de cualquier dropdown
      const isDropdownMenu = target.closest('.dropdown-menu-alumno');
      const isDropdownButton = target.closest('.btn-opciones-dropdown-alumno');
      const isDropdownItem = target.closest('.dropdown-item');
      
      // Solo cerrar si el clic NO está dentro del dropdown
      if (!isDropdownMenu && !isDropdownButton && !isDropdownItem) {
        setTimeout(() => {
          setOpenDropdownAlumno(null);
        }, 100);
      }
    };
    
    const timeoutId = setTimeout(() => {
      document.addEventListener('click', handleClickOutside, false);
    }, 50);
    
    return () => {
      clearTimeout(timeoutId);
      document.removeEventListener('click', handleClickOutside, false);
    };
  }, [openDropdownAlumno]);

  const cargarCursos = async () => {
    try {
      setLoading(true);
      const response = await api.get('/docente/cursos');
      setCursos(response.data.cursos || []);
    } catch (error) {
      console.error('Error cargando cursos:', error);
    } finally {
      setLoading(false);
    }
  };

  const cargarAlumnos = async (cursoId) => {
    try {
      setLoadingAlumnos(true);
      const response = await api.get(`/docente/cursos/${cursoId}/alumnos`);
      setAlumnos(response.data.alumnos || []);
      setCursoInfo(response.data.curso);
      setSelectedCurso(cursoId);
      
      // Hacer scroll al inicio de la página cuando se carga la lista de alumnos
      setTimeout(() => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
        const docenteCursosContainer = document.querySelector('.docente-cursos');
        if (docenteCursosContainer) {
          docenteCursosContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      }, 100);
    } catch (error) {
      console.error('Error cargando alumnos:', error);
    } finally {
      setLoadingAlumnos(false);
    }
  };

  const handleVolver = () => {
    setSelectedCurso(null);
    setCursoInfo(null);
    setAlumnos([]);
    setOpenDropdownAlumno(null);
    setMostrarModalEstrellas(false);
    setMostrarModalIncidencias(false);
    setAlumnoSeleccionado(null);
  };

  const abrirModalEstrellas = async (alumno) => {
    setAlumnoSeleccionado(alumno);
    setMostrarModalEstrellas(true);
    setFormEstrellas({ points: '', description: '' });
    await cargarHistorialEstrellas(alumno.id);
  };

  const abrirModalIncidencias = async (alumno) => {
    setAlumnoSeleccionado(alumno);
    setMostrarModalIncidencias(true);
    setFormIncidencias({ description: '' });
    await cargarHistorialIncidencias(alumno.id);
  };

  const cargarHistorialEstrellas = async (alumnoId) => {
    if (!selectedCurso || !alumnoId) return;
    
    try {
      setLoadingEstrellas(true);
      const response = await api.get(`/docente/cursos/${selectedCurso}/alumnos/${alumnoId}/estrellas`);
      setHistorialEstrellas(response.data.estrellas || []);
      setTotalEstrellas(response.data.total_estrellas || 0);
    } catch (error) {
      console.error('Error cargando historial de estrellas:', error);
      Swal.fire({
        title: 'Error',
        text: 'No se pudo cargar el historial de estrellas',
        icon: 'error',
        zIndex: 100001
      });
    } finally {
      setLoadingEstrellas(false);
    }
  };

  const handleDarEstrellas = async (e) => {
    e.preventDefault();
    
    if (!formEstrellas.points || formEstrellas.points <= 0) {
      Swal.fire({
        title: 'Error',
        text: 'La cantidad de estrellas debe ser mayor a 0',
        icon: 'error',
        zIndex: 100001
      });
      return;
    }

    if (!formEstrellas.description || formEstrellas.description.trim() === '') {
      Swal.fire({
        title: 'Error',
        text: 'La descripción es requerida',
        icon: 'error',
        zIndex: 100001
      });
      return;
    }

    try {
      setGuardandoEstrellas(true);
      await api.post(
        `/docente/cursos/${selectedCurso}/alumnos/${alumnoSeleccionado.id}/estrellas`,
        {
          points: parseInt(formEstrellas.points),
          description: formEstrellas.description.trim()
        }
      );

      Swal.fire({
        title: '¡Éxito!',
        text: `${formEstrellas.points} estrella(s) asignada(s) correctamente`,
        icon: 'success',
        zIndex: 100001
      });
      setFormEstrellas({ points: '', description: '' });
      
      // Recargar historial y actualizar lista de alumnos
      await cargarHistorialEstrellas(alumnoSeleccionado.id);
      await cargarAlumnos(selectedCurso);
    } catch (error) {
      console.error('Error dando estrellas:', error);
      Swal.fire({
        title: 'Error',
        text: error.response?.data?.error || 'No se pudieron asignar las estrellas',
        icon: 'error',
        zIndex: 100001
      });
    } finally {
      setGuardandoEstrellas(false);
    }
  };

  const handleEliminarEstrellas = async (incidentId, points) => {
    const result = await Swal.fire({
      title: '¿Estás seguro?',
      text: `Se eliminarán ${points} estrella(s). Esta acción no se puede deshacer.`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar',
      zIndex: 100001 // Mayor que el modal de estrellas (100000)
    });

    if (result.isConfirmed) {
      try {
        await api.delete(
          `/docente/cursos/${selectedCurso}/alumnos/${alumnoSeleccionado.id}/estrellas/${incidentId}`
        );

        Swal.fire({
          title: '¡Eliminado!',
          text: 'Las estrellas han sido eliminadas correctamente',
          icon: 'success',
          zIndex: 100001
        });
        
        // Recargar historial y actualizar lista de alumnos
        await cargarHistorialEstrellas(alumnoSeleccionado.id);
        await cargarAlumnos(selectedCurso);
      } catch (error) {
        console.error('Error eliminando estrellas:', error);
        Swal.fire({
          title: 'Error',
          text: error.response?.data?.error || 'No se pudieron eliminar las estrellas',
          icon: 'error',
          zIndex: 100001
        });
      }
    }
  };

  const cargarHistorialIncidencias = async (alumnoId) => {
    if (!selectedCurso || !alumnoId) return;
    
    try {
      setLoadingIncidencias(true);
      const response = await api.get(`/docente/cursos/${selectedCurso}/alumnos/${alumnoId}/incidencias`);
      setHistorialIncidencias(response.data.incidencias || []);
      setTotalIncidencias(response.data.total_incidencias || 0);
    } catch (error) {
      console.error('Error cargando historial de incidencias:', error);
      Swal.fire({
        title: 'Error',
        text: 'No se pudo cargar el historial de incidencias',
        icon: 'error',
        zIndex: 100001
      });
    } finally {
      setLoadingIncidencias(false);
    }
  };

  const handleRegistrarIncidencia = async (e) => {
    e.preventDefault();
    
    if (!formIncidencias.description || formIncidencias.description.trim() === '') {
      Swal.fire({
        title: 'Error',
        text: 'La descripción es requerida',
        icon: 'error',
        zIndex: 100001
      });
      return;
    }

    try {
      setGuardandoIncidencias(true);
      await api.post(
        `/docente/cursos/${selectedCurso}/alumnos/${alumnoSeleccionado.id}/incidencias`,
        {
          description: formIncidencias.description.trim()
        }
      );

      Swal.fire({
        title: '¡Éxito!',
        text: 'Incidencia registrada correctamente',
        icon: 'success',
        zIndex: 100001
      });
      setFormIncidencias({ description: '' });
      
      // Recargar historial
      await cargarHistorialIncidencias(alumnoSeleccionado.id);
    } catch (error) {
      console.error('Error registrando incidencia:', error);
      Swal.fire({
        title: 'Error',
        text: error.response?.data?.error || 'No se pudo registrar la incidencia',
        icon: 'error',
        zIndex: 100001
      });
    } finally {
      setGuardandoIncidencias(false);
    }
  };

  const handleEliminarIncidencia = async (incidentId) => {
    const result = await Swal.fire({
      title: '¿Estás seguro?',
      text: 'Se eliminará esta incidencia. Esta acción no se puede deshacer.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar',
      zIndex: 100001
    });

    if (result.isConfirmed) {
      try {
        await api.delete(
          `/docente/cursos/${selectedCurso}/alumnos/${alumnoSeleccionado.id}/incidencias/${incidentId}`
        );

        Swal.fire({
          title: '¡Eliminado!',
          text: 'La incidencia ha sido eliminada correctamente',
          icon: 'success',
          zIndex: 100001
        });
        
        // Recargar historial
        await cargarHistorialIncidencias(alumnoSeleccionado.id);
      } catch (error) {
        console.error('Error eliminando incidencia:', error);
        Swal.fire({
          title: 'Error',
          text: error.response?.data?.error || 'No se pudo eliminar la incidencia',
          icon: 'error',
          zIndex: 100001
        });
      }
    }
  };

  const formatearFecha = (fecha) => {
    if (!fecha) return '';
    const date = new Date(fecha);
    const dia = String(date.getDate()).padStart(2, '0');
    const mes = String(date.getMonth() + 1).padStart(2, '0');
    const año = date.getFullYear();
    let horas = date.getHours();
    const minutos = String(date.getMinutes()).padStart(2, '0');
    const ampm = horas >= 12 ? 'PM' : 'AM';
    horas = horas % 12;
    horas = horas ? horas : 12;
    return `${dia}-${mes}-${año} ${String(horas).padStart(2, '0')}:${minutos} ${ampm}`;
  };

  const handleCursoAction = (curso, action) => {
    setOpenDropdown(null); // Cerrar dropdown al seleccionar una opción
    setDropdownPosition(null);
    switch (action) {
      case 'aula':
        navigate(`/docente/cursos/${curso.id}/aula`);
        break;
      case 'alumnos':
        cargarAlumnos(curso.id);
        break;
      case 'notas':
        navigate(`/docente/cursos/${curso.id}/notas`);
        break;
      case 'asistencia':
        navigate(`/docente/cursos/${curso.id}/asistencia`);
        break;
      case 'enlaces':
        navigate(`/docente/cursos/${curso.id}/enlaces`);
        break;
      case 'copiar':
        // TODO: Implementar funcionalidad de copiar contenido
        console.log('Copiar contenido del curso:', curso.id);
        break;
      default:
        break;
    }
  };

  const toggleDropdown = (cursoId, event) => {
    if (openDropdown === cursoId) {
      setOpenDropdown(null);
      setDropdownPosition(null);
    } else {
      const button = event.currentTarget;
      const rect = button.getBoundingClientRect();
      setDropdownPosition({
        top: rect.bottom + 8,
        left: rect.left,
        width: rect.width
      });
      setOpenDropdown(cursoId);
    }
  };

  if (loading) {
    return (
      <DashboardLayout>
        <div className="docente-cursos-loading">
          <div className="loading-spinner">Cargando cursos...</div>
        </div>
      </DashboardLayout>
    );
  }

  return (
    <DashboardLayout>
      <div className="docente-cursos">
        {!selectedCurso ? (
          <>
            <div className="page-header">
              <h1>Cursos Asignados</h1>
              <p>Gestiona tus cursos y asignaturas del año académico actual</p>
            </div>

            {cursos.length > 0 ? (
          <div className="cursos-grid">
            {cursos.map((curso) => (
              <div key={curso.id} className="curso-card mundo-card">
                <div className="curso-header">
                  <div className="curso-icon">
                    {curso.curso_imagen_url ? (
                      <img 
                        src={curso.curso_imagen_url} 
                        alt={curso.curso_nombre}
                        className="curso-imagen"
                        onError={(e) => {
                          // Si la imagen falla al cargar, mostrar el emoji
                          e.target.style.display = 'none';
                          if (e.target.nextSibling) {
                            e.target.nextSibling.style.display = 'flex';
                          }
                        }}
                      />
                    ) : null}
                    {!curso.curso_imagen_url && <span className="curso-emoji">📚</span>}
                    {curso.curso_imagen_url && (
                      <span className="curso-emoji" style={{ display: 'none' }}>📚</span>
                    )}
                  </div>
                  <h3 className="curso-nombre">{curso.curso_nombre}</h3>
                </div>
                
                <div className="curso-info-compact">
                  <span className="info-compact-text">
                    {curso.nivel_nombre} - {curso.grado}° {curso.seccion} - {curso.turno_nombre}
                  </span>
                </div>

                <div className="curso-actions">
                  <button
                    className="btn-action-primary"
                    onClick={() => handleCursoAction(curso, 'aula')}
                  >
                    🎓 Aula Virtual
                  </button>
                  <div 
                    className="dropdown-options"
                    ref={(el) => (dropdownRef.current[curso.id] = el)}
                  >
                    <button 
                      ref={(el) => (buttonRef.current[curso.id] = el)}
                      className="btn-options-toggle"
                      onClick={(e) => {
                        e.stopPropagation();
                        toggleDropdown(curso.id, e);
                      }}
                    >
                      Opciones {openDropdown === curso.id ? '▲' : '▼'}
                    </button>
                    {openDropdown === curso.id && dropdownPosition && createPortal(
                      <div 
                        className="dropdown-menu-portal"
                        style={{
                          position: 'fixed',
                          top: `${dropdownPosition.top}px`,
                          left: `${dropdownPosition.left}px`,
                          width: `${dropdownPosition.width}px`,
                          zIndex: 10000
                        }}
                        onMouseDown={(e) => e.stopPropagation()}
                        onClick={(e) => e.stopPropagation()}
                      >
                        <div className="dropdown-menu">
                          <button onClick={() => handleCursoAction(curso, 'alumnos')}>
                            👥 Lista de Alumnos
                          </button>
                          <button onClick={() => handleCursoAction(curso, 'notas')}>
                            📝 Registrar Notas
                          </button>
                          <button onClick={() => handleCursoAction(curso, 'asistencia')}>
                            ✅ Registrar Asistencia
                          </button>
                          <button onClick={() => handleCursoAction(curso, 'horario')}>
                            📅 Ver Horario
                          </button>
                          <button onClick={() => handleCursoAction(curso, 'enlaces')}>
                            🔗 Link Aula Virtual
                          </button>
                          <button onClick={() => handleCursoAction(curso, 'copiar')}>
                            📋 Copiar Contenido
                          </button>
                        </div>
                      </div>,
                      document.body
                    )}
                  </div>
                </div>
              </div>
            ))}
          </div>
            ) : (
              <div className="empty-state mundo-card">
                <p>No tienes cursos asignados para el año académico actual</p>
              </div>
            )}
          </>
        ) : (
          <div className="alumnos-container">
            {/* Header con botón Volver */}
            <div className="alumnos-header-section">
              <button
                className="btn-regresar"
                onClick={handleVolver}
                type="button"
              >
                ← Volver
              </button>
              <div className="alumnos-header-info">
                {cursoInfo && (
                  <>
                    <h2 className="alumnos-header-title">
                      {cursoInfo.curso_nombre}
                    </h2>
                    <p className="alumnos-header-subtitle">
                      {cursoInfo.grado}° {cursoInfo.seccion} - {cursoInfo.nivel_nombre} - {cursoInfo.turno_nombre}
                    </p>
                  </>
                )}
              </div>
            </div>

            {/* Lista de Alumnos */}
            <div className="alumnos-list-section">
              {loadingAlumnos ? (
                <div className="loading-spinner">Cargando alumnos...</div>
              ) : alumnos.length > 0 ? (
                <div className="alumnos-table-container">
                  <table className="alumnos-table">
                    <thead>
                      <tr>
                        <th>APELLIDOS Y NOMBRES</th>
                        <th>ESTRELLAS</th>
                        <th>INCIDENCIAS</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      {alumnos.map((alumno) => (
                        <tr key={alumno.id}>
                          <td>{alumno.nombre_completo}</td>
                          <td>
                            <span className="estrellas-badge">
                              ⭐ {alumno.total_estrellas || 0}
                            </span>
                          </td>
                          <td>
                            <span className="incidencias-badge">
                              📋 {alumno.total_incidencias || 0}
                            </span>
                          </td>
                          <td>
                            <div className="dropdown-container">
                              <button
                                className="btn-opciones-dropdown-alumno"
                                type="button"
                                onClick={(e) => {
                                  e.stopPropagation();
                                  e.preventDefault();
                                  
                                  const rect = e.currentTarget.getBoundingClientRect();
                                  if (openDropdownAlumno?.id === alumno.id) {
                                    setOpenDropdownAlumno(null);
                                  } else {
                                    const dropdownWidth = 200;
                                    let left = rect.left;
                                    if (left + dropdownWidth > window.innerWidth) {
                                      left = window.innerWidth - dropdownWidth - 10;
                                    }
                                    if (left < 10) {
                                      left = 10;
                                    }
                                    
                                    // Verificar si hay espacio abajo, si no, mostrar arriba
                                    const spaceBelow = window.innerHeight - rect.bottom;
                                    const spaceAbove = rect.top;
                                    const dropdownHeight = 120; // Aproximado
                                    
                                    let top = rect.bottom + 2;
                                    if (spaceBelow < dropdownHeight && spaceAbove > dropdownHeight) {
                                      top = rect.top - dropdownHeight - 2;
                                    }
                                    
                                    setOpenDropdownAlumno({ 
                                      id: alumno.id, 
                                      top: top, 
                                      left: left
                                    });
                                  }
                                }}
                                title="Opciones"
                              >
                                <span className="btn-opciones-icon">⚙️</span>
                                Opciones {openDropdownAlumno?.id === alumno.id ? '▲' : '▼'}
                              </button>
                              {openDropdownAlumno?.id === alumno.id && openDropdownAlumno?.top && 
                                createPortal(
                                  <div 
                                    className="dropdown-menu dropdown-menu-alumno"
                                    style={{
                                      top: `${openDropdownAlumno.top}px`,
                                      left: `${openDropdownAlumno.left}px`,
                                      position: 'fixed',
                                      zIndex: 10001,
                                      transform: 'translateZ(0)',
                                      willChange: 'transform',
                                    }}
                                    onClick={(e) => e.stopPropagation()}
                                  >
                                    <button
                                      className="dropdown-item"
                                      type="button"
                                      onClick={(e) => {
                                        e.stopPropagation();
                                        e.preventDefault();
                                        setOpenDropdownAlumno(null);
                                        abrirModalIncidencias(alumno);
                                      }}
                                    >
                                      <span className="dropdown-icon">📋</span>
                                      <span>Incidencias</span>
                                    </button>
                                    <button
                                      className="dropdown-item"
                                      type="button"
                                      onClick={(e) => {
                                        e.stopPropagation();
                                        e.preventDefault();
                                        setOpenDropdownAlumno(null);
                                        abrirModalEstrellas(alumno);
                                      }}
                                    >
                                      <span className="dropdown-icon">⭐</span>
                                      <span>Estrellas</span>
                                    </button>
                                    <button
                                      className="dropdown-item"
                                      type="button"
                                      onClick={(e) => {
                                        e.stopPropagation();
                                        e.preventDefault();
                                        setOpenDropdownAlumno(null);
                                        // TODO: Implementar Notas Detalladas
                                        console.log('Notas Detalladas para alumno:', alumno.id);
                                      }}
                                    >
                                      <span className="dropdown-icon">📝</span>
                                      <span>Notas Detalladas</span>
                                    </button>
                                  </div>,
                                  document.body
                                )
                              }
                            </div>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              ) : (
                <div className="empty-state">
                  <p>No se encontraron alumnos para este curso</p>
                </div>
              )}
            </div>
          </div>
        )}

        {/* Modal de Gestión de Estrellas */}
        {mostrarModalEstrellas && alumnoSeleccionado && createPortal(
          <div className="modal-estrellas-overlay" onClick={() => setMostrarModalEstrellas(false)}>
            <div className="modal-estrellas-container" onClick={(e) => e.stopPropagation()}>
              <div className="modal-estrellas-header">
                <div className="modal-estrellas-title-section">
                  <h2 className="modal-estrellas-title">
                    ⭐ Gestión de Estrellas
                  </h2>
                  <p className="modal-estrellas-subtitle">
                    {alumnoSeleccionado.nombre_completo}
                  </p>
                  <div className="modal-estrellas-total">
                    <span className="total-label">Total de Estrellas:</span>
                    <span className="total-value">{totalEstrellas}</span>
                  </div>
                </div>
                <button
                  className="modal-estrellas-close"
                  onClick={() => setMostrarModalEstrellas(false)}
                  type="button"
                >
                  ✕
                </button>
              </div>

              <div className="modal-estrellas-body">
                {/* Formulario para dar estrellas */}
                <div className="estrellas-form-section">
                  <h3 className="section-title-estrellas">Dar Estrellas</h3>
                  <form onSubmit={handleDarEstrellas} className="form-estrellas">
                    <div className="form-group-estrellas">
                      <label htmlFor="points">Cantidad de Estrellas *</label>
                      <select
                        id="points"
                        value={formEstrellas.points}
                        onChange={(e) => setFormEstrellas({ ...formEstrellas, points: e.target.value })}
                        className="form-select-estrellas"
                        required
                      >
                        <option value="">Seleccione cantidad</option>
                        {[1, 2, 3, 4, 5, 6, 7, 8, 9, 10].map(num => (
                          <option key={num} value={num}>
                            {num} estrella{num > 1 ? 's' : ''}
                          </option>
                        ))}
                      </select>
                    </div>
                    <div className="form-group-estrellas">
                      <label htmlFor="description">Descripción *</label>
                      <textarea
                        id="description"
                        value={formEstrellas.description}
                        onChange={(e) => setFormEstrellas({ ...formEstrellas, description: e.target.value })}
                        className="form-textarea-estrellas"
                        placeholder="Ej: Participación destacada en clase"
                        rows="3"
                        required
                      />
                    </div>
                    <button
                      type="submit"
                      className="btn-dar-estrellas"
                      disabled={guardandoEstrellas}
                    >
                      {guardandoEstrellas ? 'Guardando...' : '⭐ Dar Estrellas'}
                    </button>
                  </form>
                </div>

                {/* Historial de Estrellas */}
                <div className="estrellas-historial-section">
                  <h3 className="section-title-estrellas">Historial de Estrellas</h3>
                  {loadingEstrellas ? (
                    <div className="loading-estrellas">Cargando historial...</div>
                  ) : historialEstrellas.length > 0 ? (
                    <div className="historial-table-container">
                      <table className="historial-table">
                        <thead>
                          <tr>
                            <th>Docente</th>
                            <th>Estrellas</th>
                            <th>Descripción</th>
                            <th>Fecha</th>
                            <th></th>
                          </tr>
                        </thead>
                        <tbody>
                          {historialEstrellas.map((item) => (
                            <tr key={item.id}>
                              <td>{item.docente_nombre || 'N/A'}</td>
                              <td>
                                <span className="estrellas-badge-small">
                                  ⭐ {item.points}
                                </span>
                              </td>
                              <td className="descripcion-cell">{item.description || '-'}</td>
                              <td>{formatearFecha(item.created_at)}</td>
                              <td>
                                {item.puede_eliminar ? (
                                  <button
                                    className="btn-eliminar-estrella"
                                    onClick={() => handleEliminarEstrellas(item.id, item.points)}
                                    type="button"
                                    title="Eliminar estas estrellas"
                                  >
                                    🗑️
                                  </button>
                                ) : (
                                  <span className="no-eliminar">-</span>
                                )}
                              </td>
                            </tr>
                          ))}
                        </tbody>
                      </table>
                    </div>
                  ) : (
                    <div className="empty-historial">
                      <p>No hay estrellas registradas aún</p>
                    </div>
                  )}
                </div>
              </div>
            </div>
            </div>,
            document.body
          )}

        {/* Modal de Gestión de Incidencias */}
        {mostrarModalIncidencias && alumnoSeleccionado && createPortal(
          <div className="modal-incidencias-overlay" onClick={() => setMostrarModalIncidencias(false)}>
            <div className="modal-incidencias-container" onClick={(e) => e.stopPropagation()}>
              <div className="modal-incidencias-header">
                <div className="modal-incidencias-title-section">
                  <h2 className="modal-incidencias-title">
                    📋 Gestión de Incidencias
                  </h2>
                  <p className="modal-incidencias-subtitle">
                    {alumnoSeleccionado.nombre_completo}
                  </p>
                  <div className="modal-incidencias-total">
                    <span className="total-label">Total de Incidencias:</span>
                    <span className="total-value">{totalIncidencias}</span>
                  </div>
                </div>
                <button
                  className="modal-incidencias-close"
                  onClick={() => setMostrarModalIncidencias(false)}
                  type="button"
                >
                  ✕
                </button>
              </div>

              <div className="modal-incidencias-body">
                {/* Formulario para registrar incidencias */}
                <div className="incidencias-form-section">
                  <h3 className="section-title-incidencias">Registrar Incidencia</h3>
                  <form onSubmit={handleRegistrarIncidencia} className="form-incidencias">
                    <div className="form-group-incidencias">
                      <label htmlFor="description-incidencia">Descripción de la Incidencia *</label>
                      <textarea
                        id="description-incidencia"
                        value={formIncidencias.description}
                        onChange={(e) => setFormIncidencias({ ...formIncidencias, description: e.target.value })}
                        className="form-textarea-incidencias"
                        placeholder="Ej: No trajo la tarea asignada, llegó tarde a clase, no participó en la actividad grupal..."
                        rows="4"
                        required
                      />
                    </div>
                    <button
                      type="submit"
                      className="btn-registrar-incidencia"
                      disabled={guardandoIncidencias}
                    >
                      {guardandoIncidencias ? 'Guardando...' : '📋 Registrar Incidencia'}
                    </button>
                  </form>
                </div>

                {/* Historial de Incidencias */}
                <div className="incidencias-historial-section">
                  <h3 className="section-title-incidencias">Historial de Incidencias</h3>
                  {loadingIncidencias ? (
                    <div className="loading-incidencias">Cargando historial...</div>
                  ) : historialIncidencias.length > 0 ? (
                    <div className="historial-table-container">
                      <table className="historial-table-incidencias">
                        <thead>
                          <tr>
                            <th>Docente</th>
                            <th>Curso</th>
                            <th>Descripción</th>
                            <th>Fecha</th>
                            <th></th>
                          </tr>
                        </thead>
                        <tbody>
                          {historialIncidencias.map((item) => (
                            <tr key={item.id}>
                              <td>{item.docente_nombre || 'N/A'}</td>
                              <td>
                                <span className="curso-badge-incidencia">
                                  {item.curso_nombre || 'N/A'}
                                </span>
                              </td>
                              <td className="descripcion-cell">{item.description || '-'}</td>
                              <td>{formatearFecha(item.created_at)}</td>
                              <td>
                                {item.puede_eliminar ? (
                                  <button
                                    className="btn-eliminar-incidencia"
                                    onClick={() => handleEliminarIncidencia(item.id)}
                                    type="button"
                                    title="Eliminar esta incidencia"
                                  >
                                    🗑️
                                  </button>
                                ) : (
                                  <span className="no-eliminar">-</span>
                                )}
                              </td>
                            </tr>
                          ))}
                        </tbody>
                      </table>
                    </div>
                  ) : (
                    <div className="empty-historial">
                      <p>No hay incidencias registradas aún</p>
                    </div>
                  )}
                </div>
              </div>
            </div>
          </div>,
          document.body
        )}
      </div>
    </DashboardLayout>
  );
}

export default DocenteCursos;

