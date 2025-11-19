<?php
// Cargar manejador JWT propio
require_once __DIR__ . '/../jwt_handler.php';

// Cargar configuración si no está cargada
if (!defined('JWT_SECRET')) {
    require_once __DIR__ . '/../env_loader.php';
    require_once __DIR__ . '/../config/config.php';
}

$data = json_decode(file_get_contents("php://input"), true);

try {
    $stmt = $conn->prepare("SELECT id, nombre, email, password, active FROM visitantes WHERE email = ?");
    $stmt->execute([$data['email']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($data['password'], $user['password'])) {
        throw new Exception("Credenciales incorrectas");
    }

    if (!$user['active']) {
        throw new Exception("Cuenta pendiente de aprobación administrativa");
    }

    // Generar token JWT usando el manejador propio
    // generateJWT() está definida en jwt_handler.php
    $token = generateJWT($user, 60 * 60 * 24); // 1 día de expiración

    echo json_encode([
        'success' => true,
        'token' => $token,
        'user' => [
            'id' => $user['id'],
            'nombre' => $user['nombre']
        ]
    ]);
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['error' => $e->getMessage()]);
}
?>