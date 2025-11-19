<?php
$id = $_GET['id'];
$data = json_decode(file_get_contents("php://input"), true);

try {
    // Actualizar estado del visitante
    $stmt = $conn->prepare("UPDATE visitantes SET active = ? WHERE id = ?");
    $stmt->execute([$data['approve'] ? 1 : 0, $id]);
    
    // Si se aprueba, crear registro en socios
    if ($data['approve']) {
        $stmt = $conn->prepare("
            INSERT INTO socios (visitante_id, nombre, fecha_ingreso)
            SELECT id, nombre, CURDATE() 
            FROM visitantes 
            WHERE id = ? AND NOT EXISTS (
                SELECT 1 FROM socios WHERE visitante_id = ?
            )
        ");
        $stmt->execute([$id, $id]);
    }
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al actualizar']);
}
?>