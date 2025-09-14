<?php
session_start();
require_once __DIR__ . '/config/config.php';
require_once 'includes/db.php';

// Verificar permisos
if (empty($_SESSION['user']['es_admin'])) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Validar ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'ID inválido']);
    exit;
}

try {
    // Llamar a la API para aprobar
    require_once __DIR__ . '/dashboard.php'; // Para tener acceso a callAdminAPI
    
    $response = callAdminAPI("visitantes/{$id}/approve", 'PUT', ['approve' => true]);
    
    if ($response['code'] === 200 && !empty($response['data']['success'])) {
        // Registrar en base de datos local
        $stmt = $mysqli->prepare("INSERT INTO aprobaciones (admin_id, visitante_id, fecha) VALUES (?, ?, NOW())");
        $stmt->bind_param("ii", $_SESSION['user']['id'], $id);
        $stmt->execute();
        
        // Respuesta exitosa
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    } else {
        throw new Exception($response['data']['error'] ?? 'Error al aprobar');
    }
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
?>
