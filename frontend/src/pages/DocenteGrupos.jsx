import React, { useState, useEffect } from 'react';
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
  const [alumnos, setAlumnos] = useState([]);
  const [loadingAlumnos, setLoadingAlumnos] = useState(false);

  useEffect(() => {
    cargarGrupos();
  }, []);

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
    } catch (error) {
      console.error('Error cargando alumnos:', error);
    } finally {
      setLoadingAlumnos(false);
    }
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
          <p>Lista de grupos a tu cargo en el año académico actual</p>
        </div>

        <div className={`grupos-container ${selectedGrupo ? 'with-alumnos' : ''}`}>
          <div className="grupos-list-section">
            <div className="section-controls">
              <div className="filter-container">
                <label>Filtrar:</label>
                <input
                  type="text"
                  placeholder="Buscar por grado, sección, nivel..."
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
                    <th>AÑO ACADÉMICO</th>
                    <th>Opciones</th>
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
                        <td>{grupo.anio}</td>
                        <td>
                          <button
                            className="btn-options"
                            onClick={() => cargarAlumnos(grupo.id)}
                            title="Ver lista de alumnos"
                          >
                            Lista de Alumnos
                          </button>
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

          {selectedGrupo && (
            <div className="alumnos-section mundo-card">
              <div className="section-header">
                <h2>Lista de Alumnos</h2>
                <button className="btn-close" onClick={() => setSelectedGrupo(null)}>✕</button>
              </div>
              
              {loadingAlumnos ? (
                <div className="loading-alumnos">Cargando alumnos...</div>
              ) : (
                <div className="alumnos-table-container">
                  <table className="alumnos-table">
                    <thead>
                      <tr>
                        <th>APELLIDOS Y NOMBRES</th>
                        <th>FECHA DE REGISTRO</th>
                        <th>ESTADO</th>
                        <th>Opciones</th>
                      </tr>
                    </thead>
                    <tbody>
                      {alumnos.length > 0 ? (
                        alumnos.map((alumno) => (
                          <tr key={alumno.id}>
                            <td>
                              {alumno.apellido_paterno} {alumno.apellido_materno}, {alumno.nombres}
                            </td>
                            <td>
                              {alumno.fecha_registro 
                                ? new Date(alumno.fecha_registro).toLocaleDateString('es-PE')
                                : 'N/A'}
                            </td>
                            <td>
                              <span className={`estado-badge ${alumno.estado_matricula === 0 ? 'regular' : 'inactivo'}`}>
                                {alumno.estado_matricula === 0 ? 'REGULAR' : 'INACTIVO'}
                              </span>
                            </td>
                            <td>
                              <div className="alumno-options">
                                <button className="btn-action" title="Enviar Recomendación">
                                  ✉️ Recomendación
                                </button>
                                <button className="btn-action" title="Ver Información">
                                  👤 Ver Info
                                </button>
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
          )}
        </div>
      </div>
    </DashboardLayout>
  );
}

export default DocenteGrupos;

