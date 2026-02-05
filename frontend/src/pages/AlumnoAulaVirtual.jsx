import React, { useState, useEffect, useCallback } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import DashboardLayout from '../components/DashboardLayout';
import api from '../services/api';
import './DocenteAulaVirtual-gamificado.css';

function AlumnoAulaVirtual() {
  const { cursoId } = useParams(); // asignatura_id
  const navigate = useNavigate();
  const asignaturaId = parseInt(cursoId);
  
  const [loading, setLoading] = useState(true);
  const [curso, setCurso] = useState(null);
  const [totalNotas, setTotalNotas] = useState(4);
  
  const [bimestreGlobal, setBimestreGlobal] = useState(1);
  const [archivos, setArchivos] = useState([]);
  const [tareas, setTareas] = useState([]);
  const [examenes, setExamenes] = useState([]);
  const [videos, setVideos] = useState([]);
  const [enlaces, setEnlaces] = useState([]);
  
  // Estado para card expandido
  const [expandedCard, setExpandedCard] = useState(null);

  const toggleCard = (cardName) => {
    setExpandedCard(expandedCard === cardName ? null : cardName);
  };

  const cargarDatosCurso = useCallback(async () => {
    try {
      // Obtener información del curso desde la lista de cursos
      const response = await api.get('/alumno/cursos');
      const cursoEncontrado = response.data.cursos.find(c => c.asignatura_id === asignaturaId);
      setCurso(cursoEncontrado || null);
      setTotalNotas(4); // Por defecto 4 bimestres
    } catch (error) {
      console.error('Error cargando curso:', error);
    }
  }, [asignaturaId]);

  const cargarArchivos = useCallback(async (ciclo) => {
    try {
      const response = await api.get('/alumno/aula-virtual/archivos', {
        params: { asignatura_id: asignaturaId, ciclo: ciclo || bimestreGlobal }
      });
      setArchivos(response.data.archivos || []);
    } catch (error) {
      console.error('Error cargando archivos:', error);
    }
  }, [asignaturaId, bimestreGlobal]);

  const cargarTareas = useCallback(async (ciclo) => {
    try {
      const response = await api.get('/alumno/aula-virtual/tareas', {
        params: { asignatura_id: asignaturaId, ciclo: ciclo || bimestreGlobal }
      });
      setTareas(response.data.tareas || []);
    } catch (error) {
      console.error('Error cargando tareas:', error);
    }
  }, [asignaturaId, bimestreGlobal]);

  const cargarExamenes = useCallback(async (ciclo) => {
    try {
      const response = await api.get('/alumno/aula-virtual/examenes', {
        params: { asignatura_id: asignaturaId, ciclo: ciclo || bimestreGlobal }
      });
      setExamenes(response.data.examenes || []);
    } catch (error) {
      console.error('Error cargando exámenes:', error);
    }
  }, [asignaturaId, bimestreGlobal]);

  const cargarVideos = useCallback(async (ciclo) => {
    try {
      const response = await api.get('/alumno/aula-virtual/videos', {
        params: { asignatura_id: asignaturaId, ciclo: ciclo || bimestreGlobal }
      });
      setVideos(response.data.videos || []);
    } catch (error) {
      console.error('Error cargando videos:', error);
    }
  }, [asignaturaId, bimestreGlobal]);

  const cargarEnlaces = useCallback(async (ciclo) => {
    try {
      const response = await api.get('/alumno/aula-virtual/enlaces', {
        params: { asignatura_id: asignaturaId, ciclo: ciclo || bimestreGlobal }
      });
      setEnlaces(response.data.enlaces || []);
    } catch (error) {
      console.error('Error cargando enlaces:', error);
    }
  }, [asignaturaId, bimestreGlobal]);

  // Cargar datos del curso
  useEffect(() => {
    if (asignaturaId) {
      setLoading(true);
      cargarDatosCurso().finally(() => setLoading(false));
    }
  }, [asignaturaId, cargarDatosCurso]);

  // Cargar todos los datos cuando cambia el bimestre global
  useEffect(() => {
    if (!asignaturaId || loading) return;
    
    cargarArchivos(bimestreGlobal);
    cargarTareas(bimestreGlobal);
    cargarExamenes(bimestreGlobal);
    cargarVideos(bimestreGlobal);
    cargarEnlaces(bimestreGlobal);
  }, [asignaturaId, loading, bimestreGlobal, cargarArchivos, cargarTareas, cargarExamenes, cargarVideos, cargarEnlaces]);

  const renderCardContent = (cardName) => {
    switch(cardName) {
      case 'temas':
        return renderTemasContent();
      case 'tareas':
        return renderTareasContent();
      case 'examenes':
        return renderExamenesContent();
      case 'videos':
        return renderVideosContent();
      case 'enlaces':
        return renderEnlacesContent();
      default:
        return null;
    }
  };

  const renderTemasContent = () => {
    const archivosFiltrados = archivos.filter(archivo => archivo.ciclo === bimestreGlobal);

    return (
      <div className="card-content-expanded">
        {loading ? (
          <div className="empty-state">
            <p>Cargando temas interactivos...</p>
          </div>
        ) : archivosFiltrados.length > 0 ? (
          <table className="tabla-aula-virtual">
            <thead>
              <tr>
                <th>NOMBRE</th>
                <th>FECHA</th>
                <th className="text-center">ACCIONES</th>
              </tr>
            </thead>
            <tbody>
              {archivosFiltrados.map((archivo) => (
                <tr key={archivo.id}>
                  <td>{archivo.nombre}</td>
                  <td>
                    {archivo.fecha_hora ? (
                      new Date(archivo.fecha_hora).toLocaleDateString('es-PE', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric'
                      })
                    ) : (
                      '-'
                    )}
                  </td>
                  <td className="text-center">
                    <div style={{ display: 'flex', gap: '0.5rem', justifyContent: 'center' }}>
                      {archivo.archivo_url && (
                        <a 
                          href={archivo.archivo_url} 
                          target="_blank" 
                          rel="noopener noreferrer"
                          style={{
                            padding: '0.5rem 1rem',
                            background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                            color: 'white',
                            textDecoration: 'none',
                            borderRadius: '8px',
                            fontSize: '0.875rem',
                            fontWeight: '600',
                            transition: 'all 0.2s'
                          }}
                        >
                          📄 Ver Archivo
                        </a>
                      )}
                      {archivo.enlace_url && (
                        <a 
                          href={archivo.enlace_url} 
                          target="_blank" 
                          rel="noopener noreferrer"
                          style={{
                            padding: '0.5rem 1rem',
                            background: 'linear-gradient(135deg, #4a83c1 0%, #2d5a8f 100%)',
                            color: 'white',
                            textDecoration: 'none',
                            borderRadius: '8px',
                            fontSize: '0.875rem',
                            fontWeight: '600',
                            transition: 'all 0.2s'
                          }}
                        >
                          🔗 Abrir URL
                        </a>
                      )}
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        ) : (
          <div className="empty-state">
            <p>No hay temas interactivos para este bimestre</p>
          </div>
        )}
      </div>
    );
  };

  const renderTareasContent = () => {
    const tareasFiltradas = tareas.filter(tarea => tarea.ciclo === bimestreGlobal);

    return (
      <div className="card-content-expanded">
        {loading ? (
          <div className="empty-state">
            <p>Cargando tareas...</p>
          </div>
        ) : tareasFiltradas.length > 0 ? (
          <table className="tabla-aula-virtual">
            <thead>
              <tr>
                <th>NOMBRE</th>
                <th>FECHA DE REGISTRO</th>
                <th>FECHA DE ENTREGA</th>
              </tr>
            </thead>
            <tbody>
              {tareasFiltradas.map((tarea) => (
                <tr key={tarea.id}>
                  <td>{tarea.titulo}</td>
                  <td className="text-center">
                    {tarea.fecha_hora ? (
                      new Date(tarea.fecha_hora).toLocaleDateString('es-PE')
                    ) : (
                      '-'
                    )}
                  </td>
                  <td className="text-center">
                    {tarea.fecha_entrega ? (
                      new Date(tarea.fecha_entrega).toLocaleDateString('es-PE')
                    ) : (
                      '-'
                    )}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        ) : (
          <div className="empty-state">
            <p>No hay tareas para este bimestre</p>
          </div>
        )}
      </div>
    );
  };

  const renderExamenesContent = () => {
    const examenesFiltrados = examenes.filter(examen => examen.ciclo === bimestreGlobal);

    return (
      <div className="card-content-expanded">
        {loading ? (
          <div className="empty-state">
            <p>Cargando exámenes...</p>
          </div>
        ) : examenesFiltrados.length > 0 ? (
          <table className="tabla-aula-virtual">
            <thead>
              <tr>
                <th>TÍTULO</th>
                <th>TIPO</th>
                <th>ESTADO</th>
              </tr>
            </thead>
            <tbody>
              {examenesFiltrados.map((examen) => (
                <tr key={examen.id}>
                  <td>{examen.titulo}</td>
                  <td className="text-center">{examen.tipo || '-'}</td>
                  <td className="text-center">{examen.estado || '-'}</td>
                </tr>
              ))}
            </tbody>
          </table>
        ) : (
          <div className="empty-state">
            <p>No hay exámenes para este bimestre</p>
          </div>
        )}
      </div>
    );
  };

  const renderVideosContent = () => {
    const videosFiltrados = videos.filter(v => v.ciclo === bimestreGlobal);

    return (
      <div className="card-content-expanded">
        {loading ? (
          <div className="empty-state">
            <p>Cargando videos...</p>
          </div>
        ) : videosFiltrados.length > 0 ? (
          <table className="tabla-aula-virtual">
            <thead>
              <tr>
                <th>NOMBRE</th>
                <th>FECHA</th>
                <th className="text-center">ACCIONES</th>
              </tr>
            </thead>
            <tbody>
              {videosFiltrados.map((video) => (
                <tr key={video.id}>
                  <td>{video.descripcion}</td>
                  <td>
                    {video.fecha_hora ? (
                      new Date(video.fecha_hora).toLocaleString('es-PE', { 
                        day: '2-digit', 
                        month: '2-digit', 
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                      })
                    ) : (
                      '-'
                    )}
                  </td>
                  <td className="text-center">
                    {video.enlace && (
                      <a 
                        href={video.enlace} 
                        target="_blank" 
                        rel="noopener noreferrer"
                        style={{
                          padding: '0.5rem 1rem',
                          background: 'linear-gradient(135deg, #14b8a6 0%, #0d9488 100%)',
                          color: 'white',
                          textDecoration: 'none',
                          borderRadius: '8px',
                          fontSize: '0.875rem',
                          fontWeight: '600',
                          transition: 'all 0.2s'
                        }}
                      >
                        🎥 Ver Video
                      </a>
                    )}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        ) : (
          <div className="empty-state">
            <p>No hay videos para este bimestre</p>
          </div>
        )}
      </div>
    );
  };

  const renderEnlacesContent = () => {
    const enlacesFiltrados = enlaces.filter(e => e.ciclo === bimestreGlobal);

    return (
      <div className="card-content-expanded">
        {loading ? (
          <div className="empty-state">
            <p>Cargando enlaces...</p>
          </div>
        ) : enlacesFiltrados.length > 0 ? (
          <table className="tabla-aula-virtual">
            <thead>
              <tr>
                <th>NOMBRE</th>
                <th>FECHA</th>
                <th className="text-center">ACCIONES</th>
              </tr>
            </thead>
            <tbody>
              {enlacesFiltrados.map((enlace) => (
                <tr key={enlace.id}>
                  <td>{enlace.descripcion}</td>
                  <td>
                    {enlace.fecha_hora ? (
                      new Date(enlace.fecha_hora).toLocaleString('es-PE', { 
                        day: '2-digit', 
                        month: '2-digit', 
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                      })
                    ) : (
                      '-'
                    )}
                  </td>
                  <td className="text-center">
                    {enlace.enlace && (
                      <a 
                        href={enlace.enlace} 
                        target="_blank" 
                        rel="noopener noreferrer"
                        style={{
                          padding: '0.5rem 1rem',
                          background: 'linear-gradient(135deg, #06b6d4 0%, #0891b2 100%)',
                          color: 'white',
                          textDecoration: 'none',
                          borderRadius: '8px',
                          fontSize: '0.875rem',
                          fontWeight: '600',
                          transition: 'all 0.2s'
                        }}
                      >
                        🔗 Visitar Enlace
                      </a>
                    )}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        ) : (
          <div className="empty-state">
            <p>No hay enlaces para este bimestre</p>
          </div>
        )}
      </div>
    );
  };

  if (loading) {
    return (
      <DashboardLayout>
        <div className="docente-aula-loading">
          <div className="loading-spinner">Cargando aula virtual...</div>
        </div>
      </DashboardLayout>
    );
  }

  return (
    <DashboardLayout>
      <div className="docente-aula-virtual">
        <div className="page-header">
          <button className="btn-back" onClick={() => navigate('/alumno/cursos')}>
            ← Volver a Cursos
          </button>
          <h1>🎓 Aula Virtual</h1>
          <p>{curso?.curso_nombre || 'Curso'} - {curso?.docente_nombre || 'Docente'}</p>
        </div>

        {/* Selector Global de Bimestre */}
        <div className="bimestre-selector-global">
          {Array.from({ length: totalNotas }, (_, i) => i + 1).map((bim) => (
            <button
              key={bim}
              className={`bimestre-tab-global ${bimestreGlobal === bim ? 'active' : ''}`}
              onClick={() => setBimestreGlobal(bim)}
            >
              Bimestre {bim}
            </button>
          ))}
        </div>

        {/* Grid de Cards Gamificado */}
        <div className="aula-dashboard-grid">
          {/* Card: TEMAS */}
          <div 
            className={`aula-section-card temas-card ${expandedCard === 'temas' ? 'expanded' : ''}`}
            onClick={() => expandedCard !== 'temas' && toggleCard('temas')}
          >
            <div className="card-header">
              <div className="card-icon">📚</div>
              <div className="card-info">
                <h3 className="card-title">Temas Interactivos</h3>
                <p className="card-count">{archivos.filter(a => a.ciclo === bimestreGlobal).length}</p>
                <p className="card-subtitle">Temas disponibles</p>
              </div>
            </div>
            {expandedCard === 'temas' && renderCardContent('temas')}
          </div>

          {/* Card: TAREAS */}
          <div 
            className={`aula-section-card tareas-card ${expandedCard === 'tareas' ? 'expanded' : ''}`}
            onClick={() => expandedCard !== 'tareas' && toggleCard('tareas')}
          >
            <div className="card-header">
              <div className="card-icon">📝</div>
              <div className="card-info">
                <h3 className="card-title">Tareas Virtuales</h3>
                <p className="card-count">{tareas.filter(t => t.ciclo === bimestreGlobal).length}</p>
                <p className="card-subtitle">Tareas asignadas</p>
              </div>
            </div>
            {expandedCard === 'tareas' && renderCardContent('tareas')}
          </div>

          {/* Card: EXÁMENES */}
          <div 
            className={`aula-section-card examenes-card ${expandedCard === 'examenes' ? 'expanded' : ''}`}
            onClick={() => expandedCard !== 'examenes' && toggleCard('examenes')}
          >
            <div className="card-header">
              <div className="card-icon">📊</div>
              <div className="card-info">
                <h3 className="card-title">Exámenes</h3>
                <p className="card-count">{examenes.filter(e => e.ciclo === bimestreGlobal).length}</p>
                <p className="card-subtitle">Exámenes creados</p>
              </div>
            </div>
            {expandedCard === 'examenes' && renderCardContent('examenes')}
          </div>

          {/* Card: VIDEOTECA */}
          <div 
            className={`aula-section-card videos-card ${expandedCard === 'videos' ? 'expanded' : ''}`}
            onClick={() => expandedCard !== 'videos' && toggleCard('videos')}
          >
            <div className="card-header">
              <div className="card-icon">🎥</div>
              <div className="card-info">
                <h3 className="card-title">Videoteca</h3>
                <p className="card-count">{videos.filter(v => v.ciclo === bimestreGlobal).length}</p>
                <p className="card-subtitle">Videos disponibles</p>
              </div>
            </div>
            {expandedCard === 'videos' && renderCardContent('videos')}
          </div>

          {/* Card: ENLACES */}
          <div 
            className={`aula-section-card enlaces-card ${expandedCard === 'enlaces' ? 'expanded' : ''}`}
            onClick={() => expandedCard !== 'enlaces' && toggleCard('enlaces')}
          >
            <div className="card-header">
              <div className="card-icon">🔗</div>
              <div className="card-info">
                <h3 className="card-title">Enlaces de Ayuda</h3>
                <p className="card-count">{enlaces.filter(e => e.ciclo === bimestreGlobal).length}</p>
                <p className="card-subtitle">Enlaces compartidos</p>
              </div>
            </div>
            {expandedCard === 'enlaces' && renderCardContent('enlaces')}
          </div>
        </div>
      </div>
    </DashboardLayout>
  );
}

export default AlumnoAulaVirtual;
