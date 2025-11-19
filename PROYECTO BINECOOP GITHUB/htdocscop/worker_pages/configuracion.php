<?php
// Procesar actualización de configuración
$update_success = false;
$update_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $telefono = trim($_POST['telefono'] ?? '');
    $ci = trim($_POST['ci'] ?? '');
    
    try {
        // Verificar si los campos existen en la tabla
        $columns_result = $mysqli->query("SHOW COLUMNS FROM visitantes");
        $existing_columns = [];
        while ($col = $columns_result->fetch_assoc()) {
            $existing_columns[] = $col['Field'];
        }
        
        $has_telefono = in_array('telefono', $existing_columns);
        $has_ci = in_array('ci', $existing_columns);
        
        $update_fields = [];
        $bind_values = [];
        $bind_types = '';
        
        if ($has_telefono && !empty($telefono)) {
            $update_fields[] = "telefono = ?";
            $bind_values[] = $telefono;
            $bind_types .= 's';
        }
        
        if ($has_ci && !empty($ci)) {
            $update_fields[] = "ci = ?";
            $bind_values[] = $ci;
            $bind_types .= 's';
        }
        
        if (!empty($update_fields)) {
            $bind_values[] = $worker_id;
            $bind_types .= 'i';
            
            $query = "UPDATE visitantes SET " . implode(', ', $update_fields) . " WHERE id = ?";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param($bind_types, ...$bind_values);
            
            if ($stmt->execute()) {
                $update_success = true;
                // Actualizar valores locales
                if ($has_telefono) $worker_telefono = $telefono;
                if ($has_ci) $worker_ci = $ci;
            } else {
                $update_error = 'Error al actualizar la información';
            }
            $stmt->close();
        } else {
            $update_error = 'No hay campos para actualizar o los campos no existen en la base de datos';
        }
    } catch (Exception $e) {
        error_log("Error al actualizar perfil: " . $e->getMessage());
        $update_error = 'Error al actualizar la información: ' . $e->getMessage();
    }
}
?>

<!-- Página de Configuración -->
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
            <i class="ti ti-settings text-green-600 mr-3"></i>
            Configuración de Perfil
        </h3>
        
        <?php if ($update_success): ?>
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800">
                <i class="ti ti-check-circle mr-2"></i>
                Información actualizada exitosamente
            </div>
        <?php endif; ?>
        
        <?php if ($update_error): ?>
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800">
                <i class="ti ti-alert-circle mr-2"></i>
                <?php echo htmlspecialchars($update_error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" class="space-y-6">
            <input type="hidden" name="action" value="update_profile">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Número de Teléfono
                    </label>
                    <input type="tel" 
                           name="telefono" 
                           value="<?php echo htmlspecialchars($worker_telefono); ?>"
                           placeholder="Ej: 0991234567"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    <p class="text-xs text-gray-500 mt-1">Ingresa tu número de teléfono de contacto</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Cédula de Identidad (CI)
                    </label>
                    <input type="text" 
                           name="ci" 
                           value="<?php echo htmlspecialchars($worker_ci); ?>"
                           placeholder="Ej: 12345678"
                           maxlength="20"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    <p class="text-xs text-gray-500 mt-1">Ingresa tu número de cédula de identidad</p>
                </div>
            </div>
            
            <div class="flex justify-end space-x-4 pt-4 border-t border-gray-200">
                <a href="worker_profile.php" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">
                    <i class="ti ti-check mr-2"></i>
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

