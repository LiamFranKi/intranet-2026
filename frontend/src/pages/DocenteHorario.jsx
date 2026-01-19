import React, { useState, useEffect } from 'react';
import DashboardLayout from '../components/DashboardLayout';
import api from '../services/api';
import './DocenteHorario.css';

function DocenteHorario() {
  const [loading, setLoading] = useState(true);
  const [horario, setHorario] = useState([]);

  useEffect(() => {
    cargarHorario();
  }, []);

  const cargarHorario = async () => {
    try {
      setLoading(true);
      const response = await api.get('/docente/horario');
      setHorario(response.data.horario || []);
    } catch (error) {
      console.error('Error cargando horario:', error);
    } finally {
      setLoading(false);
    }
  };

  const diasSemana = [
    { id: 1, nombre: 'Lunes' },
    { id: 2, nombre: 'Martes' },
    { id: 3, nombre: 'Miércoles' },
    { id: 4, nombre: 'Jueves' },
    { id: 5, nombre: 'Viernes' },
    { id: 6, nombre: 'Sábado' }
  ];

  if (loading) {
    return (
      <DashboardLayout>
        <div className="docente-horario-loading">
          <div className="loading-spinner">Cargando horario...</div>
        </div>
      </DashboardLayout>
    );
  }

  return (
    <DashboardLayout>
      <div className="docente-horario">
        <div className="page-header">
          <h1>Mi Horario</h1>
          <p>Horario de clases del año académico actual</p>
        </div>

        <div className="horario-container mundo-card">
          {horario.length > 0 ? (
            <div className="horario-grid">
              {diasSemana.map((dia) => {
                const clasesDelDia = horario.filter((h) => h.dia === dia.id);
                return (
                  <div key={dia.id} className="dia-column">
                    <div className="dia-header">{dia.nombre}</div>
                    <div className="clases-list">
                      {clasesDelDia.length > 0 ? (
                        clasesDelDia.map((clase) => (
                          <div key={clase.id} className="clase-item">
                            <div className="clase-hora">{clase.inicio} - {clase.fin}</div>
                            <div className="clase-titulo">{clase.titulo}</div>
                            <div className="clase-grupo">{clase.grupo}</div>
                          </div>
                        ))
                      ) : (
                        <div className="clase-item empty-clase">
                          <span>Sin clases</span>
                        </div>
                      )}
                    </div>
                  </div>
                );
              })}
            </div>
          ) : (
            <div className="empty-state">
              <p>No se encontró horario para el año académico actual</p>
            </div>
          )}
        </div>
      </div>
    </DashboardLayout>
  );
}

export default DocenteHorario;

