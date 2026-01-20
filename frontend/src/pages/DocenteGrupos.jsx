import React, { useState, useEffect } from 'react';
import { createPortal } from 'react-dom';
import { useNavigate } from 'react-router-dom';
import { QRCodeSVG } from 'qrcode.react';
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
  const [alumnoInfo, setAlumnoInfo] = useState(null); // Información completa del alumno seleccionado
  const [loadingAlumnoInfo, setLoadingAlumnoInfo] = useState(false);

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
      // Usar un delay para permitir que los eventos de los items se procesen primero
      if (!isInsideDropdown) {
        setTimeout(() => {
          setOpenDropdownGrupo(null);
          setOpenDropdownAlumno(null);
        }, 100);
      }
    };
    
    // Agregar listener con un pequeño delay para permitir que clicks en items se procesen
    const timeoutId = setTimeout(() => {
      document.addEventListener('click', handleClickOutside, false);
    }, 50);
    
    return () => {
      clearTimeout(timeoutId);
      document.removeEventListener('click', handleClickOutside, false);
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
    console.log('cargarAlumnos llamado con grupoId:', grupoId);
    try {
      setLoadingAlumnos(true);
      const response = await api.get(`/docente/grupos/${grupoId}/alumnos`);
      console.log('Alumnos recibidos:', response.data.alumnos);
      setAlumnos(response.data.alumnos || []);
      console.log('Estableciendo selectedGrupo a:', grupoId);
      // Guardar información del grupo seleccionado primero
      const grupo = grupos.find(g => g.id === grupoId);
      console.log('Grupo encontrado:', grupo);
      setGrupoInfo(grupo);
      // Luego establecer el grupo seleccionado para que renderice la vista de alumnos
      setSelectedGrupo(grupoId);
      console.log('selectedGrupo establecido, debería renderizar lista de alumnos');
      
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

  const cargarInfoAlumno = async (alumnoId) => {
    setLoadingAlumnoInfo(true);
    try {
      const response = await api.get(`/docente/alumnos/${alumnoId}/info`);
      setAlumnoInfo(response.data);
    } catch (error) {
      console.error('Error cargando información del alumno:', error);
      setAlumnoInfo(null);
    } finally {
      setLoadingAlumnoInfo(false);
    }
  };

  const cerrarModalAlumno = () => {
    setAlumnoInfo(null);
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
            {/* Debug: selectedGrupo = {String(selectedGrupo)} */}
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
                            {openDropdownGrupo?.id === grupo.id && openDropdownGrupo?.top && 
                              createPortal(
                                <div 
                                  className="dropdown-menu dropdown-menu-grupo"
                                  style={{
                                    top: `${openDropdownGrupo.top}px`,
                                    left: `${openDropdownGrupo.left}px`,
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
                                      console.log('✅ Click en Lista de Alumnos, grupo.id:', grupo.id);
                                      const grupoId = grupo.id;
                                      // Cerrar dropdown primero
                                      setOpenDropdownGrupo(null);
                                      // Cargar alumnos inmediatamente
                                      cargarAlumnos(grupoId);
                                    }}
                                  >
                                    <span className="dropdown-icon">📋</span>
                                    <span>Lista de Alumnos</span>
                                  </button>
                                  <button
                                    className="dropdown-item"
                                    onClick={(e) => {
                                      e.stopPropagation();
                                      e.preventDefault();
                                      // TODO: Implementar envío de mensaje
                                      setOpenDropdownGrupo(null);
                                    }}
                                  >
                                    <span className="dropdown-icon">✉️</span>
                                    <span>Enviar Mensaje</span>
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
            {/* Debug: Mostrando lista de alumnos para grupo {selectedGrupo} */}
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
                                          cargarInfoAlumno(alumno.id);
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

      {/* Modal de Información del Alumno */}
      {alumnoInfo && (
        <div className="alumno-info-modal-overlay" onClick={cerrarModalAlumno}>
          <div className="alumno-info-modal" onClick={(e) => e.stopPropagation()}>
            {loadingAlumnoInfo ? (
              <div className="alumno-info-loading">
                <div className="loading-spinner">Cargando información...</div>
              </div>
            ) : (
              <>
                <div className="alumno-info-header">
                  <h2 className="alumno-info-title">Información del Alumno</h2>
                  <button className="alumno-info-close" onClick={cerrarModalAlumno}>✕</button>
                </div>
                
                <div className="alumno-info-content">
                  {/* Card de Datos Personales del Alumno - Similar a apoderados */}
                  <div className="alumno-datos-personales-card alumno-apoderado-card">
                    <h3 className="alumno-apoderado-tipo">📋 Datos del Alumno</h3>
                    <table className="alumno-apoderado-table">
                      <tbody>
                        <tr>
                          <th>APELLIDOS Y NOMBRES</th>
                          <td>{alumnoInfo.alumno.apellido_paterno} {alumnoInfo.alumno.apellido_materno}, {alumnoInfo.alumno.nombres}</td>
                        </tr>
                        <tr>
                          <th>FECHA DE NACIMIENTO</th>
                          <td>
                            {alumnoInfo.alumno.fecha_nacimiento 
                              ? new Date(alumnoInfo.alumno.fecha_nacimiento).toLocaleDateString('es-PE')
                              : 'N/A'}
                          </td>
                        </tr>
                        <tr>
                          <th>Nº DE DOCUMENTO</th>
                          <td>{alumnoInfo.alumno.nro_documento || 'N/A'}</td>
                        </tr>
                        <tr>
                          <th>SEXO</th>
                          <td>
                            {alumnoInfo.alumno.sexo === 0 || alumnoInfo.alumno.sexo === '0' ? 'Masculino' : 
                             alumnoInfo.alumno.sexo === 1 || alumnoInfo.alumno.sexo === '1' ? 'Femenino' : 'N/A'}
                          </td>
                        </tr>
                        {alumnoInfo.alumno.nivel_actual && (
                          <tr>
                            <th>NIVEL ACTUAL</th>
                            <td>
                              {alumnoInfo.alumno.nivel_actual.nivel_nombre} - {alumnoInfo.alumno.nivel_actual.grado}° {alumnoInfo.alumno.nivel_actual.seccion}
                            </td>
                          </tr>
                        )}
                        {alumnoInfo.alumno.avatar && (
                          <>
                            <tr>
                              <th>AVATAR / NIVEL</th>
                              <td>
                                {alumnoInfo.alumno.avatar.name || 'N/A'}
                                {alumnoInfo.alumno.avatar.level !== undefined && alumnoInfo.alumno.avatar.level !== null && ` - Nivel ${String(alumnoInfo.alumno.avatar.level).padStart(2, '0')}`}
                              </td>
                            </tr>
                            {alumnoInfo.alumno.avatar.description && (
                              <tr>
                                <th>DESCRIPCIÓN AVATAR</th>
                                <td>{alumnoInfo.alumno.avatar.description}</td>
                              </tr>
                            )}
                            {alumnoInfo.alumno.estrellas !== undefined && (
                              <tr>
                                <th>ESTRELLAS</th>
                                <td>⭐ {alumnoInfo.alumno.estrellas || 0}</td>
                              </tr>
                            )}
                          </>
                        )}
                      </tbody>
                    </table>
                  </div>

                  {/* 3 Círculos medianos: Foto, Avatar y QR horizontalmente */}
                  <div className="alumno-medios-container">
                    {/* Foto del alumno */}
                    <div className="alumno-medio-circle">
                      {alumnoInfo.alumno.foto_url ? (
                        <>
                          <img 
                            src={alumnoInfo.alumno.foto_url} 
                            alt="Foto del alumno"
                            className="alumno-medio-img"
                            onError={(e) => {
                              e.target.style.display = 'none';
                              const placeholder = e.target.parentElement.querySelector('.alumno-medio-placeholder-fallback');
                              if (placeholder) {
                                placeholder.style.display = 'flex';
                              }
                            }}
                          />
                          <div className="alumno-medio-placeholder alumno-medio-placeholder-fallback" style={{display: 'none'}}>
                            {alumnoInfo.alumno.nombres?.charAt(0)?.toUpperCase() || 'A'}
                            {alumnoInfo.alumno.apellido_paterno?.charAt(0)?.toUpperCase() || 'A'}
                          </div>
                        </>
                      ) : (
                        <div className="alumno-medio-placeholder">
                          {alumnoInfo.alumno.nombres?.charAt(0)?.toUpperCase() || 'A'}
                          {alumnoInfo.alumno.apellido_paterno?.charAt(0)?.toUpperCase() || 'A'}
                        </div>
                      )}
                      <p className="alumno-medio-label">Foto</p>
                    </div>

                    {/* Avatar del alumno */}
                    {alumnoInfo.alumno.avatar?.image_url ? (
                      <div className="alumno-medio-circle">
                        <div className="alumno-medio-avatar-wrapper">
                          <img 
                            src={alumnoInfo.alumno.avatar.image_url} 
                            alt={alumnoInfo.alumno.avatar.name || "Avatar del alumno"}
                            className="alumno-medio-img"
                          />
                          {alumnoInfo.alumno.avatar.level > 0 && (
                            <div className="alumno-medio-level-badge">
                              Nv.{alumnoInfo.alumno.avatar.level}
                            </div>
                          )}
                        </div>
                        <p className="alumno-medio-label">Avatar</p>
                      </div>
                    ) : null}

                    {/* QR Code */}
                    {alumnoInfo.qr_code && (
                      <div className="alumno-medio-circle">
                        <div className="alumno-medio-qr">
                          <QRCodeSVG 
                            value={alumnoInfo.qr_code}
                            size={120}
                            level="H"
                          />
                        </div>
                        <p className="alumno-medio-label">Código QR</p>
                      </div>
                    )}
                  </div>

                  {/* Datos de Apoderados */}
                  {(alumnoInfo.apoderados?.padre || alumnoInfo.apoderados?.madre) && (
                    <div className="alumno-apoderados-section">
                      <h3 className="alumno-apoderados-title">Datos de Apoderados</h3>
                      <div className="alumno-apoderados-grid">
                        {alumnoInfo.apoderados.padre && (
                          <div className="alumno-apoderado-card">
                            <h4 className="alumno-apoderado-tipo">👨 Padre</h4>
                            <table className="alumno-apoderado-table">
                              <tbody>
                                <tr>
                                  <th>NOMBRES COMPLETOS</th>
                                  <td>{alumnoInfo.apoderados.padre.nombres} {alumnoInfo.apoderados.padre.apellido_paterno} {alumnoInfo.apoderados.padre.apellido_materno}</td>
                                </tr>
                                {alumnoInfo.apoderados.padre.nro_documento && (
                                  <tr>
                                    <th>Nº DE DOCUMENTO</th>
                                    <td>{alumnoInfo.apoderados.padre.nro_documento}</td>
                                  </tr>
                                )}
                                {alumnoInfo.apoderados.padre.telefono_celular && (
                                  <tr>
                                    <th>TELÉFONO CELULAR</th>
                                    <td>{alumnoInfo.apoderados.padre.telefono_celular}</td>
                                  </tr>
                                )}
                                {alumnoInfo.apoderados.padre.telefono_fijo && (
                                  <tr>
                                    <th>TELÉFONO FIJO</th>
                                    <td>{alumnoInfo.apoderados.padre.telefono_fijo}</td>
                                  </tr>
                                )}
                                {alumnoInfo.apoderados.padre.email && (
                                  <tr>
                                    <th>EMAIL</th>
                                    <td>{alumnoInfo.apoderados.padre.email}</td>
                                  </tr>
                                )}
                                {alumnoInfo.apoderados.padre.direccion && (
                                  <tr>
                                    <th>DIRECCIÓN</th>
                                    <td>{alumnoInfo.apoderados.padre.direccion}</td>
                                  </tr>
                                )}
                                {alumnoInfo.apoderados.padre.ocupacion && (
                                  <tr>
                                    <th>OCUPACIÓN</th>
                                    <td>{alumnoInfo.apoderados.padre.ocupacion}</td>
                                  </tr>
                                )}
                                {alumnoInfo.apoderados.padre.centro_trabajo_direccion && (
                                  <tr>
                                    <th>CENTRO DE TRABAJO</th>
                                    <td>{alumnoInfo.apoderados.padre.centro_trabajo_direccion}</td>
                                  </tr>
                                )}
                              </tbody>
                            </table>
                          </div>
                        )}
                        
                        {alumnoInfo.apoderados.madre && (
                          <div className="alumno-apoderado-card">
                            <h4 className="alumno-apoderado-tipo">👩 Madre</h4>
                            <table className="alumno-apoderado-table">
                              <tbody>
                                <tr>
                                  <th>NOMBRES COMPLETOS</th>
                                  <td>{alumnoInfo.apoderados.madre.nombres} {alumnoInfo.apoderados.madre.apellido_paterno} {alumnoInfo.apoderados.madre.apellido_materno}</td>
                                </tr>
                                {alumnoInfo.apoderados.madre.nro_documento && (
                                  <tr>
                                    <th>Nº DE DOCUMENTO</th>
                                    <td>{alumnoInfo.apoderados.madre.nro_documento}</td>
                                  </tr>
                                )}
                                {alumnoInfo.apoderados.madre.telefono_celular && (
                                  <tr>
                                    <th>TELÉFONO CELULAR</th>
                                    <td>{alumnoInfo.apoderados.madre.telefono_celular}</td>
                                  </tr>
                                )}
                                {alumnoInfo.apoderados.madre.telefono_fijo && (
                                  <tr>
                                    <th>TELÉFONO FIJO</th>
                                    <td>{alumnoInfo.apoderados.madre.telefono_fijo}</td>
                                  </tr>
                                )}
                                {alumnoInfo.apoderados.madre.email && (
                                  <tr>
                                    <th>EMAIL</th>
                                    <td>{alumnoInfo.apoderados.madre.email}</td>
                                  </tr>
                                )}
                                {alumnoInfo.apoderados.madre.direccion && (
                                  <tr>
                                    <th>DIRECCIÓN</th>
                                    <td>{alumnoInfo.apoderados.madre.direccion}</td>
                                  </tr>
                                )}
                                {alumnoInfo.apoderados.madre.ocupacion && (
                                  <tr>
                                    <th>OCUPACIÓN</th>
                                    <td>{alumnoInfo.apoderados.madre.ocupacion}</td>
                                  </tr>
                                )}
                                {alumnoInfo.apoderados.madre.centro_trabajo_direccion && (
                                  <tr>
                                    <th>CENTRO DE TRABAJO</th>
                                    <td>{alumnoInfo.apoderados.madre.centro_trabajo_direccion}</td>
                                  </tr>
                                )}
                              </tbody>
                            </table>
                          </div>
                        )}
                      </div>
                    </div>
                  )}

                  {/* Historial de Matrículas */}
                  {alumnoInfo.matriculas_por_nivel && alumnoInfo.matriculas_por_nivel.length > 0 && (
                    <div className="alumno-matriculas-section">
                      <h3 className="alumno-matriculas-title">Historial de Matrículas</h3>
                      <div className="alumno-matriculas-grid">
                        {alumnoInfo.matriculas_por_nivel.map((nivel) => (
                          <div key={nivel.nivel_id} className="alumno-nivel-group">
                            <h4 className="alumno-nivel-name">{nivel.nivel_nombre}</h4>
                            <div className="alumno-matriculas-list">
                              {nivel.matriculas.map((matricula) => (
                                <div key={matricula.id} className="alumno-matricula-badge">
                                  {matricula.grado}° {matricula.seccion} ({matricula.anio})
                                  {matricula.turno_nombre && ` - ${matricula.turno_nombre}`}
                                </div>
                              ))}
                            </div>
                          </div>
                        ))}
                      </div>
                    </div>
                  )}
                </div>
              </>
            )}
          </div>
        </div>
      )}
    </DashboardLayout>
  );
}

export default DocenteGrupos;

