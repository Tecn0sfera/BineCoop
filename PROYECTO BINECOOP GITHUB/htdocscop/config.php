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
require_once __DIR__ . '/env_loader.php';

// Clave API para autenticación con el servidor B (desde .env o valor por defecto)
define('ADMIN_API_KEY', env('ADMIN_API_KEY', 'isec5a931d2396eac98577583c22d783c2d50c054e1fe785855331208a70871893'));

// Configuración de la base de datos (desde .env o valores por defecto)
// IMPORTANTE: Estos valores deben coincidir con config.json para htdocscop
if (!defined('DB_HOST')) {
    define('DB_HOST', env('DB_HOST', 'sql211.infinityfree.com'));
}
if (!defined('DB_NAME')) {
    define('DB_NAME', env('DB_NAME', 'if0_39215471_admin_panel'));
}
if (!defined('DB_USER')) {
    define('DB_USER', env('DB_USER', 'if0_39215471'));
}
if (!defined('DB_PASS')) {
    define('DB_PASS', env('DB_PASS', 'RockGuidoNetaNa'));
}
if (!defined('DB_PORT')) {
    define('DB_PORT', env('DB_PORT', '3306'));
}

// Log de depuración para verificar qué valores se cargaron
error_log("DEBUG config.php (htdocscop): DB_NAME = " . DB_NAME . ", DB_HOST = " . DB_HOST);

// Configuración JWT (si usas tokens)
// define('JWT_SECRET', env('JWT_SECRET', 'otra_clave_secreta_789012'));