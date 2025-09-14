<?php
session_start();


// Configuración de errores - Solo mostrar en desarrollo, no en AJAX
$is_development = true; // Cambiar a false en producción
$is_ajax_request = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                   strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($is_development && !$is_ajax_request) {
    // Mostrar errores solo en desarrollo y NO en peticiones AJAX
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    // En producción o peticiones AJAX, no mostrar errores
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL); // Seguir logueando, pero no mostrando
}



// Mostrar errores para depurar
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$GLOBALS['allowed_config_access'] = true;
require __DIR__ . '/config.php';

// Funciones de seguridad adaptadas del login
function limpiarDato($dato) {
    return htmlspecialchars(trim($dato), ENT_QUOTES, 'UTF-8');
}

function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Control de inactividad mejorado
function checkSessionSecurity() {
    // Verificar inactividad (30 min)
    $inactivity = 1800;
    if (isset($_SESSION['worker']['last_activity'])) {
        if (time() - $_SESSION['worker']['last_activity'] > $inactivity) {
            session_unset();
            session_destroy();
            header("Location: login.php?timeout=1");
            exit();
        }
    }
    $_SESSION['worker']['last_activity'] = time();
    
    // Regenerar session ID periódicamente por seguridad
    if (!isset($_SESSION['worker']['session_regenerated'])) {
        $_SESSION['worker']['session_regenerated'] = time();
    } elseif (time() - $_SESSION['worker']['session_regenerated'] > 300) { // cada 5 minutos
        session_regenerate_id(true);
        $_SESSION['worker']['session_regenerated'] = time();
    }
}

// Redirección si no está logueado
if (empty($_SESSION['user_token']) || empty($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Aplicar verificaciones de seguridad
checkSessionSecurity();

// Conexión a la base de datos con manejo de errores
try {
    require_once 'db.php';
} catch (Exception $e) {
    error_log("Error de conexión a BD: " . $e->getMessage());
    die("Error de conexión a la base de datos");
}

// Información del trabajador logueado con sanitización
$current_worker = $_SESSION['user']; // En lugar de $_SESSION['worker']
$worker_name = limpiarDato($current_worker['name'] ?? 'Trabajador');
$worker_id = filter_var($current_worker['id'] ?? 0, FILTER_VALIDATE_INT);


if ($worker_id === false || $worker_id <= 0) {
    session_unset();
    session_destroy();
    header("Location: login.php?error=invalid_session");
    exit();
}

// Inicializar variables con valores seguros
$stats = [
    'horas_hoy' => 0,
    'horas_semana' => 0,
    'horas_mes' => 0,
    'reportes_enviados' => 0,
    'ultimo_reporte' => null
];

// Obtener estadísticas del trabajador con prepared statements
try {
    // Horas de hoy
    $query_hoy = "SELECT COALESCE(SUM(horas), 0) as total FROM tiempo_trabajado 
                  WHERE trabajador_id = ? AND DATE(fecha_inicio) = CURDATE()";
    $stmt = $mysqli->prepare($query_hoy);
    if (!$stmt) throw new Exception("Error preparing statement: " . $mysqli->error);
    
    $stmt->bind_param('i', $worker_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stats['horas_hoy'] = floatval($row['total'] ?? 0);
    $stmt->close();

    // Horas de esta semana
    $query_semana = "SELECT COALESCE(SUM(horas), 0) as total FROM tiempo_trabajado 
                     WHERE trabajador_id = ? AND YEARWEEK(fecha_inicio, 1) = YEARWEEK(CURDATE(), 1)";
    $stmt = $mysqli->prepare($query_semana);
    if (!$stmt) throw new Exception("Error preparing statement: " . $mysqli->error);
    
    $stmt->bind_param('i', $worker_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stats['horas_semana'] = floatval($row['total'] ?? 0);
    $stmt->close();

    // Horas de este mes
    $query_mes = "SELECT COALESCE(SUM(horas), 0) as total FROM tiempo_trabajado 
                  WHERE trabajador_id = ? AND MONTH(fecha_inicio) = MONTH(CURDATE()) AND YEAR(fecha_inicio) = YEAR(CURDATE())";
    $stmt = $mysqli->prepare($query_mes);
    if (!$stmt) throw new Exception("Error preparing statement: " . $mysqli->error);
    
    $stmt->bind_param('i', $worker_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stats['horas_mes'] = floatval($row['total'] ?? 0);
    $stmt->close();

    // Reportes enviados este mes
    $query_reportes = "SELECT COUNT(*) as total FROM reportes_pdf 
                       WHERE trabajador_id = ? AND MONTH(fecha_envio) = MONTH(CURDATE()) AND YEAR(fecha_envio) = YEAR(CURDATE())";
    $stmt = $mysqli->prepare($query_reportes);
    if (!$stmt) throw new Exception("Error preparing statement: " . $mysqli->error);
    
    $stmt->bind_param('i', $worker_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stats['reportes_enviados'] = intval($row['total'] ?? 0);
    $stmt->close();

    // Último reporte
    $query_ultimo = "SELECT fecha_envio FROM reportes_pdf 
                     WHERE trabajador_id = ? ORDER BY fecha_envio DESC LIMIT 1";
    $stmt = $mysqli->prepare($query_ultimo);
    if (!$stmt) throw new Exception("Error preparing statement: " . $mysqli->error);
    
    $stmt->bind_param('i', $worker_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $stats['ultimo_reporte'] = $row['fecha_envio'];
    }
    $stmt->close();

} catch (Exception $e) {
    error_log("Error al obtener estadísticas: " . $e->getMessage());
    // Mantener valores por defecto en caso de error
}

// Obtener registros de tiempo recientes con límite y sanitización
$tiempo_recientes = [];
try {
    $query_tiempo = "SELECT id, descripcion, horas, fecha_inicio, fecha_fin, estado 
                     FROM tiempo_trabajado 
                     WHERE trabajador_id = ? 
                     ORDER BY fecha_inicio DESC 
                     LIMIT 10";
    $stmt = $mysqli->prepare($query_tiempo);
    if (!$stmt) throw new Exception("Error preparing statement: " . $mysqli->error);
    
    $stmt->bind_param('i', $worker_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        // Sanitizar datos antes de mostrar
        $row['descripcion'] = limpiarDato($row['descripcion']);
        $row['horas'] = floatval($row['horas']);
        $tiempo_recientes[] = $row;
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Error al obtener registros de tiempo: " . $e->getMessage());
}

// Procesar mensajes de estado
$success_message = '';
$error_message = '';

if (isset($_SESSION['dashboard_success'])) {
    $success_message = limpiarDato($_SESSION['dashboard_success']);
    unset($_SESSION['dashboard_success']);
}

if (isset($_SESSION['dashboard_error'])) {
    $error_message = limpiarDato($_SESSION['dashboard_error']);
    unset($_SESSION['dashboard_error']);
}

// Verificar parámetros GET para mensajes
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'time_saved':
            $success_message = 'Tiempo registrado exitosamente';
            break;
        case 'pdf_uploaded':
            $success_message = 'Reporte PDF subido correctamente';
            break;
    }
}

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'invalid_data':
            $error_message = 'Datos inválidos proporcionados';
            break;
        case 'file_error':
            $error_message = 'Error al procesar el archivo';
            break;
        case 'timeout':
            $error_message = 'Sesión expirada por inactividad';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Trabajador - Sistema de Tiempo</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Tabler Icons -->
    <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" rel="stylesheet">
    
    <!-- Chart.js para gráficos -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        [x-cloak] { display: none !important; }
        
        /* Animaciones personalizadas */
        @keyframes slideInUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        @keyframes fadeInScale {
            from { transform: scale(0.95); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .animate-slide-up {
            animation: slideInUp 0.6s ease-out;
        }
        
        .animate-fade-scale {
            animation: fadeInScale 0.4s ease-out;
        }
        
        .animate-pulse-soft {
            animation: pulse 2s infinite;
        }
        
        /* Gradientes personalizados */
        .gradient-blue {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .gradient-green {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .gradient-purple {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .gradient-orange {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }
        
        /* Efectos hover mejorados */
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        
        /* Scrollbar personalizado */
        .custom-scroll::-webkit-scrollbar {
            width: 6px;
        }
        
        .custom-scroll::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }
        
        .custom-scroll::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }
        
        .custom-scroll::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
        
        /* Timer styles */
        .timer-display {
            font-family: 'Courier New', monospace;
            font-size: 2.5rem;
            font-weight: bold;
        }
        
        .timer-active {
            color: #10b981;
        }
        
        .timer-paused {
            color: #f59e0b;
        }
        
        /* Estilos para alertas similares al login */
        .alert {
            padding: 12px 16px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 8px;
            display: flex;
            align-items: center;
            font-size: 14px;
        }
        
        .alert-success {
            color: #0f5132;
            background-color: #d1e7dd;
            border-color: #badbcc;
        }
        
        .alert-danger {
            color: #842029;
            background-color: #f8d7da;
            border-color: #f5c2c7;
        }
        
        .alert-warning {
            color: #664d03;
            background-color: #fff3cd;
            border-color: #ffecb5;
        }
        
        .alert i {
            margin-right: 8px;
            font-size: 16px;
        }
        
        /* Loading states */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        
        .loading-spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Validación de formularios */
        .is-invalid {
            border-color: #dc3545 !important;
        }
        
        .error-message {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: flex;
            align-items: center;
        }
        
        .error-message i {
            margin-right: 4px;
        }
    </style>
</head>
<body class="bg-gray-50" x-data="workerApp()" x-init="init()">

<!-- Loading Overlay -->
<div x-show="loading" x-transition class="loading-overlay" style="display: none;">
    <div class="bg-white p-6 rounded-lg shadow-lg text-center">
        <div class="loading-spinner mx-auto mb-4"></div>
        <p class="text-gray-600">Procesando...</p>
    </div>
</div>

<!-- Layout Principal -->
<div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: false }">
    
    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 z-30 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0" 
           :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }">
        
        <!-- Header del Sidebar -->
        <div class="flex items-center justify-between px-6 py-4 bg-green-600 text-white">
            <h1 class="text-lg font-bold">Panel Trabajador</h1>
            <button class="lg:hidden" @click="sidebarOpen = false">
                <i class="ti ti-x text-xl"></i>
            </button>
        </div>
        
        <!-- Navegación -->
        <nav class="flex-1 px-4 py-6 space-y-2 custom-scroll overflow-y-auto h-full">
            <a href="#" class="flex items-center px-3 py-2 rounded-lg bg-green-100 text-green-700 font-medium">
                <i class="ti ti-dashboard mr-3 text-lg"></i>
                Dashboard
            </a>
            
            <a href="#" @click="showTimeModal = true" class="flex items-center px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors">
                <i class="ti ti-clock mr-3 text-lg"></i>
                Registrar Tiempo
            </a>
            
            <a href="#" @click="showPdfModal = true" class="flex items-center px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors">
                <i class="ti ti-file-upload mr-3 text-lg"></i>
                Subir Reporte
            </a>
            
            <a href="#" class="flex items-center px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors">
                <i class="ti ti-history mr-3 text-lg"></i>
                Mi Historial
            </a>
        </nav>
    </aside>

    <!-- Contenido Principal -->
    <div class="flex-1 flex flex-col overflow-hidden">
        
        <!-- Header -->
        <header class="bg-white border-b border-gray-200 px-4 py-3 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <button class="text-gray-500 lg:hidden" @click="sidebarOpen = true">
                        <i class="ti ti-menu-2 text-xl"></i>
                    </button>
                    <h2 class="text-xl font-semibold text-gray-800">Panel de Trabajo</h2>
                </div>
                
                <!-- Header Right -->
                <div class="flex items-center space-x-4">
                    <!-- Timer Display -->
                    <div class="bg-gray-100 rounded-lg px-4 py-2">
                        <div class="text-sm text-gray-600">Tiempo Activo</div>
                        <div class="timer-display text-lg" :class="{ 'timer-active': timerRunning, 'timer-paused': !timerRunning }" x-text="formatTime(currentTime)">00:00:00</div>
                    </div>
                    
                    <!-- Usuario -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="w-8 h-8 bg-green-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                <?php echo strtoupper(substr($worker_name, 0, 2)); ?>
                            </div>
                            <span class="hidden sm:block text-sm font-medium text-gray-700"><?php echo $worker_name; ?></span>
                            <i class="ti ti-chevron-down text-sm text-gray-500"></i>
                        </button>
                        
                        <div x-show="open" @click.away="open = false" x-transition
                            class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                            <div class="px-4 py-2 border-b border-gray-200">
                                <div class="text-sm font-medium text-gray-900"><?php echo $worker_name; ?></div>
                                <div class="text-xs text-gray-500">Trabajador</div>
                            </div>
                            <a href="worker_profile.php" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                                <i class="ti ti-user mr-2"></i> Mi Perfil
                            </a>
                            <div class="border-t border-gray-200 mt-1 pt-1">
                                <a href="worker_logout.php" class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                    <i class="ti ti-logout mr-2"></i> Cerrar Sesión
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto p-6 custom-scroll">
            
            <!-- Alertas de estado -->
            <?php if ($success_message): ?>
                <div class="alert alert-success animate-fade-scale">
                    <i class="ti ti-check-circle"></i>
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-danger animate-fade-scale">
                    <i class="ti ti-alert-circle"></i>
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <!-- Tarjetas de estadísticas -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                
                <!-- Horas Hoy -->
                <div class="card-hover gradient-blue rounded-xl p-6 text-white animate-fade-scale">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium opacity-90">Horas Hoy</p>
                            <p class="text-2xl font-bold mt-1"><?php echo number_format($stats['horas_hoy'], 1); ?></p>
                            <p class="text-xs opacity-75 mt-1">Trabajadas</p>
                        </div>
                        <div class="bg-white bg-opacity-20 p-3 rounded-full">
                            <i class="ti ti-clock text-2xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Horas Semana -->
                <div class="card-hover gradient-green rounded-xl p-6 text-white animate-fade-scale" style="animation-delay: 0.1s">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium opacity-90">Esta Semana</p>
                            <p class="text-2xl font-bold mt-1"><?php echo number_format($stats['horas_semana'], 1); ?></p>
                            <p class="text-xs opacity-75 mt-1">Horas</p>
                        </div>
                        <div class="bg-white bg-opacity-20 p-3 rounded-full">
                            <i class="ti ti-calendar-week text-2xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Horas Mes -->
                <div class="card-hover gradient-purple rounded-xl p-6 text-white animate-fade-scale" style="animation-delay: 0.2s">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium opacity-90">Este Mes</p>
                            <p class="text-2xl font-bold mt-1"><?php echo number_format($stats['horas_mes'], 1); ?></p>
                            <p class="text-xs opacity-75 mt-1">Horas</p>
                        </div>
                        <div class="bg-white bg-opacity-20 p-3 rounded-full">
                            <i class="ti ti-calendar text-2xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Reportes -->
                <div class="card-hover gradient-orange rounded-xl p-6 text-white animate-fade-scale" style="animation-delay: 0.3s">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium opacity-90">Reportes</p>
                            <p class="text-2xl font-bold mt-1"><?php echo $stats['reportes_enviados']; ?></p>
                            <p class="text-xs opacity-75 mt-1">Este mes</p>
                        </div>
                        <div class="bg-white bg-opacity-20 p-3 rounded-full">
                            <i class="ti ti-file-text text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grid de contenido principal -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                
                <!-- Registros de Tiempo Recientes -->
                <div class="xl:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 animate-slide-up">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                <i class="ti ti-clock-hour-3 text-green-600 mr-2"></i>
                                Registros Recientes
                            </h3>
                            <button @click="showTimeModal = true" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                <i class="ti ti-plus mr-1"></i> Nuevo Registro
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Descripción
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Horas
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Fecha
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Estado
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (!empty($tiempo_recientes)): ?>
                                    <?php foreach ($tiempo_recientes as $registro): ?>
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo $registro['descripcion']; ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900 font-mono">
                                                    <?php echo number_format($registro['horas'], 2); ?>h
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php 
                                                if (!empty($registro['fecha_inicio'])) {
                                                    $fecha = new DateTime($registro['fecha_inicio']);
                                                    echo $fecha->format('d/m/Y H:i');
                                                } else {
                                                    echo 'N/A';
                                                }
                                                ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php 
                                                $estado = $registro['estado'] ?? 'pendiente';
                                                $color = '';
                                                switch($estado) {
                                                    case 'aprobado':
                                                        $color = 'bg-green-100 text-green-800';
                                                        break;
                                                    case 'rechazado':
                                                        $color = 'bg-red-100 text-red-800';
                                                        break;
                                                    default:
                                                        $color = 'bg-yellow-100 text-yellow-800';
                                                        $estado = 'pendiente';
                                                }
                                                ?>
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $color; ?>">
                                                    <?php echo ucfirst($estado); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                            <div class="flex flex-col items-center">
                                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                                    <i class="ti ti-clock text-3xl text-gray-300"></i>
                                                </div>
                                                <p class="text-lg font-medium text-gray-600">No hay registros de tiempo</p>
                                                <p class="text-sm text-gray-400 mt-1">Registra tu primera sesión de trabajo</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Panel lateral -->
                <div class="space-y-6">
                    
                    <!-- Timer Control -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 animate-slide-up" style="animation-delay: 0.2s">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                <i class="ti ti-player-play text-green-600 mr-2"></i>
                                Control de Tiempo
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="text-center">
                                <div class="timer-display mb-4" :class="{ 'timer-active': timerRunning, 'timer-paused': !timerRunning }" x-text="formatTime(currentTime)">00:00:00</div>
                                
                                <div class="space-y-3">
                                    <button x-show="!timerRunning" @click="startTimer()" 
                                            class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-4 rounded-lg transition-colors flex items-center justify-center">
                                        <i class="ti ti-player-play mr-2"></i>
                                        Iniciar Timer
                                    </button>
                                    
                                    <button x-show="timerRunning" @click="pauseTimer()" 
                                            class="w-full bg-yellow-600 hover:bg-yellow-700 text-white font-medium py-3 px-4 rounded-lg transition-colors flex items-center justify-center">
                                        <i class="ti ti-player-pause mr-2"></i>
                                        Pausar Timer
                                    </button>
                                    
                                    <button x-show="currentTime > 0" @click="stopTimer()" 
                                            class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-3 px-4 rounded-lg transition-colors flex items-center justify-center">
                                        <i class="ti ti-player-stop mr-2"></i>
                                        Detener y Guardar
                                    </button>
                                </div>
                                
                                <div x-show="timerDescription" class="mt-4 p-3 bg-gray-50 rounded-lg">
                                    <p class="text-sm text-gray-600">Descripción actual:</p>
                                    <p class="font-medium text-gray-900" x-text="timerDescription"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Acciones rápidas -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 animate-slide-up" style="animation-delay: 0.3s">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                <i class="ti ti-zap text-orange-600 mr-2"></i>
                                Acciones Rápidas
                            </h3>
                        </div>
                        <div class="p-6 space-y-3">
                            <button @click="showTimeModal = true" class="w-full bg-green-50 hover:bg-green-100 text-green-700 font-medium py-3 px-4 rounded-lg transition-colors flex items-center">
                                <i class="ti ti-clock mr-2"></i>
                                Registrar Tiempo Manual
                            </button>
                            <button @click="showPdfModal = true" class="w-full bg-blue-50 hover:bg-blue-100 text-blue-700 font-medium py-3 px-4 rounded-lg transition-colors flex items-center">
                                <i class="ti ti-file-upload mr-2"></i>
                                Subir Reporte PDF
                            </button>
                            <button class="w-full bg-purple-50 hover:bg-purple-100 text-purple-700 font-medium py-3 px-4 rounded-lg transition-colors flex items-center">
                                <i class="ti ti-chart-bar mr-2"></i>
                                Ver Estadísticas
                            </button>
                        </div>
                    </div>

                    <!-- Resumen del día -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 animate-slide-up" style="animation-delay: 0.4s">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                <i class="ti ti-calendar-today text-blue-600 mr-2"></i>
                                Resumen de Hoy
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Horas trabajadas:</span>
                                    <span class="font-semibold text-gray-900"><?php echo number_format($stats['horas_hoy'], 1); ?>h</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Último reporte:</span>
                                    <span class="text-sm text-gray-900">
                                        <?php 
                                        if ($stats['ultimo_reporte']) {
                                            $fecha = new DateTime($stats['ultimo_reporte']);
                                            echo $fecha->format('d/m/Y');
                                        } else {
                                            echo 'Ninguno';
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal Registro de Tiempo -->
<div x-show="showTimeModal" x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="ti ti-clock mr-2 text-green-600"></i>
                    Registrar Tiempo de Trabajo
                </h3>
                <button @click="showTimeModal = false" 
                        class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="ti ti-x text-xl"></i>
                </button>
            </div>
            
            <!-- Mostrar errores de validación -->
            <div x-show="timeErrors.length > 0" class="alert alert-danger mb-4">
                <i class="ti ti-alert-circle"></i>
                <div>
                    <template x-for="error in timeErrors" :key="error">
                        <div x-text="error"></div>
                    </template>
                </div>
            </div>
            
            <form @submit.prevent="submitTimeRecord()" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Descripción del trabajo *</label>
                    <textarea x-model="timeRecord.descripcion" 
                              rows="3" 
                              placeholder="Describe las tareas realizadas..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                              :class="{ 'is-invalid': timeValidation.descripcion }"
                              maxlength="500"
                              required></textarea>
                    <span x-show="timeValidation.descripcion" class="error-message">
                        <i class="ti ti-alert-circle"></i>
                        <span x-text="timeValidation.descripcion"></span>
                    </span>
                    <small class="text-gray-500 text-xs mt-1">Máximo 500 caracteres</small>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fecha *</label>
                        <input type="date" 
                               x-model="timeRecord.fecha" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                               :class="{ 'is-invalid': timeValidation.fecha }"
                               :max="maxDate"
                               required>
                        <span x-show="timeValidation.fecha" class="error-message">
                            <i class="ti ti-alert-circle"></i>
                            <span x-text="timeValidation.fecha"></span>
                        </span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Horas *</label>
                        <input type="number" 
                               x-model="timeRecord.horas" 
                               step="0.25" 
                               min="0.25" 
                               max="24" 
                               placeholder="8.0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                               :class="{ 'is-invalid': timeValidation.horas }"
                               required>
                        <span x-show="timeValidation.horas" class="error-message">
                            <i class="ti ti-alert-circle"></i>
                            <span x-text="timeValidation.horas"></span>
                        </span>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Hora inicio</label>
                        <input type="time" 
                               x-model="timeRecord.hora_inicio"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Hora fin</label>
                        <input type="time" 
                               x-model="timeRecord.hora_fin"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" @click="closeTimeModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" :disabled="submitting"
                            class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50">
                        <i class="ti ti-check mr-1"></i>
                        <span x-text="submitting ? 'Guardando...' : 'Guardar Registro'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Subir PDF -->
<div x-show="showPdfModal" x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="ti ti-file-upload mr-2 text-blue-600"></i>
                    Subir Reporte PDF
                </h3>
                <button @click="closePdfModal()" 
                        class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="ti ti-x text-xl"></i>
                </button>
            </div>
            
            <!-- Mostrar errores de validación -->
            <div x-show="pdfErrors.length > 0" class="alert alert-danger mb-4">
                <i class="ti ti-alert-circle"></i>
                <div>
                    <template x-for="error in pdfErrors" :key="error">
                        <div x-text="error"></div>
                    </template>
                </div>
            </div>
            
            <form @submit.prevent="submitPdfReport()" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Título del reporte *</label>
                    <input type="text" 
                           x-model="pdfReport.titulo" 
                           placeholder="Reporte semanal, proyecto específico, etc."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           :class="{ 'is-invalid': pdfValidation.titulo }"
                           maxlength="255"
                           required>
                    <span x-show="pdfValidation.titulo" class="error-message">
                        <i class="ti ti-alert-circle"></i>
                        <span x-text="pdfValidation.titulo"></span>
                    </span>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                    <textarea x-model="pdfReport.descripcion" 
                              rows="3" 
                              placeholder="Breve descripción del contenido del reporte..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                              maxlength="1000"></textarea>
                    <small class="text-gray-500 text-xs mt-1">Opcional, máximo 1000 caracteres</small>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Archivo PDF *</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-gray-400 transition-colors"
                         :class="{ 'border-red-300 bg-red-50': pdfValidation.file }">
                        <div class="space-y-1 text-center">
                            <div x-show="!selectedFile">
                                <i class="ti ti-file-upload text-4xl text-gray-400"></i>
                                <div class="flex text-sm text-gray-600">
                                    <label for="pdf-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none">
                                        <span>Subir un archivo</span>
                                        <input id="pdf-upload" name="pdf-upload" type="file" class="sr-only" accept=".pdf" @change="handleFileSelect">
                                    </label>
                                    <p class="pl-1">o arrastra y suelta</p>
                                </div>
                                <p class="text-xs text-gray-500">Solo archivos PDF, máximo 10MB</p>
                            </div>
                            
                            <div x-show="selectedFile" class="text-sm">
                                <div class="flex items-center justify-center space-x-2">
                                    <i class="ti ti-file-text text-red-500"></i>
                                    <span x-text="selectedFile?.name" class="text-gray-900"></span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1" x-text="formatFileSize(selectedFile?.size)"></p>
                                <button type="button" @click="clearFile()" class="text-xs text-red-600 hover:text-red-800 mt-1">
                                    Quitar archivo
                                </button>
                            </div>
                        </div>
                    </div>
                    <span x-show="pdfValidation.file" class="error-message">
                        <i class="ti ti-alert-circle"></i>
                        <span x-text="pdfValidation.file"></span>
                    </span>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" @click="closePdfModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" :disabled="submitting || !selectedFile"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50">
                        <i class="ti ti-upload mr-1"></i>
                        <span x-text="submitting ? 'Subiendo...' : 'Subir Reporte'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Alpine.js Worker App con validaciones mejoradas y mejor manejo de errores
function workerApp() {
    return {
        // Estados generales
        loading: false,
        
        // Timer variables
        timerRunning: false,
        currentTime: 0,
        timerInterval: null,
        timerStartTime: null,
        timerDescription: '',
        
        // Modal states
        showTimeModal: false,
        showPdfModal: false,
        submitting: false,
        
        // Validación y errores
        timeErrors: [],
        pdfErrors: [],
        timeValidation: {},
        pdfValidation: {},
        
        // Form data con valores por defecto seguros
        timeRecord: {
            descripcion: '',
            fecha: '',
            horas: '',
            hora_inicio: '',
            hora_fin: ''
        },
        
        pdfReport: {
            titulo: '',
            descripcion: ''
        },
        
        selectedFile: null,
        maxDate: '',
        
        init() {
            // Configurar fecha máxima (hoy)
            const today = new Date();
            this.maxDate = today.toISOString().split('T')[0];
            this.timeRecord.fecha = this.maxDate;
            
            // Cargar estado del timer desde localStorage
            this.loadTimerState();
            
            // Configurar validación en tiempo real
            this.setupValidation();
            
            // Limpiar mensajes después de mostrarlos
            setTimeout(() => {
                this.clearUrlParams();
            }, 5000);
        },
        
        setupValidation() {
            // Watchers para validación en tiempo real
            this.$watch('timeRecord.descripcion', () => this.validateTimeField('descripcion'));
            this.$watch('timeRecord.horas', () => this.validateTimeField('horas'));
            this.$watch('timeRecord.fecha', () => this.validateTimeField('fecha'));
            this.$watch('pdfReport.titulo', () => this.validatePdfField('titulo'));
        },
        
        // Validaciones del formulario de tiempo
        validateTimeField(field) {
            switch(field) {
                case 'descripcion':
                    if (!this.timeRecord.descripcion.trim()) {
                        this.timeValidation.descripcion = 'La descripción es obligatoria';
                    } else if (this.timeRecord.descripcion.length > 500) {
                        this.timeValidation.descripcion = 'La descripción no puede exceder 500 caracteres';
                    } else {
                        delete this.timeValidation.descripcion;
                    }
                    break;
                    
                case 'horas':
                    const horas = parseFloat(this.timeRecord.horas);
                    if (!horas || horas <= 0) {
                        this.timeValidation.horas = 'Las horas son obligatorias';
                    } else if (horas > 24) {
                        this.timeValidation.horas = 'No se pueden registrar más de 24 horas';
                    } else if (horas < 0.25) {
                        this.timeValidation.horas = 'Mínimo 0.25 horas (15 minutos)';
                    } else {
                        delete this.timeValidation.horas;
                    }
                    break;
                    
                case 'fecha':
                    const fechaSeleccionada = new Date(this.timeRecord.fecha);
                    const hoy = new Date();
                    hoy.setHours(23, 59, 59, 999);
                    
                    if (!this.timeRecord.fecha) {
                        this.timeValidation.fecha = 'La fecha es obligatoria';
                    } else if (fechaSeleccionada > hoy) {
                        this.timeValidation.fecha = 'No se puede registrar tiempo en fechas futuras';
                    } else {
                        delete this.timeValidation.fecha;
                    }
                    break;
            }
        },
        
        validatePdfField(field) {
            switch(field) {
                case 'titulo':
                    if (!this.pdfReport.titulo.trim()) {
                        this.pdfValidation.titulo = 'El título es obligatorio';
                    } else if (this.pdfReport.titulo.length > 255) {
                        this.pdfValidation.titulo = 'El título no puede exceder 255 caracteres';
                    } else {
                        delete this.pdfValidation.titulo;
                    }
                    break;
            }
        },
        
        validateAllTimeFields() {
            this.timeErrors = [];
            this.timeValidation = {};
            
            this.validateTimeField('descripcion');
            this.validateTimeField('horas');
            this.validateTimeField('fecha');
            
            if (this.timeRecord.hora_inicio && this.timeRecord.hora_fin) {
                if (this.timeRecord.hora_inicio >= this.timeRecord.hora_fin) {
                    this.timeErrors.push('La hora de inicio debe ser anterior a la hora de fin');
                }
            }
            
            return Object.keys(this.timeValidation).length === 0 && this.timeErrors.length === 0;
        },
        
        validateAllPdfFields() {
            this.pdfErrors = [];
            this.pdfValidation = {};
            
            this.validatePdfField('titulo');
            
            if (!this.selectedFile) {
                this.pdfValidation.file = 'Debe seleccionar un archivo PDF';
            }
            
            return Object.keys(this.pdfValidation).length === 0 && this.pdfErrors.length === 0;
        },
        
        // Timer functions
        startTimer() {
            if (!this.timerRunning) {
                const description = prompt('Describe la tarea que vas a realizar:');
                if (description && description.trim()) {
                    this.timerDescription = this.sanitizeInput(description.trim());
                    this.timerStartTime = Date.now() - (this.currentTime * 1000);
                    this.timerRunning = true;
                    this.timerInterval = setInterval(() => {
                        this.currentTime = Math.floor((Date.now() - this.timerStartTime) / 1000);
                        this.saveTimerState();
                    }, 1000);
                    this.showToast('Timer iniciado', 'success');
                }
            }
        },
        
        pauseTimer() {
            if (this.timerRunning) {
                this.timerRunning = false;
                clearInterval(this.timerInterval);
                this.saveTimerState();
                this.showToast('Timer pausado', 'warning');
            }
        },
        
        async stopTimer() {
            if (this.currentTime > 0) {
                const hours = (this.currentTime / 3600).toFixed(2);
                
                if (confirm(`¿Guardar ${this.formatTime(this.currentTime)} de trabajo?\nDescripción: ${this.timerDescription}`)) {
                    try {
                        this.loading = true;
                        
                        const timeData = {
                            descripcion: this.timerDescription,
                            horas: hours,
                            fecha: new Date().toISOString().split('T')[0]
                        };
                        
                        await this.submitTimerRecord(timeData);
                        this.resetTimer();
                        this.showToast('Tiempo guardado exitosamente', 'success');
                        
                        setTimeout(() => window.location.reload(), 1500);
                        
                    } catch (error) {
                        this.showToast('Error al guardar tiempo: ' + error.message, 'error');
                    } finally {
                        this.loading = false;
                    }
                }
            }
        },
        
        resetTimer() {
            this.timerRunning = false;
            this.currentTime = 0;
            this.timerDescription = '';
            this.timerStartTime = null;
            clearInterval(this.timerInterval);
            this.clearTimerState();
        },
        
        formatTime(seconds) {
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;
            return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        },
        
        saveTimerState() {
            try {
                const state = {
                    running: this.timerRunning,
                    currentTime: this.currentTime,
                    startTime: this.timerStartTime,
                    description: this.timerDescription,
                    timestamp: Date.now()
                };
                localStorage.setItem('workerTimer', JSON.stringify(state));
            } catch (error) {
                console.error('Error saving timer state:', error);
            }
        },
        
        loadTimerState() {
            try {
                const saved = localStorage.getItem('workerTimer');
                if (saved) {
                    const state = JSON.parse(saved);
                    
                    if (state.timestamp && (Date.now() - state.timestamp > 24 * 60 * 60 * 1000)) {
                        this.clearTimerState();
                        return;
                    }
                    
                    this.currentTime = state.currentTime || 0;
                    this.timerDescription = state.description || '';
                    
                    if (state.running && state.startTime) {
                        this.timerStartTime = state.startTime;
                        this.timerRunning = true;
                        this.timerInterval = setInterval(() => {
                            this.currentTime = Math.floor((Date.now() - this.timerStartTime) / 1000);
                            this.saveTimerState();
                        }, 1000);
                    }
                }
            } catch (error) {
                console.error('Error loading timer state:', error);
                this.clearTimerState();
            }
        },
        
        clearTimerState() {
            try {
                localStorage.removeItem('workerTimer');
            } catch (error) {
                console.error('Error clearing timer state:', error);
            }
        },
        
        // Funciones de utilidad
        sanitizeInput(input) {
            return input.replace(/[<>]/g, '').substring(0, 500);
        },
        
        clearUrlParams() {
            if (window.history.replaceState) {
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        },
        
        // Funciones de modal
        closeTimeModal() {
            this.showTimeModal = false;
            this.resetTimeForm();
            this.timeErrors = [];
            this.timeValidation = {};
        },
        
        closePdfModal() {
            this.showPdfModal = false;
            this.resetPdfForm();
            this.pdfErrors = [];
            this.pdfValidation = {};
        },
        
        // Form submission functions con mejor manejo de errores
        async submitTimeRecord() {
            if (this.submitting) return;
            
            if (!this.validateAllTimeFields()) {
                this.showToast('Por favor corrige los errores en el formulario', 'error');
                return;
            }
            
            this.submitting = true;
            
            try {
                const formData = new FormData();
                formData.append('action', 'register_time');
                formData.append('descripcion', this.sanitizeInput(this.timeRecord.descripcion));
                formData.append('fecha', this.timeRecord.fecha);
                formData.append('horas', this.timeRecord.horas);
                formData.append('hora_inicio', this.timeRecord.hora_inicio);
                formData.append('hora_fin', this.timeRecord.hora_fin);
                
                const response = await fetch('test_api_worker.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                // Verificar que la respuesta sea válida
                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status} - ${response.statusText}`);
                }
                
                // Verificar content-type
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Respuesta no JSON:', text);
                    throw new Error('Respuesta inválida del servidor');
                }
                
                const data = await response.json();
                
                if (data.success) {
                    this.showToast(data.message || 'Tiempo registrado exitosamente', 'success');
                    this.closeTimeModal();
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    throw new Error(data.error || data.message || 'Error desconocido al registrar tiempo');
                }
                
            } catch (error) {
                console.error('Error submitTimeRecord:', error);
                
                let errorMessage = 'Error al registrar tiempo';
                if (error.message.includes('Failed to fetch')) {
                    errorMessage = 'Error de conexión. Verifica tu internet.';
                } else if (error.message.includes('JSON')) {
                    errorMessage = 'Error de comunicación con el servidor';
                } else {
                    errorMessage = error.message;
                }
                
                this.showToast(errorMessage, 'error');
            } finally {
                this.submitting = false;
            }
        },
        
        async submitTimerRecord(timeData) {
            try {
                const formData = new FormData();
                formData.append('action', 'register_time');
                formData.append('descripcion', this.sanitizeInput(timeData.descripcion));
                formData.append('fecha', timeData.fecha);
                formData.append('horas', timeData.horas);
                
                const response = await fetch('test_api_worker.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status}`);
                }
                
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Respuesta no JSON del timer:', text);
                    throw new Error('Respuesta inválida del servidor');
                }
                
                const data = await response.json();
                
                if (!data.success) {
                    throw new Error(data.error || 'Error al guardar tiempo del timer');
                }
                
                return data;
            } catch (error) {
                console.error('Error submitTimerRecord:', error);
                throw error;
            }
        },
        
        async submitPdfReport() {
            if (this.submitting || !this.selectedFile) return;
            
            if (!this.validateAllPdfFields()) {
                this.showToast('Por favor corrige los errores en el formulario', 'error');
                return;
            }
            
            this.submitting = true;
            
            try {
                const formData = new FormData();
                formData.append('action', 'upload_pdf');
                formData.append('titulo', this.sanitizeInput(this.pdfReport.titulo));
                formData.append('descripcion', this.sanitizeInput(this.pdfReport.descripcion));
                formData.append('pdf_file', this.selectedFile);
                
                const response = await fetch('test_api_worker.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status} - ${response.statusText}`);
                }
                
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Respuesta no JSON del PDF:', text);
                    throw new Error('Respuesta inválida del servidor');
                }
                
                const data = await response.json();
                
                if (data.success) {
                    this.showToast(data.message || 'Reporte subido exitosamente', 'success');
                    this.closePdfModal();
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    throw new Error(data.error || data.message || 'Error desconocido al subir reporte');
                }
                
            } catch (error) {
                console.error('Error submitPdfReport:', error);
                
                let errorMessage = 'Error al subir reporte';
                if (error.message.includes('Failed to fetch')) {
                    errorMessage = 'Error de conexión. Verifica tu internet.';
                } else if (error.message.includes('JSON')) {
                    errorMessage = 'Error de comunicación con el servidor';
                } else {
                    errorMessage = error.message;
                }
                
                this.showToast(errorMessage, 'error');
            } finally {
                this.submitting = false;
            }
        },
        
        // File handling con validación mejorada
        handleFileSelect(event) {
            const file = event.target.files[0];
            this.pdfValidation.file = '';
            
            if (!file) {
                return;
            }
            
            // Validar tipo de archivo
            if (file.type !== 'application/pdf') {
                this.pdfValidation.file = 'Solo se permiten archivos PDF';
                event.target.value = '';
                return;
            }
            
            // Validar tamaño (10MB máximo)
            const maxSize = 10 * 1024 * 1024;
            if (file.size > maxSize) {
                this.pdfValidation.file = 'El archivo es demasiado grande. Máximo 10MB';
                event.target.value = '';
                return;
            }
            
            // Validar nombre del archivo
            if (file.name.length > 255) {
                this.pdfValidation.file = 'El nombre del archivo es demasiado largo';
                event.target.value = '';
                return;
            }
            
            this.selectedFile = file;
            delete this.pdfValidation.file;
        },
        
        clearFile() {
            this.selectedFile = null;
            const fileInput = document.getElementById('pdf-upload');
            if (fileInput) {
                fileInput.value = '';
            }
            delete this.pdfValidation.file;
        },
        
        formatFileSize(bytes) {
            if (!bytes || bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        },
        
        // Form reset functions con limpieza completa
        resetTimeForm() {
            this.timeRecord = {
                descripcion: '',
                fecha: this.maxDate,
                horas: '',
                hora_inicio: '',
                hora_fin: ''
            };
            this.timeErrors = [];
            this.timeValidation = {};
        },
        
        resetPdfForm() {
            this.pdfReport = {
                titulo: '',
                descripcion: ''
            };
            this.clearFile();
            this.pdfErrors = [];
            this.pdfValidation = {};
        },
        
        // Toast notification mejorado
        showToast(message, type = 'info', duration = 5000) {
            const toast = document.createElement('div');
            const toastId = 'toast-' + Date.now();
            
            const colors = {
                success: 'bg-green-500',
                error: 'bg-red-500',
                warning: 'bg-yellow-500',
                info: 'bg-blue-500'
            };
            
            const icons = {
                success: 'ti-check',
                error: 'ti-alert-circle',
                warning: 'ti-alert-triangle',
                info: 'ti-info-circle'
            };
            
            toast.id = toastId;
            toast.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg text-white ${colors[type]} transform transition-all duration-300 translate-x-full max-w-sm`;
            toast.innerHTML = `
                <div class="flex items-center">
                    <i class="ti ${icons[type]} mr-2 text-lg flex-shrink-0"></i>
                    <span class="flex-1 text-sm">${this.sanitizeInput(message)}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200 focus:outline-none flex-shrink-0">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
            `;
            
            document.body.appendChild(toast);
            
            // Mostrar toast con animación
            setTimeout(() => toast.classList.remove('translate-x-full'), 100);
            
            // Auto-ocultar después del tiempo especificado
            setTimeout(() => {
                if (document.getElementById(toastId)) {
                    toast.classList.add('translate-x-full');
                    setTimeout(() => {
                        if (toast.parentNode) {
                            toast.remove();
                        }
                    }, 300);
                }
            }, duration);
            
            // Limitar número de toasts simultáneos
            const existingToasts = document.querySelectorAll('[id^="toast-"]');
            if (existingToasts.length > 3) {
                existingToasts[0].remove();
            }
        }
    }
}
</script>

</body>
</html>
                                                