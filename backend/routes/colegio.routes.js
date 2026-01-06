const express = require('express');
const router = express.Router();
const { getColegioData } = require('../utils/colegio');
const path = require('path');
const fs = require('fs');

/**
 * GET /api/colegio/:colegioId
 * Obtener datos del colegio (nombre, logo, colores, etc.)
 */
router.get('/:colegioId', async (req, res) => {
  try {
    const { colegioId } = req.params;
    const colegioData = await getColegioData(parseInt(colegioId));

    if (!colegioData) {
      return res.status(404).json({ error: 'Colegio no encontrado' });
    }

    // Si hay logo en MySQL, verificar si existe en la carpeta local
    let logoPath = null;
    if (colegioData.logo) {
      // El logo puede venir como ruta relativa o nombre de archivo
      const logoFileName = colegioData.logo.split('/').pop() || colegioData.logo;
      const logoLocalPath = path.join(__dirname, '..', 'public', 'assets', 'logos', logoFileName);
      
      if (fs.existsSync(logoLocalPath)) {
        logoPath = `/assets/logos/${logoFileName}`;
      } else {
        // Si no existe localmente, usar el path de MySQL (puede ser URL remota)
        logoPath = colegioData.logo;
      }
    }
    
    // Si no hay logo o no se encontró, buscar logo.png por defecto
    if (!logoPath) {
      const defaultLogoPath = path.join(__dirname, '..', 'public', 'assets', 'logos', 'logo.png');
      if (fs.existsSync(defaultLogoPath)) {
        logoPath = '/assets/logos/logo.png';
      } else {
        // Intentar con logo-default.png
        const fallbackLogoPath = path.join(__dirname, '..', 'public', 'assets', 'logos', 'logo-default.png');
        if (fs.existsSync(fallbackLogoPath)) {
          logoPath = '/assets/logos/logo-default.png';
        }
      }
    }

    res.json({
      ...colegioData,
      logo: logoPath,
    });
  } catch (error) {
    console.error('Error obteniendo datos del colegio:', error);
    res.status(500).json({ error: 'Error obteniendo datos del colegio' });
  }
});

/**
 * GET /api/colegio/:colegioId/nombre-intranet
 * Obtener solo el nombre de la intranet
 */
router.get('/:colegioId/nombre-intranet', async (req, res) => {
  try {
    const { colegioId } = req.params;
    const colegioData = await getColegioData(parseInt(colegioId));

    if (!colegioData) {
      return res.status(404).json({ error: 'Colegio no encontrado' });
    }

    res.json({
      nombre_intranet: colegioData.nombre_intranet,
      nombre: colegioData.nombre,
    });
  } catch (error) {
    console.error('Error obteniendo nombre de intranet:', error);
    res.status(500).json({ error: 'Error obteniendo nombre de intranet' });
  }
});

module.exports = router;

