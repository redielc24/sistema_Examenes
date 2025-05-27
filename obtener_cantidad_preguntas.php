<?php
include('conexion.php');

// Verificar si se recibió el ID del examen
if (isset($_GET['examen_id']) && is_numeric($_GET['examen_id'])) {
    $examen_id = $_GET['examen_id'];

    // Preparar la consulta para contar las preguntas del examen
    $sqlContarPreguntas = "SELECT COUNT(*) AS total FROM preguntas WHERE examen_id = ?";
    $stmt = $conn->prepare($sqlContarPreguntas);
    $stmt->bind_param("i", $examen_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    // Devolver el total en formato JSON
    echo json_encode(['total' => $data['total']]);
} else {
    // Devolver un error si no se proporciona un ID válido
    echo json_encode(['total' => 0]);
}

$conn->close();
?>
