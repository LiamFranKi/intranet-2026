import React, { useState, useEffect, useRef } from 'react';
import { createPortal } from 'react-dom';
import { useNavigate } from 'react-router-dom';
import DashboardLayout from '../components/DashboardLayout';
import api from '../services/api';
import './DocenteCursos.css';

function DocenteCursos() {
  const navigate = useNavigate();
  const [loading, setLoading] = useState(true);
  const [cursos, setCursos] = useState([]);
  const [selectedCurso, setSelectedCurso] = useState(null);
  const [openDropdown, setOpenDropdown] = useState(null);
  const [dropdownPosition, setDropdownPosition] = useState(null);
  const dropdownRef = useRef({});
  const buttonRef = useRef({});

  useEffect(() => {
    cargarCursos();
  }, []);

  useEffect(() => {
    const handleClickOutside = (event) => {
      // Solo cerrar si es click del botón izquierdo (button === 0)
      // Ignorar botón del medio (wheel, button === 1) y botón derecho (button === 2)
      if (event.button !== 0) return;
      
      if (openDropdown !== null) {
        const dropdownElement = document.querySelector('.dropdown-menu-portal');
        const buttonElement = buttonRef.current[openDropdown];
        
        // Verificar si el click fue fuera del dropdown y del botón
        if (
          dropdownElement && 
          !dropdownElement.contains(event.target) &&
          buttonElement &&
          !buttonElement.contains(event.target)
        ) {
          setOpenDropdown(null);
          setDropdownPosition(null);
        }
      }
    };

    if (openDropdown !== null) {
      // Usar mousedown en lugar de click para mejor control
      setTimeout(() => {
        document.addEventListener('mousedown', handleClickOutside);
      }, 0);
      
      return () => {
        document.removeEventListener('mousedown', handleClickOutside);
      };
    }
  }, [openDropdown]);

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
    setOpenDropdown(null); // Cerrar dropdown al seleccionar una opción
    setDropdownPosition(null);
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
      case 'enlaces':
        navigate(`/docente/cursos/${curso.id}/enlaces`);
        break;
      case 'copiar':
        // TODO: Implementar funcionalidad de copiar contenido
        console.log('Copiar contenido del curso:', curso.id);
        break;
      default:
        break;
    }
  };

  const toggleDropdown = (cursoId, event) => {
    if (openDropdown === cursoId) {
      setOpenDropdown(null);
      setDropdownPosition(null);
    } else {
      const button = event.currentTarget;
      const rect = button.getBoundingClientRect();
      setDropdownPosition({
        top: rect.bottom + 8,
        left: rect.left,
        width: rect.width
      });
      setOpenDropdown(cursoId);
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
                  <div className="curso-icon">
                    {curso.curso_imagen_url ? (
                      <img 
                        src={curso.curso_imagen_url} 
                        alt={curso.curso_nombre}
                        className="curso-imagen"
                        onError={(e) => {
                          // Si la imagen falla al cargar, mostrar el emoji
                          e.target.style.display = 'none';
                          if (e.target.nextSibling) {
                            e.target.nextSibling.style.display = 'flex';
                          }
                        }}
                      />
                    ) : null}
                    {!curso.curso_imagen_url && <span className="curso-emoji">📚</span>}
                    {curso.curso_imagen_url && (
                      <span className="curso-emoji" style={{ display: 'none' }}>📚</span>
                    )}
                  </div>
                  <h3 className="curso-nombre">{curso.curso_nombre}</h3>
                </div>
                
                <div className="curso-info-compact">
                  <span className="info-compact-text">
                    {curso.nivel_nombre} - {curso.grado}° {curso.seccion} - {curso.turno_nombre}
                  </span>
                </div>

                <div className="curso-actions">
                  <button
                    className="btn-action-primary"
                    onClick={() => handleCursoAction(curso, 'aula')}
                  >
                    🎓 Aula Virtual
                  </button>
                  <div 
                    className="dropdown-options"
                    ref={(el) => (dropdownRef.current[curso.id] = el)}
                  >
                    <button 
                      ref={(el) => (buttonRef.current[curso.id] = el)}
                      className="btn-options-toggle"
                      onClick={(e) => {
                        e.stopPropagation();
                        toggleDropdown(curso.id, e);
                      }}
                    >
                      Opciones {openDropdown === curso.id ? '▲' : '▼'}
                    </button>
                    {openDropdown === curso.id && dropdownPosition && createPortal(
                      <div 
                        className="dropdown-menu-portal"
                        style={{
                          position: 'fixed',
                          top: `${dropdownPosition.top}px`,
                          left: `${dropdownPosition.left}px`,
                          width: `${dropdownPosition.width}px`,
                          zIndex: 10000
                        }}
                        onMouseDown={(e) => e.stopPropagation()}
                        onClick={(e) => e.stopPropagation()}
                      >
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
                          <button onClick={() => handleCursoAction(curso, 'enlaces')}>
                            🔗 Link Aula Virtual
                          </button>
                          <button onClick={() => handleCursoAction(curso, 'copiar')}>
                            📋 Copiar Contenido
                          </button>
                        </div>
                      </div>,
                      document.body
                    )}
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

