# 📚 Explicación Detallada de Git y el Flujo de Código

## 🎯 Conceptos Básicos

### ¿Qué es Git?
Git es un sistema de control de versiones. Piensa en él como una "máquina del tiempo" para tu código:
- Guarda **snapshots** (fotografías) de tu código en diferentes momentos
- Cada snapshot se llama **commit**
- Puedes volver atrás en el tiempo si algo sale mal

### ¿Dónde vive el código?

```
┌─────────────────────────────────────────────────────────────┐
│                    TU COMPUTADORA LOCAL                    │
│  (C:\react-aula-virtual)                                     │
│  - Aquí trabajas y haces cambios                            │
│  - Tienes tu propio repositorio Git                         │
└─────────────────────────────────────────────────────────────┘
                          │
                          │ git push
                          ▼
┌─────────────────────────────────────────────────────────────┐
│                      GITHUB (Internet)                       │
│  (https://github.com/LiamFranKi/intranet-2026)              │
│  - Almacén central del código                                │
│  - Todos pueden acceder (si tienen permisos)                │
│  - Es como una "nube" para tu código                         │
└─────────────────────────────────────────────────────────────┘
                          │
                          │ git pull
                          ▼
┌─────────────────────────────────────────────────────────────┐
│                      VPS (Servidor)                          │
│  (~/intranet2026)                                            │
│  - Aquí está el sistema funcionando en producción           │
│  - También tiene su propio repositorio Git                   │
│  - Descarga código de GitHub                                 │
└─────────────────────────────────────────────────────────────┘
```

## 🔄 Flujo Normal de Trabajo

### Escenario 1: Trabajas en tu computadora local

```
1. Haces cambios en tu código (local)
   📁 C:\react-aula-virtual\frontend\src\...

2. Creas un commit (local)
   git add .
   git commit -m "Agregar nueva funcionalidad"
   ✅ El commit se crea SOLO en tu computadora

3. Subes el commit a GitHub
   git push origin main
   ✅ Ahora el commit está en GitHub

4. En el VPS, descargas los cambios
   git pull origin main
   ✅ El VPS ahora tiene los cambios que hiciste localmente
```

### Escenario 2: Haces cambios directamente en el VPS

```
1. Te conectas al VPS y haces cambios
   ssh vanguard@89.117.52.9
   cd ~/intranet2026
   nano backend/routes/docente.routes.js  # Editas un archivo

2. Creas un commit (en el VPS)
   git add .
   git commit -m "Corregir bug en producción"
   ✅ El commit se crea SOLO en el VPS

3. Subes el commit a GitHub
   git push origin main
   ✅ Ahora el commit está en GitHub

4. En tu computadora local, descargas los cambios
   git pull origin main
   ✅ Tu computadora ahora tiene los cambios del VPS
```

## 📍 Dónde se Crea el Commit

### ✅ El commit se crea DONDE ejecutas el comando `git commit`

- Si ejecutas `git commit` en tu computadora → El commit se crea en tu computadora
- Si ejecutas `git commit` en el VPS → El commit se crea en el VPS
- **GitHub NO crea commits**, solo los almacena cuando haces `git push`

## 🔀 Flujo Completo: Local → GitHub → VPS

### Paso a Paso Detallado

#### **PASO 1: Trabajas en tu computadora local**

```bash
# Estás en: C:\react-aula-virtual
# Editas archivos, agregas código, etc.
```

**Estado:**
- ✅ Cambios en tu computadora
- ❌ No hay commit todavía
- ❌ GitHub no sabe de los cambios
- ❌ VPS no sabe de los cambios

#### **PASO 2: Creas un commit (LOCAL)**

```bash
# En tu computadora local
cd C:\react-aula-virtual
git add .
git commit -m "Agregar nueva funcionalidad"
```

**Estado:**
- ✅ Cambios en tu computadora
- ✅ Commit creado en tu computadora
- ❌ GitHub todavía no sabe
- ❌ VPS todavía no sabe

#### **PASO 3: Subes el commit a GitHub**

```bash
# En tu computadora local
git push origin main
```

**Estado:**
- ✅ Cambios en tu computadora
- ✅ Commit en tu computadora
- ✅ Commit ahora está en GitHub
- ❌ VPS todavía no sabe

#### **PASO 4: VPS descarga los cambios de GitHub**

```bash
# En el VPS (servidor)
ssh vanguard@89.117.52.9
cd ~/intranet2026
git pull origin main
```

**Estado:**
- ✅ Cambios en tu computadora
- ✅ Commit en tu computadora
- ✅ Commit en GitHub
- ✅ VPS ahora tiene los cambios

## 🔀 Flujo Completo: VPS → GitHub → Local

### Paso a Paso Detallado

#### **PASO 1: Haces cambios en el VPS**

```bash
# En el VPS
ssh vanguard@89.117.52.9
cd ~/intranet2026
nano backend/routes/docente.routes.js  # Editas archivo
```

**Estado:**
- ❌ Tu computadora no sabe
- ❌ GitHub no sabe
- ✅ Cambios solo en el VPS

#### **PASO 2: Creas un commit (EN EL VPS)**

```bash
# En el VPS
git add .
git commit -m "Corregir bug en producción"
```

**Estado:**
- ❌ Tu computadora no sabe
- ❌ GitHub no sabe
- ✅ Cambios en el VPS
- ✅ Commit creado en el VPS

#### **PASO 3: Subes el commit a GitHub (DESDE EL VPS)**

```bash
# En el VPS
git push origin main
```

**Estado:**
- ❌ Tu computadora no sabe
- ✅ Commit ahora está en GitHub
- ✅ Cambios en el VPS
- ✅ Commit en el VPS

#### **PASO 4: Tu computadora descarga los cambios**

```bash
# En tu computadora local
cd C:\react-aula-virtual
git pull origin main
```

**Estado:**
- ✅ Tu computadora ahora tiene los cambios
- ✅ Commit en GitHub
- ✅ Cambios en el VPS
- ✅ Commit en el VPS

## 🎯 Caso Específico: Backup en el VPS

### ¿Qué queremos hacer?

Queremos crear un "punto de respaldo" del código que está funcionando bien en el VPS.

### Opción A: Si NO hay cambios nuevos en el VPS

```
Estado actual:
- VPS tiene el mismo código que GitHub
- Todo funciona bien
- Queremos marcar este momento como "checkpoint"

Proceso:
1. En el VPS: git pull origin main  (asegurarse de estar actualizado)
2. En el VPS: git commit --allow-empty -m "Checkpoint"
   ✅ Crea un commit vacío (solo marca el momento)
3. En el VPS: git push origin main
   ✅ Sube el commit a GitHub
```

### Opción B: Si HAY cambios en el VPS que no están en GitHub

```
Estado actual:
- VPS tiene cambios que no están en GitHub
- Todo funciona bien
- Queremos guardar estos cambios

Proceso:
1. En el VPS: git add .  (agregar cambios al staging)
2. En el VPS: git commit -m "Backup VPS"
   ✅ Crea un commit con los cambios
3. En el VPS: git push origin main
   ✅ Sube el commit (con los cambios) a GitHub
```

## 📊 Comparación Visual

### Antes del Backup

```
┌──────────────┐         ┌──────────────┐         ┌──────────────┐
│   LOCAL      │         │   GITHUB     │         │     VPS      │
│              │         │              │         │              │
│ Commit A     │ ──────> │ Commit A     │ ──────> │ Commit A     │
│ Commit B     │         │ Commit B     │         │ Commit B     │
│              │         │              │         │ (funcionando)│
└──────────────┘         └──────────────┘         └──────────────┘
```

### Después del Backup (desde el VPS)

```
┌──────────────┐         ┌──────────────┐         ┌──────────────┐
│   LOCAL      │         │   GITHUB     │         │     VPS      │
│              │         │              │         │              │
│ Commit A     │         │ Commit A     │         │ Commit A     │
│ Commit B     │         │ Commit B     │         │ Commit B     │
│              │         │ Commit C     │ <────── │ Commit C     │
│              │         │ (BACKUP)     │         │ (BACKUP)     │
└──────────────┘         └──────────────┘         └──────────────┘
                              ▲
                              │
                              │ git pull (después)
                              │
```

## 🔍 Comandos Explicados

### `git status`
**¿Qué hace?**
- Muestra el estado actual del repositorio
- Te dice si hay cambios sin commit
- Te dice si estás sincronizado con GitHub

**Ejemplo:**
```bash
git status
# Output:
# On branch main
# Your branch is up to date with 'origin/main'
# nothing to commit, working tree clean
```

### `git add .`
**¿Qué hace?**
- Agrega TODOS los archivos modificados al "staging area"
- Es como decirle a Git: "Estos archivos quiero que estén en el próximo commit"

**¿Dónde se ejecuta?**
- Donde estés trabajando (local o VPS)

### `git commit -m "mensaje"`
**¿Qué hace?**
- Crea un "snapshot" (fotografía) del código en ese momento
- Guarda todos los cambios que agregaste con `git add`

**¿Dónde se crea el commit?**
- **DONDE ejecutas el comando**
- Si lo ejecutas en local → commit en local
- Si lo ejecutas en VPS → commit en VPS

### `git push origin main`
**¿Qué hace?**
- Sube los commits locales a GitHub
- Copia los commits de tu repositorio local al repositorio en GitHub

**¿Dónde se ejecuta?**
- Puede ser desde local o desde VPS
- Sube los commits que tienes localmente a GitHub

### `git pull origin main`
**¿Qué hace?**
- Descarga commits de GitHub
- Actualiza tu repositorio local con los cambios de GitHub

**¿Dónde se ejecuta?**
- Puede ser en local o en VPS
- Descarga los commits que están en GitHub

## 🎯 Resumen Simple

1. **Commit = Fotografía del código en un momento**
2. **El commit se crea DONDE ejecutas `git commit`**
3. **`git push` = Subir commits a GitHub**
4. **`git pull` = Descargar commits de GitHub**
5. **GitHub = Almacén central, todos sincronizan con él**

## 💡 Analogía Simple

Imagina que:
- **Tu computadora** = Tu cuaderno de apuntes
- **VPS** = La pizarra en el salón
- **GitHub** = La biblioteca central

**Flujo:**
1. Escribes en tu cuaderno (local) → `git commit`
2. Copias tu apunte a la biblioteca (GitHub) → `git push`
3. Alguien lee de la biblioteca y copia a la pizarra (VPS) → `git pull`
4. O viceversa: escribes en la pizarra (VPS) → `git commit`
5. Copias a la biblioteca (GitHub) → `git push`
6. Copias de la biblioteca a tu cuaderno (local) → `git pull`

## ❓ Preguntas Frecuentes

### ¿Puedo crear commits en el VPS?
**Sí**, puedes crear commits en cualquier lugar donde tengas un repositorio Git.

### ¿El commit se crea en GitHub?
**No**, GitHub solo almacena commits. Los commits se crean localmente (en tu computadora o en el VPS) y luego se suben a GitHub.

### ¿Si creo un commit en el VPS, aparece en mi computadora?
**No automáticamente**. Debes:
1. Hacer `git push` en el VPS (subir a GitHub)
2. Hacer `git pull` en tu computadora (descargar de GitHub)

### ¿Puedo tener commits diferentes en local y VPS?
**Sí**, pero no es recomendable. Lo ideal es sincronizar siempre con GitHub.

### ¿Qué pasa si hay conflictos?
Git te avisará y tendrás que resolverlos manualmente antes de poder hacer push o pull.

