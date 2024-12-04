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

// Funciones para manejar las acciones
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        switch ($action) {
            case "add_cursante":
                $nombre = $_POST['nombre'];
                $apellido_paterno = $_POST['apellido_paterno'];
                $apellido_materno = $_POST['apellido_materno'];
                $correo = $_POST['correo'];
                $telefono = $_POST['telefono'];
                $estado = $_POST['estado'];
                $password = $_POST['password'];

                $sql = "INSERT INTO Cursante (Nombre, Apellido_paterno, Apellido_materno, Correo, Telefono, Estado, Password) 
                        VALUES ('$nombre', '$apellido_paterno', '$apellido_materno', '$correo', '$telefono', $estado, '$password')";

                if ($conn->query($sql) === TRUE) {
                    echo "Cursante agregado exitosamente.";
                } else {
                    echo "Error: " . $conn->error;
                }
                break;

            case "delete_cursante":
                $matricula = $_POST['matricula'];
                $sql = "DELETE FROM Cursante WHERE Matricula = $matricula";
                if ($conn->query($sql) === TRUE) {
                    echo "Cursante eliminado exitosamente.";
                } else {
                    echo "Error: " . $conn->error;
                }
                break;

            case "update_estado":
                $matricula = $_POST['matricula'];
                $nuevo_estado = $_POST['nuevo_estado'];
                $sql = "UPDATE Cursante SET Estado = $nuevo_estado WHERE Matricula = $matricula";

                if ($conn->query($sql) === TRUE) {
                    echo "Estado actualizado exitosamente.";
                } else {
                    echo "Error: " . $conn->error;
                }
                break;
        }
    }
}

// Cargar datos para mostrarlos
$cursantes = $conn->query("
    SELECT C.Matricula, C.Nombre, C.Apellido_paterno, C.Correo, C.Telefono, E.Descripcion AS Estado, E.ID_estado
    FROM Cursante C
    INNER JOIN Estado E ON C.Estado = E.ID_estado
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
    <title>Gestión de Usuarios - Home Admin</title>
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
    <h1 class="text-center">Gestión de Usuarios</h1>
    
  

    <!-- Mostrar cursantes -->
    <h2 class="mt-5">Cursantes Registrados</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Matrícula</th>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Teléfono</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $cursantes->fetch_assoc()) { ?>
                <tr>
                    <td><?= $row['Matricula'] ?></td>
                    <td><?= $row['Nombre'] . ' ' . $row['Apellido_paterno'] ?></td>
                    <td><?= $row['Correo'] ?></td>
                    <td><?= $row['Telefono'] ?></td>
                    <td>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="update_estado">
                            <input type="hidden" name="matricula" value="<?= $row['Matricula'] ?>">
                            <select name="nuevo_estado" class="form-select form-select-sm">
                                <?php foreach ($estados_array as $estado) { ?>
                                    <option value="<?= $estado['ID_estado'] ?>" <?= $row['ID_estado'] == $estado['ID_estado'] ? 'selected' : '' ?>>
                                        <?= $estado['Descripcion'] ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <button type="submit" class="btn btn-success btn-sm mt-1">Actualizar</button>
                        </form>
                    </td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="delete_cursante">
                            <input type="hidden" name="matricula" value="<?= $row['Matricula'] ?>">
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
