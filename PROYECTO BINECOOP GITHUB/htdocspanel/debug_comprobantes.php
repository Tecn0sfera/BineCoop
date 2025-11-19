<?php
/**
 * Script de diagnóstico para comprobantes de pago
 * Ejecutar desde el navegador para verificar el estado
 */

session_start();
if (empty($_SESSION['user']['logged_in']) || $_SESSION['user']['role'] !== 'admin') {
    die('Acceso denegado');
}

$GLOBALS['allowed_config_access'] = true;
require __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/db.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Diagnóstico de Comprobantes</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        .section { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        pre { background: #f0f0f0; padding: 10px; border-radius: 3px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Diagnóstico de Comprobantes de Pago</h1>
    
    <div class="section">
        <h2>1. Conexión a Base de Datos</h2>
        <?php if (isset($mysqli)): ?>
            <p class="success">✓ Conexión mysqli disponible</p>
            <p>Host: <?php echo $mysqli->host_info; ?></p>
            <p>Base de datos: <?php echo $mysqli->query("SELECT DATABASE()")->fetch_row()[0]; ?></p>
        <?php else: ?>
            <p class="error">✗ No hay conexión mysqli</p>
        <?php endif; ?>
    </div>
    
    <div class="section">
        <h2>2. Verificación de Tabla</h2>
        <?php
        if (isset($mysqli)) {
            $check = $mysqli->query("SHOW TABLES LIKE 'comprobantes_pago'");
            if ($check && $check->num_rows > 0):
        ?>
            <p class="success">✓ La tabla comprobantes_pago existe</p>
            <?php
            // Mostrar estructura
            $structure = $mysqli->query("DESCRIBE comprobantes_pago");
            ?>
            <h3>Estructura de la tabla:</h3>
            <pre><?php
            while ($row = $structure->fetch_assoc()) {
                echo $row['Field'] . " | " . $row['Type'] . " | " . $row['Null'] . " | " . $row['Key'] . "\n";
            }
            ?></pre>
        <?php else: ?>
            <p class="error">✗ La tabla comprobantes_pago NO existe</p>
            <p>Ejecutar: <code>create_comprobantes_table.sql</code></p>
        <?php endif; } ?>
    </div>
    
    <div class="section">
        <h2>3. Comprobantes en la Base de Datos</h2>
        <?php
        if (isset($mysqli)) {
            require_once __DIR__ . '/includes/comprobantes_db.php';
            $all = getComprobantes();
            $pendientes = getComprobantesPendientes();
        ?>
            <p class="info">Total de comprobantes: <?php echo count($all); ?></p>
            <p class="info">Comprobantes pendientes: <?php echo count($pendientes); ?></p>
            
            <?php if (!empty($all)): ?>
                <h3>Últimos 10 comprobantes:</h3>
                <table border="1" cellpadding="5" style="border-collapse: collapse; width: 100%;">
                    <tr>
                        <th>ID</th>
                        <th>Trabajador ID</th>
                        <th>Título</th>
                        <th>Estado</th>
                        <th>Fecha Envío</th>
                    </tr>
                    <?php foreach (array_slice($all, 0, 10) as $comp): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($comp['id']); ?></td>
                            <td><?php echo $comp['trabajador_id']; ?></td>
                            <td><?php echo htmlspecialchars($comp['titulo']); ?></td>
                            <td><?php echo htmlspecialchars($comp['estado']); ?></td>
                            <td><?php echo htmlspecialchars($comp['fecha_envio']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p class="error">No hay comprobantes en la base de datos</p>
            <?php endif; ?>
        <?php } ?>
    </div>
    
    <div class="section">
        <h2>4. Prueba de Funciones</h2>
        <?php
        if (isset($mysqli)) {
            echo "<p class='info'>Probando getComprobantesPendientes(5)...</p>";
            $test = getComprobantesPendientes(5);
            echo "<p>Resultado: " . count($test) . " comprobantes encontrados</p>";
            
            if (!empty($test)) {
                echo "<pre>";
                print_r($test[0]);
                echo "</pre>";
            }
        }
        ?>
    </div>
    
    <div class="section">
        <h2>5. Logs Recientes</h2>
        <p class="info">Revisar los logs del servidor para ver mensajes de DEBUG y ERROR</p>
        <p>Buscar en los logs: "comprobantes_db.php", "createComprobante", "saveComprobante"</p>
    </div>
</body>
</html>

