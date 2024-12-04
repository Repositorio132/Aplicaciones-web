<?php
session_start();

// Verificar si el capacitador ha iniciado sesión
if (!isset($_SESSION['capacitador_id'])) {
    die("Acceso no autorizado. Por favor, inicia sesión.");
}

$capacitadorId = intval($_SESSION['capacitador_id']); // Usar la variable de sesión correcta

// Conexión a la base de datos
function connectDB(): mysqli {
    $db = mysqli_connect("localhost", "root", "", "CursosDBN");
    if (!$db) {
        die("Error de conexión: " . mysqli_connect_error());
    }
    return $db;
}

$db = connectDB();

// Obtener cursos creados por el capacitador, incluyendo fechas de inicio y fin
$queryCourses = "
    SELECT Curso.Codigo_curso, 
           Curso.Nombre_curso, 
           Curso.Descripcion_curso, 
           Registro_curso.Fecha_inicio, 
           Registro_curso.Fecha_fin
    FROM Curso
    LEFT JOIN Registro_curso ON Curso.Codigo_curso = Registro_curso.Curso
    WHERE Curso.Capacitador = ?
";
$stmt = $db->prepare($queryCourses);
$stmt->bind_param("i", $capacitadorId);
$stmt->execute();
$courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

mysqli_close($db);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página de Inicio</title>
    <link rel="stylesheet" href="../css/home.css">
    <script src="../JavaScript/homeJ.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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

    <h2>Bienvenido Capacitor</h2>

    <!-- Lista de cursos -->
    <div class="courses-list">
        <h3>Cursos asignados :</h3>
        <?php if (count($courses) > 0): ?>
            <ul>
                <?php foreach ($courses as $course): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($course['Nombre_curso']); ?></strong><br>
                        Descripción: <?php echo htmlspecialchars($course['Descripcion_curso']); ?><br>
                        Fecha de inicio: <?php echo htmlspecialchars($course['Fecha_inicio'] ?? 'No registrada'); ?><br>
                        Fecha de fin: <?php echo htmlspecialchars($course['Fecha_fin'] ?? 'No registrada'); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No tienes cursos creados.</p>
        <?php endif; ?>
    </div>
</body>
</html>
