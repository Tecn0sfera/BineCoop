<?php
session_start();

// Configuración de errores - Solo para desarrollo
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

// Headers para JSON - DEBE ir antes de cualquier output
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// Función para devolver respuesta JSON y terminar
function sendJsonResponse($data) {
    // Limpiar cualquier output previo
    if (ob_get_level()) {
        ob_clean();
    }
    echo json_encode($data);
    exit;
}

// Función para logging de errores
function logError($message) {
    error_log("[WORKER API] " . date('Y-m-d H:i:s') . " - " . $message);
}

try {
    // Verificar que es una petición POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    // Verificar sesión
    if (empty($_SESSION['user_token']) || empty($_SESSION['user'])) {
        throw new Exception('Sesión no válida. Por favor, inicia sesión nuevamente.');
    }
    
    // Cargar configuración
    $GLOBALS['allowed_config_access'] = true;
    if (!file_exists(__DIR__ . '/config.php')) {
        throw new Exception('Archivo de configuración no encontrado');
    }
    require_once __DIR__ . '/config.php';
    
    if (!file_exists(__DIR__ . '/db.php')) {
        throw new Exception('Archivo de base de datos no encontrado');
    }
    require_once __DIR__ . '/db.php';
    
    // Verificar conexión a base de datos
    if (!isset($mysqli) || $mysqli->connect_error) {
        throw new Exception('Error de conexión a la base de datos: ' . ($mysqli->connect_error ?? 'Desconocido'));
    }
    
    // Obtener información del trabajador
    $current_worker = $_SESSION['user'];
    $worker_id = filter_var($current_worker['id'] ?? 0, FILTER_VALIDATE_INT);
    
    if ($worker_id === false || $worker_id <= 0) {
        throw new Exception('ID de trabajador inválido');
    }
    
    // Obtener y validar acción
    $action = trim($_POST['action'] ?? '');
    if (empty($action)) {
        throw new Exception('Acción no especificada');
    }
    
    logError("Procesando acción: $action para worker ID: $worker_id");
    
    switch ($action) {
        case 'upload_pdf':
            handlePdfUpload($mysqli, $worker_id);
            break;
            
        case 'register_time':
            handleTimeRegister($mysqli, $worker_id);
            break;
            
        default:
            throw new Exception('Acción no válida: ' . $action);
    }
    
} catch (Exception $e) {
    logError("Error: " . $e->getMessage());
    sendJsonResponse([
        'success' => false,
        'error' => $e->getMessage(),
        'debug' => $is_development ? [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ] : null
    ]);
}

function handlePdfUpload($mysqli, $worker_id) {
    // Validar datos del formulario
    $titulo = trim($_POST['titulo'] ?? '');
    if (empty($titulo)) {
        throw new Exception('El título es obligatorio');
    }
    
    if (strlen($titulo) > 255) {
        throw new Exception('El título es demasiado largo (máximo 255 caracteres)');
    }
    
    $descripcion = trim($_POST['descripcion'] ?? '');
    if (strlen($descripcion) > 1000) {
        throw new Exception('La descripción es demasiado larga (máximo 1000 caracteres)');
    }
    
    // Validar archivo subido
    if (!isset($_FILES['pdf_file'])) {
        throw new Exception('No se recibió ningún archivo');
    }
    
    $file = $_FILES['pdf_file'];
    
    // Verificar errores de upload
    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new Exception('El archivo es demasiado grande');
        case UPLOAD_ERR_PARTIAL:
            throw new Exception('El archivo se subió parcialmente');
        case UPLOAD_ERR_NO_FILE:
            throw new Exception('No se seleccionó ningún archivo');
        case UPLOAD_ERR_NO_TMP_DIR:
            throw new Exception('No se encontró la carpeta temporal');
        case UPLOAD_ERR_CANT_WRITE:
            throw new Exception('No se pudo escribir el archivo al disco');
        default:
            throw new Exception('Error desconocido al subir archivo');
    }
    
    // Validar tipo de archivo
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if ($mime_type !== 'application/pdf') {
        throw new Exception('Solo se permiten archivos PDF. Tipo detectado: ' . $mime_type);
    }
    
    // Validar tamaño (10MB máximo)
    $max_size = 10 * 1024 * 1024; // 10MB
    if ($file['size'] > $max_size) {
        throw new Exception('El archivo es demasiado grande. Máximo permitido: 10MB');
    }
    
    // Validar nombre del archivo
    $original_name = basename($file['name']);
    if (strlen($original_name) > 255) {
        throw new Exception('El nombre del archivo es demasiado largo');
    }
    
    // Crear directorio si no existe
    $upload_dir = __DIR__ . '/uploads/reports/';
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            throw new Exception('No se pudo crear el directorio de uploads');
        }
    }
    
    // Verificar permisos de escritura
    if (!is_writable($upload_dir)) {
        throw new Exception('El directorio de uploads no tiene permisos de escritura');
    }
    
    // Generar nombre único para el archivo
    $file_extension = '.pdf';
    $safe_filename = 'report_' . $worker_id . '_' . date('Y-m-d_H-i-s') . '_' . uniqid() . $file_extension;
    $file_path = $upload_dir . $safe_filename;
    
    // Mover archivo
    if (!move_uploaded_file($file['tmp_name'], $file_path)) {
        throw new Exception('No se pudo guardar el archivo en el servidor');
    }
    
    // Cargar comprobantes_db.php para usar la misma lógica que api_worker.php
    require_once __DIR__ . '/includes/comprobantes_db.php';
    
    // Verificar que tenemos conexión después de cargar comprobantes_db.php
    if (!isset($mysqli)) {
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        throw new Exception('Error: No hay conexión mysqli después de cargar comprobantes_db.php');
    }
    
    // Preparar datos del comprobante (igual que api_worker.php)
    $comprobante_data = [
        'trabajador_id' => $worker_id,
        'titulo' => $titulo,
        'descripcion' => $descripcion,
        'nombre_archivo' => $original_name,
        'ruta_archivo' => $file_path,
        'tamano_archivo' => $file['size']
    ];
    
    logError("Intentando crear comprobante para trabajador ID: $worker_id");
    logError("Datos del comprobante - Título: $titulo, Archivo: $original_name, Tamaño: {$file['size']} bytes");
    
    // Crear comprobante usando la función que funciona
    $comprobante_id = createComprobante($comprobante_data);
    
    if ($comprobante_id) {
        // Verificar que realmente se guardó
        $verify = $mysqli->query("SELECT COUNT(*) as total FROM comprobantes_pago WHERE id = '" . $mysqli->real_escape_string($comprobante_id) . "'");
        $verify_row = $verify ? $verify->fetch_assoc() : ['total' => 0];
        
        if ($verify_row['total'] > 0) {
            logError("PDF subido exitosamente. Comprobante ID: $comprobante_id");
            
            sendJsonResponse([
                'success' => true,
                'message' => 'Comprobante de pago subido exitosamente',
                'data' => [
                    'report_id' => $comprobante_id,
                    'titulo' => $titulo,
                    'filename' => $original_name,
                    'size' => $file['size']
                ]
            ]);
        } else {
            logError("ERROR: Comprobante creado pero NO encontrado en la base de datos. ID: $comprobante_id");
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            throw new Exception('Error: El comprobante no se guardó correctamente. Contacte al administrador.');
        }
    } else {
        // Eliminar archivo si falla el guardado
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        logError("ERROR: createComprobante retornó false. Verificar logs de createComprobante y saveComprobante.");
        throw new Exception('Error al guardar información del comprobante en la base de datos');
    }
}

function handleTimeRegister($mysqli, $worker_id) {
    // Validar datos del formulario
    $descripcion = trim($_POST['descripcion'] ?? '');
    if (empty($descripcion)) {
        throw new Exception('La descripción es obligatoria');
    }
    
    if (strlen($descripcion) > 500) {
        throw new Exception('La descripción es demasiado larga (máximo 500 caracteres)');
    }
    
    $fecha = trim($_POST['fecha'] ?? '');
    if (empty($fecha)) {
        throw new Exception('La fecha es obligatoria');
    }
    
    // Validar formato de fecha
    $fecha_obj = DateTime::createFromFormat('Y-m-d', $fecha);
    if (!$fecha_obj || $fecha_obj->format('Y-m-d') !== $fecha) {
        throw new Exception('Formato de fecha inválido');
    }
    
    // Validar que la fecha no sea futura
    $hoy = new DateTime();
    if ($fecha_obj > $hoy) {
        throw new Exception('No se puede registrar tiempo en fechas futuras');
    }
    
    $horas = trim($_POST['horas'] ?? '');
    if (empty($horas)) {
        throw new Exception('Las horas son obligatorias');
    }
    
    $horas_num = filter_var($horas, FILTER_VALIDATE_FLOAT);
    if ($horas_num === false || $horas_num <= 0) {
        throw new Exception('Las horas deben ser un número mayor a 0');
    }
    
    if ($horas_num > 24) {
        throw new Exception('No se pueden registrar más de 24 horas por día');
    }
    
    if ($horas_num < 0.25) {
        throw new Exception('El mínimo es 0.25 horas (15 minutos)');
    }
    
    // Obtener horas de inicio y fin (opcionales)
    $hora_inicio = trim($_POST['hora_inicio'] ?? '');
    $hora_fin = trim($_POST['hora_fin'] ?? '');
    
    // Construir fechas completas
    if (!empty($hora_inicio)) {
        $fecha_inicio = $fecha . ' ' . $hora_inicio . ':00';
        // Validar formato
        if (!DateTime::createFromFormat('Y-m-d H:i:s', $fecha_inicio)) {
            throw new Exception('Formato de hora de inicio inválido');
        }
    } else {
        $fecha_inicio = $fecha . ' 08:00:00'; // Hora por defecto
    }
    
    if (!empty($hora_fin)) {
        $fecha_fin = $fecha . ' ' . $hora_fin . ':00';
        // Validar formato
        if (!DateTime::createFromFormat('Y-m-d H:i:s', $fecha_fin)) {
            throw new Exception('Formato de hora de fin inválido');
        }
    } else {
        // Calcular fecha fin basada en las horas
        $inicio_timestamp = strtotime($fecha_inicio);
        $fecha_fin = date('Y-m-d H:i:s', $inicio_timestamp + ($horas_num * 3600));
    }
    
    // Validar que hora inicio sea menor que hora fin
    if (!empty($hora_inicio) && !empty($hora_fin)) {
        if (strtotime($fecha_inicio) >= strtotime($fecha_fin)) {
            throw new Exception('La hora de inicio debe ser anterior a la hora de fin');
        }
    }
    
    // Verificar si ya existe un registro para esta fecha
    $check_query = "SELECT COUNT(*) as count FROM tiempo_trabajado 
                    WHERE trabajador_id = ? AND DATE(fecha_inicio) = ?";
    $check_stmt = $mysqli->prepare($check_query);
    if (!$check_stmt) {
        throw new Exception('Error al verificar registros existentes: ' . $mysqli->error);
    }
    
    $check_stmt->bind_param('is', $worker_id, $fecha);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $existing_count = $check_result->fetch_assoc()['count'];
    $check_stmt->close();
    
    if ($existing_count >= 10) { // Límite razonable de registros por día
        throw new Exception('Ya tienes demasiados registros para esta fecha');
    }
    
    // Insertar registro
    $mysqli->begin_transaction();
    
    try {
        $query = "INSERT INTO tiempo_trabajado (trabajador_id, descripcion, horas, fecha_inicio, fecha_fin, estado, fecha_creacion) 
                  VALUES (?, ?, ?, ?, ?, 'pendiente', NOW())";
        
        $stmt = $mysqli->prepare($query);
        if (!$stmt) {
            throw new Exception('Error al preparar consulta: ' . $mysqli->error);
        }
        
        $stmt->bind_param('isdss', $worker_id, $descripcion, $horas_num, $fecha_inicio, $fecha_fin);
        
        if (!$stmt->execute()) {
            throw new Exception('Error al guardar registro de tiempo: ' . $stmt->error);
        }
        
        $time_id = $mysqli->insert_id;
        $stmt->close();
        
        $mysqli->commit();
        
        logError("Tiempo registrado exitosamente. Time ID: $time_id, Horas: $horas_num");
        
        sendJsonResponse([
            'success' => true,
            'message' => 'Tiempo registrado exitosamente',
            'data' => [
                'time_id' => $time_id,
                'horas' => $horas_num,
                'fecha' => $fecha
            ]
        ]);
        
    } catch (Exception $e) {
        $mysqli->rollback();
        throw $e;
    }
}

// Función para limpiar input (prevenir XSS)
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}
?>