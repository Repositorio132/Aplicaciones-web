<?php
// Conexión a la base de datos
$host = "localhost"; 
$user = "root";
$password = ""; 
$dbname = "CursosDBN";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Inicio de sesión
session_start();

// Verificar si el cursante está logueado
if (!isset($_SESSION['matricula'])) {
    echo "Por favor, inicia sesión como cursante para inscribirte.";
    exit();
}

$matricula = $_SESSION['matricula'];

// Verificar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo_curso = intval($_POST['codigo_curso']);

    // Verificar si el cursante existe en la tabla Cursante
    $cursante_query = "SELECT * FROM Cursante WHERE Matricula = ?";
    $stmt_cursante = $conn->prepare($cursante_query);
    $stmt_cursante->bind_param("i", $matricula);
    $stmt_cursante->execute();
    $cursante_result = $stmt_cursante->get_result();

    if ($cursante_result->num_rows === 0) {
        echo "La matrícula no existe. Verifica que tu sesión sea válida.";
        exit();
    }

    // Verificar si el curso existe
    $curso_query = "SELECT * FROM Curso WHERE Codigo_Curso = ?";
    $stmt_curso = $conn->prepare($curso_query);
    $stmt_curso->bind_param("i", $codigo_curso);
    $stmt_curso->execute();
    $curso_result = $stmt_curso->get_result();

    if ($curso_result->num_rows === 0) {
        echo "El curso con el código ingresado no existe.";
    } else {
        // Verificar si ya está inscrito
        $inscripcion_query = "SELECT * FROM Registro_curso WHERE Curso = ? AND Cursante = ?";
        $stmt_inscripcion = $conn->prepare($inscripcion_query);
        $stmt_inscripcion->bind_param("ii", $codigo_curso, $matricula);
        $stmt_inscripcion->execute();
        $inscripcion_result = $stmt_inscripcion->get_result();

        if ($inscripcion_result->num_rows > 0) {
            echo "Ya estás inscrito en este curso.";
        } else {
            // Inscribir al cursante
            $insert_query = "
                INSERT INTO Registro_curso (Curso, Cursante, Fecha_inscripcion, Fecha_inicio, Fecha_fin, Estado, Calificaciones, Progreso)
                VALUES (?, ?, CURDATE(), CURDATE(), NULL, NULL, NULL, NULL)
            ";
            $stmt_insert = $conn->prepare($insert_query);
            $stmt_insert->bind_param("ii", $codigo_curso, $matricula);

            if ($stmt_insert->execute()) {
                echo "Te has inscrito correctamente al curso.";
            } else {
                echo "Hubo un error al inscribirte: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscripción a Curso</title>
    <link rel="stylesheet" href="../css/cursosDisponibles.css">
    <script src="../JavaScript/homeJ.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Barra lateral -->
    <div id="sidebar" class="sidebar">
        <button class="close-btn" onclick="toggleSidebar()">&times;</button>
        <a href="home.php"><i class="fas fa-home"></i> Home</a>
        <a href="CursosDisponibles.php"><i class="fas fa-book"></i> Cursos Disponibles</a>
        <a href="profile.php"><i class="fas fa-user"></i> Perfil</a>
        <a href="#noticias"><i class="fas fa-newspaper"></i> Notificaciones</a>
        <a href="#config"><i class="fas fa-cog"></i> Configuración</a>
        <a href="auth.php"><i class="fas fa-sign-out-alt"></i> Salir</a>
    </div>

    <!-- Encabezado -->
    <header>
        <button class="open-btn" onclick="toggleSidebar()">&#9776;</button>
        <img src='../img/logo.jpeg' alt="Certify" class="logo">
    </header>
    <h1>Inscripción a Curso</h1>
    <form method="POST" action="">
        <label for="codigo_curso">Código del Curso:</label>
        <input type="number" name="codigo_curso" id="codigo_curso" required>
        <button type="submit">Inscribirse</button>
    </form>
</body>
</html>
