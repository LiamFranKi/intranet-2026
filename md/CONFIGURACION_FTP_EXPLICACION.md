# Configuración FTP/SFTP - Explicación Completa

## ¿Puedo probarlo localmente?

**NO, en desarrollo local NO funcionará la subida automática al servidor PHP.**

### ¿Por qué?
- En desarrollo local, estás en tu computadora
- El servidor PHP está en otro servidor (VPS: 89.117.52.9)
- La subida automática solo funcionará cuando el sistema Node.js esté en el servidor de Hostinger

### ¿Qué pasa en desarrollo local?
- Las fotos se guardarán **solo localmente** en `backend/uploads/personal/`
- **NO se subirán** al servidor PHP
- Esto es normal y esperado

### ¿Cuándo funcionará la subida automática?
- **Solo cuando subas el sistema a Hostinger** (producción)
- En ese momento, Node.js podrá conectarse vía SFTP al servidor PHP y subir los archivos

---

## ¿Qué es FTP_BASE_PATH?

`FTP_BASE_PATH` es la **ruta absoluta en el servidor PHP** donde está instalado tu sistema PHP.

### Ejemplos comunes:

#### Si tu sistema PHP está en:
```
/var/www/html/
├── index.php
├── Static/
│   ├── Image/
│   │   └── Fotos/
│   └── Archivos/
└── ...
```

Entonces `FTP_BASE_PATH = /var/www/html`

#### Si tu sistema PHP está en:
```
/home/vanguard/public_html/
├── index.php
├── Static/
│   ├── Image/
│   │   └── Fotos/
│   └── Archivos/
└── ...
```

Entonces `FTP_BASE_PATH = /home/vanguard/public_html`

### ¿Cómo saber cuál es la ruta correcta?

1. **Conectarte al servidor PHP vía SSH** (con las credenciales que me diste)
2. **Navegar hasta donde está tu sistema PHP**
3. **Ejecutar `pwd`** para ver la ruta actual
4. **Esa es tu `FTP_BASE_PATH`**

### Ejemplo de cómo encontrarlo:

```bash
# Conectarte al servidor
ssh vanguard@89.117.52.9

# Una vez conectado, navegar a tu sistema PHP
cd /var/www/html  # o donde esté tu sistema

# Ver la ruta actual
pwd

# Deberías ver algo como:
# /var/www/html
# o
# /home/vanguard/public_html
```

---

## Configuración en .env

Agregar estas líneas al archivo `backend/.env`:

```env
# ============================================
# CONFIGURACIÓN FTP/SFTP PARA SUBIDA AUTOMÁTICA
# ============================================
# Solo funcionará en producción (Hostinger)
# En desarrollo local, las fotos se guardan solo localmente

# IP o dominio del servidor PHP (VPS)
FTP_HOST=89.117.52.9

# Usuario SSH/SFTP del servidor PHP
FTP_USER=vanguard

# Contraseña SSH/SFTP del servidor PHP
FTP_PASSWORD=CtxADB8q0SaVYox

# Puerto SFTP (22 es el estándar)
FTP_PORT=22

# Protocolo a usar (sftp o ftp)
FTP_PROTOCOL=sftp

# RUTA BASE donde está instalado el sistema PHP en el servidor
# IMPORTANTE: Esta es la ruta ABSOLUTA en el servidor PHP
# Ejemplos comunes:
#   - /var/www/html (Apache estándar)
#   - /home/vanguard/public_html (cPanel)
#   - /var/www/vhosts/dominio.com/httpdocs (Plesk)
# 
# RUTA CONFIRMADA: /home/vanguard/nuevo.vanguardschools.edu.pe/
FTP_BASE_PATH=/home/vanguard/nuevo.vanguardschools.edu.pe
```

---

## Resumen

| Entorno | ¿Funciona la subida automática? | ¿Dónde se guardan las fotos? |
|---------|--------------------------------|------------------------------|
| **Desarrollo Local** | ❌ NO | Solo en `backend/uploads/personal/` (local) |
| **Producción (Hostinger)** | ✅ SÍ | En el servidor PHP vía SFTP |

---

## Próximos pasos

1. **En desarrollo local**: Trabaja normalmente, las fotos se guardan localmente
2. **Cuando subas a Hostinger**: 
   - Configura `FTP_BASE_PATH` con la ruta correcta
   - La subida automática funcionará
   - Ambos sistemas (PHP y Node.js) compartirán los mismos archivos

