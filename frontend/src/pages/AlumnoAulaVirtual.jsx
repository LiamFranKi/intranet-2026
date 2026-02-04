import React, { useState, useEffect } from 'react';
import DashboardLayout from '../components/DashboardLayout';
import api from '../services/api';
import './AlumnoAulaVirtual.css';

function AlumnoAulaVirtual() {
  const [loading, setLoading] = useState(true);
  const [cursos, setCursos] = useState([]);
  const [cursoSeleccionado, setCursoSeleccionado] = useState(null);
  const [temas, setTemas] = useState([]);
  const [tareas, setTareas] = useState([]);
  const [examenes, setExamenes] = useState([]);
  const [ciclo, setCiclo] = useState(1);

  useEffect(() => {
    cargarCursos();
  }, []);

  useEffect(() => {
    if (cursoSeleccionado) {
      cargarContenidoCurso();
    }
  }, [cursoSeleccionado, ciclo]);

  const cargarCursos = async () => {
    try {
      setLoading(true);
      const response = await api.get('/alumno/cursos');
      setCursos(response.data.cursos || []);
      if (response.data.cursos && response.data.cursos.length > 0) {
        setCursoSeleccionado(response.data.cursos[0]);
      }
    } catch (error) {
      console.error('Error cargando cursos:', error);
    } finally {
      setLoading(false);
    }
  };

  const cargarContenidoCurso = async () => {
    if (!cursoSeleccionado) return;
    
    try {
      // Cargar temas, tareas y exámenes del curso
      const [temasRes, tareasRes, examenesRes] = await Promise.all([
        api.get(`/alumno/cursos/${cursoSeleccionado.id}/temas`, { params: { ciclo } }),
        api.get(`/alumno/cursos/${cursoSeleccionado.id}/tareas`, { params: { ciclo } }),
        api.get(`/alumno/cursos/${cursoSeleccionado.id}/examenes`, { params: { ciclo } })
      ]);
      
      setTemas(temasRes.data.temas || []);
      setTareas(tareasRes.data.tareas || []);
      setExamenes(examenesRes.data.examenes || []);
    } catch (error) {
      console.error('Error cargando contenido del curso:', error);
    }
  };

  if (loading) {
    return (
      <DashboardLayout>
        <div className="alumno-aula-loading">
          <div className="loading-spinner">Cargando cursos...</div>
        </div>
      </DashboardLayout>
    );
  }

  return (
    <DashboardLayout>
      <div className="alumno-aula-virtual">
        <div className="aula-header">
          <h1>Aula Virtual</h1>
          <p>Accede a los contenidos de tus cursos</p>
        </div>

        {cursos.length === 0 ? (
          <div className="empty-state">
            <p>No tienes cursos asignados</p>
          </div>
        ) : (
          <>
            {/* Selector de curso */}
            <div className="curso-selector">
              <label>Selecciona un curso:</label>
              <select
                value={cursoSeleccionado?.id || ''}
                onChange={(e) => {
                  const curso = cursos.find(c => c.id === parseInt(e.target.value));
                  setCursoSeleccionado(curso);
                }}
              >
                {cursos.map(curso => (
                  <option key={curso.id} value={curso.id}>
                    {curso.curso_nombre} - {curso.docente_nombres}
                  </option>
                ))}
              </select>
            </div>

            {/* Selector de bimestre */}
            <div className="ciclo-selector">
              <label>Bimestre:</label>
              <select value={ciclo} onChange={(e) => setCiclo(parseInt(e.target.value))}>
                <option value={1}>Bimestre 1</option>
                <option value={2}>Bimestre 2</option>
                <option value={3}>Bimestre 3</option>
                <option value={4}>Bimestre 4</option>
              </select>
            </div>

            {cursoSeleccionado && (
              <div className="contenido-curso">
                {/* Temas */}
                <div className="seccion-contenido">
                  <h2>📚 Temas</h2>
                  {temas.length > 0 ? (
                    <div className="temas-grid">
                      {temas.map(tema => (
                        <div key={tema.id} className="tema-card">
                          <h3>{tema.nombre}</h3>
                          {tema.archivo_url && (
                            <a href={tema.archivo_url} target="_blank" rel="noopener noreferrer">
                              📄 Ver Archivo
                            </a>
                          )}
                          {tema.enlace_url && (
                            <a href={tema.enlace_url} target="_blank" rel="noopener noreferrer">
                              🔗 Abrir Enlace
                            </a>
                          )}
                        </div>
                      ))}
                    </div>
                  ) : (
                    <p>No hay temas disponibles</p>
                  )}
                </div>

                {/* Tareas */}
                <div className="seccion-contenido">
                  <h2>📝 Tareas</h2>
                  {tareas.length > 0 ? (
                    <div className="tareas-list">
                      {tareas.map(tarea => (
                        <div key={tarea.id} className="tarea-card">
                          <h3>{tarea.titulo}</h3>
                          <p>{tarea.descripcion}</p>
                          <p>Fecha de entrega: {new Date(tarea.fecha_entrega).toLocaleDateString('es-PE')}</p>
                          {tarea.archivo_url && (
                            <a href={tarea.archivo_url} target="_blank" rel="noopener noreferrer">
                              📎 Ver Archivo
                            </a>
                          )}
                        </div>
                      ))}
                    </div>
                  ) : (
                    <p>No hay tareas disponibles</p>
                  )}
                </div>

                {/* Exámenes */}
                <div className="seccion-contenido">
                  <h2>📋 Exámenes</h2>
                  {examenes.length > 0 ? (
                    <div className="examenes-list">
                      {examenes.map(examen => (
                        <div key={examen.id} className="examen-card">
                          <h3>{examen.titulo}</h3>
                          <p>Estado: {examen.estado}</p>
                          {examen.fecha_desde && (
                            <p>Fecha: {new Date(examen.fecha_desde).toLocaleDateString('es-PE')}</p>
                          )}
                        </div>
                      ))}
                    </div>
                  ) : (
                    <p>No hay exámenes disponibles</p>
                  )}
                </div>
              </div>
            )}
          </>
        )}
      </div>
    </DashboardLayout>
  );
}

export default AlumnoAulaVirtual;
