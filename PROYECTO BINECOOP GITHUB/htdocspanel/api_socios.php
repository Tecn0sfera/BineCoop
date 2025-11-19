<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Verificar autenticación
if (empty($_SESSION['user']['logged_in'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit();
}

$GLOBALS['allowed_config_access'] = true;
require __DIR__ . '/config/config.php';
require_once 'includes/db.php';

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit();
}

$action = $_POST['action'] ?? '';
$socio_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($socio_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID de socio inválido']);
    exit();
}

try {
    switch ($action) {
        case 'suspender':
            $query = "UPDATE visitantes SET activo = 0 WHERE id = ? AND estado_aprobacion = 'aprobado'";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("i", $socio_id);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                error_log("Socio suspendido: ID {$socio_id} por usuario: " . ($_SESSION['user']['id'] ?? 'Admin'));
                echo json_encode(['success' => true, 'message' => 'Socio suspendido exitosamente'], JSON_UNESCAPED_UNICODE);
            } else {
                throw new Exception('No se pudo suspender el socio');
            }
            break;
            
        case 'activar':
            $query = "UPDATE visitantes SET activo = 1 WHERE id = ? AND estado_aprobacion = 'aprobado'";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("i", $socio_id);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                error_log("Socio activado: ID {$socio_id} por usuario: " . ($_SESSION['user']['id'] ?? 'Admin'));
                echo json_encode(['success' => true, 'message' => 'Socio activado exitosamente'], JSON_UNESCAPED_UNICODE);
            } else {
                throw new Exception('No se pudo activar el socio');
            }
            break;
            
        case 'eliminar':
            // Verificar que el socio existe
            $check_query = "SELECT id, nombre FROM visitantes WHERE id = ? AND estado_aprobacion = 'aprobado'";
            $check_stmt = $mysqli->prepare($check_query);
            $check_stmt->bind_param("i", $socio_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception('Socio no encontrado');
            }
            
            // Eliminar el socio (cambiar estado a rechazado o eliminar según preferencia)
            // Opción 1: Cambiar a rechazado (mantener registro)
            $query = "UPDATE visitantes SET estado_aprobacion = 'rechazado', activo = 0 WHERE id = ?";
            
            // Opción 2: Eliminar completamente (descomentar si prefieres esto)
            // $query = "DELETE FROM visitantes WHERE id = ?";
            
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("i", $socio_id);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                $socio = $result->fetch_assoc();
                error_log("Socio eliminado: ID {$socio_id} - {$socio['nombre']} por usuario: " . ($_SESSION['user']['id'] ?? 'Admin'));
                echo json_encode(['success' => true, 'message' => 'Socio eliminado exitosamente'], JSON_UNESCAPED_UNICODE);
            } else {
                throw new Exception('No se pudo eliminar el socio');
            }
            break;
            
        default:
            throw new Exception('Acción no válida');
    }
    
} catch (Exception $e) {
    error_log("Error en api_socios.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>

