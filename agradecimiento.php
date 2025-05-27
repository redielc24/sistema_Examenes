<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gracias por tu Examen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa; /* Color más neutro para el fondo */
            font-family: 'Arial', sans-serif;
        }
        .container {
            max-width: 600px;
            margin-top: 100px;
            background-color: #ffffff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        h2 {
            color: #28a745; /* Color verde para un mensaje positivo */
        }
        p {
            color: #6c757d; /* Color gris para el texto */
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>¡Gracias por tu Examen!</h2>
        <p>Tu examen ha sido registrado con éxito. ¡Buena suerte en tus futuros estudios!</p>
        <div class="text-center">
            <a href="index.php" class="btn btn-primary">Regresar al Inicio</a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Redirigir después de 3 segundos (3000 ms)
        setTimeout(function() {
            window.location.href = "index.php";
        }, 5000);
    </script>
</body>
</html>
