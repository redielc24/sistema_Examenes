<?php
session_start();
include('conexion.php');

// Verificar si el usuario es administrador
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'administrador') {
    header("Location: login_administrador.php"); // Redirigir a la página de inicio de sesión si no tiene acceso
    exit();
}

// Procesar la eliminación de un examen
if (isset($_GET['eliminar'])) {
    $examen_id = $_GET['eliminar'];
    $conn->begin_transaction();
    try {
        $sqlRespuestas = "DELETE FROM respuestas WHERE resultado_id IN (SELECT id FROM resultados WHERE examen_id = ?)";
        $stmtRespuestas = $conn->prepare($sqlRespuestas);
        $stmtRespuestas->bind_param("i", $examen_id);
        $stmtRespuestas->execute();

        $sqlResultados = "DELETE FROM resultados WHERE examen_id = ?";
        $stmtResultados = $conn->prepare($sqlResultados);
        $stmtResultados->bind_param("i", $examen_id);
        $stmtResultados->execute();

        $sqlPreguntas = "DELETE FROM preguntas WHERE examen_id = ?";
        $stmtPreguntas = $conn->prepare($sqlPreguntas);
        $stmtPreguntas->bind_param("i", $examen_id);
        $stmtPreguntas->execute();

        $sqlExamen = "DELETE FROM examenes WHERE id = ?";
        $stmtExamen = $conn->prepare($sqlExamen);
        $stmtExamen->bind_param("i", $examen_id);
        $stmtExamen->execute();

        $conn->commit();
        header("Location: gestionar_examenes.php?eliminado=true");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error al eliminar el examen: " . $e->getMessage();
    }
}

// Procesar la activación o desactivación del examen
if (isset($_GET['activar']) || isset($_GET['desactivar'])) {
    $examen_id = isset($_GET['activar']) ? $_GET['activar'] : $_GET['desactivar'];
    $activo = isset($_GET['activar']) ? 1 : 0;

    $sql = "UPDATE examenes SET activo = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $activo, $examen_id);
    $stmt->execute();
    $stmt->close();
}

// Consulta SQL con cantidad de preguntas
$sql = "SELECT 
            e.id, 
            e.nombre_examen, 
            e.descripcion, 
            e.imagen_caratula, 
            e.archivo_pdf, 
            e.activo, 
            u.nombre AS creador, 
            e.duracion,
            (SELECT COUNT(*) FROM preguntas WHERE preguntas.examen_id = e.id) AS cantidad_preguntas
        FROM examenes e
        LEFT JOIN usuarios u ON e.creado_por = u.id";

$result = $conn->query($sql);
?>
<style>
        body {
            background-color: #f2dede; /* Color rojo bajito */
        }
        .container {
            max-width: 800px;
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

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Exámenes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h2 class="text-center mb-4">Gestionar Exámenes</h2>
        <a href="crear_examen.php" class="btn btn-primary mb-3">Crear Nuevo Examen</a>
        <a href="panel_administrador.php" class="btn btn-secondary mb-3">Regresar</a>

        <table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre del Examen</th>
            <th>Descripción</th>
            <th>Carátula</th>
            <th>PDF</th>
            <th>Estado</th> <!-- Cambiado el encabezado -->
            <th>Creador</th>
            <th>Duración</th>
            <th>Cantidad de Preguntas</th>
            <th>Ver Preguntas</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['nombre_examen']); ?></td>
                    <td><?php echo htmlspecialchars(substr($row['descripcion'], 0, 255)) . '...'; ?></td>
                    <td>
                        <?php if ($row['imagen_caratula']): ?>
                            <img src="<?php echo htmlspecialchars($row['imagen_caratula']); ?>" alt="Carátula" class="thumbnail">
                        <?php else: ?>
                            Sin imagen
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($row['archivo_pdf']): ?>
                            <a href="<?php echo htmlspecialchars($row['archivo_pdf']); ?>" target="_blank">Ver PDF</a>
                        <?php else: ?>
                            Sin PDF
                        <?php endif; ?>
                    </td>
                    <td>
                        <!-- Botones para activar/desactivar -->
                        <?php if ($row['activo']): ?>
                            <a href="?desactivar=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm">Desactivar</a>
                        <?php else: ?>
                            <a href="?activar=<?php echo $row['id']; ?>" class="btn btn-success btn-sm">Activar</a>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['creador']); ?></td>
                    <td><?php echo htmlspecialchars($row['duracion']); ?> minutos</td>
                    <td><?php echo $row['cantidad_preguntas']; ?></td>
                    <td>
                        <a href="ver_preguntas_examen.php?examen_id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm">Ver Preguntas</a>
                    </td>
                    <td>
                        <a href="editar_examen.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
                        <a href="?eliminar=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que deseas eliminar este examen?');">Eliminar</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="11" class="text-center">No se encontraron exámenes.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
