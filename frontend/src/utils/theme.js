/**
 * Utilidades para manejar temas y colores basados en el colegio
 */

/**
 * Extraer colores dominantes de una imagen (logo)
 * Nota: Esto requiere una librería como color-thief o similar
 * Por ahora, usamos colores por defecto o del colegio
 */
export const getThemeColors = (colorPrincipal, colorSecundario, logo) => {
  // Si hay colores definidos en MySQL, usarlos
  if (colorPrincipal && colorSecundario) {
    return {
      primary: colorPrincipal,
      secondary: colorSecundario,
    };
  }

  // Si hay logo, podríamos extraer colores (requiere librería adicional)
  // Por ahora, usar colores por defecto
  return {
    primary: '#1976d2', // Azul Material-UI por defecto
    secondary: '#dc004e', // Rosa Material-UI por defecto
  };
};

/**
 * Crear tema de Material-UI basado en colores del colegio
 */
export const createMuiTheme = (colorPrincipal, colorSecundario) => {
  const colors = getThemeColors(colorPrincipal, colorSecundario);
  
  return {
    palette: {
      primary: {
        main: colors.primary,
      },
      secondary: {
        main: colors.secondary,
      },
    },
  };
};

/**
 * Obtener URL completa del logo
 */
export const getLogoUrl = (logoPath, apiUrl) => {
  // Si no hay logoPath, intentar con logo.png por defecto
  if (!logoPath) {
    const baseUrl = apiUrl.replace('/api', '');
    return `${baseUrl}/assets/logos/logo.png`;
  }
  
  // Si ya es una URL completa, retornarla
  if (logoPath.startsWith('http://') || logoPath.startsWith('https://')) {
    return logoPath;
  }
  
  // Si es una ruta relativa, construir URL completa
  if (logoPath.startsWith('/')) {
    return `${apiUrl.replace('/api', '')}${logoPath}`;
  }
  
  // Si es solo un nombre de archivo, construir la ruta completa
  return `${apiUrl.replace('/api', '')}/assets/logos/${logoPath}`;
};

