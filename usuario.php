<?php
include('conexion.php'); // Asegúrate de tener la conexión correcta a la base de datos

// Datos del nuevo usuario
$nombre = 'Nuevo Administrador'; // Nombre completo del usuario
$usuario = 'admin123'; // Nombre de usuario (debe ser único)
$contrasena = 'contraseñaSegura123'; // Contraseña en texto claro

// Hashear la contraseña para almacenarla de forma segura
$hash_contrasena = password_hash($contrasena, PASSWORD_DEFAULT);

// Consulta para insertar el nuevo usuario en la tabla 'usuarios'
$sql = "INSERT INTO usuarios (nombre, usuario, contraseña, rol) VALUES (?, ?, ?, 'administrador')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $nombre, $usuario, $hash_contrasena);

if ($stmt->execute()) {
    echo "Usuario creado correctamente.";
} else {
    echo "Error al crear el usuario: " . $stmt->error;
}

// Cerrar declaración y conexión
$stmt->close();
$conn->close();
?>
