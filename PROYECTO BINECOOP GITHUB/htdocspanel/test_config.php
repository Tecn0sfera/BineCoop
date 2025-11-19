<?php
/**
 * Script de prueba para verificar la carga de configuración
 * Eliminar después de verificar
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Prueba de Configuración</h1>";

// Cargar env_loader
require_once __DIR__ . '/env_loader.php';

echo "<h2>Valores de DB_HOST desde diferentes fuentes:</h2>";
echo "<pre>";
echo "getenv('DB_HOST'): " . var_export(getenv('DB_HOST'), true) . "\n";
echo "\$_ENV['DB_HOST']: " . var_export($_ENV['DB_HOST'] ?? 'NO DEFINIDO', true) . "\n";
echo "\$_SERVER['DB_HOST']: " . var_export($_SERVER['DB_HOST'] ?? 'NO DEFINIDO', true) . "\n";
echo "\$GLOBALS['DB_HOST']: " . var_export($GLOBALS['DB_HOST'] ?? 'NO DEFINIDO', true) . "\n";
echo "env('DB_HOST'): " . var_export(env('DB_HOST'), true) . "\n";
echo "</pre>";

echo "<h2>Contenido de config.json:</h2>";
$configJson = __DIR__ . '/../config.json';
if (file_exists($configJson)) {
    $config = json_decode(file_get_contents($configJson), true);
    echo "<pre>";
    echo "DB_HOST desde config.json: " . var_export($config['htdocspanel']['DB_HOST'] ?? 'NO DEFINIDO', true) . "\n";
    echo "DB_PORT desde config.json: " . var_export($config['htdocspanel']['DB_PORT'] ?? 'NO DEFINIDO', true) . "\n";
    echo "DB_NAME desde config.json: " . var_export($config['htdocspanel']['DB_NAME'] ?? 'NO DEFINIDO', true) . "\n";
    echo "</pre>";
} else {
    echo "<p style='color: red;'>ERROR: config.json no encontrado en: {$configJson}</p>";
}

echo "<h2>Intentando conectar a la base de datos:</h2>";
try {
    require_once __DIR__ . '/config/database.php';
    $db = new Database();
    $conn = $db->getConnection();
    echo "<p style='color: green;'>✓ Conexión exitosa</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<h2>Logs recientes (últimas 20 líneas):</h2>";
$logFile = ini_get('error_log');
if ($logFile && file_exists($logFile)) {
    $lines = file($logFile);
    $lastLines = array_slice($lines, -20);
    echo "<pre>";
    echo htmlspecialchars(implode('', $lastLines));
    echo "</pre>";
} else {
    echo "<p>No se pudo encontrar el archivo de log. Verifica php.ini</p>";
}
?>
