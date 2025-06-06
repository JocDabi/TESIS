<?php
require('fpdf/fpdf.php');

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

// Crear PDF con clase extendida para el pie de página
class PDF extends FPDF {
    // Pie de página
    function Footer() {
        // Posición a 1.5 cm del final
        $this->SetY(-15);
        // Arial itálica 8
        $this->SetFont('Arial', 'I', 8);
        // Número de página
        $this->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Página ' . $this->PageNo() . '/{nb}'), 0, 0, 'C');
    }
}

// Crear PDF
$pdf = new PDF();
$pdf->AliasNbPages(); // Para mostrar el total de páginas (ej. "1/3")
$pdf->AddPage();

// Configurar márgenes
$pdf->SetMargins(15, 25, 15);

// --- MEMBRETE ---
// Logo (reemplaza 'logo.png' con la ruta correcta a tu imagen)
$pdf->Image('../public/img/logo.png', 15, 8, 60); // Aumenta el ancho a 45, ajusta Y a 8 para mejor alineación

// Título del reporte
$pdf->SetY(15); // Posición vertical después del logo
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'REPORTE DE PAGOS'), 0, 1, 'C');

// Fecha de generación
$pdf->SetFont('Arial', '', 10);
$fechaGeneracion = date('d/m/Y H:i:s');
$pdf->Cell(0, 6, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Generado el: ' . $fechaGeneracion), 0, 1, 'C');

// Línea separadora
$pdf->SetDrawColor(100, 100, 100);
$pdf->SetLineWidth(0.5);
$pdf->Line(15, 35, 195, 35);
$pdf->Ln(15); // Espacio después del membrete

// --- CONTENIDO PRINCIPAL ---
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Lista de Pagos'), 0, 1, 'C');
$pdf->Ln(8);

// Encabezados de la tabla
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(200, 220, 255);
$pdf->Cell(25, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'ID'), 1, 0, 'C', true);
$pdf->Cell(35, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Fecha'), 1, 0, 'C', true);
$pdf->Cell(20, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Monto'), 1, 0, 'C', true);
$pdf->Cell(50, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Descripción'), 1, 0, 'C', true);
$pdf->Cell(30, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Estado'), 1, 0, 'C', true);
$pdf->Cell(30, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Usuario'), 1, 1, 'C', true);

// Cuerpo de la tabla
$pdf->SetFont('Arial', '', 9);
foreach ($pagos as $pago) {
    // Verificar si necesita nueva página
    if ($pdf->GetY() > 250) { // Si está cerca del final de la página
        $pdf->AddPage();
        // Volver a dibujar los encabezados de la tabla en la nueva página
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(200, 220, 255);
        $pdf->Cell(25, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'ID'), 1, 0, 'C', true);
        $pdf->Cell(35, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Fecha'), 1, 0, 'C', true);
        $pdf->Cell(20, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Monto'), 1, 0, 'C', true);
        $pdf->Cell(50, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Descripción'), 1, 0, 'C', true);
        $pdf->Cell(30, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Estado'), 1, 0, 'C', true);
        $pdf->Cell(30, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Usuario'), 1, 1, 'C', true);
        $pdf->SetFont('Arial', '', 9);
    }
    
    $id = substr($pago['id'], 0, 6) . '...';
    $fecha = substr($pago['createdAt'], 0, 10);
    $monto = '$' . number_format((float)$pago['amount'], 2, ',', '.');
    $descripcion = strlen($pago['description']) > 30 ? substr($pago['description'], 0, 27) . '...' : $pago['description'];
    $estado = $pago['status'];
    $usuario = isset($pago['user']['firstName']) ? $pago['user']['firstName'] . ' ' . $pago['user']['lastName'] : 'N/A';

    $pdf->Cell(25, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $id), 1);
    $pdf->Cell(35, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $fecha), 1);
    $pdf->Cell(20, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $monto), 1, 0, 'R');
    $pdf->Cell(50, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $descripcion), 1);
    $pdf->Cell(30, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $estado), 1);
    $pdf->Cell(30, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $usuario), 1, 1);
}

// Salida
$pdf->Output('reporte_pagos.pdf', 'I');