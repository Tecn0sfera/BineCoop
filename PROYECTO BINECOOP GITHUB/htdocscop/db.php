<?php
// Cargar variables de entorno
require_once __DIR__ . '/env_loader.php';

// Parámetros de conexión desde variables de entorno
// Priorizar constantes definidas en config.php si existen, sino usar env()
$host = defined('DB_HOST') ? DB_HOST : env('DB_HOST', 'localhost');
$usuario = defined('DB_USER') ? DB_USER : env('DB_USER', 'root');
$contrasena = defined('DB_PASS') ? DB_PASS : env('DB_PASS', '');
$base_de_datos = defined('DB_NAME') ? DB_NAME : env('DB_NAME', 'binecoop_db');
$port = defined('DB_PORT') ? DB_PORT : env('DB_PORT', '3306');

// Log de depuración para verificar qué valores se están usando
error_log("DEBUG db.php (htdocscop): Conectando a BD - Host: $host, DB: $base_de_datos, User: $usuario, Port: $port");

// Crear conexión
$mysqli = new mysqli($host, $usuario, $contrasena, $base_de_datos, $port);

// Verificar conexión
if ($mysqli->connect_error) {
    error_log("Error de conexión a la base de datos: " . $mysqli->connect_error);
    die("Error de conexión a la base de datos. Por favor, contacte al administrador.");
}

// Opcional: establecer codificación de caracteres
$mysqli->set_charset("utf8mb4");
?>
