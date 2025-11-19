<?php
session_start();

// Configurar encoding UTF-8
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

// Verificar autenticación
if (empty($_SESSION['user']['logged_in'])) {
    header("Location: ../../login.php");
    exit();
}

$GLOBALS['allowed_config_access'] = true;
require __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/db.php';

$current_user = $_SESSION['user'];
$username = $current_user['username'] ?? 'Usuario';
$user_role = $current_user['role'] ?? 'admin';

// Obtener parámetros de búsqueda y filtrado
$search = $_GET['search'] ?? '';
$estado_filter = $_GET['estado'] ?? 'todos';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Construir consulta
$where_conditions = ["estado_aprobacion = 'aprobado'"];
$params = [];
$types = '';

if (!empty($search)) {
    $where_conditions[] = "(nombre LIKE ? OR email LIKE ?)";
    $search_param = "%{$search}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

if ($estado_filter !== 'todos') {
    $where_conditions[] = "activo = ?";
    $params[] = $estado_filter === 'activo' ? 1 : 0;
    $types .= 'i';
}

$where_clause = implode(' AND ', $where_conditions);

// Contar total de registros
$count_query = "SELECT COUNT(*) as total FROM visitantes WHERE {$where_clause}";
$count_stmt = $mysqli->prepare($count_query);
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_result = $count_stmt->get_result();
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $per_page);

// Obtener socios
$query = "SELECT id, nombre, email, COALESCE(fecha_aprobacion, fecha_registro) as fecha_aprobacion, 
                 activo, ultimo_acceso 
          FROM visitantes 
          WHERE {$where_clause}
          ORDER BY COALESCE(fecha_aprobacion, fecha_registro) DESC 
          LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;
$types .= 'ii';

$stmt = $mysqli->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$socios = [];
while ($row = $result->fetch_assoc()) {
    $socios[] = $row;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Socios - Cooperativa de Vivienda</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" rel="stylesheet">
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50">

<!-- Layout Principal -->
<div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: false }">
    
    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 z-30 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0" 
           :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }">
        
        <div class="flex items-center justify-between px-6 py-4 bg-indigo-600 text-white">
            <h1 class="text-lg font-bold">Cooperativa</h1>
            <button class="lg:hidden" @click="sidebarOpen = false">
                <i class="ti ti-x text-xl"></i>
            </button>
        </div>
        
        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto h-full">
            <a href="../../dashboard.php" class="flex items-center px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors">
                <i class="ti ti-dashboard mr-3 text-lg"></i>
                Dashboard
            </a>
            
            <div x-data="{ open: true }">
                <button @click="open = !open" class="flex items-center w-full px-3 py-2 rounded-lg bg-indigo-100 text-indigo-700 font-medium">
                    <i class="ti ti-users mr-3 text-lg"></i>
                    Socios
                    <i class="ti ti-chevron-down ml-auto transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                </button>
                <div x-show="open" x-collapse class="ml-6 mt-1 space-y-1">
                    <a href="lista.php" class="block px-3 py-2 text-sm text-indigo-600 bg-indigo-50 rounded-lg font-medium">Lista de Socios</a>
                    <a href="nuevo.php" class="block px-3 py-2 text-sm text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg">Nuevo Socio</a>
                </div>
            </div>
            
            <div x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center w-full px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors">
                    <i class="ti ti-building-community mr-3 text-lg"></i>
                    Viviendas
                    <i class="ti ti-chevron-down ml-auto transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                </button>
                <div x-show="open" x-collapse class="ml-6 mt-1 space-y-1">
                    <a href="../viviendas/inventario.php" class="block px-3 py-2 text-sm text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg">Inventario</a>
                    <a href="../viviendas/asignaciones.php" class="block px-3 py-2 text-sm text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg">Asignaciones</a>
                </div>
            </div>
            
            <div x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center w-full px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors">
                    <i class="ti ti-currency-dollar mr-3 text-lg"></i>
                    Finanzas
                    <i class="ti ti-chevron-down ml-auto transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                </button>
                <div x-show="open" x-collapse class="ml-6 mt-1 space-y-1">
                    <a href="../finanzas/aportes.php" class="block px-3 py-2 text-sm text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg">Aportes</a>
                    <a href="../finanzas/pagos.php" class="block px-3 py-2 text-sm text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg">Pagos</a>
                </div>
            </div>
        </nav>
    </aside>

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white border-b border-gray-200 px-4 py-3 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <button class="text-gray-500 lg:hidden" @click="sidebarOpen = true">
                        <i class="ti ti-menu-2 text-xl"></i>
                    </button>
                    <h2 class="text-xl font-semibold text-gray-800">Lista de Socios</h2>
                </div>
                <a href="nuevo.php" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                    <i class="ti ti-user-plus mr-2"></i> Nuevo Socio
                </a>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-6">
            <!-- Mensajes de error -->
            <?php if (isset($_GET['error'])): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg shadow-sm">
                    <div class="flex items-center">
                        <i class="ti ti-alert-circle mr-2 text-xl"></i>
                        <div>
                            <p class="font-semibold">Error al cargar el perfil</p>
                            <p class="text-sm">
                                <?php 
                                if ($_GET['error'] == '1') {
                                    echo 'Ocurrió un error al obtener la información del socio. Por favor intenta nuevamente.';
                                } elseif ($_GET['error'] == '2') {
                                    echo 'El socio no fue encontrado o no está aprobado.';
                                } else {
                                    echo 'Error desconocido. Por favor contacta al administrador.';
                                }
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Filtros y búsqueda -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                <form method="GET" action="lista.php" class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Buscar por nombre o email..." 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <select name="estado" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="todos" <?php echo $estado_filter === 'todos' ? 'selected' : ''; ?>>Todos los estados</option>
                            <option value="activo" <?php echo $estado_filter === 'activo' ? 'selected' : ''; ?>>Activos</option>
                            <option value="inactivo" <?php echo $estado_filter === 'inactivo' ? 'selected' : ''; ?>>Inactivos</option>
                        </select>
                    </div>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg transition-colors flex items-center">
                        <i class="ti ti-search mr-2"></i> Buscar
                    </button>
                    <?php if (!empty($search) || $estado_filter !== 'todos'): ?>
                        <a href="lista.php" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg transition-colors flex items-center">
                            <i class="ti ti-x mr-2"></i> Limpiar
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Tabla de socios -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-800">Socios Registrados</h3>
                        <span class="text-sm text-gray-500">Total: <?php echo $total_rows; ?> socios</span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Socio</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Aprobación</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Último Acceso</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (!empty($socios)): ?>
                                <?php foreach ($socios as $socio): ?>
                                    <tr class="hover:bg-gray-50 transition-colors" id="socio-row-<?php echo $socio['id']; ?>">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="w-10 h-10 bg-gradient-to-r from-green-400 to-green-600 rounded-full flex items-center justify-center text-white font-semibold mr-3">
                                                    <?php echo strtoupper(substr($socio['nombre'], 0, 2)); ?>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($socio['nombre']); ?></div>
                                                    <div class="text-sm text-gray-500">ID: <?php echo $socio['id']; ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($socio['email']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php 
                                            if (!empty($socio['fecha_aprobacion'])) {
                                                try {
                                                    $fecha = new DateTime($socio['fecha_aprobacion']);
                                                    echo $fecha->format('d/m/Y H:i');
                                                } catch (Exception $e) {
                                                    echo htmlspecialchars($socio['fecha_aprobacion']);
                                                }
                                            } else {
                                                echo 'N/A';
                                            }
                                            ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php 
                                            if (!empty($socio['ultimo_acceso'])) {
                                                try {
                                                    $fecha = new DateTime($socio['ultimo_acceso']);
                                                    echo $fecha->format('d/m/Y H:i');
                                                } catch (Exception $e) {
                                                    echo 'N/A';
                                                }
                                            } else {
                                                echo 'Nunca';
                                            }
                                            ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if ($socio['activo'] == 1): ?>
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Activo</span>
                                            <?php else: ?>
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="relative inline-block" x-data="{ open: false }">
                                                <button @click="open = !open" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                                                    <i class="ti ti-dots-vertical text-xl"></i>
                                                </button>
                                                <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border border-gray-200">
                                                    <div class="py-1">
                                                        <a href="perfil.php?id=<?php echo $socio['id']; ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                                            <i class="ti ti-user mr-2"></i> Ver Perfil
                                                        </a>
                                                        <?php if ($socio['activo'] == 1): ?>
                                                            <button onclick="suspenderSocio(<?php echo $socio['id']; ?>)" class="w-full text-left px-4 py-2 text-sm text-yellow-700 hover:bg-yellow-50 flex items-center">
                                                                <i class="ti ti-ban mr-2"></i> Suspender
                                                            </button>
                                                        <?php else: ?>
                                                            <button onclick="activarSocio(<?php echo $socio['id']; ?>)" class="w-full text-left px-4 py-2 text-sm text-green-700 hover:bg-green-50 flex items-center">
                                                                <i class="ti ti-check mr-2"></i> Activar
                                                            </button>
                                                        <?php endif; ?>
                                                        <button onclick="eliminarSocio(<?php echo $socio['id']; ?>, '<?php echo htmlspecialchars($socio['nombre'], ENT_QUOTES); ?>')" class="w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-50 flex items-center">
                                                            <i class="ti ti-trash mr-2"></i> Eliminar
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                                <i class="ti ti-users text-3xl text-gray-300"></i>
                                            </div>
                                            <p class="text-lg font-medium text-gray-600">No se encontraron socios</p>
                                            <p class="text-sm text-gray-400 mt-1">Intenta con otros filtros de búsqueda</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Paginación -->
                <?php if ($total_pages > 1): ?>
                    <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Mostrando <?php echo $offset + 1; ?> a <?php echo min($offset + $per_page, $total_rows); ?> de <?php echo $total_rows; ?> socios
                        </div>
                        <div class="flex space-x-2">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&estado=<?php echo urlencode($estado_filter); ?>" 
                                   class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Anterior</a>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&estado=<?php echo urlencode($estado_filter); ?>" 
                                   class="px-3 py-2 border border-gray-300 rounded-lg <?php echo $i === $page ? 'bg-indigo-600 text-white' : 'hover:bg-gray-50'; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&estado=<?php echo urlencode($estado_filter); ?>" 
                                   class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Siguiente</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<script>
function suspenderSocio(id) {
    if (!confirm('¿Está seguro de suspender este socio?')) return;
    
    fetch('../../api_socios.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=suspender&id=${id}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('Socio suspendido exitosamente');
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(e => alert('Error: ' + e.message));
}

function activarSocio(id) {
    if (!confirm('¿Está seguro de activar este socio?')) return;
    
    fetch('../../api_socios.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=activar&id=${id}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('Socio activado exitosamente');
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(e => alert('Error: ' + e.message));
}

function eliminarSocio(id, nombre) {
    if (!confirm(`¿Está seguro de eliminar al socio "${nombre}"? Esta acción no se puede deshacer.`)) return;
    
    fetch('../../api_socios.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=eliminar&id=${id}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById(`socio-row-${id}`).remove();
            alert('Socio eliminado exitosamente');
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(e => alert('Error: ' + e.message));
}
</script>

</body>
</html>

