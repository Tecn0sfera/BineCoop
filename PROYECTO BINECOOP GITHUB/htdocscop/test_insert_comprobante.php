<?php
/**
 * Script de prueba para insertar un comprobante directamente desde htdocscop
 * Replica exactamente el flujo de api_worker.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simular sesión (necesario para api_worker.php)
session_start();
$_SESSION['user'] = ['id' => 1]; // ID de trabajador de prueba
$_SESSION['user_token'] = 'test_token';

// Cargar config y db exactamente como api_worker.php
$GLOBALS['allowed_config_access'] = true;
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

// Verificar conexión
if (!isset($mysqli)) {
    die("ERROR: No hay conexión mysqli");
}

// Obtener nombre de la base de datos
$db_name = $mysqli->query("SELECT DATABASE()")->fetch_row()[0];
echo "<h2>Base de datos actual: $db_name</h2>";

// Verificar tabla
$check_table = $mysqli->query("SHOW TABLES LIKE 'comprobantes_pago'");
if (!$check_table || $check_table->num_rows == 0) {
    die("ERROR: La tabla comprobantes_pago no existe");
}

echo "<p>✅ Tabla comprobantes_pago existe</p>";

// Obtener trabajador de prueba
$trabajador_id = 1; // O usar el de la sesión
$query_trabajador = "SELECT id FROM visitantes WHERE estado_aprobacion = 'aprobado' LIMIT 1";
$result_trabajador = $mysqli->query($query_trabajador);
if ($result_trabajador && $result_trabajador->num_rows > 0) {
    $row = $result_trabajador->fetch_assoc();
    $trabajador_id = $row['id'];
    echo "<p>✅ Trabajador encontrado: ID $trabajador_id</p>";
} else {
    echo "<p>⚠️ No se encontró trabajador aprobado, usando ID 1</p>";
}

// Cargar comprobantes_db.php
require_once __DIR__ . '/includes/comprobantes_db.php';

// Verificar conexión después de cargar comprobantes_db.php
if (!isset($mysqli)) {
    die("ERROR: No hay conexión mysqli después de cargar comprobantes_db.php");
}

// Verificar que la tabla existe
$check_table = $mysqli->query("SHOW TABLES LIKE 'comprobantes_pago'");
if (!$check_table || $check_table->num_rows == 0) {
    die("ERROR: La tabla comprobantes_pago no existe después de cargar comprobantes_db.php");
}

echo "<p>✅ Tabla verificada después de cargar comprobantes_db.php</p>";

// Preparar datos del comprobante (igual que api_worker.php)
$titulo_clean = 'Comprobante de Prueba - ' . date('Y-m-d H:i:s');
$descripcion_clean = 'Este es un comprobante de prueba desde htdocscop';
$file_name = 'test_comprobante.pdf';
$file_path = '/uploads/test/test_comprobante.pdf';
$file_size = 1024;

$comprobante_data = [
    'trabajador_id' => $trabajador_id,
    'titulo' => $titulo_clean,
    'descripcion' => $descripcion_clean,
    'nombre_archivo' => $file_name,
    'ruta_archivo' => $file_path,
    'tamano_archivo' => $file_size
];

echo "<h3>Datos del comprobante:</h3>";
echo "<pre>" . print_r($comprobante_data, true) . "</pre>";

// Intentar crear comprobante
echo "<h3>Intentando crear comprobante...</h3>";
error_log("TEST: Intentando crear comprobante para trabajador ID: $trabajador_id");
$comprobante_id = createComprobante($comprobante_data);

if ($comprobante_id) {
    echo "<p style='color: green; font-weight: bold;'>✅ Comprobante creado con ID: $comprobante_id</p>";
    
    // Verificar que realmente se guardó
    $verify = $mysqli->query("SELECT COUNT(*) as total FROM comprobantes_pago WHERE id = '" . $mysqli->real_escape_string($comprobante_id) . "'");
    if ($verify) {
        $verify_row = $verify->fetch_assoc();
        if ($verify_row['total'] > 0) {
            echo "<p style='color: green; font-weight: bold;'>✅ Verificación: Comprobante encontrado en la base de datos</p>";
            
            // Mostrar detalles
            $comp = $mysqli->query("SELECT * FROM comprobantes_pago WHERE id = '" . $mysqli->real_escape_string($comprobante_id) . "'")->fetch_assoc();
            echo "<h3>Detalles del comprobante guardado:</h3>";
            echo "<pre>" . print_r($comp, true) . "</pre>";
        } else {
            echo "<p style='color: red; font-weight: bold;'>❌ Verificación: Comprobante NO encontrado en la base de datos</p>";
        }
    }
    
    // Contar total
    $total = $mysqli->query("SELECT COUNT(*) as total FROM comprobantes_pago")->fetch_assoc()['total'];
    echo "<p>Total de comprobantes en BD: <strong>$total</strong></p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>❌ Error: createComprobante retornó false</p>";
    echo "<p>Revisar logs del servidor para más detalles.</p>";
}

// Mostrar últimos comprobantes
$ultimos = $mysqli->query("SELECT * FROM comprobantes_pago ORDER BY fecha_envio DESC LIMIT 5");
if ($ultimos && $ultimos->num_rows > 0) {
    echo "<h3>Últimos 5 comprobantes:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Trabajador ID</th><th>Título</th><th>Fecha Envío</th><th>Estado</th></tr>";
    while ($row = $ultimos->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['trabajador_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['titulo']) . "</td>";
        echo "<td>" . htmlspecialchars($row['fecha_envio']) . "</td>";
        echo "<td>" . htmlspecialchars($row['estado']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No hay comprobantes en la base de datos</p>";
}

?>

