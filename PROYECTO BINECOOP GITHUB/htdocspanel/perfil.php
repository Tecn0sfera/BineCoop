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

// Información del usuario logueado
$current_user = $_SESSION['user'];
$username = $current_user['username'] ?? 'Usuario';
$user_role = $current_user['role'] ?? 'admin';
$user_id = $current_user['id'] ?? 0;

// Mensajes de éxito/error
$success_message = '';
$error_message = '';

// Obtener información del usuario desde la base de datos
$user_info = [
    'nombre' => $username,
    'email' => '',
    'telefono' => '',
    'direccion' => '',
    'fecha_registro' => '',
    'ultimo_acceso' => ''
];

try {
    // Buscar usuario en la base de datos
    if (!isset($mysqli) || !$mysqli) {
        throw new Exception("Conexión a la base de datos no disponible");
    }
    
    $query = "SELECT * FROM usuarios WHERE id = ? LIMIT 1";
    $stmt = $mysqli->prepare($query);
    if (!$stmt) {
        throw new Exception("Error preparando consulta: " . $mysqli->error);
    }
    
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $row = $result->fetch_assoc()) {
        $user_info = [
            'nombre' => $row['name'] ?? $row['username'] ?? $username,
            'email' => $row['email'] ?? '',
            'telefono' => $row['telefono'] ?? '',
            'direccion' => $row['direccion'] ?? '',
            'fecha_registro' => $row['created_at'] ?? $row['fecha_registro'] ?? '',
            'ultimo_acceso' => $row['ultimo_acceso'] ?? $row['last_login'] ?? ''
        ];
    }
} catch (Exception $e) {
    error_log("Error al obtener información del usuario: " . $e->getMessage());
    $error_message = "Error al cargar información del usuario: " . htmlspecialchars($e->getMessage());
}

// Procesar actualización de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    
    try {
        $update_query = "UPDATE usuarios SET name = ?, email = ?, telefono = ?, direccion = ? WHERE id = ?";
        $stmt = $mysqli->prepare($update_query);
        $stmt->bind_param("ssssi", $nombre, $email, $telefono, $direccion, $user_id);
        
        if ($stmt->execute()) {
            $success_message = "Perfil actualizado exitosamente";
            // Actualizar información local
            $user_info['nombre'] = $nombre;
            $user_info['email'] = $email;
            $user_info['telefono'] = $telefono;
            $user_info['direccion'] = $direccion;
            
            // Actualizar sesión
            $_SESSION['user']['username'] = $nombre;
        } else {
            $error_message = "Error al actualizar el perfil";
        }
    } catch (Exception $e) {
        $error_message = "Error: " . $e->getMessage();
        error_log("Error al actualizar perfil: " . $e->getMessage());
    }
}

// Procesar cambio de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_message = "Todos los campos de contraseña son requeridos";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "Las contraseñas no coinciden";
    } elseif (strlen($new_password) < 8) {
        $error_message = "La nueva contraseña debe tener al menos 8 caracteres";
    } else {
        try {
            // Verificar contraseña actual
            $query = "SELECT password FROM usuarios WHERE id = ? LIMIT 1";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $row = $result->fetch_assoc()) {
                if (password_verify($current_password, $row['password'])) {
                    // Actualizar contraseña
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_query = "UPDATE usuarios SET password = ? WHERE id = ?";
                    $stmt = $mysqli->prepare($update_query);
                    $stmt->bind_param("si", $hashed_password, $user_id);
                    
                    if ($stmt->execute()) {
                        $success_message = "Contraseña actualizada exitosamente";
                    } else {
                        $error_message = "Error al actualizar la contraseña";
                    }
                } else {
                    $error_message = "La contraseña actual es incorrecta";
                }
            } else {
                $error_message = "Usuario no encontrado";
            }
        } catch (Exception $e) {
            $error_message = "Error: " . $e->getMessage();
            error_log("Error al cambiar contraseña: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Cooperativa de Vivienda</title>
    
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
                    <h2 class="text-xl font-semibold text-gray-800">Mi Perfil</h2>
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

            <div class="max-w-4xl mx-auto">
                <!-- Información Personal -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                            <i class="ti ti-user text-indigo-600 mr-2"></i>
                            Información Personal
                        </h3>
                    </div>
                    <div class="p-6">
                        <form method="POST" action="perfil.php">
                            <input type="hidden" name="action" value="update_profile">
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nombre</label>
                                    <input type="text" name="nombre" value="<?php echo htmlspecialchars($user_info['nombre']); ?>" 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                           required>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                    <input type="email" name="email" value="<?php echo htmlspecialchars($user_info['email']); ?>" 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                           required>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Teléfono</label>
                                    <input type="tel" name="telefono" value="<?php echo htmlspecialchars($user_info['telefono']); ?>" 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Dirección</label>
                                    <input type="text" name="direccion" value="<?php echo htmlspecialchars($user_info['direccion']); ?>" 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div>
                            
                            <div class="mt-6 flex justify-end">
                                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg transition-colors">
                                    <i class="ti ti-device-floppy mr-2"></i> Guardar Cambios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Cambiar Contraseña -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                            <i class="ti ti-lock text-indigo-600 mr-2"></i>
                            Cambiar Contraseña
                        </h3>
                    </div>
                    <div class="p-6">
                        <form method="POST" action="perfil.php">
                            <input type="hidden" name="action" value="change_password">
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Contraseña Actual</label>
                                    <input type="password" name="current_password" 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                           required>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nueva Contraseña</label>
                                    <input type="password" name="new_password" 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                           required minlength="8">
                                    <p class="text-xs text-gray-500 mt-1">Mínimo 8 caracteres</p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Confirmar Nueva Contraseña</label>
                                    <input type="password" name="confirm_password" 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                           required minlength="8">
                                </div>
                            </div>
                            
                            <div class="mt-6 flex justify-end">
                                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg transition-colors">
                                    <i class="ti ti-lock mr-2"></i> Cambiar Contraseña
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Información de Cuenta -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                            <i class="ti ti-info-circle text-indigo-600 mr-2"></i>
                            Información de Cuenta
                        </h3>
                    </div>
                    <div class="p-6">
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Rol</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars(ucfirst($user_role)); ?></dd>
                            </div>
                            
                            <?php if (!empty($user_info['fecha_registro'])): ?>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Fecha de Registro</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <?php 
                                        try {
                                            $fecha = new DateTime($user_info['fecha_registro']);
                                            echo $fecha->format('d/m/Y H:i');
                                        } catch (Exception $e) {
                                            echo htmlspecialchars($user_info['fecha_registro']);
                                        }
                                        ?>
                                    </dd>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($user_info['ultimo_acceso'])): ?>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Último Acceso</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <?php 
                                        try {
                                            $fecha = new DateTime($user_info['ultimo_acceso']);
                                            echo $fecha->format('d/m/Y H:i');
                                        } catch (Exception $e) {
                                            echo htmlspecialchars($user_info['ultimo_acceso']);
                                        }
                                        ?>
                                    </dd>
                                </div>
                            <?php endif; ?>
                        </dl>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

</body>
</html>

