<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

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
$input = file_get_contents('php://input');
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

// Configuración de la base de datos
$host = 'sql211.infinityfree.com';
$dbname = 'if0_39215471_admin_panel';
$username = 'if0_39215471';
$password = 'RockGuidoNetaNa';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
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
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error interno del servidor. Por favor intenta más tarde.'
    ]);
    
} catch (Exception $e) {
    error_log("Error inesperado en registro: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error inesperado. Por favor intenta más tarde.'
    ]);
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