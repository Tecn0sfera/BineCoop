<?php

require_once 'includes/db.php';

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


$res_socios = $mysqli->query("SELECT COUNT(*) AS total FROM socios WHERE activo = 1");
$socios_activos = $res_socios->fetch_assoc()['total'];


$res_viviendas = $mysqli->query("SELECT 
    SUM(ocupada) AS ocupadas, 
    COUNT(*) AS total 
FROM viviendas");
$data_viviendas = $res_viviendas->fetch_assoc();



$mes_actual = date('Y-m');
$res_aportes = $mysqli->query("SELECT SUM(monto) AS total FROM aportes WHERE DATE_FORMAT(fecha, '%Y-%m') = '$mes_actual'");
$ingresos_mes = $res_aportes->fetch_assoc()['total'] ?? 0;


$res_proyectos = $mysqli->query("SELECT COUNT(*) AS total FROM proyectos WHERE estado = 'en curso'");
$proyectos_curso = $res_proyectos->fetch_assoc()['total'];





?>




<?php include("includes/header.php"); ?>

<div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden">

  <!-- Sidebar -->
  <?php include("includes/sidebar.php"); ?>

  <!-- Contenido principal -->
  <div class="flex-1 flex flex-col w-0">

    <!-- Header superior -->
    <header class="flex items-center justify-between bg-white px-4 py-3 shadow-md sm:px-6">
      <button class="text-gray-500 sm:hidden" @click="sidebarOpen = true">
        <i class="ti ti-menu-2 text-xl"></i>
      </button>
      <h2 class="text-lg font-semibold">Panel de Control</h2>
      <div class="relative" x-data="{ open: false }" @click.outside="open = false">
        <button @click="open = !open" class="flex items-center space-x-2">
          <img src="https://i.pravatar.cc/40" alt="Usuario" class="w-8 h-8 rounded-full" />
          <span class="hidden sm:block">Admin</span>
        </button>
        <div x-cloak x-show="open" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-2 z-50">
          <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Perfil</a>
          <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Cerrar sesión</a>
        </div>
      </div>
    </header>

    <!-- Contenido -->
    <main class="flex-1 overflow-y-auto p-6">
      <h1 class="text-2xl font-bold mb-6">Bienvenido al Panel de Administración</h1>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow p-5 hover:shadow-lg transition">
          <h3 class="text-sm font-medium text-gray-500">Socios activos</h3>
          <p class="text-2xl font-bold text-indigo-600 mt-2"><?php echo $socios_activos; ?></p>
        </div>
        <div class="bg-white rounded-xl shadow p-5 hover:shadow-lg transition">
          <h3 class="text-sm font-medium text-gray-500">Viviendas ocupadas</h3>
          <p class="text-2xl font-bold text-green-600 mt-2"><?php echo $viviendas_ocupadas; ?></p>
        </div>
        <div class="bg-white rounded-xl shadow p-5 hover:shadow-lg transition">
          <h3 class="text-sm font-medium text-gray-500">Ingresos del mes</h3>
          <p class="text-2xl font-bold text-amber-600 mt-2"><?php echo $ingresos_mes; ?></p>
        </div>
        <div class="bg-white rounded-xl shadow p-5 hover:shadow-lg transition">
          <h3 class="text-sm font-medium text-gray-500">Proyectos en curso</h3>
          <p class="text-2xl font-bold text-red-600 mt-2"><?php echo $proyectos_curso; ?></p>
        </div>
      </div>

      <!-- Tabla de ejemplo -->
      <div class="bg-white mt-10 shadow rounded-lg overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cargo</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Oficina</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Edad</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Inicio</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Salario</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr>
              <td class="px-6 py-4 whitespace-nowrap">Airl Satou</td>
              <td class="px-6 py-4 whitespace-nowrap">Contador</td>
              <td class="px-6 py-4 whitespace-nowrap">Tokyo</td>
              <td class="px-6 py-4 whitespace-nowrap">33</td>
              <td class="px-6 py-4 whitespace-nowrap">2008/11/28</td>
              <td class="px-6 py-4 whitespace-nowrap">$162,700</td>
            </tr>
            <tr>
              <td class="px-6 py-4 whitespace-nowrap">Angelica Ramos</td>
              <td class="px-6 py-4 whitespace-nowrap">CEO</td>
              <td class="px-6 py-4 whitespace-nowrap">London</td>
              <td class="px-6 py-4 whitespace-nowrap">47</td>
              <td class="px-6 py-4 whitespace-nowrap">2009/10/09</td>
              <td class="px-6 py-4 whitespace-nowrap">$1,200,000</td>
            </tr>
            <tr>
              <td class="px-6 py-4 whitespace-nowrap">Ashton Cox</td>
              <td class="px-6 py-4 whitespace-nowrap">Autor Técnico Junior</td>
              <td class="px-6 py-4 whitespace-nowrap">San Francisco</td>
              <td class="px-6 py-4 whitespace-nowrap">66</td>
              <td class="px-6 py-4 whitespace-nowrap">2009/01/12</td>
              <td class="px-6 py-4 whitespace-nowrap">$86,000</td>
            </tr>
          </tbody>
        </table>
      </div>

    </main>

    <?php include("includes/footer.php"); ?>

  </div>
</div>

