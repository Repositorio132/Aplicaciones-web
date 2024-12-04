<?php
require('../PHP/fpdf186/fpdf.php');

session_start();

// Conexión a la base de datos
function connectDB(): mysqli {
    $db = mysqli_connect("localhost", "root", "", "CursosDBN");
    if (!$db) {
        die("Error de conexión: " . mysqli_connect_error());
    }
    return $db;
}

// Obtener datos del cursante y el test
$cursanteId = $_SESSION['matricula'] ?? null; // ID del cursante desde la sesión
$codigoTest = $_GET['codigo_tarea'] ?? null;  // Código del test enviado por URL

if (!$cursanteId || !$codigoTest) {
    die("Error: Falta información del cursante o del test.");
}

$db = connectDB();

try {
    // Verificar la calificación final del cursante
    $stmt = $db->prepare("
        SELECT 
            Cursante.Nombre AS AlumnoNombre,
            Cursante.Matricula AS AlumnoMatricula,
            Curso.Nombre_curso AS CursoNombre,
            Registro_curso.Calificaciones AS Calificacion
        FROM Registro_curso
        INNER JOIN Cursante ON Registro_curso.Cursante = Cursante.Matricula
        INNER JOIN Curso ON Registro_curso.Curso = Curso.Codigo_curso
        INNER JOIN Test ON Test.Curso = Curso.Codigo_curso
        WHERE Cursante.Matricula = ? AND Test.Codigo_test = ?
    ");
    $stmt->bind_param("ii", $cursanteId, $codigoTest);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if (!$data || $data['Calificacion'] < 70) {
        die("No se alcanzó la calificación mínima para generar el certificado.");
    }

    // Generar el certificado con FPDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);

    // Título
    $pdf->Cell(0, 10, 'Certificado de Finalización', 0, 1, 'C');
    $pdf->Ln(10);

    // Detalles del cursante y curso
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'Este certificado se otorga a:', 0, 1, 'C');
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, strtoupper($data['AlumnoNombre']), 0, 1, 'C');
    $pdf->Ln(5);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'Matrícula: ' . $data['AlumnoMatricula'], 0, 1, 'C');
    $pdf->Ln(5);
    $pdf->Cell(0, 10, 'Por haber completado el curso:', 0, 1, 'C');
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, $data['CursoNombre'], 0, 1, 'C');
    $pdf->Ln(20);

    // Footer
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 10, 'Fecha de emisión: ' . date('d/m/Y'), 0, 1, 'C');

    // Descargar PDF
    $pdf->Output('D', 'Certificado_' . $data['AlumnoMatricula'] . '.pdf');
} catch (Exception $e) {
    echo "Error al generar el certificado: " . $e->getMessage();
} finally {
    $db->close();
}
