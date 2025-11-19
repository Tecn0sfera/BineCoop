<?php
// config/config.php

// Verificación mejorada que permite inclusión desde otros archivos
if (php_sapi_name() !== 'cli' && 
    empty($GLOBALS['allowed_config_access']) && 
    basename($_SERVER['SCRIPT_FILENAME']) === 'config.php') {
    http_response_code(403);
    die('Acceso prohibido');
}

// Cargar variables de entorno
require_once __DIR__ . '/../../env_loader.php';

// Clave API para autenticación con el servidor B
define('ADMIN_API_KEY', env('ADMIN_API_KEY', 'clave_api_secreta_ejemplo_cambiar_en_produccion'));

// Configuración de la base de datos
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_NAME', env('DB_NAME', 'nombre_base_datos_ejemplo'));
define('DB_USER', env('DB_USER', 'usuario_ejemplo'));
define('DB_PASS', env('DB_PASS', 'contraseña_ejemplo'));
define('DB_PORT', env('DB_PORT', '3306'));
define('DB_CHARSET', env('DB_CHARSET', 'utf8mb4'));

// Configuración JWT (si usas tokens)
define('JWT_SECRET', env('JWT_SECRET', 'clave_secreta_jwt_ejemplo_cambiar_en_produccion'));

// Configuración de entorno
define('APP_ENV', env('APP_ENV', 'development'));
define('DISPLAY_ERRORS', env('DISPLAY_ERRORS', 'false') === 'true' || env('DISPLAY_ERRORS', 'false') === true);
?>
