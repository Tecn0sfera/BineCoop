<?php
// Esto debe ser lo PRIMERO en el archivo
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$GLOBALS['allowed_config_access'] = true;
require __DIR__ . '/config/config.php';
require __DIR__ . '/config/database.php';

// Inicializar variables
$errors = [];
$formData = [
    'username' => '',
    'password' => ''
];

// Función para limpiar datos
function limpiarDato($dato) {
    return htmlspecialchars(trim($dato), ENT_QUOTES, 'UTF-8');
}

// Control de intentos fallidos
function checkLoginAttempts() {
    if (!isset($_SESSION['admin_login_attempts'])) {
        $_SESSION['admin_login_attempts'] = 0;
        $_SESSION['admin_last_attempt_time'] = time();
    }
    
    // Si han pasado más de 30 minutos, resetear intentos (más estricto para admin)
    if (time() - $_SESSION['admin_last_attempt_time'] > 1800) {
        $_SESSION['admin_login_attempts'] = 0;
    }
    
    return $_SESSION['admin_login_attempts'];
}

function incrementLoginAttempts() {
    $_SESSION['admin_login_attempts']++;
    $_SESSION['admin_last_attempt_time'] = time();
    
    // Log del intento fallido para seguridad
    error_log("Admin login attempt failed. IP: " . $_SERVER['REMOTE_ADDR'] . " Time: " . date('Y-m-d H:i:s'));
}

function getTimeUntilRetry() {
    $timeElapsed = time() - $_SESSION['admin_last_attempt_time'];
    $timeRemaining = 1800 - $timeElapsed; // 30 minutos = 1800 segundos
    return max(0, $timeRemaining);
}

// Función para validar entrada
function validarEntrada($entrada) {
    // Para admin, permitir tanto email como username
    if (filter_var($entrada, FILTER_VALIDATE_EMAIL)) {
        return 'email';
    } elseif (preg_match('/^[a-zA-Z0-9_.-]+$/', $entrada)) {
        return 'username';
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Verificar intentos de login
        $attempts = checkLoginAttempts();
        
        if ($attempts >= 3) { // Más estricto para admin (3 en lugar de 5)
            $timeRemaining = getTimeUntilRetry();
            if ($timeRemaining > 0) {
                $minutes = ceil($timeRemaining / 60);
                throw new Exception("Demasiados intentos fallidos. Acceso bloqueado por $minutes minutos.");
            } else {
                $_SESSION['admin_login_attempts'] = 0;
            }
        }
        
        // Limpiar y validar datos del formulario
        $formData['username'] = limpiarDato($_POST['username'] ?? '');
        $formData['password'] = $_POST['password'] ?? '';
        
        // Validaciones
        if (empty($formData['username'])) {
            $errors['username'] = 'El usuario o email es obligatorio';
        } elseif (strlen($formData['username']) < 3) {
            $errors['username'] = 'El usuario debe tener al menos 3 caracteres';
        } elseif (strlen($formData['username']) > 100) {
            $errors['username'] = 'El usuario no puede exceder 100 caracteres';
        } elseif (!validarEntrada($formData['username'])) {
            $errors['username'] = 'Formato de usuario o email inválido';
        }
        
        if (empty($formData['password'])) {
            $errors['password'] = 'La contraseña es obligatoria';
        } elseif (strlen($formData['password']) < 4) {
            $errors['password'] = 'La contraseña es demasiado corta';
        }
        
        // Si no hay errores de validación, proceder con autenticación
        if (empty($errors)) {
            $db = new Database();
            $conn = $db->getConnection();
            
            if (!$conn) {
                throw new Exception("Error de conexión a la base de datos");
            }
            
            // Preparar consulta
            $stmt = $conn->prepare("SELECT id, username, email, password, role FROM usuarios WHERE (username = ? OR email = ?) AND role IN ('admin', 'super_admin') LIMIT 1");
            $stmt->execute([$formData['username'], $formData['username']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                incrementLoginAttempts();
                throw new Exception("Credenciales incorrectas o acceso no autorizado");
            }
            
            // Verificar contraseña
            if (!password_verify($formData['password'], $user['password'])) {
                incrementLoginAttempts();
                throw new Exception("Credenciales incorrectas");
            }

            // Login exitoso - Configurar sesión
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'] ?? '',
                'role' => $user['role'],
                'logged_in' => true,
                'login_time' => time(),
                'last_activity' => time(),
                'is_admin' => true
            ];
            
            // Resetear intentos de login
            $_SESSION['admin_login_attempts'] = 0;
            
            // Regenerar ID de sesión por seguridad
            session_regenerate_id(true);
            
            // Manejar "recordar sesión"
            if (isset($_POST['remember']) && $_POST['remember']) {
                // Extender duración de sesión a 30 días para admin
                ini_set('session.gc_maxlifetime', 30 * 24 * 3600);
                setcookie(session_name(), session_id(), time() + 30 * 24 * 3600, '/');
            }
            
            // Log del login exitoso
            error_log("Admin login successful. User: " . $user['username'] . " IP: " . $_SERVER['REMOTE_ADDR'] . " Time: " . date('Y-m-d H:i:s'));
            
            // Redirección
            $redirectUrl = $_SESSION['redirect_after_login'] ?? '/dashboard.php';
            unset($_SESSION['redirect_after_login']);
            
            header("Location: $redirectUrl");
            exit();
        }
        
    } catch (Exception $e) {
        $errors['general'] = $e->getMessage();
        
        // Log del error para monitoreo
        error_log("Admin login error: " . $e->getMessage() . " IP: " . $_SERVER['REMOTE_ADDR']);
    }
}

// Limpiar contraseña por seguridad
$formData['password'] = '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Panel de Administración</title>
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .admin-login-container {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
        }
        
        .login-header {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .logo {
            width: 80px;
            height: 80px;
            margin-bottom: 1rem;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid rgba(255,255,255,0.2);
        }
        
        .admin-badge {
            background: rgba(255,255,255,0.2);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            display: inline-block;
            margin-top: 0.5rem;
        }
        
        .security-warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 0.9em;
        }
        
        .login-attempts-warning {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        
        .login-loading {
            display: none;
            text-align: center;
            padding: 10px;
            color: #3498db;
        }
        
        .login-loading .spinner {
            border: 2px solid #f3f3f3;
            border-top: 2px solid #3498db;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
            display: inline-block;
            margin-right: 10px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }
        
        .form-group {
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .caps-lock-warning {
            display: none;
            color: #ffc107;
            font-size: 0.85em;
            margin-top: 5px;
        }
        
        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 1.5rem 0;
        }
        
        .form-check {
            display: flex;
            align-items: center;
        }
        
        .form-check-input {
            margin-right: 8px;
        }
        
        .form-check-label {
            font-size: 0.9em;
            color: #6c757d;
        }
        
        .login-btn {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52,152,219,0.3);
        }
        
        .login-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .input-with-icon {
            position: relative;
        }
        
        .form-footer {
            text-align: center;
            padding: 1rem;
            border-top: 1px solid #e9ecef;
            background: #f8f9fa;
        }
        
        @media (max-width: 480px) {
            .login-container {
                margin: 10px;
                max-width: none;
            }
            
            .login-header {
                padding: 1.5rem;
            }
            
            .form-actions {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }
            
            .login-btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="admin-login-container">
        <div class="login-container">
            <div class="login-header">
                <img src="assets/images/logo.png" alt="Logo" class="logo" onerror="this.style.display='none'">
                <h1>Panel de Administración</h1>
                <p>Acceso Seguro al Sistema</p>
                <span class="admin-badge">
                    <i class="fas fa-shield-alt"></i> ADMIN
                </span>
            </div>
            
            <?php if (isset($_SESSION['registration_success'])): ?>
                <div class="alert alert-success" style="margin: 1rem; padding: 1rem; background: #d4edda; border: 1px solid #c3e6cb; color: #155724; border-radius: 5px;">
                    <i class="fas fa-check-circle"></i>
                    ¡Registro exitoso! Por favor inicie sesión.
                </div>
                <?php unset($_SESSION['registration_success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['logout_success'])): ?>
                <div class="alert alert-success" style="margin: 1rem; padding: 1rem; background: #d4edda; border: 1px solid #c3e6cb; color: #155724; border-radius: 5px;">
                    <i class="fas fa-sign-out-alt"></i>
                    Has cerrado sesión correctamente.
                </div>
                <?php unset($_SESSION['logout_success']); ?>
            <?php endif; ?>
            
            <div style="padding: 2rem;">
                <div class="security-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Área Restringida:</strong> Este es un acceso administrativo. Todos los intentos de acceso son monitoreados.
                </div>
                
                <?php if (!empty($errors['general'])): ?>
                    <div class="alert alert-danger" style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($errors['general']); ?>
                    </div>
                <?php endif; ?>
                
                <?php 
                $currentAttempts = 0;
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $currentAttempts = checkLoginAttempts();
                }
                if ($currentAttempts >= 2 && $currentAttempts < 3): ?>
                    <div class="login-attempts-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Advertencia:</strong> Has fallado <?php echo $currentAttempts; ?> veces. 
                        Después de 3 intentos fallidos, el acceso será bloqueado por 30 minutos.
                    </div>
                <?php endif; ?>
                
                <div class="login-loading" id="loginLoading">
                    <div class="spinner"></div>
                    Verificando credenciales...
                </div>
                
                <form method="POST" class="login-form" id="adminLoginForm" novalidate>
                    <div class="form-group">
                        <label for="username"><i class="fas fa-user-shield"></i> Usuario o Email Administrativo</label>
                        <div class="input-with-icon">
                            <input type="text" id="username" name="username" required
                                   value="<?php echo htmlspecialchars($formData['username']); ?>"
                                   class="<?php echo isset($errors['username']) ? 'is-invalid' : ''; ?>"
                                   maxlength="100"
                                   autocomplete="username">
                        </div>
                        <?php if (isset($errors['username'])): ?>
                            <span class="error-message" style="color: #dc3545; font-size: 0.85em; margin-top: 5px; display: block;">
                                <i class="fas fa-exclamation-circle"></i>
                                <?php echo htmlspecialchars($errors['username']); ?>
                            </span>
                        <?php endif; ?>
                        <span class="error-message" id="usernameError" style="display: none; color: #dc3545; font-size: 0.85em; margin-top: 5px;">
                            <i class="fas fa-exclamation-circle"></i>
                            Usuario o email inválido
                        </span>
                    </div>
                    
                    <div class="form-group">
                        <label for="password"><i class="fas fa-key"></i> Contraseña Administrativa</label>
                        <div class="input-with-icon">
                            <input type="password" id="password" name="password" required
                                   class="<?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>"
                                   autocomplete="current-password">
                            <i class="fas fa-eye password-toggle" id="passwordToggle" title="Mostrar/Ocultar contraseña"></i>
                        </div>
                        <?php if (isset($errors['password'])): ?>
                            <span class="error-message" style="color: #dc3545; font-size: 0.85em; margin-top: 5px; display: block;">
                                <i class="fas fa-exclamation-circle"></i>
                                <?php echo htmlspecialchars($errors['password']); ?>
                            </span>
                        <?php endif; ?>
                        <span class="error-message" id="passwordError" style="display: none; color: #dc3545; font-size: 0.85em; margin-top: 5px;">
                            <i class="fas fa-exclamation-circle"></i>
                            La contraseña es obligatoria
                        </span>
                        <div class="caps-lock-warning" id="capsLockWarning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Mayús activado
                        </div>
                        <small class="form-text" style="margin-top: 5px; font-size: 0.85em;">
                            <a href="forgot-password.php" style="color: #3498db;">¿Olvidaste tu contraseña?</a>
                        </small>
                    </div>
                    
                    <div class="form-actions">
                        <div class="form-check">
                            <input type="checkbox" id="remember" name="remember" class="form-check-input">
                            <label for="remember" class="form-check-label">Recordar por 30 días</label>
                        </div>
                        
                        <button type="submit" class="login-btn" id="submitBtn">
                            <i class="fas fa-shield-alt"></i> Acceder
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="form-footer">
                <small style="color: #6c757d;">
                    <i class="fas fa-info-circle"></i>
                    Acceso exclusivo para administradores autorizados
                </small>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('adminLoginForm');
            const usernameInput = document.getElementById('username');
            const passwordInput = document.getElementById('password');
            const passwordToggle = document.getElementById('passwordToggle');
            const submitBtn = document.getElementById('submitBtn');
            const loginLoading = document.getElementById('loginLoading');
            
            // Toggle para mostrar/ocultar contraseña
            passwordToggle.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                if (type === 'text') {
                    passwordToggle.classList.remove('fa-eye');
                    passwordToggle.classList.add('fa-eye-slash');
                    passwordToggle.title = 'Ocultar contraseña';
                } else {
                    passwordToggle.classList.remove('fa-eye-slash');
                    passwordToggle.classList.add('fa-eye');
                    passwordToggle.title = 'Mostrar contraseña';
                }
            });
            
            // Detectar Caps Lock
            passwordInput.addEventListener('keyup', function(event) {
                const capsLockWarning = document.getElementById('capsLockWarning');
                if (event.getModifierState && event.getModifierState('CapsLock')) {
                    capsLockWarning.style.display = 'block';
                } else {
                    capsLockWarning.style.display = 'none';
                }
            });
            
            // Validación en tiempo real
            usernameInput.addEventListener('input', function() {
                validateUsername();
            });
            
            passwordInput.addEventListener('input', function() {
                validatePassword();
            });
            
            function validateUsername() {
                const username = usernameInput.value.trim();
                const errorElement = document.getElementById('usernameError');
                
                if (username.length > 0 && username.length < 3) {
                    usernameInput.classList.add('is-invalid');
                    errorElement.textContent = 'Mínimo 3 caracteres';
                    errorElement.style.display = 'block';
                    return false;
                } else if (username.length > 100) {
                    usernameInput.classList.add('is-invalid');
                    errorElement.textContent = 'Máximo 100 caracteres';
                    errorElement.style.display = 'block';
                    return false;
                } else {
                    usernameInput.classList.remove('is-invalid');
                    errorElement.style.display = 'none';
                    return true;
                }
            }
            
            function validatePassword() {
                const password = passwordInput.value;
                const errorElement = document.getElementById('passwordError');
                
                if (!password) {
                    passwordInput.classList.add('is-invalid');
                    errorElement.style.display = 'block';
                    return false;
                } else {
                    passwordInput.classList.remove('is-invalid');
                    errorElement.style.display = 'none';
                    return true;
                }
            }
            
            // Envío del formulario
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const usernameValid = validateUsername();
                const passwordValid = validatePassword();
                
                if (!usernameValid || !passwordValid) {
                    return;
                }
                
                // Mostrar loading
                submitBtn.style.display = 'none';
                loginLoading.style.display = 'block';
                form.style.pointerEvents = 'none';
                
                // Enviar formulario real
                const realForm = document.createElement('form');
                realForm.method = 'POST';
                realForm.style.display = 'none';
                
                const usernameField = document.createElement('input');
                usernameField.name = 'username';
                usernameField.value = usernameInput.value;
                realForm.appendChild(usernameField);
                
                const passwordField = document.createElement('input');
                passwordField.name = 'password';
                passwordField.value = passwordInput.value;
                realForm.appendChild(passwordField);
                
                const rememberField = document.createElement('input');
                rememberField.name = 'remember';
                rememberField.value = document.getElementById('remember').checked ? '1' : '';
                realForm.appendChild(rememberField);
                
                document.body.appendChild(realForm);
                realForm.submit();
            });
            
            // Auto-focus
            if (!usernameInput.value) {
                usernameInput.focus();
            } else {
                passwordInput.focus();
            }
            
            // Limpiar loading si hay error
            <?php if (!empty($errors)): ?>
                submitBtn.style.display = 'inline-block';
                loginLoading.style.display = 'none';
                form.style.pointerEvents = 'auto';
            <?php endif; ?>
            
            // Animación de entrada
            const container = document.querySelector('.login-container');
            container.style.opacity = '0';
            container.style.transform = 'translateY(30px)';
            
            setTimeout(() => {
                container.style.transition = 'all 0.6s ease';
                container.style.opacity = '1';
                container.style.transform = 'translateY(0)';
            }, 100);
        });
    </script>
</body>
</html>