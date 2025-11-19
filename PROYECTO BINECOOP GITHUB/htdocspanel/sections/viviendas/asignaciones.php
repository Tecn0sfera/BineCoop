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

// Obtener asignaciones
$asignaciones = [];
try {
    // Verificar si existe la tabla socios
    $check_socios = $mysqli->query("SHOW TABLES LIKE 'socios'");
    $socios_exists = $check_socios && $check_socios->num_rows > 0;
    
    if ($socios_exists) {
        // Verificar si existe tabla viviendas
        $check_viviendas = $mysqli->query("SHOW TABLES LIKE 'viviendas'");
        $viviendas_exists = $check_viviendas && $check_viviendas->num_rows > 0;
        
        if ($viviendas_exists) {
            $query = "SELECT s.*, v.nombre as socio_nombre, v.email as socio_email, v.ci, v.telefono,
                             vi.numero as vivienda_numero, vi.bloque as vivienda_bloque
                      FROM socios s
                      INNER JOIN visitantes v ON s.visitante_id = v.id
                      LEFT JOIN viviendas vi ON s.vivienda_id = vi.id
                      WHERE v.estado_aprobacion = 'aprobado'
                      ORDER BY s.fecha_ingreso DESC";
        } else {
            // Si no hay tabla viviendas, solo obtener datos de socios
            $query = "SELECT s.*, v.nombre as socio_nombre, v.email as socio_email, v.ci, v.telefono,
                             NULL as vivienda_numero, NULL as vivienda_bloque
                      FROM socios s
                      INNER JOIN visitantes v ON s.visitante_id = v.id
                      WHERE v.estado_aprobacion = 'aprobado'
                      ORDER BY s.fecha_ingreso DESC";
        }
        
        $result = $mysqli->query($query);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $asignaciones[] = $row;
            }
        } else {
            error_log("Error en consulta de asignaciones: " . $mysqli->error);
        }
    }
} catch (Exception $e) {
    error_log("Excepción al obtener asignaciones: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignaciones de Viviendas - Cooperativa</title>
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
                    <h2 class="text-xl font-semibold text-gray-800">Asignaciones de Viviendas</h2>
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
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800">Asignaciones Activas</h3>
                    <a href="vivienda_asignacion.php" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg transition-colors flex items-center">
                        <i class="ti ti-plus mr-2"></i>
                        Nueva Asignación
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Socio</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vivienda</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha de Ingreso</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">CI</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Teléfono</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (!empty($asignaciones)): ?>
                                <?php foreach ($asignaciones as $a): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($a['socio_nombre']); ?></div>
                                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($a['socio_email']); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php if (!empty($a['vivienda_numero'])): ?>
                                                <?php echo htmlspecialchars($a['vivienda_numero']); ?>
                                                <?php if (!empty($a['vivienda_bloque'])): ?>
                                                    - Bloque <?php echo htmlspecialchars($a['vivienda_bloque']); ?>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                Sin asignar
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php 
                                            if (!empty($a['fecha_ingreso'])) {
                                                try {
                                                    $fecha = new DateTime($a['fecha_ingreso']);
                                                    echo $fecha->format('d/m/Y');
                                                } catch (Exception $e) {
                                                    echo htmlspecialchars($a['fecha_ingreso']);
                                                }
                                            } else {
                                                echo 'N/A';
                                            }
                                            ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($a['ci'] ?? 'N/A'); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($a['telefono'] ?? 'N/A'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500">No hay asignaciones registradas</td></tr>
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

