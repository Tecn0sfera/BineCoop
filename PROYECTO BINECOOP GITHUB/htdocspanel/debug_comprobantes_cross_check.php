<?php
/**
 * Script para verificar comprobantes en ambas bases de datos posibles
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/includes/db.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación Cruzada - Comprobantes</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #4F46E5; padding-bottom: 10px; }
        h2 { color: #4F46E5; margin-top: 30px; }
        .section { margin: 20px 0; padding: 15px; background: #f9fafb; border-left: 4px solid #4F46E5; }
        .success { color: #10b981; font-weight: bold; }
        .error { color: #ef4444; font-weight: bold; }
        .warning { color: #f59e0b; font-weight: bold; }
        .info { color: #3b82f6; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
        th { background: #4F46E5; color: white; }
        tr:nth-child(even) { background: #f9fafb; }
        pre { background: #1f2937; color: #10b981; padding: 15px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Verificación Cruzada - Comprobantes de Pago</h1>
        
        <?php
        // 1. Verificar conexión actual
        echo '<div class="section">';
        echo '<h2>1. Conexión Actual</h2>';
        if (isset($mysqli)) {
            $db_name = $mysqli->query("SELECT DATABASE()")->fetch_row()[0];
            echo '<p class="success">✅ Conectado a: <strong>' . htmlspecialchars($db_name) . '</strong></p>';
            
            // Verificar si existe la tabla
            $check_table = $mysqli->query("SHOW TABLES LIKE 'comprobantes_pago'");
            if ($check_table && $check_table->num_rows > 0) {
                $total = $mysqli->query("SELECT COUNT(*) as total FROM comprobantes_pago")->fetch_assoc()['total'];
                echo '<p class="info">Total de comprobantes en esta BD: <strong>' . $total . '</strong></p>';
                
                if ($total > 0) {
                    // Mostrar últimos 5
                    $ultimos = $mysqli->query("SELECT * FROM comprobantes_pago ORDER BY fecha_envio DESC LIMIT 5");
                    echo '<h3>Últimos 5 comprobantes:</h3>';
                    echo '<table>';
                    echo '<tr><th>ID</th><th>Trabajador ID</th><th>Título</th><th>Fecha Envío</th><th>Estado</th></tr>';
                    while ($row = $ultimos->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($row['id']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['trabajador_id']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['titulo']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['fecha_envio']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['estado']) . '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                }
            } else {
                echo '<p class="error">❌ La tabla comprobantes_pago no existe en esta base de datos</p>';
            }
        }
        echo '</div>';
        
        // 2. Intentar conectar a otras bases de datos posibles
        echo '<div class="section">';
        echo '<h2>2. Verificar Otras Bases de Datos</h2>';
        
        $possible_databases = [
            'if0_39215471_admin_panel',
            'if0_39215471_cooperativa',
            'if0_39215471_binecoop_db',
            'binecoop_db'
        ];
        
        $host = env('DB_HOST', 'localhost');
        $usuario = env('DB_USER', 'root');
        $contrasena = env('DB_PASS', '');
        $port = env('DB_PORT', '3306');
        
        foreach ($possible_databases as $db_name) {
            try {
                $test_mysqli = new mysqli($host, $usuario, $contrasena, $db_name, $port);
                
                if (!$test_mysqli->connect_error) {
                    echo '<h3>Base de datos: ' . htmlspecialchars($db_name) . '</h3>';
                    
                    // Verificar si existe la tabla
                    $check_table = $test_mysqli->query("SHOW TABLES LIKE 'comprobantes_pago'");
                    if ($check_table && $check_table->num_rows > 0) {
                        $total = $test_mysqli->query("SELECT COUNT(*) as total FROM comprobantes_pago")->fetch_assoc()['total'];
                        echo '<p class="info">✅ Tabla existe - Total de comprobantes: <strong>' . $total . '</strong></p>';
                        
                        if ($total > 0) {
                            // Mostrar estadísticas
                            $estados = $test_mysqli->query("SELECT estado, COUNT(*) as cantidad FROM comprobantes_pago GROUP BY estado");
                            echo '<table>';
                            echo '<tr><th>Estado</th><th>Cantidad</th></tr>';
                            while ($row = $estados->fetch_assoc()) {
                                echo '<tr><td>' . htmlspecialchars($row['estado']) . '</td><td>' . $row['cantidad'] . '</td></tr>';
                            }
                            echo '</table>';
                        }
                    } else {
                        echo '<p class="warning">⚠️ La tabla comprobantes_pago no existe</p>';
                    }
                    
                    $test_mysqli->close();
                } else {
                    echo '<p class="error">❌ No se pudo conectar a: ' . htmlspecialchars($db_name) . '</p>';
                }
            } catch (Exception $e) {
                echo '<p class="error">❌ Error al conectar a ' . htmlspecialchars($db_name) . ': ' . htmlspecialchars($e->getMessage()) . '</p>';
            }
        }
        echo '</div>';
        
        // 3. Verificar configuración de htdocscop
        echo '<div class="section">';
        echo '<h2>3. Configuración de htdocscop</h2>';
        $config_file = dirname(__DIR__) . '/config.json';
        if (file_exists($config_file)) {
            $config = json_decode(file_get_contents($config_file), true);
            if (isset($config['htdocscop']['DB_NAME'])) {
                echo '<p class="info">Base de datos configurada en htdocscop: <strong>' . htmlspecialchars($config['htdocscop']['DB_NAME']) . '</strong></p>';
            }
        } else {
            echo '<p class="warning">⚠️ No se encontró config.json</p>';
        }
        echo '</div>';
        ?>
        
        <div class="section">
            <h2>4. Acciones</h2>
            <p><a href="dashboard.php">← Volver al Dashboard</a></p>
            <p><a href="debug_comprobantes_detallado.php">Ver diagnóstico detallado</a></p>
        </div>
    </div>
</body>
</html>

