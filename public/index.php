<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión | E-Pay</title>
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
            max-width: 400px;
            padding: 2rem;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.4);
        }
        .btn-login {
            background: linear-gradient(135deg, #3182ce, #63b3ed);
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #2b6cb0, #4299e1);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(49, 130, 206, 0.3);
        }
        .logo-container {
            background: linear-gradient(135deg, #3182ce, #63b3ed);
        }
        .loading-spinner {
            display: none;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top: 3px solid #fff;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>

<body>
    <!-- Formulario de inicio de sesión -->
    <div class="login-container">
        <div class="text-center mb-8">
            <div class="flex justify-center mb-4">
                <div class="logo-container w-20 h-20 flex items-center justify-center rounded-full">
                    <i class="fas fa-lock text-3xl text-white"></i>
                </div>
            </div>
            <h1 class="text-3xl font-bold text-gray-800">Bienvenido a E-Pay</h1>
            <p class="text-gray-600 mt-2">Tu plataforma de evaluación de pagos</p>
        </div>

        <form id="loginForm" class="flex flex-col gap-5" action="php/login.php" method="post">
            <div>
                <label for="user" class="block text-gray-700 text-sm font-bold mb-2">Usuario</label>
                <div class="glass-card p-1 rounded-lg">
                    <input type="text" id="user" name="user" placeholder="Ingresa tu nombre de usuario" required
                        class="w-full py-3 px-4 bg-transparent border-0 focus:outline-none focus:ring-0">
                </div>
                <span id="user-error" class="error-message"></span>
            </div>

            <div>
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Contraseña</label>
                <div class="glass-card p-1 rounded-lg relative">
                    <input type="password" id="password" name="contrasena" placeholder="Ingresa tu contraseña" required
                        class="w-full py-3 px-4 bg-transparent border-0 focus:outline-none focus:ring-0">
                    <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 px-4 text-gray-500">
                        <i id="eye-icon" class="fas fa-eye"></i>
                    </button>
                </div>
                <script>
                    function togglePasswordVisibility() {
                        const passwordInput = document.getElementById('password');
                        const eyeIcon = document.getElementById('eye-icon');
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
                </script>
                <span id="password-error" class="error-message"></span>
            </div>

            <div class="flex items-center justify-end">
                <a href="../php/recuperar_contrasena.php" class="text-sm text-blue-600 hover:text-blue-800 transition-colors">
                    ¿Olvidaste tu contraseña?
                </a>
            </div>

            <div id="login-error" class="text-red-500 text-sm italic text-center py-2"></div>

            <button id="login-button" class="btn-login text-white font-bold py-3 px-4 rounded-lg focus:outline-none shadow-md flex justify-center items-center gap-2" type="submit">
                <span id="login-text">Iniciar sesión</span>
                <div id="login-spinner" class="loading-spinner"></div>
            </button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            const loginErrorDiv = document.getElementById('login-error');
            const loginButton = document.getElementById('login-button');
            const loginText = document.getElementById('login-text');
            const loginSpinner = document.getElementById('login-spinner');

            loginForm.addEventListener('submit', function(event) {
                event.preventDefault();
                limpiarErrores();
                
                // Mostrar estado de carga
                loginButton.disabled = true;
                loginText.textContent = "Verificando...";
                loginSpinner.style.display = "block";

                const formData = new FormData(loginForm);

                fetch('../php/login.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Error HTTP: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Animación de éxito antes de redirigir
                        loginForm.classList.add('animate-pulse');
                        setTimeout(() => {
                            window.location.href = '../HTML/dashboard.html';
                        }, 800);
                    } else {
                        if (data.errors) {
                            mostrarErrores(data.errors);
                            loginErrorDiv.textContent = '';
                        } else if (data.login_error) {
                            loginErrorDiv.textContent = data.login_error;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error en la petición AJAX:', error);
                    loginErrorDiv.textContent = 'Error al contactar el servidor. Inténtalo de nuevo.';
                })
                .finally(() => {
                    // Restaurar estado normal del botón
                    loginButton.disabled = false;
                    loginText.textContent = "Iniciar sesión";
                    loginSpinner.style.display = "none";
                });
            });

            function limpiarErrores() {
                document.getElementById('user-error').textContent = '';
                document.getElementById('password-error').textContent = '';
                loginErrorDiv.textContent = '';
            }

            function mostrarErrores(errors) {
                for (const field in errors) {
                    const errorSpan = document.getElementById(field + '-error');
                    if (errorSpan) {
                        errorSpan.textContent = errors[field];
                    }
                }
            }
        });
    </script>
</body>
</html>