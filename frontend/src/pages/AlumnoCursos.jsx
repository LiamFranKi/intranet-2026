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
                {/* Imagen del curso como cabecera */}
                {curso.curso_imagen_url && (
                  <div className="curso-imagen-header">
                    <img 
                      src={curso.curso_imagen_url} 
                      alt={curso.curso_nombre}
                      className="curso-imagen-cabecera"
                      onError={(e) => {
                        e.target.style.display = 'none';
                      }}
                    />
                  </div>
                )}
                
                {/* Contenido del card */}
                <div className="curso-content">
                  {/* Icono circular si no hay imagen de cabecera */}
                  {!curso.curso_imagen_url && (
                    <div className="curso-icon-circle">
                      {curso.curso_imagen_url ? (
                        <img 
                          src={curso.curso_imagen_url} 
                          alt={curso.curso_nombre}
                          className="curso-imagen-circle"
                          onError={(e) => {
                            e.target.style.display = 'none';
                            if (e.target.nextSibling) {
                              e.target.nextSibling.style.display = 'flex';
                            }
                          }}
                        />
                      ) : null}
                      {!curso.curso_imagen_url && <span className="curso-emoji-circle">📚</span>}
                      {curso.curso_imagen_url && (
                        <span className="curso-emoji-circle" style={{ display: 'none' }}>📚</span>
                      )}
                    </div>
                  )}
                  
                  <h3 className="curso-nombre-alumno">{curso.curso_nombre}</h3>
                  
                  <div className="curso-docente-info">
                    <span className="docente-icon">👨‍🏫</span>
                    <span className="docente-nombre">{curso.docente_nombre || 'Sin docente asignado'}</span>
                  </div>

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
                      ✉️ Enviar Mensaje Docente
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

