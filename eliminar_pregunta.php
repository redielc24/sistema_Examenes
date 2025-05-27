<?php
session_start();
include('conexion.php');

// Verificar si el usuario es administrador
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'administrador') {
    header("Location: login_administrador.php");
    exit();
}

// Verificar si se ha pasado el ID de la pregunta
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $pregunta_id = $_GET['id'];

    // Eliminar la pregunta
    $sqlDeletePregunta = "DELETE FROM preguntas WHERE id = ?";
    $stmtDeletePregunta = $conn->prepare($sqlDeletePregunta);
    $stmtDeletePregunta->bind_param("i", $pregunta_id);

    if ($stmtDeletePregunta->execute()) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: 'Pregunta eliminada con éxito.'
            }).then(() => {
                window.location.href = 'ver_preguntas.php';
            });
        </script>";
    } else {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo eliminar la pregunta. Inténtalo de nuevo.'
            });
        </script>";
    }
} else {
    echo "ID de pregunta no válido.";
    exit();
}

$conn->close();
?>
