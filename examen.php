<?php
session_start();
include('conexion.php');

// Validar y sanitizar datos recibidos
$nombres = filter_input(INPUT_POST, 'nombres', FILTER_SANITIZE_STRING);
$apellidos = filter_input(INPUT_POST, 'apellidos', FILTER_SANITIZE_STRING);
$grado = filter_input(INPUT_POST, 'grado', FILTER_SANITIZE_STRING);
$seccion = filter_input(INPUT_POST, 'seccion', FILTER_SANITIZE_STRING);
$examen_id = filter_input(INPUT_POST, 'examen_id', FILTER_VALIDATE_INT);

if (!$examen_id) {
    die('ID de examen inválido.');
}

// Consulta para obtener las preguntas del examen seleccionado
$sql = "SELECT * FROM preguntas WHERE examen_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $examen_id);
$stmt->execute();
$result = $stmt->get_result();

// Obtener los detalles del examen
$sqlExamen = "SELECT nombre_examen, archivo_pdf, duracion FROM examenes WHERE id = ?";
$stmtExamen = $conn->prepare($sqlExamen);
$stmtExamen->bind_param("i", $examen_id);
$stmtExamen->execute();
$resultExamen = $stmtExamen->get_result();
$rowExamen = $resultExamen->fetch_assoc();

// Validar datos del examen
if (!$rowExamen) {
    die('Examen no encontrado.');
}

$nombreExamen = htmlspecialchars($rowExamen['nombre_examen']);
$pdfUrl = htmlspecialchars($rowExamen['archivo_pdf']);
$duracion = $rowExamen['duracion'] * 60; // Convertir duración a segundos
$isIlimitado = ($duracion == 0); // Comprobar si es un examen con tiempo ilimitado
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Examen - <?php echo $nombreExamen; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            margin: 0;
            font-family: 'Arial', sans-serif;
            height: 100vh;
            background-color: #f8f9fa;
            display: flex;
            flex-direction: row; /* Horizontal */
        }
        .pdf-container {
            width: 75%;
            height: 100vh;
            background-color: #fff;
            border-right: 2px solid #ddd;
        }
        .pdf-container iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        .questions-container {
            width: 25%;
            height: 100vh;
            overflow-y: auto;
            padding: 20px;
            background-color: white;
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
        .question-container {
            background-color: white;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        .options {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            justify-content: center;
            margin-top: 1px;
        }
        .option-btn {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 50px;
            height: 50px;
            text-align: center;
            background-color: #FFDD43;
            border: 1px solid #ced4da;
            border-radius: 12px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: all 0.3s ease-in-out;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
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
        @media (max-width: 768px) {
            .pdf-container, .questions-container {
                width: 100%;
                height: 50%;
            }
            body {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php if (!$isIlimitado): ?>
        <div class="timer" id="timer">Cargando...</div>
    <?php endif; ?>

    <div class="pdf-container">
        <iframe src="<?php echo $pdfUrl; ?>#view=fitH"></iframe>
    </div>

    <div class="questions-container">
        <h2 class="text-center mb-4 exam-title"><?php echo $nombreExamen; ?></h2>
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
                        <h5 class="pregunta-title">Pregunta <?php echo $numero_pregunta++; ?></h5>
                        <div class="options">
                            <?php foreach (['A', 'B', 'C', 'D'] as $opcion): ?>
                                <button type="button" class="option-btn" onclick="selectOption(this, '<?php echo $opcion; ?>')">
                                    <?php echo htmlspecialchars($row['opcion_' . strtolower($opcion)]); ?>
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
