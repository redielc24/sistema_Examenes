<?php
$host = "localhost"; // Cambiar según sea necesario
$user = "root";      // Usuario de la base de datos
$password = "";      // Contraseña de la base de datos
$database = "sistema_examenes3"; // Nombre de tu base de datos

$conn = new mysqli($host, $user, $password, $database);

// Verifica si hay errores en la conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>