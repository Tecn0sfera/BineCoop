<?php
// Script para ver los logs de error de PHP
// ACCESO TEMPORAL - ELIMINAR DESPUÉS DE DEBUGGING

// Verificar que solo se pueda acceder desde localhost o con contraseña
$allowed = false;

// Permitir acceso si hay una contraseña en la URL (cambiar por una contraseña segura)
$password = $_GET['pass'] ?? '';
if ($password === 'debug2024') { // CAMBIAR ESTA CONTRASEÑA
    $allowed = true;
}

// O permitir solo desde localhost (comentar si estás en servidor remoto)
// if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] === '127.0.0.1') {
//     $allowed = true;
// }

if (!$allowed) {
    die('Acceso denegado. Agrega ?pass=debug2024 a la URL');
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Logs de Error</title>
    <style>
        body {
            font-family: monospace;
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
        }
        h1 {
            color: #4ec9b0;
        }
        .log-entry {
            background: #252526;
            padding: 10px;
            margin: 5px 0;
            border-left: 3px solid #007acc;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .error {
            border-left-color: #f48771;
        }
        .warning {
            border-left-color: #dcdcaa;
        }
        .info {
            border-left-color: #4ec9b0;
        }
        .refresh-btn {
            background: #007acc;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            margin: 10px 0;
        }
        .refresh-btn:hover {
            background: #005a9e;
        }
    </style>
</head>
<body>
    <h1>Logs de Error de PHP</h1>
    <button class="refresh-btn" onclick="location.reload()">Actualizar</button>
    
    <?php
    // Obtener la ubicación del archivo de log
    $logFile = ini_get('error_log');
    
    // Si no está configurado, intentar ubicaciones comunes
    if (empty($logFile) || !file_exists($logFile)) {
        $possibleLogs = [
            __DIR__ . '/error_log',
            __DIR__ . '/../error_log',
            __DIR__ . '/../../error_log',
            dirname(__DIR__) . '/error_log',
            $_SERVER['DOCUMENT_ROOT'] . '/error_log',
        ];
        
        foreach ($possibleLogs as $possibleLog) {
            if (file_exists($possibleLog)) {
                $logFile = $possibleLog;
                break;
            }
        }
    }
    
    if ($logFile && file_exists($logFile)) {
        echo "<h2>Archivo: " . htmlspecialchars($logFile) . "</h2>";
        echo "<p>Tamaño: " . number_format(filesize($logFile)) . " bytes</p>";
        
        // Leer las últimas 100 líneas
        $lines = file($logFile);
        $lastLines = array_slice($lines, -100);
        
        echo "<h3>Últimas 100 líneas:</h3>";
        echo "<div style='max-height: 600px; overflow-y: auto;'>";
        
        foreach ($lastLines as $line) {
            $class = 'log-entry';
            if (stripos($line, 'error') !== false || stripos($line, 'fatal') !== false) {
                $class .= ' error';
            } elseif (stripos($line, 'warning') !== false) {
                $class .= ' warning';
            } else {
                $class .= ' info';
            }
            
            echo "<div class='$class'>" . htmlspecialchars($line) . "</div>";
        }
        
        echo "</div>";
    } else {
        echo "<h2>No se encontró el archivo de log</h2>";
        echo "<p>Ubicaciones buscadas:</p><ul>";
        echo "<li>" . ini_get('error_log') . " (desde php.ini)</li>";
        echo "<li>" . __DIR__ . "/error_log</li>";
        echo "<li>" . __DIR__ . "/../error_log</li>";
        echo "<li>" . $_SERVER['DOCUMENT_ROOT'] . "/error_log</li>";
        echo "</ul>";
        
        echo "<h3>Información del sistema:</h3>";
        echo "<pre>";
        echo "PHP Version: " . phpversion() . "\n";
        echo "error_log configurado: " . (ini_get('error_log') ?: 'No configurado') . "\n";
        echo "log_errors: " . (ini_get('log_errors') ? 'On' : 'Off') . "\n";
        echo "display_errors: " . (ini_get('display_errors') ? 'On' : 'Off') . "\n";
        echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
        echo "Script Directory: " . __DIR__ . "\n";
        echo "</pre>";
    }
    ?>
    
    <hr>
    <h2>Buscar en logs específicos</h2>
    <p>También puedes buscar manualmente en:</p>
    <ul>
        <li>El directorio raíz de tu proyecto: <code>error_log</code></li>
        <li>El directorio htdocs: <code>htdocs/error_log</code></li>
        <li>Usando FTP o el panel de control de InfinityFree</li>
    </ul>
    
    <p><strong>Nota:</strong> Elimina este archivo después de terminar el debugging por seguridad.</p>
</body>
</html>

