<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user']) || !isset($_SESSION['user_token'])) {
    // Guardar mensaje de error para mostrar en login
    $_SESSION['login_error'] = 'Debes iniciar sesión para acceder a esta página';
    header("Location: login.php");
    exit();
}

// Verificar que los datos del usuario estén completos
$user = $_SESSION['user'];
$userName = '';

// Manejar diferentes estructuras posibles de datos del usuario
if (isset($user['nombre'])) {
    $userName = $user['nombre'];
} elseif (isset($user['name'])) {
    $userName = $user['name'];
} elseif (isset($user['email'])) {
    $userName = $user['email'];
} else {
    $userName = 'Usuario';
}

// Obtener información adicional si está disponible
$userEmail = $user['email'] ?? '';
$userRole = $user['role'] ?? $user['tipo'] ?? 'visitante';
$userStatus = $user['status'] ?? $user['estado'] ?? 'activo';

// Función para tiempo transcurrido desde login
function timeAgo($timestamp) {
    if (!$timestamp) return '';
    
    $time = time() - $timestamp;
    if ($time < 60) return 'hace unos segundos';
    if ($time < 3600) return 'hace ' . floor($time/60) . ' minutos';
    if ($time < 86400) return 'hace ' . floor($time/3600) . ' horas';
    return 'hace ' . floor($time/86400) . ' días';
}

$loginTime = $_SESSION['login_time'] ?? time();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Cooperativa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .dashboard-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            max-width: 800px;
            width: 100%;
            overflow: hidden;
        }
        
        .dashboard-header {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 2rem;
            text-align: center;
            position: relative;
        }
        
        .success-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }
        
        .dashboard-header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .dashboard-header p {
            opacity: 0.9;
            font-size: 1.1rem;
        }
        
        .user-info {
            padding: 2rem;
        }
        
        .user-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border-left: 4px solid #007bff;
        }
        
        .user-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .detail-item {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .detail-item i {
            color: #007bff;
            margin-right: 0.75rem;
            width: 20px;
            text-align: center;
        }
        
        .detail-label {
            font-weight: 600;
            color: #495057;
            margin-right: 0.5rem;
        }
        
        .detail-value {
            color: #6c757d;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .actions-section {
            padding: 0 2rem 2rem 2rem;
        }
        
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .action-card {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
        }
        
        .action-card:hover {
            border-color: #007bff;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,123,255,0.1);
        }
        
        .action-card i {
            font-size: 2.5rem;
            color: #007bff;
            margin-bottom: 1rem;
        }
        
        .action-card h3 {
            color: #343a40;
            margin-bottom: 0.5rem;
        }
        
        .action-card p {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .logout-section {
            text-align: center;
            padding-top: 1rem;
            border-top: 1px solid #e9ecef;
        }
        
        .btn {
            display: inline-block;
            padding: 0.75rem 2rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }
        
        .btn-logout {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
        }
        
        .btn-logout:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220,53,69,0.3);
        }
        
        .session-info {
            background: #e3f2fd;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
            text-align: center;
            color: #1565c0;
            font-size: 0.9rem;
        }
        
        @media (max-width: 768px) {
            .dashboard-header {
                padding: 1.5rem;
            }
            
            .dashboard-header h1 {
                font-size: 1.5rem;
            }
            
            .user-info {
                padding: 1.5rem;
            }
            
            .actions-section {
                padding: 0 1.5rem 1.5rem 1.5rem;
            }
            
            .user-details {
                grid-template-columns: 1fr;
            }
            
            .actions-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>¡Bienvenido al Sistema!</h1>
            <p>Has iniciado sesión correctamente</p>
        </div>
        
        <div class="user-info">
            <div class="user-card">
                <h2><i class="fas fa-user"></i> Información del Usuario</h2>
                
                <div class="user-details">
                    <div class="detail-item">
                        <i class="fas fa-user-circle"></i>
                        <span class="detail-label">Nombre:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($userName); ?></span>
                    </div>
                    
                    <?php if ($userEmail): ?>
                    <div class="detail-item">
                        <i class="fas fa-envelope"></i>
                        <span class="detail-label">Email:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($userEmail); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="detail-item">
                        <i class="fas fa-id-badge"></i>
                        <span class="detail-label">Rol:</span>
                        <span class="detail-value"><?php echo ucfirst(htmlspecialchars($userRole)); ?></span>
                    </div>
                    
                    <div class="detail-item">
                        <i class="fas fa-info-circle"></i>
                        <span class="detail-label">Estado:</span>
                        <span class="status-badge <?php echo $userStatus === 'activo' ? 'status-active' : 'status-pending'; ?>">
                            <?php echo ucfirst(htmlspecialchars($userStatus)); ?>
                        </span>
                    </div>
                </div>
                
                <?php if (isset($_SESSION['login_time'])): ?>
                <div class="session-info">
                    <i class="fas fa-clock"></i>
                    Sesión iniciada <?php echo timeAgo($loginTime); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="actions-section">
            <h3 style="margin-bottom: 1.5rem; color: #343a40;">
                <i class="fas fa-th-large"></i> Acciones Disponibles
            </h3>
            
            <div class="actions-grid">
                <a href="/dashboard.php" class="action-card">
                    <i class="fas fa-tachometer-alt"></i>
                    <h3>Dashboard</h3>
                    <p>Ver panel principal</p>
                </a>
                
                <a href="#" class="action-card">
                    <i class="fas fa-user-edit"></i>
                    <h3>Mi Perfil</h3>
                    <p>Editar información personal</p>
                </a>
                
                <a href="#" class="action-card">
                    <i class="fas fa-cog"></i>
                    <h3>Configuración</h3>
                    <p>Ajustes de la cuenta</p>
                </a>
                
                <a href="#" class="action-card">
                    <i class="fas fa-question-circle"></i>
                    <h3>Ayuda</h3>
                    <p>Centro de soporte</p>
                </a>
            </div>
            
            <div class="logout-section">
                <a href="logout.php" class="btn btn-logout" onclick="return confirm('¿Estás seguro que deseas cerrar sesión?')">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </div>
        </div>
    </div>

    <script>
        // Agregar alguna interactividad
        document.addEventListener('DOMContentLoaded', function() {
            // Animación de entrada
            const container = document.querySelector('.dashboard-container');
            container.style.opacity = '0';
            container.style.transform = 'translateY(30px)';
            
            setTimeout(() => {
                container.style.transition = 'all 0.6s ease';
                container.style.opacity = '1';
                container.style.transform = 'translateY(0)';
            }, 100);
            
            // Auto-refresh de la información de sesión cada minuto
            setInterval(function() {
                // Aquí podrías hacer una llamada AJAX para verificar que la sesión sigue activa
                console.log('Verificando sesión...');
            }, 60000);
        });
    </script>
</body>
</html>