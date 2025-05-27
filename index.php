<?php
include('conexion.php');

// Obtener los grados
$sql_grados = "SELECT id, nombre_grado FROM grados";
$result_grados = $conn->query($sql_grados);

// Obtener las secciones
$sql_secciones = "SELECT id, nombre_seccion FROM secciones";
$result_secciones = $conn->query($sql_secciones);

// Obtener los exámenes
$sql_examenes = "SELECT id, nombre_examen FROM examenes";
$result_examenes = $conn->query($sql_examenes);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - Sistema de Exámenes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(to right, #8e44ad, #3498db);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Roboto', sans-serif;
        }
        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2);
        }
        .btn-primary {
            background-color: #8e44ad;
            border: none;
        }
        .btn-primary:hover {
            background-color: #5e3370;
        }
        .admin-login {
            text-align: center;
            margin-top: 15px;
        }
        .admin-login a {
            font-size: 12px;
            color: #555;
            text-decoration: none;
        }
        .admin-login a:hover {
            color: #8e44ad;
            text-decoration: underline;
        }
        input[type="text"] {
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="card p-4">
        <h2 class="text-center text-primary mb-4"><i class="fas fa-edit"></i> Registro de Estudiante</h2>
        <form action="redireccion.php" method="POST" autocomplete="off">
            <div class="mb-3">
                <label class="form-label">Nombres:</label>
                <input type="text" name="nombres" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Apellidos:</label>
                <input type="text" name="apellidos" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Grado:</label>
                <select name="grado" class="form-select" required>
                    <option value="" disabled selected>Seleccione un grado</option>
                    <?php while ($row_grado = $result_grados->fetch_assoc()): ?>
                        <option value="<?php echo $row_grado['nombre_grado']; ?>">
                            <?php echo $row_grado['nombre_grado']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Sección:</label>
                <select name="seccion" class="form-select" required>
                    <option value="" disabled selected>Seleccione una sección</option>
                    <?php while ($row_seccion = $result_secciones->fetch_assoc()): ?>
                        <option value="<?php echo $row_seccion['nombre_seccion']; ?>">
                            <?php echo $row_seccion['nombre_seccion']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Seleccionar Examen:</label>
                <select name="examen_id" class="form-select" required>
                    <option value="" disabled selected>Seleccione un examen</option>
                    <?php while ($row_examen = $result_examenes->fetch_assoc()): ?>
                        <option value="<?php echo $row_examen['id']; ?>">
                            <?php echo $row_examen['nombre_examen']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary w-100">Iniciar Examen</button>
        </form>
        <!-- Enlace al login del administrador -->
        <div class="admin-login">
            <a href="login_administrador.php">Acceso para Administrador</a>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>
