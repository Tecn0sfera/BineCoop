<?php
try {
    $stmt = $conn->query("
        SELECT id, nombre, email, active, creado_en 
        FROM visitantes 
        ORDER BY active, creado_en DESC
    ");
    $visitantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $visitantes,
        'count' => count($visitantes)
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener visitantes']);
}
?>