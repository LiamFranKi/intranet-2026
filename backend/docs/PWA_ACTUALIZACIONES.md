# Documentación: Sistema de Actualizaciones del PWA

Este documento explica cómo funciona el sistema de actualizaciones automáticas del Progressive Web App (PWA) y qué acciones son necesarias cuando se despliegan cambios.

---

## 📋 Resumen Ejecutivo

**Respuesta corta:** Las actualizaciones del PWA son **automáticas** una vez que recompilas el frontend en el VPS. El sistema detecta cambios, descarga la nueva versión y notifica al usuario para que actualice.

---

## 🔄 Cómo Funciona el Sistema de Actualizaciones

### 1. **Detección Automática de Cambios**

El navegador verifica automáticamente si hay una nueva versión del Service Worker en cada una de estas situaciones:

- ✅ **Al cargar la página**: Cada vez que el usuario visita la aplicación
- ✅ **Cada hora**: Verificación automática en segundo plano (configurado en `index.js`)
- ✅ **Al recuperar el foco**: Cuando el usuario vuelve a la pestaña de la aplicación
- ✅ **Al navegar**: Durante la navegación normal dentro de la aplicación

### 2. **Proceso de Actualización**

Cuando se detecta una nueva versión:

1. **Descarga en segundo plano**: El navegador descarga el nuevo Service Worker sin interrumpir al usuario
2. **Instalación**: El nuevo Service Worker se instala y queda en estado "waiting" (esperando)
3. **Notificación**: Se muestra una notificación al usuario indicando que hay una nueva versión disponible
4. **Activación**: Cuando el usuario acepta o recarga la página, el nuevo Service Worker se activa
5. **Limpieza**: Se eliminan automáticamente los caches antiguos

### 3. **Estrategia de Cache**

El sistema usa una estrategia **Network First**:

- **Primero**: Intenta obtener los archivos desde la red (siempre la versión más reciente)
- **Si falla la red**: Sirve desde el cache (funciona offline)
- **Ventaja**: Los usuarios siempre ven la versión más reciente cuando hay conexión

---

## 🚀 Proceso de Despliegue (Lo que TÚ debes hacer)

### Paso 1: Actualizar el Código en el VPS

```bash
cd ~/intranet2026
git pull origin main
```

### Paso 2: Recompilar el Frontend

```bash
cd frontend
npm run build
```

**⚠️ IMPORTANTE:** Cada vez que ejecutas `npm run build`, React Scripts genera:
- Nuevos archivos JavaScript y CSS con hashes únicos (ej: `main.abc123.js`)
- Una nueva versión del Service Worker
- El navegador detecta estos cambios automáticamente

### Paso 3: Verificar (Opcional)

```bash
# Verificar que el service-worker.js esté en el build
ls -la build/service-worker.js

# Verificar que los archivos se generaron correctamente
ls -la build/static/js/
```

### Paso 4: Reiniciar Apache (si es necesario)

```bash
sudo systemctl reload apache2
```

**✅ Eso es todo.** El sistema de actualizaciones funciona automáticamente después de esto.

---

## 👤 Experiencia del Usuario

### Escenario 1: Usuario con la app abierta cuando hay actualización

1. El usuario está usando la aplicación normalmente
2. El sistema detecta una nueva versión en segundo plano
3. Aparece una notificación (SweetAlert2) en la parte superior:
   - **Título**: "Nueva versión disponible"
   - **Opciones**: "Actualizar ahora" o "Más tarde"
4. Si el usuario acepta:
   - La página se recarga automáticamente
   - Se carga la nueva versión
   - El cache antiguo se elimina
5. Si el usuario cancela:
   - Puede seguir usando la versión antigua
   - Aparece un botón flotante "🔄 Actualizar" en la esquina superior derecha
   - Puede actualizar cuando quiera

### Escenario 2: Usuario que abre la app después de una actualización

1. El usuario abre la aplicación (después de que se desplegó la nueva versión)
2. El navegador detecta automáticamente que hay una nueva versión
3. Se descarga e instala en segundo plano
4. Se muestra la notificación
5. El usuario actualiza y ve la nueva versión

### Escenario 3: Usuario offline

1. Si el usuario está offline, la app funciona desde el cache
2. Cuando recupera la conexión, se detecta la nueva versión
3. Se descarga en segundo plano
4. Se muestra la notificación cuando está lista

---

## 🔧 Configuración Técnica

### Service Worker (`frontend/public/service-worker.js`)

- **Versión del cache**: Se actualiza automáticamente en cada build
- **Estrategia**: Network First (red primero, cache como respaldo)
- **Limpieza automática**: Elimina caches antiguos al activarse

### Registro (`frontend/src/index.js`)

- **Verificación automática**: Cada hora y al cargar la página
- **Detección de actualizaciones**: Escucha eventos `updatefound`
- **Notificación**: Llama a `window.showUpdateNotification()` cuando hay actualizaciones

### Componente de Notificación (`frontend/src/components/PWAUpdateNotification.jsx`)

- **Notificación visual**: SweetAlert2 con opciones de actualizar
- **Botón flotante**: Aparece si el usuario cancela la notificación
- **Auto-activación**: Envía mensaje al Service Worker para activarse

---

## 📊 Flujo Completo de Actualización

```
┌─────────────────────────────────────────────────────────────┐
│ 1. DESARROLLADOR: git pull + npm run build en VPS          │
└───────────────────────┬─────────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────────────┐
│ 2. NAVEGADOR: Detecta que service-worker.js cambió         │
│    (hash diferente o contenido diferente)                    │
└───────────────────────┬─────────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────────────┐
│ 3. NAVEGADOR: Descarga nuevo Service Worker en segundo plano│
└───────────────────────┬─────────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────────────┐
│ 4. SERVICE WORKER: Se instala (estado: "waiting")           │
└───────────────────────┬─────────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────────────┐
│ 5. FRONTEND: Detecta actualización y muestra notificación   │
└───────────────────────┬─────────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────────────┐
│ 6. USUARIO: Acepta actualización o cancela                  │
└───────────────────────┬─────────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────────────┐
│ 7. SERVICE WORKER: Se activa (estado: "activated")         │
│    - Limpia caches antiguos                                  │
│    - Toma control de todas las páginas                       │
└───────────────────────┬─────────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────────────┐
│ 8. NAVEGADOR: Recarga la página con la nueva versión        │
└─────────────────────────────────────────────────────────────┘
```

---

## ❓ Preguntas Frecuentes

### ¿Necesito hacer algo especial para actualizar el PWA?

**No.** Solo necesitas:
1. `git pull` (actualizar código)
2. `npm run build` (recompilar)
3. El resto es automático

### ¿Los usuarios perderán datos al actualizar?

**No.** El Service Worker solo cachea archivos estáticos (HTML, CSS, JS). Los datos del usuario (localStorage, sesiones, etc.) no se tocan.

### ¿Qué pasa si un usuario no actualiza?

- Puede seguir usando la versión antigua
- Verá un botón "🔄 Actualizar" flotante
- La próxima vez que recargue la página, se actualizará automáticamente

### ¿Cuánto tiempo tarda en detectarse una actualización?

- **Inmediato**: Si el usuario tiene la app abierta, se detecta en segundos
- **Al abrir la app**: Si el usuario abre la app después del despliegue, se detecta al cargar
- **Máximo 1 hora**: Verificación automática en segundo plano cada hora

### ¿Funciona en todos los navegadores?

- ✅ **Chrome/Edge**: Soporte completo
- ✅ **Firefox**: Soporte completo
- ✅ **Safari (iOS)**: Soporte limitado (no hay Service Worker en modo standalone, pero el manifest funciona)
- ⚠️ **Safari (macOS)**: Soporte desde Safari 11.1+

### ¿Puedo forzar una actualización inmediata?

Sí, puedes:
1. Abrir Chrome DevTools → Application → Service Workers
2. Hacer clic en "Update" o "Unregister"
3. Recargar la página

O desde código:
```javascript
navigator.serviceWorker.getRegistration().then(reg => {
  if (reg) reg.update();
});
```

---

## 🐛 Troubleshooting

### El usuario no ve la actualización

1. **Verificar que el build se hizo correctamente**:
   ```bash
   ls -la ~/intranet2026/frontend/build/service-worker.js
   ```

2. **Verificar que Apache está sirviendo el nuevo build**:
   ```bash
   curl -I https://sistema.vanguardschools.edu.pe/service-worker.js
   ```

3. **Limpiar cache del navegador** (Chrome DevTools → Application → Clear storage)

### El Service Worker no se actualiza

1. **Verificar que el archivo cambió**:
   ```bash
   # Comparar hash del archivo
   md5sum ~/intranet2026/frontend/build/service-worker.js
   ```

2. **Forzar actualización manual**:
   - Chrome DevTools → Application → Service Workers → "Update"

3. **Desregistrar y volver a registrar**:
   - Chrome DevTools → Application → Service Workers → "Unregister"
   - Recargar la página

---

## 📝 Notas Importantes

1. **Versionado del cache**: El nombre del cache incluye una versión (`vanguard-intranet-v1.0.0`). Si cambias esta versión, se crea un nuevo cache y se elimina el antiguo.

2. **Archivos estáticos**: Los archivos JS y CSS generados por React Scripts tienen hashes únicos (ej: `main.abc123.js`). Esto asegura que el navegador siempre descargue la versión correcta.

3. **API no se cachea**: Las peticiones a `/api/` nunca se cachean, siempre van a la red. Esto asegura que los datos siempre estén actualizados.

4. **Estrategia Network First**: Prioriza la red sobre el cache, así los usuarios siempre ven la versión más reciente cuando hay conexión.

---

## ✅ Checklist de Despliegue

- [ ] `git pull origin main` en el VPS
- [ ] `cd frontend && npm run build`
- [ ] Verificar que `build/service-worker.js` existe
- [ ] `sudo systemctl reload apache2` (si es necesario)
- [ ] Probar en el navegador que la actualización se detecta
- [ ] Verificar en Chrome DevTools → Application → Service Workers que aparece la nueva versión

---

## 📚 Referencias

- [MDN: Service Worker API](https://developer.mozilla.org/es/docs/Web/API/Service_Worker_API)
- [Google: Service Worker Lifecycle](https://developers.google.com/web/fundamentals/primers/service-workers/lifecycle)
- [React Scripts: Service Workers](https://create-react-app.dev/docs/making-a-progressive-web-app/)

---

**Última actualización**: 2026-01-XX
**Versión del documento**: 1.0

