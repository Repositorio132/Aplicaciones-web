<?php
session_start();

// Función para conectarse a la base de datos
function connectDB(): mysqli {
    $db = mysqli_connect("localhost", "root", "", "CursosDBN");
    if (!$db) {
        die("Error de conexión: " . mysqli_connect_error());
    }
    return $db;
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

$db = connectDB();

// Verificar si se ha proporcionado el código del test en la URL
$codigoTest = $_GET['codigo_tarea'] ?? null;
$cursanteId = $_SESSION['matricula'] ?? null; // Usar `matricula` de la sesión para identificar al cursante

if (!$codigoTest) {
    die("Error: No se proporcionó un código de test válido.");
}

if (!$cursanteId) {
    die("Error: No se identificó al cursante. Por favor, inicie sesión nuevamente.");
}

// Consultar detalles del test
$tarea = [];
$stmtTarea = $db->prepare("
    SELECT T.Nombre_test AS Titulo, T.Descripcion_test AS Descripcion
    FROM Test T
    WHERE T.Codigo_test = ?
");
$stmtTarea->bind_param("i", $codigoTest);
$stmtTarea->execute();
$resultTarea = $stmtTarea->get_result();
if ($row = $resultTarea->fetch_assoc()) {
    $tarea = $row;
} else {
    die("Error: No se encontró el test con el código proporcionado.");
}
$stmtTarea->close();

// Consultar las preguntas asociadas al test
$preguntas = [];
$stmtPreguntas = $db->prepare("
    SELECT P.Num_pregunta AS Id_Pregunta, P.Texto_pregunta AS Titulo_Pregunta
    FROM Pregunta P
    WHERE P.Test = ?
");
$stmtPreguntas->bind_param("i", $codigoTest);
$stmtPreguntas->execute();
$resultPreguntas = $stmtPreguntas->get_result();

while ($row = $resultPreguntas->fetch_assoc()) {
    // Consultar las opciones de respuesta asociadas a cada pregunta
    $opciones = [];
    $stmtOpciones = $db->prepare("
        SELECT R.Numero_pregunta, R.Descripcion AS Texto, R.Puntaje_respuesta
        FROM Respuestas R
        WHERE R.Pregunta = ?
    ");
    $stmtOpciones->bind_param("i", $row['Id_Pregunta']);
    $stmtOpciones->execute();
    $resultOpciones = $stmtOpciones->get_result();

    while ($opcion = $resultOpciones->fetch_assoc()) {
        $opciones[] = [
            'Codigo_respuesta' => $opcion['Numero_pregunta'],
            'Texto' => $opcion['Texto'],
            'Puntaje_respuesta' => $opcion['Puntaje_respuesta']
        ];
    }

    $stmtOpciones->close();

    $row['Opciones'] = $opciones;
    $preguntas[] = $row;
}
$stmtPreguntas->close();

// Procesar el envío del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $respuestas = $_POST['respuestas'] ?? [];

    if (!empty($respuestas)) {
        $totalScore = 0;

        try {
            // Calcular el puntaje total del cursante
            foreach ($respuestas as $preguntaId => $respuestaIds) {
                foreach ($respuestaIds as $respuestaId) {
                    // Obtener el puntaje de cada respuesta seleccionada
                    $stmt = $db->prepare("SELECT Puntaje_respuesta FROM Respuestas WHERE Numero_pregunta = ?");
                    $stmt->bind_param("i", $respuestaId);
                    $stmt->execute();
                    $stmt->bind_result($score);
                    if ($stmt->fetch()) {
                        $totalScore += $score;
                    }
                    $stmt->close();
                }
            }

            // Actualizar la calificación del cursante en el curso
            $stmtUpdate = $db->prepare("
                UPDATE Registro_curso 
                SET Calificaciones = ? 
                WHERE Cursante = ? 
                AND Curso = (SELECT Curso FROM Test WHERE Codigo_test = ?)
            ");
            $stmtUpdate->bind_param("dii", $totalScore, $cursanteId, $codigoTest);
            $stmtUpdate->execute();
            $stmtUpdate->close();

            // Llamar al procedimiento almacenado VerificarExamen
            $stmtProc = $db->prepare("CALL VerificarExamen(?, ?)");
            $stmtProc->bind_param("ii", $cursanteId, $codigoTest);
            $stmtProc->execute();
            $stmtProc->close();

            // Verificar si la calificación es suficiente para generar el certificado
            $calificacionMinima = 70; // Calificación mínima deseada
            if ($totalScore >= $calificacionMinima) {
                // Redirigir a fpdf.php para generar el certificado
                header("Location: fpdf.php?codigo_tarea=" . urlencode($codigoTest));
                exit();
            } else {
                echo "<script>alert('No alcanzaste la calificación mínima para el certificado.');</script>";
            }
        } catch (Exception $e) {
            echo "<script>alert('Error al procesar las respuestas: " . $e->getMessage() . "');</script>";
        }
    } else {
        echo "<script>alert('Por favor, completa todas las preguntas antes de enviar.');</script>";
    }
}

mysqli_close($db);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Responder Test</title>
    <link rel="stylesheet" href="../css/home.css">
    <link rel="stylesheet" href="../css/resolverTarera.css">
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
    <main class="content">
        <?php if ($tarea): ?>
            <h2><?php echo htmlspecialchars($tarea['Titulo']); ?></h2>
            <p><?php echo htmlspecialchars($tarea['Descripcion']); ?></p>
            <form action="" method="POST">
                <fieldset>
                    <legend>Preguntas</legend>
                    <?php foreach ($preguntas as $pregunta): ?>
                        <div>
                            <p><?php echo htmlspecialchars($pregunta['Titulo_Pregunta']); ?></p>
                            <?php foreach ($pregunta['Opciones'] as $opcion): ?>
                                <label>
                                    <input 
                                        type="radio" 
                                        name="respuestas[<?php echo $pregunta['Id_Pregunta']; ?>][]" 
                                        value="<?php echo htmlspecialchars($opcion['Codigo_respuesta']); ?>" 
                                        required>
                                    <?php echo htmlspecialchars($opcion['Texto']); ?>
                                </label><br>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </fieldset>
                <button type="submit">Enviar Respuestas</button>
            </form>
        <?php else: ?>
            <p>No se encontraron detalles para este test.</p>
        <?php endif; ?>
    </main>
</body>
</html>
