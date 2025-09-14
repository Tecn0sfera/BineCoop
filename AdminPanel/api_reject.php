<?php
session_start();

// Configuración de headers para respuesta JSON
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Mostrar errores para depurar (quitar en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$GLOBALS['allowed_config_access'] = true;
require __DIR__ . '/config/config.php';

// Verificar que el usuario esté logueado
if (empty($_SESSION['user']['logged_in'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Usuario no autenticado'
    ]);
    exit();
}

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Método no permitido'
    ]);
    exit();
}

// Obtener el ID del visitante
$visitante_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($visitante_id <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'ID de visitante inválido'
    ]);
    exit();
}

// Conexión a la base de datos
require_once 'includes/db.php';

try {
    // Verificar que el visitante existe y está pendiente
    $query_check = "SELECT id, nombre, email FROM visitantes WHERE id = ? AND estado_aprobacion = 'pendiente'";
    $stmt_check = $mysqli->prepare($query_check);
    
    if (!$stmt_check) {
        throw new Exception('Error preparando consulta de verificación: ' . $mysqli->error);
    }
    
    $stmt_check->bind_param('i', $visitante_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows === 0) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Visitante no encontrado o ya fue procesado'
        ]);
        exit();
    }
    
    $visitante = $result_check->fetch_assoc();
    $stmt_check->close();
    
    // Opción 1: Actualizar el estado a 'rechazado' (mantener registro)
    $query_update = "UPDATE visitantes SET 
                     estado_aprobacion = 'rechazado',
                     fecha_aprobacion = NOW(),
                     activo = 0,
                     aprobado_por = ?
                     WHERE id = ?";
    
    // Opción 2: Eliminar completamente el registro (descomenta si prefieres esto)
    /*
    $query_update = "DELETE FROM visitantes WHERE id = ?";
    */
    
    $stmt_update = $mysqli->prepare($query_update);
    
    if (!$stmt_update) {
        throw new Exception('Error preparando consulta de actualización: ' . $mysqli->error);
    }
    
    // Para UPDATE necesitamos 2 parámetros, para DELETE solo 1
    $rechazado_por = $_SESSION['user']['id'] ?? $_SESSION['user']['nombre'] ?? 'Administrador';
    $stmt_update->bind_param('si', $rechazado_por, $visitante_id);
    
    // Para DELETE sería:
    // $stmt_update->bind_param('i', $visitante_id);
    
    if ($stmt_update->execute()) {
        $stmt_update->close();
        
        // Log de la acción
        error_log("Visitante rechazado: ID {$visitante_id} - {$visitante['nombre']} por usuario: {$rechazado_por}");
        
        // Opcional: Enviar email de notificación
        // enviar_email_rechazo($visitante['email'], $visitante['nombre']);
        
        // Respuesta exitosa
        echo json_encode([
            'success' => true,
            'message' => 'Visitante rechazado exitosamente',
            'data' => [
                'id' => $visitante_id,
                'nombre' => $visitante['nombre'],
                'nuevo_estado' => 'rechazado'
            ]
        ]);
        
    } else {
        throw new Exception('Error al actualizar el estado del visitante: ' . $stmt_update->error);
    }
    
} catch (Exception $e) {
    error_log("Error en api_reject.php: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error interno del servidor: ' . $e->getMessage()
    ]);
}

// Función opcional para enviar email de rechazo
function enviar_email_rechazo($email, $nombre) {
    // Implementar envío de email aquí
    // Ejemplo básico:
    /*
    $asunto = "Respuesta a su solicitud - Cooperativa de Vivienda";
    $mensaje = "Estimado/a {$nombre},\n\n";
    $mensaje .= "Lamentamos informarle que su solicitud no ha sido aprobada en esta ocasión.\n";
    $mensaje .= "Puede volver a postularse en el futuro.\n\n";
    $mensaje .= "Saludos cordiales,\nEquipo de la Cooperativa";
    
    $headers = "From: cooperativa@ejemplo.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    mail($email, $asunto, $mensaje, $headers);
    */
}
?>