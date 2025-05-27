<?php
session_start();
include('conexion.php');

// Verificar si el usuario es administrador
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'administrador') {
    header("Location: login_administrador.php");
    exit();
}

// Consultar opciones para los filtros
$sqlExamenes = "SELECT id, nombre_examen FROM examenes";
$examenes = $conn->query($sqlExamenes);

$sqlGrados = "SELECT id, nombre_grado FROM grados";
$grados = $conn->query($sqlGrados);

$sqlSecciones = "SELECT id, nombre_seccion FROM secciones";
$secciones = $conn->query($sqlSecciones);

// Variables para los filtros
$examen_id = isset($_GET['examen_id']) ? $_GET['examen_id'] : '';
$grado_id = isset($_GET['grado_id']) ? $_GET['grado_id'] : '';
$seccion_id = isset($_GET['seccion_id']) ? $_GET['seccion_id'] : '';

// Construir consulta de estadísticas
$sqlEstadisticas = "
    SELECT 
        preguntas.numero AS numero_pregunta,
        SUM(CASE WHEN respuestas.correcta = 1 THEN 1 ELSE 0 END) AS correctas,
        SUM(CASE WHEN respuestas.correcta = 0 THEN 1 ELSE 0 END) AS incorrectas
    FROM preguntas
    INNER JOIN respuestas ON preguntas.id = respuestas.pregunta_id
    INNER JOIN resultados ON respuestas.resultado_id = resultados.id
    WHERE 1=1";

// Agregar filtros
if (!empty($examen_id)) {
    $sqlEstadisticas .= " AND preguntas.examen_id = $examen_id";
}
if (!empty($grado_id)) {
    $sqlEstadisticas .= " AND resultados.grado_id = $grado_id";
}
if (!empty($seccion_id)) {
    $sqlEstadisticas .= " AND resultados.seccion_id = $seccion_id";
}

$sqlEstadisticas .= " GROUP BY preguntas.numero ORDER BY preguntas.numero ASC";
$estadisticas = $conn->query($sqlEstadisticas);

// Preparar datos para Chart.js
$numeros = [];
$correctas = [];
$incorrectas = [];

while ($row = $estadisticas->fetch_assoc()) {
    $numeros[] = $row['numero_pregunta'];
    $correctas[] = $row['correctas'];
    $incorrectas[] = $row['incorrectas'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas - Sistema de Exámenes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center">Estadísticas de Respuestas</h2>
    <form method="GET" class="row g-3 my-4">
        <div class="col-md-4">
            <label for="examen_id" class="form-label">Examen</label>
            <select class="form-select" id="examen_id" name="examen_id">
                <option value="">Todos</option>
                <?php while ($row = $examenes->fetch_assoc()): ?>
                    <option value="<?php echo $row['id']; ?>" <?php echo ($examen_id == $row['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($row['nombre_examen']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label for="grado_id" class="form-label">Grado</label>
            <select class="form-select" id="grado_id" name="grado_id">
                <option value="">Todos</option>
                <?php while ($row = $grados->fetch_assoc()): ?>
                    <option value="<?php echo $row['id']; ?>" <?php echo ($grado_id == $row['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($row['nombre_grado']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label for="seccion_id" class="form-label">Sección</label>
            <select class="form-select" id="seccion_id" name="seccion_id">
                <option value="">Todos</option>
                <?php while ($row = $secciones->fetch_assoc()): ?>
                    <option value="<?php echo $row['id']; ?>" <?php echo ($seccion_id == $row['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($row['nombre_seccion']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary">Filtrar</button>
            <a href="panel_administrador.php" class="btn btn-warning">Regresar al Panel</a>
        </div>
    </form>

    <canvas id="graficoBarras" style="max-height: 400px;"></canvas>
</div>

<script>
    // Datos para el gráfico
    const numeros = <?php echo json_encode($numeros); ?>;
    const correctas = <?php echo json_encode($correctas); ?>;
    const incorrectas = <?php echo json_encode($incorrectas); ?>;

    // Crear el gráfico
    const ctx = document.getElementById('graficoBarras').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: numeros,
            datasets: [
                {
                    label: 'Respuestas Correctas',
                    data: correctas,
                    backgroundColor: 'rgba(75, 192, 192, 0.7)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Respuestas Incorrectas',
                    data: incorrectas,
                    backgroundColor: 'rgba(255, 99, 132, 0.7)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Número de Pregunta'
                    }
                },
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Cantidad de Estudiantes'
                    }
                }
            }
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
