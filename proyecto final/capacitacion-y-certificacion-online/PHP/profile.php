<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['matricula'])) {
    header("Location: login.php");
    exit();
}

function connectDB(): mysqli {
    $db = mysqli_connect("localhost", "root", "", "CursosDBN");
    if (!$db) {
        die("Error de conexión: " . mysqli_connect_error());
    }
    return $db;
}

$db = connectDB();

// Obtener los datos del cursante
$matricula = $_SESSION['matricula'];
$usuario = [];
$stmtUsuario = $db->prepare("
    SELECT 
        C.Nombre, 
        C.Apellido_paterno, 
        C.Apellido_materno, 
        C.Correo, 
        C.Telefono, 
        E.Descripcion AS Estado
    FROM Cursante C
    LEFT JOIN Estado E ON C.Estado = E.ID_estado
    WHERE C.Matricula = ?
");
$stmtUsuario->bind_param("i", $matricula);
$stmtUsuario->execute();
$resultUsuario = $stmtUsuario->get_result();

if ($resultUsuario->num_rows > 0) {
    $usuario = $resultUsuario->fetch_assoc();
} else {
    die("No se encontraron datos para el usuario.");
}

$stmtUsuario->close();

// Obtener información de los cursos inscritos
$cursos = [];
$stmtCursos = $db->prepare("
    SELECT 
        Cu.Nombre_curso, 
        Cu.Descripcion_curso, 
        RC.Fecha_inscripcion, 
        RC.Fecha_inicio, 
        RC.Fecha_fin, 
        RC.Calificaciones, 
        RC.Progreso 
    FROM Registro_curso RC
    INNER JOIN Curso Cu ON RC.Curso = Cu.Codigo_curso
    WHERE RC.Cursante = ?
");
$stmtCursos->bind_param("i", $matricula);
$stmtCursos->execute();
$resultCursos = $stmtCursos->get_result();

while ($row = $resultCursos->fetch_assoc()) {
    $cursos[] = $row;
}

$stmtCursos->close();

mysqli_close($db);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil del Usuario</title>
    <link rel="stylesheet" href="../css/profile.css">
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

    <main>
        <!-- Información del cursante -->
        <section>
            <h2>Información Personal</h2>
            <p><strong>Nombre:</strong> <?php echo htmlspecialchars($usuario['Nombre'] ?? 'N/A') . ' ' . htmlspecialchars($usuario['Apellido_paterno'] ?? 'N/A') . ' ' . htmlspecialchars($usuario['Apellido_materno'] ?? 'N/A'); ?></p>
            <p><strong>Correo:</strong> <?php echo htmlspecialchars($usuario['Correo'] ?? 'N/A'); ?></p>
            <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($usuario['Telefono'] ?? 'N/A'); ?></p>
            <p><strong>Estado:</strong> <?php echo htmlspecialchars($usuario['Estado'] ?? 'N/A'); ?></p>
        </section>

        <!-- Cursos inscritos -->
        <section>
            <h2>Mis Cursos</h2>
            <?php if (!empty($cursos)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Curso</th>
                            <th>Descripción</th>
                            <th>Fecha Inscripción</th>
                            <th>Inicio</th>
                            <th>Fin</th>
                            <th>Calificaciones</th>
                            <th>Progreso</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cursos as $curso): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($curso['Nombre_curso'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($curso['Descripcion_curso'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($curso['Fecha_inscripcion'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($curso['Fecha_inicio'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($curso['Fecha_fin'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($curso['Calificaciones'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($curso['Progreso'] ?? '0') . '%'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No estás inscrito en ningún curso actualmente.</p>
            <?php endif; ?>
        </section>

        <!-- Actualización de información -->
        <section>
            <h2>Actualizar Información</h2>
            <a href="updateProfile.php" class="button">Actualizar Información</a>
        </section>
    </main>
</body>
</html>
