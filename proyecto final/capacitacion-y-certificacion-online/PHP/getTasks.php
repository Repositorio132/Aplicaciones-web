<?php
require '../capacitacion-y-certificacion-online/includes/config/conect.php'; // Archivo que contiene la función para conectarse a la base de datos

$conn = connectDB();

// Consultar las tareas creadas por el entrenador
$sql = "SELECT Num_Tarea AS id, Titulo AS title, 
               IF(CURDATE() >= Fecha_Creacion, 'available', 'not available') AS status 
        FROM Tarea";
$result = $conn->query($sql);

$tareas = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tareas[] = $row;
    }
}

// Devolver los datos en formato JSON
header('Content-Type: application/json');
echo json_encode($tareas);

$conn->close();
?>
