<?php
header('Content-Type: application/json');

// --- SIMULACIÓN DE DATOS JSON (ACTUALIZADO CON EL FORMATO ORIGINAL PROPORCIONADO) ---
$json_ejemplo = '[
    {
        "id": "406b106d-07ff-4613-bf11-19f959ebdb37",
        "createdAt": "2025-02-11T02:48:31.382Z",
        "updatedAt": null,
        "deletedAt": null,
        "amount": "50",
        "description": "Pago curso ccna5",
        "paymentMethod": "zelle",
        "status": "ready_to_be_checked",
        "course": { },
        "user": {
            "id": "933260d8-bfb3-4f7b-819f-59c3f3e4fe61",
            "createdAt": "2025-02-10T05:20:39.467Z",
            "updatedAt": null,
            "deletedAt": null,
            "email": "testtt@gmail.com",
            "identificationNumber": "12344536478941405",
            "firstName": "John",
            "lastName": "Doe",
            "role": "user"
        },
        "validatedBy": { }
    },
    {
        "id": "bd7c20b5-12c0-4f0e-8d4f-5f55b89dafd0",
        "createdAt": "2025-05-28T02:49:44.982Z",
        "updatedAt": null,
        "deletedAt": null,
        "amount": "100",
        "description": "Pago curso inglés",
        "paymentMethod": "paypal",
        "status": "completed",
        "course": { },
        "user": {
            "id": "933260d8-bfb3-4f7b-819f-59c3f3e4fe61",
            "createdAt": "2025-02-10T05:20:39.467Z",
            "updatedAt": null,
            "deletedAt": null,
            "email": "testtt@gmail.com",
            "identificationNumber": "12344536478941405",
            "firstName": "John",
            "lastName": "Loe",
            "role": "user"
        },
        "validatedBy": { }
    },
    {
        "id": "abc123d4-56ef-7890-abcd-1234567890ef",
        "createdAt": "2025-05-29T10:00:00.000Z",
        "updatedAt": null, "deletedAt": null, "amount": "75.00",
        "description": "Pago material didactico", "paymentMethod": "transferencia",
        "status": "in_process",
        "course": { },
        "user": { "id": "user-abc", "firstName": "Jane", "lastName": "Smith", "email": "jane@example.com" },
        "validatedBy": null
    },
    {
        "id": "def456g7-89ab-cdef-0123-4567890abcde",
        "createdAt": "2025-05-25T14:30:00.000Z",
        "updatedAt": null, "deletedAt": null, "amount": "200.00",
        "description": "Pago mensualidad", "paymentMethod": "tarjeta",
        "status": "in_process",
        "course": { },
        "user": { "id": "user-def", "firstName": "Peter", "lastName": "Jones", "email": "peter@example.com" },
        "validatedBy": null
    },
    {
        "id": "e0c8b7a6-d5c4-e3f2-a1b0-c9d8e7f6a5b4",
        "createdAt": "2025-05-15T09:00:00.000Z",
        "updatedAt": null, "deletedAt": null, "amount": "150.00",
        "description": "Cuota de membresía", "paymentMethod": "transferencia",
        "status": "ready_to_be_checked",
        "course": { },
        "user": { "id": "user-ghi", "firstName": "Alice", "lastName": "Wonder", "email": "alice@example.com" },
        "validatedBy": null
    },
    {
        "id": "f1d9c8b7-e6a5-d4c3-b2a1-c0d9e8f7a6b5",
        "createdAt": "2025-05-27T11:00:00.000Z",
        "updatedAt": null, "deletedAt": null, "amount": "30.00",
        "description": "Libro de texto", "paymentMethod": "zelle",
        "status": "completed",
        "course": { },
        "user": { "id": "user-jkl", "firstName": "Bob", "lastName": "Builder", "email": "bob@example.com" },
        "validatedBy": { }
    },
    {
        "id": "new-payment-1-future",
        "createdAt": "2025-05-30T09:00:00.000Z",
        "updatedAt": null, "deletedAt": null, "amount": "120.00",
        "description": "Pago curso diseño", "paymentMethod": "transferencia",
        "status": "in_process",
        "course": { },
        "user": { "id": "user-future1", "firstName": "Charlie", "lastName": "Brown", "email": "charlie@example.com" },
        "validatedBy": null
    },
    {
        "id": "new-payment-2-future",
        "createdAt": "2025-06-01T14:00:00.000Z",
        "updatedAt": null, "deletedAt": null, "amount": "80.00",
        "description": "Pago software", "paymentMethod": "paypal",
        "status": "ready_to_be_checked",
        "course": { },
        "user": { "id": "user-future2", "firstName": "Diana", "lastName": "Prince", "email": "diana@example.com" },
        "validatedBy": null
    }
]';
// --- FIN SIMULACIÓN DE DATOS ---

// Establecer la zona horaria predeterminada
date_default_timezone_set('UTC'); // Usa UTC para consistencia con los datos JSON

// Decodificar el JSON de ejemplo a un array de PHP
$all_pagos = json_decode($json_ejemplo, true);

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
        'fechaRealizado' => $pago['fechaRealizadoFormateada'], // 'YYYY-MM-DD'
        'fechaCorte' => $fecha_corte_display,                 // La fecha de corte del filtro para todos los resultados
        'cliente' => ($pago['user']['firstName'] ?? '') . ' ' . ($pago['user']['lastName'] ?? ''),
        'monto' => '$' . number_format((float)$pago['amount'], 2, '.', ','),
        'estado' => str_replace('_', ' ', $pago['status']), // 'in_process' -> 'in process'
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