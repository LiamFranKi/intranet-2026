import React, { useState, useEffect, useCallback } from 'react';
import DashboardLayout from '../components/DashboardLayout';
import api from '../services/api';
import './DocenteActividades.css';

function DocenteActividades() {
  const [loading, setLoading] = useState(true);
  const [actividades, setActividades] = useState([]);
  const [fechaSeleccionada] = useState(new Date());

  const cargarActividades = useCallback(async () => {
    try {
      setLoading(true);
      const fechaStr = fechaSeleccionada.toISOString().split('T')[0];
      const response = await api.get('/docente/actividades', {
        params: { fecha: fechaStr }
      });
      setActividades(response.data.actividades || []);
    } catch (error) {
      console.error('Error cargando actividades:', error);
    } finally {
      setLoading(false);
    }
  }, [fechaSeleccionada]);

  useEffect(() => {
    cargarActividades();
  }, [cargarActividades]);

  if (loading) {
    return (
      <DashboardLayout>
        <div className="docente-actividades-loading">
          <div className="loading-spinner">Cargando actividades...</div>
        </div>
      </DashboardLayout>
    );
  }

  return (
    <DashboardLayout>
      <div className="docente-actividades">
        <div className="page-header">
          <h1>Actividades</h1>
          <p>Calendario de actividades del año académico</p>
        </div>

        <div className="actividades-container">
          <div className="calendario-section mundo-card">
            {/* Calendario aquí */}
            <p>Calendario de actividades (solo lectura)</p>
          </div>

          <div className="actividades-list-section">
            {actividades.length > 0 ? (
              actividades.map((actividad) => (
                <div key={actividad.id} className="actividad-card mundo-card">
                  <div className="actividad-fecha">
                    {new Date(actividad.fecha_inicio).toLocaleDateString('es-PE')}
                    {actividad.fecha_fin && actividad.fecha_fin !== actividad.fecha_inicio && (
                      <> - {new Date(actividad.fecha_fin).toLocaleDateString('es-PE')}</>
                    )}
                  </div>
                  <h3>{actividad.descripcion}</h3>
                  <p className="actividad-lugar">📍 {actividad.lugar || 'Sin lugar especificado'}</p>
                  {actividad.detalles && <p className="actividad-detalles">{actividad.detalles}</p>}
                </div>
              ))
            ) : (
              <div className="empty-state mundo-card">
                <p>No hay actividades programadas</p>
              </div>
            )}
          </div>
        </div>
      </div>
    </DashboardLayout>
  );
}

export default DocenteActividades;

