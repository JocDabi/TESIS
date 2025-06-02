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

$email = $_POST['email'] ?? '';// <!- Recibe $_POST['email'] ->
$contrasena = $_POST['contrasena'] ?? '';

if (empty($email)) {
    $errors['email'] = 'El email es obligatorio.'; //<!- Error de "email" ->
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Email no válido.'; //<!- Error de "email" ->
}

if (empty($contrasena)) {
    $errors['password'] = 'La contraseña es obligatoria.';
}

if (!empty($errors)) {
    $response['errors'] = $errors;
} else {
    $sql = "SELECT * FROM usuario WHERE EMAIL = ?"; //<!- Consulta WHERE EMAIL = ? ->
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        $response['login_error'] = "Error al preparar la consulta: " . $conn->error;
        echo json_encode($response);
        exit();
    }

    $stmt->bind_param("s", $email); //<!- Bind param con $email ->
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        if (password_verify($contrasena, $user['CONTRASENA'])) {
            $_SESSION['user_id'] = $user['ID_USUARIO'];
            $_SESSION['user_nombre'] = $user['NOMBRE'];
            $response['success'] = true;
        } else {
            $response['login_error'] = "Contraseña incorrecta.";
        }
    } else {
        $response['login_error'] = "El email no está registrado."; //<!- Error de "email" ->
    }

    $stmt->close();
}

$conn->close();

echo json_encode($response);
exit;
?>