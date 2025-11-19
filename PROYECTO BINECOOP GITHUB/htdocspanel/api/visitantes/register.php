<?php
// Asegurar que no hay salida antes de los headers
if (ob_get_level()) {
    ob_clean();
}

// Establecer headers primero, antes de cualquier salida
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Cache-Control: no-cache, must-revalidate');

// Manejar preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Solo permitir método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Método no permitido'
    ]);
    exit;
}

// Leer el JSON de entrada
// Si se llama directamente (include), usar datos de GLOBALS
if (isset($GLOBALS['_POST_JSON_DATA'])) {
    $input = $GLOBALS['_POST_JSON_DATA'];
} else {
    $input = file_get_contents('php://input');
}
$data = json_decode($input, true);

// Validar que se recibieron datos JSON válidos
if (!$data) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Datos JSON inválidos'
    ]);
    exit;
}

// Validar campos requeridos
$requiredFields = ['nombre', 'email', 'password'];
$missingFields = [];

foreach ($requiredFields as $field) {
    if (empty($data[$field])) {
        $missingFields[] = $field;
    }
}

if (!empty($missingFields)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Faltan campos requeridos: ' . implode(', ', $missingFields)
    ]);
    exit;
}

// Validaciones específicas
$errors = [];

// Validar nombre
if (strlen(trim($data['nombre'])) < 2) {
    $errors['nombre'] = 'El nombre debe tener al menos 2 caracteres';
}

// Validar email
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'El formato del email no es válido';
}

// Validar contraseña
if (strlen($data['password']) < 8) {
    $errors['password'] = 'La contraseña debe tener al menos 8 caracteres';
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Datos de validación incorrectos',
        'validation_errors' => $errors
    ]);
    exit;
}

// Cargar variables de entorno (sin salida)
ob_start();
require_once __DIR__ . '/../../env_loader.php';
$envOutput = ob_get_clean();
if (!empty($envOutput)) {
    error_log("Advertencia: env_loader.php produjo salida: " . substr($envOutput, 0, 200));
}

// Configuración de la base de datos desde variables de entorno
$host = env('DB_HOST', 'localhost');
$dbname = env('DB_NAME', 'nombre_base_datos_ejemplo');
$username = env('DB_USER', 'usuario_ejemplo');
$password = env('DB_PASS', 'contraseña_ejemplo');
$port = env('DB_PORT', '3306');
$charset = env('DB_CHARSET', 'utf8mb4');

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verificar si el email ya existe
    $stmt = $pdo->prepare("SELECT id FROM visitantes WHERE email = ?");
    $stmt->execute([$data['email']]);
    
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'error' => 'El email ya está registrado'
        ]);
        exit;
    }
    
    // Preparar datos para inserción
    $nombre = trim($data['nombre']);
    $email = strtolower(trim($data['email']));
    $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
    $fechaRegistro = date('Y-m-d H:i:s');
    
    // Insertar nuevo visitante
    $sql = "INSERT INTO visitantes (nombre, email, password, fecha_registro, estado_aprobacion) 
            VALUES (?, ?, ?, ?, 'pendiente')";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nombre, $email, $passwordHash, $fechaRegistro]);
    
    $visitanteId = $pdo->lastInsertId();
    
    // Respuesta exitosa
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Registro exitoso. Tu cuenta está pendiente de aprobación administrativa.',
        'data' => [
            'id' => $visitanteId,
            'nombre' => $nombre,
            'email' => $email,
            'fecha_registro' => $fechaRegistro,
            'estado_aprobacion' => 'pendiente'
        ]
    ]);
    
    // Opcional: Enviar notificación por email al administrador
    // enviarNotificacionAdmin($nombre, $email);
    
} catch (PDOException $e) {
    error_log("Error en registro de visitante: " . $e->getMessage());
    error_log("DB Config usado: Host=$host, DB=$dbname, User=$username, Port=$port");
    
    http_response_code(500);
    
    $errorResponse = [
        'success' => false,
        'error' => 'Error de conexión a la base de datos'
    ];
    
    // Solo mostrar detalles en desarrollo
    if (env('APP_ENV', 'production') === 'development') {
        $errorResponse['debug'] = $e->getMessage();
        $errorResponse['db_config'] = [
            'host' => $host,
            'dbname' => $dbname,
            'username' => $username,
            'port' => $port
        ];
    }
    
    echo json_encode($errorResponse);
    
} catch (Exception $e) {
    error_log("Error inesperado en registro: " . $e->getMessage());
    error_log("Trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    
    $errorResponse = [
        'success' => false,
        'error' => 'Error en el servidor'
    ];
    
    // Solo mostrar detalles en desarrollo
    if (env('APP_ENV', 'production') === 'development') {
        $errorResponse['debug'] = $e->getMessage();
    }
    
    echo json_encode($errorResponse);
}

// Función opcional para notificar al administrador
function enviarNotificacionAdmin($nombre, $email) {
    $to = 'admin@cooperativa.com';
    $subject = 'Nuevo visitante registrado';
    $message = "Un nuevo visitante se ha registrado:\n\n";
    $message .= "Nombre: $nombre\n";
    $message .= "Email: $email\n";
    $message .= "Fecha: " . date('Y-m-d H:i:s') . "\n\n";
    $message .= "Por favor revisa y aprueba la cuenta en el panel de administración.";
    
    $headers = "From: sistema@cooperativa.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    mail($to, $subject, $message, $headers);
}
?>