<?php
header('Content-Type: application/json');

// === SIMULACIÓN DE DATOS CON JSON DE EJEMPLO ===
// En un futuro, aquí iría el código para hacer una petición HTTP al enlace de tu amigo
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
        "createdAt": "2025-02-11T02:49:44.982Z",
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
            "lastName": "Doe",
            "role": "user"
        },
        "validatedBy": { }
    },
    {
        "id": "abc123d4-56ef-7890-abcd-1234567890ef",
        "createdAt": "2025-02-10T10:00:00.000Z",
        "updatedAt": null, "deletedAt": null, "amount": "75.00",
        "description": "Pago material didactico", "paymentMethod": "transferencia",
        "status": "completed",
         "course": { },
        "user": { "id": "user-abc", "firstName": "Jane", "lastName": "Smith", "email": "jane@example.com" },
        "validatedBy": null
    },
     {
        "id": "def456g7-89ab-cdef-0123-4567890abcde",
        "createdAt": "2025-02-09T14:30:00.000Z",
        "updatedAt": null, "deletedAt": null, "amount": "200.00",
        "description": "Pago mensualidad", "paymentMethod": "tarjeta",
        "status": "in_process",
         "course": { },
        "user": { "id": "user-def", "firstName": "Peter", "lastName": "Jones", "email": "peter@example.com" },
        "validatedBy": null
    }
]';
// === FIN SIMULACIÓN DE DATOS ===


// Decodificar el JSON de ejemplo a un array de PHP
$all_pagos = json_decode($json_ejemplo, true); // true para decodificar como array asociativo

// --- Lógica para Recibir y Aplicar Filtros ---
$filtered_pagos = $all_pagos; // Empezamos con todos los pagos

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

// Aplicar filtro por rango de fechas (simplificado: compara solo la parte de la fecha)
if (!empty($filtro_fecha_inicio) || !empty($filtro_fecha_fin)) {
    $filtered_pagos = array_filter($filtered_pagos, function($pago) use ($filtro_fecha_inicio, $filtro_fecha_fin) {
        if (!isset($pago['createdAt'])) {
            return false; // No incluir pagos sin fecha
        }
        $fecha_pago_str = explode('T', $pago['createdAt'])[0]; // Obtener solo la parte de la fecha (YYYY-MM-DD)

        $match_fecha_inicio = empty($filtro_fecha_inicio) || ($fecha_pago_str >= $filtro_fecha_inicio);
        $match_fecha_fin = empty($filtro_fecha_fin) || ($fecha_pago_str <= $filtro_fecha_fin);

        return $match_fecha_inicio && $match_fecha_fin;
    });
    $filtered_pagos = array_values($filtered_pagos); // Re-indexar el array después de filtrar
}

// --- Simulación de Paginación (Muy Básica) ---
$pagos_a_enviar = $filtered_pagos;


// --- Preparar Respuesta ---
$response_data = [
    'data' => $pagos_a_enviar,
    'total' => count($filtered_pagos)
];


// Codificar el array de respuesta a JSON para enviar al frontend
echo json_encode($response_data);

exit();
?>