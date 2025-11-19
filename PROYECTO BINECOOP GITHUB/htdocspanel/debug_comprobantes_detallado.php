<?php
/**
 * Script de diagnóstico detallado para comprobantes de pago
 * Verifica conexiones, tablas y datos
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
    <title>Diagnóstico Detallado - Comprobantes</title>
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
        .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-error { background: #fee2e2; color: #991b1b; }
        .badge-warning { background: #fef3c7; color: #92400e; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Diagnóstico Detallado - Comprobantes de Pago</h1>
        
        <?php
        // 1. Verificar conexión
        echo '<div class="section">';
        echo '<h2>1. Conexión a Base de Datos</h2>';
        if (isset($mysqli)) {
            echo '<p class="success">✅ Conexión mysqli establecida</p>';
            
            // Información de la conexión
            $db_name = $mysqli->query("SELECT DATABASE()")->fetch_row()[0];
            echo '<p class="info">Base de datos actual: <strong>' . htmlspecialchars($db_name) . '</strong></p>';
            
            $host_info = $mysqli->host_info;
            echo '<p class="info">Host: <strong>' . htmlspecialchars($host_info) . '</strong></p>';
        } else {
            echo '<p class="error">❌ No hay conexión mysqli</p>';
        }
        echo '</div>';
        
        // 2. Verificar tabla
        echo '<div class="section">';
        echo '<h2>2. Verificación de Tabla</h2>';
        if (isset($mysqli)) {
            $check_table = $mysqli->query("SHOW TABLES LIKE 'comprobantes_pago'");
            if ($check_table && $check_table->num_rows > 0) {
                echo '<p class="success">✅ La tabla comprobantes_pago existe</p>';
                
                // Mostrar estructura de la tabla
                $columns = $mysqli->query("SHOW COLUMNS FROM comprobantes_pago");
                echo '<h3>Estructura de la tabla:</h3>';
                echo '<table>';
                echo '<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>';
                while ($col = $columns->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($col['Field']) . '</td>';
                    echo '<td>' . htmlspecialchars($col['Type']) . '</td>';
                    echo '<td>' . htmlspecialchars($col['Null']) . '</td>';
                    echo '<td>' . htmlspecialchars($col['Key']) . '</td>';
                    echo '<td>' . htmlspecialchars($col['Default'] ?? 'NULL') . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            } else {
                echo '<p class="error">❌ La tabla comprobantes_pago NO existe</p>';
                
                // Listar todas las tablas disponibles
                $all_tables = $mysqli->query("SHOW TABLES");
                if ($all_tables) {
                    echo '<h3>Tablas disponibles en la base de datos:</h3>';
                    echo '<ul>';
                    while ($row = $all_tables->fetch_row()) {
                        echo '<li>' . htmlspecialchars($row[0]) . '</li>';
                    }
                    echo '</ul>';
                }
            }
        }
        echo '</div>';
        
        // 3. Contar comprobantes
        echo '<div class="section">';
        echo '<h2>3. Estadísticas de Comprobantes</h2>';
        if (isset($mysqli)) {
            $check_table = $mysqli->query("SHOW TABLES LIKE 'comprobantes_pago'");
            if ($check_table && $check_table->num_rows > 0) {
                // Total
                $total = $mysqli->query("SELECT COUNT(*) as total FROM comprobantes_pago")->fetch_assoc()['total'];
                echo '<p>Total de comprobantes: <strong>' . $total . '</strong></p>';
                
                // Por estado
                $estados = $mysqli->query("SELECT estado, COUNT(*) as cantidad FROM comprobantes_pago GROUP BY estado");
                echo '<table>';
                echo '<tr><th>Estado</th><th>Cantidad</th></tr>';
                while ($row = $estados->fetch_assoc()) {
                    $badge_class = $row['estado'] === 'pendiente' ? 'badge-warning' : 
                                   ($row['estado'] === 'aprobado' ? 'badge-success' : 'badge-error');
                    echo '<tr>';
                    echo '<td><span class="badge ' . $badge_class . '">' . htmlspecialchars($row['estado']) . '</span></td>';
                    echo '<td>' . $row['cantidad'] . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            }
        }
        echo '</div>';
        
        // 4. Listar comprobantes pendientes
        echo '<div class="section">';
        echo '<h2>4. Comprobantes Pendientes (Últimos 10)</h2>';
        try {
            $pendientes = getComprobantesPendientes(10);
            if (count($pendientes) > 0) {
                echo '<p class="success">✅ Se encontraron ' . count($pendientes) . ' comprobantes pendientes</p>';
                echo '<table>';
                echo '<tr><th>ID</th><th>Trabajador ID</th><th>Título</th><th>Archivo</th><th>Fecha Envío</th><th>Estado</th></tr>';
                foreach ($pendientes as $comp) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($comp['id']) . '</td>';
                    echo '<td>' . htmlspecialchars($comp['trabajador_id']) . '</td>';
                    echo '<td>' . htmlspecialchars($comp['titulo']) . '</td>';
                    echo '<td>' . htmlspecialchars($comp['nombre_archivo']) . '</td>';
                    echo '<td>' . htmlspecialchars($comp['fecha_envio']) . '</td>';
                    echo '<td><span class="badge badge-warning">' . htmlspecialchars($comp['estado']) . '</span></td>';
                    echo '</tr>';
                }
                echo '</table>';
            } else {
                echo '<p class="warning">⚠️ No se encontraron comprobantes pendientes</p>';
            }
        } catch (Exception $e) {
            echo '<p class="error">❌ Error al obtener comprobantes: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
        echo '</div>';
        
        // 5. Listar todos los comprobantes (últimos 20)
        echo '<div class="section">';
        echo '<h2>5. Todos los Comprobantes (Últimos 20)</h2>';
        if (isset($mysqli)) {
            $check_table = $mysqli->query("SHOW TABLES LIKE 'comprobantes_pago'");
            if ($check_table && $check_table->num_rows > 0) {
                $all = $mysqli->query("SELECT * FROM comprobantes_pago ORDER BY fecha_envio DESC LIMIT 20");
                if ($all && $all->num_rows > 0) {
                    echo '<table>';
                    echo '<tr><th>ID</th><th>Trabajador ID</th><th>Título</th><th>Archivo</th><th>Fecha Envío</th><th>Estado</th></tr>';
                    while ($row = $all->fetch_assoc()) {
                        $badge_class = $row['estado'] === 'pendiente' ? 'badge-warning' : 
                                       ($row['estado'] === 'aprobado' ? 'badge-success' : 'badge-error');
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($row['id']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['trabajador_id']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['titulo']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['nombre_archivo']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['fecha_envio']) . '</td>';
                        echo '<td><span class="badge ' . $badge_class . '">' . htmlspecialchars($row['estado']) . '</span></td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                } else {
                    echo '<p class="warning">⚠️ No hay comprobantes en la base de datos</p>';
                }
            }
        }
        echo '</div>';
        
        // 6. Verificar trabajadores
        echo '<div class="section">';
        echo '<h2>6. Verificación de Trabajadores</h2>';
        if (isset($mysqli)) {
            // Verificar tabla visitantes
            $check_visitantes = $mysqli->query("SHOW TABLES LIKE 'visitantes'");
            if ($check_visitantes && $check_visitantes->num_rows > 0) {
                $total_visitantes = $mysqli->query("SELECT COUNT(*) as total FROM visitantes")->fetch_assoc()['total'];
                echo '<p>Total de visitantes: <strong>' . $total_visitantes . '</strong></p>';
                
                // Verificar trabajadores que tienen comprobantes
                $check_table = $mysqli->query("SHOW TABLES LIKE 'comprobantes_pago'");
                if ($check_table && $check_table->num_rows > 0) {
                    $trabajadores_con_comprobantes = $mysqli->query("
                        SELECT DISTINCT trabajador_id 
                        FROM comprobantes_pago 
                        ORDER BY trabajador_id
                    ");
                    if ($trabajadores_con_comprobantes && $trabajadores_con_comprobantes->num_rows > 0) {
                        echo '<h3>Trabajadores con comprobantes:</h3>';
                        echo '<ul>';
                        while ($row = $trabajadores_con_comprobantes->fetch_assoc()) {
                            $trabajador_id = $row['trabajador_id'];
                            // Buscar nombre
                            $nombre = 'Desconocido';
                            $query = "SELECT nombre FROM visitantes WHERE id = ?";
                            $stmt = $mysqli->prepare($query);
                            $stmt->bind_param('i', $trabajador_id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            if ($result_row = $result->fetch_assoc()) {
                                $nombre = $result_row['nombre'];
                            }
                            $stmt->close();
                            echo '<li>ID: ' . $trabajador_id . ' - ' . htmlspecialchars($nombre) . '</li>';
                        }
                        echo '</ul>';
                    }
                }
            } else {
                echo '<p class="error">❌ La tabla visitantes no existe</p>';
            }
        }
        echo '</div>';
        ?>
        
        <div class="section">
            <h2>7. Acciones</h2>
            <p><a href="dashboard.php">← Volver al Dashboard</a></p>
            <p><a href="debug_comprobantes.php">Ver diagnóstico simple</a></p>
        </div>
    </div>
</body>
</html>

