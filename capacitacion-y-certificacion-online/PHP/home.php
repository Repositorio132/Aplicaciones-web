<?php
session_start();

// Conexión a la base de datos
function connectDB(): mysqli {
    $db = mysqli_connect("localhost", "root", "", "CursosDBN");
    if (!$db) {
        error_log("Error de conexión a la base de datos: " . mysqli_connect_error());
        die("Error de conexión: " . mysqli_connect_error());
    }
    return $db;
}

$db = connectDB();

// Obtener la matrícula del usuario desde la sesión
$matricula = $_SESSION['matricula'] ?? null;

// Validar que la matrícula exista y sea válida
if (!$matricula || !is_numeric($matricula)) {
    die("Error: Matrícula inválida o no encontrada en la sesión.");
}

// Consultar los cursos en los que el usuario está inscrito
$cursos = [];
$stmt = $db->prepare("
    SELECT c.Codigo_curso, c.Nombre_curso 
    FROM Curso c
    JOIN Registro_curso rc ON c.Codigo_curso = rc.Curso
    WHERE rc.Cursante = ?
");

if (!$stmt) {
    die("Error preparando la consulta: " . $db->error);
}

$stmt->bind_param("i", $matricula);
$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $cursos[] = $row;
}

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

    <!-- Contenido principal -->
    <main class="content">
        <h2>¡BIENVENIDO, <?php echo isset($_SESSION['nombre']) ? htmlspecialchars($_SESSION['nombre']) : 'Cursante'; ?>!</h2>
        <div class="card-grid">
            <!-- Mostrar cursos en los que el usuario está inscrito -->
            <?php if (count($cursos) > 0): ?>
                <?php foreach ($cursos as $curso): ?>
                    <div class="card">
                        <h2><?php echo htmlspecialchars($curso['Nombre_curso']); ?></h2>
                        <p>Código: <?php echo htmlspecialchars($curso['Codigo_curso']); ?></p>
                        <a href="verCurso.php?codigo_curso=<?php echo urlencode($curso['Codigo_curso']); ?>">Ver curso</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No estás inscrito en ningún curso.</p>
                <a href="CursosDisponibles.php">Explorar cursos disponibles</a>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
