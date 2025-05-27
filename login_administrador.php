<?php
session_start();
include('conexion.php');

// Verificar si ya hay una sesión activa
if (isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'administrador') {
    header("Location: panel_administrador.php");
    exit();
}

$error = "";

// Procesar formulario de inicio de sesión
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario']);
    $contraseña = trim($_POST['contraseña']);

    if (!empty($usuario) && !empty($contraseña)) {
        $sql = "SELECT id, nombre, contraseña, estado FROM usuarios WHERE usuario = ? AND rol = 'administrador'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if ($user['estado'] === 'activo') {
                if (password_verify($contraseña, $user['contraseña'])) {
                    $_SESSION['id_usuario'] = $user['id'];
                    $_SESSION['nombre_usuario'] = $user['nombre'];
                    $_SESSION['tipo_usuario'] = 'administrador';
                    header("Location: panel_administrador.php");
                    exit();
                } else {
                    $error = "Contraseña incorrecta.";
                }
            } else {
                $error = "Este usuario está inactivo.";
            }
        } else {
            $error = "Usuario no encontrado.";
        }
    } else {
        $error = "Por favor, complete todos los campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #1d3557, #457b9d);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Roboto', sans-serif;
            color: #f1faee;
        }
        .login-container {
            background: rgba(0, 0, 0, 0.6);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0px 10px 15px rgba(0, 0, 0, 0.3);
            max-width: 400px;
            width: 100%;
        }
        .login-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-control {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 10px;
        }
        .form-control:focus {
            background: rgba(255, 255, 255, 0.2);
            outline: none;
            box-shadow: none;
        }
        .btn-login {
            background: #e63946;
            border: none;
            width: 100%;
            padding: 10px;
            font-size: 18px;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .btn-login:hover {
            background: #d62828;
        }
        .error-msg {
            color: #e63946;
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
        }

    </style>
</head>
<body>
    <div class="login-container">
        <h2><i class="fas fa-user-shield"></i> Administrador</h2>
        <form action="" method="POST">
            <div class="mb-3">
                <label for="usuario" class="form-label">Usuario</label>
                <input type="text" class="form-control" id="usuario" name="usuario" placeholder="Ingrese su usuario" required>
            </div>
            <div class="mb-3">
                <label for="contraseña" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="contraseña" name="contraseña" placeholder="Ingrese su contraseña" required>
            </div>
            <button type="submit" class="btn btn-login">Iniciar Sesión</button>
            <div class="d-grid gap-2 col-6 mx-auto">
                    <br><a href="index.php" class="btn btn-warning">Regresar</a>
                    </div>
                    </form>
                    
        <?php if (!empty($error)): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
