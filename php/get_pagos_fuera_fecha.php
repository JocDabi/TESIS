<?php
header('Content-Type: application/json');

// URL del endpoint de la API
$api_url = 'https://payment-gateway-backend-production.up.railway.app/transaction/transaction-json';

// Obtener datos de la API
$json_response = @file_get_contents($api_url);

if ($json_response === FALSE) {
    // Si hay error al conectar con la API, devolver error
    http_response_code(500);
    echo json_encode([
        'error' => 'No se pudo conectar con el servidor de pagos',
        'details' => error_get_last()['message'] ?? 'Error desconocido'
    ]);
    exit();
}

// Decodificar el JSON de la API
$all_pagos = json_decode($json_response, true);

if ($all_pagos === NULL) {
    // Si hay error al decodificar el JSON
    http_response_code(500);
    echo json_encode([
        'error' => 'Los datos recibidos no son válidos',
        'details' => json_last_error_msg()
    ]);
    exit();
}

// Establecer la zona horaria predeterminada
date_default_timezone_set('UTC'); // Usa UTC para consistencia con los datos JSON

// --- Manejo de la Fecha de Corte desde el Frontend ---
$fecha_corte_str_frontend = $_GET['fecha_corte'] ?? ''; // Formato esperado: dd/mm/aaaa

$fecha_corte_dt = null; // Objeto DateTime de la fecha de corte para comparación
$fecha_corte_display = ''; // String de la fecha de corte para mostrar en el frontend

if (!empty($fecha_corte_str_frontend)) {
    // Intentar parsear dd/mm/aaaa a un objeto DateTime
    $dmy_parts = explode('/', $fecha_corte_str_frontend);
    // Validar el formato y que sea una fecha válida antes de intentar crear DateTime
    if (count($dmy_parts) === 3 && checkdate($dmy_parts[1], $dmy_parts[0], $dmy_parts[2])) {
        $fecha_corte_parsed_ymd = $dmy_parts[2] . '-' . $dmy_parts[1] . '-' . $dmy_parts[0]; // Convertir a YYYY-MM-DD
        try {
            $fecha_corte_dt = new DateTime($fecha_corte_parsed_ymd, new DateTimeZone('UTC'));
            $fecha_corte_dt->setTime(0, 0, 0); // Establecer la hora a medianoche para comparar solo fechas
            $fecha_corte_display = $fecha_corte_parsed_ymd; // Para mostrar en el frontend
        } catch (Exception $e) {
            error_log("Error parsing fecha_corte from frontend: " . $e->getMessage());
            // Fallback a la fecha actual si hay error
        }
    } else {
        error_log("Invalid date format for fecha_corte: " . $fecha_corte_str_frontend);
    }
}

// Si $fecha_corte_dt no se pudo parsear o no se proporcionó, usar la fecha actual (hoy) como predeterminada
if ($fecha_corte_dt === null) {
    $fecha_corte_dt = new DateTime('now', new DateTimeZone('UTC'));
    $fecha_corte_dt->setTime(0, 0, 0); // La fecha de corte para hoy es hoy a medianoche
    $fecha_corte_display = $fecha_corte_dt->format('Y-m-d'); // Formato YYYY-MM-DD para display
}

// --- Pre-procesamiento de los pagos: Preparar 'createdAtDt' para la lógica de filtrado ---
foreach ($all_pagos as &$pago) {
    if (isset($pago['createdAt'])) {
        try {
            $createdDateObj = new DateTime($pago['createdAt'], new DateTimeZone('UTC'));
            
            // Para la visualización en la tabla (columna 'Fecha Realizado')
            $pago['fechaRealizadoFormateada'] = $createdDateObj->format('Y-m-d');

            // Para la lógica de filtrado: Objeto DateTime del createdAt a medianoche
            $pago['createdAtDt'] = $createdDateObj;
            $pago['createdAtDt']->setTime(0, 0, 0); // Importante para comparar solo las fechas
        } catch (Exception $e) {
            error_log("Error processing createdAt for payment ID " . ($pago['id'] ?? 'N/A') . ": " . $e->getMessage());
            $pago['fechaRealizadoFormateada'] = null;
            $pago['createdAtDt'] = null;
        }
    } else {
        $pago['fechaRealizadoFormateada'] = null;
        $pago['createdAtDt'] = null;
    }
}
unset($pago); // Romper la referencia al último elemento del bucle

// --- LÓGICA DE FILTRADO: Pagos cuya Fecha Realizado es POSTERIOR a la Fecha de Corte ---
$pagos_fuera_de_fecha = array_filter($all_pagos, function($pago) use ($fecha_corte_dt) {
    // Un pago está "fuera de fecha" si su fecha de creación (createdAtDt) es estrictamente posterior a la fecha de corte.
    return isset($pago['createdAtDt']) && $pago['createdAtDt'] > $fecha_corte_dt;
});

// Re-indexar el array después de filtrar para evitar claves numéricas no secuenciales
$pagos_fuera_de_fecha = array_values($pagos_fuera_de_fecha);

// --- Aplicar Filtro por Estado (opcional) ---
$filtro_status = $_GET['estado'] ?? '';

if (!empty($filtro_status) && $filtro_status !== 'Todos') {
    $pagos_fuera_de_fecha = array_filter($pagos_fuera_de_fecha, function($pago) use ($filtro_status) {
        return isset($pago['status']) && $pago['status'] === $filtro_status;
    });
    $pagos_fuera_de_fecha = array_values($pagos_fuera_de_fecha); // Re-indexar
}

// --- Preparar Respuesta Final para el Frontend ---
// Mapear los datos filtrados al formato que el frontend espera para las columnas de la tabla.
$response_data_for_frontend = [];
foreach ($pagos_fuera_de_fecha as $pago) {
    $response_data_for_frontend[] = [
        'id' => $pago['id'],
        'fechaRealizado' => $pago['fechaRealizadoFormateada'] ?? 'N/A', // 'YYYY-MM-DD'
        'fechaCorte' => $fecha_corte_display,                 // La fecha de corte del filtro para todos los resultados
        'cliente' => ($pago['user']['firstName'] ?? '') . ' ' . ($pago['user']['lastName'] ?? ''),
        'monto' => '$' . number_format((float)$pago['amount'], 2, '.', ','),
        'estado' => isset($pago['status']) ? str_replace('_', ' ', $pago['status']) : 'N/A', // 'in_process' -> 'in process'
        'acciones' => 'Ver Detalle', // Mantener esto fijo o según tu lógica
        // Incluir los datos originales del pago en el array para el modal
        'description' => $pago['description'] ?? 'Sin descripción',
        'paymentMethod' => $pago['paymentMethod'] ?? 'N/A',
        'user' => [
            'id' => $pago['user']['id'] ?? 'N/A',
            'email' => $pago['user']['email'] ?? 'N/A',
            'identificationNumber' => $pago['user']['identificationNumber'] ?? 'N/A',
            'firstName' => $pago['user']['firstName'] ?? '',
            'lastName' => $pago['user']['lastName'] ?? ''
        ]
    ];
}

echo json_encode([
    'data' => $response_data_for_frontend,
    'total' => count($response_data_for_frontend)
]);

exit();
?>