<?php
session_start();
include('conexion.php');

// Verificar si el usuario es administrador
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'administrador') {
    header("Location: login_administrador.php");
    exit();
}

// Verificar si se ha pasado el ID de la pregunta
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $pregunta_id = $_GET['id'];

    // Consultar la pregunta por su ID
    $sqlPregunta = "SELECT * FROM preguntas WHERE id = ?";
    $stmtPregunta = $conn->prepare($sqlPregunta);
    $stmtPregunta->bind_param("i", $pregunta_id);
    $stmtPregunta->execute();
    $resultPregunta = $stmtPregunta->get_result();

    if ($resultPregunta->num_rows > 0) {
        $pregunta = $resultPregunta->fetch_assoc();
    } else {
        echo "Pregunta no encontrada.";
        exit();
    }

    // Obtener los exámenes disponibles para el desplegable
    $sqlExamenes = "SELECT id, nombre_examen FROM examenes WHERE activo = '1'";
    $stmtExamenes = $conn->prepare($sqlExamenes);
    $stmtExamenes->execute();
    $resultExamenes = $stmtExamenes->get_result();

    // Verificar si se ha enviado el formulario de actualización
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Obtener los datos del formulario
        $pregunta_texto = $_POST['pregunta'];
        $opcion_a = $_POST['opcion_a'];
        $opcion_b = $_POST['opcion_b'];
        $opcion_c = $_POST['opcion_c'];
        $opcion_d = $_POST['opcion_d'];
        $respuesta_correcta = $_POST['respuesta_correcta'];
        $examen_id = $_POST['examen_id'];

        // Actualizar la pregunta en la base de datos
        $sqlUpdatePregunta = "UPDATE preguntas SET examen_id = ?, pregunta = ?, opcion_a = ?, opcion_b = ?, opcion_c = ?, opcion_d = ?, respuesta_correcta = ? WHERE id = ?";
        $stmtUpdatePregunta = $conn->prepare($sqlUpdatePregunta);
        $stmtUpdatePregunta->bind_param("issssssi", $examen_id, $pregunta_texto, $opcion_a, $opcion_b, $opcion_c, $opcion_d, $respuesta_correcta, $pregunta_id);

        if ($stmtUpdatePregunta->execute()) {
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Pregunta actualizada',
                    text: 'La pregunta se ha actualizado correctamente.'
                }).then(() => {
                    window.location.href = 'ver_preguntas.php';
                });
            </script>";
        } else {
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo actualizar la pregunta. Inténtalo de nuevo.'
                });
            </script>";
        }
    }
} else {
    echo "ID de pregunta no válido.";
    exit();
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Pregunta - Sistema de Exámenes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .container {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .circle {
            display: inline-block;
            width: 30px;
            height: 30px;
            margin: 5px;
            border-radius: 50%;
            text-align: center;
            line-height: 30px;
            background-color: #ddd;
            cursor: pointer;
        }
        .circle.selected {
            background-color: #007bff;
            color: white;
        }
        .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
        }
        .btn-warning:hover {
            background-color: #e0a800;
            border-color: #d39e00;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center mb-4">Editar Pregunta</h2>
    <form method="POST">
        <div class="mb-3">
            <label for="examen_id" class="form-label">Examen</label>
            <select class="form-select" id="examen_id" name="examen_id" required>
                <option value="">Seleccione un examen</option>
                <?php while ($rowExamen = $resultExamenes->fetch_assoc()): ?>
                    <option value="<?php echo $rowExamen['id']; ?>" <?php echo $pregunta['examen_id'] == $rowExamen['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($rowExamen['nombre_examen']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="pregunta" class="form-label">Pregunta</label>
            <textarea class="form-control" id="pregunta" name="pregunta" rows="3" required><?php echo htmlspecialchars($pregunta['pregunta']); ?></textarea>
        </div>
        <div class="mb-3">
            <label for="opcion_a" class="form-label">Opción A</label>
            <input type="text" class="form-control" id="opcion_a" name="opcion_a" value="<?php echo htmlspecialchars($pregunta['opcion_a']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="opcion_b" class="form-label">Opción B</label>
            <input type="text" class="form-control" id="opcion_b" name="opcion_b" value="<?php echo htmlspecialchars($pregunta['opcion_b']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="opcion_c" class="form-label">Opción C</label>
            <input type="text" class="form-control" id="opcion_c" name="opcion_c" value="<?php echo htmlspecialchars($pregunta['opcion_c']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="opcion_d" class="form-label">Opción D</label>
            <input type="text" class="form-control" id="opcion_d" name="opcion_d" value="<?php echo htmlspecialchars($pregunta['opcion_d']); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Respuesta Correcta</label>
            <div>
                <div class="circle" onclick="selectAnswer(this, 'A')">A</div>
                <div class="circle" onclick="selectAnswer(this, 'B')">B</div>
                <div class="circle" onclick="selectAnswer(this, 'C')">C</div>
                <div class="circle" onclick="selectAnswer(this, 'D')">D</div>
                <input type="hidden" id="respuesta_correcta" name="respuesta_correcta" value="<?php echo htmlspecialchars($pregunta['respuesta_correcta']); ?>" required>
            </div>
        </div>
        <div class="text-center">
            <button type="submit" class="btn btn-primary">Actualizar Pregunta</button>
            <a href="ver_preguntas.php" class="btn btn-warning">Volver al Listado</a>
        </div>
    </form>
</div>

<script>
    // Función para manejar la selección de respuesta correcta
    function selectAnswer(circle, answer) {
        const circles = document.querySelectorAll('.circle');
        circles.forEach(c => c.classList.remove('selected'));
        circle.classList.add('selected');
        document.getElementById('respuesta_correcta').value = answer;
    }

    // Preseleccionar la respuesta correcta basada en el valor actual
    document.addEventListener('DOMContentLoaded', () => {
        const respuestaCorrecta = "<?php echo htmlspecialchars($pregunta['respuesta_correcta']); ?>";
        const selectedCircle = document.querySelector(`.circle:nth-child(${['A', 'B', 'C', 'D'].indexOf(respuestaCorrecta) + 1})`);
        if (selectedCircle) {
            selectedCircle.classList.add('selected');
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
