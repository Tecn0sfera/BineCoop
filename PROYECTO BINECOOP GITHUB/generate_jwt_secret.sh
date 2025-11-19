#!/bin/bash

# Script para generar un JWT_SECRET seguro y actualizar config.json
# Ejecutar desde el directorio raíz del proyecto

echo "================================================"
echo "GENERADOR DE JWT_SECRET"
echo "================================================"
echo ""

# Obtener el directorio raíz del script
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# Generar JWT_SECRET seguro (64 caracteres hexadecimales)
JWT_SECRET=$(openssl rand -hex 32)

echo ""
echo "✅ JWT_SECRET generado:"
echo "$JWT_SECRET"
echo ""

# Verificar si existe config.json
CONFIG_FILE="config.json"
if [ ! -f "$CONFIG_FILE" ]; then
    echo "⚠️  No se encontró config.json"
    echo ""
    echo "Por favor, crea config.json basado en config.example.json primero."
    echo "Luego ejecuta este script nuevamente para actualizar el JWT_SECRET."
    echo ""
    exit 1
fi

# Actualizar config.json automáticamente usando jq o python
echo ""
echo "Actualizando config.json automáticamente..."

if command -v jq &> /dev/null; then
    # Usar jq si está disponible
    jq ".htdocspanel.JWT_SECRET = \"$JWT_SECRET\"" "$CONFIG_FILE" > "${CONFIG_FILE}.tmp" && mv "${CONFIG_FILE}.tmp" "$CONFIG_FILE"
    if [ $? -eq 0 ]; then
        echo "✅ config.json actualizado exitosamente"
    else
        echo "❌ Error al actualizar config.json"
        echo ""
        echo "Por favor, actualiza manualmente en config.json:"
        echo "  \"JWT_SECRET\": \"$JWT_SECRET\""
    fi
elif command -v python3 &> /dev/null; then
    # Usar Python como alternativa
    python3 << EOF
import json
import sys

try:
    with open('$CONFIG_FILE', 'r', encoding='utf-8') as f:
        config = json.load(f)
    
    config['htdocspanel']['JWT_SECRET'] = '$JWT_SECRET'
    
    with open('$CONFIG_FILE', 'w', encoding='utf-8') as f:
        json.dump(config, f, indent=2, ensure_ascii=False)
    
    print("✅ config.json actualizado exitosamente")
    sys.exit(0)
except Exception as e:
    print(f"❌ Error al actualizar config.json: {e}")
    print("")
    print("Por favor, actualiza manualmente en config.json:")
    print(f'  "JWT_SECRET": "$JWT_SECRET"')
    sys.exit(1)
EOF
else
    echo "❌ Error: Se requiere jq o python3 para actualizar config.json"
    echo ""
    echo "Por favor, actualiza manualmente en config.json:"
    echo "  \"JWT_SECRET\": \"$JWT_SECRET\""
    echo ""
    echo "O instala jq:"
    echo "  Ubuntu/Debian: sudo apt-get install jq"
    echo "  macOS: brew install jq"
fi

echo ""
echo "================================================"
echo "⚠️  IMPORTANTE: Mantén este secreto seguro"
echo "   No lo compartas ni lo subas al repositorio"
echo "================================================"
echo ""

