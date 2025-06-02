<?php
header('Content-Type: application/json');

// Establecer la zona horaria predeterminada
date_default_timezone_set('UTC'); // Usa UTC para consistencia con los datos JSON

// --- SIMULACIÓN DE DATOS JSON ---
// He añadido más variedad de estados y fechas para que los gráficos sean más interesantes.
$json_ejemplo = '[
    {
        "id": "406b106d-07ff-4613-bf11-19f959ebdb37",
        "createdAt": "2025-02-11T02:48:31.382Z",
        "amount": "50",
        "description": "Pago curso ccna5",
        "paymentMethod": "zelle",
        "status": "ready_to_be_checked",
        "user": { "firstName": "John", "lastName": "Doe" }
    },
    {
        "id": "bd7c20b5-12c0-4f0e-8d4f-5f55b89dafd0",
        "createdAt": "2025-05-20T02:49:44.982Z",
        "amount": "100",
        "description": "Pago curso inglés",
        "paymentMethod": "paypal",
        "status": "completed",
        "user": { "firstName": "Jane", "lastName": "Smith" }
    },
    {
        "id": "abc123d4-56ef-7890-abcd-1234567890ef",
        "createdAt": "2025-05-25T10:00:00.000Z",
        "amount": "75.00",
        "description": "Pago material didactico",
        "paymentMethod": "transferencia",
        "status": "in_process",
        "user": { "firstName": "Peter", "lastName": "Jones" }
    },
    {
        "id": "def456g7-89ab-cdef-0123-4567890abcde",
        "createdAt": "2025-05-28T14:30:00.000Z",
        "amount": "200.00",
        "description": "Pago mensualidad",
        "paymentMethod": "tarjeta",
        "status": "completed",
        "user": { "firstName": "Alice", "lastName": "Wonder" }
    },
    {
        "id": "e0c8b7a6-d5c4-e3f2-a1b0-c9d8e7f6a5b4",
        "createdAt": "2025-05-18T09:00:00.000Z",
        "amount": "150.00",
        "description": "Cuota de membresía",
        "paymentMethod": "transferencia",
        "status": "ready_to_be_checked",
        "user": { "firstName": "Bob", "lastName": "Builder" }
    },
    {
        "id": "f1d9c8b7-e6a5-d4c3-b2a1-c0d9e8f7a6b5",
        "createdAt": "2025-05-29T11:00:00.000Z",
        "amount": "30.00",
        "description": "Libro de texto",
        "paymentMethod": "zelle",
        "status": "rejected",
        "user": { "firstName": "Charlie", "lastName": "Brown" }
    },
    {
        "id": "g2e0f1d9-h8g7-i6j5-k4l3-m2n1o0p9q8r7",
        "createdAt": "2025-05-29T15:00:00.000Z",
        "amount": "60.00",
        "description": "Renovación",
        "paymentMethod": "paypal",
        "status": "completed",
        "user": { "firstName": "Diana", "lastName": "Prince" }
    },
    {
        "id": "h3i1j0k2-l9m8-n7o6-p5q4-r3s2t1u0v9w8",
        "createdAt": "2025-05-22T08:00:00.000Z",
        "amount": "90.00",
        "description": "Suscripción",
        "paymentMethod": "tarjeta",
        "status": "in_process",
        "user": { "firstName": "Eve", "lastName": "Adams" }
    },
    {
        "id": "i4j2k1l3-m0n9-o8p7-q6r5-s4t3u2v1w0x9",
        "createdAt": "2025-06-01T10:00:00.000Z",
        "amount": "110.00",
        "description": "Pago anticipado",
        "paymentMethod": "zelle",
        "status": "ready_to_be_checked",
        "user": { "firstName": "Frank", "lastName": "White" }
    },
    {
        "id": "j5k3l2m4-n1o0-p9q8-r7s6-t5u4v3w2x1y0",
        "createdAt": "2025-06-05T16:00:00.000Z",
        "amount": "45.00",
        "description": "Cargo adicional",
        "paymentMethod": "transferencia",
        "status": "rejected",
        "user": { "firstName": "Grace", "lastName": "Black" }
    }
]';

$all_pagos = json_decode($json_ejemplo, true);

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
    'in_process' => 0,
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