# Configuración de Subida Automática FTP/SFTP

## Descripción

Este sistema sube automáticamente todos los archivos (fotos, PDFs, documentos) al servidor PHP vía SFTP para que ambos sistemas (React/Node.js y PHP) puedan acceder a los mismos archivos.

## Variables de Entorno

Agregar al archivo `backend/.env`:

```env
# Configuración FTP/SFTP para subida automática al servidor PHP
FTP_HOST=89.117.52.9
FTP_USER=vanguard
FTP_PASSWORD=CtxADB8q0SaVYox
FTP_PORT=22
FTP_PROTOCOL=sftp
FTP_BASE_PATH=/home/vanguard/nuevo.vanguardschools.edu.pe
```

### Explicación de Variables

- **FTP_HOST**: IP o dominio del servidor PHP (VPS)
- **FTP_USER**: Usuario SSH/SFTP
- **FTP_PASSWORD**: Contraseña SSH/SFTP
- **FTP_PORT**: Puerto (22 para SFTP, 21 para FTP)
- **FTP_PROTOCOL**: Protocolo a usar ('sftp' o 'ftp')
- **FTP_BASE_PATH**: Ruta base donde está instalado el sistema PHP en el servidor
  - Ejemplos comunes:
    - `/var/www/html` (Apache estándar)
    - `/home/vanguard/public_html` (cPanel)
    - `/var/www/vhosts/dominio.com/httpdocs` (Plesk)

## Cómo Funciona

1. Usuario sube archivo desde React/Node.js
2. Node.js guarda temporalmente en Hostinger
3. Node.js sube automáticamente al servidor PHP vía SFTP
4. Node.js guarda solo el nombre del archivo en MySQL (como PHP)
5. Node.js elimina el archivo temporal de Hostinger
6. Ambos sistemas pueden acceder al mismo archivo

## Tipos de Archivo Soportados

- **personal_foto**: Fotos de perfil de personal/docentes → `/Static/Image/Fotos/`
- **alumno_foto**: Fotos de perfil de alumnos → `/Static/Image/Fotos/`
- **archivo_pdf**: PDFs de temas, exámenes → `/Static/Archivos/`
- **documento**: Documentos de alumnos → `/Static/Documentos/`
- **fondo**: Fondos/imágenes de configuración → `/Static/Image/Fondos/`
- **avatar**: Avatares → `/Static/Image/Avatars/`
- **publicacion**: Archivos de publicaciones → `/Static/Archivos/`
- **tarea**: Archivos de tareas → `/Static/Archivos/`
- **examen**: Archivos de exámenes → `/Static/Archivos/`
- **tema**: Archivos de temas → `/Static/Archivos/`

## Notas Importantes

- La ruta `FTP_BASE_PATH` debe apuntar a la raíz del sistema PHP
- Los archivos se guardan con el mismo nombre que genera multer
- En MySQL solo se guarda el nombre del archivo (no la ruta completa)
- El sistema PHP accede a los archivos como: `/Static/Image/Fotos/nombre-archivo.jpg`

