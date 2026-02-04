import React, { useState, useEffect } from 'react';
import Swal from 'sweetalert2';
import './PWAInstallPrompt.css';

function PWAInstallPrompt() {
  const [deferredPrompt, setDeferredPrompt] = useState(null);
  const [isInstallable, setIsInstallable] = useState(false);
  const [isInstalled, setIsInstalled] = useState(false);

  useEffect(() => {
    // Verificar si la app ya está instalada
    if (window.matchMedia('(display-mode: standalone)').matches) {
      setIsInstalled(true);
      return;
    }

    // Detectar si está en iOS (Safari)
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
    const isInStandaloneMode = ('standalone' in window.navigator) && (window.navigator.standalone);

    if (isIOS && isInStandaloneMode) {
      setIsInstalled(true);
      return;
    }

    // Escuchar el evento beforeinstallprompt (Chrome, Edge, etc.)
    const handleBeforeInstallPrompt = (e) => {
      // Prevenir el prompt automático
      e.preventDefault();
      // Guardar el evento para usarlo después
      setDeferredPrompt(e);
      setIsInstallable(true);
      console.log('✅ PWA: Listo para instalar');
    };

    // Escuchar cuando la app se instala
    const handleAppInstalled = () => {
      console.log('✅ PWA: Aplicación instalada');
      setIsInstalled(true);
      setIsInstallable(false);
      setDeferredPrompt(null);
    };

    window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
    window.addEventListener('appinstalled', handleAppInstalled);

    return () => {
      window.removeEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
      window.removeEventListener('appinstalled', handleAppInstalled);
    };
  }, []);

  const handleInstallClick = async () => {
    if (!deferredPrompt) {
      // Si no hay prompt disponible (iOS u otros navegadores)
      Swal.fire({
        icon: 'info',
        title: 'Instalar Aplicación',
        html: `
          <p><strong>Para instalar en iOS (iPhone/iPad):</strong></p>
          <ol style="text-align: left; margin: 1rem 0;">
            <li>Toca el botón <strong>Compartir</strong> <span style="font-size: 1.2rem;">📤</span></li>
            <li>Selecciona <strong>"Agregar a pantalla de inicio"</strong></li>
            <li>Confirma la instalación</li>
          </ol>
          <p><strong>Para instalar en Android:</strong></p>
          <ol style="text-align: left; margin: 1rem 0;">
            <li>Toca el menú <span style="font-size: 1.2rem;">⋮</span> del navegador</li>
            <li>Selecciona <strong>"Instalar app"</strong> o <strong>"Agregar a pantalla de inicio"</strong></li>
          </ol>
        `,
        confirmButtonText: 'Entendido',
        width: '90%',
        maxWidth: '500px'
      });
      return;
    }

    // Mostrar el prompt de instalación
    deferredPrompt.prompt();

    // Esperar a que el usuario responda
    const { outcome } = await deferredPrompt.userChoice;

    if (outcome === 'accepted') {
      Swal.fire({
        icon: 'success',
        title: '¡Aplicación instalada!',
        text: 'La aplicación se ha instalado correctamente. Puedes acceder a ella desde tu pantalla de inicio.',
        confirmButtonText: 'Perfecto',
        timer: 3000
      });
    } else {
      console.log('Usuario canceló la instalación');
    }

    // Limpiar el prompt
    setDeferredPrompt(null);
    setIsInstallable(false);
  };

  // No mostrar nada si ya está instalada
  if (isInstalled) {
    return null;
  }

  // Mostrar botón de instalación si está disponible
  if (isInstallable) {
    return (
      <div className="pwa-install-prompt">
        <button 
          className="pwa-install-btn"
          onClick={handleInstallClick}
          aria-label="Instalar aplicación"
        >
          📱 Instalar App
        </button>
      </div>
    );
  }

  return null;
}

export default PWAInstallPrompt;

