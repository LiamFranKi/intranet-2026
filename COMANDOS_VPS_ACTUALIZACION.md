# Comandos para Actualizar en el VPS

## Actualizar Código y Recompilar Frontend

```bash
# 1. Ir al directorio del proyecto
cd ~/intranet2026

# 2. Actualizar código desde GitHub
git pull origin main

# 3. Ir al directorio del frontend
cd frontend

# 4. Recompilar el frontend (esto incluye el Service Worker del PWA)
npm run build

# 5. Verificar que el build se completó correctamente
ls -la build/service-worker.js

# 6. (Opcional) Reiniciar Apache si es necesario
sudo systemctl reload apache2
```

## Verificación Rápida

```bash
# Verificar que el modal de Tareas está en el código
grep -n "Modal de Formulario de Tarea" frontend/src/pages/DocenteAulaVirtual.jsx

# Verificar que el service-worker.js está en el build
ls -la frontend/build/service-worker.js
```

## Si hay problemas

```bash
# Limpiar cache de npm (si es necesario)
cd ~/intranet2026/frontend
rm -rf node_modules/.cache
npm run build

# Ver logs de Apache si hay errores
sudo tail -f /var/log/apache2/sistema-error.log
```

