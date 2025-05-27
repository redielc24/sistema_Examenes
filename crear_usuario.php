<?php
session_start();
include('conexion.php');

// Verificar si el usuario es administrador
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'administrador') {
    header("Location: login_administrador.php"); // Redirigir si no es administrador
    exit();
}

// Procesar el formulario de creación de usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $usuario = trim($_POST['usuario']);
    $contraseña = trim($_POST['contraseña']);
    $confirmar_contraseña = trim($_POST['confirmar_contraseña']);
    $rol = $_POST['rol'];
    $estado = isset($_POST['estado']) ? 1 : 0; // Verificar si se seleccionó "estado"

    // Validar que los campos no estén vacíos
    if ($nombre === '' || $usuario === '' || $contraseña === '' || $confirmar_contraseña === '') {
        $error = "Todos los campos son obligatorios.";
    } elseif ($contraseña !== $confirmar_contraseña) {
        $error = "Las contraseñas no coinciden.";
    } else {
        // Hash de la contraseña
        $contraseña_hash = password_hash($contraseña, PASSWORD_DEFAULT);

        // Insertar en la base de datos
        $sql = "INSERT INTO usuarios (nombre, usuario, contraseña, rol, estado) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $nombre, $usuario, $contraseña_hash, $rol, $estado);

        if ($stmt->execute()) {
            $success = "Usuario creado exitosamente.";
        } else {
            $error = "Error al crear el usuario: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 500px;
            margin-top: 50px;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        .form-control:focus, .form-check-input:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.25);
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center mb-4">Crear Nuevo Usuario</h2>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form action="crear_usuario.php" method="POST">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre Completo</label>
                <input type="text" name="nombre" id="nombre" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="usuario" class="form-label">Nombre de Usuario</label>
                <input type="text" name="usuario" id="usuario" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="contraseña" class="form-label">Contraseña</label>
                <input type="password" name="contraseña" id="contraseña" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="confirmar_contraseña" class="form-label">Confirmar Contraseña</label>
                <input type="password" name="confirmar_contraseña" id="confirmar_contraseña" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="rol" class="form-label">Rol</label>
                <select name="rol" id="rol" class="form-select" required>
                    <option value="administrador">Administrador</option>
                    <option value="docente">Docente</option>
                </select>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="estado" id="estado" checked>
                <label class="form-check-label" for="estado">
                    Usuario activo
                </label>
            </div>
            <button type="submit" class="btn btn-primary w-100">Crear Usuario</button>
            <a href="ver_usuarios.php" class="btn btn-secondary w-100 mt-2">Regresar</a>
        </form>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Cerrar conexión
$conn->close();
?>
