<?php
session_start();
header('Content-Type: application/json');

$response = ['success' => false];
$errors = [];

include 'connect.php';

$conn = new mysqli($db_host, $db_usuario, $db_contra, $db_nombre);

if ($conn->connect_error) {
    $response['login_error'] = "Error de conexión a la base de datos: " . $conn->connect_error;
    echo json_encode($response);
    exit();
}

$usuario = $_POST['user'] ?? '';  // Variable renombrada para claridad
$contrasena = $_POST['contrasena'] ?? '';

// Validación solo de campo obligatorio (sin validación de email)
if (empty($usuario)) {
    $errors['user'] = 'El usuario es obligatorio.';
}

if (empty($contrasena)) {
    $errors['password'] = 'La contraseña es obligatoria.';
}

if (!empty($errors)) {
    $response['errors'] = $errors;
} else {
    // Corregido: usar columna USERNAME (mayúsculas)
    $sql = "SELECT * FROM usuario WHERE USERNAME = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        $response['login_error'] = "Error al preparar la consulta: " . $conn->error;
        echo json_encode($response);
        exit();
    }

    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuarioRow = $result->fetch_assoc();  // Variable renombrada

    if ($usuarioRow) {
        if (password_verify($contrasena, $usuarioRow['CONTRASENA'])) {
            $_SESSION['user_id'] = $usuarioRow['ID_USUARIO'];
            $_SESSION['user_nombre'] = $usuarioRow['NOMBRE'];
            $response['success'] = true;
        } else {
            $response['login_error'] = "Contraseña incorrecta.";
        }
    } else {
        $response['login_error'] = "Usuario no registrado.";  // Mensaje corregido
    }

    $stmt->close();
}

$conn->close();

echo json_encode($response);
exit;
?>