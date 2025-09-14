<?php
session_start();

// Configuración para API - NO mostrar errores en producción
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Función para enviar respuesta JSON limpia
function sendJsonResponse($success, $message = '', $data = null) {
    // Limpiar cualquier output previo
    if (ob_get_level()) {
        ob_clean();
    }
    
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, must-revalidate');
    
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response);
    exit();
}

// Función para manejar errores y convertirlos a JSON
function handleError($errno, $errstr, $errfile, $errline) {
    error_log("PHP Error [$errno]: $errstr in $errfile on line $errline");
    sendJsonResponse(false, 'Error interno del servidor. Por favor intenta nuevamente.');
}

// Función para manejar excepciones
function handleException($exception) {
    error_log("PHP Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine());
    sendJsonResponse(false, 'Error interno del servidor. Por favor intenta nuevamente.');
}

// Configurar manejadores de error
set_error_handler('handleError');
set_exception_handler('handleException');

try {
    // Verificar que sea una petición AJAX
    if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
        sendJsonResponse(false, 'Acceso no autorizado');
    }

    // Verificar método POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendJsonResponse(false, 'Método no permitido');
    }

    // Verificar sesión
    if (empty($_SESSION['user_token']) || empty($_SESSION['user'])) {
        sendJsonResponse(false, 'Sesión no válida', ['redirect' => 'login.php']);
    }

    $GLOBALS['allowed_config_access'] = true;
    require_once __DIR__ . '/config.php';
    require_once __DIR__ . '/db.php';

    // Función de limpieza de datos
    function limpiarDato($dato) {
        return htmlspecialchars(trim($dato), ENT_QUOTES, 'UTF-8');
    }

    // Obtener datos del trabajador
    $current_worker = $_SESSION['user'];
    $worker_id = filter_var($current_worker['id'] ?? 0, FILTER_VALIDATE_INT);

    if ($worker_id === false || $worker_id <= 0) {
        sendJsonResponse(false, 'ID de trabajador no válido', ['redirect' => 'login.php']);
    }

    // Verificar conexión a base de datos
    if (!$mysqli || $mysqli->connect_error) {
        error_log("Error de conexión a BD: " . ($mysqli->connect_error ?? 'Unknown'));
        sendJsonResponse(false, 'Error de conexión a la base de datos');
    }

    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'register_time':
            handleTimeRegistration($mysqli, $worker_id);
            break;
            
        case 'upload_pdf':
            handlePdfUpload($mysqli, $worker_id);
            break;
            
        default:
            sendJsonResponse(false, 'Acción no válida');
    }

} catch (Exception $e) {
    error_log("API Exception: " . $e->getMessage());
    sendJsonResponse(false, 'Error interno del servidor');
}

function handleTimeRegistration($mysqli, $worker_id) {
    try {
        // Validar y sanitizar datos
        $descripcion = trim($_POST['descripcion'] ?? '');
        $fecha = $_POST['fecha'] ?? '';
        $horas = $_POST['horas'] ?? '';
        $hora_inicio = $_POST['hora_inicio'] ?? '';
        $hora_fin = $_POST['hora_fin'] ?? '';

        // Validaciones
        if (empty($descripcion)) {
            sendJsonResponse(false, 'La descripción es obligatoria');
        }

        if (strlen($descripcion) > 500) {
            sendJsonResponse(false, 'La descripción no puede exceder 500 caracteres');
        }

        if (empty($fecha)) {
            sendJsonResponse(false, 'La fecha es obligatoria');
        }

        // Validar fecha
        $fecha_obj = DateTime::createFromFormat('Y-m-d', $fecha);
        if (!$fecha_obj) {
            sendJsonResponse(false, 'Formato de fecha inválido');
        }

        // No permitir fechas futuras
        $hoy = new DateTime();
        if ($fecha_obj > $hoy) {
            sendJsonResponse(false, 'No se puede registrar tiempo en fechas futuras');
        }

        // Validar horas
        $horas_num = filter_var($horas, FILTER_VALIDATE_FLOAT);
        if ($horas_num === false || $horas_num <= 0) {
            sendJsonResponse(false, 'Las horas deben ser un número válido mayor a 0');
        }

        if ($horas_num > 24) {
            sendJsonResponse(false, 'No se pueden registrar más de 24 horas por día');
        }

        if ($horas_num < 0.25) {
            sendJsonResponse(false, 'El mínimo es 0.25 horas (15 minutos)');
        }

        // Validar horarios si se proporcionan
        $fecha_inicio = null;
        $fecha_fin = null;
        
        if (!empty($hora_inicio) && !empty($hora_fin)) {
            if ($hora_inicio >= $hora_fin) {
                sendJsonResponse(false, 'La hora de inicio debe ser anterior a la hora de fin');
            }
            
            $fecha_inicio = $fecha . ' ' . $hora_inicio . ':00';
            $fecha_fin = $fecha . ' ' . $hora_fin . ':00';
        } else {
            // Si no se proporcionan horarios, usar valores por defecto
            $fecha_inicio = $fecha . ' 08:00:00';
            $fecha_fin = date('Y-m-d H:i:s', strtotime($fecha_inicio) + ($horas_num * 3600));
        }

        // Verificar que no se superponga con otros registros del mismo día
        $query_check = "SELECT COUNT(*) as count FROM tiempo_trabajado 
                       WHERE trabajador_id = ? 
                       AND DATE(fecha_inicio) = ? 
                       AND estado != 'rechazado'";
        
        $stmt_check = $mysqli->prepare($query_check);
        if (!$stmt_check) {
            error_log("Error preparing check statement: " . $mysqli->error);
            sendJsonResponse(false, 'Error en la base de datos');
        }
        
        $stmt_check->bind_param('is', $worker_id, $fecha);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        $row_check = $result_check->fetch_assoc();
        $stmt_check->close();

        // Permitir hasta 3 registros por día
        if ($row_check['count'] >= 3) {
            sendJsonResponse(false, 'Ya tienes el máximo de registros para este día (3)');
        }

        // Insertar registro
        $query = "INSERT INTO tiempo_trabajado (trabajador_id, descripcion, horas, fecha_inicio, fecha_fin, estado, fecha_creacion) 
                  VALUES (?, ?, ?, ?, ?, 'pendiente', NOW())";
        
        $stmt = $mysqli->prepare($query);
        if (!$stmt) {
            error_log("Error preparing insert statement: " . $mysqli->error);
            sendJsonResponse(false, 'Error al preparar consulta');
        }
        
        $descripcion_clean = limpiarDato($descripcion);
        $stmt->bind_param('isdss', $worker_id, $descripcion_clean, $horas_num, $fecha_inicio, $fecha_fin);
        
        if ($stmt->execute()) {
            $stmt->close();
            
            // Log de la acción
            error_log("Tiempo registrado - Trabajador ID: $worker_id, Horas: $horas_num, Fecha: $fecha");
            
            sendJsonResponse(true, 'Tiempo registrado exitosamente', [
                'horas' => $horas_num,
                'fecha' => $fecha,
                'descripcion' => $descripcion_clean
            ]);
        } else {
            error_log("Error executing insert statement: " . $stmt->error);
            $stmt->close();
            sendJsonResponse(false, 'Error al guardar el registro');
        }

    } catch (Exception $e) {
        error_log("Error in handleTimeRegistration: " . $e->getMessage());
        sendJsonResponse(false, 'Error interno al procesar el registro');
    }
}

function handlePdfUpload($mysqli, $worker_id) {
    error_log("handlePdfUpload - Iniciando para trabajador ID: $worker_id");
    
    try {
        // Validar datos del formulario
        $titulo = trim($_POST['titulo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        
        error_log("handlePdfUpload - Datos recibidos: titulo='$titulo', descripcion='$descripcion'");
        error_log("handlePdfUpload - Archivo recibido: " . print_r($_FILES['pdf_file'] ?? 'NO FILE', true));

        if (empty($titulo)) {
            error_log("handlePdfUpload - Error: título vacío");
            sendJsonResponse(false, 'El título es obligatorio');
        }

        if (strlen($titulo) > 255) {
            sendJsonResponse(false, 'El título no puede exceder 255 caracteres');
        }

        if (strlen($descripcion) > 1000) {
            sendJsonResponse(false, 'La descripción no puede exceder 1000 caracteres');
        }

        // Validar archivo
        if (!isset($_FILES['pdf_file']) || $_FILES['pdf_file']['error'] !== UPLOAD_ERR_OK) {
            $error_msg = 'Error al subir archivo';
            
            if (isset($_FILES['pdf_file']['error'])) {
                switch ($_FILES['pdf_file']['error']) {
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        $error_msg = 'El archivo es demasiado grande';
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $error_msg = 'El archivo se subió parcialmente';
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        $error_msg = 'No se seleccionó ningún archivo';
                        break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                        $error_msg = 'Error del servidor: directorio temporal no encontrado';
                        break;
                    case UPLOAD_ERR_CANT_WRITE:
                        $error_msg = 'Error del servidor: no se pudo escribir el archivo';
                        break;
                    case UPLOAD_ERR_EXTENSION:
                        $error_msg = 'Error del servidor: extensión bloqueada';
                        break;
                }
            }
            
            sendJsonResponse(false, $error_msg);
        }

        $file = $_FILES['pdf_file'];

        // Validar tipo de archivo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if ($mime_type !== 'application/pdf') {
            sendJsonResponse(false, 'Solo se permiten archivos PDF');
        }

        // Validar tamaño (10MB máximo)
        $max_size = 10 * 1024 * 1024; // 10MB
        if ($file['size'] > $max_size) {
            sendJsonResponse(false, 'El archivo es demasiado grande. Máximo 10MB');
        }

        // Validar nombre del archivo
        if (strlen($file['name']) > 255) {
            sendJsonResponse(false, 'El nombre del archivo es demasiado largo');
        }

        // Crear directorio de uploads si no existe
        $upload_dir = __DIR__ . '/uploads/reports/';
        if (!file_exists($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                error_log("Error creating upload directory: $upload_dir");
                sendJsonResponse(false, 'Error del servidor: no se pudo crear directorio');
            }
        }

        // Generar nombre único para el archivo
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $file_name = 'report_' . $worker_id . '_' . date('Y-m-d_H-i-s') . '_' . uniqid() . '.' . $file_extension;
        $file_path = $upload_dir . $file_name;

        // Mover archivo
        if (!move_uploaded_file($file['tmp_name'], $file_path)) {
            error_log("Error moving uploaded file to: $file_path");
            sendJsonResponse(false, 'Error al guardar el archivo');
        }

        // Guardar información en base de datos
        $query = "INSERT INTO reportes_pdf (trabajador_id, titulo, descripcion, nombre_archivo, ruta_archivo, tamaño_archivo, fecha_envio) 
                  VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $mysqli->prepare($query);
        if (!$stmt) {
            // Eliminar archivo si falla la preparación de la consulta
            unlink($file_path);
            error_log("Error preparing PDF insert statement: " . $mysqli->error);
            sendJsonResponse(false, 'Error en la base de datos');
        }

        $titulo_clean = limpiarDato($titulo);
        $descripcion_clean = limpiarDato($descripcion);
        $file_size = $file['size'];
        
        $stmt->bind_param('issssi', $worker_id, $titulo_clean, $descripcion_clean, $file['name'], $file_path, $file_size);
        
        if ($stmt->execute()) {
            $report_id = $mysqli->insert_id;
            $stmt->close();
            
            // Log de la acción
            error_log("PDF subido - Trabajador ID: $worker_id, Archivo: {$file['name']}, Tamaño: {$file_size} bytes");
            
            sendJsonResponse(true, 'Reporte PDF subido exitosamente', [
                'report_id' => $report_id,
                'titulo' => $titulo_clean,
                'filename' => $file['name'],
                'size' => $file_size
            ]);
        } else {
            // Eliminar archivo si falla la inserción
            unlink($file_path);
            error_log("Error executing PDF insert statement: " . $stmt->error);
            $stmt->close();
            sendJsonResponse(false, 'Error al guardar información del reporte');
        }

    } catch (Exception $e) {
        error_log("Error in handlePdfUpload: " . $e->getMessage());
        
        // Limpiar archivo si existe
        if (isset($file_path) && file_exists($file_path)) {
            unlink($file_path);
        }
        
        sendJsonResponse(false, 'Error interno al procesar el archivo');
    }
}
?>