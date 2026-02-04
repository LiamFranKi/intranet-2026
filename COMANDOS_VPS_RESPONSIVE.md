# Comandos para Actualizar en el VPS - Corrección Responsive

## Actualizar Código y Recompilar Frontend

```bash
# 1. Ir al directorio del proyecto
cd ~/intranet2026

# 2. Actualizar código desde GitHub
git pull origin main

# 3. Ir al directorio del frontend
cd frontend

# 4. Recompilar el frontend
npm run build

# 5. Verificar que el build se completó correctamente
ls -la build/static/css/main*.css

# 6. (Opcional) Reiniciar Apache si es necesario
sudo systemctl reload apache2
```

## Verificación Rápida

```bash
# Verificar que los cambios están en el código
grep -n "Responsive para móviles" frontend/src/components/CalendarioWidget.css
grep -n "Responsive para móviles" frontend/src/components/PublicacionesWidget.css
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

## Cambios Realizados

✅ **CalendarioWidget.css**: Agregados estilos responsive para móviles (max-width: 768px y 480px)
✅ **PublicacionesWidget.css**: Agregados estilos responsive para móviles (max-width: 768px y 640px)
✅ **DashboardLayout.css**: Ajustado padding y márgenes del sidebar derecho en móviles

Los widgets ahora se verán centrados y con padding adecuado en dispositivos móviles.

