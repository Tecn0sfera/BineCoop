<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $url = 'https://tectesting.fwh.is/api/auth/login';
    $data = [
        'email' => $_POST['email'],
        'password' => $_POST['password']
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $result = json_decode($response, true);
    
    if ($httpCode === 200 && $result['success']) {
        $_SESSION['user_token'] = $result['token'];
        $_SESSION['user'] = $result['user'];
        header("Location: success.php");
        exit;
    } else {
        $error = $result['error'] ?? 'Error desconocido';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Cooperativa</title>
    <link rel="stylesheet" href="https://tectesting.fwh.is/assets/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <img src="assets/images/logo.png" alt="Logo" class="logo">
            <h1>Iniciar Sesión</h1>
            <p>Sistema de Cooperativa</p>
        </div>
        
        <?php if (isset($_SESSION['registration_success'])): ?>
            <div class="alert alert-success">
                ¡Registro exitoso! Tu cuenta está pendiente de aprobación.
            </div>
            <?php unset($_SESSION['registration_success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['login_error'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($_SESSION['login_error']); ?>
            </div>
            <?php unset($_SESSION['login_error']); ?>
        <?php endif; ?>
        
        <form method="POST" class="login-form" novalidate>
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Contraseña</label>
                <input type="password" id="password" name="password" required>
                <small class="form-text">
                    <a href="forgot-password.php">¿Olvidaste tu contraseña?</a>
                </small>
            </div>
            
            <div class="form-actions">
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
