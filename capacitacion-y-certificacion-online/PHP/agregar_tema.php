<?php
session_start();

// Configuración de conexión a la base de datos
$servername = "localhost";
$username = "root"; // Cambia según tu configuración
$password = "";
$dbname = "CursosDBN";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Generar un token CSRF único para el formulario
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Manejo del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Solicitud no válida.");
    }

    // Limpiar datos de entrada
    $nombre_tema = trim($_POST['nombre_tema']);
    $descripcion_tema = trim($_POST['descripcion_tema']);

    // Verificar si ya existe un tema con el mismo nombre
    $stmt = $conn->prepare("SELECT COUNT(*) FROM Tema WHERE Nombre_tema = ?");
    $stmt->bind_param("s", $nombre_tema);
    $stmt->execute();
    $stmt->bind_result($exists);
    $stmt->fetch();
    $stmt->close();

    if ($exists > 0) {
        // Redirigir con mensaje de error
        header("Location: " . $_SERVER['PHP_SELF'] . "?error=" . urlencode("Ya existe un tema con el nombre '$nombre_tema'."));
        exit();
    } else {
        $stmt = $conn->prepare("INSERT INTO Tema (Nombre_tema, Descripcion_tema) VALUES (?, ?)");
        $stmt->bind_param("ss", $nombre_tema, $descripcion_tema);

        if ($stmt->execute()) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Regenerar token tras éxito

            // Redirigir con mensaje de éxito
            header("Location: " . $_SERVER['PHP_SELF'] . "?success=" . urlencode("¡Tema '$nombre_tema' creado correctamente!"));
            exit();
        } else {
            // Redirigir con mensaje de error
            header("Location: " . $_SERVER['PHP_SELF'] . "?error=" . urlencode("Error al crear el tema: " . $conn->error));
            exit();
        }
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Tema</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/Tema.css">
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
    <div class="container">
        <h1>Crear Nuevo Tema</h1>

        <!-- Mostrar mensaje de éxito -->
        <?php if (isset($_GET['success'])) : ?>
            <div class="success-message">
                <?php echo strip_tags($_GET['success']); ?>
            </div>
        <?php endif; ?>

        <!-- Mostrar mensaje de error -->
        <?php if (isset($_GET['error'])) : ?>
            <div class="error-message">
                <?php echo strip_tags($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="">
            <!-- Campo oculto para token CSRF -->
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <label for="nombre_tema">Nombre del Tema:</label>
            <input type="text" id="nombre_tema" name="nombre_tema" required>

            <label for="descripcion_tema">Descripción del Tema:</label>
            <textarea id="descripcion_tema" name="descripcion_tema" rows="4" cols="50" required></textarea>

            <button type="submit">Crear Tema</button>
        </form>


    </div>
</body>
</html>
