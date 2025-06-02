<?php
// Conexión a la base de datos
include 'connect.php';

$conn = new mysqli($db_host, $db_usuario, $db_contra, $db_nombre);

// Verificar conexión
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Inicializar variables
$error = "";
$success = "";
$show_question = false;
$show_reset_form = false;
$pregunta = "";
$id_usuario = "";

// Procesar el formulario de verificación de correo electrónico
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
    $email = $_POST['email'];

    $sql = "SELECT * FROM usuario WHERE EMAIL = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $pregunta_id = $row['pregunta_id'];
        $id_usuario = $row['ID_USUARIO'];

        // Obtener la pregunta de seguridad
        $sql_pregunta = "SELECT pregunta FROM preguntas_seguridad WHERE id = $pregunta_id";
        $result_pregunta = $conn->query($sql_pregunta);
        $row_pregunta = $result_pregunta->fetch_assoc();
        $pregunta = $row_pregunta['pregunta'];

        $show_question = true; // Mostrar la pregunta de seguridad
    } else {
        $error = "El correo electrónico no existe.";
    }
}

// Procesar la verificación de la respuesta
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['respuesta'])) {
    $id_usuario = $_POST['id_usuario'];
    $respuesta_usuario = $_POST['respuesta'];

    // Obtener la respuesta correcta
    $sql = "SELECT respuesta FROM usuario WHERE ID_USUARIO = $id_usuario";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $respuesta_correcta = $row['respuesta'];

    // Verificar la respuesta
    if ($respuesta_usuario == $respuesta_correcta) {
        $show_reset_form = true; // Mostrar el formulario de restablecimiento de contraseña
    } else {
        $error = "Respuesta incorrecta.";
    }
}

// Procesar el restablecimiento de la contraseña
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nueva_contrasena'])) {
    $id_usuario = $_POST['id_usuario'];
    $nueva_contrasena = password_hash($_POST['nueva_contrasena'], PASSWORD_DEFAULT);

    // Actualizar la contraseña
    $sql = "UPDATE usuario SET CONTRASENA = '$nueva_contrasena' WHERE ID_USUARIO = $id_usuario";

    if ($conn->query($sql) === TRUE) {
        $success = "Contraseña restablecida con éxito.";
    } else {
        $error = "Error al restablecer la contraseña: " . $conn->error;
    }
    echo "<script>
        setTimeout(function() {
            window.location.href = '../public/index.php';
        }, 1000);
    </script>";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: "Montserrat", sans-serif;
        }
        .error-message {
            color: red;
            font-size: 0.875rem;
            font-style: italic;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body class="bg-gray-100 flex justify-center items-center h-screen">

    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <h1 class="text-2xl font-bold text-gray-800 text-center mb-6">Recuperar Contraseña</h1>

        <?php if (!empty($error)): ?>
            <p class="error-message text-center"><?php echo $error; ?></p>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <p class="text-green-500 text-center"><?php echo $success; ?></p>
        <?php endif; ?>

        <?php if (!$show_question && !$show_reset_form): ?>
            <!-- Formulario de verificación de correo electrónico -->
            <form method="post" action="" class="flex flex-col gap-4">
                <div>
                    <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                    <input type="email" id="email" name="email" placeholder="Tu email" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="flex items-center justify-center">
                    <button type="submit" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Verificar correo
                    </button>
                </div>
            </form>
        <?php endif; ?>

        <?php if ($show_question && !$show_reset_form): ?>
            <!-- Formulario de pregunta de seguridad -->
            <form method="post" action="" class="flex flex-col gap-4">
                <input type="hidden" name="id_usuario" value="<?php echo $id_usuario; ?>">
                <div>
                    <p class="text-gray-700 text-sm font-bold mb-2"><?php echo $pregunta; ?></p>
                    <input type="text" name="respuesta" placeholder="Tu respuesta" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="flex items-center justify-center">
                    <button type="submit" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Verificar respuesta
                    </button>
                </div>
            </form>
        <?php endif; ?>

        <?php if ($show_reset_form): ?>
            <!-- Formulario de restablecimiento de contraseña -->
            <form method="post" action="" class="flex flex-col gap-4">
                <input type="hidden" name="id_usuario" value="<?php echo $id_usuario; ?>">
                <div>
                    <label for="nueva_contrasena" class="block text-gray-700 text-sm font-bold mb-2">Nueva Contraseña</label>
                    <div class="relative">
                        <input type="password" id="nueva_contrasena" name="nueva_contrasena" placeholder="Nueva contraseña" required
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 px-3 py-2 text-gray-600">
                            <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                    <script>
                        function togglePasswordVisibility() {
                            const passwordInput = document.getElementById('nueva_contrasena');
                            const eyeIcon = document.getElementById('eye-icon');
                            if (passwordInput.type === 'password') {
                                passwordInput.type = 'text';
                                eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7 1.274-4.057 5.065-7 9.542-7 1.086 0 2.13.176 3.125.502M15 12a3 3 0 11-6 0 3 3 0 016 0z" />';
                            } else {
                                passwordInput.type = 'password';
                                eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
                            }
                        }
                    </script>
                </div>
                <div class="flex items-center justify-center">
                    <button type="submit" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Restablecer contraseña
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>

</body>
</html>