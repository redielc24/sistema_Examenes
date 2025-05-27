<?php
session_start();
include('conexion.php');

// Verificar si el usuario es administrador
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'administrador') {
    header("Location: login_administrador.php");
    exit();
}

// Verificar si se pasa un ID de usuario
if (!isset($_GET['id'])) {
    header("Location: ver_usuarios.php");
    exit();
}

// Obtener el ID del usuario
$idUsuario = $_GET['id'];

// Consultar los datos del usuario
$sqlUsuario = "SELECT id, nombre, usuario, rol, contraseña FROM usuarios WHERE id = ?";
$stmtUsuario = $conn->prepare($sqlUsuario);
$stmtUsuario->bind_param("i", $idUsuario);
$stmtUsuario->execute();
$resultUsuario = $stmtUsuario->get_result();

// Si no existe el usuario, redirigir
if ($resultUsuario->num_rows == 0) {
    header("Location: ver_usuarios.php");
    exit();
}

$usuario = $resultUsuario->fetch_assoc();

// Verificar si el formulario ha sido enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener los datos del formulario
    $nombre = $_POST['nombre'];
    $usuarioNombre = $_POST['usuario'];
    $rol = $_POST['rol'];
    $nuevaContraseña = $_POST['nueva_contraseña'];
    $confirmarContraseña = $_POST['confirmar_contraseña'];

    // Validar que todos los campos estén completos
    if (empty($nombre) || empty($usuarioNombre) || empty($rol)) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Todos los campos son obligatorios.'
            });
        </script>";
    } else {
        // Si se ha ingresado una nueva contraseña, validarla
        if (!empty($nuevaContraseña) || !empty($confirmarContraseña)) {
            // Verificar que las contraseñas coincidan
            if ($nuevaContraseña !== $confirmarContraseña) {
                echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Las contraseñas no coinciden.'
                    });
                </script>";
            } else {
                // Hashear la nueva contraseña
                $nuevaContraseña = password_hash($nuevaContraseña, PASSWORD_DEFAULT);
            }
        }

        // Preparar la consulta para actualizar el usuario
        $sqlActualizar = "UPDATE usuarios SET nombre = ?, usuario = ?, rol = ?, contraseña = ? WHERE id = ?";
        
        // Si no se actualiza la contraseña, se deja la actual
        $contraseñaActual = !empty($nuevaContraseña) ? $nuevaContraseña : $usuario['contraseña'];

        $stmtActualizar = $conn->prepare($sqlActualizar);
        $stmtActualizar->bind_param("ssssi", $nombre, $usuarioNombre, $rol, $contraseñaActual, $idUsuario);

        // Ejecutar la consulta
        if ($stmtActualizar->execute()) {
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Usuario Actualizado',
                    text: 'Los cambios se han guardado exitosamente.'
                }).then(() => {
                    window.location.href = 'ver_usuarios.php';
                });
            </script>";
        } else {
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo actualizar el usuario. Inténtalo de nuevo.'
                });
            </script>";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario - Sistema de Exámenes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin-top: 50px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="text-center mb-4">Editar Usuario</h2>
    
    <form method="POST">
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre Completo</label>
            <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="usuario" class="form-label">Nombre de Usuario</label>
            <input type="text" class="form-control" id="usuario" name="usuario" value="<?php echo htmlspecialchars($usuario['usuario']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="rol" class="form-label">Rol</label>
            <select class="form-select" id="rol" name="rol" required>
                <option value="administrador" <?php echo ($usuario['rol'] == 'administrador') ? 'selected' : ''; ?>>Administrador</option>
                <option value="docente" <?php echo ($usuario['rol'] == 'docente') ? 'selected' : ''; ?>>Docente</option>
            </select>
        </div>
        
        <div class="mb-3">
            <label for="nueva_contraseña" class="form-label">Nueva Contraseña (Opcional)</label>
            <input type="password" class="form-control" id="nueva_contraseña" name="nueva_contraseña" placeholder="Ingrese nueva contraseña">
        </div>
        <div class="mb-3">
            <label for="confirmar_contraseña" class="form-label">Confirmar Nueva Contraseña</label>
            <input type="password" class="form-control" id="confirmar_contraseña" name="confirmar_contraseña" placeholder="Confirme nueva contraseña">
        </div>

                
        <div class="text-center">
            <button type="submit" class="btn btn-primary">Actualizar Usuario</button>
            <a href="ver_usuarios.php" class="btn btn-warning">Cancelar</a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
