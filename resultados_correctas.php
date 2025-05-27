<?php
session_start();
include('conexion.php');

// Verificar si el usuario es administrador
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'administrador') {
    header("Location: login_administrador.php");
    exit();
}

// Variables para almacenar los datos
$examenId = "";
$gradoId = "";
$seccionId = "";
$estudiantes = [];
$preguntas = [];
$respuestasEstudiantes = [];

// Obtener los exámenes, grados y secciones para los filtros
$sqlExamenes = "SELECT * FROM examenes WHERE activo = 1";
$sqlGrados = "SELECT * FROM grados";
$sqlSecciones = "SELECT * FROM secciones";

$examenes = $conn->query($sqlExamenes);
$grados = $conn->query($sqlGrados);
$secciones = $conn->query($sqlSecciones);

// Si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $examenId = $_POST['examen'];
    $gradoId = $_POST['grado'];
    $seccionId = $_POST['seccion'];

    // Obtener los estudiantes que coinciden con el grado y sección seleccionados
    $sqlEstudiantes = "SELECT r.id AS resultado_id, r.nombres, r.apellidos, r.grado_id, r.seccion_id, r.examen_id
                       FROM resultados r
                       WHERE r.grado_id = ? AND r.seccion_id = ? AND r.examen_id = ?";
    $stmtEstudiantes = $conn->prepare($sqlEstudiantes);
    $stmtEstudiantes->bind_param("iii", $gradoId, $seccionId, $examenId);
    $stmtEstudiantes->execute();
    $estudiantesResult = $stmtEstudiantes->get_result();
    while ($estudiante = $estudiantesResult->fetch_assoc()) {
        $estudiantes[] = $estudiante;
    }

    // Obtener las preguntas del examen seleccionado
    $sqlPreguntas = "SELECT * FROM preguntas WHERE examen_id = ? ORDER BY numero";  // Ordenar por número de pregunta
    $stmtPreguntas = $conn->prepare($sqlPreguntas);
    $stmtPreguntas->bind_param("i", $examenId);
    $stmtPreguntas->execute();
    $preguntasResult = $stmtPreguntas->get_result();
    while ($pregunta = $preguntasResult->fetch_assoc()) {
        $preguntas[] = $pregunta;
    }

    // Obtener las respuestas de los estudiantes para cada pregunta
    $sqlRespuestas = "SELECT r.resultado_id, r.pregunta_id, r.respuesta_estudiante, r.correcta
                      FROM respuestas r
                      WHERE r.resultado_id IN (SELECT id FROM resultados WHERE examen_id = ?)";
    $stmtRespuestas = $conn->prepare($sqlRespuestas);
    $stmtRespuestas->bind_param("i", $examenId);
    $stmtRespuestas->execute();
    $respuestasResult = $stmtRespuestas->get_result();
    while ($respuesta = $respuestasResult->fetch_assoc()) {
        $respuestasEstudiantes[$respuesta['resultado_id']][$respuesta['pregunta_id']] = $respuesta;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuadro de Doble Entrada</title>
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
            max-width: 1000px;
            margin-top: 50px;
        }
        .table td, .table th {
            text-align: center;
            vertical-align: middle;
        }
        .correct {
            color: green;
        }
        .incorrect {
            color: red;
        }
        .text-muted {
            color: #6c757d !important;
        }
        .puntaje {
            font-weight: bold;
            color: #007bff;
        }
    </style>
</head>
<body>
<div class="container">
    <h2 class="text-center mb-4">Cuadro de Doble Entrada de Resultados</h2>

    <!-- Formulario para filtrar por examen, grado y sección -->
    <form method="POST" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <label for="examen" class="form-label">Examen</label>
                <select name="examen" id="examen" class="form-select" required>
                    <option value="">Seleccionar Examen</option>
                    <?php while ($examen = $examenes->fetch_assoc()): ?>
                        <option value="<?php echo $examen['id']; ?>" <?php echo ($examen['id'] == $examenId) ? 'selected' : ''; ?>>
                            <?php echo $examen['nombre_examen']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="grado" class="form-label">Grado</label>
                <select name="grado" id="grado" class="form-select" required>
                    <option value="">Seleccionar Grado</option>
                    <?php while ($grado = $grados->fetch_assoc()): ?>
                        <option value="<?php echo $grado['id']; ?>" <?php echo ($grado['id'] == $gradoId) ? 'selected' : ''; ?>>
                            <?php echo $grado['nombre_grado']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="seccion" class="form-label">Sección</label>
                <select name="seccion" id="seccion" class="form-select" required>
                    <option value="">Seleccionar Sección</option>
                    <?php while ($seccion = $secciones->fetch_assoc()): ?>
                        <option value="<?php echo $seccion['id']; ?>" <?php echo ($seccion['id'] == $seccionId) ? 'selected' : ''; ?>>
                            <?php echo $seccion['nombre_seccion']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
        <div class="text-center mt-3">
            <button type="submit" class="btn btn-primary">Ver Resultados</button>
            <a href="panel_administrador.php" class="btn btn-secondary">Regresar</a>
        </div>
    </form>

    <?php if (!empty($estudiantes) && !empty($preguntas)): ?>
        <h3 class="text-center mb-4">Resultados por Estudiante</h3>
        <table class="table table-bordered table-hover">
            <thead class="table-primary">
                <tr>
                    <th>Estudiante</th>
                    <?php foreach ($preguntas as $pregunta): ?>
                        <!-- Mostrar solo el número de la pregunta -->
                        <th><?php echo $pregunta['numero']; ?></th>
                    <?php endforeach; ?>
                    <th>Puntaje</th> <!-- Columna de puntaje -->
                </tr>
            </thead>
            <tbody>
                <?php foreach ($estudiantes as $estudiante): ?>
                    <tr>
                        <td><?php echo $estudiante['apellidos'] . ', ' . $estudiante['nombres']; ?></td>
                        <?php 
                        $puntaje = 0; // Variable para calcular el puntaje
                        foreach ($preguntas as $pregunta): 
                            $respuesta = isset($respuestasEstudiantes[$estudiante['resultado_id']][$pregunta['id']]) ? $respuestasEstudiantes[$estudiante['resultado_id']][$pregunta['id']] : null;
                        ?>
                            <td>
                                <?php
                                // Verificar si el estudiante ha respondido esta pregunta
                                if ($respuesta) {
                                    // Si la respuesta es correcta
                                    if ($respuesta['respuesta_estudiante'] == $pregunta['respuesta_correcta']) {
                                        echo '<span class="correct">&#10004;</span>'; // Check verde
                                        $puntaje++; // Aumentar puntaje
                                    } else {
                                        echo '<span class="incorrect">X</span>'; // X roja
                                    }
                                } else {
                                    echo '<span class="text-muted">V</span>'; // V vacío
                                }
                                ?>
                            </td>
                        <?php endforeach; ?>
                        <td class="puntaje"><?php echo $puntaje; ?> / <?php echo count($preguntas); ?></td> <!-- Mostrar puntaje -->
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="mt-4 text-center">
            <p><strong>✅</strong> Correcta | <strong>❌</strong> Incorrecta | <strong>V</strong> Vacío</p>
        </div>
        <div class="text-center">
                        <br><a href="panel_administrador.php" class="btn btn-warning">Volver al Panel</a>
        </div>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
