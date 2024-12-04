<?php
session_start();

// Verificar si el capacitador ha iniciado sesión
if (!isset($_SESSION['capacitador_id'])) {
    header("Location: login.php");
    exit();
}

$capacitadorId = intval($_SESSION['capacitador_id']); // ID del capacitador

// Conexión a la base de datos
function connectDB(): mysqli {
    $db = mysqli_connect("localhost", "root", "", "CursosDBN");
    if (!$db) {
        die("Error de conexión: " . mysqli_connect_error());
    }
    return $db;
}

$db = connectDB();

// Obtener información del capacitador
$queryCapacitador = "
    SELECT 
        Codigo_capacitador,
        Nombre,
        Primer_apellido,
        Segundo_apellido,
        Numero_celular,
        Correo
    FROM Capacitor
    WHERE Codigo_capacitador = ?
";
$stmtCapacitador = $db->prepare($queryCapacitador);
$stmtCapacitador->bind_param("i", $capacitadorId);
$stmtCapacitador->execute();
$capacitador = $stmtCapacitador->get_result()->fetch_assoc();
$stmtCapacitador->close();

// Obtener los cursos asignados al capacitador
$queryCursos = "
    SELECT 
        C.Nombre_curso, 
        C.Descripcion_curso, 
        RC.Fecha_inicio, 
        RC.Fecha_fin
    FROM Curso C
    LEFT JOIN Registro_curso RC ON C.Codigo_curso = RC.Curso
    WHERE C.Capacitador = ?
";
$stmtCursos = $db->prepare($queryCursos);
$stmtCursos->bind_param("i", $capacitadorId);
$stmtCursos->execute();
$resultCursos = $stmtCursos->get_result();

$cursos = [];
while ($row = $resultCursos->fetch_assoc()) {
    $cursos[] = $row;
}

$stmtCursos->close();
mysqli_close($db);

// Verificar si se encontró el capacitador
if (!$capacitador) {
    die("Capacitador no encontrado.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil del Capacitor</title>
    <link rel="stylesheet" href="../css/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
        <!-- Información del capacitador -->
        <section>
            <h2>Información Personal</h2>
            <p><strong>ID Capacitor:</strong> <?php echo htmlspecialchars($capacitador['Codigo_capacitador'] ?? 'N/A'); ?></p>
            <p><strong>Nombre Completo:</strong> <?php echo htmlspecialchars($capacitador['Nombre'] ?? 'N/A') . ' ' . htmlspecialchars($capacitador['Primer_apellido'] ?? 'N/A') . ' ' . htmlspecialchars($capacitador['Segundo_apellido'] ?? 'N/A'); ?></p>
            <p><strong>Correo Electrónico:</strong> <?php echo htmlspecialchars($capacitador['Correo'] ?? 'N/A'); ?></p>
            <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($capacitador['Numero_celular'] ?? 'N/A'); ?></p>
        </section>

        <!-- Cursos asignados -->
        <section>
            <h2>Mis Cursos Asignados</h2>
            <?php if (!empty($cursos)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Curso</th>
                            <th>Descripción</th>
                            <th>Inicio</th>
                            <th>Fin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cursos as $curso): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($curso['Nombre_curso'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($curso['Descripcion_curso'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($curso['Fecha_inicio'] ?? 'No registrada'); ?></td>
                                <td><?php echo htmlspecialchars($curso['Fecha_fin'] ?? 'No registrada'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No tienes cursos asignados actualmente.</p>
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
