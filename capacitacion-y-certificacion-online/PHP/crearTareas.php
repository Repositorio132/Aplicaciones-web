<?php
include "../includes/config/conect.php";
$db = connectDB();

// Inicializar variables
$mensaje = "";
$courses = [];
$testTypes = [];
$selectedCourseID = intval($_GET['curso'] ?? 0); // Obtener el curso desde la URL
$selectedCourseName = "";
$selectedCourseTopic = "";

// Validar que el curso exista y obtener su tema
$queryCourse = "SELECT C.Nombre_curso, T.Nombre_tema 
                FROM Curso C 
                JOIN Tema T ON C.Tema = T.Codigo_tema 
                WHERE C.Codigo_curso = ?";
$stmt = $db->prepare($queryCourse);
$stmt->bind_param("i", $selectedCourseID);
$stmt->execute();
$stmt->bind_result($selectedCourseName, $selectedCourseTopic);
$stmt->fetch();
$stmt->close();

if (empty($selectedCourseName)) {
    die("Error: El curso seleccionado no existe.");
}

// Obtener tipos de test existentes
$queryTestTypes = "SELECT Codigo_tipotest, Descripcion FROM Tipo_test";
$resultTestTypes = $db->query($queryTestTypes);
if ($resultTestTypes) {
    $testTypes = $resultTestTypes->fetch_all(MYSQLI_ASSOC);
} else {
    die("Error al obtener tipos de test: " . $db->error);
}

// Procesar la creación de test y preguntas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['createTask'])) {
    $courseID = $selectedCourseID; // Usar siempre el curso preseleccionado
    $taskTitle = trim($_POST['taskTitle'] ?? '');
    $taskDescription = trim($_POST['taskDescription'] ?? '');
    $taskDeadline = $_POST['taskDeadline'] ?? '';
    $taskMaxScore = floatval($_POST['taskMaxScore'] ?? 0);
    $testType = intval($_POST['testType'] ?? 0);
    $questions = $_POST['questions'] ?? [];

    // Validar que todos los campos estén completos
    if ($courseID && $taskTitle && $taskDescription && $taskDeadline && $taskMaxScore > 0 && $testType > 0 && !empty($questions)) {
        try {
            // Crear el test
            $stmt = $db->prepare("INSERT INTO Test (Nombre_test, Descripcion_test, Fecha_limite, Puntaje, Tipo_test, Curso) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssiii", $taskTitle, $taskDescription, $taskDeadline, $taskMaxScore, $testType, $courseID);
            $stmt->execute();
            $testId = $stmt->insert_id;

            // Agregar preguntas con opciones y respuesta correcta
            foreach ($questions as $question) {
                $questionText = trim($question['title']);
                $options = $question['options'] ?? [];
                $scores = $question['scores'] ?? [];
                $correctAnswer = intval($question['correctAnswer'] ?? -1);

                // Validar que la pregunta tenga texto y al menos dos opciones
                if (!empty($questionText) && count($options) >= 2 && count($scores) === count($options) && $correctAnswer >= 0) {
                    // Insertar la pregunta
                    $stmtQuestion = $db->prepare("INSERT INTO Pregunta (Texto_pregunta, Test) VALUES (?, ?)");
                    $stmtQuestion->bind_param("si", $questionText, $testId);
                    $stmtQuestion->execute();
                    $questionId = $stmtQuestion->insert_id;

                    // Insertar opciones
                    foreach ($options as $key => $option) {
                        $score = floatval($scores[$key] ?? 0);
                        $stmtOption = $db->prepare("INSERT INTO Respuestas (Descripcion, Puntaje_respuesta, Pregunta) VALUES (?, ?, ?)");
                        $stmtOption->bind_param("sdi", $option, $score, $questionId);
                        $stmtOption->execute();
                    }
                } else {
                    throw new Exception("Error: Cada pregunta debe tener al menos dos opciones y un puntaje válido.");
                }
            }

            $mensaje = "Test y preguntas creadas exitosamente.";
        } catch (Exception $e) {
            $mensaje = "Error al crear el test: " . $e->getMessage();
        }
    } else {
        $mensaje = "Todos los campos son obligatorios y deben estar completos.";
    }
}

$db->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Test con Opción Múltiple</title>
    <link rel="stylesheet" href="../css/crearTarea.css">
    <script src="../JavaScript/homeJ.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script>
        let questionCount = 0;

        function addQuestion() {
            questionCount++;
            const container = document.getElementById('questionsContainer');
            const questionHTML = `
                <div class="question">
                    <h4>Pregunta ${questionCount}</h4>
                    <label for="questions[${questionCount}][title]">Texto de la pregunta:</label>
                    <input type="text" name="questions[${questionCount}][title]" required>

                    <div class="options">
                        <h5>Opciones:</h5>
                        <button type="button" onclick="addOption(${questionCount})">Agregar Opción</button>
                        <div id="optionsContainer${questionCount}"></div>
                    </div>

                    <label for="questions[${questionCount}][correctAnswer]">Respuesta Correcta (Número de Opción):</label>
                    <input type="number" name="questions[${questionCount}][correctAnswer]" min="0" required>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', questionHTML);
        }

        function addOption(questionIndex) {
            const container = document.getElementById(`optionsContainer${questionIndex}`);
            const optionCount = container.children.length;
            const optionHTML = `
                <div class="option">
                    <label>Opción ${optionCount + 1}:</label>
                    <input type="text" name="questions[${questionIndex}][options][]" required>
                    <label>Puntaje:</label>
                    <input type="number" name="questions[${questionIndex}][scores][]" min="0" step="0.01" required>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', optionHTML);
        }
    </script>
</head>
<body>
     <!-- Barra lateral -->
     <div id="sidebar" class="sidebar">
        <button class="close-btn" onclick="toggleSidebar()">&times;</button>
        <a href="homeCapacitor.php"><i class="fas fa-home"></i> Inicio</a>
        <a href="/PHP/CursosAsignados.php"><i class="fas fa-book"></i> Ver cursos asignados </a>
        <a href="profileCapacitor.php"><i class="fas fa-user"></i> Perfil</a>
        <a href="viewStudents.php"><i class="fas fa-users"></i> Ver Alumnos</a>
        <a href="auth.php"><i class="fas fa-sign-out-alt"></i> Salir</a>
    </div>

     <!-- Encabezado -->
    <header>
        <button class="open-btn" onclick="toggleSidebar()">&#9776;</button>
        <img src='../img/logo.jpeg' alt="Certify" class="logo">
    </header>
    <div class="container">
        <h1>Crear Test con Preguntas de Opción Múltiple</h1>
        <?php if (!empty($mensaje)): ?>
            <p class="alert"><?= htmlspecialchars($mensaje) ?></p>
        <?php endif; ?>

        <form method="POST">
            <label for="courseName">Curso:</label>
            <input type="text" id="courseName" value="<?= htmlspecialchars($selectedCourseName) ?>" readonly>
            <input type="hidden" name="courseID" value="<?= htmlspecialchars($selectedCourseID) ?>">

            <label for="courseTopic">Tema del Curso:</label>
            <input type="text" id="courseTopic" value="<?= htmlspecialchars($selectedCourseTopic) ?>" readonly>

            <label for="testType">Tipo de Test:</label>
            <select id="testType" name="testType" required>
                <option value="">Seleccione</option>
                <?php foreach ($testTypes as $type): ?>
                    <option value="<?= htmlspecialchars($type['Codigo_tipotest']) ?>"><?= htmlspecialchars($type['Descripcion']) ?></option>
                <?php endforeach; ?>
            </select>

            <label for="taskTitle">Título del Test:</label>
            <input type="text" id="taskTitle" name="taskTitle" required>

            <label for="taskDescription">Descripción:</label>
            <textarea id="taskDescription" name="taskDescription" required></textarea>

            <label for="taskDeadline">Fecha de Entrega:</label>
            <input type="date" id="taskDeadline" name="taskDeadline" required>

            <label for="taskMaxScore">Puntaje Máximo:</label>
            <input type="number" id="taskMaxScore" name="taskMaxScore" min="1" required>

            <h3>Preguntas</h3>
            <div id="questionsContainer"></div>
            <button type="button" onclick="addQuestion()">Agregar Pregunta</button>

            <button type="submit" name="createTask">Crear Test</button>
        </form>
    </div>
</body>
</html>
