# 🎨 DISEÑO COMPLETO: LOGIN, DASHBOARD Y AULA VIRTUAL

## 📋 ÍNDICE

1. [Variables CSS Globales](#variables-css-globales)
2. [Diseño del Login](#diseño-del-login)
3. [Diseño del Dashboard](#diseño-del-dashboard)
4. [Diseño del Aula Virtual](#diseño-del-aula-virtual)
5. [Componentes Compartidos](#componentes-compartidos)

---

## 🎨 VARIABLES CSS GLOBALES

### Colores Principales

```css
:root {
  /* Colores primarios */
  --primary: #1976d2;
  --primary-dark: #0d47a1;
  --primary-light: #42a5f5;
  
  --secondary: #00897b;
  --secondary-dark: #00695c;
  --secondary-light: #4db6ac;
  
  --accent: #ff6f00;
  --accent-dark: #e65100;
  --accent-light: #ff9800;
  
  /* Gradientes principales */
  --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  --gradient-secondary: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  --gradient-success: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
  --gradient-info: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
  
  /* Colores personalizables (desde configuración) */
  --primary-color: #667eea;
  --secondary-color: #764ba2;
  --sidebar-bg: #667eea;
  --sidebar-text: white;
  --navbar-bg: #ffffff;
  
  /* Grises */
  --dark: #1a1a2e;
  --gray-dark: #2c3e50;
  --gray: #666;
  --gray-light: #999;
  --light: #f5f5f5;
  
  /* Estados */
  --success: #4caf50;
  --error: #f44336;
  --warning: #ff9800;
  --info: #2196f3;
  
  /* Sombras */
  --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
  --shadow-md: 0 4px 20px rgba(0, 0, 0, 0.1);
  --shadow-lg: 0 8px 30px rgba(0, 0, 0, 0.12);
  --shadow-xl: 0 20px 60px rgba(0, 0, 0, 0.15);
  
  /* Bordes */
  --radius-sm: 8px;
  --radius-md: 12px;
  --radius-lg: 16px;
  --radius-xl: 24px;
  --radius-full: 9999px;
}
```

### Tipografía

```css
body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 
    'Oxygen', 'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue',
    sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  line-height: 1.6;
}

h1 { font-size: 2.5rem; font-weight: 700; }
h2 { font-size: 2rem; font-weight: 700; }
h3 { font-size: 1.75rem; font-weight: 700; }
```

---

## 🔐 DISEÑO DEL LOGIN

### Estructura HTML

```html
<div class="login-page">
  <div class="login-background"></div>
  <div class="login-container-simple">
    <div class="login-card-wide">
      <!-- Panel Izquierdo: Branding -->
      <div class="login-branding-left">
        <div class="branding-content">
          <div class="brand-logo-main">
            <img src="{logoUrl}" alt="Logo" class="brand-logo-image" />
            <h1>{nombreSistema}</h1>
          </div>
          <p class="brand-description">Accede a tu aula virtual...</p>
          <div class="brand-features-list">
            <div class="feature-item">
              <span class="feature-icon">🎮</span>
              <span>Aula Virtual Gamificada</span>
            </div>
            <!-- Más features -->
          </div>
        </div>
      </div>
      
      <!-- Panel Derecho: Formulario -->
      <div class="login-form-right">
        <button class="btn-back">← Volver al Inicio</button>
        <div class="form-header">
          <h2>Iniciar Sesión</h2>
          <p>Ingresa tus credenciales para acceder</p>
        </div>
        <form class="login-form">
          <!-- Campos del formulario -->
        </form>
      </div>
    </div>
  </div>
</div>
```

### Estilos CSS - Login

#### Contenedor Principal

```css
.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
}

.login-background {
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, var(--primary-color, #667eea) 0%, var(--secondary-color, #764ba2) 100%);
  z-index: 0;
}

.login-container-simple {
  position: relative;
  z-index: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  padding: 3rem 2rem;
}
```

#### Tarjeta de Login (Dos Paneles)

```css
.login-card-wide {
  background: white;
  border-radius: 24px;
  box-shadow: 0 30px 100px rgba(0, 0, 0, 0.4);
  max-width: 1000px;
  width: 100%;
  display: grid;
  grid-template-columns: 1.1fr 1fr; /* Panel izquierdo ligeramente más ancho */
  overflow: hidden;
  min-height: 420px;
  animation: fadeInUp 0.6s ease;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
```

#### Panel Izquierdo (Branding)

```css
.login-branding-left {
  background: linear-gradient(135deg, var(--primary-color, #667eea) 0%, var(--secondary-color, #764ba2) 100%);
  color: white;
  padding: 2rem 2rem;
  display: flex;
  align-items: center;
  position: relative;
  overflow-x: hidden;
  overflow-y: auto;
}

.login-branding-left::before {
  content: '';
  position: absolute;
  inset: 0;
  background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>');
  opacity: 0.3;
}

.branding-content {
  position: relative;
  z-index: 1;
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.brand-logo-main {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.brand-logo-image {
  height: 50px;
  width: auto;
  object-fit: contain;
}

.brand-logo-main h1 {
  font-size: 1.75rem;
  font-weight: 800;
  margin: 0;
  color: white;
}

.brand-description {
  font-size: 0.95rem;
  line-height: 1.5;
  opacity: 0.95;
  margin-bottom: 1.5rem;
  text-align: center;
}

.brand-features-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  width: 100%;
}

.feature-item {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  padding: 1rem 1.25rem;
  background: rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(10px);
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.3);
  font-size: 0.95rem;
  color: #1a1a2e;
  font-weight: 600;
  min-height: 70px;
  word-wrap: break-word;
  text-shadow: 0 1px 2px rgba(255, 255, 255, 0.5);
}

.feature-item .feature-icon {
  font-size: 2.5rem;
  flex-shrink: 0;
  width: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
}
```

#### Panel Derecho (Formulario)

```css
.login-form-right {
  padding: 2rem 2rem;
  background: white;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.btn-back {
  background: transparent;
  border: none;
  color: #666;
  font-size: 0.85rem;
  cursor: pointer;
  margin-bottom: 1.5rem;
  transition: color 0.3s;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.25rem;
}

.btn-back:hover {
  color: var(--primary);
}

.form-header {
  margin-bottom: 1.25rem;
  text-align: left;
}

.form-header h2 {
  font-size: 1.5rem;
  font-weight: 800;
  color: var(--dark);
  margin-bottom: 0.4rem;
}

.form-header p {
  color: #666;
  font-size: 0.85rem;
}
```

#### Campos de Formulario

```css
.login-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.form-group label {
  font-weight: 600;
  color: var(--dark);
  font-size: 0.95rem;
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 1rem;
  font-size: 1.25rem;
  color: #999;
  pointer-events: none;
}

.input-wrapper input {
  width: 100%;
  padding: 1rem 1rem 1rem 3rem;
  border: 2px solid #e0e0e0;
  border-radius: 12px;
  font-size: 1rem;
  transition: all 0.3s;
  outline: none;
}

.input-wrapper input:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 4px rgba(25, 118, 210, 0.1);
}

.toggle-password {
  position: absolute;
  right: 1rem;
  background: transparent;
  border: none;
  font-size: 1.25rem;
  cursor: pointer;
  padding: 0.5rem;
  transition: opacity 0.3s;
}
```

#### Botón de Envío

```css
.btn-submit {
  width: 100%;
  padding: 1rem;
  background: linear-gradient(135deg, var(--primary-color, #667eea) 0%, var(--secondary-color, #764ba2) 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 1.05rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  position: relative;
  overflow: hidden;
}

.btn-submit::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, var(--secondary-color, #764ba2) 0%, var(--primary-color, #667eea) 100%);
  opacity: 0;
  transition: opacity 0.3s;
}

.btn-submit:hover::before {
  opacity: 1;
}

.btn-submit span,
.btn-submit > * {
  position: relative;
  z-index: 1;
}

.btn-submit:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.spinner {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
```

#### Responsive - Login

```css
@media (max-width: 900px) {
  .login-card-wide {
    grid-template-columns: 1fr;
    max-width: 500px;
  }
  
  .login-branding-left {
    display: none;
  }
  
  .login-form-right {
    padding: 3rem 2.5rem;
  }
  
  .form-header {
    text-align: center;
  }
}

@media (max-width: 640px) {
  .login-form-right {
    padding: 2rem 1.5rem;
  }
  
  .form-header h2 {
    font-size: 1.4rem;
  }
}
```

---

## 📊 DISEÑO DEL DASHBOARD

### Estructura HTML

```html
<div class="dashboard-layout">
  <!-- Overlay para móvil -->
  <div class="dashboard-overlay"></div>
  
  <!-- Sidebar -->
  <aside class="dashboard-sidebar">
    <!-- Logo y navegación -->
  </aside>
  
  <!-- Contenido principal -->
  <div class="dashboard-content">
    <header class="dashboard-header">
      <!-- Header con usuario y acciones -->
    </header>
    <main class="dashboard-main">
      <!-- Contenido del dashboard -->
    </main>
  </div>
</div>
```

### Sidebar

#### Estilos CSS - Sidebar

```css
.dashboard-sidebar {
  width: 280px;
  background: var(--sidebar-bg, #667eea);
  color: var(--sidebar-text, white);
  display: flex;
  flex-direction: column;
  position: fixed;
  left: 0;
  top: 0;
  bottom: 0;
  z-index: 999;
  box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease;
}

.sidebar-header {
  padding: 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.sidebar-logo {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.sidebar-logo-image {
  height: 32px;
  width: auto;
  object-fit: contain;
}

.logo-text {
  font-size: 1.25rem;
  font-weight: 800;
  letter-spacing: 0.5px;
  color: var(--sidebar-text, white);
  text-transform: capitalize;
}
```

#### Navegación del Sidebar

```css
.sidebar-nav {
  flex: 1;
  overflow-y: auto;
  padding: 1rem 0;
}

.sidebar-item {
  margin-bottom: 0.25rem;
}

.sidebar-link {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.875rem 1.5rem;
  color: var(--sidebar-text, white);
  opacity: 0.95;
  text-decoration: none;
  transition: all 0.2s;
  position: relative;
}

.sidebar-link:hover {
  background: rgba(255, 255, 255, 0.12);
  color: var(--sidebar-text, white);
  opacity: 1;
}

.sidebar-link.active {
  background: rgba(255, 255, 255, 0.2) !important;
  color: var(--sidebar-text, white) !important;
  opacity: 1 !important;
  font-weight: 600;
  border-left: 3px solid var(--sidebar-text, white);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.sidebar-icon {
  font-size: 1.25rem;
  width: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
  text-shadow: 0 1px 3px rgba(0, 0, 0, 0.4);
  transition: all 0.2s;
}

.sidebar-label {
  font-size: 0.95rem;
  font-weight: 500;
  text-transform: capitalize;
  letter-spacing: 0.3px;
}
```

#### Submenús del Sidebar

```css
.sidebar-category {
  cursor: pointer;
  font-weight: 600;
  font-size: 0.875rem;
  text-transform: capitalize;
  letter-spacing: 0.5px;
  color: var(--sidebar-text, white);
  opacity: 0.9;
  margin-top: 1rem;
  position: relative;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.875rem 1.5rem;
  transition: all 0.2s;
}

.submenu-arrow {
  margin-left: auto;
  font-size: 0.75rem;
  transition: transform 0.3s ease;
  color: var(--sidebar-text, white);
  opacity: 0.85;
}

.submenu-arrow.open {
  transform: rotate(-180deg);
}

.sidebar-submenu {
  margin-left: 3rem;
  margin-top: 0.25rem;
  margin-bottom: 0.5rem;
  animation: slideDown 0.3s ease;
  overflow: hidden;
}

@keyframes slideDown {
  from {
    opacity: 0;
    max-height: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    max-height: 500px;
    transform: translateY(0);
  }
}

.sidebar-sublink {
  display: block;
  padding: 0.625rem 1.5rem;
  color: var(--sidebar-text, white);
  opacity: 0.9;
  text-decoration: none;
  font-size: 0.9rem;
  transition: all 0.2s;
  border-left: 2px solid transparent;
}

.sidebar-sublink:hover {
  color: var(--sidebar-text, white);
  opacity: 1;
  background: rgba(255, 255, 255, 0.1);
  border-left-color: var(--sidebar-text, white);
  border-left-width: 3px;
}

.sidebar-sublink.active {
  color: var(--sidebar-text, white) !important;
  opacity: 1 !important;
  background: rgba(255, 255, 255, 0.25) !important;
  font-weight: 600;
  border-left: 3px solid var(--sidebar-text, white) !important;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
}
```

### Header

#### Estilos CSS - Header

```css
.dashboard-header {
  background: var(--navbar-bg, #ffffff);
  border-bottom: 1px solid #e5e7eb;
  padding: 1rem 2rem;
  display: flex;
  align-items: center;
  gap: 1.5rem;
  position: sticky;
  top: 0;
  z-index: 100;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  justify-content: space-between;
}

.header-menu-btn {
  display: none; /* Visible solo en móvil */
  background: transparent;
  border: none;
  padding: 0.5rem;
  cursor: pointer;
  border-radius: 8px;
  transition: background 0.2s;
}

.header-left {
  display: flex;
  align-items: center;
  flex: 1;
}

.header-user {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.5rem 0.75rem;
  border-radius: 10px;
  cursor: pointer;
  transition: background 0.2s;
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--primary-color, #667eea) 0%, var(--secondary-color, #764ba2) 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 700;
  font-size: 1rem;
}

.user-info {
  display: flex;
  flex-direction: column;
}

.user-name {
  font-size: 0.9rem;
  font-weight: 600;
  color: #1f2937;
  line-height: 1.2;
}

.user-role {
  font-size: 0.75rem;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.header-btn {
  position: relative;
  background: transparent;
  border: none;
  padding: 0.625rem;
  cursor: pointer;
  border-radius: 8px;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.header-btn:hover {
  background: #f3f4f6;
}

.notification-badge {
  position: absolute;
  top: 4px;
  right: 4px;
  background: #ef4444;
  color: white;
  font-size: 0.65rem;
  font-weight: 700;
  padding: 0.125rem 0.375rem;
  border-radius: 10px;
  min-width: 18px;
  text-align: center;
}
```

### Dashboard Layout

#### Estilos CSS - Layout

```css
.dashboard-layout {
  display: flex;
  min-height: 100vh;
  background: #f5f6fa;
}

.dashboard-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 0;
  transition: margin-left 0.3s ease;
  margin-left: 280px; /* Ancho del sidebar */
}

.dashboard-main {
  flex: 1;
  padding: 2rem;
  overflow-y: auto;
}

.dashboard-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 998;
  animation: fadeIn 0.3s ease;
}
```

### Dashboard Admin - Cards y Estadísticas

#### Estilos CSS - Dashboard Cards

```css
.dashboard-container {
  max-width: 1400px;
  margin: 0 auto;
}

.dashboard-welcome {
  margin-bottom: 2rem;
}

.dashboard-welcome h1 {
  font-size: 2rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 0.5rem;
}

.dashboard-welcome p {
  font-size: 1rem;
  color: #6b7280;
}

/* Grid de estadísticas */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  display: flex;
  gap: 1rem;
  transition: all 0.3s;
  border-left: 4px solid var(--card-color);
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
}

.stat-icon {
  font-size: 3rem;
  line-height: 1;
}

.stat-content {
  flex: 1;
}

.stat-title {
  font-size: 0.875rem;
  font-weight: 500;
  color: #6b7280;
  margin: 0 0 0.5rem 0;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.stat-value {
  font-size: 2rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 0.5rem;
}

/* Grid principal */
.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 1.5rem;
  margin-bottom: 1.5rem;
}

.dashboard-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.card-header {
  padding: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
}

.card-header h2 {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
}

.card-body {
  padding: 1.5rem;
}
```

#### Actividad Reciente

```css
.activity-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.activity-item {
  display: flex;
  gap: 1rem;
  padding: 1rem;
  background: #f9fafb;
  border-radius: 12px;
  transition: all 0.2s;
}

.activity-item:hover {
  background: #f3f4f6;
  transform: translateX(4px);
}

.activity-icon {
  font-size: 1.5rem;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: white;
  border-radius: 10px;
  flex-shrink: 0;
}

.activity-text {
  font-size: 0.9rem;
  color: #374151;
  margin: 0 0 0.25rem 0;
}

.activity-time {
  font-size: 0.75rem;
  color: #9ca3af;
}
```

#### Accesos Rápidos

```css
.quick-access-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.quick-access-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
  background: linear-gradient(135deg, var(--primary-color, #667eea) 0%, var(--secondary-color, #764ba2) 100%);
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s;
  text-align: center;
  gap: 0.5rem;
}

.quick-access-item:hover {
  transform: translateY(-4px) scale(1.02);
  box-shadow: 0 12px 24px rgba(102, 126, 234, 0.3);
  background: linear-gradient(135deg, var(--secondary-color, #764ba2) 0%, var(--primary-color, #667eea) 100%);
}

.quick-icon {
  font-size: 2.5rem;
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
}

.quick-title {
  font-size: 0.9rem;
  font-weight: 600;
  color: white;
}
```

#### Responsive - Dashboard

```css
@media (max-width: 1024px) {
  .dashboard-overlay {
    display: block;
  }
  
  .dashboard-content {
    margin-left: 0;
  }
  
  .dashboard-sidebar {
    transform: translateX(-100%);
  }
  
  .dashboard-sidebar.open {
    transform: translateX(0);
  }
  
  .header-menu-btn {
    display: block;
  }
  
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
  }
  
  .dashboard-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
}

@media (max-width: 640px) {
  .dashboard-main {
    padding: 1rem;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .user-info {
    display: none;
  }
  
  .quick-access-grid {
    grid-template-columns: 1fr;
  }
}
```

---

## 🎓 DISEÑO DEL AULA VIRTUAL

### Estructura HTML

```html
<div class="aula-container">
  <!-- Sidebar izquierdo con asignaciones -->
  <div class="aula-sidebar">
    <div class="aula-sidebar-header">
      <h2>Asignaciones</h2>
      <input type="text" class="aula-search" placeholder="Buscar..." />
      <select class="aula-filter">
        <option>Todos los grados</option>
      </select>
    </div>
    <div class="aula-sidebar-lista">
      <!-- Lista de asignaciones -->
    </div>
  </div>
  
  <!-- Contenido principal -->
  <div class="aula-content">
    <!-- Panel principal con mundos, temas, tareas, etc. -->
  </div>
</div>
```

### Estilos CSS - Aula Virtual

#### Contenedor Principal

```css
.aula-container {
  display: flex;
  gap: 1.5rem;
  padding: 2rem 0;
  min-height: calc(100vh - 140px);
}
```

#### Sidebar de Asignaciones

```css
.aula-sidebar {
  width: 320px;
  background: #ffffff;
  border-radius: 18px;
  box-shadow: 0 18px 42px rgba(148, 163, 184, 0.22);
  display: flex;
  flex-direction: column;
  align-self: flex-start;
}

.aula-sidebar-header {
  padding: 1.5rem 1.75rem 1rem;
  border-bottom: 1px solid rgba(229, 231, 235, 0.8);
}

.aula-sidebar-header h2 {
  font-size: 1.25rem;
  font-weight: 700;
  margin-bottom: 0.35rem;
  color: #1f2937;
}

.aula-search,
.aula-filter {
  width: 100%;
  margin-bottom: 0.7rem;
  padding: 0.65rem 0.85rem;
  border-radius: 12px;
  border: 2px solid #e5e7eb;
  background: #ffffff;
  font-size: 0.95rem;
  transition: border 0.2s, box-shadow 0.2s;
}

.aula-search:focus,
.aula-filter:focus {
  outline: none;
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
}

.aula-sidebar-lista {
  flex: 1;
  overflow-y: auto;
  padding: 0.75rem 1.25rem 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  min-height: 0;
}

.aula-asignacion {
  background: #f9fafb;
  border-radius: 14px;
  border: 2px solid transparent;
  padding: 0.75rem 0.9rem;
  text-align: left;
  cursor: pointer;
  transition: border 0.2s, background 0.2s, transform 0.2s;
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.aula-asignacion:hover {
  border-color: #c7d2fe;
  background: #eef2ff;
  transform: translateY(-1px);
}

.aula-asignacion.active {
  border-color: #6366f1;
  background: #eef2ff;
}

.aula-asignacion-curso {
  font-weight: 700;
  color: #1d4ed8;
}

.aula-asignacion-detalle {
  display: flex;
  gap: 0.6rem;
  font-size: 0.85rem;
  color: #475569;
}
```

#### Panel Principal

```css
.aula-content {
  flex: 1;
  min-height: 100%;
  background: transparent;
}

.aula-panel {
  background: #ffffff;
  border-radius: 20px;
  box-shadow: 0 18px 44px rgba(99, 102, 241, 0.18);
  padding: 2rem;
  display: flex;
  flex-direction: column;
  gap: 1.75rem;
}

.aula-panel-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1.5rem;
}

.aula-panel-header h1 {
  margin: 0;
  font-size: 1.8rem;
  font-weight: 700;
  color: #111827;
}

.aula-panel-header p {
  margin: 0.4rem 0 0;
  color: #6b7280;
  font-size: 0.95rem;
}

.aula-panel-actions {
  display: flex;
  gap: 0.75rem;
}

.btn-outline {
  background: transparent;
  border: 2px solid #6366f1;
  color: #4f46e5;
  padding: 0.55rem 1.2rem;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s, color 0.2s, transform 0.2s;
}

.btn-outline:hover {
  background: #eef2ff;
  color: #4f46e5;
  transform: translateY(-1px);
}
```

#### Mundos (Bimestres)

```css
.aula-mundos {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.aula-mundos h2 {
  margin: 0;
  font-size: 1.3rem;
  font-weight: 700;
  color: #1f2937;
}

.aula-mundos-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
  gap: 1rem;
}

.mundo-card {
  border: 1px solid rgba(99, 102, 241, 0.18);
  background: linear-gradient(145deg, #eef2ff, #ffffff);
  border-radius: 16px;
  padding: 1rem 1.2rem;
  display: flex;
  flex-direction: column;
  gap: 0.85rem;
  box-shadow: 0 8px 26px rgba(99, 102, 241, 0.16);
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s, border 0.2s;
}

.mundo-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 32px rgba(79, 70, 229, 0.22);
}

.mundo-card.active {
  border-color: #4f46e5;
  box-shadow: 0 14px 36px rgba(79, 70, 229, 0.25);
}

.mundo-numero {
  font-weight: 700;
  color: #4338ca;
  font-size: 0.95rem;
}

.mundo-objetivo {
  font-size: 0.85rem;
  color: #475569;
}

.mundo-indicadores {
  display: flex;
  justify-content: space-between;
  gap: 0.5rem;
}

.mundo-indicadores strong {
  font-size: 1.1rem;
  color: #111827;
}

.mundo-indicadores span {
  font-size: 0.75rem;
  color: #6b7280;
}
```

#### Secciones (Temas, Tareas, Exámenes)

```css
.aula-seccion {
  background: #f9fafb;
  border-radius: 18px;
  padding: 1.35rem;
  border: 1px solid rgba(226, 232, 240, 0.65);
}

.aula-seccion + .aula-seccion {
  margin-top: 1rem;
}

.aula-seccion-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.9rem;
}

.aula-seccion-header h2,
.aula-seccion-header h3 {
  margin: 0;
  color: #1f2937;
  font-size: 1.15rem;
  font-weight: 700;
}

.badge-soft {
  background: #eef2ff;
  color: #4338ca;
  border-radius: 999px;
  padding: 0.35rem 0.8rem;
  font-size: 0.8rem;
  font-weight: 600;
}

.aula-lista {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.aula-item {
  background: #ffffff;
  border-radius: 14px;
  padding: 0.8rem 1rem;
  border: 1px solid rgba(229, 231, 235, 0.7);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.aula-item-info {
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
}

.aula-item-info strong {
  color: #1f2937;
}

.aula-item-info p {
  margin: 0;
  font-size: 0.85rem;
  color: #6b7280;
}

.badge-preguntas {
  background: #eef2ff;
  color: #4338ca;
  padding: 0.375rem 0.75rem;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 600;
  white-space: nowrap;
}

.aula-item-actions {
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.aula-item-link {
  color: #4f46e5;
  font-weight: 600;
  text-decoration: none;
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1.2rem;
  padding: 0.25rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.2s, opacity 0.2s;
}

.aula-item-link:hover {
  transform: scale(1.1);
  opacity: 0.8;
}
```

#### Grid de Secciones

```css
.aula-seccion-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 1rem;
}
```

#### Formularios del Aula

```css
.aula-form label {
  display: block;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.35rem;
  font-size: 0.9rem;
}

.aula-input,
.aula-select,
.aula-textarea {
  width: 100%;
  padding: 0.75rem 0.9rem;
  border: 2px solid #e5e7eb;
  border-radius: 10px;
  font-size: 1rem;
  margin-bottom: 1rem;
  transition: border 0.2s, box-shadow 0.2s;
}

.aula-input:focus,
.aula-select:focus,
.aula-textarea:focus {
  outline: none;
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
}
```

#### Responsive - Aula Virtual

```css
@media (max-width: 1100px) {
  .aula-container {
    flex-direction: column;
  }

  .aula-sidebar {
    width: 100%;
    max-height: 400px;
    height: auto !important;
  }

  .aula-sidebar-lista {
    max-height: 300px !important;
    overflow-y: auto;
  }
}

@media (max-width: 768px) {
  .aula-panel {
    padding: 1.5rem;
  }

  .aula-panel-header {
    flex-direction: column;
    align-items: stretch;
  }

  .aula-panel-actions {
    justify-content: flex-end;
  }

  .aula-mundos-grid,
  .aula-seccion-grid {
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  }
}
```

---

## 🔧 COMPONENTES COMPARTIDOS

### Botones

```css
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border-radius: 12px;
  font-weight: 600;
  transition: all 0.3s;
  border: none;
  cursor: pointer;
}

.btn-primary {
  background: var(--gradient-primary);
  color: white;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
}
```

### Scrollbar Personalizado

```css
::-webkit-scrollbar {
  width: 10px;
  height: 10px;
}

::-webkit-scrollbar-track {
  background: var(--light);
}

::-webkit-scrollbar-thumb {
  background: var(--gray-light);
  border-radius: 9999px;
}

::-webkit-scrollbar-thumb:hover {
  background: var(--gray);
}
```

### Estados Vacíos

```css
.empty-state {
  text-align: center;
  padding: 2rem;
  color: #6b7280;
}

.empty-state p {
  margin: 0;
  font-size: 0.9rem;
}
```

### Loading Spinner

```css
.spinner-large {
  width: 48px;
  height: 48px;
  border: 4px solid rgba(102, 126, 234, 0.1);
  border-top-color: #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
```

---

## 📱 RESPONSIVE BREAKPOINTS

```css
/* Desktop */
@media (min-width: 1025px) {
  /* Estilos para desktop */
}

/* Tablets */
@media (max-width: 1024px) {
  /* Estilos para tablets */
}

/* Móviles */
@media (max-width: 768px) {
  /* Estilos para móviles */
}

/* Móviles pequeños */
@media (max-width: 640px) {
  /* Estilos para móviles pequeños */
}
```

---

## 🎨 COLORES DE REFERENCIA

### Paleta Principal
- **Primary**: `#667eea` (Azul púrpura)
- **Secondary**: `#764ba2` (Púrpura oscuro)
- **Success**: `#43e97b` (Verde)
- **Info**: `#4facfe` (Azul claro)
- **Warning**: `#ff9800` (Naranja)
- **Error**: `#f44336` (Rojo)

### Grises
- **Dark**: `#1a1a2e`
- **Gray Dark**: `#2c3e50`
- **Gray**: `#666`
- **Gray Light**: `#999`
- **Light**: `#f5f5f5`

---

## 📝 NOTAS IMPORTANTES

1. **Colores Personalizables**: El sistema usa variables CSS que pueden configurarse desde la base de datos (configuraciones)
2. **Gradientes**: Se utilizan gradientes lineales de 135deg para un efecto moderno
3. **Sombras**: Se usan múltiples niveles de sombra para profundidad
4. **Border Radius**: Bordes redondeados grandes (12px-24px) para un diseño moderno
5. **Transiciones**: Todas las interacciones tienen transiciones suaves (0.2s-0.3s)
6. **Iconos**: Se usan emojis directamente en el HTML (no iconos SVG)
7. **Tipografía**: Fuente Inter con antialiasing para mejor legibilidad
8. **Espaciado**: Sistema consistente de espaciado basado en rem

---

**¡Listo para replicar el diseño en otra aplicación!** 🚀

