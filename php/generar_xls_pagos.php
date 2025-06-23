<?php
require 'vendor/autoload.php'; // Asegúrate de tener instalado PhpSpreadsheet via Composer

// Para PhpSpreadsheet 1.21 (PHP 8.0)
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// URL del endpoint remoto
$url = 'https://payment-gateway-backend-production.up.railway.app/transaction/transaction-json';

// Obtener JSON desde el endpoint
$json = file_get_contents($url);

// Verificar que la respuesta sea válida
if ($json === FALSE) {
    die("No se pudo obtener el JSON del endpoint.");
}

// Decodificar JSON a array
$pagos = json_decode($json, true);

// Verificar si el JSON fue correctamente decodificado
if ($pagos === NULL) {
    die("Error al decodificar el JSON.");
}

// Recibir filtros desde GET
$filtro_fecha_inicio = $_GET['fecha_inicio'] ?? '';
$filtro_fecha_fin = $_GET['fecha_fin'] ?? '';
$filtro_estado = $_GET['estado'] ?? '';

// Filtrar por estado
if (!empty($filtro_estado)) {
    $pagos = array_filter($pagos, function($pago) use ($filtro_estado) {
        return isset($pago['status']) && $pago['status'] === $filtro_estado;
    });
    $pagos = array_values($pagos);
}

// Filtrar por fechas
if (!empty($filtro_fecha_inicio) || !empty($filtro_fecha_fin)) {
    $pagos = array_filter($pagos, function($pago) use ($filtro_fecha_inicio, $filtro_fecha_fin) {
        if (!isset($pago['createdAt'])) return false;
        $fecha_pago = substr($pago['createdAt'], 0, 10);
        $match_inicio = empty($filtro_fecha_inicio) || ($fecha_pago >= $filtro_fecha_inicio);
        $match_fin = empty($filtro_fecha_fin) || ($fecha_pago <= $filtro_fecha_fin);
        return $match_inicio && $match_fin;
    });
    $pagos = array_values($pagos);
}

// Crear un nuevo Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// --- MEMBRETE ---
// Logo (opcional en Excel, podrías insertar una imagen si lo deseas)
$drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
$drawing->setName('Logo');
$drawing->setDescription('Logo');
$drawing->setPath('../public/img/logo.png');
$drawing->setHeight(36);
$drawing->setCoordinates('A1');
$drawing->setWorksheet($sheet);

// Título del reporte (ajustado a la nueva columna G)
$sheet->mergeCells('A1:G1');
$sheet->setCellValue('A1', 'REPORTE DE TRANSACCIONES');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Fecha de generación
$sheet->mergeCells('A2:G2');
$fechaGeneracion = date('d/m/Y H:i:s');
$sheet->setCellValue('A2', 'Generado el: ' . $fechaGeneracion);
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Mostrar rango de fechas filtrado si aplica
if (!empty($filtro_fecha_inicio) || !empty($filtro_fecha_fin)) {
    $rango = 'Filtrado por fecha: ';
    if (!empty($filtro_fecha_inicio) && !empty($filtro_fecha_fin)) {
        $rango .= date('d/m/Y', strtotime($filtro_fecha_inicio)) . ' al ' . date('d/m/Y', strtotime($filtro_fecha_fin));
    } elseif (!empty($filtro_fecha_inicio)) {
        $rango .= 'Desde ' . date('d/m/Y', strtotime($filtro_fecha_inicio));
    } elseif (!empty($filtro_fecha_fin)) {
        $rango .= 'Hasta ' . date('d/m/Y', strtotime($filtro_fecha_fin));
    }
    $sheet->mergeCells('A3:G3');
    $sheet->setCellValue('A3', $rango);
    $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
}

// Espacio antes de la tabla
$sheet->setCellValue('A5', 'Lista de transacciones');
$sheet->getStyle('A5')->getFont()->setBold(true)->setSize(14);
$sheet->mergeCells('A5:G5');
$sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Encabezados de la tabla (con la nueva columna Curso)
$sheet->setCellValue('A7', 'ID');
$sheet->setCellValue('B7', 'Fecha');
$sheet->setCellValue('C7', 'Curso'); // Nueva columna
$sheet->setCellValue('D7', 'Monto');
$sheet->setCellValue('E7', 'Descripción');
$sheet->setCellValue('F7', 'Estado');
$sheet->setCellValue('G7', 'Usuario');

// Estilo para los encabezados
$headerStyle = [
    'font' => ['bold' => true],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FFC8DCFF']
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN
        ]
    ]
];
$sheet->getStyle('A7:G7')->applyFromArray($headerStyle);

// Cuerpo de la tabla
if (empty($pagos)) {
    $sheet->mergeCells('A8:G8');
    $sheet->setCellValue('A8', 'No hay pagos encontrados en ese rango.');
    $sheet->getStyle('A8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
} else {
    $row = 8;
    foreach ($pagos as $pago) {
        $id = substr($pago['id'], 0, 6) . '...';
        $fecha = substr($pago['createdAt'], 0, 10);
        $curso = isset($pago['course']['name']) ? $pago['course']['name'] : 'N/A'; // Nuevo campo curso
        $monto = '$' . number_format((float)$pago['amount'], 2, ',', '.');
        $descripcion = $pago['description']; // En Excel no truncamos para aprovechar el espacio
        $estado = $pago['status'];
        $usuario = isset($pago['user']['firstName']) ? $pago['user']['firstName'] . ' ' . $pago['user']['lastName'] : 'N/A';

        $sheet->setCellValue('A'.$row, $id);
        $sheet->setCellValue('B'.$row, $fecha);
        $sheet->setCellValue('C'.$row, $curso); // Nueva columna
        $sheet->setCellValue('D'.$row, $monto);
        $sheet->setCellValue('E'.$row, $descripcion);
        $sheet->setCellValue('F'.$row, $estado);
        $sheet->setCellValue('G'.$row, $usuario);
        
        $row++;
    }

    // Aplicar bordes a los datos
    $dataStyle = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN
            ]
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_LEFT
        ]
    ];
    $sheet->getStyle('A8:G'.($row-1))->applyFromArray($dataStyle);
    
    // Alinear montos a la derecha
    $sheet->getStyle('D8:D'.($row-1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    
    // Autoajustar columnas
    foreach(range('A','G') as $columnID) {
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
    }
    
    // Ajustar el ancho de la columna de curso si es necesario
    $sheet->getColumnDimension('C')->setWidth(30); // Ancho fijo para la columna de curso
}

// Configurar el nombre del archivo y descargar
$filename = 'reporte_pagos_' . date('Ymd_His') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;