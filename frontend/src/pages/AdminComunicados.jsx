import React, { useState, useEffect } from 'react';
import DashboardLayout from '../components/DashboardLayout';
import api from '../services/api';
import { normalizeStaticFileUrl } from '../config/staticFiles';
import { useAuth } from '../context/AuthContext';
import './DocenteComunicados.css';
import Swal from 'sweetalert2';

function AdminComunicados() {
  const { user } = useAuth();
  const [loading, setLoading] = useState(true);
  const [comunicados, setComunicados] = useState([]);
  const [searchTerm, setSearchTerm] = useState('');
  const [currentPage, setCurrentPage] = useState(1);
  const [comunicadosLeidos, setComunicadosLeidos] = useState(new Set());
  const [mostrarFormulario, setMostrarFormulario] = useState(false);
  const [formulario, setFormulario] = useState({
    descripcion: '',
    contenido: '',
    tipo: 'TEXTO',
    privacidad: '',
    show_in_home: false
  });
  const [archivoSeleccionado, setArchivoSeleccionado] = useState(null);
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

  const handleCrearComunicado = async (e) => {
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
        
        setFormulario({
          descripcion: '',
          contenido: '',
          tipo: 'TEXTO',
          privacidad: '',
          show_in_home: false
        });
        setArchivoSeleccionado(null);
        setMostrarFormulario(false);
        cargarComunicados();
      }
    } catch (error) {
      console.error('Error creando comunicado:', error);
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: error.response?.data?.error || 'Error al crear comunicado'
      });
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
            onClick={() => setMostrarFormulario(!mostrarFormulario)}
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

        {mostrarFormulario && (
          <div className="formulario-comunicado" style={{
            background: 'white',
            padding: '2rem',
            borderRadius: '16px',
            marginBottom: '2rem',
            boxShadow: '0 2px 8px rgba(0,0,0,0.1)'
          }}>
            <h2 style={{ marginBottom: '1.5rem', color: '#1f2937' }}>Nuevo Comunicado</h2>
            <form onSubmit={handleCrearComunicado}>
              <div style={{ marginBottom: '1rem' }}>
                <label style={{ display: 'block', marginBottom: '0.5rem', fontWeight: '600', color: '#374151' }}>
                  Descripción *
                </label>
                <input
                  type="text"
                  required
                  value={formulario.descripcion}
                  onChange={(e) => setFormulario({...formulario, descripcion: e.target.value})}
                  style={{
                    width: '100%',
                    padding: '0.75rem',
                    border: '1px solid #e5e7eb',
                    borderRadius: '8px',
                    fontSize: '1rem'
                  }}
                />
              </div>
              <div style={{ marginBottom: '1rem' }}>
                <label style={{ display: 'block', marginBottom: '0.5rem', fontWeight: '600', color: '#374151' }}>
                  Contenido
                </label>
                <textarea
                  value={formulario.contenido}
                  onChange={(e) => setFormulario({...formulario, contenido: e.target.value})}
                  rows="6"
                  style={{
                    width: '100%',
                    padding: '0.75rem',
                    border: '1px solid #e5e7eb',
                    borderRadius: '8px',
                    fontSize: '1rem',
                    resize: 'vertical'
                  }}
                />
              </div>
              <div style={{ marginBottom: '1rem' }}>
                <label style={{ display: 'block', marginBottom: '0.5rem', fontWeight: '600', color: '#374151' }}>
                  Tipo
                </label>
                <select
                  value={formulario.tipo}
                  onChange={(e) => setFormulario({...formulario, tipo: e.target.value})}
                  style={{
                    width: '100%',
                    padding: '0.75rem',
                    border: '1px solid #e5e7eb',
                    borderRadius: '8px',
                    fontSize: '1rem'
                  }}
                >
                  <option value="TEXTO">Texto</option>
                  <option value="ARCHIVO">Archivo</option>
                </select>
              </div>
              {formulario.tipo === 'ARCHIVO' && (
                <div style={{ marginBottom: '1rem' }}>
                  <label style={{ display: 'block', marginBottom: '0.5rem', fontWeight: '600', color: '#374151' }}>
                    Archivo
                  </label>
                  <input
                    type="file"
                    onChange={(e) => setArchivoSeleccionado(e.target.files[0])}
                    accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.rar,.jpg,.jpeg,.png,.gif"
                    style={{
                      width: '100%',
                      padding: '0.75rem',
                      border: '1px solid #e5e7eb',
                      borderRadius: '8px',
                      fontSize: '1rem'
                    }}
                  />
                </div>
              )}
              <div style={{ marginBottom: '1rem' }}>
                <label style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', cursor: 'pointer' }}>
                  <input
                    type="checkbox"
                    checked={formulario.show_in_home}
                    onChange={(e) => setFormulario({...formulario, show_in_home: e.target.checked})}
                  />
                  <span style={{ fontWeight: '600', color: '#374151' }}>Mostrar en inicio</span>
                </label>
              </div>
              <div style={{ display: 'flex', gap: '1rem', justifyContent: 'flex-end' }}>
                <button
                  type="button"
                  onClick={() => {
                    setMostrarFormulario(false);
                    setFormulario({
                      descripcion: '',
                      contenido: '',
                      tipo: 'TEXTO',
                      privacidad: '',
                      show_in_home: false
                    });
                    setArchivoSeleccionado(null);
                  }}
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
                  Crear Comunicado
                </button>
              </div>
            </form>
          </div>
        )}

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
                  <div className="comunicado-card-footer">
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
    </DashboardLayout>
  );
}

export default AdminComunicados;

