<?php
// Conexión a la base de datos
$host = "localhost";
$dbname = "CursosDB";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Lógica para agregar un examen
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo_curso = $_POST['codigo_curso'];
    $tipo_examen = $_POST['tipo_examen'];
    $hora_inicio = $_POST['hora_inicio'];
    $hora_fin = $_POST['hora_fin'];
    $puntaje = $_POST['puntaje'];

    $sql = "INSERT INTO Examen (Codigo_Curso, TipoExamen, Hora_Inicio, Hora_Fin, Puntaje) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$codigo_curso, $tipo_examen, $hora_inicio, $hora_fin, $puntaje]);

    $mensaje = "Examen agregado exitosamente.";
}

// Consulta para obtener los exámenes existentes
$sql = "SELECT e.Codigo_Examen, e.TipoExamen, e.Hora_Inicio, e.Hora_Fin, e.Puntaje, c.Nombre_Curso 
        FROM Examen e 
        INNER JOIN Curso c ON e.Codigo_Curso = c.Codigo_Curso";
$examenes = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Consulta para obtener los cursos disponibles
$sqlCursos = "SELECT Codigo_Curso, Nombre_Curso FROM Curso";
$cursos = $pdo->query($sqlCursos)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Exámenes</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        th { background-color: #f2f2f2; }
        form { margin-bottom: 20px; }
    </style>
</head>
<body>
    <h1>Gestión de Exámenes</h1>

    <?php if (!empty($mensaje)): ?>
        <p style="color: green;"><?php echo $mensaje; ?></p>
    <?php endif; ?>

    <h2>Agregar Examen</h2>
    <form method="post">
        <label for="codigo_curso">Curso:</label>
        <select id="codigo_curso" name="codigo_curso" required>
            <option value="">Seleccione un curso</option>
            <?php foreach ($cursos as $curso): ?>
                <option value="<?php echo $curso['Codigo_Curso']; ?>">
                    <?php echo $curso['Nombre_Curso']; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br><br>
        <label for="tipo_examen">Tipo de Examen:</label>
        <input type="text" id="tipo_examen" name="tipo_examen" required>
        <br><br>
        <label for="hora_inicio">Hora de Inicio:</label>
        <input type="time" id="hora_inicio" name="hora_inicio" required>
        <br><br>
        <label for="hora_fin">Hora de Fin:</label>
        <input type="time" id="hora_fin" name="hora_fin" required>
        <br><br>
        <label for="puntaje">Puntaje Máximo:</label>
        <input type="number" step="0.01" id="puntaje" name="puntaje" required>
        <br><br>
        <button type="submit">Agregar Examen</button>
    </form>

    <h2>Lista de Exámenes</h2>
    <table>
        <thead>
            <tr>
                <th>Código de Examen</th>
                <th>Curso</th>
                <th>Tipo de Examen</th>
                <th>Hora de Inicio</th>
                <th>Hora de Fin</th>
                <th>Puntaje Máximo</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($examenes) > 0): ?>
                <?php foreach ($examenes as $examen): ?>
                    <tr>
                        <td><?php echo $examen['Codigo_Examen']; ?></td>
                        <td><?php echo $examen['Nombre_Curso']; ?></td>
                        <td><?php echo $examen['TipoExamen']; ?></td>
                        <td><?php echo $examen['Hora_Inicio']; ?></td>
                        <td><?php echo $examen['Hora_Fin']; ?></td>
                        <td><?php echo $examen['Puntaje']; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">No hay exámenes registrados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
