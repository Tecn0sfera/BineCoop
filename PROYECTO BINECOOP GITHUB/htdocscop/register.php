<?php
session_start();

// Inicializar variables
$errors = [];
$formData = [
    'nombre' => '',
    'email' => '',
    'password' => '',
    'confirm_password' => ''
];

// Función para validar email
function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Función para validar contraseña
function validarPassword($password) {
    // Mínimo 8 caracteres, al menos una mayúscula, una minúscula y un número
    return strlen($password) >= 8 && 
           preg_match('/[A-Z]/', $password) && 
           preg_match('/[a-z]/', $password) && 
           preg_match('/[0-9]/', $password);
}

// Función para limpiar datos
function limpiarDato($dato) {
    return htmlspecialchars(trim($dato), ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Limpiar y almacenar datos del formulario
    $formData['nombre'] = limpiarDato($_POST['nombre'] ?? '');
    $formData['email'] = limpiarDato($_POST['email'] ?? '');
    $formData['password'] = $_POST['password'] ?? '';
    $formData['confirm_password'] = $_POST['confirm_password'] ?? '';
    
    // Validar nombre
    if (empty($formData['nombre'])) {
        $errors['nombre'] = 'El nombre es obligatorio';
    } elseif (strlen($formData['nombre']) < 2) {
        $errors['nombre'] = 'El nombre debe tener al menos 2 caracteres';
    } elseif (strlen($formData['nombre']) > 100) {
        $errors['nombre'] = 'El nombre no puede exceder 100 caracteres';
    } elseif (!preg_match('/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]+$/', $formData['nombre'])) {
        $errors['nombre'] = 'El nombre solo puede contener letras y espacios';
    }
    
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
    } elseif (strlen($formData['password']) < 8) {
        $errors['password'] = 'La contraseña debe tener al menos 8 caracteres';
    } elseif (!preg_match('/[A-Z]/', $formData['password'])) {
        $errors['password'] = 'La contraseña debe contener al menos una mayúscula';
    } elseif (!preg_match('/[a-z]/', $formData['password'])) {
        $errors['password'] = 'La contraseña debe contener al menos una minúscula';
    } elseif (!preg_match('/[0-9]/', $formData['password'])) {
        $errors['password'] = 'La contraseña debe contener al menos un número';
    }
    
    // Validar confirmación de contraseña
    if (empty($formData['confirm_password'])) {
        $errors['confirm_password'] = 'Debes confirmar la contraseña';
    } elseif ($formData['password'] !== $formData['confirm_password']) {
        $errors['confirm_password'] = 'Las contraseñas no coinciden';
    }
    
    // Si no hay errores, proceder con el registro
    if (empty($errors)) {
        // Usar include directo en lugar de cURL para evitar problemas de redirección
        // Buscar el archivo de API de forma relativa
        $apiFilePath = null;
        
        // Obtener el directorio actual del script
        $currentDir = __DIR__;
        
        // Función auxiliar para buscar archivo recursivamente desde el directorio actual
        function findRegisterFile($dir, $maxDepth = 3, $currentDepth = 0) {
            if ($currentDepth >= $maxDepth || !is_dir($dir)) {
                return null;
            }
            
            $items = @scandir($dir);
            if (!$items) {
                return null;
            }
            
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }
                
                $path = $dir . DIRECTORY_SEPARATOR . $item;
                
                // Si es el archivo que buscamos y está en la estructura correcta
                if (is_file($path) && $item === 'register.php') {
                    $parent = dirname($path);
                    $grandparent = dirname($parent);
                    if (basename($parent) === 'visitantes' && basename($grandparent) === 'api') {
                        return $path;
                    }
                }
                
                // Buscar recursivamente en subdirectorios
                if (is_dir($path)) {
                    $found = findRegisterFile($path, $maxDepth, $currentDepth + 1);
                    if ($found) {
                        return $found;
                    }
                }
            }
            
            return null;
        }
        
        // Intentar múltiples rutas posibles
        // Buscar de forma relativa desde el directorio del script actual
        $possiblePaths = [
            // Ruta 1: Relativa desde el directorio actual del script (./api/visitantes/register.php)
            $currentDir . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR . 'visitantes' . DIRECTORY_SEPARATOR . 'register.php',
            // Ruta 2: Relativa desde el directorio actual usando ./ (explícito)
            $currentDir . DIRECTORY_SEPARATOR . '.' . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR . 'visitantes' . DIRECTORY_SEPARATOR . 'register.php',
            // Ruta 3: Subir un nivel y buscar api (../api/visitantes/register.php)
            $currentDir . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR . 'visitantes' . DIRECTORY_SEPARATOR . 'register.php',
            // Ruta 4: Subir dos niveles y buscar api (../../api/visitantes/register.php)
            $currentDir . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR . 'visitantes' . DIRECTORY_SEPARATOR . 'register.php',
            // Ruta 5: Usando barras normales desde current dir (./api/visitantes/register.php)
            str_replace('\\', '/', $currentDir) . '/api/visitantes/register.php',
            // Ruta 6: Usando barras normales subiendo un nivel (../api/visitantes/register.php)
            str_replace('\\', '/', $currentDir) . '/../api/visitantes/register.php',
            // Ruta 7: Usando realpath con ruta relativa
            realpath($currentDir . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR . 'visitantes' . DIRECTORY_SEPARATOR . 'register.php'),
            // Ruta 8: Usando realpath subiendo un nivel
            realpath($currentDir . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR . 'visitantes' . DIRECTORY_SEPARATOR . 'register.php'),
        ];
        
        // Si no se encuentra en rutas directas, buscar recursivamente
        $recursiveSearch = null;
        
        // Normalizar todas las rutas y buscar el archivo
        foreach ($possiblePaths as $idx => $path) {
            if (!$path) {
                continue;
            }
            
            // Normalizar la ruta
            $normalizedPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
            
            // Intentar con realpath si es posible
            $realPath = realpath($normalizedPath);
            if ($realPath && file_exists($realPath)) {
                $apiFilePath = $realPath;
                error_log("Archivo de API encontrado en ruta " . ($idx + 1) . ": " . $apiFilePath);
                break;
            }
            
            // Si realpath falla, intentar con la ruta normalizada directamente
            if (file_exists($normalizedPath)) {
                $apiFilePath = $normalizedPath;
                error_log("Archivo de API encontrado en ruta " . ($idx + 1) . " (sin realpath): " . $apiFilePath);
                break;
            }
        }
        
        // Si no se encontró en rutas directas, buscar recursivamente desde el directorio actual
        if (!$apiFilePath) {
            error_log("No se encontró en rutas directas, buscando recursivamente desde el directorio actual...");
            $recursiveSearch = findRegisterFile($currentDir);
            if ($recursiveSearch && file_exists($recursiveSearch)) {
                $apiFilePath = $recursiveSearch;
                error_log("Archivo de API encontrado mediante búsqueda recursiva: " . $apiFilePath);
            }
        }
        
        if (!$apiFilePath || !file_exists($apiFilePath)) {
            error_log("Error: Archivo de API no encontrado.");
            error_log("Directorio actual (__DIR__): " . $currentDir);
            error_log("Rutas intentadas:");
            foreach ($possiblePaths as $idx => $path) {
                $normalized = $path ? str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path) : 'null';
                $exists = $path && file_exists($normalized) ? 'EXISTE' : 'NO EXISTE';
                error_log("  Ruta " . ($idx + 1) . ": " . $normalized . " - " . $exists);
            }
            
            $errors['general'] = 'Error de configuración del servidor. El archivo de registro no se encuentra. Contacta al administrador.';
        } else {
            // El archivo existe, proceder con el registro
            // Crear el JSON con los datos del formulario
            $jsonData = json_encode([
                'nombre' => $formData['nombre'],
                'email' => $formData['email'],
                'password' => $formData['password']
            ]);
            
            // Guardar los datos JSON en GLOBALS para que el API los pueda leer
            $GLOBALS['_POST_JSON_DATA'] = $jsonData;
            
            // Guardar el método original
            $originalMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            $_SERVER['REQUEST_METHOD'] = 'POST';
            
            // Incluir el archivo API y capturar su salida
            try {
                // Limpiar cualquier salida previa
                if (ob_get_level()) {
                    ob_clean();
                }
                
                // Capturar la salida del API
                ob_start();
                include $apiFilePath;
                $response = ob_get_clean();
                
                // Restaurar método original
                $_SERVER['REQUEST_METHOD'] = $originalMethod;
                
                // Limpiar GLOBALS
                unset($GLOBALS['_POST_JSON_DATA']);
                
                // Obtener el código HTTP
                $httpCode = http_response_code();
                
                // Intentar decodificar la respuesta JSON
                $result = json_decode($response, true);
                
                if ($result && isset($result['success']) && $result['success']) {
                    // Mostrar mensaje de éxito en lugar de redirigir
                    if (isset($result['message'])) {
                        $_SESSION['registration_success_message'] = $result['message'];
                    } else {
                        $_SESSION['registration_success_message'] = 'Registro exitoso. Tu cuenta está pendiente de aprobación administrativa.';
                    }
                    $_SESSION['registration_success'] = true;

                    // Recargar la página para mostrar el mensaje (manteniendo parámetros como ?i=1)
                    $redirectTarget = $_SERVER['REQUEST_URI'] ?? ($_SERVER['PHP_SELF'] ?? 'register.php');
                    if (empty($redirectTarget)) {
                        $redirectTarget = 'register.php';
                    }
                    header("Location: " . $redirectTarget);
                    exit;
                } else {
                    // Manejar errores
                    if (isset($result['error'])) {
                        $errorMessage = limpiarDato($result['error']);
                    } else {
                        $errorMessage = 'Error en el registro. Inténtalo más tarde.';
                        error_log("Error en registro - HTTP Code: $httpCode, Response: " . substr($response, 0, 500));
                    }
                    
                    if (isset($result['validation_errors'])) {
                        $errorMessage .= ' ' . implode(', ', array_values($result['validation_errors']));
                    }
                    
                    $errors['general'] = $errorMessage;
                }
                
            } catch (Exception $e) {
                // Limpiar buffers
                while (ob_get_level()) {
                    ob_end_clean();
                }
                // Restaurar método original
                $_SERVER['REQUEST_METHOD'] = $originalMethod;
                // Limpiar GLOBALS
                unset($GLOBALS['_POST_JSON_DATA']);
                
                error_log("Excepción al incluir API de registro: " . $e->getMessage());
                error_log("Trace: " . $e->getTraceAsString());
                $errors['general'] = 'Error al procesar el registro. Inténtalo más tarde.';
            }
        }
    }
}
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Cooperativa</title>
    <link rel="stylesheet" href="https://tectesting.fwh.is/assets/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .password-requirements {
            font-size: 0.85em;
            color: #666;
            margin-top: 5px;
        }
        .password-requirements ul {
            margin: 0;
            padding-left: 20px;
        }
        .password-requirements li {
            margin-bottom: 2px;
        }
        .password-requirements li.valid {
            color: #28a745;
        }
        .password-requirements li.invalid {
            color: #dc3545;
        }
        .password-strength {
            height: 4px;
            background-color: #e9ecef;
            border-radius: 2px;
            margin-top: 5px;
            overflow: hidden;
        }
        .password-strength-bar {
            height: 100%;
            transition: width 0.3s ease;
            border-radius: 2px;
        }
        .strength-weak { background-color: #dc3545; }
        .strength-medium { background-color: #ffc107; }
        .strength-strong { background-color: #28a745; }
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
            <h1>Registro de Visitante</h1>
            <p>Tu cuenta requerirá aprobación administrativa</p>
        </div>
        
        <?php if (isset($_SESSION['registration_success_message'])): ?>
            <div class="alert alert-warning" style="background-color: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <i class="fas fa-exclamation-triangle"></i>
                <?php 
                echo htmlspecialchars($_SESSION['registration_success_message']); 
                unset($_SESSION['registration_success_message']); // Limpiar el mensaje después de mostrarlo
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($errors['general']); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="login-form" id="registerForm" novalidate>
            <div class="form-group">
                <label for="nombre"><i class="fas fa-user"></i> Nombre Completo</label>
                <input type="text" id="nombre" name="nombre" required
                       value="<?php echo htmlspecialchars($formData['nombre']); ?>"
                       class="<?php echo isset($errors['nombre']) ? 'is-invalid' : ''; ?>"
                       maxlength="100">
                <?php if (isset($errors['nombre'])): ?>
                    <span class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($errors['nombre']); ?>
                    </span>
                <?php endif; ?>
                <small class="form-text">Solo letras y espacios, 2-100 caracteres</small>
            </div>
            
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Correo Electrónico</label>
                <input type="email" id="email" name="email" required
                       value="<?php echo htmlspecialchars($formData['email']); ?>"
                       class="<?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>"
                       maxlength="255">
                <?php if (isset($errors['email'])): ?>
                    <span class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($errors['email']); ?>
                    </span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Contraseña</label>
                <input type="password" id="password" name="password" required
                       class="<?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>">
                <div class="password-strength">
                    <div class="password-strength-bar" id="passwordStrengthBar"></div>
                </div>
                <?php if (isset($errors['password'])): ?>
                    <span class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($errors['password']); ?>
                    </span>
                <?php endif; ?>
                <div class="password-requirements" id="passwordRequirements">
                    <small>La contraseña debe contener:</small>
                    <ul>
                        <li id="length">Al menos 8 caracteres</li>
                        <li id="uppercase">Una letra mayúscula</li>
                        <li id="lowercase">Una letra minúscula</li>
                        <li id="number">Un número</li>
                    </ul>
                </div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password"><i class="fas fa-lock"></i> Confirmar Contraseña</label>
                <input type="password" id="confirm_password" name="confirm_password" required
                       class="<?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>">
                <?php if (isset($errors['confirm_password'])): ?>
                    <span class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($errors['confirm_password']); ?>
                    </span>
                <?php endif; ?>
                <span class="error-message" id="confirmPasswordError" style="display: none;">
                    <i class="fas fa-exclamation-circle"></i>
                    Las contraseñas no coinciden
                </span>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="login-btn" id="submitBtn">
                    <i class="fas fa-user-plus"></i> Registrarse
                </button>
            </div>
            
            <div class="form-footer">
                <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a></p>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registerForm');
            const nombreInput = document.getElementById('nombre');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const submitBtn = document.getElementById('submitBtn');
            
            // Validación en tiempo real para el nombre
            nombreInput.addEventListener('input', function() {
                validateName();
            });
            
            // Validación en tiempo real para el email
            emailInput.addEventListener('input', function() {
                validateEmail();
            });
            
            // Validación en tiempo real para la contraseña
            passwordInput.addEventListener('input', function() {
                validatePassword();
                checkPasswordMatch();
            });
            
            // Validación en tiempo real para confirmar contraseña
            confirmPasswordInput.addEventListener('input', function() {
                checkPasswordMatch();
            });
            
            function validateName() {
                const name = nombreInput.value.trim();
                const nameRegex = /^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]+$/;
                
                if (name.length < 2) {
                    setFieldError(nombreInput, 'El nombre debe tener al menos 2 caracteres');
                    return false;
                } else if (name.length > 100) {
                    setFieldError(nombreInput, 'El nombre no puede exceder 100 caracteres');
                    return false;
                } else if (!nameRegex.test(name)) {
                    setFieldError(nombreInput, 'El nombre solo puede contener letras y espacios');
                    return false;
                } else {
                    clearFieldError(nombreInput);
                    return true;
                }
            }
            
            function validateEmail() {
                const email = emailInput.value.trim();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                
                if (!emailRegex.test(email)) {
                    setFieldError(emailInput, 'Por favor ingresa un email válido');
                    return false;
                } else {
                    clearFieldError(emailInput);
                    return true;
                }
            }
            
            function validatePassword() {
                const password = passwordInput.value;
                const requirements = {
                    length: password.length >= 8,
                    uppercase: /[A-Z]/.test(password),
                    lowercase: /[a-z]/.test(password),
                    number: /[0-9]/.test(password)
                };
                
                // Actualizar indicadores visuales
                Object.keys(requirements).forEach(req => {
                    const element = document.getElementById(req);
                    if (requirements[req]) {
                        element.classList.add('valid');
                        element.classList.remove('invalid');
                    } else {
                        element.classList.add('invalid');
                        element.classList.remove('valid');
                    }
                });
                
                // Calcular fuerza de la contraseña
                const validRequirements = Object.values(requirements).filter(Boolean).length;
                const strengthBar = document.getElementById('passwordStrengthBar');
                
                if (validRequirements === 0) {
                    strengthBar.style.width = '0%';
                } else if (validRequirements < 3) {
                    strengthBar.style.width = '33%';
                    strengthBar.className = 'password-strength-bar strength-weak';
                } else if (validRequirements < 4) {
                    strengthBar.style.width = '66%';
                    strengthBar.className = 'password-strength-bar strength-medium';
                } else {
                    strengthBar.style.width = '100%';
                    strengthBar.className = 'password-strength-bar strength-strong';
                }
                
                return Object.values(requirements).every(Boolean);
            }
            
            function checkPasswordMatch() {
                const password = passwordInput.value;
                const confirmPassword = confirmPasswordInput.value;
                const errorElement = document.getElementById('confirmPasswordError');
                
                if (confirmPassword && password !== confirmPassword) {
                    confirmPasswordInput.classList.add('is-invalid');
                    errorElement.style.display = 'block';
                    return false;
                } else {
                    confirmPasswordInput.classList.remove('is-invalid');
                    errorElement.style.display = 'none';
                    return true;
                }
            }
            
            function setFieldError(field, message) {
                field.classList.add('is-invalid');
                // Aquí puedes agregar lógica para mostrar el mensaje de error
            }
            
            function clearFieldError(field) {
                field.classList.remove('is-invalid');
                // Aquí puedes agregar lógica para ocultar el mensaje de error
            }
            
            // Validación antes de enviar el formulario
            form.addEventListener('submit', function(e) {
                const nameValid = validateName();
                const emailValid = validateEmail();
                const passwordValid = validatePassword();
                const passwordMatchValid = checkPasswordMatch();
                
                if (!nameValid || !emailValid || !passwordValid || !passwordMatchValid) {
                    e.preventDefault();
                    alert('Por favor corrige los errores en el formulario');
                }
            });
        });
    </script>
</body>
</html>