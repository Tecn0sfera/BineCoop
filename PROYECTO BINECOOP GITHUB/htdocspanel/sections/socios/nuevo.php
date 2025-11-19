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

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $apellido = trim($_POST['apellido'] ?? '');
    $ci = trim($_POST['ci'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    
    if (empty($nombre) || empty($email) || empty($password)) {
        $error = 'Nombre, email y contraseña son requeridos';
    } else {
        // Verificar si el email ya existe
        $check_query = "SELECT id FROM visitantes WHERE email = ?";
        $check_stmt = $mysqli->prepare($check_query);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error = 'El email ya está registrado';
        } else {
            // Crear nuevo visitante
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $fecha_registro = date('Y-m-d H:i:s');
            
            $insert_query = "INSERT INTO visitantes (nombre, email, password, fecha_registro, estado_aprobacion, activo) VALUES (?, ?, ?, ?, 'aprobado', 1)";
            $insert_stmt = $mysqli->prepare($insert_query);
            $insert_stmt->bind_param("ssss", $nombre, $email, $password_hash, $fecha_registro);
            
            if ($insert_stmt->execute()) {
                $visitante_id = $mysqli->insert_id;
                
                // Si hay datos adicionales, crear registro en socios
                if (!empty($apellido) || !empty($ci) || !empty($telefono)) {
                    $socio_query = "INSERT INTO socios (visitante_id, apellido, ci, telefono, fecha_ingreso) VALUES (?, ?, ?, ?, CURDATE())";
                    $socio_stmt = $mysqli->prepare($socio_query);
                    $socio_stmt->bind_param("isss", $visitante_id, $apellido, $ci, $telefono);
                    $socio_stmt->execute();
                }
                
                $success = true;
                // Redirigir después de 2 segundos
                header("refresh:2;url=lista.php");
            } else {
                $error = 'Error al crear el socio: ' . $mysqli->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Socio - Cooperativa de Vivienda</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" rel="stylesheet">
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50">

<div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: false }">
    
    <!-- Sidebar (similar a lista.php) -->
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
                    <a href="lista.php" class="block px-3 py-2 text-sm text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg">Lista de Socios</a>
                    <a href="nuevo.php" class="block px-3 py-2 text-sm text-indigo-600 bg-indigo-50 rounded-lg font-medium">Nuevo Socio</a>
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
                    <a href="lista.php" class="text-gray-500 hover:text-gray-700">
                        <i class="ti ti-arrow-left mr-2"></i> Volver
                    </a>
                    <h2 class="text-xl font-semibold text-gray-800">Nuevo Socio</h2>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-6">
            <?php if ($success): ?>
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
                    <div class="flex items-center">
                        <i class="ti ti-check-circle mr-2"></i>
                        <span>Socio creado exitosamente. Redirigiendo...</span>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
                    <div class="flex items-center">
                        <i class="ti ti-alert-circle mr-2"></i>
                        <span><?php echo htmlspecialchars($error); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <div class="max-w-3xl mx-auto">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-6">Información del Socio</h3>
                    
                    <form method="POST" action="nuevo.php" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nombre *</label>
                                <input type="text" name="nombre" required 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                       value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Apellido</label>
                                <input type="text" name="apellido" 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                       value="<?php echo htmlspecialchars($_POST['apellido'] ?? ''); ?>">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                                <input type="email" name="email" required 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Contraseña *</label>
                                <input type="password" name="password" required 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Cédula de Identidad</label>
                                <input type="text" name="ci" 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                       value="<?php echo htmlspecialchars($_POST['ci'] ?? ''); ?>">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Teléfono</label>
                                <input type="text" name="telefono" 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                       value="<?php echo htmlspecialchars($_POST['telefono'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-4 pt-4 border-t border-gray-200">
                            <a href="lista.php" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                                Cancelar
                            </a>
                            <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors">
                                Crear Socio
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

</body>
</html>

