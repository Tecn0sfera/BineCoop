<?php
/**
 * Endpoint para servir PDFs de comprobantes
 * Permite que htdocspanel acceda a archivos de htdocscop
 */

session_start();

// Headers para permitir CORS desde htdocspanel
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Obtener ID del comprobante
$comprobante_id = trim($_GET['id'] ?? '');

if (empty($comprobante_id)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'ID de comprobante no proporcionado']);
    exit();
}

// Cargar configuración y base de datos
$GLOBALS['allowed_config_access'] = true;
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/comprobantes_db.php';

// Obtener comprobante de la base de datos
$comprobante = getComprobanteById($comprobante_id);

if (!$comprobante) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Comprobante no encontrado']);
    exit();
}

$file_path = $comprobante['ruta_archivo'];
$file_name = $comprobante['nombre_archivo'];

// Verificar que el archivo existe
if (!file_exists($file_path)) {
    // Intentar construir la ruta desde el directorio actual
    $filename_only = basename($file_path);
    $alt_path = __DIR__ . '/uploads/reports/' . $filename_only;
    
    if (file_exists($alt_path)) {
        $file_path = $alt_path;
    } else {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Archivo no encontrado',
            'ruta_intentada' => $file_path,
            'ruta_alternativa' => $alt_path
        ]);
        exit();
    }
}

// Verificar que el archivo es legible
if (!is_readable($file_path)) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Archivo no accesible']);
    exit();
}

// Servir el archivo
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . htmlspecialchars($file_name, ENT_QUOTES, 'UTF-8') . '"');
header('Content-Length: ' . filesize($file_path));
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

readfile($file_path);
exit();

