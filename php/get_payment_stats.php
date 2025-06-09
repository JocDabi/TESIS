<?php
header('Content-Type: application/json');

// Establecer la zona horaria predeterminada
date_default_timezone_set('UTC'); // Usa UTC para consistencia con los datos JSON

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

// --- Obtener y parsear fechas de filtro ---
$start_date_str = $_GET['fecha_inicio'] ?? null; // Formato esperado: dd/mm/aaaa
$end_date_str = $_GET['fecha_fin'] ?? null;     // Formato esperado: dd/mm/aaaa

$start_date_dt = null;
$end_date_dt = null;

// Parsear fecha de inicio
if ($start_date_str) {
    $dmy_parts = explode('/', $start_date_str);
    if (count($dmy_parts) === 3 && checkdate($dmy_parts[1], $dmy_parts[0], $dmy_parts[2])) {
        $start_date_ymd = $dmy_parts[2] . '-' . $dmy_parts[1] . '-' . $dmy_parts[0];
        try {
            $start_date_dt = new DateTime($start_date_ymd, new DateTimeZone('UTC'));
            $start_date_dt->setTime(0, 0, 0); // Inicio del día
        } catch (Exception $e) {
            error_log("Error parsing start_date: " . $e->getMessage());
        }
    }
}

// Parsear fecha de fin
if ($end_date_str) {
    $dmy_parts = explode('/', $end_date_str);
    if (count($dmy_parts) === 3 && checkdate($dmy_parts[1], $dmy_parts[0], $dmy_parts[2])) {
        $end_date_ymd = $dmy_parts[2] . '-' . $dmy_parts[1] . '-' . $dmy_parts[0];
        try {
            $end_date_dt = new DateTime($end_date_ymd, new DateTimeZone('UTC'));
            $end_date_dt->setTime(23, 59, 59); // Fin del día
        } catch (Exception $e) {
            error_log("Error parsing end_date: " . $e->getMessage());
        }
    }
}

// --- Filtrar pagos por rango de fechas ---
$filtered_pagos = array_filter($all_pagos, function($pago) use ($start_date_dt, $end_date_dt) {
    if (!isset($pago['createdAt'])) {
        return false;
    }
    try {
        $pago_date = new DateTime($pago['createdAt'], new DateTimeZone('UTC'));
        // Convertir el 'createdAt' a solo fecha para comparación
        $pago_date->setTime(0, 0, 0);

        $in_range = true;
        if ($start_date_dt && $pago_date < $start_date_dt) {
            $in_range = false;
        }
        // Para la fecha final, ajustamos la comparación a menos o igual
        if ($end_date_dt && $pago_date > $end_date_dt) {
             $in_range = false;
        }

        return $in_range;

    } catch (Exception $e) {
        error_log("Error processing createdAt for payment ID " . ($pago['id'] ?? 'N/A') . " during date filtering: " . $e->getMessage());
        return false;
    }
});

// --- Calcular estadísticas por estado ---
$status_counts = [
    'ready_to_be_checked' => 0,
    'completed' => 0,
    'rejected' => 0,
    // Puedes añadir otros estados si los tienes en tus datos
];

foreach ($filtered_pagos as $pago) {
    $status = $pago['status'] ?? 'unknown';
    if (array_key_exists($status, $status_counts)) {
        $status_counts[$status]++;
    } else {
        // Manejar estados desconocidos si es necesario
        // $status_counts['unknown']++;
    }
}

$total_operations = count($filtered_pagos);

echo json_encode([
    'status_counts' => $status_counts,
    'total_operations' => $total_operations
]);

exit();
?>