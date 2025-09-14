<?php
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

    // Generar token JWT
    $token = generateJWT($user);

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

function generateJWT($user) {
    $secret = JWT_SECRET;
    $payload = [
        'sub' => $user['id'],
        'nombre' => $user['nombre'],
        'email' => $user['email'],
        'exp' => time() + (60 * 60 * 24) // 1 día
    ];
    return \Firebase\JWT\JWT::encode($payload, $secret, 'HS256');
}
?>