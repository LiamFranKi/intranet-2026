import React, { createContext, useContext, useState, useEffect } from 'react';
import api from '../services/api';

const ColegioContext = createContext();

export const useColegio = () => {
  const context = useContext(ColegioContext);
  if (!context) {
    throw new Error('useColegio debe usarse dentro de ColegioProvider');
  }
  return context;
};

export const ColegioProvider = ({ children, colegioId = 1 }) => {
  const [colegioData, setColegioData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const obtenerDatosColegio = async () => {
      try {
        setLoading(true);
        const response = await api.get(`/colegio/${colegioId}`);
        setColegioData(response.data);
        setError(null);
      } catch (err) {
        console.error('Error obteniendo datos del colegio:', err);
        setError(err.message);
        // Valores por defecto en caso de error
        setColegioData({
          nombre_intranet: 'Aula Virtual',
          nombre: 'Colegio',
          logo: null,
          color_principal: null,
          color_secundario: null,
        });
      } finally {
        setLoading(false);
      }
    };

    obtenerDatosColegio();
  }, [colegioId]);

  // Aplicar variables CSS globales basadas en configuración del colegio
  useEffect(() => {
    const root = document.documentElement;
    const primary = colegioData?.color_principal || '#667eea';
    const secondary = colegioData?.color_secundario || '#764ba2';

    root.style.setProperty('--primary-color', primary);
    root.style.setProperty('--secondary-color', secondary);

    // Sidebar / navbar (pueden ajustarse luego según diseño)
    root.style.setProperty('--sidebar-bg', primary);
    root.style.setProperty('--sidebar-text', 'white');
    root.style.setProperty('--navbar-bg', '#ffffff');
  }, [colegioData]);

  const value = {
    colegioData,
    loading,
    error,
    nombreIntranet: colegioData?.nombre_intranet || 'Aula Virtual',
    nombre: colegioData?.nombre || 'Colegio',
    logo: colegioData?.logo || null,
    colorPrincipal: colegioData?.color_principal || null,
    colorSecundario: colegioData?.color_secundario || null,
    anioActivo: colegioData?.anio_activo || null,
  };

  return (
    <ColegioContext.Provider value={value}>
      {children}
    </ColegioContext.Provider>
  );
};

