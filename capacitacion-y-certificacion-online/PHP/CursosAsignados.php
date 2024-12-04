<?php
session_start();

// Verificar si el usuario está autenticado como capacitador
if (!isset($_SESSION['capacitador_id'])) {
    header("Location: login.php");
    exit();
}

// Obtener el ID del capacitador de la sesión
$capacitador_id = $_SESSION['capacitador_id'];

// Conexión a la base de datos
include '../includes/config/conect.php';

try {
    $db = connectDB();

    // Consulta para obtener los cursos asignados al capacitador
    $query = "
        SELECT 
            c.Codigo_curso,
            c.Nombre_curso,
            c.Descripcion_curso,
            t.Nombre_tema
        FROM 
            Curso c
        LEFT JOIN 
            Tema t ON c.Tema = t.Codigo_tema
        WHERE 
            c.Capacitador = ?";
    
    $stmt = $db->prepare($query);
    $stmt->bind_param("s", $capacitador_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }

    $stmt->close();
    $db->close();
} catch (Exception $e) {
    echo "Error al cargar los cursos: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Cursos</title>
    <link rel="stylesheet" href="../css/viewStudents.css"> <!-- Agrega tu archivo de estilos -->
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
    <main>
        <section class="courses-section">
            <?php if (empty($courses)): ?>
                <p>No tienes cursos asignados actualmente.</p>
            <?php else: ?>
                <table class="courses-table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nombre del Curso</th>
                            <th>Descripción</th>
                            <th>Tema</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $course): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($course['Codigo_curso'] ?? 'Sin código'); ?></td>
                                <td><?php echo htmlspecialchars($course['Nombre_curso'] ?? 'Sin nombre'); ?></td>
                                <td><?php echo htmlspecialchars($course['Descripcion_curso'] ?? 'Sin descripción'); ?></td>
                                <td><?php echo htmlspecialchars($course['Nombre_tema'] ?? 'Sin tema'); ?></td>
                                <td>
                                    <a href="crearTareas.php?curso=<?php echo urlencode($course['Codigo_curso']); ?>" class="assign-task-btn">Asignar Tareas</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </main>
    <script src="../js/scripts.js"></script>
</body>
</html>
