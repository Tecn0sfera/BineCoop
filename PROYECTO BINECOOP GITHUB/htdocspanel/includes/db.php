<?php
// Cargar variables de entorno
require_once __DIR__ . '/../env_loader.php';

// Parámetros de conexión desde variables de entorno
$host = env('DB_HOST', 'localhost');
$usuario = env('DB_USER', 'usuario_ejemplo');
$contrasena = env('DB_PASS', 'contraseña_ejemplo');
$base_de_datos = env('DB_NAME', 'nombre_base_datos_ejemplo');
$port = env('DB_PORT', '3306');
$charset = env('DB_CHARSET', 'utf8mb4');

// Crear conexión
$mysqli = new mysqli($host, $usuario, $contrasena, $base_de_datos, $port);

// Verificar conexión
if ($mysqli->connect_error) {
    error_log("Error de conexión a BD: " . $mysqli->connect_error);
    die("Error de conexión a la base de datos: " . $mysqli->connect_error);
}

// Establecer codificación de caracteres
$mysqli->set_charset($charset);
?>

