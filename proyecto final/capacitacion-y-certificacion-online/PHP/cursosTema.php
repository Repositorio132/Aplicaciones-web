<?php
// Configuración de la base de datos
$host = 'localhost';
$usuario = 'root';
$password = '';
$base_datos = 'CursosDB';

// Conectar a la base de datos
$conn = new mysqli($host, $usuario, $password, $base_datos);

// Verificar la conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Mensaje para retroalimentación
$mensaje = "";

// Procesar formulario al enviar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_tema = $_POST['nombre_tema'];
    $descripcion_tema = $_POST['descripcion_tema'];

    // Validar que los campos no estén vacíos
    if (!empty($nombre_tema) && !empty($descripcion_tema)) {
        $stmt = $conn->prepare("INSERT INTO Tema (Nombre_Tema, Descripcion_Tema) VALUES (?, ?)");
        $stmt->bind_param("ss", $nombre_tema, $descripcion_tema);

        if ($stmt->execute()) {
            $mensaje = "Tema creado exitosamente.";
        } else {
            $mensaje = "Error al crear el tema: " . $conn->error;
        }

        $stmt->close();
    } else {
        $mensaje = "Por favor, completa todos los campos.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Tema</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }
        input, textarea {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
        .mensaje {
            margin-top: 20px;
            padding: 10px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
            text-align: center;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Crear Tema</h1>
        <?php if (!empty($mensaje)): ?>
            <div class="mensaje <?= strpos($mensaje, 'Error') === false ? '' : 'error'; ?>">
                <?= htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>
        <form action="../PHP/cursosTema.php" method="POST">
            <div class="form-group">
                <label for="nombre_tema">Nombre del Tema:</label>
                <input type="text" id="nombre_tema" name="nombre_tema" required>
            </div>
            <div class="form-group">
                <label for="descripcion_tema">Descripción del Tema:</label>
                <textarea id="descripcion_tema" name="descripcion_tema" rows="5" required></textarea>
            </div>
            <button type="submit">Crear Tema</button>
        </form>
    </div>
</body>
</html>