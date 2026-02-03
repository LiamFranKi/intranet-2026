#!/bin/bash

# Script para crear backup/commit en el VPS
# Ejecutar desde: cd ~/intranet2026 && bash backup-vps.sh

set -e

echo "💾 Iniciando backup del estado actual del VPS..."
echo ""

# Colores para output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Verificar que estamos en el directorio correcto
if [ ! -d ".git" ]; then
    echo -e "${RED}❌ Error: No se encontró el repositorio git.${NC}"
    echo "Por favor, ejecuta este script desde: cd ~/intranet2026"
    exit 1
fi

echo -e "${GREEN}✅ Repositorio git encontrado${NC}"
echo ""

# Verificar estado actual
echo "📊 Verificando estado del repositorio..."
git status --short
echo ""

# Traer cambios remotos
echo "📥 Sincronizando con GitHub..."
git fetch origin
echo ""

# Verificar si hay cambios remotos
if git diff --quiet HEAD origin/main; then
    echo -e "${GREEN}✅ El repositorio local está sincronizado con GitHub${NC}"
else
    echo -e "${YELLOW}⚠️  Hay cambios remotos. Actualizando...${NC}"
    git pull origin main || {
        echo -e "${RED}❌ Error al hacer pull. Revisa los conflictos manualmente.${NC}"
        exit 1
    }
fi
echo ""

# Verificar si hay cambios locales
if git diff --quiet && git diff --cached --quiet; then
    echo -e "${YELLOW}ℹ️  No hay cambios locales. Creando commit vacío como checkpoint...${NC}"
    COMMIT_MSG="💾 Checkpoint VPS - Estado funcionando correctamente - $(date +%Y-%m-%d\ %H:%M:%S)"
    git commit --allow-empty -m "$COMMIT_MSG"
else
    echo -e "${GREEN}📝 Hay cambios locales. Creando commit con los cambios...${NC}"
    git add .
    COMMIT_MSG="💾 Backup VPS - Estado funcionando correctamente - $(date +%Y-%m-%d\ %H:%M:%S)"
    git commit -m "$COMMIT_MSG"
fi
echo ""

# Subir a GitHub
echo "🚀 Subiendo cambios a GitHub..."
git push origin main || {
    echo -e "${RED}❌ Error al hacer push. Verifica tus credenciales de git.${NC}"
    exit 1
}
echo ""

# Verificar estado final
echo "✅ Verificando estado final..."
git status
echo ""

# Mostrar últimos commits
echo "📜 Últimos 3 commits:"
git log --oneline -3
echo ""

echo -e "${GREEN}✅ Backup completado exitosamente!${NC}"
echo ""
echo "📋 Resumen:"
echo "  - Commit creado: $COMMIT_MSG"
echo "  - Sincronizado con GitHub"
echo "  - Estado del sistema: Funcionando correctamente"
echo ""
echo "💡 Para volver a este estado en el futuro:"
echo "   git log --oneline -10"
echo "   git reset --hard <hash-del-commit>"
echo ""

