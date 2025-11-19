<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Panel de Administración</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" rel="stylesheet">
  <style>
    [x-cloak] { display: none !important; }
  </style>
</head>
<body class="bg-gray-100 text-gray-800">
  <div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 z-30 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out sm:translate-x-0 sm:relative sm:flex sm:flex-col" :class="{ '-translate-x-full': !sidebarOpen }">
      <div class="flex items-center justify-between px-4 py-4 bg-indigo-600 text-white">
        <h1 class="text-lg font-bold">Cooperativa de Vivienda</h1>
        <button class="sm:hidden" @click="sidebarOpen = false">
          <i class="ti ti-x"></i>
        </button>
      </div>
      <nav class="flex-1 px-4 py-6 text-sm text-gray-700">
        <ul class="space-y-2" x-data="{}">
          <li>
            <a href="index.php" class="flex items-center px-3 py-2 rounded hover:bg-indigo-100">
              <i class="ti ti-home mr-2"></i> Dashboard
            </a>
          </li>

          <!-- Socios -->
          <li x-data="{ open: false }">
            <button @click="open = !open" class="flex items-center w-full px-3 py-2 rounded hover:bg-indigo-100">
              <i class="ti ti-users mr-2"></i> Socios <i class="ti ti-chevron-down ml-auto" :class="{ 'rotate-180': open }"></i>
            </button>
            <ul x-show="open" x-cloak class="ml-6 mt-1 space-y-1">
              <li><a href="sections/socios/lista.php" class="block hover:underline">Lista de Socios</a></li>
              <li><a href="sections/socios/nuevo.php" class="block hover:underline">Nuevo Socio</a></li>
              <li><a href="sections/socios/familiares.php" class="block hover:underline">Miembros Familiares</a></li>
              <li><a href="sections/socios/estado.php" class="block hover:underline">Estado de Socio</a></li>
            </ul>
          </li>


            <!-- Añadir opción de administración -->
            <li class="mb-1">
                <a href="#admin-section" class="block p-2 hover:bg-gray-200 rounded flex items-center" 
                   onclick="document.getElementById('admin-section').classList.toggle('hidden')">
                    <i class="fas fa-user-shield mr-2"></i>
                    <span>Administración</span>
                    <i class="fas fa-chevron-down ml-auto"></i>
                </a>
                <ul id="admin-section" class="hidden pl-4">
                    <li><a href="#pending-approval" class="block p-2 hover:bg-gray-200 rounded">Aprobación Visitantes</a></li>
                    <li><a href="admin_users.php" class="block p-2 hover:bg-gray-200 rounded">Gestión de Usuarios</a></li>
                </ul>
            </li>

          <!-- Viviendas -->
          <li x-data="{ open: false }">
            <button @click="open = !open" class="flex items-center w-full px-3 py-2 rounded hover:bg-indigo-100">
              <i class="ti ti-building-community mr-2"></i> Viviendas <i class="ti ti-chevron-down ml-auto" :class="{ 'rotate-180': open }"></i>
            </button>
            <ul x-show="open" x-cloak class="ml-6 mt-1 space-y-1">
              <li><a href="sections/viviendas/inventario.php" class="block hover:underline">Inventario</a></li>
              <li><a href="sections/viviendas/derechos.php" class="block hover:underline">Derechos de Uso</a></li>
              <li><a href="sections/viviendas/asignaciones.php" class="block hover:underline">Asignaciones</a></li>
              <li><a href="sections/viviendas/areas.php" class="block hover:underline">Áreas Comunes</a></li>
            </ul>
          </li>

          <!-- Finanzas -->
          <li x-data="{ open: false }">
            <button @click="open = !open" class="flex items-center w-full px-3 py-2 rounded hover:bg-indigo-100">
              <i class="ti ti-currency-dollar mr-2"></i> Finanzas <i class="ti ti-chevron-down ml-auto" :class="{ 'rotate-180': open }"></i>
            </button>
            <ul x-show="open" x-cloak class="ml-6 mt-1 space-y-1">
              <li><a href="sections/finanzas/aportes.php" class="block hover:underline">Aportes</a></li>
              <li><a href="sections/finanzas/pagos.php" class="block hover:underline">Pagos</a></li>
              <li><a href="sections/finanzas/tipos-pago.php" class="block hover:underline">Tipos de Pago</a></li>
              <li><a href="sections/finanzas/reportes.php" class="block hover:underline">Reportes Financieros</a></li>
            </ul>
          </li>

          <!-- Proyectos -->
          <li x-data="{ open: false }">
            <button @click="open = !open" class="flex items-center w-full px-3 py-2 rounded hover:bg-indigo-100">
              <i class="ti ti-layout-grid mr-2"></i> Proyectos <i class="ti ti-chevron-down ml-auto" :class="{ 'rotate-180': open }"></i>
            </button>
            <ul x-show="open" x-cloak class="ml-6 mt-1 space-y-1">
              <li><a href="sections/proyectos/lista.php" class="block hover:underline">Lista</a></li>
              <li><a href="sections/proyectos/nuevo.php" class="block hover:underline">Nuevo Proyecto</a></li>
              <li><a href="sections/proyectos/institutos.php" class="block hover:underline">Institutos Técnicos</a></li>
              <li><a href="sections/proyectos/reguladores.php" class="block hover:underline">Organismos Reguladores</a></li>
            </ul>
          </li>

          <!-- Organización -->
          <li x-data="{ open: false }">
            <button @click="open = !open" class="flex items-center w-full px-3 py-2 rounded hover:bg-indigo-100">
              <i class="ti ti-users-group mr-2"></i> Organización <i class="ti ti-chevron-down ml-auto" :class="{ 'rotate-180': open }"></i>
            </button>
            <ul x-show="open" x-cloak class="ml-6 mt-1 space-y-1">
              <li><a href="sections/organizacion/comisiones.php" class="block hover:underline">Comisiones</a></li>
              <li><a href="sections/organizacion/eventos.php" class="block hover:underline">Eventos</a></li>
              <li><a href="sections/organizacion/participacion.php" class="block hover:underline">Participación</a></li>
            </ul>
          </li>


          <!-- Configuración -->
          <li x-data="{ open: false }">
            <button @click="open = !open" class="flex items-center w-full px-3 py-2 rounded hover:bg-indigo-100">
              <i class="ti ti-settings mr-2"></i> Configuración <i class="ti ti-chevron-down ml-auto" :class="{ 'rotate-180': open }"></i>
            </button>
            <ul x-show="open" x-cloak class="ml-6 mt-1 space-y-1">
              <li><a href="#" class="block hover:underline">Parámetros del Sistema</a></li>
              <li><a href="#" class="block hover:underline">Backup de BD</a></li>
              <li><a href="#" class="block hover:underline">Logs del Sistema</a></li>
            </ul>
          </li>

        </ul>
      </nav>
    </aside>

    <!-- Contenido principal -->
    <div class="flex-1 flex flex-col w-0">
      <!-- Header -->
      <header class="flex items-center justify-between bg-white px-4 py-3 shadow-md sm:px-6">
        <button class="text-gray-500 sm:hidden" @click="sidebarOpen = true">
          <i class="ti ti-menu-2"></i>
        </button>
        <h2 class="text-lg font-semibold">Panel de Control</h2>
        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
          <button @click="open = !open" class="flex items-center space-x-2">
            <img src="https://i.pravatar.cc/40" alt="Usuario" class="w-8 h-8 rounded-full" />
            <span class="hidden sm:block">Admin</span>
          </button>
          <div x-cloak x-show="open" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-2 z-50">
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Perfil</a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Cerrar sesión</a>
          </div>
        </div>
      </header>

      <!-- Dashboard -->
      <main class="flex-1 overflow-y-auto p-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div class="bg-white rounded-xl shadow p-5 hover:shadow-lg transition">
            <h3 class="text-sm font-medium text-gray-500">Socios activos</h3>
            <p class="text-2xl font-bold text-indigo-600 mt-2">243</p>
          </div>
          <div class="bg-white rounded-xl shadow p-5 hover:shadow-lg transition">
            <h3 class="text-sm font-medium text-gray-500">Viviendas adjudicadas</h3>
            <p class="text-2xl font-bold text-indigo-600 mt-2">121</p>
          </div>
          <div class="bg-white rounded-xl shadow p-5 hover:shadow-lg transition">
            <h3 class="text-sm font-medium text-gray-500">Ingresos mensuales</h3>
            <p class="text-2xl font-bold text-indigo-600 mt-2">$582,000</p>
          </div>
          <div class="bg-white rounded-xl shadow p-5 hover:shadow-lg transition">
            <h3 class="text-sm font-medium text-gray-500">Documentos pendientes</h3>
            <p class="text-2xl font-bold text-indigo-600 mt-2">17</p>
          </div>
        </div>
      </main>
    </div>
  </div>
</body>
</html>

