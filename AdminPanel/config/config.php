<?php
// config/config.php

// Verificación 
if (php_sapi_name() !== 'cli' && 
    empty($GLOBALS['allowed_config_access']) && 
    basename($_SERVER['SCRIPT_FILENAME']) === 'config.php') {
    http_response_code(403);
    die('Acceso prohibido');
}

// Clave API para autenticación con el servidor B
define('ADMIN_API_KEY', 'isec5a931d2396eac98577583c22d783c2d50c054e1fe785855331208a70871893');

// Configuración de la base de datos
define('DB_HOST', 'sql211.infinityfree.com');
define('DB_NAME', ' if0_39215471_admin_panel');
define('DB_USER', 'if0_39215471');
define('DB_PASS', 'Raeaeae');

?>
