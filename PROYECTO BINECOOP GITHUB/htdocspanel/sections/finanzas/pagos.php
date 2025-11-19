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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Pago - Cooperativa</title>
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
                    <i class="ti ti-currency-dollar mr-3 text-lg"></i> Finanzas
                    <i class="ti ti-chevron-down ml-auto transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                </button>
                <div x-show="open" x-collapse class="ml-6 mt-1 space-y-1">
                    <a href="aportes.php" class="block px-3 py-2 text-sm text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg">Aportes</a>
                    <a href="pagos.php" class="block px-3 py-2 text-sm text-indigo-600 bg-indigo-50 rounded-lg font-medium">Pagos</a>
                </div>
            </div>
        </nav>
    </aside>
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white border-b border-gray-200 px-4 py-3 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <button class="text-gray-500 lg:hidden" @click="sidebarOpen = true"><i class="ti ti-menu-2 text-xl"></i></button>
                    <h2 class="text-xl font-semibold text-gray-800">Registrar Pago</h2>
                </div>
            </div>
        </header>
        <main class="flex-1 overflow-y-auto p-6">
            <div class="max-w-2xl mx-auto">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-6">Nuevo Pago</h3>
                    <form class="space-y-6">
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
                            <label class="block text-sm font-medium text-gray-700 mb-2">Monto</label>
                            <input type="number" step="0.01" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fecha</label>
                            <input type="date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div class="flex justify-end space-x-4 pt-4 border-t border-gray-200">
                            <button type="button" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancelar</button>
                            <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg">Registrar Pago</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>
</body>
</html>

