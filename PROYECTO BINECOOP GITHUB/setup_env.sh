#!/bin/bash

# Script de configuración automática de variables de entorno
# Para htdocscop y htdocspanel
# Lee la configuración desde config.json
# Ejecutar desde el directorio raíz del proyecto

echo "================================================"
echo "CONFIGURACIÓN AUTOMÁTICA DE VARIABLES DE ENTORNO"
echo "================================================"
echo ""

# Obtener el directorio raíz del script
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# Verificar si existe config.json
CONFIG_FILE="config.json"
if [ ! -f "$CONFIG_FILE" ]; then
    echo "❌ Error: No se encontró el archivo $CONFIG_FILE"
    echo ""
    echo "Por favor, crea un archivo $CONFIG_FILE basado en config.example.json"
    echo "o ejecuta el script con valores por defecto."
    echo ""
    read -p "¿Deseas usar valores por defecto? (s/N): " use_defaults
    if [[ ! "$use_defaults" =~ ^[Ss]$ ]]; then
        echo "Saliendo..."
        exit 1
    fi
    CONFIG_FILE=""
fi

# Función para leer valor del JSON
read_json_value() {
    local section=$1
    local key=$2
    local default_value=$3
    
    if [ -n "$CONFIG_FILE" ] && command -v jq &> /dev/null; then
        # Usar jq si está disponible
        local value=$(jq -r ".${section}.${key} // \"\"" "$CONFIG_FILE" 2>/dev/null)
        if [ -n "$value" ] && [ "$value" != "null" ]; then
            echo "$value"
        else
            echo "$default_value"
        fi
    elif [ -n "$CONFIG_FILE" ] && command -v python3 &> /dev/null; then
        # Usar Python como alternativa
        local value=$(python3 -c "import json, sys; data = json.load(open('$CONFIG_FILE')); print(data.get('$section', {}).get('$key', '$default_value'))" 2>/dev/null)
        echo "$value"
    else
        echo "$default_value"
    fi
}

# Función para crear .env en htdocscop
setup_htdocscop() {
    local ENV_FILE="htdocscop/.env"
    
    if [ -f "$ENV_FILE" ]; then
        echo "⚠️  El archivo $ENV_FILE ya existe."
        read -p "¿Deseas sobrescribirlo? (s/N): " overwrite
        if [[ ! "$overwrite" =~ ^[Ss]$ ]]; then
            echo "✅ Saltando configuración de htdocscop..."
            return
        fi
    fi
    
    echo ""
    echo "📝 Configurando htdocscop/.env"
    echo "-------------------------------------------"
    
    # Leer valores del JSON o usar defaults
    local db_host=$(read_json_value "htdocscop" "DB_HOST" "sql211.infinityfree.com")
    local db_port=$(read_json_value "htdocscop" "DB_PORT" "3306")
    local db_name=$(read_json_value "htdocscop" "DB_NAME" "if0_39215471_admin_panel")
    local db_user=$(read_json_value "htdocscop" "DB_USER" "if0_39215471")
    local db_pass=$(read_json_value "htdocscop" "DB_PASS" "RockGuidoNetaNa")
    local admin_api_key=$(read_json_value "htdocscop" "ADMIN_API_KEY" "isec5a931d2396eac98577583c22d783c2d50c054e1fe785855331208a70871893")
    
    if [ -n "$CONFIG_FILE" ]; then
        echo "✅ Valores leídos desde $CONFIG_FILE"
    else
        echo "⚠️  Usando valores por defecto"
    fi
    echo ""
    
    # Crear el archivo .env
    cat > "$ENV_FILE" << EOF
# Configuración de Base de Datos
DB_HOST=$db_host
DB_PORT=$db_port
DB_NAME=$db_name
DB_USER=$db_user
DB_PASS=$db_pass

# Clave API para autenticación
ADMIN_API_KEY=$admin_api_key
EOF
    
    # Establecer permisos (solo lectura para otros, lectura/escritura para propietario)
    chmod 600 "$ENV_FILE" 2>/dev/null || chmod 644 "$ENV_FILE"
    
    echo "✅ Archivo $ENV_FILE creado exitosamente"
}

# Función para crear .env en htdocspanel
setup_htdocspanel() {
    local ENV_FILE="htdocspanel/.env"
    
    if [ -f "$ENV_FILE" ]; then
        echo "⚠️  El archivo $ENV_FILE ya existe."
        read -p "¿Deseas sobrescribirlo? (s/N): " overwrite
        if [[ ! "$overwrite" =~ ^[Ss]$ ]]; then
            echo "✅ Saltando configuración de htdocspanel..."
            return
        fi
    fi
    
    echo ""
    echo "📝 Configurando htdocspanel/.env"
    echo "-------------------------------------------"
    
    # Leer valores del JSON o usar defaults
    local db_host=$(read_json_value "htdocspanel" "DB_HOST" "localhost")
    local db_port=$(read_json_value "htdocspanel" "DB_PORT" "3306")
    local db_name=$(read_json_value "htdocspanel" "DB_NAME" "nombre_base_datos_ejemplo")
    local db_user=$(read_json_value "htdocspanel" "DB_USER" "usuario_ejemplo")
    local db_pass=$(read_json_value "htdocspanel" "DB_PASS" "contraseña_ejemplo")
    local db_charset=$(read_json_value "htdocspanel" "DB_CHARSET" "utf8mb4")
    local admin_api_key=$(read_json_value "htdocspanel" "ADMIN_API_KEY" "clave_api_secreta_ejemplo_cambiar_en_produccion")
    local jwt_secret=$(read_json_value "htdocspanel" "JWT_SECRET" "clave_secreta_jwt_ejemplo_cambiar_en_produccion")
    local app_env=$(read_json_value "htdocspanel" "APP_ENV" "development")
    local display_errors=$(read_json_value "htdocspanel" "DISPLAY_ERRORS" "false")
    
    if [ -n "$CONFIG_FILE" ]; then
        echo "✅ Valores leídos desde $CONFIG_FILE"
    else
        echo "⚠️  Usando valores por defecto"
    fi
    echo ""
    
    # Crear el archivo .env
    cat > "$ENV_FILE" << EOF
# Configuración de Base de Datos
DB_HOST=$db_host
DB_PORT=$db_port
DB_NAME=$db_name
DB_USER=$db_user
DB_PASS=$db_pass
DB_CHARSET=$db_charset

# Clave API para autenticación
ADMIN_API_KEY=$admin_api_key

# Configuración JWT
JWT_SECRET=$jwt_secret

# Configuración de entorno
APP_ENV=$app_env
DISPLAY_ERRORS=$display_errors
EOF
    
    # Establecer permisos (solo lectura para otros, lectura/escritura para propietario)
    chmod 600 "$ENV_FILE" 2>/dev/null || chmod 644 "$ENV_FILE"
    
    echo "✅ Archivo $ENV_FILE creado exitosamente"
}

# Menú principal
echo "Selecciona qué configurar:"
echo "1) Solo htdocscop"
echo "2) Solo htdocspanel"
echo "3) Ambos (htdocscop y htdocspanel)"
echo "4) Salir"
echo ""
read -p "Opción [3]: " option
option=${option:-3}

case $option in
    1)
        setup_htdocscop
        ;;
    2)
        setup_htdocspanel
        ;;
    3)
        setup_htdocscop
        setup_htdocspanel
        ;;
    4)
        echo "Saliendo..."
        exit 0
        ;;
    *)
        echo "❌ Opción inválida"
        exit 1
        ;;
esac

echo ""
echo "================================================"
echo "✅ Configuración completada"
echo "================================================"
echo ""
if [ -n "$CONFIG_FILE" ]; then
    echo "✅ Configuración leída desde: $CONFIG_FILE"
else
    echo "⚠️  Se usaron valores por defecto (no se encontró config.json)"
fi
echo ""
echo "NOTAS IMPORTANTES:"
echo "- Los archivos .env NO deben ser subidos al repositorio Git"
echo "- Asegúrate de que .env esté en .gitignore"
echo "- Los valores tienen prioridad sobre los valores por defecto"
echo "- Crea config.json basado en config.example.json para personalizar"
echo ""
