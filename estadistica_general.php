<?php
session_start();
include('conexion.php');

// Verificar si el usuario es administrador
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'administrador') {
    header("Location: login_administrador.php");
    exit();
}

// Obtener estadísticas básicas
$sqlTotalExamenes = "SELECT COUNT(*) AS total FROM resultados";
$totalExamenes = $conn->query($sqlTotalExamenes)->fetch_assoc()['total'];

$sqlPromedioPuntajes = "SELECT e.nombre_examen, AVG(r.puntaje) AS promedio 
                        FROM resultados r 
                        JOIN examenes e ON r.examen_id = e.id 
                        GROUP BY r.examen_id";
$promediosPuntajes = $conn->query($sqlPromedioPuntajes);

$sqlGradoMasExamenes = "SELECT g.nombre_grado, COUNT(*) AS cantidad 
                        FROM resultados r 
                        JOIN grados g ON r.grado_id = g.id 
                        GROUP BY r.grado_id 
                        ORDER BY cantidad DESC 
                        LIMIT 1";
$gradoMasExamenes = $conn->query($sqlGradoMasExamenes)->fetch_assoc();

$sqlMejoresEstudiantes = "SELECT r.nombres, r.apellidos, MAX(r.puntaje) AS puntaje, e.nombre_examen 
                          FROM resultados r 
                          JOIN examenes e ON r.examen_id = e.id 
                          GROUP BY r.nombres, r.apellidos, r.examen_id 
                          ORDER BY puntaje DESC 
                          LIMIT 5";
$mejoresEstudiantes = $conn->query($sqlMejoresEstudiantes);

$sqlDistribucionPorGrado = "SELECT g.nombre_grado, COUNT(*) AS cantidad 
                            FROM resultados r 
                            JOIN grados g ON r.grado_id = g.id 
                            GROUP BY r.grado_id";
$distribucionPorGrado = $conn->query($sqlDistribucionPorGrado);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrador - Sistema de Exámenes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            padding: 30px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1 class="text-center mb-4">Panel de Administración</h1>

    <!-- Estadísticas Resumidas -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Total de Exámenes Realizados</h5>
                    <p class="card-text fs-2"><?php echo $totalExamenes; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Grado con Más Exámenes</h5>
                    <p class="card-text fs-4">
                        <?php echo $gradoMasExamenes['nombre_grado'] ?? 'Sin datos'; ?> 
                        (<?php echo $gradoMasExamenes['cantidad'] ?? '0'; ?>)
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Promedio de Puntajes</h5>
                    <p class="card-text fs-4">
                        <?php 
                        $promedioGeneral = 0;
                        if ($promediosPuntajes->num_rows > 0) {
                            foreach ($promediosPuntajes as $promedio) {
                                $promedioGeneral += $promedio['promedio'];
                            }
                            $promedioGeneral = round($promedioGeneral / $promediosPuntajes->num_rows, 2);
                        }
                        echo $promedioGeneral;
                        ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Mejores Estudiantes -->
    <div class="mb-4">
        <h3 class="text-center">Top 5 Estudiantes con Mejor Puntaje</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Examen</th>
                    <th>Puntaje</th>
                </tr>
            </thead>
            <tbody>
                <?php $contador = 1; ?>
                <?php while ($estudiante = $mejoresEstudiantes->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $contador++; ?></td>
                        <td><?php echo $estudiante['nombres'] . ' ' . $estudiante['apellidos']; ?></td>
                        <td><?php echo $estudiante['nombre_examen']; ?></td>
                        <td><?php echo $estudiante['puntaje']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <div class="text-center">
                        <br><a href="panel_administrador.php" class="btn btn-warning">Volver al Panel</a>
        </div>
    </div>

    <!-- Gráfico de Distribución por Grado -->
    <div class="mb-4">
        <h3 class="text-center">Distribución de Exámenes por Grado</h3>
        <canvas id="graficoDistribucion"></canvas>
    </div>
</div>

<script>
    // Datos para el gráfico de distribución
    const ctx = document.getElementById('graficoDistribucion').getContext('2d');
    const labels = <?php 
        $labels = [];
        $data = [];
        while ($fila = $distribucionPorGrado->fetch_assoc()) {
            $labels[] = $fila['nombre_grado'];
            $data[] = $fila['cantidad'];
        }
        echo json_encode($labels); 
    ?>;
    const data = <?php echo json_encode($data); ?>;

    // Configuración del gráfico
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Cantidad de Exámenes',
                data: data,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
</body>
</html>

<?php
$conn->close();
?>
