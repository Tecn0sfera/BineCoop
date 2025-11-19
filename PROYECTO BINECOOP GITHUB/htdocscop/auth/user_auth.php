<?php
// user_auth.php - API de autenticación para usuarios (VERSIÓN FINAL CORREGIDA)

// Configuración de errores (DESACTIVAR en producción)
error_reporting(E_ALL);
ini_set('display_errors', 0); // No mostrar errores en la salida
ini_set('log_errors', 1);

// Manejar errores fatales
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
        }
        echo json_encode([
            'success' => false,
            'error' => 'Error interno del servidor',
            'debug' => $error['message'] . ' en ' . $error['file'] . ' línea ' . $error['line']
        ]);
    }
});

// Headers CORS más específicos (solo si no se han enviado ya)
if (!headers_sent()) {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization, User-Agent');
}

// Manejar preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Método no permitido. Solo POST es aceptado.'
    ]);
    exit();
}

// Función recursiva para buscar archivo (definida fuera para evitar redefinición)
if (!function_exists('searchFileRecursive')) {
    function searchFileRecursive($dir, $filename, $maxDepth, $currentDepth = 0) {
        if ($currentDepth >= $maxDepth || !is_dir($dir)) {
            return null;
        }
        
        $items = @scandir($dir);
        if (!$items) {
            return null;
        }
        
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            
            if (is_file($path) && $item === $filename) {
                return $path;
            }
            
            if (is_dir($path)) {
                $found = searchFileRecursive($path, $filename, $maxDepth, $currentDepth + 1);
                if ($found) {
                    return $found;
                }
            }
        }
        
        return null;
    }
}

// Función para buscar archivo de forma relativa y recursiva
if (!function_exists('findRequiredFile')) {
    function findRequiredFile($filename, $currentDir = null, $maxDepth = 3) {
        if ($currentDir === null) {
            $currentDir = __DIR__;
        }
        
        // Rutas posibles a intentar primero (rutas directas)
        $possiblePaths = [
            // Desde el directorio actual
            $currentDir . DIRECTORY_SEPARATOR . $filename,
            // Subir un nivel
            $currentDir . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $filename,
            // Subir dos niveles
            $currentDir . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $filename,
            // Desde el directorio padre del actual
            dirname($currentDir) . DIRECTORY_SEPARATOR . $filename,
            // Desde el directorio abuelo
            dirname(dirname($currentDir)) . DIRECTORY_SEPARATOR . $filename,
        ];
        
        foreach ($possiblePaths as $path) {
            $normalized = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
            $realPath = realpath($normalized);
            if ($realPath && file_exists($realPath)) {
                return $realPath;
            }
            if (file_exists($normalized)) {
                return $normalized;
            }
        }
        
        // Si no se encuentra en rutas directas, buscar recursivamente
        // Buscar desde el directorio actual y desde el padre
        $searchDirs = [$currentDir, dirname($currentDir)];
        foreach ($searchDirs as $searchDir) {
            if (is_dir($searchDir)) {
                $recursiveResult = searchFileRecursive($searchDir, $filename, $maxDepth);
                if ($recursiveResult) {
                    return $recursiveResult;
                }
            }
        }
        
        return null;
    }
}

// Cargar variables de entorno
// Buscar desde el directorio actual y también desde donde se incluyó este archivo
$searchDirs = [__DIR__];
if (isset($_SERVER['SCRIPT_FILENAME'])) {
    $callingDir = dirname($_SERVER['SCRIPT_FILENAME']);
    if ($callingDir !== __DIR__) {
        $searchDirs[] = $callingDir;
    }
}

$envLoaderPath = null;
foreach ($searchDirs as $searchDir) {
    $envLoaderPath = findRequiredFile('env_loader.php', $searchDir);
    if ($envLoaderPath) {
        break;
    }
}

if ($envLoaderPath && file_exists($envLoaderPath)) {
    // Capturar cualquier salida de env_loader.php
    ob_start();
    try {
        require_once $envLoaderPath;
    } catch (Exception $e) {
        error_log("Error al cargar env_loader.php: " . $e->getMessage());
    }
    $envOutput = ob_get_clean();
    if (!empty($envOutput)) {
        error_log("Advertencia: env_loader.php produjo salida: " . substr($envOutput, 0, 200));
    }
} else {
    // Fallback: intentar ruta relativa estándar
    $fallbackPath = __DIR__ . '/../env_loader.php';
    if (file_exists($fallbackPath)) {
        ob_start();
        try {
            require_once $fallbackPath;
        } catch (Exception $e) {
            error_log("Error al cargar env_loader.php (fallback): " . $e->getMessage());
        }
        $envOutput = ob_get_clean();
        if (!empty($envOutput)) {
            error_log("Advertencia: env_loader.php (fallback) produjo salida: " . substr($envOutput, 0, 200));
        }
    } else {
        error_log("Error: No se pudo encontrar env_loader.php");
    }
}

// Cargar manejador JWT
$jwtHandlerPath = null;
foreach ($searchDirs as $searchDir) {
    $jwtHandlerPath = findRequiredFile('jwt_handler.php', $searchDir);
    if ($jwtHandlerPath) {
        break;
    }
}

if ($jwtHandlerPath && file_exists($jwtHandlerPath)) {
    // Capturar cualquier salida de jwt_handler.php
    ob_start();
    try {
        require_once $jwtHandlerPath;
    } catch (Exception $e) {
        error_log("Error al cargar jwt_handler.php: " . $e->getMessage());
    }
    $jwtOutput = ob_get_clean();
    if (!empty($jwtOutput)) {
        error_log("Advertencia: jwt_handler.php produjo salida: " . substr($jwtOutput, 0, 200));
    }
} else {
    // Fallback: intentar ruta relativa estándar
    $fallbackPath = __DIR__ . '/../jwt_handler.php';
    if (file_exists($fallbackPath)) {
        ob_start();
        try {
            require_once $fallbackPath;
        } catch (Exception $e) {
            error_log("Error al cargar jwt_handler.php (fallback): " . $e->getMessage());
        }
        $jwtOutput = ob_get_clean();
        if (!empty($jwtOutput)) {
            error_log("Advertencia: jwt_handler.php (fallback) produjo salida: " . substr($jwtOutput, 0, 200));
        }
    } else {
        error_log("Error: No se pudo encontrar jwt_handler.php");
    }
}

// Función auxiliar para obtener variables de entorno de forma segura
if (!function_exists('env')) {
    function env($key, $default = null) {
        // Intentar obtener desde $_ENV
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }
        // Intentar obtener desde $_SERVER
        if (isset($_SERVER[$key])) {
            return $_SERVER[$key];
        }
        // Intentar obtener desde getenv()
        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }
        // Retornar valor por defecto
        return $default;
    }
}

// Configuración de la base de datos desde variables de entorno
$DB_CONFIG = [
    'host' => env('DB_HOST', 'localhost'),
    'dbname' => env('DB_NAME', 'nombre_base_datos_ejemplo'),
    'username' => env('DB_USER', 'usuario_ejemplo'),
    'password' => env('DB_PASS', 'contraseña_ejemplo'),
    'charset' => env('DB_CHARSET', 'utf8mb4'),
    'port' => env('DB_PORT', '3306')
];

// Configuración JWT desde variables de entorno (mantener para compatibilidad)
if (!defined('USER_JWT_SECRET')) {
    define('USER_JWT_SECRET', env('JWT_SECRET', 'clave_secreta_jwt_ejemplo_cambiar_en_produccion'));
}

// Log del intento de conexión
error_log("=== INICIO AUTH API - " . date('Y-m-d H:i:s') . " ===");
error_log("Método: " . $_SERVER['REQUEST_METHOD']);
error_log("User-Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'No definido'));
error_log("Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'No definido'));

try {
    // Leer datos del request
    // Si hay datos en GLOBALS (cuando se incluye desde otro script), usarlos
    if (isset($GLOBALS['_POST_JSON_DATA'])) {
        $input = $GLOBALS['_POST_JSON_DATA'];
        error_log("Usando datos de GLOBALS: " . $input);
    } else {
        $input = file_get_contents("php://input");
        error_log("Raw input: " . $input);
    }
    
    if (empty($input)) {
        // Intentar con $_POST si no hay input JSON
        if (!empty($_POST)) {
            $data = $_POST;
            error_log("Usando datos POST: " . print_r($data, true));
        } else {
            throw new Exception("No se recibieron datos de entrada");
        }
    } else {
        $data = json_decode($input, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Error JSON: " . json_last_error_msg() . ". Raw data: " . substr($input, 0, 200));
        }
        error_log("Datos JSON decodificados: " . print_r($data, true));
    }
    
    // Validar campos obligatorios
    if (empty($data['email']) || empty($data['password'])) {
        error_log("Error: Campos faltantes - email: " . ($data['email'] ?? 'vacío') . ", password: " . (empty($data['password']) ? 'vacío' : 'presente'));
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Email y contraseña son obligatorios'
        ]);
        exit();
    }
    
    // Limpiar y validar email
    $email = trim(strtolower($data['email']));
    $password = $data['password'];
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        error_log("Error: Email inválido - " . $email);
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Formato de email inválido'
        ]);
        exit();
    }
    
    error_log("Intentando login para: " . $email);
    
    // Conectar a la base de datos
    $dsn = "mysql:host={$DB_CONFIG['host']};port={$DB_CONFIG['port']};dbname={$DB_CONFIG['dbname']};charset={$DB_CONFIG['charset']}";
    $conn = new PDO($dsn, $DB_CONFIG['username'], $DB_CONFIG['password']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    error_log("Conexión BD exitosa");
    
    // Buscar usuario en la tabla visitantes (usando los nombres correctos de las columnas)
    $stmt = $conn->prepare("SELECT id, nombre, email, password, activo, estado_aprobacion FROM visitantes WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    error_log("Consulta BD ejecutada. Usuario encontrado: " . ($user ? "Sí (ID: {$user['id']})" : "No"));
    
    if (!$user) {
        error_log("Error: Usuario no encontrado - " . $email);
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Credenciales incorrectas'
        ]);
        exit();
    }
    
    // Verificar contraseña
    $passwordValid = password_verify($password, $user['password']);
    error_log("Verificación de contraseña: " . ($passwordValid ? "Correcta" : "Incorrecta"));
    
    if (!$passwordValid) {
        error_log("Error: Contraseña incorrecta para usuario " . $email);
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Credenciales incorrectas'
        ]);
        exit();
    }
    
    // Verificar estado de la cuenta (usando ambas columnas)
    $isActive = $user['activo'] == 1;
    $isApproved = $user['estado_aprobacion'] === 'aprobado';
    
    error_log("Estado cuenta - Activo: " . ($isActive ? "Sí" : "No") . ", Aprobado: " . ($isApproved ? "Sí" : "No ({$user['estado_aprobacion']})"));
    
    if (!$isActive) {
        error_log("Error: Cuenta inactiva para usuario " . $email);
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'Tu cuenta está desactivada. Contacta al administrador.'
        ]);
        exit();
    }
    
    if (!$isApproved) {
        error_log("Error: Cuenta no aprobada para usuario " . $email . " (estado: {$user['estado_aprobacion']})");
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'Cuenta pendiente de aprobación administrativa'
        ]);
        exit();
    }
    
    // Actualizar último acceso
    try {
        $updateStmt = $conn->prepare("UPDATE visitantes SET ultimo_acceso = NOW() WHERE id = ?");
        $updateStmt->execute([$user['id']]);
        error_log("Último acceso actualizado");
    } catch (Exception $e) {
        error_log("No se pudo actualizar ultimo_acceso: " . $e->getMessage());
    }
    
    // Generar token JWT
    $token = generateUserJWT($user);
    error_log("Token JWT generado exitosamente");
    
    // Respuesta exitosa
    http_response_code(200);
    $response = [
        'success' => true,
        'token' => $token,
        'user' => [
            'id' => (int)$user['id'],
            'nombre' => $user['nombre'],
            'email' => $user['email'],
            'activo' => (bool)$user['activo'],
            'estado_aprobacion' => $user['estado_aprobacion']
        ],
        'message' => 'Login exitoso'
    ];
    
    error_log("Login exitoso para: " . $email . " (ID: " . $user['id'] . ")");
    echo json_encode($response);
    
} catch (PDOException $e) {
    error_log("ERROR BD: " . $e->getMessage());
    error_log("DB Config usado: Host=" . $DB_CONFIG['host'] . ", DB=" . $DB_CONFIG['dbname'] . ", User=" . $DB_CONFIG['username']);
    http_response_code(500);
    
    // En desarrollo, mostrar más detalles del error
    $errorResponse = [
        'success' => false,
        'error' => 'Error de conexión a la base de datos'
    ];
    
    // Solo mostrar detalles en desarrollo (APP_ENV === 'development')
    if (env('APP_ENV', 'production') === 'development') {
        $errorResponse['debug'] = $e->getMessage();
        $errorResponse['db_config'] = [
            'host' => $DB_CONFIG['host'],
            'dbname' => $DB_CONFIG['dbname'],
            'username' => $DB_CONFIG['username'],
            'port' => $DB_CONFIG['port']
        ];
    }
    
    echo json_encode($errorResponse);
    
} catch (Exception $e) {
    error_log("ERROR GENERAL: " . $e->getMessage());
    error_log("Trace: " . $e->getTraceAsString());
    http_response_code(500);
    
    $errorResponse = [
        'success' => false,
        'error' => 'Error en el servidor'
    ];
    
    // Solo mostrar detalles en desarrollo
    if (env('APP_ENV', 'production') === 'development') {
        $errorResponse['debug'] = $e->getMessage();
    }
    
    echo json_encode($errorResponse);
}

/**
 * Las funciones generateUserJWT(), validateUserToken() y requireUserAuth()
 * ahora están centralizadas en jwt_handler.php para mejor mantenimiento
 * y consistencia en toda la aplicación.
 */
?>