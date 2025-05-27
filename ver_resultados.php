<?php
session_start();
include('conexion.php');

// Verificar si el usuario es administrador
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'administrador') {
    header("Location: login_administrador.php");
    exit();
}

// Obtener los exámenes, grados y secciones para los filtros
$sqlExamenes = "SELECT id, nombre_examen FROM examenes";
$resultExamenes = $conn->query($sqlExamenes);

$sqlGrados = "SELECT id, nombre_grado FROM grados";
$resultGrados = $conn->query($sqlGrados);

$sqlSecciones = "SELECT id, nombre_seccion FROM secciones";
$resultSecciones = $conn->query($sqlSecciones);

// Obtener los resultados según los filtros
$examenSeleccionado = $gradoSeleccionado = $seccionSeleccionada = '';
$resultados = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener los valores seleccionados
    $examenSeleccionado = $_POST['examen'];
    $gradoSeleccionado = $_POST['grado'];
    $seccionSeleccionada = $_POST['seccion'];

    // Consulta para obtener los resultados filtrados
    $sqlResultados = "SELECT r.id, r.puntaje, r.fecha_realizacion AS fecha, 
                              r.nombres AS nombre_estudiante, 
                              e.nombre_examen AS nombre_examen, 
                              g.nombre_grado AS grado, 
                              s.nombre_seccion AS seccion
                       FROM resultados r
                       JOIN examenes e ON r.examen_id = e.id
                       JOIN grados g ON r.grado_id = g.id
                       JOIN secciones s ON r.seccion_id = s.id
                       WHERE r.examen_id = ? AND r.grado_id = ? AND r.seccion_id = ?
                       ORDER BY r.fecha_realizacion DESC";

    $stmtResultados = $conn->prepare($sqlResultados);
    $stmtResultados->bind_param("iii", $examenSeleccionado, $gradoSeleccionado, $seccionSeleccionada);
    $stmtResultados->execute();
    $resultados = $stmtResultados->get_result();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Resultados - Sistema de Exámenes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin-top: 50px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="text-center mb-4">Ver Resultados de Exámenes</h2>

    <!-- Formulario para seleccionar examen, grado y sección -->
    <form method="POST">
        <div class="mb-3">
            <label for="examen" class="form-label">Seleccionar Examen</label>
            <select class="form-select" id="examen" name="examen" required>
                <option value="">Seleccione un examen</option>
                <?php while ($row = $resultExamenes->fetch_assoc()): ?>
                    <option value="<?php echo $row['id']; ?>" <?php echo ($row['id'] == $examenSeleccionado) ? 'selected' : ''; ?>>
                        <?php echo $row['nombre_examen']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <div class="mb-3">
            <label for="grado" class="form-label">Seleccionar Grado</label>
            <select class="form-select" id="grado" name="grado" required>
                <option value="">Seleccione un grado</option>
                <?php while ($row = $resultGrados->fetch_assoc()): ?>
                    <option value="<?php echo $row['id']; ?>" <?php echo ($row['id'] == $gradoSeleccionado) ? 'selected' : ''; ?>>
                        <?php echo $row['nombre_grado']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="seccion" class="form-label">Seleccionar Sección</label>
            <select class="form-select" id="seccion" name="seccion" required>
                <option value="">Seleccione una sección</option>
                <?php while ($row = $resultSecciones->fetch_assoc()): ?>
                    <option value="<?php echo $row['id']; ?>" <?php echo ($row['id'] == $seccionSeleccionada) ? 'selected' : ''; ?>>
                        <?php echo $row['nombre_seccion']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3 text-center">
            <button type="submit" class="btn btn-primary">Ver Resultados</button>
        </div>
    </form>

    <?php if (!empty($resultados)): ?>
        <h3 class="text-center mb-4">Resultados del Examen</h3>
        <table id="tablaResultados" class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Estudiante</th>
                    <th>Examen</th>
                    <th>Grado</th>
                    <th>Sección</th>
                    <th>Puntaje</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($resultado = $resultados->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $resultado['id']; ?></td>
                        <td><?php echo $resultado['nombre_estudiante']; ?></td>
                        <td><?php echo $resultado['nombre_examen']; ?></td>
                        <td><?php echo $resultado['grado']; ?></td>
                        <td><?php echo $resultado['seccion']; ?></td>
                        <td><?php echo $resultado['puntaje']; ?></td>
                        <td><?php echo $resultado['fecha']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>

        </table>

    <?php elseif ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
        <p class="text-center text-danger">No se encontraron resultados para los filtros seleccionados.</p>
    <?php endif; ?>
    <div class="text-center">
                        <br><a href="panel_administrador.php" class="btn btn-warning">Volver al Panel</a>
        </div>
</div>

<!-- Incluir jQuery y scripts de DataTables -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        $('#tablaResultados').DataTable({
            ordering: true, // Habilita el ordenamiento
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.1/i18n/es-ES.json" // Traducción al español
            }
        });
    });
</script>
</body>
</html>

<?php
$conn->close();
?>
