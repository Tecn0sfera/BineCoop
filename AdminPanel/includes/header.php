<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Panel de Administración</title>

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Alpine.js -->
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

  <!-- Tabler Icons -->
  <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" rel="stylesheet">

  <!-- Ocultar elementos hasta que Alpine cargue -->
  <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-gray-100 text-gray-800">
<header class="bg-blue-800 text-white py-4 shadow-md">
  <div class="w-full flex justify-between items-center px-4 sm:px-6 md:px-8 lg:px-16">
    
    <!-- Título -->
    <h1 class="text-xl sm:text-2xl font-semibold">Panel de Administración</h1>

    <!-- Panel derecho: notificaciones + usuario -->
    <div class="flex items-center gap-4 md:gap-8">
      
      <!-- Notificaciones -->
      <div class="relative">
        <i class="ti ti-bell text-2xl cursor-pointer"></i>
        <span class="absolute -top-1 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">3</span>
      </div>

      <!-- Usuario con dropdown -->
      <div class="relative" x-data="{ open: false }">
        <div @click="open = !open" class="flex items-center space-x-2 cursor-pointer hover:opacity-80">
          <div class="bg-white text-blue-800 font-bold w-8 h-8 rounded-full flex items-center justify-center">AD</div>
          <span class="hidden sm:inline">Admin User</span>
        </div>

        <!-- Dropdown -->
        <div x-show="open" @click.outside="open = false" x-cloak
             class="absolute right-0 mt-2 w-48 bg-white text-gray-800 rounded shadow-md z-50">
          <a href="#" class="block px-4 py-2 hover:bg-gray-100">
            <i class="ti ti-user-cog mr-2"></i> Perfil
          </a>
          <a href="#" class="block px-4 py-2 hover:bg-gray-100">
            <i class="ti ti-settings mr-2"></i> Configuración
          </a>
          <a href="logout.php" class="block px-4 py-2 hover:bg-gray-100 text-red-600">
            <i class="ti ti-logout mr-2"></i> Cerrar Sesión
          </a>
        </div>
      </div>
    </div>
  </div>
</header>



