<?php
function connectDB(): mysqli {
    $db = mysqli_connect("localhost", "root", "", "CursosDB");
    if (!$db) {
        // En caso de error de conexión, devolvemos una respuesta en JSON con el mensaje de error
        echo json_encode(["success" => false, "error" => "Error de conexión: " . mysqli_connect_error()]);
        exit;
    }
    return $db;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtenemos los datos JSON enviados desde JavaScript
    $data = json_decode(file_get_contents("php://input"), true);
    $nombre = $data['nombre'] ?? '';
    $apellido = $data['apellido'] ?? '';

    if (!$nombre || !$apellido) {
        echo json_encode(["success" => false, "error" => "Nombre o apellido faltante"]);
        exit;
    }

    // Conexión a la base de datos
    $db = connectDB();

    // Consulta SQL para buscar la matrícula
    $stmt = $db->prepare("SELECT Matricula FROM Cursante WHERE Nombre = ? AND Apellido = ?");
    if (!$stmt) {
        echo json_encode(["success" => false, "error" => "Error en la preparación: " . $db->error]);
        exit;
    }
    
    $stmt->bind_param("ss", $nombre, $apellido);
    $stmt->execute();
    $stmt->bind_result($matricula);

    if ($stmt->fetch()) {
        echo json_encode(["success" => true, "matricula" => $matricula]);
    } else {
        echo json_encode(["success" => false, "error" => "No se encontró la matrícula"]);
    }

    // Cierre de recursos
    $stmt->close();
    mysqli_close($db);
}
?>
