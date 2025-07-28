<?php
session_start();


// Mostrar errores para depurar
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$GLOBALS['allowed_config_access'] = true;
require __DIR__ . '/config/config.php';


// Redirección si no está logueado
if (empty($_SESSION['user']['logged_in'])) {
    header("Location: login.php");
    exit();
}

// Verificar inactividad (30 min)
$inactivity = 1800;
if (isset($_SESSION['user']['last_activity']) && 
    (time() - $_SESSION['user']['last_activity'] > $inactivity)) {
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=1");
    exit();
}
$_SESSION['user']['last_activity'] = time();

// Conexión a la base de datos
require_once 'includes/db.php';

/**
 * Función para llamar a la API de administración
 * @param string $endpoint Endpoint de la API (ej: 'visitantes')
 * @param string $method Método HTTP (GET, POST, PUT, DELETE)
 * @param array|null $data Datos a enviar en el cuerpo
 * @return array Respuesta de la API
 * @throws Exception Si hay errores en la conexión
 */
function callAdminAPI($endpoint, $method = 'GET', $data = null) {
    // Verificar que la clave API esté configurada
    if (!defined('ADMIN_API_KEY') || empty(ADMIN_API_KEY)) {
        throw new Exception("Configuración de API no disponible");
    }

    // Construir URL completa
    $url = 'https://tectesting.fwh.is/api/admin/' . ltrim($endpoint, '/');
    
    // Inicializar cURL
    $ch = curl_init($url);
    
    // Configurar headers
    $headers = [
        'Authorization: Bearer ' . ADMIN_API_KEY,
        'Content-Type: application/json',
        'Accept: application/json'
    ];
    
    // Configurar opciones de cURL
    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_CUSTOMREQUEST => strtoupper($method),
        CURLOPT_TIMEOUT => 10, // Timeout de 10 segundos
        CURLOPT_SSL_VERIFYPEER => true, // Para producción
        CURLOPT_FAILONERROR => false // Para manejar errores manualmente
    ];
    
    // Configurar datos si existen
    if (!empty($data)) {
        $options[CURLOPT_POSTFIELDS] = json_encode($data);
    }
    
    curl_setopt_array($ch, $options);
    
    // Ejecutar la petición
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Manejar errores de conexión
    if ($error) {
        throw new Exception("Error en conexión API: " . $error);
    }
    
    // Decodificar respuesta
    $decoded = json_decode($response, true) ?? [];
    
    // Manejar errores de la API
    if ($httpCode >= 400) {
        $errorMsg = $decoded['error'] ?? $decoded['message'] ?? 'Error desconocido en la API';
        throw new Exception("Error {$httpCode}: {$errorMsg}");
    }
    
    return [
        'code' => $httpCode,
        'data' => $decoded
    ];
}

// Obtener datos de visitantes pendientes con manejo de errores
$visitantes_pendientes = [];
try {
    $api_response = callAdminAPI('visitantes');
    if ($api_response['code'] === 200 && !empty($api_response['data']['data'])) {
        $visitantes_pendientes = $api_response['data']['data'];
    }
} catch (Exception $e) {
    error_log("Error al obtener visitantes: " . $e->getMessage());
    $_SESSION['error_message'] = "Error temporal al obtener datos. Por favor intente más tarde.";
}


// Consultas SQL
$socios_total = 0;
$viviendas_ocupadas = 0;
$viviendas_total = 0;
$proyectos_total = 0;
$ingresos_mes = 0;

// Socios
$query_socios = "SELECT COUNT(*) AS total FROM socios";
if ($result = $mysqli->query($query_socios)) {
    $row = $result->fetch_assoc();
    $socios_total = (int)$row['total'];
}

// Viviendas
$query_ocupadas = "SELECT COUNT(*) AS ocupadas FROM viviendas WHERE estado = 'ocupada'";
if ($result = $mysqli->query($query_ocupadas)) {
    $row = $result->fetch_assoc();
    $viviendas_ocupadas = (int)$row['ocupadas'];
}

$query_total_viviendas = "SELECT COUNT(*) AS total FROM viviendas";
if ($result = $mysqli->query($query_total_viviendas)) {
    $row = $result->fetch_assoc();
    $viviendas_total = (int)$row['total'];
}

// Proyectos en curso
$query_proyectos = "SELECT COUNT(*) AS total FROM proyectos WHERE estado = 'en curso'";
if ($result = $mysqli->query($query_proyectos)) {
    $row = $result->fetch_assoc();
    $proyectos_total = (int)$row['total'];
}


?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Cooperativa de Vivienda</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Tailwind y FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.0/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs" defer></script>
    <script src="https://kit.fontawesome.com/a2d79f6f3f.js" crossorigin="anonymous"></script>
    <script>
    // Función para aprobar visitantes
     function approveVisitante(id) {
        if (!confirm('¿Está seguro de aprobar este visitante como socio?')) {
            return;
        }
        
        fetch(`api_approve.php?id=${id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Eliminar fila visualmente
                const row = document.getElementById(`row-${id}`);
                if (row) row.remove();
                
                // Actualizar contador
                const counter = document.getElementById('pending-count');
                if (counter) {
                    counter.textContent = parseInt(counter.textContent) - 1;
                }
                
                // Mostrar notificación
                alert('Visitante aprobado exitosamente');
            } else {
                throw new Error(data.error || 'Error desconocido');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al aprobar visitante: ' + error.message);
        });
    }
    </script>
</head>
<body class="bg-gray-100 text-gray-800">

    <?php include 'includes/header.php'; ?>

    <div class="flex">
        <?php include 'includes/sidebar.php'; ?>

        <main class="flex-1 p-6">
            <h1 class="text-2xl font-bold mb-6">Panel de Control</h1>


            <!-- Mensajes de error -->
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
                    <?php echo htmlspecialchars($_SESSION['error_message']); 
                          unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>

            <!-- Tarjetas de resumen -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-6">
                <div class="bg-blue-100 border-l-4 border-blue-500 p-4 rounded shadow">
                    <h3 class="text-lg font-semibold">Socios Registrados</h3>
                    <p class="text-3xl"><?php echo $socios_total; ?></p>
                </div>

                <div class="bg-yellow-100 border-l-4 border-yellow-500 p-4 rounded shadow">
                    <h3 class="text-lg font-semibold">Viviendas Ocupadas</h3>
                    <p class="text-2xl"><?php echo "$viviendas_ocupadas / $viviendas_total"; ?></p>
                </div>

                <div class="bg-green-100 border-l-4 border-green-500 p-4 rounded shadow">
                    <h3 class="text-lg font-semibold">Ingresos del Mes</h3>
                    <p class="text-2xl">$<?php echo number_format($ingresos_mes, 2, ',', '.'); ?></p>
                </div>

                <div class="bg-red-100 border-l-4 border-red-500 p-4 rounded shadow">
                    <h3 class="text-lg font-semibold">Proyectos en Curso</h3>
                    <p class="text-2xl"><?php echo $proyectos_total; ?></p>
                </div>

                <div class="bg-purple-100 border-l-4 border-purple-500 p-4 rounded shadow">
                    <h3 class="text-lg font-semibold">Visitantes Pendientes</h3>
                    <p class="text-3xl" id="pending-count"><?php echo count($visitantes_pendientes); ?></p>
                </div>
            </div>


            <!-- Contenedor principal para los listados -->
            <div class="flex flex-col space-y-6 mb-8">
                <!-- Sección de Visitantes Pendientes -->
                <div class="bg-white rounded-lg shadow-md">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-800">Visitantes Pendientes de Aprobación</h2>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (!empty($visitantes_pendientes)): ?>
                                    <?php foreach ($visitantes_pendientes as $visitante): ?>
                                        <?php if (is_array($visitante) && empty($visitante['active'])): ?>
                                        <tr id="row-<?php echo htmlspecialchars($visitante['id']); ?>">
                                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($visitante['nombre'] ?? ''); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($visitante['email'] ?? ''); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap"><?php 
                                                echo isset($visitante['creado_en']) ? 
                                                date('d/m/Y H:i', strtotime($visitante['creado_en'])) : 
                                                'N/A'; 
                                            ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <button onclick="approveVisitante(<?php echo htmlspecialchars($visitante['id']); ?>)" 
                                                        class="bg-green-500 hover:bg-green-700 text-white py-1 px-3 rounded text-sm">
                                                    <i class="fas fa-check mr-1"></i> Aprobar
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                            No hay visitantes pendientes de aprobación
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Listado de socios -->
                <div class="bg-white rounded shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-800">Listado de Socios</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CI</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Correo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php
                                $query = "SELECT v.nombre AS nombre, s.ci AS ci, v.email AS email, IF(v.active = 1, 'Activo', 'Inactivo') AS estado FROM socios s JOIN visitantes v ON s.visitante_id = v.id LIMIT 5";
                                if ($result = $mysqli->query($query)) {
                                    while ($socio = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td class='px-6 py-4 whitespace-nowrap'>" . htmlspecialchars($socio['nombre']) . "</td>";
                                        echo "<td class='px-6 py-4 whitespace-nowrap'>" . htmlspecialchars($socio['ci']) . "</td>";
                                        echo "<td class='px-6 py-4 whitespace-nowrap'>" . htmlspecialchars($socio['email']) . "</td>";
                                        echo "<td class='px-6 py-4 whitespace-nowrap'>" . htmlspecialchars($socio['estado']) . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4' class='px-6 py-4 text-red-600'>Error al cargar los socios.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
