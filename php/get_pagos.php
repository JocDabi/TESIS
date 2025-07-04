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

// --- Lógica para Recibir y Aplicar Filtros ---
$filtered_pagos = $all_pagos; // Empezamos con todos los pagos

// Agrega esto en la sección donde procesas los parámetros GET (antes de aplicar los filtros)
$orden = $_GET['order'] ?? 'desc'; // 'asc' o 'desc'
$ordenar_por = $_GET['order_by'] ?? 'createdAt'; // Campo por el que ordenar

// --- Ordenación ---
usort($filtered_pagos, function($a, $b) use ($ordenar_por, $orden) {
    $valorA = $a[$ordenar_por] ?? '';
    $valorB = $b[$ordenar_por] ?? '';
    
    if ($orden === 'asc') {
        return $valorA <=> $valorB;
    } else {
        return $valorB <=> $valorA;
    }
});

// Recibir parámetros de filtro del frontend (usando GET)
$filtro_fecha_inicio = $_GET['fecha_inicio'] ?? '';
$filtro_fecha_fin = $_GET['fecha_fin'] ?? '';
$filtro_status = $_GET['estado'] ?? '';

// Aplicar filtro por estado
if (!empty($filtro_status)) {
    $filtered_pagos = array_filter($filtered_pagos, function($pago) use ($filtro_status) {
        return isset($pago['status']) && $pago['status'] === $filtro_status;
    });
    $filtered_pagos = array_values($filtered_pagos); // Re-indexar el array después de filtrar
}

// Aplicar filtro por rango de fechas
if (!empty($filtro_fecha_inicio) || !empty($filtro_fecha_fin)) {
    $filtered_pagos = array_filter($filtered_pagos, function($pago) use ($filtro_fecha_inicio, $filtro_fecha_fin) {
        if (!isset($pago['createdAt'])) {
            return false; // No incluir pagos sin fecha
        }
        
        try {
            $fecha_pago = new DateTime($pago['createdAt']);
            $fecha_pago_str = $fecha_pago->format('Y-m-d'); // Formato YYYY-MM-DD
            
            // Convertir fechas de filtro a DateTime para mejor comparación
            $fecha_inicio_dt = !empty($filtro_fecha_inicio) ? new DateTime($filtro_fecha_inicio) : null;
            $fecha_fin_dt = !empty($filtro_fecha_fin) ? new DateTime($filtro_fecha_fin) : null;
            
            $match_fecha_inicio = empty($filtro_fecha_inicio) || ($fecha_pago >= $fecha_inicio_dt);
            $match_fecha_fin = empty($filtro_fecha_fin) || ($fecha_pago <= $fecha_fin_dt);

            return $match_fecha_inicio && $match_fecha_fin;
        } catch (Exception $e) {
            error_log("Error procesando fecha para pago ID " . ($pago['id'] ?? 'N/A') . ": " . $e->getMessage());
            return false;
        }
    });
    $filtered_pagos = array_values($filtered_pagos); // Re-indexar el array después de filtrar
}

// Recibir parámetros de paginación
$pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$limite = isset($_GET['limite']) ? max(1, intval($_GET['limite'])) : 25;

$total_pagos = count($filtered_pagos);

// --- Paginación ---
$offset = ($pagina - 1) * $limite;
$pagos_paginados = array_slice($filtered_pagos, $offset, $limite);

// --- Preparar Respuesta ---
$response_data = [
    'data' => $pagos_paginados,
    'total' => $total_pagos
];

// Codificar el array de respuesta a JSON para enviar al frontend
echo json_encode($response_data);

exit();
?>