import React, { useState, useEffect } from 'react';
import DashboardLayout from '../components/DashboardLayout';
import api from '../services/api';
import './DocenteComunicados.css';

function DocenteComunicados() {
  const [loading, setLoading] = useState(true);
  const [comunicados, setComunicados] = useState([]);

  useEffect(() => {
    cargarComunicados();
  }, []);

  const cargarComunicados = async () => {
    try {
      setLoading(true);
      const response = await api.get('/docente/comunicados');
      setComunicados(response.data.comunicados || []);
    } catch (error) {
      console.error('Error cargando comunicados:', error);
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
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
        <div className="page-header">
          <h1>Comunicados</h1>
          <p>Comunicados enviados por la administración</p>
        </div>

        <div className="comunicados-list">
          {comunicados.length > 0 ? (
            comunicados.map((comunicado) => (
              <div key={comunicado.id} className="comunicado-card mundo-card">
                <div className="comunicado-header">
                  <h3>{comunicado.descripcion}</h3>
                  <span className="comunicado-fecha">
                    {new Date(comunicado.fecha_hora).toLocaleDateString('es-PE', {
                      year: 'numeric',
                      month: 'long',
                      day: 'numeric',
                      hour: '2-digit',
                      minute: '2-digit'
                    })}
                  </span>
                </div>
                <div className="comunicado-contenido">
                  <div dangerouslySetInnerHTML={{ __html: comunicado.contenido }} />
                  {comunicado.tipo === 'ARCHIVO' && comunicado.archivo && (
                    <div className="comunicado-archivo">
                      <a href={comunicado.archivo} target="_blank" rel="noopener noreferrer">
                        📎 Descargar archivo
                      </a>
                    </div>
                  )}
                </div>
              </div>
            ))
          ) : (
            <div className="empty-state mundo-card">
              <p>No hay comunicados disponibles</p>
            </div>
          )}
        </div>
      </div>
    </DashboardLayout>
  );
}

export default DocenteComunicados;

