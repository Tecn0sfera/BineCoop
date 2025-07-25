<?php
session_start();


// Simulación de base de datos de usuarios (en producción usaría una base de datos real)
$usuariosValidos = [
    'admin' => [
        'password' => password_hash('Admin1234', PASSWORD_BCRYPT),
        'nombre' => 'Administrador Principal',
        'rol' => 'admin'
    ],
    'gestor' => [
        'password' => password_hash('Gestor5678', PASSWORD_BCRYPT),
        'nombre' => 'Gestor de Viviendas',
        'rol' => 'gestor'
    ]
];

// Procesar el formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (isset($usuariosValidos[$username]) && password_verify($password, $usuariosValidos[$username]['password'])) {
        // Autenticación exitosa
        $_SESSION['usuario'] = [
            'username' => $username,
            'nombre' => $usuariosValidos[$username]['nombre'],
            'rol' => $usuariosValidos[$username]['rol'],
            'logged_in' => true
        ];
        
        // Redirigir al dashboard
        header('Location: index.php');
        exit();
    } else {
        $error = "Usuario o contraseña incorrectos";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Cooperativa de Vivienda</title>
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <img src="assets/images/logo-cooperativa.png" alt="Logo Cooperativa" class="logo">
            <h1>Sistema de Gestión</h1>
            <p>Cooperativa de Vivienda</p>
        </div>
        
        <form method="POST" class="login-form">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="username"><i class="fas fa-user"></i> Usuario</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Contraseña</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
            </button>
        </form>
        
        <div class="login-footer">
            <p>¿Problemas para acceder? <a href="#">Contactar al administrador</a></p>
        </div>
    </div>
</body>
</html>