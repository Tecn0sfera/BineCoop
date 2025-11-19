<?php
/**
 * Cargador de variables de entorno desde archivo .env o config.json
 * config.json tiene PRIORIDAD ABSOLUTA sobre .env
 */

// Evitar doble carga usando una variable global
if (!isset($GLOBALS['env_loader_loaded'])) {
    $GLOBALS['env_loader_loaded'] = false;
}

// Lista de variables de base de datos que deben ser limpiadas
$dbVars = ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS', 'DB_CHARSET', 
           'ADMIN_API_KEY', 'JWT_SECRET', 'APP_ENV', 'DISPLAY_ERRORS'];

// Función para limpiar variables de entorno
function clearEnvVar($key) {
    putenv($key);
    unset($_ENV[$key]);
    unset($_SERVER[$key]);
    unset($GLOBALS[$key]);
}

// Función para establecer variable de entorno en todas las fuentes
function setEnvVar($key, $value) {
    $value = trim($value);
    putenv("{$key}={$value}");
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
    $GLOBALS[$key] = $value;
}

// Función para aplicar configuración desde un array
function applyConfig($config, $section = 'htdocscop') {
    global $dbVars;
    
    if (!$config || !is_array($config)) {
        return false;
    }
    
    // Si hay una sección específica, usarla
    if ($section && isset($config[$section])) {
        $configData = $config[$section];
    } else {
        // Si no hay sección, usar el array completo
        $configData = $config;
    }
    
    if (!is_array($configData)) {
        return false;
    }
    
    // Aplicar todas las variables de la configuración
    foreach ($configData as $key => $value) {
        if (!empty($value) || $value === '0' || $value === 0) {
            setEnvVar($key, (string)$value);
            error_log("  {$key} = " . (strlen((string)$value) > 50 ? substr((string)$value, 0, 50) . '...' : (string)$value));
        }
    }
    
    return true;
}

// Función para cargar variables de entorno desde .env
if (!function_exists('loadEnv')) {
    function loadEnv($envFile = null, $forceReload = false) {
        global $dbVars;
        
        // Evitar doble carga (a menos que se fuerce)
        if ($GLOBALS['env_loader_loaded'] === true && !$forceReload) {
            // Verificar que JWT_SECRET esté disponible
            $jwtSecret = env('JWT_SECRET', '');
            if (empty($jwtSecret)) {
                error_log("Advertencia: env_loader ya cargado pero JWT_SECRET no está disponible. Forzando recarga...");
                $forceReload = true;
            } else {
                return;
            }
        }
        
        // PASO 1: Limpiar TODAS las variables de DB primero para evitar conflictos
        foreach ($dbVars as $var) {
            clearEnvVar($var);
        }
        
        // PASO 2: Cargar desde config.json
        // Intentar múltiples rutas posibles para config.json
        $possibleConfigPaths = [
            __DIR__ . '/../config.json',  // Desde htdocscop/ hacia raíz
            dirname(__DIR__) . '/config.json',  // Alternativa
            realpath(__DIR__ . '/../config.json'),  // Ruta real
        ];
        
        $configJson = null;
        foreach ($possibleConfigPaths as $path) {
            if ($path && file_exists($path)) {
                $configJson = $path;
                error_log("config.json encontrado en: " . $configJson);
                break;
            }
        }
        
        if ($configJson && file_exists($configJson)) {
            try {
                $configContent = file_get_contents($configJson);
                $localConfig = json_decode($configContent, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    error_log("Error al decodificar config.json: " . json_last_error_msg());
                } else if ($localConfig && isset($localConfig['htdocscop'])) {
                    error_log("=== Cargando configuración desde config.json para htdocscop ===");
                    error_log("Ruta: " . $configJson);
                    
                    if (applyConfig($localConfig, 'htdocscop')) {
                        error_log("=== Configuración completa cargada desde config.json para htdocscop ===");
                        
                        // Verificar que JWT_SECRET se cargó correctamente
                        $jwtSecret = env('JWT_SECRET', '');
                        if (empty($jwtSecret)) {
                            error_log("ERROR: JWT_SECRET no se cargó después de applyConfig");
                            error_log("Variables en config['htdocscop']: " . implode(', ', array_keys($localConfig['htdocscop'] ?? [])));
                        } else {
                            error_log("JWT_SECRET cargado exitosamente (longitud: " . strlen($jwtSecret) . ")");
                        }
                        
                        $GLOBALS['env_loader_loaded'] = true;
                        return;
                    } else {
                        error_log("ERROR: applyConfig retornó false");
                    }
                } else {
                    error_log("Advertencia: config.json no tiene sección 'htdocscop'");
                    error_log("Secciones disponibles: " . implode(', ', array_keys($localConfig ?? [])));
                }
            } catch (Exception $e) {
                error_log("Error al leer config.json: " . $e->getMessage());
                error_log("Trace: " . $e->getTraceAsString());
            }
        } else {
            error_log("Advertencia: config.json no encontrado en ninguna de las rutas intentadas");
            foreach ($possibleConfigPaths as $path) {
                error_log("  Intentado: " . ($path ?: 'null'));
            }
        }
        
        // PASO 3: Si no se encontró config.json, intentar cargar desde .env
        if ($envFile === null) {
            $envFile = __DIR__ . '/.env';
        }
        
        if (file_exists($envFile)) {
            error_log("Cargando configuración desde .env para htdocscop: {$envFile}");
            $lines = @file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
            if ($lines === false) {
                error_log("Error: No se pudo leer el archivo .env en {$envFile}.");
                $GLOBALS['env_loader_loaded'] = true;
                return;
            }
            
            foreach ($lines as $line) {
                // Ignorar comentarios
                $line = trim($line);
                if (empty($line) || strpos($line, '#') === 0) {
                    continue;
                }
                
                // Separar clave y valor
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    
                    // Remover comillas si existen
                    $value = trim($value, '"\'');
                    
                    // Solo establecer si no está ya definida (para no sobrescribir config.json)
                    if (!empty($key) && !getenv($key) && !isset($_ENV[$key])) {
                        setEnvVar($key, $value);
                    }
                }
            }
        } else {
            error_log("Archivo .env no encontrado en: {$envFile}");
        }
        
        $GLOBALS['env_loader_loaded'] = true;
    }
}

// Función helper para obtener variable de entorno con valor por defecto
if (!function_exists('env')) {
    function env($key, $default = null) {
        // Intentar obtener de múltiples fuentes en orden de prioridad
        // 1. GLOBALS (si fue establecido explícitamente)
        if (isset($GLOBALS[$key])) {
            $value = $GLOBALS[$key];
            $source = 'GLOBALS';
        }
        // 2. getenv()
        elseif (($value = getenv($key)) !== false) {
            $source = 'getenv';
        }
        // 3. $_ENV
        elseif (isset($_ENV[$key])) {
            $value = $_ENV[$key];
            $source = '$_ENV';
        }
        // 4. $_SERVER
        elseif (isset($_SERVER[$key])) {
            $value = $_SERVER[$key];
            $source = '$_SERVER';
        }
        // 5. Default
        else {
            $value = $default;
            $source = 'default';
        }
        
        // Log de depuración para DB_HOST (solo las primeras veces para no saturar logs)
        static $dbHostLogCount = 0;
        if ($key === 'DB_HOST' && $dbHostLogCount < 3) {
            error_log("env('DB_HOST') llamado en htdocscop - Valor: " . ($value ?: 'null') . ", Fuente: {$source}, Default: " . ($default ?: 'null'));
            $dbHostLogCount++;
        }
        
        // Convertir strings 'true'/'false' a booleanos
        if ($value === 'true') {
            return true;
        }
        if ($value === 'false') {
            return false;
        }
        
        return $value;
    }
}

// Cargar .env automáticamente solo una vez
if ($GLOBALS['env_loader_loaded'] === false) {
    loadEnv();
} else {
    // Verificar que JWT_SECRET esté disponible incluso si ya se cargó
    $jwtSecret = env('JWT_SECRET', '');
    if (empty($jwtSecret)) {
        error_log("Advertencia: env_loader ya cargado pero JWT_SECRET no está disponible. Forzando recarga...");
        loadEnv(null, true);
    }
}