<?php
// Conexión a la base de datos
function connectDB(): mysqli {
    $db = mysqli_connect("localhost", "root", "", "CursosDBN");
    if (!$db) {
        die("Error de conexión: " . mysqli_connect_error());
    }
    return $db;
}

$db = connectDB();

// Obtener todos los cursos registrados, incluyendo fechas de inicio y fin
$queryCourses = "
    SELECT Curso.Codigo_curso, 
           Curso.Nombre_curso, 
           Curso.Descripcion_curso, 
           Registro_curso.Fecha_inicio, 
           Registro_curso.Fecha_fin
    FROM Curso
    LEFT JOIN Registro_curso ON Curso.Codigo_curso = Registro_curso.Curso
";
$result = $db->query($queryCourses);
$courses = $result->fetch_all(MYSQLI_ASSOC);

mysqli_close($db);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página de Inicio - Administrador</title>
    <link rel="stylesheet" href="../css/home.css">
    <script src="../JavaScript/homeJ.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Barra lateral -->
    <div id="sidebar" class="sidebar">
        <button class="close-btn" onclick="toggleSidebar()">&times;</button>
        <a href="homeAdmin.php"><i class="fas fa-home"></i> Inicio</a>
        <a href="agregar_tema.php"><i class="fas fa-file-alt"></i> Crear Tema </a>
        <a href="/PHP/CreateCoursess.php"><i class="fas fa-book"></i> Crear Cursos</a>
        <a href="/PHP/AdministrarUsuarios.php"><i class="fas fa-users"></i> Gestionar Usuarios</a>
        <a href="ActualizarCursos.php"><i class="fas fa-file-alt"></i> Gestionar Cursos </a>
        <a href="addMaterial.php"><i class="fas fa-file-alt"></i> Agregar Material de Apoyo </a>
        <a href="auth.php"><i class="fas fa-sign-out-alt"></i> Salir</a>
    </div>

    <!-- Encabezado -->
    <header>
        <button class="open-btn" onclick="toggleSidebar()">&#9776;</button>
        <img src='../img/logo.jpeg' alt="Certify" class="logo">
    </header>

    <h2>Bienvenido Administrador</h2>

    <!-- Lista de cursos -->
    <div class="courses-list">
        <h3>Todos los Cursos Registrados:</h3>
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
            <p>No hay cursos registrados.</p>
        <?php endif; ?>
    </div>
</body>
</html>
