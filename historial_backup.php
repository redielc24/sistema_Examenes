<?php
session_start();

// Verificar si el usuario es administrador
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'administrador') {
    header("Location: login_administrador.php");
    exit();
}

// Directorio donde se almacenan los backups
$directorioBackup = __DIR__ . "/backups";

// Obtener la lista de archivos de backup
$archivosBackup = [];
if (is_dir($directorioBackup)) {
    $archivosBackup = array_diff(scandir($directorioBackup), ['.', '..']);
}

// Ordenar los archivos por fecha de creación (últimos primero)
usort($archivosBackup, function($a, $b) use ($directorioBackup) {
    return filemtime($directorioBackup . "/" . $b) - filemtime($directorioBackup . "/" . $a);
});
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Backups</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 50px;
            max-width: 800px;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .btn-download {
            color: white;
            background-color: #4CAF50;
            border: none;
        }
        .btn-download:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center mb-4">Historial de Backups</h2>
        <?php if (count($archivosBackup) > 0): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Nombre del Archivo</th>
                        <th>Fecha de Creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($archivosBackup as $archivo): 
                        $rutaArchivo = $directorioBackup . "/" . $archivo;
                        $fechaCreacion = date("Y-m-d H:i:s", filemtime($rutaArchivo));
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($archivo); ?></td>
                            <td><?php echo $fechaCreacion; ?></td>
                            <td>
                                <a href="backups/<?php echo urlencode($archivo); ?>" class="btn btn-download btn-sm" download>Descargar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center">No se encontraron backups en el sistema.</p>
        <?php endif; ?>
        <a href="panel_administrador.php" class="btn btn-secondary mt-3">Regresar al Panel</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
