<?php
// Parámetros de conexión
$host = 'sql211.infinityfree.com';      // o 127.0.0.1
$usuario = 'if0_39215471';        // Cambiar si tu usuario MySQL no es root
$contrasena = 'RockGuidoNetaNa';         // Cambiar si tu root tiene contraseña
$base_de_datos = 'if0_39215471_admin_panel';

// Crear conexión
$mysqli = new mysqli($host, $usuario, $contrasena, $base_de_datos);

// Verificar conexión
if ($mysqli->connect_error) {
    die("Error de conexión a la base de datos: " . $mysqli->connect_error);
}

// Opcional: establecer codificación de caracteres
$mysqli->set_charset("utf8mb4");
?>

