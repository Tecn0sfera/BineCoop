<?php
// user_auth.php - API de autenticación para usuarios (VERSIÓN FINAL CORREGIDA)

// Configuración de errores (DESACTIVAR en producción)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Headers CORS más específicos
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization, User-Agent');

// Manejar preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Método no permitido. Solo POST es aceptado.'
    ]);
    exit();
}

// Configuración de la base de datos
$DB_CONFIG = [
    'host' => 'sql211.infinityfree.com',
    'dbname' => 'if0_39215471_admin_panel',
    'username' => 'if0_39215471',
    'password' => 'RockGuidoNetaNa',
    'charset' => 'utf8mb4'
];

// Configuración JWT
define('USER_JWT_SECRET', 'tu_clave_secreta_usuarios_2024_diferente');

// Log del intento de conexión
error_log("=== INICIO AUTH API - " . date('Y-m-d H:i:s') . " ===");
error_log("Método: " . $_SERVER['REQUEST_METHOD']);
error_log("User-Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'No definido'));
error_log("Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'No definido'));

try {
    // Leer datos del request
    $input = file_get_contents("php://input");
    error_log("Raw input: " . $input);
    
    if (empty($input)) {
        // Intentar con $_POST si no hay input JSON
        if (!empty($_POST)) {
            $data = $_POST;
            error_log("Usando datos POST: " . print_r($data, true));
        } else {
            throw new Exception("No se recibieron datos de entrada");
        }
    } else {
        $data = json_decode($input, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Error JSON: " . json_last_error_msg() . ". Raw data: " . substr($input, 0, 200));
        }
        error_log("Datos JSON decodificados: " . print_r($data, true));
    }
    
    // Validar campos obligatorios
    if (empty($data['email']) || empty($data['password'])) {
        error_log("Error: Campos faltantes - email: " . ($data['email'] ?? 'vacío') . ", password: " . (empty($data['password']) ? 'vacío' : 'presente'));
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Email y contraseña son obligatorios'
        ]);
        exit();
    }
    
    // Limpiar y validar email
    $email = trim(strtolower($data['email']));
    $password = $data['password'];
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        error_log("Error: Email inválido - " . $email);
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Formato de email inválido'
        ]);
        exit();
    }
    
    error_log("Intentando login para: " . $email);
    
    // Conectar a la base de datos
    $dsn = "mysql:host={$DB_CONFIG['host']};dbname={$DB_CONFIG['dbname']};charset={$DB_CONFIG['charset']}";
    $conn = new PDO($dsn, $DB_CONFIG['username'], $DB_CONFIG['password']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    error_log("Conexión BD exitosa");
    
    // Buscar usuario en la tabla visitantes (usando los nombres correctos de las columnas)
    $stmt = $conn->prepare("SELECT id, nombre, email, password, activo, estado_aprobacion FROM visitantes WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    error_log("Consulta BD ejecutada. Usuario encontrado: " . ($user ? "Sí (ID: {$user['id']})" : "No"));
    
    if (!$user) {
        error_log("Error: Usuario no encontrado - " . $email);
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Credenciales incorrectas'
        ]);
        exit();
    }
    
    // Verificar contraseña
    $passwordValid = password_verify($password, $user['password']);
    error_log("Verificación de contraseña: " . ($passwordValid ? "Correcta" : "Incorrecta"));
    
    if (!$passwordValid) {
        error_log("Error: Contraseña incorrecta para usuario " . $email);
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Credenciales incorrectas'
        ]);
        exit();
    }
    
    // Verificar estado de la cuenta (usando ambas columnas)
    $isActive = $user['activo'] == 1;
    $isApproved = $user['estado_aprobacion'] === 'aprobado';
    
    error_log("Estado cuenta - Activo: " . ($isActive ? "Sí" : "No") . ", Aprobado: " . ($isApproved ? "Sí" : "No ({$user['estado_aprobacion']})"));
    
    if (!$isActive) {
        error_log("Error: Cuenta inactiva para usuario " . $email);
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'Tu cuenta está desactivada. Contacta al administrador.'
        ]);
        exit();
    }
    
    if (!$isApproved) {
        error_log("Error: Cuenta no aprobada para usuario " . $email . " (estado: {$user['estado_aprobacion']})");
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'Cuenta pendiente de aprobación administrativa'
        ]);
        exit();
    }
    
    // Actualizar último acceso
    try {
        $updateStmt = $conn->prepare("UPDATE visitantes SET ultimo_acceso = NOW() WHERE id = ?");
        $updateStmt->execute([$user['id']]);
        error_log("Último acceso actualizado");
    } catch (Exception $e) {
        error_log("No se pudo actualizar ultimo_acceso: " . $e->getMessage());
    }
    
    // Generar token JWT
    $token = generateUserJWT($user);
    error_log("Token JWT generado exitosamente");
    
    // Respuesta exitosa
    http_response_code(200);
    $response = [
        'success' => true,
        'token' => $token,
        'user' => [
            'id' => (int)$user['id'],
            'nombre' => $user['nombre'],
            'email' => $user['email'],
            'activo' => (bool)$user['activo'],
            'estado_aprobacion' => $user['estado_aprobacion']
        ],
        'message' => 'Login exitoso'
    ];
    
    error_log("Login exitoso para: " . $email . " (ID: " . $user['id'] . ")");
    echo json_encode($response);
    
} catch (PDOException $e) {
    error_log("ERROR BD: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error de base de datos',
        'debug' => $e->getMessage() // QUITAR en producción
    ]);
    
} catch (Exception $e) {
    error_log("ERROR GENERAL: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * Generar JWT para usuarios
 */
function generateUserJWT($user) {
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $payload = json_encode([
        'sub' => $user['id'],
        'type' => 'user',
        'nombre' => $user['nombre'],
        'email' => $user['email'],
        'iat' => time(),
        'exp' => time() + (60 * 60 * 24 * 7) // 7 días
    ]);
    
    $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
    
    $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, USER_JWT_SECRET, true);
    $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    
    return $base64Header . "." . $base64Payload . "." . $base64Signature;
}

/**
 * Validar token de usuario
 */
function validateUserToken($token) {
    try {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return false;
        
        $signature = $parts[2];
        $expectedSignature = str_replace(['+', '/', '='], ['-', '_', ''], 
            base64_encode(hash_hmac('sha256', $parts[0] . '.' . $parts[1], USER_JWT_SECRET, true))
        );
        
        if ($signature !== $expectedSignature) return false;
        
        $payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1]));
        $payloadData = json_decode($payload, true);
        
        if ($payloadData['exp'] < time()) return false;
        if ($payloadData['type'] !== 'user') return false;
        
        return $payloadData;
    } catch (Exception $e) {
        error_log("Error validando token: " . $e->getMessage());
        return false;
    }
}

/**
 * Función para requerir autenticación en otras páginas
 */
function requireUserAuth() {
    $headers = getallheaders();
    $token = null;
    
    if (isset($headers['Authorization'])) {
        $auth = $headers['Authorization'];
        if (strpos($auth, 'Bearer ') === 0) {
            $token = substr($auth, 7);
        }
    }
    
    if (!$token) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Token requerido']);
        exit();
    }
    
    $userData = validateUserToken($token);
    if (!$userData) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Token inválido o expirado']);
        exit();
    }
    
    return $userData;
}
?>