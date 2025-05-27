<?php
session_start();
include('conexion.php');

// Validar parámetros recibidos por POST
if (!isset($_POST['examen_id'], $_POST['nombres'], $_POST['apellidos'], $_POST['grado'], $_POST['seccion'])) {
    die('Faltan datos necesarios.');
}

$examen_id = filter_var($_POST['examen_id'], FILTER_VALIDATE_INT);
$nombres = htmlspecialchars(trim($_POST['nombres']));
$apellidos = htmlspecialchars(trim($_POST['apellidos']));
$grado = htmlspecialchars(trim($_POST['grado']));
$seccion = htmlspecialchars(trim($_POST['seccion']));

if (!$examen_id) {
    die('ID de examen inválido.');
}

// Consulta para obtener las preguntas del examen seleccionado (orden aleatorio)
$sql = "SELECT * FROM preguntas WHERE examen_id = ? ORDER BY RAND()";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $examen_id);
$stmt->execute();
$result = $stmt->get_result();

// Obtener detalles del examen
$sqlExamen = "SELECT nombre_examen, duracion FROM examenes WHERE id = ?";
$stmtExamen = $conn->prepare($sqlExamen);
$stmtExamen->bind_param("i", $examen_id);
$stmtExamen->execute();
$resultExamen = $stmtExamen->get_result();
$rowExamen = $resultExamen->fetch_assoc();

if (!$rowExamen) {
    die('Examen no encontrado.');
}

$nombreExamen = htmlspecialchars($rowExamen['nombre_examen']);
$duracion = $rowExamen['duracion'] * 60; // Convertir duración a segundos
$isIlimitado = ($duracion == 0); // Examen sin tiempo limitado
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Examen - <?php echo $nombreExamen; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            margin-top: 50px;
        }
        .question-container {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        .pregunta-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #E0375A;
        }
        .options {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .option-btn {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 15px;
            background-color: #FFDD43;
            border: 1px solid #ced4da;
            border-radius: 12px;
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
            transition: all 0.3s ease-in-out;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            width: 48%;
        }
        .option-btn:hover {
            background-color: #d4edda;
            transform: scale(1.05);
        }
        .option-btn.selected {
            background-color: #007bff;
            color: white;
            box-shadow: 0px 6px 10px rgba(0, 0, 0, 0.2);
        }
        .submit-btn {
            margin-top: 20px;
            padding: 15px;
            width: 100%;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
        }
        .submit-btn:hover {
            background-color: #218838;
        }
        .timer {
            position: fixed;
            top: 10px;
            right: 10px;
            background-color: #8F2285;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 18px;
            font-weight: bold;
            z-index: 1000;
        }
    </style>
</head>
<body>

    <?php if (!$isIlimitado): ?>
        <div class="timer" id="timer">Cargando...</div>
    <?php endif; ?>

    <div class="container">
        <h2 class="text-center"><?php echo $nombreExamen; ?></h2>
        <p class="text-center text-muted">
            <strong>Estudiante:</strong> <?php echo htmlspecialchars($nombres . ' ' . $apellidos); ?><br>
            <strong>Grado y Sección:</strong> <?php echo htmlspecialchars($grado . ' - ' . $seccion); ?>
        </p>

        <form action="guardar_resultado.php" method="POST" id="examForm">
            <input type="hidden" name="nombres" value="<?php echo htmlspecialchars($nombres); ?>">
            <input type="hidden" name="apellidos" value="<?php echo htmlspecialchars($apellidos); ?>">
            <input type="hidden" name="grado" value="<?php echo htmlspecialchars($grado); ?>">
            <input type="hidden" name="seccion" value="<?php echo htmlspecialchars($seccion); ?>">
            <input type="hidden" name="examen_id" value="<?php echo htmlspecialchars($examen_id); ?>">

            <?php if ($result->num_rows > 0): ?>
                <?php $numero_pregunta = 1; ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="question-container">
                        <h5 class="pregunta-title"><?php echo htmlspecialchars($row['pregunta']); ?></h5>
                        <div class="options">
                            <?php
                            // Crear un array con las opciones y luego aleatorizarlas
                            $opciones = [
                                'A' => $row['opcion_a'],
                                'B' => $row['opcion_b'],
                                'C' => $row['opcion_c'],
                                'D' => $row['opcion_d']
                            ];
                            // Aleatorizar las opciones
                            $keys = array_keys($opciones);
                            shuffle($keys); // Reordenar las claves (A, B, C, D)
                            foreach ($keys as $key): ?>
                                <button type="button" class="option-btn" onclick="selectOption(this, '<?php echo $key; ?>')">
                                    <?php echo htmlspecialchars($opciones[$key]); ?>
                                </button>
                            <?php endforeach; ?>
                            <input type="hidden" name="respuesta_<?php echo $row['id']; ?>" class="respuesta">
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No hay preguntas disponibles para este examen.</p>
            <?php endif; ?>

            <button type="submit" class="submit-btn">Enviar Examen</button>
        </form>
    </div>

    <script>
        let isIlimitado = <?php echo $isIlimitado ? 'true' : 'false'; ?>;
        let timeLeft = <?php echo $isIlimitado ? 0 : $duracion; ?>;
        const timerElement = document.getElementById('timer');

        if (!isIlimitado) {
            const updateTimer = () => {
                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    Swal.fire({
                        icon: 'warning',
                        title: '¡Tiempo terminado!',
                        text: 'El examen será enviado automáticamente.',
                        showConfirmButton: false,
                        timer: 6000
                    });
                    setTimeout(() => document.getElementById('examForm').submit(), 2000);
                } else {
                    const minutes = Math.floor(timeLeft / 60);
                    const seconds = timeLeft % 60;
                    timerElement.textContent = `Tiempo restante: ${minutes}m ${seconds}s`;
                    timeLeft--;
                }
            };
            const timerInterval = setInterval(updateTimer, 1000);
            updateTimer();
        }

        function selectOption(button, answer) {
            const parent = button.closest('.question-container');
            const buttons = parent.querySelectorAll('.option-btn');
            buttons.forEach(btn => btn.classList.remove('selected'));
            button.classList.add('selected');
            parent.querySelector('.respuesta').value = answer;
        }
    </script>

</body>
</html>
