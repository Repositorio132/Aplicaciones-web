<?php
// Función para conectar a la base de datos
function connectDB(): mysqli {
    $db = new mysqli("localhost", "root", "", "CursosDBN");
    if ($db->connect_error) {
        die("Error de conexión: " . $db->connect_error);
    }
    return $db;
}

$db = connectDB();

// Obtener capacitadores disponibles
$queryTrainers = "SELECT Codigo_capacitador, CONCAT(Nombre, ' ', Primer_apellido, ' ', Segundo_apellido) AS NombreCompleto FROM Capacitor";
$resultTrainers = $db->query($queryTrainers);
$trainers = $resultTrainers ? $resultTrainers->fetch_all(MYSQLI_ASSOC) : [];

// Obtener estados disponibles
$queryStatuses = "SELECT ID_estado, Descripcion FROM Estado";
$resultStatuses = $db->query($queryStatuses);
$statuses = $resultStatuses ? $resultStatuses->fetch_all(MYSQLI_ASSOC) : [];

// Obtener temas disponibles
$queryTopics = "SELECT Codigo_tema, Nombre_tema FROM Tema";
$resultTopics = $db->query($queryTopics);
$topics = $resultTopics ? $resultTopics->fetch_all(MYSQLI_ASSOC) : [];

// Procesar creación de curso
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courseName = trim($_POST['courseName'] ?? '');
    $courseDescription = trim($_POST['courseDescription'] ?? '');
    $availability = intval($_POST['availability'] ?? 0);
    $fechaFin = $_POST['fechaFin'] ?? '';
    $trainerId = intval($_POST['trainerId'] ?? 0);
    $topicId = intval($_POST['topicId'] ?? 0); // Selección de tema

    if ($courseName && $courseDescription && $availability && $fechaFin && $trainerId && $topicId) {
        try {
            // Preparar la llamada al procedimiento, ahora incluyendo el tema
            $stmt = $db->prepare("CALL RegistrarCurso(?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssiis", $courseName, $courseDescription, $fechaFin, $availability, $trainerId, $topicId);

            if ($stmt->execute()) {
                $mensaje = "Curso creado exitosamente.";
            } else {
                throw new Exception("Error al crear el curso: " . $stmt->error);
            }
        } catch (Exception $e) {
            $mensaje = "Error: " . $e->getMessage();
        }
    } else {
        $mensaje = "Todos los campos son obligatorios.";
    }
}

// Obtener cursos existentes
$queryCourses = "
    SELECT Curso.Codigo_curso, 
           Curso.Nombre_curso, 
           Curso.Descripcion_curso, 
           CONCAT(Capacitor.Nombre, ' ', Capacitor.Primer_apellido, ' ', Capacitor.Segundo_apellido) AS Nombre_Entrenador, 
           Estado.Descripcion AS Estado
    FROM Curso
    LEFT JOIN Capacitor ON Curso.Capacitador = Capacitor.Codigo_capacitador
    LEFT JOIN Registro_curso ON Curso.Codigo_curso = Registro_curso.Curso
    LEFT JOIN Estado ON Registro_curso.Estado = Estado.ID_estado
";
$resultCourses = $db->query($queryCourses);
$courses = $resultCourses ? $resultCourses->fetch_all(MYSQLI_ASSOC) : [];

$db->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Creación de Cursos y Asignación de Capacitadores</title>
    <script src="../JavaScript/homeJ.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../css/createCourses.css">
    <script>
        function validateForm(event) {
            const courseName = document.getElementById('courseName').value.trim();
            const courseDescription = document.getElementById('courseDescription').value.trim();
            const trainerId = document.getElementById('trainerId').value;
            const topicId = document.getElementById('topicId').value;
            const availability = document.getElementById('availability').value;
            const fechaFin = document.getElementById('fechaFin').value;

            if (!courseName || !courseDescription || !trainerId || !topicId || !availability || !fechaFin) {
                alert('Por favor, complete todos los campos antes de enviar.');
                event.preventDefault();
            }
        }
    </script>
</head>
<body>
    <!-- Barra lateral -->
    <div id="sidebar" class="sidebar">
        <button class="close-btn" onclick="toggleSidebar()">&times;</button>
        <a href="homeCapacitor.php"><i class="fas fa-home"></i> Inicio</a>
        <a href="CreateCourses.php"><i class="fas fa-book"></i> Crear cursos</a>
        <a href="profile.php"><i class="fas fa-user"></i> Perfil</a>
        <a href="viewStudents.php"><i class="fas fa-users"></i> Ver Alumnos</a>
        <a href="#config"><i class="fas fa-cog"></i> Configuración</a>
        <a href="auth.php"><i class="fas fa-sign-out-alt"></i> Salir</a>
    </div>

    <!-- Encabezado -->
    <header>
        <button class="open-btn" onclick="toggleSidebar()">&#9776;</button>
        <h1>Mi Plataforma</h1>
    </header>
    
    <div class="container">
        <h1>Crear y Asignar Cursos</h1>
        <?php if (isset($mensaje)): ?>
            <p class="alert"><?= $mensaje ?></p>
        <?php endif; ?>
        <form method="POST" onsubmit="validateForm(event)">
            <label for="courseName">Nombre del Curso:</label>
            <input type="text" id="courseName" name="courseName" required>

            <label for="courseDescription">Descripción:</label>
            <textarea id="courseDescription" name="courseDescription" required></textarea>

            <label for="trainerId">Capacitador:</label>
            <select id="trainerId" name="trainerId" required>
                <option value="">Seleccione</option>
                <?php foreach ($trainers as $trainer): ?>
                    <option value="<?= $trainer['Codigo_capacitador'] ?>"><?= $trainer['NombreCompleto'] ?></option>
                <?php endforeach; ?>
            </select>

            <label for="topicId">Tema:</label>
            <select id="topicId" name="topicId" required>
                <option value="">Seleccione</option>
                <?php foreach ($topics as $topic): ?>
                    <option value="<?= $topic['Codigo_tema'] ?>"><?= $topic['Nombre_tema'] ?></option>
                <?php endforeach; ?>
            </select>

            <label for="availability">Estado:</label>
            <select id="availability" name="availability" required>
                <option value="">Seleccione</option>
                <?php foreach ($statuses as $status): ?>
                    <option value="<?= $status['ID_estado'] ?>"><?= $status['Descripcion'] ?></option>
                <?php endforeach; ?>
            </select>

            <label for="fechaFin">Fecha de Finalización:</label>
            <input type="date" id="fechaFin" name="fechaFin" required>

            <button type="submit">Crear Curso</button>
        </form>

        <h2>Cursos Existentes</h2>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Capacitador</th>
                    <th>Estado</th>
                    <th>Código</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($courses as $course): ?>
                    <tr>
                        <td><?= $course['Nombre_curso'] ?></td>
                        <td><?= $course['Descripcion_curso'] ?></td>
                        <td><?= $course['Nombre_Entrenador'] ?></td>
                        <td><?= $course['Estado'] ?></td>
                        <td><?= $course['Codigo_curso'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <h2>Gestionar Tareas</h2>
    <p><a href="crearTareas.php">Agregar Test al Curso</a></p>
</body>
</html>
