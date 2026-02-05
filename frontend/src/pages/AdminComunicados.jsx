import React, { useState, useEffect } from 'react';
import { createPortal } from 'react-dom';
import DashboardLayout from '../components/DashboardLayout';
import api from '../services/api';
import { normalizeStaticFileUrl } from '../config/staticFiles';
import { useAuth } from '../context/AuthContext';
import './DocenteComunicados.css';
import './DocenteGrupos.css';
import Swal from 'sweetalert2';

function AdminComunicados() {
  const { user } = useAuth();
  const [loading, setLoading] = useState(true);
  const [comunicados, setComunicados] = useState([]);
  const [searchTerm, setSearchTerm] = useState('');
  const [currentPage, setCurrentPage] = useState(1);
  const [comunicadosLeidos, setComunicadosLeidos] = useState(new Set());
  const [mostrarModal, setMostrarModal] = useState(false);
  const [comunicadoEditando, setComunicadoEditando] = useState(null);
  const [formulario, setFormulario] = useState({
    descripcion: '',
    contenido: '',
    tipo: 'TEXTO',
    privacidad: '',
    show_in_home: false
  });
  const [archivoSeleccionado, setArchivoSeleccionado] = useState(null);
  const [archivoActual, setArchivoActual] = useState(null);
  const [pagination, setPagination] = useState({
    total: 0,
    page: 1,
    limit: 12,
    totalPages: 1
  });

  useEffect(() => {
    const leidosGuardados = localStorage.getItem('comunicados_leidos');
    if (leidosGuardados) {
      try {
        const ids = JSON.parse(leidosGuardados);
        setComunicadosLeidos(new Set(ids));
      } catch (e) {
        console.error('Error cargando comunicados leídos:', e);
      }
    }
  }, []);

  useEffect(() => {
    cargarComunicados();
  }, [currentPage, searchTerm]);

  const cargarComunicados = async () => {
    try {
      setLoading(true);
      const params = {
        page: currentPage,
        limit: 12,
        ...(searchTerm && { search: searchTerm })
      };
      const response = await api.get('/docente/comunicados', { params });
      const comunicadosData = response.data.comunicados || [];
      
      setComunicados(comunicadosData);
      setPagination(response.data.pagination || pagination);
    } catch (error) {
      console.error('Error cargando comunicados:', error);
    } finally {
      setLoading(false);
    }
  };

  const abrirModalCrear = () => {
    setComunicadoEditando(null);
    setFormulario({
      descripcion: '',
      contenido: '',
      tipo: 'TEXTO',
      privacidad: '',
      show_in_home: false
    });
    setArchivoSeleccionado(null);
    setArchivoActual(null);
    setMostrarModal(true);
  };

  const abrirModalEditar = (comunicado) => {
    setComunicadoEditando(comunicado);
    setFormulario({
      descripcion: comunicado.descripcion || '',
      contenido: comunicado.contenido || '',
      tipo: comunicado.tipo || 'TEXTO',
      privacidad: comunicado.privacidad || '',
      show_in_home: comunicado.show_in_home === 1 || comunicado.show_in_home === true
    });
    setArchivoSeleccionado(null);
    setArchivoActual(comunicado.archivo_url || null);
    setMostrarModal(true);
  };

  const cerrarModal = () => {
    setMostrarModal(false);
    setComunicadoEditando(null);
    setFormulario({
      descripcion: '',
      contenido: '',
      tipo: 'TEXTO',
      privacidad: '',
      show_in_home: false
    });
    setArchivoSeleccionado(null);
    setArchivoActual(null);
  };

  const handleGuardarComunicado = async (e) => {
    e.preventDefault();
    
    try {
      const formData = new FormData();
      formData.append('descripcion', formulario.descripcion);
      formData.append('contenido', formulario.contenido);
      formData.append('tipo', formulario.tipo);
      formData.append('privacidad', formulario.privacidad);
      formData.append('show_in_home', formulario.show_in_home ? '1' : '0');
      
      if (archivoSeleccionado) {
        formData.append('archivo', archivoSeleccionado);
      }

      if (comunicadoEditando) {
        // Editar
        const response = await api.put(`/docente/comunicados/${comunicadoEditando.id}`, formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });
        
        if (response.data.success) {
          Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: 'Comunicado actualizado correctamente',
            timer: 2000,
            showConfirmButton: false
          });
          cerrarModal();
          cargarComunicados();
        }
      } else {
        // Crear
        const response = await api.post('/docente/comunicados', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });
        
        if (response.data.success) {
          Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: 'Comunicado creado correctamente',
            timer: 2000,
            showConfirmButton: false
          });
          cerrarModal();
          cargarComunicados();
        }
      }
    } catch (error) {
      console.error('Error guardando comunicado:', error);
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: error.response?.data?.error || 'Error al guardar comunicado'
      });
    }
  };

  const handleEliminarComunicado = async (comunicado) => {
    const result = await Swal.fire({
      title: '¿Estás seguro?',
      text: `¿Deseas eliminar el comunicado "${comunicado.descripcion}"?`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#ef4444',
      cancelButtonColor: '#6b7280',
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar'
    });

    if (result.isConfirmed) {
      try {
        const response = await api.delete(`/docente/comunicados/${comunicado.id}`);
        
        if (response.data.success) {
          Swal.fire({
            icon: 'success',
            title: '¡Eliminado!',
            text: 'Comunicado eliminado correctamente',
            timer: 2000,
            showConfirmButton: false
          });
          cargarComunicados();
        }
      } catch (error) {
        console.error('Error eliminando comunicado:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: error.response?.data?.error || 'Error al eliminar comunicado'
        });
      }
    }
  };

  const handleSearch = (e) => {
    e.preventDefault();
    setCurrentPage(1);
  };

  const marcarComoLeido = (comunicadoId) => {
    const nuevosLeidos = new Set(comunicadosLeidos);
    nuevosLeidos.add(comunicadoId);
    setComunicadosLeidos(nuevosLeidos);
    
    try {
      localStorage.setItem('comunicados_leidos', JSON.stringify(Array.from(nuevosLeidos)));
    } catch (e) {
      console.error('Error guardando comunicados leídos:', e);
    }
  };

  const handleVerArchivo = (comunicadoId, archivoUrl) => {
    if (archivoUrl) {
      marcarComoLeido(comunicadoId);
      
      let urlFinal = archivoUrl.trim();
      
      if (!urlFinal.startsWith('http://') && !urlFinal.startsWith('https://')) {
        urlFinal = normalizeStaticFileUrl(archivoUrl);
      }
      
      if (!urlFinal) {
        alert('Error: No se pudo construir la URL del archivo');
        return;
      }
      
      try {
        const urlObj = new URL(urlFinal);
        const nuevaVentana = window.open(urlObj.href, '_blank', 'noopener,noreferrer');
        
        if (!nuevaVentana) {
          const link = document.createElement('a');
          link.href = urlObj.href;
          link.target = '_blank';
          link.rel = 'noopener noreferrer';
          document.body.appendChild(link);
          link.click();
          setTimeout(() => {
            document.body.removeChild(link);
          }, 100);
        }
      } catch (error) {
        console.error('Error al procesar URL:', error);
        alert(`Error al abrir el archivo.\nURL: ${urlFinal}\n\nError: ${error.message}`);
      }
    }
  };

  const esComunicadoNuevo = (comunicadoId, fechaHora) => {
    if (!comunicadosLeidos.has(comunicadoId)) {
      if (fechaHora) {
        const fechaComunicado = new Date(fechaHora);
        const hace7Dias = new Date();
        hace7Dias.setDate(hace7Dias.getDate() - 7);
        return fechaComunicado >= hace7Dias;
      }
      return true;
    }
    return false;
  };

  const formatearFecha = (fechaHora) => {
    if (!fechaHora) return '';
    const fecha = new Date(fechaHora);
    const dia = String(fecha.getDate()).padStart(2, '0');
    const mes = String(fecha.getMonth() + 1).padStart(2, '0');
    const año = fecha.getFullYear();
    let horas = fecha.getHours();
    const minutos = String(fecha.getMinutes()).padStart(2, '0');
    const ampm = horas >= 12 ? 'PM' : 'AM';
    horas = horas % 12;
    horas = horas ? horas : 12;
    return `${dia}-${mes}-${año} ${String(horas).padStart(2, '0')}:${minutos} ${ampm}`;
  };

  const renderPagination = () => {
    const pages = [];
    const maxPagesToShow = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxPagesToShow / 2));
    let endPage = Math.min(pagination.totalPages, startPage + maxPagesToShow - 1);

    if (endPage - startPage < maxPagesToShow - 1) {
      startPage = Math.max(1, endPage - maxPagesToShow + 1);
    }

    for (let i = startPage; i <= endPage; i++) {
      pages.push(i);
    }

    return (
      <div className="comunicados-pagination">
        <button
          className="pagination-btn"
          onClick={() => setCurrentPage(prev => Math.max(1, prev - 1))}
          disabled={currentPage === 1}
        >
          ← Anterior
        </button>
        <div className="pagination-pages">
          {startPage > 1 && (
            <>
              <button
                className={`pagination-btn ${currentPage === 1 ? 'active' : ''}`}
                onClick={() => setCurrentPage(1)}
              >
                1
              </button>
              {startPage > 2 && <span className="pagination-ellipsis">...</span>}
            </>
          )}
          {pages.map(page => (
            <button
              key={page}
              className={`pagination-btn ${currentPage === page ? 'active' : ''}`}
              onClick={() => setCurrentPage(page)}
            >
              {page}
            </button>
          ))}
          {endPage < pagination.totalPages && (
            <>
              {endPage < pagination.totalPages - 1 && <span className="pagination-ellipsis">...</span>}
              <button
                className={`pagination-btn ${currentPage === pagination.totalPages ? 'active' : ''}`}
                onClick={() => setCurrentPage(pagination.totalPages)}
              >
                {pagination.totalPages}
              </button>
            </>
          )}
        </div>
        <button
          className="pagination-btn"
          onClick={() => setCurrentPage(prev => Math.min(pagination.totalPages, prev + 1))}
          disabled={currentPage === pagination.totalPages}
        >
          Siguiente →
        </button>
      </div>
    );
  };

  if (loading && comunicados.length === 0) {
    return (
      <DashboardLayout>
        <div className="docente-comunicados-loading">
          <div className="loading-spinner">Cargando comunicados...</div>
        </div>
      </DashboardLayout>
    );
  }

  return (
    <DashboardLayout>
      <div className="docente-comunicados">
        <div className="page-header" style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
          <div>
            <h1>Comunicados</h1>
            <p>Comunicados y anuncios oficiales del colegio</p>
          </div>
          <button 
            onClick={abrirModalCrear}
            style={{
              padding: '0.75rem 1.5rem',
              background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
              color: 'white',
              border: 'none',
              borderRadius: '10px',
              cursor: 'pointer',
              fontSize: '1rem',
              fontWeight: '600',
              boxShadow: '0 4px 12px rgba(102, 126, 234, 0.3)'
            }}
          >
            ➕ Crear Comunicado
          </button>
        </div>

        <div className="comunicados-search-bar">
          <form onSubmit={handleSearch} className="search-form">
            <input
              type="text"
              placeholder="Buscar comunicados..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="search-input"
            />
            <button type="submit" className="search-btn">
              🔍 Buscar
            </button>
          </form>
        </div>

        {comunicados.length > 0 ? (
          <>
            <div className="comunicados-grid">
              {comunicados.map((comunicado) => {
                const esNuevo = esComunicadoNuevo(comunicado.id, comunicado.fecha_hora);
                return (
                <div 
                  key={comunicado.id} 
                  className={`comunicado-card ${esNuevo ? 'comunicado-nuevo' : ''}`}
                  style={{ position: 'relative' }}
                >
                  <div className="comunicado-card-header">
                    <div className="comunicado-icon">
                      {comunicado.tipo === 'ARCHIVO' ? '📄' : '📝'}
                    </div>
                    <div className="comunicado-badges">
                      {esNuevo && (
                        <div className="comunicado-badge-nuevo">NUEVO</div>
                      )}
                      <div className="comunicado-badge">
                        {comunicado.estado === 'ACTIVO' ? 'ACTIVO' : 'INACTIVO'}
                      </div>
                    </div>
                  </div>
                  <div className="comunicado-card-body">
                    <h3 className="comunicado-titulo">{comunicado.descripcion}</h3>
                    {comunicado.contenido && (
                      <div 
                        className="comunicado-contenido"
                        dangerouslySetInnerHTML={{ __html: comunicado.contenido }}
                      />
                    )}
                    <div className="comunicado-fecha">
                      📅 {formatearFecha(comunicado.fecha_hora)}
                    </div>
                  </div>
                  <div className="comunicado-card-footer" style={{ display: 'flex', gap: '0.5rem', alignItems: 'center', justifyContent: 'flex-end' }}>
                    {comunicado.archivo_url && (
                      <button
                        className="btn-ver-archivo"
                        onClick={(e) => {
                          e.preventDefault();
                          e.stopPropagation();
                          handleVerArchivo(comunicado.id, comunicado.archivo_url);
                        }}
                      >
                        👁️ Ver
                      </button>
                    )}
                    {!comunicado.archivo_url && comunicado.tipo === 'TEXTO' && (
                      <button
                        className="btn-ver-archivo"
                        onClick={() => marcarComoLeido(comunicado.id)}
                      >
                        ✓ Leído
                      </button>
                    )}
                    <button
                      onClick={() => abrirModalEditar(comunicado)}
                      style={{
                        background: 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
                        border: 'none',
                        borderRadius: '8px',
                        padding: '0.5rem 0.75rem',
                        cursor: 'pointer',
                        fontSize: '0.9rem',
                        color: 'white',
                        fontWeight: '600',
                        boxShadow: '0 2px 6px rgba(16, 185, 129, 0.3)',
                        transition: 'all 0.2s',
                        display: 'flex',
                        alignItems: 'center',
                        gap: '0.3rem'
                      }}
                      onMouseEnter={(e) => {
                        e.target.style.transform = 'scale(1.05)';
                        e.target.style.boxShadow = '0 4px 12px rgba(16, 185, 129, 0.4)';
                      }}
                      onMouseLeave={(e) => {
                        e.target.style.transform = 'scale(1)';
                        e.target.style.boxShadow = '0 2px 6px rgba(16, 185, 129, 0.3)';
                      }}
                      title="Editar"
                    >
                      ✏️ Editar
                    </button>
                    <button
                      onClick={() => handleEliminarComunicado(comunicado)}
                      style={{
                        background: 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)',
                        border: 'none',
                        borderRadius: '8px',
                        padding: '0.5rem 0.75rem',
                        cursor: 'pointer',
                        fontSize: '0.9rem',
                        color: 'white',
                        fontWeight: '600',
                        boxShadow: '0 2px 6px rgba(239, 68, 68, 0.3)',
                        transition: 'all 0.2s',
                        display: 'flex',
                        alignItems: 'center',
                        gap: '0.3rem'
                      }}
                      onMouseEnter={(e) => {
                        e.target.style.transform = 'scale(1.05)';
                        e.target.style.boxShadow = '0 4px 12px rgba(239, 68, 68, 0.4)';
                      }}
                      onMouseLeave={(e) => {
                        e.target.style.transform = 'scale(1)';
                        e.target.style.boxShadow = '0 2px 6px rgba(239, 68, 68, 0.3)';
                      }}
                      title="Eliminar"
                    >
                      🗑️ Eliminar
                    </button>
                  </div>
                </div>
                );
              })}
            </div>

            {pagination.totalPages > 1 && (
              <div className="comunicados-pagination-container">
                <div className="pagination-info">
                  Mostrando {((currentPage - 1) * pagination.limit) + 1} - {Math.min(currentPage * pagination.limit, pagination.total)} de {pagination.total}
                </div>
                {renderPagination()}
              </div>
            )}
          </>
        ) : (
          <div className="empty-state">
            <p>No se encontraron comunicados</p>
          </div>
        )}
      </div>

      {/* Modal de Crear/Editar Comunicado */}
      {mostrarModal && createPortal(
        <div 
          className="modal-mensaje-overlay" 
          onClick={cerrarModal}
          style={{ zIndex: 100000 }}
        >
          <div 
            className="modal-mensaje-container" 
            onClick={(e) => e.stopPropagation()}
            role="dialog"
            aria-modal="true"
            aria-labelledby="modal-comunicado-title"
            style={{ maxWidth: '800px' }}
          >
            <div className="modal-mensaje-header">
              <h2 id="modal-comunicado-title">
                {comunicadoEditando ? '✏️ Editar Comunicado' : '➕ Crear Nuevo Comunicado'}
              </h2>
              <button
                className="modal-mensaje-close"
                onClick={cerrarModal}
                type="button"
                aria-label="Cerrar modal"
              >
                ✕
              </button>
            </div>

            <div className="modal-mensaje-body">
              <form onSubmit={handleGuardarComunicado}>
                <div className="form-group">
                  <label>Descripción *</label>
                  <input
                    type="text"
                    required
                    value={formulario.descripcion}
                    onChange={(e) => setFormulario({...formulario, descripcion: e.target.value})}
                    className="form-input"
                    placeholder="Ej: Circular informativa sobre..."
                  />
                </div>
                <div className="form-group">
                  <label>Contenido</label>
                  <textarea
                    value={formulario.contenido}
                    onChange={(e) => setFormulario({...formulario, contenido: e.target.value})}
                    rows="6"
                    className="form-input"
                    placeholder="Contenido del comunicado..."
                  />
                </div>
                <div className="form-group">
                  <label>Tipo</label>
                  <select
                    value={formulario.tipo}
                    onChange={(e) => setFormulario({...formulario, tipo: e.target.value})}
                    className="form-input"
                  >
                    <option value="TEXTO">Texto</option>
                    <option value="ARCHIVO">Archivo</option>
                  </select>
                </div>
                {formulario.tipo === 'ARCHIVO' && (
                  <div className="form-group">
                    <label>Archivo</label>
                    <input
                      type="file"
                      onChange={(e) => setArchivoSeleccionado(e.target.files[0])}
                      accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.rar,.jpg,.jpeg,.png,.gif"
                      className="form-input"
                    />
                    {archivoActual && !archivoSeleccionado && (
                      <div style={{ 
                        marginTop: '0.75rem', 
                        padding: '0.75rem 1rem',
                        background: 'linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%)',
                        borderRadius: '10px',
                        border: '2px solid #bae6fd',
                        display: 'flex',
                        alignItems: 'center',
                        gap: '0.75rem'
                      }}>
                        <span style={{ fontSize: '1.5rem' }}>📎</span>
                        <div style={{ flex: 1 }}>
                          <p style={{ margin: 0, fontSize: '0.85rem', color: '#6b7280', fontWeight: '600' }}>
                            Archivo actual:
                          </p>
                          <a 
                            href={archivoActual} 
                            target="_blank" 
                            rel="noopener noreferrer"
                            style={{ 
                              fontSize: '0.9rem', 
                              color: '#4a83c1',
                              textDecoration: 'none',
                              wordBreak: 'break-all'
                            }}
                            onMouseEnter={(e) => e.target.style.textDecoration = 'underline'}
                            onMouseLeave={(e) => e.target.style.textDecoration = 'none'}
                          >
                            {archivoActual.split('/').pop()}
                          </a>
                        </div>
                      </div>
                    )}
                    {archivoSeleccionado && (
                      <div style={{ 
                        marginTop: '0.75rem', 
                        padding: '0.75rem 1rem',
                        background: 'linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%)',
                        borderRadius: '10px',
                        border: '2px solid #6ee7b7',
                        display: 'flex',
                        alignItems: 'center',
                        gap: '0.75rem'
                      }}>
                        <span style={{ fontSize: '1.5rem' }}>✅</span>
                        <div>
                          <p style={{ margin: 0, fontSize: '0.85rem', color: '#065f46', fontWeight: '600' }}>
                            Nuevo archivo seleccionado:
                          </p>
                          <p style={{ margin: '0.25rem 0 0 0', fontSize: '0.9rem', color: '#047857' }}>
                            {archivoSeleccionado.name}
                          </p>
                        </div>
                      </div>
                    )}
                  </div>
                )}
                <div className="form-group" style={{ 
                  display: 'flex', 
                  alignItems: 'center', 
                  justifyContent: 'center',
                  padding: '1rem',
                  background: 'linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%)',
                  borderRadius: '12px',
                  border: '2px solid #e5e7eb'
                }}>
                  <label style={{ 
                    display: 'flex', 
                    alignItems: 'center', 
                    gap: '0.75rem', 
                    cursor: 'pointer',
                    margin: 0,
                    userSelect: 'none'
                  }}>
                    <input
                      type="checkbox"
                      checked={formulario.show_in_home}
                      onChange={(e) => setFormulario({...formulario, show_in_home: e.target.checked})}
                      style={{
                        width: '20px',
                        height: '20px',
                        cursor: 'pointer',
                        accentColor: '#667eea'
                      }}
                    />
                    <span style={{ 
                      fontSize: '1rem', 
                      fontWeight: '600',
                      color: '#374151'
                    }}>
                      Mostrar en inicio
                    </span>
                  </label>
                </div>
                <div style={{ display: 'flex', gap: '1rem', justifyContent: 'flex-end', marginTop: '1.5rem' }}>
                  <button
                    type="button"
                    onClick={cerrarModal}
                    style={{
                      padding: '0.75rem 1.5rem',
                      background: '#f3f4f6',
                      color: '#374151',
                      border: 'none',
                      borderRadius: '8px',
                      cursor: 'pointer',
                      fontSize: '1rem',
                      fontWeight: '600'
                    }}
                  >
                    Cancelar
                  </button>
                  <button
                    type="submit"
                    style={{
                      padding: '0.75rem 1.5rem',
                      background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                      color: 'white',
                      border: 'none',
                      borderRadius: '8px',
                      cursor: 'pointer',
                      fontSize: '1rem',
                      fontWeight: '600'
                    }}
                  >
                    {comunicadoEditando ? 'Guardar Cambios' : 'Crear Comunicado'}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>,
        document.body
      )}
    </DashboardLayout>
  );
}

export default AdminComunicados;
