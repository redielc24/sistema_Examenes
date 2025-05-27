<?php
session_start();
include('conexion.php');

// Verificar si el usuario es administrador
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'administrador') {
    header("Location: login_administrador.php"); // Redirigir si no tiene acceso
    exit();
}

// Cambiar el estado del usuario
if (isset($_GET['cambiar_estado'])) {
    $usuario_id = $_GET['cambiar_estado'];
    $estado_actual = $_GET['estado'];

    // Cambiar entre "activo" e "inactivo"
    $nuevo_estado = ($estado_actual == 'activo') ? 'inactivo' : 'activo';

    $sql = "UPDATE usuarios SET estado = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $nuevo_estado, $usuario_id);

    if ($stmt->execute()) {
        header("Location: ver_usuarios.php?estado_cambiado=true");
        exit();
    } else {
        echo "Error al cambiar el estado del usuario.";
    }
}

// Consultar todos los usuarios
$sql = "SELECT id, nombre, usuario, rol, estado, creado_en FROM usuarios";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 50px;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .btn-estado {
            width: 100px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mb-4">Gestión de Usuarios</h2>
        <a href="crear_usuario.php" class="btn btn-primary mb-3">Agregar Usuario</a>
        <a href="panel_administrador.php" class="btn btn-secondary mb-3">Regresar</a>
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Creado en</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['nombre']; ?></td>
                            <td><?php echo $row['usuario']; ?></td>
                            <td><?php echo ucfirst($row['rol']); ?></td>
                            <td>
                                <a href="?cambiar_estado=<?php echo $row['id']; ?>&estado=<?php echo $row['estado']; ?>"
                                   class="btn btn-sm btn-<?php echo $row['estado'] == 'activo' ? 'success' : 'danger'; ?> btn-estado">
                                    <?php echo ucfirst($row['estado']); ?>
                                </a>
                            </td>
                            <td><?php echo date("d/m/Y H:i", strtotime($row['creado_en'])); ?></td>
                            <td>
                                <a href="editar_usuario.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
                                <a href="eliminar_usuario.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm"
                                   onclick="return confirm('¿Estás seguro de eliminar este usuario?');">Eliminar</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No hay usuarios registrados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Cerrar conexión
$conn->close();
?>
