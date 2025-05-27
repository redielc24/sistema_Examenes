<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
<?php
session_start();

// Configuración de la base de datos y la ruta del backup
$host = "localhost";
$usuario = "root";
$contraseña = ""; // Cambiar si tiene contraseña
$nombreBaseDatos = "sistema_examenes3";
$directorioBackup = __DIR__ . "/backups";

// Verifica si el usuario es administrador
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'administrador') {
    header("Location: login_administrador.php");
    exit();
}

// Asegúrate de que la carpeta de backups exista
if (!is_dir($directorioBackup)) {
    mkdir($directorioBackup, 0777, true);
}

// Nombre del archivo de backup
$nombreArchivo = "backup_" . date("Y-m-d_H-i-s") . ".sql";
$rutaArchivo = $directorioBackup . "/" . $nombreArchivo;

// Ruta completa al binario de mysqldump
$rutaMysqldump = "C:\\xampp\\mysql\\bin\\mysqldump.exe"; // Cambia la ruta si es diferente

// Comando para realizar el backup
$comando = "\"$rutaMysqldump\" -h $host -u $usuario";
if (!empty($contraseña)) {
    $comando .= " -p$contraseña";
}
$comando .= " $nombreBaseDatos > \"$rutaArchivo\"";

// Ejecutar el comando
$output = [];
$resultado = null;
exec($comando, $output, $resultado);

// Mostrar resultado al usuario
if ($resultado === 0) {
    echo "<div style='text-align: center; margin-top: 50px;'>";
    echo "<h3>✅ Backup realizado con éxito</h3>";
    echo "<p>Descarga tu copia de seguridad aquí:</p>";
    echo "<a href='backups/$nombreArchivo' class='btn btn-primary'>Descargar Backup</a><br><br>";
    echo "<a href='crear_backup.php' class='btn btn-secondary'>Volver</a>";
    echo "</div>";
} else {
    echo "<div style='text-align: center; margin-top: 50px; color: red;'>";
    echo "<h3>❌ Error al realizar el backup</h3>";
    echo "<p>Código de error: $resultado</p>";
    echo "<a href='crear_backup.php' class='btn btn-secondary'>Volver</a>";
    echo "</div>";
}
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>