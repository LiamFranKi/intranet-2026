import React, { useState, useEffect } from 'react';
import { createPortal } from 'react-dom';
import { useNavigate } from 'react-router-dom';
import DashboardLayout from '../components/DashboardLayout';
import api from '../services/api';
import './DocenteGrupos.css';

function DocenteGrupos() {
  const navigate = useNavigate();
  const [loading, setLoading] = useState(true);
  const [grupos, setGrupos] = useState([]);
  const [filter, setFilter] = useState('');
  const [selectedGrupo, setSelectedGrupo] = useState(null);
  const [grupoInfo, setGrupoInfo] = useState(null); // Información del grupo seleccionado
  const [alumnos, setAlumnos] = useState([]);
  const [loadingAlumnos, setLoadingAlumnos] = useState(false);
  const [openDropdownGrupo, setOpenDropdownGrupo] = useState(null); // { id, top, left } o null
  const [openDropdownAlumno, setOpenDropdownAlumno] = useState(null); // { id, top, left } o null

  useEffect(() => {
    cargarGrupos();
  }, []);

  // Cerrar dropdowns al hacer clic fuera
  useEffect(() => {
    if (!openDropdownGrupo && !openDropdownAlumno) return;
    
    const handleClickOutside = (event) => {
      const target = event.target;
      
      // Verificar si el clic está dentro de cualquier dropdown
      const isDropdownMenu = target.closest('.dropdown-menu');
      const isDropdownButton = target.closest('.btn-opciones-dropdown');
      const isDropdownItem = target.closest('.dropdown-item');
      const isDropdownContainer = target.closest('.dropdown-container');
      
      // También verificar si el clic está en un elemento dentro del dropdown
      const isInsideDropdown = isDropdownMenu || isDropdownButton || isDropdownItem || isDropdownContainer;
      
      // Solo cerrar si el clic NO está dentro del dropdown
      if (!isInsideDropdown) {
        // Usar setTimeout para permitir que otros eventos se procesen primero
        setTimeout(() => {
          setOpenDropdownGrupo(null);
          setOpenDropdownAlumno(null);
        }, 0);
      }
    };
    
    // Agregar el listener con capture phase para capturarlo antes que otros handlers
    document.addEventListener('mousedown', handleClickOutside, true);
    
    return () => {
      document.removeEventListener('mousedown', handleClickOutside, true);
    };
  }, [openDropdownGrupo, openDropdownAlumno]);

  const cargarGrupos = async () => {
    try {
      setLoading(true);
      const response = await api.get('/docente/grupos');
      setGrupos(response.data.grupos || []);
    } catch (error) {
      console.error('Error cargando grupos:', error);
    } finally {
      setLoading(false);
    }
  };

  const cargarAlumnos = async (grupoId) => {
    try {
      setLoadingAlumnos(true);
      const response = await api.get(`/docente/grupos/${grupoId}/alumnos`);
      setAlumnos(response.data.alumnos || []);
      setSelectedGrupo(grupoId);
      // Guardar información del grupo seleccionado
      const grupo = grupos.find(g => g.id === grupoId);
      setGrupoInfo(grupo);
      
      // Hacer scroll al inicio de la página cuando se carga la lista de alumnos
      setTimeout(() => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
        // También hacer scroll del contenedor principal al inicio
        const docenteGruposContainer = document.querySelector('.docente-grupos');
        if (docenteGruposContainer) {
          docenteGruposContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      }, 100);
    } catch (error) {
      console.error('Error cargando alumnos:', error);
    } finally {
      setLoadingAlumnos(false);
    }
  };

  const volverAGrupos = () => {
    setSelectedGrupo(null);
    setGrupoInfo(null);
    setAlumnos([]);
    setOpenDropdownAlumno(null);
  };

  const gruposFiltrados = grupos.filter(grupo => {
    const searchTerm = filter.toLowerCase();
    return (
      grupo.grado?.toString().includes(searchTerm) ||
      grupo.seccion?.toLowerCase().includes(searchTerm) ||
      grupo.nivel_nombre?.toLowerCase().includes(searchTerm) ||
      grupo.turno_nombre?.toLowerCase().includes(searchTerm)
    );
  });

  if (loading) {
    return (
      <DashboardLayout>
        <div className="docente-grupos-loading">
          <div className="loading-spinner">Cargando grupos...</div>
        </div>
      </DashboardLayout>
    );
  }

  return (
    <DashboardLayout>
      <div className="docente-grupos">
        <div className="page-header">
          <h1>Grupos Asignados</h1>
          <p className="page-subtitle">Lista de grupos a tu cargo en el año académico actual</p>
        </div>

        {!selectedGrupo ? (
          <div className="grupos-container">
            <div className="grupos-list-section">
            <div className="section-controls">
              <div className="filter-container">
                <input
                  type="text"
                  placeholder="🔍 Buscar por grado, sección, nivel..."
                  value={filter}
                  onChange={(e) => setFilter(e.target.value)}
                  className="filter-input"
                />
              </div>
            </div>

            <div className="grupos-table-container">
              <table className="grupos-table">
                <thead>
                  <tr>
                    <th>GRADO</th>
                    <th>SECCIÓN</th>
                    <th>NIVEL</th>
                    <th>TURNO</th>
                    <th>ALUMNOS</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  {gruposFiltrados.length > 0 ? (
                    gruposFiltrados.map((grupo) => (
                      <tr key={grupo.id}>
                        <td>{grupo.grado}°</td>
                        <td>{grupo.seccion}</td>
                        <td>{grupo.nivel_nombre}</td>
                        <td>{grupo.turno_nombre}</td>
                        <td>{grupo.total_alumnos || 0}</td>
                        <td>
                          <div className="dropdown-container">
                            <button
                              className="btn-opciones-dropdown"
                              type="button"
                              onClick={(e) => {
                                e.stopPropagation();
                                e.preventDefault();
                                
                                const rect = e.currentTarget.getBoundingClientRect();
                                if (openDropdownGrupo?.id === grupo.id) {
                                  setOpenDropdownGrupo(null);
                                } else {
                                  // Cerrar otros dropdowns primero
                                  // Calcular posición: justo debajo del botón
                                  const dropdownWidth = 180;
                                  let left = rect.left;
                                  // Si el dropdown se sale por la derecha, ajustar
                                  if (left + dropdownWidth > window.innerWidth) {
                                    left = window.innerWidth - dropdownWidth - 10;
                                  }
                                  // Asegurar que no se salga por la izquierda
                                  if (left < 10) {
                                    left = 10;
                                  }
                                  setOpenDropdownGrupo({ 
                                    id: grupo.id, 
                                    top: rect.bottom + 2, 
                                    left: left
                                  });
                                }
                              }}
                              title="Opciones"
                            >
                              <span className="btn-opciones-icon">⚙️</span>
                              Opciones {openDropdownGrupo?.id === grupo.id ? '▲' : '▼'}
                            </button>
                            {openDropdownGrupo?.id === grupo.id && openDropdownGrupo?.top && (
                              <div 
                                className="dropdown-menu dropdown-menu-grupo"
                                style={{
                                  top: `${openDropdownGrupo.top}px`,
                                  left: `${openDropdownGrupo.left}px`,
                                  position: 'fixed',
                                  zIndex: 10001
                                }}
                              >
                                <button
                                  className="dropdown-item"
                                  onClick={(e) => {
                                    e.stopPropagation();
                                    cargarAlumnos(grupo.id);
                                    setOpenDropdownGrupo(null);
                                  }}
                                >
                                  <span className="dropdown-icon">📋</span>
                                  <span>Lista de Alumnos</span>
                                </button>
                                <button
                                  className="dropdown-item"
                                  onClick={(e) => {
                                    e.stopPropagation();
                                    // TODO: Implementar envío de mensaje
                                    setOpenDropdownGrupo(null);
                                  }}
                                >
                                  <span className="dropdown-icon">✉️</span>
                                  <span>Enviar Mensaje</span>
                                </button>
                              </div>
                            )}
                          </div>
                        </td>
                      </tr>
                    ))
                  ) : (
                    <tr>
                      <td colSpan="6" className="empty-state">
                        {filter ? 'No se encontraron grupos con ese filtro' : 'No tienes grupos asignados'}
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
            </div>
          </div>
        ) : (
          <div className="alumnos-container">
            <div className="alumnos-header-section mundo-card">
              <button className="btn-regresar" onClick={volverAGrupos}>
                <span className="btn-icon">←</span>
                Volver
              </button>
              <div className="grupo-info">
                <h2 className="section-title-blue">
                  Lista de Alumnos - {grupoInfo?.grado}° {grupoInfo?.seccion}
                </h2>
                <p className="grupo-subtitle">{grupoInfo?.nivel_nombre} - {grupoInfo?.turno_nombre}</p>
              </div>
            </div>
            
            <div className="alumnos-list-section mundo-card">
              {loadingAlumnos ? (
                <div className="loading-alumnos">Cargando alumnos...</div>
              ) : (
                <div className="alumnos-table-container">
                  <table className="alumnos-table">
                    <thead>
                      <tr>
                        <th>APELLIDOS Y NOMBRES</th>
                        <th>FECHA DE NACIMIENTO</th>
                        <th>TELÉFONO</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      {alumnos.length > 0 ? (
                        alumnos.map((alumno, index) => (
                          <tr key={`alumno-${alumno.id}-${index}-${alumno.telefono || ''}`}>
                            <td>
                              {alumno.apellido_paterno} {alumno.apellido_materno}, {alumno.nombres}
                            </td>
                            <td>
                              {alumno.fecha_nacimiento 
                                ? new Date(alumno.fecha_nacimiento).toLocaleDateString('es-PE')
                                : 'N/A'}
                            </td>
                            <td>
                              {alumno.telefono || 'N/A'}
                            </td>
                            <td>
                              <div className="dropdown-container">
                                <button
                                  className="btn-opciones-dropdown"
                                  type="button"
                                  onClick={(e) => {
                                    e.stopPropagation();
                                    e.preventDefault();
                                    
                                    // Cerrar otros dropdowns primero
                                    if (openDropdownAlumno?.id !== alumno.id) {
                                      setOpenDropdownGrupo(null);
                                    }
                                    
                                    const rect = e.currentTarget.getBoundingClientRect();
                                    if (openDropdownAlumno?.id === alumno.id) {
                                      setOpenDropdownAlumno(null);
                                    } else {
                                      // Calcular posición del dropdown
                                      const dropdownWidth = 180;
                                      const dropdownHeight = 90; // Aproximado
                                      let left = rect.left;
                                      let top = rect.bottom + 2;
                                      
                                      // Si el dropdown se sale por la derecha, ajustar
                                      if (left + dropdownWidth > window.innerWidth - 10) {
                                        left = window.innerWidth - dropdownWidth - 10;
                                      }
                                      // Asegurar que no se salga por la izquierda
                                      if (left < 10) {
                                        left = 10;
                                      }
                                      
                                      // Si el dropdown se sale por abajo, mostrarlo arriba
                                      if (top + dropdownHeight > window.innerHeight - 10) {
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
                                        zIndex: 99999
                                      }}
                                      onClick={(e) => e.stopPropagation()}
                                    >
                                      <button
                                        className="dropdown-item"
                                        onClick={(e) => {
                                          e.stopPropagation();
                                          // TODO: Implementar envío de mensaje
                                          setOpenDropdownAlumno(null);
                                        }}
                                      >
                                        <span className="dropdown-icon">✉️</span>
                                        <span>Enviar Mensaje</span>
                                      </button>
                                      <button
                                        className="dropdown-item"
                                        onClick={(e) => {
                                          e.stopPropagation();
                                          // TODO: Implementar ver información
                                          setOpenDropdownAlumno(null);
                                        }}
                                      >
                                        <span className="dropdown-icon">ℹ️</span>
                                        <span>Ver Información</span>
                                      </button>
                                    </div>,
                                    document.body
                                  )
                                }
                              </div>
                            </td>
                          </tr>
                        ))
                      ) : (
                        <tr>
                          <td colSpan="4" className="empty-state">
                            No hay alumnos en este grupo
                          </td>
                        </tr>
                      )}
                    </tbody>
                  </table>
                </div>
              )}
            </div>
          </div>
        )}
      </div>
    </DashboardLayout>
  );
}

export default DocenteGrupos;

