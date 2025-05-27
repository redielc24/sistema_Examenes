<?php
session_start();
include('conexion.php');

// Verificar si el usuario es administrador
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'administrador') {
    header("Location: login_administrador.php");
    exit();
}

// Consultar todos los exámenes activos para el desplegable
$sqlExamenes = "SELECT id, nombre_examen FROM examenes";
$stmtExamenes = $conn->prepare($sqlExamenes);
$stmtExamenes->execute();
$resultExamenes = $stmtExamenes->get_result();

// Consultar las preguntas de un examen seleccionado
$preguntas = [];
if (isset($_POST['examen_id']) && !empty($_POST['examen_id'])) {
    $examen_id = $_POST['examen_id'];
    $sqlPreguntas = "SELECT id, pregunta, opcion_a, opcion_b, opcion_c, opcion_d, respuesta_correcta 
                     FROM preguntas WHERE examen_id = ?";
    $stmtPreguntas = $conn->prepare($sqlPreguntas);
    $stmtPreguntas->bind_param("i", $examen_id);
    $stmtPreguntas->execute();
    $resultPreguntas = $stmtPreguntas->get_result();
    while ($row = $resultPreguntas->fetch_assoc()) {
        $preguntas[] = $row;
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Preguntas - Sistema de Exámenes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .table-container {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .btn-custom {
            background-color: #007bff;
            color: white;
        }
        .btn-custom:hover {
            background-color: #0056b3;
        }
        .btn-delete {
            background-color: #dc3545;
            color: white;
        }
        .btn-delete:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center mb-4">Ver Preguntas de Examen</h2>

    <!-- Formulario para seleccionar examen -->
    <form method="POST" class="mb-4">
        <div class="mb-3">
            <label for="examen_id" class="form-label">Seleccionar Examen</label>
            <select class="form-select" id="examen_id" name="examen_id" required>
                <option value="">Seleccione un examen</option>
                <?php while ($rowExamen = $resultExamenes->fetch_assoc()): ?>
                    <option value="<?php echo $rowExamen['id']; ?>" <?php echo isset($examen_id) && $examen_id == $rowExamen['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($rowExamen['nombre_examen']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Ver Preguntas</button>
        <a href="panel_administrador.php" class="btn btn-warning">Regresar</a>
    </form>

    <?php if (isset($preguntas) && count($preguntas) > 0): ?>
        <!-- Mostrar preguntas en una tabla -->
        <div class="table-container">
            <h4 class="mb-3">Listado de Preguntas</h4>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Pregunta</th>
                        <th>Opción A</th>
                        <th>Opción B</th>
                        <th>Opción C</th>
                        <th>Opción D</th>
                        <th>Respuesta Correcta</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($preguntas as $index => $pregunta): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($pregunta['pregunta']) ?: 'Pregunta ' . ($index + 1); ?></td>
                            <td><?php echo htmlspecialchars($pregunta['opcion_a']) ?: 'A'; ?></td>
                            <td><?php echo htmlspecialchars($pregunta['opcion_b']) ?: 'B'; ?></td>
                            <td><?php echo htmlspecialchars($pregunta['opcion_c']) ?: 'C'; ?></td>
                            <td><?php echo htmlspecialchars($pregunta['opcion_d']) ?: 'D'; ?></td>
                            <td><?php echo htmlspecialchars($pregunta['respuesta_correcta']); ?></td>
                            <td>
                                <a href="editar_pregunta.php?id=<?php echo $pregunta['id']; ?>" class="btn btn-custom btn-sm">Editar</a>
                                <a href="eliminar_pregunta.php?id=<?php echo $pregunta['id']; ?>" class="btn btn-delete btn-sm" onclick="return confirm('¿Estás seguro de eliminar esta pregunta?');">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
                    </div>
    <?php elseif (isset($examen_id)): ?>
        <p>No hay preguntas disponibles para este examen.</p>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
