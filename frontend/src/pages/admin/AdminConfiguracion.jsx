import React, { useState, useEffect, useCallback } from 'react';
import DashboardLayout from '../../components/DashboardLayout';
import api from '../../services/api';
import Swal from 'sweetalert2';
import './AdminConfiguracion.css';

const CICLOS = [
  { value: 0, label: 'Mensual' },
  { value: 1, label: 'Bimestral' },
  { value: 2, label: 'Trimestral' },
  { value: 3, label: 'Cuatrimestral' },
  { value: 4, label: 'Semestral' },
  { value: 5, label: 'Anual' }
];

const MESES = [
  { value: 1, label: 'Enero' },
  { value: 2, label: 'Febrero' },
  { value: 3, label: 'Marzo' },
  { value: 4, label: 'Abril' },
  { value: 5, label: 'Mayo' },
  { value: 6, label: 'Junio' },
  { value: 7, label: 'Julio' },
  { value: 8, label: 'Agosto' },
  { value: 9, label: 'Septiembre' },
  { value: 10, label: 'Octubre' },
  { value: 11, label: 'Noviembre' },
  { value: 12, label: 'Diciembre' }
];

function AdminConfiguracion() {
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [config, setConfig] = useState({
    // Información General
    titulo_intranet: '',
    codigo_modular: '',
    resolucion_creacion: '',
    texto_intranet: '',
    titulo_formulario_matricula: '',
    
    // Información Recaudo
    recaudo_nro_sucursal: '',
    recaudo_nro_cuenta: '',
    recaudo_razon_social: '',
    
    // Datos UGEL/DRE
    ugel_codigo: '',
    ugel_nombre: '',
    
    // Año Académico
    anio_activo: '',
    anio_matriculas: '',
    
    // Pagos
    ciclo_pensiones: 0,
    inicio_pensiones: 1,
    total_pensiones: 10,
    moneda: 'S/.',
    monto_adicional: 0,
    bloquear_deudores: 'NO',
    
    // Notas
    ciclo_notas: 0,
    inicio_notas: 1,
    total_notas: 4,
    rangos_ciclos_notas: {},
    
    // Apreciaciones
    rangos_mensajes: [],
    
    // Conversión a Letras - Primaria
    rangos_letras_primaria: [],
    
    // Vencimiento de Pensiones
    pensiones_vencimiento: {},
    dias_tolerancia: 0,
    
    // Comisión Pago con Tarjeta
    comision_tarjeta_debito: 0,
    comision_tarjeta_credito: 0,
    
    // Exámenes Bloques
    clave_bloques: '',
    
    // Facturación
    ruc: '',
    razon_social: '',
    direccion: '',
    link_consulta_facturas: '',
    
    // Matrícula Online
    email_notificacion_matricula_online: '',
    remitente_emails: '',
    email_matricula_apoderado: '',
    enable_enrollment_form: 'NO',
    
    // Otros
    show_birthday_window: 'NO',
    
    // Archivos
    login_fondo: null,
    libreta_logo: null,
    libreta_fondo: null,
    boleta_logo: null
  });

  const [archivosPreview, setArchivosPreview] = useState({
    login_fondo: null,
    libreta_logo: null,
    libreta_fondo: null,
    boleta_logo: null
  });

  useEffect(() => {
    cargarConfiguracion();
  }, []);

  const cargarConfiguracion = async () => {
    try {
      setLoading(true);
      const response = await api.get('/admin/configuracion');
      const data = response.data;
      
      // Asegurar que inicio_pensiones sea un número
      const inicioPensiones = data.inicio_pensiones ? parseInt(data.inicio_pensiones) : 1;
      
      // Asegurar que rangos_letras_primaria sea un array
      let rangosLetrasPrimaria = [];
      if (data.rangos_letras_primaria) {
        if (Array.isArray(data.rangos_letras_primaria)) {
          rangosLetrasPrimaria = data.rangos_letras_primaria;
        } else if (typeof data.rangos_letras_primaria === 'string') {
          try {
            const parsed = JSON.parse(data.rangos_letras_primaria);
            rangosLetrasPrimaria = Array.isArray(parsed) ? parsed : [];
          } catch (e) {
            console.warn('Error parseando rangos_letras_primaria:', e);
            rangosLetrasPrimaria = [];
          }
        }
      }
      
      // Debug: verificar qué se está recibiendo
      console.log('rangos_letras_primaria recibido:', data.rangos_letras_primaria);
      console.log('rangos_letras_primaria procesado:', rangosLetrasPrimaria);

      setConfig({
        ...data,
        inicio_pensiones: inicioPensiones,
        rangos_letras_primaria: rangosLetrasPrimaria,
        login_fondo: null,
        libreta_logo: null,
        libreta_fondo: null,
        boleta_logo: null
      });

      // Cargar previews de archivos existentes
      setArchivosPreview({
        login_fondo: data.login_fondo,
        libreta_logo: data.libreta_logo,
        libreta_fondo: data.libreta_fondo,
        boleta_logo: data.boleta_logo
      });
    } catch (error) {
      console.error('Error cargando configuración:', error);
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'No se pudo cargar la configuración'
      });
    } finally {
      setLoading(false);
    }
  };

  const handleInputChange = (e) => {
    const { name, value, type, checked } = e.target;
    setConfig(prev => ({
      ...prev,
      [name]: type === 'checkbox' ? checked : value
    }));
  };

  const handleFileChange = (e) => {
    const { name, files } = e.target;
    if (files && files[0]) {
      setConfig(prev => ({
        ...prev,
        [name]: files[0]
      }));

      // Crear preview
      const reader = new FileReader();
      reader.onload = (event) => {
        setArchivosPreview(prev => ({
          ...prev,
          [name]: event.target.result
        }));
      };
      reader.readAsDataURL(files[0]);
    }
  };

  const handleRangoMensajeChange = (index, field, value) => {
    const nuevosRangos = [...config.rangos_mensajes];
    if (!nuevosRangos[index]) {
      nuevosRangos[index] = { rango: '', mensaje: '' };
    }
    nuevosRangos[index][field] = value;
    setConfig(prev => ({ ...prev, rangos_mensajes: nuevosRangos }));
  };

  const agregarRangoMensaje = () => {
    setConfig(prev => ({
      ...prev,
      rangos_mensajes: [...prev.rangos_mensajes, { rango: '', mensaje: '' }]
    }));
  };

  const eliminarRangoMensaje = (index) => {
    const nuevosRangos = config.rangos_mensajes.filter((_, i) => i !== index);
    setConfig(prev => ({ ...prev, rangos_mensajes: nuevosRangos }));
  };

  const handleRangoLetraPrimariaChange = (index, field, value) => {
    const nuevosRangos = [...config.rangos_letras_primaria];
    if (!nuevosRangos[index]) {
      nuevosRangos[index] = { rango: '', letra: '' };
    }
    nuevosRangos[index][field] = value;
    setConfig(prev => ({ ...prev, rangos_letras_primaria: nuevosRangos }));
  };

  const agregarRangoLetraPrimaria = () => {
    setConfig(prev => ({
      ...prev,
      rangos_letras_primaria: [...prev.rangos_letras_primaria, { rango: '', letra: '' }]
    }));
  };

  const eliminarRangoLetraPrimaria = (index) => {
    const nuevosRangos = config.rangos_letras_primaria.filter((_, i) => i !== index);
    setConfig(prev => ({ ...prev, rangos_letras_primaria: nuevosRangos }));
  };

  const handleRangoCicloNotaChange = (ciclo, field, value) => {
    setConfig(prev => ({
      ...prev,
      rangos_ciclos_notas: {
        ...prev.rangos_ciclos_notas,
        [ciclo]: {
          ...prev.rangos_ciclos_notas[ciclo],
          [field]: value
        }
      }
    }));
  };

  const handleVencimientoPensionChange = (pension, value) => {
    setConfig(prev => ({
      ...prev,
      pensiones_vencimiento: {
        ...prev.pensiones_vencimiento,
        [pension]: value
      }
    }));
  };

  const generarRangosCiclosNotas = useCallback(() => {
    const total = parseInt(config.total_notas) || 4;
    const nuevosRangos = {};
    
    for (let i = 1; i <= total; i++) {
      if (!config.rangos_ciclos_notas[i]) {
        nuevosRangos[i] = { inicio: '', final: '' };
      } else {
        nuevosRangos[i] = config.rangos_ciclos_notas[i];
      }
    }
    
    setConfig(prev => ({ ...prev, rangos_ciclos_notas: nuevosRangos }));
  }, [config.total_notas, config.ciclo_notas, config.rangos_ciclos_notas]);

  useEffect(() => {
    generarRangosCiclosNotas();
  }, [generarRangosCiclosNotas]);

  // Función genérica para guardar una sección específica
  const handleSaveSection = async (fields, sectionName) => {
    try {
      setSaving(true);
      
      const formData = new FormData();
      
      // Agregar solo los campos especificados
      fields.forEach(field => {
        if (field === 'login_fondo' || field === 'libreta_logo' || field === 'libreta_fondo' || field === 'boleta_logo') {
          // Archivos - solo agregar si hay un archivo nuevo seleccionado
          if (config[field] && config[field] instanceof File) {
            formData.append(field, config[field]);
          }
        } else {
          // Campos de texto/objetos
          const value = config[field];
          if (value !== undefined && value !== null) {
            if (typeof value === 'object' && !Array.isArray(value)) {
              formData.append(field, JSON.stringify(value));
            } else if (Array.isArray(value)) {
              formData.append(field, JSON.stringify(value));
            } else {
              formData.append(field, value);
            }
          }
        }
      });

      await api.put('/admin/configuracion', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      });

      Swal.fire({
        icon: 'success',
        title: 'Éxito',
        text: `${sectionName} guardado correctamente`
      });

      // Recargar configuración para obtener URLs actualizadas
      await cargarConfiguracion();
    } catch (error) {
      console.error('Error guardando configuración:', error);
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: error.response?.data?.error || `No se pudo guardar ${sectionName}`
      });
    } finally {
      setSaving(false);
    }
  };

  const handleReiniciarAccesos = async () => {
    const result = await Swal.fire({
      title: '¿Está seguro?',
      text: 'Esto reiniciará los datos de acceso para ALUMNOS y APODERADOS',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Sí, reiniciar',
      cancelButtonText: 'Cancelar'
    });

    if (result.isConfirmed) {
      try {
        await api.post('/admin/configuracion/reiniciar-accesos');
        Swal.fire({
          icon: 'success',
          title: 'Éxito',
          text: 'Accesos reiniciados correctamente'
        });
      } catch (error) {
        console.error('Error reiniciando accesos:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'No se pudieron reiniciar los accesos'
        });
      }
    }
  };

  if (loading) {
    return (
      <DashboardLayout>
        <div className="admin-configuracion-loading">
          <p>Cargando configuración...</p>
        </div>
      </DashboardLayout>
    );
  }

  const cicloLabel = CICLOS.find(c => c.value === parseInt(config.ciclo_notas))?.label || 'Ciclo';
  const totalNotas = parseInt(config.total_notas) || 4;
  const opcionesPago = ['Matrícula', ...Array.from({ length: parseInt(config.total_pensiones) || 10 }, (_, i) => `Pensión ${i + 1}`)];

  return (
    <DashboardLayout>
      <div className="admin-configuracion">
        <div className="config-header">
          <h1>⚙️ Configuración General</h1>
          <p>Administra la configuración general del sistema</p>
        </div>

        <div className="config-form">
          {/* INFORMACIÓN GENERAL */}
          <div className="config-card config-card-1">
            <div className="config-section">
              <h2 className="section-title">📋 INFORMACIÓN GENERAL</h2>
            <div className="form-grid">
              <div className="form-group">
                <label>Título / Nombre del Colegio</label>
                <input
                  type="text"
                  name="titulo_intranet"
                  value={config.titulo_intranet}
                  onChange={handleInputChange}
                  placeholder="Título del intranet"
                />
              </div>
              <div className="form-group">
                <label>Código Modular</label>
                <input
                  type="text"
                  name="codigo_modular"
                  value={config.codigo_modular}
                  onChange={handleInputChange}
                />
              </div>
              <div className="form-group">
                <label>Resolución de Creación</label>
                <input
                  type="text"
                  name="resolucion_creacion"
                  value={config.resolucion_creacion}
                  onChange={handleInputChange}
                />
              </div>
              <div className="form-group">
                <label>Fondo - Login</label>
                {archivosPreview.login_fondo && (
                  <div className="file-preview">
                    <img src={archivosPreview.login_fondo} alt="Preview" />
                  </div>
                )}
                <input
                  type="file"
                  name="login_fondo"
                  accept=".jpg,.jpeg,.png"
                  onChange={handleFileChange}
                />
                <small>Solo archivos JPG, PNG</small>
              </div>
              <div className="form-group">
                <label>Texto Intranet</label>
                <input
                  type="text"
                  name="texto_intranet"
                  value={config.texto_intranet}
                  onChange={handleInputChange}
                />
              </div>
              <div className="form-group">
                <label>Título Formulario Matrícula</label>
                <input
                  type="text"
                  name="titulo_formulario_matricula"
                  value={config.titulo_formulario_matricula}
                  onChange={handleInputChange}
                />
              </div>
            </div>
            <div className="card-actions">
              <button 
                type="button" 
                onClick={() => handleSaveSection([
                  'titulo_intranet', 'codigo_modular', 'resolucion_creacion', 
                  'texto_intranet', 'titulo_formulario_matricula', 'login_fondo'
                ], 'Información General')}
                disabled={saving}
                className="btn-submit-card"
              >
                {saving ? 'Guardando...' : '💾 Guardar'}
              </button>
            </div>
            </div>
          </div>

          {/* INFORMACIÓN RECAUDO */}
          <div className="config-card config-card-2">
            <div className="config-section">
              <h2 className="section-title">💰 INFORMACIÓN RECAUDO</h2>
            <div className="form-grid">
              <div className="form-group">
                <label>N° de Sucursal</label>
                <input
                  type="text"
                  name="recaudo_nro_sucursal"
                  value={config.recaudo_nro_sucursal}
                  onChange={handleInputChange}
                />
              </div>
              <div className="form-group">
                <label>N° de Cuenta</label>
                <input
                  type="text"
                  name="recaudo_nro_cuenta"
                  value={config.recaudo_nro_cuenta}
                  onChange={handleInputChange}
                />
              </div>
              <div className="form-group">
                <label>Razón Social</label>
                <input
                  type="text"
                  name="recaudo_razon_social"
                  value={config.recaudo_razon_social}
                  onChange={handleInputChange}
                />
              </div>
            </div>
            <div className="card-actions">
              <button 
                type="button" 
                onClick={() => handleSaveSection([
                  'recaudo_nro_sucursal', 'recaudo_nro_cuenta', 'recaudo_razon_social'
                ], 'Información Recaudo')}
                disabled={saving}
                className="btn-submit-card"
              >
                {saving ? 'Guardando...' : '💾 Guardar'}
              </button>
            </div>
            </div>
          </div>

          {/* DATOS UGEL/DRE */}
          <div className="config-card config-card-3">
            <div className="config-section">
              <h2 className="section-title">🏛️ DATOS - UGEL / DRE</h2>
            <div className="form-grid">
              <div className="form-group">
                <label>Código (UGEL - DRE)</label>
                <input
                  type="text"
                  name="ugel_codigo"
                  value={config.ugel_codigo}
                  onChange={handleInputChange}
                />
              </div>
              <div className="form-group">
                <label>Nombre (UGEL - DRE)</label>
                <input
                  type="text"
                  name="ugel_nombre"
                  value={config.ugel_nombre}
                  onChange={handleInputChange}
                />
              </div>
            </div>
            <div className="card-actions">
              <button 
                type="button" 
                onClick={() => handleSaveSection([
                  'ugel_codigo', 'ugel_nombre'
                ], 'Datos UGEL/DRE')}
                disabled={saving}
                className="btn-submit-card"
              >
                {saving ? 'Guardando...' : '💾 Guardar'}
              </button>
            </div>
            </div>
          </div>

          {/* DISEÑO LIBRETA DE NOTAS */}
          <div className="config-card config-card-4">
            <div className="config-section">
              <h2 className="section-title">📓 DISEÑO LIBRETA DE NOTAS</h2>
            <div className="form-grid">
              <div className="form-group">
                <label>Logo</label>
                {archivosPreview.libreta_logo && (
                  <div className="file-preview">
                    <img src={archivosPreview.libreta_logo} alt="Preview" />
                  </div>
                )}
                <input
                  type="file"
                  name="libreta_logo"
                  accept=".jpg,.jpeg"
                  onChange={handleFileChange}
                />
                <small>Solo archivos .jpg</small>
              </div>
              <div className="form-group">
                <label>Fondo</label>
                {archivosPreview.libreta_fondo && (
                  <div className="file-preview">
                    <img src={archivosPreview.libreta_fondo} alt="Preview" />
                  </div>
                )}
                <input
                  type="file"
                  name="libreta_fondo"
                  accept=".jpg,.jpeg"
                  onChange={handleFileChange}
                />
                <small>Solo archivos .jpg</small>
              </div>
            </div>
            <div className="card-actions">
              <button 
                type="button" 
                onClick={() => handleSaveSection([
                  'libreta_logo', 'libreta_fondo'
                ], 'Diseño Libreta de Notas')}
                disabled={saving}
                className="btn-submit-card"
              >
                {saving ? 'Guardando...' : '💾 Guardar'}
              </button>
            </div>
            </div>
          </div>

          {/* AÑO ACADÉMICO */}
          <div className="config-card config-card-5">
            <div className="config-section">
              <h2 className="section-title">📅 AÑO ACADÉMICO</h2>
            <div className="form-grid">
              <div className="form-group">
                <label>Año Activo</label>
                <input
                  type="number"
                  name="anio_activo"
                  value={config.anio_activo}
                  onChange={handleInputChange}
                />
              </div>
              <div className="form-group">
                <label>Año Matrículas</label>
                <input
                  type="number"
                  name="anio_matriculas"
                  value={config.anio_matriculas}
                  onChange={handleInputChange}
                />
              </div>
            </div>
            <div className="card-actions">
              <button 
                type="button" 
                onClick={() => handleSaveSection([
                  'anio_activo', 'anio_matriculas'
                ], 'Año Académico')}
                disabled={saving}
                className="btn-submit-card"
              >
                {saving ? 'Guardando...' : '💾 Guardar'}
              </button>
            </div>
            </div>
          </div>

          {/* PAGOS */}
          <div className="config-card config-card-6">
            <div className="config-section">
              <h2 className="section-title">💳 PAGOS</h2>
            <div className="form-grid">
              <div className="form-group">
                <label>Ciclo de Pensiones</label>
                <select
                  name="ciclo_pensiones"
                  value={config.ciclo_pensiones}
                  onChange={handleInputChange}
                >
                  {CICLOS.map(ciclo => (
                    <option key={ciclo.value} value={ciclo.value}>{ciclo.label}</option>
                  ))}
                </select>
              </div>
              {config.ciclo_pensiones === 0 && (
                <div className="form-group">
                  <label>Inicio de Cobros</label>
                  <select
                    name="inicio_pensiones"
                    value={config.inicio_pensiones}
                    onChange={handleInputChange}
                  >
                    {MESES.map(mes => (
                      <option key={mes.value} value={mes.value}>{mes.label}</option>
                    ))}
                  </select>
                </div>
              )}
              <div className="form-group">
                <label>Total Pensiones</label>
                <input
                  type="number"
                  name="total_pensiones"
                  value={config.total_pensiones}
                  onChange={handleInputChange}
                  min="1"
                />
              </div>
              <div className="form-group">
                <label>Moneda</label>
                <input
                  type="text"
                  name="moneda"
                  value={config.moneda}
                  onChange={handleInputChange}
                  placeholder="S/., US$"
                />
              </div>
              <div className="form-group">
                <label>Monto Adicional S/.</label>
                <input
                  type="number"
                  name="monto_adicional"
                  value={config.monto_adicional}
                  onChange={handleInputChange}
                  step="0.01"
                />
              </div>
              <div className="form-group">
                <label>Bloquear Deudores</label>
                <select
                  name="bloquear_deudores"
                  value={config.bloquear_deudores}
                  onChange={handleInputChange}
                >
                  <option value="NO">NO</option>
                  <option value="SI">SI</option>
                </select>
              </div>
            </div>
            <div className="card-actions">
              <button 
                type="button" 
                onClick={() => handleSaveSection([
                  'ciclo_pensiones', 'inicio_pensiones', 'total_pensiones',
                  'moneda', 'monto_adicional', 'bloquear_deudores'
                ], 'Pagos')}
                disabled={saving}
                className="btn-submit-card"
              >
                {saving ? 'Guardando...' : '💾 Guardar'}
              </button>
            </div>
            </div>
          </div>

          {/* NOTAS */}
          <div className="config-card config-card-7">
            <div className="config-section">
              <h2 className="section-title">📝 NOTAS</h2>
            <div className="form-grid">
              <div className="form-group">
                <label>Ciclo de Notas</label>
                <select
                  name="ciclo_notas"
                  value={config.ciclo_notas}
                  onChange={handleInputChange}
                >
                  {CICLOS.map(ciclo => (
                    <option key={ciclo.value} value={ciclo.value}>{ciclo.label}</option>
                  ))}
                </select>
              </div>
              {config.ciclo_notas === 0 && (
                <div className="form-group">
                  <label>Inicio de Registro</label>
                  <select
                    name="inicio_notas"
                    value={config.inicio_notas}
                    onChange={handleInputChange}
                  >
                    {MESES.map(mes => (
                      <option key={mes.value} value={mes.value}>{mes.label}</option>
                    ))}
                  </select>
                </div>
              )}
              <div className="form-group">
                <label>Total Ciclos</label>
                <input
                  type="number"
                  name="total_notas"
                  value={config.total_notas}
                  onChange={handleInputChange}
                  min="1"
                />
              </div>
            </div>
            {config.ciclo_notas !== 0 && (
              <div className="rangos-ciclos-notas">
                <h3>Rangos de Ciclos de Notas</h3>
                <table className="rangos-table">
                  <thead>
                    <tr>
                      <th></th>
                      <th>Inicio</th>
                      <th>Final</th>
                    </tr>
                  </thead>
                  <tbody>
                    {Array.from({ length: totalNotas }, (_, i) => i + 1).map(ciclo => (
                      <tr key={ciclo}>
                        <th>{cicloLabel} {ciclo}</th>
                        <td>
                          <input
                            type="text"
                            placeholder="dd-mm"
                            value={config.rangos_ciclos_notas[ciclo]?.inicio || ''}
                            onChange={(e) => handleRangoCicloNotaChange(ciclo, 'inicio', e.target.value)}
                            style={{ width: '90px' }}
                          />
                        </td>
                        <td>
                          <input
                            type="text"
                            placeholder="dd-mm"
                            value={config.rangos_ciclos_notas[ciclo]?.final || ''}
                            onChange={(e) => handleRangoCicloNotaChange(ciclo, 'final', e.target.value)}
                            style={{ width: '90px' }}
                          />
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            )}
            <div className="card-actions">
              <button 
                type="button" 
                onClick={() => handleSaveSection([
                  'ciclo_notas', 'inicio_notas', 'total_notas', 'rangos_ciclos_notas'
                ], 'Notas')}
                disabled={saving}
                className="btn-submit-card"
              >
                {saving ? 'Guardando...' : '💾 Guardar'}
              </button>
            </div>
            </div>
          </div>

          {/* APRECIACIONES */}
          <div className="config-card config-card-8">
            <div className="config-section">
              <h2 className="section-title">⭐ APRECIACIONES</h2>
            <button type="button" onClick={agregarRangoMensaje} className="btn-add">
              ➕ Agregar Nuevo
            </button>
            <table className="rangos-table">
              <thead>
                <tr>
                  <th>Rango / Letra</th>
                  <th>Apreciación</th>
                  <th>Acción</th>
                </tr>
              </thead>
              <tbody>
                {config.rangos_mensajes.map((rango, index) => (
                  <tr key={index}>
                    <td>
                      <input
                        type="text"
                        value={rango.rango || ''}
                        onChange={(e) => handleRangoMensajeChange(index, 'rango', e.target.value)}
                        placeholder="Rango"
                        style={{ width: '90px' }}
                      />
                    </td>
                    <td>
                      <input
                        type="text"
                        value={rango.mensaje || ''}
                        onChange={(e) => handleRangoMensajeChange(index, 'mensaje', e.target.value)}
                        placeholder="Mensaje"
                        style={{ width: '300px' }}
                      />
                    </td>
                    <td>
                      <button
                        type="button"
                        onClick={() => eliminarRangoMensaje(index)}
                        className="btn-delete"
                      >
                        🗑️
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
            <div className="card-actions">
              <button 
                type="button" 
                onClick={() => handleSaveSection([
                  'rangos_mensajes'
                ], 'Apreciaciones')}
                disabled={saving}
                className="btn-submit-card"
              >
                {saving ? 'Guardando...' : '💾 Guardar'}
              </button>
            </div>
            </div>
          </div>

          {/* CONVERSIÓN A NOTAS A LETRAS - PRIMARIA */}
          <div className="config-card config-card-9">
            <div className="config-section">
              <h2 className="section-title">🔤 CONVERSIÓN A NOTAS A LETRAS - PRIMARIA</h2>
            <button type="button" onClick={agregarRangoLetraPrimaria} className="btn-add">
              ➕ Agregar Nuevo
            </button>
            <table className="rangos-table">
              <thead>
                <tr>
                  <th>Rango</th>
                  <th>Letra</th>
                  <th>Acción</th>
                </tr>
              </thead>
              <tbody>
                {config.rangos_letras_primaria.map((rango, index) => (
                  <tr key={index}>
                    <td>
                      <input
                        type="text"
                        value={rango.rango || ''}
                        onChange={(e) => handleRangoLetraPrimariaChange(index, 'rango', e.target.value)}
                        placeholder="Rango"
                        style={{ width: '90px' }}
                      />
                    </td>
                    <td>
                      <input
                        type="text"
                        value={rango.letra || ''}
                        onChange={(e) => handleRangoLetraPrimariaChange(index, 'letra', e.target.value)}
                        placeholder="Letra"
                        style={{ width: '300px' }}
                      />
                    </td>
                    <td>
                      <button
                        type="button"
                        onClick={() => eliminarRangoLetraPrimaria(index)}
                        className="btn-delete"
                      >
                        🗑️
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
            <div className="card-actions">
              <button 
                type="button" 
                onClick={() => handleSaveSection([
                  'rangos_letras_primaria'
                ], 'Conversión a Notas a Letras - Primaria')}
                disabled={saving}
                className="btn-submit-card"
              >
                {saving ? 'Guardando...' : '💾 Guardar'}
              </button>
            </div>
            </div>
          </div>

          {/* VENCIMIENTO DE PENSIONES */}
          <div className="config-card config-card-10">
            <div className="config-section">
              <h2 className="section-title">📆 VENCIMIENTO DE PENSIONES</h2>
            <table className="rangos-table">
              <tbody>
                <tr>
                  <th>Matrícula</th>
                  <td>
                    <input
                      type="text"
                      placeholder="dd-mm"
                      value={config.pensiones_vencimiento[-1] || ''}
                      onChange={(e) => handleVencimientoPensionChange(-1, e.target.value)}
                      style={{ width: '70px' }}
                    />
                  </td>
                </tr>
                {opcionesPago.slice(1).map((pago, index) => (
                  <tr key={index}>
                    <th>{pago}</th>
                    <td>
                      <input
                        type="text"
                        placeholder="dd-mm"
                        value={config.pensiones_vencimiento[index + 1] || ''}
                        onChange={(e) => handleVencimientoPensionChange(index + 1, e.target.value)}
                        style={{ width: '70px' }}
                      />
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
            <div className="form-grid">
              <div className="form-group">
                <label>Días de Tolerancia</label>
                <input
                  type="number"
                  name="dias_tolerancia"
                  value={config.dias_tolerancia}
                  onChange={handleInputChange}
                />
              </div>
            </div>
            <div className="card-actions">
              <button 
                type="button" 
                onClick={() => handleSaveSection([
                  'pensiones_vencimiento', 'dias_tolerancia'
                ], 'Vencimiento de Pensiones')}
                disabled={saving}
                className="btn-submit-card"
              >
                {saving ? 'Guardando...' : '💾 Guardar'}
              </button>
            </div>
            </div>
          </div>

          {/* COMISIÓN PAGO CON TARJETA */}
          <div className="config-card config-card-11">
            <div className="config-section">
              <h2 className="section-title">💳 COMISIÓN PAGO CON TARJETA</h2>
            <div className="form-grid">
              <div className="form-group">
                <label>Tarjeta de Débito %</label>
                <input
                  type="number"
                  name="comision_tarjeta_debito"
                  value={config.comision_tarjeta_debito}
                  onChange={handleInputChange}
                  step="0.01"
                />
              </div>
              <div className="form-group">
                <label>Tarjeta de Crédito %</label>
                <input
                  type="number"
                  name="comision_tarjeta_credito"
                  value={config.comision_tarjeta_credito}
                  onChange={handleInputChange}
                  step="0.01"
                />
              </div>
            </div>
            <div className="card-actions">
              <button 
                type="button" 
                onClick={() => handleSaveSection([
                  'comision_tarjeta_debito', 'comision_tarjeta_credito'
                ], 'Comisión Pago con Tarjeta')}
                disabled={saving}
                className="btn-submit-card"
              >
                {saving ? 'Guardando...' : '💾 Guardar'}
              </button>
            </div>
            </div>
          </div>

          {/* FACTURACIÓN */}
          <div className="config-card config-card-13">
            <div className="config-section">
              <h2 className="section-title">🧾 FACTURACIÓN</h2>
            <div className="form-grid">
              <div className="form-group">
                <label>RUC</label>
                <input
                  type="text"
                  name="ruc"
                  value={config.ruc}
                  onChange={handleInputChange}
                />
              </div>
              <div className="form-group">
                <label>Razón Social</label>
                <input
                  type="text"
                  name="razon_social"
                  value={config.razon_social}
                  onChange={handleInputChange}
                />
              </div>
              <div className="form-group">
                <label>Dirección</label>
                <input
                  type="text"
                  name="direccion"
                  value={config.direccion}
                  onChange={handleInputChange}
                />
              </div>
              <div className="form-group">
                <label>Link Consulta Boletas</label>
                <input
                  type="text"
                  name="link_consulta_facturas"
                  value={config.link_consulta_facturas}
                  onChange={handleInputChange}
                />
              </div>
              <div className="form-group">
                <label>Logo Boleta</label>
                {archivosPreview.boleta_logo && (
                  <div className="file-preview">
                    <img src={archivosPreview.boleta_logo} alt="Preview" />
                  </div>
                )}
                <input
                  type="file"
                  name="boleta_logo"
                  accept=".jpg,.jpeg"
                  onChange={handleFileChange}
                />
                <small>Solo archivos .jpg</small>
              </div>
            </div>
            <div className="card-actions">
              <button 
                type="button" 
                onClick={() => handleSaveSection([
                  'ruc', 'razon_social', 'direccion', 'link_consulta_facturas', 'boleta_logo'
                ], 'Facturación')}
                disabled={saving}
                className="btn-submit-card"
              >
                {saving ? 'Guardando...' : '💾 Guardar'}
              </button>
            </div>
            </div>
          </div>

          {/* MATRÍCULA ONLINE */}
          <div className="config-card config-card-14">
            <div className="config-section">
              <h2 className="section-title">📝 MATRÍCULA ONLINE</h2>
            <div className="form-grid">
              <div className="form-group full-width">
                <label>Correos Notificación</label>
                <textarea
                  name="email_notificacion_matricula_online"
                  value={config.email_notificacion_matricula_online}
                  onChange={handleInputChange}
                  rows="3"
                />
              </div>
              <div className="form-group">
                <label>Remitente Emails</label>
                <input
                  type="text"
                  name="remitente_emails"
                  value={config.remitente_emails}
                  onChange={handleInputChange}
                />
              </div>
              <div className="form-group full-width">
                <label>Mensaje Matrícula Apoderado</label>
                <textarea
                  name="email_matricula_apoderado"
                  value={config.email_matricula_apoderado}
                  onChange={handleInputChange}
                  rows="10"
                />
              </div>
              <div className="form-group">
                <label>Habilitar Formulario</label>
                <select
                  name="enable_enrollment_form"
                  value={config.enable_enrollment_form}
                  onChange={handleInputChange}
                >
                  <option value="NO">NO</option>
                  <option value="SI">SI</option>
                </select>
              </div>
            </div>
            <div className="card-actions">
              <button 
                type="button" 
                onClick={() => handleSaveSection([
                  'email_notificacion_matricula_online', 'remitente_emails',
                  'email_matricula_apoderado', 'enable_enrollment_form'
                ], 'Matrícula Online')}
                disabled={saving}
                className="btn-submit-card"
              >
                {saving ? 'Guardando...' : '💾 Guardar'}
              </button>
            </div>
            </div>
          </div>

          {/* OTROS */}
          <div className="config-card config-card-15">
            <div className="config-section">
              <h2 className="section-title">⚙️ OTROS</h2>
            <div className="form-grid">
              <div className="form-group">
                <label>Mostrar Ventana Cumpleaños</label>
                <select
                  name="show_birthday_window"
                  value={config.show_birthday_window}
                  onChange={handleInputChange}
                >
                  <option value="NO">NO</option>
                  <option value="SI">SI</option>
                </select>
              </div>
            </div>
            <div className="card-actions">
              <button 
                type="button" 
                onClick={() => handleSaveSection([
                  'show_birthday_window'
                ], 'Otros')}
                disabled={saving}
                className="btn-submit-card"
              >
                {saving ? 'Guardando...' : '💾 Guardar'}
              </button>
            </div>
            </div>
          </div>

          {/* REINICIAR ACCESOS */}
          <div className="config-card config-card-16">
            <div className="config-section">
              <h2 className="section-title">🔄 REINICIAR ACCESOS</h2>
            <div className="form-grid">
              <div className="form-group full-width">
                <button
                  type="button"
                  onClick={handleReiniciarAccesos}
                  className="btn-warning"
                >
                  Reiniciar Datos de Acceso
                </button>
              </div>
            </div>
            </div>
          </div>

        </div>
      </div>
    </DashboardLayout>
  );
}

export default AdminConfiguracion;

