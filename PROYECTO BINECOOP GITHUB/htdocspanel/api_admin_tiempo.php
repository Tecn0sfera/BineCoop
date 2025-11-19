<?php
session_start();

// Configuración de errores
$is_development = true; // Cambiar a false en producción
$is_ajax_request = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                   strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($is_development && !$is_ajax_request) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL);
}

// Headers para JSON
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// Función para respuesta JSON
function sendJsonResponse($data) {
    if (ob_get_level()) {
        ob_clean();
    }
    echo json_encode($data);
    exit;
}

// Función para logging
function logAdminAction($message) {
    error_log("[ADMIN API] " . date('Y-m-d H:i:s') . " - " . $message);
}

try {
    // Verificar método de petición
    if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST', 'PUT', 'DELETE'])) {
        throw new Exception('Método no permitido');
    }

    // Verificar sesión y permisos de admin
    if (empty($_SESSION['user']['logged_in']) || $_SESSION['user']['role'] !== 'admin') {
        http_response_code(403);
        sendJsonResponse([
            'success' => false, 
            'error' => 'Acceso no autorizado. Se requieren permisos de administrador.'
        ]);
    }

    // Cargar dependencias
    $GLOBALS['allowed_config_access'] = true;
    if (!file_exists(__DIR__ . '/config.php')) {
        throw new Exception('Archivo de configuración no encontrado');
    }
    require_once __DIR__ . '/config.php';
    
    if (!file_exists(__DIR__ . '/includes/db.php')) {
        // Intentar ruta alternativa
        if (!file_exists(__DIR__ . '/db.php')) {
            throw new Exception('Archivo de base de datos no encontrado');
        }
        require_once __DIR__ . '/db.php';
    } else {
        require_once __DIR__ . '/includes/db.php';
    }

    // Verificar conexión a BD
    if (!isset($mysqli) || $mysqli->connect_error) {
        throw new Exception('Error de conexión a la base de datos');
    }

    // Obtener parámetros según el método
    $params = [];
    switch($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            $params = $_GET;
            break;
        case 'POST':
            $params = $_POST;
            break;
        case 'PUT':
        case 'DELETE':
            parse_str(file_get_contents('php://input'), $params);
            break;
    }

    $action = trim($params['action'] ?? '');
    $id = filter_var($params['id'] ?? 0, FILTER_VALIDATE_INT);

    if (empty($action)) {
        throw new Exception('Acción no especificada');
    }

    // Obtener información del admin
    $admin_id = $_SESSION['user']['id'] ?? 0;
    $admin_name = $_SESSION['user']['name'] ?? 'Admin';

    logAdminAction("Admin $admin_name (ID: $admin_id) ejecutando acción: $action");

    switch($action) {
        case 'approve':
            handleApproveTime($mysqli, $id, $admin_id);
            break;
            
        case 'reject':
            handleRejectTime($mysqli, $id, $admin_id, $params);
            break;
            
        case 'get_pending':
            handleGetPendingTimes($mysqli);
            break;
            
        case 'get_all':
            handleGetAllTimes($mysqli, $params);
            break;
            
        case 'get_worker_times':
            handleGetWorkerTimes($mysqli, $params);
            break;
            
        case 'update_time':
            handleUpdateTime($mysqli, $id, $params, $admin_id);
            break;
            
        case 'delete_time':
            handleDeleteTime($mysqli, $id, $admin_id);
            break;
            
        case 'bulk_action':
            handleBulkAction($mysqli, $params, $admin_id);
            break;
            
        case 'get_stats':
            handleGetStats($mysqli, $params);
            break;
            
        default:
            throw new Exception('Acción no válida: ' . $action);
    }

} catch (Exception $e) {
    logAdminAction("Error: " . $e->getMessage());
    http_response_code(500);
    sendJsonResponse([
        'success' => false,
        'error' => $e->getMessage(),
        'debug' => $is_development ? [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ] : null
    ]);
}

// Funciones de manejo de acciones
function handleApproveTime($mysqli, $id, $admin_id) {
    if (!$id) {
        throw new Exception('ID inválido para aprobar');
    }

    // Verificar que el registro existe y está pendiente
    $check_stmt = $mysqli->prepare("SELECT trabajador_id, descripcion, horas, estado FROM tiempo_trabajado WHERE id = ?");
    $check_stmt->bind_param('i', $id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if (!$result || $result->num_rows === 0) {
        throw new Exception('Registro de tiempo no encontrado');
    }
    
    $record = $result->fetch_assoc();
    $check_stmt->close();
    
    if ($record['estado'] !== 'pendiente') {
        throw new Exception('Solo se pueden aprobar registros pendientes. Estado actual: ' . $record['estado']);
    }

    // Iniciar transacción
    $mysqli->begin_transaction();
    
    try {
        // Actualizar estado
        $stmt = $mysqli->prepare("UPDATE tiempo_trabajado SET estado = 'aprobado', fecha_aprobacion = NOW(), aprobado_por = ? WHERE id = ?");
        $stmt->bind_param('ii', $admin_id, $id);
        
        if (!$stmt->execute()) {
            throw new Exception('Error al aprobar registro: ' . $stmt->error);
        }
        
        $stmt->close();
        
        // Registrar en log de admin (si existe la tabla)
        $log_stmt = $mysqli->prepare("INSERT INTO admin_logs (admin_id, action, target_table, target_id, description, fecha) VALUES (?, 'approve', 'tiempo_trabajado', ?, ?, NOW())");
        $description = "Aprobó " . $record['horas'] . "h para trabajador ID " . $record['trabajador_id'];
        $log_stmt->bind_param('iis', $admin_id, $id, $description);
        $log_stmt->execute(); // No fallar si la tabla no existe
        $log_stmt->close();
        
        $mysqli->commit();
        
        logAdminAction("Registro de tiempo ID $id aprobado exitosamente");
        
        sendJsonResponse([
            'success' => true,
            'message' => 'Registro aprobado exitosamente',
            'data' => [
                'id' => $id,
                'new_status' => 'aprobado',
                'worker_id' => $record['trabajador_id'],
                'hours' => $record['horas']
            ]
        ]);
        
    } catch (Exception $e) {
        $mysqli->rollback();
        throw $e;
    }
}

function handleRejectTime($mysqli, $id, $admin_id, $params) {
    if (!$id) {
        throw new Exception('ID inválido para rechazar');
    }

    $reason = trim($params['reason'] ?? '');
    
    // Verificar que el registro existe
    $check_stmt = $mysqli->prepare("SELECT trabajador_id, descripcion, horas, estado FROM tiempo_trabajado WHERE id = ?");
    $check_stmt->bind_param('i', $id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if (!$result || $result->num_rows === 0) {
        throw new Exception('Registro de tiempo no encontrado');
    }
    
    $record = $result->fetch_assoc();
    $check_stmt->close();
    
    if ($record['estado'] !== 'pendiente') {
        throw new Exception('Solo se pueden rechazar registros pendientes. Estado actual: ' . $record['estado']);
    }

    // Iniciar transacción
    $mysqli->begin_transaction();
    
    try {
        // Actualizar estado con razón de rechazo
        $stmt = $mysqli->prepare("UPDATE tiempo_trabajado SET estado = 'rechazado', fecha_rechazo = NOW(), rechazado_por = ?, razon_rechazo = ? WHERE id = ?");
        $stmt->bind_param('isi', $admin_id, $reason, $id);
        
        if (!$stmt->execute()) {
            throw new Exception('Error al rechazar registro: ' . $stmt->error);
        }
        
        $stmt->close();
        
        // Log de admin
        $log_stmt = $mysqli->prepare("INSERT INTO admin_logs (admin_id, action, target_table, target_id, description, fecha) VALUES (?, 'reject', 'tiempo_trabajado', ?, ?, NOW())");
        $description = "Rechazó " . $record['horas'] . "h para trabajador ID " . $record['trabajador_id'] . ". Razón: " . $reason;
        $log_stmt->bind_param('iis', $admin_id, $id, $description);
        $log_stmt->execute();
        $log_stmt->close();
        
        $mysqli->commit();
        
        logAdminAction("Registro de tiempo ID $id rechazado. Razón: $reason");
        
        sendJsonResponse([
            'success' => true,
            'message' => 'Registro rechazado exitosamente',
            'data' => [
                'id' => $id,
                'new_status' => 'rechazado',
                'reason' => $reason,
                'worker_id' => $record['trabajador_id']
            ]
        ]);
        
    } catch (Exception $e) {
        $mysqli->rollback();
        throw $e;
    }
}

function handleGetPendingTimes($mysqli) {
    $query = "SELECT tt.id, tt.trabajador_id, tt.descripcion, tt.horas, tt.fecha_inicio, tt.fecha_fin, tt.estado, tt.fecha_creacion,
                     u.name as trabajador_nombre, u.email as trabajador_email
              FROM tiempo_trabajado tt
              LEFT JOIN users u ON tt.trabajador_id = u.id
              WHERE tt.estado = 'pendiente'
              ORDER BY tt.fecha_creacion DESC
              LIMIT 50";
    
    $result = $mysqli->query($query);
    
    if (!$result) {
        throw new Exception('Error al obtener registros pendientes: ' . $mysqli->error);
    }
    
    $records = [];
    while ($row = $result->fetch_assoc()) {
        // Sanitizar datos
        $row['descripcion'] = htmlspecialchars($row['descripcion'], ENT_QUOTES, 'UTF-8');
        $row['trabajador_nombre'] = htmlspecialchars($row['trabajador_nombre'] ?? 'Usuario desconocido', ENT_QUOTES, 'UTF-8');
        $records[] = $row;
    }
    
    sendJsonResponse([
        'success' => true,
        'data' => $records,
        'count' => count($records)
    ]);
}

function handleGetAllTimes($mysqli, $params) {
    // Parámetros de paginación y filtros
    $page = max(1, intval($params['page'] ?? 1));
    $per_page = min(100, max(10, intval($params['per_page'] ?? 20)));
    $offset = ($page - 1) * $per_page;
    
    $status_filter = $params['status'] ?? '';
    $worker_filter = filter_var($params['worker_id'] ?? 0, FILTER_VALIDATE_INT);
    $date_from = $params['date_from'] ?? '';
    $date_to = $params['date_to'] ?? '';
    
    // Construir WHERE clause
    $where_conditions = [];
    $bind_params = [];
    $bind_types = '';
    
    if (!empty($status_filter) && in_array($status_filter, ['pendiente', 'aprobado', 'rechazado'])) {
        $where_conditions[] = "tt.estado = ?";
        $bind_params[] = $status_filter;
        $bind_types .= 's';
    }
    
    if ($worker_filter) {
        $where_conditions[] = "tt.trabajador_id = ?";
        $bind_params[] = $worker_filter;
        $bind_types .= 'i';
    }
    
    if (!empty($date_from)) {
        $where_conditions[] = "DATE(tt.fecha_inicio) >= ?";
        $bind_params[] = $date_from;
        $bind_types .= 's';
    }
    
    if (!empty($date_to)) {
        $where_conditions[] = "DATE(tt.fecha_inicio) <= ?";
        $bind_params[] = $date_to;
        $bind_types .= 's';
    }
    
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // Query principal
    $query = "SELECT tt.id, tt.trabajador_id, tt.descripcion, tt.horas, tt.fecha_inicio, tt.fecha_fin, 
                     tt.estado, tt.fecha_creacion, tt.fecha_aprobacion, tt.fecha_rechazo, tt.razon_rechazo,
                     u.name as trabajador_nombre, u.email as trabajador_email,
                     admin_approve.name as aprobado_por_nombre,
                     admin_reject.name as rechazado_por_nombre
              FROM tiempo_trabajado tt
              LEFT JOIN users u ON tt.trabajador_id = u.id
              LEFT JOIN users admin_approve ON tt.aprobado_por = admin_approve.id
              LEFT JOIN users admin_reject ON tt.rechazado_por = admin_reject.id
              $where_clause
              ORDER BY tt.fecha_creacion DESC
              LIMIT ? OFFSET ?";
    
    // Agregar parámetros de paginación
    $bind_params[] = $per_page;
    $bind_params[] = $offset;
    $bind_types .= 'ii';
    
    $stmt = $mysqli->prepare($query);
    if ($bind_types) {
        $stmt->bind_param($bind_types, ...$bind_params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $records = [];
    while ($row = $result->fetch_assoc()) {
        // Sanitizar datos
        $row['descripcion'] = htmlspecialchars($row['descripcion'], ENT_QUOTES, 'UTF-8');
        $row['trabajador_nombre'] = htmlspecialchars($row['trabajador_nombre'] ?? 'Usuario desconocido', ENT_QUOTES, 'UTF-8');
        $records[] = $row;
    }
    
    $stmt->close();
    
    // Contar total para paginación
    $count_query = "SELECT COUNT(*) as total FROM tiempo_trabajado tt LEFT JOIN users u ON tt.trabajador_id = u.id $where_clause";
    $count_stmt = $mysqli->prepare($count_query);
    if ($bind_types && !empty($where_conditions)) {
        // Remover los parámetros de paginación para el count
        $count_bind_types = substr($bind_types, 0, -2);
        $count_bind_params = array_slice($bind_params, 0, -2);
        if ($count_bind_types) {
            $count_stmt->bind_param($count_bind_types, ...$count_bind_params);
        }
    }
    
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total_records = $count_result->fetch_assoc()['total'];
    $count_stmt->close();
    
    sendJsonResponse([
        'success' => true,
        'data' => $records,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $per_page,
            'total_records' => $total_records,
            'total_pages' => ceil($total_records / $per_page)
        ]
    ]);
}

function handleGetStats($mysqli, $params) {
    $date_from = $params['date_from'] ?? date('Y-m-01'); // Primer día del mes
    $date_to = $params['date_to'] ?? date('Y-m-d'); // Hoy
    
    // Stats generales
    $stats_query = "SELECT 
                        COUNT(*) as total_registros,
                        SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                        SUM(CASE WHEN estado = 'aprobado' THEN 1 ELSE 0 END) as aprobados,
                        SUM(CASE WHEN estado = 'rechazado' THEN 1 ELSE 0 END) as rechazados,
                        SUM(CASE WHEN estado = 'aprobado' THEN horas ELSE 0 END) as horas_aprobadas,
                        AVG(CASE WHEN estado = 'aprobado' THEN horas ELSE NULL END) as promedio_horas
                    FROM tiempo_trabajado 
                    WHERE DATE(fecha_inicio) BETWEEN ? AND ?";
    
    $stmt = $mysqli->prepare($stats_query);
    $stmt->bind_param('ss', $date_from, $date_to);
    $stmt->execute();
    $stats = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    // Top trabajadores por horas
    $top_workers_query = "SELECT u.name, u.id, SUM(tt.horas) as total_horas, COUNT(tt.id) as total_registros
                          FROM tiempo_trabajado tt
                          LEFT JOIN users u ON tt.trabajador_id = u.id
                          WHERE tt.estado = 'aprobado' AND DATE(tt.fecha_inicio) BETWEEN ? AND ?
                          GROUP BY tt.trabajador_id, u.name, u.id
                          ORDER BY total_horas DESC
                          LIMIT 10";
    
    $stmt = $mysqli->prepare($top_workers_query);
    $stmt->bind_param('ss', $date_from, $date_to);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $top_workers = [];
    while ($row = $result->fetch_assoc()) {
        $row['name'] = htmlspecialchars($row['name'] ?? 'Usuario desconocido', ENT_QUOTES, 'UTF-8');
        $top_workers[] = $row;
    }
    $stmt->close();
    
    sendJsonResponse([
        'success' => true,
        'data' => [
            'general_stats' => $stats,
            'top_workers' => $top_workers,
            'period' => [
                'from' => $date_from,
                'to' => $date_to
            ]
        ]
    ]);
}

function handleBulkAction($mysqli, $params, $admin_id) {
    $action = $params['bulk_action'] ?? '';
    $ids = $params['ids'] ?? [];
    
    if (empty($action) || !in_array($action, ['approve', 'reject', 'delete'])) {
        throw new Exception('Acción bulk inválida');
    }
    
    if (empty($ids) || !is_array($ids)) {
        throw new Exception('No se proporcionaron IDs válidos');
    }
    
    // Validar y filtrar IDs
    $valid_ids = array_filter(array_map('intval', $ids), function($id) {
        return $id > 0;
    });
    
    if (empty($valid_ids)) {
        throw new Exception('No se encontraron IDs válidos');
    }
    
    if (count($valid_ids) > 100) {
        throw new Exception('Máximo 100 registros por operación bulk');
    }
    
    $mysqli->begin_transaction();
    
    try {
        $placeholders = str_repeat('?,', count($valid_ids) - 1) . '?';
        $success_count = 0;
        
        switch($action) {
            case 'approve':
                $query = "UPDATE tiempo_trabajado SET estado = 'aprobado', fecha_aprobacion = NOW(), aprobado_por = ? WHERE id IN ($placeholders) AND estado = 'pendiente'";
                $stmt = $mysqli->prepare($query);
                $stmt->bind_param('i' . str_repeat('i', count($valid_ids)), $admin_id, ...$valid_ids);
                break;
                
            case 'reject':
                $reason = $params['bulk_reason'] ?? 'Rechazo masivo por administrador';
                $query = "UPDATE tiempo_trabajado SET estado = 'rechazado', fecha_rechazo = NOW(), rechazado_por = ?, razon_rechazo = ? WHERE id IN ($placeholders) AND estado = 'pendiente'";
                $stmt = $mysqli->prepare($query);
                $stmt->bind_param('is' . str_repeat('i', count($valid_ids)), $admin_id, $reason, ...$valid_ids);
                break;
                
            case 'delete':
                $query = "DELETE FROM tiempo_trabajado WHERE id IN ($placeholders)";
                $stmt = $mysqli->prepare($query);
                $stmt->bind_param(str_repeat('i', count($valid_ids)), ...$valid_ids);
                break;
        }
        
        if ($stmt->execute()) {
            $success_count = $stmt->affected_rows;
        }
        
        $stmt->close();
        
        $mysqli->commit();
        
        logAdminAction("Acción bulk '$action' ejecutada en $success_count registros");
        
        sendJsonResponse([
            'success' => true,
            'message' => "Acción '$action' aplicada a $success_count registros",
            'data' => [
                'action' => $action,
                'affected_records' => $success_count,
                'requested_ids' => count($valid_ids)
            ]
        ]);
        
    } catch (Exception $e) {
        $mysqli->rollback();
        throw $e;
    }
}
?>