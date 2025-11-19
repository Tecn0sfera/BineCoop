<?php
/**
 * Sistema de almacenamiento de comprobantes de pago en base de datos
 * Versión para htdocscop - usa su propia conexión a la base de datos
 */

// Asegurar que tenemos conexión a la base de datos de htdocscop
// IMPORTANTE: Si ya existe $mysqli (cargado desde api_worker.php), NO crear una nueva conexión
if (!isset($mysqli)) {
    require_once __DIR__ . '/../db.php';
}

if (!isset($mysqli)) {
    error_log("ERROR comprobantes_db.php (htdocscop): No se pudo cargar la conexión a la base de datos");
} else {
    // Log de la base de datos que estamos usando
    try {
        $db_name = $mysqli->query("SELECT DATABASE()")->fetch_row()[0];
        error_log("DEBUG comprobantes_db.php (htdocscop): Usando conexión existente - Base de datos: " . $db_name);
    } catch (Exception $e) {
        error_log("DEBUG comprobantes_db.php (htdocscop): Error al obtener nombre de BD: " . $e->getMessage());
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
    
    if (!isset($mysqli)) {
        error_log("ERROR getComprobanteById: No hay conexión a la base de datos");
        return null;
    }
    
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
 * Versión simplificada - siempre INSERT para nuevos comprobantes
 */
function saveComprobante($comprobante) {
    global $mysqli;
    
    if (!isset($mysqli)) {
        error_log("ERROR saveComprobante (htdocscop): No hay conexión a la base de datos");
        return false;
    }
    
    if (empty($comprobante['id'])) {
        // Generar ID único
        $comprobante['id'] = uniqid('comp_', true);
    }
    
    // Para nuevos comprobantes, siempre hacer INSERT (no verificar si existe)
    // Esto es más simple y evita problemas con getComprobanteById
    $query = "INSERT INTO comprobantes_pago 
              (id, trabajador_id, titulo, descripcion, nombre_archivo, ruta_archivo, 
               tamano_archivo, fecha_envio, estado, fecha_aprobacion, fecha_rechazo,
               aprobado_por, rechazado_por, razon_rechazo) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($query);
    
    if (!$stmt) {
        error_log("ERROR saveComprobante (htdocscop): Error preparando consulta: " . $mysqli->error);
        error_log("ERROR saveComprobante (htdocscop): Query: " . $query);
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
    
    error_log("DEBUG saveComprobante (htdocscop): Preparando INSERT - ID: " . $comprobante['id'] . ", trabajador_id: " . $comprobante['trabajador_id']);
    
    // Usar directamente los valores del array como en htdocspanel
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
    
    // Asegurar que autocommit está activado (autocommit es un método, no una propiedad)
    // Por defecto mysqli tiene autocommit activado, así que no necesitamos verificarlo
    
    $result = $stmt->execute();
    
    if (!$result) {
        error_log("ERROR saveComprobante (htdocscop): Error al ejecutar consulta: " . $stmt->error);
        error_log("ERROR saveComprobante (htdocscop): Query: " . $query);
        error_log("ERROR saveComprobante (htdocscop): Error SQL: " . $mysqli->error);
        error_log("ERROR saveComprobante (htdocscop): Datos - ID: " . ($comprobante['id'] ?? 'N/A') . ", trabajador_id: " . ($comprobante['trabajador_id'] ?? 'N/A'));
        $stmt->close();
        return false;
    }
    
    $affected_rows = $stmt->affected_rows;
    error_log("DEBUG saveComprobante (htdocscop): execute() retornó: " . ($result ? 'true' : 'false'));
    error_log("DEBUG saveComprobante (htdocscop): affected_rows: " . $affected_rows);
    
    $stmt->close();
    
    // Verificar inmediatamente después de cerrar el statement
    $verify_query = "SELECT COUNT(*) as total FROM comprobantes_pago WHERE id = ?";
    $verify_stmt = $mysqli->prepare($verify_query);
    if ($verify_stmt) {
        $verify_stmt->bind_param('s', $comprobante['id']);
        $verify_stmt->execute();
        $verify_result = $verify_stmt->get_result();
        $verify_row = $verify_result->fetch_assoc();
        $verify_stmt->close();
        
        if ($verify_row['total'] > 0) {
            error_log("SUCCESS saveComprobante (htdocscop): Comprobante guardado y verificado con ID: " . $comprobante['id']);
            return $comprobante['id'];
        } else {
            error_log("ERROR saveComprobante (htdocscop): Comprobante NO encontrado después de insertar. ID: " . $comprobante['id']);
            error_log("ERROR saveComprobante (htdocscop): Esto indica que el INSERT no se confirmó en la base de datos");
            return false;
        }
    } else {
        error_log("WARNING saveComprobante (htdocscop): No se pudo verificar el comprobante");
        // Aun así retornar el ID si execute() fue exitoso
        if ($result && $affected_rows > 0) {
            error_log("SUCCESS saveComprobante (htdocscop): Comprobante guardado con ID: " . $comprobante['id']);
            return $comprobante['id'];
        }
        return false;
    }
}

/**
 * Crear un nuevo comprobante
 */
function createComprobante($data) {
    global $mysqli;
    
    // Verificar que tenemos conexión
    if (!isset($mysqli)) {
        error_log("ERROR createComprobante (htdocscop): No hay conexión a la base de datos");
        return false;
    }
    
    // Obtener nombre de la base de datos actual
    try {
        $db_result = $mysqli->query("SELECT DATABASE()");
        if ($db_result) {
            $db_name = $db_result->fetch_row()[0];
            error_log("DEBUG createComprobante (htdocscop): Usando base de datos: " . $db_name);
        }
    } catch (Exception $e) {
        error_log("DEBUG createComprobante (htdocscop): Error al obtener nombre de BD: " . $e->getMessage());
    }
    
    // Verificar que la tabla existe
    $check_table = $mysqli->query("SHOW TABLES LIKE 'comprobantes_pago'");
    if (!$check_table || $check_table->num_rows == 0) {
        error_log("ERROR createComprobante (htdocscop): La tabla comprobantes_pago no existe. Ejecutar create_comprobantes_table.sql");
        // Listar tablas disponibles para diagnóstico
        try {
            $all_tables = $mysqli->query("SHOW TABLES");
            $table_list = [];
            while ($row = $all_tables->fetch_row()) {
                $table_list[] = $row[0];
            }
            error_log("DEBUG createComprobante (htdocscop): Tablas disponibles: " . implode(', ', $table_list));
        } catch (Exception $e) {
            error_log("DEBUG createComprobante (htdocscop): No se pudieron listar tablas: " . $e->getMessage());
        }
        return false;
    }
    
    error_log("DEBUG createComprobante (htdocscop): Datos recibidos - trabajador_id: " . ($data['trabajador_id'] ?? 'N/A') . ", titulo: " . ($data['titulo'] ?? 'N/A'));
    
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
    
    error_log("DEBUG createComprobante (htdocscop): Comprobante preparado con ID: " . $comprobante['id']);
    
    $result = saveComprobante($comprobante);
    
    if ($result) {
        error_log("SUCCESS createComprobante (htdocscop): Comprobante creado con ID: " . $comprobante['id']);
        
        // Verificar que realmente se guardó
        try {
            $verify = $mysqli->query("SELECT COUNT(*) as total FROM comprobantes_pago WHERE id = '" . $mysqli->real_escape_string($comprobante['id']) . "'");
            if ($verify) {
                $verify_row = $verify->fetch_assoc();
                error_log("DEBUG createComprobante (htdocscop): Verificación - Comprobante encontrado en BD: " . ($verify_row['total'] > 0 ? 'SÍ' : 'NO'));
            }
        } catch (Exception $e) {
            error_log("DEBUG createComprobante (htdocscop): Error al verificar: " . $e->getMessage());
        }
    } else {
        error_log("ERROR createComprobante (htdocscop): No se pudo guardar el comprobante. Verificar logs de saveComprobante.");
    }
    
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
    
    // Verificar que la tabla existe
    $check_table = $mysqli->query("SHOW TABLES LIKE 'comprobantes_pago'");
    if (!$check_table || $check_table->num_rows == 0) {
        error_log("ERROR getComprobantesPendientes: La tabla comprobantes_pago no existe");
        return [];
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
    return $comprobantes;
}

/**
 * Contar comprobantes por estado
 */
function countComprobantesByEstado($estado) {
    global $mysqli;
    
    if (!isset($mysqli)) {
        error_log("ERROR countComprobantesByEstado: No hay conexión a la base de datos");
        return 0;
    }
    
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

