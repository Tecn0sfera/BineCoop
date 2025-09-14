<?php
session_start();

// Activar mostrar errores para debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h3>Debug API Worker</h3>";

// Verificar sesión
echo "<h4>1. Verificación de sesión:</h4>";
echo "SESSION existe: " . (session_id() ? "SÍ" : "NO") . "<br>";
echo "user_token: " . (isset($_SESSION['user_token']) ? "SÍ" : "NO") . "<br>";
echo "user: " . (isset($_SESSION['user']) ? "SÍ" : "NO") . "<br>";

if (isset($_SESSION['user'])) {
    echo "user data: <pre>" . print_r($_SESSION['user'], true) . "</pre>";
}

// Verificar config.php
echo "<h4>2. Verificación de config:</h4>";
try {
    $GLOBALS['allowed_config_access'] = true;
    require_once __DIR__ . '/config.php';
    echo "config.php: CARGADO<br>";
} catch (Exception $e) {
    echo "config.php ERROR: " . $e->getMessage() . "<br>";
}

// Verificar db.php
echo "<h4>3. Verificación de base de datos:</h4>";
try {
    require_once __DIR__ . '/db.php';
    echo "db.php: CARGADO<br>";
    
    if (isset($mysqli)) {
        if ($mysqli->connect_error) {
            echo "MySQL ERROR: " . $mysqli->connect_error . "<br>";
        } else {
            echo "MySQL: CONECTADO<br>";
            echo "Base de datos: " . $mysqli->get_server_info() . "<br>";
        }
    } else {
        echo "Variable \$mysqli no existe<br>";
    }
} catch (Exception $e) {
    echo "db.php ERROR: " . $e->getMessage() . "<br>";
}

// Verificar tablas
echo "<h4>4. Verificación de tablas:</h4>";
if (isset($mysqli) && !$mysqli->connect_error) {
    // Tabla tiempo_trabajado
    $result = $mysqli->query("SHOW TABLES LIKE 'tiempo_trabajado'");
    echo "tiempo_trabajado: " . ($result->num_rows > 0 ? "EXISTE" : "NO EXISTE") . "<br>";
    
    if ($result->num_rows > 0) {
        $desc = $mysqli->query("DESCRIBE tiempo_trabajado");
        echo "Campos tiempo_trabajado:<br>";
        while ($row = $desc->fetch_assoc()) {
            echo "- " . $row['Field'] . " (" . $row['Type'] . ")<br>";
        }
    }
    
    // Tabla reportes_pdf
    $result = $mysqli->query("SHOW TABLES LIKE 'reportes_pdf'");
    echo "<br>reportes_pdf: " . ($result->num_rows > 0 ? "EXISTE" : "NO EXISTE") . "<br>";
    
    if ($result->num_rows > 0) {
        $desc = $mysqli->query("DESCRIBE reportes_pdf");
        echo "Campos reportes_pdf:<br>";
        while ($row = $desc->fetch_assoc()) {
            echo "- " . $row['Field'] . " (" . $row['Type'] . ")<br>";
        }
    }
}

// Verificar permisos de directorio
echo "<h4>5. Verificación de directorios:</h4>";
$upload_dir = __DIR__ . '/uploads/reports/';
echo "Directorio uploads: " . $upload_dir . "<br>";
echo "Existe: " . (file_exists($upload_dir) ? "SÍ" : "NO") . "<br>";
echo "Escribible: " . (is_writable(dirname($upload_dir)) ? "SÍ" : "NO") . "<br>";

// Verificar configuración de PHP
echo "<h4>6. Configuración PHP:</h4>";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "post_max_size: " . ini_get('post_max_size') . "<br>";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "<br>";
echo "file_uploads: " . (ini_get('file_uploads') ? "ON" : "OFF") . "<br>";

echo "<h4>7. Test de POST:</h4>";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "POST recibido:<br>";
    echo "action: " . ($_POST['action'] ?? 'NO DEFINIDA') . "<br>";
    echo "_POST data: <pre>" . print_r($_POST, true) . "</pre>";
    echo "_FILES data: <pre>" . print_r($_FILES, true) . "</pre>";
} else {
    echo "No hay datos POST<br>";
}

?>

<form method="POST" enctype="multipart/form-data">
    <h4>Test de envío:</h4>
    <input type="hidden" name="action" value="register_time">
    <input type="text" name="descripcion" placeholder="Descripción" required><br><br>
    <input type="date" name="fecha" value="<?php echo date('Y-m-d'); ?>" required><br><br>
    <input type="number" name="horas" placeholder="Horas" step="0.25" value="1" required><br><br>
    <button type="submit">Test Registro Tiempo</button>
</form>

<form method="POST" enctype="multipart/form-data">
    <h4>Test de PDF:</h4>
    <input type="hidden" name="action" value="upload_pdf">
    <input type="text" name="titulo" placeholder="Título" required><br><br>
    <textarea name="descripcion" placeholder="Descripción"></textarea><br><br>
    <input type="file" name="pdf_file" accept=".pdf" required><br><br>
    <button type="submit">Test Subir PDF</button>
</form>