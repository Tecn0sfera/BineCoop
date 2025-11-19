<?php
// Iniciar sesión al principio
session_start();

// Verificar autenticación
if (empty($_SESSION['user']['logged_in'])) {
    header("Location: login.php");
    exit();
}

// Verificar inactividad (30 minutos)
$inactivity = 1800; // 30 minutos en segundos
if (isset($_SESSION['user']['last_activity']) && 
    (time() - $_SESSION['user']['last_activity'] > $inactivity)) {
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=1");
    exit();
}

// Actualizar tiempo de actividad
$_SESSION['user']['last_activity'] = time();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Cooperativa de Vivienda</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <h1>Dashboard</h1>
            
            <!-- Tarjetas resumen -->
            <div class="card-container">
                <div class="card primary">
                    <h3>Socios Activos</h3>
                    <p>245</p>
                    <a href="#" class="view-details">Ver detalles <i class="fas fa-arrow-right"></i></a>
                </div>
                
                <div class="card warning">
                    <h3>Viviendas Ocupadas</h3>
                    <p>180/200</p>
                    <a href="#" class="view-details">Ver detalles <i class="fas fa-arrow-right"></i></a>
                </div>
                
                <div class="card success">
                    <h3>Ingresos del Mes</h3>
                    <p>$85,420</p>
                    <a href="#" class="view-details">Ver detalles <i class="fas fa-arrow-right"></i></a>
                </div>
                
                <div class="card danger">
                    <h3>Proyectos en Curso</h3>
                    <p>3</p>
                    <a href="#" class="view-details">Ver detalles <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            
            <!-- Tabla de ejemplo -->
            <div class="table-container">
                <div class="table-header">
                    <div class="entries-show">
                        Mostrar 
                        <select>
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select> 
                        entradas
                    </div>
                    <div class="search-box">
                        Buscar: <input type="text">
                    </div>
                </div>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Cargo</th>
                            <th>Oficina</th>
                            <th>Edad</th>
                            <th>Fecha Inicio</th>
                            <th>Salario</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Airl Satou</td>
                            <td>Accountant</td>
                            <td>Tokyo</td>
                            <td>33</td>
                            <td>2008/11/28</td>
                            <td>$162,700</td>
                        </tr>
                        <tr>
                            <td>Angelica Ramos</td>
                            <td>Chief Executive Officer (CEO)</td>
                            <td>London</td>
                            <td>47</td>
                            <td>2009/10/09</td>
                            <td>$1,200,000</td>
                        </tr>
                        <tr>
                            <td>Ashton Cox</td>
                            <td>Junior Technical Author</td>
                            <td>San Francisco</td>
                            <td>66</td>
                            <td>2009/01/12</td>
                            <td>$86,000</td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="table-footer">
                    <div class="showing-entries">
                        Mostrando 1 a 3 de 3 entradas
                    </div>
                    <div class="pagination">
                        <button disabled>Anterior</button>
                        <button class="active">1</button>
                        <button>Siguiente</button>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/script.js"></script>
</body>
</html>
