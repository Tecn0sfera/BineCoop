<?php
/**
 * Manejador JWT propio para htdocscop
 * Implementación nativa sin dependencias externas
 * Compatible con el estándar JWT (RFC 7519)
 */

// Cargar variables de entorno si no están cargadas
if (!function_exists('env')) {
    require_once __DIR__ . '/env_loader.php';
}

/**
 * Clase JWT Handler
 */
class JWTHandler {
    private $secret;
    private $algorithm = 'HS256';
    
    public function __construct($secret = null) {
        // Obtener el secreto desde variable de entorno o parámetro
        $this->secret = $secret ?? env('JWT_SECRET', '');
        
        if (empty($this->secret)) {
            throw new Exception('JWT_SECRET no está configurado. Por favor, configura JWT_SECRET en tu archivo .env');
        }
    }
    
    /**
     * Generar token JWT
     * 
     * @param array $payload Datos a incluir en el token
     * @param int $expiration Tiempo de expiración en segundos (por defecto 7 días)
     * @return string Token JWT
     */
    public function generateToken($payload, $expiration = null) {
        // Configurar tiempo de expiración por defecto (7 días)
        if ($expiration === null) {
            $expiration = 60 * 60 * 24 * 7; // 7 días
        }
        
        // Header del JWT
        $header = [
            'typ' => 'JWT',
            'alg' => $this->algorithm
        ];
        
        // Payload con datos estándar
        $jwtPayload = [
            'iat' => time(), // Issued at
            'exp' => time() + $expiration, // Expiration
            'nbf' => time() // Not before
        ];
        
        // Combinar payload personalizado con estándar
        $jwtPayload = array_merge($jwtPayload, $payload);
        
        // Codificar header y payload en base64url
        $base64Header = $this->base64UrlEncode(json_encode($header));
        $base64Payload = $this->base64UrlEncode(json_encode($jwtPayload));
        
        // Crear signature
        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, $this->secret, true);
        $base64Signature = $this->base64UrlEncode($signature);
        
        // Retornar token completo
        return $base64Header . "." . $base64Payload . "." . $base64Signature;
    }
    
    /**
     * Validar y decodificar token JWT
     * 
     * @param string $token Token JWT a validar
     * @return array|false Payload decodificado o false si es inválido
     */
    public function validateToken($token) {
        try {
            // Separar las partes del token
            $parts = explode('.', $token);
            
            if (count($parts) !== 3) {
                return false;
            }
            
            [$base64Header, $base64Payload, $signature] = $parts;
            
            // Verificar la firma
            $expectedSignature = $this->base64UrlEncode(
                hash_hmac('sha256', $base64Header . "." . $base64Payload, $this->secret, true)
            );
            
            if (!hash_equals($signature, $expectedSignature)) {
                error_log("JWT: Firma inválida");
                return false;
            }
            
            // Decodificar payload
            $payload = json_decode($this->base64UrlDecode($base64Payload), true);
            
            if (!$payload) {
                error_log("JWT: Payload inválido");
                return false;
            }
            
            // Verificar expiración
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                error_log("JWT: Token expirado");
                return false;
            }
            
            // Verificar not before
            if (isset($payload['nbf']) && $payload['nbf'] > time()) {
                error_log("JWT: Token aún no válido");
                return false;
            }
            
            return $payload;
            
        } catch (Exception $e) {
            error_log("JWT Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener token del header Authorization
     * 
     * @return string|null Token o null si no se encuentra
     */
    public static function getTokenFromHeader() {
        $headers = getallheaders();
        
        if (isset($headers['Authorization'])) {
            $auth = $headers['Authorization'];
            if (strpos($auth, 'Bearer ') === 0) {
                return substr($auth, 7);
            }
        }
        
        // También verificar en $_SERVER por si getallheaders() no funciona
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $auth = $_SERVER['HTTP_AUTHORIZATION'];
            if (strpos($auth, 'Bearer ') === 0) {
                return substr($auth, 7);
            }
        }
        
        return null;
    }
    
    /**
     * Codificar en base64url (RFC 4648)
     */
    private function base64UrlEncode($data) {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }
    
    /**
     * Decodificar desde base64url
     */
    private function base64UrlDecode($data) {
        // Agregar padding si es necesario
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $data .= str_repeat('=', $padlen);
        }
        
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
    }
}

/**
 * Funciones de conveniencia para compatibilidad con código existente
 */

/**
 * Generar token JWT para usuario
 * Compatible con generateUserJWT() existente
 */
function generateUserJWT($user, $expiration = null) {
    $jwt = new JWTHandler();
    
    $payload = [
        'sub' => $user['id'] ?? $user['id'],
        'type' => 'user',
        'nombre' => $user['nombre'] ?? '',
        'email' => $user['email'] ?? ''
    ];
    
    // Si no se especifica expiración, usar 7 días por defecto
    if ($expiration === null) {
        $expiration = 60 * 60 * 24 * 7; // 7 días
    }
    
    return $jwt->generateToken($payload, $expiration);
}

/**
 * Validar token de usuario
 * Compatible con validateUserToken() existente
 */
function validateUserToken($token) {
    $jwt = new JWTHandler();
    $payload = $jwt->validateToken($token);
    
    if ($payload && isset($payload['type']) && $payload['type'] === 'user') {
        return $payload;
    }
    
    return false;
}

/**
 * Generar token JWT genérico
 * Compatible con generateJWT() existente
 */
function generateJWT($user, $expiration = null) {
    $jwt = new JWTHandler();
    
    $payload = [
        'sub' => $user['id'] ?? $user['id'],
        'nombre' => $user['nombre'] ?? '',
        'email' => $user['email'] ?? ''
    ];
    
    // Si no se especifica expiración, usar 1 día por defecto
    if ($expiration === null) {
        $expiration = 60 * 60 * 24; // 1 día
    }
    
    return $jwt->generateToken($payload, $expiration);
}

/**
 * Requerir autenticación de usuario
 * Compatible con requireUserAuth() existente
 */
function requireUserAuth() {
    $token = JWTHandler::getTokenFromHeader();
    
    if (!$token) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Token requerido']);
        exit();
    }
    
    $userData = validateUserToken($token);
    if (!$userData) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Token inválido o expirado']);
        exit();
    }
    
    return $userData;
}

/**
 * Generar un JWT_SECRET seguro
 * 
 * @param int $length Longitud del secreto (por defecto 64 caracteres)
 * @return string Secreto seguro
 */
function generateJWTSecret($length = 64) {
    // Usar random_bytes para generar bytes aleatorios seguros
    $bytes = random_bytes($length);
    // Convertir a hexadecimal
    return bin2hex($bytes);
}
