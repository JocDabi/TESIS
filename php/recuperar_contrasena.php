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

// Procesar el formulario de verificación de usuario
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username'])) {
    $username = trim($_POST['username']);

    if (empty($username)) {
        $error = "El nombre de usuario es obligatorio.";
    } else {
        // Usar consulta preparada para seguridad
        $sql = "SELECT * FROM usuario WHERE USERNAME = ?";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $pregunta_id = $row['pregunta_id'];
                $id_usuario = $row['ID_USUARIO'];

                // Obtener la pregunta de seguridad
                $sql_pregunta = "SELECT pregunta FROM preguntas_seguridad WHERE id = ?";
                $stmt_pregunta = $conn->prepare($sql_pregunta);
                
                if ($stmt_pregunta) {
                    $stmt_pregunta->bind_param("i", $pregunta_id);
                    $stmt_pregunta->execute();
                    $result_pregunta = $stmt_pregunta->get_result();
                    
                    if ($result_pregunta->num_rows > 0) {
                        $row_pregunta = $result_pregunta->fetch_assoc();
                        $pregunta = $row_pregunta['pregunta'];
                        $show_question = true;
                    } else {
                        $error = "Pregunta de seguridad no encontrada.";
                    }
                    $stmt_pregunta->close();
                } else {
                    $error = "Error en la base de datos.";
                }
            } else {
                $error = "El usuario no existe.";
            }
            $stmt->close();
        } else {
            $error = "Error en la base de datos.";
        }
    }
}

// Procesar la verificación de la respuesta
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['respuesta'])) {
    $id_usuario = $_POST['id_usuario'];
    $respuesta_usuario = trim($_POST['respuesta']);

    if (empty($respuesta_usuario)) {
        $error = "La respuesta es obligatoria.";
    } else {
        // Obtener la respuesta correcta con consulta preparada
        $sql = "SELECT respuesta FROM usuario WHERE ID_USUARIO = ?";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("i", $id_usuario);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $respuesta_correcta = $row['respuesta'];

                // Verificar la respuesta
                if ($respuesta_usuario === $respuesta_correcta) {
                    $show_reset_form = true;
                } else {
                    $error = "Respuesta incorrecta.";
                }
            } else {
                $error = "Usuario no encontrado.";
            }
            $stmt->close();
        } else {
            $error = "Error en la base de datos.";
        }
    }
}

// Procesar el restablecimiento de la contraseña
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nueva_contrasena'])) {
    $id_usuario = $_POST['id_usuario'];
    $nueva_contrasena = $_POST['nueva_contrasena'];

    if (empty($nueva_contrasena)) {
        $error = "La nueva contraseña es obligatoria.";
    } else {
        $nueva_contrasena_hash = password_hash($nueva_contrasena, PASSWORD_DEFAULT);

        // Actualizar la contraseña con consulta preparada
        $sql = "UPDATE usuario SET CONTRASENA = ? WHERE ID_USUARIO = ?";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("si", $nueva_contrasena_hash, $id_usuario);
            
            if ($stmt->execute()) {
                $success = "Contraseña restablecida con éxito.";
            } else {
                $error = "Error al restablecer la contraseña: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "Error en la base de datos.";
        }
    }
    
    if ($success) {
        echo "<script>
            setTimeout(function() {
                window.location.href = '../public/index.php';
            }, 1000);
        </script>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña | E-Pay</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            font-family: "Montserrat", sans-serif;
        }
        .error-message {
            color: #e53e3e;
            font-size: 0.875rem;
            font-style: italic;
            margin-top: 0.25rem;
        }
        body {
            background: url('../public/img/background2.png') no-repeat center center fixed;
            background-size: cover;
            position: relative;
            min-height: 100vh;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.92);
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.5);
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 450px;
            padding: 2.5rem;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.4);
        }
        .btn-primary {
            background: linear-gradient(135deg, #3182ce, #63b3ed);
            transition: all 0.3s ease;
            color: white;
            font-weight: bold;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            border: none;
            cursor: pointer;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #2b6cb0, #4299e1);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(49, 130, 206, 0.3);
        }
        .logo-container {
            background: linear-gradient(135deg, #3182ce, #63b3ed);
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin: 0 auto 1.5rem;
        }
        .input-field {
            width: 100%;
            padding: 0.9rem 1rem;
            background: transparent;
            border: none;
            outline: none;
            font-size: 1rem;
        }
        .section-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 0.5rem;
            text-align: center;
        }
        .section-subtitle {
            color: #4a5568;
            text-align: center;
            margin-bottom: 2rem;
        }
        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #718096;
            cursor: pointer;
        }
        .loading-spinner {
            display: none;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top: 3px solid #fff;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
            margin-left: 10px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .back-link {
            display: inline-flex;
            align-items: center;
            color: #3182ce;
            font-weight: 500;
            margin-top: 1rem;
            transition: color 0.2s;
        }
        .back-link:hover {
            color: #2b6cb0;
            text-decoration: underline;
        }
        .success-message {
            color: #38a169;
            font-weight: 500;
            text-align: center;
            margin: 1rem 0;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="text-center mb-6">
            <div class="logo-container">
                <i class="fas fa-key text-3xl text-white"></i>
            </div>
            <h1 class="section-title">Recuperar Contraseña</h1>
            <p class="section-subtitle">Restablece tu acceso a E-Pay</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-500"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700"><?php echo $error; ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-500"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700"><?php echo $success; ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!$show_question && !$show_reset_form): ?>
            <!-- Formulario de verificación de usuario -->
            <form method="post" action="" class="flex flex-col gap-5">
                <div>
                    <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Nombre de usuario</label>
                    <div class="glass-card">
                        <input type="text" id="username" name="username" placeholder="Ingresa tu nombre de usuario" required
                            class="input-field">
                    </div>
                </div>

                <button class="btn-primary mt-2 flex justify-center items-center" type="submit" id="verify-button">
                    <span id="verify-text">Verificar usuario</span>
                    <div id="verify-spinner" class="loading-spinner"></div>
                </button>

                <div class="text-center mt-4">
                    <a href="../public/index.php" class="back-link">
                        <i class="fas fa-arrow-left mr-2"></i> Volver al inicio de sesión
                    </a>
                </div>
            </form>
        <?php endif; ?>

        <?php if ($show_question && !$show_reset_form): ?>
            <!-- Formulario de pregunta de seguridad -->
            <form method="post" action="" class="flex flex-col gap-5">
                <input type="hidden" name="id_usuario" value="<?php echo $id_usuario; ?>">
                
                <div>
                    <p class="text-gray-700 text-base font-semibold mb-3 text-center">Tu pregunta de seguridad:</p>
                    <div class="glass-card p-4 text-center">
                        <p class="text-gray-800 font-medium"><?php echo $pregunta; ?></p>
                    </div>
                </div>
                
                <div>
                    <label for="respuesta" class="block text-gray-700 text-sm font-bold mb-2">Respuesta</label>
                    <div class="glass-card">
                        <input type="text" id="respuesta" name="respuesta" placeholder="Ingresa tu respuesta" required
                            class="input-field">
                    </div>
                </div>

                <button class="btn-primary mt-2 flex justify-center items-center" type="submit" id="answer-button">
                    <span id="answer-text">Verificar respuesta</span>
                    <div id="answer-spinner" class="loading-spinner"></div>
                </button>

                <div class="text-center mt-4">
                    <a href="recuperar_contrasena.php" class="back-link">
                        <i class="fas fa-arrow-left mr-2"></i> Intentar con otro usuario
                    </a>
                </div>
            </form>
        <?php endif; ?>

        <?php if ($show_reset_form): ?>
            <!-- Formulario de restablecimiento de contraseña -->
            <form method="post" action="" class="flex flex-col gap-5">
                <input type="hidden" name="id_usuario" value="<?php echo $id_usuario; ?>">
                
                <div>
                    <label for="nueva_contrasena" class="block text-gray-700 text-sm font-bold mb-2">Nueva Contraseña</label>
                    <div class="glass-card relative">
                        <input type="password" id="nueva_contrasena" name="nueva_contrasena" placeholder="Ingresa tu nueva contraseña" required
                            class="input-field pr-10">
                        <button type="button" onclick="togglePasswordVisibility('nueva_contrasena', 'eye-icon')" class="password-toggle">
                            <i id="eye-icon" class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="mt-1">
                    <ul class="text-xs text-gray-600">
                        <li class="flex items-center mb-1"><i class="fas fa-info-circle mr-2 text-blue-500"></i> Mínimo 8 caracteres</li>
                        <li class="flex items-center"><i class="fas fa-info-circle mr-2 text-blue-500"></i> Combina letras, números y símbolos</li>
                    </ul>
                </div>

                <button class="btn-primary mt-2 flex justify-center items-center" type="submit" id="reset-button">
                    <span id="reset-text">Restablecer contraseña</span>
                    <div id="reset-spinner" class="loading-spinner"></div>
                </button>

                <div class="text-center mt-4">
                    <a href="../public/index.php" class="back-link">
                        <i class="fas fa-arrow-left mr-2"></i> Volver al inicio de sesión
                    </a>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script>
        function togglePasswordVisibility(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const eyeIcon = document.getElementById(iconId);
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Configurar los event listeners para los formularios
            const forms = document.querySelectorAll('form');
            
            forms.forEach(form => {
                form.addEventListener('submit', function() {
                    const button = this.querySelector('button[type="submit"]');
                    const spinner = button.querySelector('.loading-spinner');
                    const textSpan = button.querySelector('span');
                    
                    if (spinner && textSpan) {
                        button.disabled = true;
                        textSpan.textContent = "Procesando...";
                        spinner.style.display = "block";
                    }
                });
            });
        });
    </script>
</body>
</html>