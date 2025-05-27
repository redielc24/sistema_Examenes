<?php
session_start();
include('conexion.php');

// Verificar si el usuario es administrador
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'administrador') {
    header("Location: login_administrador.php");
    exit();
}

// Inicializar variables
$mensaje = "";
$nombre_examen = "";
$descripcion = "";
$activo = 0;
$creado_por = 0;
$imagen_caratula = "";
$archivo_pdf = "";
$examen_id = 0;
$tiempo_limitado = 0;
$duracion = null;

// Verificar si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $examen_id = $_POST['examen_id'];
    $nombre_examen = $_POST['nombre_examen'];
    $descripcion = $_POST['descripcion'];
    $activo = $_POST['activo'];
    $creado_por = $_POST['creado_por'];
    $tiempo_limitado = isset($_POST['tiempo_limitado']) ? 1 : 0;
    $duracion = $tiempo_limitado ? $_POST['duracion'] : null;

    // Subir y procesar la imagen de carátula
    if (!empty($_FILES['imagen_caratula']['name'])) {
        $imagen_caratula = 'uploads/' . basename($_FILES['imagen_caratula']['name']);
        if (move_uploaded_file($_FILES['imagen_caratula']['tmp_name'], $imagen_caratula)) {
            // Se subió la imagen correctamente
        } else {
            $mensaje = "Error al subir la imagen de carátula.";
        }
    }

    // Subir y procesar el archivo PDF
    if (!empty($_FILES['archivo_pdf']['name'])) {
        $archivo_pdf = 'uploads/' . basename($_FILES['archivo_pdf']['name']);
        if (move_uploaded_file($_FILES['archivo_pdf']['tmp_name'], $archivo_pdf)) {
            // Se subió el archivo PDF correctamente
        } else {
            $mensaje = "Error al subir el archivo PDF.";
        }
    }

    // Actualizar el examen en la base de datos
    $sql = "UPDATE examenes SET nombre_examen = ?, descripcion = ?, activo = ?, creado_por = ?, tiempo_limitado = ?, duracion = ?";

    // Agregar condiciones para actualizar solo si se ha cargado una nueva imagen o PDF
    $params = [$nombre_examen, $descripcion, $activo, $creado_por, $tiempo_limitado, $duracion];
    $types = "ssiiii";

    if ($imagen_caratula) {
        $sql .= ", imagen_caratula = ?";
        $params[] = $imagen_caratula;
        $types .= "s";
    }
    if ($archivo_pdf) {
        $sql .= ", archivo_pdf = ?";
        $params[] = $archivo_pdf;
        $types .= "s";
    }
    $sql .= " WHERE id = ?";
    $params[] = $examen_id;
    $types .= "i";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        $mensaje = "Examen actualizado con éxito.";
    } else {
        $mensaje = "Error al actualizar el examen: " . $stmt->error;
    }
    $stmt->close();
}

// Consultar el examen a editar
if (isset($_GET['id'])) {
    $examen_id = $_GET['id'];
    $sql = "SELECT nombre_examen, descripcion, activo, creado_por, imagen_caratula, archivo_pdf, tiempo_limitado, duracion FROM examenes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $examen_id);
    $stmt->execute();
    $stmt->bind_result($nombre_examen, $descripcion, $activo, $creado_por, $imagen_caratula, $archivo_pdf, $tiempo_limitado, $duracion);
    $stmt->fetch();
    $stmt->close();
}

// Cerrar conexión
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Examen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f2dede; /* Color rojo bajito */
        }
        .container {
            max-width: 600px;
            margin-top: 50px;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        .thumbnail {
            width: 60px;
            height: auto;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center mb-4">Editar Examen</h2>
        <?php if ($mensaje): ?>
            <div class="alert alert-success"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        <form method="POST" action="" enctype="multipart/form-data">
            <input type="hidden" name="examen_id" value="<?php echo $examen_id; ?>">

            <div class="mb-3">
                <label for="nombre_examen" class="form-label">Nombre del Examen</label>
                <input type="text" class="form-control" name="nombre_examen" value="<?php echo htmlspecialchars($nombre_examen); ?>" required>
            </div>

            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea class="form-control" name="descripcion" rows="3" required><?php echo htmlspecialchars($descripcion); ?></textarea>
            </div>

            <div class="mb-3">
                <label for="activo" class="form-label">Activo</label>
                <select class="form-control" id="activo" name="activo" required>
                    <option value="1" <?php if ($activo == 1) echo 'selected'; ?>>Sí</option>
                    <option value="0" <?php if ($activo == 0) echo 'selected'; ?>>No</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="creado_por" class="form-label">Creado Por</label>
                <input type="number" class="form-control" name="creado_por" value="<?php echo $creado_por; ?>" required>
            </div>

            <!-- Campo para activar límite de tiempo -->
            <div class="mb-3">
                <label for="tiempo_limitado" class="form-label">¿Limite de Tiempo?</label>
                <input type="checkbox" class="form-check-input" name="tiempo_limitado" <?php echo ($tiempo_limitado == 1) ? 'checked' : ''; ?>>
            </div>

            <!-- Campo para la duración solo si está activado el límite de tiempo -->
            <div class="mb-3" id="duracion-container" style="<?php echo ($tiempo_limitado == 1) ? '' : 'display:none'; ?>">
                <label for="duracion" class="form-label">Duración en minutos</label>
                <input type="number" class="form-control" name="duracion" value="<?php echo $duracion; ?>" <?php echo ($tiempo_limitado == 1) ? 'required' : ''; ?>>
            </div>

            <div class="mb-3">
                <label for="imagen_caratula" class="form-label">Imagen de Carátula</label>
                <input type="file" class="form-control" name="imagen_caratula" accept="image/*">
                <?php if ($imagen_caratula): ?>
                    <p>Imagen actual:</p>
                    <img src="<?php echo $imagen_caratula; ?>" alt="Carátula" class="thumbnail">
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="archivo_pdf" class="form-label">Archivo PDF</label>
                <input type="file" class="form-control" name="archivo_pdf" accept="application/pdf">
                <?php if ($archivo_pdf): ?>
                    <p>Archivo actual: <a href="<?php echo $archivo_pdf; ?>" target="_blank">Ver PDF</a></p>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary">Actualizar Examen</button>
            <a href="gestionar_examenes.php" class="btn btn-secondary">Regresar</a>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Mostrar o ocultar el campo de duración según si el checkbox de tiempo limitado está activado
        const tiempoLimiteCheckbox = document.querySelector('input[name="tiempo_limitado"]');
        const duracionContainer = document.getElementById('duracion-container');
        tiempoLimiteCheckbox.addEventListener('change', function () {
            duracionContainer.style.display = tiempoLimiteCheckbox.checked ? '' : 'none';
        });
    </script>
</body>
</html>
