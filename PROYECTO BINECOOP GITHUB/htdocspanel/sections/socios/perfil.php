<?php
session_start();

// Configurar encoding UTF-8
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

// Verificar autenticación
if (empty($_SESSION['user']['logged_in'])) {
    header("Location: ../../login.php");
    exit();
}

$GLOBALS['allowed_config_access'] = true;
require __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/db.php';

$socio_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($socio_id <= 0) {
    header("Location: lista.php");
    exit();
}

// Obtener información del socio
$socio = null;
$error_message = null;

try {
    // Verificar si las tablas socios y viviendas existen
    $checkTables = $mysqli->query("SHOW TABLES LIKE 'socios'");
    $sociosTableExists = $checkTables && $checkTables->num_rows > 0;
    
    $checkTables = $mysqli->query("SHOW TABLES LIKE 'viviendas'");
    $viviendasTableExists = $checkTables && $checkTables->num_rows > 0;
    
    // Construir consulta según qué tablas existen
    // Priorizar datos de socios, pero también buscar en visitantes
    if ($sociosTableExists && $viviendasTableExists) {
        // Si ambas tablas existen, usar JOIN completo
        $query = "SELECT v.*, 
                         COALESCE(s.apellido, NULL) as apellido,
                         COALESCE(s.ci, v.ci, NULL) as ci,
                         COALESCE(s.telefono, v.telefono, NULL) as telefono,
                         s.fecha_ingreso, s.vivienda_id, s.tipo_asignacion,
                         vi.numero as vivienda_numero, vi.bloque as vivienda_bloque, vi.tipo as vivienda_tipo
                  FROM visitantes v
                  LEFT JOIN socios s ON v.id = s.visitante_id
                  LEFT JOIN viviendas vi ON s.vivienda_id = vi.id
                  WHERE v.id = ? AND v.estado_aprobacion = 'aprobado'";
    } elseif ($sociosTableExists) {
        // Si solo existe socios, hacer JOIN sin viviendas
        $query = "SELECT v.*,
                         COALESCE(s.apellido, NULL) as apellido,
                         COALESCE(s.ci, v.ci, NULL) as ci,
                         COALESCE(s.telefono, v.telefono, NULL) as telefono,
                         s.fecha_ingreso, s.vivienda_id, s.tipo_asignacion,
                         NULL as vivienda_numero, NULL as vivienda_bloque, NULL as vivienda_tipo
                  FROM visitantes v
                  LEFT JOIN socios s ON v.id = s.visitante_id
                  WHERE v.id = ? AND v.estado_aprobacion = 'aprobado'";
    } else {
        // Si no existe socios, solo obtener datos de visitantes
        $query = "SELECT v.*, NULL as apellido, 
                         COALESCE(v.ci, NULL) as ci,
                         COALESCE(v.telefono, NULL) as telefono,
                         NULL as fecha_ingreso, NULL as vivienda_id, NULL as tipo_asignacion,
                         NULL as vivienda_numero, NULL as vivienda_bloque, NULL as vivienda_tipo
                  FROM visitantes v
                  WHERE v.id = ? AND v.estado_aprobacion = 'aprobado'";
    }

    $stmt = $mysqli->prepare($query);
    if (!$stmt) {
        error_log("Error preparando consulta en perfil.php: " . $mysqli->error);
        error_log("Query intentada: " . $query);
        throw new Exception("Error al preparar la consulta. Por favor contacta al administrador.");
    }
    
    $stmt->bind_param("i", $socio_id);
    
    if (!$stmt->execute()) {
        error_log("Error ejecutando consulta en perfil.php: " . $stmt->error);
        error_log("Query: " . $query);
        throw new Exception("Error al ejecutar la consulta. Por favor contacta al administrador.");
    }
    
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // El visitante no existe o no está aprobado
        error_log("Visitante con ID {$socio_id} no encontrado o no está aprobado");
        header("Location: lista.php?error=2");
        exit();
    }

    $socio = $result->fetch_assoc();
    if (!$socio || empty($socio['id'])) {
        error_log("Error: No se pudo obtener datos del socio con ID {$socio_id}");
        throw new Exception("No se pudieron obtener los datos del socio.");
    }
    
    // Cerrar el statement
    $stmt->close();
    
} catch (mysqli_sql_exception $e) {
    error_log("Error SQL al obtener información del socio ID {$socio_id}: " . $e->getMessage());
    error_log("Error SQL: " . $e->getSqlState() . " - " . $e->getCode());
    $error_message = "Error de base de datos: " . $e->getMessage();
} catch (Exception $e) {
    error_log("Error al obtener información del socio ID {$socio_id}: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    $error_message = $e->getMessage();
}

// Obtener historial de actividad (si existe tabla de logs)
$historial = [];
// Aquí puedes agregar consultas para obtener historial de pagos, aportes, etc.

// Si hay error o no hay socio, redirigir con mensaje apropiado
if (isset($error_message) || !isset($socio) || !$socio || empty($socio['id'])) {
    $error_code = isset($error_message) ? 1 : 2;
    header("Location: lista.php?error={$error_code}");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Socio - <?php echo htmlspecialchars($socio['nombre'] ?? 'Socio'); ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" rel="stylesheet">
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50">

<div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: false }">
    
    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 z-30 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0" 
           :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }">
        
        <div class="flex items-center justify-between px-6 py-4 bg-indigo-600 text-white">
            <h1 class="text-lg font-bold">Cooperativa</h1>
            <button class="lg:hidden" @click="sidebarOpen = false">
                <i class="ti ti-x text-xl"></i>
            </button>
        </div>
        
        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto h-full">
            <a href="../../dashboard.php" class="flex items-center px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors">
                <i class="ti ti-dashboard mr-3 text-lg"></i>
                Dashboard
            </a>
            
            <div x-data="{ open: true }">
                <button @click="open = !open" class="flex items-center w-full px-3 py-2 rounded-lg bg-indigo-100 text-indigo-700 font-medium">
                    <i class="ti ti-users mr-3 text-lg"></i>
                    Socios
                    <i class="ti ti-chevron-down ml-auto transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                </button>
                <div x-show="open" x-collapse class="ml-6 mt-1 space-y-1">
                    <a href="lista.php" class="block px-3 py-2 text-sm text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg">Lista de Socios</a>
                    <a href="nuevo.php" class="block px-3 py-2 text-sm text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg">Nuevo Socio</a>
                </div>
            </div>
        </nav>
    </aside>

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white border-b border-gray-200 px-4 py-3 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <button class="text-gray-500 lg:hidden" @click="sidebarOpen = true">
                        <i class="ti ti-menu-2 text-xl"></i>
                    </button>
                    <a href="lista.php" class="text-gray-500 hover:text-gray-700">
                        <i class="ti ti-arrow-left mr-2"></i> Volver
                    </a>
                    <h2 class="text-xl font-semibold text-gray-800">Perfil de Socio</h2>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-6">
            <!-- Información Principal -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                <div class="flex items-start justify-between mb-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-20 h-20 bg-gradient-to-r from-green-400 to-green-600 rounded-full flex items-center justify-center text-white font-bold text-2xl">
                            <?php echo strtoupper(substr($socio['nombre'], 0, 2)); ?>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900"><?php echo htmlspecialchars($socio['nombre']); ?></h1>
                            <p class="text-gray-500"><?php echo htmlspecialchars($socio['email']); ?></p>
                            <div class="mt-2">
                                <?php if ($socio['activo'] == 1): ?>
                                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-green-100 text-green-800">Activo</span>
                                <?php else: ?>
                                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-red-100 text-red-800">Inactivo</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="text-gray-400 hover:text-gray-600">
                            <i class="ti ti-dots-vertical text-xl"></i>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border border-gray-200">
                            <div class="py-1">
                                <?php if ($socio['activo'] == 1): ?>
                                    <button onclick="suspenderSocio(<?php echo $socio['id']; ?>)" class="w-full text-left px-4 py-2 text-sm text-yellow-700 hover:bg-yellow-50 flex items-center">
                                        <i class="ti ti-ban mr-2"></i> Suspender
                                    </button>
                                <?php else: ?>
                                    <button onclick="activarSocio(<?php echo $socio['id']; ?>)" class="w-full text-left px-4 py-2 text-sm text-green-700 hover:bg-green-50 flex items-center">
                                        <i class="ti ti-check mr-2"></i> Activar
                                    </button>
                                <?php endif; ?>
                                <button onclick="eliminarSocio(<?php echo $socio['id']; ?>, '<?php echo htmlspecialchars($socio['nombre'], ENT_QUOTES); ?>')" class="w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-50 flex items-center">
                                    <i class="ti ti-trash mr-2"></i> Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <label class="text-sm font-medium text-gray-500">ID de Socio</label>
                        <p class="text-lg font-semibold text-gray-900">#<?php echo $socio['id']; ?></p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Fecha de Registro</label>
                        <p class="text-lg font-semibold text-gray-900">
                            <?php 
                            if (!empty($socio['fecha_registro'])) {
                                try {
                                    $fecha = new DateTime($socio['fecha_registro']);
                                    echo $fecha->format('d/m/Y');
                                } catch (Exception $e) {
                                    echo htmlspecialchars($socio['fecha_registro']);
                                }
                            } else {
                                echo 'N/A';
                            }
                            ?>
                        </p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Fecha de Aprobación</label>
                        <p class="text-lg font-semibold text-gray-900">
                            <?php 
                            if (!empty($socio['fecha_aprobacion'])) {
                                try {
                                    $fecha = new DateTime($socio['fecha_aprobacion']);
                                    echo $fecha->format('d/m/Y');
                                } catch (Exception $e) {
                                    echo htmlspecialchars($socio['fecha_aprobacion']);
                                }
                            } else {
                                echo 'N/A';
                            }
                            ?>
                        </p>
                    </div>
                    <?php if (!empty($socio['fecha_ingreso'])): ?>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Fecha de Ingreso</label>
                        <p class="text-lg font-semibold text-gray-900">
                            <?php 
                            try {
                                $fecha = new DateTime($socio['fecha_ingreso']);
                                echo $fecha->format('d/m/Y');
                            } catch (Exception $e) {
                                echo htmlspecialchars($socio['fecha_ingreso']);
                            }
                            ?>
                        </p>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($socio['ultimo_acceso'])): ?>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Último Acceso</label>
                        <p class="text-lg font-semibold text-gray-900">
                            <?php 
                            try {
                                $fecha = new DateTime($socio['ultimo_acceso']);
                                echo $fecha->format('d/m/Y H:i');
                            } catch (Exception $e) {
                                echo 'N/A';
                            }
                            ?>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Información de Contacto e Identificación -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">Información de Contacto e Identificación</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php if (!empty($socio['apellido'])): ?>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Apellido</label>
                            <p class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($socio['apellido']); ?></p>
                        </div>
                        <?php endif; ?>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Cédula de Identidad</label>
                            <p class="text-lg font-semibold text-gray-900">
                                <?php if (!empty($socio['ci'])): ?>
                                    <?php echo htmlspecialchars($socio['ci']); ?>
                                <?php else: ?>
                                    <span class="text-gray-400">No registrado</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Teléfono</label>
                            <p class="text-lg font-semibold text-gray-900">
                                <?php if (!empty($socio['telefono'])): ?>
                                    <?php echo htmlspecialchars($socio['telefono']); ?>
                                <?php else: ?>
                                    <span class="text-gray-400">No registrado</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Secciones adicionales -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Historial de Actividad -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="ti ti-history mr-2 text-indigo-600"></i>
                        Historial de Actividad
                    </h3>
                    <div class="space-y-3">
                        <div class="text-center py-8 text-gray-500">
                            <i class="ti ti-inbox text-4xl mb-2"></i>
                            <p>No hay actividad registrada</p>
                        </div>
                    </div>
                </div>

                <!-- Información Adicional -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="ti ti-info-circle mr-2 text-indigo-600"></i>
                        Información Adicional
                    </h3>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Estado de Aprobación</label>
                            <p class="text-sm text-gray-900 capitalize"><?php echo htmlspecialchars($socio['estado_aprobacion']); ?></p>
                        </div>
                        <?php if (!empty($socio['aprobado_por'])): ?>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Aprobado por</label>
                            <p class="text-sm text-gray-900">Usuario ID: <?php echo htmlspecialchars($socio['aprobado_por']); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Viviendas Asignadas -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="ti ti-building-community text-indigo-600 mr-2"></i>
                        Viviendas Asignadas
                    </h3>
                    <div class="space-y-3">
                        <?php
                        // Obtener viviendas asignadas
                        $viviendas_asignadas = [];
                        try {
                            // Verificar si existe la tabla socios
                            $check_socios = $mysqli->query("SHOW TABLES LIKE 'socios'");
                            $socios_exists = $check_socios && $check_socios->num_rows > 0;
                            
                            if ($socios_exists) {
                                // Verificar si existe tabla viviendas
                                $check_viviendas = $mysqli->query("SHOW TABLES LIKE 'viviendas'");
                                $viviendas_exists = $check_viviendas && $check_viviendas->num_rows > 0;
                                
                                if ($viviendas_exists) {
                                    // Buscar viviendas asignadas con JOIN
                                    $query_viviendas = "SELECT vi.*, s.tipo_asignacion, s.fecha_ingreso
                                                       FROM socios s
                                                       LEFT JOIN viviendas vi ON s.vivienda_id = vi.id
                                                       WHERE s.visitante_id = ?";
                                } else {
                                    // Si no hay tabla viviendas, solo mostrar tipo de asignación
                                    $query_viviendas = "SELECT s.tipo_asignacion, s.fecha_ingreso, NULL as numero, NULL as bloque, NULL as tipo
                                                       FROM socios s
                                                       WHERE s.visitante_id = ?";
                                }
                                
                                $stmt_viviendas = $mysqli->prepare($query_viviendas);
                                if ($stmt_viviendas) {
                                    $stmt_viviendas->bind_param('i', $socio_id);
                                    $stmt_viviendas->execute();
                                    $result_viviendas = $stmt_viviendas->get_result();
                                    
                                    while ($row = $result_viviendas->fetch_assoc()) {
                                        $viviendas_asignadas[] = $row;
                                    }
                                    $stmt_viviendas->close();
                                }
                            }
                            
                            // Si no hay viviendas asignadas pero hay tipo_asignacion en el socio principal, mostrarlo
                            if (empty($viviendas_asignadas) && !empty($socio['tipo_asignacion'])) {
                                $viviendas_asignadas[] = [
                                    'tipo_asignacion' => $socio['tipo_asignacion'],
                                    'fecha_ingreso' => $socio['fecha_ingreso'],
                                    'numero' => $socio['vivienda_numero'] ?? null,
                                    'bloque' => $socio['vivienda_bloque'] ?? null,
                                    'tipo' => $socio['vivienda_tipo'] ?? null
                                ];
                            }
                        } catch (Exception $e) {
                            error_log("Error al obtener viviendas asignadas: " . $e->getMessage());
                        }
                        
                        if (!empty($viviendas_asignadas)):
                            foreach ($viviendas_asignadas as $vivienda):
                        ?>
                            <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <?php if (!empty($vivienda['numero'])): ?>
                                            <p class="font-semibold text-gray-900">
                                                Vivienda <?php echo htmlspecialchars($vivienda['numero']); ?>
                                                <?php if (!empty($vivienda['bloque'])): ?>
                                                    - Bloque <?php echo htmlspecialchars($vivienda['bloque']); ?>
                                                <?php endif; ?>
                                            </p>
                                        <?php elseif (!empty($vivienda['tipo_asignacion'])): ?>
                                            <p class="font-semibold text-gray-900">
                                                Asignación: <?php echo ucfirst(htmlspecialchars($vivienda['tipo_asignacion'])); ?>
                                            </p>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($vivienda['tipo'])): ?>
                                            <p class="text-sm text-gray-600 mt-1">
                                                Tipo de Vivienda: <?php echo ucfirst(htmlspecialchars($vivienda['tipo'])); ?>
                                            </p>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($vivienda['metros_cuadrados'])): ?>
                                            <p class="text-sm text-gray-600 mt-1">
                                                Metros²: <?php echo htmlspecialchars($vivienda['metros_cuadrados']); ?>
                                            </p>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($vivienda['fecha_ingreso'])): ?>
                                            <p class="text-xs text-gray-500 mt-1">
                                                Asignada el: <?php 
                                                try {
                                                    $fecha = new DateTime($vivienda['fecha_ingreso']);
                                                    echo $fecha->format('d/m/Y');
                                                } catch (Exception $e) {
                                                    echo htmlspecialchars($vivienda['fecha_ingreso']);
                                                }
                                                ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full ml-4">
                                        Asignada
                                    </span>
                                </div>
                            </div>
                        <?php 
                            endforeach;
                        else:
                        ?>
                            <div class="text-center py-8 text-gray-500">
                                <i class="ti ti-building-off text-4xl mb-2"></i>
                                <p>No hay viviendas asignadas</p>
                                <p class="text-sm mt-1">Las viviendas asignadas aparecerán aquí</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
function suspenderSocio(id) {
    if (!confirm('¿Está seguro de suspender este socio?')) return;
    fetch('../../api_socios.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=suspender&id=${id}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('Socio suspendido exitosamente');
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    });
}

function activarSocio(id) {
    if (!confirm('¿Está seguro de activar este socio?')) return;
    fetch('../../api_socios.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=activar&id=${id}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('Socio activado exitosamente');
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    });
}

function eliminarSocio(id, nombre) {
    if (!confirm(`¿Está seguro de eliminar al socio "${nombre}"? Esta acción no se puede deshacer.`)) return;
    fetch('../../api_socios.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=eliminar&id=${id}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('Socio eliminado exitosamente');
            window.location.href = 'lista.php';
        } else {
            alert('Error: ' + data.error);
        }
    });
}
</script>

</body>
</html>

