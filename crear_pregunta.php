<?php
session_start();
include('conexion.php');

// Verificar si el usuario es administrador
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'administrador') {
    header("Location: login_administrador.php");
    exit();
}

// Consultar todos los exámenes activos para permitir al usuario seleccionar uno
$sqlExamenes = "SELECT id, nombre_examen FROM examenes WHERE activo = '1'";
$stmtExamenes = $conn->prepare($sqlExamenes);
$stmtExamenes->execute();
$resultExamenes = $stmtExamenes->get_result();

// Verificar si el formulario ha sido enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pregunta = $_POST['pregunta'];
    $opcion_a = $_POST['opcion_a'];
    $opcion_b = $_POST['opcion_b'];
    $opcion_c = $_POST['opcion_c'];
    $opcion_d = $_POST['opcion_d'];
    $respuesta_correcta = $_POST['respuesta_correcta'];
    $examen_id = $_POST['examen_id'];
    $numero_pregunta = $_POST['numero_pregunta'] + 1; // Número de la pregunta basado en el total de preguntas previas

    // Validar campos vacíos y asignar valores predeterminados
    $pregunta = empty(trim($pregunta)) ? "Pregunta " . ($numero_pregunta + 1) : $pregunta;
    $opcion_a = empty(trim($opcion_a)) ? "A" : $opcion_a;
    $opcion_b = empty(trim($opcion_b)) ? "B" : $opcion_b;
    $opcion_c = empty(trim($opcion_c)) ? "C" : $opcion_c;
    $opcion_d = empty(trim($opcion_d)) ? "D" : $opcion_d;

    // Insertar la pregunta en la base de datos
    $sqlInsertarPregunta = "INSERT INTO preguntas (examen_id, numero, pregunta, opcion_a, opcion_b, opcion_c, opcion_d, respuesta_correcta) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmtPregunta = $conn->prepare($sqlInsertarPregunta);
    $stmtPregunta->bind_param("iissssss", $examen_id, $numero_pregunta, $pregunta, $opcion_a, $opcion_b, $opcion_c, $opcion_d, $respuesta_correcta);

    if ($stmtPregunta->execute()) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: 'Pregunta agregada con éxito.'
            });
        </script>";
    } else {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo agregar la pregunta. Inténtalo de nuevo.'
            });
        </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Pregunta - Sistema de Exámenes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-color: #f7f9fc; /* Fondo suave */
        }
        .custom-container {
            background-color: #ffffff; /* Color suave del contenedor */
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        .circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            background-color: #007bff;
            color: white;
            font-weight: bold;
            cursor: pointer;
            margin-right: 10px;
            transition: background-color 0.3s;
        }
        .circle.selected {
            background-color: red;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="custom-container mx-auto" style="max-width: 600px;">
        <h2 class="text-center mb-4">Crear Nueva Pregunta</h2>
        <form id="crearPreguntaForm" method="POST">
            <div class="mb-3">
                <label for="examen_id" class="form-label">Examen</label>
                <select class="form-select" id="examen_id" name="examen_id" onchange="actualizarCantidadPreguntas()" required>
                    <option value="">Seleccione un examen</option>
                    <?php while ($rowExamen = $resultExamenes->fetch_assoc()): ?>
                        <option value="<?php echo $rowExamen['id']; ?>"><?php echo htmlspecialchars($rowExamen['nombre_examen']); ?></option>
                    <?php endwhile; ?>
                </select>
                <small class="form-text text-muted">Cantidad de preguntas: <span id="cantidad_preguntas">0</span></small>
                <input type="hidden" id="numero_pregunta" name="numero_pregunta" value="0">
            </div>
            <div class="mb-3">
                <label for="pregunta" class="form-label">Pregunta</label>
                <textarea class="form-control" id="pregunta" name="pregunta" rows="3"></textarea>
            </div>
            <div class="mb-3">
                <label for="opcion_a" class="form-label">Opción A</label>
                <input type="text" class="form-control" id="opcion_a" name="opcion_a">
            </div>
            <div class="mb-3">
                <label for="opcion_b" class="form-label">Opción B</label>
                <input type="text" class="form-control" id="opcion_b" name="opcion_b">
            </div>
            <div class="mb-3">
                <label for="opcion_c" class="form-label">Opción C</label>
                <input type="text" class="form-control" id="opcion_c" name="opcion_c">
            </div>
            <div class="mb-3">
                <label for="opcion_d" class="form-label">Opción D</label>
                <input type="text" class="form-control" id="opcion_d" name="opcion_d">
            </div>
            <div class="mb-3">
                <label class="form-label">Respuesta Correcta</label>
                <div>
                    <div class="circle" onclick="selectAnswer(this, 'A')">A</div>
                    <div class="circle" onclick="selectAnswer(this, 'B')">B</div>
                    <div class="circle" onclick="selectAnswer(this, 'C')">C</div>
                    <div class="circle" onclick="selectAnswer(this, 'D')">D</div>
                    <input type="hidden" id="respuesta_correcta" name="respuesta_correcta" required>
                </div>
            </div>
            <div class="text-center">
                <button type="submit" class="btn btn-primary">Crear Pregunta</button>
                <a href="panel_administrador.php" class="btn btn-warning">Regresar al Panel</a>
            </div>

                    </form>
    </div>
</div>

<script>
    function selectAnswer(circle, answer) {
        const circles = document.querySelectorAll('.circle');
        circles.forEach(c => c.classList.remove('selected'));
        circle.classList.add('selected');
        document.getElementById('respuesta_correcta').value = answer;
    }

    function actualizarCantidadPreguntas() {
        const examenId = document.getElementById('examen_id').value;
        if (examenId) {
            fetch(`obtener_cantidad_preguntas.php?examen_id=${examenId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('cantidad_preguntas').innerText = data.total;
                    document.getElementById('numero_pregunta').value = data.total;
                })
                .catch(error => console.error('Error:', error));
        } else {
            document.getElementById('cantidad_preguntas').innerText = 0;
            document.getElementById('numero_pregunta').value = 0;
        }
    }

    document.getElementById('crearPreguntaForm').addEventListener('submit', function(event) {
        if (!document.getElementById('respuesta_correcta').value) {
            event.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Debe seleccionar una respuesta correcta.'
            });
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
