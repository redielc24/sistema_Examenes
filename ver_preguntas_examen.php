<?php
session_start();
include('conexion.php');

// Verificar si el usuario es administrador
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'administrador') {
    header("Location: login_administrador.php");
    exit();
}

$examen_id = isset($_GET['examen_id']) ? (int)$_GET['examen_id'] : 0;

if ($examen_id == 0) {
    echo "ID de examen no válido.";
    exit();
}

// Consultar las preguntas del examen
$sql = "SELECT p.id, p.numero, p.respuesta_correcta FROM preguntas p WHERE p.examen_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $examen_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preguntas del Examen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            color: #495057;
        }
        .container {
            max-width: 900px;
            margin-top: 50px;
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #007bff;
            margin-bottom: 30px;
        }
        table th, table td {
            text-align: center;
        }
        .btn-regresar {
            margin-bottom: 20px;
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-regresar:hover {
            background-color: #0056b3;
            border-color: #004085;
        }

        table td {
            background-color: #f1f1f1;
        }
        table tbody tr:nth-child(even) {
            background-color: #e9ecef;
        }
        /* Cambiar el color de la fila al pasar el cursor */
        table tbody tr:hover {
            background-color: #d1ecf1; /* Un color suave de fondo al pasar el cursor */
            cursor: pointer; /* Cambiar el cursor para que el usuario sepa que la fila es interactiva */
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center mb-4">Preguntas del Examen</h2>
        <a href="gestionar_examenes.php" class="btn btn-warning">
            <i class="bi bi-arrow-left-circle"></i> Regresar a la gestión de exámenes
        </a>

        <?php if ($result->num_rows > 0): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Pregunta N°</th>
                        <th>Respuesta Correcta</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['numero']; ?></td>
                            <td><?php echo $row['respuesta_correcta']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="alert alert-warning text-center">No hay preguntas para este examen.</p>
        <?php endif; ?>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
