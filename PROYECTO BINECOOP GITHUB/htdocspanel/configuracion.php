<?php
session_start();

// Configurar encoding UTF-8
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

// Verificar autenticación
if (empty($_SESSION['user']['logged_in'])) {
    header("Location: login.php");
    exit();
}

$GLOBALS['allowed_config_access'] = true;
require __DIR__ . '/config/config.php';
require_once 'includes/db.php';

// Verificar permisos de administrador
$current_user = $_SESSION['user'];
$user_role = $current_user['role'] ?? 'admin';

if ($user_role !== 'admin' && $user_role !== 'super_admin') {
    header("Location: dashboard.php");
    exit();
}

$username = $current_user['username'] ?? 'Usuario';

// Mensajes de éxito/error
$success_message = '';
$error_message = '';

// Procesar actualización de configuración
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_config') {
    // Aquí puedes agregar lógica para actualizar configuraciones del sistema
    $success_message = "Configuración actualizada exitosamente";
}

// Cargar configuraciones actuales desde config.json
$config_data = [];
$config_file = __DIR__ . '/../config.json';
if (file_exists($config_file)) {
    $config_content = file_get_contents($config_file);
    $config_data = json_decode($config_content, true);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - Cooperativa de Vivienda</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Tabler Icons -->
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
        
        <!-- Header del Sidebar -->
        <div class="flex items-center justify-between px-6 py-4 bg-indigo-600 text-white">
            <h1 class="text-lg font-bold">Cooperativa</h1>
            <button class="lg:hidden" @click="sidebarOpen = false">
                <i class="ti ti-x text-xl"></i>
            </button>
        </div>
        
        <!-- Navegación -->
        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto h-full">
            <a href="dashboard.php" class="flex items-center px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors">
                <i class="ti ti-dashboard mr-3 text-lg"></i>
                Dashboard
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
                    <h2 class="text-xl font-semibold text-gray-800">Configuración</h2>
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="dashboard.php" class="text-gray-600 hover:text-gray-800">
                        <i class="ti ti-arrow-left mr-1"></i> Volver al Dashboard
                    </a>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto p-6">
            
            <!-- Mensajes -->
            <?php if (!empty($success_message)): ?>
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center">
                        <i class="ti ti-check text-green-500 mr-2"></i>
                        <span class="text-green-700"><?php echo htmlspecialchars($success_message); ?></span>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-center">
                        <i class="ti ti-alert-circle text-red-500 mr-2"></i>
                        <span class="text-red-700"><?php echo htmlspecialchars($error_message); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <div class="max-w-4xl mx-auto space-y-6">
                
                <!-- Configuración de Base de Datos -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                            <i class="ti ti-database text-indigo-600 mr-2"></i>
                            Configuración de Base de Datos
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">DB Host</label>
                                    <input type="text" value="<?php echo htmlspecialchars($config_data['htdocspanel']['DB_HOST'] ?? ''); ?>" 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50" 
                                           readonly>
                                    <p class="text-xs text-gray-500 mt-1">Configurado en config.json</p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">DB Port</label>
                                    <input type="text" value="<?php echo htmlspecialchars($config_data['htdocspanel']['DB_PORT'] ?? ''); ?>" 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50" 
                                           readonly>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">DB Name</label>
                                    <input type="text" value="<?php echo htmlspecialchars($config_data['htdocspanel']['DB_NAME'] ?? ''); ?>" 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50" 
                                           readonly>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">DB User</label>
                                    <input type="text" value="<?php echo htmlspecialchars($config_data['htdocspanel']['DB_USER'] ?? ''); ?>" 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50" 
                                           readonly>
                                </div>
                            </div>
                            
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <div class="flex items-start">
                                    <i class="ti ti-alert-triangle text-yellow-600 mr-2 mt-0.5"></i>
                                    <div>
                                        <p class="text-sm font-medium text-yellow-800">Nota importante</p>
                                        <p class="text-sm text-yellow-700 mt-1">
                                            La configuración de la base de datos se gestiona desde el archivo <code class="bg-yellow-100 px-1 rounded">config.json</code> en la raíz del proyecto. 
                                            Para realizar cambios, edita directamente ese archivo.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Configuración de Sistema -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                            <i class="ti ti-settings text-indigo-600 mr-2"></i>
                            Configuración de Sistema
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Entorno</label>
                                    <div class="px-4 py-2 border border-gray-300 rounded-lg bg-gray-50">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                            <?php echo ($config_data['htdocspanel']['APP_ENV'] ?? 'production') === 'development' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'; ?>">
                                            <?php echo htmlspecialchars(strtoupper($config_data['htdocspanel']['APP_ENV'] ?? 'production')); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Mostrar Errores</label>
                                    <div class="px-4 py-2 border border-gray-300 rounded-lg bg-gray-50">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                            <?php echo ($config_data['htdocspanel']['DISPLAY_ERRORS'] ?? 'false') === 'true' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'; ?>">
                                            <?php echo ($config_data['htdocspanel']['DISPLAY_ERRORS'] ?? 'false') === 'true' ? 'Activado' : 'Desactivado'; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Archivos de Configuración -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                            <i class="ti ti-file-text text-indigo-600 mr-2"></i>
                            Archivos de Configuración
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                <div class="flex items-center">
                                    <i class="ti ti-file-code text-gray-400 mr-3"></i>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">config.json</p>
                                        <p class="text-xs text-gray-500">Configuración principal del sistema</p>
                                    </div>
                                </div>
                                <span class="text-xs text-gray-500"><?php echo file_exists($config_file) ? 'Existe' : 'No existe'; ?></span>
                            </div>
                            
                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                <div class="flex items-center">
                                    <i class="ti ti-file-json text-gray-400 mr-3"></i>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">data/notifications.json</p>
                                        <p class="text-xs text-gray-500">Archivo de notificaciones del sistema</p>
                                    </div>
                                </div>
                                <span class="text-xs text-gray-500">
                                    <?php echo file_exists(__DIR__ . '/data/notifications.json') ? 'Existe' : 'No existe'; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información del Sistema -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                            <i class="ti ti-info-circle text-indigo-600 mr-2"></i>
                            Información del Sistema
                        </h3>
                    </div>
                    <div class="p-6">
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Versión PHP</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo PHP_VERSION; ?></dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Servidor</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Desconocido'; ?></dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Usuario Actual</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($username); ?></dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Rol</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars(ucfirst($user_role)); ?></dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

</body>
</html>

