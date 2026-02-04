import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import DashboardLayout from '../components/DashboardLayout';
import api from '../services/api';
import { useAuth } from '../context/AuthContext';
import Swal from 'sweetalert2';
import './AlumnoPerfil.css';

// Función para obtener iniciales del nombre
function getInitials(nombre) {
  if (!nombre) return 'A';
  const parts = String(nombre).trim().split(/\s+/).slice(0, 2);
  return parts.map((p) => p[0]?.toUpperCase()).join('') || 'A';
}

function AlumnoPerfil() {
  const navigate = useNavigate();
  const { user, setUser } = useAuth();
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [perfil, setPerfil] = useState(null);
  const [formData, setFormData] = useState({
    nombres: '',
    apellido_paterno: '',
    apellido_materno: '',
    email: '',
    telefono_celular: '',
    direccion: '',
    fecha_nacimiento: ''
  });
  const [fotoPreview, setFotoPreview] = useState(null);
  const [fotoFile, setFotoFile] = useState(null);
  const [uploadingPhoto, setUploadingPhoto] = useState(false);
  const [showPasswordChange, setShowPasswordChange] = useState(false);
  const [passwordData, setPasswordData] = useState({
    password_actual: '',
    password_nueva: '',
    password_confirmar: ''
  });
  const [changingPassword, setChangingPassword] = useState(false);

  useEffect(() => {
    cargarPerfil();
  }, []);

  const cargarPerfil = async () => {
    try {
      setLoading(true);
      const response = await api.get('/alumno/perfil');
      const data = response.data;
      setPerfil(data);
      
      // Formatear fecha de nacimiento para input type="date"
      const formatearFechaParaInput = (fecha) => {
        if (!fecha) return '';
        try {
          if (typeof fecha === 'string') {
            if (fecha === '0000-00-00' || fecha === '0000-00-00 00:00:00' || fecha.trim() === '') {
              return '';
            }
            if (fecha.includes('T')) {
              return fecha.split('T')[0];
            } else if (fecha.includes(' ')) {
              return fecha.split(' ')[0];
            } else if (fecha.match(/^\d{4}-\d{2}-\d{2}$/)) {
              const [año, mes, dia] = fecha.split('-').map(Number);
              if (año > 0 && mes > 0 && dia > 0 && mes <= 12 && dia <= 31) {
                return fecha;
              }
              return '';
            }
          }
          if (fecha instanceof Date && !isNaN(fecha.getTime()) && fecha.getFullYear() > 1900) {
            return fecha.toISOString().split('T')[0];
          }
          return '';
        } catch (error) {
          console.error('Error formateando fecha:', error);
          return '';
        }
      };

      setFormData({
        nombres: data.nombres || '',
        apellido_paterno: data.apellido_paterno || '',
        apellido_materno: data.apellido_materno || '',
        email: data.email || '',
        telefono_celular: data.telefono_celular || '',
        direccion: data.direccion || '',
        fecha_nacimiento: formatearFechaParaInput(data.fecha_nacimiento)
      });
      
      if (data.foto) {
        setFotoPreview(data.foto);
      }
    } catch (error) {
      console.error('Error cargando perfil:', error);
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'No se pudo cargar el perfil',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
      });
    } finally {
      setLoading(false);
    }
  };

  const handleFotoChange = (e) => {
    const file = e.target.files[0];
    if (file) {
      if (file.size > 5 * 1024 * 1024) {
        Swal.fire({
          icon: 'warning',
          title: 'Archivo muy grande',
          text: 'La imagen no debe superar los 5MB',
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000
        });
        return;
      }
      setFotoFile(file);
      setFotoPreview(URL.createObjectURL(file));
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSaving(true);

    try {
      const formDataToSend = new FormData();
      formDataToSend.append('nombres', formData.nombres);
      formDataToSend.append('apellido_paterno', formData.apellido_paterno);
      formDataToSend.append('apellido_materno', formData.apellido_materno);
      formDataToSend.append('email', formData.email);
      formDataToSend.append('telefono_celular', formData.telefono_celular);
      formDataToSend.append('direccion', formData.direccion);
      formDataToSend.append('fecha_nacimiento', formData.fecha_nacimiento);
      
      if (fotoFile) {
        formDataToSend.append('foto', fotoFile);
      }

      const response = await api.put('/alumno/perfil', formDataToSend, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      });

      // Actualizar usuario en contexto
      if (response.data && user) {
        setUser({
          ...user,
          nombres: response.data.nombres || formData.nombres,
          apellidos: `${formData.apellido_paterno} ${formData.apellido_materno}`.trim(),
          foto: response.data.foto || fotoPreview
        });
      }

      Swal.fire({
        icon: 'success',
        title: 'Perfil actualizado',
        text: 'Tus datos se han guardado correctamente',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2000
      });

      await cargarPerfil();
    } catch (error) {
      console.error('Error actualizando perfil:', error);
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: error.response?.data?.error || 'No se pudo actualizar el perfil',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
      });
    } finally {
      setSaving(false);
    }
  };

  const handlePasswordChange = async (e) => {
    e.preventDefault();
    
    if (passwordData.password_nueva !== passwordData.password_confirmar) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Las contraseñas no coinciden',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
      });
      return;
    }

    if (passwordData.password_nueva.length < 6) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'La contraseña debe tener al menos 6 caracteres',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
      });
      return;
    }

    setChangingPassword(true);

    try {
      await api.put('/alumno/perfil/password', {
        password_actual: passwordData.password_actual,
        password_nueva: passwordData.password_nueva
      });

      Swal.fire({
        icon: 'success',
        title: 'Contraseña actualizada',
        text: 'Tu contraseña se ha cambiado correctamente',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2000
      });

      setShowPasswordChange(false);
      setPasswordData({
        password_actual: '',
        password_nueva: '',
        password_confirmar: ''
      });
    } catch (error) {
      console.error('Error cambiando contraseña:', error);
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: error.response?.data?.error || 'No se pudo cambiar la contraseña',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
      });
    } finally {
      setChangingPassword(false);
    }
  };

  if (loading) {
    return (
      <DashboardLayout>
        <div className="alumno-perfil-loading">
          <div className="loading-spinner">Cargando perfil...</div>
        </div>
      </DashboardLayout>
    );
  }

  if (!perfil) {
    return (
      <DashboardLayout>
        <div className="alumno-perfil-error">
          <p>Error al cargar el perfil</p>
        </div>
      </DashboardLayout>
    );
  }

  const nombreCompleto = `${formData.nombres} ${formData.apellido_paterno} ${formData.apellido_materno}`.trim();

  return (
    <DashboardLayout>
      <div className="alumno-perfil">
        <div className="perfil-header">
          <h1>Mi Perfil</h1>
          <p>Gestiona tu información personal</p>
        </div>

        <div className="perfil-content">
          {/* Foto de perfil */}
          <div className="perfil-foto-section">
            <div className="foto-container">
              {fotoPreview ? (
                <img 
                  src={fotoPreview} 
                  alt={nombreCompleto}
                  className="foto-preview"
                  onError={(e) => {
                    e.target.style.display = 'none';
                    const placeholder = e.target.nextElementSibling;
                    if (placeholder) placeholder.style.display = 'flex';
                  }}
                />
              ) : null}
              <div 
                className="foto-placeholder"
                style={{ display: fotoPreview ? 'none' : 'flex' }}
              >
                {getInitials(nombreCompleto)}
              </div>
            </div>
            <label htmlFor="foto-input" className="btn-cambiar-foto">
              <input
                type="file"
                id="foto-input"
                accept="image/*"
                onChange={handleFotoChange}
                style={{ display: 'none' }}
              />
              📷 Cambiar Foto
            </label>
          </div>

          {/* Formulario de datos */}
          <form className="perfil-form" onSubmit={handleSubmit}>
            <div className="form-row">
              <div className="form-group">
                <label htmlFor="nombres">Nombres</label>
                <input
                  type="text"
                  id="nombres"
                  value={formData.nombres}
                  onChange={(e) => setFormData({ ...formData, nombres: e.target.value })}
                  required
                />
              </div>
            </div>

            <div className="form-row">
              <div className="form-group">
                <label htmlFor="apellido_paterno">Apellido Paterno</label>
                <input
                  type="text"
                  id="apellido_paterno"
                  value={formData.apellido_paterno}
                  onChange={(e) => setFormData({ ...formData, apellido_paterno: e.target.value })}
                  required
                />
              </div>

              <div className="form-group">
                <label htmlFor="apellido_materno">Apellido Materno</label>
                <input
                  type="text"
                  id="apellido_materno"
                  value={formData.apellido_materno}
                  onChange={(e) => setFormData({ ...formData, apellido_materno: e.target.value })}
                />
              </div>
            </div>

            <div className="form-row">
              <div className="form-group">
                <label htmlFor="email">Email</label>
                <input
                  type="email"
                  id="email"
                  value={formData.email}
                  onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                />
              </div>

              <div className="form-group">
                <label htmlFor="telefono_celular">Teléfono Celular</label>
                <input
                  type="tel"
                  id="telefono_celular"
                  value={formData.telefono_celular}
                  onChange={(e) => setFormData({ ...formData, telefono_celular: e.target.value })}
                />
              </div>
            </div>

            <div className="form-row">
              <div className="form-group">
                <label htmlFor="direccion">Dirección</label>
                <input
                  type="text"
                  id="direccion"
                  value={formData.direccion}
                  onChange={(e) => setFormData({ ...formData, direccion: e.target.value })}
                />
              </div>

              <div className="form-group">
                <label htmlFor="fecha_nacimiento">Fecha de Nacimiento</label>
                <input
                  type="date"
                  id="fecha_nacimiento"
                  value={formData.fecha_nacimiento}
                  onChange={(e) => setFormData({ ...formData, fecha_nacimiento: e.target.value })}
                />
              </div>
            </div>

            <div className="form-actions">
              <button type="submit" className="btn-primary" disabled={saving}>
                {saving ? 'Guardando...' : 'Guardar Cambios'}
              </button>
            </div>
          </form>

          {/* Cambiar contraseña */}
          <div className="password-section">
            <div className="password-header">
              <h2>Cambiar Contraseña</h2>
              <button
                type="button"
                className="btn-toggle"
                onClick={() => setShowPasswordChange(!showPasswordChange)}
              >
                {showPasswordChange ? 'Ocultar' : 'Mostrar'}
              </button>
            </div>

            {showPasswordChange && (
              <form className="password-form" onSubmit={handlePasswordChange}>
                <div className="form-group">
                  <label htmlFor="password_actual">Contraseña Actual</label>
                  <input
                    type="password"
                    id="password_actual"
                    value={passwordData.password_actual}
                    onChange={(e) => setPasswordData({ ...passwordData, password_actual: e.target.value })}
                    required
                  />
                </div>

                <div className="form-group">
                  <label htmlFor="password_nueva">Nueva Contraseña</label>
                  <input
                    type="password"
                    id="password_nueva"
                    value={passwordData.password_nueva}
                    onChange={(e) => setPasswordData({ ...passwordData, password_nueva: e.target.value })}
                    required
                    minLength={6}
                  />
                </div>

                <div className="form-group">
                  <label htmlFor="password_confirmar">Confirmar Nueva Contraseña</label>
                  <input
                    type="password"
                    id="password_confirmar"
                    value={passwordData.password_confirmar}
                    onChange={(e) => setPasswordData({ ...passwordData, password_confirmar: e.target.value })}
                    required
                    minLength={6}
                  />
                </div>

                <div className="form-actions">
                  <button type="submit" className="btn-primary" disabled={changingPassword}>
                    {changingPassword ? 'Cambiando...' : 'Cambiar Contraseña'}
                  </button>
                </div>
              </form>
            )}
          </div>
        </div>
      </div>
    </DashboardLayout>
  );
}

export default AlumnoPerfil;
