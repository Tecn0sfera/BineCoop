#!/bin/bash

# Script para generar JWT_SECRET y ADMIN_API_KEY seguros
# Ejecutar desde el directorio raíz del proyecto

echo "================================================"
echo "GENERADOR DE SECRETOS SEGUROS"
echo "================================================"
echo ""
echo "Este script generará:"
echo "  - JWT_SECRET (para htdocspanel)"
echo "  - ADMIN_API_KEY (para htdocscop y htdocspanel)"
echo ""

# Obtener el directorio raíz del script
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# Generar JWT_SECRET seguro (64 caracteres hexadecimales)
echo "[1/2] Generando JWT_SECRET seguro..."
JWT_SECRET=$(openssl rand -hex 32)

# Verificar que JWT_SECRET se generó correctamente
if [ -z "$JWT_SECRET" ]; then
    echo "❌ Error: No se pudo generar JWT_SECRET"
    exit 1
fi

# Verificar que JWT_SECRET no sea solo ceros
if echo "$JWT_SECRET" | grep -qE '^0+$'; then
    echo "❌ Error: JWT_SECRET generado es inválido (solo ceros)"
    echo "Reintentando..."
    JWT_SECRET=$(openssl rand -hex 32)
fi

# Generar ADMIN_API_KEY seguro (64 caracteres hexadecimales)
echo "[2/2] Generando ADMIN_API_KEY seguro..."
ADMIN_API_KEY=$(openssl rand -hex 32)

# Verificar que ADMIN_API_KEY se generó correctamente
if [ -z "$ADMIN_API_KEY" ]; then
    echo "❌ Error: No se pudo generar ADMIN_API_KEY"
    exit 1
fi

# Verificar que ADMIN_API_KEY no sea solo ceros
if echo "$ADMIN_API_KEY" | grep -qE '^0+$'; then
    echo "❌ Error: ADMIN_API_KEY generado es inválido (solo ceros)"
    echo "Reintentando..."
    ADMIN_API_KEY=$(openssl rand -hex 32)
fi

echo ""
echo "✅ Secretos generados:"
echo ""
echo "JWT_SECRET:"
echo "$JWT_SECRET"
echo ""
echo "ADMIN_API_KEY:"
echo "$ADMIN_API_KEY"
echo ""

# Verificar si existe config.json
CONFIG_FILE="config.json"
if [ ! -f "$CONFIG_FILE" ]; then
    echo "⚠️  No se encontró config.json"
    echo ""
    echo "Por favor, crea config.json basado en config.example.json primero."
    echo "Luego ejecuta este script nuevamente."
    echo ""
    exit 1
fi

# Validar JSON antes de actualizar
echo ""
echo "Validando formato de config.json..."
if ! jq empty "$CONFIG_FILE" 2>/dev/null && ! python3 -m json.tool "$CONFIG_FILE" > /dev/null 2>&1; then
    echo "❌ Error: config.json tiene formato JSON inválido"
    echo ""
    echo "Por favor, corrige el formato de config.json antes de continuar."
    echo "Puedes usar config.example.json como referencia."
    echo ""
    echo "Valores generados para copiar manualmente después de corregir:"
    echo '  "htdocspanel": {'
    echo "    \"JWT_SECRET\": \"$JWT_SECRET\","
    echo "    \"ADMIN_API_KEY\": \"$ADMIN_API_KEY\""
    echo "  },"
    echo '  "htdocscop": {'
    echo "    \"ADMIN_API_KEY\": \"$ADMIN_API_KEY\""
    echo "  }"
    exit 1
fi

echo "✅ config.json tiene formato válido"
echo ""

# Actualizar config.json automáticamente usando jq o python
echo "Actualizando config.json automáticamente..."
echo ""

if command -v jq &> /dev/null; then
    # Usar jq si está disponible
    jq ".htdocspanel.JWT_SECRET = \"$JWT_SECRET\" | .htdocspanel.ADMIN_API_KEY = \"$ADMIN_API_KEY\" | .htdocscop.ADMIN_API_KEY = \"$ADMIN_API_KEY\"" "$CONFIG_FILE" > "${CONFIG_FILE}.tmp"
    if [ $? -eq 0 ]; then
        # Validar el JSON generado antes de reemplazar
        if jq empty "${CONFIG_FILE}.tmp" 2>/dev/null; then
            # Verificar que los valores se actualizaron correctamente
            UPDATED_JWT=$(jq -r '.htdocspanel.JWT_SECRET' "${CONFIG_FILE}.tmp")
            UPDATED_ADMIN=$(jq -r '.htdocspanel.ADMIN_API_KEY' "${CONFIG_FILE}.tmp")
            if [ "$UPDATED_JWT" = "$JWT_SECRET" ] && [ "$UPDATED_ADMIN" = "$ADMIN_API_KEY" ]; then
                mv "${CONFIG_FILE}.tmp" "$CONFIG_FILE"
                echo "✅ config.json actualizado exitosamente"
                echo "   - htdocspanel.JWT_SECRET actualizado"
                echo "   - htdocspanel.ADMIN_API_KEY actualizado"
                echo "   - htdocscop.ADMIN_API_KEY actualizado"
            else
                rm -f "${CONFIG_FILE}.tmp"
                echo "❌ Error: Los valores no se actualizaron correctamente"
                echo ""
                echo "Por favor, actualiza manualmente en config.json:"
                echo '  "htdocspanel": {'
                echo "    \"JWT_SECRET\": \"$JWT_SECRET\","
                echo "    \"ADMIN_API_KEY\": \"$ADMIN_API_KEY\""
                echo "  },"
                echo '  "htdocscop": {'
                echo "    \"ADMIN_API_KEY\": \"$ADMIN_API_KEY\""
                echo "  }"
            fi
        else
            rm -f "${CONFIG_FILE}.tmp"
            echo "❌ Error: El JSON generado es inválido"
            echo ""
            echo "Por favor, actualiza manualmente en config.json:"
            echo '  "htdocspanel": {'
            echo "    \"JWT_SECRET\": \"$JWT_SECRET\","
            echo "    \"ADMIN_API_KEY\": \"$ADMIN_API_KEY\""
            echo "  },"
            echo '  "htdocscop": {'
            echo "    \"ADMIN_API_KEY\": \"$ADMIN_API_KEY\""
            echo "  }"
        fi
    else
        echo "❌ Error al actualizar config.json"
        echo ""
        echo "Por favor, actualiza manualmente en config.json:"
        echo '  "htdocspanel": {'
        echo "    \"JWT_SECRET\": \"$JWT_SECRET\","
        echo "    \"ADMIN_API_KEY\": \"$ADMIN_API_KEY\""
        echo "  },"
        echo '  "htdocscop": {'
        echo "    \"ADMIN_API_KEY\": \"$ADMIN_API_KEY\""
        echo "  }"
    fi
elif command -v python3 &> /dev/null; then
    # Usar Python como alternativa
    python3 << EOF
import json
import sys

try:
    # Validar JSON antes de leer
    with open('$CONFIG_FILE', 'r', encoding='utf-8') as f:
        content = f.read()
        try:
            config = json.loads(content)
        except json.JSONDecodeError as e:
            print(f"❌ Error: config.json tiene formato JSON inválido")
            print(f"   Error en línea {e.lineno}, columna {e.colno}: {e.msg}")
            print("")
            print("Por favor, corrige el formato de config.json antes de continuar.")
            print("Puedes usar config.example.json como referencia.")
            sys.exit(1)
    
    # Asegurar que las secciones existan
    if 'htdocspanel' not in config:
        config['htdocspanel'] = {}
    if 'htdocscop' not in config:
        config['htdocscop'] = {}
    
    # Actualizar valores
    config['htdocspanel']['JWT_SECRET'] = '$JWT_SECRET'
    config['htdocspanel']['ADMIN_API_KEY'] = '$ADMIN_API_KEY'
    config['htdocscop']['ADMIN_API_KEY'] = '$ADMIN_API_KEY'
    
    # Validar antes de escribir
    json_str = json.dumps(config, indent=2, ensure_ascii=False)
    json.loads(json_str)  # Validar que el JSON generado sea válido
    
    with open('$CONFIG_FILE', 'w', encoding='utf-8') as f:
        f.write(json_str)
    
    # Verificar que los valores se actualizaron correctamente
    with open('$CONFIG_FILE', 'r', encoding='utf-8') as f:
        updated_config = json.load(f)
        if (updated_config.get('htdocspanel', {}).get('JWT_SECRET') == '$JWT_SECRET' and 
            updated_config.get('htdocspanel', {}).get('ADMIN_API_KEY') == '$ADMIN_API_KEY'):
            print("✅ config.json actualizado exitosamente")
            print("   - htdocspanel.JWT_SECRET actualizado")
            print("   - htdocspanel.ADMIN_API_KEY actualizado")
            print("   - htdocscop.ADMIN_API_KEY actualizado")
            sys.exit(0)
        else:
            print("❌ Error: Los valores no se actualizaron correctamente")
            sys.exit(1)
except json.JSONDecodeError as e:
    print(f"❌ Error: JSON inválido generado")
    print(f"   {e}")
    sys.exit(1)
except Exception as e:
    print(f"❌ Error al actualizar config.json: {e}")
    print("")
    print("Por favor, actualiza manualmente en config.json:")
    print('  "htdocspanel": {')
    print(f"    \"JWT_SECRET\": \"$JWT_SECRET\",")
    print(f"    \"ADMIN_API_KEY\": \"$ADMIN_API_KEY\"")
    print("  },")
    print('  "htdocscop": {')
    print(f"    \"ADMIN_API_KEY\": \"$ADMIN_API_KEY\"")
    print("  }")
    sys.exit(1)
EOF
else
    echo "❌ Error: Se requiere jq o python3 para actualizar config.json"
    echo ""
    echo "Por favor, actualiza manualmente en config.json:"
    echo '  "htdocspanel": {'
    echo "    \"JWT_SECRET\": \"$JWT_SECRET\","
    echo "    \"ADMIN_API_KEY\": \"$ADMIN_API_KEY\""
    echo "  },"
    echo '  "htdocscop": {'
    echo "    \"ADMIN_API_KEY\": \"$ADMIN_API_KEY\""
    echo "  }"
    echo ""
    echo "O instala jq:"
    echo "  Ubuntu/Debian: sudo apt-get install jq"
    echo "  macOS: brew install jq"
fi

echo ""
echo "================================================"
echo "⚠️  IMPORTANTE: Mantén estos secretos seguros"
echo "   No los compartas ni los subas al repositorio"
echo "   Ejecuta setup_env.sh para aplicar los cambios"
echo "================================================"
echo ""

