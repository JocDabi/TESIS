<?php
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Script PHP de prueba contactado correctamente!'];

echo json_encode($response);
exit();
?>