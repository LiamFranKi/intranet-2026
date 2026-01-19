# 📊 PLANTILLA COMPLETA: DASHBOARD ADMINISTRADOR

## 📋 INFORMACIÓN GENERAL

Este documento contiene **TODOS** los detalles de diseño, colores, tipografía, iconos y textos exactos del Dashboard del Administrador para replicar el diseño de forma idéntica.

---

## 🔤 TIPOGRAFÍA (FONT-FAMILY)

### Fuente Principal
```css
font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 
  'Oxygen', 'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue',
  sans-serif;
```

### Propiedades de Texto
- **Antialiasing**: `-webkit-font-smoothing: antialiased;`
- **Moz Antialiasing**: `-moz-osx-font-smoothing: grayscale;`
- **Line Height**: `1.6` (general), `1.2` (títulos)

### Tamaños de Fuente Específicos

| Elemento | Tamaño | Peso | Color |
|----------|--------|------|-------|
| Título Principal (h1) | `2rem` (32px) | `700` (Bold) | `#1f2937` |
| Subtítulo (p) | `1rem` (16px) | `400` (Normal) | `#6b7280` |
| Título de Card (h2) | `1.25rem` (20px) | `700` (Bold) | `#1f2937` |
| Título de Stat Card | `0.875rem` (14px) | `500` (Medium) | `#6b7280` |
| Valor de Stat Card | `2rem` (32px) | `700` (Bold) | `#1f2937` |
| Texto de Actividad | `0.9rem` (14.4px) | `400` (Normal) | `#374151` |
| Tiempo de Actividad | `0.75rem` (12px) | `400` (Normal) | `#9ca3af` |
| Título Acceso Rápido | `0.9rem` (14.4px) | `600` (SemiBold) | `white` |

---

## 🎨 COLORES

### Colores Principales

| Nombre | Código Hex | Uso |
|--------|------------|-----|
| **Primary Color** | `#667eea` | Color principal del sistema (gradiente) |
| **Secondary Color** | `#764ba2` | Color secundario del sistema (gradiente) |
| **Dark** | `#1f2937` | Textos principales, títulos |
| **Gray Dark** | `#2c3e50` | Textos secundarios |
| **Gray Medium** | `#6b7280` | Textos descriptivos, subtítulos |
| **Gray Light** | `#9ca3af` | Textos de tiempo, metadata |
| **Background** | `#f5f6fa` | Fondo principal del dashboard |
| **White** | `#ffffff` | Fondo de cards, elementos |

### Colores de Stat Cards (Borde Izquierdo)

| Card | Color | Código Hex |
|------|-------|------------|
| Total Alumnos | Azul púrpura | `#667eea` |
| Total Docentes | Rosa | `#f093fb` |
| Total Grados | Azul claro | `#4facfe` |
| Total Ingresos | Verde | `#43e97b` |

### Colores de Gráficos

#### Gráfico de Barras (Alumnos por Nivel)
- **Background Colors** (con opacidad 0.8):
  - `rgba(102, 126, 234, 0.8)` - Azul púrpura
  - `rgba(240, 147, 251, 0.8)` - Rosa
  - `rgba(79, 172, 254, 0.8)` - Azul claro
  - `rgba(67, 233, 123, 0.8)` - Verde
- **Border Colors** (sin opacidad):
  - `rgba(102, 126, 234, 1)`
  - `rgba(240, 147, 251, 1)`
  - `rgba(79, 172, 254, 1)`
  - `rgba(67, 233, 123, 1)`
- **Border Width**: `2px`

#### Gráfico de Líneas (Ingresos vs Deudas)
- **Ingresos**:
  - Border: `rgba(67, 233, 123, 1)` (Verde)
  - Background: `rgba(67, 233, 123, 0.2)` (Verde con opacidad)
- **Deudas**:
  - Border: `rgba(239, 68, 68, 1)` (Rojo)
  - Background: `rgba(239, 68, 68, 0.2)` (Rojo con opacidad)
- **Tension**: `0.4` (curva suave)

### Colores de Estados

| Estado | Background | Color Texto |
|--------|-----------|-------------|
| Hover Card | `#f3f4f6` | - |
| Hover Stat Card | Sombra aumentada | - |
| Active Link | `rgba(255, 255, 255, 0.2)` | `white` |
| Empty State | - | `#6b7280` |

---

## 📐 BARRA DE MENÚ (SIDEBAR)

### Color de Fondo
```css
background: var(--sidebar-bg, #667eea);
```
**Color por defecto**: `#667eea` (Azul púrpura)

### Color de Texto
```css
color: var(--sidebar-text, white);
```
**Color por defecto**: `white` (Blanco)

### Ancho
- **Desktop**: `280px` (fijo)
- **Móvil**: `280px` (máximo `85vw`)

### Estructura del Sidebar

#### Header del Sidebar
- **Padding**: `1.5rem` (24px)
- **Border Bottom**: `1px solid rgba(255, 255, 255, 0.1)`
- **Logo**: Imagen o emoji `🎓` (tamaño `2rem`)
- **Texto Logo**: `1.25rem`, peso `800`, color `white`

#### Navegación
- **Padding Vertical**: `1rem` (16px)
- **Gap entre items**: `0.25rem` (4px)

#### Items del Menú
- **Padding**: `0.875rem 1.5rem` (14px 24px)
- **Font Size**: `0.95rem` (15.2px)
- **Font Weight**: `500` (Medium)
- **Color**: `white` con `opacity: 0.95`
- **Hover**: `background: rgba(255, 255, 255, 0.12)`, `opacity: 1`
- **Active**: `background: rgba(255, 255, 255, 0.2)`, `border-left: 3px solid white`, `font-weight: 600`

#### Iconos del Menú
- **Tamaño**: `1.25rem` (20px)
- **Ancho fijo**: `24px`
- **Filter**: `drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3))`
- **Text Shadow**: `0 1px 3px rgba(0, 0, 0, 0.4)`

#### Submenús
- **Margin Left**: `3rem` (48px)
- **Padding Sublink**: `0.625rem 1.5rem` (10px 24px)
- **Font Size**: `0.9rem` (14.4px)
- **Border Left Active**: `3px solid white`

### Menú Completo con Iconos

| Título | Icono | Tipo | Ruta |
|--------|-------|------|------|
| **Dashboard** | `📊` | Link directo | `/admin/dashboard` |
| **Matrículas** | `📋` | Link directo | `/admin/matriculas` |
| **Usuarios** | `👥` | Submenú | - |
| └─ Administradores | - | Sublink | `/admin/usuarios/administradores` |
| └─ Personal | - | Sublink | `/admin/usuarios/personal` |
| └─ Alumnos | - | Sublink | `/admin/usuarios/alumnos` |
| └─ Apoderados | - | Sublink | `/admin/usuarios/apoderados` |
| **Académico** | `🎓` | Submenú | - |
| └─ Niveles | - | Sublink | `/admin/academico/niveles` |
| └─ Grados | - | Sublink | `/admin/academico/grados` |
| └─ Cursos | - | Sublink | `/admin/academico/cursos` |
| └─ Asignaturas | - | Sublink | `/admin/academico/asignaturas` |
| └─ Áreas | - | Sublink | `/admin/academico/areas` |
| **Gamificación** | `🎮` | Submenú | - |
| └─ Niveles (Gamificación) | - | Sublink | `/admin/gamificacion/niveles` |
| └─ Logros | - | Sublink | `/admin/gamificacion/logros` |
| └─ Avatares | - | Sublink | `/admin/gamificacion/avatares` |
| └─ Ranking | - | Sublink | `/admin/gamificacion/ranking` |
| **Aula Virtual** | `🏫` | Link directo | `/admin/aula-virtual` |
| **Calendario** | `📅` | Link directo | `/calendario` |
| **Notificaciones** | `🔔` | Link directo | `/admin/notificaciones` |
| **ASISTENTE IA** | `🤖` | Link directo | `/admin/vanguarcito` |
| **Reportes** | `📈` | Submenú | - |
| └─ Asistencias | - | Sublink | `/admin/reportes/asistencias` |
| └─ Notas | - | Sublink | `/admin/reportes/notas` |
| └─ Estadísticas | - | Sublink | `/admin/reportes/estadisticas` |
| **Configuración** | `⚙️` | Submenú | - |
| └─ Año Escolar | - | Sublink | `/admin/config/anio-escolar` |
| └─ General | - | Sublink | `/admin/config/general` |
| └─ Temas y Colores | - | Sublink | `/admin/config/temas` |
| └─ PWA | - | Sublink | `/admin/config/pwa` |

### Footer del Sidebar
- **Versión**: `v1.0.0`
- **Font Size**: `0.75rem` (12px)
- **Color**: `white` con `opacity: 0.8`
- **Padding**: `1rem 1.5rem` (16px 24px)

---

## 📊 HEADER (BARRA SUPERIOR)

### Color de Fondo
```css
background: var(--navbar-bg, #ffffff);
```
**Color por defecto**: `#ffffff` (Blanco)

### Border
- **Border Bottom**: `1px solid #e5e7eb` (Gris claro)

### Padding
- **Desktop**: `1rem 2rem` (16px 32px)
- **Tablet**: `1rem 1.5rem` (16px 24px)
- **Móvil**: `0.875rem 1rem` (14px 16px)

### Elementos del Header

#### Botón Menú (Móvil)
- **Icono**: `☰` (Hamburger)
- **Font Size**: `1.5rem` (24px)
- **Color**: `#374151`
- **Display**: `none` en desktop, `block` en móvil

#### Avatar de Usuario
- **Tamaño**: `40px × 40px`
- **Border Radius**: `50%` (círculo)
- **Background**: `linear-gradient(135deg, #667eea 0%, #764ba2 100%)`
- **Color Texto**: `white`
- **Font Size**: `1rem` (16px)
- **Font Weight**: `700` (Bold)
- **Contenido**: Primera letra del nombre completo

#### Información de Usuario
- **Nombre**:
  - Font Size: `0.9rem` (14.4px)
  - Font Weight: `600` (SemiBold)
  - Color: `#1f2937`
- **Rol**:
  - Font Size: `0.75rem` (12px)
  - Color: `#6b7280`
  - Text Transform: `uppercase`
  - Letter Spacing: `0.5px`

#### Botones de Acción
- **Notificaciones**: `🔔`
  - Badge: Fondo `#ef4444` (Rojo), texto `white`
  - Font Size Badge: `0.65rem` (10.4px)
  - Font Weight Badge: `700` (Bold)
- **Mi Perfil**: `👤`
- **Cerrar Sesión**: `⏻`
  - Hover: Fondo `#fee2e2`, border `#fecaca`
  - Color Icono: `#dc2626`

---

## 🎯 CONTENIDO DEL DASHBOARD

### Contenedor Principal
- **Max Width**: `1400px`
- **Margin**: `0 auto` (centrado)
- **Background**: `#f5f6fa` (Gris muy claro)

### Sección de Bienvenida

#### Título Principal
```html
¡Bienvenido de vuelta, {nombre_completo}! 👋
```
- **Icono**: `👋`
- **Font Size**: `2rem` (32px)
- **Font Weight**: `700` (Bold)
- **Color**: `#1f2937`
- **Margin Bottom**: `0.5rem` (8px)

#### Subtítulo
```html
Aquí tienes un resumen de tu sistema educativo
```
- **Font Size**: `1rem` (16px)
- **Color**: `#6b7280`
- **Margin Bottom**: `2rem` (32px)

---

## 📈 STAT CARDS (TARJETAS DE ESTADÍSTICAS)

### Grid de Stats
- **Display**: `grid`
- **Grid Template Columns**: `repeat(auto-fit, minmax(250px, 1fr))`
- **Gap**: `1.5rem` (24px)
- **Margin Bottom**: `2rem` (32px)

### Stat Card Individual

#### Estructura
```html
<div class="stat-card">
  <div class="stat-icon">{icono}</div>
  <div class="stat-content">
    <h3 class="stat-title">{título}</h3>
    <div class="stat-value">{valor}</div>
  </div>
</div>
```

#### Estilos
- **Background**: `white`
- **Border Radius**: `16px`
- **Padding**: `1.5rem` (24px)
- **Box Shadow**: `0 1px 3px rgba(0, 0, 0, 0.1)`
- **Display**: `flex`
- **Gap**: `1rem` (16px)
- **Border Left**: `4px solid {color_card}`
- **Transition**: `all 0.3s`
- **Hover**: 
  - Transform: `translateY(-4px)`
  - Box Shadow: `0 12px 24px rgba(0, 0, 0, 0.15)`

#### Icono de Stat Card
- **Font Size**: `3rem` (48px)
- **Line Height**: `1`

#### Título de Stat Card
- **Font Size**: `0.875rem` (14px)
- **Font Weight**: `500` (Medium)
- **Color**: `#6b7280`
- **Margin**: `0 0 0.5rem 0`
- **Text Transform**: `uppercase`
- **Letter Spacing**: `0.5px`

#### Valor de Stat Card
- **Font Size**: `2rem` (32px)
- **Font Weight**: `700` (Bold)
- **Color**: `#1f2937`
- **Margin Bottom**: `0.5rem` (8px)

### Stats Cards Específicas

| Título | Icono | Color Borde | Valor Ejemplo |
|--------|-------|-------------|---------------|
| **Total Alumnos** | `👨‍🎓` | `#667eea` | `1,234` (formato es-PE) |
| **Total Docentes** | `👨‍🏫` | `#f093fb` | `45` (formato es-PE) |
| **Total Grados** | `📚` | `#4facfe` | `12` (formato es-PE) |
| **Total Ingresos** | `💰` | `#43e97b` | `S/. 12,345.67` |

---

## 📋 CARDS PRINCIPALES

### Grid de Cards
- **Display**: `grid`
- **Grid Template Columns**: `repeat(auto-fit, minmax(400px, 1fr))`
- **Gap**: `1.5rem` (24px)
- **Margin Bottom**: `1.5rem` (24px)

### Card Individual

#### Estructura
```html
<div class="dashboard-card">
  <div class="card-header">
    <h2>{título con icono}</h2>
  </div>
  <div class="card-body">
    {contenido}
  </div>
</div>
```

#### Estilos
- **Background**: `white`
- **Border Radius**: `16px`
- **Box Shadow**: `0 1px 3px rgba(0, 0, 0, 0.1)`
- **Overflow**: `hidden`

#### Card Header
- **Padding**: `1.5rem` (24px)
- **Border Bottom**: `1px solid #e5e7eb`
- **Título (h2)**:
  - Font Size: `1.25rem` (20px)
  - Font Weight: `700` (Bold)
  - Color: `#1f2937`
  - Margin: `0`

#### Card Body
- **Padding**: `1.5rem` (24px)
- **Min Height**: `200px` (para gráficos)

---

## 📋 ACTIVIDAD RECIENTE

### Título del Card
```html
📋 Actividad Reciente
```
- **Icono**: `📋`
- **Font Size**: `1.25rem` (20px)
- **Font Weight**: `700` (Bold)
- **Color**: `#1f2937`

### Lista de Actividades

#### Contenedor
- **Display**: `flex`
- **Flex Direction**: `column`
- **Gap**: `1rem` (16px)

#### Item de Actividad

##### Estructura
```html
<div class="activity-item">
  <span class="activity-icon">{icono}</span>
  <div class="activity-content">
    <p class="activity-text">{texto}</p>
    <span class="activity-time">{tiempo}</span>
  </div>
</div>
```

##### Estilos
- **Display**: `flex`
- **Gap**: `1rem` (16px)
- **Padding**: `1rem` (16px)
- **Background**: `#f9fafb`
- **Border Radius**: `12px`
- **Transition**: `all 0.2s`
- **Hover**:
  - Background: `#f3f4f6`
  - Transform: `translateX(4px)`

##### Icono de Actividad
- **Font Size**: `1.5rem` (24px)
- **Width**: `40px`
- **Height**: `40px`
- **Background**: `white`
- **Border Radius**: `10px`
- **Display**: `flex`
- **Align Items**: `center`
- **Justify Content**: `center`
- **Flex Shrink**: `0`

##### Texto de Actividad
- **Font Size**: `0.9rem` (14.4px)
- **Color**: `#374151`
- **Margin**: `0 0 0.25rem 0`

##### Tiempo de Actividad
- **Font Size**: `0.75rem` (12px)
- **Color**: `#9ca3af`

### Estado Vacío
```html
No hay actividades recientes
```
- **Text Align**: `center`
- **Padding**: `2rem` (32px)
- **Color**: `#6b7280`
- **Font Size**: `0.9rem` (14.4px)

---

## 🚀 ACCESOS RÁPIDOS

### Título del Card
```html
🚀 Accesos Rápidos
```
- **Icono**: `🚀`
- **Font Size**: `1.25rem` (20px)
- **Font Weight**: `700` (Bold)
- **Color**: `#1f2937`

### Grid de Accesos
- **Display**: `grid`
- **Grid Template Columns**: `repeat(2, 1fr)`
- **Gap**: `1rem` (16px)

### Item de Acceso Rápido

#### Estructura
```html
<div class="quick-access-item">
  <span class="quick-icon">{icono}</span>
  <span class="quick-title">{título}</span>
</div>
```

#### Estilos
- **Display**: `flex`
- **Flex Direction**: `column`
- **Align Items**: `center`
- **Justify Content**: `center`
- **Padding**: `1.5rem` (24px)
- **Background**: `linear-gradient(135deg, #667eea 0%, #764ba2 100%)`
- **Border Radius**: `12px`
- **Cursor**: `pointer`
- **Transition**: `all 0.3s`
- **Text Align**: `center`
- **Gap**: `0.5rem` (8px)
- **Hover**:
  - Transform: `translateY(-4px) scale(1.02)`
  - Box Shadow: `0 12px 24px rgba(102, 126, 234, 0.3)`
  - Background: `linear-gradient(135deg, #764ba2 0%, #667eea 100%)` (invertido)

#### Icono de Acceso
- **Font Size**: `2.5rem` (40px)
- **Filter**: `drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2))`

#### Título de Acceso
- **Font Size**: `0.9rem` (14.4px)
- **Font Weight**: `600` (SemiBold)
- **Color**: `white`

### Accesos Rápidos Específicos

| Título | Icono | Ruta |
|--------|-------|------|
| **Gestionar Usuarios** | `👥` | `/admin/usuarios/administradores` |
| **Ver Alumnos** | `🎓` | `/admin/academico/grados` |
| **Reportes** | `📊` | `/admin/reportes` |
| **Configuración** | `⚙️` | `/admin/config/anio-escolar` |

---

## 📊 GRÁFICOS

### Gráfico: Alumnos por Nivel

#### Título del Card
```html
📊 Alumnos por Nivel
```
- **Icono**: `📊`
- **Font Size**: `1.25rem` (20px)
- **Font Weight**: `700` (Bold)
- **Color**: `#1f2937`

#### Tipo de Gráfico
- **Tipo**: Bar Chart (Gráfico de Barras)
- **Altura**: `300px`
- **Position**: `relative`

#### Configuración
- **Responsive**: `true`
- **Maintain Aspect Ratio**: `false`
- **Legend Position**: `top`
- **Scales Y**: `beginAtZero: true`

#### Colores (según nivel)
- Nivel 1: `rgba(102, 126, 234, 0.8)` / `rgba(102, 126, 234, 1)`
- Nivel 2: `rgba(240, 147, 251, 0.8)` / `rgba(240, 147, 251, 1)`
- Nivel 3: `rgba(79, 172, 254, 0.8)` / `rgba(79, 172, 254, 1)`
- Nivel 4: `rgba(67, 233, 123, 0.8)` / `rgba(67, 233, 123, 1)`

### Gráfico: Ingresos vs Deudas

#### Título del Card
```html
💰 Ingresos vs Deudas
```
- **Icono**: `💰`
- **Font Size**: `1.25rem` (20px)
- **Font Weight**: `700` (Bold)
- **Color**: `#1f2937`

#### Tipo de Gráfico
- **Tipo**: Line Chart (Gráfico de Líneas)
- **Altura**: `300px`
- **Position**: `relative`

#### Configuración
- **Responsive**: `true`
- **Maintain Aspect Ratio**: `false`
- **Legend Position**: `top`
- **Tension**: `0.4` (curva suave)
- **Fill**: `true` (área rellena)

#### Datasets
- **Ingresos**:
  - Label: `"Ingresos"`
  - Border Color: `rgba(67, 233, 123, 1)` (Verde)
  - Background Color: `rgba(67, 233, 123, 0.2)` (Verde con opacidad)
- **Deudas**:
  - Label: `"Deudas"`
  - Border Color: `rgba(239, 68, 68, 1)` (Rojo)
  - Background Color: `rgba(239, 68, 68, 0.2)` (Rojo con opacidad)

### Estado Vacío de Gráficos
```html
No hay datos disponibles
```
- **Text Align**: `center`
- **Padding**: `2rem` (32px)
- **Color**: `#6b7280`
- **Font Size**: `0.9rem` (14.4px)

---

## 🔄 ESTADO DE CARGA

### Estructura
```html
<div class="loading-state">
  <div class="spinner-large"></div>
  <p>Cargando dashboard...</p>
</div>
```

### Spinner
- **Width**: `48px`
- **Height**: `48px`
- **Border**: `4px solid rgba(102, 126, 234, 0.1)`
- **Border Top Color**: `#667eea`
- **Border Radius**: `50%`
- **Animation**: `spin 1s linear infinite`
- **Margin**: `0 auto 1rem`

### Texto de Carga
```html
Cargando dashboard...
```
- **Font Size**: `0.9rem` (14.4px)
- **Color**: `#6b7280`
- **Text Align**: `center`

---

## 📱 RESPONSIVE

### Breakpoints

#### Desktop (> 1024px)
- Sidebar: Visible, fijo a la izquierda
- Header Menu Button: Oculto
- Stats Grid: `repeat(auto-fit, minmax(250px, 1fr))`
- Dashboard Grid: `repeat(auto-fit, minmax(400px, 1fr))`
- Quick Access Grid: `repeat(2, 1fr)`

#### Tablet (≤ 1024px)
- Sidebar: Oculto por defecto, se muestra con overlay
- Header Menu Button: Visible
- Stats Grid: `repeat(2, 1fr)`
- Dashboard Grid: `1fr` (una columna)
- Quick Access Grid: `1fr` (una columna)
- User Info: Visible

#### Móvil (≤ 640px)
- Sidebar: Oculto por defecto, se muestra con overlay
- Header Menu Button: Visible
- Stats Grid: `1fr` (una columna)
- Dashboard Grid: `1fr` (una columna)
- Quick Access Grid: `1fr` (una columna)
- User Info: Oculto (solo avatar)
- Padding Cards: `1.25rem` (20px)
- Stat Icon: `2.5rem` (40px)
- Stat Value: `1.75rem` (28px)

---

## 🎨 SOMBRAS Y EFECTOS

### Sombras

| Elemento | Sombra |
|----------|--------|
| Stat Card | `0 1px 3px rgba(0, 0, 0, 0.1)` |
| Stat Card Hover | `0 12px 24px rgba(0, 0, 0, 0.15)` |
| Dashboard Card | `0 1px 3px rgba(0, 0, 0, 0.1)` |
| Quick Access Hover | `0 12px 24px rgba(102, 126, 234, 0.3)` |
| Sidebar | `2px 0 10px rgba(0, 0, 0, 0.1)` |
| Header | `0 1px 3px rgba(0, 0, 0, 0.05)` |

### Transiciones

| Elemento | Transición |
|----------|------------|
| Stat Card | `all 0.3s` |
| Activity Item | `all 0.2s` |
| Quick Access Item | `all 0.3s` |
| Sidebar | `transform 0.3s ease` |
| Header Button | `all 0.2s` |

### Transformaciones

| Elemento | Transform |
|----------|-----------|
| Stat Card Hover | `translateY(-4px)` |
| Activity Item Hover | `translateX(4px)` |
| Quick Access Hover | `translateY(-4px) scale(1.02)` |

---

## 📝 TEXTOS EXACTOS

### Títulos y Frases

1. **Bienvenida**:
   - `¡Bienvenido de vuelta, {nombre_completo}! 👋`
   - `Aquí tienes un resumen de tu sistema educativo`

2. **Stat Cards**:
   - `Total Alumnos`
   - `Total Docentes`
   - `Total Grados`
   - `Total Ingresos`

3. **Cards**:
   - `📋 Actividad Reciente`
   - `🚀 Accesos Rápidos`
   - `📊 Alumnos por Nivel`
   - `💰 Ingresos vs Deudas`

4. **Accesos Rápidos**:
   - `Gestionar Usuarios`
   - `Ver Alumnos`
   - `Reportes`
   - `Configuración`

5. **Estados Vacíos**:
   - `No hay actividades recientes`
   - `No hay datos disponibles`
   - `Cargando dashboard...`

---

## 🎯 RESUMEN DE ESPECIFICACIONES

### Colores Clave
- **Sidebar**: `#667eea` (Azul púrpura)
- **Header**: `#ffffff` (Blanco)
- **Background**: `#f5f6fa` (Gris muy claro)
- **Cards**: `#ffffff` (Blanco)
- **Textos Principales**: `#1f2937` (Gris oscuro)
- **Textos Secundarios**: `#6b7280` (Gris medio)

### Tipografía
- **Fuente**: `Inter` (fallback a sistema)
- **Tamaños**: De `0.75rem` (12px) a `2rem` (32px)
- **Pesos**: `400` (Normal), `500` (Medium), `600` (SemiBold), `700` (Bold), `800` (ExtraBold)

### Espaciado
- **Gaps**: `0.5rem` (8px) a `2rem` (32px)
- **Padding**: `0.75rem` (12px) a `2rem` (32px)
- **Margins**: `0.5rem` (8px) a `2rem` (32px)

### Border Radius
- **Cards**: `16px`
- **Items**: `12px`
- **Avatar**: `50%` (círculo)
- **Badges**: `999px` (pill)

---

**¡Plantilla completa lista para replicar!** 🚀

