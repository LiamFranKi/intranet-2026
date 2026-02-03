# 🔧 Solución: Directorio Nginx no existe

## Problema
El directorio `/etc/nginx/sites-available` no existe en tu sistema.

## Solución

### Paso 1: Verificar si Nginx está instalado

```bash
nginx -v
```

Si no está instalado, instálalo:

```bash
sudo apt-get update
sudo apt-get install nginx -y
```

### Paso 2: Verificar estructura de Nginx

```bash
# Ver qué directorios existen
ls -la /etc/nginx/
```

### Paso 3: Crear directorios si no existen

```bash
# Crear directorios necesarios
sudo mkdir -p /etc/nginx/sites-available
sudo mkdir -p /etc/nginx/sites-enabled

# Verificar que se crearon
ls -la /etc/nginx/
```

### Paso 4: Verificar configuración principal de Nginx

```bash
# Ver archivo de configuración principal
cat /etc/nginx/nginx.conf
```

Busca líneas que incluyan `sites-enabled` o `sites-available`. Si no están, necesitamos agregarlas.

### Paso 5: Si Nginx usa estructura diferente

Algunos sistemas usan `/etc/nginx/conf.d/` en lugar de `sites-available`. Verifica:

```bash
ls -la /etc/nginx/conf.d/
```

Si este directorio existe, podemos usar ese en su lugar.

