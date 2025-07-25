<?php
session_start();

require_once 'config/database.php'; // Asumiendo que tienes este archivo

$db = new Database();
$conn = $db->getConnection();

$errors = [];
$formData = [
    'username' => '',
    'email' => ''
];

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'username' => trim($_POST['username']),
        'email' => trim($_POST['email'])
    ];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validaciones
    if (empty($formData['username'])) {
        $errors['username'] = 'El nombre de usuario es requerido';
    } elseif (strlen($formData['username']) < 4) {
        $errors['username'] = 'El usuario debe tener al menos 4 caracteres';
    }
    
    if (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'El email no es válido';
    }
    
    if (strlen($password) < 8) {
        $errors['password'] = 'La contraseña debe tener al menos 8 caracteres';
    } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errors['password'] = 'La contraseña debe contener al menos una mayúscula y un número';
    } elseif ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Las contraseñas no coinciden';
    }
    
    // Verificar si el usuario/email ya existe
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("SELECT * FROM usuarios WHERE username = :username OR email = :email LIMIT 1");
            $stmt->execute([
                ':username' => $formData['username'],
                ':email' => $formData['email']
            ]);
            
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($user['username'] === $formData['username']) {
                    $errors['username'] = 'Este usuario ya existe';
                }
                if ($user['email'] === $formData['email']) {
                    $errors['email'] = 'Este email ya está registrado';
                }
            }
        } catch (PDOException $e) {
            $errors['general'] = 'Error en el sistema. Por favor intente más tarde.';
        }
    }
    
    // Si no hay errores, registrar al usuario
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO usuarios 
                (username, email, password, role, created_at) 
                VALUES (:username, :email, :password, 'user', NOW())
            ");
            
            $stmt->execute([
                ':username' => $formData['username'],
                ':email' => $formData['email'],
                ':password' => password_hash($password, PASSWORD_BCRYPT)
            ]);
            
            $_SESSION['registration_success'] = true;
            header('Location: login.php');
            exit();
        } catch (PDOException $e) {
            $errors['general'] = 'Error al registrar el usuario. Por favor intente nuevamente.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Panel de Administración</title>
    <link rel="stylesheet" href="assets/css/login.css"> <!-- Reutilizamos los estilos del login -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <img src="assets/images/logo.png" alt="Logo" class="logo">
            <h1>Crear Cuenta</h1>
            <p>Registro para nuevo personal administrativo</p>
        </div>
        
        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($errors['general']); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="login-form" novalidate>
            <div class="form-group">
                <label for="username"><i class="fas fa-user"></i> Nombre de Usuario</label>
                <input type="text" id="username" name="username" required
                       value="<?php echo htmlspecialchars($formData['username']); ?>"
                       class="<?php echo isset($errors['username']) ? 'is-invalid' : ''; ?>">
                <?php if (isset($errors['username'])): ?>
                    <span class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($errors['username']); ?>
                    </span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Correo Electrónico</label>
                <input type="email" id="email" name="email" required
                       value="<?php echo htmlspecialchars($formData['email']); ?>"
                       class="<?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>">
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
                <?php if (isset($errors['password'])): ?>
                    <span class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($errors['password']); ?>
                    </span>
                <?php endif; ?>
                <small class="form-text">Mínimo 8 caracteres con al menos una mayúscula y un número</small>
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
            </div>
            
            <div class="form-actions">
                <div class="form-check">
                    <input type="checkbox" id="terms" name="terms" required
                           class="form-check-input <?php echo isset($errors['terms']) ? 'is-invalid' : ''; ?>">
                    <label for="terms" class="form-check-label">
                        Acepto los <a href="#" class="terms-link">términos y condiciones</a>
                    </label>
                    <?php if (isset($errors['terms'])): ?>
                        <span class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo htmlspecialchars($errors['terms']); ?>
                        </span>
                    <?php endif; ?>
                </div>
                
                <button type="submit" class="login-btn">
                    <i class="fas fa-user-plus"></i> Registrarse
                </button>
            </div>
            
            <div class="form-footer">
                <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a></p>
            </div>
        </form>
    </div>
    
    <script src="assets/js/register.js"></script>
</body>
</html>
