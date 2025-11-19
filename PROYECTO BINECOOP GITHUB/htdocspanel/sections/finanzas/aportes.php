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

// Obtener lista de socios aprobados
$socios = [];
try {
    $query = "SELECT v.id, v.nombre, v.email, v.telefono, v.ci 
              FROM visitantes v 
              WHERE v.estado_aprobacion = 'aprobado' AND v.activo = 1 
              ORDER BY v.nombre ASC";
    $result = $mysqli->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $socios[] = [
                'id' => $row['id'],
                'nombre' => $row['nombre'],
                'email' => $row['email'],
                'telefono' => $row['telefono'] ?? '',
                'ci' => $row['ci'] ?? ''
            ];
        }
    }
} catch (Exception $e) {
    error_log("Error al obtener socios: " . $e->getMessage());
}

// Obtener comprobantes de pago desde base de datos
require_once __DIR__ . '/../../includes/comprobantes_db.php';
$todos_comprobantes = getComprobantes();

// Agrupar comprobantes por trabajador/socio
$comprobantes_por_socio = [];
foreach ($todos_comprobantes as $comp) {
    $socio_id = $comp['trabajador_id'];
    if (!isset($comprobantes_por_socio[$socio_id])) {
        $comprobantes_por_socio[$socio_id] = [];
    }
    $comprobantes_por_socio[$socio_id][] = $comp;
}

// Procesar formulario de aporte manual
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'registrar_aporte') {
    $socio_id = intval($_POST['socio_id'] ?? 0);
    $monto = floatval($_POST['monto'] ?? 0);
    $fecha = $_POST['fecha'] ?? date('Y-m-d');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $comprobante_id = trim($_POST['comprobante_id'] ?? '');
    
    if ($socio_id <= 0) {
        $error_message = 'Debe seleccionar un socio';
    } elseif ($monto <= 0) {
        $error_message = 'El monto debe ser mayor a cero';
    } else {
        try {
            // Verificar si existe tabla de aportes, si no, crear estructura simple
            $check_table = $mysqli->query("SHOW TABLES LIKE 'aportes'");
            $table_exists = $check_table && $check_table->num_rows > 0;
            
            if (!$table_exists) {
                // Crear tabla de aportes si no existe
                $create_table = "CREATE TABLE IF NOT EXISTS aportes (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    socio_id INT NOT NULL,
                    monto DECIMAL(10,2) NOT NULL,
                    fecha DATE NOT NULL,
                    descripcion TEXT,
                    comprobante_id VARCHAR(255),
                    creado_por INT,
                    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_socio (socio_id),
                    INDEX idx_fecha (fecha)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
                $mysqli->query($create_table);
            }
            
            // Insertar aporte
            $query = "INSERT INTO aportes (socio_id, monto, fecha, descripcion, comprobante_id, creado_por) 
                     VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $mysqli->prepare($query);
            if ($stmt) {
                $creado_por = $_SESSION['user']['id'] ?? null;
                $stmt->bind_param('idsssi', $socio_id, $monto, $fecha, $descripcion, $comprobante_id, $creado_por);
                
                if ($stmt->execute()) {
                    $success_message = 'Aporte registrado exitosamente';
                } else {
                    $error_message = 'Error al registrar el aporte: ' . $stmt->error;
                }
                $stmt->close();
            } else {
                $error_message = 'Error al preparar la consulta: ' . $mysqli->error;
            }
        } catch (Exception $e) {
            error_log("Error registrando aporte: " . $e->getMessage());
            $error_message = 'Error al registrar el aporte: ' . $e->getMessage();
        }
    }
}

// Obtener aportes registrados
$aportes = [];
try {
    $check_table = $mysqli->query("SHOW TABLES LIKE 'aportes'");
    $table_exists = $check_table && $check_table->num_rows > 0;
    
    if ($table_exists) {
        $query = "SELECT a.*, v.nombre as socio_nombre, v.ci as socio_ci
                  FROM aportes a
                  INNER JOIN visitantes v ON a.socio_id = v.id
                  ORDER BY a.fecha DESC, a.creado_en DESC
                  LIMIT 50";
        $result = $mysqli->query($query);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $aportes[] = $row;
            }
        }
    }
} catch (Exception $e) {
    error_log("Error al obtener aportes: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aportes - Cooperativa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" rel="stylesheet">
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-gray-50">
<div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: false, showForm: false }">
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
                    <i class="ti ti-currency-dollar mr-3 text-lg"></i> Finanzas
                    <i class="ti ti-chevron-down ml-auto transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                </button>
                <div x-show="open" x-collapse class="ml-6 mt-1 space-y-1">
                    <a href="aportes.php" class="block px-3 py-2 text-sm text-indigo-600 bg-indigo-50 rounded-lg font-medium">Aportes</a>
                    <a href="pagos.php" class="block px-3 py-2 text-sm text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg">Pagos</a>
                </div>
            </div>
        </nav>
    </aside>
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white border-b border-gray-200 px-4 py-3 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <button class="text-gray-500 lg:hidden" @click="sidebarOpen = true"><i class="ti ti-menu-2 text-xl"></i></button>
                    <h2 class="text-xl font-semibold text-gray-800">Aportes</h2>
                </div>
                <button @click="showForm = !showForm" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg flex items-center">
                    <i class="ti ti-plus mr-2"></i>
                    Nuevo Aporte
                </button>
            </div>
        </header>
        <main class="flex-1 overflow-y-auto p-6">
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
            
            <!-- Formulario de Nuevo Aporte -->
            <div x-show="showForm" x-cloak class="mb-6 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-6">Registrar Nuevo Aporte</h3>
                <form method="POST" action="" class="space-y-6">
                    <input type="hidden" name="action" value="registrar_aporte">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Socio *</label>
                        <select name="socio_id" id="socio_id_aporte" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" style="pointer-events: auto; cursor: pointer;">
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
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Monto *</label>
                            <input type="number" name="monto" step="0.01" min="0.01" required 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                   placeholder="0.00">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fecha *</label>
                            <input type="date" name="fecha" required 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                   value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Comprobante de Pago (Opcional)</label>
                        <select name="comprobante_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Sin comprobante</option>
                            <?php 
                            // Mostrar comprobantes pendientes o aprobados
                            foreach ($todos_comprobantes as $comp):
                                if ($comp['estado'] === 'pendiente' || $comp['estado'] === 'aprobado'):
                                    // Obtener nombre del trabajador
                                    $trabajador_nombre = 'Trabajador ' . $comp['trabajador_id'];
                                    $query_nombre = "SELECT nombre FROM visitantes WHERE id = ?";
                                    $stmt_nombre = $mysqli->prepare($query_nombre);
                                    if ($stmt_nombre) {
                                        $stmt_nombre->bind_param('i', $comp['trabajador_id']);
                                        $stmt_nombre->execute();
                                        $result_nombre = $stmt_nombre->get_result();
                                        if ($row = $result_nombre->fetch_assoc()) {
                                            $trabajador_nombre = $row['nombre'];
                                        }
                                        $stmt_nombre->close();
                                    }
                            ?>
                                <option value="<?php echo htmlspecialchars($comp['id']); ?>">
                                    <?php echo htmlspecialchars($comp['titulo']); ?> - <?php echo htmlspecialchars($trabajador_nombre); ?> 
                                    (<?php echo htmlspecialchars($comp['nombre_archivo']); ?>)
                                </option>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Puede vincular un comprobante de pago existente a este aporte</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                        <textarea name="descripcion" rows="3" 
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                  placeholder="Descripción del aporte (opcional)"></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-4 pt-4 border-t border-gray-200">
                        <button type="button" @click="showForm = false" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Cancelar
                        </button>
                        <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg">
                            <i class="ti ti-check mr-2"></i>
                            Registrar Aporte
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Lista de Aportes -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Aportes Registrados</h3>
                </div>
                
                <div class="overflow-x-auto">
                    <?php if (!empty($aportes)): ?>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Socio</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comprobante</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($aportes as $aporte): ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($aporte['socio_nombre']); ?>
                                            </div>
                                            <?php if (!empty($aporte['socio_ci'])): ?>
                                                <div class="text-sm text-gray-500">
                                                    CI: <?php echo htmlspecialchars($aporte['socio_ci']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-green-600">
                                                $<?php echo number_format($aporte['monto'], 2); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php 
                                            $fecha = new DateTime($aporte['fecha']);
                                            echo $fecha->format('d/m/Y');
                                            ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php if (!empty($aporte['comprobante_id'])): ?>
                                                <a href="../../api_admin_workers.php?action=download_report&report_id=<?php echo urlencode($aporte['comprobante_id']); ?>" 
                                                   target="_blank"
                                                   class="text-blue-600 hover:text-blue-900 flex items-center">
                                                    <i class="ti ti-file-type-pdf mr-1"></i>
                                                    Ver comprobante
                                                </a>
                                            <?php else: ?>
                                                <span class="text-gray-400">Sin comprobante</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            <?php echo htmlspecialchars($aporte['descripcion'] ?? ''); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="p-12 text-center text-gray-500">
                            <i class="ti ti-inbox text-4xl mb-2"></i>
                            <p>No hay aportes registrados</p>
                            <p class="text-sm mt-1">Use el botón "Nuevo Aporte" para registrar uno</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>
</body>
</html>
