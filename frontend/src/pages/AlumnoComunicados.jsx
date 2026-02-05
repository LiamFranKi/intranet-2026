import React, { useState, useEffect } from 'react';
import DashboardLayout from '../components/DashboardLayout';
import api from '../services/api';
import { normalizeStaticFileUrl } from '../config/staticFiles';
import './AlumnoComunicados.css';

function AlumnoComunicados() {
  const [loading, setLoading] = useState(true);
  const [comunicados, setComunicados] = useState([]);
  const [searchTerm, setSearchTerm] = useState('');
  const [currentPage, setCurrentPage] = useState(1);
  const [comunicadosLeidos, setComunicadosLeidos] = useState(new Set());
  const [pagination, setPagination] = useState({
    total: 0,
    page: 1,
    limit: 12,
    totalPages: 1
  });

  // Cargar comunicados leídos desde localStorage
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
      const response = await api.get('/alumno/comunicados', { params });
      const comunicadosData = response.data.comunicados || [];
      
      setComunicados(comunicadosData);
      setPagination(response.data.pagination || pagination);
    } catch (error) {
      console.error('Error cargando comunicados:', error);
    } finally {
      setLoading(false);
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
      
      let urlFinal = normalizeStaticFileUrl(archivoUrl);
      
      if (!urlFinal) {
        console.error('❌ No se pudo normalizar la URL:', archivoUrl);
        return;
      }
      
      try {
        const urlObj = new URL(urlFinal);
        
        const link = document.createElement('a');
        link.href = urlObj.href;
        link.target = '_blank';
        link.rel = 'noopener noreferrer';
        link.style.position = 'fixed';
        link.style.top = '-9999px';
        link.style.left = '-9999px';
        document.body.appendChild(link);
        
        link.click();
        
        setTimeout(() => {
          document.body.removeChild(link);
        }, 100);
        
      } catch (error) {
        console.error('❌ Error al procesar URL:', error);
        alert(`Error al abrir el archivo. URL: ${urlFinal}`);
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
        <div className="alumno-comunicados-loading">
          <div className="loading-spinner">Cargando comunicados...</div>
        </div>
      </DashboardLayout>
    );
  }

  return (
    <DashboardLayout>
      <div className="alumno-comunicados">
        <div className="page-header">
          <h1>Comunicados</h1>
          <p>Comunicados y anuncios oficiales del colegio</p>
        </div>

        {/* Barra de búsqueda */}
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

        {/* Grid de cards */}
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

            {/* Paginación */}
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

export default AlumnoComunicados;

