<?php
// test_api.php - Script para probar la conexión con user_auth.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Test de Conexión API - " . date('Y-m-d H:i:s') . "</h2>";
echo "<hr>";

// Configuración de prueba
$API_URL = 'https://tectesting.fwh.is/auth/user_auth.php';

// Test 1: Verificar que la URL responde
echo "<h3>1. Verificando acceso a la URL</h3>";
$ch = curl_init($API_URL);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_NOBODY => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_FOLLOWLOCATION => true
]);

$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ Error cURL: " . $error . "<br>";
} else {
    echo "✅ URL accesible - HTTP Code: " . $httpCode . "<br>";
}

// Test 2: Método GET (debe fallar)
echo "<h3>2. Test método GET (debe fallar con 405)</h3>";
$ch = curl_init($API_URL);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => false
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: " . $httpCode . "<br>";
echo "Response: " . htmlspecialchars(substr($response, 0, 200)) . "<br>";

// Test 3: POST sin datos (debe fallar con 400)
echo "<h3>3. Test POST sin datos (debe fallar con 400)</h3>";
$ch = curl_init($API_URL);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json']
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: " . $httpCode . "<br>";
echo "Response: " . htmlspecialchars($response) . "<br>";

// Test 4: POST con credenciales incorrectas (debe fallar con 401)
echo "<h3>4. Test credenciales incorrectas (debe fallar con 401)</h3>";
$testData = [
    'email' => 'test@noexiste.com',
    'password' => 'passwordincorrecto'
];

$ch = curl_init($API_URL);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($testData),
    CURLOPT_TIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json',
        'User-Agent: TestScript/1.0'
    ]
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "HTTP Code: " . $httpCode . "<br>";
echo "cURL Error: " . ($curlError ?: 'Ninguno') . "<br>";
echo "Response: " . htmlspecialchars($response) . "<br>";

if ($response) {
    $decoded = json_decode($response, true);
    if ($decoded) {
        echo "JSON válido: <pre>" . print_r($decoded, true) . "</pre>";
    } else {
        echo "❌ No es JSON válido<br>";
    }
}

// Test 5: Verificar estructura de la base de datos
echo "<h3>5. Verificando estructura de la tabla visitantes</h3>";
try {
    $dsn = "mysql:host=sql211.infinityfree.com;dbname=if0_39215471_admin_panel;charset=utf8mb4";
    $conn = new PDO($dsn, 'if0_39215471', 'RockGuidoNetaNa');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verificar si la tabla existe
    $stmt = $conn->prepare("SHOW TABLES LIKE 'visitantes'");
    $stmt->execute();
    $tableExists = $stmt->fetch();
    
    if ($tableExists) {
        echo "✅ Tabla 'visitantes' existe<br>";
        
        // Ver estructura
        $stmt = $conn->prepare("DESCRIBE visitantes");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<strong>Estructura de la tabla:</strong><br>";
        foreach ($columns as $column) {
            echo "- {$column['Field']} ({$column['Type']}) " . 
                 ($column['Null'] == 'YES' ? 'NULL' : 'NOT NULL') . 
                 ($column['Key'] ? " {$column['Key']}" : '') . "<br>";
        }
        
        // Contar usuarios activos
        $stmt = $conn->prepare("SELECT COUNT(*) as total, SUM(active) as activos FROM visitantes");
        $stmt->execute();
        $counts = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<br><strong>Estadísticas:</strong><br>";
        echo "- Total usuarios: " . $counts['total'] . "<br>";
        echo "- Usuarios activos: " . ($counts['activos'] ?: 0) . "<br>";
        
        // Mostrar algunos usuarios (sin contraseñas)
        $stmt = $conn->prepare("SELECT id, nombre, email, active FROM visitantes LIMIT 3");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($users) {
            echo "<br><strong>Usuarios de ejemplo:</strong><br>";
            foreach ($users as $user) {
                echo "- ID: {$user['id']}, Email: {$user['email']}, Activo: " . 
                     ($user['active'] ? 'Sí' : 'No') . "<br>";
            }
        }
        
    } else {
        echo "❌ Tabla 'visitantes' no existe<br>";
    }
    
} catch (PDOException $e) {
    echo "❌ Error de conexión BD: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h3>Resumen:</h3>";
echo "<p>Si todos los tests anteriores muestran respuestas correctas, tu API debería funcionar.</p>";
echo "<p>Los códigos HTTP esperados son:</p>";
echo "<ul>";
echo "<li>Test 1: 405 (Method Not Allowed) para GET</li>";
echo "<li>Test 2: 400 (Bad Request) para POST sin datos</li>";
echo "<li>Test 3: 401 (Unauthorized) para credenciales incorrectas</li>";
echo "</ul>";

// JavaScript para auto-actualizar cada 30 segundos
echo "<script>";
echo "setTimeout(() => location.reload(), 30000);";
echo "</script>";
?>

<style>
body { 
    font-family: Arial, sans-serif; 
    margin: 20px; 
    background: #f5f5f5; 
}
h2, h3 { 
    color: #333; 
}
pre { 
    background: #eee; 
    padding: 10px; 
    border-radius: 5px; 
}
hr { 
    margin: 20px 0; 
}
</style>