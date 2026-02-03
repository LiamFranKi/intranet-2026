# 💾 Guía para Crear Backup/Commit en el VPS

## 📋 Objetivo
Crear un commit de respaldo del estado actual del sistema que está funcionando correctamente en el VPS, y sincronizarlo con GitHub.

## 🔧 Pasos a Ejecutar en el VPS

### Paso 1: Conectarse al VPS
```bash
ssh vanguard@89.117.52.9
```

### Paso 2: Navegar al directorio del proyecto
```bash
cd ~/intranet2026
```

### Paso 3: Verificar estado del repositorio
```bash
# Ver el estado actual
git status

# Ver si hay cambios locales
git diff

# Ver los últimos commits
git log --oneline -5
```

### Paso 4: Verificar configuración de Git
```bash
# Verificar remoto
git remote -v

# Verificar usuario de git (si es necesario configurarlo)
git config user.name
git config user.email

# Si no está configurado, configurarlo:
# git config user.name "VPS Backup"
# git config user.email "vps@vanguardschools.edu.pe"
```

### Paso 5: Sincronizar con GitHub (Traer cambios remotos)
```bash
# Traer cambios de GitHub sin aplicar
git fetch origin

# Ver diferencias entre local y remoto
git log HEAD..origin/main --oneline

# Si hay cambios remotos, hacer merge
git pull origin main
```

### Paso 6: Crear commit de respaldo del estado actual
```bash
# Opción A: Si hay cambios locales que no están en GitHub
# (archivos modificados, .env local, etc.)
git add .
git commit -m "💾 Backup VPS - Estado funcionando correctamente - $(date +%Y-%m-%d)"

# Opción B: Si todo está sincronizado, crear un commit vacío como checkpoint
git commit --allow-empty -m "💾 Checkpoint VPS - Estado funcionando correctamente - $(date +%Y-%m-%d)"
```

### Paso 7: Subir cambios a GitHub
```bash
# Subir el commit de respaldo
git push origin main

# Verificar que se subió correctamente
git log --oneline -3
```

### Paso 8: Verificar que todo está sincronizado
```bash
# Verificar que local y remoto están sincronizados
git status

# Debería decir: "Your branch is up to date with 'origin/main'"
```

## 🔄 Comandos Rápidos (Todo en uno)

Si quieres hacer todo de una vez, ejecuta estos comandos en secuencia:

```bash
cd ~/intranet2026
git status
git fetch origin
git pull origin main
git add .
git commit -m "💾 Backup VPS - Estado funcionando correctamente - $(date +%Y-%m-%d)" || git commit --allow-empty -m "💾 Checkpoint VPS - Estado funcionando correctamente - $(date +%Y-%m-%d)"
git push origin main
git log --oneline -3
```

## 📝 Notas Importantes

1. **Archivos .env**: Los archivos `.env` normalmente están en `.gitignore`, así que no se subirán a GitHub (esto es correcto por seguridad).

2. **Si hay conflictos**: Si `git pull` muestra conflictos, puedes:
   ```bash
   # Ver los conflictos
   git status
   
   # Resolver manualmente o
   # Guardar cambios locales temporalmente
   git stash
   git pull origin main
   git stash pop
   ```

3. **Si quieres hacer un tag (versión)**: Después del commit, puedes crear un tag:
   ```bash
   git tag -a v1.0.0-backup -m "Backup del estado funcionando correctamente"
   git push origin v1.0.0-backup
   ```

4. **Verificar que el sistema sigue funcionando**: Después del commit, verifica que todo sigue funcionando:
   ```bash
   pm2 status
   pm2 logs intranet2026-backend --lines 5
   ```

## 🎯 Resultado Esperado

Al final deberías tener:
- ✅ Un commit en el VPS con el mensaje de backup
- ✅ El mismo commit en GitHub
- ✅ El sistema funcionando correctamente
- ✅ Un punto de retorno si algo sale mal

## 🔙 Cómo Volver a Este Estado (Si algo sale mal)

Si necesitas volver a este estado:

```bash
cd ~/intranet2026
git log --oneline -10  # Buscar el commit de backup
git reset --hard <hash-del-commit>  # Reemplazar <hash-del-commit> con el hash real
pm2 restart intranet2026-backend
```

O usando el tag (si creaste uno):
```bash
cd ~/intranet2026
git checkout v1.0.0-backup
pm2 restart intranet2026-backend
```

