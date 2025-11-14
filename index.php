<?php
require_once 'config/database.php';
require_once 'config/session.php';

// Si ya está logueado, redirigir al dashboard
if (isLoggedIn()) {
    header('Location: parent/dashboard.php');
    exit();
}

$error = '';

// Procesar el login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dni = $_POST['dni'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($dni) && !empty($password)) {
        $conn = getConnection();

        // Buscar al padre por DNI
        $stmt = $conn->prepare("SELECT id, dni, password, nombre, apellido FROM padres WHERE dni = ?");
        $stmt->bind_param("s", $dni);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $padre = $result->fetch_assoc();

            // Verificar la contraseña
            if (password_verify($password, $padre['password'])) {
                // Login exitoso
                $_SESSION['padre_id'] = $padre['id'];
                $_SESSION['padre_dni'] = $padre['dni'];
                $_SESSION['padre_nombre'] = $padre['nombre'] . ' ' . $padre['apellido'];

                header('Location: parent/dashboard.php');
                exit();
            } else {
                $error = 'DNI o contraseña incorrectos';
            }
        } else {
            $error = 'DNI o contraseña incorrectos';
        }

        $stmt->close();
        $conn->close();
    } else {
        $error = 'Por favor complete todos los campos';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Notas - Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <div style="font-size: 3.5rem; margin-bottom: 0.5rem;">🎓</div>
                <h1>Sistema de Notas Escolares</h1>
                <p>Portal para Padres de Familia</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="index.php" class="login-form">
                <div class="form-group">
                    <label for="dni">DNI del Padre/Tutor</label>
                    <input
                        type="text"
                        id="dni"
                        name="dni"
                        required
                        maxlength="20"
                        placeholder="Ingrese su DNI"
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        placeholder="Ingrese su contraseña"
                    >
                </div>

                <button type="submit" class="btn btn-primary btn-block">Iniciar Sesión</button>
            </form>

            <div class="login-footer">
                <p><small>Para acceder, use su DNI como usuario y contraseña</small></p>
                <p><small>Si tiene problemas para acceder, contacte con la administración del colegio</small></p>
            </div>
        </div>
    </div>
</body>
</html>
