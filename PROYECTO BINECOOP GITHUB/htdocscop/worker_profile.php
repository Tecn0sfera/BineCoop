<?php
session_start();

// Configuración de errores
$is_development = true;
$is_ajax_request = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                 strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($is_development && !$is_ajax_request) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL);
}

$GLOBALS['allowed_config_access'] = true;
require __DIR__ . '/config.php';

// Funciones de seguridad
function limpiarDato($dato) {
    return htmlspecialchars(trim($dato), ENT_QUOTES, 'UTF-8');
}

// Redirección si no está logueado
if (empty($_SESSION['user_token']) || empty($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Conexión a la base de datos
try {
    require_once 'db.php';
} catch (Exception $e) {
    error_log("Error de conexión a BD: " . $e->getMessage());
    die("Error de conexión a la base de datos");
}

// Información del trabajador
$current_worker = $_SESSION['user'];
$worker_name = limpiarDato($current_worker['nombre'] ?? $current_worker['name'] ?? 'Trabajador');
$worker_id = filter_var($current_worker['id'] ?? 0, FILTER_VALIDATE_INT);

if ($worker_id === false || $worker_id <= 0) {
    session_unset();
    session_destroy();
    header("Location: login.php?error=invalid_session");
    exit();
}

// Obtener información adicional del trabajador (teléfono, CI)
$worker_telefono = '';
$worker_ci = '';
try {
    $query = "SELECT telefono, ci FROM visitantes WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    if ($stmt) {
        $stmt->bind_param('i', $worker_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $worker_telefono = $row['telefono'] ?? '';
            $worker_ci = $row['ci'] ?? '';
        }
        $stmt->close();
    }
} catch (Exception $e) {
    error_log("Error al obtener datos del trabajador: " . $e->getMessage());
}

// Determinar página actual
$current_page = isset($_GET['page']) ? limpiarDato($_GET['page']) : 'perfil';

// Obtener estadísticas del trabajador
$stats = [
    'total_horas' => 0,
    'horas_aprobadas' => 0,
    'horas_pendientes' => 0,
    'total_reportes' => 0,
    'reportes_pendientes' => 0,
    'total_aportes' => 0,
    'viviendas_asignadas' => 0
];

// Horas totales
try {
    $query = "SELECT COALESCE(SUM(horas), 0) as total FROM tiempo_trabajado WHERE trabajador_id = ?";
    $stmt = $mysqli->prepare($query);
    if ($stmt) {
        $stmt->bind_param('i', $worker_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stats['total_horas'] = floatval($row['total'] ?? 0);
        $stmt->close();
    }
} catch (Exception $e) {
    error_log("Error al obtener horas totales: " . $e->getMessage());
}

// Horas aprobadas
try {
    $query = "SELECT COALESCE(SUM(horas), 0) as total FROM tiempo_trabajado WHERE trabajador_id = ? AND estado = 'aprobado'";
    $stmt = $mysqli->prepare($query);
    if ($stmt) {
        $stmt->bind_param('i', $worker_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stats['horas_aprobadas'] = floatval($row['total'] ?? 0);
        $stmt->close();
    }
} catch (Exception $e) {
    error_log("Error al obtener horas aprobadas: " . $e->getMessage());
}

// Horas pendientes
try {
    $query = "SELECT COALESCE(SUM(horas), 0) as total FROM tiempo_trabajado WHERE trabajador_id = ? AND estado = 'pendiente'";
    $stmt = $mysqli->prepare($query);
    if ($stmt) {
        $stmt->bind_param('i', $worker_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stats['horas_pendientes'] = floatval($row['total'] ?? 0);
        $stmt->close();
    }
} catch (Exception $e) {
    error_log("Error al obtener horas pendientes: " . $e->getMessage());
}

// Total reportes
try {
    $query = "SELECT COUNT(*) as total FROM reportes_pdf WHERE trabajador_id = ?";
    $stmt = $mysqli->prepare($query);
    if ($stmt) {
        $stmt->bind_param('i', $worker_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stats['total_reportes'] = intval($row['total'] ?? 0);
        $stmt->close();
    }
} catch (Exception $e) {
    error_log("Error al obtener total reportes: " . $e->getMessage());
}

// Obtener información de aportes (si existe tabla)
$aportes = [];
try {
    $check_table = $mysqli->query("SHOW TABLES LIKE 'aportes'");
    if ($check_table && $check_table->num_rows > 0) {
        $query = "SELECT COALESCE(SUM(monto), 0) as total FROM aportes WHERE trabajador_id = ?";
        $stmt = $mysqli->prepare($query);
        if ($stmt) {
            $stmt->bind_param('i', $worker_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stats['total_aportes'] = floatval($row['total'] ?? 0);
            $stmt->close();
        }
    }
} catch (Exception $e) {
    error_log("Error al obtener aportes: " . $e->getMessage());
}

// Obtener viviendas asignadas (si existe tabla)
try {
    $check_table = $mysqli->query("SHOW TABLES LIKE 'viviendas'");
    if ($check_table && $check_table->num_rows > 0) {
        $query = "SELECT COUNT(*) as total FROM viviendas WHERE trabajador_id = ? OR asignado_a = ?";
        $stmt = $mysqli->prepare($query);
        if ($stmt) {
            $stmt->bind_param('ii', $worker_id, $worker_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stats['viviendas_asignadas'] = intval($row['total'] ?? 0);
            $stmt->close();
        }
    }
} catch (Exception $e) {
    error_log("Error al obtener viviendas: " . $e->getMessage());
}

// Obtener registros recientes
$registros_recientes = [];
try {
    $query = "SELECT id, descripcion, horas, fecha_inicio, estado 
              FROM tiempo_trabajado 
              WHERE trabajador_id = ? 
              ORDER BY fecha_inicio DESC 
              LIMIT 5";
    $stmt = $mysqli->prepare($query);
    if ($stmt) {
        $stmt->bind_param('i', $worker_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $registros_recientes[] = $row;
        }
        $stmt->close();
    }
} catch (Exception $e) {
    error_log("Error al obtener registros recientes: " . $e->getMessage());
}

// Tarifa por hora (configurable)
$tarifa_por_hora = 15.00;
$monto_total_estimado = $stats['horas_aprobadas'] * $tarifa_por_hora;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Panel Trabajador</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Tabler Icons -->
    <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" rel="stylesheet">
    
    <style>
        [x-cloak] { display: none !important; }
        
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-50">
    
    <!-- Layout Principal -->
    <div class="flex h-screen overflow-hidden">
        
        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 z-30 w-64 bg-white shadow-lg lg:static lg:inset-0">
            <div class="flex items-center justify-between px-6 py-4 bg-green-600 text-white">
                <h1 class="text-lg font-bold">Panel Trabajador</h1>
            </div>
            
            <nav class="flex-1 px-4 py-6 space-y-2">
                <a href="dashboard.php" class="flex items-center px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors">
                    <i class="ti ti-arrow-left mr-3 text-lg"></i>
                    Volver al Dashboard
                </a>
                <a href="worker_profile.php?page=info_personal" class="flex items-center px-3 py-2 rounded-lg <?php echo (isset($_GET['page']) && $_GET['page'] === 'info_personal') ? 'bg-green-100 text-green-700 font-medium' : 'text-gray-700 hover:bg-gray-100'; ?> transition-colors">
                    <i class="ti ti-user mr-3 text-lg"></i>
                    Info Personal
                </a>
                <a href="worker_profile.php?page=configuracion" class="flex items-center px-3 py-2 rounded-lg <?php echo (isset($_GET['page']) && $_GET['page'] === 'configuracion') ? 'bg-green-100 text-green-700 font-medium' : 'text-gray-700 hover:bg-gray-100'; ?> transition-colors">
                    <i class="ti ti-settings mr-3 text-lg"></i>
                    Configuración
                </a>
            </nav>
        </aside>
        
        <!-- Contenido Principal -->
        <div class="flex-1 flex flex-col overflow-hidden">
            
            <!-- Header -->
            <header class="bg-white border-b border-gray-200 px-4 py-3 shadow-sm">
                <h2 class="text-xl font-semibold text-gray-800">Mi Perfil</h2>
            </header>
            
            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto p-6">
                
                <?php if ($current_page === 'configuracion'): ?>
                    <!-- Página de Configuración -->
                    <?php include 'worker_pages/configuracion.php'; ?>
                <?php elseif ($current_page === 'info_personal'): ?>
                    <!-- Página de Información Personal -->
                    <?php
                    // Obtener información completa del socio
                    $socio_info = [];
                    try {
                        $query_socio = "SELECT v.*, s.tipo_asignacion, s.fecha_ingreso, s.vivienda_id 
                                       FROM visitantes v 
                                       LEFT JOIN socios s ON v.id = s.visitante_id 
                                       WHERE v.id = ?";
                        $stmt_socio = $mysqli->prepare($query_socio);
                        if ($stmt_socio) {
                            $stmt_socio->bind_param('i', $worker_id);
                            $stmt_socio->execute();
                            $result_socio = $stmt_socio->get_result();
                            if ($row = $result_socio->fetch_assoc()) {
                                $socio_info = $row;
                            }
                            $stmt_socio->close();
                        }
                    } catch (Exception $e) {
                        error_log("Error al obtener información del socio: " . $e->getMessage());
                    }
                    ?>
                    <div class="max-w-4xl mx-auto">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                            <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                                <i class="ti ti-user text-green-600 mr-3"></i>
                                Información Personal
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nombre Completo</label>
                                    <div class="px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-900">
                                        <?php echo htmlspecialchars($socio_info['nombre'] ?? $worker_name); ?>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                    <div class="px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-900">
                                        <?php echo htmlspecialchars($socio_info['email'] ?? 'No disponible'); ?>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Cédula de Identidad (CI)</label>
                                    <div class="px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-900">
                                        <?php echo htmlspecialchars($socio_info['ci'] ?? $worker_ci ?: 'No registrada'); ?>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Teléfono</label>
                                    <div class="px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-900">
                                        <?php echo htmlspecialchars($socio_info['telefono'] ?? $worker_telefono ?: 'No registrado'); ?>
                                    </div>
                                </div>
                                
                                <?php if (!empty($socio_info['tipo_asignacion'])): ?>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Asignación</label>
                                    <div class="px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-900">
                                        <?php echo ucfirst(htmlspecialchars($socio_info['tipo_asignacion'])); ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($socio_info['fecha_ingreso'])): ?>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Fecha de Ingreso</label>
                                    <div class="px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-900">
                                        <?php 
                                        $fecha_ingreso = new DateTime($socio_info['fecha_ingreso']);
                                        echo $fecha_ingreso->format('d/m/Y');
                                        ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">ID de Trabajador</label>
                                    <div class="px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-900">
                                        <?php echo $worker_id; ?>
                                    </div>
                                </div>
                                
                                <?php if (!empty($socio_info['creado_en'])): ?>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Fecha de Registro</label>
                                    <div class="px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-900">
                                        <?php 
                                        $fecha_registro = new DateTime($socio_info['creado_en']);
                                        echo $fecha_registro->format('d/m/Y H:i');
                                        ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Página de Perfil -->
                
                <!-- Información del Usuario -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-20 h-20 bg-green-600 rounded-full flex items-center justify-center text-white font-bold text-2xl">
                            <?php echo strtoupper(substr($worker_name, 0, 2)); ?>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900"><?php echo $worker_name; ?></h3>
                            <p class="text-gray-500">Trabajador</p>
                            <p class="text-sm text-gray-400 mt-1">ID: <?php echo $worker_id; ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Estadísticas -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    
                    <!-- Total Horas -->
                    <div class="card-hover bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium opacity-90">Total Horas</p>
                                <p class="text-2xl font-bold mt-1"><?php echo number_format($stats['total_horas'], 1); ?></p>
                            </div>
                            <i class="ti ti-clock text-3xl opacity-50"></i>
                        </div>
                    </div>
                    
                    <!-- Horas Aprobadas -->
                    <div class="card-hover bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium opacity-90">Horas Aprobadas</p>
                                <p class="text-2xl font-bold mt-1"><?php echo number_format($stats['horas_aprobadas'], 1); ?></p>
                            </div>
                            <i class="ti ti-check text-3xl opacity-50"></i>
                        </div>
                    </div>
                    
                    <!-- Horas Pendientes -->
                    <div class="card-hover bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium opacity-90">Horas Pendientes</p>
                                <p class="text-2xl font-bold mt-1"><?php echo number_format($stats['horas_pendientes'], 1); ?></p>
                            </div>
                            <i class="ti ti-hourglass text-3xl opacity-50"></i>
                        </div>
                    </div>
                    
                    <!-- Total Reportes -->
                    <div class="card-hover bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium opacity-90">Total Reportes</p>
                                <p class="text-2xl font-bold mt-1"><?php echo $stats['total_reportes']; ?></p>
                            </div>
                            <i class="ti ti-file-text text-3xl opacity-50"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Secciones de Información -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    
                    <!-- Aportes -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="ti ti-currency-dollar text-green-600 mr-2"></i>
                            Aportes
                        </h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-gray-600">Total Aportes:</span>
                                <span class="font-bold text-green-600">$<?php echo number_format($stats['total_aportes'], 2); ?></span>
                            </div>
                            <p class="text-sm text-gray-500">Los aportes se registran automáticamente según tus horas aprobadas.</p>
                        </div>
                    </div>
                    
                    <!-- Viviendas -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="ti ti-building-community text-blue-600 mr-2"></i>
                            Viviendas
                        </h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-gray-600">Viviendas Asignadas:</span>
                                <span class="font-bold text-blue-600"><?php echo $stats['viviendas_asignadas']; ?></span>
                            </div>
                            <p class="text-sm text-gray-500">Consulta con el administrador para más información sobre viviendas.</p>
                        </div>
                    </div>
                    
                    <!-- Pagos -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="ti ti-wallet text-purple-600 mr-2"></i>
                            Pagos
                        </h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-gray-600">Monto Estimado:</span>
                                <span class="font-bold text-purple-600">$<?php echo number_format($monto_total_estimado, 2); ?></span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-gray-600">Tarifa por Hora:</span>
                                <span class="font-semibold text-gray-700">$<?php echo number_format($tarifa_por_hora, 2); ?></span>
                            </div>
                            <a href="dashboard.php?page=pagos" class="block text-center text-sm text-purple-600 hover:text-purple-700 font-medium mt-2">
                                Ver detalles de pagos →
                            </a>
                        </div>
                    </div>
                    
                    <!-- Estadísticas -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="ti ti-chart-bar text-orange-600 mr-2"></i>
                            Estadísticas
                        </h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-gray-600">Promedio Horas/Día:</span>
                                <span class="font-semibold text-gray-700">
                                    <?php 
                                    $dias_trabajados = max(1, $stats['total_horas'] > 0 ? ceil($stats['total_horas'] / 8) : 1);
                                    echo number_format($stats['total_horas'] / $dias_trabajados, 1); 
                                    ?>h
                                </span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-gray-600">Tasa de Aprobación:</span>
                                <span class="font-semibold text-gray-700">
                                    <?php 
                                    $tasa = $stats['total_horas'] > 0 ? ($stats['horas_aprobadas'] / $stats['total_horas']) * 100 : 0;
                                    echo number_format($tasa, 1); 
                                    ?>%
                                </span>
                            </div>
                            <a href="dashboard.php?page=estadisticas" class="block text-center text-sm text-orange-600 hover:text-orange-700 font-medium mt-2">
                                Ver más estadísticas →
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Registros Recientes -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="ti ti-history text-gray-600 mr-2"></i>
                        Registros Recientes
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descripción</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Horas</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (!empty($registros_recientes)): ?>
                                    <?php foreach ($registros_recientes as $registro): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm text-gray-900"><?php echo limpiarDato($registro['descripcion']); ?></td>
                                            <td class="px-4 py-3 text-sm font-semibold text-gray-900"><?php echo number_format($registro['horas'], 2); ?>h</td>
                                            <td class="px-4 py-3 text-sm text-gray-500">
                                                <?php 
                                                $fecha = new DateTime($registro['fecha_inicio']);
                                                echo $fecha->format('d/m/Y');
                                                ?>
                                            </td>
                                            <td class="px-4 py-3">
                                                <?php 
                                                $estado = $registro['estado'] ?? 'pendiente';
                                                $color = $estado === 'aprobado' ? 'bg-green-100 text-green-800' : 
                                                        ($estado === 'rechazado' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800');
                                                ?>
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $color; ?>">
                                                    <?php echo ucfirst($estado); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                            No hay registros recientes
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <?php endif; ?>
            </main>
        </div>
    </div>
    
</body>
</html>

