<?php
/**
 * Script de prueba para insertar un comprobante directamente
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/comprobantes_db.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Insertar Comprobante</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #4F46E5; padding-bottom: 10px; }
        .success { color: #10b981; font-weight: bold; padding: 10px; background: #d1fae5; border-radius: 4px; margin: 10px 0; }
        .error { color: #ef4444; font-weight: bold; padding: 10px; background: #fee2e2; border-radius: 4px; margin: 10px 0; }
        .info { color: #3b82f6; padding: 10px; background: #dbeafe; border-radius: 4px; margin: 10px 0; }
        pre { background: #1f2937; color: #10b981; padding: 15px; border-radius: 4px; overflow-x: auto; }
        button { background: #4F46E5; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background: #4338CA; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test: Insertar Comprobante de Prueba</h1>
        
        <?php
        // Verificar conexión
        if (!isset($mysqli)) {
            echo '<div class="error">❌ No hay conexión mysqli</div>';
            exit;
        }
        
        $db_name = $mysqli->query("SELECT DATABASE()")->fetch_row()[0];
        echo '<div class="info">📊 Base de datos: <strong>' . htmlspecialchars($db_name) . '</strong></div>';
        
        // Verificar tabla
        $check_table = $mysqli->query("SHOW TABLES LIKE 'comprobantes_pago'");
        if (!$check_table || $check_table->num_rows == 0) {
            echo '<div class="error">❌ La tabla comprobantes_pago no existe</div>';
            exit;
        }
        
        echo '<div class="success">✅ Tabla comprobantes_pago existe</div>';
        
        // Obtener un trabajador de prueba
        $trabajador_id = null;
        $query_trabajador = "SELECT id FROM visitantes WHERE estado_aprobacion = 'aprobado' LIMIT 1";
        $result_trabajador = $mysqli->query($query_trabajador);
        if ($result_trabajador && $result_trabajador->num_rows > 0) {
            $row = $result_trabajador->fetch_assoc();
            $trabajador_id = $row['id'];
            echo '<div class="info">👤 Trabajador de prueba encontrado: ID ' . $trabajador_id . '</div>';
        } else {
            echo '<div class="error">❌ No se encontró ningún trabajador aprobado para la prueba</div>';
            exit;
        }
        
        // Intentar insertar comprobante de prueba
        if (isset($_GET['test']) && $_GET['test'] === 'insert') {
            echo '<h2>Intentando insertar comprobante de prueba...</h2>';
            
            $comprobante_data = [
                'trabajador_id' => $trabajador_id,
                'titulo' => 'Comprobante de Prueba - ' . date('Y-m-d H:i:s'),
                'descripcion' => 'Este es un comprobante de prueba generado automáticamente',
                'nombre_archivo' => 'test_comprobante.pdf',
                'ruta_archivo' => '/uploads/test/test_comprobante.pdf',
                'tamano_archivo' => 1024
            ];
            
            echo '<div class="info">📝 Datos del comprobante:</div>';
            echo '<pre>' . print_r($comprobante_data, true) . '</pre>';
            
            $comprobante_id = createComprobante($comprobante_data);
            
            if ($comprobante_id) {
                echo '<div class="success">✅ Comprobante insertado exitosamente con ID: ' . htmlspecialchars($comprobante_id) . '</div>';
                
                // Verificar que se guardó
                $verify = $mysqli->query("SELECT * FROM comprobantes_pago WHERE id = '" . $mysqli->real_escape_string($comprobante_id) . "'");
                if ($verify && $verify->num_rows > 0) {
                    $comp = $verify->fetch_assoc();
                    echo '<div class="success">✅ Verificación: Comprobante encontrado en la base de datos</div>';
                    echo '<div class="info">📋 Detalles del comprobante guardado:</div>';
                    echo '<pre>' . print_r($comp, true) . '</pre>';
                } else {
                    echo '<div class="error">❌ Verificación: Comprobante NO encontrado en la base de datos</div>';
                }
                
                // Contar total
                $total = $mysqli->query("SELECT COUNT(*) as total FROM comprobantes_pago")->fetch_assoc()['total'];
                echo '<div class="info">📊 Total de comprobantes en BD: ' . $total . '</div>';
            } else {
                echo '<div class="error">❌ Error al insertar comprobante. Revisar logs del servidor.</div>';
            }
        } else {
            echo '<h2>Instrucciones</h2>';
            echo '<p>Este script intentará insertar un comprobante de prueba directamente en la base de datos.</p>';
            echo '<p><strong>Haz clic en el botón para ejecutar la prueba:</strong></p>';
            echo '<a href="?test=insert"><button>Insertar Comprobante de Prueba</button></a>';
        }
        
        // Mostrar comprobantes existentes
        $total_actual = $mysqli->query("SELECT COUNT(*) as total FROM comprobantes_pago")->fetch_assoc()['total'];
        echo '<hr>';
        echo '<h2>Estado Actual</h2>';
        echo '<div class="info">📊 Total de comprobantes en la base de datos: <strong>' . $total_actual . '</strong></div>';
        
        if ($total_actual > 0) {
            $ultimos = $mysqli->query("SELECT * FROM comprobantes_pago ORDER BY fecha_envio DESC LIMIT 5");
            echo '<h3>Últimos 5 comprobantes:</h3>';
            echo '<table style="width: 100%; border-collapse: collapse;">';
            echo '<tr style="background: #4F46E5; color: white;"><th style="padding: 10px; border: 1px solid #ddd;">ID</th><th style="padding: 10px; border: 1px solid #ddd;">Trabajador</th><th style="padding: 10px; border: 1px solid #ddd;">Título</th><th style="padding: 10px; border: 1px solid #ddd;">Estado</th></tr>';
            while ($row = $ultimos->fetch_assoc()) {
                echo '<tr>';
                echo '<td style="padding: 10px; border: 1px solid #ddd;">' . htmlspecialchars($row['id']) . '</td>';
                echo '<td style="padding: 10px; border: 1px solid #ddd;">' . htmlspecialchars($row['trabajador_id']) . '</td>';
                echo '<td style="padding: 10px; border: 1px solid #ddd;">' . htmlspecialchars($row['titulo']) . '</td>';
                echo '<td style="padding: 10px; border: 1px solid #ddd;">' . htmlspecialchars($row['estado']) . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        }
        ?>
        
        <hr>
        <p><a href="debug_comprobantes_detallado.php">← Volver al diagnóstico detallado</a></p>
        <p><a href="dashboard.php">← Volver al Dashboard</a></p>
    </div>
</body>
</html>

