<?php
include "../includes/config/conect.php";
$db = connectDB();

// Inicializar variables
$mensaje = "";
$courses = [];
$testTypes = [];

// Obtener todos los cursos disponibles
$queryCourses = "SELECT Codigo_curso, Nombre_curso FROM Curso";
$resultCourses = $db->query($queryCourses);
if ($resultCourses) {
    $courses = $resultCourses->fetch_all(MYSQLI_ASSOC);
} else {
    die("Error al obtener cursos: " . $db->error);
}

// Obtener todos los tipos de test disponibles
$queryTestTypes = "SELECT Codigo_tipotest, Descripcion FROM Tipo_test";
$resultTestTypes = $db->query($queryTestTypes);
if ($resultTestTypes) {
    $testTypes = $resultTestTypes->fetch_all(MYSQLI_ASSOC);
} else {
    die("Error al obtener tipos de test: " . $db->error);
}

// Procesar la creación de test y preguntas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['createTask'])) {
    $courseID = intval($_POST['courseID'] ?? 0);
    $taskTitle = trim($_POST['taskTitle'] ?? '');
    $taskDescription = trim($_POST['taskDescription'] ?? '');
    $taskDeadline = $_POST['taskDeadline'] ?? '';
    $taskMaxScore = floatval($_POST['taskMaxScore'] ?? 0);
    $testType = intval($_POST['testType'] ?? 0);
    $questions = $_POST['questions'] ?? [];

    // Insertar datos del test y preguntas
    try {
        // Crear el test
        $stmt = $db->prepare("INSERT INTO Test (Nombre_test, Descripcion_test, Fecha_limite, Puntaje, Tipo_test, Curso) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiii", $taskTitle, $taskDescription, $taskDeadline, $taskMaxScore, $testType, $courseID);
        $stmt->execute();
        $testId = $stmt->insert_id;

        // Insertar preguntas y respuestas
        foreach ($questions as $question) {
            $questionText = trim($question['title'] ?? '');
            $options = $question['options'] ?? [];
            $scores = $question['scores'] ?? [];
            $correctAnswer = intval($question['correctAnswer'] ?? -1);

            // Insertar pregunta
            $stmtQuestion = $db->prepare("INSERT INTO Pregunta (Texto_pregunta, Test) VALUES (?, ?)");
            $stmtQuestion->bind_param("si", $questionText, $testId);
            $stmtQuestion->execute();
            $questionId = $stmtQuestion->insert_id;

            // Insertar respuestas
            foreach ($options as $key => $option) {
                $score = floatval($scores[$key] ?? 0);
                $stmtOption = $db->prepare("INSERT INTO Respuestas (Descripcion, Puntaje_respuesta, Pregunta) VALUES (?, ?, ?)");
                $stmtOption->bind_param("sdi", $option, $score, $questionId);
                $stmtOption->execute();
            }
        }

        $mensaje = "Test y preguntas creadas exitosamente.";
    } catch (Exception $e) {
        $mensaje = "Error al crear el test: " . $e->getMessage();
    }
}

$db->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrador - Crear Test</title>
    <link rel="stylesheet" href="../css/createCourses.css">
    <link rel="stylesheet" href="../css/crearTarea.css">
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
    <div class="container">
        <h1>Administrador - Crear Test con Preguntas</h1>
        <?php if (!empty($mensaje)): ?>
            <p class="alert"><?= htmlspecialchars($mensaje) ?></p>
        <?php endif; ?>

        <form method="POST">
            <label for="courseID">Seleccionar Curso:</label>
            <select id="courseID" name="courseID" required>
                <option value="">Seleccione un curso</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?= htmlspecialchars($course['Codigo_curso']) ?>"><?= htmlspecialchars($course['Nombre_curso']) ?></option>
                <?php endforeach; ?>
            </select>

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
