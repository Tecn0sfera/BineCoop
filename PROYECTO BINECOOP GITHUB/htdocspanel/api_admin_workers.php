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
            $report_id = trim($_POST['report_id'] ?? '');
            if (empty($report_id)) {
                throw new Exception('ID de reporte inválido');
            }
            
            require_once __DIR__ . '/includes/comprobantes_db.php';
            
            if (approveComprobante($report_id, $_SESSION['user']['id'])) {
                echo json_encode(['success' => true, 'message' => 'Reporte aprobado exitosamente']);
            } else {
                throw new Exception('No se pudo aprobar el reporte. Puede que ya esté procesado o no exista.');
            }
            break;
            
        case 'reject_report':
            $report_id = trim($_POST['report_id'] ?? '');
            $reason = trim($_POST['reason'] ?? '');
            
            if (empty($report_id)) {
                throw new Exception('ID de reporte inválido');
            }
            
            require_once __DIR__ . '/includes/comprobantes_db.php';
            
            if (rejectComprobante($report_id, $_SESSION['user']['id'], $reason)) {
                echo json_encode(['success' => true, 'message' => 'Reporte rechazado']);
            } else {
                throw new Exception('No se pudo rechazar el reporte. Puede que ya esté procesado o no exista.');
            }
            break;
            
        case 'download_report':
            $report_id = trim($_GET['report_id'] ?? '');
            if (empty($report_id)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'ID de comprobante inválido']);
                exit();
            }
            
            require_once __DIR__ . '/includes/comprobantes_db.php';
            
            $comprobante = getComprobanteById($report_id);
            
            if (!$comprobante) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Comprobante no encontrado']);
                exit();
            }
            
            // Como htdocscop y htdocspanel están en diferentes volúmenes,
            // usar el endpoint proxy en htdocscop para servir el archivo
            // Detectar la URL de htdocscop de forma inteligente
            
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $current_url = $protocol . '://' . $host;
            
            // Intentar diferentes formas de construir la URL de htdocscop
            $htdocscop_base = '';
            
            // Opción 1: Si el host contiene "htdocspanel", reemplazar por "htdocscop"
            if (strpos($host, 'htdocspanel') !== false) {
                $htdocscop_base = str_replace('htdocspanel', 'htdocscop', $current_url);
            }
            // Opción 2: Si el host contiene "admin", reemplazar por "worker" o "cop"
            elseif (strpos($host, 'admin') !== false) {
                $htdocscop_base = str_replace('admin', 'worker', $current_url);
                // Si no funciona, intentar con "cop"
                if (empty($htdocscop_base)) {
                    $htdocscop_base = str_replace('admin', 'cop', $current_url);
                }
            }
            // Opción 3: Intentar como subdirectorio
            else {
                // Construir URL relativa al mismo dominio
                $current_path = dirname($_SERVER['SCRIPT_NAME']);
                $htdocscop_base = $current_url . str_replace('/htdocspanel', '/htdocscop', $current_path);
            }
            
            // Si aún no tenemos una URL, usar una ruta relativa
            if (empty($htdocscop_base)) {
                // Intentar construir desde la estructura del proyecto
                $script_dir = dirname($_SERVER['SCRIPT_NAME']);
                $htdocscop_base = $current_url . str_replace('htdocspanel', 'htdocscop', $script_dir);
            }
            
            // Construir la URL completa del endpoint proxy
            $proxy_url = rtrim($htdocscop_base, '/') . '/serve_pdf.php?id=' . urlencode($report_id);
            
            error_log("DEBUG download_report: Redirigiendo a proxy URL: " . $proxy_url);
            
            // Redirigir al endpoint proxy
            header('Location: ' . $proxy_url);
            exit();
            break;
            
        case 'get_time_details':
            $time_id = intval($_GET['time_id'] ?? 0);
            if ($time_id <= 0) {
                throw new Exception('ID de tiempo inválido');
            }
            
            $query = "SELECT tt.*, COALESCE(u.username, v.nombre, CONCAT('Trabajador ', tt.trabajador_id)) as trabajador_nombre
                      FROM tiempo_trabajado tt
                      LEFT JOIN usuarios u ON tt.trabajador_id = u.id
                      LEFT JOIN visitantes v ON tt.trabajador_id = v.id 
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
            require_once __DIR__ . '/includes/comprobantes_db.php';
            $stats['reportes_pendientes'] = countComprobantesByEstado('pendiente');
            
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
                             COALESCE(u.username, v.nombre, CONCAT('Trabajador ', tt.trabajador_id)) as trabajador_nombre,
                             tt.trabajador_id as trabajador_id
                      FROM tiempo_trabajado tt
                      LEFT JOIN usuarios u ON tt.trabajador_id = u.id
                      LEFT JOIN visitantes v ON tt.trabajador_id = v.id
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
            
            echo json_encode(['success' => true, 'time_records' => $tiempo_pendiente]);
            break;
            
        case 'get_pending_reports':
            // Obtener reportes pendientes con límite
            $limit = intval($_GET['limit'] ?? 10);
            
            require_once __DIR__ . '/includes/comprobantes_db.php';
            
            $comprobantes = getComprobantesPendientes($limit);
            
            // Obtener nombres de trabajadores
            $reportes_pendientes = [];
            foreach ($comprobantes as $comp) {
                $trabajador_id = $comp['trabajador_id'];
                
                // Intentar obtener nombre del trabajador
                $trabajador_nombre = 'Trabajador ' . $trabajador_id;
                
                // Buscar en usuarios
                $query = "SELECT username FROM usuarios WHERE id = ?";
                $stmt = $mysqli->prepare($query);
                $stmt->bind_param('i', $trabajador_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    $trabajador_nombre = $row['username'];
                }
                $stmt->close();
                
                // Si no se encontró, buscar en visitantes
                if ($trabajador_nombre === 'Trabajador ' . $trabajador_id) {
                    $query = "SELECT nombre FROM visitantes WHERE id = ?";
                    $stmt = $mysqli->prepare($query);
                    $stmt->bind_param('i', $trabajador_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($row = $result->fetch_assoc()) {
                        $trabajador_nombre = $row['nombre'];
                    }
                    $stmt->close();
                }
                
                $reportes_pendientes[] = [
                    'id' => $comp['id'],
                    'titulo' => $comp['titulo'],
                    'descripcion' => $comp['descripcion'] ?? '',
                    'fecha_envio' => $comp['fecha_envio'],
                    'nombre_archivo' => $comp['nombre_archivo'],
                    'trabajador_nombre' => $trabajador_nombre,
                    'trabajador_id' => $trabajador_id
                ];
            }
            
            echo json_encode(['success' => true, 'reports' => $reportes_pendientes]);
            break;
            
        default:
            throw new Exception('Acción no válida');
    }
    
} catch (Exception $e) {
    error_log("Error en api_admin_workers.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>