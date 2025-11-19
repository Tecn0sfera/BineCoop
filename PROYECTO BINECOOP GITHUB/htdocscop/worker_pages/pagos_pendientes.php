<?php
// Función limpiarDato si no está definida
if (!function_exists('limpiarDato')) {
    function limpiarDato($dato) {
        return htmlspecialchars(trim($dato), ENT_QUOTES, 'UTF-8');
    }
}

// Suponer tarifa por hora (esto debería venir de la configuración del usuario o sistema)
$tarifa_por_hora = 15.00; // Puedes hacer esto configurable

// Calcular pagos basados en horas aprobadas
$pagos_pendientes = [];
$pagos_pagados = [];
$meses_data = [];

// Obtener comprobantes del trabajador (desde base de datos)
require_once __DIR__ . '/../includes/comprobantes_db.php';
$comprobantes_trabajador = [];
$todos_comprobantes = getComprobantes();
foreach ($todos_comprobantes as $comp) {
    if ($comp['trabajador_id'] == $worker_id) {
        $fecha_envio = $comp['fecha_envio'];
        if ($fecha_envio) {
            $fecha = new DateTime($fecha_envio);
            $mes_comprobante = $fecha->format('Y-m');
            if (!isset($comprobantes_trabajador[$mes_comprobante])) {
                $comprobantes_trabajador[$mes_comprobante] = [];
            }
            $comprobantes_trabajador[$mes_comprobante][] = $comp;
        }
    }
}

// Obtener horas aprobadas por mes (últimos 6 meses)
try {
    $query_meses = "SELECT 
        DATE_FORMAT(fecha_inicio, '%Y-%m') as mes,
        DATE_FORMAT(fecha_inicio, '%M %Y') as mes_nombre,
        COALESCE(SUM(horas), 0) as total_horas,
        COUNT(*) as total_registros
        FROM tiempo_trabajado 
        WHERE trabajador_id = ? AND estado = 'aprobado'
        AND fecha_inicio >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(fecha_inicio, '%Y-%m')
        ORDER BY mes DESC";
    
    $stmt_meses = $mysqli->prepare($query_meses);
    if ($stmt_meses) {
        $stmt_meses->bind_param('i', $worker_id);
        $stmt_meses->execute();
        $result_meses = $stmt_meses->get_result();
        
        while ($row = $result_meses->fetch_assoc()) {
            $mes = $row['mes'];
            $tiene_comprobante = isset($comprobantes_trabajador[$mes]) && count($comprobantes_trabajador[$mes]) > 0;
            
            $meses_data[] = [
                'mes' => $mes,
                'mes_nombre' => $row['mes_nombre'],
                'total_horas' => floatval($row['total_horas']),
                'total_registros' => intval($row['total_registros']),
                'monto' => floatval($row['total_horas']) * $tarifa_por_hora,
                'tiene_comprobante' => $tiene_comprobante
            ];
        }
        $stmt_meses->close();
    }
} catch (Exception $e) {
    error_log("Error al obtener datos de meses: " . $e->getMessage());
}

// Calcular totales
$total_horas_pendientes = 0;
$total_monto_pendiente = 0;
$total_horas_pagadas = 0;
$total_monto_pagado = 0;

// Simular pagos: considerar que los meses anteriores a este mes están "pagados"
$mes_actual = date('Y-m');
foreach ($meses_data as $mes_data) {
    if ($mes_data['mes'] < $mes_actual) {
        // Meses anteriores: considerar como pagados
        $pagos_pagados[] = $mes_data;
        $total_horas_pagadas += $mes_data['total_horas'];
        $total_monto_pagado += $mes_data['monto'];
    } else {
        // Mes actual y futuros: pendientes
        $pagos_pendientes[] = $mes_data;
        $total_horas_pendientes += $mes_data['total_horas'];
        $total_monto_pendiente += $mes_data['monto'];
    }
}

// Obtener horas por día de la última semana para gráfica
$horas_semana = [];
try {
    $query_semana = "SELECT 
        DATE(fecha_inicio) as fecha,
        COALESCE(SUM(horas), 0) as horas
        FROM tiempo_trabajado 
        WHERE trabajador_id = ? 
        AND estado = 'aprobado'
        AND fecha_inicio >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY DATE(fecha_inicio)
        ORDER BY fecha ASC";
    
    $stmt_semana = $mysqli->prepare($query_semana);
    if ($stmt_semana) {
        $stmt_semana->bind_param('i', $worker_id);
        $stmt_semana->execute();
        $result_semana = $stmt_semana->get_result();
        
        while ($row = $result_semana->fetch_assoc()) {
            $horas_semana[] = [
                'fecha' => $row['fecha'],
                'horas' => floatval($row['horas'])
            ];
        }
        $stmt_semana->close();
    }
} catch (Exception $e) {
    error_log("Error al obtener horas de la semana: " . $e->getMessage());
}

// Obtener horas por mes para gráfica de barras
$horas_meses = [];
try {
    $query_meses_grafica = "SELECT 
        DATE_FORMAT(fecha_inicio, '%Y-%m') as mes,
        DATE_FORMAT(fecha_inicio, '%b') as mes_corto,
        COALESCE(SUM(horas), 0) as horas
        FROM tiempo_trabajado 
        WHERE trabajador_id = ? 
        AND estado = 'aprobado'
        AND fecha_inicio >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(fecha_inicio, '%Y-%m')
        ORDER BY mes ASC";
    
    $stmt_meses_grafica = $mysqli->prepare($query_meses_grafica);
    if ($stmt_meses_grafica) {
        $stmt_meses_grafica->bind_param('i', $worker_id);
        $stmt_meses_grafica->execute();
        $result_meses_grafica = $stmt_meses_grafica->get_result();
        
        while ($row = $result_meses_grafica->fetch_assoc()) {
            $horas_meses[] = [
                'mes' => $row['mes_corto'],
                'horas' => floatval($row['horas'])
            ];
        }
        $stmt_meses_grafica->close();
    }
} catch (Exception $e) {
    error_log("Error al obtener horas por mes: " . $e->getMessage());
}

// Preparar datos para gráficas JavaScript
$chart_semana_labels = [];
$chart_semana_data = [];

for ($i = 6; $i >= 0; $i--) {
    $fecha = date('Y-m-d', strtotime("-$i days"));
    $fecha_formato = date('d/m', strtotime("-$i days"));
    $chart_semana_labels[] = $fecha_formato;
    
    $horas_encontradas = false;
    foreach ($horas_semana as $hora_dia) {
        if ($hora_dia['fecha'] === $fecha) {
            $chart_semana_data[] = $hora_dia['horas'];
            $horas_encontradas = true;
            break;
        }
    }
    if (!$horas_encontradas) {
        $chart_semana_data[] = 0;
    }
}

$chart_meses_labels = [];
$chart_meses_data = [];
foreach ($horas_meses as $mes_data) {
    $chart_meses_labels[] = $mes_data['mes'];
    $chart_meses_data[] = $mes_data['horas'];
}
?>

<!-- Header -->
<div class="mb-6 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                <i class="ti ti-wallet text-green-600 mr-3"></i>
                Pagos Pendientes
            </h2>
            <p class="text-sm text-gray-500 mt-1">Consulta tus pagos pendientes y estadísticas de ingresos</p>
        </div>
        <div class="text-right">
            <p class="text-xs text-gray-500">Tarifa por hora</p>
            <p class="text-lg font-bold text-green-600">$<?php echo number_format($tarifa_por_hora, 2); ?></p>
        </div>
    </div>
    
    <!-- Resumen de pagos -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mt-6">
        <div class="bg-gradient-to-br from-green-500 to-green-600 p-4 rounded-lg text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium opacity-90 mb-1">Pendiente</p>
                    <p class="text-2xl font-bold">$<?php echo number_format($total_monto_pendiente, 2); ?></p>
                    <p class="text-xs opacity-75 mt-1"><?php echo number_format($total_horas_pendientes, 1); ?>h</p>
                </div>
                <i class="ti ti-clock text-3xl opacity-50"></i>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-4 rounded-lg text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium opacity-90 mb-1">Pagado</p>
                    <p class="text-2xl font-bold">$<?php echo number_format($total_monto_pagado, 2); ?></p>
                    <p class="text-xs opacity-75 mt-1"><?php echo number_format($total_horas_pagadas, 1); ?>h</p>
                </div>
                <i class="ti ti-check text-3xl opacity-50"></i>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 p-4 rounded-lg text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium opacity-90 mb-1">Total</p>
                    <p class="text-2xl font-bold">$<?php echo number_format($total_monto_pendiente + $total_monto_pagado, 2); ?></p>
                    <p class="text-xs opacity-75 mt-1"><?php echo number_format($total_horas_pendientes + $total_horas_pagadas, 1); ?>h</p>
                </div>
                <i class="ti ti-wallet text-3xl opacity-50"></i>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-orange-500 to-orange-600 p-4 rounded-lg text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium opacity-90 mb-1">Meses</p>
                    <p class="text-2xl font-bold"><?php echo count($meses_data); ?></p>
                    <p class="text-xs opacity-75 mt-1">Con registros</p>
                </div>
                <i class="ti ti-calendar text-3xl opacity-50"></i>
            </div>
        </div>
    </div>
</div>

<!-- Gráficas -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Gráfica de horas de la semana -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Horas Trabajadas - Última Semana</h3>
        <div style="height: 300px;">
            <canvas id="chartSemana"></canvas>
        </div>
    </div>
    
    <!-- Gráfica de horas por mes -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Horas por Mes - Últimos 6 Meses</h3>
        <div style="height: 300px;">
            <canvas id="chartMeses"></canvas>
        </div>
    </div>
</div>

<!-- Tabla de pagos pendientes -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Pagos Pendientes</h3>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mes</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Horas</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registros</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (!empty($pagos_pendientes)): ?>
                    <?php foreach ($pagos_pendientes as $pago): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    <?php echo $pago['mes_nombre']; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-mono font-semibold text-gray-900">
                                    <?php echo number_format($pago['total_horas'], 2); ?>h
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <?php echo $pago['total_registros']; ?> registro(s)
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-green-600">
                                    $<?php echo number_format($pago['monto'], 2); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-2">
                                    <span class="px-3 py-1 inline-flex items-center text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        <i class="ti ti-hourglass mr-1"></i>
                                        Pendiente
                                    </span>
                                    <?php if ($pago['tiene_comprobante']): ?>
                                        <button onclick="marcarComoListo('<?php echo htmlspecialchars($pago['mes'], ENT_QUOTES); ?>')" 
                                                class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-xs font-medium rounded-lg transition-colors flex items-center">
                                            <i class="ti ti-check mr-1"></i>
                                            Marcar como listo
                                        </button>
                                    <?php else: ?>
                                        <span class="px-3 py-1 inline-flex items-center text-xs text-gray-500 bg-gray-100 rounded-lg" title="Debe subir un comprobante primero">
                                            <i class="ti ti-file-off mr-1"></i>
                                            Sin comprobante
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                    <i class="ti ti-check text-3xl text-gray-300"></i>
                                </div>
                                <p class="text-lg font-medium text-gray-600">No hay pagos pendientes</p>
                                <p class="text-sm text-gray-400 mt-1">Todos los pagos han sido procesados</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Tabla de pagos pagados (últimos 3 meses) -->
<?php if (!empty($pagos_pagados)): ?>
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Pagos Recientes</h3>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mes</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Horas</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach (array_slice($pagos_pagados, 0, 3) as $pago): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                <?php echo $pago['mes_nombre']; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-mono text-gray-900">
                                <?php echo number_format($pago['total_horas'], 2); ?>h
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-blue-600">
                                $<?php echo number_format($pago['monto'], 2); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 inline-flex items-center text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                <i class="ti ti-check mr-1"></i>
                                Pagado
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<script>
// Función para marcar pago como listo
function marcarComoListo(mes) {
    if (!confirm('¿Desea marcar este pago como listo? Esto notificará al administrador.')) {
        return;
    }
    
    fetch('api_worker.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=marcar_pago_listo&mes=' + encodeURIComponent(mes)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Pago marcado como listo exitosamente');
            location.reload();
        } else {
            alert('Error: ' + (data.error || data.message || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al marcar el pago como listo');
    });
}

// Inicializar gráficas cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Gráfica de línea - Horas de la semana
    const ctxSemana = document.getElementById('chartSemana');
    if (ctxSemana) {
        const semanaData = <?php echo json_encode($chart_semana_data); ?>;
        const maxValue = Math.max(...semanaData, 1);
        
        new Chart(ctxSemana, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($chart_semana_labels); ?>,
                datasets: [{
                    label: 'Horas Trabajadas',
                    data: semanaData,
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 2,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: maxValue > 0 ? maxValue * 1.2 : 1,
                        ticks: {
                            callback: function(value) {
                                return value + 'h';
                            }
                        }
                    }
                }
            }
        });
    }
    
    // Gráfica de barras - Horas por mes
    const ctxMeses = document.getElementById('chartMeses');
    if (ctxMeses) {
        const mesesData = <?php echo json_encode($chart_meses_data); ?>;
        const maxValue = Math.max(...mesesData, 1);
        
        new Chart(ctxMeses, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($chart_meses_labels); ?>,
                datasets: [{
                    label: 'Horas Aprobadas',
                    data: mesesData,
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 2,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: maxValue > 0 ? maxValue * 1.2 : 1,
                        ticks: {
                            callback: function(value) {
                                return value + 'h';
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>

