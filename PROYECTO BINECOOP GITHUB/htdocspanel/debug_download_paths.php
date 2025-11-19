<?php
/**
 * Script de diagnóstico para verificar rutas de descarga de comprobantes
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/comprobantes_db.php';

echo "<h1>Diagnóstico de Rutas de Comprobantes</h1>";

// Obtener todos los comprobantes
$comprobantes = getComprobantes();

if (empty($comprobantes)) {
    echo "<p>No hay comprobantes en la base de datos.</p>";
    exit;
}

echo "<h2>Total de comprobantes: " . count($comprobantes) . "</h2>";

echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
echo "<tr>";
echo "<th>ID</th>";
echo "<th>Nombre Archivo</th>";
echo "<th>Ruta Guardada</th>";
echo "<th>¿Existe?</th>";
echo "<th>Rutas Alternativas</th>";
echo "</tr>";

foreach ($comprobantes as $comp) {
    $ruta_original = $comp['ruta_archivo'];
    $nombre_archivo = $comp['nombre_archivo'];
    $filename_only = basename($ruta_original);
    
    echo "<tr>";
    echo "<td>" . htmlspecialchars($comp['id']) . "</td>";
    echo "<td>" . htmlspecialchars($nombre_archivo) . "</td>";
    echo "<td style='word-break: break-all;'>" . htmlspecialchars($ruta_original) . "</td>";
    
    // Verificar si existe la ruta original
    $existe_original = file_exists($ruta_original) && is_readable($ruta_original);
    echo "<td style='color: " . ($existe_original ? 'green' : 'red') . ";'>" . ($existe_original ? '✅ SÍ' : '❌ NO') . "</td>";
    
    // Intentar rutas alternativas
    $rutas_alternativas = [
        __DIR__ . '/../htdocscop/uploads/reports/' . basename($ruta_original),
        __DIR__ . '/../htdocscop/uploads/reports/' . $filename_only,
    ];
    
    if (strpos($ruta_original, 'htdocscop') !== false) {
        $parts = explode('htdocscop', $ruta_original);
        if (count($parts) > 1) {
            $rutas_alternativas[] = __DIR__ . '/../htdocscop' . $parts[1];
        }
    }
    
    if (strpos($ruta_original, '/home/') === 0 || strpos($ruta_original, '/var/') === 0) {
        $base_dirs = [
            __DIR__ . '/../htdocscop',
            dirname(__DIR__) . '/htdocscop',
            '/home/vol19_1/infinityfree.com/if0_39532356/htdocs',
        ];
        
        foreach ($base_dirs as $base_dir) {
            if (strpos($ruta_original, 'uploads/reports') !== false) {
                $parts = explode('uploads/reports', $ruta_original);
                if (count($parts) > 1) {
                    $rutas_alternativas[] = $base_dir . '/uploads/reports' . $parts[1];
                }
            }
            $rutas_alternativas[] = $base_dir . '/uploads/reports/' . $filename_only;
        }
    }
    
    $rutas_encontradas = [];
    foreach ($rutas_alternativas as $ruta_alt) {
        if (file_exists($ruta_alt) && is_readable($ruta_alt)) {
            $rutas_encontradas[] = "<span style='color: green;'>✅ " . htmlspecialchars($ruta_alt) . "</span>";
        } else {
            $rutas_encontradas[] = "<span style='color: red;'>❌ " . htmlspecialchars($ruta_alt) . "</span>";
        }
    }
    
    echo "<td style='word-break: break-all; font-size: 11px;'>" . implode("<br>", $rutas_encontradas) . "</td>";
    echo "</tr>";
}

echo "</table>";

// Información adicional
echo "<h2>Información del Sistema</h2>";
echo "<p><strong>Directorio actual (htdocspanel):</strong> " . __DIR__ . "</p>";
echo "<p><strong>Ruta esperada htdocscop:</strong> " . __DIR__ . '/../htdocscop' . "</p>";
echo "<p><strong>¿Existe htdocscop/uploads/reports?:</strong> " . (is_dir(__DIR__ . '/../htdocscop/uploads/reports') ? '✅ SÍ' : '❌ NO') . "</p>";

if (is_dir(__DIR__ . '/../htdocscop/uploads/reports')) {
    $archivos = scandir(__DIR__ . '/../htdocscop/uploads/reports');
    $archivos = array_filter($archivos, function($f) { return $f !== '.' && $f !== '..'; });
    echo "<p><strong>Archivos en htdocscop/uploads/reports:</strong> " . count($archivos) . "</p>";
    if (count($archivos) > 0) {
        echo "<ul>";
        foreach (array_slice($archivos, 0, 10) as $archivo) {
            echo "<li>" . htmlspecialchars($archivo) . "</li>";
        }
        if (count($archivos) > 10) {
            echo "<li>... y " . (count($archivos) - 10) . " más</li>";
        }
        echo "</ul>";
    }
}

?>

