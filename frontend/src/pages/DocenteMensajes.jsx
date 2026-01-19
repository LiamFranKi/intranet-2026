import React, { useState, useEffect } from 'react';
import DashboardLayout from '../components/DashboardLayout';
import api from '../services/api';
import Swal from 'sweetalert2';
import './DocenteMensajes.css';

function DocenteMensajes() {
  const [loading, setLoading] = useState(true);
  const [mensajes, setMensajes] = useState([]);
  const [mostrarFormulario, setMostrarFormulario] = useState(false);
  const [formData, setFormData] = useState({
    destinatario: '',
    tipo_destinatario: 'alumno', // alumno, apoderado, grupo
    asunto: '',
    mensaje: ''
  });

  useEffect(() => {
    cargarMensajes();
  }, []);

  const cargarMensajes = async () => {
    try {
      setLoading(true);
      const response = await api.get('/docente/mensajes');
      setMensajes(response.data.mensajes || []);
    } catch (error) {
      console.error('Error cargando mensajes:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      await api.post('/docente/mensajes', {
        destinatario_id: parseInt(formData.destinatario),
        asunto: formData.asunto,
        mensaje: formData.mensaje
      });
      Swal.fire({
        icon: 'success',
        title: '¡Mensaje enviado!',
        text: 'El mensaje se ha enviado correctamente',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
      });
      setMostrarFormulario(false);
      setFormData({
        destinatario: '',
        tipo_destinatario: 'alumno',
        asunto: '',
        mensaje: ''
      });
      cargarMensajes();
    } catch (error) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: error.response?.data?.error || 'No se pudo enviar el mensaje',
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
        <div className="docente-mensajes-loading">
          <div className="loading-spinner">Cargando mensajes...</div>
        </div>
      </DashboardLayout>
    );
  }

  return (
    <DashboardLayout>
      <div className="docente-mensajes">
        <div className="page-header">
          <h1>Mensajes</h1>
          <p>Envía mensajes a alumnos, padres de familia o grupos</p>
          <button className="btn-nuevo-mensaje" onClick={() => setMostrarFormulario(!mostrarFormulario)}>
            ✉️ Nuevo Mensaje
          </button>
        </div>

        {mostrarFormulario && (
          <div className="mensaje-form mundo-card">
            <h2>Enviar Mensaje</h2>
            <form onSubmit={handleSubmit}>
              <div className="form-group">
                <label>Tipo de Destinatario</label>
                <select
                  value={formData.tipo_destinatario}
                  onChange={(e) => setFormData({ ...formData, tipo_destinatario: e.target.value })}
                >
                  <option value="alumno">Alumno</option>
                  <option value="apoderado">Padre de Familia</option>
                  <option value="grupo">Grupo</option>
                </select>
              </div>
              <div className="form-group">
                <label>Destinatario</label>
                <input
                  type="text"
                  value={formData.destinatario}
                  onChange={(e) => setFormData({ ...formData, destinatario: e.target.value })}
                  placeholder="Selecciona o busca el destinatario"
                  required
                />
              </div>
              <div className="form-group">
                <label>Asunto</label>
                <input
                  type="text"
                  value={formData.asunto}
                  onChange={(e) => setFormData({ ...formData, asunto: e.target.value })}
                  required
                />
              </div>
              <div className="form-group">
                <label>Mensaje</label>
                <textarea
                  value={formData.mensaje}
                  onChange={(e) => setFormData({ ...formData, mensaje: e.target.value })}
                  rows="5"
                  required
                />
              </div>
              <div className="form-actions">
                <button type="button" onClick={() => setMostrarFormulario(false)}>
                  Cancelar
                </button>
                <button type="submit">Enviar Mensaje</button>
              </div>
            </form>
          </div>
        )}

        <div className="mensajes-list">
          {mensajes.length > 0 ? (
            mensajes.map((mensaje) => (
              <div key={mensaje.id} className="mensaje-card mundo-card">
                <div className="mensaje-header">
                  <h3>{mensaje.asunto}</h3>
                  <span className="mensaje-fecha">
                    {new Date(mensaje.fecha_hora).toLocaleDateString('es-PE')}
                  </span>
                </div>
                <div className="mensaje-info">
                  <span>{mensaje.tipo === 'ENVIADO' ? 'Para' : 'De'}: {mensaje.tipo === 'ENVIADO' ? mensaje.destinatario_usuario : mensaje.remitente_usuario}</span>
                </div>
                <div className="mensaje-contenido">
                  <p>{mensaje.mensaje}</p>
                </div>
                <div className="mensaje-estado">
                  <span className={mensaje.estado === 'LEIDO' ? 'leido' : 'no-leido'}>
                    {mensaje.estado === 'LEIDO' ? '✓ Leído' : '○ No leído'}
                  </span>
                </div>
              </div>
            ))
          ) : (
            <div className="empty-state mundo-card">
              <p>No hay mensajes</p>
            </div>
          )}
        </div>
      </div>
    </DashboardLayout>
  );
}

export default DocenteMensajes;

