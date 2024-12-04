<?php
session_start();
include '../includes/config/conect.php';
include "../PHP/MessageHandler.php";
include "../PHP/InputValidator.php";

// Handle switching between login and registration
$currentPage = 'login'; // Default page
if (isset($_GET['action'])) {
    $currentPage = ($_GET['action'] == 'register') ? 'register' : 'login';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['matricula'], $_POST['password'])) {
    $matricula = trim($_POST['matricula']);
    $password = trim($_POST['password']);

    // Conexión a la base de datos
    $db = connectDB();

    // Verificar que la matrícula no esté vacía
    if (empty($matricula)) {
        $error = "The registration number is required.";
    } else {
        // Determinar si la matrícula corresponde a un Cursante o Capacitor
        $prefix = substr($matricula, 0, 1);

        if ($prefix === '1') {
            $table = 'Cursante';
            $column = 'Matricula';
        } elseif ($prefix === '2') {
            $table = 'Capacitor';
            $column = 'Codigo_capacitador';
        } else {
            $error = "Invalid registration number format.";
            $db->close();
            exit();
        }

        // Preparar la consulta para la tabla y columna adecuada
        $stmt = $db->prepare("SELECT Password FROM $table WHERE $column = ?");
        $stmt->bind_param("s", $matricula);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            // Verificar si la contraseña es correcta
            if ($password === $row['Password']) {
                $_SESSION['matricula'] = $matricula;

                if ($prefix === '1') {
                    header("Location: home.php");
                } elseif ($prefix === '2') {
                    $_SESSION['capacitador_id'] = $matricula;
                    header("Location: homeCapacitor.php");
                }
                exit();
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "Registration number or code not found.";
        }
        $stmt->close();
    }
    $db->close();
}

// Registration logic
$successMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nombre'], $_POST['apellido_paterno'])) {
    try {
        $conn = connectDB();
        $conn->begin_transaction();

        $nombre = InputValidator::sanitizeInput($_POST['nombre']);
        $apellidoPaterno = InputValidator::sanitizeInput($_POST['apellido_paterno']);
        $apellidoMaterno = InputValidator::sanitizeInput($_POST['apellido_materno']);
        $numeroCelular = InputValidator::sanitizeInput($_POST['numero_celular']);
        $email = InputValidator::sanitizeInput($_POST['email']);
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];
        $userType = $_POST['user_type'];

        if (!$nombre || !$apellidoPaterno || !$apellidoMaterno || !$numeroCelular || !$email || !$password || !$confirmPassword || !$userType) {
            throw new Exception('All fields are required.');
        }

        if ($password !== $confirmPassword) {
            throw new Exception('Passwords do not match.');
        }

        $table = $userType === 'Student' ? 'Cursante' : 'Capacitor';
        $stmt = $conn->prepare("SELECT 1 FROM $table WHERE Correo = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();

        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception('El correo ya está registrado.');
        }

        $stmt = $userType === 'Student'
            ? $conn->prepare("INSERT INTO Cursante (Nombre, Apellido_paterno, Apellido_materno, Telefono, Correo, Password) VALUES (?, ?, ?, ?, ?, ?)")
            : $conn->prepare("INSERT INTO Capacitor (Nombre, Primer_apellido, Segundo_apellido, Numero_celular, Correo, Password) VALUES (?, ?, ?, ?, ?, ?)");

        $stmt->bind_param("ssssss", $nombre, $apellidoPaterno, $apellidoMaterno, $numeroCelular, $email, $password);

        if (!$stmt->execute()) {
            throw new Exception('Error saving the data.');
        }

        // Retrieve registration number or trainer code using email as reference
        $query = $userType === 'Student'
            ? "SELECT Matricula FROM Cursante WHERE Correo = ?"
            : "SELECT Codigo_capacitador FROM Capacitor WHERE Correo = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $id = $result->fetch_assoc();

        if ($userType === 'Student') {
            $matricula = $id['Matricula'];
            $successMessage = "Registration successful. Your registration number is: $matricula. You can now log in.";
        } else {
            $codigo = $id['Codigo_capacitador'];
            $successMessage = "Registration successful. Your trainer code is: $codigo. You can now log in.";
        }

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        $successMessage = "Error: " . $e->getMessage();
    } finally {
        $stmt->close();
        $conn->close();
    }
}
?>

<?php if (!empty($successMessage)): ?>
    <div class="message success">
        <p><?php echo htmlspecialchars($successMessage); ?></p>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="message error">
        <p><?php echo htmlspecialchars($error); ?></p>
    </div>
<?php endif; ?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../css/auth.css">
    <title>FORMULARIO DE REGISTRO E INICIO SESIÓN</title>
</head>
<body>

<style>
.responsive-img {
    width: 100%; /* Ancho fijo */
    height: 100%; /* Altura fija */
}

.margin1 {
    margin: 0 20px; /* Espacio fuera del contenedor hacia los lados */
}

</style>

    <div class="container-form register">
    <div class="information">
            <div class="info-childs">
            <img src='../img/logo2.png' class="responsive-img" width="100">
                <h2>Welcome</h2>
                <p>Please log in to join our learning community</p>
                <input type="button" value="Log in" id="sign-in">
            </div>
        </div>
        <div class="form-information">
            <!-- Mostrar mensaje de éxito o error -->
            <?php if (!empty($successMessage)): ?>
                <div class="message">
                    <p><?php echo htmlspecialchars($successMessage); ?></p>
                </div>
            <?php endif; ?>
            <div class="form-information-childs">
                <h2>Create an account</h2>
                <form class="form form-register" method="POST">
                    <div class="form-group">
                        <label for="nombre">Name:</label>
                        <input type="text" id="nombre" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="apellido_paterno">Last name</label>
                        <input type="text" id="apellido_paterno" name="apellido_paterno" required>
                    </div>
                    <div class="form-group">
                        <label for="apellido_materno">Second last name:</label>
                        <input type="text" id="apellido_materno" name="apellido_materno" required>
                    </div>
                    <div class="form-group">
                        <label for="numero_celular">Cellphone number:</label>
                        <input type="text" id="numero_celular" name="numero_celular" maxlength="10" minlength="10" required>
                        
                    </div>
                    <div class="form-group">
                        <label for="email">Email adress:</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" maxlength="10" minlength="10" pattern="^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{10}$" title="Password must be exactly 10 characters long, include at least 1 uppercase letter, 1 number, and 1 special character." placeholder="Enter a secure password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm password:</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="radio" id="student" name="user_type" value="Student" required>
                            I'm a student.
                        </label>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="radio" id="trainer" name="user_type" value="Trainer" required>
                            I'm a trainer.
                        </label>
                    </div>
                    <button type="submit" class="register-btn">Register</button>
                </form>
            </div>
        </div>
    </div>


    <div class="container-form login hide">
        <div class="information">
            <div class="info-childs">
            <img src='../img/logo2.png' class="responsive-img" width="100">
                <h2>Welcome again!</h2>
                <p>Please log in to join the community</p>
                <input type="button" value="Register" id="sign-up">
            </div>
       </div>
        <div class="form-information">
            <div class="form-information-childs">
                <h2>Log in</h2>
                <form class="form form-login" novalidate method="POST">
                    <div class="form-group">
                        <label for="matricula">Student ID(Registration ID)</label>
                        <input type="text" id="matricula" name="matricula" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <button type="submit" class="login-btn">Log In</button>
                </form>
            </div>
        </div>
    </div>
    <script src="../JavaScript/script.js"></script>
    <script src="../JavaScript/register.js" type="module"></script>
    <!-- <script src="js/iffe_login.js"></script> -->
    <script src="js/login_modulo.js" type="module"></script>
</body>
</html>
