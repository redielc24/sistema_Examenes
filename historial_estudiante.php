<?php
session_start();
include('conexion.php');

// Verificar si el usuario es administrador
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'administrador') {
    header("Location: login_administrador.php");
    exit();
}

// Variables para almacenar datos
$nombreEstudiante = "";
$resultados = [];

// Si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombreEstudiante = trim($_POST['nombre_estudiante']);

    // Consulta para obtener el historial del estudiante
    $sqlHistorial = "SELECT r.id, e.nombre_examen, g.nombre_grado, s.nombre_seccion, r.puntaje, r.fecha_realizacion,
                            CONCAT(r.nombres, ' ', r.apellidos) AS nombre_completo
                     FROM resultados r
                     JOIN examenes e ON r.examen_id = e.id
                     JOIN grados g ON r.grado_id = g.id
                     JOIN secciones s ON r.seccion_id = s.id
                     WHERE CONCAT(r.nombres, ' ', r.apellidos) LIKE ?
                     ORDER BY r.fecha_realizacion DESC";

    $stmtHistorial = $conn->prepare($sqlHistorial);
    $searchTerm = "%$nombreEstudiante%";
    $stmtHistorial->bind_param("s", $searchTerm);
    $stmtHistorial->execute();
    $resultados = $stmtHistorial->get_result();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial del Estudiante</title>
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
            max-width: 900px;
            margin-top: 50px;
        }
        .highlight {
            background-color: #ffeeba !important;
        }
    </style>
</head>
<body>
<div class="container">
    <h2 class="text-center mb-4">Historial del Estudiante</h2>

    <!-- Formulario para buscar estudiante -->
    <form method="POST" class="mb-4">
        <div class="mb-3">
            <label for="nombre_estudiante" class="form-label">Nombre del Estudiante</label>
            <input type="text" class="form-control" id="nombre_estudiante" name="nombre_estudiante"
                   value="<?php echo htmlspecialchars($nombreEstudiante); ?>" required>
        </div>
        <div class="text-center">
            <button type="submit" class="btn btn-primary">Buscar</button>
            <a href="panel_administrador.php" class="btn btn-secondary">Regresar</a>
        </div>
    </form>

    <?php if ($resultados && $resultados->num_rows > 0): ?>
        <h3 class="text-center mb-4">Historial de Exámenes</h3>
        <table class="table table-bordered table-hover">
            <thead class="table-primary">
                <tr>
                    <th>#</th>
                    <th>Estudiante</th> <!-- Nueva columna para mostrar el nombre completo -->
                    <th>Examen</th>
                    <th>Grado</th>
                    <th>Sección</th>
                    <th>Puntaje</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $contador = 0;
                $mejorPuntaje = -1;
                while ($row = $resultados->fetch_assoc()):
                    $contador++;
                    $esMejorPuntaje = $row['puntaje'] > $mejorPuntaje;
                    if ($esMejorPuntaje) {
                        $mejorPuntaje = $row['puntaje'];
                    }
                ?>
                    <tr class="<?php echo $esMejorPuntaje ? 'highlight' : ''; ?>">
                        <td><?php echo $contador; ?></td>
                        <td><?php echo htmlspecialchars($row['nombre_completo']); ?></td> <!-- Mostrar nombre completo -->
                        <td><?php echo htmlspecialchars($row['nombre_examen']); ?></td>
                        <td><?php echo htmlspecialchars($row['nombre_grado']); ?></td>
                        <td><?php echo htmlspecialchars($row['nombre_seccion']); ?></td>
                        <td><?php echo htmlspecialchars($row['puntaje']); ?></td>
                        <td><?php echo htmlspecialchars($row['fecha_realizacion']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
            
        </table>

    <?php elseif ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
        <p class="text-center text-danger">No se encontraron resultados para "<?php echo htmlspecialchars($nombreEstudiante); ?>"</p>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
