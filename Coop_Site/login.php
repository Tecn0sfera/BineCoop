<?php
session_start();

// Inicializar variables
$errors = [];
$formData = [
    'email' => '',
    'password' => ''
];

// Función para validar email
function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Función para limpiar datos
function limpiarDato($dato) {
    return htmlspecialchars(trim($dato), ENT_QUOTES, 'UTF-8');
}

// Control de intentos fallidos
function checkLoginAttempts() {
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['last_attempt_time'] = time();
    }
    
    // Si han pasado más de 15 minutos, resetear intentos
    if (time() - $_SESSION['last_attempt_time'] > 900) {
        $_SESSION['login_attempts'] = 0;
    }
    
    return $_SESSION['login_attempts'];
}

function incrementLoginAttempts() {
    $_SESSION['login_attempts']++;
    $_SESSION['last_attempt_time'] = time();
}

function getTimeUntilRetry() {
    $timeElapsed = time() - $_SESSION['last_attempt_time'];
    $timeRemaining = 900 - $timeElapsed; // 15 minutos = 900 segundos
    return max(0, $timeRemaining);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar intentos de login
    $attempts = checkLoginAttempts();
    
    if ($attempts >= 5) {
        $timeRemaining = getTimeUntilRetry();
        if ($timeRemaining > 0) {
            $minutes = ceil($timeRemaining / 60);
            $errors['general'] = "Demasiados intentos fallidos. Inténtalo de nuevo en $minutes minutos.";
        } else {
            $_SESSION['login_attempts'] = 0;
        }
    }
    
    if (empty($errors)) {
        // Limpiar y almacenar datos del formulario
        $formData['email'] = limpiarDato($_POST['email'] ?? '');
        $formData['password'] = $_POST['password'] ?? '';
        
        // Validar email
        if (empty($formData['email'])) {
            $errors['email'] = 'El email es obligatorio';
        } elseif (!validarEmail($formData['email'])) {
            $errors['email'] = 'Por favor ingresa un email válido';
        } elseif (strlen($formData['email']) > 255) {
            $errors['email'] = 'El email no puede exceder 255 caracteres';
        }
        
        // Validar contraseña
        if (empty($formData['password'])) {
            $errors['password'] = 'La contraseña es obligatoria';
        } elseif (strlen($formData['password']) < 4) {
            $errors['password'] = 'La contraseña es demasiado corta';
        }
        
        // Si no hay errores de validación, proceder con el login
        if (empty($errors)) {
            $url = 'https://tectesting.fwh.is/auth/user_auth.php';
            $data = [
                'email' => $formData['email'],
                'password' => $formData['password']
            ];
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout de 10 segundos
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json',
                'User-Agent: CooperativaApp/1.0'
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($response === false || !empty($curlError)) {
                $errors['general'] = 'Error de conexión. Por favor, inténtalo más tarde.';
            } else {
                $result = json_decode($response, true);
                
                if ($httpCode === 200 && isset($result['success']) && $result['success']) {
                    // Login exitoso
                    $_SESSION['user_token'] = $result['token'];
                    $_SESSION['user'] = $result['user'];
                    $_SESSION['login_attempts'] = 0; // Resetear intentos
                    
                    // Regenerar ID de sesión por seguridad
                    session_regenerate_id(true);
                    
                    // Redirección con verificación
                    $redirectUrl = $_SESSION['redirect_after_login'] ?? 'dashboard.php';
                    unset($_SESSION['redirect_after_login']);
                    
                    header("Location: $redirectUrl");
                    exit;
                } else {
                    // Login fallido
                    incrementLoginAttempts();
                    
                    if (isset($result['error'])) {
                        $errors['general'] = $result['error'];
                    } elseif ($httpCode === 401) {
                        $errors['general'] = 'Email o contraseña incorrectos';
                    } elseif ($httpCode === 403) {
                        $errors['general'] = 'Tu cuenta está pendiente de aprobación';
                    } else {
                        $errors['general'] = 'Error en el servidor. Inténtalo más tarde.';
                    }
                    
                    $remainingAttempts = 5 - $_SESSION['login_attempts'];
                    if ($remainingAttempts > 0 && $remainingAttempts <= 2) {
                        $errors['general'] .= " (Te quedan $remainingAttempts intentos)";
                    }
                }
            }
        }
    }
}

// Limpiar contraseña del formulario por seguridad
$formData['password'] = '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Cooperativa</title>
    <link rel="stylesheet" href="https://tectesting.fwh.is/assets/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .login-attempts-warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .login-loading {
            display: none;
            text-align: center;
            padding: 10px;
        }
        .login-loading .spinner {
            border: 2px solid #f3f3f3;
            border-top: 2px solid #007bff;
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
        }
        .input-with-icon {
            position: relative;
        }
        .caps-lock-warning {
            display: none;
            color: #ffc107;
            font-size: 0.85em;
            margin-top: 5px;
        }
        .remember-me {
            display: flex;
            align-items: center;
            margin: 15px 0;
            font-size: 0.9em;
        }
        .remember-me input[type="checkbox"] {
            margin-right: 8px;
        }
        body {
            background-image: url('./we.webp');
            background-size: cover;
            background-repeat: repeat;
        } 
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Iniciar Sesión</h1>
            <p>Sistema de Cooperativa</p>
        </div>
        
        <?php if (isset($_SESSION['registration_success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                ¡Registro exitoso! Tu cuenta está pendiente de aprobación.
            </div>
            <?php unset($_SESSION['registration_success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['logout_success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-sign-out-alt"></i>
                Has cerrado sesión correctamente.
            </div>
            <?php unset($_SESSION['logout_success']); ?>
        <?php endif; ?>
        
        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($errors['general']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= 3 && $_SESSION['login_attempts'] < 5): ?>
            <div class="login-attempts-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Advertencia:</strong> Has fallado <?php echo $_SESSION['login_attempts']; ?> veces. 
                Después de 5 intentos fallidos, tu acceso será bloqueado temporalmente.
            </div>
        <?php endif; ?>
        
        <div class="login-loading" id="loginLoading">
            <div class="spinner"></div>
            Iniciando sesión...
        </div>
        
        <form method="POST" class="login-form" id="loginForm" novalidate>
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email</label>
                <div class="input-with-icon">
                    <input type="email" id="email" name="email" required
                           value="<?php echo htmlspecialchars($formData['email']); ?>"
                           class="<?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>"
                           maxlength="255"
                           autocomplete="email">
                </div>
                <?php if (isset($errors['email'])): ?>
                    <span class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($errors['email']); ?>
                    </span>
                <?php endif; ?>
                <span class="error-message" id="emailError" style="display: none;">
                    <i class="fas fa-exclamation-circle"></i>
                    Por favor ingresa un email válido
                </span>
            </div>
            
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Contraseña</label>
                <div class="input-with-icon">
                    <input type="password" id="password" name="password" required
                           class="<?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>"
                           autocomplete="current-password">
                    <i class="fas fa-eye password-toggle" id="passwordToggle" title="Mostrar/Ocultar contraseña"></i>
                </div>
                <?php if (isset($errors['password'])): ?>
                    <span class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($errors['password']); ?>
                    </span>
                <?php endif; ?>
                <span class="error-message" id="passwordError" style="display: none;">
                    <i class="fas fa-exclamation-circle"></i>
                    La contraseña es obligatoria
                </span>
                <div class="caps-lock-warning" id="capsLockWarning">
                    <i class="fas fa-exclamation-triangle"></i>
                    Mayús activado
                </div>
                <small class="form-text">
                    <a href="forgot-password.php">¿Olvidaste tu contraseña?</a>
                </small>
            </div>
            
            <div class="remember-me">
                <input type="checkbox" id="remember_me" name="remember_me">
                <label for="remember_me">Recordar mis datos</label>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="login-btn" id="submitBtn">
                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                </button>
            </div>
            
            <div class="form-footer">
                <p>¿No tienes una cuenta? <a href="register.php">Regístrate aquí</a></p>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const passwordToggle = document.getElementById('passwordToggle');
            const submitBtn = document.getElementById('submitBtn');
            const loginLoading = document.getElementById('loginLoading');
            const rememberMeCheckbox = document.getElementById('remember_me');
            
            // Cargar datos recordados
            loadRememberedData();
            
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
            
            // Validación en tiempo real del email
            emailInput.addEventListener('input', function() {
                validateEmail();
            });
            
            // Validación en tiempo real de la contraseña
            passwordInput.addEventListener('input', function() {
                validatePassword();
            });
            
            function validateEmail() {
                const email = emailInput.value.trim();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                const errorElement = document.getElementById('emailError');
                
                if (email && !emailRegex.test(email)) {
                    emailInput.classList.add('is-invalid');
                    errorElement.style.display = 'block';
                    return false;
                } else {
                    emailInput.classList.remove('is-invalid');
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
            
            // Funciones para recordar datos
            function saveRememberedData() {
                if (rememberMeCheckbox.checked) {
                    localStorage.setItem('rememberedEmail', emailInput.value);
                    localStorage.setItem('rememberMe', 'true');
                } else {
                    localStorage.removeItem('rememberedEmail');
                    localStorage.removeItem('rememberMe');
                }
            }
            
            function loadRememberedData() {
                const remembered = localStorage.getItem('rememberMe');
                const rememberedEmail = localStorage.getItem('rememberedEmail');
                
                if (remembered === 'true' && rememberedEmail) {
                    emailInput.value = rememberedEmail;
                    rememberMeCheckbox.checked = true;
                }
            }
            
            // Validación y envío del formulario
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const emailValid = validateEmail();
                const passwordValid = validatePassword();
                
                if (!emailValid || !passwordValid) {
                    return;
                }
                
                // Guardar datos si está marcado "recordar"
                saveRememberedData();
                
                // Mostrar loading
                submitBtn.style.display = 'none';
                loginLoading.style.display = 'block';
                
                // Enviar formulario
                form.style.pointerEvents = 'none'; // Prevenir múltiples envíos
                
                // Crear un nuevo elemento form para envío real
                const realForm = document.createElement('form');
                realForm.method = 'POST';
                realForm.style.display = 'none';
                
                // Copiar campos
                const emailField = document.createElement('input');
                emailField.name = 'email';
                emailField.value = emailInput.value;
                realForm.appendChild(emailField);
                
                const passwordField = document.createElement('input');
                passwordField.name = 'password';
                passwordField.value = passwordInput.value;
                realForm.appendChild(passwordField);
                
                document.body.appendChild(realForm);
                realForm.submit();
            });
            
            // Auto-focus en el primer campo vacío
            if (!emailInput.value) {
                emailInput.focus();
            } else if (!passwordInput.value) {
                passwordInput.focus();
            }
            
            // Limpiar loading si hay error
            <?php if (!empty($errors)): ?>
                submitBtn.style.display = 'block';
                loginLoading.style.display = 'none';
                form.style.pointerEvents = 'auto';
            <?php endif; ?>
        });
    </script>
</body>
</html>