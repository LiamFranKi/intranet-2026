# 🎨 CONFIGURACIÓN DE LOGO Y NOMBRE DE INTRANET

## 📋 CÓMO FUNCIONA

### 1. **Nombre de la Intranet**

El nombre se obtiene desde MySQL en el siguiente orden:

1. **Tabla `configuraciones`** (si existe):
   - Campo `nombre_empresa` → Se usa como `nombre_intranet`
   
2. **Tabla `colegios`** (fallback):
   - Campo `nombre` → Se usa como `nombre_intranet` si no hay `configuraciones`

**Query utilizado:**
```sql
SELECT nombre_empresa, logo, color_principal, color_secundario
FROM configuraciones 
WHERE colegio_id = ?
```

Si la tabla `configuraciones` no existe, se usa directamente:
```sql
SELECT nombre FROM colegios WHERE id = ?
```

---

### 2. **Logo del Colegio**

El logo se maneja de la siguiente manera:

#### Opción A: Logo en MySQL (Ruta/Nombre)
- Si en MySQL hay un campo `logo` con la ruta o nombre del archivo
- El sistema busca el archivo en: `backend/public/assets/logos/`
- Si existe localmente, se sirve desde ahí
- Si no existe localmente, se usa la ruta de MySQL (puede ser URL remota)

#### Opción B: Logo Local (Recomendado)
- Colocar el logo en: `backend/public/assets/logos/`
- Nombre sugerido: `logo-colegio-{id}.png` o `logo-default.png`
- El sistema lo servirá automáticamente en: `http://localhost:5000/assets/logos/logo-colegio-1.png`

---

## 📁 ESTRUCTURA DE CARPETAS

```
react-aula-virtual/
├── backend/
│   └── public/
│       └── assets/
│           └── logos/
│               ├── logo-default.png      (Logo por defecto)
│               ├── logo-colegio-1.png    (Logo del colegio 1)
│               └── logo-colegio-2.png    (Logo del colegio 2)
│
└── frontend/
    └── public/
        └── assets/
            └── logos/
                └── (logos copiados para PWA)
```

---

## 🔧 CONFIGURACIÓN PASO A PASO

### Paso 1: Colocar el Logo

1. **Obtener el logo del colegio** (desde el sistema PHP o diseñarlo)
2. **Colocar en la carpeta:**
   ```
   backend/public/assets/logos/logo-colegio-{id}.png
   ```
   Ejemplo: `logo-colegio-1.png` para colegio_id = 1

3. **Formato recomendado:**
   - PNG con transparencia
   - Tamaño: 200x200px o 300x300px
   - Fondo transparente

### Paso 2: Configurar en MySQL (Opcional)

Si quieres que el sistema lea el nombre del logo desde MySQL:

```sql
-- Si existe tabla configuraciones
UPDATE configuraciones 
SET logo = 'logo-colegio-1.png'
WHERE colegio_id = 1;

-- O crear registro si no existe
INSERT INTO configuraciones (colegio_id, nombre_empresa, logo)
VALUES (1, 'Nombre de la Intranet', 'logo-colegio-1.png');
```

### Paso 3: Usar en React

El logo y nombre se obtienen automáticamente mediante el contexto:

```jsx
import { useColegio } from '../context/ColegioContext';

function Header() {
  const { nombreIntranet, logo, colorPrincipal } = useColegio();

  return (
    <header>
      {logo && <img src={logo} alt={nombreIntranet} />}
      <h1>{nombreIntranet}</h1>
    </header>
  );
}
```

---

## 🎨 ADAPTACIÓN DE COLORES

### Opción 1: Colores desde MySQL

Si en la tabla `configuraciones` hay campos `color_principal` y `color_secundario`:

```sql
UPDATE configuraciones 
SET color_principal = '#1976d2',
    color_secundario = '#dc004e'
WHERE colegio_id = 1;
```

El sistema los usará automáticamente para el tema.

### Opción 2: Extraer Colores del Logo (Futuro)

Se puede implementar una librería como `color-thief` para extraer colores dominantes del logo automáticamente.

---

## 📡 ENDPOINTS DE LA API

### GET `/api/colegio/:colegioId`

Obtiene todos los datos del colegio:

```json
{
  "id": 1,
  "nombre": "Colegio Ejemplo",
  "nombre_intranet": "Intranet del Colegio",
  "anio_activo": 2025,
  "logo": "/assets/logos/logo-colegio-1.png",
  "color_principal": "#1976d2",
  "color_secundario": "#dc004e",
  "bloquear_deudores": "SI",
  "dias_tolerancia": 5
}
```

### GET `/api/colegio/:colegioId/nombre-intranet`

Obtiene solo el nombre de la intranet:

```json
{
  "nombre_intranet": "Intranet del Colegio",
  "nombre": "Colegio Ejemplo"
}
```

---

## ✅ CHECKLIST

- [ ] Crear carpeta `backend/public/assets/logos/`
- [ ] Colocar logo del colegio en la carpeta
- [ ] Verificar que el logo se sirve correctamente: `http://localhost:5000/assets/logos/logo-colegio-1.png`
- [ ] Configurar nombre en MySQL (tabla `configuraciones` o `colegios`)
- [ ] Probar endpoint: `GET /api/colegio/1`
- [ ] Verificar que React obtiene el nombre y logo correctamente

---

## 🔍 TROUBLESHOOTING

### El logo no se muestra

1. **Verificar que el archivo existe:**
   ```bash
   ls backend/public/assets/logos/
   ```

2. **Verificar que el servidor sirve archivos estáticos:**
   - El servidor debe tener: `app.use('/assets', express.static(...))`

3. **Verificar la ruta en el navegador:**
   - Abrir: `http://localhost:5000/assets/logos/logo-colegio-1.png`

### El nombre no se obtiene

1. **Verificar que existe el colegio en MySQL:**
   ```sql
   SELECT * FROM colegios WHERE id = 1;
   ```

2. **Verificar si existe tabla configuraciones:**
   ```sql
   SHOW TABLES LIKE 'configuraciones';
   ```

3. **Revisar logs del servidor** para ver errores

---

**El sistema está diseñado para ser flexible: funciona con o sin tabla `configuraciones`, y con logos locales o remotos.** 🎨

