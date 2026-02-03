# 📝 Guía Paso a Paso: Crear Backup en el VPS

## 🎯 Objetivo
Crear un "punto de respaldo" del código que está funcionando bien en el VPS y guardarlo en GitHub.

## 📍 ¿Dónde se hace cada cosa?

- **VPS** = Servidor donde está funcionando el sistema
- **GitHub** = Almacén en la nube donde guardamos el código
- **Local** = Tu computadora (no la usaremos en este proceso)

## 🔄 Flujo del Backup

```
VPS (funcionando bien)
    │
    │ 1. Verificar estado
    │ 2. Crear commit (en el VPS)
    │
    ▼
GitHub (almacén central)
    │
    │ 3. Subir commit (git push)
    │
    ▼
✅ Backup guardado en GitHub
```

## 📋 Pasos Detallados

### PASO 1: Conectarse al VPS

```bash
# En tu computadora, abre una terminal y ejecuta:
ssh vanguard@89.117.52.9

# Te pedirá la contraseña:
# Contraseña: CtxADB8q0SaVYox
```

**¿Qué hace esto?**
- Te conecta al servidor (VPS)
- Ahora estás trabajando directamente en el servidor

---

### PASO 2: Ir al directorio del proyecto

```bash
cd ~/intranet2026
```

**¿Qué hace esto?**
- Te mueve a la carpeta donde está el código del proyecto
- `~` significa "carpeta home del usuario"

**Verificar que estás en el lugar correcto:**
```bash
pwd
# Debería mostrar: /home/vanguard/intranet2026

ls -la
# Debería mostrar archivos como: backend/, frontend/, etc.
```

---

### PASO 3: Verificar el estado actual

```bash
git status
```

**¿Qué hace esto?**
- Te muestra si hay cambios sin guardar
- Te dice si estás sincronizado con GitHub

**Posibles resultados:**

**A) Todo está sincronizado:**
```
On branch main
Your branch is up to date with 'origin/main'.
nothing to commit, working tree clean
```
✅ Significa: El código del VPS es igual al de GitHub

**B) Hay cambios sin guardar:**
```
On branch main
Your branch is ahead of 'origin/main' by 1 commit.
Changes not staged for commit:
  modified:   backend/routes/docente.routes.js
```
⚠️ Significa: Hay cambios en el VPS que no están en GitHub

---

### PASO 4: Sincronizar con GitHub (traer cambios remotos)

```bash
git fetch origin
git pull origin main
```

**¿Qué hace esto?**
- `git fetch`: Ve si hay cambios en GitHub que no tienes
- `git pull`: Descarga esos cambios y los aplica

**Si todo está bien, verás:**
```
Already up to date.
```

**Si había cambios, verás:**
```
Updating abc1234..def5678
Fast-forward
 backend/routes/docente.routes.js | 5 +++++
 1 file changed, 5 insertions(+)
```

---

### PASO 5: Crear el commit de backup

**Opción A: Si NO hay cambios nuevos (todo está sincronizado)**

```bash
git commit --allow-empty -m "💾 Checkpoint VPS - Estado funcionando correctamente - $(date +%Y-%m-%d)"
```

**¿Qué hace esto?**
- Crea un commit "vacío" (sin cambios de código)
- Es como poner una "bandera" que dice: "En este momento todo funcionaba bien"
- El `$(date +%Y-%m-%d)` agrega la fecha automáticamente

**Resultado:**
```
[main abc1234] 💾 Checkpoint VPS - Estado funcionando correctamente - 2026-02-03
```

---

**Opción B: Si HAY cambios nuevos (archivos modificados)**

Primero, agregar los cambios:
```bash
git add .
```

**¿Qué hace esto?**
- Agrega todos los archivos modificados al "staging area"
- Es como decirle a Git: "Estos archivos quiero guardarlos"

Luego, crear el commit:
```bash
git commit -m "💾 Backup VPS - Estado funcionando correctamente - $(date +%Y-%m-%d)"
```

**¿Qué hace esto?**
- Crea un commit con los cambios que agregaste
- Guarda una "fotografía" del código en ese momento

**Resultado:**
```
[main def5678] 💾 Backup VPS - Estado funcionando correctamente - 2026-02-03
 1 file changed, 10 insertions(+), 5 deletions(-)
```

---

### PASO 6: Subir el commit a GitHub

```bash
git push origin main
```

**¿Qué hace esto?**
- Copia el commit que acabas de crear (en el VPS) a GitHub
- Ahora el commit está guardado en la nube

**Resultado esperado:**
```
Enumerating objects: 5, done.
Counting objects: 100% (5/5), done.
Writing objects: 100% (3/3), 245 bytes | 245.00 KiB/s, done.
To https://github.com/LiamFranKi/intranet-2026.git
   abc1234..def5678  main -> main
```

✅ **¡Listo!** El backup está ahora en GitHub.

---

### PASO 7: Verificar que todo está bien

```bash
git status
```

**Debería mostrar:**
```
On branch main
Your branch is up to date with 'origin/main'.
nothing to commit, working tree clean
```

**Ver los últimos commits:**
```bash
git log --oneline -5
```

**Debería mostrar algo como:**
```
def5678 💾 Backup VPS - Estado funcionando correctamente - 2026-02-03
abc1234 Corregir duplicación de estrellas
...
```

---

## 🎯 Resumen del Proceso

```
1. Conectarse al VPS
   ssh vanguard@89.117.52.9

2. Ir al proyecto
   cd ~/intranet2026

3. Verificar estado
   git status

4. Sincronizar con GitHub
   git pull origin main

5. Crear commit (en el VPS)
   git add .                    # Solo si hay cambios
   git commit -m "Backup..."    # Crea el commit EN EL VPS

6. Subir a GitHub
   git push origin main          # Copia el commit a GitHub

7. Verificar
   git status
   git log --oneline -3
```

## 📊 Visualización del Flujo

### Antes del Backup

```
┌─────────────────┐         ┌─────────────────┐
│      VPS        │         │     GitHub      │
│                 │         │                 │
│ Código v1.0     │ ──────> │ Código v1.0     │
│ (funcionando)   │         │                 │
└─────────────────┘         └─────────────────┘
```

### Después del Backup

```
┌─────────────────┐         ┌─────────────────┐
│      VPS        │         │     GitHub      │
│                 │         │                 │
│ Código v1.0     │         │ Código v1.0     │
│ Commit Backup   │ ──────> │ Commit Backup   │
│ (marcador)      │         │ (guardado)      │
└─────────────────┘         └─────────────────┘
```

## ❓ Preguntas Frecuentes

### ¿El commit se crea en el VPS o en GitHub?
**En el VPS**. Cuando ejecutas `git commit`, el commit se crea en el VPS. Luego, con `git push`, lo copias a GitHub.

### ¿Qué pasa si hay un error al hacer push?
Git te dirá qué pasó. Posibles causas:
- Problemas de conexión
- No tienes permisos
- Hay conflictos

### ¿Puedo hacer esto desde mi computadora local?
**Sí**, pero el backup sería del código de tu computadora, no del VPS. Si quieres respaldar el estado del VPS, debes hacerlo desde el VPS.

### ¿Los archivos .env se suben a GitHub?
**No**, los archivos `.env` están en `.gitignore`, así que no se suben. Esto es correcto por seguridad.

### ¿Cómo sé si el backup funcionó?
Ejecuta:
```bash
git log --oneline -3
```
Deberías ver tu commit de backup en la lista.

## 🔙 Cómo Volver a Este Estado (Si algo sale mal)

Si en el futuro algo se rompe y quieres volver a este estado:

```bash
# 1. Ver los commits
git log --oneline -10

# 2. Encontrar el hash del commit de backup
# Ejemplo: def5678 💾 Backup VPS - Estado funcionando correctamente

# 3. Volver a ese commit
git reset --hard def5678

# 4. Reiniciar el backend
pm2 restart intranet2026-backend
```

## ✅ Checklist Final

- [ ] Conectado al VPS
- [ ] En el directorio correcto (`~/intranet2026`)
- [ ] Estado verificado (`git status`)
- [ ] Sincronizado con GitHub (`git pull`)
- [ ] Commit creado (`git commit`)
- [ ] Commit subido a GitHub (`git push`)
- [ ] Verificado (`git log` muestra el commit)

¡Listo! Tu backup está guardado en GitHub. 🎉

