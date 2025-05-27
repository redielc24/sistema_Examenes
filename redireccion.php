<?php
session_start();
include('conexion.php');

// Recibir datos del formulario
$nombres = $_POST['nombres'];
$apellidos = $_POST['apellidos'];
$grado = $_POST['grado'];
$seccion = $_POST['seccion'];
$examen_id = $_POST['examen_id'];

// Obtener el tipo de examen
$sqlExamen = "SELECT tipo_examen FROM examenes WHERE id = ?";
$stmt = $conn->prepare($sqlExamen);
$stmt->bind_param("i", $examen_id);
$stmt->execute();
$resultExamen = $stmt->get_result();
$rowExamen = $resultExamen->fetch_assoc();

if (!$rowExamen) {
    die('Examen no encontrado.');
}

$tipo_examen = $rowExamen['tipo_examen'];

// Redireccionar según el tipo de examen usando POST
if ($tipo_examen == 'PDF') {
    ?>
    <form id="redirectForm" action="examen_pdf.php" method="POST">
        <input type="hidden" name="examen_id" value="<?php echo $examen_id; ?>">
        <input type="hidden" name="nombres" value="<?php echo $nombres; ?>">
        <input type="hidden" name="apellidos" value="<?php echo $apellidos; ?>">
        <input type="hidden" name="grado" value="<?php echo $grado; ?>">
        <input type="hidden" name="seccion" value="<?php echo $seccion; ?>">
    </form>
    <script>
        document.getElementById("redirectForm").submit();
    </script>
    <?php
    exit();
} elseif ($tipo_examen == 'TEXTO') {
    ?>
    <form id="redirectForm" action="examen_texto.php" method="POST">
        <input type="hidden" name="examen_id" value="<?php echo $examen_id; ?>">
        <input type="hidden" name="nombres" value="<?php echo $nombres; ?>">
        <input type="hidden" name="apellidos" value="<?php echo $apellidos; ?>">
        <input type="hidden" name="grado" value="<?php echo $grado; ?>">
        <input type="hidden" name="seccion" value="<?php echo $seccion; ?>">
    </form>
    <script>
        document.getElementById("redirectForm").submit();
    </script>
    <?php
    exit();
} else {
    echo "Tipo de examen no reconocido.";
    exit();
}
?>
