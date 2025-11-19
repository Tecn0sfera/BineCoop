<?php
$data = json_decode(file_get_contents("php://input"), true);

try {
    // Validación básica
    if (empty($data['nombre']) || empty($data['email']) || empty($data['password'])) {
        throw new Exception("Todos los campos son requeridos");
    }

    // Verificar si el email ya existe
    $stmt = $conn->prepare("SELECT id FROM visitantes WHERE email = ?");
    $stmt->execute([$data['email']]);
    if ($stmt->rowCount() > 0) {
        throw new Exception("El email ya está registrado");
    }

    // Insertar nuevo visitante (active=0 por defecto)
    $stmt = $conn->prepare("INSERT INTO visitantes (nombre, email, password) VALUES (?, ?, ?)");
    $stmt->execute([
        $data['nombre'],
        $data['email'],
        password_hash($data['password'], PASSWORD_BCRYPT)
    ]);

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Registro exitoso. Espera aprobación administrativa.'
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>