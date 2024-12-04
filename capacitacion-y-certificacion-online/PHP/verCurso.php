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

// Configuración de errores
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Conexión a la base de datos
$db = connectDB();

// Obtener la matrícula del usuario autenticado (desde la sesión)
$matriculaCursante = $_SESSION['matricula'] ?? null;

// Verificar si se ha proporcionado el código del curso
$codigoCurso = $_GET['codigo_curso'] ?? null;
if (!$codigoCurso) {
    die("El código del curso es necesario.");
}

// Consultar información del curso
$curso = [];
$stmtCurso = $db->prepare("
    SELECT Nombre_curso, Descripcion_curso
    FROM Curso
    WHERE Codigo_curso = ?
");
$stmtCurso->bind_param("i", $codigoCurso);
$stmtCurso->execute();
$resultCurso = $stmtCurso->get_result();

if ($row = $resultCurso->fetch_assoc()) {
    $curso = $row;
}
$stmtCurso->close();

// Consultar los tests asociados al curso
$tests = [];
$stmtTests = $db->prepare("
    SELECT Codigo_test, Nombre_test, Descripcion_test, Fecha_limite, Puntaje
    FROM Test
    WHERE Curso = ?
");
$stmtTests->bind_param("i", $codigoCurso);
$stmtTests->execute();
$resultTests = $stmtTests->get_result();

while ($row = $resultTests->fetch_assoc()) {
    $tests[] = $row;
}
$stmtTests->close();

// Verificar si el examen final está disponible
$examenFinalDisponible = false;
foreach ($tests as $test) {
    if ($test['Nombre_test'] === 'Examen Final') {
        $examenFinalDisponible = true;
        break;
    }
}

mysqli_close($db);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Curso</title>
    <link rel="stylesheet" href="../css/home.css">
    <script src="../JavaScript/homeJ.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../css/verCurso.css">
</head>
<body>
    <!-- Sidebar -->
    <div id="sidebar" class="sidebar">
        <button class="close-btn" onclick="toggleSidebar()">&times;</button>
        <a href="home.php"><i class="fas fa-home"></i> Inicio</a>
        <a href="CursosDisponibles.php"><i class="fas fa-book"></i> Cursos Disponibles</a>
        <a href="profile.php"><i class="fas fa-user"></i> Perfil</a>
        <a href="#noticias"><i class="fas fa-newspaper"></i> Noticias</a>
        <a href="#config"><i class="fas fa-cog"></i> Configuración</a>
        <a href="auth.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
    </div>
    
    <header>
        <button class="open-btn" onclick="toggleSidebar()">&#9776;</button>
        <h1>Detalles del Curso</h1>
    </header>

    <main class="content">
        <?php if ($curso): ?>
            <h2><?php echo htmlspecialchars($curso['Nombre_curso']); ?></h2>
            <p><strong>Descripción:</strong> <?php echo htmlspecialchars($curso['Descripcion_curso']); ?></p>

            <h3>Tests</h3>
            <?php if (!empty($tests)): ?>
                <ul>
                    <?php foreach ($tests as $test): ?>
                        <li>
                            <strong><?php echo htmlspecialchars($test['Nombre_test']); ?></strong><br>
                            <?php echo htmlspecialchars($test['Descripcion_test']); ?><br>
                            <strong>Fecha Límite:</strong> <?php echo htmlspecialchars($test['Fecha_limite']); ?><br>
                            <strong>Puntaje Máximo:</strong> <?php echo htmlspecialchars($test['Puntaje']); ?><br>
                            <form action="resolverTarea.php" method="GET" style="margin-top: 10px;">
                                <input type="hidden" name="codigo_tarea" value="<?php echo $test['Codigo_test']; ?>">
                                <button type="submit">Resolver Tarea</button>
                            </form>
                        </li>
                        <br>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No se encontraron tests para este curso.</p>
            <?php endif; ?>

            <h3>Examen Final</h3>
            <?php if ($examenFinalDisponible): ?>
                <form action="../PHP/resolverTarea.php" method="GET">
                    <input type="hidden" name="codigo_curso" value="<?php echo htmlspecialchars($codigoCurso); ?>">
                    <button type="submit">Resolver Examen Final</button>
                </form>
            <?php else: ?>
                <p><strong>Aún no disponible.</strong></p>
            <?php endif; ?>

        <?php else: ?>
            <p>No se encontraron detalles para este curso.</p>
        <?php endif; ?>
    </main>
</body>
</html>
