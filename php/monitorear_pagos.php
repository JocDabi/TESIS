<?php
// Conexión a la base de datos
include 'connect.php';

$conn = new mysqli($db_host, $db_usuario, $db_contra, $db_nombre);

// Verificar conexión
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Consulta para obtener las transacciones con el id_usuario
$sql = "SELECT id_transacciones, fecha, monto, estado, id_usuario FROM transacciones";
$result = $conn->query($sql);

// Cerrar conexión
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitorear Pagos Realizados</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="icon" href="./img/Recurso 1.png">
    <style>
        * {
            font-family: "Montserrat", sans-serif;
        }
        .dropdown:hover .dropdown-menu {
            display: block;
        }
        .dropdown-menu {
            display: none;
            position: absolute;
            background-color: white;
            border: 1px solid #e2e8f0;
            padding: 0.5rem 0;
            box-shadow: 0 2px 4px 0 rgba(0,0,0,0.1);
            min-width: 220px;
            z-index: 10;
        }
        .dropdown-menu a {
            padding: 0.75rem 1.25rem;
            display: block;
            color: #4a5568;
            transition: background-color 0.2s, color 0.2s;
            font-size: 0.9rem;
        }
        .dropdown-menu a:hover {
            background-color: #f7fafc;
            color: #1a202c;
        }
    </style>
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">

    <nav class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex w-full">
                    <div class="hidden md:ml-6 md:flex space-x-8">
                        <div class="dropdown relative">
                            <button class="text-gray-700 hover:bg-gray-100 px-3 py-4 rounded-md text-sm font-medium focus:outline-none">
                                Transacciones
                            </button>
                            <div class="dropdown-menu absolute mt-2 right-0 md:left-0">
                                <a href="./monitorear_pagos.html" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Monitorear pagos realizados</a>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Identificar pagos fuera de fecha</a>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Detectar deudas</a>
                            </div>
                        </div>
                        <div class="dropdown relative">
                            <button class="text-gray-700 hover:bg-gray-100 px-3 py-4 rounded-md text-sm font-medium focus:outline-none">
                                Reportes y gráficos
                            </button>
                            <div class="dropdown-menu absolute mt-2 right-0 md:left-0">
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Generar lista de pagos</a>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Generar gráficos de estadísticas de las transacciones</a>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Generar un informe sobre incidentes de seguridad</a>
                            </div>
                        </div>
                        <div class="dropdown relative">
                            <button class="text-gray-700 hover:bg-gray-100 px-3 py-4 rounded-md text-sm font-medium focus:outline-none">
                                Ayuda
                            </button>
                            <div class="dropdown-menu absolute mt-2 right-0 md:left-0">
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Manual de usuario</a>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Información del sistema</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="hidden md:flex items-center ml-6">
                    <div class="ml-3 relative dropdown">
                        <div>
                            <button type="button" class="max-w-xs bg-gray-800 rounded-full flex items-center text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-white" id="user-menu-button" aria-expanded="false" aria-haspopup="true">
                                <span class="sr-only">Abrir menú de usuario</span>
                                <img class="h-8 w-8 rounded-full" src="https://images.unsplash.com/photo-1472099645781-865e4da108fa?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="Foto de perfil">
                            </button>
                        </div>
                        <div class="dropdown-menu absolute mt-2 right-0 w-48 py-1 bg-white shadow-md origin-top-right focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" tabindex="-1">
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1" id="user-menu-item-0">Tu perfil</a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1" id="user-menu-item-1">Configuración</a>
                            <a href="../index.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1" id="user-menu-item-2">Cerrar sesión</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8 ">
        <div class="px-4 py-6 sm:px-0">
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800">Monitoreo de Pagos Realizados</h2>
                </div>

                <div class="p-6"> 

                    <div class="mb-4 flex space-x-4"> 
                        <div class="relative">
                            <label for="filtro-fecha-inicio" class="block text-gray-700 text-sm font-bold mb-2">Fecha Inicio:</label>
                            <input type="date" id="filtro-fecha-inicio" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>

                        <div class="relative">
                            <label for="filtro-fecha-fin" class="block text-gray-700 text-sm font-bold mb-2">Fecha Fin:</label>
                            <input type="date" id="filtro-fecha-fin" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>

                        <div class="relative">
                            <label for="filtro-estado" class="block text-gray-700 text-sm font-bold mb-2">Estado:</label>
                            <select id="filtro-estado" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="">Todos</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="pagado">Pagado</option>
                                <option value="rechazado">Rechazado</option>
                            </select>
                        </div>

                        <div class="relative">
                            <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="button">
                                Filtrar
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto"> <!-- Tabla responsiva -->
                        <table class="min-w-full table-auto border-collapse border border-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="border border-gray-200 px-4 py-2 text-left">ID Pago</th>
                                    <th class="border border-gray-200 px-4 py-2 text-left">Fecha</th>
                                    <th class="border border-gray-200 px-4 py-2 text-left">Cliente</th>
                                    <th class="border border-gray-200 px-4 py-2 text-left">Monto</th>
                                    <th class="border border-gray-200 px-4 py-2 text-left">Estado</th>
                                    <th class="border border-gray-200 px-4 py-2 text-left">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td class='border border-gray-200 px-4 py-2'>" . $row["id_transacciones"] . "</td>";
                                        echo "<td class='border border-gray-200 px-4 py-2'>" . $row["fecha"] . "</td>";
                                        echo "<td class='border border-gray-200 px-4 py-2'>" . $row["id_usuario"] . "</td>";
                                        echo "<td class='border border-gray-200 px-4 py-2'>$" . $row["monto"] . "</td>";
                                        // Estilo para el estado
                                        $estado_class = "";
                                        if ($row["estado"] == "Completada") {
                                            $estado_class = "text-green-800 bg-green-100";
                                        } elseif ($row["estado"] == "Pendiente") {
                                            $estado_class = "text-yellow-800 bg-yellow-100";
                                        } elseif ($row["estado"] == "Cancelada") {
                                            $estado_class = "text-red-800 bg-red-100";
                                        }
                                        echo "<td class='border border-gray-200 px-4 py-2'><span class='inline-block px-2 py-1 font-semibold leading-tight rounded-full $estado_class'>" . $row["estado"] . "</span></td>";
                                        echo "<td class='border border-gray-200 px-4 py-2'>
                                            <button onclick='mostrarDetalles(" . $row["id_transacciones"] . ")' class='bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded text-xs'>Ver Detalle</button>
                                        </td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='6' class='border border-gray-200 px-4 py-2 text-center'>No hay transacciones registradas.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 flex justify-between items-center">
                        <div class="text-gray-700 text-sm">
                            Mostrando 1 a <?php echo $result->num_rows; ?> de <?php echo $result->num_rows; ?> pagos
                        </div>
                        <div class="space-x-2">
                            <button class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-3 rounded">Anterior</button>
                            <button class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-3 rounded">Siguiente</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <!-- Modal para detalles de la transacción -->
    <div id="modalDetalles" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white p-6 rounded-lg shadow-md w-full max-w-2xl">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Detalles de la Transacción</h2>
            <div id="detallesTransaccion" class="overflow-x-auto">
                <!-- Aquí se cargarán los detalles de la transacción -->
            </div>
            <div class="mt-6 flex justify-end">
                <button onclick="cerrarModal()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Cerrar</button>
            </div>
        </div>
    </div>

    <footer class="bg-gray-800 text-white text-center py-4 mt-auto">
        <p>&copy; 2024 DCM Development. Todos los derechos reservados.</p>
    </footer>

    <script>
        // Función para mostrar el modal con los detalles de la transacción
        function mostrarDetalles(idTransaccion) {
            fetch(`detalles_transaccion.php?id=${idTransaccion}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('detallesTransaccion').innerHTML = data;
                    document.getElementById('modalDetalles').classList.remove('hidden');
                })
                .catch(error => console.error('Error:', error));
        }

        // Función para cerrar el modal
        function cerrarModal() {
            document.getElementById('modalDetalles').classList.add('hidden');
        }
    </script>

</body>
</html>