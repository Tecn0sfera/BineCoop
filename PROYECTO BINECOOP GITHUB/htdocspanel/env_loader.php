<?php
/**
 * Cargador de variables de entorno desde archivo .env o config.json
 * config.json tiene PRIORIDAD ABSOLUTA sobre .env
 */

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
function applyConfig($config, $section = 'htdocspanel') {
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

// Función para cargar variables de entorno
function loadEnv($envFile = null) {
    global $dbVars;
    
    // PASO 1: Limpiar TODAS las variables de DB primero para evitar conflictos
    foreach ($dbVars as $var) {
        clearEnvVar($var);
    }
    
    // PASO 2: Cargar desde config.json local
    $configJson = __DIR__ . '/../config.json';
    
    if (file_exists($configJson)) {
        try {
            $configContent = file_get_contents($configJson);
            $localConfig = json_decode($configContent, true);
            
            if ($localConfig && isset($localConfig['htdocspanel'])) {
                error_log("=== Cargando configuración desde config.json local para htdocspanel ===");
                
                if (applyConfig($localConfig, 'htdocspanel')) {
                    error_log("=== Configuración completa cargada desde config.json local ===");
                    return;
                }
            } else {
                error_log("Advertencia: config.json no encontrado o no tiene sección 'htdocspanel'");
            }
        } catch (Exception $e) {
            error_log("Error al leer config.json: " . $e->getMessage());
        }
    } else {
        error_log("Advertencia: config.json no encontrado en: {$configJson}");
    }
    
    // PASO 3: Si no se encontró config.json, intentar cargar desde .env
    if ($envFile === null) {
        $envFile = __DIR__ . '/.env';
    }
    
    if (file_exists($envFile)) {
        error_log("Cargando configuración desde .env: {$envFile}");
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
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
                if (!getenv($key) && !isset($_ENV[$key])) {
                    setEnvVar($key, $value);
                }
            }
        }
    } else {
        error_log("Archivo .env no encontrado en: {$envFile}");
    }
}

// Cargar variables de entorno automáticamente
loadEnv();

// Función helper para obtener variable de entorno con valor por defecto
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
        error_log("env('DB_HOST') llamado - Valor: " . ($value ?: 'null') . ", Fuente: {$source}, Default: " . ($default ?: 'null'));
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
