<?php
session_start();

// Verifica si el usuario es administrador
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'administrador') {
    header("Location: login_administrador.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Backup - Sistema de Exámenes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 50px;
            max-width: 600px;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .btn-backup {
            background: linear-gradient(45deg, #4CAF50, #45a049);
            color: white;
            border: none;
        }
        .btn-backup:hover {
            background: linear-gradient(45deg, #45a049, #4CAF50);
        }
    </style>
</head>
<body>
    <div class="container text-center">
        <h2>Crear Backup</h2>
        <p>Guarda una copia de seguridad de la base de datos del sistema.</p>
        <form action="procesar_backup.php" method="post">
            <button type="submit" class="btn btn-backup btn-lg mb-3">Generar Backup</button>
        </form>
        <a href="panel_administrador.php" class="btn btn-secondary">Regresar al Panel</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
