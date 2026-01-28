import React, { useState, useEffect, useRef, useMemo } from 'react';
import { createPortal } from 'react-dom';
import { useNavigate } from 'react-router-dom';
import DashboardLayout from '../components/DashboardLayout';
import api from '../services/api';
import Swal from 'sweetalert2';
import jsPDF from 'jspdf';
import ReactQuill from 'react-quill';
import 'react-quill/dist/quill.snow.css';
import './DocenteCursos.css';

function DocenteCursos() {
  const navigate = useNavigate();
  const [loading, setLoading] = useState(true);
  const [cursos, setCursos] = useState([]);
  const [selectedCurso, setSelectedCurso] = useState(null);
  const [cursoInfo, setCursoInfo] = useState(null); // Información del curso seleccionado
  const [alumnos, setAlumnos] = useState([]);
  const [loadingAlumnos, setLoadingAlumnos] = useState(false);
  const [openDropdown, setOpenDropdown] = useState(null);
  const [openDropdownAlumno, setOpenDropdownAlumno] = useState(null); // { id, top, left } o null
  const [dropdownPosition, setDropdownPosition] = useState(null);
  const [mostrarModalEstrellas, setMostrarModalEstrellas] = useState(false);
  const [mostrarModalIncidencias, setMostrarModalIncidencias] = useState(false);
  const [alumnoSeleccionado, setAlumnoSeleccionado] = useState(null);
  const [historialEstrellas, setHistorialEstrellas] = useState([]);
  const [historialIncidencias, setHistorialIncidencias] = useState([]);
  const [totalEstrellas, setTotalEstrellas] = useState(0);
  const [totalIncidencias, setTotalIncidencias] = useState(0);
  const [loadingEstrellas, setLoadingEstrellas] = useState(false);
  const [loadingIncidencias, setLoadingIncidencias] = useState(false);
  const [formEstrellas, setFormEstrellas] = useState({ points: '', description: '' });
  const [formIncidencias, setFormIncidencias] = useState({ description: '' });
  const [guardandoEstrellas, setGuardandoEstrellas] = useState(false);
  const [guardandoIncidencias, setGuardandoIncidencias] = useState(false);
  const [mostrarModalNotasDetalladas, setMostrarModalNotasDetalladas] = useState(false);
  const [notasDetalladas, setNotasDetalladas] = useState(null);
  const [loadingNotasDetalladas, setLoadingNotasDetalladas] = useState(false);
  const [cicloSeleccionado, setCicloSeleccionado] = useState(1); // 1-4 = Bimestres
  const dropdownRef = useRef({});
  const buttonRef = useRef({});
  
  // Estados para modal de mensaje
  const [mostrarModalMensaje, setMostrarModalMensaje] = useState(false);
  const [cursoParaMensaje, setCursoParaMensaje] = useState(null); // Curso seleccionado para enviar mensaje
  const [asuntoMensaje, setAsuntoMensaje] = useState('');
  const [contenidoMensaje, setContenidoMensaje] = useState('');
  const [archivosAdjuntosMensaje, setArchivosAdjuntosMensaje] = useState([]);
  const [enviandoMensaje, setEnviandoMensaje] = useState(false);
  const quillRefMensaje = useRef(null);

  // Estados para modal de horario
  const [mostrarModalHorario, setMostrarModalHorario] = useState(false);
  const [cursoParaHorario, setCursoParaHorario] = useState(null);
  const [horarioCurso, setHorarioCurso] = useState([]);
  const [loadingHorario, setLoadingHorario] = useState(false);
  const [infoCursoHorario, setInfoCursoHorario] = useState(null);

  // Estados para modal de aula virtual
  const [mostrarModalAulaVirtual, setMostrarModalAulaVirtual] = useState(false);
  const [cursoParaAulaVirtual, setCursoParaAulaVirtual] = useState(null);
  const [linkAulaVirtual, setLinkAulaVirtual] = useState('');
  const [habilitarAula, setHabilitarAula] = useState('NO');
  const [loadingAulaVirtual, setLoadingAulaVirtual] = useState(false);
  const [guardandoAulaVirtual, setGuardandoAulaVirtual] = useState(false);

  // Estados para modal de notas
  const [mostrarModalNotas, setMostrarModalNotas] = useState(false);
  const [cursoParaNotas, setCursoParaNotas] = useState(null);
  const [datosNotas, setDatosNotas] = useState(null); // { curso, criterios, alumnos, ciclo }
  const [cicloSeleccionadoNotas, setCicloSeleccionadoNotas] = useState(1);
  const [loadingNotas, setLoadingNotas] = useState(false);
  const [guardandoNotas, setGuardandoNotas] = useState(false);
  const [notasEditadas, setNotasEditadas] = useState({}); // { matricula_id: { criterio_id: { indicador_id: [notas...] } } }
  const [forceUpdate, setForceUpdate] = useState(0); // Para forzar re-render cuando cambien las notas

  useEffect(() => {
    cargarCursos();
  }, []);

  // Configurar handler de imágenes para ReactQuill en modal de mensaje
  useEffect(() => {
    if (quillRefMensaje.current && mostrarModalMensaje) {
      const quill = quillRefMensaje.current.getEditor();
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
  }, [mostrarModalMensaje]);

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

  // Cerrar dropdowns de alumnos al hacer clic fuera
  useEffect(() => {
    if (!openDropdownAlumno) return;
    
    const handleClickOutside = (event) => {
      const target = event.target;
      
      // Verificar si el clic está dentro de cualquier dropdown
      const isDropdownMenu = target.closest('.dropdown-menu-alumno');
      const isDropdownButton = target.closest('.btn-opciones-dropdown-alumno');
      const isDropdownItem = target.closest('.dropdown-item');
      
      // Solo cerrar si el clic NO está dentro del dropdown
      if (!isDropdownMenu && !isDropdownButton && !isDropdownItem) {
        setTimeout(() => {
          setOpenDropdownAlumno(null);
        }, 100);
      }
    };
    
    const timeoutId = setTimeout(() => {
      document.addEventListener('click', handleClickOutside, false);
    }, 50);
    
    return () => {
      clearTimeout(timeoutId);
      document.removeEventListener('click', handleClickOutside, false);
    };
  }, [openDropdownAlumno]);

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

  const cargarAlumnos = async (cursoId) => {
    try {
      setLoadingAlumnos(true);
      const response = await api.get(`/docente/cursos/${cursoId}/alumnos`);
      setAlumnos(response.data.alumnos || []);
      setCursoInfo(response.data.curso);
      setSelectedCurso(cursoId);
      
      // Hacer scroll al inicio de la página cuando se carga la lista de alumnos
      setTimeout(() => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
        const docenteCursosContainer = document.querySelector('.docente-cursos');
        if (docenteCursosContainer) {
          docenteCursosContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      }, 100);
    } catch (error) {
      console.error('Error cargando alumnos:', error);
    } finally {
      setLoadingAlumnos(false);
    }
  };

  const handleVolver = () => {
    setSelectedCurso(null);
    setCursoInfo(null);
    setAlumnos([]);
    setOpenDropdownAlumno(null);
    setMostrarModalEstrellas(false);
    setMostrarModalIncidencias(false);
    setAlumnoSeleccionado(null);
  };

  const abrirModalEstrellas = async (alumno) => {
    setAlumnoSeleccionado(alumno);
    setMostrarModalEstrellas(true);
    setFormEstrellas({ points: '', description: '' });
    await cargarHistorialEstrellas(alumno.id);
  };

  const abrirModalIncidencias = async (alumno) => {
    setAlumnoSeleccionado(alumno);
    setMostrarModalIncidencias(true);
    setFormIncidencias({ description: '' });
    await cargarHistorialIncidencias(alumno.id);
  };

  const cargarHistorialEstrellas = async (alumnoId) => {
    if (!selectedCurso || !alumnoId) return;
    
    try {
      setLoadingEstrellas(true);
      const response = await api.get(`/docente/cursos/${selectedCurso}/alumnos/${alumnoId}/estrellas`);
      setHistorialEstrellas(response.data.estrellas || []);
      setTotalEstrellas(response.data.total_estrellas || 0);
    } catch (error) {
      console.error('Error cargando historial de estrellas:', error);
      Swal.fire({
        title: 'Error',
        text: 'No se pudo cargar el historial de estrellas',
        icon: 'error',
        zIndex: 100001
      });
    } finally {
      setLoadingEstrellas(false);
    }
  };

  const handleDarEstrellas = async (e) => {
    e.preventDefault();
    
    if (!formEstrellas.points || formEstrellas.points <= 0) {
      Swal.fire({
        title: 'Error',
        text: 'La cantidad de estrellas debe ser mayor a 0',
        icon: 'error',
        zIndex: 100001
      });
      return;
    }

    if (!formEstrellas.description || formEstrellas.description.trim() === '') {
      Swal.fire({
        title: 'Error',
        text: 'La descripción es requerida',
        icon: 'error',
        zIndex: 100001
      });
      return;
    }

    try {
      setGuardandoEstrellas(true);
      await api.post(
        `/docente/cursos/${selectedCurso}/alumnos/${alumnoSeleccionado.id}/estrellas`,
        {
          points: parseInt(formEstrellas.points),
          description: formEstrellas.description.trim()
        }
      );

      Swal.fire({
        title: '¡Éxito!',
        text: `${formEstrellas.points} estrella(s) asignada(s) correctamente`,
        icon: 'success',
        zIndex: 100001
      });
      setFormEstrellas({ points: '', description: '' });
      
      // Recargar historial y actualizar lista de alumnos
      await cargarHistorialEstrellas(alumnoSeleccionado.id);
      await cargarAlumnos(selectedCurso);
    } catch (error) {
      console.error('Error dando estrellas:', error);
      Swal.fire({
        title: 'Error',
        text: error.response?.data?.error || 'No se pudieron asignar las estrellas',
        icon: 'error',
        zIndex: 100001
      });
    } finally {
      setGuardandoEstrellas(false);
    }
  };

  const handleEliminarEstrellas = async (incidentId, points) => {
    const result = await Swal.fire({
      title: '¿Estás seguro?',
      text: `Se eliminarán ${points} estrella(s). Esta acción no se puede deshacer.`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar',
      zIndex: 100001 // Mayor que el modal de estrellas (100000)
    });

    if (result.isConfirmed) {
      try {
        await api.delete(
          `/docente/cursos/${selectedCurso}/alumnos/${alumnoSeleccionado.id}/estrellas/${incidentId}`
        );

        Swal.fire({
          title: '¡Eliminado!',
          text: 'Las estrellas han sido eliminadas correctamente',
          icon: 'success',
          zIndex: 100001
        });
        
        // Recargar historial y actualizar lista de alumnos
        await cargarHistorialEstrellas(alumnoSeleccionado.id);
        await cargarAlumnos(selectedCurso);
      } catch (error) {
        console.error('Error eliminando estrellas:', error);
        Swal.fire({
          title: 'Error',
          text: error.response?.data?.error || 'No se pudieron eliminar las estrellas',
          icon: 'error',
          zIndex: 100001
        });
      }
    }
  };

  const cargarHistorialIncidencias = async (alumnoId) => {
    if (!selectedCurso || !alumnoId) return;
    
    try {
      setLoadingIncidencias(true);
      const response = await api.get(`/docente/cursos/${selectedCurso}/alumnos/${alumnoId}/incidencias`);
      setHistorialIncidencias(response.data.incidencias || []);
      setTotalIncidencias(response.data.total_incidencias || 0);
    } catch (error) {
      console.error('Error cargando historial de incidencias:', error);
      Swal.fire({
        title: 'Error',
        text: 'No se pudo cargar el historial de incidencias',
        icon: 'error',
        zIndex: 100001
      });
    } finally {
      setLoadingIncidencias(false);
    }
  };

  const abrirModalNotasDetalladas = async (alumno) => {
    console.log('=== INICIANDO abrirModalNotasDetalladas ===');
    console.log('selectedCurso:', selectedCurso);
    console.log('alumno:', alumno);
    
    if (!selectedCurso || !alumno) {
      console.error('No se puede abrir modal: selectedCurso o alumno faltante', { selectedCurso, alumno });
      Swal.fire({
        title: 'Error',
        text: 'No se puede abrir el modal. Faltan datos necesarios.',
        icon: 'error',
        zIndex: 100001
      });
      return;
    }
    
    try {
      console.log('Abriendo modal de notas detalladas para alumno:', alumno.id);
      
      // Primero establecer los estados necesarios
      setAlumnoSeleccionado(alumno);
      setCicloSeleccionado(1); // Empezar con el primer bimestre
      setNotasDetalladas(null); // Resetear notas antes de cargar
      
      // Luego abrir el modal
      setMostrarModalNotasDetalladas(true);
      console.log('Estado mostrarModalNotasDetalladas establecido a true');
      
      // Cargar las notas después de abrir el modal
      await cargarNotasDetalladas(alumno.id);
    } catch (error) {
      console.error('Error en abrirModalNotasDetalladas:', error);
      Swal.fire({
        title: 'Error',
        text: 'Ocurrió un error al abrir el modal de notas detalladas',
        icon: 'error',
        zIndex: 100001
      });
    }
  };

  const cargarNotasDetalladas = async (alumnoId) => {
    if (!selectedCurso || !alumnoId) return;
    
    try {
      setLoadingNotasDetalladas(true);
      const response = await api.get(`/docente/cursos/${selectedCurso}/alumnos/${alumnoId}/notas-detalladas`);
      setNotasDetalladas(response.data);
    } catch (error) {
      console.error('Error cargando notas detalladas:', error);
      Swal.fire({
        title: 'Error',
        text: error.response?.data?.error || 'No se pudieron cargar las notas detalladas',
        icon: 'error',
        zIndex: 100001
      });
    } finally {
      setLoadingNotasDetalladas(false);
    }
  };

  const handleRegistrarIncidencia = async (e) => {
    e.preventDefault();
    
    if (!formIncidencias.description || formIncidencias.description.trim() === '') {
      Swal.fire({
        title: 'Error',
        text: 'La descripción es requerida',
        icon: 'error',
        zIndex: 100001
      });
      return;
    }

    try {
      setGuardandoIncidencias(true);
      await api.post(
        `/docente/cursos/${selectedCurso}/alumnos/${alumnoSeleccionado.id}/incidencias`,
        {
          description: formIncidencias.description.trim()
        }
      );

      Swal.fire({
        title: '¡Éxito!',
        text: 'Incidencia registrada correctamente',
        icon: 'success',
        zIndex: 100001
      });
      setFormIncidencias({ description: '' });
      
      // Recargar historial
      await cargarHistorialIncidencias(alumnoSeleccionado.id);
    } catch (error) {
      console.error('Error registrando incidencia:', error);
      Swal.fire({
        title: 'Error',
        text: error.response?.data?.error || 'No se pudo registrar la incidencia',
        icon: 'error',
        zIndex: 100001
      });
    } finally {
      setGuardandoIncidencias(false);
    }
  };

  const handleEliminarIncidencia = async (incidentId) => {
    const result = await Swal.fire({
      title: '¿Estás seguro?',
      text: 'Se eliminará esta incidencia. Esta acción no se puede deshacer.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar',
      zIndex: 100001
    });

    if (result.isConfirmed) {
      try {
        await api.delete(
          `/docente/cursos/${selectedCurso}/alumnos/${alumnoSeleccionado.id}/incidencias/${incidentId}`
        );

        Swal.fire({
          title: '¡Eliminado!',
          text: 'La incidencia ha sido eliminada correctamente',
          icon: 'success',
          zIndex: 100001
        });
        
        // Recargar historial
        await cargarHistorialIncidencias(alumnoSeleccionado.id);
      } catch (error) {
        console.error('Error eliminando incidencia:', error);
        Swal.fire({
          title: 'Error',
          text: error.response?.data?.error || 'No se pudo eliminar la incidencia',
          icon: 'error',
          zIndex: 100001
        });
      }
    }
  };

  const formatearFecha = (fecha) => {
    if (!fecha) return '';
    const date = new Date(fecha);
    const dia = String(date.getDate()).padStart(2, '0');
    const mes = String(date.getMonth() + 1).padStart(2, '0');
    const año = date.getFullYear();
    let horas = date.getHours();
    const minutos = String(date.getMinutes()).padStart(2, '0');
    const ampm = horas >= 12 ? 'PM' : 'AM';
    horas = horas % 12;
    horas = horas ? horas : 12;
    return `${dia}-${mes}-${año} ${String(horas).padStart(2, '0')}:${minutos} ${ampm}`;
  };

  const formatearFechaPDF = (fecha) => {
    if (!fecha) return '';
    const date = new Date(fecha);
    const dia = String(date.getDate()).padStart(2, '0');
    const mes = String(date.getMonth() + 1).padStart(2, '0');
    const año = date.getFullYear();
    let horas = date.getHours();
    const minutos = String(date.getMinutes()).padStart(2, '0');
    const ampm = horas >= 12 ? 'PM' : 'AM';
    horas = horas % 12;
    horas = horas ? horas : 12;
    return `${dia}/${mes}/${año} ${String(horas).padStart(2, '0')}:${minutos} ${ampm}`;
  };

  const exportarPDFEstrellas = () => {
    if (!alumnoSeleccionado || historialEstrellas.length === 0) {
      Swal.fire({
        title: 'Sin datos',
        text: 'No hay estrellas para exportar',
        icon: 'info',
        zIndex: 100001
      });
      return;
    }

    const doc = new jsPDF('p', 'mm', 'a4');
    const pageWidth = doc.internal.pageSize.getWidth();
    const pageHeight = doc.internal.pageSize.getHeight();
    const margin = 15;
    const contentWidth = pageWidth - (margin * 2);
    let yPosition = margin;

    // Colores - Azul claro para estrellas
    const colorHeader = [96, 165, 250]; // #60a5fa (azul claro)
    const colorBorder = [59, 130, 246]; // #3b82f6 (borde más oscuro)
    const colorText = [30, 41, 59]; // #1e293b
    const colorLight = [219, 234, 254]; // #dbeafe
    const colorDark = [30, 64, 175]; // #1e40af

    // ========== HEADER PRINCIPAL ==========
    doc.setFillColor(...colorHeader);
    doc.rect(margin, yPosition, contentWidth, 30, 'F');
    
    // Borde del header
    doc.setDrawColor(...colorBorder);
    doc.setLineWidth(0.5);
    doc.rect(margin, yPosition, contentWidth, 30);
    
    // Título (sin emoji, jsPDF no los renderiza bien)
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(22);
    doc.setFont('helvetica', 'bold');
    doc.text('REPORTE DE ESTRELLAS', margin + 5, yPosition + 12);
    
    // Información del alumno
    doc.setFontSize(12);
    doc.setFont('helvetica', 'normal');
    doc.text(`Alumno: ${alumnoSeleccionado.nombre_completo}`, margin + 5, yPosition + 22);
    
    yPosition += 35;

    // ========== CUADRO DE INFORMACIÓN DEL CURSO ==========
    if (cursoInfo) {
      // Fondo del cuadro
      doc.setFillColor(...colorLight);
      doc.rect(margin, yPosition, contentWidth, 25, 'F');
      
      // Borde del cuadro
      doc.setDrawColor(...colorBorder);
      doc.setLineWidth(0.3);
      doc.rect(margin, yPosition, contentWidth, 25);
      
      // Título del cuadro
      doc.setFillColor(...colorHeader);
      doc.rect(margin, yPosition, contentWidth, 7, 'F');
      doc.setTextColor(255, 255, 255);
      doc.setFontSize(11);
      doc.setFont('helvetica', 'bold');
      doc.text('INFORMACIÓN DEL CURSO', margin + 5, yPosition + 5);
      
      yPosition += 10;
      
      // Contenido del cuadro
      doc.setTextColor(...colorText);
      doc.setFont('helvetica', 'normal');
      doc.setFontSize(9);
      doc.text(`Curso: ${cursoInfo.curso_nombre}`, margin + 5, yPosition);
      yPosition += 5;
      doc.text(`Grado: ${cursoInfo.grado}° ${cursoInfo.seccion}`, margin + 5, yPosition);
      yPosition += 5;
      doc.text(`Nivel: ${cursoInfo.nivel_nombre} - Turno: ${cursoInfo.turno_nombre}`, margin + 5, yPosition);
      yPosition += 5;
      
      // Total destacado (sin emoji) - con más espacio del borde
      doc.setFont('helvetica', 'bold');
      doc.setFontSize(10);
      doc.setTextColor(...colorDark);
      doc.text(`Total de Estrellas: ${totalEstrellas}`, margin + 5, yPosition - 2);
      yPosition += 12;
    }

    // ========== TABLA DE ESTRELLAS ==========
    doc.setFontSize(12);
    doc.setFont('helvetica', 'bold');
    doc.setTextColor(...colorText);
    doc.text('HISTORIAL DE ESTRELLAS', margin, yPosition);
    yPosition += 8;

    // Encabezados de tabla con fondo dorado
    doc.setFillColor(...colorHeader);
    doc.rect(margin, yPosition, contentWidth, 9, 'F');
    
    // Borde del header de tabla
    doc.setDrawColor(...colorBorder);
    doc.setLineWidth(0.3);
    doc.rect(margin, yPosition, contentWidth, 9);
    
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(9);
    doc.setFont('helvetica', 'bold');
    
    // Líneas divisorias en el header - ESTRELLAS más ancho, FECHA pegado a la derecha
    doc.setDrawColor(255, 255, 255);
    doc.setLineWidth(0.2);
    doc.line(margin + 50, yPosition, margin + 50, yPosition + 9);
    doc.line(margin + 75, yPosition, margin + 75, yPosition + 9); // ESTRELLAS más ancho
    doc.line(margin + 135, yPosition, margin + 135, yPosition + 9); // DESCRIPCIÓN ajustada
    // FECHA va hasta el borde derecho (sin línea divisoria al final, usa el borde de la tabla)
    
    // Centrar textos en headers
    doc.text('DOCENTE', margin + 25, yPosition + 6, { align: 'center' });
    doc.text('ESTRELLAS', margin + 62.5, yPosition + 6, { align: 'center' });
    doc.text('DESCRIPCIÓN', margin + 105, yPosition + 6, { align: 'center' });
    doc.text('FECHA', margin + 157.5, yPosition + 6, { align: 'center' }); // Más a la derecha
    yPosition += 11;

    // Filas de datos
    doc.setTextColor(...colorText);
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(8);
    doc.setDrawColor(...colorBorder);

    historialEstrellas.forEach((item, index) => {
      // Verificar si necesitamos nueva página
      if (yPosition > pageHeight - 35) {
        doc.addPage();
        yPosition = margin;
        // Repetir encabezados
        doc.setFillColor(...colorHeader);
        doc.rect(margin, yPosition, contentWidth, 9, 'F');
        doc.setDrawColor(...colorBorder);
        doc.rect(margin, yPosition, contentWidth, 9);
        doc.setTextColor(255, 255, 255);
        doc.setFontSize(9);
        doc.setFont('helvetica', 'bold');
        doc.setDrawColor(255, 255, 255);
        doc.line(margin + 50, yPosition, margin + 50, yPosition + 9);
        doc.line(margin + 75, yPosition, margin + 75, yPosition + 9);
        doc.line(margin + 135, yPosition, margin + 135, yPosition + 9);
        doc.line(margin + 160, yPosition, margin + 160, yPosition + 9);
        doc.text('DOCENTE', margin + 25, yPosition + 6, { align: 'center' });
        doc.text('ESTRELLAS', margin + 62.5, yPosition + 6, { align: 'center' });
        doc.text('DESCRIPCIÓN', margin + 105, yPosition + 6, { align: 'center' });
        doc.text('FECHA', margin + 147.5, yPosition + 6, { align: 'center' });
        yPosition += 11;
        doc.setTextColor(...colorText);
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(8);
        doc.setDrawColor(...colorBorder);
      }

      // Datos
      doc.setTextColor(...colorText);
      const docente = item.docente_nombre || 'N/A';
      const descripcion = item.description || '-';
      const fecha = formatearFechaPDF(item.created_at);
      
      // Descripción - usar splitText para texto largo y mostrar completo
      const maxWidth = 65; // Ancho máximo para descripción
      const descripcionLines = doc.splitTextToSize(descripcion, maxWidth);
      
      // Calcular altura necesaria para la descripción
      const alturaDescripcion = descripcionLines.length * 4; // 4mm por línea
      const alturaFila = Math.max(9, alturaDescripcion + 2); // Mínimo 9mm, más si la descripción es larga

      // Fondo alternado
      if (index % 2 === 0) {
        doc.setFillColor(...colorLight);
        doc.rect(margin, yPosition - 3, contentWidth, alturaFila, 'F');
      }

      // Borde de fila
      doc.setDrawColor(...colorBorder);
      doc.setLineWidth(0.2);
      doc.rect(margin, yPosition - 3, contentWidth, alturaFila);

      // Líneas divisorias verticales - ESTRELLAS más ancho, FECHA pegado a la derecha
      doc.setLineWidth(0.1);
      doc.line(margin + 50, yPosition - 3, margin + 50, yPosition - 3 + alturaFila);
      doc.line(margin + 75, yPosition - 3, margin + 75, yPosition - 3 + alturaFila);
      doc.line(margin + 135, yPosition - 3, margin + 135, yPosition - 3 + alturaFila);
      // FECHA usa el borde derecho de la tabla (no necesita línea divisoria)

      // Centros de columnas
      const centroDocente = margin + 25;
      const centroEstrellas = margin + 62.5;
      const centroDescripcion = margin + 105;
      const centroFecha = margin + 157.5; // Más a la derecha, cerca del borde
      const anchoDocente = 45;
      const anchoDescripcion = 60;
      
      // Docente - centrado y completo (usar splitText si es muy largo)
      const docenteLines = doc.splitTextToSize(docente, anchoDocente);
      let docenteY = yPosition + 2.5;
      docenteLines.forEach((line, lineIndex) => {
        if (lineIndex === 0) {
          doc.text(line, centroDocente, docenteY, { align: 'center' });
        } else {
          docenteY += 4;
          doc.text(line, centroDocente, docenteY, { align: 'center' });
        }
      });
      
      // Estrellas (centrado, sin emoji)
      doc.setFont('helvetica', 'bold');
      doc.text(`${item.points}`, centroEstrellas, yPosition + 2.5, { align: 'center' });
      doc.setFont('helvetica', 'normal');
      
      // Mostrar todas las líneas de la descripción - centrado
      let descY = yPosition + 2.5;
      descripcionLines.forEach((line, lineIndex) => {
        if (lineIndex === 0) {
          doc.text(line, centroDescripcion, descY, { align: 'center', maxWidth: anchoDescripcion });
        } else {
          descY += 4;
          doc.text(line, centroDescripcion, descY, { align: 'center', maxWidth: anchoDescripcion });
        }
      });
      
      // Fecha - centrado, pegado a la derecha
      doc.text(fecha, centroFecha, yPosition + 2.5, { align: 'center' });

      yPosition += alturaFila;
    });

    // ========== PIE DE PÁGINA ==========
    const fechaGeneracion = new Date().toLocaleDateString('es-PE', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
    doc.setFontSize(8);
    doc.setTextColor(100, 100, 100);
    doc.setFont('helvetica', 'italic');
    doc.text(`Generado el: ${fechaGeneracion}`, margin, pageHeight - 10);

    // Guardar PDF
    const nombreArchivo = `Estrellas_${alumnoSeleccionado.nombre_completo.replace(/\s+/g, '_')}_${new Date().getTime()}.pdf`;
    doc.save(nombreArchivo);

    Swal.fire({
      title: '¡PDF Generado!',
      text: 'El reporte de estrellas se ha descargado correctamente',
      icon: 'success',
      zIndex: 100001
    });
  };

  const exportarPDFNotasDetalladas = () => {
    if (!alumnoSeleccionado || !notasDetalladas || !notasDetalladas.criterios || notasDetalladas.criterios.length === 0) {
      Swal.fire({
        title: 'Sin datos',
        text: 'No hay notas detalladas para exportar',
        icon: 'info',
        zIndex: 100001
      });
      return;
    }

    const doc = new jsPDF('p', 'mm', 'a4');
    const pageWidth = doc.internal.pageSize.getWidth();
    const pageHeight = doc.internal.pageSize.getHeight();
    const margin = 15;
    const contentWidth = pageWidth - (margin * 2);
    let yPosition = margin;

    // Colores - Verde azulado para notas
    const colorHeader = [34, 197, 94]; // #22c55e (verde)
    const colorBorder = [22, 163, 74]; // #16a34a (borde más oscuro)
    const colorText = [30, 41, 59]; // #1e293b
    const colorLight = [220, 252, 231]; // #dcfce7
    const colorDark = [20, 83, 45]; // #14532d
    const colorAprobado = [34, 197, 94]; // Verde para aprobado
    const colorDesaprobado = [239, 68, 68]; // Rojo para desaprobado

    // ========== HEADER PRINCIPAL ==========
    doc.setFillColor(...colorHeader);
    doc.rect(margin, yPosition, contentWidth, 30, 'F');
    
    // Borde del header
    doc.setDrawColor(...colorBorder);
    doc.setLineWidth(0.5);
    doc.rect(margin, yPosition, contentWidth, 30);
    
    // Título
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(20);
    doc.setFont('helvetica', 'bold');
    doc.text('REPORTE DE NOTAS DETALLADAS', margin + 5, yPosition + 12);
    
    // Información del alumno
    doc.setFontSize(12);
    doc.setFont('helvetica', 'normal');
    const nombreAlumno = notasDetalladas.alumno?.nombre_completo || alumnoSeleccionado?.nombre_completo || 'Alumno';
    doc.text(`Alumno: ${nombreAlumno}`, margin + 5, yPosition + 22);
    
    yPosition += 35;

    // ========== CUADRO DE INFORMACIÓN DEL CURSO ==========
    if (cursoInfo || notasDetalladas.curso) {
      const curso = notasDetalladas.curso || cursoInfo;
      
      // Fondo del cuadro
      doc.setFillColor(...colorLight);
      doc.rect(margin, yPosition, contentWidth, 30, 'F');
      
      // Borde del cuadro
      doc.setDrawColor(...colorBorder);
      doc.setLineWidth(0.3);
      doc.rect(margin, yPosition, contentWidth, 30);
      
      // Título del cuadro
      doc.setFillColor(...colorHeader);
      doc.rect(margin, yPosition, contentWidth, 7, 'F');
      doc.setTextColor(255, 255, 255);
      doc.setFontSize(11);
      doc.setFont('helvetica', 'bold');
      doc.text('INFORMACIÓN DEL CURSO', margin + 5, yPosition + 5);
      
      yPosition += 10;
      
      // Contenido del cuadro
      doc.setTextColor(...colorText);
      doc.setFont('helvetica', 'normal');
      doc.setFontSize(9);
      doc.text(`Curso: ${curso?.nombre || curso?.curso_nombre || 'N/A'}`, margin + 5, yPosition);
      yPosition += 5;
      
      if (cursoInfo) {
        doc.text(`Grado: ${cursoInfo.grado}° ${cursoInfo.seccion}`, margin + 5, yPosition);
        yPosition += 5;
        doc.text(`Nivel: ${cursoInfo.nivel_nombre} - Turno: ${cursoInfo.turno_nombre}`, margin + 5, yPosition);
        yPosition += 5;
      }
      
      // Tipo de calificación
      const tipoCalif = curso?.nivel?.tipo_calificacion === 0 ? 'Cualitativa (Letras)' : 'Cuantitativa (0-20)';
      doc.text(`Tipo: ${tipoCalif}`, margin + 5, yPosition);
      yPosition += 5;
      
      // Bimestre
      const nombresBimestres = ['I', 'II', 'III', 'IV'];
      doc.setFont('helvetica', 'bold');
      doc.setFontSize(10);
      doc.setTextColor(...colorDark);
      doc.text(`Bimestre: ${nombresBimestres[cicloSeleccionado - 1]}`, margin + 5, yPosition);
      yPosition += 12;
    }

    // ========== TABLA DE NOTAS ==========
    doc.setFontSize(12);
    doc.setFont('helvetica', 'bold');
    doc.setTextColor(...colorText);
    doc.text('NOTAS POR CRITERIO', margin, yPosition);
    yPosition += 8;

    // Procesar cada criterio
    notasDetalladas.criterios.forEach((criterio, criterioIndex) => {
      // Verificar si necesitamos nueva página
      if (yPosition > pageHeight - 50) {
        doc.addPage();
        yPosition = margin;
      }

      const notaCriterio = criterio.notas?.[cicloSeleccionado] || null;
      const subnotasCriterio = notasDetalladas.notas?.[cicloSeleccionado]?.[criterio.id] || {};
      const tieneIndicadores = criterio.indicadores && criterio.indicadores.length > 0;

      // Guardar posición inicial del criterio
      const yInicioCriterio = yPosition;

      // Nombre del criterio (header verde) - dibujar primero
      doc.setFillColor(...colorHeader);
      doc.rect(margin, yPosition, contentWidth, 7, 'F');
      doc.setTextColor(255, 255, 255);
      doc.setFontSize(10);
      doc.setFont('helvetica', 'bold');
      
      let nombreCriterio = criterio.descripcion || 'Criterio';
      if (notasDetalladas.curso?.nivel?.tipo_calificacion_final === 1 && criterio.peso) {
        nombreCriterio += ` (${criterio.peso}%)`;
      }
      
      doc.text(nombreCriterio, margin + 5, yPosition + 5);
      yPosition += 9;
      
      // Guardar posición donde empieza el contenido (después del header)
      const yInicioContenido = yPosition;
      
      // Estimar altura del contenido para dibujar fondo primero
      let alturaEstimadaContenido = 0;
      if (tieneIndicadores) {
        criterio.indicadores.forEach((indicador) => {
          alturaEstimadaContenido += 12; // Por cada indicador
        });
        if (notaCriterio) alturaEstimadaContenido += 8; // Nota final del criterio
      } else {
        alturaEstimadaContenido = 10; // Nota directa o mensaje
      }
      
      // Dibujar fondo claro del contenido ANTES de dibujar el contenido
      doc.setFillColor(...colorLight);
      doc.rect(margin, yInicioContenido, contentWidth, alturaEstimadaContenido, 'F');

      if (tieneIndicadores) {
        // Guardar posición inicial de todos los indicadores para centrar la nota del criterio
        const yInicioTodosIndicadores = yPosition;
        const espacioNota = 25; // Espacio para la nota del criterio al final
        const xNotaCriterio = margin + contentWidth - espacioNota; // Posición fija de la nota del criterio
        
        // Procesar indicadores
        criterio.indicadores.forEach((indicador) => {
          const subnotasIndicador = subnotasCriterio[indicador.id] || {};
          const cuadros = indicador.cuadros || 0;
          const notasArray = [];
          
          // Obtener todas las subnotas
          for (let i = 0; i < cuadros; i++) {
            const nota = subnotasIndicador[i] !== undefined && subnotasIndicador[i] !== null && subnotasIndicador[i] !== '' 
              ? subnotasIndicador[i] 
              : '-';
            notasArray.push(nota);
          }

          // Verificar si necesitamos nueva página para este indicador
          if (yPosition > pageHeight - 25) {
            doc.addPage();
            yPosition = margin;
          }

          // Agregar espacio antes de "GENERAL:" (salto de línea)
          yPosition += 3;
          
          // Nombre del indicador
          doc.setTextColor(...colorText);
          doc.setFontSize(9);
          doc.setFont('helvetica', 'normal');
          doc.text(`  ${indicador.descripcion || 'Indicador'}:`, margin + 5, yPosition);
          yPosition += 5;

          // Subnotas en una línea - alineación horizontal uniforme
          doc.setFontSize(8);
          const notaWidth = 10; // Ancho de cada nota
          const spacing = 3; // Espaciado entre notas
          const limiteX = xNotaCriterio - 5; // Límite antes de la nota
          
          // Calcular posición inicial para alinear todas las notas a la izquierda
          let xPos = margin + 5;
          let yActual = yPosition;
          
          // Dibujar todas las subnotas
          notasArray.forEach((nota, idx) => {
            // Verificar si cabe en la línea actual, si no, nueva línea
            if (xPos + notaWidth > limiteX) {
              yActual += 6;
              xPos = margin + 5;
            }
            
            const esValida = nota !== '-' && nota !== null && nota !== '';
            const esAprobado = esValida && parseFloat(nota) >= (notasDetalladas.curso?.nivel?.nota_aprobatoria || 11);
            
            // Fondo de la nota
            if (esValida) {
              doc.setFillColor(...(esAprobado ? colorAprobado : colorDesaprobado));
              doc.rect(xPos, yActual - 3, notaWidth, 5, 'F');
            } else {
              // Fondo blanco para notas vacías
              doc.setFillColor(255, 255, 255);
              doc.rect(xPos, yActual - 3, notaWidth, 5, 'F');
            }
            
            // Borde
            doc.setDrawColor(...colorBorder);
            doc.setLineWidth(0.1);
            doc.rect(xPos, yActual - 3, notaWidth, 5);
            
            // Texto centrado
            doc.setTextColor(esValida ? 255 : colorText[0], esValida ? 255 : colorText[1], esValida ? 255 : colorText[2]);
            doc.setFont('helvetica', esValida ? 'bold' : 'normal');
            doc.text(nota.toString(), xPos + notaWidth / 2, yActual, { align: 'center' });
            
            xPos += notaWidth + spacing;
          });

          // Actualizar yPosition si hubo múltiples líneas
          yPosition = yActual;
          yPosition += 7; // Espacio después de cada indicador
        });

        // Nota del criterio - centrada verticalmente con TODAS las subnotas de TODOS los indicadores
        if (notaCriterio !== null && notaCriterio !== '') {
          const alturaTotalIndicadores = yPosition - yInicioTodosIndicadores;
          const yCentroIndicadores = yInicioTodosIndicadores + alturaTotalIndicadores / 2;
          
          const esAprobado = parseFloat(notaCriterio) >= (notasDetalladas.curso?.nivel?.nota_aprobatoria || 11);
          doc.setFont('helvetica', 'bold');
          doc.setFontSize(10);
          doc.setFillColor(...(esAprobado ? colorAprobado : colorDesaprobado));
          const alturaNota = 6;
          const yNota = yCentroIndicadores - alturaNota / 2; // Centrar verticalmente con todas las subnotas
          doc.rect(xNotaCriterio, yNota, 20, alturaNota, 'F');
          doc.setDrawColor(...colorBorder);
          doc.rect(xNotaCriterio, yNota, 20, alturaNota);
          doc.setTextColor(255, 255, 255);
          doc.text(`Nota: ${notaCriterio}`, xNotaCriterio + 10, yNota + alturaNota / 2 + 1, { align: 'center' });
        }
        
        yPosition += 3; // Espacio después de todos los indicadores
      } else {
        // Sin indicadores - mostrar nota directa
        if (notaCriterio !== null && notaCriterio !== '') {
          const esAprobado = parseFloat(notaCriterio) >= (notasDetalladas.curso?.nivel?.nota_aprobatoria || 11);
          doc.setFont('helvetica', 'bold');
          doc.setFontSize(11);
          doc.setFillColor(...(esAprobado ? colorAprobado : colorDesaprobado));
          doc.rect(margin + contentWidth - 30, yPosition - 3, 25, 7, 'F');
          doc.setDrawColor(...colorBorder);
          doc.rect(margin + contentWidth - 30, yPosition - 3, 25, 7);
          doc.setTextColor(255, 255, 255);
          doc.text(`Nota: ${notaCriterio}`, margin + contentWidth - 17.5, yPosition + 1.5, { align: 'center' });
        } else {
          doc.setTextColor(...colorText);
          doc.setFont('helvetica', 'normal');
          doc.setFontSize(9);
          doc.text('Sin nota registrada', margin + 5, yPosition);
        }
        yPosition += 10;
      }

      // Calcular altura real del criterio
      const alturaReal = yPosition - yInicioCriterio;
      const alturaRealContenido = yPosition - yInicioContenido;
      
      // Si la altura real del contenido es mayor a la estimada, extender el fondo
      if (alturaRealContenido > alturaEstimadaContenido) {
        doc.setFillColor(...colorLight);
        doc.rect(margin, yInicioContenido + alturaEstimadaContenido, contentWidth, alturaRealContenido - alturaEstimadaContenido, 'F');
      }
      
      // Dibujar borde completo del criterio
      doc.setDrawColor(...colorBorder);
      doc.setLineWidth(0.3);
      doc.rect(margin, yInicioCriterio, contentWidth, alturaReal);

      yPosition += 3; // Espacio entre criterios
    });

    // Examen Mensual (si aplica)
    if (notasDetalladas.curso?.examen_mensual) {
      if (yPosition > pageHeight - 30) {
        doc.addPage();
        yPosition = margin;
      }

      doc.setFillColor(...colorHeader);
      doc.rect(margin, yPosition, contentWidth, 7, 'F');
      doc.setTextColor(255, 255, 255);
      doc.setFontSize(10);
      doc.setFont('helvetica', 'bold');
      
      let nombreExamen = 'Examen Mensual';
      if (notasDetalladas.curso?.nivel?.tipo_calificacion_final === 1 && notasDetalladas.curso?.peso_examen_mensual) {
        nombreExamen += ` (${notasDetalladas.curso.peso_examen_mensual}%)`;
      }
      
      doc.text(nombreExamen, margin + 5, yPosition + 5);
      yPosition += 9;

      const examen1 = notasDetalladas.notas?.[cicloSeleccionado]?.examen_mensual?.[1];
      const examen2 = notasDetalladas.notas?.[cicloSeleccionado]?.examen_mensual?.[2];
      const promedioExamen = (examen1 && examen2) 
        ? Math.round((parseFloat(examen1) + parseFloat(examen2)) / 2)
        : null;

      doc.setTextColor(...colorText);
      doc.setFontSize(9);
      doc.setFont('helvetica', 'normal');
      doc.text('  Examen 1:', margin + 5, yPosition);
      if (examen1 !== null && examen1 !== '') {
        const esAprobado = parseFloat(examen1) >= (notasDetalladas.curso?.nivel?.nota_aprobatoria || 11);
        doc.setFillColor(...(esAprobado ? colorAprobado : colorDesaprobado));
        doc.rect(margin + 40, yPosition - 3, 15, 5, 'F');
        doc.setDrawColor(...colorBorder);
        doc.rect(margin + 40, yPosition - 3, 15, 5);
        doc.setTextColor(255, 255, 255);
        doc.setFont('helvetica', 'bold');
        doc.text(examen1.toString(), margin + 47.5, yPosition, { align: 'center' });
      } else {
        doc.text('-', margin + 40, yPosition);
      }
      yPosition += 6;

      doc.setFont('helvetica', 'normal');
      doc.text('  Examen 2:', margin + 5, yPosition);
      if (examen2 !== null && examen2 !== '') {
        const esAprobado = parseFloat(examen2) >= (notasDetalladas.curso?.nivel?.nota_aprobatoria || 11);
        doc.setFillColor(...(esAprobado ? colorAprobado : colorDesaprobado));
        doc.rect(margin + 40, yPosition - 3, 15, 5, 'F');
        doc.setDrawColor(...colorBorder);
        doc.rect(margin + 40, yPosition - 3, 15, 5);
        doc.setTextColor(255, 255, 255);
        doc.setFont('helvetica', 'bold');
        doc.text(examen2.toString(), margin + 47.5, yPosition, { align: 'center' });
      } else {
        doc.text('-', margin + 40, yPosition);
      }
      yPosition += 6;

      doc.setFont('helvetica', 'bold');
      doc.text('  Promedio:', margin + 5, yPosition);
      if (promedioExamen !== null) {
        const esAprobado = promedioExamen >= (notasDetalladas.curso?.nivel?.nota_aprobatoria || 11);
        doc.setFillColor(...(esAprobado ? colorAprobado : colorDesaprobado));
        doc.rect(margin + 40, yPosition - 3, 15, 5, 'F');
        doc.setDrawColor(...colorBorder);
        doc.rect(margin + 40, yPosition - 3, 15, 5);
        doc.setTextColor(255, 255, 255);
        doc.text(promedioExamen.toString(), margin + 47.5, yPosition, { align: 'center' });
      } else {
        doc.setTextColor(...colorText);
        doc.setFont('helvetica', 'normal');
        doc.text('-', margin + 40, yPosition);
      }
      yPosition += 10;
    }

    // Promedio Final
    if (yPosition > pageHeight - 30) {
      doc.addPage();
      yPosition = margin;
    }

    const promedioFinal = notasDetalladas.notas?.[cicloSeleccionado]?.promedio_final;
    
    doc.setFillColor(...colorHeader);
    doc.rect(margin, yPosition, contentWidth, 10, 'F');
    doc.setDrawColor(...colorBorder);
    doc.setLineWidth(0.5);
    doc.rect(margin, yPosition, contentWidth, 10);
    
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(14);
    doc.setFont('helvetica', 'bold');
    doc.text('PROMEDIO FINAL', margin + 5, yPosition + 7);
    
    if (promedioFinal !== null && promedioFinal !== '') {
      const esAprobado = parseFloat(promedioFinal) >= (notasDetalladas.curso?.nivel?.nota_aprobatoria || 11);
      doc.setFillColor(...(esAprobado ? colorAprobado : colorDesaprobado));
      doc.rect(margin + contentWidth - 30, yPosition + 1, 25, 8, 'F');
      doc.setDrawColor(...colorBorder);
      doc.rect(margin + contentWidth - 30, yPosition + 1, 25, 8);
      doc.setTextColor(255, 255, 255);
      doc.setFontSize(16);
      doc.text(parseFloat(promedioFinal).toFixed(0), margin + contentWidth - 17.5, yPosition + 6.5, { align: 'center' });
    } else {
      doc.setTextColor(255, 255, 255);
      doc.setFontSize(12);
      doc.text('Sin promedio', margin + contentWidth - 20, yPosition + 6.5, { align: 'right' });
    }
    
    yPosition += 15;

    // ========== PIE DE PÁGINA ==========
    const fechaGeneracion = new Date().toLocaleDateString('es-PE', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
    doc.setFontSize(8);
    doc.setTextColor(100, 100, 100);
    doc.setFont('helvetica', 'italic');
    doc.text(`Generado el: ${fechaGeneracion}`, margin, pageHeight - 10);

    // Guardar PDF
    const nombresBimestres = ['I', 'II', 'III', 'IV'];
    const nombreArchivo = `Notas_Detalladas_${nombreAlumno.replace(/\s+/g, '_')}_${nombresBimestres[cicloSeleccionado - 1]}_Bimestre_${new Date().getTime()}.pdf`;
    doc.save(nombreArchivo);

    Swal.fire({
      title: '¡PDF Generado!',
      text: 'El reporte de notas detalladas se ha descargado correctamente',
      icon: 'success',
      zIndex: 100001
    });
  };

  const exportarPDFIncidencias = () => {
    if (!alumnoSeleccionado || historialIncidencias.length === 0) {
      Swal.fire({
        title: 'Sin datos',
        text: 'No hay incidencias para exportar',
        icon: 'info',
        zIndex: 100001
      });
      return;
    }

    const doc = new jsPDF('p', 'mm', 'a4');
    const pageWidth = doc.internal.pageSize.getWidth();
    const pageHeight = doc.internal.pageSize.getHeight();
    const margin = 15;
    const contentWidth = pageWidth - (margin * 2);
    let yPosition = margin;

    // Colores - Morado claro para incidencias
    const colorHeader = [167, 139, 250]; // #a78bfa (morado claro)
    const colorBorder = [139, 92, 246]; // #8b5cf6 (borde más oscuro)
    const colorText = [30, 41, 59]; // #1e293b
    const colorLight = [233, 213, 255]; // #e9d5ff
    const colorDark = [107, 33, 168]; // #6b21a8

    // ========== HEADER PRINCIPAL ==========
    doc.setFillColor(...colorHeader);
    doc.rect(margin, yPosition, contentWidth, 30, 'F');
    
    // Borde del header
    doc.setDrawColor(...colorBorder);
    doc.setLineWidth(0.5);
    doc.rect(margin, yPosition, contentWidth, 30);
    
    // Título (sin emoji, jsPDF no los renderiza bien)
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(22);
    doc.setFont('helvetica', 'bold');
    doc.text('REPORTE DE INCIDENCIAS', margin + 5, yPosition + 12);
    
    // Información del alumno
    doc.setFontSize(12);
    doc.setFont('helvetica', 'normal');
    doc.text(`Alumno: ${alumnoSeleccionado.nombre_completo}`, margin + 5, yPosition + 22);
    
    yPosition += 35;

    // ========== CUADRO DE INFORMACIÓN DEL CURSO ==========
    if (cursoInfo) {
      // Fondo del cuadro
      doc.setFillColor(...colorLight);
      doc.rect(margin, yPosition, contentWidth, 25, 'F');
      
      // Borde del cuadro
      doc.setDrawColor(...colorBorder);
      doc.setLineWidth(0.3);
      doc.rect(margin, yPosition, contentWidth, 25);
      
      // Título del cuadro
      doc.setFillColor(...colorHeader);
      doc.rect(margin, yPosition, contentWidth, 7, 'F');
      doc.setTextColor(255, 255, 255);
      doc.setFontSize(11);
      doc.setFont('helvetica', 'bold');
      doc.text('INFORMACIÓN DEL CURSO', margin + 5, yPosition + 5);
      
      yPosition += 10;
      
      // Contenido del cuadro
      doc.setTextColor(...colorText);
      doc.setFont('helvetica', 'normal');
      doc.setFontSize(9);
      doc.text(`Curso: ${cursoInfo.curso_nombre}`, margin + 5, yPosition);
      yPosition += 5;
      doc.text(`Grado: ${cursoInfo.grado}° ${cursoInfo.seccion}`, margin + 5, yPosition);
      yPosition += 5;
      doc.text(`Nivel: ${cursoInfo.nivel_nombre} - Turno: ${cursoInfo.turno_nombre}`, margin + 5, yPosition);
      yPosition += 5;
      
      // Total destacado (sin emoji) - con más espacio del borde
      doc.setFont('helvetica', 'bold');
      doc.setFontSize(10);
      doc.setTextColor(...colorDark);
      doc.text(`Total de Incidencias: ${totalIncidencias}`, margin + 5, yPosition - 2);
      yPosition += 12;
    }

    // ========== TABLA DE INCIDENCIAS ==========
    doc.setFontSize(12);
    doc.setFont('helvetica', 'bold');
    doc.setTextColor(...colorText);
    doc.text('HISTORIAL DE INCIDENCIAS', margin, yPosition);
    yPosition += 8;

    // Encabezados de tabla con fondo rojo
    doc.setFillColor(...colorHeader);
    doc.rect(margin, yPosition, contentWidth, 9, 'F');
    
    // Borde del header de tabla
    doc.setDrawColor(...colorBorder);
    doc.setLineWidth(0.3);
    doc.rect(margin, yPosition, contentWidth, 9);
    
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(9);
    doc.setFont('helvetica', 'bold');
    
    // Líneas divisorias en el header - CURSO y DESCRIPCIÓN más anchos, FECHA pegado a la derecha
    doc.setDrawColor(255, 255, 255);
    doc.setLineWidth(0.2);
    doc.line(margin + 45, yPosition, margin + 45, yPosition + 9);
    doc.line(margin + 75, yPosition, margin + 75, yPosition + 9); // CURSO más ancho
    doc.line(margin + 140, yPosition, margin + 140, yPosition + 9); // DESCRIPCIÓN más ancho
    // FECHA va hasta el borde derecho (sin línea divisoria al final, usa el borde de la tabla)
    
    // Centrar textos en headers
    doc.text('DOCENTE', margin + 22.5, yPosition + 6, { align: 'center' });
    doc.text('CURSO', margin + 60, yPosition + 6, { align: 'center' });
    doc.text('DESCRIPCIÓN', margin + 107.5, yPosition + 6, { align: 'center' });
    doc.text('FECHA', margin + 157.5, yPosition + 6, { align: 'center' }); // Más a la derecha
    yPosition += 11;

    // Filas de datos
    doc.setTextColor(...colorText);
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(8);
    doc.setDrawColor(...colorBorder);

    historialIncidencias.forEach((item, index) => {
      // Verificar si necesitamos nueva página
      if (yPosition > pageHeight - 35) {
        doc.addPage();
        yPosition = margin;
        // Repetir encabezados
        doc.setFillColor(...colorHeader);
        doc.rect(margin, yPosition, contentWidth, 9, 'F');
        doc.setDrawColor(...colorBorder);
        doc.rect(margin, yPosition, contentWidth, 9);
        doc.setTextColor(255, 255, 255);
        doc.setFontSize(9);
        doc.setFont('helvetica', 'bold');
        doc.setDrawColor(255, 255, 255);
        doc.line(margin + 45, yPosition, margin + 45, yPosition + 9);
        doc.line(margin + 75, yPosition, margin + 75, yPosition + 9);
        doc.line(margin + 140, yPosition, margin + 140, yPosition + 9);
        // FECHA usa el borde derecho de la tabla
        doc.text('DOCENTE', margin + 22.5, yPosition + 6, { align: 'center' });
        doc.text('CURSO', margin + 60, yPosition + 6, { align: 'center' });
        doc.text('DESCRIPCIÓN', margin + 107.5, yPosition + 6, { align: 'center' });
        doc.text('FECHA', margin + 157.5, yPosition + 6, { align: 'center' }); // Más a la derecha
        yPosition += 11;
        doc.setTextColor(...colorText);
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(8);
        doc.setDrawColor(...colorBorder);
      }

      // Datos
      doc.setTextColor(...colorText);
      const docente = item.docente_nombre || 'N/A';
      const curso = item.curso_nombre || 'N/A';
      const descripcion = item.description || '-';
      const fecha = formatearFechaPDF(item.created_at);

      // Descripción - usar splitText para texto largo y mostrar completo
      const maxWidth = 65; // Ancho máximo para descripción
      const descripcionLines = doc.splitTextToSize(descripcion, maxWidth);
      
      // Calcular altura necesaria para la descripción
      const alturaDescripcion = descripcionLines.length * 4; // 4mm por línea
      const alturaFila = Math.max(9, alturaDescripcion + 2); // Mínimo 9mm, más si la descripción es larga

      // Fondo alternado
      if (index % 2 === 0) {
        doc.setFillColor(...colorLight);
        doc.rect(margin, yPosition - 3, contentWidth, alturaFila, 'F');
      }

      // Borde de fila
      doc.setDrawColor(...colorBorder);
      doc.setLineWidth(0.2);
      doc.rect(margin, yPosition - 3, contentWidth, alturaFila);

      // Líneas divisorias verticales - CURSO y DESCRIPCIÓN más anchos, FECHA pegado a la derecha
      doc.setLineWidth(0.1);
      doc.line(margin + 45, yPosition - 3, margin + 45, yPosition - 3 + alturaFila);
      doc.line(margin + 75, yPosition - 3, margin + 75, yPosition - 3 + alturaFila);
      doc.line(margin + 140, yPosition - 3, margin + 140, yPosition - 3 + alturaFila);
      // FECHA usa el borde derecho de la tabla (no necesita línea divisoria)

      // Centros de columnas
      const centroDocente = margin + 22.5;
      const centroCurso = margin + 60;
      const centroDescripcion = margin + 107.5;
      const centroFecha = margin + 157.5; // Más a la derecha, cerca del borde
      const anchoDocente = 40;
      const anchoCurso = 55; // Más ancho
      const anchoDescripcion = 60; // Más ancho
      
      // Docente - centrado y completo (usar splitText si es muy largo)
      const docenteLines = doc.splitTextToSize(docente, anchoDocente);
      let docenteY = yPosition + 2.5;
      docenteLines.forEach((line, lineIndex) => {
        if (lineIndex === 0) {
          doc.text(line, centroDocente, docenteY, { align: 'center' });
        } else {
          docenteY += 4;
          doc.text(line, centroDocente, docenteY, { align: 'center' });
        }
      });
      
      // Curso - centrado y completo (usar splitText si es muy largo)
      const cursoLines = doc.splitTextToSize(curso, anchoCurso);
      let cursoY = yPosition + 2.5;
      cursoLines.forEach((line, lineIndex) => {
        if (lineIndex === 0) {
          doc.text(line, centroCurso, cursoY, { align: 'center' });
        } else {
          cursoY += 4;
          doc.text(line, centroCurso, cursoY, { align: 'center' });
        }
      });
      
      // Mostrar todas las líneas de la descripción - centrado
      let descY = yPosition + 2.5;
      descripcionLines.forEach((line, lineIndex) => {
        if (lineIndex === 0) {
          doc.text(line, centroDescripcion, descY, { align: 'center', maxWidth: anchoDescripcion });
        } else {
          descY += 4;
          doc.text(line, centroDescripcion, descY, { align: 'center', maxWidth: anchoDescripcion });
        }
      });
      
      // Fecha - centrado, pegado a la derecha
      doc.text(fecha, centroFecha, yPosition + 2.5, { align: 'center' });

      yPosition += alturaFila;
    });

    // ========== PIE DE PÁGINA ==========
    const fechaGeneracion = new Date().toLocaleDateString('es-PE', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
    doc.setFontSize(8);
    doc.setTextColor(100, 100, 100);
    doc.setFont('helvetica', 'italic');
    doc.text(`Generado el: ${fechaGeneracion}`, margin, pageHeight - 10);

    // Guardar PDF
    const nombreArchivo = `Incidencias_${alumnoSeleccionado.nombre_completo.replace(/\s+/g, '_')}_${new Date().getTime()}.pdf`;
    doc.save(nombreArchivo);

    Swal.fire({
      title: '¡PDF Generado!',
      text: 'El reporte de incidencias se ha descargado correctamente',
      icon: 'success',
      zIndex: 100001
    });
  };

  const handleCursoAction = (curso, action) => {
    setOpenDropdown(null); // Cerrar dropdown al seleccionar una opción
    setDropdownPosition(null);
    switch (action) {
      case 'aula':
        navigate(`/docente/cursos/${curso.id}/aula`);
        break;
      case 'alumnos':
        cargarAlumnos(curso.id);
        break;
      case 'notas':
        // Abrir modal de registrar notas para el curso
        setCursoParaNotas(curso);
        setMostrarModalNotas(true);
        cargarNotasCurso(curso.id);
        break;
      case 'enlaces':
        // Abrir modal de aula virtual para el curso
        setCursoParaAulaVirtual(curso);
        setMostrarModalAulaVirtual(true);
        cargarLinkAulaVirtual(curso.id);
        break;
      case 'copiar':
        // TODO: Implementar funcionalidad de copiar contenido
        console.log('Copiar contenido del curso:', curso.id);
        break;
      case 'horario':
        // Abrir modal de horario para el curso
        setCursoParaHorario(curso);
        setMostrarModalHorario(true);
        cargarHorarioCurso(curso.id);
        break;
      default:
        break;
    }
  };

  // Obtener grupo_id del curso (necesario para enviar mensaje)
  const obtenerGrupoIdDelCurso = async (cursoId) => {
    try {
      const response = await api.get(`/docente/cursos/${cursoId}/alumnos`);
      return response.data.curso?.grupo_id || null;
    } catch (error) {
      console.error('Error obteniendo grupo_id del curso:', error);
      return null;
    }
  };

  // Cargar notas del curso
  const cargarNotasCurso = async (cursoId, ciclo = null) => {
    try {
      setLoadingNotas(true);
      const cicloACargar = ciclo !== null ? ciclo : cicloSeleccionadoNotas;
      const response = await api.get(`/docente/cursos/${cursoId}/notas`, {
        params: { ciclo: cicloACargar }
      });
      
      setDatosNotas(response.data);
      
      // Inicializar notasEditadas con los datos recibidos
      const notasIniciales = {};
      response.data.alumnos.forEach(alumno => {
        notasIniciales[alumno.matricula_id] = {};
        response.data.criterios.forEach(criterio => {
          notasIniciales[alumno.matricula_id][criterio.id] = {};
          
          if (criterio.indicadores && criterio.indicadores.length > 0) {
            criterio.indicadores.forEach(indicador => {
              const notasIndicador = alumno.notas_detalladas?.[criterio.id]?.[indicador.id] || [];
              notasIniciales[alumno.matricula_id][criterio.id][indicador.id] = [...notasIndicador];
            });
          }
        });
        
        // Agregar exámenes mensuales si aplica
        if (response.data.curso.examen_mensual && alumno.examenes_mensuales) {
          notasIniciales[alumno.matricula_id].examen_mensual = { ...alumno.examenes_mensuales };
        }
      });
      
      setNotasEditadas(notasIniciales);
    } catch (error) {
      console.error('Error cargando notas del curso:', error);
      Swal.fire('Error', 'No se pudo cargar las notas del curso', 'error');
      setDatosNotas(null);
    } finally {
      setLoadingNotas(false);
    }
  };

  // Cambiar ciclo y recargar notas
  const cambiarCicloNotas = async (nuevoCiclo) => {
    setCicloSeleccionadoNotas(nuevoCiclo);
    if (cursoParaNotas) {
      await cargarNotasCurso(cursoParaNotas.id, nuevoCiclo);
    }
  };

  // Actualizar nota en el estado local
  const actualizarNota = (matriculaId, criterioId, indicadorId, indice, valor) => {
    setNotasEditadas(prev => {
      const nuevo = { ...prev };
      if (!nuevo[matriculaId]) nuevo[matriculaId] = {};
      if (!nuevo[matriculaId][criterioId]) nuevo[matriculaId][criterioId] = {};
      if (!nuevo[matriculaId][criterioId][indicadorId]) {
        nuevo[matriculaId][criterioId][indicadorId] = [];
      }
      
      const notasIndicador = [...(nuevo[matriculaId][criterioId][indicadorId] || [])];
      while (notasIndicador.length <= indice) {
        notasIndicador.push('');
      }
      notasIndicador[indice] = valor;
      nuevo[matriculaId][criterioId][indicadorId] = notasIndicador;
      
      return nuevo;
    });
    // Forzar re-render para actualizar promedios
    setForceUpdate(prev => prev + 1);
  };

  // Calcular promedio de un indicador
  const calcularPromedioIndicador = (matriculaId, criterioId, indicadorId) => {
    const notas = notasEditadas[matriculaId]?.[criterioId]?.[indicadorId] || [];
    const notasValidas = notas
      .map(n => parseFloat(n))
      .filter(n => !isNaN(n) && n !== null && n !== '');
    
    if (notasValidas.length === 0) return null;
    
    return Math.round(notasValidas.reduce((sum, n) => sum + n, 0) / notasValidas.length);
  };

  // Calcular promedio de un criterio
  const calcularPromedioCriterio = (matriculaId, criterioId) => {
    const criterio = datosNotas?.criterios?.find(c => c.id === criterioId);
    if (!criterio) return null;

    if (criterio.indicadores && criterio.indicadores.length > 0) {
      const promediosIndicadores = criterio.indicadores
        .map(indicador => calcularPromedioIndicador(matriculaId, criterioId, indicador.id))
        .filter(p => p !== null);
      
      if (promediosIndicadores.length === 0) return null;
      
      return Math.round(
        promediosIndicadores.reduce((sum, p) => sum + p, 0) / promediosIndicadores.length
      );
    } else {
      // Si no hay indicadores, usar la nota directa
      const notaDirecta = notasEditadas[matriculaId]?.[criterioId]?.directa;
      if (notaDirecta && notaDirecta !== '') {
        const nota = parseFloat(notaDirecta);
        return !isNaN(nota) ? Math.round(nota) : null;
      }
      // Si no hay en notasEditadas, buscar en los datos originales
      const alumno = datosNotas?.alumnos?.find(a => a.matricula_id === matriculaId);
      if (alumno?.notas_criterios?.[criterioId]) {
        const nota = parseFloat(alumno.notas_criterios[criterioId]);
        return !isNaN(nota) ? Math.round(nota) : null;
      }
    }
    
    return null;
  };

  // Calcular promedio final del curso usando los pesos de los criterios
  const calcularPromedioFinal = (matriculaId) => {
    if (!datosNotas || !datosNotas.criterios) return null;

    const tipoCalificacionFinal = datosNotas.curso?.nivel?.tipo_calificacion_final || 0;
    
    if (tipoCalificacionFinal === 1) {
      // Porcentual: usar pesos de los criterios
      let sumaPonderada = 0;
      let sumaPesos = 0;

      datosNotas.criterios.forEach(criterio => {
        const promedioCriterio = calcularPromedioCriterio(matriculaId, criterio.id);
        const peso = parseFloat(criterio.peso) || 0;
        
        if (promedioCriterio !== null && peso > 0) {
          sumaPonderada += (promedioCriterio * peso / 100);
          sumaPesos += peso;
        }
      });

      // Agregar examen mensual si aplica
      if (datosNotas.curso.examen_mensual) {
        const examen1 = parseFloat(notasEditadas[matriculaId]?.examen_mensual?.[1] || 
                                   datosNotas.alumnos.find(a => a.matricula_id === matriculaId)?.examenes_mensuales?.[1] || 0);
        const examen2 = parseFloat(notasEditadas[matriculaId]?.examen_mensual?.[2] || 
                                   datosNotas.alumnos.find(a => a.matricula_id === matriculaId)?.examenes_mensuales?.[2] || 0);
        
        if (!isNaN(examen1) && !isNaN(examen2) && examen1 > 0 && examen2 > 0) {
          const promedioExamen = Math.round((examen1 + examen2) / 2);
          const pesoExamen = datosNotas.curso.peso_examen_mensual || 0;
          
          if (pesoExamen > 0) {
            sumaPonderada += (promedioExamen * pesoExamen / 100);
            sumaPesos += pesoExamen;
          }
        }
      }

      if (sumaPesos > 0) {
        // Si los pesos suman 100, usar directamente; si no, normalizar
        if (Math.abs(sumaPesos - 100) < 0.01) {
          return Math.round(sumaPonderada);
        } else {
          return Math.round((sumaPonderada / sumaPesos) * 100);
        }
      }
    } else {
      // Promedio simple
      const promediosCriterios = datosNotas.criterios
        .map(criterio => calcularPromedioCriterio(matriculaId, criterio.id))
        .filter(p => p !== null);

      if (promediosCriterios.length > 0) {
        let suma = promediosCriterios.reduce((sum, p) => sum + p, 0);
        let count = promediosCriterios.length;

        // Agregar examen mensual si aplica
        if (datosNotas.curso.examen_mensual) {
          const examen1 = parseFloat(notasEditadas[matriculaId]?.examen_mensual?.[1] || 
                                     datosNotas.alumnos.find(a => a.matricula_id === matriculaId)?.examenes_mensuales?.[1] || 0);
          const examen2 = parseFloat(notasEditadas[matriculaId]?.examen_mensual?.[2] || 
                                     datosNotas.alumnos.find(a => a.matricula_id === matriculaId)?.examenes_mensuales?.[2] || 0);
          
          if (!isNaN(examen1) && !isNaN(examen2) && examen1 > 0 && examen2 > 0) {
            const promedioExamen = Math.round((examen1 + examen2) / 2);
            suma += promedioExamen;
            count += 1;
          }
        }

        return Math.round(suma / count);
      }
    }

    return null;
  };

  // Guardar notas
  const guardarNotas = async () => {
    if (!cursoParaNotas || !datosNotas) {
      Swal.fire('Error', 'No se ha seleccionado un curso', 'error');
      return;
    }

    if (guardandoNotas) {
      return;
    }

    setGuardandoNotas(true);

    try {
      await api.post(`/docente/cursos/${cursoParaNotas.id}/notas`, {
        ciclo: cicloSeleccionadoNotas,
        notas: notasEditadas
      }, {
        timeout: 120000 // 2 minutos para guardar notas (puede haber muchos alumnos)
      });

      Swal.fire({
        title: '¡Guardado!',
        text: 'Las notas se han guardado correctamente',
        icon: 'success',
        confirmButtonText: 'Aceptar'
      });

      // Recargar notas
      await cargarNotasCurso(cursoParaNotas.id);
    } catch (error) {
      console.error('Error guardando notas:', error);
      Swal.fire('Error', error.response?.data?.error || 'Error al guardar las notas', 'error');
    } finally {
      setGuardandoNotas(false);
    }
  };

  // Cargar link del aula virtual de un curso
  const cargarLinkAulaVirtual = async (cursoId) => {
    try {
      setLoadingAulaVirtual(true);
      const response = await api.get(`/docente/cursos/${cursoId}/aula-virtual`);
      setLinkAulaVirtual(response.data.aula_virtual || '');
      setHabilitarAula(response.data.habilitar_aula || 'NO');
    } catch (error) {
      console.error('Error cargando link del aula virtual:', error);
      Swal.fire('Error', 'No se pudo cargar el link del aula virtual', 'error');
      setLinkAulaVirtual('');
      setHabilitarAula('NO');
    } finally {
      setLoadingAulaVirtual(false);
    }
  };

  // Guardar link del aula virtual
  const guardarLinkAulaVirtual = async () => {
    if (!cursoParaAulaVirtual) {
      Swal.fire('Error', 'No se ha seleccionado un curso', 'error');
      return;
    }

    // Prevenir doble envío
    if (guardandoAulaVirtual) {
      return;
    }

    setGuardandoAulaVirtual(true);

    try {
      const response = await api.put(`/docente/cursos/${cursoParaAulaVirtual.id}/aula-virtual`, {
        aula_virtual: linkAulaVirtual.trim(),
        habilitar_aula: habilitarAula
      });

      Swal.fire({
        title: '¡Guardado!',
        text: response.data.message || 'Link del aula virtual guardado correctamente',
        icon: 'success',
        confirmButtonText: 'Aceptar'
      });

      // Cerrar modal
      setMostrarModalAulaVirtual(false);
      setCursoParaAulaVirtual(null);
      setLinkAulaVirtual('');
      setHabilitarAula('NO');
    } catch (error) {
      console.error('Error guardando link del aula virtual:', error);
      Swal.fire('Error', error.response?.data?.error || 'Error al guardar el link del aula virtual', 'error');
    } finally {
      setGuardandoAulaVirtual(false);
    }
  };

  // Cargar horario de un curso específico
  const cargarHorarioCurso = async (cursoId) => {
    try {
      setLoadingHorario(true);
      const response = await api.get(`/docente/cursos/${cursoId}/horario`);
      const horariosRecibidos = response.data.horario || [];
      const cursoInfo = response.data.curso || null;
      
      // Normalizar días (similar a DocenteHorario.jsx)
      const dias = horariosRecibidos
        .map((c) => typeof c.dia === 'number' ? c.dia : null)
        .filter((d) => d !== null);

      let horarioNormalizado = horariosRecibidos;
      if (dias.length > 0) {
        const minDia = Math.min(...dias);
        const maxDia = Math.max(...dias);
        const usaCeroCuatroComoLunesViernes = minDia === 0 && maxDia <= 4;

        horarioNormalizado = horariosRecibidos.map((c) => ({
          ...c,
          diaNormalizado: usaCeroCuatroComoLunesViernes
            ? (typeof c.dia === 'number' ? c.dia + 1 : c.dia)
            : c.dia
        }));
      }

      setHorarioCurso(horarioNormalizado);
      setInfoCursoHorario(cursoInfo);
    } catch (error) {
      console.error('Error cargando horario del curso:', error);
      Swal.fire('Error', 'No se pudo cargar el horario del curso', 'error');
      setHorarioCurso([]);
      setInfoCursoHorario(null);
    } finally {
      setLoadingHorario(false);
    }
  };

  // Manejar selección de archivos para mensaje
  const handleArchivoChangeMensaje = (e) => {
    const files = Array.from(e.target.files);
    setArchivosAdjuntosMensaje([...archivosAdjuntosMensaje, ...files]);
  };

  // Eliminar archivo adjunto del mensaje
  const eliminarArchivoMensaje = (index) => {
    setArchivosAdjuntosMensaje(archivosAdjuntosMensaje.filter((_, i) => i !== index));
  };

  // Enviar mensaje al grupo del curso
  const enviarMensajeGrupo = async () => {
    if (!cursoParaMensaje) {
      Swal.fire('Error', 'No se ha seleccionado un curso', 'error');
      return;
    }

    if (!asuntoMensaje.trim()) {
      Swal.fire('Error', 'El asunto es requerido', 'error');
      return;
    }

    // Verificar que el contenido no esté vacío (sin HTML vacío)
    const contenidoLimpio = contenidoMensaje.replace(/<[^>]*>/g, '').trim();
    if (!contenidoLimpio) {
      Swal.fire('Error', 'El mensaje es requerido', 'error');
      return;
    }

    // Prevenir doble envío
    if (enviandoMensaje) {
      return;
    }

    setEnviandoMensaje(true);

    try {
      // Obtener grupo_id del curso
      const grupoId = await obtenerGrupoIdDelCurso(cursoParaMensaje.id);
      
      if (!grupoId) {
        Swal.fire('Error', 'No se pudo obtener el grupo del curso', 'error');
        setEnviandoMensaje(false);
        return;
      }

      // Crear FormData para enviar archivos
      const formData = new FormData();
      formData.append('destinatarios', JSON.stringify([])); // Sin destinatarios directos
      formData.append('grupos', JSON.stringify([grupoId])); // Enviar al grupo
      formData.append('asunto', asuntoMensaje.trim());
      formData.append('mensaje', contenidoMensaje);

      // Agregar archivos
      archivosAdjuntosMensaje.forEach((archivo) => {
        formData.append('archivos', archivo);
      });

      const response = await api.post('/docente/mensajes/enviar', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });

      Swal.fire({
        title: '¡Mensaje Enviado!',
        text: response.data.message || 'El mensaje se ha enviado correctamente al grupo',
        icon: 'success',
        confirmButtonText: 'Aceptar'
      });
      
      // Limpiar formulario y cerrar modal
      setAsuntoMensaje('');
      setContenidoMensaje('');
      setArchivosAdjuntosMensaje([]);
      setMostrarModalMensaje(false);
      setCursoParaMensaje(null);
    } catch (error) {
      console.error('Error enviando mensaje:', error);
      Swal.fire('Error', error.response?.data?.error || 'Error al enviar el mensaje', 'error');
    } finally {
      setEnviandoMensaje(false);
    }
  };

  // Funciones auxiliares para formatear horario (similar a DocenteHorario.jsx)
  const diasSemana = [
    { id: 1, nombre: 'Lunes', abrev: 'LUNES' },
    { id: 2, nombre: 'Martes', abrev: 'MARTES' },
    { id: 3, nombre: 'Miércoles', abrev: 'MIÉRCOLES' },
    { id: 4, nombre: 'Jueves', abrev: 'JUEVES' },
    { id: 5, nombre: 'Viernes', abrev: 'VIERNES' }
  ];

  // Función para convertir hora a formato HH:MM AM/PM
  const formatearHora = (hora) => {
    if (!hora) return '';
    
    const horaUpper = hora.toUpperCase().trim();
    if (horaUpper.includes('AM') || horaUpper.includes('PM')) {
      return hora.trim();
    }
    
    const partes = hora.split(':');
    if (partes.length < 2) return hora;
    
    const h = parseInt(partes[0], 10);
    const m = partes[1] ? partes[1].split(' ')[0] : '00';
    const minutos = m.padStart(2, '0');
    
    if (isNaN(h)) return hora;
    
    if (h === 0) return `12:${minutos} AM`;
    if (h < 12) return `${h}:${minutos} AM`;
    if (h === 12) return `12:${minutos} PM`;
    return `${h - 12}:${minutos} PM`;
  };

  // Convertir hora HH:MM:SS a minutos para ordenar
  const horaAMinutos = (hora) => {
    if (!hora) return 0;
    const [h, m] = hora.split(':');
    return parseInt(h) * 60 + parseInt(m || 0);
  };

  // Obtener todos los bloques de horarios únicos ordenados
  const horariosUnicos = useMemo(() => {
    if (!horarioCurso || horarioCurso.length === 0) return [];
    
    const bloques = horarioCurso.map(c => ({
      inicio: c.inicio || c.hora_inicio || '',
      fin: c.fin || c.hora_final || ''
    })).filter(b => b.inicio && b.fin);
    
    const unicos = {};
    bloques.forEach(b => {
      const key = `${b.inicio}-${b.fin}`;
      if (!unicos[key]) {
        unicos[key] = b;
      }
    });
    
    return Object.values(unicos).sort((a, b) => horaAMinutos(a.inicio) - horaAMinutos(b.inicio));
  }, [horarioCurso]);

  // Función para obtener clase en un día y bloque de horario específico
  const obtenerClase = (dia, horaInicio, horaFin) => {
    const clase = horarioCurso.find(c => {
      if (c.diaNormalizado !== dia) return false;
      const inicioClase = c.inicio || c.hora_inicio || '';
      const finClase = c.fin || c.hora_final || '';
      return inicioClase === horaInicio && finClase === horaFin;
    });
    return clase;
  };

  // Función para formatear el texto de la clase
  const formatearClase = (clase) => {
    if (!clase) return null;
    const titulo = clase.titulo || '';
    const grupo = clase.grupo || '';
    
    if (!titulo && !grupo) return null;
    if (!grupo) return titulo;
    if (!titulo) return grupo;
    
    return `${titulo} - ${grupo}`;
  };

  // Paleta de colores pastel (similar a DocenteHorario.jsx)
  const colorPalette = [
    '#FFF9C4', '#BBDEFB', '#F3E5F5', '#C8E6C9', '#E0F7FA',
    '#FFF3E0', '#FCE4EC', '#E1F5FE', '#F1F8E9', '#FFEBEE',
    '#E8EAF6', '#E0F2F1', '#E1BEE7', '#FFE0B2', '#FFCCBC',
    '#D1C4E9', '#F8BBD0', '#B2DFDB', '#C5E1A5', '#FFE082'
  ];

  const hashString = (str) => {
    let hash = 0;
    const normalized = str.trim().toLowerCase();
    for (let i = 0; i < normalized.length; i++) {
      const char = normalized.charCodeAt(i);
      hash = ((hash << 5) - hash) + char;
      hash = hash + (hash << 10);
      hash = hash ^ (hash >> 6);
    }
    hash = hash + (hash << 3);
    hash = hash ^ (hash >> 11);
    hash = hash + (hash << 15);
    return Math.abs(hash);
  };

  const getColorForCourse = (titulo) => {
    if (!titulo || !titulo.trim()) return null;
    const tituloNormalizado = titulo.trim();
    const hash = hashString(tituloNormalizado);
    const index = hash % colorPalette.length;
    return colorPalette[index];
  };

  const aclararColor = (colorHex, factor = 0.4) => {
    if (!colorHex) return '#ffffff';
    const r = parseInt(colorHex.slice(1, 3), 16);
    const g = parseInt(colorHex.slice(3, 5), 16);
    const b = parseInt(colorHex.slice(5, 7), 16);
    const rNuevo = Math.round(r + (255 - r) * factor);
    const gNuevo = Math.round(g + (255 - g) * factor);
    const bNuevo = Math.round(b + (255 - b) * factor);
    return `#${rNuevo.toString(16).padStart(2, '0')}${gNuevo.toString(16).padStart(2, '0')}${bNuevo.toString(16).padStart(2, '0')}`;
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
        {!selectedCurso ? (
          <>
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
          </>
        ) : (
          <div className="alumnos-container">
            {/* Header con botón Volver */}
            <div className="alumnos-header-section">
              <button
                className="btn-regresar"
                onClick={handleVolver}
                type="button"
              >
                ← Volver
              </button>
              <div className="alumnos-header-info">
                {cursoInfo && (
                  <>
                    <h2 className="alumnos-header-title">
                      {cursoInfo.curso_nombre}
                    </h2>
                    <p className="alumnos-header-subtitle">
                      {cursoInfo.grado}° {cursoInfo.seccion} - {cursoInfo.nivel_nombre} - {cursoInfo.turno_nombre}
                    </p>
                  </>
                )}
              </div>
            </div>

            {/* Lista de Alumnos */}
            <div className="alumnos-list-section">
              {loadingAlumnos ? (
                <div className="loading-spinner">Cargando alumnos...</div>
              ) : alumnos.length > 0 ? (
                <div className="alumnos-table-container">
                  <table className="alumnos-table">
                    <thead>
                      <tr>
                        <th>APELLIDOS Y NOMBRES</th>
                        <th>ESTRELLAS</th>
                        <th>INCIDENCIAS</th>
                        <th>PROMEDIO</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      {alumnos.map((alumno) => (
                        <tr key={alumno.id}>
                          <td>{alumno.nombre_completo}</td>
                          <td>
                            <span className="estrellas-badge">
                              ⭐ {alumno.total_estrellas || 0}
                            </span>
                          </td>
                          <td>
                            <span className="incidencias-badge">
                              📋 {alumno.total_incidencias || 0}
                            </span>
                          </td>
                          <td>
                            <span className={`promedio-badge ${alumno.promedio_final && parseFloat(alumno.promedio_final) >= 11 ? 'aprobado' : 'desaprobado'}`}>
                              {alumno.promedio_final ? parseFloat(alumno.promedio_final).toFixed(0) : '-'}
                            </span>
                          </td>
                          <td>
                            <div className="dropdown-container">
                              <button
                                className="btn-opciones-dropdown-alumno"
                                type="button"
                                onClick={(e) => {
                                  e.stopPropagation();
                                  e.preventDefault();
                                  
                                  const rect = e.currentTarget.getBoundingClientRect();
                                  if (openDropdownAlumno?.id === alumno.id) {
                                    setOpenDropdownAlumno(null);
                                  } else {
                                    const dropdownWidth = 200;
                                    let left = rect.left;
                                    if (left + dropdownWidth > window.innerWidth) {
                                      left = window.innerWidth - dropdownWidth - 10;
                                    }
                                    if (left < 10) {
                                      left = 10;
                                    }
                                    
                                    // Verificar si hay espacio abajo, si no, mostrar arriba
                                    const spaceBelow = window.innerHeight - rect.bottom;
                                    const spaceAbove = rect.top;
                                    const dropdownHeight = 120; // Aproximado
                                    
                                    let top = rect.bottom + 2;
                                    if (spaceBelow < dropdownHeight && spaceAbove > dropdownHeight) {
                                      top = rect.top - dropdownHeight - 2;
                                    }
                                    
                                    setOpenDropdownAlumno({ 
                                      id: alumno.id, 
                                      top: top, 
                                      left: left
                                    });
                                  }
                                }}
                                title="Opciones"
                              >
                                <span className="btn-opciones-icon">⚙️</span>
                                Opciones {openDropdownAlumno?.id === alumno.id ? '▲' : '▼'}
                              </button>
                              {openDropdownAlumno?.id === alumno.id && openDropdownAlumno?.top && 
                                createPortal(
                                  <div 
                                    className="dropdown-menu dropdown-menu-alumno"
                                    style={{
                                      top: `${openDropdownAlumno.top}px`,
                                      left: `${openDropdownAlumno.left}px`,
                                      position: 'fixed',
                                      zIndex: 10001,
                                      transform: 'translateZ(0)',
                                      willChange: 'transform',
                                    }}
                                    onClick={(e) => e.stopPropagation()}
                                  >
                                    <button
                                      className="dropdown-item"
                                      type="button"
                                      onClick={(e) => {
                                        e.stopPropagation();
                                        e.preventDefault();
                                        setOpenDropdownAlumno(null);
                                        abrirModalIncidencias(alumno);
                                      }}
                                    >
                                      <span className="dropdown-icon">📋</span>
                                      <span>Incidencias</span>
                                    </button>
                                    <button
                                      className="dropdown-item"
                                      type="button"
                                      onClick={(e) => {
                                        e.stopPropagation();
                                        e.preventDefault();
                                        setOpenDropdownAlumno(null);
                                        abrirModalEstrellas(alumno);
                                      }}
                                    >
                                      <span className="dropdown-icon">⭐</span>
                                      <span>Estrellas</span>
                                    </button>
                                    <button
                                      className="dropdown-item"
                                      type="button"
                                      onClick={(e) => {
                                        e.stopPropagation();
                                        e.preventDefault();
                                        setOpenDropdownAlumno(null);
                                        abrirModalNotasDetalladas(alumno);
                                      }}
                                    >
                                      <span className="dropdown-icon">📝</span>
                                      <span>Notas Detalladas</span>
                                    </button>
                                  </div>,
                                  document.body
                                )
                              }
                            </div>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              ) : (
                <div className="empty-state">
                  <p>No se encontraron alumnos para este curso</p>
                </div>
              )}
            </div>
          </div>
        )}

        {/* Modal de Gestión de Estrellas */}
        {mostrarModalEstrellas && alumnoSeleccionado && createPortal(
          <div className="modal-estrellas-overlay" onClick={() => setMostrarModalEstrellas(false)}>
            <div className="modal-estrellas-container" onClick={(e) => e.stopPropagation()}>
              <div className="modal-estrellas-header">
                <div className="modal-estrellas-title-section">
                  <h2 className="modal-estrellas-title">
                    ⭐ Gestión de Estrellas
                  </h2>
                  <p className="modal-estrellas-subtitle">
                    {alumnoSeleccionado.nombre_completo}
                  </p>
                  <div className="modal-estrellas-total">
                    <span className="total-label">Total de Estrellas:</span>
                    <span className="total-value">{totalEstrellas}</span>
                  </div>
                </div>
                <div className="modal-estrellas-actions">
                  <button
                    className="btn-exportar-pdf"
                    onClick={exportarPDFEstrellas}
                    type="button"
                    title="Exportar a PDF"
                  >
                    📄 Exportar PDF
                  </button>
                  <button
                    className="modal-estrellas-close"
                    onClick={() => setMostrarModalEstrellas(false)}
                    type="button"
                  >
                    ✕
                  </button>
                </div>
              </div>

              <div className="modal-estrellas-body">
                {/* Formulario para dar estrellas */}
                <div className="estrellas-form-section">
                  <h3 className="section-title-estrellas">Dar Estrellas</h3>
                  <form onSubmit={handleDarEstrellas} className="form-estrellas">
                    <div className="form-group-estrellas">
                      <label htmlFor="points">Cantidad de Estrellas *</label>
                      <select
                        id="points"
                        value={formEstrellas.points}
                        onChange={(e) => setFormEstrellas({ ...formEstrellas, points: e.target.value })}
                        className="form-select-estrellas"
                        required
                      >
                        <option value="">Seleccione cantidad</option>
                        {[1, 2, 3, 4, 5, 6, 7, 8, 9, 10].map(num => (
                          <option key={num} value={num}>
                            {num} estrella{num > 1 ? 's' : ''}
                          </option>
                        ))}
                      </select>
                    </div>
                    <div className="form-group-estrellas">
                      <label htmlFor="description">Descripción *</label>
                      <textarea
                        id="description"
                        value={formEstrellas.description}
                        onChange={(e) => setFormEstrellas({ ...formEstrellas, description: e.target.value })}
                        className="form-textarea-estrellas"
                        placeholder="Ej: Participación destacada en clase"
                        rows="3"
                        required
                      />
                    </div>
                    <button
                      type="submit"
                      className="btn-dar-estrellas"
                      disabled={guardandoEstrellas}
                    >
                      {guardandoEstrellas ? 'Guardando...' : '⭐ Dar Estrellas'}
                    </button>
                  </form>
                </div>

                {/* Historial de Estrellas */}
                <div className="estrellas-historial-section">
                  <h3 className="section-title-estrellas">Historial de Estrellas</h3>
                  {loadingEstrellas ? (
                    <div className="loading-estrellas">Cargando historial...</div>
                  ) : historialEstrellas.length > 0 ? (
                    <div className="historial-table-container">
                      <table className="historial-table">
                        <thead>
                          <tr>
                            <th>Docente</th>
                            <th>Estrellas</th>
                            <th>Descripción</th>
                            <th>Fecha</th>
                            <th></th>
                          </tr>
                        </thead>
                        <tbody>
                          {historialEstrellas.map((item) => (
                            <tr key={item.id}>
                              <td>{item.docente_nombre || 'N/A'}</td>
                              <td>
                                <span className="estrellas-badge-small">
                                  ⭐ {item.points}
                                </span>
                              </td>
                              <td className="descripcion-cell">{item.description || '-'}</td>
                              <td>{formatearFecha(item.created_at)}</td>
                              <td>
                                {item.puede_eliminar ? (
                                  <button
                                    className="btn-eliminar-estrella"
                                    onClick={() => handleEliminarEstrellas(item.id, item.points)}
                                    type="button"
                                    title="Eliminar estas estrellas"
                                  >
                                    🗑️
                                  </button>
                                ) : (
                                  <span className="no-eliminar">-</span>
                                )}
                              </td>
                            </tr>
                          ))}
                        </tbody>
                      </table>
                    </div>
                  ) : (
                    <div className="empty-historial">
                      <p>No hay estrellas registradas aún</p>
                    </div>
                  )}
                </div>
              </div>
            </div>
            </div>,
            document.body
          )}

        {/* Modal de Gestión de Incidencias */}
        {mostrarModalIncidencias && alumnoSeleccionado && createPortal(
          <div className="modal-incidencias-overlay" onClick={() => setMostrarModalIncidencias(false)}>
            <div className="modal-incidencias-container" onClick={(e) => e.stopPropagation()}>
              <div className="modal-incidencias-header">
                <div className="modal-incidencias-title-section">
                  <h2 className="modal-incidencias-title">
                    📋 Gestión de Incidencias
                  </h2>
                  <p className="modal-incidencias-subtitle">
                    {alumnoSeleccionado.nombre_completo}
                  </p>
                  <div className="modal-incidencias-total">
                    <span className="total-label">Total de Incidencias:</span>
                    <span className="total-value">{totalIncidencias}</span>
                  </div>
                </div>
                <div className="modal-incidencias-actions">
                  <button
                    className="btn-exportar-pdf-incidencias"
                    onClick={exportarPDFIncidencias}
                    type="button"
                    title="Exportar a PDF"
                  >
                    📄 Exportar PDF
                  </button>
                  <button
                    className="modal-incidencias-close"
                    onClick={() => setMostrarModalIncidencias(false)}
                    type="button"
                  >
                    ✕
                  </button>
                </div>
              </div>

              <div className="modal-incidencias-body">
                {/* Formulario para registrar incidencias */}
                <div className="incidencias-form-section">
                  <h3 className="section-title-incidencias">Registrar Incidencia</h3>
                  <form onSubmit={handleRegistrarIncidencia} className="form-incidencias">
                    <div className="form-group-incidencias">
                      <label htmlFor="description-incidencia">Descripción de la Incidencia *</label>
                      <textarea
                        id="description-incidencia"
                        value={formIncidencias.description}
                        onChange={(e) => setFormIncidencias({ ...formIncidencias, description: e.target.value })}
                        className="form-textarea-incidencias"
                        placeholder="Ej: No trajo la tarea asignada, llegó tarde a clase, no participó en la actividad grupal..."
                        rows="4"
                        required
                      />
                    </div>
                    <button
                      type="submit"
                      className="btn-registrar-incidencia"
                      disabled={guardandoIncidencias}
                    >
                      {guardandoIncidencias ? 'Guardando...' : '📋 Registrar Incidencia'}
                    </button>
                  </form>
                </div>

                {/* Historial de Incidencias */}
                <div className="incidencias-historial-section">
                  <h3 className="section-title-incidencias">Historial de Incidencias</h3>
                  {loadingIncidencias ? (
                    <div className="loading-incidencias">Cargando historial...</div>
                  ) : historialIncidencias.length > 0 ? (
                    <div className="historial-table-container">
                      <table className="historial-table-incidencias">
                        <thead>
                          <tr>
                            <th>Docente</th>
                            <th>Curso</th>
                            <th>Descripción</th>
                            <th>Fecha</th>
                            <th></th>
                          </tr>
                        </thead>
                        <tbody>
                          {historialIncidencias.map((item) => (
                            <tr key={item.id}>
                              <td>{item.docente_nombre || 'N/A'}</td>
                              <td>
                                <span className="curso-badge-incidencia">
                                  {item.curso_nombre || 'N/A'}
                                </span>
                              </td>
                              <td className="descripcion-cell">{item.description || '-'}</td>
                              <td>{formatearFecha(item.created_at)}</td>
                              <td>
                                {item.puede_eliminar ? (
                                  <button
                                    className="btn-eliminar-incidencia"
                                    onClick={() => handleEliminarIncidencia(item.id)}
                                    type="button"
                                    title="Eliminar esta incidencia"
                                  >
                                    🗑️
                                  </button>
                                ) : (
                                  <span className="no-eliminar">-</span>
                                )}
                              </td>
                            </tr>
                          ))}
                        </tbody>
                      </table>
                    </div>
                  ) : (
                    <div className="empty-historial">
                      <p>No hay incidencias registradas aún</p>
                    </div>
                  )}
                </div>
              </div>
            </div>
          </div>,
          document.body
        )}

        {/* Modal de Notas Detalladas */}
        {mostrarModalNotasDetalladas && alumnoSeleccionado && createPortal(
          <div 
            className="modal-notas-detalladas-overlay" 
            onClick={() => {
              console.log('Click en overlay, cerrando modal');
              setMostrarModalNotasDetalladas(false);
            }}
            style={{ zIndex: 10000 }}
          >
            <div 
              className="modal-notas-detalladas-container" 
              onClick={(e) => {
                e.stopPropagation();
                console.log('Click en container, no cerrar');
              }}
            >
              <div className="modal-notas-detalladas-header">
                <div>
                  <h2 className="modal-notas-detalladas-title">📝 Notas Detalladas</h2>
                  <p className="modal-notas-detalladas-subtitle">
                    {notasDetalladas?.alumno?.nombre_completo || alumnoSeleccionado?.nombre_completo || 'Alumno'}
                  </p>
                </div>
                <div className="modal-notas-detalladas-actions">
                  <button
                    className="btn-exportar-pdf-notas"
                    onClick={exportarPDFNotasDetalladas}
                    type="button"
                    title="Exportar a PDF"
                  >
                    📄 Exportar PDF
                  </button>
                  <button
                    className="modal-notas-detalladas-close"
                    onClick={() => {
                      console.log('Click en botón cerrar');
                      setMostrarModalNotasDetalladas(false);
                    }}
                    type="button"
                  >
                    ✕
                  </button>
                </div>
              </div>

              <div className="modal-notas-detalladas-body">
                {loadingNotasDetalladas ? (
                  <div className="loading-notas-detalladas">Cargando notas...</div>
                ) : notasDetalladas ? (
                  <>
                    {/* Información del Curso */}
                    <div className="notas-detalladas-info">
                      <h3 className="curso-nombre-notas">{notasDetalladas.curso?.nombre || 'Curso'}</h3>
                      <p className="curso-tipo-notas">
                        {notasDetalladas.curso?.nivel?.tipo_calificacion === 0 
                          ? 'Calificación: Cualitativa (Letras)' 
                          : 'Calificación: Cuantitativa (0-20)'}
                      </p>
                      {notasDetalladas.curso?.nivel?.tipo_calificacion_final === 1 && (
                        <p className="curso-tipo-notas">Cálculo Final: Por Porcentaje</p>
                      )}
                      {notasDetalladas.curso?.nivel?.tipo_calificacion_final === 0 && (
                        <p className="curso-tipo-notas">Cálculo Final: Por Promedio</p>
                      )}
                    </div>

                    {/* Selector de Ciclo/Bimestre */}
                    <div className="ciclo-selector">
                      <label>Bimestre:</label>
                      <select
                        value={cicloSeleccionado}
                        onChange={(e) => setCicloSeleccionado(parseInt(e.target.value))}
                        className="select-ciclo"
                      >
                        <option value={1}>I Bimestre</option>
                        <option value={2}>II Bimestre</option>
                        <option value={3}>III Bimestre</option>
                        <option value={4}>IV Bimestre</option>
                      </select>
                    </div>

                    {/* Tabla de Notas */}
                    {notasDetalladas.criterios && notasDetalladas.criterios.length > 0 ? (
                      <div className="notas-detalladas-table-container">
                        <table className="notas-detalladas-table">
                          <thead>
                            <tr>
                              <th>Criterio</th>
                              <th>Notas</th>
                            </tr>
                          </thead>
                          <tbody>
                            {notasDetalladas.criterios.map((criterio) => {
                              // La nota del criterio viene en criterio.notas[ciclo]
                              const notaCriterio = criterio.notas?.[cicloSeleccionado] || null;
                              
                              // Las subnotas vienen en notasDetalladas.notas[ciclo][criterio_id][indicador_id][indice]
                              const subnotasCriterio = notasDetalladas.notas?.[cicloSeleccionado]?.[criterio.id] || {};
                              
                              // Verificar si tiene indicadores
                              const tieneIndicadores = criterio.indicadores && criterio.indicadores.length > 0;
                              
                              return (
                                <tr key={criterio.id}>
                                  <td className="criterio-nombre">
                                    <strong>{criterio.descripcion}</strong>
                                    {notasDetalladas.curso?.nivel?.tipo_calificacion_final === 1 && (
                                      <span className="criterio-peso"> ({criterio.peso}%)</span>
                                    )}
                                  </td>
                                  <td className="criterio-notas">
                                    {tieneIndicadores ? (
                                      <div className="indicadores-container">
                                        {criterio.indicadores.map((indicador) => {
                                          // Acceder a las subnotas: notasDetalladas.notas[ciclo][criterio_id][indicador_id]
                                          const subnotasIndicador = subnotasCriterio[indicador.id] || {};
                                          const cuadros = indicador.cuadros || 0;
                                          const notasArray = [];
                                          
                                          // Obtener todas las subnotas del indicador
                                          // El objeto deserializado tiene la estructura: [indicador_id][indice] = nota
                                          for (let i = 0; i < cuadros; i++) {
                                            const nota = subnotasIndicador[i] !== undefined && subnotasIndicador[i] !== null && subnotasIndicador[i] !== '' 
                                              ? subnotasIndicador[i] 
                                              : '-';
                                            notasArray.push(nota);
                                          }
                                          
                                          // Calcular promedio del indicador
                                          const notasValidas = notasArray.filter(n => n !== '-' && n !== null && n !== '');
                                          const promedio = notasValidas.length > 0
                                            ? Math.round(notasValidas.reduce((sum, n) => sum + parseFloat(n), 0) / notasValidas.length)
                                            : null;
                                          
                                          return (
                                            <div key={indicador.id} className="indicador-group">
                                              <div className="indicador-nombre">{indicador.descripcion}</div>
                                              <div className="subnotas-container">
                                                {notasArray.map((nota, idx) => (
                                                  <span
                                                    key={idx}
                                                    className={`subnota ${nota !== '-' && nota !== null && nota !== '' 
                                                      ? (parseFloat(nota) >= (notasDetalladas.curso?.nivel?.nota_aprobatoria || 11) 
                                                          ? 'aprobado' 
                                                          : 'desaprobado')
                                                      : 'vacio'}`}
                                                  >
                                                    {nota}
                                                  </span>
                                                ))}
                                                <span className={`promedio-indicador ${promedio !== null 
                                                  ? (promedio >= (notasDetalladas.curso?.nivel?.nota_aprobatoria || 11) 
                                                      ? 'aprobado' 
                                                      : 'desaprobado')
                                                  : 'vacio'}`}>
                                                  {promedio !== null ? promedio : '-'}
                                                </span>
                                              </div>
                                            </div>
                                          );
                                        })}
                                      </div>
                                    ) : (
                                      <div className="nota-simple">
                                        <span className={`nota-valor ${notaCriterio !== null && notaCriterio !== '' 
                                          ? (parseFloat(notaCriterio) >= (notasDetalladas.curso?.nivel?.nota_aprobatoria || 11) 
                                              ? 'aprobado' 
                                              : 'desaprobado')
                                          : 'vacio'}`}>
                                          {notaCriterio !== null && notaCriterio !== '' ? notaCriterio : '-'}
                                        </span>
                                      </div>
                                    )}
                                  </td>
                                </tr>
                              );
                            })}

                            {/* Examen Mensual (si aplica) */}
                            {notasDetalladas.curso?.examen_mensual && (
                              <tr>
                                <td className="criterio-nombre">
                                  <strong>Examen Mensual</strong>
                                  {notasDetalladas.curso?.nivel?.tipo_calificacion_final === 1 && (
                                    <span className="criterio-peso"> ({notasDetalladas.curso?.peso_examen_mensual}%)</span>
                                  )}
                                </td>
                                <td className="criterio-notas">
                                  <div className="examen-mensual-container">
                                    {[1, 2].map((nro) => {
                                      const notaExamen = notasDetalladas.notas?.[cicloSeleccionado]?.examen_mensual?.[nro] || null;
                                      return (
                                        <span
                                          key={nro}
                                          className={`nota-examen ${notaExamen !== null && notaExamen !== '' 
                                            ? (parseFloat(notaExamen) >= (notasDetalladas.curso?.nivel?.nota_aprobatoria || 11) 
                                                ? 'aprobado' 
                                                : 'desaprobado')
                                            : 'vacio'}`}
                                        >
                                          {notaExamen !== null && notaExamen !== '' ? notaExamen : '-'}
                                        </span>
                                      );
                                    })}
                                    {/* Promedio de exámenes mensuales */}
                                    {(() => {
                                      const examen1 = notasDetalladas.notas?.[cicloSeleccionado]?.examen_mensual?.[1];
                                      const examen2 = notasDetalladas.notas?.[cicloSeleccionado]?.examen_mensual?.[2];
                                      const promedioExamen = (examen1 && examen2) 
                                        ? Math.round((parseFloat(examen1) + parseFloat(examen2)) / 2)
                                        : null;
                                      return (
                                        <span className={`promedio-examen ${promedioExamen !== null 
                                          ? (promedioExamen >= (notasDetalladas.curso?.nivel?.nota_aprobatoria || 11) 
                                              ? 'aprobado' 
                                              : 'desaprobado')
                                          : 'vacio'}`}>
                                          {promedioExamen !== null ? promedioExamen : '-'}
                                        </span>
                                      );
                                    })()}
                                  </div>
                                </td>
                              </tr>
                            )}

                            {/* Promedio Final - Diseño mejorado */}
                            <tr className="promedio-final-row">
                              <td className="criterio-nombre-promedio">
                                <strong>PROMEDIO FINAL</strong>
                              </td>
                              <td className="criterio-notas-promedio">
                                <div className="promedio-final-container">
                                  <span className={`promedio-final-badge ${notasDetalladas.notas?.[cicloSeleccionado]?.promedio_final 
                                    ? (parseFloat(notasDetalladas.notas[cicloSeleccionado].promedio_final) >= (notasDetalladas.curso?.nivel?.nota_aprobatoria || 11) 
                                        ? 'aprobado' 
                                        : 'desaprobado')
                                    : 'vacio'}`}>
                                    {notasDetalladas.notas?.[cicloSeleccionado]?.promedio_final 
                                      ? parseFloat(notasDetalladas.notas[cicloSeleccionado].promedio_final).toFixed(0)
                                      : '-'}
                                  </span>
                                </div>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    ) : (
                      <div className="empty-notas-detalladas">
                        <p>No hay criterios configurados para este curso</p>
                      </div>
                    )}
                  </>
                ) : (
                  <div className="empty-notas-detalladas">
                    <p>No se pudieron cargar las notas detalladas</p>
                  </div>
                )}
              </div>
            </div>
          </div>,
          document.body
        )}

        {/* Modal de Enviar Mensaje al Grupo */}
        {mostrarModalMensaje && cursoParaMensaje && createPortal(
          <div 
            className="modal-mensaje-overlay" 
            onClick={() => setMostrarModalMensaje(false)}
            style={{ zIndex: 100000 }}
          >
            <div 
              className="modal-mensaje-container" 
              onClick={(e) => e.stopPropagation()}
              role="dialog"
              aria-modal="true"
              aria-labelledby="modal-mensaje-title"
            >
              <div className="modal-mensaje-header">
                <h2 id="modal-mensaje-title">✉️ Enviar Mensaje al Grupo</h2>
                <button
                  className="modal-mensaje-close"
                  onClick={() => setMostrarModalMensaje(false)}
                  type="button"
                  aria-label="Cerrar modal"
                >
                  ✕
                </button>
              </div>

              <div className="modal-mensaje-body">
                {/* Información del curso/grupo */}
                <div className="mensaje-grupo-info">
                  <p><strong>Curso:</strong> {cursoParaMensaje.curso_nombre}</p>
                  <p><strong>Grupo:</strong> {cursoParaMensaje.grado}° {cursoParaMensaje.seccion} - {cursoParaMensaje.nivel_nombre} - {cursoParaMensaje.turno_nombre}</p>
                  <p className="mensaje-info-text">El mensaje se enviará a todos los alumnos del grupo</p>
                </div>

                {/* Campo Asunto */}
                <div className="form-group">
                  <label htmlFor="asunto-mensaje-grupo">Asunto:</label>
                  <input
                    type="text"
                    id="asunto-mensaje-grupo"
                    className="form-input"
                    value={asuntoMensaje}
                    onChange={(e) => setAsuntoMensaje(e.target.value)}
                    placeholder="Asunto del mensaje"
                  />
                </div>

                {/* Campo Mensaje con Editor de Texto Enriquecido */}
                <div className="form-group">
                  <label htmlFor="mensaje-editor-grupo">Mensaje:</label>
                  <div id="mensaje-editor-wrapper-grupo">
                    <ReactQuill
                      ref={quillRefMensaje}
                      theme="snow"
                      value={contenidoMensaje}
                      onChange={setContenidoMensaje}
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
                      id="archivos-input-grupo"
                      multiple
                      onChange={handleArchivoChangeMensaje}
                      style={{ display: 'none' }}
                    />
                    <label htmlFor="archivos-input-grupo" className="btn-adjuntar-archivo">
                      📎 Adjuntar Archivos
                    </label>
                    {archivosAdjuntosMensaje.length > 0 && (
                      <div className="archivos-lista">
                        {archivosAdjuntosMensaje.map((archivo, index) => (
                          <div key={index} className="archivo-item">
                            <span className="archivo-nombre">{archivo.name}</span>
                            <span className="archivo-tamaño">
                              {(archivo.size / 1024).toFixed(2)} KB
                            </span>
                            <button
                              type="button"
                              className="archivo-eliminar"
                              onClick={() => eliminarArchivoMensaje(index)}
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
                      setMostrarModalMensaje(false);
                      setAsuntoMensaje('');
                      setContenidoMensaje('');
                      setArchivosAdjuntosMensaje([]);
                      setCursoParaMensaje(null);
                    }}
                    type="button"
                  >
                    Cancelar
                  </button>
                  <button
                    className="btn-enviar"
                    onClick={enviarMensajeGrupo}
                    disabled={enviandoMensaje}
                    type="button"
                  >
                    {enviandoMensaje ? '⏳ Enviando...' : '✉️ Enviar Mensaje'}
                  </button>
                </div>
              </div>
            </div>
          </div>,
          document.body
        )}

        {/* Modal de Horario del Curso */}
        {mostrarModalHorario && createPortal(
          <div 
            className="modal-horario-overlay"
            onClick={() => setMostrarModalHorario(false)}
          >
            <div 
              className="modal-horario-container"
              onClick={(e) => e.stopPropagation()}
              role="dialog"
              aria-modal="true"
              aria-labelledby="modal-horario-title"
            >
              <div className="modal-horario-header">
                <h2 id="modal-horario-title">
                  📅 Horario - {cursoParaHorario?.curso_nombre || 'Curso'}
                </h2>
                <button
                  className="modal-horario-close"
                  onClick={() => {
                    setMostrarModalHorario(false);
                    setCursoParaHorario(null);
                    setHorarioCurso([]);
                    setInfoCursoHorario(null);
                  }}
                  type="button"
                  aria-label="Cerrar modal"
                >
                  ✕
                </button>
              </div>

              <div className="modal-horario-body">
                {infoCursoHorario && (
                  <div className="horario-curso-info">
                    <p><strong>Curso:</strong> {infoCursoHorario.curso_nombre}</p>
                    <p><strong>Grupo:</strong> {infoCursoHorario.nivel_nombre} - {infoCursoHorario.grado}° {infoCursoHorario.seccion}</p>
                  </div>
                )}

                {loadingHorario ? (
                  <div className="horario-loading">
                    <div className="loading-spinner">Cargando horario...</div>
                  </div>
                ) : horarioCurso.length > 0 ? (
                  <div className="horario-container-modal">
                    <div className="horario-table-wrapper">
                      <table className="horario-table">
                        <thead>
                          <tr>
                            <th className="horario-hora-col">Hora</th>
                            {diasSemana.map((dia) => (
                              <th key={dia.id} className="horario-dia-col">{dia.abrev}</th>
                            ))}
                          </tr>
                        </thead>
                        <tbody>
                          {horariosUnicos.map((bloque, idx) => {
                            const claseLunes = obtenerClase(1, bloque.inicio, bloque.fin);
                            const claseMartes = obtenerClase(2, bloque.inicio, bloque.fin);
                            const claseMiercoles = obtenerClase(3, bloque.inicio, bloque.fin);
                            const claseJueves = obtenerClase(4, bloque.inicio, bloque.fin);
                            const claseViernes = obtenerClase(5, bloque.inicio, bloque.fin);
                            
                            const tieneClase = claseLunes || claseMartes || claseMiercoles || claseJueves || claseViernes;
                            
                            if (!tieneClase) return null;
                            
                            return (
                              <tr key={idx}>
                                <td className="horario-hora-cell">
                                  {formatearHora(bloque.inicio)} - {formatearHora(bloque.fin)}
                                </td>
                                <td
                                  className={`horario-clase-cell ${!claseLunes ? 'horario-cell-empty' : ''}`}
                                  style={claseLunes ? { backgroundColor: aclararColor(getColorForCourse(claseLunes.titulo), 0.5) } : {}}
                                >
                                  {formatearClase(claseLunes) || ''}
                                </td>
                                <td
                                  className={`horario-clase-cell ${!claseMartes ? 'horario-cell-empty' : ''}`}
                                  style={claseMartes ? { backgroundColor: aclararColor(getColorForCourse(claseMartes.titulo), 0.5) } : {}}
                                >
                                  {formatearClase(claseMartes) || ''}
                                </td>
                                <td
                                  className={`horario-clase-cell ${!claseMiercoles ? 'horario-cell-empty' : ''}`}
                                  style={claseMiercoles ? { backgroundColor: aclararColor(getColorForCourse(claseMiercoles.titulo), 0.5) } : {}}
                                >
                                  {formatearClase(claseMiercoles) || ''}
                                </td>
                                <td
                                  className={`horario-clase-cell ${!claseJueves ? 'horario-cell-empty' : ''}`}
                                  style={claseJueves ? { backgroundColor: aclararColor(getColorForCourse(claseJueves.titulo), 0.5) } : {}}
                                >
                                  {formatearClase(claseJueves) || ''}
                                </td>
                                <td
                                  className={`horario-clase-cell ${!claseViernes ? 'horario-cell-empty' : ''}`}
                                  style={claseViernes ? { backgroundColor: aclararColor(getColorForCourse(claseViernes.titulo), 0.5) } : {}}
                                >
                                  {formatearClase(claseViernes) || ''}
                                </td>
                              </tr>
                            );
                          })}
                        </tbody>
                      </table>
                    </div>
                  </div>
                ) : (
                  <div className="empty-state">
                    <p>No se encontró horario para este curso</p>
                  </div>
                )}
              </div>
            </div>
          </div>,
          document.body
        )}

        {/* Modal de Link Aula Virtual */}
        {mostrarModalAulaVirtual && createPortal(
          <div 
            className="modal-aula-virtual-overlay"
            onClick={() => setMostrarModalAulaVirtual(false)}
          >
            <div 
              className="modal-aula-virtual-container"
              onClick={(e) => e.stopPropagation()}
              role="dialog"
              aria-modal="true"
              aria-labelledby="modal-aula-virtual-title"
            >
              <div className="modal-aula-virtual-header">
                <h2 id="modal-aula-virtual-title">
                  🔗 Link Aula Virtual
                </h2>
                <button
                  className="modal-aula-virtual-close"
                  onClick={() => {
                    setMostrarModalAulaVirtual(false);
                    setCursoParaAulaVirtual(null);
                    setLinkAulaVirtual('');
                    setHabilitarAula('NO');
                  }}
                  type="button"
                  aria-label="Cerrar modal"
                >
                  ✕
                </button>
              </div>

              <div className="modal-aula-virtual-body">
                {cursoParaAulaVirtual && (
                  <div className="aula-virtual-curso-info">
                    <p><strong>Curso:</strong> {cursoParaAulaVirtual.curso_nombre}</p>
                    <p><strong>Grupo:</strong> {cursoParaAulaVirtual.nivel_nombre} - {cursoParaAulaVirtual.grado}° {cursoParaAulaVirtual.seccion}</p>
                  </div>
                )}

                {loadingAulaVirtual ? (
                  <div className="aula-virtual-loading">
                    <div className="loading-spinner">Cargando...</div>
                  </div>
                ) : (
                  <>
                    {/* Campo Link Aula Virtual */}
                    <div className="form-group-aula-virtual">
                      <label htmlFor="link-aula-virtual">
                        Link Aula Virtual
                        <span className="label-hint">(Zoom, Meet, Teams, etc.)</span>
                      </label>
                      <div className="input-wrapper-aula-virtual">
                        <input
                          type="url"
                          id="link-aula-virtual"
                          className="form-input-aula-virtual"
                          value={linkAulaVirtual}
                          onChange={(e) => setLinkAulaVirtual(e.target.value)}
                          placeholder="https://zoom.us/j/123456789 o https://meet.google.com/abc-defg-hij"
                        />
                        {linkAulaVirtual && (
                          <button
                            type="button"
                            className="input-clear-btn"
                            onClick={() => setLinkAulaVirtual('')}
                            aria-label="Limpiar campo"
                            title="Limpiar"
                          >
                            ✕
                          </button>
                        )}
                      </div>
                      <p className="form-hint">
                        Puedes dejar este campo en blanco para eliminar el link guardado.
                      </p>
                    </div>

                    {/* Toggle para habilitar/deshabilitar para alumnos */}
                    <div className="form-group-aula-virtual">
                      <label className="toggle-label">
                        <span className="toggle-label-text">
                          Habilitar para alumnos
                          <span className="label-hint">(Los alumnos podrán ver y acceder al link)</span>
                        </span>
                        <div className="toggle-switch">
                          <input
                            type="checkbox"
                            id="habilitar-aula"
                            checked={habilitarAula === 'SI'}
                            onChange={(e) => setHabilitarAula(e.target.checked ? 'SI' : 'NO')}
                          />
                          <span className="toggle-slider"></span>
                        </div>
                      </label>
                    </div>

                    {/* Botones */}
                    <div className="form-actions-aula-virtual">
                      <button
                        className="btn-cancelar-aula-virtual"
                        onClick={() => {
                          setMostrarModalAulaVirtual(false);
                          setCursoParaAulaVirtual(null);
                          setLinkAulaVirtual('');
                          setHabilitarAula('NO');
                        }}
                        type="button"
                        disabled={guardandoAulaVirtual}
                      >
                        Cancelar
                      </button>
                      <button
                        className="btn-guardar-aula-virtual"
                        onClick={guardarLinkAulaVirtual}
                        disabled={guardandoAulaVirtual}
                        type="button"
                      >
                        {guardandoAulaVirtual ? '⏳ Guardando...' : '💾 Guardar Datos'}
                      </button>
                    </div>
                  </>
                )}
              </div>
            </div>
          </div>,
          document.body
        )}

        {/* Modal de Registrar Notas */}
        {mostrarModalNotas && createPortal(
          <div className="modal-notas-overlay" onClick={(e) => {
            if (e.target.classList.contains('modal-notas-overlay')) {
              setMostrarModalNotas(false);
            }
          }}>
            <div className="modal-notas-container" onClick={(e) => e.stopPropagation()}>
              {loadingNotas ? (
                <div className="loading-overlay-notas">
                  <div className="loading-spinner-notas">
                    <div className="spinner-ring"></div>
                    <div className="spinner-ring"></div>
                    <div className="spinner-ring"></div>
                    <div className="spinner-ring"></div>
                  </div>
                  <p className="loading-text-notas">Cargando notas...</p>
                </div>
              ) : datosNotas ? (
                <>
                  <div className="modal-notas-header">
                    <h2>
                      {datosNotas.curso.curso_nombre.toUpperCase()} - {datosNotas.curso.grado}° {datosNotas.curso.seccion} - {datosNotas.curso.nivel_nombre.toUpperCase()} - BIMESTRE {cicloSeleccionadoNotas}
                    </h2>
                    <button
                      className="modal-close-btn"
                      onClick={() => {
                        setMostrarModalNotas(false);
                        setCursoParaNotas(null);
                        setDatosNotas(null);
                        setNotasEditadas({});
                      }}
                    >
                      ✕
                    </button>
                  </div>

                  <div className="modal-notas-body">
                    {/* Selector de Bimestre y Botones */}
                    <div className="notas-controls">
                      <div className="bimestre-selector">
                        <label>Seleccionar Bimestre:</label>
                        <select
                          value={cicloSeleccionadoNotas}
                          onChange={(e) => cambiarCicloNotas(parseInt(e.target.value))}
                          disabled={loadingNotas}
                        >
                          <option value={1}>Bimestre 1</option>
                          <option value={2}>Bimestre 2</option>
                          <option value={3}>Bimestre 3</option>
                          <option value={4}>Bimestre 4</option>
                        </select>
                      </div>
                      
                      {/* Botones de acción */}
                      <div className="notas-actions-top">
                        <button
                          className="btn-cancelar-notas"
                          onClick={() => {
                            setMostrarModalNotas(false);
                            setCursoParaNotas(null);
                            setDatosNotas(null);
                            setNotasEditadas({});
                          }}
                          disabled={guardandoNotas}
                        >
                          Cancelar
                        </button>
                        <button
                          className="btn-guardar-notas"
                          onClick={guardarNotas}
                          disabled={guardandoNotas}
                        >
                          {guardandoNotas ? '⏳ Guardando...' : '💾 Guardar Datos'}
                        </button>
                        <button
                          className="btn-imprimir-notas"
                          onClick={() => {
                            // TODO: Implementar impresión
                            Swal.fire('Info', 'Funcionalidad de impresión próximamente', 'info');
                          }}
                          disabled={guardandoNotas}
                        >
                          🖨️ Imprimir
                        </button>
                      </div>
                    </div>

                    {/* Barra azul informativa */}
                    <div className="blue-bar-notas">
                      Ingrese notas de: {datosNotas.curso.nivel.nota_minima} - {datosNotas.curso.nivel.nota_maxima}.
                    </div>

                    {/* Tabla de notas */}
                    <div className="notas-table-wrapper">
                      <table className="notas-table">
                        <thead>
                          <tr>
                            <th>N°</th>
                            <th className="col-name">APELLIDOS Y NOMBRES</th>
                            {datosNotas.criterios.map(criterio => (
                              <th key={criterio.id}>
                                <div className="th-criterio-content">
                                  <div className="th-criterio-nombre">{criterio.descripcion.toUpperCase()}</div>
                                  <div className="th-criterio-peso">({criterio.peso}%)</div>
                                </div>
                              </th>
                            ))}
                            {datosNotas.curso.examen_mensual && (
                              <th>EXAMEN MENSUAL ({datosNotas.curso.peso_examen_mensual}%)</th>
                            )}
                            <th>PROM.</th>
                          </tr>
                        </thead>
                        <tbody>
                          {datosNotas.alumnos.map((alumno) => (
                            <tr key={alumno.matricula_id}>
                              <td>{alumno.numero}</td>
                              <td className="col-name">{alumno.nombre_completo}</td>
                              {datosNotas.criterios.map(criterio => {
                                const promedioCriterio = calcularPromedioCriterio(alumno.matricula_id, criterio.id);
                                const tieneIndicadores = criterio.indicadores && criterio.indicadores.length > 0;
                                
                                return (
                                  <td key={criterio.id}>
                                    {tieneIndicadores ? (
                                      <div className="box-row">
                                        {criterio.indicadores.map(indicador => {
                                          const cuadros = indicador.cuadros || 1;
                                          return Array.from({ length: cuadros }, (_, i) => {
                                            const valor = notasEditadas[alumno.matricula_id]?.[criterio.id]?.[indicador.id]?.[i] || '';
                                            return (
                                              <input
                                                key={`${indicador.id}-${i}`}
                                                type="text"
                                                className="box"
                                                value={valor}
                                                onChange={(e) => {
                                                  const nuevoValor = e.target.value;
                                                  // Validar que sea un número entre nota_minima y nota_maxima
                                                  if (nuevoValor === '' || 
                                                      (!isNaN(nuevoValor) && 
                                                       parseFloat(nuevoValor) >= datosNotas.curso.nivel.nota_minima && 
                                                       parseFloat(nuevoValor) <= datosNotas.curso.nivel.nota_maxima)) {
                                                    actualizarNota(alumno.matricula_id, criterio.id, indicador.id, i, nuevoValor);
                                                  }
                                                }}
                                                onBlur={(e) => {
                                                  // Formatear el valor al perder el foco
                                                  const valor = parseFloat(e.target.value);
                                                  if (!isNaN(valor)) {
                                                    const valorFormateado = Math.round(valor).toString();
                                                    actualizarNota(alumno.matricula_id, criterio.id, indicador.id, i, valorFormateado);
                                                  }
                                                }}
                                                placeholder=""
                                                maxLength={2}
                                              />
                                            );
                                          });
                                        })}
                                        <div className={`box avg ${promedioCriterio !== null && promedioCriterio < datosNotas.curso.nivel.nota_aprobatoria ? 'below-min' : ''}`}>
                                          {promedioCriterio !== null ? promedioCriterio : ''}
                                        </div>
                                      </div>
                                    ) : (
                                      <div className="box-row">
                                        <input
                                          type="text"
                                          className="box"
                                          value={notasEditadas[alumno.matricula_id]?.[criterio.id]?.directa || 
                                                 alumno.notas_criterios?.[criterio.id] || ''}
                                          onChange={(e) => {
                                            const nuevoValor = e.target.value;
                                            if (nuevoValor === '' || 
                                                (!isNaN(nuevoValor) && 
                                                 parseFloat(nuevoValor) >= datosNotas.curso.nivel.nota_minima && 
                                                 parseFloat(nuevoValor) <= datosNotas.curso.nivel.nota_maxima)) {
                                              setNotasEditadas(prev => {
                                                const nuevo = { ...prev };
                                                if (!nuevo[alumno.matricula_id]) nuevo[alumno.matricula_id] = {};
                                                if (!nuevo[alumno.matricula_id][criterio.id]) nuevo[alumno.matricula_id][criterio.id] = {};
                                                nuevo[alumno.matricula_id][criterio.id].directa = nuevoValor;
                                                return nuevo;
                                              });
                                              setForceUpdate(prev => prev + 1);
                                            }
                                          }}
                                          onBlur={(e) => {
                                            const valor = parseFloat(e.target.value);
                                            if (!isNaN(valor)) {
                                              const valorFormateado = Math.round(valor).toString();
                                              setNotasEditadas(prev => {
                                                const nuevo = { ...prev };
                                                if (!nuevo[alumno.matricula_id]) nuevo[alumno.matricula_id] = {};
                                                if (!nuevo[alumno.matricula_id][criterio.id]) nuevo[alumno.matricula_id][criterio.id] = {};
                                                nuevo[alumno.matricula_id][criterio.id].directa = valorFormateado;
                                                return nuevo;
                                              });
                                              setForceUpdate(prev => prev + 1);
                                            }
                                          }}
                                          placeholder=""
                                          maxLength={2}
                                        />
                                      </div>
                                    )}
                                  </td>
                                );
                              })}
                              {datosNotas.curso.examen_mensual && (
                                <td>
                                  <div className="box-row">
                                    <input
                                      type="text"
                                      className="box"
                                      value={notasEditadas[alumno.matricula_id]?.examen_mensual?.[1] || 
                                             alumno.examenes_mensuales?.[1] || ''}
                                      onChange={(e) => {
                                        const nuevoValor = e.target.value;
                                        if (nuevoValor === '' || 
                                            (!isNaN(nuevoValor) && 
                                             parseFloat(nuevoValor) >= datosNotas.curso.nivel.nota_minima && 
                                             parseFloat(nuevoValor) <= datosNotas.curso.nivel.nota_maxima)) {
                                          setNotasEditadas(prev => {
                                            const nuevo = { ...prev };
                                            if (!nuevo[alumno.matricula_id]) nuevo[alumno.matricula_id] = {};
                                            if (!nuevo[alumno.matricula_id].examen_mensual) nuevo[alumno.matricula_id].examen_mensual = {};
                                            nuevo[alumno.matricula_id].examen_mensual[1] = nuevoValor;
                                            return nuevo;
                                          });
                                          setForceUpdate(prev => prev + 1);
                                        }
                                      }}
                                      placeholder=""
                                      maxLength={2}
                                    />
                                    <input
                                      type="text"
                                      className="box"
                                      value={notasEditadas[alumno.matricula_id]?.examen_mensual?.[2] || 
                                             alumno.examenes_mensuales?.[2] || ''}
                                      onChange={(e) => {
                                        const nuevoValor = e.target.value;
                                        if (nuevoValor === '' || 
                                            (!isNaN(nuevoValor) && 
                                             parseFloat(nuevoValor) >= datosNotas.curso.nivel.nota_minima && 
                                             parseFloat(nuevoValor) <= datosNotas.curso.nivel.nota_maxima)) {
                                          setNotasEditadas(prev => {
                                            const nuevo = { ...prev };
                                            if (!nuevo[alumno.matricula_id]) nuevo[alumno.matricula_id] = {};
                                            if (!nuevo[alumno.matricula_id].examen_mensual) nuevo[alumno.matricula_id].examen_mensual = {};
                                            nuevo[alumno.matricula_id].examen_mensual[2] = nuevoValor;
                                            return nuevo;
                                          });
                                          setForceUpdate(prev => prev + 1);
                                        }
                                      }}
                                      placeholder=""
                                      maxLength={2}
                                    />
                                    <div className="box avg">
                                      {(() => {
                                        const examen1 = parseFloat(notasEditadas[alumno.matricula_id]?.examen_mensual?.[1] || alumno.examenes_mensuales?.[1] || 0);
                                        const examen2 = parseFloat(notasEditadas[alumno.matricula_id]?.examen_mensual?.[2] || alumno.examenes_mensuales?.[2] || 0);
                                        if (!isNaN(examen1) && !isNaN(examen2) && examen1 > 0 && examen2 > 0) {
                                          return Math.round((examen1 + examen2) / 2);
                                        }
                                        return '';
                                      })()}
                                    </div>
                                  </div>
                                </td>
                              )}
                              <td>
                                <div className={`box avg final ${(() => {
                                  const promedioFinal = calcularPromedioFinal(alumno.matricula_id);
                                  return promedioFinal !== null && promedioFinal < datosNotas.curso.nivel.nota_aprobatoria ? 'below-min' : '';
                                })()}`}>
                                  {calcularPromedioFinal(alumno.matricula_id) || alumno.promedio_final || ''}
                                </div>
                              </td>
                            </tr>
                          ))}
                        </tbody>
                      </table>
                    </div>
                  </div>
                </>
              ) : (
                <div className="loading-overlay-notas">
                  <div className="loading-spinner-notas">
                    <div className="spinner-ring"></div>
                    <div className="spinner-ring"></div>
                    <div className="spinner-ring"></div>
                    <div className="spinner-ring"></div>
                  </div>
                  <p className="loading-text-notas">No se pudieron cargar las notas</p>
                </div>
              )}
            </div>
          </div>,
          document.body
        )}
      </div>
    </DashboardLayout>
  );
}

export default DocenteCursos;

