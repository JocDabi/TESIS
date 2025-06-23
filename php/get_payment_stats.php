<?php
header('Content-Type: application/json');
date_default_timezone_set('UTC');

$api_url = 'https://payment-gateway-backend-production.up.railway.app/transaction/transaction-json';
$json_response = @file_get_contents($api_url);

if ($json_response === FALSE) {
    http_response_code(500);
    echo json_encode([
        'error' => 'No se pudo conectar con el servidor de pagos',
        'details' => error_get_last()['message'] ?? 'Error desconocido'
    ]);
    exit();
}

$all_pagos = json_decode($json_response, true);

if ($all_pagos === NULL) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Los datos recibidos no son válidos',
        'details' => json_last_error_msg()
    ]);
    exit();
}

// Procesar filtros de fecha
$start_date_str = $_GET['fecha_inicio'] ?? null;
$end_date_str = $_GET['fecha_fin'] ?? null;

$start_date_dt = null;
$end_date_dt = null;

if ($start_date_str) {
    $dmy_parts = explode('/', $start_date_str);
    if (count($dmy_parts) === 3 && checkdate($dmy_parts[1], $dmy_parts[0], $dmy_parts[2])) {
        $start_date_ymd = $dmy_parts[2] . '-' . $dmy_parts[1] . '-' . $dmy_parts[0];
        try {
            $start_date_dt = new DateTime($start_date_ymd, new DateTimeZone('UTC'));
            $start_date_dt->setTime(0, 0, 0);
        } catch (Exception $e) {
            error_log("Error parsing start_date: " . $e->getMessage());
        }
    }
}

if ($end_date_str) {
    $dmy_parts = explode('/', $end_date_str);
    if (count($dmy_parts) === 3 && checkdate($dmy_parts[1], $dmy_parts[0], $dmy_parts[2])) {
        $end_date_ymd = $dmy_parts[2] . '-' . $dmy_parts[1] . '-' . $dmy_parts[0];
        try {
            $end_date_dt = new DateTime($end_date_ymd, new DateTimeZone('UTC'));
            $end_date_dt->setTime(23, 59, 59);
        } catch (Exception $e) {
            error_log("Error parsing end_date: " . $e->getMessage());
        }
    }
}

// Filtrar y procesar transacciones
$filtered_pagos = [];
$status_counts = [
    'completed' => 0,
    'ready_to_be_checked' => 0,
    'rejected' => 0
];

$course_counts = [];
$payment_method_counts = [];
$daily_transactions = [];
$monthly_transactions = [];
$amount_by_status = [
    'completed' => 0,
    'ready_to_be_checked' => 0,
    'rejected' => 0
];
$amount_by_course = [];
$amount_by_payment_method = [];

foreach ($all_pagos as $pago) {
    try {
        // Filtrar por fecha
        $pago_date = new DateTime($pago['createdAt'], new DateTimeZone('UTC'));
        $pago_date_only = clone $pago_date;
        $pago_date_only->setTime(0, 0, 0);
        
        $in_range = true;
        if ($start_date_dt && $pago_date_only < $start_date_dt) {
            $in_range = false;
        }
        if ($end_date_dt && $pago_date_only > $end_date_dt) {
            $in_range = false;
        }
        
        if (!$in_range) continue;
        
        $filtered_pagos[] = $pago;
        
        // Estadísticas básicas
        $status = $pago['status'] ?? 'unknown';
        $course_name = $pago['course']['name'] ?? 'Curso no especificado';
        $payment_method = $pago['paymentMethod'] ?? 'Método no especificado';
        $amount = floatval($pago['amount'] ?? 0);
        
        // Conteo por estado
        if (array_key_exists($status, $status_counts)) {
            $status_counts[$status]++;
            $amount_by_status[$status] += $amount;
        }
        
        // Conteo por curso
        if (!isset($course_counts[$course_name])) {
            $course_counts[$course_name] = 0;
            $amount_by_course[$course_name] = 0;
        }
        $course_counts[$course_name]++;
        $amount_by_course[$course_name] += $amount;
        
        // Conteo por método de pago
        if (!isset($payment_method_counts[$payment_method])) {
            $payment_method_counts[$payment_method] = 0;
            $amount_by_payment_method[$payment_method] = 0;
        }
        $payment_method_counts[$payment_method]++;
        $amount_by_payment_method[$payment_method] += $amount;
        
        // Estadísticas temporales
        $day_key = $pago_date->format('Y-m-d');
        $month_key = $pago_date->format('Y-m');
        
        if (!isset($daily_transactions[$day_key])) {
            $daily_transactions[$day_key] = [
                'count' => 0,
                'amount' => 0
            ];
        }
        $daily_transactions[$day_key]['count']++;
        $daily_transactions[$day_key]['amount'] += $amount;
        
        if (!isset($monthly_transactions[$month_key])) {
            $monthly_transactions[$month_key] = [
                'count' => 0,
                'amount' => 0
            ];
        }
        $monthly_transactions[$month_key]['count']++;
        $monthly_transactions[$month_key]['amount'] += $amount;
        
    } catch (Exception $e) {
        error_log("Error processing transaction ID " . ($pago['id'] ?? 'N/A') . ": " . $e->getMessage());
    }
}

// Ordenar datos para mejor visualización
arsort($course_counts);
arsort($payment_method_counts);
ksort($daily_transactions);
ksort($monthly_transactions);

// Preparar respuesta
$response = [
    'basic_stats' => [
        'total_transactions' => count($filtered_pagos),
        'total_amount' => array_sum($amount_by_status),
        'status_counts' => $status_counts,
        'course_counts' => $course_counts,
        'payment_method_counts' => $payment_method_counts,
    ],
    'amount_stats' => [
        'by_status' => $amount_by_status,
        'by_course' => $amount_by_course,
        'by_payment_method' => $amount_by_payment_method,
    ],
    'time_stats' => [
        'daily' => $daily_transactions,
        'monthly' => $monthly_transactions,
    ],
    'raw_data' => $filtered_pagos // Opcional: solo para desarrollo
];

echo json_encode($response);
exit();
?>