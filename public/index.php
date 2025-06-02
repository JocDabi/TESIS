<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="icon" href="./img/Recurso 1.png">
</head>

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

<body class="bg-gray-100 flex justify-center items-center h-screen">

    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <h1 class="text-2xl font-bold text-gray-800 text-center mb-6">Iniciar sesión</h1>

        <form id="loginForm" class="flex flex-col gap-4" action="php/login.php" method="post">
            <div>
                <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                <input type="email" id="email" name="email" placeholder="Tu email" required  <!- type="email", id="email", name="email"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <span id="email-error" class="error-message"></span>
            </div>

            <div>
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Contraseña</label>
                <div class="relative">
                    <input type="password" id="password" name="contrasena" placeholder="Tu contraseña" required
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
                        const passwordInput = document.getElementById('password');
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
                <span id="password-error" class="error-message"></span>
            </div>

            <div class="flex items-center justify-end">
                <a href="/tesis_final/php/recuperar_contrasena.php" class="inline-block align-baseline font-bold text-sm text-indigo-500 hover:text-indigo-800">
                    ¿Olvidaste tu contraseña?
                </a>
            </div>

            <div id="login-error" class="text-red-500 text-sm italic text-center"></div>

            <div class="flex items-center justify-center">
                <button class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                    Iniciar sesión
                </button>
            </div>
        </form>

        <hr class="my-6 border-gray-300">

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            const loginErrorDiv = document.getElementById('login-error');

            loginForm.addEventListener('submit', function(event) {
                event.preventDefault();

                limpiarErrores();

                const formData = new FormData(loginForm);

                fetch('/tesis_final/php/login.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Error HTTP: ${response.status} ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        window.location.href = 'HTML/dashboard.html';
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
                    alert('Error al contactar el servidor. Inténtalo de nuevo.');
                });
            });

            function limpiarErrores() {
                document.getElementById('email-error').textContent = ''; //<!- id="email-error" ->
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