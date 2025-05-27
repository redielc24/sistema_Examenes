<?php
session_start();
// Verificar si el usuario es administrador
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'administrador') {
    header("Location: login_administrador.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administrador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa; /* Color gris claro */
        }
        .container {
            max-width: 1100px;
            margin-top: 20px;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        .section-title {
            margin-top: 40px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Panel de Administrador</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="#">Inicio</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="gestionExamenes" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Gestión de Exámenes
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="gestionExamenes">
                            <li><a class="dropdown-item" href="crear_examen.php">Crear Examen</a></li>
                            <li><a class="dropdown-item" href="gestionar_examenes.php">Ver Exámenes</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="gestionPreguntas" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Gestión de Preguntas
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="gestionPreguntas">
                            <li><a class="dropdown-item" href="crear_pregunta.php">Agregar Pregunta</a></li>
                            <li><a class="dropdown-item" href="ver_preguntas.php">Ver Preguntas</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="gestionUsuarios" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Gestión de Usuarios
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="gestionUsuarios">
                            <li><a class="dropdown-item" href="crear_usuario.php">Agregar Usuario</a></li>
                            <li><a class="dropdown-item" href="ver_usuarios.php">Ver Usuarios</a></li>
                        </ul>
                    </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="gestionExamenes" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Gestión de Resultados
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="gestionExamenes">
                                    <li><a class="dropdown-item" href="ver_resultados.php">Ver resultados</a></li>
                                    <li><a class="dropdown-item" href="historial_estudiante.php">Resultado por etudiante</a></li>
                                    <li><a class="dropdown-item" href="resultados_correctas.php">Correctas e Incorrectas</a></li>
                                    <li><a class="dropdown-item" href="estadistica_general.php">General</a></li>
                                    <li><a class="dropdown-item" href="estadistica_barras.php">Gráficos de barras</a></li>
                                </ul>
                            </li>

                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="gestionBackup" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Backups
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="gestionExamenes">
                                    <li><a class="dropdown-item" href="crear_backup.php">Crear copia BD</a></li>
                                    <li><a class="dropdown-item" href="historial_backup.php">Historial</a></li>
                                    
                                </ul>
                            </li>
                                    

                                        <li class="nav-item btn-red">
                        <a class="nav-link text-danger" href="logout.php">Cerrar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    </table>

        

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
</body>
</html>
