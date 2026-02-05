import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import DashboardLayout from '../components/DashboardLayout';
import api from '../services/api';
import './AlumnoCursos.css';
import './DocenteCursos.css';

function AlumnoCursos() {
  const navigate = useNavigate();
  const [loading, setLoading] = useState(true);
  const [cursos, setCursos] = useState([]);

  useEffect(() => {
    cargarCursos();
  }, []);

  const cargarCursos = async () => {
    try {
      setLoading(true);
      const response = await api.get('/alumno/cursos');
      setCursos(response.data.cursos || []);
    } catch (error) {
      console.error('Error cargando cursos:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleAulaVirtual = (curso) => {
    navigate(`/alumno/aula-virtual/${curso.asignatura_id}`);
  };

  const handleEnviarMensaje = (curso) => {
    // Navegar a mensajes con el docente pre-seleccionado
    navigate(`/alumno/mensajes?docente_id=${curso.docente_id}&curso=${encodeURIComponent(curso.curso_nombre)}`);
  };

  if (loading) {
    return (
      <DashboardLayout>
        <div className="alumno-cursos-loading">
          <div className="loading-spinner">Cargando cursos...</div>
        </div>
      </DashboardLayout>
    );
  }

  return (
    <DashboardLayout>
      <div className="alumno-cursos">
        <div className="page-header">
          <h1>📚 Mis Cursos</h1>
          <p>Gestiona tus cursos y accede al aula virtual</p>
        </div>

        {cursos.length > 0 ? (
          <div className="cursos-grid">
            {cursos.map((curso) => (
              <div key={curso.asignatura_id} className="curso-card-alumno mundo-card">
                {/* Contenido del card */}
                <div className="curso-content">
                  {/* Foto circular del docente arriba */}
                  <div className="docente-foto-container">
                    {curso.docente_foto_url ? (
                      <img 
                        src={curso.docente_foto_url} 
                        alt={curso.docente_nombre}
                        className="docente-foto-circular"
                        onError={(e) => {
                          e.target.style.display = 'none';
                          if (e.target.nextSibling) {
                            e.target.nextSibling.style.display = 'flex';
                          }
                        }}
                      />
                    ) : null}
                    {!curso.docente_foto_url && (
                      <div className="docente-foto-placeholder">
                        <span className="docente-icon-placeholder">👨‍🏫</span>
                      </div>
                    )}
                  </div>
                  
                  {/* Nombre del curso */}
                  <h3 className="curso-nombre-alumno">{curso.curso_nombre}</h3>
                  
                  {/* Nombre del docente */}
                  <div className="curso-docente-nombre">
                    <span className="docente-nombre-text">{curso.docente_nombre || 'Sin docente asignado'}</span>
                  </div>

                  {/* Botones de acción */}
                  <div className="curso-actions-alumno">
                    <button
                      className="btn-aula-virtual"
                      onClick={() => handleAulaVirtual(curso)}
                    >
                      🎓 Aula Virtual
                    </button>
                    <button
                      className="btn-enviar-mensaje"
                      onClick={() => handleEnviarMensaje(curso)}
                    >
                      ✉️ Enviar Mensaje
                    </button>
                  </div>
                </div>
              </div>
            ))}
          </div>
        ) : (
          <div className="empty-state mundo-card">
            <div className="empty-icon">📚</div>
            <p>No tienes cursos asignados para el año académico actual</p>
          </div>
        )}
      </div>
    </DashboardLayout>
  );
}

export default AlumnoCursos;

