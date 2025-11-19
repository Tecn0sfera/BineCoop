<?php
/**
 * Script para generar un ADMIN_API_KEY seguro
 * Ejecutar desde línea de comandos: php generate_admin_api_key.php
 */

// Función para generar secreto seguro
function generateAdminAPIKey($length = 64) {
    // Usar random_bytes para generar bytes aleatorios seguros
    $bytes = random_bytes($length / 2); // Dividir por 2 porque bin2hex duplica la longitud
    // Convertir a hexadecimal
    return bin2hex($bytes);
}

// Generar el secreto
$apiKey = generateAdminAPIKey(64);

echo "========================================\n";
echo "GENERADOR DE ADMIN_API_KEY\n";
echo "========================================\n\n";
echo "ADMIN_API_KEY generado:\n";
echo $apiKey . "\n\n";

// Buscar config.json en el directorio raíz (un nivel arriba)
$configFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config.json';

if (file_exists($configFile)) {
    echo "Actualizando config.json automáticamente...\n";
    echo "Esto actualizará tanto htdocscop como htdocspanel.\n\n";
    
    try {
        // Leer el archivo JSON
        $configContent = file_get_contents($configFile);
        $config = json_decode($configContent, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Error al leer config.json: " . json_last_error_msg());
        }
        
        // Asegurar que las secciones existan
        if (!isset($config['htdocscop'])) {
            $config['htdocscop'] = [];
        }
        if (!isset($config['htdocspanel'])) {
            $config['htdocspanel'] = [];
        }
        
        // Actualizar valores
        $config['htdocscop']['ADMIN_API_KEY'] = $apiKey;
        $config['htdocspanel']['ADMIN_API_KEY'] = $apiKey;
        
        // Guardar el archivo
        $jsonOutput = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        
        if (file_put_contents($configFile, $jsonOutput) === false) {
            throw new Exception("Error al escribir en config.json");
        }
        
        // Validar el JSON guardado
        $validation = json_decode($jsonOutput, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Error: JSON inválido generado");
        }
        
        echo "✅ config.json actualizado exitosamente\n";
        echo "   - htdocscop.ADMIN_API_KEY actualizado\n";
        echo "   - htdocspanel.ADMIN_API_KEY actualizado\n\n";
        
    } catch (Exception $e) {
        echo "❌ Error al actualizar config.json: " . $e->getMessage() . "\n\n";
        echo "Por favor, actualiza manualmente en config.json:\n";
        echo '   "htdocscop": {' . "\n";
        echo '     "ADMIN_API_KEY": "' . $apiKey . '"' . "\n";
        echo '   },' . "\n";
        echo '   "htdocspanel": {' . "\n";
        echo '     "ADMIN_API_KEY": "' . $apiKey . '"' . "\n";
        echo '   }' . "\n\n";
    }
} else {
    echo "⚠️  No se encontró config.json en el directorio raíz\n";
    echo "   Ruta buscada: " . $configFile . "\n\n";
    echo "Por favor, crea config.json basado en config.example.json primero.\n";
    echo "Luego ejecuta este script nuevamente.\n\n";
    echo "O copia manualmente este valor a tu config.json:\n";
    echo '   "htdocscop": {' . "\n";
    echo '     "ADMIN_API_KEY": "' . $apiKey . '"' . "\n";
    echo '   },' . "\n";
    echo '   "htdocspanel": {' . "\n";
    echo '     "ADMIN_API_KEY": "' . $apiKey . '"' . "\n";
    echo '   }' . "\n\n";
}

echo "========================================\n";
echo "⚠️  IMPORTANTE: Mantén este secreto seguro\n";
echo "   No lo compartas ni lo subas al repositorio\n";
echo "   Este mismo ADMIN_API_KEY se usará en ambas\n";
echo "   carpetas (htdocscop y htdocspanel)\n";
echo "========================================\n";

?>

