<?php
// Conexión a la base de datos
include 'connect.php';

$conn = new mysqli($db_host, $db_usuario, $db_contra, $db_nombre);

// Verificar conexión
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Obtener el ID de la transacción desde la URL
$idTransaccion = $_GET['id'];

// Consulta para obtener los detalles de la transacción
$sql = "SELECT * FROM transacciones WHERE id_transacciones = $idTransaccion";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo "<table class='min-w-full table-auto border-collapse border border-gray-200'>
            <thead class='bg-gray-100'>
                <tr>
                    <th class='border border-gray-200 px-4 py-2 text-left'>ID Pago</th>
                    <th class='border border-gray-200 px-4 py-2 text-left'>Fecha</th>
                    <th class='border border-gray-200 px-4 py-2 text-left'>Cliente</th>
                    <th class='border border-gray-200 px-4 py-2 text-left'>Monto</th>
                    <th class='border border-gray-200 px-4 py-2 text-left'>Estado</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class='border border-gray-200 px-4 py-2'>" . $row["id_transacciones"] . "</td>
                    <td class='border border-gray-200 px-4 py-2'>" . $row["fecha"] . "</td>
                    <td class='border border-gray-200 px-4 py-2'>" . $row["id_usuario"] . "</td>
                    <td class='border border-gray-200 px-4 py-2'>$" . $row["monto"] . "</td>
                    <td class='border border-gray-200 px-4 py-2'>" . $row["estado"] . "</td>
                </tr>
            </tbody>
          </table>";
} else {
    echo "No se encontraron detalles para esta transacción.";
}

// Cerrar conexión
$conn->close();
?>