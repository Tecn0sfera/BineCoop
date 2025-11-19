<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Denegado</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <main class="main-content" style="text-align: center; padding: 50px;">
            <i class="fas fa-ban" style="font-size: 72px; color: #e74c3c; margin-bottom: 20px;"></i>
            <h1>Acceso Denegado</h1>
            <p>No tienes permisos suficientes para acceder a esta sección.</p>
            <a href="index.php" class="btn btn-primary">Volver al Dashboard</a>
        </main>
    </div>
</body>
</html>