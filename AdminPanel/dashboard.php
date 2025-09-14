<?php
session_start();

// Mostrar errores para depurar
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$GLOBALS['allowed_config_access'] = true;
require __DIR__ . '/config/config.php';

// RedirecciÃ³n si no estÃ¡ logueado
if (empty($_SESSION['user']['logged_in'])) {
    header("Location: login.php");
    exit();
}

// Verificar inactividad (30 min)
$inactivity = 1800;
if (isset($_SESSION['user']['last_activity']) && 
    (time() - $_SESSION['user']['last_activity'] > $inactivity)) {
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=1");
    exit();
}
$_SESSION['user']['last_activity'] = time();

// ConexiÃ³n a la base de datos
require_once 'includes/db.php';

// InformaciÃ³n del usuario logueado
$current_user = $_SESSION['user'];
$username = $current_user['username'] ?? 'Usuario';
$user_role = $current_user['role'] ?? 'admin';
$user_id = $current_user['id'] ?? 0;

// Obtener visitantes pendientes
$visitantes_pendientes = [];
$error_message = '';

try {
    $query_visitantes = "SELECT id, nombre, email, fecha_registro FROM visitantes WHERE estado_aprobacion = 'pendiente' ORDER BY fecha_registro ASC";
    
    $result = $mysqli->query($query_visitantes);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $visitantes_pendientes[] = [
                'id' => $row['id'],
                'nombre' => $row['nombre'],
                'email' => $row['email'],
                'creado_en' => $row['fecha_registro']
            ];
        }
    } else {
        $error_message = "Error en consulta: " . $mysqli->error;
    }
} catch (Exception $e) {
    error_log("Error al obtener visitantes pendientes: " . $e->getMessage());
    $error_message = "Error al obtener visitantes pendientes: " . $e->getMessage();
}

// EstadÃ­sticas
$stats = [
    'socios_total' => 0,
    'viviendas_ocupadas' => 0,
    'viviendas_total' => 0,
    'proyectos_total' => 0,
    'ingresos_mes' => 0,
    'visitantes_pendientes' => count($visitantes_pendientes)
];

// Contar socios aprobados
try {
    $query_socios = "SELECT COUNT(*) AS total FROM visitantes WHERE estado_aprobacion = 'aprobado'";
    $result = $mysqli->query($query_socios);
    if ($result) {
        $row = $result->fetch_assoc();
        $stats['socios_total'] = (int)$row['total'];
    }
} catch (Exception $e) {
    error_log("Error contando socios: " . $e->getMessage());
}

// Verificar otras tablas y obtener datos
$tables_to_check = ['viviendas', 'proyectos'];
foreach ($tables_to_check as $table) {
    try {
        $tables_result = $mysqli->query("SHOW TABLES LIKE '$table'");
        if ($tables_result && $tables_result->num_rows > 0) {
            switch ($table) {
                case 'viviendas':
                    $query = "SELECT COUNT(*) AS ocupadas FROM viviendas WHERE estado = 'ocupada'";
                    $result = $mysqli->query($query);
                    if ($result) {
                        $stats['viviendas_ocupadas'] = (int)$result->fetch_assoc()['ocupadas'];
                    }
                    
                    $query = "SELECT COUNT(*) AS total FROM viviendas";
                    $result = $mysqli->query($query);
                    if ($result) {
                        $stats['viviendas_total'] = (int)$result->fetch_assoc()['total'];
                    }
                    break;
                    
                case 'proyectos':
                    $query = "SELECT COUNT(*) AS total FROM proyectos WHERE estado = 'en curso'";
                    $result = $mysqli->query($query);
                    if ($result) {
                        $stats['proyectos_total'] = (int)$result->fetch_assoc()['total'];
                    }
                    break;
            }
        }
    } catch (Exception $e) {
        error_log("Error verificando tabla $table: " . $e->getMessage());
    }
}

// Obtener estadísticas de trabajadores
$worker_stats = [
    'total_trabajadores' => 0,
    'horas_pendientes' => 0,
    'reportes_pendientes' => 0,
    'total_horas_mes' => 0
];

try {
    // Contar trabajadores activos
    $query = "SELECT COUNT(*) as total FROM usuarios WHERE rol = 'trabajador' AND activo = 1";
    $result = $mysqli->query($query);
    if ($result) {
        $row = $result->fetch_assoc();
        $worker_stats['total_trabajadores'] = (int)$row['total'];
    }

    // Horas pendientes de aprobación
    $query = "SELECT COUNT(*) as total FROM tiempo_trabajado WHERE estado = 'pendiente'";
    $result = $mysqli->query($query);
    if ($result) {
        $row = $result->fetch_assoc();
        $worker_stats['horas_pendientes'] = (int)$row['total'];
    }

    // Reportes pendientes de revisión
    $query = "SELECT COUNT(*) as total FROM reportes_pdf WHERE estado = 'pendiente'";
    $result = $mysqli->query($query);
    if ($result) {
        $row = $result->fetch_assoc();
        $worker_stats['reportes_pendientes'] = (int)$row['total'];
    }

    // Total horas este mes
    $query = "SELECT COALESCE(SUM(horas), 0) as total FROM tiempo_trabajado 
              WHERE MONTH(fecha_inicio) = MONTH(CURDATE()) AND YEAR(fecha_inicio) = YEAR(CURDATE())
              AND estado = 'aprobado'";
    $result = $mysqli->query($query);
    if ($result) {
        $row = $result->fetch_assoc();
        $worker_stats['total_horas_mes'] = floatval($row['total']);
    }
} catch (Exception $e) {
    error_log("Error al obtener estadísticas de trabajadores: " . $e->getMessage());
}

// Obtener registros de tiempo pendientes
$tiempo_pendiente = [];
try {
    $query = "SELECT tt.id, tt.descripcion, tt.horas, tt.fecha_inicio, tt.estado,
                     u.name as trabajador_nombre, u.id as trabajador_id
              FROM tiempo_trabajado tt
              JOIN usuarios u ON tt.trabajador_id = u.id
              WHERE tt.estado = 'pendiente'
              ORDER BY tt.fecha_inicio DESC
              LIMIT 10";
    
    $result = $mysqli->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $tiempo_pendiente[] = [
                'id' => $row['id'],
                'descripcion' => $row['descripcion'],
                'horas' => floatval($row['horas']),
                'fecha_inicio' => $row['fecha_inicio'],
                'trabajador_nombre' => $row['trabajador_nombre'],
                'trabajador_id' => $row['trabajador_id'],
                'estado' => $row['estado']
            ];
        }
    }
} catch (Exception $e) {
    error_log("Error al obtener tiempo pendiente: " . $e->getMessage());
}

// Obtener reportes PDF pendientes
$reportes_pendientes = [];
try {
    $query = "SELECT rp.id, rp.titulo, rp.descripcion, rp.fecha_envio, rp.nombre_archivo,
                     u.name as trabajador_nombre, u.id as trabajador_id
              FROM reportes_pdf rp
              JOIN usuarios u ON rp.trabajador_id = u.id
              WHERE rp.estado = 'pendiente'
              ORDER BY rp.fecha_envio DESC
              LIMIT 10";
    
    $result = $mysqli->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $reportes_pendientes[] = [
                'id' => $row['id'],
                'titulo' => $row['titulo'],
                'descripcion' => $row['descripcion'],
                'fecha_envio' => $row['fecha_envio'],
                'nombre_archivo' => $row['nombre_archivo'],
                'trabajador_nombre' => $row['trabajador_nombre'],
                'trabajador_id' => $row['trabajador_id']
            ];
        }
    }
} catch (Exception $e) {
    error_log("Error al obtener reportes pendientes: " . $e->getMessage());
}


?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Cooperativa de Vivienda</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Tabler Icons -->
    <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" rel="stylesheet">
    
    <!-- Chart.js para grÃ¡ficos -->
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
        
        .gradient-pink {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }
        
        /* Efectos hover mejorados */
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        
        /* Loading skeleton */
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }
        
        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
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

        .gradient-teal {
            background: linear-gradient(135deg, #14b8a6 0%, #06b6d4 100%);
        }

        .gradient-yellow {
            background: linear-gradient(135deg, #f59e0b 0%, #eab308 100%);
        }
    </style>
</head>
<body class="bg-gray-50" x-data="dashboardApp()" x-init="init()">

<!-- Layout Principal -->
<div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: false }">
    
    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 z-30 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0" 
           :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }">
        
        <!-- Header del Sidebar -->
        <div class="flex items-center justify-between px-6 py-4 bg-indigo-600 text-white">
            <h1 class="text-lg font-bold">Cooperativa</h1>
            <button class="lg:hidden" @click="sidebarOpen = false">
                <i class="ti ti-x text-xl"></i>
            </button>
        </div>
        
        <!-- NavegaciÃ³n -->
        <nav class="flex-1 px-4 py-6 space-y-2 custom-scroll overflow-y-auto h-full">
            <a href="dashboard.php" class="flex items-center px-3 py-2 rounded-lg bg-indigo-100 text-indigo-700 font-medium">
                <i class="ti ti-dashboard mr-3 text-lg"></i>
                Dashboard
            </a>
            
            <!-- Socios -->
            <div x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center w-full px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors">
                    <i class="ti ti-users mr-3 text-lg"></i>
                    <span>Socios</span>
                    <i class="ti ti-chevron-down ml-auto transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                </button>
                <div x-show="open" x-collapse class="ml-6 mt-1 space-y-1">
                    <a href="sections/socios/lista.php" class="block px-3 py-2 text-sm text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg">Lista de Socios</a>
                    <a href="sections/socios/nuevo.php" class="block px-3 py-2 text-sm text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg">Nuevo Socio</a>
                </div>
            </div>
            
            <!-- Viviendas -->
            <div x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center w-full px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors">
                    <i class="ti ti-building-community mr-3 text-lg"></i>
                    <span>Viviendas</span>
                    <i class="ti ti-chevron-down ml-auto transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                </button>
                <div x-show="open" x-collapse class="ml-6 mt-1 space-y-1">
                    <a href="sections/viviendas/inventario.php" class="block px-3 py-2 text-sm text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg">Inventario</a>
                    <a href="sections/viviendas/asignaciones.php" class="block px-3 py-2 text-sm text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg">Asignaciones</a>
                </div>
            </div>
            
            <!-- Finanzas -->
            <div x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center w-full px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors">
                    <i class="ti ti-currency-dollar mr-3 text-lg"></i>
                    <span>Finanzas</span>
                    <i class="ti ti-chevron-down ml-auto transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                </button>
                <div x-show="open" x-collapse class="ml-6 mt-1 space-y-1">
                    <a href="sections/finanzas/aportes.php" class="block px-3 py-2 text-sm text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg">Aportes</a>
                    <a href="sections/finanzas/pagos.php" class="block px-3 py-2 text-sm text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg">Pagos</a>
                </div>
            </div>
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
                        <h2 class="text-xl font-semibold text-gray-800">Dashboard</h2>
                    </div>
                    
                    <!-- Header Right -->
                    <div class="flex items-center space-x-4">
                        <!-- Notificaciones -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open; loadNotifications()" 
                                    class="relative p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                                <i class="ti ti-bell text-xl"></i>
                                <span x-show="unreadCount > 0" 
                                    x-text="unreadCount" 
                                    class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full px-1.5 py-0.5 min-w-5 text-center animate-pulse-soft">
                                </span>
                            </button>
                            
                            <!-- Panel de notificaciones mejorado -->
                            <div x-show="open" 
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 transform scale-95"
                                x-transition:enter-end="opacity-1 transform scale-100"
                                @click.away="open = false"
                                class="absolute right-0 mt-2 w-96 bg-white rounded-xl shadow-xl border border-gray-200 z-50 max-h-96 overflow-hidden">
                                
                                <!-- Header del panel -->
                                <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-lg font-semibold text-gray-800">Notificaciones</h3>
                                        <div class="flex items-center space-x-2">
                                            <!-- Contador -->
                                            <span x-show="unreadCount > 0" 
                                                class="bg-red-100 text-red-800 text-xs font-medium px-2 py-1 rounded-full"
                                                x-text="unreadCount + ' no leÃ­das'">
                                            </span>
                                            
                                            <!-- BotÃ³n marcar todas como leÃ­das -->
                                            <button x-show="unreadCount > 0" 
                                                    @click="markAllAsRead()"
                                                    class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                                                Marcar todas
                                            </button>
                                            
                                            <!-- BotÃ³n actualizar -->
                                            <button @click="loadNotifications()" 
                                                    :disabled="loading"
                                                    class="p-1 text-gray-400 hover:text-gray-600 transition-colors">
                                                <i class="ti ti-refresh text-sm" :class="{ 'animate-spin': loading }"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Lista de notificaciones -->
                                <div class="max-h-80 overflow-y-auto custom-scroll">
                                    <!-- Loading state -->
                                    <div x-show="loading && notifications.length === 0" class="px-4 py-8 text-center">
                                        <div class="animate-pulse space-y-3">
                                            <div class="h-4 bg-gray-200 rounded w-3/4 mx-auto"></div>
                                            <div class="h-3 bg-gray-200 rounded w-1/2 mx-auto"></div>
                                            <div class="h-3 bg-gray-200 rounded w-5/6 mx-auto"></div>
                                        </div>
                                    </div>
                                    
                                    <!-- Notificaciones -->
                                    <template x-for="notification in notifications" :key="notification.id">
                                        <div class="px-4 py-3 border-b border-gray-100 hover:bg-gray-50 transition-colors relative group"
                                            :class="{ 'bg-blue-50 border-l-4 border-l-blue-500': !notification.leida }"
                                            @click="markAsRead(notification.id)">
                                            <div class="flex items-start space-x-3">
                                                <div class="flex-shrink-0 mt-1">
                                                    <i :class="{
                                                        'ti ti-info-circle text-blue-500': notification.tipo === 'info',
                                                        'ti ti-check text-green-500': notification.tipo === 'success',
                                                        'ti ti-alert-triangle text-yellow-500': notification.tipo === 'warning',
                                                        'ti ti-alert-circle text-red-500': notification.tipo === 'error'
                                                    }" class="text-lg"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-start justify-between">
                                                        <p class="text-sm font-medium text-gray-900" 
                                                        :class="{ 'font-semibold': !notification.leida }"
                                                        x-text="notification.titulo"></p>
                                                        
                                                        <!-- Indicador no leÃ­da -->
                                                        <div x-show="!notification.leida" 
                                                            class="w-2 h-2 bg-blue-500 rounded-full flex-shrink-0 ml-2 mt-1"></div>
                                                    </div>
                                                    <p class="text-sm text-gray-600 mt-1" x-text="notification.mensaje"></p>
                                                    <div class="flex items-center justify-between mt-2">
                                                        <p class="text-xs text-gray-400" x-text="formatDate(notification.created_at)"></p>
                                                        
                                                        <!-- Acciones (solo visible al hacer hover) -->
                                                        <div class="opacity-0 group-hover:opacity-100 transition-opacity flex items-center space-x-1">
                                                            <button x-show="!notification.leida" 
                                                                    @click.stop="markAsRead(notification.id)"
                                                                    class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                                                                Marcar leÃ­da
                                                            </button>
                                                            <button @click.stop="deleteNotification(notification.id)"
                                                                    class="text-xs text-red-600 hover:text-red-800 font-medium ml-2">
                                                                Eliminar
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                    
                                    <!-- Estado vacÃ­o -->
                                    <div x-show="!loading && notifications.length === 0" 
                                        class="px-4 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                                <i class="ti ti-bell-off text-3xl text-gray-300"></i>
                                            </div>
                                            <p class="text-lg font-medium text-gray-600">No hay notificaciones</p>
                                            <p class="text-sm text-gray-400 mt-1">Las nuevas notificaciones aparecerÃ¡n aquÃ­</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Footer del panel -->
                                <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
                                    <div class="flex items-center justify-between">
                                        <button @click="showCreateNotification = true; open = false" 
                                                class="text-sm text-indigo-600 hover:text-indigo-800 font-medium flex items-center">
                                            <i class="ti ti-plus mr-1"></i>
                                            Crear notificaciÃ³n
                                        </button>
                                        <a href="notifications.php" 
                                        class="text-sm text-gray-600 hover:text-gray-800 font-medium">
                                            Ver todas â†’
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Usuario -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100 transition-colors">
                                <div class="w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                    <?php echo strtoupper(substr($username, 0, 2)); ?>
                                </div>
                                <span class="hidden sm:block text-sm font-medium text-gray-700"><?php echo htmlspecialchars($username); ?></span>
                                <i class="ti ti-chevron-down text-sm text-gray-500"></i>
                            </button>
                            
                            <div x-show="open" @click.away="open = false" x-transition
                                class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                                <div class="px-4 py-2 border-b border-gray-200">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($username); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo htmlspecialchars($user_role); ?></div>
                                </div>
                                <a href="profile.php" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                                    <i class="ti ti-user mr-2"></i> Mi Perfil
                                </a>
                                <a href="settings.php" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                                    <i class="ti ti-settings mr-2"></i> ConfiguraciÃ³n
                                </a>
                                <div class="border-t border-gray-200 mt-1 pt-1">
                                    <a href="logout.php" class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                        <i class="ti ti-logout mr-2"></i> Cerrar SesiÃ³n
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto p-6 custom-scroll">
            
            <!-- Mensajes de estado -->
            <?php if (!empty($error_message)): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg animate-slide-up">
                    <div class="flex items-center">
                        <i class="ti ti-alert-circle text-red-500 mr-2"></i>
                        <span class="text-red-700"><?php echo htmlspecialchars($error_message); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Tarjetas de estadÃ­sticas -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6 mb-8">
                
                <!-- Socios Activos -->
                <div class="card-hover gradient-blue rounded-xl p-6 text-white animate-fade-scale">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium opacity-90">Socios Activos</p>
                            <p class="text-2xl font-bold mt-1"><?php echo $stats['socios_total']; ?></p>
                            <p class="text-xs opacity-75 mt-1">Total registrados</p>
                        </div>
                        <div class="bg-white bg-opacity-20 p-3 rounded-full">
                            <i class="ti ti-users text-2xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Viviendas -->
                <div class="card-hover gradient-green rounded-xl p-6 text-white animate-fade-scale" style="animation-delay: 0.1s">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium opacity-90">Viviendas</p>
                            <p class="text-2xl font-bold mt-1"><?php echo $stats['viviendas_ocupadas']; ?>/<?php echo $stats['viviendas_total']; ?></p>
                            <p class="text-xs opacity-75 mt-1">Ocupadas/Total</p>
                        </div>
                        <div class="bg-white bg-opacity-20 p-3 rounded-full">
                            <i class="ti ti-building-community text-2xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Proyectos -->
                <div class="card-hover gradient-purple rounded-xl p-6 text-white animate-fade-scale" style="animation-delay: 0.2s">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium opacity-90">Proyectos</p>
                            <p class="text-2xl font-bold mt-1"><?php echo $stats['proyectos_total']; ?></p>
                            <p class="text-xs opacity-75 mt-1">En curso</p>
                        </div>
                        <div class="bg-white bg-opacity-20 p-3 rounded-full">
                            <i class="ti ti-layout-grid text-2xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Ingresos -->
                <div class="card-hover gradient-orange rounded-xl p-6 text-white animate-fade-scale" style="animation-delay: 0.3s">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium opacity-90">Ingresos</p>
                            <p class="text-2xl font-bold mt-1">$<?php echo number_format($stats['ingresos_mes'], 0); ?></p>
                            <p class="text-xs opacity-75 mt-1">Este mes</p>
                        </div>
                        <div class="bg-white bg-opacity-20 p-3 rounded-full">
                            <i class="ti ti-currency-dollar text-2xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Pendientes -->
                <div class="card-hover gradient-pink rounded-xl p-6 text-white animate-fade-scale" style="animation-delay: 0.4s">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium opacity-90">Pendientes</p>
                            <p class="text-2xl font-bold mt-1" id="pending-count"><?php echo $stats['visitantes_pendientes']; ?></p>
                            <p class="text-xs opacity-75 mt-1">Por aprobar</p>
                        </div>
                        <div class="bg-white bg-opacity-20 p-3 rounded-full">
                            <i class="ti ti-clock text-2xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Trabajadores Activos -->
<div class="card-hover gradient-teal rounded-xl p-6 text-white animate-fade-scale" style="animation-delay: 0.5s">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm font-medium opacity-90">Trabajadores</p>
            <p class="text-2xl font-bold mt-1"><?php echo $worker_stats['total_trabajadores']; ?></p>
            <p class="text-xs opacity-75 mt-1">Activos</p>
        </div>
        <div class="bg-white bg-opacity-20 p-3 rounded-full">
            <i class="ti ti-users-group text-2xl"></i>
        </div>
    </div>
</div>

<!-- Horas Pendientes -->
<div class="card-hover gradient-yellow rounded-xl p-6 text-white animate-fade-scale" style="animation-delay: 0.6s">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm font-medium opacity-90">Horas</p>
            <p class="text-2xl font-bold mt-1"><?php echo $worker_stats['horas_pendientes']; ?></p>
            <p class="text-xs opacity-75 mt-1">Por aprobar</p>
        </div>
        <div class="bg-white bg-opacity-20 p-3 rounded-full">
            <i class="ti ti-clock-hour-4 text-2xl"></i>
        </div>
    </div>
</div>

<!-- SECCIÓN DE TIEMPO TRABAJADO PENDIENTE -->
<div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200 animate-slide-up" style="animation-delay: 0.6s">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                <i class="ti ti-clock-hour-4 text-orange-600 mr-2"></i>
                Horas de Trabajo Pendientes
            </h3>
            <div class="flex items-center space-x-2">
                <span class="bg-orange-100 text-orange-800 text-sm font-medium px-2.5 py-0.5 rounded-full">
                    <?php echo count($tiempo_pendiente); ?> pendientes
                </span>
                <button onclick="approveAllTime()" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-lg text-sm font-medium transition-colors">
                    <i class="ti ti-check-all mr-1"></i> Aprobar Todas
                </button>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Trabajador
                    </th>
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
                        Acciones
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (!empty($tiempo_pendiente)): ?>
                    <?php foreach ($tiempo_pendiente as $tiempo): ?>
                        <tr id="time-row-<?php echo $tiempo['id']; ?>" class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-gradient-to-r from-orange-500 to-red-600 rounded-full flex items-center justify-center text-white font-semibold mr-3">
                                        <?php echo strtoupper(substr($tiempo['trabajador_nombre'], 0, 2)); ?>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($tiempo['trabajador_nombre']); ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            ID: <?php echo $tiempo['trabajador_id']; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 max-w-xs">
                                    <?php echo htmlspecialchars(substr($tiempo['descripcion'], 0, 100)) . (strlen($tiempo['descripcion']) > 100 ? '...' : ''); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">
                                    <?php echo number_format($tiempo['horas'], 2); ?>h
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php 
                                if (!empty($tiempo['fecha_inicio'])) {
                                    $fecha = new DateTime($tiempo['fecha_inicio']);
                                    echo $fecha->format('d/m/Y H:i');
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button onclick="approveTime(<?php echo $tiempo['id']; ?>)" 
                                            class="bg-green-500 hover:bg-green-600 text-white py-1.5 px-3 rounded-lg text-xs transition-colors flex items-center">
                                        <i class="ti ti-check mr-1"></i> Aprobar
                                    </button>
                                    <button onclick="rejectTime(<?php echo $tiempo['id']; ?>)" 
                                            class="bg-red-500 hover:bg-red-600 text-white py-1.5 px-3 rounded-lg text-xs transition-colors flex items-center">
                                        <i class="ti ti-x mr-1"></i> Rechazar
                                    </button>
                                    <button onclick="viewTimeDetails(<?php echo $tiempo['id']; ?>)" 
                                            class="bg-blue-500 hover:bg-blue-600 text-white py-1.5 px-3 rounded-lg text-xs transition-colors flex items-center">
                                        <i class="ti ti-eye mr-1"></i> Ver
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                    <i class="ti ti-clock-check text-3xl text-gray-300"></i>
                                </div>
                                <p class="text-lg font-medium text-gray-600">No hay horas pendientes</p>
                                <p class="text-sm text-gray-400 mt-1">Todas las horas han sido revisadas</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- SECCIÓN DE REPORTES PDF PENDIENTES -->
<div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200 animate-slide-up" style="animation-delay: 0.7s">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                <i class="ti ti-file-text text-purple-600 mr-2"></i>
                Reportes PDF Pendientes
            </h3>
            <span class="bg-purple-100 text-purple-800 text-sm font-medium px-2.5 py-0.5 rounded-full">
                <?php echo count($reportes_pendientes); ?> pendientes
            </span>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Trabajador
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Título
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Archivo
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Fecha Envío
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Acciones
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (!empty($reportes_pendientes)): ?>
                    <?php foreach ($reportes_pendientes as $reporte): ?>
                        <tr id="report-row-<?php echo $reporte['id']; ?>" class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-blue-600 rounded-full flex items-center justify-center text-white font-semibold mr-3">
                                        <?php echo strtoupper(substr($reporte['trabajador_nombre'], 0, 2)); ?>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($reporte['trabajador_nombre']); ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            ID: <?php echo $reporte['trabajador_id']; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($reporte['titulo']); ?>
                                </div>
                                <?php if (!empty($reporte['descripcion'])): ?>
                                    <div class="text-sm text-gray-500 mt-1">
                                        <?php echo htmlspecialchars(substr($reporte['descripcion'], 0, 60)) . (strlen($reporte['descripcion']) > 60 ? '...' : ''); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center text-sm text-gray-900">
                                    <i class="ti ti-file-type-pdf text-red-500 mr-2"></i>
                                    <?php echo htmlspecialchars(substr($reporte['nombre_archivo'], 0, 20)) . (strlen($reporte['nombre_archivo']) > 20 ? '...' : ''); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php 
                                if (!empty($reporte['fecha_envio'])) {
                                    $fecha = new DateTime($reporte['fecha_envio']);
                                    echo $fecha->format('d/m/Y H:i');
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button onclick="downloadReport(<?php echo $reporte['id']; ?>)" 
                                            class="bg-blue-500 hover:bg-blue-600 text-white py-1.5 px-3 rounded-lg text-xs transition-colors flex items-center">
                                        <i class="ti ti-download mr-1"></i> Descargar
                                    </button>
                                    <button onclick="approveReport(<?php echo $reporte['id']; ?>)" 
                                            class="bg-green-500 hover:bg-green-600 text-white py-1.5 px-3 rounded-lg text-xs transition-colors flex items-center">
                                        <i class="ti ti-check mr-1"></i> Aprobar
                                    </button>
                                    <button onclick="rejectReport(<?php echo $reporte['id']; ?>)" 
                                            class="bg-red-500 hover:bg-red-600 text-white py-1.5 px-3 rounded-lg text-xs transition-colors flex items-center">
                                        <i class="ti ti-x mr-1"></i> Rechazar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                    <i class="ti ti-file-check text-3xl text-gray-300"></i>
                                </div>
                                <p class="text-lg font-medium text-gray-600">No hay reportes pendientes</p>
                                <p class="text-sm text-gray-400 mt-1">Todos los reportes han sido revisados</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


            </div>

            <!-- Grid de contenido principal -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                
                <!-- Visitantes Pendientes -->
                <div class="xl:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 animate-slide-up">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                <i class="ti ti-user-clock text-indigo-600 mr-2"></i>
                                Visitantes Pendientes
                            </h3>
                            <span class="bg-red-100 text-red-800 text-sm font-medium px-2.5 py-0.5 rounded-full">
                                <?php echo count($visitantes_pendientes); ?> pendientes
                            </span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Visitante
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Email
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Registro
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (!empty($visitantes_pendientes)): ?>
                                    <?php foreach ($visitantes_pendientes as $visitante): ?>
                                        <tr id="row-<?php echo $visitante['id']; ?>" class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="w-10 h-10 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold mr-3">
                                                        <?php echo strtoupper(substr($visitante['nombre'], 0, 2)); ?>
                                                    </div>
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900">
                                                            <?php echo htmlspecialchars($visitante['nombre']); ?>
                                                        </div>
                                                        <div class="text-sm text-gray-500">
                                                            ID: <?php echo $visitante['id']; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($visitante['email']); ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php 
                                                if (!empty($visitante['creado_en'])) {
                                                    $fecha = new DateTime($visitante['creado_en']);
                                                    echo $fecha->format('d/m/Y H:i');
                                                } else {
                                                    echo 'N/A';
                                                }
                                                ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    <button onclick="approveVisitante(<?php echo $visitante['id']; ?>)" 
                                                            class="bg-green-500 hover:bg-green-600 text-white py-1.5 px-3 rounded-lg text-xs transition-colors flex items-center">
                                                        <i class="ti ti-check mr-1"></i> Aprobar
                                                    </button>
                                                    <button onclick="rejectVisitante(<?php echo $visitante['id']; ?>)" 
                                                            class="bg-red-500 hover:bg-red-600 text-white py-1.5 px-3 rounded-lg text-xs transition-colors flex items-center">
                                                        <i class="ti ti-x mr-1"></i> Rechazar
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                            <div class="flex flex-col items-center">
                                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                                    <i class="ti ti-users text-3xl text-gray-300"></i>
                                                </div>
                                                <p class="text-lg font-medium text-gray-600">No hay visitantes pendientes</p>
                                                <p class="text-sm text-gray-400 mt-1">Los nuevos registros aparecerÃ¡n aquÃ­</p>
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
                    
                    <!-- Actividad reciente -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 animate-slide-up" style="animation-delay: 0.2s">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                <i class="ti ti-clock text-green-600 mr-2"></i>
                                Actividad Reciente
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div class="flex items-start space-x-3">
                                    <div class="w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Nuevo socio aprobado</p>
                                        <p class="text-xs text-gray-500">Hace 2 horas</p>
                                    </div>
                                </div>
                                <div class="flex items-start space-x-3">
                                    <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Pago registrado</p>
                                        <p class="text-xs text-gray-500">Hace 4 horas</p>
                                    </div>
                                </div>
                                <div class="flex items-start space-x-3">
                                    <div class="w-2 h-2 bg-yellow-500 rounded-full mt-2"></div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Vivienda asignada</p>
                                        <p class="text-xs text-gray-500">Ayer</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- GrÃ¡fico rÃ¡pido -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 animate-slide-up" style="animation-delay: 0.3s">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                <i class="ti ti-chart-pie text-purple-600 mr-2"></i>
                                Resumen Mensual
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="relative h-48">
                                <canvas id="monthlyChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Acciones rÃ¡pidas -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 animate-slide-up" style="animation-delay: 0.4s">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                <i class="ti ti-zap text-orange-600 mr-2"></i>
                                Acciones RÃ¡pidas
                            </h3>
                        </div>
                        <div class="p-6 space-y-3">
                            <button class="w-full bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-medium py-3 px-4 rounded-lg transition-colors flex items-center">
                                <i class="ti ti-user-plus mr-2"></i>
                                Nuevo Socio
                            </button>
                            <button class="w-full bg-green-50 hover:bg-green-100 text-green-700 font-medium py-3 px-4 rounded-lg transition-colors flex items-center">
                                <i class="ti ti-currency-dollar mr-2"></i>
                                Registrar Pago
                            </button>
                            <button class="w-full bg-purple-50 hover:bg-purple-100 text-purple-700 font-medium py-3 px-4 rounded-lg transition-colors flex items-center">
                                <i class="ti ti-report mr-2"></i>
                                Generar Reporte
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de socios recientes -->
            <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200 animate-slide-up" style="animation-delay: 0.5s">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i class="ti ti-user-check text-blue-600 mr-2"></i>
                        Ãšltimos Socios Aprobados
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Socio</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha AprobaciÃ³n</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php
                            try {
                                $query_socios_aprobados = "SELECT nombre, email, COALESCE(fecha_aprobacion, fecha_registro) as fecha_mostrar, CASE WHEN activo = 1 THEN 'Activo' ELSE 'Inactivo' END as estado FROM visitantes WHERE estado_aprobacion = 'aprobado' ORDER BY COALESCE(fecha_aprobacion, fecha_registro) DESC LIMIT 5";
                                
                                $result = $mysqli->query($query_socios_aprobados);
                                if ($result && $result->num_rows > 0) {
                                    while ($socio = $result->fetch_assoc()) {
                                        echo "<tr class='hover:bg-gray-50 transition-colors'>";
                                        echo "<td class='px-6 py-4 whitespace-nowrap'>";
                                        echo "<div class='flex items-center'>";
                                        echo "<div class='w-10 h-10 bg-gradient-to-r from-green-400 to-green-600 rounded-full flex items-center justify-center text-white font-semibold mr-3'>";
                                        echo strtoupper(substr($socio['nombre'], 0, 2));
                                        echo "</div>";
                                        echo "<div class='text-sm font-medium text-gray-900'>" . htmlspecialchars($socio['nombre']) . "</div>";
                                        echo "</div>";
                                        echo "</td>";
                                        echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900'>" . htmlspecialchars($socio['email']) . "</td>";
                                        echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>";
                                        
                                        if (!empty($socio['fecha_mostrar'])) {
                                            try {
                                                $fecha = new DateTime($socio['fecha_mostrar']);
                                                echo $fecha->format('d/m/Y H:i');
                                            } catch (Exception $e) {
                                                echo htmlspecialchars($socio['fecha_mostrar']);
                                            }
                                        } else {
                                            echo 'N/A';
                                        }
                                        echo "</td>";
                                        echo "<td class='px-6 py-4 whitespace-nowrap'>";
                                        if ($socio['estado'] === 'Activo') {
                                            echo "<span class='px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800'>Activo</span>";
                                        } else {
                                            echo "<span class='px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800'>Inactivo</span>";
                                        }
                                        echo "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4' class='px-6 py-12 text-center text-gray-500'>";
                                    echo "<div class='flex flex-col items-center'>";
                                    echo "<div class='w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4'>";
                                    echo "<i class='ti ti-user-plus text-3xl text-gray-300'></i>";
                                    echo "</div>";
                                    echo "<p class='text-lg font-medium text-gray-600'>No hay socios aprobados aÃºn</p>";
                                    echo "</div>";
                                    echo "</td></tr>";
                                }
                            } catch (Exception $e) {
                                echo "<tr><td colspan='4' class='px-6 py-4 text-red-600'>Error al cargar los datos: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal para crear notificaciÃ³n -->
<div x-show="showCreateNotification" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
     style="display: none;">
    
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="ti ti-bell mr-2 text-indigo-600"></i>
                    Nueva NotificaciÃ³n
                </h3>
                <button @click="showCreateNotification = false" 
                        class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="ti ti-x text-xl"></i>
                </button>
            </div>
            
            <form @submit.prevent="createNotification()" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">TÃ­tulo</label>
                    <input type="text" x-model="newNotification.titulo" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           required>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mensaje</label>
                    <textarea x-model="newNotification.mensaje" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                              required></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
                    <select x-model="newNotification.tipo"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="info">InformaciÃ³n</option>
                        <option value="success">Ã‰xito</option>
                        <option value="warning">Advertencia</option>
                        <option value="error">Error</option>
                    </select>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" @click="showCreateNotification = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">
                        <i class="ti ti-send mr-1"></i>
                        Crear NotificaciÃ³n
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Alpine.js Dashboard App
// Alpine.js Dashboard App - VersiÃ³n mejorada
function dashboardApp() {
    return {
        notifications: [],
        unreadCount: 0,
        showCreateNotification: false,
        loading: false,
        newNotification: {
            titulo: '',
            mensaje: '',
            tipo: 'info',
            para_todos: false
        },
        
        init() {
            this.loadNotifications();
            this.updateUnreadCount();
            this.initChart();
            
            // Actualizar cada 30 segundos
            setInterval(() => {
                this.updateUnreadCount();
            }, 30000);

            // Actualizar notificaciones cada 2 minutos
            setInterval(() => {
                this.loadNotifications();
            }, 120000);
        },
        
        async loadNotifications() {
            try {
                this.loading = true;
                const response = await fetch('api_notifications.php?action=get', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
                
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }
                
                const data = await response.json();
                
                if (data.success) {
                    this.notifications = data.notifications || [];
                    this.updateUnreadCount();
                } else {
                    console.error('Error loading notifications:', data.error);
                    this.showToast('Error al cargar notificaciones: ' + data.error, 'error');
                }
            } catch (error) {
                console.error('Error loading notifications:', error);
                this.showToast('Error de conexiÃ³n al cargar notificaciones', 'error');
                // Mantener notificaciones existentes en caso de error
            } finally {
                this.loading = false;
            }
        },
        
        async updateUnreadCount() {
            try {
                const response = await fetch('api_notifications.php?action=count_unread', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
                
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }
                
                const data = await response.json();
                
                if (data.success) {
                    this.unreadCount = data.count || 0;
                } else {
                    console.error('Error updating unread count:', data.error);
                }
            } catch (error) {
                console.error('Error updating unread count:', error);
                // Calcular conteo local como respaldo
                this.unreadCount = this.notifications.filter(n => !n.leida).length;
            }
        },
        
        async markAsRead(notificationId) {
            try {
                const formData = new FormData();
                formData.append('action', 'mark_read');
                formData.append('notification_id', notificationId);
                
                const response = await fetch('api_notifications.php', {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }
                
                const data = await response.json();
                
                if (data.success) {
                    // Actualizar estado local
                    const notification = this.notifications.find(n => n.id == notificationId);
                    if (notification && !notification.leida) {
                        notification.leida = true;
                        this.unreadCount = Math.max(0, this.unreadCount - 1);
                    }
                } else {
                    console.error('Error marking as read:', data.error);
                    this.showToast('Error al marcar como leÃ­da: ' + data.error, 'error');
                }
            } catch (error) {
                console.error('Error marking as read:', error);
                this.showToast('Error de conexiÃ³n', 'error');
            }
        },
        
        async markAllAsRead() {
            try {
                const formData = new FormData();
                formData.append('action', 'mark_all_read');
                
                const response = await fetch('api_notifications.php', {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }
                
                const data = await response.json();
                
                if (data.success) {
                    // Actualizar estado local
                    this.notifications.forEach(n => n.leida = true);
                    this.unreadCount = 0;
                    this.showToast('Todas las notificaciones marcadas como leÃ­das', 'success');
                } else {
                    console.error('Error marking all as read:', data.error);
                    this.showToast('Error al marcar todas como leÃ­das: ' + data.error, 'error');
                }
            } catch (error) {
                console.error('Error marking all as read:', error);
                this.showToast('Error de conexiÃ³n', 'error');
            }
        },
        
        async deleteNotification(notificationId) {
            if (!confirm('Â¿EstÃ¡ seguro de eliminar esta notificaciÃ³n?')) return;
            
            try {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('notification_id', notificationId);
                
                const response = await fetch('api_notifications.php', {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }
                
                const data = await response.json();
                
                if (data.success) {
                    // Remover de la lista local
                    const index = this.notifications.findIndex(n => n.id == notificationId);
                    if (index !== -1) {
                        const wasUnread = !this.notifications[index].leida;
                        this.notifications.splice(index, 1);
                        if (wasUnread) {
                            this.unreadCount = Math.max(0, this.unreadCount - 1);
                        }
                    }
                    this.showToast('NotificaciÃ³n eliminada', 'success');
                } else {
                    console.error('Error deleting notification:', data.error);
                    this.showToast('Error al eliminar notificaciÃ³n: ' + data.error, 'error');
                }
            } catch (error) {
                console.error('Error deleting notification:', error);
                this.showToast('Error de conexiÃ³n', 'error');
            }
        },
        
        formatDate(dateString) {
            try {
                const date = new Date(dateString);
                const now = new Date();
                const diff = now - date;
                const minutes = Math.floor(diff / 60000);
                const hours = Math.floor(diff / 3600000);
                const days = Math.floor(diff / 86400000);
                
                if (minutes < 1) return 'Hace un momento';
                if (minutes < 60) return `Hace ${minutes} min`;
                if (hours < 24) return `Hace ${hours}h`;
                if (days < 7) return `Hace ${days}d`;
                
                return date.toLocaleDateString('es-ES', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            } catch (error) {
                return dateString;
            }
        },
        
        async createNotification() {
            if (!this.newNotification.titulo.trim() || !this.newNotification.mensaje.trim()) {
                this.showToast('TÃ­tulo y mensaje son requeridos', 'warning');
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('action', 'create');
                formData.append('titulo', this.newNotification.titulo.trim());
                formData.append('mensaje', this.newNotification.mensaje.trim());
                formData.append('tipo', this.newNotification.tipo);
                formData.append('para_todos', this.newNotification.para_todos ? '1' : '0');
                
                const response = await fetch('api_notifications.php', {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }
                
                const data = await response.json();
                
                if (data.success) {
                    this.showCreateNotification = false;
                    this.newNotification = { titulo: '', mensaje: '', tipo: 'info', para_todos: false };
                    this.showToast('NotificaciÃ³n creada exitosamente', 'success');
                    
                    // Recargar notificaciones para ver la nueva
                    await this.loadNotifications();
                } else {
                    console.error('Error creating notification:', data.error);
                    this.showToast('Error al crear notificaciÃ³n: ' + data.error, 'error');
                }
            } catch (error) {
                console.error('Error creating notification:', error);
                this.showToast('Error de conexiÃ³n al crear notificaciÃ³n', 'error');
            }
        },
        
        async createVisitorNotification(visitorName, visitorId) {
            try {
                const formData = new FormData();
                formData.append('action', 'auto_create_pending_visitor');
                formData.append('visitor_name', visitorName);
                formData.append('visitor_id', visitorId);
                
                const response = await fetch('api_notifications.php', {
                    method: 'POST',
                    body: formData
                });
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        // Recargar notificaciones
                        await this.loadNotifications();
                    }
                }
            } catch (error) {
                console.error('Error creating visitor notification:', error);
            }
        },
        
        initChart() {
            const ctx = document.getElementById('monthlyChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Socios', 'Pendientes', 'Viviendas', 'Proyectos'],
                        datasets: [{
                            data: [<?php echo $stats['socios_total']; ?>, <?php echo $stats['visitantes_pendientes']; ?>, <?php echo $stats['viviendas_total']; ?>, <?php echo $stats['proyectos_total']; ?>],
                            backgroundColor: [
                                'rgba(99, 102, 241, 0.8)',
                                'rgba(236, 72, 153, 0.8)', 
                                'rgba(34, 197, 94, 0.8)',
                                'rgba(245, 158, 11, 0.8)'
                            ],
                            borderWidth: 2,
                            borderColor: 'white'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { boxWidth: 12, fontSize: 12 }
                            }
                        }
                    }
                });
            }
        },
        
        showToast(message, type = 'info') {
            const toast = document.createElement('div');
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
            
            toast.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg text-white ${colors[type]} transform transition-all duration-300 translate-x-full max-w-sm`;
            toast.innerHTML = `
                <div class="flex items-center">
                    <i class="ti ${icons[type]} mr-2 text-lg"></i>
                    <span class="flex-1">${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200 focus:outline-none">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
            `;
            
            document.body.appendChild(toast);
            
            // Mostrar toast
            setTimeout(() => toast.classList.remove('translate-x-full'), 100);
            
            // Ocultar automÃ¡ticamente despuÃ©s de 5 segundos
            setTimeout(() => {
                toast.classList.add('translate-x-full');
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.remove();
                    }
                }, 300);
            }, 5000);
        }
    }
}

// Funciones para aprobar/rechazar visitantes
function approveVisitante(id) {
    if (!confirm('Â¿EstÃ¡ seguro de aprobar este visitante como socio?')) return;
    
    fetch(`api_approve.php?id=${id}`, { method: 'POST' })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const row = document.getElementById(`row-${id}`);
                if (row) row.remove();
                
                const counter = document.getElementById('pending-count');
                if (counter) counter.textContent = parseInt(counter.textContent) - 1;
                
                // Mostrar toast de Ã©xito
                window.dispatchEvent(new CustomEvent('show-toast', { 
                    detail: { message: 'Visitante aprobado exitosamente', type: 'success' }
                }));
            } else {
                throw new Error(data.error || 'Error desconocido');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            window.dispatchEvent(new CustomEvent('show-toast', { 
                detail: { message: 'Error al aprobar visitante: ' + error.message, type: 'error' }
            }));
        });
}

function rejectVisitante(id) {
    if (!confirm('Â¿EstÃ¡ seguro de rechazar este visitante?')) return;
    
    fetch(`api_reject.php?id=${id}`, { method: 'POST' })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const row = document.getElementById(`row-${id}`);
                if (row) row.remove();
                
                const counter = document.getElementById('pending-count');
                if (counter) counter.textContent = parseInt(counter.textContent) - 1;
                
                window.dispatchEvent(new CustomEvent('show-toast', { 
                    detail: { message: 'Visitante rechazado', type: 'success' }
                }));
            } else {
                throw new Error(data.error || 'Error desconocido');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            window.dispatchEvent(new CustomEvent('show-toast', { 
                detail: { message: 'Error al rechazar visitante: ' + error.message, type: 'error' }
            }));
        });
}

// Toast event listener
window.addEventListener('show-toast', (e) => {
    const appInstance = Alpine.$data(document.body);
    if (appInstance && typeof appInstance.showToast === 'function') {
        appInstance.showToast(e.detail.message, e.detail.type);
    }
});



function approveTime(timeId) {
    if (!confirm('¿Aprobar estas horas de trabajo?')) return;
    
    fetch('api_admin_workers.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=approve_time&time_id=${timeId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const row = document.getElementById(`time-row-${timeId}`);
            if (row) row.remove();
            
            showToast('Horas aprobadas exitosamente', 'success');
            updatePendingCounters();
        } else {
            throw new Error(data.error || 'Error desconocido');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error al aprobar horas: ' + error.message, 'error');
    });
}

function rejectTime(timeId) {
    const reason = prompt('Motivo del rechazo (opcional):');
    if (reason === null) return; // Usuario canceló
    
    fetch('api_admin_workers.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=reject_time&time_id=${timeId}&reason=${encodeURIComponent(reason || '')}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const row = document.getElementById(`time-row-${timeId}`);
            if (row) row.remove();
            
            showToast('Horas rechazadas', 'warning');
            updatePendingCounters();
        } else {
            throw new Error(data.error || 'Error desconocido');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error al rechazar horas: ' + error.message, 'error');
    });
}

function approveAllTime() {
    if (!confirm('¿Aprobar todas las horas pendientes?')) return;
    
    fetch('api_admin_workers.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=approve_all_time'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remover todas las filas de tiempo pendiente
            document.querySelectorAll('[id^="time-row-"]').forEach(row => row.remove());
            
            showToast(`${data.approved_count} registros aprobados`, 'success');
            updatePendingCounters();
        } else {
            throw new Error(data.error || 'Error desconocido');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error al aprobar todas las horas: ' + error.message, 'error');
    });
}

// Funciones para manejar reportes PDF
function downloadReport(reportId) {
    window.open(`api_admin_workers.php?action=download_report&report_id=${reportId}`, '_blank');
}

function approveReport(reportId) {
    if (!confirm('¿Aprobar este reporte?')) return;
    
    fetch('api_admin_workers.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=approve_report&report_id=${reportId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const row = document.getElementById(`report-row-${reportId}`);
            if (row) row.remove();
            
            showToast('Reporte aprobado exitosamente', 'success');
            updatePendingCounters();
        } else {
            throw new Error(data.error || 'Error desconocido');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error al aprobar reporte: ' + error.message, 'error');
    });
}

function rejectReport(reportId) {
    const reason = prompt('Motivo del rechazo (opcional):');
    if (reason === null) return; // Usuario canceló
    
    fetch('api_admin_workers.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=reject_report&report_id=${reportId}&reason=${encodeURIComponent(reason || '')}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const row = document.getElementById(`report-row-${reportId}`);
            if (row) row.remove();
            
            showToast('Reporte rechazado', 'warning');
            updatePendingCounters();
        } else {
            throw new Error(data.error || 'Error desconocido');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error al rechazar reporte: ' + error.message, 'error');
    });
}

function viewTimeDetails(timeId) {
    fetch(`api_admin_workers.php?action=get_time_details&time_id=${timeId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const details = data.time_record;
            alert(`Detalles del registro:
            
Trabajador: ${details.trabajador_nombre}
Descripción: ${details.descripcion}
Horas: ${details.horas}h
Fecha: ${details.fecha_inicio}
Estado: ${details.estado}

${details.fecha_fin ? `Hora fin: ${details.fecha_fin}` : ''}`);
        } else {
            throw new Error(data.error || 'Error al obtener detalles');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error al cargar detalles: ' + error.message, 'error');
    });
}

function updatePendingCounters() {
    // Actualizar contadores después de aprobar/rechazar
    setTimeout(() => {
        location.reload(); // Recargar para actualizar estadísticas
    }, 1500);
}

// Función showToast ya existe en el dashboard, reutilizar
function showToast(message, type = 'info') {
    // Usar la función existente del dashboard o implementar una básica
    const appInstance = Alpine.$data(document.body);
    if (appInstance && typeof appInstance.showToast === 'function') {
        appInstance.showToast(message, type);
    } else {
        // Fallback toast básico
        const toast = document.createElement('div');
        const colors = {
            success: 'bg-green-500',
            error: 'bg-red-500',
            warning: 'bg-yellow-500',
            info: 'bg-blue-500'
        };
        
        toast.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg text-white ${colors[type]} transform transition-all duration-300 max-w-sm`;
        toast.innerHTML = `
            <div class="flex items-center">
                <span class="flex-1">${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                    <i class="ti ti-x"></i>
                </button>
            </div>
        `;
        
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 5000);
    }
}
</script>

</body>
</html