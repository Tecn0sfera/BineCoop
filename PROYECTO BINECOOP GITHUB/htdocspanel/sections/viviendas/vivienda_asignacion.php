<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

if (empty($_SESSION['user']['logged_in'])) {
    header("Location: ../../login.php");
    exit();
}

$GLOBALS['allowed_config_access'] = true;
require __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/db.php';

$success_message = '';
$error_message = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'asignar_vivienda') {
    $socio_id = intval($_POST['socio_id'] ?? 0);
    $tipo_asignacion = $_POST['tipo_asignacion'] ?? 'casa';
    
    if ($socio_id <= 0) {
        $error_message = 'Debe seleccionar un socio';
    } else {
        try {
            // Verificar si existe la tabla socios, si no existe, crearla
            $check_table = $mysqli->query("SHOW TABLES LIKE 'socios'");
            $socios_table_exists = $check_table && $check_table->num_rows > 0;
            
            if (!$socios_table_exists) {
                // Crear la tabla socios si no existe
                $create_table_sql = "CREATE TABLE IF NOT EXISTS `socios` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `visitante_id` INT(11) NOT NULL,
                    `apellido` VARCHAR(100) DEFAULT NULL,
                    `ci` VARCHAR(20) DEFAULT NULL,
                    `telefono` VARCHAR(20) DEFAULT NULL,
                    `fecha_ingreso` DATE DEFAULT NULL,
                    `vivienda_id` INT(11) DEFAULT NULL,
                    `tipo_asignacion` ENUM('casa', 'departamento') DEFAULT NULL,
                    `creado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `actualizado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY `unique_visitante_id` (`visitante_id`),
                    INDEX `idx_vivienda` (`vivienda_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
                
                if (!$mysqli->query($create_table_sql)) {
                    throw new Exception('Error al crear la tabla socios: ' . $mysqli->error);
                }
                error_log("Tabla socios creada automáticamente");
            }
            
            // Verificar si existe la columna tipo_asignacion, si no existe, agregarla
            $columns_result = $mysqli->query("SHOW COLUMNS FROM socios LIKE 'tipo_asignacion'");
            $has_tipo_asignacion = $columns_result && $columns_result->num_rows > 0;
            
            if (!$has_tipo_asignacion) {
                $alter_sql = "ALTER TABLE `socios` ADD COLUMN `tipo_asignacion` ENUM('casa', 'departamento') DEFAULT NULL AFTER `vivienda_id`";
                if (!$mysqli->query($alter_sql)) {
                    error_log("Advertencia: No se pudo agregar la columna tipo_asignacion: " . $mysqli->error);
                } else {
                    error_log("Columna tipo_asignacion agregada automáticamente");
                }
            }
            
            // Verificar si el socio ya tiene una asignación
            $check_query = "SELECT id FROM socios WHERE visitante_id = ?";
            $check_stmt = $mysqli->prepare($check_query);
            if (!$check_stmt) {
                throw new Exception('Error al preparar la consulta: ' . $mysqli->error);
            }
            $check_stmt->bind_param('i', $socio_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            // Verificar si existe la columna tipo_asignacion
            $columns_result = $mysqli->query("SHOW COLUMNS FROM socios LIKE 'tipo_asignacion'");
            $has_tipo_asignacion = $columns_result && $columns_result->num_rows > 0;
            
            if ($check_result->num_rows > 0) {
                // Actualizar asignación existente
                $socio_row = $check_result->fetch_assoc();
                
                if ($has_tipo_asignacion) {
                    $update_query = "UPDATE socios SET tipo_asignacion = ?, fecha_ingreso = CURDATE() WHERE id = ?";
                    $update_stmt = $mysqli->prepare($update_query);
                    $update_stmt->bind_param('si', $tipo_asignacion, $socio_row['id']);
                } else {
                    $update_query = "UPDATE socios SET fecha_ingreso = CURDATE() WHERE id = ?";
                    $update_stmt = $mysqli->prepare($update_query);
                    $update_stmt->bind_param('i', $socio_row['id']);
                }
                
                if ($update_stmt->execute()) {
                    $success_message = 'Asignación actualizada exitosamente';
                } else {
                    $error_message = 'Error al actualizar la asignación: ' . $update_stmt->error;
                }
                $update_stmt->close();
            } else {
                // Crear nueva asignación
                if ($has_tipo_asignacion) {
                    $insert_query = "INSERT INTO socios (visitante_id, tipo_asignacion, fecha_ingreso) VALUES (?, ?, CURDATE())";
                    $insert_stmt = $mysqli->prepare($insert_query);
                    $insert_stmt->bind_param('is', $socio_id, $tipo_asignacion);
                } else {
                    $insert_query = "INSERT INTO socios (visitante_id, fecha_ingreso) VALUES (?, CURDATE())";
                    $insert_stmt = $mysqli->prepare($insert_query);
                    $insert_stmt->bind_param('i', $socio_id);
                }
                
                if ($insert_stmt->execute()) {
                    $success_message = 'Asignación creada exitosamente';
                } else {
                    $error_message = 'Error al crear la asignación: ' . $insert_stmt->error;
                }
                $insert_stmt->close();
            }
            
            $check_stmt->close();
        } catch (Exception $e) {
            error_log("Error asignando vivienda: " . $e->getMessage());
            $error_message = 'Error al asignar vivienda: ' . $e->getMessage();
        }
    }
}

// Obtener lista de socios aprobados
$socios = [];
try {
    $query = "SELECT v.id, v.nombre, v.email, v.ci, v.telefono 
              FROM visitantes v 
              WHERE v.estado_aprobacion = 'aprobado' AND v.activo = 1 
              ORDER BY v.nombre ASC";
    $result = $mysqli->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $socios[] = $row;
        }
    }
} catch (Exception $e) {
    error_log("Error al obtener socios: " . $e->getMessage());
}

// Obtener lista de viviendas disponibles
$viviendas = [];
try {
    $query = "SELECT id, numero, bloque, metros_cuadrados, estado 
              FROM viviendas 
              WHERE estado = 'libre' OR estado IS NULL
              ORDER BY numero ASC";
    $result = $mysqli->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $viviendas[] = $row;
        }
    }
} catch (Exception $e) {
    error_log("Error al obtener viviendas: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Asignación de Vivienda - Cooperativa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" rel="stylesheet">
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-gray-50">
<div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: false }">
    <aside class="fixed inset-y-0 left-0 z-30 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0" 
           :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }">
        <div class="flex items-center justify-between px-6 py-4 bg-indigo-600 text-white">
            <h1 class="text-lg font-bold">Cooperativa</h1>
            <button class="lg:hidden" @click="sidebarOpen = false"><i class="ti ti-x text-xl"></i></button>
        </div>
        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto h-full">
            <a href="../../dashboard.php" class="flex items-center px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors">
                <i class="ti ti-dashboard mr-3 text-lg"></i> Dashboard
            </a>
            <div x-data="{ open: true }">
                <button @click="open = !open" class="flex items-center w-full px-3 py-2 rounded-lg bg-indigo-100 text-indigo-700 font-medium">
                    <i class="ti ti-building-community mr-3 text-lg"></i> Viviendas
                    <i class="ti ti-chevron-down ml-auto transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                </button>
                <div x-show="open" x-collapse class="ml-6 mt-1 space-y-1">
                    <a href="inventario.php" class="block px-3 py-2 text-sm text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg">Inventario</a>
                    <a href="asignaciones.php" class="block px-3 py-2 text-sm text-indigo-600 bg-indigo-50 rounded-lg font-medium">Asignaciones</a>
                </div>
            </div>
        </nav>
    </aside>
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white border-b border-gray-200 px-4 py-3 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <button class="text-gray-500 lg:hidden" @click="sidebarOpen = true"><i class="ti ti-menu-2 text-xl"></i></button>
                    <a href="asignaciones.php" class="text-gray-500 hover:text-gray-700">
                        <i class="ti ti-arrow-left mr-2"></i>
                    </a>
                    <h2 class="text-xl font-semibold text-gray-800">Nueva Asignación de Vivienda</h2>
                </div>
            </div>
        </header>
        <main class="flex-1 overflow-y-auto p-6">
            <div class="max-w-2xl mx-auto">
                <?php if ($success_message): ?>
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800">
                        <i class="ti ti-check-circle mr-2"></i>
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error_message): ?>
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800">
                        <i class="ti ti-alert-circle mr-2"></i>
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-6">Información de la Asignación</h3>
                    <form method="POST" action="" class="space-y-6">
                        <input type="hidden" name="action" value="asignar_vivienda">
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Socio *</label>
                            <select name="socio_id" id="socio_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" style="pointer-events: auto; cursor: pointer;">
                                <option value="">Seleccionar socio...</option>
                                <?php if (!empty($socios)): ?>
                                    <?php foreach ($socios as $socio): ?>
                                        <option value="<?php echo intval($socio['id']); ?>">
                                            <?php echo htmlspecialchars($socio['nombre']); ?>
                                            <?php if (!empty($socio['ci'])): ?>
                                                (CI: <?php echo htmlspecialchars($socio['ci']); ?>) 
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>No hay socios disponibles</option>
                                <?php endif; ?>
                            </select>
                            <?php if (empty($socios)): ?>
                                <p class="text-sm text-red-500 mt-1">No hay socios aprobados disponibles. Por favor, apruebe socios primero.</p>
                            <?php endif; ?>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Asignación *</label>
                            <select name="tipo_asignacion" id="tipo_asignacion" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="casa">Casa</option>
                                <option value="departamento">Departamento</option>
                            </select>
                        </div>
                        
                        <div class="flex justify-end space-x-4 pt-4 border-t border-gray-200">
                            <a href="asignaciones.php" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                Cancelar
                            </a>
                            <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg">
                                <i class="ti ti-check mr-2"></i>
                                Asignar Vivienda
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>
</body>
</html>

