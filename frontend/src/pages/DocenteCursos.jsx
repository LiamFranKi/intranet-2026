import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import DashboardLayout from '../components/DashboardLayout';
import api from '../services/api';
import './DocenteCursos.css';

function DocenteCursos() {
  const navigate = useNavigate();
  const [loading, setLoading] = useState(true);
  const [cursos, setCursos] = useState([]);
  const [selectedCurso, setSelectedCurso] = useState(null);

  useEffect(() => {
    cargarCursos();
  }, []);

  const cargarCursos = async () => {
    try {
      setLoading(true);
      const response = await api.get('/docente/cursos');
      setCursos(response.data.cursos || []);
    } catch (error) {
      console.error('Error cargando cursos:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleCursoAction = (curso, action) => {
    switch (action) {
      case 'aula':
        navigate(`/docente/cursos/${curso.id}/aula`);
        break;
      case 'alumnos':
        navigate(`/docente/cursos/${curso.id}/alumnos`);
        break;
      case 'notas':
        navigate(`/docente/cursos/${curso.id}/notas`);
        break;
      case 'asistencia':
        navigate(`/docente/cursos/${curso.id}/asistencia`);
        break;
      default:
        break;
    }
  };

  if (loading) {
    return (
      <DashboardLayout>
        <div className="docente-cursos-loading">
          <div className="loading-spinner">Cargando cursos...</div>
        </div>
      </DashboardLayout>
    );
  }

  return (
    <DashboardLayout>
      <div className="docente-cursos">
        <div className="page-header">
          <h1>Cursos Asignados</h1>
          <p>Gestiona tus cursos y asignaturas del año académico actual</p>
        </div>

        {cursos.length > 0 ? (
          <div className="cursos-grid">
            {cursos.map((curso) => (
              <div key={curso.id} className="curso-card mundo-card">
                <div className="curso-header">
                  <div className="curso-icon">📚</div>
                  <h3 className="curso-nombre">{curso.nombre}</h3>
                </div>
                
                <div className="curso-info">
                  <div className="info-item">
                    <span className="info-label">Nivel:</span>
                    <span className="info-value">{curso.nivel_nombre}</span>
                  </div>
                  <div className="info-item">
                    <span className="info-label">Grado:</span>
                    <span className="info-value">{curso.grado}° {curso.seccion}</span>
                  </div>
                  <div className="info-item">
                    <span className="info-label">Turno:</span>
                    <span className="info-value">{curso.turno_nombre}</span>
                  </div>
                  <div className="info-item">
                    <span className="info-label">Año:</span>
                    <span className="info-value">{curso.anio}</span>
                  </div>
                </div>

                <div className="curso-actions">
                  <button
                    className="btn-action-primary"
                    onClick={() => handleCursoAction(curso, 'aula')}
                  >
                    🎓 Aula Virtual
                  </button>
                  <div className="dropdown-options">
                    <button className="btn-options-toggle">Opciones ▼</button>
                    <div className="dropdown-menu">
                      <button onClick={() => handleCursoAction(curso, 'alumnos')}>
                        👥 Lista de Alumnos
                      </button>
                      <button onClick={() => handleCursoAction(curso, 'notas')}>
                        📝 Registrar Notas
                      </button>
                      <button onClick={() => handleCursoAction(curso, 'asistencia')}>
                        ✅ Registrar Asistencia
                      </button>
                      <button onClick={() => handleCursoAction(curso, 'horario')}>
                        📅 Ver Horario
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            ))}
          </div>
        ) : (
          <div className="empty-state mundo-card">
            <p>No tienes cursos asignados para el año académico actual</p>
          </div>
        )}
      </div>
    </DashboardLayout>
  );
}

export default DocenteCursos;

