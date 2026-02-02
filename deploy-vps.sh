#!/bin/bash

# Script de despliegue para VPS
# VPS: 89.117.52.9
# Usuario: vanguard
# Carpeta destino: intranet206

set -e

echo "🚀 Iniciando despliegue en VPS..."

# Variables
VPS_IP="89.117.52.9"
VPS_USER="vanguard"
VPS_PASSWORD="CtxADB8q0SaVYox"
VPS_FOLDER="intranet206"
GITHUB_REPO="https://github.com/LiamFranKi/intranet-2026.git"

echo "📦 Clonando repositorio desde GitHub..."
sshpass -p "$VPS_PASSWORD" ssh -o StrictHostKeyChecking=no "$VPS_USER@$VPS_IP" << 'ENDSSH'
    cd ~
    
    # Crear carpeta si no existe
    if [ ! -d "intranet206" ]; then
        mkdir -p intranet206
    fi
    
    cd intranet206
    
    # Clonar o actualizar repositorio
    if [ -d ".git" ]; then
        echo "📥 Actualizando repositorio existente..."
        git pull origin main
    else
        echo "📥 Clonando repositorio..."
        git clone https://github.com/LiamFranKi/intranet-2026.git .
    fi
    
    echo "📦 Instalando dependencias del backend..."
    cd backend
    npm install --production
    
    echo "📦 Instalando dependencias del frontend..."
    cd ../frontend
    npm install
    
    echo "🏗️ Compilando frontend..."
    npm run build
    
    echo "✅ Instalación completada"
ENDSSH

echo ""
echo "✅ Despliegue completado!"
echo ""
echo "📝 Próximos pasos:"
echo "1. Conectarse al VPS: ssh vanguard@89.117.52.9"
echo "2. Configurar variables de entorno en backend/.env"
echo "3. Configurar variables de entorno en frontend/.env"
echo "4. Iniciar el backend con PM2: pm2 start ecosystem.config.js"
echo "5. Configurar Nginx para servir el frontend"
echo ""
echo "📖 Ver guía completa en: DEPLOYMENT_PRODUCTION.md"

