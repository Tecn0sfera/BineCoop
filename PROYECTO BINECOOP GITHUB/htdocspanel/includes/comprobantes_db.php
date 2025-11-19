<?php
/**
 * Sistema de almacenamiento de comprobantes de pago en base de datos
 */

// Asegurar que tenemos conexión a la base de datos
// En htdocspanel, siempre usar la conexión local
if (!isset($mysqli)) {
    // Intentar cargar desde htdocspanel/includes/db.php primero
    $db_path = __DIR__ . '/db.php';
    if (file_exists($db_path)) {
        require_once $db_path;
        if (isset($mysqli)) {
            error_log("DEBUG comprobantes_db.php (htdocspanel): Conexión cargada desde: " . $db_path);
            error_log("DEBUG comprobantes_db.php: Base de datos: " . $mysqli->query("SELECT DATABASE()")->fetch_row()[0]);
        }
    }
    
    if (!isset($mysqli)) {
        error_log("ERROR comprobantes_db.php (htdocspanel): No se pudo cargar la conexión a la base de datos desde: " . $db_path);
    }
} else {
    error_log("DEBUG comprobantes_db.php (htdocspanel): Usando conexión mysqli existente");
    // Log de la base de datos actual
    try {
        $db_name = $mysqli->query("SELECT DATABASE()")->fetch_row()[0];
        error_log("DEBUG comprobantes_db.php: Base de datos actual: " . $db_name);
    } catch (Exception $e) {
        error_log("DEBUG comprobantes_db.php: No se pudo obtener nombre de BD: " . $e->getMessage());
    }
}

/**
 * Obtener todos los comprobantes
 */
function getComprobantes($estado = null) {
    global $mysqli;
    
    if (!isset($mysqli)) {
        error_log("ERROR getComprobantes: No hay conexión a la base de datos");
        return [];
    }
    
    // Verificar que la tabla existe
    $check_table = $mysqli->query("SHOW TABLES LIKE 'comprobantes_pago'");
    if (!$check_table || $check_table->num_rows == 0) {
        error_log("ERROR getComprobantes: La tabla comprobantes_pago no existe. Ejecutar create_comprobantes_table.sql");
        return [];
    }
    
    if ($estado === null) {
        $query = "SELECT * FROM comprobantes_pago ORDER BY fecha_envio DESC";
        $stmt = $mysqli->prepare($query);
    } else {
        $query = "SELECT * FROM comprobantes_pago WHERE estado = ? ORDER BY fecha_envio DESC";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('s', $estado);
    }
    
    if (!$stmt) {
        error_log("Error preparando consulta getComprobantes: " . $mysqli->error);
        return [];
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $comprobantes = [];
    
    while ($row = $result->fetch_assoc()) {
        $comprobantes[] = $row;
    }
    
    $stmt->close();
    return $comprobantes;
}

/**
 * Obtener un comprobante por ID
 */
function getComprobanteById($id) {
    global $mysqli;
    
    $query = "SELECT * FROM comprobantes_pago WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    
    if (!$stmt) {
        error_log("Error preparando consulta getComprobanteById: " . $mysqli->error);
        return null;
    }
    
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $comprobante = $result->fetch_assoc();
    $stmt->close();
    
    return $comprobante ?: null;
}

/**
 * Guardar un comprobante (crear o actualizar)
 */
function saveComprobante($comprobante) {
    global $mysqli;
    
    if (!isset($mysqli)) {
        error_log("ERROR saveComprobante: No hay conexión a la base de datos");
        return false;
    }
    
    if (empty($comprobante['id'])) {
        // Generar ID único
        $comprobante['id'] = uniqid('comp_', true);
    }
    
    // Verificar si existe
    $existing = getComprobanteById($comprobante['id']);
    
    if ($existing) {
        // Actualizar
        $query = "UPDATE comprobantes_pago SET 
                  trabajador_id = ?, titulo = ?, descripcion = ?, 
                  nombre_archivo = ?, ruta_archivo = ?, tamano_archivo = ?,
                  estado = ?, fecha_aprobacion = ?, fecha_rechazo = ?,
                  aprobado_por = ?, rechazado_por = ?, razon_rechazo = ?
                  WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        
        if (!$stmt) {
            error_log("Error preparando consulta update comprobante: " . $mysqli->error);
            return false;
        }
        
        $descripcion = $comprobante['descripcion'] ?? null;
        $tamano = $comprobante['tamano_archivo'] ?? 0;
        $estado = $comprobante['estado'] ?? 'pendiente';
        $fecha_aprobacion = $comprobante['fecha_aprobacion'] ?? null;
        $fecha_rechazo = $comprobante['fecha_rechazo'] ?? null;
        $aprobado_por = $comprobante['aprobado_por'] ?? null;
        $rechazado_por = $comprobante['rechazado_por'] ?? null;
        $razon_rechazo = $comprobante['razon_rechazo'] ?? null;
        
        $stmt->bind_param('issssisssiiss',
            $comprobante['trabajador_id'],
            $comprobante['titulo'],
            $descripcion,
            $comprobante['nombre_archivo'],
            $comprobante['ruta_archivo'],
            $tamano,
            $estado,
            $fecha_aprobacion,
            $fecha_rechazo,
            $aprobado_por,
            $rechazado_por,
            $razon_rechazo,
            $comprobante['id']
        );
    } else {
        // Insertar
        $query = "INSERT INTO comprobantes_pago 
                  (id, trabajador_id, titulo, descripcion, nombre_archivo, ruta_archivo, 
                   tamano_archivo, fecha_envio, estado, fecha_aprobacion, fecha_rechazo,
                   aprobado_por, rechazado_por, razon_rechazo) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($query);
        
        if (!$stmt) {
            error_log("Error preparando consulta insert comprobante: " . $mysqli->error);
            return false;
        }
        
        $fecha_envio = $comprobante['fecha_envio'] ?? date('Y-m-d H:i:s');
        $estado = $comprobante['estado'] ?? 'pendiente';
        $descripcion = $comprobante['descripcion'] ?? null;
        $tamano = $comprobante['tamano_archivo'] ?? 0;
        $fecha_aprobacion = $comprobante['fecha_aprobacion'] ?? null;
        $fecha_rechazo = $comprobante['fecha_rechazo'] ?? null;
        $aprobado_por = $comprobante['aprobado_por'] ?? null;
        $rechazado_por = $comprobante['rechazado_por'] ?? null;
        $razon_rechazo = $comprobante['razon_rechazo'] ?? null;
        
        $stmt->bind_param('sissssisssiiss',
            $comprobante['id'],
            $comprobante['trabajador_id'],
            $comprobante['titulo'],
            $descripcion,
            $comprobante['nombre_archivo'],
            $comprobante['ruta_archivo'],
            $tamano,
            $fecha_envio,
            $estado,
            $fecha_aprobacion,
            $fecha_rechazo,
            $aprobado_por,
            $rechazado_por,
            $razon_rechazo
        );
    }
    
    $result = $stmt->execute();
    
    if (!$result) {
        error_log("ERROR saveComprobante: Error al ejecutar consulta: " . $stmt->error);
        $stmt->close();
        return false;
    }
    
    $stmt->close();
    
    error_log("SUCCESS saveComprobante: Comprobante guardado con ID: " . $comprobante['id']);
    return $comprobante['id'];
}

/**
 * Crear un nuevo comprobante
 */
function createComprobante($data) {
    global $mysqli;
    
    // Verificar que tenemos conexión
    if (!isset($mysqli)) {
        error_log("ERROR createComprobante: No hay conexión a la base de datos");
        return false;
    }
    
    // Verificar que la tabla existe
    $check_table = $mysqli->query("SHOW TABLES LIKE 'comprobantes_pago'");
    if (!$check_table || $check_table->num_rows == 0) {
        error_log("ERROR createComprobante: La tabla comprobantes_pago no existe");
        return false;
    }
    
    $comprobante = [
        'id' => uniqid('comp_', true),
        'trabajador_id' => intval($data['trabajador_id']),
        'titulo' => $data['titulo'],
        'descripcion' => $data['descripcion'] ?? '',
        'nombre_archivo' => $data['nombre_archivo'],
        'ruta_archivo' => $data['ruta_archivo'],
        'tamano_archivo' => intval($data['tamano_archivo']),
        'fecha_envio' => date('Y-m-d H:i:s'),
        'estado' => 'pendiente'
    ];
    
    $result = saveComprobante($comprobante);
    
    if ($result) {
        error_log("SUCCESS createComprobante: Comprobante creado con ID: " . $comprobante['id']);
    } else {
        error_log("ERROR createComprobante: No se pudo guardar el comprobante");
    }
    
    return $result;
}

/**
 * Aprobar un comprobante
 */
function approveComprobante($id, $admin_id) {
    global $mysqli;
    
    $comprobante = getComprobanteById($id);
    if (!$comprobante) {
        return false;
    }
    
    if ($comprobante['estado'] !== 'pendiente') {
        return false;
    }
    
    $query = "UPDATE comprobantes_pago SET 
              estado = 'aprobado', 
              fecha_aprobacion = NOW(),
              aprobado_por = ?
              WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    
    if (!$stmt) {
        error_log("Error preparando consulta approveComprobante: " . $mysqli->error);
        return false;
    }
    
    $stmt->bind_param('is', $admin_id, $id);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

/**
 * Rechazar un comprobante
 */
function rejectComprobante($id, $admin_id, $razon = '') {
    global $mysqli;
    
    $comprobante = getComprobanteById($id);
    if (!$comprobante) {
        return false;
    }
    
    if ($comprobante['estado'] !== 'pendiente') {
        return false;
    }
    
    $query = "UPDATE comprobantes_pago SET 
              estado = 'rechazado', 
              fecha_rechazo = NOW(),
              rechazado_por = ?,
              razon_rechazo = ?
              WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    
    if (!$stmt) {
        error_log("Error preparando consulta rejectComprobante: " . $mysqli->error);
        return false;
    }
    
    $stmt->bind_param('iss', $admin_id, $razon, $id);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

/**
 * Obtener comprobantes pendientes
 */
function getComprobantesPendientes($limit = null) {
    global $mysqli;
    
    if (!isset($mysqli)) {
        error_log("ERROR getComprobantesPendientes: No hay conexión a la base de datos");
        return [];
    }
    
    // Obtener nombre de la base de datos actual
    try {
        $db_result = $mysqli->query("SELECT DATABASE()");
        if ($db_result) {
            $db_name = $db_result->fetch_row()[0];
            error_log("DEBUG getComprobantesPendientes: Consultando en base de datos: " . $db_name);
        }
    } catch (Exception $e) {
        error_log("DEBUG getComprobantesPendientes: Error al obtener nombre de BD: " . $e->getMessage());
    }
    
    // Verificar que la tabla existe
    $check_table = $mysqli->query("SHOW TABLES LIKE 'comprobantes_pago'");
    if (!$check_table || $check_table->num_rows == 0) {
        error_log("ERROR getComprobantesPendientes: La tabla comprobantes_pago no existe en la base de datos actual");
        // Intentar listar todas las tablas para diagnóstico
        try {
            $all_tables = $mysqli->query("SHOW TABLES");
            $table_list = [];
            while ($row = $all_tables->fetch_row()) {
                $table_list[] = $row[0];
            }
            error_log("DEBUG getComprobantesPendientes: Tablas disponibles: " . implode(', ', $table_list));
        } catch (Exception $e) {
            error_log("DEBUG getComprobantesPendientes: No se pudieron listar tablas: " . $e->getMessage());
        }
        return [];
    }
    
    // Contar total de comprobantes (todos los estados)
    try {
        $count_all = $mysqli->query("SELECT COUNT(*) as total FROM comprobantes_pago");
        if ($count_all) {
            $total_row = $count_all->fetch_assoc();
            error_log("DEBUG getComprobantesPendientes: Total de comprobantes en BD: " . $total_row['total']);
        }
        
        $count_pendientes = $mysqli->query("SELECT COUNT(*) as total FROM comprobantes_pago WHERE estado = 'pendiente'");
        if ($count_pendientes) {
            $pendientes_row = $count_pendientes->fetch_assoc();
            error_log("DEBUG getComprobantesPendientes: Comprobantes pendientes: " . $pendientes_row['total']);
        }
    } catch (Exception $e) {
        error_log("DEBUG getComprobantesPendientes: Error al contar comprobantes: " . $e->getMessage());
    }
    
    if ($limit === null) {
        $query = "SELECT * FROM comprobantes_pago WHERE estado = 'pendiente' ORDER BY fecha_envio DESC";
        $stmt = $mysqli->prepare($query);
    } else {
        $query = "SELECT * FROM comprobantes_pago WHERE estado = 'pendiente' ORDER BY fecha_envio DESC LIMIT ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('i', $limit);
    }
    
    if (!$stmt) {
        error_log("Error preparando consulta getComprobantesPendientes: " . $mysqli->error);
        return [];
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $comprobantes = [];
    
    while ($row = $result->fetch_assoc()) {
        $comprobantes[] = $row;
    }
    
    $stmt->close();
    
    error_log("DEBUG getComprobantesPendientes: Retornando " . count($comprobantes) . " comprobantes pendientes");
    return $comprobantes;
}

/**
 * Contar comprobantes por estado
 */
function countComprobantesByEstado($estado) {
    global $mysqli;
    
    $query = "SELECT COUNT(*) as total FROM comprobantes_pago WHERE estado = ?";
    $stmt = $mysqli->prepare($query);
    
    if (!$stmt) {
        error_log("Error preparando consulta countComprobantesByEstado: " . $mysqli->error);
        return 0;
    }
    
    $stmt->bind_param('s', $estado);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return intval($row['total'] ?? 0);
}

