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
            {cursos.map((curso, index) => {
              // Colores pastel suaves para cada card
              const coloresPastel = [
                'linear-gradient(145deg, #fef3f2, #fff5f5)', // Rosa pastel
                'linear-gradient(145deg, #f0fdf4, #f0fff4)', // Verde pastel
                'linear-gradient(145deg, #eff6ff, #f0f9ff)', // Azul pastel
                'linear-gradient(145deg, #faf5ff, #faf5ff)', // Púrpura pastel
                'linear-gradient(145deg, #fff7ed, #fffaf0)', // Naranja pastel
                'linear-gradient(145deg, #f0fdfa, #f0fffe)', // Turquesa pastel
                'linear-gradient(145deg, #fefce8, #fffff0)', // Amarillo pastel
                'linear-gradient(145deg, #fce7f3, #fdf2f8)', // Rosa claro pastel
              ];
              const colorFondo = coloresPastel[index % coloresPastel.length];
              
              return (
              <div 
                key={curso.asignatura_id} 
                className="curso-card-alumno mundo-card"
                style={{ background: colorFondo }}
              >
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
              );
            })}
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

