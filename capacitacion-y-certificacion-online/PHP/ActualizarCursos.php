<?php
// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "CursosDBN";

$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Manejar acciones
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        switch ($action) {
            case "update_estado":
                $curso_id = $_POST['curso_id'];
                $nuevo_estado = $_POST['nuevo_estado'];
                $stmt = $conn->prepare("UPDATE Registro_curso SET Estado = ? WHERE Curso = ?");
                $stmt->bind_param("ii", $nuevo_estado, $curso_id);
                if ($stmt->execute()) {
                    echo "Estado del curso actualizado exitosamente.";
                } else {
                    echo "Error: " . $conn->error;
                }
                $stmt->close();
                break;

            case "delete_curso":
                $curso_id = $_POST['curso_id'];
                $stmt = $conn->prepare("DELETE FROM Curso WHERE Codigo_curso = ?");
                $stmt->bind_param("i", $curso_id);
                if ($stmt->execute()) {
                    echo "Curso eliminado exitosamente.";
                } else {
                    echo "Error: " . $conn->error;
                }
                $stmt->close();
                break;
        }
    }
}

// Consultar cursos y estados
$cursos = $conn->query("
    SELECT RC.Curso AS Codigo_curso, C.Nombre_curso AS Nombre, C.Descripcion_curso AS Descripcion, 
           RC.Estado AS ID_estado, E.Descripcion AS Estado_descripcion
    FROM Registro_curso RC
    JOIN Curso C ON RC.Curso = C.Codigo_curso
    JOIN Estado E ON RC.Estado = E.ID_estado
");

$estados = $conn->query("SELECT * FROM Estado");
$estados_array = [];
while ($estado = $estados->fetch_assoc()) {
    $estados_array[] = $estado;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Cursos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/admin.css">
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
<div class="container mt-5">
    <h1 class="text-center">Gestión de Cursos</h1>

    <!-- Mostrar cursos -->
    <h2 class="mt-4">Lista de Cursos</h2>
    <table class="table">
        <thead>
            <tr>
                <th>ID Curso</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($curso = $cursos->fetch_assoc()) { ?>
                <tr>
                    <td><?= $curso['Codigo_curso'] ?></td>
                    <td><?= $curso['Nombre'] ?></td>
                    <td><?= $curso['Descripcion'] ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="update_estado">
                            <input type="hidden" name="curso_id" value="<?= $curso['Codigo_curso'] ?>">
                            <select name="nuevo_estado" class="form-select form-select-sm">
                                <?php foreach ($estados_array as $estado) { ?>
                                    <option value="<?= $estado['ID_estado'] ?>" <?= $curso['ID_estado'] == $estado['ID_estado'] ? 'selected' : '' ?>>
                                        <?= $estado['Descripcion'] ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <button type="submit" class="btn btn-success btn-sm mt-1">Actualizar</button>
                        </form>
                    </td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="delete_curso">
                            <input type="hidden" name="curso_id" value="<?= $curso['Codigo_curso'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
</body>
</html>
