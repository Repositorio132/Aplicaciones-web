<?php
// Configuración de conexión a la base de datos
$host = "localhost";
$user = "root";
$password = "";
$dbname = "CursosDBN";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Procesar la solicitud de baja o activación
$mensaje = "";
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['matricula']) && isset($_GET['accion'])) {
    $matricula = $_GET['matricula'];
    $accion = $_GET['accion'];

    if ($accion === 'baja') {
        $query = "UPDATE Registro_curso SET Estado = (SELECT ID_estado FROM Estado WHERE Descripcion = 'Baja') WHERE Cursante = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $matricula);

        if ($stmt->execute()) {
            $mensaje = "El alumno ha sido dado de baja.";
        } else {
            $mensaje = "Error al dar de baja al alumno: " . $stmt->error;
        }
        $stmt->close();
    } elseif ($accion === 'activar') {
        $query = "UPDATE Registro_curso SET Estado = (SELECT ID_estado FROM Estado WHERE Descripcion = 'Activo') WHERE Cursante = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $matricula);

        if ($stmt->execute()) {
            $mensaje = "El alumno ha sido activado nuevamente.";
        } else {
            $mensaje = "Error al activar al alumno: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Consulta para obtener los alumnos inscritos con su progreso
$query = "
    SELECT 
        rc.Cursante AS Matricula,
        c.Nombre AS NombreAlumno,
        cu.Nombre_curso AS Nombre_Curso,
        e.Descripcion AS Estado,
        COALESCE(rc.Progreso, 0) AS Progreso
    FROM Registro_curso rc
    INNER JOIN Cursante c ON rc.Cursante = c.Matricula
    INNER JOIN Curso cu ON rc.Curso = cu.Codigo_curso
    INNER JOIN Estado e ON rc.Estado = e.ID_estado;
";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumnos Inscritos por Curso</title>
    <link rel="stylesheet" href="../css/viewStudents.css">
    <script src="../JavaScript/homeJ.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script>
        function confirmarBaja(matricula) {
            if (confirm('¿Estás seguro de que deseas dar de baja a este alumno?')) {
                window.location.href = "viewStudents.php?matricula=" + matricula + "&accion=baja";
            }
        }
        function confirmarActivacion(matricula) {
            if (confirm('¿Estás seguro de que deseas activar nuevamente a este alumno?')) {
                window.location.href = "viewStudents.php?matricula=" + matricula + "&accion=activar";
            }
        }
    </script>
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

    <h1>Alumnos Inscritos por Curso</h1>

    <div class="search-bar">
        <form method="GET">
            <input type="text" name="search" placeholder="Buscar por matrícula..." />
            <button type="submit">Buscar</button>
        </form>
    </div>

    <?php if ($mensaje): ?>
        <p class="mensaje"><?php echo htmlspecialchars($mensaje); ?></p>
    <?php endif; ?>

    <?php if ($result && $result->num_rows > 0): ?>
        <table border="1">
            <thead>
                <tr>
                    <th>Matrícula</th>
                    <th>Nombre del Alumno</th>
                    <th>Curso</th>
                    <th>Estado</th>
                    <th>Progreso (%)</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['Matricula'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($row['NombreAlumno'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($row['Nombre_Curso'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($row['Estado'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars((string)$row['Progreso']); ?>%</td>
                        <td>
                            <?php if ($row['Estado'] === 'Activo'): ?>
                                <a href="javascript:void(0);" onclick="confirmarBaja('<?php echo htmlspecialchars($row['Matricula']); ?>')" class="btn-baja">Baja</a>
                            <?php elseif ($row['Estado'] === 'Baja'): ?>
                                <a href="javascript:void(0);" onclick="confirmarActivacion('<?php echo htmlspecialchars($row['Matricula']); ?>')" class="btn-activar">Activar</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No hay alumnos inscritos en ningún curso.</p>
    <?php endif; ?>

    <?php $conn->close(); ?>
</body>
</html>
