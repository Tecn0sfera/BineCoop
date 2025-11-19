<?php
// Iniciar buffer de salida al inicio para evitar cualquier salida antes del DOCTYPE
ob_start();

session_start();

// Headers de seguridad contra XSS y otros ataques
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\' https://cdnjs.cloudflare.com; style-src \'self\' \'unsafe-inline\' https://cdnjs.cloudflare.com https://tectesting.fwh.is; img-src \'self\' data: https:; font-src \'self\' https://cdnjs.cloudflare.com;');

// Inicializar variables
$errors = [];
$formData = [
    'email' => '',
    'password' => ''
];

// Función para validar email
function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Función para sanitizar datos contra XSS
function sanitizarInput($dato) {
    if (!is_string($dato)) {
        return '';
    }
    
    // Eliminar caracteres nulos y espacios al inicio/final
    $dato = trim($dato);
    
    // Validar que el string no esté vacío antes de procesar
    if ($dato === '') {
        return '';
    }
    
    // Eliminar caracteres de control excepto saltos de línea y tabulaciones
    // Usar @ para suprimir warnings y verificar resultado
    $resultado = @preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $dato);
    if ($resultado !== null) {
        $dato = $resultado;
    }
    
    // Convertir entidades HTML peligrosas
    $dato = htmlspecialchars($dato, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
    // Eliminar posibles intentos de inyección de scripts
    // Usar @ para suprimir warnings y verificar resultados
    $resultado = @preg_replace('/javascript:/i', '', $dato);
    if ($resultado !== null) {
        $dato = $resultado;
    }
    
    $resultado = @preg_replace('/on\w+\s*=/i', '', $dato);
    if ($resultado !== null) {
        $dato = $resultado;
    }
    
    $resultado = @preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi', '', $dato);
    if ($resultado !== null) {
        $dato = $resultado;
    }
    
    return $dato;
}

// Función para limpiar datos (mantener compatibilidad)
function limpiarDato($dato) {
    return sanitizarInput($dato);
}

// Función para sanitizar datos para uso en atributos HTML
function sanitizarParaAtributo($dato) {
    if (!is_string($dato)) {
        return '';
    }
    
    // Sanitizar primero
    $dato = sanitizarInput($dato);
    
    // Eliminar comillas dobles y simples adicionales para uso seguro en atributos
    $dato = str_replace(['"', "'"], '', $dato);
    
    // Asegurar que no haya caracteres que puedan romper atributos HTML
    $dato = htmlspecialchars($dato, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
    
    return $dato;
}

// Control de intentos fallidos
function checkLoginAttempts() {
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['last_attempt_time'] = time();
    }
    
    // Si han pasado más de 15 minutos, resetear intentos
    if (time() - $_SESSION['last_attempt_time'] > 900) {
        $_SESSION['login_attempts'] = 0;
    }
    
    return $_SESSION['login_attempts'];
}

function incrementLoginAttempts() {
    $_SESSION['login_attempts']++;
    $_SESSION['last_attempt_time'] = time();
}

function getTimeUntilRetry() {
    $timeElapsed = time() - $_SESSION['last_attempt_time'];
    $timeRemaining = 900 - $timeElapsed; // 15 minutos = 900 segundos
    return max(0, $timeRemaining);
}

// Detectar si es una petición AJAX al inicio
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
         strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Si es AJAX, asegurar que no haya salida antes del JSON
if ($isAjax && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Limpiar cualquier buffer de salida al inicio para peticiones AJAX
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    ob_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar intentos de login
    $attempts = checkLoginAttempts();
    
    if ($attempts >= 5) {
        $timeRemaining = getTimeUntilRetry();
        if ($timeRemaining > 0) {
            $minutes = ceil($timeRemaining / 60);
            $errorMessage = "Demasiados intentos fallidos. Inténtalo de nuevo en $minutes minutos.";
            
            // Si es AJAX, devolver JSON y salir
            if ($isAjax) {
                while (ob_get_level() > 0) {
                    ob_end_clean();
                }
                if (!headers_sent()) {
                    header('Content-Type: application/json; charset=utf-8');
                }
                echo json_encode([
                    'success' => false,
                    'error' => $errorMessage
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit();
            }
            
            $errors['general'] = $errorMessage;
        } else {
            $_SESSION['login_attempts'] = 0;
        }
    }
    
    if (empty($errors)) {
        // Sanitizar y validar datos del formulario
        $rawEmail = $_POST['email'] ?? '';
        $rawPassword = $_POST['password'] ?? '';
        
        // Validar que sean strings y no arrays u otros tipos
        if (!is_string($rawEmail)) {
            $rawEmail = '';
        }
        if (!is_string($rawPassword)) {
            $rawPassword = '';
        }
        
        // Asegurar que los valores no sean null antes de procesar
        $rawEmail = (string)$rawEmail;
        $rawPassword = (string)$rawPassword;
        
        // Sanitizar email (permitir @ y puntos para emails válidos)
        $rawEmail = trim($rawEmail);
        // Eliminar solo caracteres peligrosos pero mantener @ y puntos para emails
        // Usar @ para suprimir warnings y verificar resultado
        $resultado = @preg_replace('/[<>"\']/', '', $rawEmail);
        if ($resultado !== null) {
            $rawEmail = $resultado;
        }
        $formData['email'] = sanitizarInput($rawEmail);
        
        // Sanitizar contraseña (eliminar caracteres peligrosos pero mantener la estructura)
        $rawPassword = trim($rawPassword);
        // Eliminar caracteres de control y nulos
        // Usar @ para suprimir warnings y verificar resultado
        $resultado = @preg_replace('/[\x00-\x1F\x7F]/', '', $rawPassword);
        if ($resultado !== null) {
            $rawPassword = $resultado;
        }
        // Limitar longitud máxima
        if (strlen($rawPassword) > 1000) {
            $rawPassword = substr($rawPassword, 0, 1000);
        }
        $formData['password'] = $rawPassword;
        
        // Validar email (formato de email válido)
        if (empty($formData['email'])) {
            $errors['email'] = 'El email es obligatorio';
        } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Por favor ingresa un email válido';
        } elseif (strlen($formData['email']) > 255) {
            $errors['email'] = 'El email no puede exceder 255 caracteres';
        } elseif (strlen($formData['email']) < 1) {
            $errors['email'] = 'El email no puede estar vacío';
        }
        
        // Validar contraseña
        if (empty($formData['password'])) {
            $errors['password'] = 'La contraseña es obligatoria';
        } elseif (strlen($formData['password']) < 4) {
            $errors['password'] = 'La contraseña es demasiado corta';
        } elseif (strlen($formData['password']) > 1000) {
            $errors['password'] = 'La contraseña es demasiado larga';
        }
        
        // Si no hay errores de validación, proceder con el login
        if (empty($errors)) {
            // Usar include directo en lugar de cURL para evitar problemas de redirección
            // Buscar el archivo de API de forma relativa
            $apiFilePath = null;
            
            // Obtener el directorio actual del script
            $currentDir = __DIR__;
            
            // Función auxiliar para buscar archivo recursivamente desde el directorio actual
            function findUserAuthFile($dir, $maxDepth = 3, $currentDepth = 0) {
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
                    
                    // Si es el archivo que buscamos y está en la estructura correcta
                    if (is_file($path) && $item === 'user_auth.php') {
                        $parent = dirname($path);
                        if (basename($parent) === 'auth') {
                            return $path;
                        }
                    }
                    
                    // Buscar recursivamente en subdirectorios
                    if (is_dir($path)) {
                        $found = findUserAuthFile($path, $maxDepth, $currentDepth + 1);
                        if ($found) {
                            return $found;
                        }
                    }
                }
                
                return null;
            }
            
            // Intentar múltiples rutas posibles
            // Buscar de forma relativa desde el directorio del script actual
            $possiblePaths = [
                // Ruta 1: Relativa desde el directorio actual del script (./auth/user_auth.php)
                $currentDir . DIRECTORY_SEPARATOR . 'auth' . DIRECTORY_SEPARATOR . 'user_auth.php',
                // Ruta 2: Relativa desde el directorio actual usando ./ (explícito)
                $currentDir . DIRECTORY_SEPARATOR . '.' . DIRECTORY_SEPARATOR . 'auth' . DIRECTORY_SEPARATOR . 'user_auth.php',
                // Ruta 3: Subir un nivel y buscar auth (../auth/user_auth.php)
                $currentDir . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'auth' . DIRECTORY_SEPARATOR . 'user_auth.php',
                // Ruta 4: Subir dos niveles y buscar auth (../../auth/user_auth.php)
                $currentDir . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'auth' . DIRECTORY_SEPARATOR . 'user_auth.php',
                // Ruta 5: Usando barras normales desde current dir (./auth/user_auth.php)
                str_replace('\\', '/', $currentDir) . '/auth/user_auth.php',
                // Ruta 6: Usando barras normales subiendo un nivel (../auth/user_auth.php)
                str_replace('\\', '/', $currentDir) . '/../auth/user_auth.php',
                // Ruta 7: Usando realpath con ruta relativa
                realpath($currentDir . DIRECTORY_SEPARATOR . 'auth' . DIRECTORY_SEPARATOR . 'user_auth.php'),
                // Ruta 8: Usando realpath subiendo un nivel
                realpath($currentDir . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'auth' . DIRECTORY_SEPARATOR . 'user_auth.php'),
            ];
            
            // Normalizar todas las rutas y buscar el archivo
            foreach ($possiblePaths as $idx => $path) {
                if (!$path) {
                    continue;
                }
                
                // Normalizar la ruta
                $normalizedPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
                
                // Intentar con realpath si es posible
                $realPath = realpath($normalizedPath);
                if ($realPath && file_exists($realPath)) {
                    $apiFilePath = $realPath;
                    error_log("Archivo de API encontrado en ruta " . ($idx + 1) . ": " . $apiFilePath);
                    break;
                }
                
                // Si realpath falla, intentar con la ruta normalizada directamente
                if (file_exists($normalizedPath)) {
                    $apiFilePath = $normalizedPath;
                    error_log("Archivo de API encontrado en ruta " . ($idx + 1) . " (sin realpath): " . $apiFilePath);
                    break;
                }
            }
            
            // Si no se encontró en rutas directas, buscar recursivamente desde el directorio actual
            if (!$apiFilePath) {
                error_log("No se encontró en rutas directas, buscando recursivamente desde el directorio actual...");
                $recursiveSearch = findUserAuthFile($currentDir);
                if ($recursiveSearch && file_exists($recursiveSearch)) {
                    $apiFilePath = $recursiveSearch;
                    error_log("Archivo de API encontrado mediante búsqueda recursiva: " . $apiFilePath);
                }
            }
            
            if (!$apiFilePath || !file_exists($apiFilePath)) {
                error_log("Error: Archivo de API no encontrado.");
                error_log("Directorio actual (__DIR__): " . $currentDir);
                error_log("Rutas intentadas:");
                foreach ($possiblePaths as $idx => $path) {
                    $normalized = $path ? str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path) : 'null';
                    $exists = $path && file_exists($normalized) ? 'EXISTE' : 'NO EXISTE';
                    error_log("  Ruta " . ($idx + 1) . ": " . $normalized . " - " . $exists);
                }
                
                $errors['general'] = 'Error de configuración del servidor. El archivo de autenticación no se encuentra. Contacta al administrador.';
            } else {
                // El archivo existe, proceder con el login
            // Asegurar que los datos estén sanitizados antes de enviar
            $safeEmail = $formData['email'];
            $safePassword = $formData['password'];
            
            // Validar que no sean arrays u otros tipos antes de json_encode
            if (!is_string($safeEmail) || !is_string($safePassword)) {
                $errors['general'] = 'Error en los datos enviados';
            } else {
                    // Crear el JSON con los datos del formulario
                    $jsonData = json_encode([
                    'email' => $safeEmail,
                    'password' => $safePassword
                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    
                    // Guardar los datos JSON en GLOBALS para que el API los pueda leer
                    $GLOBALS['_POST_JSON_DATA'] = $jsonData;
                    
                    // Guardar el método original
                    $originalMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
                    $_SERVER['REQUEST_METHOD'] = 'POST';
                    
                    // Incluir el archivo API y capturar su salida
                    try {
                        // Limpiar cualquier salida previa antes de incluir el API
                        // Esto es crítico para evitar capturar HTML previo
                        while (ob_get_level() > 0) {
                            ob_end_clean();
                        }
                        
                        // Iniciar un nuevo buffer limpio para capturar solo la salida del API
                        // Asegurar que no haya buffers previos que interfieran
                        while (ob_get_level() > 0) {
                            ob_end_clean();
                        }
                        ob_start();
                        
                        // Incluir el archivo API
                        include $apiFilePath;
                        
                        // Obtener la respuesta capturada
                        $response = ob_get_clean();
                        
                        // IMPORTANTE: Si la respuesta contiene JSON y el login fue exitoso,
                        // debemos redirigir ANTES de que se muestre cualquier HTML
                        // No continuar procesando si tenemos una respuesta JSON válida
                        
                        // Si la respuesta está vacía, podría ser un problema
                        if (empty($response)) {
                            error_log("Advertencia: user_auth.php no devolvió ninguna respuesta");
                            // Si es AJAX, devolver JSON de error
                            if ($isAjax) {
                                while (ob_get_level() > 0) {
                                    ob_end_clean();
                                }
                                if (!headers_sent()) {
                                    header('Content-Type: application/json; charset=utf-8');
                                }
                                echo json_encode([
                                    'success' => false,
                                    'error' => 'El servidor no devolvió una respuesta válida'
                                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                                exit();
                            }
                            throw new Exception('El servidor no devolvió una respuesta válida');
                        }
                        
                        // Limpiar cualquier salida HTML que pueda haber quedado
                        // Si la respuesta contiene HTML, intentar extraer solo el JSON
                        if (strpos($response, '<!DOCTYPE') !== false || strpos($response, '<html') !== false) {
                            // Buscar JSON dentro del HTML usando una expresión más robusta
                            // Buscar el último JSON válido en la respuesta
                            if (preg_match_all('/\{[^{}]*(?:\{[^{}]*\}[^{}]*)*\}/s', $response, $matches)) {
                                // Tomar el último match (que debería ser el JSON del API)
                                $response = end($matches[0]);
                    } else {
                                // Si no se encuentra JSON, es un error
                                error_log("Error: user_auth.php devolvió HTML en lugar de JSON.");
                                error_log("Primeros 500 caracteres de la respuesta: " . substr($response, 0, 500));
                                // Si es AJAX, devolver JSON de error
                                if ($isAjax) {
                                    while (ob_get_level() > 0) {
                                        ob_end_clean();
                                    }
                                    if (!headers_sent()) {
                                        header('Content-Type: application/json; charset=utf-8');
                                    }
                                    echo json_encode([
                                        'success' => false,
                                        'error' => 'El servidor devolvió una respuesta inválida (HTML en lugar de JSON)'
                                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                                    exit();
                                }
                                throw new Exception('El servidor devolvió una respuesta inválida');
                            }
                        }
                        
                        // Limpiar espacios en blanco, saltos de línea y caracteres de control al inicio y final
                        $response = trim($response);
                        
                        // Buscar el primer { que parece ser el inicio de un JSON válido
                        $firstBrace = strpos($response, '{');
                        if ($firstBrace === false) {
                            error_log("Error: No se encontró '{' en la respuesta. Primeros caracteres: " . substr($response, 0, 200));
                            // Si es AJAX, devolver JSON de error
                            if ($isAjax) {
                                while (ob_get_level() > 0) {
                                    ob_end_clean();
                                }
                                if (!headers_sent()) {
                                    header('Content-Type: application/json; charset=utf-8');
                                }
                                echo json_encode([
                                    'success' => false,
                                    'error' => 'El servidor devolvió una respuesta inválida (no se encontró JSON)'
                                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                                exit();
                            }
                            throw new Exception('El servidor devolvió una respuesta inválida');
                        }
                        
                        // Si hay contenido antes del primer {, eliminarlo
                        if ($firstBrace > 0) {
                            $response = substr($response, $firstBrace);
                        }
                        
                        // Buscar el último } que cierra el JSON
                        // Necesitamos encontrar el } que cierra el objeto principal
                        $braceCount = 0;
                        $lastBrace = -1;
                        for ($i = 0; $i < strlen($response); $i++) {
                            if ($response[$i] === '{') {
                                $braceCount++;
                            } elseif ($response[$i] === '}') {
                                $braceCount--;
                                if ($braceCount === 0) {
                                    $lastBrace = $i;
                                    break; // Encontramos el } que cierra el objeto principal
                                }
                            }
                        }
                        
                        if ($lastBrace === -1) {
                            error_log("Error: No se encontró el '}' de cierre del JSON. Respuesta: " . substr($response, 0, 500));
                            throw new Exception('El servidor devolvió una respuesta inválida');
                        }
                        
                        // Extraer solo el JSON válido
                        $response = substr($response, 0, $lastBrace + 1);
                        
                        // Verificar que la respuesta comience con { (JSON válido)
                        if (substr($response, 0, 1) !== '{') {
                            error_log("Error: La respuesta no comienza con '{'. Primeros caracteres: " . substr($response, 0, 100));
                            throw new Exception('El servidor devolvió una respuesta inválida');
                        }
                        
                        // Verificar que la respuesta termine con } (JSON válido)
                        if (substr($response, -1) !== '}') {
                            error_log("Error: La respuesta no termina con '}'. Últimos caracteres: " . substr($response, -100));
                            throw new Exception('El servidor devolvió una respuesta inválida');
                        }
                        
                        // Restaurar método original
                        $_SERVER['REQUEST_METHOD'] = $originalMethod;
                        
                        // Limpiar GLOBALS
                        unset($GLOBALS['_POST_JSON_DATA']);
                        
                        // Obtener el código HTTP (puede que no esté disponible si se incluyó)
                        $httpCode = http_response_code();
                        if (!$httpCode) {
                            $httpCode = 200; // Por defecto
                        }
                        
                        // Intentar decodificar la respuesta JSON
                        $result = json_decode($response, true);
                        
                        // Si no se pudo decodificar, loggear el error
                        if ($result === null && json_last_error() !== JSON_ERROR_NONE) {
                            error_log("Error decodificando JSON: " . json_last_error_msg());
                            error_log("Respuesta recibida (primeros 1000 caracteres): " . substr($response, 0, 1000));
                            // Si es AJAX, devolver JSON de error
                            if ($isAjax) {
                                while (ob_get_level() > 0) {
                                    ob_end_clean();
                                }
                                if (!headers_sent()) {
                                    header('Content-Type: application/json; charset=utf-8');
                                }
                                echo json_encode([
                                    'success' => false,
                                    'error' => 'Error al procesar la respuesta del servidor: ' . json_last_error_msg()
                                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                                exit();
                            }
                            throw new Exception('Error al procesar la respuesta del servidor: ' . json_last_error_msg());
                        }
                        
                        // Determinar si es una petición AJAX (antes de procesar la respuesta)
                        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                                 strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
                        
                        // CRÍTICO: Si el login fue exitoso, redirigir INMEDIATAMENTE
                        // No continuar procesando si tenemos un login exitoso
                        if ($httpCode === 200 && $result && isset($result['success']) && $result['success']) {
                            error_log("LOGIN EXITOSO - Iniciando proceso de redirección");
                            
                            // Login exitoso
                            $_SESSION['user_token'] = $result['token'] ?? null;
                            $_SESSION['user'] = $result['user'] ?? null;
                            $_SESSION['login_attempts'] = 0; // Resetear intentos
                            
                            error_log("Sesión guardada - user_token: " . (isset($_SESSION['user_token']) ? 'presente' : 'ausente'));
                            error_log("Sesión guardada - user: " . (isset($_SESSION['user']) ? 'presente' : 'ausente'));
                            
                            // Regenerar ID de sesión por seguridad
                            session_regenerate_id(true);
                            
                            // Redirección con verificación
                            $redirectUrl = $_SESSION['redirect_after_login'] ?? 'dashboard.php';
                            unset($_SESSION['redirect_after_login']);
                            
                            error_log("URL de redirección: $redirectUrl");
                            
                            // CRÍTICO: Limpiar TODA la salida antes de redirigir
                            // Esto incluye cualquier JSON que pueda haber quedado en los buffers
                            // IMPORTANTE: La respuesta JSON ya fue capturada con ob_get_clean(),
                            // así que NO debería haber nada en los buffers, pero lo limpiamos por seguridad
                            while (ob_get_level() > 0) {
                                ob_end_clean();
                            }
                            
                            error_log("Buffers limpiados. Verificando headers...");
                            
                            // Verificar si los headers ya se enviaron
                            if (headers_sent($file, $line)) {
                                error_log("ERROR CRÍTICO: Headers ya enviados en $file:$line antes de redirigir");
                                error_log("Esto significa que hay output antes de la redirección");
                                error_log("Esto NO debería pasar - hay un problema con el flujo del código");
                                // Si los headers ya se enviaron, NO podemos usar header()
                                // En este caso, el navegador ya recibió el JSON, así que debemos
                                // usar JavaScript para redirigir desde el cliente
                                // Pero como ya se mostró el JSON, esto es un fallback de emergencia
                                
                                // Limpiar TODO antes de mostrar el fallback
                                while (ob_get_level() > 0) {
                                    ob_end_clean();
                                }
                                
                                // Mostrar solo un HTML mínimo con JavaScript para redirigir
                                echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><meta http-equiv='refresh' content='0;url=" . htmlspecialchars($redirectUrl, ENT_QUOTES, 'UTF-8') . "'></head><body><script>window.location.href='" . htmlspecialchars($redirectUrl, ENT_QUOTES, 'UTF-8') . "';</script><noscript><meta http-equiv='refresh' content='0;url=" . htmlspecialchars($redirectUrl, ENT_QUOTES, 'UTF-8') . "'></noscript><p>Redirigiendo... <a href='" . htmlspecialchars($redirectUrl, ENT_QUOTES, 'UTF-8') . "'>Haz clic aquí</a></p></body></html>";
                                
                                die(); // Detener ejecución inmediatamente
                            }
                            
                            error_log("Headers OK. Redirigiendo a: $redirectUrl");
                            
                            // Redirigir usando header Location (método tradicional y confiable)
                            // Esto SOLO funciona si los headers NO se han enviado
                            header("Location: $redirectUrl", true, 302);
                            
                            error_log("Header Location enviado. Haciendo die()...");
                            
                            // CRÍTICO: Usar die() en lugar de exit() para asegurar que se detenga
                            // También limpiar cualquier buffer restante antes de salir
                            while (ob_get_level() > 0) {
                                ob_end_clean();
                            }
                            
                            // Asegurar que no haya ningún output antes de die()
                            if (ob_get_level() > 0) {
                                ob_end_clean();
                            }
                            
                            die(); // CRÍTICO: Esto DEBE detener el script completamente
                        } else {
                            // Login fallido
                            incrementLoginAttempts();
                            
                            // Si es AJAX, devolver JSON y salir (ya detectado al inicio)
                            if ($isAjax) {
                                // Limpiar cualquier salida previa
                                while (ob_get_level() > 0) {
                                    ob_end_clean();
                                }
                                
                                if (!headers_sent()) {
                                    header('Content-Type: application/json; charset=utf-8');
                                }
                                
                                $errorMessage = 'Error en el servidor';
                                if (isset($result['error'])) {
                                    $errorMessage = sanitizarInput($result['error']);
                                } elseif (isset($result['debug'])) {
                                    $errorMessage = sanitizarInput($result['error'] ?? 'Error en el servidor') . ' (Debug: ' . sanitizarInput($result['debug']) . ')';
                                } elseif ($httpCode === 401) {
                                    $errorMessage = 'Email o contraseña incorrectos';
                                } elseif ($httpCode === 403) {
                                    $errorMessage = 'Tu cuenta está pendiente de aprobación';
                                } elseif ($httpCode === 500) {
                                    $errorMessage = 'Error en el servidor. Por favor, verifica los logs del sistema.';
                                    error_log("Error 500 en login - HTTP Code: $httpCode, Response: " . substr($response, 0, 500));
                                } else {
                                    $errorMessage = 'Error en el servidor (Código: ' . $httpCode . ')';
                                    if (isset($result['error'])) {
                                        $errorMessage .= ': ' . sanitizarInput($result['error']);
                                    }
                                    error_log("Error en login - HTTP Code: $httpCode, Response: " . substr($response, 0, 500));
                                }
                                
                                $remainingAttempts = 5 - $_SESSION['login_attempts'];
                                if ($remainingAttempts > 0 && $remainingAttempts <= 2) {
                                    $errorMessage .= " (Te quedan $remainingAttempts intentos)";
                                }
                                
                                echo json_encode([
                                    'success' => false,
                                    'error' => $errorMessage
                                ]);
                                exit();
                            }
                            
                            // Si no es AJAX, establecer error para mostrar en el formulario
                            if (isset($result['error'])) {
                                $errors['general'] = sanitizarInput($result['error']);
                            } elseif (isset($result['debug'])) {
                                // En desarrollo, mostrar información de debug
                                $errors['general'] = sanitizarInput($result['error'] ?? 'Error en el servidor') . ' (Debug: ' . sanitizarInput($result['debug']) . ')';
                            } elseif ($httpCode === 401) {
                                $errors['general'] = 'Email o contraseña incorrectos';
                            } elseif ($httpCode === 403) {
                                $errors['general'] = 'Tu cuenta está pendiente de aprobación';
                            } elseif ($httpCode === 500) {
                                $errors['general'] = 'Error en el servidor. Por favor, verifica los logs del sistema.';
                                error_log("Error 500 en login - HTTP Code: $httpCode, Response: " . substr($response, 0, 500));
                            } else {
                                $errorMsg = 'Error en el servidor (Código: ' . $httpCode . ')';
                                if (isset($result['error'])) {
                                    $errorMsg .= ': ' . sanitizarInput($result['error']);
                                }
                                $errors['general'] = $errorMsg;
                                error_log("Error en login - HTTP Code: $httpCode, Response: " . substr($response, 0, 500));
                            }
                            
                            $remainingAttempts = 5 - $_SESSION['login_attempts'];
                            if ($remainingAttempts > 0 && $remainingAttempts <= 2) {
                                $errors['general'] .= " (Te quedan $remainingAttempts intentos)";
                            }
                        }
                    } catch (Exception $e) {
                        // Limpiar buffers
                        while (ob_get_level() > 0) {
                            ob_end_clean();
                        }
                        // Restaurar método original
                        $_SERVER['REQUEST_METHOD'] = $originalMethod;
                        // Limpiar GLOBALS
                        unset($GLOBALS['_POST_JSON_DATA']);
                        
                        error_log("Excepción al incluir API de autenticación: " . $e->getMessage());
                        error_log("Trace: " . $e->getTraceAsString());
                        
                        // Si es AJAX, devolver JSON y salir (ya detectado al inicio)
                        if ($isAjax) {
                            if (!headers_sent()) {
                                header('Content-Type: application/json; charset=utf-8');
                            }
                            echo json_encode([
                                'success' => false,
                                'error' => 'Error al procesar el login. Inténtalo más tarde.'
                            ]);
                            exit();
                        }
                        
                        // Si no es AJAX, establecer error para mostrar en el formulario
                        $errors['general'] = 'Error al procesar el login. Inténtalo más tarde.';
                    }
                }
            }
        }
    }
    
    // Si es AJAX y llegamos aquí sin haber devuelto JSON, algo salió mal
    // Devolver un error genérico
    if ($isAjax) {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode([
            'success' => false,
            'error' => 'Error al procesar la solicitud'
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit();
    }
}

// Limpiar cualquier buffer de salida que pueda haber quedado antes de mostrar el HTML
// Esto es importante para evitar que JSON o errores se muestren en la página
while (ob_get_level() > 0) {
    ob_end_clean();
}

// Limpiar contraseña del formulario por seguridad
$formData['password'] = '';

// Asegurar que no haya salida antes del DOCTYPE (crítico para evitar modo Quirks)
// El buffer inicial ya fue limpiado arriba, así que ahora podemos empezar el HTML limpio
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Cooperativa</title>
    <link rel="stylesheet" href="https://tectesting.fwh.is/assets/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .login-attempts-warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .login-loading {
            display: none;
            text-align: center;
            padding: 10px;
        }
        .login-loading .spinner {
            border: 2px solid #f3f3f3;
            border-top: 2px solid #007bff;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
            display: inline-block;
            margin-right: 10px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }
        #manualRedirectBtn {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 10px;
        }
        #manualRedirectBtn:hover {
            background-color: #218838;
        }
        .form-group {
            position: relative;
        }
        .input-with-icon {
            position: relative;
        }
        .caps-lock-warning {
            display: none;
            color: #ffc107;
            font-size: 0.85em;
            margin-top: 5px;
        }
        .remember-me {
            display: flex;
            align-items: center;
            margin: 15px 0;
            font-size: 0.9em;
        }
        .remember-me input[type="checkbox"] {
            margin-right: 8px;
        }
        body {
            background-image: url('./we.webp');
            background-size: cover;
            background-repeat: repeat;
        } 
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Iniciar Sesión</h1>
            <p>Sistema de Cooperativa</p>
        </div>
        
        <?php if (isset($_SESSION['registration_success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                ¡Registro exitoso! Tu cuenta está pendiente de aprobación.
            </div>
            <?php unset($_SESSION['registration_success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['logout_success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-sign-out-alt"></i>
                Has cerrado sesión correctamente.
            </div>
            <?php unset($_SESSION['logout_success']); ?>
        <?php endif; ?>
        
        <?php
            $serverErrorMessage = $errors['general'] ?? '';
            $showServerError = !empty($serverErrorMessage);
        ?>
        <div class="alert alert-danger" id="loginError" style="<?php echo $showServerError ? '' : 'display: none;'; ?>">
                <i class="fas fa-exclamation-circle"></i>
            <?php echo $showServerError ? htmlspecialchars($serverErrorMessage) : ''; ?>
            </div>
        
        <div class="alert alert-success" id="loginSuccess" style="display: none;">
            <i class="fas fa-check-circle"></i>
            <span id="loginSuccessMessage">Login exitoso. Redirigiendo...</span>
            <br>
            <button type="button" id="manualRedirectBtn" class="btn btn-primary mt-2" style="display: none;">
                Ir al Dashboard
            </button>
        </div>
        
        <?php if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= 3 && $_SESSION['login_attempts'] < 5): ?>
            <div class="login-attempts-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Advertencia:</strong> Has fallado <?php echo $_SESSION['login_attempts']; ?> veces. 
                Después de 5 intentos fallidos, tu acceso será bloqueado temporalmente.
            </div>
        <?php endif; ?>
        
        <div class="login-loading" id="loginLoading">
            <div class="spinner"></div>
            Iniciando sesión...
        </div>
        
        <form method="POST" class="login-form" id="loginForm" novalidate>
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email</label>
                <div class="input-with-icon">
                    <input type="email" id="email" name="email" required
                           value="<?php echo htmlspecialchars($formData['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                           class="<?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>"
                           maxlength="255"
                           autocomplete="email">
                </div>
                <?php if (isset($errors['email'])): ?>
                    <span class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($errors['email']); ?>
                    </span>
                <?php endif; ?>
                <span class="error-message" id="emailError" style="display: none;">
                    <i class="fas fa-exclamation-circle"></i>
                    Por favor ingresa un email válido
                </span>
            </div>
            
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Contraseña</label>
                <div class="input-with-icon">
                    <input type="password" id="password" name="password" required
                           class="<?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>"
                           autocomplete="current-password">
                    <i class="fas fa-eye password-toggle" id="passwordToggle" title="Mostrar/Ocultar contraseña"></i>
                </div>
                <?php if (isset($errors['password'])): ?>
                    <span class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($errors['password']); ?>
                    </span>
                <?php endif; ?>
                <span class="error-message" id="passwordError" style="display: none;">
                    <i class="fas fa-exclamation-circle"></i>
                    La contraseña es obligatoria
                </span>
                <div class="caps-lock-warning" id="capsLockWarning">
                    <i class="fas fa-exclamation-triangle"></i>
                    Mayús activado
                </div>
                <small class="form-text">
                    <a href="#">¿Olvidaste tu contraseña?</a>
                </small>
            </div>
            
            <div class="remember-me">
                <input type="checkbox" id="remember_me" name="remember_me">
                <label for="remember_me">Recordar mis datos</label>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="login-btn" id="submitBtn">
                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                </button>
            </div>
            
            <div class="form-footer">
                <p>¿No tienes una cuenta? <a href="register.php">Regístrate aquí</a></p>
            </div>
        </form>
    </div>

    <script>
        // Función para sanitizar inputs contra XSS
        function sanitizeInput(input) {
            if (typeof input !== 'string') {
                return '';
            }
            
            // Eliminar espacios al inicio y final
            let sanitized = input.trim();
            
            // Eliminar caracteres de control y nulos
            sanitized = sanitized.replace(/[\x00-\x1F\x7F]/g, '');
            
            // Eliminar posibles scripts y eventos
            sanitized = sanitized.replace(/javascript:/gi, '');
            sanitized = sanitized.replace(/on\w+\s*=/gi, '');
            sanitized = sanitized.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '');
            sanitized = sanitized.replace(/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/gi, '');
            sanitized = sanitized.replace(/<object\b[^<]*(?:(?!<\/object>)<[^<]*)*<\/object>/gi, '');
            sanitized = sanitized.replace(/<embed\b[^<]*>/gi, '');
            
            // Escapar caracteres HTML peligrosos usando DOM
            const div = document.createElement('div');
            div.textContent = sanitized;
            sanitized = div.textContent || div.innerText || '';
            
            return sanitized;
        }
        
        // Función para sanitizar para uso en atributos HTML
        function sanitizeForAttribute(input) {
            let sanitized = sanitizeInput(input);
            // Eliminar comillas que podrían romper atributos
            sanitized = sanitized.replace(/["']/g, '');
            return sanitized;
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const passwordToggle = document.getElementById('passwordToggle');
            const submitBtn = document.getElementById('submitBtn');
            const loginLoading = document.getElementById('loginLoading');
            const rememberMeCheckbox = document.getElementById('remember_me');
            
            // Cargar datos recordados
            loadRememberedData();
            
            // Toggle para mostrar/ocultar contraseña
            passwordToggle.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                if (type === 'text') {
                    passwordToggle.classList.remove('fa-eye');
                    passwordToggle.classList.add('fa-eye-slash');
                    passwordToggle.title = 'Ocultar contraseña';
                } else {
                    passwordToggle.classList.remove('fa-eye-slash');
                    passwordToggle.classList.add('fa-eye');
                    passwordToggle.title = 'Mostrar contraseña';
                }
            });
            
            // Detectar Caps Lock
            passwordInput.addEventListener('keyup', function(event) {
                const capsLockWarning = document.getElementById('capsLockWarning');
                if (event.getModifierState && event.getModifierState('CapsLock')) {
                    capsLockWarning.style.display = 'block';
                } else {
                    capsLockWarning.style.display = 'none';
                }
            });
            
            // Validación en tiempo real del email
            emailInput.addEventListener('input', function() {
                validateEmail();
            });
            
            // Validación cuando el campo pierde el foco
            emailInput.addEventListener('blur', function() {
                validateEmail();
            });
            
            // Validación en tiempo real de la contraseña
            passwordInput.addEventListener('input', function() {
                validatePassword();
            });
            
            function validateEmail() {
                // Sanitizar primero (permitir @ y puntos para emails)
                let email = emailInput.value.trim();
                // Eliminar solo caracteres peligrosos pero mantener @ y puntos
                email = email.replace(/[<>"']/g, '');
                
                // Actualizar el valor del input con el valor sanitizado si cambió
                if (emailInput.value.trim() !== email) {
                    emailInput.value = email;
                }
                
                // Validar formato de email
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                const errorElement = document.getElementById('emailError');
                
                // Si está vacío, no validar (se encarga el atributo required)
                if (!email) {
                    emailInput.classList.remove('is-invalid');
                    errorElement.style.display = 'none';
                    return true;
                }
                
                // Validar formato de email
                if (!emailRegex.test(email)) {
                    emailInput.classList.add('is-invalid');
                    errorElement.style.display = 'block';
                    return false;
                } else {
                    emailInput.classList.remove('is-invalid');
                    errorElement.style.display = 'none';
                    return true;
                }
            }
            
            function validatePassword() {
                // Sanitizar contraseña (eliminar caracteres peligrosos pero mantener estructura)
                let password = passwordInput.value;
                // Eliminar caracteres de control y nulos
                password = password.replace(/[\x00-\x1F\x7F]/g, '');
                
                // Limitar longitud máxima
                if (password.length > 1000) {
                    password = password.substring(0, 1000);
                    passwordInput.value = password;
                }
                
                const errorElement = document.getElementById('passwordError');
                
                if (!password) {
                    passwordInput.classList.add('is-invalid');
                    errorElement.style.display = 'block';
                    return false;
                } else {
                    passwordInput.classList.remove('is-invalid');
                    errorElement.style.display = 'none';
                    return true;
                }
            }
            
            // Función para sanitizar email (mantiene @ y puntos)
            function sanitizeEmail(email) {
                if (typeof email !== 'string') {
                    return '';
                }
                // Eliminar solo caracteres peligrosos pero mantener @ y puntos
                let sanitized = email.trim();
                sanitized = sanitized.replace(/[<>"']/g, '');
                // Eliminar caracteres de control
                sanitized = sanitized.replace(/[\x00-\x1F\x7F]/g, '');
                return sanitized;
            }
            
            // Funciones para recordar datos
            function saveRememberedData() {
                if (rememberMeCheckbox.checked) {
                    // Sanitizar antes de guardar en localStorage (mantiene @)
                    const sanitizedEmail = sanitizeEmail(emailInput.value);
                    localStorage.setItem('rememberedEmail', sanitizedEmail);
                    localStorage.setItem('rememberMe', 'true');
                } else {
                    localStorage.removeItem('rememberedEmail');
                    localStorage.removeItem('rememberMe');
                }
            }
            
            function loadRememberedData() {
                const remembered = localStorage.getItem('rememberMe');
                const rememberedEmail = localStorage.getItem('rememberedEmail');
                
                if (remembered === 'true' && rememberedEmail) {
                    // Sanitizar al cargar desde localStorage (mantiene @)
                    const sanitizedEmail = sanitizeEmail(rememberedEmail);
                    emailInput.value = sanitizedEmail;
                    rememberMeCheckbox.checked = true;
                }
            }
            
            const errorDiv = document.getElementById('loginError');
            const supportsAjaxLogin = typeof window.fetch === 'function' && typeof window.FormData === 'function';

            function showError(message) {
                if (!errorDiv) {
                    return;
                }
                errorDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + (message || 'Error en el servidor');
                errorDiv.style.display = 'block';
            }

            function hideError() {
                if (!errorDiv) {
                    return;
                }
                errorDiv.style.display = 'none';
                errorDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i>';
            }

            function showLoadingState() {
                submitBtn.style.display = 'none';
                loginLoading.style.display = 'block';
                form.style.pointerEvents = 'none';
            }

            function resetLoadingState() {
                submitBtn.style.display = 'block';
                loginLoading.style.display = 'none';
                form.style.pointerEvents = 'auto';
            }

            function sanitizeBeforeSubmit() {
                emailInput.value = sanitizeEmail(emailInput.value);
                passwordInput.value = passwordInput.value.replace(/[\x00-\x1F\x7F]/g, '');
                if (passwordInput.value.length > 1000) {
                    passwordInput.value = passwordInput.value.substring(0, 1000);
                }
            }

            function fallbackToStandardSubmit() {
                resetLoadingState();
                form.removeEventListener('submit', handleAjaxSubmit);
                form.addEventListener('submit', handleStandardSubmit);
                form.submit();
            }

            function handleAjaxSubmit(e) {
                e.preventDefault();
                sanitizeBeforeSubmit();
                
                const emailValid = validateEmail();
                const passwordValid = validatePassword();
                
                if (!emailValid || !passwordValid) {
                    return;
                }
                
                hideError();
                saveRememberedData();
                showLoadingState();

                const formData = new FormData();
                formData.append('email', sanitizeEmail(emailInput.value));

                let sanitizedPassword = passwordInput.value.replace(/[\x00-\x1F\x7F]/g, '');
                if (sanitizedPassword.length > 1000) {
                    sanitizedPassword = sanitizedPassword.substring(0, 1000);
                }
                formData.append('password', sanitizedPassword);

                const requestUrl = form.getAttribute('action') || 'login.php';

                fetch(requestUrl, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => {
                    return response.text().then(text => {
                        console.log('Respuesta completa del servidor (primeros 500 caracteres):', text.substring(0, 500));
                        
                        const firstBrace = text.indexOf('{');
                        if (firstBrace === -1) {
                            console.error('No se encontró { en la respuesta');
                            throw new Error('Respuesta no válida del servidor: no se encontró JSON');
                        }

                        let braceCount = 0;
                        let lastBrace = -1;
                        for (let i = firstBrace; i < text.length; i++) {
                            if (text[i] === '{') {
                                braceCount++;
                            } else if (text[i] === '}') {
                                braceCount--;
                                if (braceCount === 0) {
                                    lastBrace = i;
                                    break;
                                }
                            }
                        }

                        if (lastBrace === -1) {
                            console.error('No se encontró el } de cierre del JSON');
                            throw new Error('Respuesta no válida del servidor: JSON incompleto');
                        }

                        const jsonText = text.substring(firstBrace, lastBrace + 1);
                        console.log('JSON extraído:', jsonText);
                        
                        try {
                            const parsed = JSON.parse(jsonText);
                            console.log('JSON parseado correctamente:', parsed);
                            console.log('redirect en parsed:', parsed.redirect);
                            return parsed;
                        } catch (parseError) {
                            console.error('Error al parsear JSON:', parseError);
                            console.error('JSON que falló:', jsonText);
                            throw new Error('Error al parsear JSON: ' + parseError.message);
                        }
                    });
                })
                .then(data => {
                    console.log('Respuesta del servidor recibida:', data);
                    console.log('Tipo de data:', typeof data);
                    console.log('data.success:', data.success);
                    console.log('data.redirect:', data.redirect);
                    
                    // Verificar que data existe y tiene success
                    if (!data) {
                        console.error('Respuesta vacía del servidor');
                        showError('Error: Respuesta vacía del servidor');
                        resetLoadingState();
                        return;
                    }
                    
                    // Verificar éxito (aceptar true, 'true', o 1)
                    const isSuccess = data.success === true || data.success === 'true' || data.success === 1;
                    
                    console.log('isSuccess evaluado como:', isSuccess);
                    
                    if (isSuccess) {
                        console.log('Login exitoso, preparando redirección...');
                        
                        if (data.token) {
                            localStorage.setItem('user_token', data.token);
                            console.log('Token guardado en localStorage');
                        }
                        if (data.user) {
                            localStorage.setItem('user', JSON.stringify(data.user));
                            console.log('Usuario guardado en localStorage');
                        }

                        const redirectUrl = data.redirect || 'dashboard.php';
                        console.log('URL de redirección:', redirectUrl);
                        console.log('URL actual:', window.location.href);
                        
                        // Ocultar el formulario y mostrar mensaje de éxito
                        if (form) {
                            form.style.display = 'none';
                        }
                        const successDiv = document.getElementById('loginSuccess');
                        const successMessage = document.getElementById('loginSuccessMessage');
                        const manualRedirectBtn = document.getElementById('manualRedirectBtn');
                        if (successDiv) {
                            successDiv.style.display = 'block';
                            if (successMessage) {
                                successMessage.textContent = 'Login exitoso. Redirigiendo a ' + redirectUrl + '...';
                            }
                        }
                        
                        // Configurar botón de redirección manual como fallback
                        if (manualRedirectBtn) {
                            manualRedirectBtn.onclick = function() {
                                console.log('Redirección manual activada');
                                window.location.href = redirectUrl;
                            };
                        }
                        
                        // Forzar redirección inmediata
                        console.log('Intentando redirigir ahora...');
                        
                        // Detener cualquier otro procesamiento
                        if (loginLoading) {
                            loginLoading.style.display = 'none';
                        }
                        
                        // Función de redirección que intenta múltiples métodos
                        function forceRedirect(url) {
                            console.log('forceRedirect llamado con URL:', url);
                            console.log('Tipo de URL:', typeof url);
                            console.log('URL es válida:', url && url.length > 0);
                            
                            // Verificar que la URL sea válida
                            if (!url || url.length === 0) {
                                console.error('URL inválida para redirección');
                                return;
                            }
                            
                            // Método 1: window.location.href (más común) - INMEDIATO
                            console.log('Intentando Método 1: window.location.href');
                            try {
                                window.location.href = url;
                                console.log('Método 1: window.location.href =', url, 'ejecutado');
                                // No continuar después de esto si funciona
                                return;
                            } catch (e) {
                                console.error('Error con href:', e);
                            }
                            
                            // Método 2: window.location.replace (no deja historial) - INMEDIATO
                            console.log('Intentando Método 2: window.location.replace');
                            try {
                                window.location.replace(url);
                                console.log('Método 2: window.location.replace ejecutado');
                                return;
                            } catch (e) {
                                console.error('Error con replace:', e);
                            }
                            
                            // Método 3: window.location.assign - INMEDIATO
                            console.log('Intentando Método 3: window.location.assign');
                            try {
                                window.location.assign(url);
                                console.log('Método 3: window.location.assign ejecutado');
                                return;
                            } catch (e) {
                                console.error('Error con assign:', e);
                            }
                            
                            // Método 4: Usar top.location (para iframes) - INMEDIATO
                            console.log('Intentando Método 4: window.top.location');
                            try {
                                if (window.top && window.top !== window) {
                                    window.top.location.href = url;
                                    console.log('Método 4: window.top.location.href ejecutado');
                                    return;
                                } else {
                                    console.log('window.top no disponible o es la misma ventana');
                                }
                            } catch (e) {
                                console.error('Error con top.location:', e);
                            }
                            
                            // Método 5: Usar document.location como último recurso
                            console.log('Intentando Método 5: document.location');
                            try {
                                document.location = url;
                                console.log('Método 5: document.location ejecutado');
                            } catch (e) {
                                console.error('Error con document.location:', e);
                            }
                        }
                        
                        // Construir URL completa si es necesario
                        let finalRedirectUrl = redirectUrl;
                        if (!redirectUrl.startsWith('http://') && !redirectUrl.startsWith('https://') && !redirectUrl.startsWith('/')) {
                            // Es una URL relativa, construirla
                            const currentPath = window.location.pathname;
                            const basePath = currentPath.substring(0, currentPath.lastIndexOf('/') + 1);
                            finalRedirectUrl = basePath + redirectUrl;
                        }
                        
                        console.log('URL final de redirección:', finalRedirectUrl);
                        console.log('URL actual:', window.location.href);
                        
                        // Esperar un momento para asegurar que la sesión se haya guardado en el servidor
                        // antes de redirigir
                        console.log('Esperando 300ms para asegurar que la sesión se guarde...');
                        
                        setTimeout(() => {
                            console.log('Ejecutando redirección forzada ahora...');
                            
                            // Ejecutar redirección forzada
                            forceRedirect(finalRedirectUrl);
                            
                            // Si después de 2 segundos aún estamos aquí, mostrar botón manual
                            setTimeout(() => {
                                const currentHref = window.location.href;
                                console.log('Verificando redirección después de 2 segundos. URL actual:', currentHref);
                                
                                if (currentHref.indexOf('login.php') !== -1) {
                                    console.warn('La redirección automática no funcionó después de 2 segundos');
                                    console.warn('Esto podría significar que:');
                                    console.warn('1. La sesión no se guardó correctamente');
                                    console.warn('2. dashboard.php está redirigiendo de vuelta');
                                    console.warn('3. Hay un problema con la navegación del navegador');
                                    console.warn('Mostrando botón manual de redirección');
                                    
                                    if (manualRedirectBtn) {
                                        manualRedirectBtn.style.display = 'inline-block';
                                        // Hacer que el botón use la URL completa
                                        manualRedirectBtn.onclick = function() {
                                            console.log('Botón manual: redirigiendo a', finalRedirectUrl);
                                            // Limpiar cualquier estado antes de redirigir
                                            if (form) {
                                                form.reset();
                                            }
                                            window.location.href = finalRedirectUrl;
                                            return false;
                                        };
                                    }
                                    
                                    if (successMessage) {
                                        successMessage.textContent = 'Login exitoso. Si no se redirige automáticamente, haz clic en el botón para ir al Dashboard.';
                                    }
                                } else {
                                    console.log('Redirección exitosa detectada');
                                }
                            }, 2000);
                        }, 300);
                    } else {
                        console.error('Login fallido - data.success no es true');
                        console.error('data completo:', JSON.stringify(data, null, 2));
                        const errorMsg = data.error || data.message || 'Error en el servidor';
                        console.error('Mensaje de error:', errorMsg);
                        showError(errorMsg);
                        resetLoadingState();
                    }
                })
                .catch(error => {
                    console.error('Error en login AJAX:', error);
                    console.error('Stack trace:', error.stack);
                    showError('No se pudo procesar la solicitud. Reintentando...');
                    fallbackToStandardSubmit();
                });
            }

            function handleStandardSubmit(e) {
                // Validar antes de enviar
                sanitizeBeforeSubmit();
                const emailValid = validateEmail();
                const passwordValid = validatePassword();

                // Si hay errores de validación, prevenir el envío
                if (!emailValid || !passwordValid) {
                    e.preventDefault();
                    showError('Por favor corrige los errores en el formulario');
                    return false;
                }

                // Si todo está bien, permitir que el formulario se envíe normalmente
                // El servidor se encargará de la redirección
                hideError();
                saveRememberedData();
                showLoadingState();
                
                // No prevenir el submit - dejar que el formulario se envíe normalmente
                // return true; (implícito)
            }

            // Usar submit normal del formulario (más confiable que AJAX)
            // El servidor se encargará de la redirección con header("Location: ...")
            form.addEventListener('submit', handleStandardSubmit);
            
            // Opcional: Si quieres mantener AJAX para feedback sin recargar la página,
            // puedes descomentar la siguiente línea y comentar la anterior:
            // if (supportsAjaxLogin) {
            //     form.addEventListener('submit', handleAjaxSubmit);
            // } else {
            //     form.addEventListener('submit', handleStandardSubmit);
            // }
            
            // Auto-focus en el primer campo vacío
            if (!emailInput.value) {
                emailInput.focus();
            } else if (!passwordInput.value) {
                passwordInput.focus();
            }
            
            // Limpiar loading si hay error
            <?php if (!empty($errors)): ?>
                submitBtn.style.display = 'block';
                loginLoading.style.display = 'none';
                form.style.pointerEvents = 'auto';
            <?php endif; ?>
        });
    </script>
</body>
</html>