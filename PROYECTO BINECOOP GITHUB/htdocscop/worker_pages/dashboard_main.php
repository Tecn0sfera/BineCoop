<?php
// Función limpiarDato si no está definida
if (!function_exists('limpiarDato')) {
    function limpiarDato($dato) {
        return htmlspecialchars(trim($dato), ENT_QUOTES, 'UTF-8');
    }
}

// Obtener registros de tiempo recientes con límite y sanitización
$tiempo_recientes = [];
try {
    $query_tiempo = "SELECT id, descripcion, horas, fecha_inicio, fecha_fin, estado 
                     FROM tiempo_trabajado 
                     WHERE trabajador_id = ? 
                     ORDER BY fecha_inicio DESC 
                     LIMIT 10";
    $stmt = $mysqli->prepare($query_tiempo);
    if (!$stmt) throw new Exception("Error preparing statement: " . $mysqli->error);
    
    $stmt->bind_param('i', $worker_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        // Sanitizar datos antes de mostrar
        $row['descripcion'] = limpiarDato($row['descripcion']);
        $row['horas'] = floatval($row['horas']);
        $tiempo_recientes[] = $row;
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Error al obtener registros de tiempo: " . $e->getMessage());
}

// Obtener reportes recientes desde base de datos
$reportes_recientes = [];
try {
    require_once __DIR__ . '/../includes/comprobantes_db.php';
    $todos_comprobantes = getComprobantes();
    
    foreach ($todos_comprobantes as $comp) {
        if ($comp['trabajador_id'] == $worker_id) {
            $reportes_recientes[] = [
                'id' => $comp['id'],
                'titulo' => limpiarDato($comp['titulo']),
                'fecha_envio' => $comp['fecha_envio']
            ];
            if (count($reportes_recientes) >= 5) {
                break;
            }
        }
    }
} catch (Exception $e) {
    error_log("Error al obtener reportes: " . $e->getMessage());
}

// Asegurar que $stats tenga todas las claves necesarias
if (!isset($stats)) {
    $stats = [];
}
$stats['reportes_enviados'] = $stats['reportes_enviados'] ?? $stats['comprobantes_enviados'] ?? 0;
$stats['ultimo_reporte'] = $stats['ultimo_reporte'] ?? $stats['ultimo_comprobante'] ?? null;
?>

<!-- Tarjetas de estadísticas -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    
    <!-- Horas Hoy -->
    <div class="card-hover gradient-blue rounded-xl p-6 text-white animate-fade-scale">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium opacity-90">Horas Hoy</p>
                <p class="text-2xl font-bold mt-1"><?php echo number_format($stats['horas_hoy'], 1); ?></p>
                <p class="text-xs opacity-75 mt-1">Trabajadas</p>
            </div>
            <div class="bg-white bg-opacity-20 p-3 rounded-full">
                <i class="ti ti-clock text-2xl"></i>
            </div>
        </div>
    </div>

    <!-- Horas Semana -->
    <div class="card-hover gradient-green rounded-xl p-6 text-white animate-fade-scale" style="animation-delay: 0.1s">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium opacity-90">Esta Semana</p>
                <p class="text-2xl font-bold mt-1"><?php echo number_format($stats['horas_semana'], 1); ?></p>
                <p class="text-xs opacity-75 mt-1">Horas</p>
            </div>
            <div class="bg-white bg-opacity-20 p-3 rounded-full">
                <i class="ti ti-calendar-week text-2xl"></i>
            </div>
        </div>
    </div>

    <!-- Horas Mes -->
    <div class="card-hover gradient-purple rounded-xl p-6 text-white animate-fade-scale" style="animation-delay: 0.2s">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium opacity-90">Este Mes</p>
                <p class="text-2xl font-bold mt-1"><?php echo number_format($stats['horas_mes'], 1); ?></p>
                <p class="text-xs opacity-75 mt-1">Horas</p>
            </div>
            <div class="bg-white bg-opacity-20 p-3 rounded-full">
                <i class="ti ti-calendar text-2xl"></i>
            </div>
        </div>
    </div>

    <!-- Reportes -->
    <div class="card-hover gradient-orange rounded-xl p-6 text-white animate-fade-scale" style="animation-delay: 0.3s">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium opacity-90">Reportes</p>
                <p class="text-2xl font-bold mt-1"><?php echo isset($stats['reportes_enviados']) ? $stats['reportes_enviados'] : (isset($stats['comprobantes_enviados']) ? $stats['comprobantes_enviados'] : 0); ?></p>
                <p class="text-xs opacity-75 mt-1">Este mes</p>
            </div>
            <div class="bg-white bg-opacity-20 p-3 rounded-full">
                <i class="ti ti-file-text text-2xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Grid de contenido principal -->
<div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
    
    <!-- Registros de Tiempo Recientes -->
    <div class="xl:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 animate-slide-up">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="ti ti-clock-hour-3 text-green-600 mr-2"></i>
                    Registros Recientes
                </h3>
                <div class="flex space-x-2">
                    <a href="dashboard.php?page=historial" class="text-sm text-green-600 hover:text-green-700 font-medium">
                        Ver todo
                    </a>
                    <button @click="showTimeModal = true" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        <i class="ti ti-plus mr-1"></i> Nuevo Registro
                    </button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Descripción
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Horas
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Fecha
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Estado
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (!empty($tiempo_recientes)): ?>
                        <?php foreach ($tiempo_recientes as $registro): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo $registro['descripcion']; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 font-mono">
                                        <?php echo number_format($registro['horas'], 2); ?>h
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php 
                                    if (!empty($registro['fecha_inicio'])) {
                                        $fecha = new DateTime($registro['fecha_inicio']);
                                        echo $fecha->format('d/m/Y H:i');
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php 
                                    $estado = $registro['estado'] ?? 'pendiente';
                                    $color = '';
                                    switch($estado) {
                                        case 'aprobado':
                                            $color = 'bg-green-100 text-green-800';
                                            break;
                                        case 'rechazado':
                                            $color = 'bg-red-100 text-red-800';
                                            break;
                                        default:
                                            $color = 'bg-yellow-100 text-yellow-800';
                                            $estado = 'pendiente';
                                    }
                                    ?>
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $color; ?>">
                                        <?php echo ucfirst($estado); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                        <i class="ti ti-clock text-3xl text-gray-300"></i>
                                    </div>
                                    <p class="text-lg font-medium text-gray-600">No hay registros de tiempo</p>
                                    <p class="text-sm text-gray-400 mt-1">Registra tu primera sesión de trabajo</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Panel lateral -->
    <div class="space-y-6">
        
        <!-- Timer Control -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 animate-slide-up" style="animation-delay: 0.2s">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="ti ti-player-play text-green-600 mr-2"></i>
                    Control de Tiempo
                </h3>
            </div>
            <div class="p-6">
                <div class="text-center">
                    <div class="timer-display mb-4" :class="{ 'timer-active': timerRunning, 'timer-paused': !timerRunning }" x-text="formatTime(currentTime)">00:00:00</div>
                    
                    <div class="space-y-3">
                        <button x-show="!timerRunning" @click="startTimer()" 
                                class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-4 rounded-lg transition-colors flex items-center justify-center">
                            <i class="ti ti-player-play mr-2"></i>
                            Iniciar Timer
                        </button>
                        
                        <button x-show="timerRunning" @click="pauseTimer()" 
                                class="w-full bg-yellow-600 hover:bg-yellow-700 text-white font-medium py-3 px-4 rounded-lg transition-colors flex items-center justify-center">
                            <i class="ti ti-player-pause mr-2"></i>
                            Pausar Timer
                        </button>
                        
                        <button x-show="currentTime > 0" @click="stopTimer()" 
                                class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-3 px-4 rounded-lg transition-colors flex items-center justify-center">
                            <i class="ti ti-player-stop mr-2"></i>
                            Detener y Guardar
                        </button>
                        
                        <button x-show="currentTime > 0" @click="cancelTimer()" 
                                class="w-full bg-gray-600 hover:bg-gray-700 text-white font-medium py-3 px-4 rounded-lg transition-colors flex items-center justify-center">
                            <i class="ti ti-x mr-2"></i>
                            Cancelar Timer
                        </button>
                    </div>
                    
                    <div x-show="timerDescription" class="mt-4 p-3 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-600">Descripción actual:</p>
                        <p class="font-medium text-gray-900" x-text="timerDescription"></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones rápidas -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 animate-slide-up" style="animation-delay: 0.3s">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="ti ti-zap text-orange-600 mr-2"></i>
                    Acciones Rápidas
                </h3>
            </div>
            <div class="p-6 space-y-3">
                <button @click="showTimeModal = true" class="w-full bg-green-50 hover:bg-green-100 text-green-700 font-medium py-3 px-4 rounded-lg transition-colors flex items-center">
                    <i class="ti ti-clock mr-2"></i>
                    Registrar Tiempo Manual
                </button>
                <button @click="showPdfModal = true" class="w-full bg-blue-50 hover:bg-blue-100 text-blue-700 font-medium py-3 px-4 rounded-lg transition-colors flex items-center">
                    <i class="ti ti-file-upload mr-2"></i>
                    Subir Comprobante de Pago
                </button>
                <a href="dashboard.php?page=pagos" class="w-full bg-purple-50 hover:bg-purple-100 text-purple-700 font-medium py-3 px-4 rounded-lg transition-colors flex items-center">
                    <i class="ti ti-wallet mr-2"></i>
                    Ver Pagos Pendientes
                </a>
            </div>
        </div>

        <!-- Reportes Recientes -->
        <?php if (!empty($reportes_recientes)): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 animate-slide-up" style="animation-delay: 0.4s">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="ti ti-file-text text-blue-600 mr-2"></i>
                    Reportes Recientes
                </h3>
            </div>
            <div class="p-6">
                <div class="space-y-3">
                    <?php foreach ($reportes_recientes as $reporte): ?>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900"><?php echo $reporte['titulo']; ?></p>
                                <p class="text-xs text-gray-500">
                                    <?php 
                                    $fecha = new DateTime($reporte['fecha_envio']);
                                    echo $fecha->format('d/m/Y');
                                    ?>
                                </p>
                            </div>
                            <i class="ti ti-file-pdf text-red-500"></i>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Resumen del día -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 animate-slide-up" style="animation-delay: 0.5s">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="ti ti-calendar-today text-blue-600 mr-2"></i>
                    Resumen de Hoy
                </h3>
            </div>
            <div class="p-6">
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Horas trabajadas:</span>
                        <span class="font-semibold text-gray-900"><?php echo number_format($stats['horas_hoy'], 1); ?>h</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Último reporte:</span>
                        <span class="text-sm text-gray-900">
                            <?php 
                            $ultimo_reporte = $stats['ultimo_reporte'] ?? $stats['ultimo_comprobante'] ?? null;
                            if ($ultimo_reporte) {
                                $fecha = new DateTime($ultimo_reporte);
                                echo $fecha->format('d/m/Y');
                            } else {
                                echo 'Ninguno';
                            }
                            ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

