<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Verificar autenticación
if (empty($_SESSION['user']['logged_in'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit();
}

$notifications_file = __DIR__ . '/data/notifications.json';
$notifications_dir = dirname($notifications_file);

// Crear directorio si no existe
if (!file_exists($notifications_dir)) {
    mkdir($notifications_dir, 0755, true);
}

// Función para cargar notificaciones
function loadNotifications($file) {
    if (!file_exists($file)) {
        // Crear archivo vacío si no existe
        file_put_contents($file, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
        return [];
    }
    
    $content = file_get_contents($file);
    if ($content === false || empty(trim($content))) {
        return [];
    }
    
    $data = json_decode($content, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Error decodificando JSON de notificaciones: " . json_last_error_msg());
        // Si el JSON está corrupto, crear uno nuevo
        file_put_contents($file, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
        return [];
    }
    
    return is_array($data) ? $data : [];
}

// Función para guardar notificaciones
function saveNotifications($file, $notifications) {
    $result = file_put_contents($file, json_encode($notifications, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
    
    if ($result === false) {
        throw new Exception('Error al guardar notificaciones');
    }
    
    return true;
}

try {
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    $user_id = $_SESSION['user']['id'] ?? 0;
    $user_role = $_SESSION['user']['role'] ?? 'user';
    
    $notifications = loadNotifications($notifications_file);
    
    switch ($action) {
        case 'get':
            // Obtener notificaciones del usuario (todas las globales y las personales)
            $user_notifications = array_filter($notifications, function($n) use ($user_id) {
                return $n['usuario_id'] === null || $n['usuario_id'] == $user_id;
            });
            
            // Ordenar por fecha descendente
            usort($user_notifications, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });
            
            // Limitar a 50
            $user_notifications = array_slice($user_notifications, 0, 50);
            
            echo json_encode([
                'success' => true, 
                'notifications' => array_values($user_notifications)
            ], JSON_UNESCAPED_UNICODE);
            break;
            
        case 'count_unread':
            // Contar notificaciones no leídas
            $unread = array_filter($notifications, function($n) use ($user_id) {
                return ($n['usuario_id'] === null || $n['usuario_id'] == $user_id) && 
                       !($n['leida'] ?? false);
            });
            
            echo json_encode([
                'success' => true, 
                'count' => count($unread)
            ]);
            break;
            
        case 'mark_read':
            // Marcar notificación como leída
            if (!isset($_POST['notification_id'])) {
                throw new Exception('ID de notificación requerido');
            }
            
            $notification_id = intval($_POST['notification_id']);
            $found = false;
            
            foreach ($notifications as &$notification) {
                if ($notification['id'] == $notification_id) {
                    // Verificar que el usuario tenga acceso
                    if ($notification['usuario_id'] !== null && $notification['usuario_id'] != $user_id) {
                        throw new Exception('No autorizado');
                    }
                    
                    $notification['leida'] = true;
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                throw new Exception('Notificación no encontrada');
            }
            
            saveNotifications($notifications_file, $notifications);
            echo json_encode(['success' => true, 'message' => 'Notificación marcada como leída'], JSON_UNESCAPED_UNICODE);
            break;
            
        case 'mark_all_read':
            // Marcar todas como leídas
            foreach ($notifications as &$notification) {
                if (($notification['usuario_id'] === null || $notification['usuario_id'] == $user_id) && 
                    !($notification['leida'] ?? false)) {
                    $notification['leida'] = true;
                }
            }
            
            saveNotifications($notifications_file, $notifications);
            echo json_encode(['success' => true, 'message' => 'Todas las notificaciones marcadas como leídas'], JSON_UNESCAPED_UNICODE);
            break;
            
        case 'delete':
            // Eliminar notificación
            if (!isset($_POST['notification_id'])) {
                throw new Exception('ID de notificación requerido');
            }
            
            $notification_id = intval($_POST['notification_id']);
            $found = false;
            
            $notifications = array_filter($notifications, function($n) use ($notification_id, $user_id, &$found) {
                if ($n['id'] == $notification_id) {
                    // Solo permitir eliminar notificaciones personales o si es admin
                    if ($n['usuario_id'] == $user_id || $_SESSION['user']['role'] === 'admin') {
                        $found = true;
                        return false; // Eliminar
                    } else {
                        throw new Exception('No autorizado para eliminar esta notificación');
                    }
                }
                return true; // Mantener
            });
            
            if (!$found) {
                throw new Exception('Notificación no encontrada');
            }
            
            // Re-indexar array
            $notifications = array_values($notifications);
            saveNotifications($notifications_file, $notifications);
            
            echo json_encode(['success' => true, 'message' => 'Notificación eliminada'], JSON_UNESCAPED_UNICODE);
            break;
            
        case 'create':
            // Crear notificación (solo para administradores)
            if ($user_role !== 'admin' && $user_role !== 'super_admin') {
                throw new Exception('Permisos insuficientes');
            }
            
            $titulo = trim($_POST['titulo'] ?? '');
            $mensaje = trim($_POST['mensaje'] ?? '');
            $tipo = $_POST['tipo'] ?? 'info';
            $para_todos = isset($_POST['para_todos']) && $_POST['para_todos'] === '1';
            
            if (empty($titulo) || empty($mensaje)) {
                throw new Exception('Título y mensaje son requeridos');
            }
            
            if (!in_array($tipo, ['info', 'success', 'warning', 'error'])) {
                $tipo = 'info';
            }
            
            // Generar ID único
            $max_id = 0;
            foreach ($notifications as $n) {
                if ($n['id'] > $max_id) {
                    $max_id = $n['id'];
                }
            }
            $new_id = $max_id + 1;
            
            $new_notification = [
                'id' => $new_id,
                'titulo' => $titulo,
                'mensaje' => $mensaje,
                'tipo' => $tipo,
                'usuario_id' => $para_todos ? null : $user_id,
                'leida' => false,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $notifications[] = $new_notification;
            saveNotifications($notifications_file, $notifications);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Notificación creada exitosamente',
                'notification_id' => $new_id
            ], JSON_UNESCAPED_UNICODE);
            break;
            
        case 'auto_create_pending_visitor':
            // Crear notificación automática para visitante pendiente
            $visitor_name = trim($_POST['visitor_name'] ?? '');
            $visitor_id = intval($_POST['visitor_id'] ?? 0);
            
            if (empty($visitor_name) || $visitor_id <= 0) {
                throw new Exception('Datos del visitante requeridos');
            }
            
            // Generar ID único
            $max_id = 0;
            foreach ($notifications as $n) {
                if ($n['id'] > $max_id) {
                    $max_id = $n['id'];
                }
            }
            $new_id = $max_id + 1;
            
            $new_notification = [
                'id' => $new_id,
                'titulo' => 'Nuevo visitante registrado',
                'mensaje' => "El visitante {$visitor_name} está pendiente de aprobación (ID: {$visitor_id})",
                'tipo' => 'info',
                'usuario_id' => null, // Global
                'leida' => false,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $notifications[] = $new_notification;
            saveNotifications($notifications_file, $notifications);
            
            echo json_encode(['success' => true, 'message' => 'Notificación automática creada'], JSON_UNESCAPED_UNICODE);
            break;
            
        default:
            throw new Exception('Acción no válida');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    error_log("Error en API notificaciones: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
