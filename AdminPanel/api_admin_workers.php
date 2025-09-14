<?php
// api_admin_workers.php
session_start();

// Verificar permisos de administrador
if (empty($_SESSION['user']['logged_in']) || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit();
}

$GLOBALS['allowed_config_access'] = true;
require __DIR__ . '/config/config.php';
require_once 'includes/db.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'approve_time':
            $time_id = intval($_POST['time_id'] ?? 0);
            if ($time_id <= 0) {
                throw new Exception('ID de tiempo inválido');
            }
            
            $query = "UPDATE tiempo_trabajado SET estado = 'aprobado', fecha_aprobacion = NOW(), aprobado_por = ? WHERE id = ? AND estado = 'pendiente'";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param('ii', $_SESSION['user']['id'], $time_id);
            
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Tiempo aprobado exitosamente']);
            } else {
                throw new Exception('No se pudo aprobar el tiempo o ya fue procesado');
            }
            break;
            
        case 'reject_time':
            $time_id = intval($_POST['time_id'] ?? 0);
            $reason = trim($_POST['reason'] ?? '');
            
            if ($time_id <= 0) {
                throw new Exception('ID de tiempo inválido');
            }
            
            $query = "UPDATE tiempo_trabajado SET estado = 'rechazado', fecha_rechazo = NOW(), rechazado_por = ?, razon_rechazo = ? WHERE id = ? AND estado = 'pendiente'";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param('isi', $_SESSION['user']['id'], $reason, $time_id);
            
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Tiempo rechazado']);
            } else {
                throw new Exception('No se pudo rechazar el tiempo o ya fue procesado');
            }
            break;
            
        case 'approve_all_time':
            $query = "UPDATE tiempo_trabajado SET estado = 'aprobado', fecha_aprobacion = NOW(), aprobado_por = ? WHERE estado = 'pendiente'";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param('i', $_SESSION['user']['id']);
            
            if ($stmt->execute()) {
                $approved_count = $stmt->affected_rows;
                echo json_encode(['success' => true, 'approved_count' => $approved_count]);
            } else {
                throw new Exception('Error al aprobar los tiempos');
            }
            break;
            
        case 'approve_report':
            $report_id = intval($_POST['report_id'] ?? 0);
            if ($report_id <= 0) {
                throw new Exception('ID de reporte inválido');
            }
            
            $query = "UPDATE reportes_pdf SET estado = 'aprobado', fecha_aprobacion = NOW(), aprobado_por = ? WHERE id = ? AND estado = 'pendiente'";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param('ii', $_SESSION['user']['id'], $report_id);
            
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Reporte aprobado exitosamente']);
            } else {
                throw new Exception('No se pudo aprobar el reporte o ya fue procesado');
            }
            break;
            
        case 'reject_report':
            $report_id = intval($_POST['report_id'] ?? 0);
            $reason = trim($_POST['reason'] ?? '');
            
            if ($report_id <= 0) {
                throw new Exception('ID de reporte inválido');
            }
            
            $query = "UPDATE reportes_pdf SET estado = 'rechazado', fecha_rechazo = NOW(), rechazado_por = ?, razon_rechazo = ? WHERE id = ? AND estado = 'pendiente'";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param('isi', $_SESSION['user']['id'], $reason, $report_id);
            
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Reporte rechazado']);
            } else {
                throw new Exception('No se pudo rechazar el reporte o ya fue procesado');
            }
            break;
            
        case 'download_report':
            $report_id = intval($_GET['report_id'] ?? 0);
            if ($report_id <= 0) {
                throw new Exception('ID de reporte inválido');
            }
            
            $query = "SELECT nombre_archivo, ruta_archivo FROM reportes_pdf WHERE id = ?";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param('i', $report_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                $file_path = $row['ruta_archivo'];
                $file_name = $row['nombre_archivo'];
                
                if (file_exists($file_path)) {
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment; filename="' . $file_name . '"');
                    header('Content-Length: ' . filesize($file_path));
                    readfile($file_path);
                    exit();
                } else {
                    throw new Exception('Archivo no encontrado');
                }
            } else {
                throw new Exception('Reporte no encontrado');
            }
            break;
            
        case 'get_time_details':
            $time_id = intval($_GET['time_id'] ?? 0);
            if ($time_id <= 0) {
                throw new Exception('ID de tiempo inválido');
            }
            
            $query = "SELECT tt.*, u.name as trabajador_nombre 
                     FROM tiempo_trabajado tt 
                     JOIN usuarios u ON tt.trabajador_id = u.id 
                     WHERE tt.id = ?";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param('i', $time_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                echo json_encode(['success' => true, 'time_record' => $row]);
            } else {
                throw new Exception('Registro de tiempo no encontrado');
            }
            break;
            
        case 'get_worker_stats':
            // Obtener estadísticas de trabajadores
            $stats = [];
            
            // Trabajadores activos
            $query = "SELECT COUNT(*) as total FROM usuarios WHERE rol = 'trabajador' AND activo = 1";
            $result = $mysqli->query($query);
            $stats['total_trabajadores'] = $result->fetch_assoc()['total'];
            
            // Horas pendientes
            $query = "SELECT COUNT(*) as total FROM tiempo_trabajado WHERE estado = 'pendiente'";
            $result = $mysqli->query($query);
            $stats['horas_pendientes'] = $result->fetch_assoc()['total'];
            
            // Reportes pendientes
            $query = "SELECT COUNT(*) as total FROM reportes_pdf WHERE estado = 'pendiente'";
            $result = $mysqli->query($query);
            $stats['reportes_pendientes'] = $result->fetch_assoc()['total'];
            
            // Total horas este mes
            $query = "SELECT COALESCE(SUM(horas), 0) as total 
                     FROM tiempo_trabajado 
                     WHERE MONTH(fecha_inicio) = MONTH(CURDATE()) 
                     AND YEAR(fecha_inicio) = YEAR(CURDATE())
                     AND estado = 'aprobado'";
            $result = $mysqli->query($query);
            $stats['total_horas_mes'] = floatval($result->fetch_assoc()['total']);
            
            echo json_encode(['success' => true, 'stats' => $stats]);
            break;
            
        case 'get_pending_time':
            // Obtener tiempo pendiente con límite
            $limit = intval($_GET['limit'] ?? 10);
            
            $query = "SELECT tt.id, tt.descripcion, tt.horas, tt.fecha_inicio, tt.estado,
                             u.name as trabajador_nombre, u.id as trabajador_id
                      FROM tiempo_trabajado tt
                      JOIN usuarios u ON tt.trabajador_id = u.id
                      WHERE tt.estado = 'pendiente'
                      ORDER BY tt.fecha_inicio DESC
                      LIMIT ?";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param('i', $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $tiempo_pendiente = [];
            while ($row = $result->fetch_assoc()) {
                $tiempo_pendiente[] = [
                    'id' => $row['id'],
                    'descripcion' => $row['descripcion'],
                    'horas' => floatval($row['horas']),
                    'fecha_inicio' => $row['fecha_inicio'],
                    'trabajador_nombre' => $row['trabajador_nombre'],
                    'trabajador_id' => $row['trabajador_id'],
                    'estado' => $row['estado']
                ];
            }
            
            echo json_encode(['success' => true, 'tiempo_pendiente' => $tiempo_pendiente]);
            break;
            
        case 'get_pending_reports':
            // Obtener reportes pendientes con límite
            $limit = intval($_GET['limit'] ?? 10);
            
            $query = "SELECT rp.id, rp.titulo, rp.descripcion, rp.fecha_envio, rp.nombre_archivo,
                             u.name as trabajador_nombre, u.id as trabajador_id
                      FROM reportes_pdf rp
                      JOIN usuarios u ON rp.trabajador_id = u.id
                      WHERE rp.estado = 'pendiente'
                      ORDER BY rp.fecha_envio DESC
                      LIMIT ?";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param('i', $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $reportes_pendientes = [];
            while ($row = $result->fetch_assoc()) {
                $reportes_pendientes[] = [
                    'id' => $row['id'],
                    'titulo' => $row['titulo'],
                    'descripcion' => $row['descripcion'],
                    'fecha_envio' => $row['fecha_envio'],
                    'nombre_archivo' => $row['nombre_archivo'],
                    'trabajador_nombre' => $row['trabajador_nombre'],
                    'trabajador_id' => $row['trabajador_id']
                ];
            }
            
            echo json_encode(['success' => true, 'reportes_pendientes' => $reportes_pendientes]);
            break;
            
        default:
            throw new Exception('Acción no válida');
    }
    
} catch (Exception $e) {
    error_log("Error en api_admin_workers.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>