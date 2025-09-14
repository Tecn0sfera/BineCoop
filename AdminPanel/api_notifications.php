<?php
session_start();

// Verificar autenticación
if (empty($_SESSION['user']['logged_in'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit();
}

$GLOBALS['allowed_config_access'] = true;
require __DIR__ . '/config/config.php';
require_once 'includes/db.php';

header('Content-Type: application/json');

try {
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    $user_id = $_SESSION['user']['id'];
    
    switch ($action) {
        case 'get':
            // Obtener notificaciones del usuario con estado de lectura
            $query = "
                SELECT 
                    n.id,
                    n.titulo,
                    n.mensaje,
                    n.tipo,
                    n.created_at,
                    CASE 
                        WHEN n.usuario_id IS NOT NULL THEN n.leida
                        ELSE COALESCE(nl.notificacion_id IS NOT NULL, 0)
                    END as leida
                FROM notificaciones n
                LEFT JOIN notificaciones_lecturas nl ON (n.id = nl.notificacion_id AND nl.usuario_id = ?)
                WHERE n.usuario_id IS NULL OR n.usuario_id = ?
                ORDER BY n.created_at DESC 
                LIMIT 50
            ";
            
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("ii", $user_id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $notifications = [];
            while ($row = $result->fetch_assoc()) {
                $notifications[] = [
                    'id' => (int)$row['id'],
                    'titulo' => $row['titulo'],
                    'mensaje' => $row['mensaje'],
                    'tipo' => $row['tipo'],
                    'leida' => (bool)$row['leida'],
                    'created_at' => $row['created_at']
                ];
            }
            
            echo json_encode(['success' => true, 'notifications' => $notifications]);
            break;
            
        case 'count_unread':
            // Contar notificaciones no leídas
            $query = "
                SELECT COUNT(*) as count 
                FROM notificaciones n
                LEFT JOIN notificaciones_lecturas nl ON (n.id = nl.notificacion_id AND nl.usuario_id = ?)
                WHERE (n.usuario_id IS NULL OR n.usuario_id = ?)
                AND (
                    (n.usuario_id IS NOT NULL AND n.leida = FALSE)
                    OR (n.usuario_id IS NULL AND nl.notificacion_id IS NULL)
                )
            ";
            
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("ii", $user_id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            echo json_encode(['success' => true, 'count' => (int)$row['count']]);
            break;
            
        case 'mark_read':
            // Marcar notificación como leída
            if (!isset($_POST['notification_id'])) {
                throw new Exception('ID de notificación requerido');
            }
            
            $notification_id = (int)$_POST['notification_id'];
            
            // Verificar si la notificación existe y el usuario tiene acceso
            $check_query = "SELECT usuario_id FROM notificaciones WHERE id = ?";
            $stmt = $mysqli->prepare($check_query);
            $stmt->bind_param("i", $notification_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception('Notificación no encontrada');
            }
            
            $notification = $result->fetch_assoc();
            
            if ($notification['usuario_id'] === null) {
                // Notificación global - usar tabla de lecturas
                $query = "INSERT INTO notificaciones_lecturas (notificacion_id, usuario_id) 
                         VALUES (?, ?) 
                         ON DUPLICATE KEY UPDATE leida_at = CURRENT_TIMESTAMP";
                $stmt = $mysqli->prepare($query);
                $stmt->bind_param("ii", $notification_id, $user_id);
            } else {
                // Notificación personal - marcar directamente
                $query = "UPDATE notificaciones SET leida = TRUE WHERE id = ? AND usuario_id = ?";
                $stmt = $mysqli->prepare($query);
                $stmt->bind_param("ii", $notification_id, $user_id);
            }
            
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Notificación marcada como leída']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo marcar la notificación']);
            }
            break;
            
        case 'mark_all_read':
            // Marcar todas como leídas
            $mysqli->begin_transaction();
            
            try {
                // Marcar notificaciones personales
                $query1 = "UPDATE notificaciones SET leida = TRUE WHERE usuario_id = ? AND leida = FALSE";
                $stmt1 = $mysqli->prepare($query1);
                $stmt1->bind_param("i", $user_id);
                $stmt1->execute();
                
                // Marcar notificaciones globales no leídas
                $query2 = "
                    INSERT INTO notificaciones_lecturas (notificacion_id, usuario_id)
                    SELECT n.id, ? 
                    FROM notificaciones n
                    LEFT JOIN notificaciones_lecturas nl ON (n.id = nl.notificacion_id AND nl.usuario_id = ?)
                    WHERE n.usuario_id IS NULL AND nl.notificacion_id IS NULL
                    ON DUPLICATE KEY UPDATE leida_at = CURRENT_TIMESTAMP
                ";
                $stmt2 = $mysqli->prepare($query2);
                $stmt2->bind_param("ii", $user_id, $user_id);
                $stmt2->execute();
                
                $mysqli->commit();
                echo json_encode(['success' => true, 'message' => 'Todas las notificaciones marcadas como leídas']);
                
            } catch (Exception $e) {
                $mysqli->rollback();
                throw $e;
            }
            break;
            
        case 'delete':
            // Eliminar notificación (solo personales)
            if (!isset($_POST['notification_id'])) {
                throw new Exception('ID de notificación requerido');
            }
            
            $notification_id = (int)$_POST['notification_id'];
            $query = "DELETE FROM notificaciones WHERE id = ? AND usuario_id = ?";
            
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("ii", $notification_id, $user_id);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Notificación eliminada']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo eliminar la notificación']);
            }
            break;
            
        case 'create':
            // Crear notificación (solo para administradores)
            if ($_SESSION['user']['role'] !== 'admin' && $_SESSION['user']['role'] !== 'super_admin') {
                throw new Exception('Permisos insuficientes');
            }
            
            $titulo = trim($_POST['titulo'] ?? '');
            $mensaje = trim($_POST['mensaje'] ?? '');
            $tipo = $_POST['tipo'] ?? 'info';
            $para_todos = isset($_POST['para_todos']) && $_POST['para_todos'] === '1';
            $usuario_destino = !$para_todos ? (int)($_POST['usuario_destino'] ?? 0) : null;
            
            if (empty($titulo) || empty($mensaje)) {
                throw new Exception('Título y mensaje son requeridos');
            }
            
            if (!in_array($tipo, ['info', 'success', 'warning', 'error'])) {
                $tipo = 'info';
            }
            
            $target_user_id = $para_todos ? null : ($usuario_destino ?: $user_id);
            
            $query = "INSERT INTO notificaciones (titulo, mensaje, tipo, usuario_id) VALUES (?, ?, ?, ?)";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("sssi", $titulo, $mensaje, $tipo, $target_user_id);
            $stmt->execute();
            
            $notification_id = $mysqli->insert_id;
            
            echo json_encode([
                'success' => true, 
                'message' => 'Notificación creada exitosamente',
                'notification_id' => $notification_id
            ]);
            break;
            
        case 'auto_create_pending_visitor':
            // Crear notificación automática para visitante pendiente
            $visitor_name = trim($_POST['visitor_name'] ?? '');
            $visitor_id = (int)($_POST['visitor_id'] ?? 0);
            
            if (empty($visitor_name) || $visitor_id <= 0) {
                throw new Exception('Datos del visitante requeridos');
            }
            
            $titulo = "Nuevo visitante registrado";
            $mensaje = "El visitante {$visitor_name} está pendiente de aprobación (ID: {$visitor_id})";
            $tipo = 'info';
            
            $query = "INSERT INTO notificaciones (titulo, mensaje, tipo, usuario_id) VALUES (?, ?, ?, NULL)";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("sss", $titulo, $mensaje, $tipo);
            $stmt->execute();
            
            echo json_encode(['success' => true, 'message' => 'Notificación automática creada']);
            break;
            
        default:
            throw new Exception('Acción no válida');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    error_log("Error en API notificaciones: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    error_log("Error SQL en API notificaciones: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Error de base de datos']);
}
?>