<?php
// Iniciar sesión
session_start();

// Verificar si el usuario está autenticado como administrador
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'administrador') {
    header('Location: login_administrador.php');
    exit();
}

// Incluir la conexión a la base de datos
include 'conexion.php';

// Obtener datos del usuario autenticado
$creador_id = $_SESSION['id_usuario'];
$creador_nombre = $_SESSION['nombre_usuario'];

// Inicializar mensaje de respuesta
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre_examen = $_POST['nombre_examen'];
    $descripcion = $_POST['descripcion'];
    $activo = isset($_POST['activo']) ? 1 : 0;
    $tipo_examen = $_POST['tipo_examen'];
    $imagen_caratula = $_FILES['imagen_caratula']['name'];
    $archivo_pdf = $_FILES['archivo_pdf']['name'];
    $tiempo_limitado = isset($_POST['tiempo_limitado']) ? 1 : 0;
    $duracion = $tiempo_limitado ? (int)$_POST['duracion_minutos'] : null;

    // Subir la imagen de la carátula
    if ($imagen_caratula) {
        $imagen_caratula_path = 'uploads/' . basename($imagen_caratula);
        move_uploaded_file($_FILES['imagen_caratula']['tmp_name'], $imagen_caratula_path);
    } else {
        $imagen_caratula_path = null;
    }

    // Subir el archivo PDF
    $archivo_pdf_path = null;
    if (!empty($_FILES['archivo_pdf']['name'])) {
        $archivo_pdf_path = 'uploads/' . basename($archivo_pdf);
        if (!move_uploaded_file($_FILES['archivo_pdf']['tmp_name'], $archivo_pdf_path)) {
            die('Error al mover el archivo PDF al directorio de destino.');
        }
    }

    // Insertar el examen en la base de datos
    $sql = "INSERT INTO examenes (nombre_examen, descripcion, activo, creado_por, imagen_caratula, archivo_pdf, tipo_examen, tiempo_limitado, duracion) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssiisssii", $nombre_examen, $descripcion, $activo, $creador_id, $imagen_caratula_path, $archivo_pdf_path, $tipo_examen, $tiempo_limitado, $duracion);

    if ($stmt->execute()) {
        $mensaje = 'success';
    } else {
        echo "Error en la consulta: " . $stmt->error;
        $mensaje = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Examen</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            <?php if ($mensaje === 'success'): ?>
            Swal.fire({
                icon: 'success',
                title: '¡Examen Creado!',
                text: 'El examen se ha creado correctamente.',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                window.location.href = 'gestionar_examenes.php';
            });
            <?php elseif ($mensaje === 'error'): ?>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Hubo un problema al crear el examen. Por favor, inténtalo de nuevo.',
                confirmButtonText: 'Aceptar'
            });
            <?php endif; ?>
        });
    </script>
</head>
<body>
<div class="container mt-5">
    <h2>Crear Nuevo Examen</h2>
    <form method="POST" action="crear_examen.php" enctype="multipart/form-data">
        <div class="form-group">
            <label for="nombre_examen">Nombre del Examen</label>
            <input type="text" class="form-control" id="nombre_examen" name="nombre_examen" required>
        </div>
        <div class="form-group">
            <label for="descripcion">Descripción</label>
            <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required></textarea>
        </div>
        <div class="form-group">
            <label for="activo">Activo</label>
            <input type="checkbox" id="activo" name="activo" checked>
        </div>
        <div class="form-group">
            <label for="imagen_caratula">Imagen de Carátula (opcional)</label>
            <input type="file" class="form-control-file" id="imagen_caratula" name="imagen_caratula" accept="image/*">
        </div>
        <div class="form-group">
            <label for="archivo_pdf">Archivo PDF</label>
            <input type="file" class="form-control-file" id="archivo_pdf" name="archivo_pdf" accept="application/pdf" required>
        </div>
        <div class="form-group">
            <label for="tipo_examen">Tipo de Examen</label>
            <select class="form-control" id="tipo_examen" name="tipo_examen" required>
                <option value="PDF">PDF</option>
                <option value="TEXTO">Texto</option>
                <option value="MIXTO">Mixto</option>
            </select>
        </div>
        <div class="form-group">
            <label for="tiempo_limitado">¿Activar Temporizador?</label>
            <input type="checkbox" id="tiempo_limitado" name="tiempo_limitado" onchange="toggleDuracionField()">
        </div>
        <div class="form-group" id="duracion_field" style="display: none;">
            <label for="duracion_minutos">Duración (minutos)</label>
            <input type="number" class="form-control" id="duracion_minutos" name="duracion_minutos" min="1">
        </div>
        <div class="form-group">
            <label for="creador">Creado por</label>
            <input type="text" class="form-control" id="creador" name="creador" value="<?= htmlspecialchars($creador_nombre) ?>" readonly>
        </div>
        <button type="submit" class="btn btn-primary">Crear Examen</button>
        <a href="panel_administrador.php" class="btn btn-warning">Cancelar</a>
    </form>
</div>

<script>
    function toggleDuracionField() {
        const duracionField = document.getElementById('duracion_field');
        const tiempoLimitadoCheckbox = document.getElementById('tiempo_limitado');
        if (tiempoLimitadoCheckbox.checked) {
            duracionField.style.display = 'block';
        } else {
            duracionField.style.display = 'none';
            document.getElementById('duracion_minutos').value = '';
        }
    }
</script>
</body>
</html>
