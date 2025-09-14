<?php
// config/config.php

if (!defined('ACCESS_ALLOWED')) {
    die('Acceso directo no permitido');
}

// Clave API para autenticación con el servidor B
define('ADMIN_API_KEY', 'isec5a931d2396eac98577583c22d783c2d50c054e1fe785855331208a70871893');

// Configuración de la base de datos
define('DB_HOST', 'sql211.infinityfree.com');
define('DB_NAME', ' if0_39215471_admin_panel');
define('DB_USER', 'if0_39215471');
define('DB_PASS', 'RockGuidoNetaNa');

// Configuración JWT (si usas tokens)
// define('JWT_SECRET', 'otra_clave_secreta_789012');
?>
