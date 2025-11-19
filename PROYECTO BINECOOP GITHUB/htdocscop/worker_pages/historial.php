<?php
// Función limpiarDato si no está definida
if (!function_exists('limpiarDato')) {
    function limpiarDato($dato) {
        return htmlspecialchars(trim($dato), ENT_QUOTES, 'UTF-8');
    }
}

// Obtener parámetros de paginación y filtros
$page = isset($_GET['page_num']) ? max(1, intval($_GET['page_num'])) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

$filter_estado = isset($_GET['estado']) ? limpiarDato($_GET['estado']) : '';
$filter_fecha_desde = isset($_GET['fecha_desde']) ? limpiarDato($_GET['fecha_desde']) : '';
$filter_fecha_hasta = isset($_GET['fecha_hasta']) ? limpiarDato($_GET['fecha_hasta']) : '';
$search = isset($_GET['search']) ? limpiarDato($_GET['search']) : '';

// Construir consulta con filtros
$where_conditions = ["trabajador_id = ?"];
$params = [$worker_id];
$param_types = 'i';

if ($filter_estado && $filter_estado !== 'todos') {
    $where_conditions[] = "estado = ?";
    $params[] = $filter_estado;
    $param_types .= 's';
}

if ($filter_fecha_desde) {
    $where_conditions[] = "DATE(fecha_inicio) >= ?";
    $params[] = $filter_fecha_desde;
    $param_types .= 's';
}

if ($filter_fecha_hasta) {
    $where_conditions[] = "DATE(fecha_inicio) <= ?";
    $params[] = $filter_fecha_hasta;
    $param_types .= 's';
}

if ($search) {
    $where_conditions[] = "descripcion LIKE ?";
    $params[] = '%' . $search . '%';
    $param_types .= 's';
}

$where_clause = implode(' AND ', $where_conditions);

// Obtener total de registros
$total_registros = 0;
try {
    $query_count = "SELECT COUNT(*) as total FROM tiempo_trabajado WHERE $where_clause";
    $stmt_count = $mysqli->prepare($query_count);
    if ($stmt_count) {
        if (!empty($params)) {
            $stmt_count->bind_param($param_types, ...$params);
        }
        $stmt_count->execute();
        $result_count = $stmt_count->get_result();
        $row_count = $result_count->fetch_assoc();
        $total_registros = intval($row_count['total'] ?? 0);
        $stmt_count->close();
    }
} catch (Exception $e) {
    error_log("Error al contar registros: " . $e->getMessage());
}

$total_pages = max(1, ceil($total_registros / $per_page));

// Obtener registros con paginación
$registros = [];
try {
    $query = "SELECT id, descripcion, horas, fecha_inicio, fecha_fin, estado, fecha_creacion 
              FROM tiempo_trabajado 
              WHERE $where_clause 
              ORDER BY fecha_inicio DESC 
              LIMIT ? OFFSET ?";
    
    $params[] = $per_page;
    $params[] = $offset;
    $param_types .= 'ii';
    
    $stmt = $mysqli->prepare($query);
    if ($stmt) {
        $stmt->bind_param($param_types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $row['descripcion'] = limpiarDato($row['descripcion']);
            $row['horas'] = floatval($row['horas']);
            $registros[] = $row;
        }
        $stmt->close();
    }
} catch (Exception $e) {
    error_log("Error al obtener registros: " . $e->getMessage());
}

// Estadísticas del historial
$stats_historial = [
    'total_horas' => 0,
    'horas_aprobadas' => 0,
    'horas_pendientes' => 0,
    'total_registros' => 0
];

try {
    $query_stats = "SELECT 
        COALESCE(SUM(horas), 0) as total_horas,
        COALESCE(SUM(CASE WHEN estado = 'aprobado' THEN horas ELSE 0 END), 0) as horas_aprobadas,
        COALESCE(SUM(CASE WHEN estado = 'pendiente' THEN horas ELSE 0 END), 0) as horas_pendientes,
        COUNT(*) as total_registros
        FROM tiempo_trabajado 
        WHERE trabajador_id = ?";
    
    $stmt_stats = $mysqli->prepare($query_stats);
    if ($stmt_stats) {
        $stmt_stats->bind_param('i', $worker_id);
        $stmt_stats->execute();
        $result_stats = $stmt_stats->get_result();
        if ($row_stats = $result_stats->fetch_assoc()) {
            $stats_historial['total_horas'] = floatval($row_stats['total_horas'] ?? 0);
            $stats_historial['horas_aprobadas'] = floatval($row_stats['horas_aprobadas'] ?? 0);
            $stats_historial['horas_pendientes'] = floatval($row_stats['horas_pendientes'] ?? 0);
            $stats_historial['total_registros'] = intval($row_stats['total_registros'] ?? 0);
        }
        $stmt_stats->close();
    }
} catch (Exception $e) {
    error_log("Error al obtener estadísticas del historial: " . $e->getMessage());
}
?>

<!-- Header -->
<div class="mb-6 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                <i class="ti ti-history text-green-600 mr-3"></i>
                Mi Historial de Trabajo
            </h2>
            <p class="text-sm text-gray-500 mt-1">Consulta todos tus registros de tiempo trabajado</p>
        </div>
    </div>
    
    <!-- Estadísticas rápidas -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mt-6">
        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-blue-600 font-medium mb-1">Total Horas</p>
                    <p class="text-2xl font-bold text-blue-900"><?php echo number_format($stats_historial['total_horas'], 1); ?>h</p>
                </div>
                <i class="ti ti-clock text-3xl text-blue-300"></i>
            </div>
        </div>
        
        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-green-600 font-medium mb-1">Aprobadas</p>
                    <p class="text-2xl font-bold text-green-900"><?php echo number_format($stats_historial['horas_aprobadas'], 1); ?>h</p>
                </div>
                <i class="ti ti-check text-3xl text-green-300"></i>
            </div>
        </div>
        
        <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-yellow-600 font-medium mb-1">Pendientes</p>
                    <p class="text-2xl font-bold text-yellow-900"><?php echo number_format($stats_historial['horas_pendientes'], 1); ?>h</p>
                </div>
                <i class="ti ti-hourglass text-3xl text-yellow-300"></i>
            </div>
        </div>
        
        <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-purple-600 font-medium mb-1">Total Registros</p>
                    <p class="text-2xl font-bold text-purple-900"><?php echo $stats_historial['total_registros']; ?></p>
                </div>
                <i class="ti ti-list text-3xl text-purple-300"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="mb-6 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <form method="GET" action="dashboard.php" class="space-y-4">
        <input type="hidden" name="page" value="historial">
        
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Buscar en descripción..." 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                <select name="estado" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="todos" <?php echo $filter_estado === 'todos' || !$filter_estado ? 'selected' : ''; ?>>Todos</option>
                    <option value="aprobado" <?php echo $filter_estado === 'aprobado' ? 'selected' : ''; ?>>Aprobado</option>
                    <option value="pendiente" <?php echo $filter_estado === 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                    <option value="rechazado" <?php echo $filter_estado === 'rechazado' ? 'selected' : ''; ?>>Rechazado</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Desde</label>
                <input type="date" name="fecha_desde" value="<?php echo htmlspecialchars($filter_fecha_desde); ?>" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Hasta</label>
                <input type="date" name="fecha_hasta" value="<?php echo htmlspecialchars($filter_fecha_hasta); ?>" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    <i class="ti ti-filter mr-2"></i>Filtrar
                </button>
    </div>
</div>

<!-- Sección de Comprobantes de Pago PDF -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 mt-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
            <i class="ti ti-file-type-pdf text-red-600 mr-2"></i>
            Comprobantes de Pago
        </h3>
    </div>
    <div class="p-6">
        <?php
        // Obtener comprobantes del trabajador desde base de datos
        require_once __DIR__ . '/../includes/comprobantes_db.php';
        $comprobantes_trabajador = [];
        $todos_comprobantes = getComprobantes();
        foreach ($todos_comprobantes as $comp) {
            if ($comp['trabajador_id'] == $worker_id) {
                $comprobantes_trabajador[] = $comp;
            }
        }
        
        if (!empty($comprobantes_trabajador)):
        ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Archivo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($comprobantes_trabajador as $comp): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($comp['titulo']); ?>
                                    </div>
                                    <?php if (!empty($comp['descripcion'])): ?>
                                        <div class="text-sm text-gray-500 mt-1">
                                            <?php echo htmlspecialchars(substr($comp['descripcion'], 0, 50)) . (strlen($comp['descripcion']) > 50 ? '...' : ''); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center text-sm text-gray-900">
                                        <i class="ti ti-file-type-pdf text-red-500 mr-2"></i>
                                        <?php echo htmlspecialchars(substr($comp['nombre_archivo'], 0, 30)) . (strlen($comp['nombre_archivo']) > 30 ? '...' : ''); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php 
                                    if (!empty($comp['fecha_envio'])) {
                                        $fecha = new DateTime($comp['fecha_envio']);
                                        echo $fecha->format('d/m/Y H:i');
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $estado = $comp['estado'] ?? 'pendiente';
                                    $estado_colors = [
                                        'aprobado' => 'bg-green-100 text-green-800',
                                        'rechazado' => 'bg-red-100 text-red-800',
                                        'pendiente' => 'bg-yellow-100 text-yellow-800'
                                    ];
                                    $estado_text = [
                                        'aprobado' => 'Aprobado',
                                        'rechazado' => 'Rechazado',
                                        'pendiente' => 'Pendiente'
                                    ];
                                    $color = $estado_colors[$estado] ?? 'bg-gray-100 text-gray-800';
                                    $text = $estado_text[$estado] ?? ucfirst($estado);
                                    ?>
                                    <span class="px-3 py-1 inline-flex items-center text-xs leading-5 font-semibold rounded-full <?php echo $color; ?>">
                                        <?php echo $text; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="../../htdocspanel/api_admin_workers.php?action=download_report&report_id=<?php echo urlencode($comp['id']); ?>" 
                                       target="_blank"
                                       class="text-blue-600 hover:text-blue-900 flex items-center">
                                        <i class="ti ti-download mr-1"></i>
                                        Descargar
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-8 text-gray-500">
                <i class="ti ti-file-off text-4xl mb-2"></i>
                <p>No hay comprobantes de pago registrados</p>
                <p class="text-sm mt-1">Sube comprobantes desde el menú "Subir Comprobante"</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($search || $filter_estado || $filter_fecha_desde || $filter_fecha_hasta): ?>
        <div class="pt-2">
            <a href="dashboard.php?page=historial" class="text-sm text-red-600 hover:text-red-800">
                <i class="ti ti-x mr-1"></i>Limpiar filtros
            </a>
        </div>
        <?php endif; ?>
    </form>
</div>

<!-- Tabla de registros -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Registros de Tiempo</h3>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Horas</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Horario</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (!empty($registros)): ?>
                    <?php foreach ($registros as $registro): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php 
                                $fecha = new DateTime($registro['fecha_inicio']);
                                echo $fecha->format('d/m/Y');
                                ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900 max-w-md">
                                    <?php echo $registro['descripcion']; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-mono font-semibold text-gray-900">
                                    <?php echo number_format($registro['horas'], 2); ?>h
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php 
                                $fecha_inicio = new DateTime($registro['fecha_inicio']);
                                $hora_inicio = $fecha_inicio->format('H:i');
                                
                                if (!empty($registro['fecha_fin'])) {
                                    $fecha_fin = new DateTime($registro['fecha_fin']);
                                    $hora_fin = $fecha_fin->format('H:i');
                                    echo $hora_inicio . ' - ' . $hora_fin;
                                } else {
                                    echo $hora_inicio;
                                }
                                ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php 
                                $estado = $registro['estado'] ?? 'pendiente';
                                $color = '';
                                $icon = '';
                                switch($estado) {
                                    case 'aprobado':
                                        $color = 'bg-green-100 text-green-800';
                                        $icon = 'ti-check';
                                        break;
                                    case 'rechazado':
                                        $color = 'bg-red-100 text-red-800';
                                        $icon = 'ti-x';
                                        break;
                                    default:
                                        $color = 'bg-yellow-100 text-yellow-800';
                                        $icon = 'ti-hourglass';
                                        $estado = 'pendiente';
                                }
                                ?>
                                <span class="px-3 py-1 inline-flex items-center text-xs leading-5 font-semibold rounded-full <?php echo $color; ?>">
                                    <i class="ti <?php echo $icon; ?> mr-1"></i>
                                    <?php echo ucfirst($estado); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                    <i class="ti ti-clock text-3xl text-gray-300"></i>
                                </div>
                                <p class="text-lg font-medium text-gray-600">No se encontraron registros</p>
                                <p class="text-sm text-gray-400 mt-1">Intenta ajustar los filtros de búsqueda</p>
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
            Mostrando <span class="font-medium"><?php echo $offset + 1; ?></span> a 
            <span class="font-medium"><?php echo min($offset + $per_page, $total_registros); ?></span> de 
            <span class="font-medium"><?php echo $total_registros; ?></span> registros
        </div>
        <div class="flex space-x-2">
            <?php if ($page > 1): ?>
                <a href="?page=historial&page_num=<?php echo $page - 1; ?><?php echo $filter_estado ? '&estado=' . urlencode($filter_estado) : ''; ?><?php echo $filter_fecha_desde ? '&fecha_desde=' . urlencode($filter_fecha_desde) : ''; ?><?php echo $filter_fecha_hasta ? '&fecha_hasta=' . urlencode($filter_fecha_hasta) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                   class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    <i class="ti ti-chevron-left mr-1"></i>Anterior
                </a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                <a href="?page=historial&page_num=<?php echo $i; ?><?php echo $filter_estado ? '&estado=' . urlencode($filter_estado) : ''; ?><?php echo $filter_fecha_desde ? '&fecha_desde=' . urlencode($filter_fecha_desde) : ''; ?><?php echo $filter_fecha_hasta ? '&fecha_hasta=' . urlencode($filter_fecha_hasta) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                   class="px-3 py-2 text-sm font-medium <?php echo $i === $page ? 'bg-green-600 text-white' : 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50'; ?> rounded-lg">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
                <a href="?page=historial&page_num=<?php echo $page + 1; ?><?php echo $filter_estado ? '&estado=' . urlencode($filter_estado) : ''; ?><?php echo $filter_fecha_desde ? '&fecha_desde=' . urlencode($filter_fecha_desde) : ''; ?><?php echo $filter_fecha_hasta ? '&fecha_hasta=' . urlencode($filter_fecha_hasta) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                   class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    Siguiente<i class="ti ti-chevron-right ml-1"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

