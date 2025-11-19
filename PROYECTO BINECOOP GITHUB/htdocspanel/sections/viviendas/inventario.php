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

// Obtener viviendas separadas por tipo
$departamentos = [];
$casas = [];
try {
    // Verificar si existe la tabla viviendas
    $check_table = $mysqli->query("SHOW TABLES LIKE 'viviendas'");
    $viviendas_exists = $check_table && $check_table->num_rows > 0;
    
    if ($viviendas_exists) {
        // Verificar si existe la columna tipo
        $check_column = $mysqli->query("SHOW COLUMNS FROM viviendas LIKE 'tipo'");
        $tipo_exists = $check_column && $check_column->num_rows > 0;
        
        if ($tipo_exists) {
            // Obtener departamentos
            $query_deptos = "SELECT v.*, s.nombre as socio_nombre, s.email as socio_email 
                            FROM viviendas v
                            LEFT JOIN socios s2 ON v.id = s2.vivienda_id
                            LEFT JOIN visitantes s ON s2.visitante_id = s.id
                            WHERE v.tipo = 'departamento'
                            ORDER BY v.bloque, v.numero";
            $result_deptos = $mysqli->query($query_deptos);
            if ($result_deptos) {
                while ($row = $result_deptos->fetch_assoc()) {
                    $departamentos[] = $row;
                }
            }
            
            // Obtener casas
            $query_casas = "SELECT v.*, s.nombre as socio_nombre, s.email as socio_email 
                           FROM viviendas v
                           LEFT JOIN socios s2 ON v.id = s2.vivienda_id
                           LEFT JOIN visitantes s ON s2.visitante_id = s.id
                           WHERE v.tipo = 'casa'
                           ORDER BY v.numero";
            $result_casas = $mysqli->query($query_casas);
            if ($result_casas) {
                while ($row = $result_casas->fetch_assoc()) {
                    $casas[] = $row;
                }
            }
        } else {
            // Si no existe la columna tipo, obtener todas las viviendas
            $query = "SELECT v.*, s.nombre as socio_nombre, s.email as socio_email 
                     FROM viviendas v
                     LEFT JOIN socios s2 ON v.id = s2.vivienda_id
                     LEFT JOIN visitantes s ON s2.visitante_id = s.id
                     ORDER BY v.bloque, v.numero";
            $result = $mysqli->query($query);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    // Asignar a departamentos por defecto si no hay tipo
                    $departamentos[] = $row;
                }
            }
        }
    }
} catch (Exception $e) {
    error_log("Excepción al obtener viviendas: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario de Viviendas - Cooperativa</title>
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
                    <a href="inventario.php" class="block px-3 py-2 text-sm text-indigo-600 bg-indigo-50 rounded-lg font-medium">Inventario</a>
                    <a href="asignaciones.php" class="block px-3 py-2 text-sm text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg">Asignaciones</a>
                </div>
            </div>
        </nav>
    </aside>
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white border-b border-gray-200 px-4 py-3 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <button class="text-gray-500 lg:hidden" @click="sidebarOpen = true"><i class="ti ti-menu-2 text-xl"></i></button>
                    <h2 class="text-xl font-semibold text-gray-800">Inventario de Viviendas</h2>
                </div>
            </div>
        </header>
        <main class="flex-1 overflow-y-auto p-6">
            <?php if (isset($mysqli) && $mysqli->connect_error): ?>
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
                    <p class="font-semibold">Error de conexión a la base de datos</p>
                    <p class="text-sm"><?php echo htmlspecialchars($mysqli->connect_error); ?></p>
                </div>
            <?php endif; ?>
            <!-- Departamentos -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
                <div class="px-6 py-4 border-b border-gray-200 bg-indigo-50">
                    <h3 class="text-lg font-semibold text-indigo-800 flex items-center">
                        <i class="ti ti-building mr-2"></i>
                        Departamentos (Edificio)
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Número</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bloque</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Metros²</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Socio Asignado</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (!empty($departamentos)): ?>
                                <?php foreach ($departamentos as $v): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($v['numero'] ?? 'N/A'); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($v['bloque'] ?? 'N/A'); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($v['metros_cuadrados'] ?? 'N/A'); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php 
                                            $estado = $v['estado'] ?? 'libre';
                                            if ($estado === 'ocupada'): ?>
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Ocupada</span>
                                            <?php elseif ($estado === 'mantenimiento'): ?>
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Mantenimiento</span>
                                            <?php else: ?>
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Libre</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo !empty($v['socio_nombre']) ? htmlspecialchars($v['socio_nombre']) : 'Sin asignar'; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500">No hay departamentos registrados</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Casas -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200 bg-green-50">
                    <h3 class="text-lg font-semibold text-green-800 flex items-center">
                        <i class="ti ti-home mr-2"></i>
                        Casas
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Número</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bloque</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Metros²</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Socio Asignado</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (!empty($casas)): ?>
                                <?php foreach ($casas as $v): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($v['numero'] ?? 'N/A'); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($v['bloque'] ?? 'N/A'); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($v['metros_cuadrados'] ?? 'N/A'); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php 
                                            $estado = $v['estado'] ?? 'libre';
                                            if ($estado === 'ocupada'): ?>
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Ocupada</span>
                                            <?php elseif ($estado === 'mantenimiento'): ?>
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Mantenimiento</span>
                                            <?php else: ?>
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Libre</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo !empty($v['socio_nombre']) ? htmlspecialchars($v['socio_nombre']) : 'Sin asignar'; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500">No hay casas registradas</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>
</body>
</html>

