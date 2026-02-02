#!/bin/bash

# Script de configuración inicial para VPS
# Ejecutar en el VPS después de clonar el repositorio

set -e

echo "🚀 Configurando VPS para intranet206..."

# Variables
PROJECT_DIR="$HOME/intranet206"
GITHUB_REPO="https://github.com/LiamFranKi/intranet-2026.git"

# Crear carpeta si no existe
if [ ! -d "$PROJECT_DIR" ]; then
    echo "📁 Creando carpeta $PROJECT_DIR..."
    mkdir -p "$PROJECT_DIR"
fi

cd "$PROJECT_DIR"

# Clonar o actualizar repositorio
if [ -d ".git" ]; then
    echo "📥 Actualizando repositorio existente..."
    git pull origin main
else
    echo "📥 Clonando repositorio desde GitHub..."
    git clone "$GITHUB_REPO" .
fi

# Verificar Node.js
echo "🔍 Verificando Node.js..."
if ! command -v node &> /dev/null; then
    echo "⚠️  Node.js no está instalado. Instalando Node.js 18..."
    curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
    sudo apt-get install -y nodejs
else
    echo "✅ Node.js ya está instalado: $(node --version)"
fi

# Instalar dependencias del backend
echo "📦 Instalando dependencias del backend..."
cd backend
npm install --production

# Instalar dependencias del frontend
echo "📦 Instalando dependencias del frontend..."
cd ../frontend
npm install

echo ""
echo "✅ Configuración inicial completada!"
echo ""
echo "📝 Próximos pasos:"
echo "1. Configurar variables de entorno en backend/.env"
echo "2. Configurar variables de entorno en frontend/.env"
echo "3. Compilar frontend: cd frontend && npm run build"
echo "4. Configurar PM2: pm2 start ecosystem.config.js"
echo ""
echo "📖 Ver guía completa: cat INSTRUCCIONES_DESPLIEGUE_VPS.md"

