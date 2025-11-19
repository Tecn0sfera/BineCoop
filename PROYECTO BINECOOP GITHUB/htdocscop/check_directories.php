<?php
// check_directories.php - Script para verificar configuración

echo "<h2>Verificación del Sistema</h2>\n";

// 1. Verificar directorio uploads
$upload_dir = __DIR__ . '/uploads/reports/';
echo "<h3>1. Directorio de uploads</h3>\n";
echo "Ruta: $upload_dir<br>\n";

if (!is_dir($upload_dir)) {
    echo "❌ Directorio no existe. Intentando crear...<br>\n";
    if (mkdir($upload_dir, 0755, true)) {
        echo "✅ Directorio creado exitosamente<br>\n";
    } else {
        echo "❌ No se pudo crear el directorio<br>\n";
    }
} else {
    echo "✅ Directorio existe<br>\n";
}

if (is_writable($upload_dir)) {
    echo "✅ Directorio tiene permisos de escritura<br>\n";
} else {
    echo "❌ Directorio NO tiene permisos de escritura<br>\n";
    echo "Ejecuta: chmod 755 $upload_dir<br>\n";
}

// 2. Verificar límites de PHP
echo "<h3>2. Configuración de PHP</h3>\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "<br>\n";
echo "post_max_size: " . ini_get('post_max_size') . "<br>\n";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "<br>\n";
echo "max_execution_time: " . ini_get('max_execution_time') . "<br>\n";

// 3. Verificar extensiones
echo "<h3>3. Extensiones PHP</h3>\n";
$extensions = ['mysqli', 'fileinfo', 'json'];
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ $ext cargado<br>\n";
    } else {
        echo "❌ $ext NO cargado<br>\n";
    }
}

// 4. Verificar archivos
echo "<h3>4. Archivos requeridos</h3>\n";
$files = ['config.php', 'db.php'];
foreach ($files as $file) {
    $filepath = __DIR__ . '/' . $file;
    if (file_exists($filepath)) {
        echo "✅ $file existe<br>\n";
        if (is_readable($filepath)) {
            echo "✅ $file es legible<br>\n";
        } else {
            echo "❌ $file NO es legible<br>\n";
        }
    } else {
        echo "❌ $file NO existe<br>\n";
    }
}

// 5. Test de base de datos
echo "<h3>5. Conexión a Base de Datos</h3>\n";
try {
    $GLOBALS['allowed_config_access'] = true;
    require_once __DIR__ . '/config.php';
    require_once __DIR__ . '/db.php';
    
    if (isset($mysqli) && !$mysqli->connect_error) {
        echo "✅ Conexión a BD exitosa<br>\n";
        echo "Servidor: " . $mysqli->server_info . "<br>\n";
        
        // Verificar tablas
        $tables = ['tiempo_trabajado', 'reportes_pdf'];
        foreach ($tables as $table) {
            $result = $mysqli->query("SHOW TABLES LIKE '$table'");
            if ($result && $result->num_rows > 0) {
                echo "✅ Tabla '$table' existe<br>\n";
            } else {
                echo "❌ Tabla '$table' NO existe<br>\n";
            }
        }
    } else {
        echo "❌ Error de conexión a BD: " . ($mysqli->connect_error ?? 'Unknown') . "<br>\n";
    }
} catch (Exception $e) {
    echo "❌ Error al verificar BD: " . $e->getMessage() . "<br>\n";
}

// 6. Verificar sesión
echo "<h3>6. Estado de Sesión</h3>\n";
session_start();
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "✅ Sesiones funcionando<br>\n";
    if (isset($_SESSION['user'])) {
        echo "✅ Usuario logueado: " . ($_SESSION['user']['name'] ?? 'Unknown') . "<br>\n";
    } else {
        echo "⚠️ No hay usuario logueado<br>\n";
    }
} else {
    echo "❌ Problema con sesiones<br>\n";
}

echo "<br><strong>Verificación completada</strong>\n";
?>