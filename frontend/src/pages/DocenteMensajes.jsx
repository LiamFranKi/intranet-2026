import React, { useState, useEffect, useCallback } from 'react';
import DashboardLayout from '../components/DashboardLayout';
import api from '../services/api';
import Swal from 'sweetalert2';
import ReactQuill from 'react-quill';
import 'react-quill/dist/quill.snow.css';
import './DocenteMensajes.css';

function DocenteMensajes() {
  const [vista, setVista] = useState('recibidos'); // 'recibidos', 'enviados', 'nuevo'
  const [mensajesRecibidos, setMensajesRecibidos] = useState([]);
  const [mensajesEnviados, setMensajesEnviados] = useState([]);
  const [loading, setLoading] = useState(false);
  const [mensajeSeleccionado, setMensajeSeleccionado] = useState(null);
  const [mensajesSeleccionados, setMensajesSeleccionados] = useState(new Set());
  const [anioFiltro, setAnioFiltro] = useState(null); // null = todos los años
  const quillRef = React.useRef(null);
  
  // Estados para nuevo mensaje
  const [busquedaDestinatario, setBusquedaDestinatario] = useState('');
  const [resultadosBusqueda, setResultadosBusqueda] = useState([]);
  const [destinatariosSeleccionados, setDestinatariosSeleccionados] = useState([]);
  const [asunto, setAsunto] = useState('');
  const [contenido, setContenido] = useState('');
  const [archivosAdjuntos, setArchivosAdjuntos] = useState([]);
  const [mostrarResultados, setMostrarResultados] = useState(false);
  const [enviando, setEnviando] = useState(false);

  // Configurar handler de imágenes para ReactQuill
  useEffect(() => {
    if (quillRef.current && vista === 'nuevo') {
      const quill = quillRef.current.getEditor();
      const toolbar = quill.getModule('toolbar');
      
      toolbar.addHandler('image', async () => {
        const input = document.createElement('input');
        input.setAttribute('type', 'file');
        input.setAttribute('accept', 'image/*');
        input.click();

        input.onchange = async () => {
          const file = input.files[0];
          if (!file) return;

          // Validar tamaño (máximo 5MB)
          if (file.size > 5 * 1024 * 1024) {
            Swal.fire('Error', 'La imagen no puede ser mayor a 5MB', 'error');
            return;
          }

          // Validar tipo
          if (!file.type.startsWith('image/')) {
            Swal.fire('Error', 'Solo se permiten archivos de imagen', 'error');
            return;
          }

          try {
            const formData = new FormData();
            formData.append('imagen', file);

            const response = await api.post('/docente/mensajes/subir-imagen', formData, {
              headers: {
                'Content-Type': 'multipart/form-data',
              },
            });

            // Obtener la URL completa de la imagen
            const imagenUrl = `${api.defaults.baseURL.replace('/api', '')}${response.data.url}`;
            
            // Insertar la imagen en el editor
            const range = quill.getSelection(true);
            quill.insertEmbed(range.index, 'image', imagenUrl);
            quill.setSelection(range.index + 1);
          } catch (error) {
            console.error('Error subiendo imagen:', error);
            Swal.fire('Error', 'No se pudo subir la imagen', 'error');
          }
        };
      });
    }
  }, [vista]);

  // Cargar mensajes recibidos
  const cargarMensajesRecibidos = useCallback(async () => {
    try {
      setLoading(true);
      const params = {};
      if (anioFiltro) {
        params.anio = anioFiltro;
      }
      const response = await api.get('/docente/mensajes/recibidos', { params });
      const mensajes = response.data.mensajes || [];
      // Debug: verificar archivos en mensajes
      mensajes.forEach((mensaje, index) => {
        if (mensaje.archivos && mensaje.archivos.length > 0) {
          console.log(`📎 [FRONTEND] Mensaje recibido ${index} (ID: ${mensaje.id}) tiene ${mensaje.archivos.length} archivo(s):`, mensaje.archivos);
        }
      });
      setMensajesRecibidos(mensajes);
      setMensajesSeleccionados(new Set()); // Limpiar selección al cargar
      
      // Si no hay mensajes y había un filtro de año, limpiarlo
      if (mensajes.length === 0 && anioFiltro) {
        setAnioFiltro(null);
      }
    } catch (error) {
      console.error('Error cargando mensajes recibidos:', error);
      Swal.fire('Error', 'No se pudieron cargar los mensajes recibidos', 'error');
      setMensajesSeleccionados(new Set());
    } finally {
      setLoading(false);
    }
  }, [anioFiltro]);

  // Cargar mensajes enviados
  const cargarMensajesEnviados = useCallback(async () => {
    try {
      setLoading(true);
      const params = {};
      if (anioFiltro) {
        params.anio = anioFiltro;
      }
      const response = await api.get('/docente/mensajes/enviados', { params });
      const mensajes = response.data.mensajes || [];
      // Debug: verificar archivos en mensajes
      mensajes.forEach((mensaje, index) => {
        if (mensaje.archivos && mensaje.archivos.length > 0) {
          console.log(`📎 [FRONTEND] Mensaje ${index} (ID: ${mensaje.id}) tiene ${mensaje.archivos.length} archivo(s):`, mensaje.archivos);
        }
      });
      setMensajesEnviados(mensajes);
      setMensajesSeleccionados(new Set()); // Limpiar selección al cargar
      
      // Si no hay mensajes y había un filtro de año, limpiarlo y recargar sin filtro
      if (mensajes.length === 0 && anioFiltro) {
        setAnioFiltro(null);
        // No recargar aquí porque causaría un loop, el useEffect lo hará
      }
    } catch (error) {
      console.error('Error cargando mensajes enviados:', error);
      Swal.fire('Error', 'No se pudieron cargar los mensajes enviados', 'error');
      // En caso de error, también limpiar selección
      setMensajesSeleccionados(new Set());
    } finally {
      setLoading(false);
    }
  }, [anioFiltro]);

  // Buscar destinatarios
  const buscarDestinatarios = useCallback(async (termino) => {
    if (!termino || termino.trim().length < 2) {
      setResultadosBusqueda([]);
      setMostrarResultados(false);
      return;
    }

    try {
      const response = await api.get('/docente/mensajes/buscar-destinatarios', {
        params: { q: termino }
      });
      // Asegurarse de que siempre sea un array y que los campos sean strings
      const resultados = Array.isArray(response.data?.resultados) 
        ? response.data.resultados.map(r => ({
            ...r,
            nombre_completo: String(r.nombre_completo || ''),
            tipo: String(r.tipo || ''),
            info_adicional: r.info_adicional ? String(r.info_adicional) : null
          }))
        : [];
      setResultadosBusqueda(resultados);
      setMostrarResultados(true);
    } catch (error) {
      console.error('Error buscando destinatarios:', error);
      setResultadosBusqueda([]);
      setMostrarResultados(false);
      // No mostrar error al usuario si no hay resultados, es normal
    }
  }, []);

  // Efecto para buscar destinatarios cuando cambia el término
  useEffect(() => {
    const timeoutId = setTimeout(() => {
      buscarDestinatarios(busquedaDestinatario);
    }, 300);

    return () => clearTimeout(timeoutId);
  }, [busquedaDestinatario, buscarDestinatarios]);

  // Cargar mensajes según la vista
  useEffect(() => {
    if (vista === 'recibidos') {
      cargarMensajesRecibidos();
    } else if (vista === 'enviados') {
      cargarMensajesEnviados();
    }
  }, [vista, cargarMensajesRecibidos, cargarMensajesEnviados]);

  // Obtener años únicos de los mensajes para el filtro
  const [aniosDisponibles, setAniosDisponibles] = useState([]);
  
  const cargarAniosDisponibles = useCallback(async () => {
    try {
      const response = await api.get('/docente/mensajes/anios-disponibles');
      const anios = response.data.anios || [];
      setAniosDisponibles(anios);
      
      // Si el año filtrado ya no está disponible, limpiar el filtro
      if (anioFiltro && !anios.includes(anioFiltro)) {
        setAnioFiltro(null);
      }
    } catch (error) {
      console.error('Error cargando años disponibles:', error);
      // Si falla, calcular desde los mensajes ya cargados como fallback
      const todosMensajes = [...mensajesRecibidos, ...mensajesEnviados];
      const anios = new Set();
      todosMensajes.forEach(m => {
        if (m.fecha_hora) {
          anios.add(new Date(m.fecha_hora).getFullYear());
        }
      });
      const aniosArray = Array.from(anios).sort((a, b) => b - a);
      setAniosDisponibles(aniosArray);
      
      // Si el año filtrado ya no está disponible, limpiar el filtro
      if (anioFiltro && !aniosArray.includes(anioFiltro)) {
        setAnioFiltro(null);
      }
    }
  }, [anioFiltro]); // Incluir anioFiltro para verificar si aún existe

  // Cargar años disponibles solo al montar
  useEffect(() => {
    cargarAniosDisponibles();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []); // Solo al montar

  const obtenerAniosDisponibles = () => {
    return aniosDisponibles;
  };

  // Manejar selección de mensaje
  const toggleMensajeSeleccionado = (mensajeId) => {
    const nuevosSeleccionados = new Set(mensajesSeleccionados);
    if (nuevosSeleccionados.has(mensajeId)) {
      nuevosSeleccionados.delete(mensajeId);
    } else {
      nuevosSeleccionados.add(mensajeId);
    }
    setMensajesSeleccionados(nuevosSeleccionados);
  };

  // Marcar todos / Desmarcar todos
  const toggleMarcarTodos = () => {
    const mensajes = vista === 'recibidos' ? mensajesRecibidos : mensajesEnviados;
    if (mensajesSeleccionados.size === mensajes.length) {
      setMensajesSeleccionados(new Set());
    } else {
      setMensajesSeleccionados(new Set(mensajes.map(m => m.id)));
    }
  };

  // Eliminar mensajes seleccionados
  const eliminarMensajesSeleccionados = async () => {
    if (mensajesSeleccionados.size === 0) {
      Swal.fire('Atención', 'Debe seleccionar al menos un mensaje para eliminar', 'warning');
      return;
    }

    const resultado = await Swal.fire({
      title: '¿Eliminar mensajes?',
      text: `¿Está seguro de eliminar ${mensajesSeleccionados.size} mensaje(s)?`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#ef4444',
      cancelButtonColor: '#6b7280',
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar'
    });

    if (!resultado.isConfirmed) return;

    try {
      const idsArray = Array.from(mensajesSeleccionados);
      const response = await api.delete('/docente/mensajes', {
        data: { mensajesIds: idsArray }
      });

      Swal.fire('Éxito', response.data?.message || `${idsArray.length} mensaje(s) eliminado(s) correctamente`, 'success');
      
      // Limpiar selección
      setMensajesSeleccionados(new Set());
      
      // Recargar años disponibles PRIMERO para actualizar el combo
      // La función cargarAniosDisponibles ya verifica y limpia el filtro si el año ya no existe
      await cargarAniosDisponibles();
      
      // Recargar mensajes de la vista actual
      if (vista === 'recibidos') {
        await cargarMensajesRecibidos();
      } else {
        await cargarMensajesEnviados();
      }
    } catch (error) {
      console.error('Error eliminando mensajes:', error);
      Swal.fire('Error', error.response?.data?.error || 'Error al eliminar mensajes', 'error');
    }
  };

  // Seleccionar destinatario
  const seleccionarDestinatario = (resultado) => {
    const yaSeleccionado = destinatariosSeleccionados.some(
      d => (d.usuario_id && d.usuario_id === resultado.usuario_id) ||
           (d.grupo_id && d.grupo_id === resultado.grupo_id)
    );

    if (!yaSeleccionado) {
      setDestinatariosSeleccionados([...destinatariosSeleccionados, resultado]);
    }
    setBusquedaDestinatario('');
    setMostrarResultados(false);
  };

  // Eliminar destinatario
  const eliminarDestinatario = (index) => {
    setDestinatariosSeleccionados(destinatariosSeleccionados.filter((_, i) => i !== index));
  };

  // Manejar selección de archivos
  const handleArchivoChange = (e) => {
    const files = Array.from(e.target.files);
    setArchivosAdjuntos([...archivosAdjuntos, ...files]);
  };

  // Eliminar archivo adjunto
  const eliminarArchivo = (index) => {
    setArchivosAdjuntos(archivosAdjuntos.filter((_, i) => i !== index));
  };

  // Enviar mensaje
  const enviarMensaje = async () => {
    // Prevenir doble envío
    if (enviando) {
      return;
    }

    if (destinatariosSeleccionados.length === 0) {
      Swal.fire('Error', 'Debe seleccionar al menos un destinatario', 'error');
      return;
    }

    if (!asunto.trim()) {
      Swal.fire('Error', 'El asunto es requerido', 'error');
      return;
    }

    // Verificar que el contenido no esté vacío (sin HTML vacío)
    const contenidoLimpio = contenido.replace(/<[^>]*>/g, '').trim();
    if (!contenidoLimpio) {
      Swal.fire('Error', 'El mensaje es requerido', 'error');
      return;
    }

    setEnviando(true);
    try {
      const usuarios = destinatariosSeleccionados.filter(d => d.usuario_id).map(d => d.usuario_id);
      const grupos = destinatariosSeleccionados.filter(d => d.grupo_id).map(d => d.grupo_id);

      // Crear FormData para enviar archivos
      const formData = new FormData();
      formData.append('destinatarios', JSON.stringify(usuarios));
      formData.append('grupos', JSON.stringify(grupos));
      formData.append('asunto', asunto.trim());
      formData.append('mensaje', contenido);

      // Agregar archivos
      archivosAdjuntos.forEach((archivo) => {
        formData.append('archivos', archivo);
      });

      const response = await api.post('/docente/mensajes/enviar', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });

      // Mostrar mensaje detallado
      let mensajeExito = response.data.message;
      if (response.data.grupos > 0) {
        mensajeExito += `\n\n📊 Resumen:\n- ${response.data.grupos} grupo(s) seleccionado(s)\n- ${response.data.alumnosEnGrupos} alumno(s) recibirán el mensaje`;
        if (response.data.destinatariosDirectos > 0) {
          mensajeExito += `\n- ${response.data.destinatariosDirectos} destinatario(s) directo(s)`;
        }
      }
      
      Swal.fire({
        title: '¡Mensaje Enviado!',
        text: mensajeExito,
        icon: 'success',
        confirmButtonText: 'Aceptar'
      });
      
      // Limpiar formulario
      setDestinatariosSeleccionados([]);
      setAsunto('');
      setContenido('');
      setArchivosAdjuntos([]);
      setBusquedaDestinatario('');
      setVista('enviados');
      cargarMensajesEnviados();
    } catch (error) {
      console.error('Error enviando mensaje:', error);
      Swal.fire('Error', error.response?.data?.error || 'Error al enviar el mensaje', 'error');
    } finally {
      setEnviando(false);
    }
  };

  // Formatear fecha
  const formatearFecha = (fechaHora) => {
    const fecha = new Date(fechaHora);
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);
    const fechaMsg = new Date(fecha);
    fechaMsg.setHours(0, 0, 0, 0);

    if (fechaMsg.getTime() === hoy.getTime()) {
      return fecha.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit' });
    }

    return fecha.toLocaleDateString('es-PE', {
      day: 'numeric',
      month: 'short',
      year: fecha.getFullYear() !== hoy.getFullYear() ? 'numeric' : undefined
    });
  };

  // Procesar HTML del mensaje para convertir URLs relativas de imágenes a absolutas
  const procesarHTMLMensaje = (html) => {
    if (!html) return '';
    
    const baseURL = api.defaults.baseURL.replace('/api', '');
    
    // Convertir URLs relativas de imágenes a absolutas
    // Buscar todas las imágenes con src que empiecen con /uploads/
    const htmlProcesado = html.replace(
      /<img([^>]*)\ssrc=["'](\/uploads\/[^"']+)["']([^>]*)>/gi,
      (match, before, src, after) => {
        // Si ya es una URL completa (http/https), no hacer nada
        if (src.startsWith('http://') || src.startsWith('https://')) {
          return match;
        }
        // Convertir URL relativa a absoluta
        const absoluteUrl = `${baseURL}${src}`;
        return `<img${before} src="${absoluteUrl}"${after}>`;
      }
    );
    
    return htmlProcesado;
  };

  const mensajes = vista === 'recibidos' ? mensajesRecibidos : mensajesEnviados;
  const tituloLista = vista === 'recibidos' ? 'Mensajes Recibidos' : 'Mensajes Enviados';

  return (
    <DashboardLayout>
      <div className="docente-mensajes">
        <div className="page-header">
          <h1>Mensajes</h1>
          <p>Gestiona tus comunicaciones</p>
        </div>

        <div className="mensajes-container">
          {/* Sidebar */}
          <div className="mensajes-sidebar">
            <button 
              className="btn-nuevo-mensaje"
              onClick={() => setVista('nuevo')}
            >
              ✉️ Enviar Nuevo
            </button>

            <div className="sidebar-seccion">
              <h3>LISTA DE MENSAJES</h3>
              <button
                className={`sidebar-item ${vista === 'recibidos' ? 'active' : ''}`}
                onClick={() => setVista('recibidos')}
              >
                <span className="sidebar-icon">📥</span>
                Mensajes Recibidos
              </button>
              <button
                className={`sidebar-item ${vista === 'enviados' ? 'active' : ''}`}
                onClick={() => setVista('enviados')}
              >
                <span className="sidebar-icon">📤</span>
                Mensajes Enviados
              </button>
            </div>
          </div>

          {/* Contenido Principal */}
          <div className="mensajes-content">
            {vista === 'nuevo' ? (
              <div className="nuevo-mensaje-form">
                <h2>Nuevo Mensaje</h2>
                
                {/* Campo Para */}
                <div className="form-group">
                  <label>Para:</label>
                  <div className="destinatarios-input-container">
                    <div className="destinatarios-seleccionados">
                      {destinatariosSeleccionados.map((dest, index) => {
                        const nombreCompleto = String(dest?.nombre_completo || 'Sin nombre');
                        const tipo = String(dest?.tipo || '');
                        return (
                          <span key={index} className="destinatario-tag">
                            {nombreCompleto}
                            {tipo && <span className="destinatario-tipo">{tipo}</span>}
                            <button
                              type="button"
                              className="destinatario-remove"
                              onClick={() => eliminarDestinatario(index)}
                            >
                              ×
                            </button>
                          </span>
                        );
                      })}
                    </div>
                    <input
                      type="text"
                      className="destinatarios-input"
                      placeholder="Buscar destinatario, grupo, alumno o apoderado..."
                      value={busquedaDestinatario}
                      onChange={(e) => setBusquedaDestinatario(e.target.value)}
                      onFocus={() => setMostrarResultados(true)}
                    />
                    {mostrarResultados && resultadosBusqueda.length > 0 && (
                      <div className="resultados-busqueda">
                        {resultadosBusqueda.map((resultado, index) => {
                          // Asegurarse de que todos los valores sean strings válidos
                          const nombreCompleto = String(resultado?.nombre_completo || 'Sin nombre');
                          const tipo = String(resultado?.tipo || '');
                          const infoAdicional = resultado?.info_adicional ? String(resultado.info_adicional) : null;
                          
                          return (
                            <div
                              key={index}
                              className="resultado-item"
                              onClick={() => seleccionarDestinatario(resultado)}
                            >
                              <div className="resultado-info">
                                <span className="resultado-nombre">{nombreCompleto}</span>
                                {tipo && <span className="resultado-tipo">{tipo}</span>}
                              </div>
                              {infoAdicional && (
                                <span className="resultado-extra">{infoAdicional}</span>
                              )}
                            </div>
                          );
                        })}
                      </div>
                    )}
                    {mostrarResultados && resultadosBusqueda.length === 0 && busquedaDestinatario.trim().length >= 2 && (
                      <div className="resultados-busqueda">
                        <div className="resultado-item resultado-vacio">
                          <span>No se encontraron resultados</span>
                        </div>
                      </div>
                    )}
                  </div>
                </div>

                {/* Campo Asunto */}
                <div className="form-group">
                  <label>Asunto:</label>
                  <input
                    type="text"
                    className="form-input"
                    value={asunto}
                    onChange={(e) => setAsunto(e.target.value)}
                    placeholder="Asunto del mensaje"
                  />
                </div>

                {/* Campo Mensaje con Editor de Texto Enriquecido */}
                <div className="form-group">
                  <label htmlFor="mensaje-editor">Mensaje:</label>
                  <div id="mensaje-editor-wrapper">
                    <ReactQuill
                      ref={quillRef}
                      theme="snow"
                      value={contenido}
                      onChange={setContenido}
                      placeholder="Escribe tu mensaje aquí..."
                      modules={{
                        toolbar: [
                          [{ 'header': [1, 2, 3, false] }],
                          ['bold', 'italic', 'underline', 'strike'],
                          [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                          [{ 'color': [] }, { 'background': [] }],
                          [{ 'align': [] }],
                          ['link', 'image'],
                          ['clean']
                        ]
                      }}
                      formats={[
                        'header', 'bold', 'italic', 'underline', 'strike',
                        'list', 'bullet', 'color', 'background', 'align',
                        'link', 'image'
                      ]}
                    />
                  </div>
                </div>

                {/* Campo Archivos Adjuntos */}
                <div className="form-group">
                  <label>Archivos Adjuntos:</label>
                  <div className="archivos-container">
                    <input
                      type="file"
                      id="archivos-input"
                      multiple
                      onChange={handleArchivoChange}
                      style={{ display: 'none' }}
                    />
                    <label htmlFor="archivos-input" className="btn-adjuntar-archivo">
                      📎 Adjuntar Archivos
                    </label>
                    {archivosAdjuntos.length > 0 && (
                      <div className="archivos-lista">
                        {archivosAdjuntos.map((archivo, index) => (
                          <div key={index} className="archivo-item">
                            <span className="archivo-nombre">{archivo.name}</span>
                            <span className="archivo-tamaño">
                              {(archivo.size / 1024).toFixed(2)} KB
                            </span>
                            <button
                              type="button"
                              className="archivo-eliminar"
                              onClick={() => eliminarArchivo(index)}
                            >
                              ×
                            </button>
                          </div>
                        ))}
                      </div>
                    )}
                  </div>
                </div>

                {/* Botones */}
                <div className="form-actions">
                  <button
                    className="btn-cancelar"
                    onClick={() => {
                      setVista('recibidos');
                      setDestinatariosSeleccionados([]);
                      setAsunto('');
                      setContenido('');
                      setArchivosAdjuntos([]);
                      setBusquedaDestinatario('');
                    }}
                  >
                    Cancelar
                  </button>
                  <button
                    className="btn-enviar"
                    onClick={enviarMensaje}
                    disabled={enviando}
                  >
                    {enviando ? '⏳ Enviando...' : '✉️ Enviar Mensaje'}
                  </button>
                </div>
              </div>
            ) : (
              <div className="mensajes-lista">
                <div className="lista-header">
                  <h2>{tituloLista}</h2>
                  <div className="lista-controls">
                    {/* Filtro por año */}
                    <div className="filtro-anio">
                      <label htmlFor="filtro-anio-select">Filtrar por año:</label>
                      <select
                        id="filtro-anio-select"
                        value={anioFiltro || ''}
                        onChange={(e) => setAnioFiltro(e.target.value ? parseInt(e.target.value) : null)}
                        className="select-anio"
                      >
                        <option value="">Todos los años</option>
                        {obtenerAniosDisponibles().map(anio => (
                          <option key={anio} value={anio}>{anio}</option>
                        ))}
                      </select>
                    </div>
                    {/* Botones de acción */}
                    {mensajesSeleccionados.size > 0 && (
                      <button
                        className="btn-eliminar-seleccionados"
                        onClick={eliminarMensajesSeleccionados}
                      >
                        🗑️ Eliminar ({mensajesSeleccionados.size})
                      </button>
                    )}
                    <div className="lista-pagination">
                      <button className="btn-pagination">‹</button>
                      <button className="btn-pagination">›</button>
                    </div>
                  </div>
                </div>

                {loading ? (
                  <div className="loading-mensajes">Cargando mensajes...</div>
                ) : mensajes.length === 0 ? (
                  <div className="empty-mensajes">
                    <div className="empty-icon">📭</div>
                    <h3>No hay mensajes</h3>
                    <p>No tienes mensajes {vista === 'recibidos' ? 'recibidos' : 'enviados'}</p>
                  </div>
                ) : (
                  <div className="mensajes-table">
                    {/* Checkbox "Marcar todos" */}
                    {mensajes.length > 0 && (
                      <div className="mensaje-row-header">
                        <div className="mensaje-checkbox">
                          <input
                            type="checkbox"
                            checked={mensajesSeleccionados.size === mensajes.length && mensajes.length > 0}
                            onChange={toggleMarcarTodos}
                            onClick={(e) => e.stopPropagation()}
                          />
                        </div>
                        <div className="mensaje-header-text">
                          {mensajesSeleccionados.size > 0 
                            ? `${mensajesSeleccionados.size} seleccionado(s)`
                            : 'Seleccionar todos'}
                        </div>
                      </div>
                    )}
                    {mensajes.map((mensaje) => {
                      const nombre = vista === 'recibidos' 
                        ? mensaje.remitente_nombre_completo 
                        : mensaje.destinatario_nombre_completo;
                      const esNoLeido = mensaje.estado === 'NO_LEIDO';
                      const estaSeleccionado = mensajesSeleccionados.has(mensaje.id);

                      return (
                        <div
                          key={mensaje.id}
                          className={`mensaje-row ${esNoLeido ? 'no-leido' : ''} ${estaSeleccionado ? 'seleccionado' : ''}`}
                          onClick={() => {
                            console.log('🔍 [DEBUG] Mensaje seleccionado:', mensaje);
                            console.log('🔍 [DEBUG] Archivos del mensaje:', mensaje.archivos);
                            setMensajeSeleccionado(mensaje);
                          }}
                        >
                          <div className="mensaje-checkbox" onClick={(e) => e.stopPropagation()}>
                            <input
                              type="checkbox"
                              checked={estaSeleccionado}
                              onChange={() => toggleMensajeSeleccionado(mensaje.id)}
                            />
                          </div>
                          <div className="mensaje-favorito">
                            <span className={mensaje.favorito === 'SI' ? 'star-filled' : 'star-empty'}>
                              ★
                            </span>
                          </div>
                          <div className="mensaje-info">
                            <div className="mensaje-nombre">
                              {nombre || 'Sin nombre'}
                            </div>
                            <div className="mensaje-asunto">
                              {mensaje.asunto}
                            </div>
                          </div>
                          <div className="mensaje-fecha">
                            {formatearFecha(mensaje.fecha_hora)}
                          </div>
                        </div>
                      );
                    })}
                  </div>
                )}
              </div>
            )}
          </div>
        </div>

        {/* Modal de detalle de mensaje */}
        {mensajeSeleccionado && (
          <div className="mensaje-modal-overlay" onClick={() => setMensajeSeleccionado(null)}>
            <div className="mensaje-modal" onClick={(e) => e.stopPropagation()}>
              <div className="mensaje-modal-header">
                <h3>{mensajeSeleccionado.asunto}</h3>
                <button className="mensaje-modal-close" onClick={() => setMensajeSeleccionado(null)}>
                  ×
                </button>
              </div>
              <div className="mensaje-modal-body">
                <div className="mensaje-modal-info">
                  <div>
                    <strong>{vista === 'recibidos' ? 'De:' : 'Para:'}</strong>{' '}
                    {vista === 'recibidos' 
                      ? mensajeSeleccionado.remitente_nombre_completo 
                      : mensajeSeleccionado.destinatario_nombre_completo}
                  </div>
                  <div>
                    <strong>Fecha:</strong> {new Date(mensajeSeleccionado.fecha_hora).toLocaleString('es-PE')}
                  </div>
                </div>
                <div 
                  className="mensaje-modal-contenido" 
                  dangerouslySetInnerHTML={{ __html: procesarHTMLMensaje(mensajeSeleccionado.mensaje) }}
                  style={{
                    wordBreak: 'break-word'
                  }}
                />
                
                {/* Archivos Adjuntos */}
                {(() => {
                  console.log('🔍 [DEBUG MODAL] mensajeSeleccionado:', mensajeSeleccionado);
                  console.log('🔍 [DEBUG MODAL] mensajeSeleccionado.archivos:', mensajeSeleccionado.archivos);
                  console.log('🔍 [DEBUG MODAL] Es array?', Array.isArray(mensajeSeleccionado.archivos));
                  console.log('🔍 [DEBUG MODAL] Longitud:', mensajeSeleccionado.archivos?.length);
                  
                  if (mensajeSeleccionado.archivos && Array.isArray(mensajeSeleccionado.archivos) && mensajeSeleccionado.archivos.length > 0) {
                    return (
                      <div className="mensaje-archivos">
                        <h4>Archivos Adjuntos ({mensajeSeleccionado.archivos.length}):</h4>
                        <div className="archivos-lista-modal">
                          {mensajeSeleccionado.archivos.map((archivo, index) => {
                        // Construir URL completa del archivo
                        const baseURL = api.defaults.baseURL.replace('/api', '');
                        // El backend devuelve archivo_url como ruta relativa (/uploads/mensajes/filename)
                        const archivoUrl = archivo.archivo_url 
                          ? `${baseURL}${archivo.archivo_url}`
                          : archivo.archivo
                          ? `${baseURL}/uploads/mensajes/${archivo.archivo}`
                          : null;
                        
                        if (!archivoUrl) {
                          console.warn('⚠️ [DEBUG] Archivo sin URL válida:', archivo);
                          return null;
                        }
                        
                        // Determinar si es imagen
                        const nombreArchivo = archivo.nombre_archivo || archivo.archivo || '';
                        const esImagen = /\.(jpg|jpeg|png|gif|webp)$/i.test(nombreArchivo);
                        
                        return (
                          <div key={archivo.id || `archivo-${index}`} className="archivo-item-modal">
                            {esImagen ? (
                              <div className="archivo-imagen">
                                <img 
                                  src={archivoUrl} 
                                  alt={nombreArchivo || 'Imagen adjunta'}
                                  style={{ maxWidth: '300px', maxHeight: '300px', borderRadius: '8px' }}
                                  onError={(e) => {
                                    console.error('❌ Error cargando imagen:', archivoUrl, archivo);
                                    e.target.style.display = 'none';
                                  }}
                                />
                                <a
                                  href={archivoUrl}
                                  target="_blank"
                                  rel="noopener noreferrer"
                                  className="archivo-enlace"
                                  download
                                >
                                  📎 {nombreArchivo}
                                </a>
                              </div>
                            ) : (
                              <a
                                href={archivoUrl}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="archivo-enlace"
                                download
                              >
                                📎 {nombreArchivo}
                              </a>
                            )}
                          </div>
                        );
                      })}
                    </div>
                  </div>
                    );
                  }
                  return null;
                })()}
              </div>
            </div>
          </div>
        )}
      </div>
    </DashboardLayout>
  );
}

export default DocenteMensajes;
