<?php
require('fpdf/fpdf.php');

// Suponiendo que este JSON venga de una fuente externa:
$json = '[{"id":"406b106d-07ff-4613-bf11-19f959ebdb37","createdAt":"2025-02-11T02:48:31.382Z","updatedAt":null,"deletedAt":null,"amount":"50","description":"Pago curso ccna5","paymentMethod":"zelle","status":"ready_to_be_checked","course":{},"user":{"id":"933260d8-bfb3-4f7b-819f-59c3f3e4fe61","createdAt":"2025-02-10T05:20:39.467Z","updatedAt":null,"deletedAt":null,"email":"testtt@gmail.com","identificationNumber":"12344536478941405","firstName":"John","lastName":"Doe","role":"user"},"validatedBy":{}},{"id":"bd7c20b5-12c0-4f0e-8d4f-5f55b89dafd0","createdAt":"2025-02-11T02:49:44.982Z","updatedAt":null,"deletedAt":null,"amount":"100","description":"Pago curso inglés","paymentMethod":"paypal","status":"completed","course":{},"user":{"id":"933260d8-bfb3-4f7b-819f-59c3f3e4fe61","createdAt":"2025-02-10T05:20:39.467Z","updatedAt":null,"deletedAt":null,"email":"testtt@gmail.com","identificationNumber":"12344536478941405","firstName":"John","lastName":"Doe","role":"user"},"validatedBy":{}},{"id":"abc123d4-56ef-7890-abcd-1234567890ef","createdAt":"2025-02-10T10:00:00.000Z","updatedAt":null,"deletedAt":null,"amount":"75.00","description":"Pago material didactico","paymentMethod":"transferencia","status":"completed","course":{},"user":{"id":"user-abc","firstName":"Jane","lastName":"Smith","email":"jane@example.com"},"validatedBy":null},{"id":"def456g7-89ab-cdef-0123-4567890abcde","createdAt":"2025-02-09T14:30:00.000Z","updatedAt":null,"deletedAt":null,"amount":"200.00","description":"Pago mensualidad","paymentMethod":"tarjeta","status":"in_process","course":{},"user":{"id":"user-def","firstName":"Peter","lastName":"Jones","email":"peter@example.com"},"validatedBy":null}]';

// Decodificar JSON a array
$pagos = json_decode($json, true);

// Crear PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Lista de Pagos'), 0, 1, 'C');
$pdf->Ln(10);

// Encabezados
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(200, 220, 255);
$pdf->Cell(25, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'ID'), 1, 0, 'C', true);
$pdf->Cell(35, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Fecha'), 1, 0, 'C', true);
$pdf->Cell(20, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Monto'), 1, 0, 'C', true);
$pdf->Cell(50, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Descripción'), 1, 0, 'C', true);
$pdf->Cell(30, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Estado'), 1, 0, 'C', true);
$pdf->Cell(30, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Usuario'), 1, 1, 'C', true);

// Cuerpo
$pdf->SetFont('Arial', '', 9);
foreach ($pagos as $pago) {
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
$pdf->Output('pagos.pdf', 'I');
