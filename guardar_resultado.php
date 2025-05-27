<?php
session_start();
include('conexion.php');

// Verificar que los datos estén presentes
if (isset($_POST['nombres'], $_POST['apellidos'], $_POST['grado'], $_POST['seccion'], $_POST['examen_id'])) {
    $nombres = $_POST['nombres'];
    $apellidos = $_POST['apellidos'];
    $grado = $_POST['grado'];
    $seccion = $_POST['seccion'];
    $examen_id = $_POST['examen_id'];
    $puntaje = 0;

    // Obtener ID de grado y sección
    $grado_id = $conn->query("SELECT id FROM grados WHERE nombre_grado = '$grado'")->fetch_assoc()['id'];
    $seccion_id = $conn->query("SELECT id FROM secciones WHERE nombre_seccion = '$seccion'")->fetch_assoc()['id'];

    // Insertar en resultados
    $sql_resultado = "INSERT INTO resultados (nombres, apellidos, grado_id, seccion_id, examen_id, puntaje) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_resultado = $conn->prepare($sql_resultado);
    $stmt_resultado->bind_param("ssiisi", $nombres, $apellidos, $grado_id, $seccion_id, $examen_id, $puntaje);
    $stmt_resultado->execute();
    $resultado_id = $stmt_resultado->insert_id;

    // Verificar respuestas y calcular puntaje
    $preguntas = $conn->query("SELECT * FROM preguntas WHERE examen_id = $examen_id");
    while ($pregunta = $preguntas->fetch_assoc()) {
        $pregunta_id = $pregunta['id'];
        $respuesta_correcta = $pregunta['respuesta_correcta'];
        $respuesta_usuario = $_POST['respuesta_' . $pregunta_id] ?? '';

        $correcta = ($respuesta_usuario === $respuesta_correcta) ? 1 : 0;
        $puntaje += $correcta;

        // Insertar respuesta
        $conn->query("INSERT INTO respuestas (resultado_id, pregunta_id, respuesta_estudiante, correcta) 
                      VALUES ($resultado_id, $pregunta_id, '$respuesta_usuario', $correcta)");
    }

    // Actualizar puntaje
    $conn->query("UPDATE resultados SET puntaje = $puntaje WHERE id = $resultado_id");

    header("Location: agradecimiento.php");
    exit();
} else {
    echo "Datos incompletos.";
}
?>
