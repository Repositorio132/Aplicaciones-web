<?php
include "../includes/config/conect.php";
$db = connectDB();

$mensaje = "";

// Obtener cursos
$queryCourses = "SELECT Codigo_curso, Nombre_curso, Tema FROM Curso";
$courses = $db->query($queryCourses)->fetch_all(MYSQLI_ASSOC);

// Manejar envío del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cursoID = intval($_POST['cursoID'] ?? 0);
    $nombreMaterial = trim($_POST['nombreMaterial'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $tipoMaterial = $_POST['tipoMaterial'] ?? 'otros';
    $archivo = $_FILES['archivo'];

    if ($cursoID && $nombreMaterial && isset($archivo['tmp_name']) && !empty($archivo['tmp_name'])) {
        // Obtener el tema asociado al curso
        $queryTema = $db->prepare("SELECT Tema FROM Curso WHERE Codigo_curso = ?");
        $queryTema->bind_param("i", $cursoID);
        $queryTema->execute();
        $temaResult = $queryTema->get_result()->fetch_assoc();
        $temaID = $temaResult['Tema'] ?? null;

        if (!$temaID) {
            $mensaje = "No se encontró el tema asociado al curso seleccionado.";
        } else {
            // Manejar la subida del archivo
            $uploadDir = '../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $filePath = $uploadDir . basename($archivo['name']);
            if (move_uploaded_file($archivo['tmp_name'], $filePath)) {
                try {
                    // Insertar material en la base de datos
                    $estadoPorDefecto = 1; // Suponiendo que existe un estado por defecto
                    $stmt = $db->prepare("INSERT INTO Material_apoyo (Nombre_material, Descripcion, Estado, Tema) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("ssii", $nombreMaterial, $descripcion, $estadoPorDefecto, $temaID);
                    $stmt->execute();

                    $mensaje = "Material de apoyo agregado exitosamente.";
                } catch (Exception $e) {
                    $mensaje = "Error al agregar material: " . $e->getMessage();
                }
            } else {
                $mensaje = "Error al subir el archivo.";
            }
        }
    } else {
        $mensaje = "Todos los campos son obligatorios.";
    }
}

$db->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Material de Apoyo</title>
    <script src="../JavaScript/homeJ.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../css/MaterialApoyo.css">
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
        <h1>Agregar Material de Apoyo</h1>
        <?php if (!empty($mensaje)): ?>
            <p class="alert"><?= htmlspecialchars($mensaje) ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <label for="cursoID">Curso:</label>
            <select id="cursoID" name="cursoID" required>
                <option value="">Seleccione un curso</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?= htmlspecialchars($course['Codigo_curso']) ?>"><?= htmlspecialchars($course['Nombre_curso']) ?></option>
                <?php endforeach; ?>
            </select>

            <label for="nombreMaterial">Nombre del Material:</label>
            <input type="text" id="nombreMaterial" name="nombreMaterial" required>

            <label for="descripcion">Descripción:</label>
            <textarea id="descripcion" name="descripcion" required></textarea>

            <label for="tipoMaterial">Tipo de Material:</label>
            <select id="tipoMaterial" name="tipoMaterial" required>
                <option value="video">Video</option>
                <option value="imagen">Imagen</option>
                <option value="documento">Documento</option>
                <option value="otros">Otros</option>
            </select>

            <label for="archivo" class="custom-file-label">Seleccionar Archivo</label>
<input type="file" id="archivo" name="archivo" accept="video/*,image/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" required>


            <button type="submit">Agregar Material</button>
        </form>
    </div>
</body>
</html>
