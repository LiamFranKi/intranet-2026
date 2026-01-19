import React, { useState, useEffect } from 'react';
import DashboardLayout from '../components/DashboardLayout';
import api from '../services/api';
import Swal from 'sweetalert2';
import './DocenteTutoria.css';

function DocenteTutoria() {
  const [loading, setLoading] = useState(true);
  const [esTutor, setEsTutor] = useState(false);
  const [grupoTutor, setGrupoTutor] = useState(null);
  const [alumnos, setAlumnos] = useState([]);
  const [mostrarRecomendacion, setMostrarRecomendacion] = useState(false);
  const [alumnoSeleccionado, setAlumnoSeleccionado] = useState(null);
  const [recomendacion, setRecomendacion] = useState('');

  useEffect(() => {
    verificarTutoria();
  }, []);

  const verificarTutoria = async () => {
    try {
      setLoading(true);
      const response = await api.get('/docente/tutoria/alumnos');
      setEsTutor(response.data.es_tutor);
      setGrupoTutor(response.data.grupo);
      setAlumnos(response.data.alumnos || []);
    } catch (error) {
      console.error('Error verificando tutoría:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleEnviarRecomendacion = async () => {
    if (!alumnoSeleccionado || !recomendacion.trim()) {
      return;
    }

    try {
      // Obtener matricula_id del alumno
      const matriculaId = alumnoSeleccionado.matricula_id || alumnoSeleccionado.id;
      
      await api.post('/docente/tutoria/recomendaciones', {
        matricula_id: matriculaId,
        recomendacion: recomendacion
      });

      Swal.fire({
        icon: 'success',
        title: '¡Recomendación enviada!',
        text: 'La recomendación se ha enviado correctamente',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
      });

      setMostrarRecomendacion(false);
      setAlumnoSeleccionado(null);
      setRecomendacion('');
    } catch (error) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: error.response?.data?.error || 'No se pudo enviar la recomendación',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
      });
    }
  };

  if (loading) {
    return (
      <DashboardLayout>
        <div className="docente-tutoria-loading">
          <div className="loading-spinner">Verificando...</div>
        </div>
      </DashboardLayout>
    );
  }

  if (!esTutor) {
    return (
      <DashboardLayout>
        <div className="docente-tutoria">
          <div className="no-tutor mundo-card">
            <h2>No eres tutor</h2>
            <p>No estás asignado como tutor de ningún grupo en el año académico actual.</p>
          </div>
        </div>
      </DashboardLayout>
    );
  }

  return (
    <DashboardLayout>
      <div className="docente-tutoria">
        <div className="page-header">
          <h1>Tutoría</h1>
          <p>Gestiona tu grupo como tutor</p>
        </div>

        {grupoTutor && (
          <>
            <div className="tutoria-content mundo-card">
              <div className="grupo-info">
                <h2>Grupo: {grupoTutor.grado}° {grupoTutor.seccion}</h2>
                <p>{grupoTutor.nivel_nombre} - {grupoTutor.turno_nombre}</p>
              </div>

              <div className="tutoria-actions">
                <button 
                  className="btn-enviar-recomendacion"
                  onClick={() => setMostrarRecomendacion(!mostrarRecomendacion)}
                >
                  📝 Enviar Recomendación
                </button>
              </div>

              {mostrarRecomendacion && (
                <div className="recomendacion-form">
                  <h3>Enviar Recomendación</h3>
                  <div className="form-group">
                    <label>Alumno</label>
                    <select
                      value={alumnoSeleccionado?.id || ''}
                      onChange={(e) => {
                        const alumno = alumnos.find(a => a.id === parseInt(e.target.value));
                        setAlumnoSeleccionado(alumno);
                      }}
                    >
                      <option value="">Selecciona un alumno</option>
                      {alumnos.map((alumno) => (
                        <option key={alumno.id} value={alumno.id}>
                          {alumno.apellido_paterno} {alumno.apellido_materno}, {alumno.nombres}
                        </option>
                      ))}
                    </select>
                  </div>
                  <div className="form-group">
                    <label>Recomendación</label>
                    <textarea
                      value={recomendacion}
                      onChange={(e) => setRecomendacion(e.target.value)}
                      rows="5"
                      placeholder="Escribe la recomendación para el alumno..."
                    />
                  </div>
                  <div className="form-actions">
                    <button type="button" onClick={() => {
                      setMostrarRecomendacion(false);
                      setAlumnoSeleccionado(null);
                      setRecomendacion('');
                    }}>
                      Cancelar
                    </button>
                    <button 
                      type="button"
                      onClick={handleEnviarRecomendacion}
                      disabled={!alumnoSeleccionado || !recomendacion.trim()}
                    >
                      Enviar Recomendación
                    </button>
                  </div>
                </div>
              )}

              <div className="alumnos-list">
                <h3>Alumnos Tutelados ({alumnos.length})</h3>
                {alumnos.length > 0 ? (
                  <div className="alumnos-grid">
                    {alumnos.map((alumno) => (
                      <div key={alumno.id} className="alumno-card">
                        <div className="alumno-avatar">
                          {alumno.foto ? (
                            <img src={alumno.foto} alt={alumno.nombres} />
                          ) : (
                            <span>{alumno.nombres?.[0] || 'A'}</span>
                          )}
                        </div>
                        <div className="alumno-info">
                          <h4>{alumno.apellido_paterno} {alumno.apellido_materno}</h4>
                          <p>{alumno.nombres}</p>
                          <button 
                            className="btn-ver-perfil"
                            onClick={() => {
                              setAlumnoSeleccionado(alumno);
                              setMostrarRecomendacion(true);
                            }}
                          >
                            Ver Perfil
                          </button>
                        </div>
                      </div>
                    ))}
                  </div>
                ) : (
                  <div className="empty-state">
                    <p>No hay alumnos asignados a este grupo</p>
                  </div>
                )}
              </div>
            </div>
          </>
        )}
      </div>
    </DashboardLayout>
  );
}

export default DocenteTutoria;

