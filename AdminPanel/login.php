<?php
// Esto debe ser lo PRIMERO en el archivo
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        
        if (empty($username) || empty($password)) {
            throw new Exception("Usuario y contraseña son requeridos");
        }

        $stmt = $conn->prepare("SELECT id, username, password, role FROM usuarios WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password'])) {
            throw new Exception("Credenciales incorrectas");
        }

        // Configurar sesión
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
            'logged_in' => true,
            'last_activity' => time()
        ];

        // Redirigir al index.php en la raíz
        header("Location: /dashboard.php");
        exit();
        
    } catch (Exception $e) {
        $_SESSION['login_error'] = $e->getMessage();
        header("Location: login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Panel de Administración</title>
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <img src="assets/images/logo.png" alt="Logo" class="logo">
            <h1>Iniciar Sesión</h1>
            <p>Sistema de Administración</p>
        </div>
        
        <?php if (isset($_SESSION['registration_success'])): ?>
            <div class="alert alert-success">
                ¡Registro exitoso! Por favor inicie sesión.
            </div>
            <?php unset($_SESSION['registration_success']); ?>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="login-form" novalidate>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <div class="form-group">
        <label for="username"><i class="fas fa-user"></i> Usuario o Email</label>
        <input type="text" id="username" name="username" required>
    </div>
    
    <div class="form-group">
        <label for="password"><i class="fas fa-lock"></i> Contraseña</label>
        <input type="password" id="password" name="password" required>
        <small class="form-text">
            <a href="forgot-password.php">¿Olvidaste tu contraseña?</a>
        </small>
    </div>
    
    <!-- Checkbox y botón dentro del formulario -->
    <div class="form-actions">
        <div class="form-check">
            <input type="checkbox" id="remember" name="remember" class="form-check-input">
            <label for="remember" class="form-check-label">Recordar sesión</label>
        </div>
        
        <button type="submit" class="login-btn">
            <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
        </button>
    </div>
    
    <div class="form-footer">
        <p>¿No tienes una cuenta? <a href="register.php">Regístrate aquí</a></p>
    </div>
</form>
    </div>
</body>
</html>
